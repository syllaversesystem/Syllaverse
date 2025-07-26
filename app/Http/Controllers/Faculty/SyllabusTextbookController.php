<?php

// File: app/Http/Controllers/Faculty/SyllabusTextbookController.php
// Description: Enhanced textbook upload, delete, and listing controller with `type` support – Syllaverse

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Syllabus;
use App\Models\SyllabusTextbook;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SyllabusTextbookController extends Controller
{
    /**
     * Upload multiple textbook files and associate them with a syllabus.
     */
    public function store(Request $request, Syllabus $syllabus)
    {
        try {
            Log::info('[SyllabusTextbook] Upload triggered', [
                'syllabus_id' => $syllabus->id,
                'has_file' => $request->hasFile('textbook_files'),
                'file_names' => collect($request->file('textbook_files'))->pluck('name')->toArray(),
            ]);

            $request->validate([
                'textbook_files.*' => 'required|mimes:pdf,doc,docx,xls,xlsx,csv,txt|max:5120',
                'type' => 'nullable|in:main,other',
            ]);

            $uploaded = [];
            $defaultType = $request->input('type', 'main');

            if ($request->hasFile('textbook_files')) {
                foreach ($request->file('textbook_files') as $file) {
                    $path = $file->store('syllabi/textbooks', 'public');

                    $textbook = $syllabus->textbooks()->create([
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'type' => $defaultType,
                    ]);

                    $uploaded[] = [
                        'id' => $textbook->id,
                        'name' => $textbook->original_name,
                        'url' => Storage::url($textbook->file_path),
                        'type' => $textbook->type,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Textbooks uploaded successfully.',
                'files' => $uploaded,
            ]);
        } catch (\Throwable $e) {
            Log::error('[SyllabusTextbook] Upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during upload.',
            ], 500);
        }
    }

    /**
     * Delete a specific textbook file from storage and database.
     */
    public function destroy(SyllabusTextbook $textbook)
    {
        try {
            Log::info("[SyllabusTextbook] Deleting textbook ID: {$textbook->id}");

            if ($textbook->file_path && Storage::disk('public')->exists($textbook->file_path)) {
                Storage::disk('public')->delete($textbook->file_path);
            }

            $textbook->delete();

            return response()->json([
                'success' => true,
                'message' => 'Textbook deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            Log::error('[SyllabusTextbook] Delete failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during deletion.',
            ], 500);
        }
    }

    /**
     * List all uploaded textbooks of a given type for a syllabus.
     */
    public function list(Syllabus $syllabus, Request $request)
    {
        $type = $request->query('type', 'main');

        $files = $syllabus->textbooks()
            ->where('type', $type)
            ->orderBy('created_at')
            ->get()
            ->map(function ($textbook) {
                return [
                    'id' => $textbook->id,
                    'name' => $textbook->original_name,
                    'url' => Storage::url($textbook->file_path),
                    'type' => $textbook->type,
                ];
            });

        return response()->json([
            'success' => true,
            'files' => $files,
        ]);
    }
}
