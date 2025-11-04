<?php
// -----------------------------------------------------------------------------
// * File: app/Http/Controllers/Faculty/CourseController.php
// * Description: AJAX-first CRUD for Courses with role-based access control (Faculty)
//               Department resolution via active Appointments and role-based restrictions.
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-01-16] Created faculty version based on admin CourseController
// -----------------------------------------------------------------------------

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Course;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    /**
     * Get the user's department ID based on their role
     * For basic faculty users, return their faculty appointment department
     * For administrative users, return null to allow department selection
     */
    private function getUserDepartmentId($user)
    {
        // Get all active appointments for the user
        $userAppointments = $user->appointments()->active()->get();
        
        // Log for debugging
        \Log::info('User appointments for ' . $user->id, $userAppointments->toArray());
        
        // Check if user has any administrative roles
        $hasAdministrativeRole = $userAppointments->contains(function($appointment) {
            return in_array($appointment->role, ['VCAA', 'ASSOC_VCAA', 'DEAN', 'DEPT_CHAIR', 'PROG_CHAIR']);
        });
        
        \Log::info('Has administrative role: ' . ($hasAdministrativeRole ? 'true' : 'false'));
        
        // If user has administrative role, allow them to choose department
        if ($hasAdministrativeRole) {
            return null;
        }
        
        // For basic faculty users, get their department from faculty appointment
        $facultyAppointment = $userAppointments->filter(function($appointment) {
            return $appointment->role === 'FACULTY' && 
                   $appointment->scope_type === 'Department' && 
                   !empty($appointment->scope_id);
        })->first();
        
        $departmentId = $facultyAppointment ? $facultyAppointment->scope_id : null;
        \Log::info('Faculty department ID: ' . $departmentId);
        
        return $departmentId;
    }

    /** Decide if current request expects JSON (fetch/XHR). */
    private function isAjax(Request $request): bool
    {
        return $request->ajax() || $request->wantsJson() || $request->expectsJson();
    }

    /** Check if user can manage courses (faculty with appointments). */
    private function canManageCourses(User $user): bool
    {
        return $user->appointments()
            ->active()
            ->whereIn('role', ['VCAA', 'ASSOC_VCAA', 'DEAN', 'DEPT_CHAIR', 'PROG_CHAIR', 'FACULTY'])
            ->exists();
    }

    /**
     * Get all department IDs this user is allowed to act on,
     * based on active appointments.
     */
    private function actingDepartmentIds(User $user): array
    {
        $appts = $user->appointments()
            ->active()
            ->get(['role', 'scope_type', 'scope_id']);

        $deptIds = [];
        $programIds = [];

        foreach ($appts as $a) {
            if ($a->role === 'DEPT_CHAIR') {
                $deptIds[] = (int) $a->scope_id;
            } elseif ($a->role === 'FACULTY') {
                $deptIds[] = (int) $a->scope_id;
            } elseif ($a->role === 'PROG_CHAIR') {
                $programIds[] = (int) $a->scope_id;
            }
        }

        if (!empty($programIds)) {
            $deptFromPrograms = Program::whereIn('id', $programIds)
                ->pluck('department_id')
                ->map(fn ($v) => (int) $v)
                ->all();
            $deptIds = array_merge($deptIds, $deptFromPrograms);
        }

        // Unique and reindex
        return array_values(array_unique(array_filter($deptIds)));
    }

    /** 
     * Determine department ID for course creation based on user role and request.
     */
    private function determineDepartmentForCourse(User $user, Request $request): ?int
    {
        $userDepartmentId = $this->getUserDepartmentId($user);
        
        // If user has auto-assigned department, use it
        if ($userDepartmentId) {
            return $userDepartmentId;
        }
        
        // Otherwise, use department from request (for administrative users)
        return $request->filled('department_id') ? (int) $request->department_id : null;
    }

    /** Ensure the course belongs to one of user's acting departments. */
    private function ensureSameDepartmentOrAbort(User $user, Course $course, Request $request)
    {
        $allowed = $this->actingDepartmentIds($user);
        $ok = in_array((int) $course->department_id, $allowed, true);

        if (!$ok) {
            $msg = 'You can only manage courses in your acting department(s).';
            if ($this->isAjax($request)) {
                abort(response()->json(['message' => $msg], 403));
            }
            abort(403, $msg);
        }
    }

    /**
     * Display the courses management page
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get filter parameter for department
        $departmentFilter = $request->get('department');
        
        // Build courses query with optional department filter (exclude deleted courses)
        $coursesQuery = Course::with(['department'])->notDeleted()->orderBy('title');
        if ($departmentFilter && $departmentFilter !== 'all') {
            $coursesQuery->where('department_id', $departmentFilter);
        }
        $courses = $coursesQuery->get();
        
        // Get all departments for dropdowns
        $departments = \App\Models\Department::all();
        
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
        
        // Check if user has any administrative roles (to show department column)
        $hasAdministrativeRole = $userAppointments->contains(function($appointment) {
            return in_array($appointment->role, ['VCAA', 'ASSOC_VCAA', 'DEAN', 'DEPT_CHAIR', 'PROG_CHAIR']);
        });
        
        // Hide department column for users without institution-wide scope (only VCAA/ASSOC_VCAA can see department column)
        $showDepartmentColumn = $hasInstitutionWideRole;
        
        // Check for department-specific roles
        $departmentSpecificAppointments = $userAppointments->filter(function($appointment) {
            return in_array($appointment->role, ['DEAN', 'DEPT_CHAIR', 'PROG_CHAIR']) && 
                   $appointment->scope_type === 'Department' && 
                   !empty($appointment->scope_id);
        });
        
        // Role-based dropdown logic
        if ($departmentSpecificAppointments->isNotEmpty() && !$hasInstitutionWideRole) {
            // User has ONLY department-specific roles - restrict to their department
            $firstDeptAppointment = $departmentSpecificAppointments->first();
            $userDepartment = $firstDeptAppointment->scope_id;
            $showDepartmentDropdown = false;
        }
        
        // For modals: hide department dropdown if user doesn't have VCAA/Associate VCAA role
        // Only VCAA and Associate VCAA users can see department selection in course modals
        $hasVcaaRole = $userAppointments->contains(function($appointment) {
            return in_array($appointment->role, ['VCAA', 'ASSOC_VCAA']);
        });
        $showAddDepartmentDropdown = $hasVcaaRole;
        $showEditDepartmentDropdown = $hasVcaaRole;
        
        // If user has no administrative role, get their department from scope
        if (!$hasAdministrativeRole) {
            $facultyAppointment = $userAppointments->filter(function($appointment) {
                return $appointment->role === 'FACULTY' && 
                       $appointment->scope_type === 'Department' && 
                       !empty($appointment->scope_id);
            })->first();
            
            if ($facultyAppointment) {
                $userDepartment = $facultyAppointment->scope_id;
            }
        }
        
        // Filter-based dropdown logic: if a specific department is filtered, pre-select it but keep dropdown pickable
        if ($departmentFilter && $departmentFilter !== 'all') {
            $userDepartment = $departmentFilter;
        }
        
        return view('faculty.courses.index', compact(
            'courses', 
            'departments',
            'userDepartment',
            'showDepartmentDropdown',
            'showAddDepartmentDropdown',
            'showEditDepartmentDropdown',
            'showDepartmentFilter',
            'showDepartmentColumn',
            'departmentFilter'
        ));
    }

    /**
     * Create: Validates, resolves department from appointments, creates course,
     * syncs prerequisites. Returns JSON (201) or redirects.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $userDepartmentId = $this->getUserDepartmentId($user);
        $departmentId = $userDepartmentId ?? $request->input('department_id');

        // Check if a deleted course with the same code exists
        $deletedCourse = Course::where('code', $request->code)
                                ->where('status', Course::STATUS_DELETED)
                                ->first();

        // Build validation rules dynamically
        $validationRules = [
            'title'              => 'required|string|max:255',
            'course_category'    => 'required|string|max:255',
            'contact_hours_lec'  => 'required|integer|min:0',
            'contact_hours_lab'  => 'nullable|integer|min:0',
            'description'        => 'nullable|string',
            'prerequisite_ids'   => 'nullable|array',
            'prerequisite_ids.*' => 'exists:courses,id',
        ];

        // Add code validation (different rules for new vs restore)
        if ($deletedCourse) {
            // For restore, just validate code format (no uniqueness check)
            $validationRules['code'] = 'required|string|max:25';
        } else {
            // For new courses, validate uniqueness
            $validationRules['code'] = [
                'required', 'string', 'max:25',
                Rule::unique('courses', 'code')->where(function ($query) {
                    return $query->whereIn('status', [Course::STATUS_ACTIVE, Course::STATUS_INACTIVE]);
                })
            ];
        }

        // Only add department_id validation if user doesn't have auto-assigned department
        if (!$userDepartmentId) {
            $validationRules['department_id'] = 'required|exists:departments,id';
        }

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            if ($this->isAjax($request)) {
                return response()->json([
                    'message' => 'Validation error.',
                    'errors'  => $validator->errors(),
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        // Ensure we have a valid department ID
        if (!$departmentId) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unable to determine department for course creation.',
                    'errors' => ['department_id' => ['Department is required but could not be determined from your role.']]
                ], 422);
            }
            return redirect()->back()->withErrors(['department_id' => 'Department is required but could not be determined from your role.']);
        }

        $lec = (int) $request->contact_hours_lec;
        $lab = (int) ($request->contact_hours_lab ?? 0);
        $totalUnits = $lec + $lab;

        if ($deletedCourse) {
            // Restore the deleted course
            $deletedCourse->update([
                'department_id'     => $departmentId,
                'course_category'   => $request->course_category,
                'title'             => $request->title,
                'contact_hours_lec' => $lec,
                'contact_hours_lab' => $lab,
                'total_units'       => $totalUnits,
                'description'       => $request->description,
                'status'            => Course::STATUS_ACTIVE,
            ]);

            if ($request->filled('prerequisite_ids')) {
                $deletedCourse->prerequisites()->sync($request->prerequisite_ids);
            }

        if ($this->isAjax($request)) {
            $freshCourse = $deletedCourse->fresh()->load('department');
            return response()->json([
                'message' => 'Course restored successfully!',
                'id' => $freshCourse->id,
                'department_id' => $freshCourse->department_id,
                'department_code' => $freshCourse->department->code ?? '',
                'department_name' => $freshCourse->department->name ?? '',
                'course' => $freshCourse,
            ], 201);
        }            return redirect()->route('faculty.courses.index')->with('success', 'Course restored successfully!');
        }

        // Create new course
        $course = Course::create([
            'department_id'     => $departmentId,
            'course_category'   => $request->course_category,
            'code'              => $request->code,
            'title'             => $request->title,
            'contact_hours_lec' => $lec,
            'contact_hours_lab' => $lab,
            'total_units'       => $totalUnits,
            'description'       => $request->description,
        ]);

        if ($request->filled('prerequisite_ids')) {
            $course->prerequisites()->sync($request->prerequisite_ids);
        }

        if ($this->isAjax($request)) {
            $course->load('department');
            return response()->json([
                'message' => 'Course added successfully!',
                'id' => $course->id,
                'department_id' => $course->department_id,
                'department_code' => $course->department->code ?? '',
                'department_name' => $course->department->name ?? '',
                'course' => $course,
            ], 201);
        }

        return redirect()->route('faculty.courses.index')->with('success', 'Course added successfully!');
    }

    /**
     * Update: Validates, checks department authorization by appointments,
     * updates fields and prerequisites. Returns JSON (200) or redirects.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $course = Course::findOrFail($id);

        $userDepartmentId = $this->getUserDepartmentId($user);
        $departmentId = $userDepartmentId ?? $request->input('department_id');

        // Build validation rules dynamically
        $validationRules = [
            'code'               => [
                'required', 'string', 'max:25',
                Rule::unique('courses', 'code')->ignore($course->id)->where(function ($query) {
                    return $query->whereIn('status', [Course::STATUS_ACTIVE, Course::STATUS_INACTIVE]);
                })
            ],
            'title'              => 'required|string|max:255',
            'course_category'    => 'required|string|max:255',
            'contact_hours_lec'  => 'required|integer|min:0',
            'contact_hours_lab'  => 'nullable|integer|min:0',
            'description'        => 'nullable|string',
            'prerequisite_ids'   => 'nullable|array',
            'prerequisite_ids.*' => 'exists:courses,id',
        ];

        // Only add department_id validation if user doesn't have auto-assigned department
        if (!$userDepartmentId) {
            $validationRules['department_id'] = 'required|exists:departments,id';
        }

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            if ($this->isAjax($request)) {
                return response()->json([
                    'message' => 'Validation error.',
                    'errors'  => $validator->errors(),
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        // Ensure we have a valid department ID
        if (!$departmentId) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unable to determine department for course update.',
                    'errors' => ['department_id' => ['Department is required but could not be determined from your role.']]
                ], 422);
            }
            return redirect()->back()->withErrors(['department_id' => 'Department is required but could not be determined from your role.']);
        }

        $lec = (int) $request->contact_hours_lec;
        $lab = (int) ($request->contact_hours_lab ?? 0);
        $totalUnits = $lec + $lab;

        $course->update([
            'course_category'   => $request->course_category,
            'code'              => $request->code,
            'title'             => $request->title,
            'contact_hours_lec' => $lec,
            'contact_hours_lab' => $lab,
            'total_units'       => $totalUnits,
            'description'       => $request->description,
            'department_id'     => $departmentId,
        ]);

        $course->prerequisites()->sync($request->prerequisite_ids ?? []);

        if ($this->isAjax($request)) {
            $freshCourse = $course->fresh()->load('department');
            return response()->json([
                'message' => 'Course updated successfully!',
                'id' => $freshCourse->id,
                'department_id' => $freshCourse->department_id,
                'department_code' => $freshCourse->department->code ?? '',
                'department_name' => $freshCourse->department->name ?? '',
                'course' => $freshCourse,
            ], 200);
        }

        return redirect()->route('faculty.courses.index')->with('success', 'Course updated successfully!');
    }

    /**
     * Remove or delete a course based on action_type.
     */
    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        $course = Course::findOrFail($id);

        // Validate action type
        $request->validate([
            'action_type' => 'required|in:remove,delete'
        ]);

        $actionType = $request->input('action_type');
        $message = '';

        if ($actionType === 'remove') {
            // Set status to deleted (soft removal)
            $course->update(['status' => Course::STATUS_DELETED]);
            $message = 'Course removed successfully! It can be restored later if needed.';
        } else {
            // Permanent deletion - detach relationships first
            $course->prerequisites()->detach();
            $course->isPrerequisiteFor()->detach();
            $course->delete();
            $message = 'Course deleted permanently!';
        }

        if ($this->isAjax($request)) {
            return response()->json([
                'message' => $message,
                'id'      => (int) $id,
                'action'  => $actionType,
            ], 200);
        }

        return redirect()->route('faculty.courses.index')->with('success', $message);
    }

    /**
     * Search for deleted courses based on title or code.
     */
    public function searchDeleted(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $deletedCourses = Course::where('status', Course::STATUS_DELETED)
            ->where(function($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('code', 'LIKE', "%{$query}%");
            })
            ->with('department')
            ->limit(5)
            ->get();

        return response()->json($deletedCourses->map(function($course) {
            return [
                'id' => $course->id,
                'title' => $course->title,
                'code' => $course->code,
                'description' => $course->description,
                'course_category' => $course->course_category,
                'contact_hours_lec' => $course->contact_hours_lec,
                'contact_hours_lab' => $course->contact_hours_lab,
                'department_id' => $course->department_id,
                'department_name' => $course->department->name ?? 'Unknown Department',
                'display_text' => "{$course->title} ({$course->code}) - {$course->department->name}",
            ];
        }));
    }

    /**
     * Filter courses by department via AJAX.
     */
    public function filterByDepartment(Request $request)
    {
        $user = auth()->user();
        $departmentFilter = $request->get('department');
        $q = trim((string) $request->get('q', ''));
        
        // Build courses query with optional department filter (exclude deleted courses)
        $coursesQuery = Course::with(['department', 'prerequisites'])->notDeleted();
        if ($departmentFilter && $departmentFilter !== 'all') {
            $coursesQuery->where('department_id', $departmentFilter);
        }
        if ($q !== '') {
            $coursesQuery->where(function($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            });
        }
        $courses = $coursesQuery->get();
        $isSearch = $q !== '';
        
        // Get all departments for context
        $departments = \App\Models\Department::all();
        
        // Get user permissions (same logic as index method)
        $userAppointments = $user->appointments()->active()->get();
        
        // Check for institution-wide roles (roles with Institution scope)
        $hasInstitutionWideRole = $userAppointments->contains(function($appointment) {
            return in_array($appointment->role, ['VCAA', 'ASSOC_VCAA']) || 
                   ($appointment->scope_type === 'Institution') ||
                   ($appointment->role === 'DEAN' && $appointment->scope_type === 'Institution');
        });
        
        // Hide department column for users without institution-wide scope (only VCAA/ASSOC_VCAA can see department column)
        $showDepartmentColumn = $hasInstitutionWideRole;
        
        // Check if user can manage courses
        $canManageCourses = $user->role === 'faculty' 
            || (method_exists($user, 'isDeptChair') && $user->isDeptChair())
            || (method_exists($user, 'isProgChair') && $user->isProgChair());
        
        // Return JSON response with table data
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('faculty.courses.partials.courses-table-content', compact(
                    'courses',
                    'departments', 
                    'canManageCourses',
                    'showDepartmentColumn',
                    'departmentFilter',
                    'isSearch'
                ))->render(),
                'count' => $courses->count(),
                'department_filter' => $departmentFilter,
                'search' => $q,
            ]);
        }
        
        // Fallback to redirect for non-AJAX requests
        return redirect()->route('faculty.courses.index', ['department' => $departmentFilter]);
    }
}