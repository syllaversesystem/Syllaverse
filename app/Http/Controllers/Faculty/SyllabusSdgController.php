<?php

// File: app/Http/Controllers/Faculty/SyllabusSdgController.php
// Description: Handles AJAX-based Sustainable Development Goal (SDG) mapping logic for faculty syllabus – Syllaverse

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Syllabus;
use App\Models\Sdg;
use App\Models\SyllabusSdg;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SyllabusSdgController extends Controller
{
    /**
     * Attach an SDG to the syllabus (used via modal, returns JSON for AJAX).
     */
    public function attach(Request $request, Syllabus $syllabus)
    {
        // Ownership guard: only the faculty who owns the syllabus may modify its SDGs
        if ($syllabus->faculty_id !== Auth::id() && ! Auth::guard('admin')->check()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        // Accept either a single sdg_id or an array sdg_ids for bulk attach
        if ($request->has('sdg_ids')) {
            $data = $request->validate([
                'sdg_ids' => 'required|array',
                'sdg_ids.*' => 'required|integer|exists:sdgs,id',
            ]);

            $created = [];
            $nextOrder = ($syllabus->sdgs()->max('sort_order') ?? 0) + 1;
            foreach ($data['sdg_ids'] as $sid) {
                $sdg = Sdg::find($sid);
                if (!$sdg) continue;
                // skip duplicates by title
                if ($syllabus->sdgs()->where('title', $sdg->title)->exists()) continue;

                $entry = SyllabusSdg::create([
                    'syllabus_id' => $syllabus->id,
                    'code' => 'SDG' . $nextOrder,
                    'sort_order' => $nextOrder,
                    'title' => $sdg->title,
                    'description' => $sdg->description,
                ]);

                $created[] = [
                    'title' => $entry->title,
                    'description' => $entry->description,
                    'sdg_id' => $sdg->id,
                    'pivot_id' => $entry->id,
                    'code' => $entry->code,
                ];

                $nextOrder++;
            }

            return response()->json(['created' => $created]);
        }

        // Backwards-compatible single attach
        $request->validate([
            'sdg_id' => 'required|exists:sdgs,id',
        ]);

        $sdg = Sdg::findOrFail($request->sdg_id);

        // Avoid duplicate mapping by code or sdg title
        if ($syllabus->sdgs()->where('title', $sdg->title)->exists()) {
            return response()->json(['error' => 'SDG already mapped.'], 409);
        }

        $nextOrder = ($syllabus->sdgs()->max('sort_order') ?? 0) + 1;
        $entry = SyllabusSdg::create([
            'syllabus_id' => $syllabus->id,
            'code' => 'SDG' . $nextOrder,
            'sort_order' => $nextOrder,
            'title' => $sdg->title,
            'description' => $sdg->description,
        ]);

        return response()->json([
            'title' => $entry->title,
            'description' => $entry->description,
            'sdg_id' => $sdg->id,
            'pivot_id' => $entry->id,
            'code' => $entry->code,
        ]);
    }

    /**
     * Update title and description for a mapped SDG (pivot table).
     */
    public function update(Request $request, Syllabus $syllabus, $pivotId)
    {
        if ($syllabus->faculty_id !== Auth::id() && ! Auth::guard('admin')->check()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        // Accept partial updates (autosave of description only)
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $entry = SyllabusSdg::where('id', $pivotId)
            ->where('syllabus_id', $syllabus->id)
            ->firstOrFail();

        // Only update provided fields
        $updates = [];
        if ($request->has('title')) $updates['title'] = $request->title;
        if ($request->has('description')) $updates['description'] = $request->description;
        if ($request->has('code')) $updates['code'] = $request->code;

        if (!empty($updates)) {
            $entry->update($updates);
        }

        return response()->json(['message' => 'Updated successfully.']);
    }

    /**
     * Bulk update SDG descriptions and codes/positions for a syllabus.
     * Expects payload: { sdgs: [{ id: pivotId|null, code, description, position }] }
     */
    public function bulkUpdate(Request $request, Syllabus $syllabus)
    {
        if ($syllabus->faculty_id !== Auth::id() && ! Auth::guard('admin')->check()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
    // Debug: log incoming payload for troubleshooting when update appears to silently fail
    try { Log::debug('SyllabusSdgController::bulkUpdate payload', $request->all()); } catch (\Throwable $e) {}
        $data = $request->validate([
            'sdgs' => 'required|array',
            'sdgs.*.id' => 'nullable|integer',
            'sdgs.*.title' => 'nullable|string|max:255',
            'sdgs.*.code' => 'nullable|string|max:50',
            'sdgs.*.description' => 'nullable|string|max:1000',
            'sdgs.*.position' => 'nullable|integer',
        ]);
        // Update entries inside a transaction and avoid writing 'code' directly to prevent
        // transient uniqueness conflicts (code is derived from position). After updating
        // descriptions and sort_order, call resequence() to recompute codes deterministically.
        \DB::beginTransaction();
        try {
            foreach ($data['sdgs'] as $item) {
                if (!empty($item['id'])) {
                    $entry = SyllabusSdg::where('id', $item['id'])->where('syllabus_id', $syllabus->id)->first();
                    if ($entry) {
                        $updates = [];
                        if (array_key_exists('description', $item)) $updates['description'] = $item['description'];
                        if (array_key_exists('position', $item)) $updates['sort_order'] = $item['position'];
                        if (array_key_exists('title', $item)) $updates['title'] = $item['title'];
                        if (!empty($updates)) $entry->update($updates);
                    }
                }
            }

            // Recompute codes and ensure contiguous ordering
            $this->resequence($syllabus);

            // Handle explicit code overrides safely:
            // Collect desired code updates (id => code) when provided
            $codeUpdates = [];
            foreach ($data['sdgs'] as $item) {
                if (!empty($item['id']) && array_key_exists('code', $item) && $item['code'] !== null) {
                    $codeUpdates[(int)$item['id']] = (string)$item['code'];
                }
            }

            if (!empty($codeUpdates)) {
                // Ensure no duplicate target codes in payload
                if (count(array_unique($codeUpdates)) !== count($codeUpdates)) {
                    return response()->json(['error' => 'Duplicate code values provided in payload.'], 422);
                }

                $ids = array_keys($codeUpdates);
                // Check for collisions with existing entries not being updated
                $collision = SyllabusSdg::where('syllabus_id', $syllabus->id)
                    ->whereNotIn('id', $ids)
                    ->whereIn('code', array_values($codeUpdates))
                    ->exists();
                if ($collision) {
                    return response()->json(['error' => 'One or more requested codes collide with existing entries.'], 422);
                }

                // First set temporary unique placeholders to avoid uniqueness constraint
                $placeholders = [];
                foreach ($codeUpdates as $id => $targetCode) {
                    $placeholder = 'TMP_' . uniqid() . '_' . $id;
                    $placeholders[$id] = $placeholder;
                    SyllabusSdg::where('id', $id)->where('syllabus_id', $syllabus->id)->update(['code' => $placeholder]);
                }

                // Now set final codes (placeholders freed previous values)
                foreach ($codeUpdates as $id => $targetCode) {
                    SyllabusSdg::where('id', $id)->where('syllabus_id', $syllabus->id)->update(['code' => $targetCode]);
                }
            }

            \DB::commit();
            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            \DB::rollBack();
            return response()->json(['error' => 'Failed to update SDGs', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Reorder SDG pivot positions. Expects { positions: [{ id: pivotId, position }] }
     */
    public function reorder(Request $request, Syllabus $syllabus)
    {
        if ($syllabus->faculty_id !== Auth::id() && ! Auth::guard('admin')->check()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        // Accept either { ids: [id1, id2, ...] } or legacy { positions: [{ id: pivotId, position }] }
        $payload = $request->all();
        $ids = [];
        if (isset($payload['ids']) && is_array($payload['ids'])) {
            // normalize and filter numeric ids
            foreach ($payload['ids'] as $v) {
                if (is_numeric($v)) $ids[] = (int)$v;
            }
        } elseif (isset($payload['positions']) && is_array($payload['positions'])) {
            foreach ($payload['positions'] as $p) {
                if (is_array($p) && array_key_exists('id', $p) && is_numeric($p['id'])) $ids[] = (int)$p['id'];
            }
        } else {
            return response()->json(['error' => 'Invalid payload for reorder. Provide ids or positions.'], 422);
        }

    // Debug: log incoming payload for reorder troubleshooting
    try { Log::debug('SyllabusSdgController::reorder payload', $payload); } catch (\Throwable $e) {}

    // Ensure we have at least one numeric id
        $ids = array_values(array_filter($ids, function($v){ return is_int($v) && $v > 0; }));
        if (!count($ids)) {
            return response()->json(['error' => 'No valid ids provided for reorder.'], 422);
        }

        // perform bulk update in a single transaction using CASE WHEN to minimize queries
        \DB::beginTransaction();
        try {
            if (count($ids)) {
                $cases = [];
                $params = [];
                foreach ($ids as $index => $id) {
                    $pos = $index + 1;
                    $cases[] = "WHEN id = ? THEN ?";
                    $params[] = $id;
                    $params[] = $pos;
                }
                $caseSql = implode(' ', $cases);
                $idsList = implode(',', array_map(function($i){ return '?'; }, $ids));

                $sql = "UPDATE syllabus_sdgs SET sort_order = CASE {$caseSql} END WHERE syllabus_id = ? AND id IN ({$idsList})";
                // bind syllabus_id followed by the ids for the IN clause
                array_push($params, $syllabus->id);
                $params = array_merge($params, $ids);

                \DB::update($sql, $params);
            }

            // ensure contiguous sequence (defensive)
            $this->resequence($syllabus);
            \DB::commit();
            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            \DB::rollBack();
            return response()->json(['error' => 'Failed to reorder', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Detach an SDG from the syllabus.
     */
    public function detach(Syllabus $syllabus, Sdg $sdg)
    {
        if ($syllabus->faculty_id !== Auth::id() && ! Auth::guard('admin')->check()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        // remove any per-syllabus entries matching the SDG title or code
        $deleted = SyllabusSdg::where('syllabus_id', $syllabus->id)
            ->where(function ($q) use ($sdg) {
                $q->where('code', $sdg->code)->orWhere('title', $sdg->title);
            })->delete();

    // ensure remaining entries have contiguous sort_order values
    $this->resequence($syllabus);

    return response()->json(['message' => "SDG removed.", 'deleted' => $deleted]);
    }

    /**
     * Delete a per-syllabus SDG entry by its entry id (used by JS when row contains the entry id)
     */
    public function destroyEntry(Syllabus $syllabus, $id)
    {
        if ($syllabus->faculty_id !== Auth::id() && ! Auth::guard('admin')->check()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        $entry = SyllabusSdg::where('id', $id)->where('syllabus_id', $syllabus->id)->first();
        if (!$entry) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $entry->delete();

        // resequence remaining entries so DB numbering matches UI
        $this->resequence($syllabus);

        return response()->json(['message' => 'Entry removed.']);
    }

    /**
     * Ensure syllabus SDG entries have contiguous sort_order (1..N)
     */
    protected function resequence(Syllabus $syllabus)
    {
        $entries = SyllabusSdg::where('syllabus_id', $syllabus->id)->orderBy('sort_order')->get();
        $finalCodes = [];
        $ids = [];
        $pos = 1;

        // compute final codes and collect ids
        foreach ($entries as $e) {
            $finalCodes[$e->id] = 'SDG' . $pos;
            $ids[] = $e->id;
            $pos++;
        }

        if (empty($ids)) return;

        // Step 1: set temporary unique placeholders for all affected rows to avoid
        // transient uniqueness conflicts when assigning final codes.
        foreach ($ids as $id) {
            $placeholder = 'TMP_' . uniqid() . '_' . $id;
            SyllabusSdg::where('id', $id)->where('syllabus_id', $syllabus->id)->update(['code' => $placeholder]);
        }

        // Step 2: set final codes and contiguous sort_order in a single CASE update
        $casesCode = [];
        $casesOrder = [];
        $paramsCode = [];
        $paramsOrder = [];
        $i = 1;
        foreach ($finalCodes as $id => $code) {
            // build the CASE WHEN fragments for code and for sort_order
            $casesCode[] = "WHEN id = ? THEN ?";
            $casesOrder[] = "WHEN id = ? THEN ?";

            // collect params for each CASE separately (important: keep order)
            // code CASE params: id, code
            $paramsCode[] = $id;
            $paramsCode[] = $code;

            // sort_order CASE params: id, position
            $paramsOrder[] = $id;
            $paramsOrder[] = $i;

            $i++;
        }

        $caseSqlCode = implode(' ', $casesCode);
        $caseSqlOrder = implode(' ', $casesOrder);
        $idsList = implode(',', array_fill(0, count($ids), '?'));

        // Merge params: all code-case params first, then all order-case params,
        // then syllabus_id, then ids for the IN() clause. This ordering must
        // match the positional placeholders in the generated SQL above.
        $params = array_merge($paramsCode, $paramsOrder);

        $sql = "UPDATE syllabus_sdgs SET code = CASE {$caseSqlCode} END, sort_order = CASE {$caseSqlOrder} END WHERE syllabus_id = ? AND id IN ({$idsList})";

        // append syllabus_id and the ids for the IN clause
        $params[] = $syllabus->id;
        $params = array_merge($params, $ids);

        \DB::update($sql, $params);
    }
}
