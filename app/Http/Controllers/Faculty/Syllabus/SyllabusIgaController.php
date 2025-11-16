<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/Faculty/Syllabus/SyllabusIgaController.php
// * Description: Handles CRUD and ordering for master IGA items used in syllabi – Syllaverse
// -------------------------------------------------------------------------------

namespace App\Http\Controllers\Faculty\Syllabus;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SyllabusIga;
use Illuminate\Support\Facades\Auth;

class SyllabusIgaController extends Controller
{
    // Batch update IGAs (accepts array of {id, code, description, position})
    public function update(Request $request, $syllabusId)
    {
        // This endpoint is used by the frontend to persist the current list/order of IGAs.
        $request->validate([
            'igas' => 'required|array',
            'igas.*.id' => 'nullable|integer|exists:syllabus_igas,id',
            'igas.*.code' => 'required|string',
            'igas.*.title' => 'nullable|string|max:255',
            'igas.*.description' => 'nullable|string|max:2000',
            'igas.*.position' => 'required|integer',
        ]);

        $incomingIds = collect($request->igas)->pluck('id')->filter();
        $existingIds = SyllabusIga::where('syllabus_id', $syllabusId)->pluck('id');

        // delete removed per-syllabus IGAs
        $toDelete = $existingIds->diff($incomingIds);
        if ($toDelete->isNotEmpty()) {
            SyllabusIga::whereIn('id', $toDelete)->delete();
        }

        $createdIds = [];
        \DB::transaction(function() use ($request, &$createdIds, $syllabusId) {
            foreach ($request->igas as $igaData) {
                $attrs = [
                    'syllabus_id' => $syllabusId,
                    'code' => $igaData['code'] ?? null,
                    'title' => $igaData['title'] ?? '',
                    'description' => $igaData['description'] ?? '',
                    'position' => $igaData['position'] ?? 0,
                ];

                if (!empty($igaData['id'])) {
                    SyllabusIga::where('id', $igaData['id'])->update($attrs);
                    $createdIds[] = (int)$igaData['id'];
                } else {
                    $new = SyllabusIga::create($attrs);
                    if ($new) $createdIds[] = $new->id;
                }
            }
        });

        return response()->json(['success' => true, 'message' => 'IGAs updated successfully.', 'ids' => $createdIds]);
    }

    // Delete one IGA
    public function destroy($id)
    {
    $m = SyllabusIga::findOrFail($id);
    $m->delete();
        return response()->json(['message' => 'IGA deleted.']);
    }

    // Optional reorder endpoint (accepts positions array)
    public function reorder(Request $request)
    {
        $request->validate([
            'positions' => 'required|array',
            'positions.*.id' => 'required|integer|exists:syllabus_igas,id',
            'positions.*.code' => 'required|string',
            'positions.*.position' => 'required|integer',
        ]);

        foreach ($request->positions as $item) {
            SyllabusIga::where('id', $item['id'])->update(['code' => $item['code'], 'position' => $item['position']]);
        }

        return response()->json(['message' => 'IGA order updated successfully.']);
    }
}
