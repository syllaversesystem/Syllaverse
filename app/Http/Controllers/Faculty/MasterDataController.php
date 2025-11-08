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

    // Departments for optional filtering (VCAA/Associate VCAA will see ALL)
    $departments = Department::orderBy('name')->get();

    // Courses (active, ordered by code) for optional course-level filtering on ILO tab
    $courses = Course::active()->orderBy('code')->get();

        // Only users with institution-wide scope (VCAA/ASSOC_VCAA) should see the department filter
        $appointments = method_exists($user, 'appointments') ? $user->appointments()->active()->get() : collect();
        $showDepartmentFilter = $appointments->contains(function ($appointment) {
            return in_array($appointment->role, ['VCAA', 'ASSOC_VCAA']);
        });

        return view('faculty.master-data.index', [
            'departments' => $departments,
            'showDepartmentFilter' => $showDepartmentFilter,
            'showCdioTab' => true,
            'courses' => $courses,
        ]);
    }
}
