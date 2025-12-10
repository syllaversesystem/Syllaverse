<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Syllabus;

class AssessmentMappingController extends Controller
{
    /**
     * Upsert AI-generated assessment mapping into syllabus_assessment_mappings.
     * Payload: { rows: [ { name: string, week_marks: object, position: int } ] }
     */
    public function apply(Request $request, Syllabus $syllabus)
    {
        $rows = $request->input('rows', []);
        if (!is_array($rows)) {
            return response()->json(['error' => 'Invalid payload'], 422);
        }
        $count = 0;
        DB::beginTransaction();
        try {
            foreach ($rows as $r) {
                $name = trim((string)($r['name'] ?? ''));
                $position = (int)($r['position'] ?? 0);
                $weekMarks = $r['week_marks'] ?? [];
                if ($name === '') { continue; }
                // Normalize week marks: ensure scalar 'x' or null only
                $norm = [];
                if (is_array($weekMarks)) {
                    foreach ($weekMarks as $k => $v) {
                        $norm[(string)$k] = ($v === 'x' ? 'x' : null);
                    }
                }
                // Check existing record by syllabus + name
                $existing = DB::table('syllabus_assessment_mappings')
                    ->where('syllabus_id', $syllabus->id)
                    ->where('name', $name)
                    ->first();
                if ($existing) {
                    DB::table('syllabus_assessment_mappings')
                        ->where('id', $existing->id)
                        ->update([
                            'week_marks' => json_encode($norm),
                            'position' => $position,
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('syllabus_assessment_mappings')->insert([
                        'syllabus_id' => $syllabus->id,
                        'name' => $name,
                        'week_marks' => json_encode($norm),
                        'position' => $position,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $count++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('AssessmentMapping apply failed', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Database error applying mapping'], 500);
        }
        return response()->json(['applied' => $count]);
    }
}
