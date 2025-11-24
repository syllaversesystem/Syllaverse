<?php

namespace App\Http\Controllers\Faculty\Syllabus;

use App\Http\Controllers\Controller;
use App\Models\SyllabusIloSoCpa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IloSoCpaController extends Controller
{
    /**
     * Save ILO-SO-CPA mapping for a syllabus
     */
    public function save(Request $request)
    {
        $request->validate([
            'syllabus_id' => 'required|exists:syllabi,id',
            'so_columns' => 'nullable|array',
            'so_columns.*' => 'string',
            'mappings' => 'array', // Allow empty array to delete all
            'mappings.*.ilo_text' => 'nullable|string', // Allow empty ILO text
            'mappings.*.sos' => 'nullable',
            'mappings.*.c' => 'nullable|string',
            'mappings.*.p' => 'nullable|string',
            'mappings.*.a' => 'nullable|string',
            'mappings.*.position' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();

            $syllabusId = $request->syllabus_id;

            // Update SO columns in syllabi table
            DB::table('syllabi')
                ->where('id', $syllabusId)
                ->update(['so_columns' => json_encode($request->so_columns ?? [])]);

            // Delete existing mappings for this syllabus
            SyllabusIloSoCpa::where('syllabus_id', $syllabusId)->delete();

            // Insert new mappings
            foreach ($request->mappings as $mapping) {
                SyllabusIloSoCpa::create([
                    'syllabus_id' => $syllabusId,
                    'ilo_text' => $mapping['ilo_text'],
                    'sos' => $mapping['sos'] ?? [],
                    'c' => $mapping['c'] ?? null,
                    'p' => $mapping['p'] ?? null,
                    'a' => $mapping['a'] ?? null,
                    'position' => $mapping['position'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'ILO-SO-CPA mapping saved successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving ILO-SO-CPA mapping: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to save mapping: ' . $e->getMessage()
            ], 500);
        }
    }
}
