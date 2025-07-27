<?php

// File: app/Http/Controllers/Faculty/SyllabusIloController.php
// Description: Handles AJAX-based update of ILOs (per syllabus) – Syllaverse

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Syllabus;
use App\Models\SyllabusIlo;
use Illuminate\Support\Facades\Auth;

class SyllabusIloController extends Controller
{
    public function update(Request $request, $syllabusId)
    {
        $syllabus = Syllabus::where('faculty_id', Auth::id())->findOrFail($syllabusId);

        $request->validate([
            'ilos' => 'required|array',
            'ilos.*' => 'required|string|max:1000',
        ]);

        // Clear old ILOs and insert new ones
        $syllabus->ilos()->delete();

        foreach ($request->ilos as $description) {
            SyllabusIlo::create([
                'syllabus_id' => $syllabus->id,
                'description' => $description,
            ]);
        }

        // Return JSON response for AJAX
        return response()->json(['message' => 'Intended Learning Outcomes updated successfully.']);
    }
}
