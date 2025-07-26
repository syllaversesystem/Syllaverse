<?php

// File: app/Http/Controllers/Faculty/SyllabusTLAController.php
// Description: Handles AJAX updates of TLA rows for a syllabus – Syllaverse

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Syllabus;

class SyllabusTLAController extends Controller
{
    public function update(Request $request, $id)
    {
        $request->validate([
            'tla' => 'required|array',
            'tla.*.ch' => 'nullable|string|max:255',
            'tla.*.topic' => 'nullable|string|max:1000',
            'tla.*.wks' => 'nullable|string|max:10',
            'tla.*.outcomes' => 'nullable|string|max:1000',
            'tla.*.ilo' => 'nullable|string|max:100',
            'tla.*.so' => 'nullable|string|max:100',
            'tla.*.delivery' => 'nullable|string|max:255',
        ]);

        // Get the syllabus with ownership check
        $syllabus = Syllabus::where('faculty_id', auth()->id())->findOrFail($id);

        // Delete existing TLA entries
        $syllabus->tla()->delete();

        // Save the new TLA rows
        foreach ($request->tla as $row) {
            if (!empty(array_filter($row))) { // skip empty rows
                $syllabus->tla()->create([
                    'ch' => $row['ch'] ?? null,
                    'topic' => $row['topic'] ?? null,
                    'wks' => $row['wks'] ?? null,
                    'outcomes' => $row['outcomes'] ?? null,
                    'ilo' => $row['ilo'] ?? null,
                    'so' => $row['so'] ?? null,
                    'delivery' => $row['delivery'] ?? null,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'TLA updated successfully.',
        ]);
    }
}
