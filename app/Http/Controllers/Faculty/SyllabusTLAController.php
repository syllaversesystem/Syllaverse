<?php

// -----------------------------------------------------------------------------
// File: app/Http/Controllers/Faculty/SyllabusTLAController.php
// Description: Handles AJAX updates of TLA rows and appending blank rows – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-28] Added append() method for inserting new row immediately on "Add Row" click.
// [2025-07-29] Updated syncIlo and syncSo to return ILO/SO codes for Blade table injection.
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
        $tla = TLA::findOrFail($id);

        if ($tla->syllabus->faculty_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $tla->delete();

        return response()->json([
            'success' => true,
            'message' => 'TLA row deleted successfully.',
        ]);
    }

    // 🧩 Sync ILOs to a TLA row (GET = fetch mapped IDs + codes, POST = sync)
public function syncIlo(Request $request, $id)
{
    $tla = TLA::with('ilos')->findOrFail($id);

    // GET → fetch current mappings
    if ($request->isMethod('get')) {
        return response()->json([
            'ilos' => $tla->ilos->pluck('id'),
            'ilo_codes' => $tla->ilos->pluck('code')
        ]);
    }

    // POST → update mappings
    $validated = $request->validate([
        'ilo_ids' => 'array',
        'ilo_ids.*' => 'exists:syllabus_ilos,id'
    ]);

    $tla->ilos()->sync($validated['ilo_ids']);

    return response()->json(['success' => true]);
}


    // 🧩 Sync SOs to a TLA row (GET = fetch, POST = sync)
    public function syncSo(Request $request, $id)
    {
        $tla = TLA::with('sos')->findOrFail($id);

        if ($request->isMethod('get')) {
            return response()->json([
                'sos' => $tla->sos->pluck('id'),
                'so_codes' => $tla->sos->pluck('code')
            ]);
        }

        $validated = $request->validate([
            'so_ids' => 'array',
            'so_ids.*' => 'exists:syllabus_sos,id'
        ]);

        $tla->sos()->sync($validated['so_ids']);

        return response()->json(['success' => true]);
    }
}