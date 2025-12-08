<?php

// File: app/Http/Controllers/Faculty/Syllabus/SyllabusTextbookController.php
// Description: Enhanced textbook upload, delete, and listing controller with `type` support – Syllaverse

namespace App\Http\Controllers\Faculty\Syllabus;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Syllabus;
use App\Models\SyllabusTextbook;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\TextbookChunkService;

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

            // Max is in kilobytes. 300 MB = 300 * 1024 = 307200 KB
            // Two paths: file upload OR adding a text-only reference
            if ($request->hasFile('textbook_files')) {
                $request->validate([
                    'textbook_files.*' => 'required|mimes:pdf,doc,docx,xls,xlsx,csv,txt|max:307200',
                    'type' => 'nullable|in:main,other',
                ]);
            } else {
                $request->validate([
                    'reference' => 'required|string|max:500',
                    'type' => 'nullable|in:main,other',
                ]);
            }

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

                    // Attempt lightweight ingestion (docx/txt) into textbook_chunks
                    try {
                        app(TextbookChunkService::class)->ingest($path, $textbook->id);
                    } catch (\Throwable $e) { /* skip */ }

                    $uploaded[] = [
                        'id' => $textbook->id,
                        'name' => $textbook->original_name,
                        'url' => Storage::url($textbook->file_path),
                        'type' => $textbook->type,
                    ];
                }
            } else {
                // Create a text-only reference item (no file)
                $refText = trim($request->input('reference'));
                if ($refText !== '') {
                    $textbook = $syllabus->textbooks()->create([
                        'file_path' => null,
                        'original_name' => $refText,
                        'type' => $defaultType,
                    ]);

                    $uploaded[] = [
                        'id' => $textbook->id,
                        'name' => $textbook->original_name,
                        'url' => null,
                        'type' => $textbook->type,
                        'is_reference' => true,
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
     * Update textbook metadata (e.g., original_name).
     */
    public function update(Request $request, SyllabusTextbook $textbook)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            // Preserve original extension; only allow changing the base name
            $currentExt = pathinfo($textbook->original_name, PATHINFO_EXTENSION);
            $newBase = pathinfo($data['name'], PATHINFO_FILENAME);
            $newName = $currentExt ? ($newBase . '.' . $currentExt) : $newBase;

            $textbook->original_name = $newName;
            $textbook->save();

            return response()->json([
                'success' => true,
                'message' => 'Textbook updated successfully.',
                'file' => [
                    'id' => $textbook->id,
                    'name' => $textbook->original_name,
                    'url' => $textbook->file_path ? Storage::url($textbook->file_path) : null,
                    'type' => $textbook->type,
                    'is_reference' => $textbook->file_path ? false : true,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('[SyllabusTextbook] Update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during update.',
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

            // Remove any stored chunks tied to this textbook
            try { DB::table('textbook_chunks')->where('textbook_id', $textbook->id)->orWhere('source_path', $textbook->file_path)->delete(); } catch (\Throwable $e) { /* skip */ }

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
                    'url' => $textbook->file_path ? Storage::url($textbook->file_path) : null,
                    'type' => $textbook->type,
                    'is_reference' => $textbook->file_path ? false : true,
                ];
            });

        return response()->json([
            'success' => true,
            'files' => $files,
        ]);
    }
}
