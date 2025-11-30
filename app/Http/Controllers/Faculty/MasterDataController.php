<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    /**
     * Display the Master Data page (SO, ILO, SDG, IGA, CDIO tabs).
     */
    public function index(): View
    {
        $user = Auth::guard('faculty')->user();
        // Active appointments for scoping
        $appointments = method_exists($user, 'appointments') ? $user->appointments()->active()->get() : collect();

        // Institution-wide roles (see all departments & courses)
        $hasInstitutionWideOnly = $appointments->isNotEmpty() && $appointments->every(function($a){
            return in_array($a->role, ['VCAA','ASSOC_VCAA']);
        });

        // Determine a single department scope from department-scoped roles
        $deptScopedRoles = [
            \App\Models\Appointment::ROLE_DEPT,
            \App\Models\Appointment::ROLE_DEPT_HEAD,
            \App\Models\Appointment::ROLE_PROG,
            \App\Models\Appointment::ROLE_DEAN,
            \App\Models\Appointment::ROLE_ASSOC_DEAN,
            \App\Models\Appointment::ROLE_FACULTY,
        ];
        $deptAppt = $appointments->first(function($a) use ($deptScopedRoles){
            return in_array($a->role, $deptScopedRoles, true) && $a->scope_type === \App\Models\Appointment::SCOPE_DEPT && !empty($a->scope_id);
        });
        $departmentId = $deptAppt?->scope_id;

        // Departments list (all if institution-wide, else just scoped department)
        if ($hasInstitutionWideOnly || !$departmentId) {
            $departments = Department::orderBy('name')->get();
        } else {
            $departments = Department::where('id', (int)$departmentId)->get();
        }

        // Courses filtered by department unless institution-wide
        if ($hasInstitutionWideOnly || !$departmentId) {
            $courses = Course::active()->orderBy('code')->get();
        } else {
            $courses = Course::active()->where('department_id', (int)$departmentId)->orderBy('code')->get();
        }

        // Show department filter only if institution-wide (can switch among departments)
        $showDepartmentFilter = $hasInstitutionWideOnly;

        return view('faculty.master-data.index', [
            'departments' => $departments, // Pre-filtered department collection
            'showDepartmentFilter' => $showDepartmentFilter,
            'showCdioTab' => true,
            'courses' => $courses, // Pre-filtered courses collection
            'scoped_department_id' => $departmentId ? (int)$departmentId : null,
        ]);
    }
}
