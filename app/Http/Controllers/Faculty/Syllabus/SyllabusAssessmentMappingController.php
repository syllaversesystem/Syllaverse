<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/Faculty/Syllabus/SyllabusAssessmentMappingController.php
// * Description: Handles Assessment Mapping (weekly distribution) for syllabi – Syllaverse
// -------------------------------------------------------------------------------

namespace App\Http\Controllers\Faculty\Syllabus;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Syllabus;
use App\Models\SyllabusAssessmentMapping;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SyllabusAssessmentMappingController extends Controller
{
    /**
     * Save assessment mappings for a syllabus
     * POST /faculty/syllabi/{id}/assessment-mappings
     */
    public function update(Request $request, $syllabusId)
    {
        // Get authenticated faculty ID
        $facultyId = auth()->check() ? auth()->id() : null;
        
        if (!$facultyId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // Find syllabus and verify ownership
        $syllabus = Syllabus::where('id', $syllabusId)
            ->where('faculty_id', $facultyId)
            ->first();

        if (!$syllabus) {
            return response()->json([
                'success' => false,
                'message' => 'Syllabus not found or access denied',
            ], 404);
        }

        // Validate request (allow empty array to delete all mappings)
        $validated = $request->validate([
            'mappings' => 'present|array',
            'mappings.*.name' => 'nullable|string|max:255',
            'mappings.*.week_marks' => 'nullable|array',
            'mappings.*.position' => 'nullable|integer',
        ]);

        DB::beginTransaction();

        try {
            // Delete existing mappings for this syllabus
            SyllabusAssessmentMapping::where('syllabus_id', $syllabus->id)->delete();

            $createdMappings = [];
            $position = 0;

            // Create new mappings
            foreach ($validated['mappings'] as $mapping) {
                // Skip empty mappings
                if (empty($mapping['name']) && empty($mapping['week_marks'])) {
                    continue;
                }

                $created = SyllabusAssessmentMapping::create([
                    'syllabus_id' => $syllabus->id,
                    'name' => $mapping['name'] ?? null,
                    'week_marks' => $mapping['week_marks'] ?? [],
                    'position' => $mapping['position'] ?? $position,
                ]);

                $createdMappings[] = $created;
                $position++;
            }

            DB::commit();

            Log::info('SyllabusAssessmentMappingController.update: saved assessment mappings', [
                'syllabus_id' => $syllabus->id,
                'mappings_count' => count($createdMappings),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Assessment mappings saved successfully',
                'mappings' => $createdMappings,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('SyllabusAssessmentMappingController.update: failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'syllabus_id' => $syllabus->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save assessment mappings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Load assessment mappings for a syllabus
     * GET /faculty/syllabi/{id}/assessment-mappings
     */
    public function index($syllabusId)
    {
        try {
            $facultyId = auth()->check() ? auth()->id() : null;

            if (!$facultyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $syllabus = Syllabus::where('id', $syllabusId)
                ->where('faculty_id', $facultyId)
                ->first();

            if (!$syllabus) {
                return response()->json([
                    'success' => false,
                    'message' => 'Syllabus not found or access denied',
                ], 404);
            }

            // Load mappings ordered by position
            $mappings = SyllabusAssessmentMapping::where('syllabus_id', $syllabus->id)
                ->orderBy('position')
                ->get();

            return response()->json([
                'success' => true,
                'mappings' => $mappings,
            ]);
        } catch (\Throwable $e) {
            Log::error('SyllabusAssessmentMappingController.index: failed', [
                'error' => $e->getMessage(),
                'syllabus_id' => $syllabusId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load assessment mappings: ' . $e->getMessage(),
            ], 500);
        }
    }
}
