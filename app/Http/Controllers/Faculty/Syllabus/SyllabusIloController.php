<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/Faculty/Syllabus/SyllabusIloController.php
// * Description: Handles CRUD and sorting for syllabus-specific ILOs – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Regenerated to support inline updates, drag-reorder, add and delete.
// [2025-07-29] Updated reorder method to accept structured payload: id, position, code.
// [2025-07-29] Synced reorder() structure to match SO sortable logic.
// [2025-07-29] Refactored update() method to accept structured object array (id, code, description, position).
// -------------------------------------------------------------------------------


namespace App\Http\Controllers\Faculty\Syllabus;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Syllabus;
use App\Models\SyllabusIlo;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SyllabusIloController extends Controller
{
    // 📝 Updates all ILOs in batch with id, code, description, position
    public function update(Request $request, $syllabusId)
    {
        // Resolve currently authenticated faculty user id explicitly via faculty guard
        $facultyId = null;
        try { $facultyId = Auth::guard('faculty')->id(); } catch (\Throwable $e) { /* guard not available */ }
        // Fallback to default Auth::id() if faculty guard returns null
        if (!$facultyId) { $facultyId = Auth::id(); }

        // Attempt scoped lookup first; if it fails, fallback to direct id (logged for diagnostics)
        $syllabus = Syllabus::where('faculty_id', $facultyId)->where('id', $syllabusId)->first();
        if (!$syllabus) {
            \Log::warning('SyllabusIloController.update: scoped syllabus not found, attempting unscoped fallback', [
                'syllabus_id' => $syllabusId,
                'faculty_id_attempt' => $facultyId,
                'auth_default_id' => Auth::id(),
            ]);
            $syllabus = Syllabus::findOrFail($syllabusId);
        }

        // Debug: log incoming payload & resolved syllabus
        try {
            \Log::info('SyllabusIloController.update: invoked', [
                'syllabus_id' => $syllabusId,
                'resolved_faculty_id' => $facultyId,
                'payload_keys' => array_keys($request->all()),
            ]);
            $incomingPreview = $request->input('ilos');
            if (is_array($incomingPreview)) {
                \Log::debug('SyllabusIloController.update: incoming ilos summary', [
                    'count' => count($incomingPreview),
                    'first' => $incomingPreview[0] ?? null,
                ]);
            }
        } catch (\Throwable $e) { /* ignore logging errors */ }

        $request->validate([
            'ilos' => 'required|array',
            'ilos.*.id' => 'nullable|integer|exists:syllabus_ilos,id',
            'ilos.*.code' => 'required|string',
            // allow nullable description so empty/placeholder ILO rows can be created client-side
            'ilos.*.description' => 'nullable|string|max:1000',
            'ilos.*.position' => 'required|integer',
        ]);

        $incomingIds = collect($request->ilos)->pluck('id')->filter();
        $existingIds = SyllabusIlo::where('syllabus_id', $syllabus->id)->pluck('id');

        // 🔥 Determine deletions
        $toDelete = $existingIds->diff($incomingIds);
        if ($toDelete->count()) {
            \Log::debug('SyllabusIloController.update: deleting removed ILOs', [ 'delete_ids' => $toDelete->values() ]);
            SyllabusIlo::whereIn('id', $toDelete)->delete();
        } else {
            \Log::debug('SyllabusIloController.update: no deletions');
        }

        // Prepare tracking arrays for logging
        $createdIds = [];
        $updatedIds = [];
        
        
        
        
        \DB::transaction(function() use ($request, $syllabus, &$createdIds, &$updatedIds) {
            foreach ($request->ilos as $iloData) {
                $attrs = [
                    'syllabus_id' => $syllabus->id,
                    'code' => $iloData['code'],
                    'description' => $iloData['description'] ?? '',
                    'position' => $iloData['position'],
                ];

                if (!empty($iloData['id'])) {
                    $affected = SyllabusIlo::where('id', $iloData['id'])->where('syllabus_id', $syllabus->id)->update($attrs);
                    if ($affected) { $updatedIds[] = (int)$iloData['id']; }
                } else {
                    // create new
                    $new = SyllabusIlo::create($attrs);
                    if ($new) $createdIds[] = $new->id;
                }
            }
        });

        try {
            \Log::info('SyllabusIloController.update: upsert summary', [
                'created_count' => count($createdIds),
                'updated_count' => count($updatedIds),
                'created_ids' => $createdIds,
                'updated_ids' => $updatedIds,
            ]);
        } catch (\Throwable $e) { /* ignore logging errors */ }
        return response()->json([
            'success' => true,
            'message' => 'ILOs updated successfully.',
            'created_ids' => $createdIds,
            'updated_ids' => $updatedIds,
            'deleted_ids' => $toDelete->values(),
        ]);
    }

    /**
     * Seed syllabus-level ILOs from the associated course definition.
     */
    public function seedFromCourse(Syllabus $syllabus, ?Course $course): void
    {
        if (! $course) {
            return;
        }

        if (! method_exists($course, 'ilos')) {
            try {
                \Log::warning('SyllabusIloController.seedFromCourse: course has no ilos relation', [
                    'course_id' => $course->id ?? null,
                    'syllabus_id' => $syllabus->id,
                ]);
            } catch (\Throwable $e) { /* noop */ }
            return;
        }

        $ilos = $course->relationLoaded('ilos') ? $course->ilos : $course->ilos()->orderBy('position')->get();

        if (! $ilos || $ilos->isEmpty()) {
            try {
                \Log::info('SyllabusIloController.seedFromCourse: no course ILOs found', [
                    'course_id' => $course->id ?? null,
                    'syllabus_id' => $syllabus->id,
                ]);
            } catch (\Throwable $e) { /* noop */ }
            return;
        }

        SyllabusIlo::where('syllabus_id', $syllabus->id)->delete();

        foreach ($ilos as $index => $ilo) {
            $code = trim((string) ($ilo->code ?? ''));
            if ($code === '') {
                $code = 'ILO' . ($index + 1);
            }

            $description = (string) ($ilo->description ?? '');
            $position = isset($ilo->position) && is_numeric($ilo->position)
                ? (int) $ilo->position
                : ($index + 1);

            try {
                SyllabusIlo::create([
                    'syllabus_id' => $syllabus->id,
                    'code' => $code,
                    'description' => $description,
                    'position' => $position,
                ]);
            } catch (\Throwable $e) {
                try {
                    \Log::warning('SyllabusIloController.seedFromCourse: failed to create syllabus ILO', [
                        'syllabus_id' => $syllabus->id,
                        'code' => $code,
                        'error' => $e->getMessage(),
                    ]);
                } catch (\Throwable $ignored) { /* noop */ }
            }
        }
    }

    /**
     * Persist ILOs when the main syllabus update payload includes a structured `ilos` array.
     */
    public function syncFromRequest(Request $request, Syllabus $syllabus): void
    {
        if (! $request->has('ilos')) {
            return;
        }

        $raw = $request->input('ilos');
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($raw)) {
            try {
                \Log::warning('SyllabusIloController.syncFromRequest: invalid payload type', [
                    'syllabus_id' => $syllabus->id,
                    'payload_type' => gettype($raw),
                ]);
            } catch (\Throwable $e) { /* noop */ }
            return;
        }

        $rows = collect($raw)
            ->filter(fn($row) => is_array($row))
            ->map(function ($row, $index) {
                $code = trim((string) ($row['code'] ?? ''));
                if ($code === '') {
                    $code = 'ILO' . ($index + 1);
                }

                $description = (string) ($row['description'] ?? '');
                $position = isset($row['position']) && is_numeric($row['position'])
                    ? (int) $row['position']
                    : ($index + 1);

                return [
                    'id' => isset($row['id']) ? (int) $row['id'] : null,
                    'code' => $code,
                    'description' => $description,
                    'position' => $position,
                ];
            })
            ->values();

        $incomingIds = $rows->pluck('id')->filter();
        $existingIds = SyllabusIlo::where('syllabus_id', $syllabus->id)->pluck('id');
        $toDelete = $existingIds->diff($incomingIds);

        if ($toDelete->count()) {
            SyllabusIlo::whereIn('id', $toDelete)->delete();
        }

        $createdIds = [];
        $updatedIds = [];

        DB::transaction(function () use ($rows, $syllabus, &$createdIds, &$updatedIds) {
            foreach ($rows as $row) {
                $attributes = [
                    'syllabus_id' => $syllabus->id,
                    'code' => $row['code'],
                    'description' => $row['description'],
                    'position' => $row['position'],
                ];

                if (!empty($row['id'])) {
                    $affected = SyllabusIlo::where('id', $row['id'])
                        ->where('syllabus_id', $syllabus->id)
                        ->update($attributes);
                    if ($affected) {
                        $updatedIds[] = (int) $row['id'];
                    }
                } else {
                    $new = SyllabusIlo::create($attributes);
                    if ($new) {
                        $createdIds[] = $new->id;
                    }
                }
            }
        });

        try {
            \Log::info('SyllabusIloController.syncFromRequest: applied changes', [
                'syllabus_id' => $syllabus->id,
                'created_count' => count($createdIds),
                'updated_count' => count($updatedIds),
                'deleted_count' => count($toDelete),
                'created_ids' => $createdIds,
                'updated_ids' => $updatedIds,
                'deleted_ids' => $toDelete->values(),
            ]);
        } catch (\Throwable $e) { /* noop */ }
    }

    // ➕ Adds a new ILO
    public function store(Request $request)
    {
        $request->validate([
            'syllabus_id' => 'required|exists:syllabi,id',
            'description' => 'nullable|string|max:1000'
        ]);

        try { \Log::info('SyllabusIloController.store: incoming', ['syllabus_id' => $request->syllabus_id, 'description_len' => strlen($request->description ?? '')]); } catch (\Throwable $e) { /* noop */ }

        $max = SyllabusIlo::where('syllabus_id', $request->syllabus_id)->max('position');

        $ilo = SyllabusIlo::create([
            'syllabus_id' => $request->syllabus_id,
            'code' => 'ILO' . (($max ?? 0) + 1),
            'description' => $request->description,
            'position' => ($max ?? 0) + 1
        ]);

        try { \Log::info('SyllabusIloController.store: created', ['id' => $ilo->id, 'code' => $ilo->code]); } catch (\Throwable $e) { /* noop */ }

        return response()->json(['message' => 'ILO added.', 'id' => $ilo->id, 'code' => $ilo->code]);
    }

    // ✏️ Updates only the description (used for inline update)
    public function inlineUpdate(Request $request, $syllabusId, $iloId)
    {
        $request->validate([
            'description' => 'nullable|string|max:1000',
        ]);

        $ilo = SyllabusIlo::where('syllabus_id', $syllabusId)->findOrFail($iloId);
        $ilo->update(['description' => $request->description]);

        return back()->with('success', 'ILO updated.');
    }

    // ❌ Deletes one ILO and reorders remaining codes/positions
    public function destroy(Request $request, $id)
    {
        $facultyId = null;
        try { $facultyId = Auth::guard('faculty')->id(); } catch (\Throwable $e) { /* guard not available */ }
        if (! $facultyId) { $facultyId = Auth::id(); }

        $ilo = SyllabusIlo::with('syllabus')->findOrFail($id);

        if ($ilo->syllabus && $facultyId && $ilo->syllabus->faculty_id && (int) $ilo->syllabus->faculty_id !== (int) $facultyId) {
            abort(403, 'You are not allowed to delete this ILO.');
        }

        $syllabusId = $ilo->syllabus_id;
        $ilo->delete();

        $remaining = SyllabusIlo::where('syllabus_id', $syllabusId)
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        $payload = [];
        foreach ($remaining as $index => $row) {
            $newPosition = $index + 1;
            $row->position = $newPosition;
            $row->code = 'ILO' . $newPosition;
            if ($row->isDirty(['position', 'code'])) {
                $row->save();
            }
            $payload[] = [
                'id' => $row->id,
                'code' => $row->code,
                'position' => $row->position,
            ];
        }

        try {
            \Log::info('SyllabusIloController.destroy: deleted ILO and resequenced', [
                'deleted_id' => $id,
                'syllabus_id' => $syllabusId,
                'remaining_count' => count($payload),
            ]);
        } catch (\Throwable $e) { /* noop */ }

        return response()->json([
            'message' => 'ILO deleted.',
            'ilos' => $payload,
        ]);
    }

    // 🔁 Reorder endpoint (optional if you split order from save)
    public function reorder(Request $request)
    {
        $request->validate([
            'positions' => 'required|array',
            'positions.*.id' => 'required|integer|exists:syllabus_ilos,id',
            'positions.*.code' => 'required|string',
            'positions.*.position' => 'required|integer',
            'syllabus_id' => 'required|exists:syllabi,id'
        ]);

        foreach ($request->positions as $item) {
            SyllabusIlo::where('id', $item['id'])
                ->where('syllabus_id', $request->syllabus_id)
                ->update([
                    'code' => $item['code'],
                    'position' => $item['position']
                ]);
        }

        return response()->json(['message' => 'ILO order updated successfully.']);
    }

    // 📥 Load predefined ILOs from master data (replaces existing ILOs)
    public function loadPredefinedIlos(Request $request, $syllabusId)
    {
        $syllabus = Syllabus::where('faculty_id', Auth::id())->findOrFail($syllabusId);

        if (!$syllabus->course_id) {
            return response()->json(['message' => 'No course associated with this syllabus.'], 400);
        }

        // Get predefined ILOs for this course
        $predefinedIlos = \App\Models\IntendedLearningOutcome::where('course_id', $syllabus->course_id)
            ->orderBy('position')
            ->get();

        if ($predefinedIlos->isEmpty()) {
            return response()->json(['message' => 'No predefined ILOs found for this course.'], 404);
        }

        // Delete existing ILOs for this syllabus
        SyllabusIlo::where('syllabus_id', $syllabus->id)->delete();

        // Create new ILOs from predefined data
        $newIlos = [];
        foreach ($predefinedIlos as $index => $predefined) {
            $ilo = SyllabusIlo::create([
                'syllabus_id' => $syllabus->id,
                'code' => 'ILO' . ($index + 1),
                'description' => $predefined->description,
                'position' => $index + 1,
            ]);
            $newIlos[] = [
                'id' => $ilo->id,
                'code' => $ilo->code,
                'description' => $ilo->description,
                'position' => $ilo->position,
            ];
        }

        return response()->json([
            'message' => 'Predefined ILOs loaded successfully.',
            'ilos' => $newIlos,
        ]);
    }
}
