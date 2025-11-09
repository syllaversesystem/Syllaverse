<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\IntendedLearningOutcome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IloController extends Controller
{
    /**
     * Return ILOs for a given course (JSON).
     */
    public function filter(Request $request)
    {
        $courseId = $request->query('course_id');
        if (!$courseId) {
            return response()->json(['ilos' => []]);
        }
        try {
            $ilos = IntendedLearningOutcome::query()
                ->where('course_id', $courseId)
                ->orderBy('position')
                ->orderBy('id')
                ->get(['id', 'code', 'description']);

            return response()->json(['ilos' => $ilos]);
        } catch (\Throwable $e) {
            Log::error('ILO filter error', ['error' => $e->getMessage()]);
            return response()->json(['ilos' => []], 500);
        }
    }

    /**
     * Store a new ILO (master-data context).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id'    => ['required', 'integer', 'exists:courses,id'],
            'description'  => ['required', 'string', 'max:2000'],
            'code'         => ['nullable', 'string', 'max:64'],
        ]);

        try {
            // If code not supplied (should be auto), generate next sequential ILO# for this course
            if (empty($validated['code'])) {
                $maxNumeric = 0;
                $existingCodes = IntendedLearningOutcome::where('course_id', $validated['course_id'])->pluck('code');
                foreach ($existingCodes as $c) {
                    if (preg_match('/^ILO(\d+)$/i', $c, $m)) {
                        $n = (int)$m[1];
                        $maxNumeric = max($maxNumeric, $n);
                    }
                }
                $validated['code'] = 'ILO' . ($maxNumeric + 1);
            }

            // Ensure uniqueness of code within course
            $exists = IntendedLearningOutcome::where('course_id', $validated['course_id'])
                ->where('code', $validated['code'])
                ->exists();
            if ($exists) {
                return $this->errorResponse('Code already exists for this course.', 422);
            }

            // Determine next position (append at end)
            $nextPosition = (int)IntendedLearningOutcome::where('course_id', $validated['course_id'])->max('position');
            $validated['position'] = $nextPosition + 1;

            $ilo = IntendedLearningOutcome::create($validated);

            return $this->successResponse([
                'ilo' => $ilo->only(['id', 'code', 'description'])
            ], 201);
        } catch (\Throwable $e) {
            Log::error('ILO store error', ['error' => $e->getMessage()]);
            return $this->errorResponse('Failed to create ILO.', 500);
        }
    }

    /**
     * Update an existing ILO (description only for now).
     */
    public function update(Request $request, $id)
    {
        $ilo = IntendedLearningOutcome::find($id);
        if (!$ilo) {
            return $this->errorResponse('ILO not found.', 404);
        }
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:2000'],
        ]);
        try {
            $ilo->description = $validated['description'];
            $ilo->save();
            return $this->successResponse([
                'ilo' => $ilo->only(['id', 'code', 'description'])
            ], 200);
        } catch (\Throwable $e) {
            Log::error('ILO update error', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->errorResponse('Failed to update ILO.', 500);
        }
    }

    /**
     * Destroy an ILO.
     */
    public function destroy($id)
    {
        $ilo = IntendedLearningOutcome::find($id);
        if (!$ilo) {
            return $this->errorResponse('ILO not found.', 404);
        }
        try {
            $courseId = $ilo->course_id;
            \DB::transaction(function () use ($ilo, $courseId) {
                // Delete the ILO
                $ilo->delete();
                // Fetch remaining IDs ordered by current position then id
                $remaining = IntendedLearningOutcome::where('course_id', $courseId)
                    ->orderBy('position')
                    ->orderBy('id')
                    ->pluck('id');
                // Phase 1: assign temporary codes to avoid uniqueness conflicts
                foreach ($remaining as $rid) {
                    IntendedLearningOutcome::where('id', $rid)
                        ->where('course_id', $courseId)
                        ->update(['code' => 'TMP' . $rid]);
                }
                // Phase 2: assign new sequential positions & codes
                $pos = 1;
                foreach ($remaining as $rid) {
                    IntendedLearningOutcome::where('id', $rid)
                        ->where('course_id', $courseId)
                        ->update([
                            'position' => $pos,
                            'code' => 'ILO' . $pos,
                        ]);
                    $pos++;
                }
            });
            return $this->successResponse(['deleted' => true, 'renumbered' => true]);
        } catch (\Throwable $e) {
            Log::error('ILO destroy error', ['id' => $id, 'error' => $e->getMessage()]);
            return $this->errorResponse('Failed to delete ILO.', 500);
        }
    }

    /**
     * Reorder ILOs within a course (positions start at 1 sequentially).
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'order'     => ['required', 'array', 'min:1'],
            'order.*'   => ['integer', 'exists:intended_learning_outcomes,id'],
        ]);

        try {
            $providedIds = collect($validated['order'])->map(fn ($v) => (int) $v);
            // Verify all IDs belong to course and that the provided list is COMPLETE (no missing ILOs)
            $courseId = (int) $validated['course_id'];
            $courseCount = IntendedLearningOutcome::where('course_id', $courseId)->count();
            $ilos = IntendedLearningOutcome::where('course_id', $courseId)
                ->whereIn('id', $providedIds)
                ->get(['id', 'course_id']);
            if ($ilos->count() !== $providedIds->count()) {
                return $this->errorResponse('One or more ILOs do not belong to the specified course.', 422);
            }
            if ($courseCount !== $providedIds->count()) {
                // Do not renumber codes if order is incomplete to avoid duplicates
                return $this->errorResponse('Incomplete order list provided. Please reorder with the full list visible.', 422);
            }

            // Persist new order and sequential codes in a transaction.
            // Two-phase update to avoid potential unique(code) conflicts: set temp codes, then final codes.
            \DB::transaction(function () use ($providedIds, $courseId) {
                // Phase 1: assign temporary unique codes
                foreach ($providedIds as $id) {
                    IntendedLearningOutcome::where('id', $id)
                        ->where('course_id', $courseId)
                        ->update(['code' => 'TMP' . $id]);
                }
                // Phase 2: assign positions and final sequential codes
                $pos = 1;
                foreach ($providedIds as $id) {
                    IntendedLearningOutcome::where('id', $id)
                        ->where('course_id', $courseId)
                        ->update([
                            'position' => $pos,
                            'code' => 'ILO' . $pos,
                        ]);
                    $pos++;
                }
            });
            return $this->successResponse(['reordered' => true, 'updated_codes' => true]);
        } catch (\Throwable $e) {
            Log::error('ILO reorder error', ['error' => $e->getMessage()]);
            return $this->errorResponse('Failed to reorder ILOs.', 500);
        }
    }

    // --- Helper JSON responses (support form fallback) ---
    protected function successResponse(array $payload, int $status = 200)
    {
        if ($this->wantsJson()) {
            return response()->json(array_merge(['status' => 'ok'], $payload), $status);
        }
        return redirect()->back()->with('success', $payload['message'] ?? 'Operation successful');
    }

    protected function errorResponse(string $message, int $status)
    {
        if ($this->wantsJson()) {
            return response()->json(['status' => 'error', 'message' => $message], $status);
        }
        return redirect()->back()->withErrors(['ilo' => $message]);
    }

    protected function wantsJson(): bool
    {
        $request = request();
        return $request->expectsJson() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';
    }
}
