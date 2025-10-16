<?php
// -----------------------------------------------------------------------------
// * File: app/Http/Controllers/Admin/CourseController.php
// * Description: AJAX-first CRUD for Courses with prerequisite syncing (Admin)
//               Department resolution via active Appointments (scope_id and Program→department).
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-08-16] Recreated from scratch – contact-hours-only, AJAX responses.
// [2025-08-17] Refactor – removed reliance on users.department_id; now derives
//              acting department(s) from active appointments (DEPT/PROG/FACULTY).
// -----------------------------------------------------------------------------

namespace App\Http\Controllers\Admin;

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
    /** Decide if current request expects JSON (fetch/XHR). */
    private function isAjax(Request $request): bool
    {
        return $request->ajax() || $request->wantsJson() || $request->expectsJson();
    }

    /** Check if user can manage courses (admin or chair via appointments). */
    private function canManageCourses(User $user): bool
    {
        if ($user->role === 'admin') {
            return true;
        }
        return $user->appointments()
            ->active()
            ->whereIn('role', [Appointment::ROLE_DEPT, Appointment::ROLE_PROG])
            ->exists();
    }

    /**
     * Get all department IDs this user is allowed to act on,
     * based on active appointments.
     *
     * DEPT_CHAIR -> department = scope_id
     * FACULTY    -> department = scope_id (if you allow faculty to manage, include it)
     * PROG_CHAIR -> department = Program(scope_id).department_id
     */
    private function actingDepartmentIds(User $user): array
    {
        $appts = $user->appointments()
            ->active()
            ->get(['role', 'scope_type', 'scope_id']);

        $deptIds = [];
        $programIds = [];

        foreach ($appts as $a) {
            if ($a->role === Appointment::ROLE_DEPT) {
                $deptIds[] = (int) $a->scope_id;
            } elseif ($a->role === Appointment::ROLE_FACULTY) {
                // Include this if Faculty should also manage courses; if not, remove.
                $deptIds[] = (int) $a->scope_id;
            } elseif ($a->role === Appointment::ROLE_PROG) {
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

    /** Pick a single department for creation (first available). */
    private function pickDepartmentIdForCreate(User $user): ?int
    {
        $ids = $this->actingDepartmentIds($user);
        return $ids[0] ?? null;
    }

    /** 
     * Determine department ID for course creation based on user role and request.
     * VCAA/ASSOC_VCAA: Use department_id from request (dropdown)
     * DEAN/DEPT_CHAIR/PROG_CHAIR only: Use their specific department
     * VCAA + CHAIR roles: Use department_id from request if provided, otherwise their department
     */
    private function determineDepartmentForCourse(User $user, Request $request): ?int
    {
        $userAppointments = $user->appointments()->active()->get();
        
        // Check for VCAA/ASSOC_VCAA roles
        $hasVcaaRole = $userAppointments->contains(function($appointment) {
            return in_array($appointment->role, [Appointment::ROLE_VCAA, Appointment::ROLE_ASSOC_VCAA]);
        });
        
        // Check for department-specific roles
        $departmentAppointments = $userAppointments->filter(function($appointment) {
            // DEPT_CHAIR roles with department scope
            if ($appointment->role === Appointment::ROLE_DEPT && 
                $appointment->scope_type === Appointment::SCOPE_DEPT && 
                !empty($appointment->scope_id)) {
                return true;
            }
            
            // PROG_CHAIR roles with department scope (directly assigned to department)
            if ($appointment->role === Appointment::ROLE_PROG && 
                $appointment->scope_type === Appointment::SCOPE_DEPT && 
                !empty($appointment->scope_id)) {
                return true;
            }
            
            // DEAN roles: Accept both department-scoped and institution-scoped 
            // If institution-scoped, scope_id should contain the department they oversee
            if ($appointment->role === Appointment::ROLE_DEAN) {
                // Department-specific DEAN
                if ($appointment->scope_type === Appointment::SCOPE_DEPT && !empty($appointment->scope_id)) {
                    return true;
                }
                // Institution-wide DEAN with department assignment
                if ($appointment->scope_type === Appointment::SCOPE_INSTITUTION && !empty($appointment->scope_id)) {
                    return true;
                }
            }
            
            return false;
        });
        
        // Check for program-specific roles and get their departments
        $programAppointments = $userAppointments->filter(function($appointment) {
            // Only PROG_CHAIR roles that are program-scoped (not department-scoped)
            return $appointment->role === Appointment::ROLE_PROG && 
                   $appointment->scope_type === Appointment::SCOPE_PROG && 
                   !empty($appointment->scope_id);
        });
        
        if ($hasVcaaRole) {
            // VCAA/ASSOC_VCAA: Always use department dropdown when provided
            if ($request->filled('department_id')) {
                return (int) $request->department_id;
            }
            
            // If VCAA also has department roles, can fall back to their department
            if ($departmentAppointments->isNotEmpty()) {
                return (int) $departmentAppointments->first()->scope_id;
            }
            
            // If VCAA has program roles, get department from program
            if ($programAppointments->isNotEmpty()) {
                $programId = $programAppointments->first()->scope_id;
                $program = Program::find($programId);
                return $program ? (int) $program->department_id : null;
            }
            
            return null; // VCAA should select department
        }
        
        // Non-VCAA roles: Use their specific department
        if ($departmentAppointments->isNotEmpty()) {
            return (int) $departmentAppointments->first()->scope_id;
        }
        
        // Program chairs: Get department from their program
        if ($programAppointments->isNotEmpty()) {
            $programId = $programAppointments->first()->scope_id;
            $program = Program::find($programId);
            return $program ? (int) $program->department_id : null;
        }
        
        // Check if user has DEAN role without specific department (institution-wide DEAN)
        $institutionDeans = $userAppointments->filter(function($appointment) {
            return $appointment->role === Appointment::ROLE_DEAN && 
                   $appointment->scope_type === Appointment::SCOPE_INSTITUTION && 
                   empty($appointment->scope_id);
        });
        
        if ($institutionDeans->isNotEmpty()) {
            // Institution-wide DEAN without specific department should use dropdown
            if ($request->filled('department_id')) {
                return (int) $request->department_id;
            }
            return null; // Should use dropdown
        }
        
        return null;
    }

    /** Ensure the course belongs to one of user's acting departments. */
    private function ensureSameDepartmentOrAbort(User $user, Course $course, Request $request)
    {
        $allowed = $this->actingDepartmentIds($user);
        $ok = in_array((int) $course->department_id, $allowed, true) || $user->role === 'admin';

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
        $departmentFilter = $request->get('department_filter');
        
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
        
        // Check for department-specific roles
        $departmentSpecificAppointments = $userAppointments->filter(function($appointment) {
            return in_array($appointment->role, ['DEAN', 'DEPT_CHAIR', 'PROG_CHAIR']) && 
                   $appointment->scope_type === 'Department' && 
                   !empty($appointment->scope_id);
        });
        
        // Role-based dropdown logic for course creation
        $showDepartmentDropdownInModal = $hasInstitutionWideRole;
        if ($departmentSpecificAppointments->isNotEmpty()) {
            if (!$hasInstitutionWideRole) {
                // User has ONLY department-specific roles - restrict to their department
                $firstDeptAppointment = $departmentSpecificAppointments->first();
                $userDepartment = $firstDeptAppointment->scope_id;
                $showDepartmentDropdown = false;
                $showDepartmentDropdownInModal = false;
            } else {
                // User has VCAA + department roles - show dropdown but can have default
                $firstDeptAppointment = $departmentSpecificAppointments->first();
                $userDepartment = $firstDeptAppointment->scope_id;
                $showDepartmentDropdownInModal = true;
            }
        }
        
        return view('admin.courses.index', compact(
            'courses', 
            'departments',
            'userDepartment',
            'showDepartmentDropdown',
            'showDepartmentDropdownInModal',
            'showDepartmentFilter',
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
        if (!$user || !$this->canManageCourses($user)) {
            $msg = 'You are not allowed to create courses.';
            return $this->isAjax($request)
                ? response()->json(['message' => $msg], 403)
                : back()->with('error', $msg);
        }

        // Check if a deleted course with the same code exists
        $deletedCourse = Course::where('code', $request->code)
                                ->where('status', Course::STATUS_DELETED)
                                ->first();

        if ($deletedCourse) {
            // Restore the deleted course
            $validator = Validator::make($request->all(), [
                'code'               => 'required|string|max:25', // Remove unique validation for restoration
                'title'              => 'required|string|max:255',
                'course_category'    => 'required|string|max:255',
                'has_iga'            => 'nullable|boolean',
                'contact_hours_lec'  => 'required|integer|min:0',
                'contact_hours_lab'  => 'nullable|integer|min:0',
                'description'        => 'required|string',
                'department_id'      => 'nullable|exists:departments,id',
                'prerequisite_ids'   => 'nullable|array',
                'prerequisite_ids.*' => 'exists:courses,id',
            ]);

            if ($validator->fails()) {
                if ($this->isAjax($request)) {
                    return response()->json([
                        'message' => 'Validation error.',
                        'errors'  => $validator->errors(),
                    ], 422);
                }
                return back()->withErrors($validator)->withInput();
            }

            $departmentId = $this->determineDepartmentForCourse($user, $request);
            $lec = (int) $request->contact_hours_lec;
            $lab = (int) ($request->contact_hours_lab ?? 0);
            $totalUnits = $lec + $lab;

            $deletedCourse->update([
                'department_id'     => $departmentId,
                'course_category'   => $request->course_category,
                'title'             => $request->title,
                'has_iga'           => $request->has_iga ? true : false,
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
                return response()->json([
                    'message' => 'Course restored successfully!',
                    'id'      => $deletedCourse->id,
                    'course'  => $deletedCourse->fresh()->load('department'),
                ], 201);
            }

            return redirect()->route('admin.courses.index')->with('success', 'Course restored successfully!');
        }

        // Create new course (validate unique code among non-deleted courses)
        $validator = Validator::make($request->all(), [
            'code'               => [
                'required', 'string', 'max:25',
                Rule::unique('courses', 'code')->where(function ($query) {
                    return $query->whereIn('status', [Course::STATUS_ACTIVE, Course::STATUS_INACTIVE]);
                })
            ],
            'title'              => 'required|string|max:255',
            'course_category'    => 'required|string|max:255',
            'has_iga'            => 'nullable|boolean',
            'contact_hours_lec'  => 'required|integer|min:0',
            'contact_hours_lab'  => 'nullable|integer|min:0',
            'description'        => 'required|string',
            'department_id'      => 'nullable|exists:departments,id',
            'prerequisite_ids'   => 'nullable|array',
            'prerequisite_ids.*' => 'exists:courses,id',
        ]);

        if ($validator->fails()) {
            if ($this->isAjax($request)) {
                return response()->json([
                    'message' => 'Validation error.',
                    'errors'  => $validator->errors(),
                ], 422);
            }
            return back()
                ->withErrors($validator, 'addCourse')
                ->withInput()
                ->with('openModal', 'addCourseModal');
        }

        // Determine department based on role and request
        $departmentId = $this->determineDepartmentForCourse($user, $request);
        if (!$departmentId) {
            $msg = 'Unable to determine department. Please select a department or check your appointments.';
            return $this->isAjax($request)
                ? response()->json(['message' => $msg], 403)
                : back()->with('error', $msg);
        }

        $lec = (int) $request->contact_hours_lec;
        $lab = (int) ($request->contact_hours_lab ?? 0);

        // If total_units exists in schema, keep a sensible fill; else ignored.
        $totalUnits = $lec + $lab;

        $course = Course::create([
            'department_id'     => $departmentId,
            'course_category'   => $request->course_category,
            'code'              => $request->code,
            'title'             => $request->title,
            'has_iga'           => $request->has_iga ? true : false,
            'contact_hours_lec' => $lec,
            'contact_hours_lab' => $lab,
            'total_units'       => $totalUnits,
            'description'       => $request->description,
        ]);

        if ($request->filled('prerequisite_ids')) {
            $course->prerequisites()->sync($request->prerequisite_ids);
        }

        if ($this->isAjax($request)) {
            return response()->json([
                'message' => 'Course added successfully!',
                'id'      => $course->id,
            ], 201);
        }

        return redirect()->route('admin.master-data.index', [
            'tab' => 'programcourse',
            'subtab' => 'courses',
        ])->with('success', 'Course added successfully!');
    }

    /**
     * Update: Validates, checks department authorization by appointments,
     * updates fields and prerequisites. Returns JSON (200) or redirects.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || !$this->canManageCourses($user)) {
            $msg = 'You are not allowed to update courses.';
            return $this->isAjax($request)
                ? response()->json(['message' => $msg], 403)
                : back()->with('error', $msg);
        }

        $course = Course::findOrFail($id);
        $this->ensureSameDepartmentOrAbort($user, $course, $request);

        $validator = Validator::make($request->all(), [
            'code'               => [
                'required', 'string', 'max:25',
                Rule::unique('courses', 'code')->ignore($course->id)->where(function ($query) {
                    return $query->whereIn('status', [Course::STATUS_ACTIVE, Course::STATUS_INACTIVE]);
                })
            ],
            'title'              => 'required|string|max:255',
            'course_category'    => 'required|string|max:255',
            'has_iga'            => 'nullable|boolean',
            'contact_hours_lec'  => 'required|integer|min:0',
            'contact_hours_lab'  => 'nullable|integer|min:0',
            'description'        => 'required|string',
            'prerequisite_ids'   => 'nullable|array',
            'prerequisite_ids.*' => 'exists:courses,id',
        ]);

        if ($validator->fails()) {
            if ($this->isAjax($request)) {
                return response()->json([
                    'message' => 'Validation error.',
                    'errors'  => $validator->errors(),
                ], 422);
            }
            return back()
                ->withErrors($validator, 'editCourse')
                ->withInput()
                ->with('openModal', 'editCourseModal');
        }

        $lec = (int) $request->contact_hours_lec;
        $lab = (int) ($request->contact_hours_lab ?? 0);
        $totalUnits = $lec + $lab;

        $course->update([
            'course_category'   => $request->course_category,
            'code'              => $request->code,
            'title'             => $request->title,
            'has_iga'           => $request->has_iga ? true : false,
            'contact_hours_lec' => $lec,
            'contact_hours_lab' => $lab,
            'total_units'       => $totalUnits,
            'description'       => $request->description,
        ]);

        $course->prerequisites()->sync($request->prerequisite_ids ?? []);

        if ($this->isAjax($request)) {
            return response()->json([
                'message' => 'Course updated successfully!',
                'id'      => $course->id,
            ], 200);
        }

        return redirect()->route('admin.master-data.index', [
            'tab' => 'programcourse',
            'subtab' => 'courses',
        ])->with('success', 'Course updated successfully!');
    }

    /**
     * Remove or delete a course based on action_type.
     */
    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || !$this->canManageCourses($user)) {
            $msg = 'You are not allowed to manage courses.';
            return $this->isAjax($request)
                ? response()->json(['message' => $msg], 403)
                : back()->with('error', $msg);
        }

        $course = Course::findOrFail($id);
        $this->ensureSameDepartmentOrAbort($user, $course, $request);

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

        return redirect()->route('admin.master-data.index', [
            'tab' => 'programcourse',
            'subtab' => 'courses',
        ])->with('success', $message);
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
                'has_iga' => $course->has_iga,
                'contact_hours_lec' => $course->contact_hours_lec,
                'contact_hours_lab' => $course->contact_hours_lab,
                'department_id' => $course->department_id,
                'department_name' => $course->department->name ?? 'Unknown Department',
                'display_text' => "{$course->title} ({$course->code}) - {$course->department->name}",
            ];
        }));
    }
}
