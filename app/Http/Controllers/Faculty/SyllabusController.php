<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/Faculty/SyllabusController.php
// * Description: Full controller for faculty syllabus management (SDG logic moved to SyllabusSdgController) – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-07-28] Initial creation – faculty syllabus controller with create/edit/export logic.
// [2025-07-29] Updated ILO and SO cloning to include `code` and `position` for compliance with new schema.
// [2025-07-29] Eager-loaded TLA ILO/SO relationships to display mapped codes in Blade.
// -------------------------------------------------------------------------------

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Syllabus;
use App\Models\SyllabusIlo;
use App\Models\SyllabusSo;
use App\Models\StudentOutcome;
use App\Models\Course;
use App\Models\Program;
use App\Models\GeneralInformation;
use App\Models\Sdg;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;

class SyllabusController extends Controller
{
    public function index()
    {
        $syllabi = Syllabus::with('course')
            ->where('faculty_id', auth()->id())
            ->latest()
            ->get();

        $programs = Program::all();
        $courses = Course::all();

        return view('faculty.syllabus.index', compact('syllabi', 'programs', 'courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'program_id' => 'nullable|exists:programs,id',
            'course_id' => 'required|exists:courses,id',
            'academic_year' => 'required|string',
            'semester' => 'required|string',
            'year_level' => 'required|string',
        ]);

        $mission = GeneralInformation::where('section', 'mission')->first()?->content ?? '';
        $vision  = GeneralInformation::where('section', 'vision')->first()?->content ?? '';

        $syllabus = Syllabus::create([
            'faculty_id' => Auth::id(),
            'program_id' => $request->program_id,
            'course_id' => $request->course_id,
            'title' => $request->title,
            'academic_year' => $request->academic_year,
            'semester' => $request->semester,
            'year_level' => $request->year_level,
            'mission' => $mission,
            'vision' => $vision,
        ]);

        $course = Course::with('ilos')->find($request->course_id);
        foreach ($course->ilos as $ilo) {
            SyllabusIlo::create([
                'syllabus_id' => $syllabus->id,
                'code' => $ilo->code,
                'description' => $ilo->description,
            ]);
        }

        $masterSOs = StudentOutcome::orderBy('position')->get();
        foreach ($masterSOs as $index => $so) {
            SyllabusSo::create([
                'syllabus_id' => $syllabus->id,
                'code' => $so->code,
                'description' => $so->description,
                'position' => $index + 1,
            ]);
        }

        return redirect()->route('faculty.syllabi.index')
            ->with('success', 'Syllabus created successfully.');
    }

    public function show($id)
    {
        $syllabus = Syllabus::with([
            'course', 'program', 'ilos', 'sos', 'sdgs',
            'tla.ilos:id,code',
            'tla.sos:id,code'
        ])->findOrFail($id);

        $programs = Program::all();
        $courses = Course::all();
        $sdgs = Sdg::all();

        return view('faculty.syllabus.syllabus', [
            'syllabus' => $syllabus,
            'default' => $syllabus->only([
                'id', 'title', 'program_id', 'course_id',
                'academic_year', 'semester', 'year_level',
                'mission', 'vision', 'description', 'instructor', 'contact_hours'
            ]) + ['sdgs' => $syllabus->sdgs],
            'programs' => $programs,
            'courses' => $courses,
            'ilos' => $syllabus->ilos,
            'sos' => $syllabus->sos,
            'sdgs' => $sdgs,
        ]);
    }

    public function update(Request $request, $id)
    {
        $syllabus = Syllabus::where('faculty_id', Auth::id())->findOrFail($id);

        $request->validate([
            'mission' => 'required|string',
            'vision' => 'required|string',
        ]);

        $syllabus->update([
            'mission' => $request->mission,
            'vision' => $request->vision,
        ]);

        return redirect()->route('faculty.syllabi.show', $syllabus->id)
            ->with('success', 'Syllabus updated successfully.');
    }

    public function destroy($id)
    {
        $syllabus = Syllabus::where('faculty_id', auth()->id())->findOrFail($id);
        $syllabus->delete();

        return redirect()->route('faculty.syllabi.index')
            ->with('success', 'Syllabus deleted successfully.');
    }

    public function exportPdf($id)
    {
        $syllabus = Syllabus::with(['course', 'program'])->findOrFail($id);
        $pdf = Pdf::loadView('faculty.syllabus.exports.pdf', compact('syllabus'));
        return $pdf->download('syllabus_' . $syllabus->id . '.pdf');
    }

    public function exportWord($id)
    {
        $syllabus = Syllabus::with(['course', 'program'])->findOrFail($id);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Georgia');
        $phpWord->setDefaultFontSize(12);
        $section = $phpWord->addSection();

        $section->addText('BATANGAS STATE UNIVERSITY', ['bold' => true, 'size' => 14], ['alignment' => 'center']);
        $section->addText('The National Engineering University', ['bold' => true, 'color' => 'B22222'], ['alignment' => 'center']);
        $section->addText('ARASOF–Nasugbu Campus', null, ['alignment' => 'center']);
        $section->addText('COURSE INFORMATION SYLLABUS (CIS)', ['bold' => true], ['alignment' => 'center']);
        $section->addTextBreak(1);

        $section->addText('I. VISION', ['bold' => true, 'underline' => 'single']);
        $section->addText($syllabus->vision);
        $section->addTextBreak(1);

        $section->addText('II. MISSION', ['bold' => true, 'underline' => 'single']);
        $section->addText($syllabus->mission);
        $section->addTextBreak(1);

        $section->addText('III. COURSE INFORMATION', ['bold' => true, 'underline' => 'single']);
        $table = $section->addTable(['borderSize' => 6, 'cellMargin' => 50]);
        $table->addRow();
        $table->addCell(3000)->addText('Course Title', ['bold' => true]);
        $table->addCell(8000)->addText($syllabus->course->title ?? 'N/A');
        $table->addRow();
        $table->addCell(3000)->addText('Course Code', ['bold' => true]);
        $table->addCell(8000)->addText($syllabus->course->code ?? 'N/A');
        $table->addRow();
        $table->addCell(3000)->addText('Program', ['bold' => true]);
        $table->addCell(8000)->addText($syllabus->program->name ?? 'N/A');
        $table->addRow();
        $table->addCell(3000)->addText('Academic Year', ['bold' => true]);
        $table->addCell(8000)->addText($syllabus->academic_year);
        $table->addRow();
        $table->addCell(3000)->addText('Semester', ['bold' => true]);
        $table->addCell(8000)->addText($syllabus->semester);
        $table->addRow();
        $table->addCell(3000)->addText('Year Level', ['bold' => true]);
        $table->addCell(8000)->addText($syllabus->year_level);

        $filename = 'syllabus_' . $syllabus->id . '.docx';
        $tempPath = storage_path($filename);
        $phpWord->save($tempPath, 'Word2007');

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }
}
