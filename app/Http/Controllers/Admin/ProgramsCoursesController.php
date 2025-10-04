<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Course;
use App\Models\Department;
use Illuminate\Http\Request;

class ProgramsCoursesController extends Controller
{
    /**
     * Display the programs and courses management page
     */
    public function index()
    {
        // Get all programs with their department and courses
        $programs = Program::with(['department', 'courses'])->get();
        
        // Get all courses with their program and department
        $courses = Course::with(['program', 'program.department'])->get();
        
        // Get all departments for dropdowns
        $departments = Department::all();
        
        return view('admin.programs-courses.index', compact('programs', 'courses', 'departments'));
    }
}
