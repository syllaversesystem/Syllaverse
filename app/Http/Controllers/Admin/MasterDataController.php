<?php

// File: app/Http/Controllers/Admin/MasterDataController.php
// Description: Handles SO, ILO, Programs, and Courses management – auto-insert logic for ILOs removed (Syllaverse)
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Added reorderIlo() with safe 3-pass logic using request order directly.
// [2025-07-29] Updated redirects to retain tab and subtab state for ILO CRUD actions.
// [2025-07-29] Added 'open_modal' session flash to reopen ILO form after redirect.
// -----------------------------------------------------------------------------

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StudentOutcome;
use App\Models\IntendedLearningOutcome;
use App\Models\Program;
use App\Models\Course;

class MasterDataController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $selectedCourseId = $request->input('course_id');

        return view('admin.master-data.index', [
            'studentOutcomes' => StudentOutcome::all(),
            'intendedLearningOutcomes' => $selectedCourseId
                ? IntendedLearningOutcome::where('course_id', $selectedCourseId)->orderBy('position')->get()
                : collect(),
            'courses' => Course::where('department_id', $user->department_id)->get(),
            'programs' => Program::where('department_id', $user->department_id)->get(),
        ]);
    }

    public function store(Request $request, $type)
{
    $rules = [
        'description' => 'required|string',
    ];

    if ($type === 'ilo') {
        $rules['course_id'] = 'required|exists:courses,id';
    }

    $validated = $request->validate($rules);

    if ($type === 'so') {
        // ✅ Use max position to avoid duplicate codes
        $nextPosition = StudentOutcome::max('position') + 1;
        $nextCode = 'SO' . $nextPosition;

        StudentOutcome::create([
            'code' => $nextCode,
            'description' => $validated['description'],
            'position' => $nextPosition,
        ]);

        return redirect()->route('admin.master-data.index', [
            'tab' => 'soilo',
            'subtab' => 'so'
        ])->with('success', "SO '{$nextCode}' added successfully!");
    }

    if ($type === 'ilo') {
        $count = IntendedLearningOutcome::where('course_id', $validated['course_id'])->count();
        $nextCode = 'ILO' . ($count + 1);
        $nextPosition = $count + 1;

        IntendedLearningOutcome::create([
            'code' => $nextCode,
            'description' => $validated['description'],
            'course_id' => $validated['course_id'],
            'position' => $nextPosition,
        ]);

        return redirect()->route('admin.master-data.index', [
            'course_id' => $validated['course_id'],
            'tab' => 'soilo',
            'subtab' => 'ilo'
        ])->with('open_modal', 'add-ilo')
          ->with('success', "ILO '{$nextCode}' added successfully!");
    }

    return back();
}

    public function update(Request $request, $type, $id)
    {
        $rules = [
            'code' => 'required|string|max:10',
            'description' => 'required|string',
        ];

        $request->validate($rules);

        $model = $type === 'so'
            ? StudentOutcome::findOrFail($id)
            : IntendedLearningOutcome::findOrFail($id);

        $model->update($request->only('code', 'description'));

        $redirectParams = ['tab' => 'soilo'];
        if ($type === 'ilo') {
            $redirectParams += [
                'course_id' => $request->input('course_id'),
                'subtab' => 'ilo'
            ];
        } else {
            $redirectParams['subtab'] = 'so';
        }

        return redirect()->route('admin.master-data.index', $redirectParams)
            ->with('success', strtoupper($type) . ' updated successfully!');
    }

    public function destroy($type, $id)
    {
        $model = $type === 'so'
            ? StudentOutcome::findOrFail($id)
            : IntendedLearningOutcome::findOrFail($id);

        $courseId = $model->course_id ?? null;

        $model->delete();

        $redirectParams = ['tab' => 'soilo'];
        if ($type === 'ilo' && $courseId) {
            $redirectParams += [
                'course_id' => $courseId,
                'subtab' => 'ilo'
            ];
        } else {
            $redirectParams['subtab'] = 'so';
        }

        return redirect()->route('admin.master-data.index', $redirectParams)
            ->with('success', strtoupper($type) . ' deleted successfully!');
    }

    public function reorderIlo(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'course_id' => 'required|exists:courses,id',
            ]);

            $ilos = IntendedLearningOutcome::whereIn('id', $request->ids)
                ->where('course_id', $request->course_id)
                ->get()
                ->keyBy('id');

            // Step 1: Temporarily assign unique placeholder codes
            foreach ($ilos as $ilo) {
                $ilo->forceFill(['code' => '__TEMP__' . $ilo->id])->save();
            }

            // Step 2: Update position and real code
            foreach ($request->ids as $index => $id) {
                if ($ilos->has($id)) {
                    $ilos[$id]->forceFill([
                        'position' => $index + 1,
                        'code' => 'ILO' . ($index + 1)
                    ])->save();
                }
            }

            return response()->json(['message' => 'ILO order updated successfully.']);

        } catch (\Throwable $e) {
            \Log::error('ILO reorder failed: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Server error.'], 500);
        }
    }


    // 🧩 Handles AJAX call to reorder Student Outcomes
// 🧩 Handles AJAX call to reorder Student Outcomes
public function reorderSo(Request $request)
{
    try {
        $data = $request->validate([
            'orderedIds' => 'required|array',
            'orderedIds.*' => 'integer',
        ]);

        $studentOutcomes = StudentOutcome::whereIn('id', $data['orderedIds'])
            ->get()
            ->keyBy('id');

        // 🔁 STEP 1: Assign temporary placeholder codes to prevent conflicts
        foreach ($studentOutcomes as $so) {
            $so->forceFill(['code' => '__TEMP__' . $so->id])->save();
        }

        // 🔁 STEP 2: Assign correct codes and positions
        foreach ($data['orderedIds'] as $index => $id) {
            if (isset($studentOutcomes[$id])) {
                $studentOutcomes[$id]->forceFill([
                    'position' => $index + 1,
                    'code' => 'SO' . ($index + 1),
                ])->save();
            }
        }

        return response()->json(['message' => 'Student Outcomes reordered successfully.']);
    } catch (\Throwable $e) {
        \Log::error('SO reorder failed: ' . $e->getMessage(), [
            'stack' => $e->getTraceAsString(),
        ]);
        return response()->json(['message' => 'Server error.'], 500);
    }
}





}
