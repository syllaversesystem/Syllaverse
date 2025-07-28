<?php

// -----------------------------------------------------------------------------
// File: app/Http/Controllers/Faculty/SyllabusTLAController.php
// Description: Handles AJAX updates of TLA rows and appending blank rows – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-28] Added append() method for inserting new row immediately on "Add Row" click.
// -----------------------------------------------------------------------------

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Syllabus;
use App\Models\TLA;

class SyllabusTLAController extends Controller
{
    // 🔄 Handles full TLA array save (manual form submission)
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

        $syllabus = Syllabus::where('faculty_id', auth()->id())->findOrFail($id);

        // Clear old entries
        $syllabus->tla()->delete();

        foreach ($request->tla as $row) {
            if (!empty(array_filter($row))) {
                TLA::create([
                    'syllabus_id' => $syllabus->id,
                    'ch' => $row['ch'] ?? '',
                    'topic' => $row['topic'] ?? '',
                    'wks' => $row['wks'] ?? '',
                    'outcomes' => $row['outcomes'] ?? '',
                    'ilo' => $row['ilo'] ?? '',
                    'so' => $row['so'] ?? '',
                    'delivery' => $row['delivery'] ?? '',
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'TLA rows saved successfully.',
        ]);
    }

    // ➕ Immediately inserts a new blank TLA row
    public function append(Request $request, $id)
    {
        $syllabus = Syllabus::where('faculty_id', auth()->id())->findOrFail($id);

        $tla = TLA::create([
            'syllabus_id' => $syllabus->id,
            'ch' => '',
            'topic' => '',
            'wks' => '',
            'outcomes' => '',
            'ilo' => '',
            'so' => '',
            'delivery' => '',
        ]);

        return response()->json([
            'success' => true,
            'row' => $tla,
            'message' => 'New TLA row added and saved to database.',
        ]);
    }


    public function destroy($id)
{
    $tla = \App\Models\TLA::findOrFail($id);

    // Optional: check ownership via syllabus
    if ($tla->syllabus->faculty_id !== auth()->id()) {
        return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
    }

    $tla->delete();

    return response()->json([
        'success' => true,
        'message' => 'TLA row deleted successfully.',
    ]);
}
}
