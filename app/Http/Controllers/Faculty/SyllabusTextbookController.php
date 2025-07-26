<?php

// File: app/Http/Controllers/Faculty/SyllabusTextbookController.php
// Description: Handles AJAX upload of textbook files for Faculty syllabi – Syllaverse

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Syllabus;

class SyllabusTextbookController extends Controller
{
    /**
     * Store or replace the uploaded textbook file for a syllabus.
     */
    public function store(Request $request, $id)
    {
        $request->validate([
            'textbook_file' => 'required|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,csv,txt',
        ]);

        $syllabus = Syllabus::findOrFail($id);

        // Delete old file if exists
        if ($syllabus->textbook_file_path && Storage::disk('public')->exists($syllabus->textbook_file_path)) {
            Storage::disk('public')->delete($syllabus->textbook_file_path);
        }

        // Store new file
        $path = $request->file('textbook_file')->store('syllabi/textbooks', 'public');

        // Update record
        $syllabus->update([
            'textbook_file_path' => $path,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Textbook uploaded successfully.',
            'file_url' => Storage::url($path),
        ]);
    }
}
