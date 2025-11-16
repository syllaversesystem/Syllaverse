<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/Faculty/Syllabus/SyllabusAssessmentTasksController.php
// * Description: Handles Assessment Tasks & Distribution Map for syllabi – Syllaverse
// -------------------------------------------------------------------------------

namespace App\Http\Controllers\Faculty\Syllabus;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Syllabus;
use App\Models\SyllabusAssessmentTask;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SyllabusAssessmentTasksController extends Controller
{
    /**
     * Persist the serialized Assessment Tasks JSON payload into syllabus.assessment_tasks_data
     * (Legacy support for JSON column)
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
     * 
     * Saves to normalized syllabus_assessment_tasks table (preferred)
     * Also maintains legacy assessment_tasks_data JSON column for backward compatibility
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

        // Accept either legacy 'assessment_tasks_data' (serialized JSON string)
        // or new JSON body shape { sections: [...] } posted by the AT partial helper.
        $hasLegacy = $request->has('assessment_tasks_data');
        $hasSections = $request->has('sections');
        $hasRows = $request->has('rows');

        if (! $hasLegacy && ! $hasSections && ! $hasRows) {
            return response()->json([
                'success' => false,
                'message' => 'Missing payload: expected assessment_tasks_data, sections, or rows',
            ], 422);
        }

        DB::beginTransaction();
        
        try {
            $sections = [];
            $dataForJsonColumn = null;
            
            // Normalize incoming payload
            if ($hasLegacy) {
                $data = (string) $request->input('assessment_tasks_data');
                $decoded = json_decode($data, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid JSON: ' . json_last_error_msg(),
                    ], 422);
                }
                $sections = $decoded['sections'] ?? $decoded;
                $dataForJsonColumn = $data;
            } elseif ($hasSections) {
                $sections = $request->input('sections');
                if (!is_array($sections)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid sections payload: expected array',
                    ], 422);
                }
                $dataForJsonColumn = json_encode(['sections' => $sections]);
            } elseif ($hasRows) {
                $sections = $request->input('rows');
                if (!is_array($sections)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid rows payload: expected array',
                    ], 422);
                }
                $dataForJsonColumn = json_encode(['sections' => $sections]);
            }

            // Delete existing assessment tasks for this syllabus
            SyllabusAssessmentTask::where('syllabus_id', $syllabus->id)->delete();

            $position = 0;
            $createdCount = 0;

            // Process each section and its rows
            foreach ($sections as $section) {
                $sectionNum = $section['section_num'] ?? null;
                $sectionLabel = $section['section_label'] ?? null;
                $mainRow = $section['main_row'] ?? [];
                $subRows = $section['sub_rows'] ?? [];
                $mainIloColumns = $section['main_ilo_columns'] ?? [];

                // Create main row entry
                if (!empty($mainRow['task']) || !empty($mainRow['percent']) || !empty($mainRow['code'])) {
                    SyllabusAssessmentTask::create([
                        'syllabus_id' => $syllabus->id,
                        'section_number' => $sectionNum,
                        'row_type' => 'main',
                        'section_label' => $sectionLabel,
                        'section_legacy' => $sectionNum ? "Section {$sectionNum}" : null,
                        'code' => $mainRow['code'] ?? null,
                        'task' => $mainRow['task'] ?? null,
                        'ird' => null, // Main rows don't have I/R/D
                        'percent' => !empty($mainRow['percent']) ? floatval($mainRow['percent']) : null,
                        'ilo_flags' => !empty($mainIloColumns) ? $mainIloColumns : null,
                        'c' => null,
                        'p' => null,
                        'a' => null,
                        'position' => $position++,
                    ]);
                    $createdCount++;
                }

                // Create sub row entries
                foreach ($subRows as $subRow) {
                    if (empty($subRow['task']) && empty($subRow['percent']) && empty($subRow['code'])) {
                        continue; // Skip empty rows
                    }

                    $iloColumns = $subRow['ilo_columns'] ?? [];
                    $cpaColumns = $subRow['cpa_columns'] ?? [];

                    // Parse C, P, A values - handle both string and numeric inputs
                    $cValue = null;
                    $pValue = null;
                    $aValue = null;
                    
                    if (isset($cpaColumns[0]) && $cpaColumns[0] !== null && $cpaColumns[0] !== '') {
                        $cValue = is_numeric($cpaColumns[0]) ? intval($cpaColumns[0]) : null;
                    }
                    if (isset($cpaColumns[1]) && $cpaColumns[1] !== null && $cpaColumns[1] !== '') {
                        $pValue = is_numeric($cpaColumns[1]) ? intval($cpaColumns[1]) : null;
                    }
                    if (isset($cpaColumns[2]) && $cpaColumns[2] !== null && $cpaColumns[2] !== '') {
                        $aValue = is_numeric($cpaColumns[2]) ? intval($cpaColumns[2]) : null;
                    }

                    SyllabusAssessmentTask::create([
                        'syllabus_id' => $syllabus->id,
                        'section_number' => $sectionNum,
                        'row_type' => 'sub',
                        'section_label' => $sectionLabel,
                        'section_legacy' => $sectionNum ? "Section {$sectionNum}" : null,
                        'code' => $subRow['code'] ?? null,
                        'task' => $subRow['task'] ?? null,
                        'ird' => $subRow['ird'] ?? null,
                        'percent' => !empty($subRow['percent']) ? floatval($subRow['percent']) : null,
                        'ilo_flags' => !empty($iloColumns) ? $iloColumns : null,
                        'c' => $cValue,
                        'p' => $pValue,
                        'a' => $aValue,
                        'position' => $position++,
                    ]);
                    $createdCount++;
                }
            }

            // Also save to legacy JSON column for backward compatibility
            if ($dataForJsonColumn) {
                $syllabus->assessment_tasks_data = $dataForJsonColumn;
                $syllabus->save();
            }

            DB::commit();

            Log::info('SyllabusAssessmentTasksController.store: saved assessment tasks', [
                'syllabus_id' => $syllabus->id,
                'sections_count' => count($sections),
                'tasks_created' => $createdCount,
                'source' => $hasLegacy ? 'assessment_tasks_data' : ($hasSections ? 'sections' : 'rows'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Assessment tasks saved successfully.',
                'tasks_count' => $createdCount,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            
            Log::error('SyllabusAssessmentTasksController.store: failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'syllabus_id' => $syllabus->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save assessment tasks: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Load assessment tasks for a syllabus
     * GET /faculty/syllabi/{syllabus}/assessment-tasks
     */
    public function index($syllabusId)
    {
        try {
            $facultyId = null;
            try { $facultyId = Auth::guard('faculty')->id(); } catch (\Throwable $e) { /* guard not available */ }
            if (!$facultyId) { $facultyId = Auth::id(); }

            $syllabus = Syllabus::where('faculty_id', $facultyId)->where('id', $syllabusId)->first();
            if (!$syllabus) {
                $syllabus = Syllabus::findOrFail($syllabusId);
            }

            // Load from normalized table
            $tasks = SyllabusAssessmentTask::where('syllabus_id', $syllabus->id)
                ->orderBy('section_number')
                ->orderBy('position')
                ->get();

            // Group by section and reconstruct structure
            $sections = [];

            foreach ($tasks as $task) {
                $sectionKey = $task->section_number ?? 1;
                
                if (!isset($sections[$sectionKey])) {
                    $sections[$sectionKey] = [
                        'section_num' => $sectionKey,
                        'section_label' => $task->section_label,
                        'main_row' => null,
                        'main_ilo_columns' => [],
                        'sub_rows' => [],
                    ];
                }

                // Use row_type to distinguish main vs sub
                if ($task->row_type === 'main') {
                    // Main row
                    $sections[$sectionKey]['main_row'] = [
                        'code' => $task->code,
                        'task' => $task->task,
                        'percent' => $task->percent,
                    ];
                    $sections[$sectionKey]['main_ilo_columns'] = $task->ilo_flags ?? [];
                    $sections[$sectionKey]['section_label'] = $task->section_label;
                } else {
                    // Sub row
                    $sections[$sectionKey]['sub_rows'][] = [
                        'code' => $task->code,
                        'task' => $task->task,
                        'ird' => $task->ird,
                        'percent' => $task->percent,
                        'ilo_columns' => $task->ilo_flags ?? [],
                        'cpa_columns' => [$task->c, $task->p, $task->a],
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'sections' => array_values($sections),
            ]);
        } catch (\Throwable $e) {
            Log::error('SyllabusAssessmentTasksController.index: failed', [
                'error' => $e->getMessage(),
                'syllabus_id' => $syllabusId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load assessment tasks: ' . $e->getMessage(),
            ], 500);
        }
    }
}
