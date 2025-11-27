<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/Faculty/Syllabus/IloCdioSdgController.php
// * Description: Handles ILO-CDIO-SDG mapping save operations – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-11-26] Initial creation – controller for ILO-CDIO-SDG mapping CRUD.
// -------------------------------------------------------------------------------

namespace App\Http\Controllers\Faculty\Syllabus;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Syllabus;
use App\Models\SyllabusIloCdioSdg;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IloCdioSdgController extends Controller
{
    /**
     * Save ILO-CDIO-SDG mappings for a syllabus
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        try {
            // Validate incoming data - allow empty arrays for deletion
            $validated = $request->validate([
                'syllabus_id' => 'required|integer',
                'mappings' => 'nullable|array',
                'mappings.*.ilo_text' => 'nullable|string',
                'mappings.*.cdios' => 'nullable|array',
                'mappings.*.sdgs' => 'nullable|array',
                'mappings.*.position' => 'nullable|integer',
            ]);

            $syllabusId = $validated['syllabus_id'];

            \Log::info('ILO-CDIO-SDG Save Request', [
                'syllabus_id' => $syllabusId,
                'mappings_count' => count($validated['mappings'] ?? []),
                'mappings' => $validated['mappings'] ?? []
            ]);

            // Find syllabus and verify ownership
            $syllabus = Syllabus::where('faculty_id', Auth::id())->findOrFail($syllabusId);

            DB::beginTransaction();

            // Delete existing ILO-CDIO-SDG mappings for this syllabus
            $deletedCount = SyllabusIloCdioSdg::where('syllabus_id', $syllabusId)->delete();
            \Log::info('Deleted existing mappings', ['count' => $deletedCount]);

            // Insert new mappings only if there are any
            if (!empty($validated['mappings'])) {
                $insertedCount = 0;
                foreach ($validated['mappings'] as $mapping) {
                    $created = SyllabusIloCdioSdg::create([
                        'syllabus_id' => $syllabusId,
                        'ilo_text' => $mapping['ilo_text'] ?? '',
                        'cdios' => $mapping['cdios'] ?? [],
                        'sdgs' => $mapping['sdgs'] ?? [],
                        'position' => $mapping['position'] ?? 0,
                    ]);
                    $insertedCount++;
                    \Log::info('Inserted mapping', ['id' => $created->id, 'ilo_text' => $created->ilo_text]);
                }
                \Log::info('Total inserted', ['count' => $insertedCount]);
            } else {
                \Log::info('No mappings to insert');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'ILO-CDIO-SDG mappings saved successfully.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all()),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Syllabus not found or you do not have permission to edit it.',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to save ILO-CDIO-SDG mappings', [
                'syllabus_id' => $request->input('syllabus_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save ILO-CDIO-SDG mappings: ' . $e->getMessage(),
            ], 500);
        }
    }
}
