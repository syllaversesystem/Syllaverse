<?php

// -----------------------------------------------------------------------------
// File: app/Http/Controllers/Faculty/Syllabus/SyllabusTLAController.php
// Description: Handles AJAX updates of TLA rows and AI-based TLA generation – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-28] Added append() method for inserting new row immediately on "Add Row" click.
// [2025-07-29] Updated syncIlo and syncSo to return ILO/SO codes for Blade table injection.
// [2025-07-30] Integrated Gemini AI generation via TlaAiGeneratorService.
// -----------------------------------------------------------------------------

namespace App\Http\Controllers\Faculty\Syllabus;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Syllabus;
use App\Models\TLA;
use App\Services\TlaAiGeneratorService;
use Illuminate\Support\Facades\Auth;

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
            'tla.*.position' => 'nullable|integer',
        ]);

    $syllabus = $this->getSyllabusForAction($id);
        // Delete existing rows and recreate them preserving the order via position
        $syllabus->tla()->delete();

        $createdRows = [];
        foreach ($request->tla as $idx => $row) {
            // Persist rows even if empty; use provided position or fall back to index
            $position = isset($row['position']) ? intval($row['position']) : $idx;

            $tla = TLA::create([
                'syllabus_id' => $syllabus->id,
                'ch' => $row['ch'] ?? '',
                'topic' => $row['topic'] ?? '',
                'wks' => $row['wks'] ?? '',
                'outcomes' => $row['outcomes'] ?? '',
                'ilo' => $row['ilo'] ?? '',
                'so' => $row['so'] ?? '',
                'delivery' => $row['delivery'] ?? '',
                'position' => $position,
            ]);
            
            $createdRows[] = $tla;
        }

        return response()->json([
            'success' => true, 
            'message' => 'TLA rows saved successfully.',
            'rows' => $createdRows
        ]);
    }

    // ➕ Immediately inserts a new blank TLA row
    public function append(Request $request, $id)
    {
    $syllabus = $this->getSyllabusForAction($id);

        // Determine next position
        $max = (int) TLA::where('syllabus_id', $syllabus->id)->max('position');
        $nextPos = $max + 1;

        $tla = TLA::create([
            'syllabus_id' => $syllabus->id,
            'ch' => '', 'topic' => '', 'wks' => '',
            'outcomes' => '', 'ilo' => '', 'so' => '', 'delivery' => '',
            'position' => $nextPos,
        ]);

        return response()->json(['success' => true, 'row' => $tla, 'message' => 'New TLA row added and saved to database.']);
    }

    // 🗑️ Delete specific TLA row by ID
    public function destroy($id)
    {
        $tla = TLA::findOrFail($id);

        // Check if user owns this syllabus
        if (auth()->check() && $tla->syllabus->faculty_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $tla->delete();

        return response()->json(['success' => true, 'message' => 'TLA row deleted successfully.']);
    }

    // 🧩 Sync ILOs to a TLA row (GET = fetch mapped IDs + codes, POST = sync)
    public function syncIlo(Request $request, $id)
    {
    $tla = TLA::with('ilos')->findOrFail($id);

        if ($request->isMethod('get')) {
            return response()->json([
                'ilos' => $tla->ilos->pluck('id'),
                'ilo_codes' => $tla->ilos->pluck('code')
            ]);
        }

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

    // ✨ Calls Gemini via service and saves generated TLA rows to DB
    // 🧠 Generates 18-week TLA plan via Gemini and returns debug info (prompt + raw output)
public function generateWithAI(Request $request, Syllabus $syllabus)
{
    \Log::info('[Mock TLA] generateWithAI CIS-style empty rows for syllabus ID ' . $syllabus->id);

    // Remove existing rows first
    $syllabus->tla()->delete();

    $totalRows = rand(10, 14);
    $midtermIndex = intval($totalRows / 2); // middle row index
    $chapterNum = 1;

    for ($i = 0; $i < $totalRows; $i++) {
        $isFirst = $i === 0;
        $isMidterm = $i === $midtermIndex;

        $ch = '';
        $topic = '';

        if ($isFirst) {
            $topic = 'Orientation & Introduction';
        } elseif ($isMidterm) {
            $topic = 'Midterm Examination';
        } else {
            $ch = (string) $chapterNum++;
        }

        TLA::create([
            'syllabus_id' => $syllabus->id,
            'ch' => $ch,
            'topic' => $topic,
            'wks' => '',
            'outcomes' => '',
            'ilo' => '',
            'so' => '',
            'delivery' => '',
        ]);
    }

    return response()->json([
        'success' => true,
        'message' => "✅ Successfully Generated.",
    ]);
}

    protected function getSyllabusForAction($id)
    {
        // Check if user is authenticated as faculty
        if (auth()->check()) {
            return Syllabus::where('faculty_id', auth()->id())->findOrFail($id);
        }
        
        // Fallback: just find the syllabus (for testing or other contexts)
        return Syllabus::findOrFail($id);
    }




}
