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
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get filter parameter for department
        $departmentFilter = $request->get('department_filter');
        
        // Build programs query with optional department filter
        $programsQuery = Program::with(['department'])->notDeleted();
        if ($departmentFilter && $departmentFilter !== 'all') {
            $programsQuery->where('department_id', $departmentFilter);
        }
        $programs = $programsQuery->get();
        
        // Get all courses with their department  
        $courses = Course::with(['department'])->get();
        
        // Get all departments for dropdowns
        $departments = Department::all();
        
        // Check user roles and determine department dropdown visibility
        $userDepartment = null;
        $showDepartmentDropdown = true;
        
        // Get all active appointments for the user
        $userAppointments = $user->appointments()->active()->get();
        
        // Check for institution-wide roles (roles with Institution scope)
        $hasInstitutionWideRole = $userAppointments->contains(function($appointment) {
            return in_array($appointment->role, ['VCAA', 'ASSOC_VCAA']) || 
                   ($appointment->scope_type === 'Institution') ||
                   ($appointment->role === 'DEAN' && $appointment->scope_type === 'Institution');
        });
        
        // Check specifically for VCAA/ASSOC_VCAA roles for department filter
        $showDepartmentFilter = $userAppointments->contains(function($appointment) {
            return in_array($appointment->role, ['VCAA', 'ASSOC_VCAA']);
        });
        
        // Check for department-specific roles
        $departmentSpecificAppointments = $userAppointments->filter(function($appointment) {
            return in_array($appointment->role, ['DEAN', 'DEPT_CHAIR', 'PROG_CHAIR']) && 
                   $appointment->scope_type === 'Department' && 
                   !empty($appointment->scope_id);
        });
        
        // Role-based dropdown logic:
        // 1. If user has institution-wide roles: ALWAYS show dropdown (can select any department)
        // 2. If user has ONLY department-specific roles: hide dropdown (auto-assign their department)
        // 3. If user has BOTH: show dropdown (institution-wide role takes precedence)
        
        if ($departmentSpecificAppointments->isNotEmpty() && !$hasInstitutionWideRole) {
            // User has ONLY department-specific roles - restrict to their department
            $firstDeptAppointment = $departmentSpecificAppointments->first();
            $userDepartment = $firstDeptAppointment->scope_id;
            $showDepartmentDropdown = false;
        }
        // If $hasInstitutionWideRole is true, $showDepartmentDropdown remains true (default)
        // This covers both: institution-only users and mixed role users
        
        return view('admin.programs-courses.index', compact(
            'programs', 
            'courses', 
            'departments',
            'userDepartment',
            'showDepartmentDropdown',
            'showDepartmentFilter',
            'departmentFilter'
        ));
    }
}
