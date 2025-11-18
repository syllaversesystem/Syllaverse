<?php

namespace App\Http\Controllers\Faculty\Syllabus;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SyllabusCdio;
use App\Models\Syllabus;
use Illuminate\Support\Facades\Auth;

class SyllabusCdioController extends Controller
{
    public function update(Request $request, $syllabusId)
    {
        // Accepts an array of cdios and upserts them for a syllabus
        try { 
            \Log::debug('SyllabusCdioController::update called', ['syllabusId' => $syllabusId, 'user' => Auth::id(), 'payload_keys' => array_keys($request->all())]);
            try { \Log::debug('SyllabusCdioController::update payload', ['cdios' => $request->input('cdios')]); } catch (\Throwable $__e) { /* noop */ }
        } catch (\Throwable $__e) { /* noop */ }
    $syllabus = $this->getSyllabusForAction($syllabusId);
        $items = $request->input('cdios');
        if (! is_array($items)) {
            return response()->json(['ok' => false, 'message' => 'Invalid payload'], 422);
        }

        // delete existing and recreate (simple, mirrors SO approach)
        SyllabusCdio::where('syllabus_id', $syllabus->id)->delete();
        $pos = 1;
        foreach ($items as $it) {
            SyllabusCdio::create([
                'syllabus_id' => $syllabus->id,
                'code' => $it['code'] ?? null,
                'title' => $it['title'] ?? null,
                'description' => $it['description'] ?? null,
                'position' => $it['position'] ?? $pos++,
            ]);
        }

        return response()->json(['ok' => true]);
    }

    public function reorder(Request $request, $syllabusId)
    {
        $syllabus = $this->getSyllabusForAction($syllabusId);

        // Support two payload shapes: positions: [{id,pos}] or order: [id,...]
        $positions = $request->input('positions');
        if (is_array($positions)) {
            foreach ($positions as $item) {
                if (isset($item['id']) && isset($item['position'])) {
                    SyllabusCdio::where('id', $item['id'])->where('syllabus_id', $syllabus->id)->update(['position' => $item['position']]);
                }
            }
            return response()->json(['ok' => true]);
        }

        $order = $request->input('order');
        if (! is_array($order)) return response()->json(['ok' => false], 422);
        foreach ($order as $index => $id) {
            SyllabusCdio::where('id', $id)->where('syllabus_id', $syllabus->id)->update(['position' => $index + 1]);
        }
        return response()->json(['ok' => true]);
    }

    public function destroy($id)
    {
        $cdio = SyllabusCdio::findOrFail($id);
        $cdio->delete();
        return response()->json(['ok' => true, 'message' => 'CDIO deleted successfully']);
    }

    public function loadPredefinedCdios(Request $request, $syllabus)
    {
        // Authorization check - faculty only
        $syllabus = Syllabus::where('faculty_id', Auth::id())->findOrFail($syllabus);

        // Validate that cdio_ids is provided and is an array
        $request->validate([
            'cdio_ids' => 'required|array',
            'cdio_ids.*' => 'integer|exists:cdios,id',
        ]);

        $selectedIds = $request->cdio_ids;

        if (empty($selectedIds)) {
            return response()->json(['message' => 'Please select at least one CDIO to load.'], 400);
        }

        // Get selected predefined CDIOs from master data
        $predefinedCdios = \App\Models\Cdio::whereIn('id', $selectedIds)->orderBy('id')->get();

        if ($predefinedCdios->isEmpty()) {
            return response()->json(['message' => 'No predefined CDIOs found.'], 404);
        }

        // Delete existing CDIOs for this syllabus
        SyllabusCdio::where('syllabus_id', $syllabus->id)->delete();

        // Create new CDIOs from predefined data
        $newCdios = [];
        foreach ($predefinedCdios as $index => $predefined) {
            $cdio = SyllabusCdio::create([
                'syllabus_id' => $syllabus->id,
                'code' => 'CDIO' . ($index + 1),
                'title' => $predefined->title,
                'description' => $predefined->description,
                'position' => $index + 1,
            ]);
            $newCdios[] = [
                'id' => $cdio->id,
                'code' => $cdio->code,
                'title' => $cdio->title,
                'description' => $cdio->description,
                'position' => $cdio->position,
            ];
        }

        return response()->json([
            'message' => count($newCdios) . ' CDIO' . (count($newCdios) !== 1 ? 's' : '') . ' loaded successfully.',
            'cdios' => $newCdios,
        ]);
    }

    // Add single CDIO (used by inline add flows)
    public function store(Request $request)
    {
        $request->validate([
            'syllabus_id' => 'required|exists:syllabi,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000'
        ]);

    $syllabus = $this->getSyllabusForAction($request->syllabus_id);

        $max = SyllabusCdio::where('syllabus_id', $syllabus->id)->max('position');
        $pos = ($max ?? 0) + 1;

        $cdio = SyllabusCdio::create([
            'syllabus_id' => $syllabus->id,
            'code' => 'CDIO' . $pos,
            'title' => $request->input('title'),
            'description' => $request->description,
            'position' => $pos,
        ]);

        return response()->json(['message' => 'CDIO added.', 'id' => $cdio->id]);
    }

    // Inline update: update description only
    public function inlineUpdate(Request $request, $syllabusId, $cdioId)
    {
        $request->validate(['title' => 'nullable|string|max:255', 'description' => 'nullable|string|max:1000']);
        $syllabus = $this->getSyllabusForAction($syllabusId);
        $cdio = SyllabusCdio::where('syllabus_id', $syllabus->id)->findOrFail($cdioId);
        $cdio->update([
            'title' => $request->input('title'),
            'description' => $request->description
        ]);
        return response()->json(['message' => 'CDIO updated.']);
    }

    /**
     * Resolve syllabus by id - faculty can only access their own syllabi.
     */
    protected function getSyllabusForAction($syllabusId)
    {
        return Syllabus::where('faculty_id', Auth::id())->findOrFail($syllabusId);
    }
}
