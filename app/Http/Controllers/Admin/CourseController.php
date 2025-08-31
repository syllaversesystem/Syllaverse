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

        $validator = Validator::make($request->all(), [
            'code'               => 'required|string|max:25|unique:courses,code',
            'title'              => 'required|string|max:255',
            'course_category'    => 'required|string|max:255',
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
                ->withErrors($validator, 'addCourse')
                ->withInput()
                ->with('openModal', 'addCourseModal');
        }

        // Determine department via appointments
        $departmentId = $this->pickDepartmentIdForCreate($user);
        if (!$departmentId) {
            $msg = 'No active appointment found to determine your department.';
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
            'code'               => 'required|string|max:25|unique:courses,code,' . $course->id,
            'title'              => 'required|string|max:255',
            'course_category'    => 'required|string|max:255',
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
     * Delete: Checks authorization by appointments, detaches prereqs both ways,
     * deletes course. Returns JSON (200) or redirects.
     */
    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || !$this->canManageCourses($user)) {
            $msg = 'You are not allowed to delete courses.';
            return $this->isAjax($request)
                ? response()->json(['message' => $msg], 403)
                : back()->with('error', $msg);
        }

        $course = Course::findOrFail($id);
        $this->ensureSameDepartmentOrAbort($user, $course, $request);

        $course->prerequisites()->detach();
        $course->isPrerequisiteFor()->detach();
        $course->delete();

        if ($this->isAjax($request)) {
            return response()->json([
                'message' => 'Course deleted successfully!',
                'id'      => (int) $id,
            ], 200);
        }

        return redirect()->route('admin.master-data.index', [
            'tab' => 'programcourse',
            'subtab' => 'courses',
        ])->with('success', 'Course deleted successfully!');
    }
}
