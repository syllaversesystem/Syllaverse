<?php

// -------------------------------------------------------------------------------
// * File: app/Http/Controllers/Faculty/Syllabus/SyllabusController.php
// * Description: Full controller for faculty syllabus management (SDG logic moved to SyllabusSdgController) – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-07-28] Initial creation – faculty syllabus controller with create/edit/export logic.
// [2025-07-29] Updated ILO and SO cloning to include `code` and `position` for compliance with new schema.
// [2025-07-29] Eager-loaded TLA ILO/SO relationships to display mapped codes in Blade.
// -------------------------------------------------------------------------------

namespace App\Http\Controllers\Faculty\Syllabus;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Syllabus;
use App\Models\SyllabusSo;
use App\Models\StudentOutcome;
use App\Models\Course;
use App\Models\Program;
use App\Models\GeneralInformation;
use App\Models\Iga;
use App\Models\SyllabusIga;
use App\Models\Cdio;
use App\Models\SyllabusCdio;
use App\Models\SyllabusCoursePolicy;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;

class SyllabusController extends Controller
{
    public function __construct(
        protected SyllabusMissionVisionController $missionVision,
        protected SyllabusCourseInfoController $courseInfo,
        protected SyllabusCriteriaController $criteria,
        protected SyllabusIloController $ilos,
        protected SyllabusAssessmentTasksController $assessmentTasks,
        protected SyllabusCoursePolicyController $coursePolicy,
    ) {
    }
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
            'faculty_id' => 'nullable|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'academic_year' => 'required|string',
            'semester' => 'required|string',
            'year_level' => 'required|string',
        ]);

        $facultyId = $request->input('faculty_id') ?: Auth::id();

        $syllabus = Syllabus::create([
            'faculty_id' => $facultyId,
            'program_id' => $request->program_id,
            'course_id' => $request->course_id,
            'title' => $request->title,
            'academic_year' => $request->academic_year,
            'semester' => $request->semester,
            'year_level' => $request->year_level,
        ]);

    // Diagnostic: log created syllabus id so we can correlate model-created seeding
    try { \Log::info('SyllabusController::store created syllabus', ['syllabus_id' => $syllabus->id, 'faculty_id' => $facultyId]); } catch (\Throwable $__e) {}
    // seed mission & vision defaults via dedicated partial controller
    $this->missionVision->seedFromGeneralInformation($syllabus);

        $course = Course::with(['ilos', 'prerequisites'])->find($request->course_id);
        $this->courseInfo->seedFromCourse($syllabus, $course);
        $this->ilos->seedFromCourse($syllabus, $course);

        // Copy master IGAs into per-syllabus IGAs if master table exists
        try {
            if (Schema::hasTable('igas')) {
                $masterIgas = Iga::ordered()->get();
                $pos = 1;
                foreach ($masterIgas as $m) {
                    SyllabusIga::create([
                        'syllabus_id' => $syllabus->id,
                        'code' => $m->code ?? (\App\Models\Iga::makeCodeFromPosition($pos)),
                        'description' => $m->description ?? $m->title ?? null,
                        'position' => $pos++,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // don't break syllabus creation if master IGAs cannot be fetched for any reason
            \Log::warning('Failed to copy master IGAs into syllabus', ['error' => $e->getMessage()]);
        }

        // Seed course policies from general_information via dedicated controller
        $this->coursePolicy->seedFromGeneralInformation($syllabus);

        // Copy master Student Outcomes into per-syllabus SOs if master table exists
        try {
            if (Schema::hasTable('student_outcomes')) {
                $masterSOs = StudentOutcome::orderBy('position')->get();
                $pos = 1;
                foreach ($masterSOs as $mso) {
                    SyllabusSo::create([
                        'syllabus_id' => $syllabus->id,
                        'code' => $mso->code ?? ('SO' . $pos),
                        'description' => $mso->description ?? $mso->title ?? null,
                        'position' => $pos++,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // don't break syllabus creation if master SOs cannot be fetched for any reason
            \Log::warning('Failed to copy master StudentOutcomes into syllabus', ['error' => $e->getMessage()]);
        }

        // Copy master CDIOs into per-syllabus CDIOs if master table exists
        try {
            if (Schema::hasTable('cdios')) {
                $masterCdios = Cdio::ordered()->get();
                $pos = 1;
                foreach ($masterCdios as $mcdio) {
                    SyllabusCdio::create([
                        'syllabus_id' => $syllabus->id,
                        'code' => $mcdio->code ?? (Cdio::makeCodeFromPosition($pos)),
                        'description' => $mcdio->description ?? $mcdio->title ?? null,
                        'position' => $pos++,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Failed to copy master CDIOs into syllabus', ['error' => $e->getMessage()]);
        }

        // Diagnostic logging for CDIO seed
        try {
            $masterCdioCount = isset($masterCdios) && is_iterable($masterCdios) ? count($masterCdios) : 0;
            $createdCdioCount = SyllabusCdio::where('syllabus_id', $syllabus->id)->count();
            \Log::info('Syllabus::store CDIO seed summary', ['syllabus_id' => $syllabus->id, 'master_count' => $masterCdioCount, 'created_count' => $createdCdioCount]);
        } catch (\Throwable $__e) {
            \Log::warning('Syllabus::store failed to compute CDIO seed counts', ['error' => $__e->getMessage()]);
        }

        // Diagnostic logging: report how many master SOs existed and how many per-syllabus SOs were created
        try {
            $masterCount = isset($masterSOs) && is_iterable($masterSOs) ? count($masterSOs) : 0;
            $createdCount = \App\Models\SyllabusSo::where('syllabus_id', $syllabus->id)->count();
            \Log::info('Syllabus::store SO seed summary', ['syllabus_id' => $syllabus->id, 'master_count' => $masterCount, 'created_count' => $createdCount]);
        } catch (\Throwable $__e) {
            \Log::warning('Syllabus::store failed to compute SO seed counts', ['error' => $__e->getMessage()]);
        }

        return redirect()->route('faculty.syllabi.show', $syllabus->id)
            ->with('success', 'Syllabus created successfully. You can now edit it.');
    }

    public function show($id)
    {
        $syllabus = Syllabus::with([
            'course', 'program', 'faculty', 'ilos', 'sos', 'sdgs', 'courseInfo', 'criteria', 'cdios',
            'tla.ilos:id,code',
            'tla.sos:id,code',
            // eager-load assessmentMappings so the partial can hydrate saved rows
            'assessmentMappings',
            // eager-load normalized mapping rows so blade partials can render them
            'iloSoCpa',
            'iloIga',
            'iloCdioSdg'
        ])->findOrFail($id);

        $programs = Program::all();
        $courses = Course::all();

        // load mission/vision defaults through the dedicated partial controller helper
        $missionVisionDefaults = $this->missionVision->defaults($syllabus);
        // load per-syllabus course policies so the partial can pre-fill the textareas
        $coursePolicies = [];
        try {
            if (Schema::hasTable('syllabus_course_policies')) {
                $coursePolicies = \App\Models\SyllabusCoursePolicy::where('syllabus_id', $syllabus->id)
                    ->orderBy('position')
                    ->get();
            }
        } catch (\Throwable $e) {
            // best-effort: log and continue without breaking view render
            \Log::warning('Syllabus::show failed to load course policies', ['error' => $e->getMessage(), 'syllabus_id' => $syllabus->id]);
        }

        return view('faculty.syllabus.syllabus', [
            'syllabus' => $syllabus,
            'default' => array_merge(
                $syllabus->only([
                    'id', 'title', 'program_id', 'course_id',
                    'academic_year', 'semester', 'year_level',
                    'description', 'instructor', 'contact_hours'
                ]),
                $missionVisionDefaults,
                ['sdgs' => $syllabus->sdgs]
            ),
            'programs' => $programs,
            'courses' => $courses,
            'ilos' => $syllabus->ilos,
            'sos' => $syllabus->sos,
            'igas' => $syllabus->igas ?? collect(),
            'cdios' => $syllabus->cdios ?? collect(),
            'coursePolicies' => $coursePolicies,
            'soColumns' => $syllabus->so_columns ? json_decode($syllabus->so_columns, true) : [],
        ]);
    }

    public function update(Request $request, $id)
    {
        $syllabus = Syllabus::where('faculty_id', Auth::id())->findOrFail($id);

        // Debug: log incoming keys and specific fields to help diagnose why some partials aren't persisting
        try {
            \Log::info('Syllabus::update called', ['syllabus_id' => $id, 'incoming_keys' => array_keys($request->all())]);
            \Log::info('Syllabus::update criteria_data', ['criteria_data' => $request->input('criteria_data')]);
            \Log::info('Syllabus::update criteria_lecture', ['criteria_lecture' => $request->input('criteria_lecture')]);
            \Log::info('Syllabus::update criteria_laboratory', ['criteria_laboratory' => $request->input('criteria_laboratory')]);
            // Debug: log full payload to help diagnose missing fields from the client
            try { \Log::debug('Syllabus::update full_payload', $request->all()); } catch (\Throwable $__e) { /* noop */ }
        } catch (\Throwable $e) {
            // ignore logging failures
        }

        $request->validate(array_merge([
            // criteria_data may arrive as a JSON string (hidden input) or as an array (AJAX)
            'criteria_data' => 'nullable',
            // Assessment Tasks serialized JSON from the AT module
            'assessment_tasks_data' => 'nullable|string',
        ], 
        // optional ILOs payload (same shape as SyllabusIloController expects)
        $request->has('ilos') ? [
            'ilos' => 'array',
            'ilos.*.id' => 'nullable|integer|exists:syllabus_ilos,id',
            'ilos.*.code' => 'required_with:ilos|string',
            'ilos.*.description' => 'required_with:ilos|string|max:1000',
            'ilos.*.position' => 'required_with:ilos|integer',
        ] : []));

        $this->missionVision->syncFromRequest($request, $syllabus);

        $this->courseInfo->syncFromRequest($request, $syllabus);

        $this->criteria->syncFromRequest($request, $syllabus);

    $this->ilos->syncFromRequest($request, $syllabus);

        // Persist course policies via dedicated controller
        $this->coursePolicy->syncFromRequest($request, $syllabus);

        // ILO persistence primarily flows through the live-save endpoint; syncFromRequest keeps
        // this method compatible when the full payload includes an `ilos` array.

        // Delegate assessment tasks persistence to dedicated controller
        $this->assessmentTasks->syncFromRequest($request, $syllabus);

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
    $syllabus = Syllabus::with(['course', 'program', 'courseInfo'])->findOrFail($id);
        $pdf = Pdf::loadView('faculty.syllabus.exports.pdf', compact('syllabus'));
        return $pdf->download('syllabus_' . $syllabus->id . '.pdf');
    }

    public function exportWord($id)
    {
    $syllabus = Syllabus::with(['course', 'program', 'courseInfo', 'faculty'])->findOrFail($id);

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
    $section->addText($syllabus->missionVision?->vision ?? '');
        $section->addTextBreak(1);

        $section->addText('II. MISSION', ['bold' => true, 'underline' => 'single']);
    $section->addText($syllabus->missionVision?->mission ?? '');
        $section->addTextBreak(1);

        $section->addText('III. COURSE INFORMATION', ['bold' => true, 'underline' => 'single']);

        // Build CIS-style variables
        $course = $syllabus->course;
    $program = $syllabus->program;
    $faculty = $syllabus->faculty ?? auth()->user();
    $courseInfo = $syllabus->courseInfo;

    // prefer free-text contact_hours when present, else fall back to numeric lec/lab
    $contactText = trim((string) ($courseInfo?->contact_hours ?? ''));
    if ($contactText !== '') {
        $lec = (int) ($courseInfo?->contact_hours_lec ?? $course->contact_hours_lec ?? 0);
        $lab = (int) ($courseInfo?->contact_hours_lab ?? $course->contact_hours_lab ?? 0);
        $total = $lec + $lab;
        // keep contactText as provided
    } else {
        $lec = (int) ($courseInfo?->contact_hours_lec ?? $course->contact_hours_lec ?? 0);
        $lab = (int) ($courseInfo?->contact_hours_lab ?? $course->contact_hours_lab ?? 0);
        $total = $lec + $lab;
        $contactText = $total ? "{$total} ({$lec} hrs lec; {$lab} hrs lab)" : '-';
    }

    
        $prereqs = $course
            ? ($course->relationLoaded('prerequisites') ? $course->prerequisites : $course->prerequisites()->get())
            : collect();
        $prereqStr = $prereqs->map(function($c){
            $code = trim((string) ($c->code ?? ''));
            $title = trim((string) ($c->title ?? ''));
            return $title ? ($code . ' - ' . $title) : $code;
        })->filter()->values()->implode("\n");

    $courseCategory = $courseInfo?->course_category ?? $course->course_category ?? $course->category ?? $course->type ?? ($program->name ?? '');
        $employeeCode = $courseInfo?->employee_code
            ?? $faculty->employee_code
            ?? $faculty->employee_no
            ?? $faculty->emp_no
            ?? $faculty->code
            ?? $faculty->id_no
            ?? '';
        $designation = trim((string) ($courseInfo?->instructor_designation ?? $faculty->designation ?? ''));
        $facultyDetails = trim(collect([$designation, $courseInfo?->instructor_email ?? $faculty->email])->filter()->implode("\n"));
        $email = trim((string) ($courseInfo?->instructor_email ?? $faculty->email ?? ''));
        $referenceCMO = $courseInfo?->reference_cmo ?? $course->reference_cmo ?? '';
        $datePrepared = $courseInfo?->date_prepared ?? optional($syllabus->created_at)->format('F d, Y') ?: '-';
        $periodOfStudy = $courseInfo?->academic_year ?? $syllabus->academic_year ?? '';
        $revisionNo = $courseInfo?->revision_no ?? $syllabus->revision_no ?? '-';
        $revisionDate = $courseInfo?->revision_date ?? optional($syllabus->revision_date)->format('F d, Y') ?: '-';

        // CIS-style 4-column table (label, value, label, value)
        $table = $section->addTable(['borderSize' => 6, 'cellMargin' => 50]);

        $table->addRow();
        $table->addCell(2200)->addText('Course Title', ['bold' => true]);
        $table->addCell(3800)->addText($course->title ?? '');
        $table->addCell(2200)->addText('Course Code', ['bold' => true]);
        $table->addCell(3800)->addText($course->code ?? '');

        $table->addRow();
        $table->addCell(2200)->addText('Course Category', ['bold' => true]);
        $table->addCell(3800)->addText($courseCategory);
        $table->addCell(2200)->addText('Pre-requisite(s)', ['bold' => true]);
        $table->addCell(3800)->addText($prereqStr);

        $table->addRow();
        $table->addCell(2200)->addText('Semester/Year', ['bold' => true]);
        $table->addCell(3800)->addText(trim(($syllabus->semester ?? '') . (isset($syllabus->year_level) ? ' / ' . $syllabus->year_level : '')));
        $table->addCell(2200)->addText('Credit Hours', ['bold' => true]);
        $table->addCell(3800)->addText("{$total} ({$lec} hrs lec; {$lab} hrs lab)");

        // Row group: Course Instructor label spans 3 rows. Value changes each row.
        // Row 1: Name | Emp No  + Reference CMO
        $table->addRow();
        $table->addCell(2200, ['vMerge' => 'restart'])->addText('Course Instructor', ['bold' => true]);
        $leftValCell = $table->addCell(3800);
        $inline = $leftValCell->addTable(['borderSize' => 0, 'cellMargin' => 50]);
        $inline->addRow();
    $inline->addCell(2800, [
            'borderTopSize' => 0, 'borderBottomSize' => 0,
            'borderLeftSize' => 0, 'borderRightSize' => 0,
    ])->addText($courseInfo?->instructor_name ?? $faculty->name ?? '');
        $inline->addCell(1000, [
            'borderTopSize' => 0, 'borderBottomSize' => 0,
            'borderLeftSize' => 6, 'borderLeftColor' => '000000',
            'borderRightSize' => 0,
        ])->addText($employeeCode);
        $table->addCell(2200)->addText('Reference CMO', ['bold' => true]);
        $table->addCell(3800)->addText($referenceCMO ?: '-');

        // Row 2: Designation | Date Prepared
        $table->addRow();
        $table->addCell(2200, ['vMerge' => 'continue']);
        $leftValCell2 = $table->addCell(3800);
        $inline2 = $leftValCell2->addTable(['borderSize' => 0, 'cellMargin' => 50]);
        // row 2.1: designation
        $inline2->addRow();
        $inline2->addCell(2800, [
            'borderTopSize' => 0, 'borderBottomSize' => 0,
            'borderLeftSize' => 0, 'borderRightSize' => 0,
        ])->addText($designation ?: '-', ['size' => 10]);
        $inline2->addCell(1000, [
            'borderTopSize' => 0, 'borderBottomSize' => 0,
            'borderLeftSize' => 6, 'borderLeftColor' => '000000',
            'borderRightSize' => 0,
        ])->addText('');
        $table->addCell(2200)->addText('Date Prepared', ['bold' => true]);
        $table->addCell(3800)->addText($datePrepared ?: '-');

        // Row 3: Email (with top border to mimic separator) | Revision No.
        $table->addRow();
        $table->addCell(2200, ['vMerge' => 'continue']);
        $leftValCell3 = $table->addCell(3800);
        $inline3 = $leftValCell3->addTable(['borderSize' => 0, 'cellMargin' => 50]);
        $inline3->addRow();
        $inline3->addCell(2800, [
            'borderTopSize' => 0,
            'borderLeftSize' => 0, 'borderRightSize' => 0,
        ])->addText($email ?: '-', ['size' => 10]);
        $inline3->addCell(1000, [
            'borderTopSize' => 0,
            'borderLeftSize' => 6, 'borderLeftColor' => '000000',
        ])->addText('');
        $table->addCell(2200)->addText('Revision No.', ['bold' => true]);
        $table->addCell(3800)->addText($revisionNo ?: '-');

        // Row: Period of Study aligned with Revision Date
        $table->addRow();
        $table->addCell(2200)->addText('Period of Study', ['bold' => true]);
        $table->addCell(3800)->addText($periodOfStudy ?: '-');
        $table->addCell(2200)->addText('Revision Date', ['bold' => true]);
        $table->addCell(3800)->addText($revisionDate ?: '-');

        $filename = 'syllabus_' . $syllabus->id . '.docx';
        $tempPath = storage_path($filename);
        $phpWord->save($tempPath, 'Word2007');

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

}
