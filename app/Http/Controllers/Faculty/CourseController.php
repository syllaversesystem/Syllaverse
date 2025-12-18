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
            // Treat DEPT_HEAD as equivalent to legacy DEPT_CHAIR
            return in_array($appointment->role, ['VCAA', 'ASSOC_VCAA', 'DEAN', 'DEPT_CHAIR', 'DEPT_HEAD', 'PROG_CHAIR']);
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
            if (in_array($a->role, ['DEPT_CHAIR', 'DEPT_HEAD'])) {
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
        // Resolve appointments first (needed for scoping BEFORE querying courses)
        $userAppointments = $user->appointments()->active()->get();

        // Institution-wide roles (see ALL departments)
        $hasInstitutionWideRole = $userAppointments->contains(function($appointment) {
            return in_array($appointment->role, ['VCAA', 'ASSOC_VCAA']) ||
                   ($appointment->scope_type === 'Institution') ||
                   ($appointment->role === 'DEAN' && $appointment->scope_type === 'Institution');
        });

        // Department filter param only honored for institution-wide users
        $departmentFilter = $hasInstitutionWideRole ? $request->get('department') : null;

        // Determine a single scoped department for non institution-wide users
        $scopedDepartmentId = null;

        if (!$hasInstitutionWideRole) {
            // 1. Department-scoped leadership roles (DEPT_HEAD/DEPT_CHAIR, DEAN with Department scope)
            $deptRoleAppt = $userAppointments->first(function($a) {
                return in_array($a->role, ['DEPT_HEAD', 'DEPT_CHAIR', 'DEAN']) &&
                       $a->scope_type === 'Department' && !empty($a->scope_id);
            });
            if ($deptRoleAppt) {
                $scopedDepartmentId = (int) $deptRoleAppt->scope_id;
            }

            // 2. Program Chair role -> map program to department
            if (!$scopedDepartmentId) {
                $progAppt = $userAppointments->first(function($a) {
                    return $a->role === 'PROG_CHAIR' && $a->scope_type === 'Program' && !empty($a->scope_id);
                });
                if ($progAppt) {
                    $progDept = Program::where('id', $progAppt->scope_id)->value('department_id');
                    if ($progDept) { $scopedDepartmentId = (int) $progDept; }
                }
            }

            // 3. Faculty department appointment fallback
            if (!$scopedDepartmentId) {
                $facultyAppt = $userAppointments->first(function($a) {
                    return $a->role === 'FACULTY' && $a->scope_type === 'Department' && !empty($a->scope_id);
                });
                if ($facultyAppt) { $scopedDepartmentId = (int) $facultyAppt->scope_id; }
            }
        }

        // Build courses query (exclude deleted); apply scoping
        $coursesQuery = Course::with(['department', 'prerequisites'])->notDeleted()->orderBy('title');
        if ($hasInstitutionWideRole) {
            if ($departmentFilter && $departmentFilter !== 'all') {
                $coursesQuery->where('department_id', $departmentFilter);
            }
        } elseif ($scopedDepartmentId) {
            $coursesQuery->where('department_id', $scopedDepartmentId);
        }
        $courses = $coursesQuery->get();

        // Departments list (only needed for institution-wide users or dropdown display in modals)
        $departments = \App\Models\Department::all();

        // UI flags
        $showDepartmentFilter = $hasInstitutionWideRole; // Only VCAA / ASSOC_VCAA (and institution-wide DEAN if any) can filter
        $showDepartmentColumn = $hasInstitutionWideRole; // Hide column for scoped users

        // Department selection inside modals (assigning department when creating/editing)
        $hasVcaaRole = $userAppointments->contains(function($a) { return in_array($a->role, ['VCAA', 'ASSOC_VCAA']); });
        $showAddDepartmentDropdown  = $hasVcaaRole; // Only institution-wide can choose department
        $showEditDepartmentDropdown = $hasVcaaRole;

        // Maintain legacy variables consumed by Blade
        $userDepartment = $scopedDepartmentId; // Pre-selected / implicit department for scoped users
        $showDepartmentDropdown = $hasInstitutionWideRole; // (Not explicitly used in Blade right now)

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
            'cmo_reference'      => 'nullable|string|max:255',
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
                'cmo_reference'     => $request->cmo_reference,
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
            'cmo_reference'     => $request->cmo_reference,
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
            'cmo_reference'      => 'nullable|string|max:255',
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
            'cmo_reference'     => $request->cmo_reference,
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
                'cmo_reference' => $course->cmo_reference,
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
        
        // Appointments & institution-wide check
        $userAppointments = $user->appointments()->active()->get();
        $hasInstitutionWideRole = $userAppointments->contains(function($a) {
            return in_array($a->role, ['VCAA', 'ASSOC_VCAA']) ||
                   ($a->scope_type === 'Institution') ||
                   ($a->role === 'DEAN' && $a->scope_type === 'Institution');
        });

        // Resolve scoped department for non institution-wide users
        $scopedDeptId = null;
        if (!$hasInstitutionWideRole) {
            $deptAppt = $userAppointments->first(function($a) {
                return in_array($a->role, ['DEPT_HEAD', 'DEPT_CHAIR', 'DEAN']) && $a->scope_type === 'Department' && !empty($a->scope_id);
            });
            if ($deptAppt) { $scopedDeptId = (int) $deptAppt->scope_id; }
            if (!$scopedDeptId) {
                $progAppt = $userAppointments->first(function($a) {
                    return $a->role === 'PROG_CHAIR' && $a->scope_type === 'Program' && !empty($a->scope_id);
                });
                if ($progAppt) {
                    $progDept = Program::where('id', $progAppt->scope_id)->value('department_id');
                    if ($progDept) { $scopedDeptId = (int) $progDept; }
                }
            }
            if (!$scopedDeptId) {
                $facultyAppt = $userAppointments->first(function($a) {
                    return $a->role === 'FACULTY' && $a->scope_type === 'Department' && !empty($a->scope_id);
                });
                if ($facultyAppt) { $scopedDeptId = (int) $facultyAppt->scope_id; }
            }
        }

        // Build query with enforced scoping
        $coursesQuery = Course::with(['department', 'prerequisites'])->notDeleted();
        if ($hasInstitutionWideRole) {
            if ($departmentFilter && $departmentFilter !== 'all') {
                $coursesQuery->where('department_id', $departmentFilter);
            }
        } elseif ($scopedDeptId) {
            $coursesQuery->where('department_id', $scopedDeptId);
        }
        if ($q !== '') {
            $coursesQuery->where(function($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            });
        }
        $courses = $coursesQuery->get();
        $isSearch = $q !== '';

        // Departments list for institution-wide context (still passed for uniform partial)
        $departments = \App\Models\Department::all();
        $showDepartmentColumn = $hasInstitutionWideRole;

        // Capability flag
        $canManageCourses = $user->role === 'faculty' ||
            (method_exists($user, 'isDeptChair') && $user->isDeptChair()) ||
            (method_exists($user, 'isProgChair') && $user->isProgChair());

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
                'department_filter' => $hasInstitutionWideRole ? $departmentFilter : $scopedDeptId,
                'search' => $q,
            ]);
        }

        return redirect()->route('faculty.courses.index');
    }
}