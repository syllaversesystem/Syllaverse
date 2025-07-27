<?php

// File: app/Http/Controllers/Admin/MasterDataController.php
// Description: Handles SO, ILO, Programs, and Courses management – auto-insert logic for ILOs removed (Syllaverse)


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
            $count = StudentOutcome::count();
            $nextCode = 'SO' . ($count + 1);

            StudentOutcome::create([
                'code' => $nextCode,
                'description' => $validated['description'],
            ]);

            return back()->with('success', "SO '{$nextCode}' added successfully!");
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

            return redirect()
                ->route('admin.master-data.index', ['course_id' => $validated['course_id']])
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

        if ($type === 'ilo') {
            $rules['position'] = 'required|integer|min:1';
        }

        $request->validate($rules);

        $model = $type === 'so'
            ? StudentOutcome::findOrFail($id)
            : IntendedLearningOutcome::findOrFail($id);

        $model->update($request->only('code', 'description', 'position'));

        return back()->with('success', strtoupper($type) . ' updated successfully!');
    }

    public function destroy($type, $id)
    {
        $model = $type === 'so'
            ? StudentOutcome::findOrFail($id)
            : IntendedLearningOutcome::findOrFail($id);

        $courseId = $model->course_id ?? null;

        $model->delete();

        if ($type === 'ilo' && $courseId) {
            return redirect()
                ->route('admin.master-data.index', ['course_id' => $courseId])
                ->with('success', 'ILO deleted successfully!');
        }

        return back()->with('success', strtoupper($type) . ' deleted successfully!');
    }
}
