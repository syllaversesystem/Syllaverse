<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/SuperAdmin/MasterDataController.php
// * Description: CRUD + reorder for Master Data (SDG, IGA, CDIO, General Info) + Assessment Tasks (LEC/LAB)
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-12] Order-aware index (uses ->ordered()); added `reorder()` endpoint (drag & drop).
// [2025-08-12] Auto-assign `sort_order` and `code` on create; resequence codes after delete/reorder.
// [2025-08-12] JSON-aware responses for fetch/AJAX, with redirect+flash fallback.
// [2025-08-12] Fix – `reorder()` now accepts either {ids:[...]} or {order:[...]}; relaxed validator to numeric.
// [2025-08-12] Fix – Reorder now uses two-phase update (vacate codes → assign final codes) to avoid unique collisions.
// [2025-08-17] Add – Assessment Tasks (LEC/LAB) CRUD without description; early bypass in store/update/destroy.
// [2025-08-17] Change – No reorder for Assessment Tasks; index now also provides $taskGroups for Assessment Tasks tab.
// -------------------------------------------------------------------------------

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Models\Sdg;
use App\Models\Iga;
use App\Models\Cdio;
use App\Models\GeneralInformation;
// Assessment Task master data is optional in some deployments; avoid querying missing tables

class MasterDataController extends Controller
{
    // ░░░ START: Index ░░░
    /** Show Master Data lists in display order (sort_order, then id). Also loads Assessment Task groups. */
    public function index()
    {
        return view('superadmin.master-data.index', [
            'sdgs'        => Sdg::ordered()->get(),
            'igas'        => Iga::ordered()->get(),
            'cdios'       => Cdio::ordered()->get(),
            'info'        => GeneralInformation::all()->keyBy('section'),
            // Assessment Tasks tab disabled in this deployment (table may not exist)
        ]);
    }
    // ░░░ END: Index ░░░


    // ░░░ START: Store ░░░
    /** Create a new item; Assessment Tasks bypass generic validation. */
    public function store(Request $request, string $type): JsonResponse|RedirectResponse
    {
        // ✅ Bypass for Assessment Tasks (no description field)
        if ($type === 'assessment-task') {
            return $this->createAssessmentTask($request);
        }

        // SDG/IGA/CDIO – keep generic validation
        $request->validate([
            'title'       => 'nullable|string|max:255',
            'description' => 'required|string',
        ]);

        $cls = $this->modelClass($type);
        if (!$cls) abort(404);

        $data = ['description' => $request->description];
        if (in_array($type, ['sdg', 'iga', 'cdio'], true)) {
            $data['title'] = $request->title;
        }

        /** @var \Illuminate\Database\Eloquent\Model $item */
        $item = $cls::create($data);

        return $this->respond($request, true, strtoupper($type) . ' added successfully!', 200, null, [
            'id'         => $item->id,
            'code'       => $item->code ?? null,
            'sort_order' => (int) ($item->sort_order ?? 0),
        ]);
    }
    // ░░░ END: Store ░░░


    // ░░░ START: Update ░░░
    /** Update an item’s text fields; Assessment Tasks bypass generic validation. */
    public function update(Request $request, string $type, int $id): JsonResponse|RedirectResponse
    {
        // ✅ Bypass for Assessment Tasks
        if ($type === 'assessment-task') {
            return $this->updateAssessmentTask($request, $id);
        }

        // SDG/IGA/CDIO – keep generic validation
        $request->validate([
            'title'       => 'nullable|string|max:255',
            'description' => 'required|string',
        ]);

        $cls = $this->modelClass($type);
        if (!$cls) abort(404);

        /** @var \Illuminate\Database\Eloquent\Model $item */
        $item = $cls::findOrFail($id);

        $payload = ['description' => $request->description];
        if (in_array($type, ['sdg', 'iga', 'cdio'], true)) {
            $payload['title'] = $request->title;
        }

        $item->update($payload);

        return $this->respond($request, true, strtoupper($type) . ' updated successfully!', 200, null, [
            'id'         => $item->id,
            'code'       => $item->code ?? null,
            'sort_order' => (int) ($item->sort_order ?? 0),
        ]);
    }
    // ░░░ END: Update ░░░


