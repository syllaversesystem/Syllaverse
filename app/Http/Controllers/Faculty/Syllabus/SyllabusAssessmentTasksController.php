<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/Faculty/Syllabus/SyllabusAssessmentTasksController.php
// * Description: Handles Assessment Tasks & Distribution Map for syllabi – Syllaverse
// -------------------------------------------------------------------------------

namespace App\Http\Controllers\Faculty\Syllabus;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Syllabus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SyllabusAssessmentTasksController extends Controller
{
    /**
     * Persist the serialized Assessment Tasks JSON payload into syllabus.assessment_tasks_data
     */
    public function syncFromRequest(Request $request, Syllabus $syllabus): void
    {
        if (! $request->has('assessment_tasks_data')) {
            return;
        }

        try {
            $raw = $request->input('assessment_tasks_data');
            
            // Validate it's a valid JSON string
            if (is_string($raw)) {
                $decoded = json_decode($raw, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::warning('SyllabusAssessmentTasksController::syncFromRequest: invalid JSON', [
                        'syllabus_id' => $syllabus->id,
                        'error' => json_last_error_msg(),
                    ]);
                    return;
                }
            }

            $syllabus->assessment_tasks_data = $raw;
            $syllabus->save();
            
            Log::info('SyllabusAssessmentTasksController::syncFromRequest: persisted assessment_tasks_data', [
                'syllabus_id' => $syllabus->id,
                'data_length' => strlen($raw ?? ''),
            ]);
        } catch (\Throwable $e) {
            Log::warning('SyllabusAssessmentTasksController::syncFromRequest: failed', [
                'error' => $e->getMessage(),
                'syllabus_id' => $syllabus->id,
            ]);
        }
    }

    /**
     * Dedicated endpoint for saving assessment tasks via AJAX
     * POST /faculty/syllabi/{syllabus}/assessment-tasks
     */
    public function store(Request $request, $syllabusId)
    {
        $facultyId = null;
        try { $facultyId = Auth::guard('faculty')->id(); } catch (\Throwable $e) { /* guard not available */ }
        if (!$facultyId) { $facultyId = Auth::id(); }

        $syllabus = Syllabus::where('faculty_id', $facultyId)->where('id', $syllabusId)->first();
        if (!$syllabus) {
            Log::warning('SyllabusAssessmentTasksController.store: scoped syllabus not found', [
                'syllabus_id' => $syllabusId,
                'faculty_id' => $facultyId,
            ]);
            $syllabus = Syllabus::findOrFail($syllabusId);
        }

        $request->validate([
            'assessment_tasks_data' => 'required|string',
        ]);

        try {
            $data = $request->input('assessment_tasks_data');
            
            // Validate JSON
            $decoded = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid JSON: ' . json_last_error_msg(),
                ], 422);
            }

            $syllabus->assessment_tasks_data = $data;
            $syllabus->save();

            Log::info('SyllabusAssessmentTasksController.store: saved assessment tasks', [
                'syllabus_id' => $syllabus->id,
                'rows_count' => is_array($decoded) ? count($decoded) : 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Assessment tasks saved successfully.',
            ]);
        } catch (\Throwable $e) {
            Log::error('SyllabusAssessmentTasksController.store: failed', [
                'error' => $e->getMessage(),
                'syllabus_id' => $syllabus->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save assessment tasks: ' . $e->getMessage(),
            ], 500);
        }
    }
}
