<?php

// File: app/Http/Controllers/Faculty/SyllabusIloController.php
// Description: Handles CRUD and sorting for syllabus-specific ILOs – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Regenerated to support inline updates, drag-reorder, add and delete.
// [2025-07-29] Updated reorder method to accept structured payload: id, position, code.
// [2025-07-29] Synced reorder() structure to match SO sortable logic.
// -----------------------------------------------------------------------------

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Syllabus;
use App\Models\SyllabusIlo;
use Illuminate\Support\Facades\Auth;

class SyllabusIloController extends Controller
{
    public function update(Request $request, $syllabusId)
    {
        $syllabus = Syllabus::where('faculty_id', Auth::id())->findOrFail($syllabusId);

        $request->validate([
            'ilos' => 'required|array',
            'ilos.*' => 'required|string|max:1000',
        ]);

        $syllabus->ilos()->delete();

        foreach ($request->ilos as $index => $description) {
            SyllabusIlo::create([
                'syllabus_id' => $syllabus->id,
                'code' => 'ILO' . ($index + 1),
                'description' => $description,
                'position' => $index + 1,
            ]);
        }

        return response()->json(['message' => 'ILOs updated successfully.']);
    }

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

    public function inlineUpdate(Request $request, $syllabusId, $iloId)
    {
        $request->validate([
            'description' => 'nullable|string|max:1000',
        ]);

        $ilo = SyllabusIlo::where('syllabus_id', $syllabusId)->findOrFail($iloId);
        $ilo->update(['description' => $request->description]);

        return back()->with('success', 'ILO updated.');
    }

    public function destroy($id)
    {
        $ilo = SyllabusIlo::findOrFail($id);
        $ilo->delete();

        return response()->json(['message' => 'ILO deleted.']);
    }

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
