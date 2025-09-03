<?php

// File: app/Http/Controllers/Faculty/SyllabusSdgController.php
// Description: Handles AJAX-based Sustainable Development Goal (SDG) mapping logic for faculty syllabus – Syllaverse

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Syllabus;
use App\Models\Sdg;
use App\Models\SyllabusSdg;

class SyllabusSdgController extends Controller
{
    /**
     * Attach an SDG to the syllabus (used via modal, returns JSON for AJAX).
     */
    public function attach(Request $request, Syllabus $syllabus)
    {
        $request->validate([
            'sdg_id' => 'required|exists:sdgs,id',
        ]);

        $sdg = Sdg::findOrFail($request->sdg_id);

        // Avoid duplicate mapping
        if ($syllabus->sdgs()->where('sdg_id', $sdg->id)->exists()) {
            return response()->json(['error' => 'SDG already mapped.'], 409);
        }

        $syllabus->sdgs()->attach($sdg->id, [
            'title' => $sdg->title,
            'description' => $sdg->description,
        ]);

        $pivot = $syllabus->sdgs()->where('sdg_id', $sdg->id)->first()->pivot;

        return response()->json([
            'title' => $pivot->title,
            'description' => $pivot->description,
            'sdg_id' => $sdg->id,
            'pivot_id' => $pivot->id,
        ]);
    }

    /**
     * Update title and description for a mapped SDG (pivot table).
     */
    public function update(Request $request, Syllabus $syllabus, $pivotId)
    {
        // Accept partial updates (autosave of description only)
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $pivot = SyllabusSdg::where('id', $pivotId)
            ->where('syllabus_id', $syllabus->id)
            ->firstOrFail();

        // Only update provided fields to avoid overwriting existing values with null
        $updates = [];
        if ($request->has('title')) $updates['title'] = $request->title;
        if ($request->has('description')) $updates['description'] = $request->description;

        if (!empty($updates)) {
            $pivot->update($updates);
        }

        return response()->json(['message' => 'Updated successfully.']);
    }

    /**
     * Bulk update SDG descriptions and codes/positions for a syllabus.
     * Expects payload: { sdgs: [{ id: pivotId|null, code, description, position }] }
     */
    public function bulkUpdate(Request $request, Syllabus $syllabus)
    {
        $data = $request->validate([
            'sdgs' => 'required|array',
            'sdgs.*.id' => 'nullable|integer',
            'sdgs.*.code' => 'nullable|string|max:50',
            'sdgs.*.description' => 'nullable|string|max:1000',
            'sdgs.*.position' => 'nullable|integer',
        ]);

        foreach ($data['sdgs'] as $item) {
            if (!empty($item['id'])) {
                $pivot = SyllabusSdg::where('id', $item['id'])->where('syllabus_id', $syllabus->id)->first();
                if ($pivot) {
                    $pivot->update([ 'description' => $item['description'] ?? $pivot->description, 'code' => $item['code'] ?? $pivot->code, 'position' => $item['position'] ?? $pivot->position ]);
                }
            }
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Reorder SDG pivot positions. Expects { positions: [{ id: pivotId, position }] }
     */
    public function reorder(Request $request, Syllabus $syllabus)
    {
        $data = $request->validate(['positions' => 'required|array', 'positions.*.id' => 'required|integer', 'positions.*.position' => 'required|integer']);
        foreach ($data['positions'] as $p) {
            SyllabusSdg::where('id', $p['id'])->where('syllabus_id', $syllabus->id)->update(['position' => $p['position']]);
        }
        return response()->json(['ok' => true]);
    }

    /**
     * Detach an SDG from the syllabus.
     */
    public function detach(Syllabus $syllabus, Sdg $sdg)
    {
        $syllabus->sdgs()->detach($sdg->id);

        return response()->json(['message' => 'SDG removed.']);
    }
}