    // ░░░ START: Destroy ░░░
    /** Delete and resequence remaining items so codes stay contiguous (…1, …2, …3…). */
    public function destroy(string $type, int $id): JsonResponse|RedirectResponse
    {
        // ✅ Bypass for Assessment Tasks
        if ($type === 'assessment-task') {
            return $this->destroyAssessmentTask($id);
        }

        $cls = $this->modelClass($type);
        if (!$cls) abort(404);

        /** @var \Illuminate\Database\Eloquent\Model $item */
        $item = $cls::findOrFail($id);
        $item->delete();

        $this->resequenceAll($type);

        return $this->respond($request = request(), true, strtoupper($type) . ' deleted successfully!', 200);
    }
    // ░░░ END: Destroy ░░░


    // ░░░ START: Reorder (SDG/IGA/CDIO only) ░░░
    /**
     * Persist a new order (1-based) based on an array of IDs.
     * Why two-phase? Codes (e.g., SDG1) are UNIQUE; if we assign final codes row-by-row,
     * swaps can collide (Duplicate entry 'SDG1'). We avoid this by:
     *  1) Writing the new sort_order for all rows (requested order first, then the rest),
     *  2) Temporarily vacating ALL codes to unique non-conflicting values (TMP_{id}),
     *  3) Assigning final codes as PREFIX + sort_order in one pass.
     */
    public function reorder(Request $request, string $type): JsonResponse
    {
        // Assessment Tasks do not support drag/reorder here
        if ($type === 'assessment-task') {
            return response()->json(['ok' => false, 'message' => 'Reorder not supported for Assessment Tasks.'], 404);
        }

        // Accept either "ids" or legacy "order"
        $incoming = $request->input('ids') ?? $request->input('order');
        $request->merge(['ids' => $incoming]);

        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'numeric',
        ]);

        $cls = $this->modelClass($type);
        if (!$cls) abort(404);

        $ids = array_values(array_unique(array_map('intval', $request->input('ids', []))));
        $existing = $cls::whereIn('id', $ids)->pluck('id')->all();

        if (count($existing) !== count($ids)) {
            return response()->json([
                'ok'      => false,
                'message' => 'Some items were not found for this type.',
            ], 422);
        }

        $prefix = $this->prefixForType($type);

        DB::transaction(function () use ($cls, $ids, $prefix) {
            // A) Apply new sort_order to provided IDs (1..N)
            foreach ($ids as $idx => $id) {
                $cls::where('id', $id)->update(['sort_order' => $idx + 1]);
            }

            // B) Append any rows not included in payload (N+1..M)
            $pos  = count($ids);
            $rest = $cls::whereNotIn('id', $ids)->ordered()->get(['id']);
            foreach ($rest as $row) {
                $cls::where('id', $row->id)->update(['sort_order' => ++$pos]);
            }

            // C) Vacate all codes to a guaranteed-unique temp so we don't hit UNIQUE collisions
            $cls::query()->update(['code' => DB::raw("CONCAT('TMP_', id)")]);

            // D) Assign final codes based on the new sort_order
            $cls::query()->update(['code' => DB::raw("CONCAT('{$prefix}', sort_order)")]);
        });

        // Return fresh mapping so UI can sync without a full reload
        $items = $cls::ordered()
            ->get(['id', 'code', 'sort_order'])
            ->map(fn ($r) => ['id' => (int) $r->id, 'code' => $r->code, 'sort_order' => (int) $r->sort_order]);

        return response()->json([
            'ok'      => true,
            'message' => strtoupper($type) . ' order saved.',
            'items'   => $items,
        ]);
    }
    // ░░░ END: Reorder (SDG/IGA/CDIO only) ░░░


    // ░░░ START: General Information ░░░
    /** Upsert a General Information section’s content by its key. */
    public function updateGeneralInfo(Request $request, string $section): JsonResponse|RedirectResponse
    {
        $request->validate([
            $section => 'required|string',
        ]);

        GeneralInformation::updateOrCreate(
            ['section' => $section],
            ['content' => $request->input($section)]
        );

        return $this->respond($request, true, ucfirst($section) . ' updated successfully.', 200);
    }
    // ░░░ END: General Information ░░░


    // ░░░ START: Helpers (generic) ░░░
    /** Resolve Eloquent model class from type slug. */
    private function modelClass(string $type): ?string
    {
        return [
            'sdg'  => Sdg::class,
            'iga'  => Iga::class,
            'cdio' => Cdio::class,
        ][$type] ?? null;
    }

    /** Get the code prefix for a type (e.g., 'SDG'). */
    private function prefixForType(string $type): string
    {
        return match ($type) {
            'sdg'   => Sdg::codePrefix(),
            'iga'   => Iga::codePrefix(),
            'cdio'  => Cdio::codePrefix(),
            default => '',
        };
    }

    /** Rebuild sort_order and code from 1..N in current order (used after deletes). */
    private function resequenceAll(string $type): void
    {
        $cls = $this->modelClass($type);
        if (!$cls) return;

        $prefix = $this->prefixForType($type);

        DB::transaction(function () use ($cls, $prefix) {
            $rows = $cls::ordered()->get(['id']);
            $pos  = 1;
            foreach ($rows as $row) {
                $cls::where('id', $row->id)->update([
                    'sort_order' => $pos,
                    'code'       => $prefix . $pos,
                ]);
                $pos++;
            }
        });
    }

    /**
     * Smart JSON/redirect response:
     * - If the request expects JSON (fetch/AJAX), return JSON.
     * - Otherwise, redirect back with a flash message.
     */
    private function respond(
        Request $request,
        bool $ok,
        string $message,
        int $status = 200,
        array $errors = null,
        array $extra = []
    ): JsonResponse|RedirectResponse {
        $wantsJson = $request->expectsJson()
            || $request->wantsJson()
            || $request->ajax()
            || str_contains(strtolower($request->header('accept', '')), 'application/json');

        if ($wantsJson) {
            $payload = array_merge(['ok' => $ok, 'message' => $message], $extra);
            if ($errors !== null) $payload['errors'] = $errors;
            return response()->json($payload, $status);
        }

        if ($status === 422 && $errors) {
            return back()->withErrors($errors)->withInput();
        }

        return $ok ? back()->with('success', $message) : back()->with('error', $message);
    }
    // ░░░ END: Helpers (generic) ░░░


    // ░░░ START: Assessment Task – Helpers (no description, no reorder) ░░░

    /**
     * This creates a new Assessment Task (e.g., ME, FE) under a specific group (LEC/LAB).
     * We auto-place it at the end by assigning the next sort_order within that group.
     */
    private function createAssessmentTask(Request $request): JsonResponse|RedirectResponse
    {
        // Guard: if AssessmentTaskGroup or AssessmentTask tables are missing, return a friendly error
        if (!\Schema::hasTable('assessment_task_groups') || !\Schema::hasTable('assessment_tasks')) {
            return $this->respond($request, false, 'Assessment Tasks are not enabled in this deployment.', 404);
        }

        $validated = $this->validateAssessmentTask($request, updating: false);

        // Place at end of the group
        $last = AssessmentTask::where('group_id', $validated['group_id'])->max('sort_order');
        $sort = is_null($last) ? 1 : ($last + 1);

        $task = AssessmentTask::create([
            'group_id'   => $validated['group_id'],
            'code'       => $validated['code'],
            'title'      => $validated['title'],
            'sort_order' => $sort,
            'is_active'  => true,
        ]);

        return $this->respond(
            $request,
            true,
            'Assessment task added successfully!',
            200,
            null,
            [
                'id'         => $task->id,
                'group_id'   => (int) $task->group_id,
                'code'       => $task->code,
                'title'      => $task->title,
                'sort_order' => (int) $task->sort_order,
            ]
        );
    }

    /**
     * Small helper that validates payload for create/update of Assessment Tasks.
     * On update, we ignore the current row for the (group_id, code) uniqueness.
     */
    private function validateAssessmentTask(Request $request, bool $updating, ?int $taskId = null): array
    {
        // Guard: if tables missing, throw a validation exception to short-circuit
        if (!\Schema::hasTable('assessment_task_groups') || !\Schema::hasTable('assessment_tasks')) {
            abort(404, 'Assessment Tasks not available');
        }

        $groupId = (int) $request->input('group_id');

        // Unique 'code' scoped to group_id
        $uniqueCode = Rule::unique('assessment_tasks', 'code')
            ->where(fn ($q) => $q->where('group_id', $groupId));

        if ($updating && $taskId) {
            $uniqueCode = $uniqueCode->ignore($taskId);
        }

        $rules = [
            'group_id' => ['required', 'integer', Rule::exists('assessment_task_groups', 'id')],
            'code'     => ['required', 'string', 'max:16', $uniqueCode],
            'title'    => ['required', 'string', 'max:150'],
        ];

        $validated = $request->validate($rules);

        // Ensure group exists
    AssessmentTaskGroup::findOrFail($validated['group_id']);

        return $validated;
    }

    /**
     * This updates the code/title of an Assessment Task.
     * If group_id changes, we move the task to the end of the new group.
     */
    private function updateAssessmentTask(Request $request, int $id): JsonResponse|RedirectResponse
    {
        // Guard
        if (!\Schema::hasTable('assessment_task_groups') || !\Schema::hasTable('assessment_tasks')) {
            return $this->respond($request, false, 'Assessment Tasks are not enabled in this deployment.', 404);
        }

        /** @var AssessmentTask $task */
        $task = AssessmentTask::findOrFail($id);

        $incomingGroupId = (int) $request->input('group_id', $task->group_id);
        $validated = $this->validateAssessmentTask($request, updating: true, taskId: $task->id);

        if ($task->group_id !== $incomingGroupId) {
            $last = AssessmentTask::where('group_id', $incomingGroupId)->max('sort_order');
            $task->group_id   = $incomingGroupId;
            $task->sort_order = is_null($last) ? 1 : ($last + 1);
        }

        $task->code  = $validated['code'];
        $task->title = $validated['title'];
        $task->save();

        return $this->respond(
            $request,
            true,
            'Assessment task updated successfully!',
            200,
            null,
            [
                'id'         => $task->id,
                'group_id'   => (int) $task->group_id,
                'code'       => $task->code,
                'title'      => $task->title,
                'sort_order' => (int) $task->sort_order,
            ]
        );
    }

    /**
     * This deletes an Assessment Task and re-sequences the remaining tasks in that group
     * so sort_order stays contiguous (1..N).
     */
    private function destroyAssessmentTask(int $id): JsonResponse|RedirectResponse
    {
        // Guard
        if (!\Schema::hasTable('assessment_task_groups') || !\Schema::hasTable('assessment_tasks')) {
            return $this->respond(request(), false, 'Assessment Tasks are not enabled in this deployment.', 404);
        }

        /** @var AssessmentTask $task */
        $task = AssessmentTask::findOrFail($id);
        $groupId = (int) $task->group_id;
        $deletedId = $task->id;

        $task->delete();
        $this->resequenceAssessmentTasks($groupId);

        return $this->respond(
            request(),
            true,
            'Assessment task deleted successfully.',
            200,
            null,
            ['id' => $deletedId]
        );
    }

    /** Resequence sort_order 1..N for every task in a group—used after deletes or moves. */
    private function resequenceAssessmentTasks(int $groupId): void
    {
        if (!\Schema::hasTable('assessment_task_groups') || !\Schema::hasTable('assessment_tasks')) return;

        $rows = AssessmentTask::where('group_id', $groupId)
            ->orderBy('sort_order')->orderBy('id')->get(['id']);

        $pos = 1;
        foreach ($rows as $row) {
            AssessmentTask::where('id', $row->id)->update(['sort_order' => $pos++]);
        }
    }
    // ░░░ END: Assessment Task – Helpers (no description, no reorder) ░░░
}
