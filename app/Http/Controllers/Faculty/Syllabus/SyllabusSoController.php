<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/Faculty/Syllabus/SyllabusSoController.php
// * Description: Handles AJAX-based updates, reordering, and deletion of syllabus-specific SOs – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Added reorder() and destroy() methods; update() now saves code[] and position.
// -------------------------------------------------------------------------------


namespace App\Http\Controllers\Faculty\Syllabus;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Syllabus;
use App\Models\SyllabusSo;

class SyllabusSoController extends Controller
{
    // ✅ Save updated SO list (code + description)
    public function update(Request $request, $syllabusId)
    {
        $syllabus = $this->getSyllabusForAction($syllabusId);

        $request->validate([
            'sos' => 'required|array',
            'sos.*' => 'required|string|max:1000',
            'so_titles' => 'nullable|array',
            'so_titles.*' => 'nullable|string|max:255',
            'code' => 'required|array',
            'code.*' => 'required|string|max:20',
        ]);

        // Delete old SOs
        $syllabus->sos()->delete();

        // Insert updated SOs
        foreach ($request->sos as $index => $description) {
            SyllabusSo::create([
                'syllabus_id' => $syllabus->id,
                'code' => $request->code[$index] ?? 'SO' . ($index + 1),
                'title' => $request->input('so_titles.' . $index),
                'description' => $description,
                'position' => $index + 1,
            ]);
        }

        return response()->json(['message' => 'Student Outcomes updated successfully.']);
    }

    // ✅ Save reordered SOs via drag-and-drop
    public function reorder(Request $request, $syllabusId)
{
    $request->validate([
        'positions' => 'required|array',
        'positions.*.id' => 'required|integer|exists:syllabus_sos,id',
        'positions.*.position' => 'required|integer',
    ]);

    foreach ($request->positions as $item) {
        $code = 'SO' . $item['position']; // 👈 auto-regenerate code from position
        SyllabusSo::where('id', $item['id'])
            ->where('syllabus_id', $syllabusId)
            ->update([
                'position' => $item['position'],
                'code' => $code,
            ]);
    }

    return response()->json(['message' => 'SO order and codes saved successfully.']);
}


    // ✅ Delete a single SO via AJAX
    public function destroy($id)
    {
        $so = SyllabusSo::findOrFail($id);

        // Only allow delete if owned by current faculty
        if ($so->syllabus->faculty_id !== Auth::id() && ! Auth::guard('admin')->check()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $so->delete();

        return response()->json(['message' => 'SO deleted successfully.']);
    }

    protected function getSyllabusForAction($syllabusId)
    {
        if (Auth::guard('admin')->check()) {
            return Syllabus::findOrFail($syllabusId);
        }
        return Syllabus::where('faculty_id', Auth::id())->findOrFail($syllabusId);
    }
}
