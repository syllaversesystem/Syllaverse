<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/Faculty/Syllabus/IloIgaController.php
// * Description: Handles ILO-IGA mapping save operations – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-11-25] Initial creation – controller for ILO-IGA mapping CRUD.
// -------------------------------------------------------------------------------

namespace App\Http\Controllers\Faculty\Syllabus;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Syllabus;
use App\Models\SyllabusIloIga;
use App\Models\SyllabusIga;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IloIgaController extends Controller
{
    /**
     * Save ILO-IGA mappings for a syllabus (new format from partial)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveMapping(Request $request)
    {
        try {
            // Validate incoming data - allow empty arrays for deletion
            $validated = $request->validate([
                'syllabus_id' => 'required|integer',
                'iga_labels' => 'nullable|array',
                'mappings' => 'nullable|array',
                'mappings.*.ilo_text' => 'nullable|string',
                'mappings.*.igas' => 'nullable|array',
                'mappings.*.position' => 'nullable|integer',
            ]);

            $syllabusId = $validated['syllabus_id'];

            // Find syllabus and verify ownership
            $syllabus = Syllabus::where('faculty_id', Auth::id())->findOrFail($syllabusId);

            DB::beginTransaction();

            // Delete existing ILO-IGA mappings for this syllabus
            SyllabusIloIga::where('syllabus_id', $syllabusId)->delete();

            // Insert new mappings only if there are any
            if (!empty($validated['mappings'])) {
                foreach ($validated['mappings'] as $mapping) {
                    SyllabusIloIga::create([
                        'syllabus_id' => $syllabusId,
                        'ilo_text' => $mapping['ilo_text'] ?? '',
                        'igas' => $mapping['igas'] ?? [],
                        'position' => $mapping['position'] ?? 0,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'ILO-IGA mappings saved successfully.',
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
            \Log::error('Failed to save ILO-IGA mappings', [
                'syllabus_id' => $request->input('syllabus_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save ILO-IGA mappings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save ILO-IGA mappings for a syllabus
     * 
     * @param Request $request
     * @param int $syllabusId
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request, $syllabusId)
    {
        // Find syllabus and verify ownership
        $syllabus = Syllabus::where('faculty_id', Auth::id())->findOrFail($syllabusId);

        // Validate incoming data
        $request->validate([
            'iga_headers' => 'required|array',
            'iga_headers.*.code' => 'required|string',
            'iga_headers.*.title' => 'required|string',
            'iga_headers.*.description' => 'nullable|string',
            'iga_headers.*.position' => 'required|integer',
            'mappings' => 'required|array',
            'mappings.*.ilo_text' => 'nullable|string',
            'mappings.*.igas' => 'required|array',
            'mappings.*.position' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();

            // Delete existing IGA headers for this syllabus
            SyllabusIga::where('syllabus_id', $syllabusId)->delete();

            // Insert new IGA headers
            foreach ($request->iga_headers as $header) {
                SyllabusIga::create([
                    'syllabus_id' => $syllabusId,
                    'code' => $header['code'],
                    'title' => $header['title'],
                    'description' => $header['description'] ?? '',
                    'position' => $header['position'],
                ]);
            }

            // Delete existing ILO-IGA mappings for this syllabus
            SyllabusIloIga::where('syllabus_id', $syllabusId)->delete();

            // Insert new mappings
            foreach ($request->mappings as $mapping) {
                SyllabusIloIga::create([
                    'syllabus_id' => $syllabusId,
                    'ilo_text' => $mapping['ilo_text'],
                    'igas' => $mapping['igas'],
                    'position' => $mapping['position'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'ILO-IGA mappings saved successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to save ILO-IGA mappings', [
                'syllabus_id' => $syllabusId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save ILO-IGA mappings: ' . $e->getMessage(),
            ], 500);
        }
    }
}
