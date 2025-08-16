<?php

// -----------------------------------------------------------------------------
// * File: app/Http/Controllers/Admin/IntendedLearningOutcomeController.php
// * Description: CRUD + Reorder (AJAX) for ILOs – scoped by appointments (Dept/Prog/Admin)
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-08-18] Initial creation – store/update/destroy with JSON-first responses.
// [2025-08-18] Added reorder() – Save Order endpoint; temp-code pass to avoid conflicts.
// -----------------------------------------------------------------------------

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Course;
use App\Models\IntendedLearningOutcome as ILO;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IntendedLearningOutcomeController extends Controller
{
    /**
     * Small helper: decide if the client expects JSON (AJAX).
     * This keeps redirects working for non-AJAX fallbacks.
     */
    protected function wantsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->wantsJson() || $request->ajax();
    }

    /**
     * Check if current user may manage ILOs for a given course (by department scope).
     * Admin can manage all; chairs/faculty can manage within their department/program scope.
     */
    protected function canManageCourse(int $courseId): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        if ($user->role === 'admin') return true;

        $course = Course::select('department_id')->findOrFail($courseId);

        // Department Chair or Faculty appointments → department scope_id
        $deptIds = $user->appointments()
            ->active()
            ->whereIn('role', [Appointment::ROLE_DEPT, Appointment::ROLE_FACULTY])
            ->pluck('scope_id');

        // Program Chair appointments → infer department via program
        $progIds = $user->appointments()
            ->active()
            ->where('role', Appointment::ROLE_PROG)
            ->pluck('scope_id');

        if ($progIds->isNotEmpty()) {
            $deptFromProg = Program::whereIn('id', $progIds)->pluck('department_id');
            $deptIds = $deptIds->merge($deptFromProg)->unique();
        }

        return $deptIds->contains($course->department_id);
    }

    /**
     * Store a new ILO for a course.
     * Plain-English: validates inputs, auto-assigns next code/position, returns JSON.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'course_id'   => 'required|exists:courses,id',
            'description' => 'required|string',
        ]);

        if (!$this->canManageCourse((int) $data['course_id'])) {
            return $this->wantsJson($request)
                ? response()->json(['message' => 'Forbidden'], 403)
                : abort(403, 'Forbidden');
        }

        $count    = (int) ILO::where('course_id', $data['course_id'])->count();
        $position = $count + 1;
        $code     = 'ILO' . $position;

        $ilo = ILO::create([
            'course_id'   => $data['course_id'],
            'description' => $data['description'],
            'position'    => $position,
            'code'        => $code,
        ]);

        if ($this->wantsJson($request)) {
            return response()->json([
                'message' => "ILO '{$code}' added.",
                'ilo'     => $ilo,
            ], 201);
        }

        return redirect()->route('admin.master-data.index', [
            'tab'       => 'soilo',
            'subtab'    => 'ilo',
            'course_id' => $data['course_id'],
        ])->with('success', "ILO '{$code}' added.");
    }

    /**
     * Update ILO description.
     * Plain-English: code/position are maintained by the Save Order feature.
     */
    public function update(Request $request, int $id)
    {
        $ilo = ILO::findOrFail($id);

        if (!$this->canManageCourse((int) $ilo->course_id)) {
            return $this->wantsJson($request)
                ? response()->json(['message' => 'Forbidden'], 403)
                : abort(403, 'Forbidden');
        }

        $data = $request->validate([
            'description' => 'required|string',
        ]);

        $ilo->update(['description' => $data['description']]);

        if ($this->wantsJson($request)) {
            return response()->json([
                'message' => 'ILO updated.',
                'ilo'     => $ilo,
            ]);
        }

        return redirect()->route('admin.master-data.index', [
            'tab'       => 'soilo',
            'subtab'    => 'ilo',
            'course_id' => $ilo->course_id,
        ])->with('success', 'ILO updated.');
    }

    /**
     * Destroy an ILO.
     * Plain-English: deletes the row and returns JSON for the UI to refresh.
     */
    public function destroy(Request $request, int $id)
    {
        $ilo = ILO::findOrFail($id);

        if (!$this->canManageCourse((int) $ilo->course_id)) {
            return $this->wantsJson($request)
                ? response()->json(['message' => 'Forbidden'], 403)
                : abort(403, 'Forbidden');
        }

        $ilo->delete();

        if ($this->wantsJson($request)) {
            return response()->json([
                'message' => 'ILO deleted.',
                'id'      => $id,
            ]);
        }

        return redirect()->route('admin.master-data.index', [
            'tab'       => 'soilo',
            'subtab'    => 'ilo',
            'course_id' => $ilo->course_id,
        ])->with('success', 'ILO deleted.');
    }

    /**
     * Reorder ILOs within a course based on the provided ID order.
     * Plain-English: verifies all IDs belong to the course, temporarily assigns
     * placeholder codes to avoid conflicts, then writes new position+code: ILO1..n.
     */
    public function reorder(Request $request)
    {
        $payload = $request->validate([
            'ids'       => 'required|array|min:1',
            'ids.*'     => 'integer',
            'course_id' => 'required|exists:courses,id',
        ]);

        if (!$this->canManageCourse((int) $payload['course_id'])) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Load current ILOs for the course, keyed by id.
        $ilos = ILO::where('course_id', $payload['course_id'])
            ->orderBy('position')
            ->get()
            ->keyBy('id');

        // Validate: all provided ids must exist under the same course
        $ids = array_map('intval', $payload['ids']);
        if (count($ids) !== $ilos->count() || collect($ids)->diff($ilos->keys())->isNotEmpty()) {
            return response()->json([
                'message' => 'Invalid ILO set for this course.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Step 1: Assign temporary codes to avoid (rare) unique conflicts
            foreach ($ids as $id) {
                /** @var ILO $ilo */
                $ilo = $ilos->get($id);
                $ilo->forceFill(['code' => '__TEMP__' . $ilo->id])->save();
            }

            // Step 2: Reassign position and final codes in the new order
            foreach ($ids as $index => $id) {
                /** @var ILO $ilo */
                $ilo = $ilos->get($id);
                $ilo->forceFill([
                    'position' => $index + 1,
                    'code'     => 'ILO' . ($index + 1),
                ])->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'ILO order updated successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('ILO reorder failed: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Server error updating ILO order.',
            ], 500);
        }
    }
}
