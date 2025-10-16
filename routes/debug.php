// Test route to check course data structure
Route::get('/debug/courses', function() {
    $courses = \App\Models\Course::with('department')->get();
    
    return response()->json([
        'total_courses' => $courses->count(),
        'sample_course' => $courses->first(),
        'courses_with_departments' => $courses->whereNotNull('department')->count(),
    ]);
});