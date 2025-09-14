<?php

namespace App\Http\Controllers\Faculty;

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
        $syllabus = $cdio->syllabus;
        if (! $syllabus || ($syllabus->faculty_id !== Auth::id() && ! Auth::guard('admin')->check())) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }
        $cdio->delete();
        return response()->json(['ok' => true]);
    }

    // Add single CDIO (used by inline add flows)
    public function store(Request $request)
    {
        $request->validate([
            'syllabus_id' => 'required|exists:syllabi,id',
            'description' => 'nullable|string|max:1000'
        ]);

    $syllabus = $this->getSyllabusForAction($request->syllabus_id);

        $max = SyllabusCdio::where('syllabus_id', $syllabus->id)->max('position');
        $pos = ($max ?? 0) + 1;

        $cdio = SyllabusCdio::create([
            'syllabus_id' => $syllabus->id,
            'code' => 'CDIO' . $pos,
            'description' => $request->description,
            'position' => $pos,
        ]);

        return response()->json(['message' => 'CDIO added.', 'id' => $cdio->id]);
    }

    // Inline update: update description only
    public function inlineUpdate(Request $request, $syllabusId, $cdioId)
    {
        $request->validate(['description' => 'nullable|string|max:1000']);
        $syllabus = $this->getSyllabusForAction($syllabusId);
        $cdio = SyllabusCdio::where('syllabus_id', $syllabus->id)->findOrFail($cdioId);
        $cdio->update(['description' => $request->description]);
        return response()->json(['message' => 'CDIO updated.']);
    }

    /**
     * Resolve syllabus by id with admin-aware scoping.
     * Admin users may access any syllabus; faculty may only access their own.
     */
    protected function getSyllabusForAction($syllabusId)
    {
        if (Auth::guard('admin')->check()) {
            return Syllabus::findOrFail($syllabusId);
        }
        return Syllabus::where('faculty_id', Auth::id())->findOrFail($syllabusId);
    }
}
