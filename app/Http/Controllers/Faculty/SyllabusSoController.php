<?php

// File: app/Http/Controllers/Faculty/SyllabusSoController.php
// Description: Handles AJAX-based updates of syllabus-specific SOs – Syllaverse

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Syllabus;
use App\Models\SyllabusSo;

class SyllabusSoController extends Controller
{
    public function update(Request $request, $syllabusId)
    {
        $syllabus = Syllabus::where('faculty_id', Auth::id())->findOrFail($syllabusId);

        $request->validate([
            'sos' => 'required|array',
            'sos.*' => 'required|string|max:1000'
        ]);

        // Delete existing SOs
        $syllabus->sos()->delete();

        // Insert new ones
        foreach ($request->sos as $description) {
            SyllabusSo::create([
                'syllabus_id' => $syllabus->id,
                'description' => $description,
            ]);
        }

        // AJAX JSON Response
        return response()->json(['message' => 'Student Outcomes updated successfully.']);
    }
}
