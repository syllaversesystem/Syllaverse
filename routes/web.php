<?php

// ------------------------------------------------
// File: routes/web.php
// Description: Web Routes Configuration for Syllaverse
// ------------------------------------------------

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Faculty\AuthController as FacultyAuthController;
use App\Http\Controllers\AIChatController;
use App\Http\Controllers\AssessmentMappingAIController;

// ------------------------------------------------
// Redirect root to Faculty login
// ------------------------------------------------
Route::get('/', function () {
    return redirect()->route('faculty.login.form');
});

// ------------------------------------------------
// AI Chat endpoint (faculty syllabus contextual suggestions)
// ------------------------------------------------
Route::post('/faculty/syllabi/{syllabus}/ai-chat', [AIChatController::class, 'chat'])->name('faculty.syllabi.ai.chat');
// AI auto-map endpoint (Assessment Mappings)
Route::post('/faculty/syllabi/{syllabus}/assessment-mappings/ai-map', [AssessmentMappingAIController::class, 'autoMap'])->name('faculty.assessment-mappings.ai-map');

// ------------------------------------------------
// Generic login route (for Laravel default authentication redirects)
// ------------------------------------------------
Route::get('/login', function () {
    // Default to faculty login for now, could be made smarter based on context
    return redirect()->route('faculty.login.form');
})->name('login');

// ------------------------------------------------
// Super Admin Routes
// ------------------------------------------------
require __DIR__.'/superadmin.php';

// ------------------------------------------------
// Faculty Login View (with Google Login Button)
// ------------------------------------------------
Route::get('/faculty/login', function () {
    return view('auth.faculty-login');
})->name('faculty.login.form');

// ------------------------------------------------
// Faculty Routes (Modularized)
// ------------------------------------------------
require __DIR__.'/faculty.php';

// ------------------------------------------------
// Student Login Route (UI only, for now)
// ------------------------------------------------
Route::get('/student/login', function () {
    return view('auth.student-login');
})->name('student.login.form');

// ------------------------------------------------
// Test Route for Criteria Assessment (Development Only)
// ------------------------------------------------
Route::get('/test-criteria', function () {
    // Get or create a test syllabus
    $syllabus = \App\Models\Syllabus::with(['course', 'program', 'courseInfo', 'criteria'])->first();
    
    if (!$syllabus) {
        // Create a minimal test syllabus if none exists
        $syllabus = \App\Models\Syllabus::create([
            'title' => 'Test Syllabus',
            'faculty_id' => 1, // Assuming user ID 1 exists
            'program_id' => null,
            'course_id' => null,
        ]);
        
        // Create course info if it doesn't exist
        \App\Models\SyllabusCourseInfo::create([
            'syllabus_id' => $syllabus->id,
            'course_code' => 'TEST 101',
            'course_title' => 'Test Course',
            'units' => 3,
            'prerequisites' => 'None',
            'corequisites' => 'None',
        ]);
    }
    
    // Set up the data structure expected by the syllabus view
    $default = [
        'id' => $syllabus->id,
        'title' => $syllabus->title,
    ];
    
    return view('faculty.syllabus.syllabus', compact('syllabus', 'default'));
})->name('test.criteria');

// Simple TLA module UI (prototype): one-row, one-column table
Route::get('/faculty/syllabus/tla', function () {
    return view('faculty.syllabus.tla');
})->name('faculty.syllabus.tla');

// ------------------------------------------------
// Fun Motivational Page for Meg 💕
// ------------------------------------------------
Route::get('/pwede-pa-meg', function () {
    return view('fun.pwede-pa-meg');
})->name('fun.pwede-pa-meg');
