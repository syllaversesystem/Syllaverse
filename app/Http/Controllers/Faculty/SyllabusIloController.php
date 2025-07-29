<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/Faculty/SyllabusIloController.php
// * Description: Handles CRUD and sorting for syllabus-specific ILOs – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Regenerated to support inline updates, drag-reorder, add and delete.
// [2025-07-29] Updated reorder method to accept structured payload: id, position, code.
// [2025-07-29] Synced reorder() structure to match SO sortable logic.
// [2025-07-29] Refactored update() method to accept structured object array (id, code, description, position).
// -------------------------------------------------------------------------------


namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Syllabus;
use App\Models\SyllabusIlo;
use Illuminate\Support\Facades\Auth;

class SyllabusIloController extends Controller
{
    // 📝 Updates all ILOs in batch with id, code, description, position
    public function update(Request $request, $syllabusId)
    {
        $syllabus = Syllabus::where('faculty_id', Auth::id())->findOrFail($syllabusId);

        $request->validate([
            'ilos' => 'required|array',
            'ilos.*.id' => 'nullable|integer|exists:syllabus_ilos,id',
            'ilos.*.code' => 'required|string',
            'ilos.*.description' => 'required|string|max:1000',
            'ilos.*.position' => 'required|integer',
        ]);

        $incomingIds = collect($request->ilos)->pluck('id')->filter();
        $existingIds = SyllabusIlo::where('syllabus_id', $syllabus->id)->pluck('id');

        // 🔥 Delete ILOs that were removed in frontend
        $toDelete = $existingIds->diff($incomingIds);
        SyllabusIlo::whereIn('id', $toDelete)->delete();

        // 🔄 Upsert ILOs based on submitted payload
        foreach ($request->ilos as $iloData) {
            SyllabusIlo::updateOrCreate(
                ['id' => $iloData['id'] ?? null],
                [
                    'syllabus_id' => $syllabus->id,
                    'code' => $iloData['code'],
                    'description' => $iloData['description'],
                    'position' => $iloData['position'],
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'ILOs updated successfully.']);
    }

    // ➕ Adds a new ILO
    public function store(Request $request)
    {
        $request->validate([
            'syllabus_id' => 'required|exists:syllabi,id',
            'description' => 'nullable|string|max:1000'
        ]);

        $max = SyllabusIlo::where('syllabus_id', $request->syllabus_id)->max('position');

        $ilo = SyllabusIlo::create([
            'syllabus_id' => $request->syllabus_id,
            'code' => 'ILO' . (($max ?? 0) + 1),
            'description' => $request->description,
            'position' => ($max ?? 0) + 1
        ]);

        return response()->json(['message' => 'ILO added.', 'id' => $ilo->id]);
    }

    // ✏️ Updates only the description (used for inline update)
    public function inlineUpdate(Request $request, $syllabusId, $iloId)
    {
        $request->validate([
            'description' => 'nullable|string|max:1000',
        ]);

        $ilo = SyllabusIlo::where('syllabus_id', $syllabusId)->findOrFail($iloId);
        $ilo->update(['description' => $request->description]);

        return back()->with('success', 'ILO updated.');
    }

    // ❌ Deletes one ILO
    public function destroy($id)
    {
        $ilo = SyllabusIlo::findOrFail($id);
        $ilo->delete();

        return response()->json(['message' => 'ILO deleted.']);
    }

    // 🔁 Reorder endpoint (optional if you split order from save)
    public function reorder(Request $request)
    {
        $request->validate([
            'positions' => 'required|array',
            'positions.*.id' => 'required|integer|exists:syllabus_ilos,id',
            'positions.*.code' => 'required|string',
            'positions.*.position' => 'required|integer',
            'syllabus_id' => 'required|exists:syllabi,id'
        ]);

        foreach ($request->positions as $item) {
            SyllabusIlo::where('id', $item['id'])
                ->where('syllabus_id', $request->syllabus_id)
                ->update([
                    'code' => $item['code'],
                    'position' => $item['position']
                ]);
        }

        return response()->json(['message' => 'ILO order updated successfully.']);
    }
}
