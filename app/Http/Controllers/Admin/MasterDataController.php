<?php
// -----------------------------------------------------------------------------
// * File: app/Http/Controllers/Admin/MasterDataController.php
// * Description: Handles Master Data page composition (SO, ILO, Programs, Courses)
//                – view-only loader scoped by the acting user's appointments.
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Added reorderIlo(), reorderSo(), store/update/destroy for SO/ILO.
// [2025-08-18] ✂️ Cleanup – removed all SO/ILO CRUD & reorder methods; this
//              controller now only composes data for the Master Data index.
//              (SO CRUD moved to StudentOutcomeController; ILO TBD.)
// -----------------------------------------------------------------------------

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Course;
use App\Models\IntendedLearningOutcome;
use App\Models\Program;
use App\Models\StudentOutcome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterDataController extends Controller
{
    /**
     * Show the Master Data page (SO, ILO, Programs, Courses).
     * - Admin: sees all Programs/Courses.
     * - Dept Chair / Faculty: sees items under their department scope_id(s).
     * - Program Chair: sees items under their program's department.
     * - ILO list is returned only for a course within the user's accessible set.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $selectedCourseId = (int) $request->input('course_id');

        // Admin: no restriction; load all programs & courses.
        if ($user->role === 'admin') {
            $programs = Program::with('department')->get();

            $courses = Course::with(['prerequisites:id,code'])
                ->orderBy('code')
                ->get();

            $iloList = $selectedCourseId
                ? IntendedLearningOutcome::where('course_id', $selectedCourseId)->orderBy('position')->get()
                : collect();

            return view('admin.master-data.index', [
                'studentOutcomes'          => StudentOutcome::all(),
                'intendedLearningOutcomes' => $iloList,
                'programs'                 => $programs,
                'courses'                  => $courses,
            ]);
        }

        // Non-admin: gather department ids from DEPT/FACULTY roles
        $deptIds = $user->appointments()
            ->active()
            ->whereIn('role', [Appointment::ROLE_DEPT, Appointment::ROLE_FACULTY])
            ->pluck('scope_id')
            ->values();

        // Also include department ids implied by PROG chair roles
        $progIds = $user->appointments()
            ->active()
            ->where('role', Appointment::ROLE_PROG)
            ->pluck('scope_id')
            ->values();

        if ($progIds->isNotEmpty()) {
            $deptFromProg = Program::whereIn('id', $progIds)->pluck('department_id');
            $deptIds = $deptIds->merge($deptFromProg)->unique()->values();
        }

        // If user has no scoped departments, show empty lists
        if ($deptIds->isEmpty()) {
            return view('admin.master-data.index', [
                'studentOutcomes'          => StudentOutcome::all(),
                'intendedLearningOutcomes' => collect(),
                'programs'                 => collect(),
                'courses'                  => collect(),
            ]);
        }

        // Load programs/courses for accessible departments
        $programs = Program::whereIn('department_id', $deptIds)->with('department')->get();

        $courses = Course::with(['prerequisites:id,code'])
            ->whereIn('department_id', $deptIds)
            ->orderBy('code')
            ->get();

        // Only load ILOs for a course the user can access
        $iloList = collect();
        if ($selectedCourseId) {
            $canAccessCourse = $courses->contains(fn ($c) => (int) $c->id === $selectedCourseId);
            if ($canAccessCourse) {
                $iloList = IntendedLearningOutcome::where('course_id', $selectedCourseId)
                    ->orderBy('position')
                    ->get();
            }
        }

        return view('admin.master-data.index', [
            'studentOutcomes'          => StudentOutcome::all(),
            'intendedLearningOutcomes' => $iloList,
            'programs'                 => $programs,
            'courses'                  => $courses,
        ]);
    }


    /**
 * AJAX: Return ILOs for a given course_id as JSON.
 * Plain-English: Verify the user can access the course, then return its ILOs ordered by position.
 */
public function fetchIlos(Request $request)
{
    $request->validate([
        'course_id' => 'required|integer|exists:courses,id',
    ]);

    $user = Auth::user();
    $courseId = (int) $request->course_id;

    // Determine accessible department IDs for the user (admin can see all)
    if ($user->role !== 'admin') {
        $deptIds = $user->appointments()
            ->active()
            ->whereIn('role', [\App\Models\Appointment::ROLE_DEPT, \App\Models\Appointment::ROLE_FACULTY])
            ->pluck('scope_id')
            ->values();

        $progIds = $user->appointments()
            ->active()
            ->where('role', \App\Models\Appointment::ROLE_PROG)
            ->pluck('scope_id')
            ->values();

        if ($progIds->isNotEmpty()) {
            $deptFromProg = \App\Models\Program::whereIn('id', $progIds)->pluck('department_id');
            $deptIds = $deptIds->merge($deptFromProg)->unique()->values();
        }

        // If user has no dept scope, deny
        if ($deptIds->isEmpty()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Ensure the requested course belongs to an accessible department
        $canAccess = \App\Models\Course::where('id', $courseId)
            ->whereIn('department_id', $deptIds)
            ->exists();

        if (!$canAccess) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
    }

    // Load ILOs
    $ilos = \App\Models\IntendedLearningOutcome::where('course_id', $courseId)
        ->orderBy('position')
        ->get(['id','code','description','position']);

    return response()->json([
        'ilos' => $ilos,
    ]);
}
}
