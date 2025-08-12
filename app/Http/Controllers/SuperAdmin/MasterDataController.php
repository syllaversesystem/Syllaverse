<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/SuperAdmin/MasterDataController.php
// * Description: CRUD + reorder for Master Data (SDG, IGA, CDIO, General Info) – auto code & sort_order
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-12] Order-aware index (uses ->ordered()); added `reorder()` endpoint (drag & drop).
// [2025-08-12] Auto-assign `sort_order` and `code` on create; resequence codes after delete/reorder.
// [2025-08-12] JSON-aware responses for fetch/AJAX, with redirect+flash fallback.
// [2025-08-12] Fix – `reorder()` now accepts either {ids:[...]} or {order:[...]}; relaxed validator to numeric.
// [2025-08-12] Fix – Reorder now uses two-phase update (vacate codes → assign final codes) to avoid unique collisions.
// -------------------------------------------------------------------------------

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

use App\Models\Sdg;
use App\Models\Iga;
use App\Models\Cdio;
use App\Models\GeneralInformation;

class MasterDataController extends Controller
{
    // ░░░ START: Index ░░░
    /** Show Master Data lists in display order (sort_order, then id). */
    public function index()
    {
        return view('superadmin.master-data.index', [
            'sdgs'  => Sdg::ordered()->get(),
            'igas'  => Iga::ordered()->get(),
            'cdios' => Cdio::ordered()->get(),
            'info'  => GeneralInformation::all()->keyBy('section'),
        ]);
    }
    // ░░░ END: Index ░░░

    // ░░░ START: Store ░░░
    /** Create a new item for the given type; sort_order and code are auto-derived. */
    public function store(Request $request, string $type): JsonResponse|RedirectResponse
    {
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
    /** Update an item’s text fields; codes remain tied to sort_order. */
    public function update(Request $request, string $type, int $id): JsonResponse|RedirectResponse
    {
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
        $cls = $this->modelClass($type);
        if (!$cls) abort(404);

        /** @var \Illuminate\Database\Eloquent\Model $item */
        $item = $cls::findOrFail($id);
        $item->delete();

        $this->resequenceAll($type);

        return $this->respond($request = request(), true, strtoupper($type) . ' deleted successfully!', 200);
    }
    // ░░░ END: Destroy ░░░

    // ░░░ START: Reorder ░░░
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

            // Append any rows not included in payload (N+1..M) in current order
            $pos  = count($ids);
            $rest = $cls::whereNotIn('id', $ids)->ordered()->get(['id']);
            foreach ($rest as $row) {
                $cls::where('id', $row->id)->update(['sort_order' => ++$pos]);
            }

            // B) Vacate all codes to a guaranteed-unique temp so we don't hit UNIQUE collisions
            $cls::query()->update(['code' => DB::raw("CONCAT('TMP_', id)")]);

            // C) Assign final codes based on the new sort_order
            $cls::query()->update(['code' => DB::raw("CONCAT('{$prefix}', sort_order)")]);
        });

        // Return fresh mapping so UI can sync without a full reload
        $items = $cls::ordered()
            ->get(['id', 'code', 'sort_order'])
            ->map(fn($r) => ['id' => (int) $r->id, 'code' => $r->code, 'sort_order' => (int) $r->sort_order]);

        return response()->json([
            'ok'      => true,
            'message' => strtoupper($type) . ' order saved.',
            'items'   => $items,
        ]);
    }
    // ░░░ END: Reorder ░░░

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

    // ░░░ START: Helpers ░░░
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
            $pos = 1;
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
    // ░░░ END: Helpers ░░░
}
