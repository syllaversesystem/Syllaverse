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
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
        ]);

        $pivot = SyllabusSdg::where('id', $pivotId)
            ->where('syllabus_id', $syllabus->id)
            ->firstOrFail();

        $pivot->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return response()->json(['message' => 'Updated successfully.']);
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
