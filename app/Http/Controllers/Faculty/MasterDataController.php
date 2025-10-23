<?php
// -----------------------------------------------------------------------------
// * File: app/Http/Controllers/Faculty/MasterDataController.php
// * Description: Handles Master Data page composition (SO, ILO, Programs, Courses)
//                – view-only loader scoped by the acting user's appointments.
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-01-20] Copied from Admin MasterDataController for Faculty module
// -----------------------------------------------------------------------------

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Course;
use App\Models\Department;
use App\Models\IntendedLearningOutcome;
use App\Models\Program;
use App\Models\StudentOutcome;
use App\Models\Sdg;
use App\Models\Iga;
use App\Models\Cdio;
use App\Models\GeneralInformation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

            return view('faculty.master-data.index', [
                'studentOutcomes'          => StudentOutcome::with('department')->get(),
                'intendedLearningOutcomes' => $iloList,
                'programs'                 => $programs,
                'courses'                  => $courses,
                'departments'              => Department::orderBy('code')->get(),
                'sdgs'                     => Sdg::ordered()->get(),
                'igas'                     => Iga::ordered()->get(),
                'cdios'                    => Cdio::ordered()->get(),
                'info'                     => GeneralInformation::all()->keyBy('section'),
                'currentUser'              => $user,
                'userPrimaryDepartmentId'  => $user->getPrimaryDepartmentId(),
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
            return view('faculty.master-data.index', [
                'studentOutcomes'          => StudentOutcome::all(),
                'intendedLearningOutcomes' => collect(),
                'programs'                 => collect(),
                'courses'                  => collect(),
                'departments'              => collect(),
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

        return view('faculty.master-data.index', [
            'studentOutcomes'          => StudentOutcome::with('department')->get(),
            'intendedLearningOutcomes' => $iloList,
            'programs'                 => $programs,
            'courses'                  => $courses,
            'departments'              => Department::whereIn('id', $deptIds)->orderBy('code')->get(),
            'sdgs'                     => Sdg::ordered()->get(),
            'igas'                     => Iga::ordered()->get(),
            'cdios'                    => Cdio::ordered()->get(),
            'info'                     => GeneralInformation::all()->keyBy('section'),
            'currentUser'              => $user,
            'userPrimaryDepartmentId'  => $user->getPrimaryDepartmentId(),
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
        ->get(['id','code','description','position','created_at']);

    return response()->json([
        'ilos' => $ilos,
    ]);
}

    // ░░░ START: SDG/CDIO/IGA CRUD Methods ░░░
    
    /** Create a new SDG/CDIO/IGA item */
    public function store(Request $request, string $type)
    {
        if (!in_array($type, ['sdg', 'iga', 'cdio'])) {
            return response()->json(['message' => 'Invalid type'], 400);
        }

        $modelClass = $this->getModelClass($type);
        $maxSortOrder = $modelClass::max('sort_order') ?? 0;
        $maxCode = $modelClass::max('code') ?? 0;

        $validated = $request->validate([
            'description' => 'required|string',
        ]);

        $item = $modelClass::create([
            'code' => $maxCode + 1,
            'description' => $validated['description'],
            'sort_order' => $maxSortOrder + 1,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'item' => $item]);
        }

        return redirect()
            ->route('faculty.master-data')
            ->with('success', ucfirst($type) . ' created successfully!');
    }

    /** Update an existing SDG/CDIO/IGA item */
    public function update(Request $request, string $type, int $id)
    {
        if (!in_array($type, ['sdg', 'iga', 'cdio'])) {
            return response()->json(['message' => 'Invalid type'], 400);
        }

        $modelClass = $this->getModelClass($type);
        $item = $modelClass::findOrFail($id);

        $validated = $request->validate([
            'description' => 'required|string',
        ]);

        $item->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'item' => $item]);
        }

        return redirect()
            ->route('faculty.master-data')
            ->with('success', ucfirst($type) . ' updated successfully!');
    }

    /** Delete an SDG/CDIO/IGA item */
    public function destroy(Request $request, string $type, int $id)
    {
        if (!in_array($type, ['sdg', 'iga', 'cdio'])) {
            return response()->json(['message' => 'Invalid type'], 400);
        }

        $modelClass = $this->getModelClass($type);
        $item = $modelClass::findOrFail($id);
        $item->delete();

        // Resequence codes and sort orders
        $this->resequenceCodes($type);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('faculty.master-data')
            ->with('success', ucfirst($type) . ' deleted successfully!');
    }

    /** Reorder SDG/CDIO/IGA items */
    public function reorder(Request $request, string $type)
    {
        if (!in_array($type, ['sdg', 'iga', 'cdio'])) {
            return response()->json(['message' => 'Invalid type'], 400);
        }

        $validated = $request->validate([
            'ids' => 'array',
            'ids.*' => 'numeric',
            'order' => 'array',
            'order.*' => 'numeric',
        ]);

        $ids = $validated['ids'] ?? $validated['order'] ?? [];
        $modelClass = $this->getModelClass($type);

        DB::transaction(function () use ($modelClass, $ids) {
            // Two-phase update to avoid unique constraint violations
            foreach ($ids as $index => $id) {
                $modelClass::where('id', $id)->update(['sort_order' => -($index + 1)]);
            }
            foreach ($ids as $index => $id) {
                $modelClass::where('id', $id)->update([
                    'sort_order' => $index + 1,
                    'code' => $index + 1,
                ]);
            }
        });

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('faculty.master-data')
            ->with('success', ucfirst($type) . ' order updated!');
    }

    /** Update general information */
    public function updateGeneralInfo(Request $request, string $section)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        GeneralInformation::updateOrCreate(
            ['section' => $section],
            ['content' => $validated['content']]
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('faculty.master-data')
            ->with('success', 'Information updated successfully!');
    }

    // ░░░ START: Helper Methods ░░░
    
    private function getModelClass(string $type): string
    {
        return match ($type) {
            'sdg' => Sdg::class,
            'iga' => Iga::class,
            'cdio' => Cdio::class,
            default => throw new \InvalidArgumentException("Invalid type: $type"),
        };
    }

    private function resequenceCodes(string $type): void
    {
        $modelClass = $this->getModelClass($type);
        $items = $modelClass::orderBy('sort_order')->get();

        foreach ($items as $index => $item) {
            $item->update([
                'code' => $index + 1,
                'sort_order' => $index + 1,
            ]);
        }
    }
    // ░░░ END: Helper Methods ░░░
    
    // ░░░ END: SDG/CDIO/IGA CRUD Methods ░░░
}