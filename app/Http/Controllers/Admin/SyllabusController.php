<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Syllabus;
use App\Models\Program;
use App\Models\Course;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;

class SyllabusController extends Controller
{
    public function index()
    {
        // Show only syllabi that belong to the currently authenticated admin user
        // (stored in syllabus.faculty_id). This limits admin view to their own syllabi.
        $adminId = \Illuminate\Support\Facades\Auth::guard('admin')->id();
        $syllabi = Syllabus::with('course', 'program', 'faculty')
            ->where('faculty_id', $adminId)
            ->latest()
            ->get();

        $programs = Program::all();
        $courses = Course::all();

        return view('admin.syllabus.index', compact('syllabi', 'programs', 'courses'));
    }

    public function create()
    {
        $programs = Program::all();
        $courses = Course::all();
        $faculties = \App\Models\User::where('role', 'faculty')->get();
        return view('admin.syllabus.create', compact('programs', 'courses', 'faculties'));
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
            'faculty_id' => 'nullable|exists:users,id',
        ]);

        $facultyId = $request->input('faculty_id') ?: auth()->id();

        $mission = \App\Models\GeneralInformation::where('section', 'mission')->first()?->content ?? '';
        $vision  = \App\Models\GeneralInformation::where('section', 'vision')->first()?->content ?? '';

        $syllabus = Syllabus::create([
            'faculty_id' => $facultyId,
            'program_id' => $request->program_id,
            'course_id' => $request->course_id,
            'title' => $request->title,
            'academic_year' => $request->academic_year,
            'semester' => $request->semester,
            'year_level' => $request->year_level,
        ]);

        try { \Log::info('Admin created syllabus', ['syllabus_id' => $syllabus->id, 'faculty_id' => $facultyId]); } catch (\Throwable $__e) {}

        // persist mission/vision
        $syllabus->missionVision()->create(['mission' => $mission, 'vision' => $vision]);

        // copy course ilos
        $course = Course::with(['ilos', 'prerequisites'])->find($request->course_id);
        foreach ($course->ilos as $ilo) {
            \App\Models\SyllabusIlo::create(['syllabus_id' => $syllabus->id, 'code' => $ilo->code, 'description' => $ilo->description]);
        }

        // copy master IGAs if present
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('igas')) {
                $masterIgas = \App\Models\Iga::ordered()->get();
                $pos = 1;
                foreach ($masterIgas as $m) {
                    \App\Models\SyllabusIga::create(['syllabus_id' => $syllabus->id, 'code' => $m->code ?? (\App\Models\Iga::makeCodeFromPosition($pos)), 'description' => $m->description ?? $m->title ?? null, 'position' => $pos++]);
                }
            }
        } catch (\Throwable $e) { \Log::warning('Failed copying master IGAs for admin created syllabus', ['error' => $e->getMessage()]); }

        return redirect()->route('admin.syllabi.show', $syllabus->id)->with('success', 'Syllabus created successfully.');
    }

    public function show($id)
    {
        $syllabus = Syllabus::with([
            'course', 'program', 'faculty', 'ilos', 'sos', 'sdgs', 'courseInfo', 'criteria', 'cdios',
            'tla.ilos:id,code', 'tla.sos:id,code', 'assessmentMappings'
        ])->findOrFail($id);

        $programs = Program::all();
        $courses = Course::all();
        $sdgs = \App\Models\Sdg::all();

        $missionVision = $syllabus->missionVision;

        $coursePolicies = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('syllabus_course_policies')) {
                $coursePolicies = \App\Models\SyllabusCoursePolicy::where('syllabus_id', $syllabus->id)
                    ->orderBy('position')
                    ->get();
            }
        } catch (\Throwable $e) {
            \Log::warning('Admin\SyllabusController::show failed to load course policies', ['error' => $e->getMessage(), 'syllabus_id' => $syllabus->id]);
        }

        // Reuse faculty view for the detailed syllabus editor/renderer
        return view('faculty.syllabus.syllabus', [
            'syllabus' => $syllabus,
            'default' => array_merge(
                $syllabus->only([
                    'id', 'title', 'program_id', 'course_id',
                    'academic_year', 'semester', 'year_level',
                    'description', 'instructor', 'contact_hours'
                ]),
                ['mission' => $missionVision?->mission ?? '', 'vision' => $missionVision?->vision ?? ''],
                ['sdgs' => $syllabus->sdgs]
            ),
            'programs' => $programs,
            'courses' => $courses,
            'ilos' => $syllabus->ilos,
            'sos' => $syllabus->sos,
            'igas' => $syllabus->igas ?? collect(),
            'cdios' => $syllabus->cdios ?? collect(),
            'coursePolicies' => $coursePolicies,
            'sdgs' => $sdgs,
            // allow the faculty view to adapt to admin layout and route names
            'layout' => 'layouts.admin',
            'routePrefix' => 'admin.syllabi',
        ]);
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

        // Minimal Word export: reuse faculty controller formatting logic where possible
        $course = $syllabus->course;
        $program = $syllabus->program;
        $faculty = $syllabus->faculty ?? auth()->user();
        $courseInfo = $syllabus->courseInfo;

        $contactText = trim((string) ($courseInfo?->contact_hours ?? '')) ?: '-';
        $prereqs = $course ? ($course->relationLoaded('prerequisites') ? $course->prerequisites : $course->prerequisites()->get()) : collect();
        $prereqStr = $prereqs->map(fn($c) => (trim((string) ($c->code ?? '')) . ' - ' . trim((string) ($c->title ?? ''))))->filter()->values()->implode("\n");

        $table = $section->addTable(['borderSize' => 6, 'cellMargin' => 50]);
        $table->addRow();
        $table->addCell(2200)->addText('Course Title', ['bold' => true]);
        $table->addCell(3800)->addText($course->title ?? '');
        $table->addCell(2200)->addText('Course Code', ['bold' => true]);
        $table->addCell(3800)->addText($course->code ?? '');

        $filename = 'syllabus_' . $syllabus->id . '.docx';
        $tempPath = storage_path($filename);
        $phpWord->save($tempPath, 'Word2007');

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

    public function update(Request $request, $id)
    {
        $syllabus = Syllabus::findOrFail($id);

        $request->validate(array_merge([
            'mission' => 'required|string',
            'vision' => 'required|string',
            'criteria_data' => 'nullable',
            'assessment_tasks_data' => 'nullable|string',
        ], $request->has('ilos') ? [
            'ilos' => 'array',
            'ilos.*.id' => 'nullable|integer|exists:syllabus_ilos,id',
            'ilos.*.code' => 'required_with:ilos|string',
            'ilos.*.description' => 'required_with:ilos|string|max:1000',
            'ilos.*.position' => 'required_with:ilos|integer',
        ] : []));

        // mission/vision
        $missionVision = $syllabus->missionVision;
        if ($missionVision) {
            $missionVision->update(['mission' => $request->mission, 'vision' => $request->vision]);
        } else {
            $syllabus->missionVision()->create(['mission' => $request->mission, 'vision' => $request->vision]);
        }

        // CourseInfo updates (best-effort like faculty controller)
        if ($request->hasAny(['course_title','course_code','course_category','course_prerequisites','semester','year_level','credit_hours_text','instructor_name','employee_code','reference_cmo','instructor_designation','date_prepared','instructor_email','revision_no','academic_year','revision_date','course_description','criteria_lecture','criteria_laboratory','contact_hours','contact_hours_lec','contact_hours_lab','tla_strategies'])) {
            $data = $request->only(['course_title','course_code','course_category','course_prerequisites','semester','year_level','credit_hours_text','instructor_name','employee_code','reference_cmo','instructor_designation','date_prepared','instructor_email','revision_no','academic_year','revision_date','course_description','criteria_lecture','criteria_laboratory','contact_hours','contact_hours_lec','contact_hours_lab','tla_strategies']);

            try {
                if (! \Illuminate\Support\Facades\Schema::hasColumn('syllabus_course_infos', 'criteria_lecture')) unset($data['criteria_lecture']);
                if (! \Illuminate\Support\Facades\Schema::hasColumn('syllabus_course_infos', 'criteria_laboratory')) unset($data['criteria_laboratory']);
            } catch (\Throwable $e) { }

            $courseInfo = $syllabus->courseInfo;
            if ($courseInfo) $courseInfo->update($data); else { $data['syllabus_id'] = $syllabus->id; \App\Models\SyllabusCourseInfo::create($data); }
        }

        // ilos upsert
        if ($request->has('ilos') && is_array($request->ilos)) {
            $incomingIds = collect($request->ilos)->pluck('id')->filter();
            $existingIds = \App\Models\SyllabusIlo::where('syllabus_id', $syllabus->id)->pluck('id');
            $toDelete = $existingIds->diff($incomingIds);
            if ($toDelete->isNotEmpty()) \App\Models\SyllabusIlo::whereIn('id', $toDelete)->delete();
            foreach ($request->ilos as $iloData) {
                \App\Models\SyllabusIlo::updateOrCreate(['id' => $iloData['id'] ?? null], ['syllabus_id' => $syllabus->id, 'code' => $iloData['code'], 'description' => $iloData['description'], 'position' => $iloData['position'] ?? 0]);
            }
        }

        // criteria_data
        if ($request->has('criteria_data')) {
            $incoming = $request->input('criteria_data');
            if (!is_array($incoming)) { $decoded = json_decode((string)$incoming, true); $incoming = is_array($decoded) ? $decoded : []; }
            $syllabus->criteria()->delete();
            foreach ($incoming as $index => $section) {
                if (!is_array($section)) continue;
                $key = $section['key'] ?? null; $heading = $section['heading'] ?? null; $values = $section['value'] ?? [];
                $normalized = [];
                if (is_array($values)) foreach ($values as $v) { if (!is_array($v)) continue; $desc = trim($v['description'] ?? ''); $pct = trim($v['percent'] ?? ''); if ($desc === '' && $pct === '') continue; $normalized[] = ['description' => $desc, 'percent' => $pct]; }
                if ($key || $heading || count($normalized) > 0) {
                    \App\Models\SyllabusCriteria::create(['syllabus_id' => $syllabus->id, 'key' => $key ?? ('section_' . $index), 'heading' => $heading, 'section' => $heading, 'value' => $normalized, 'position' => $index]);
                }
            }
        }

        if ($request->has('assessment_tasks_data')) {
            try { $syllabus->assessment_tasks_data = $request->input('assessment_tasks_data'); $syllabus->save(); } catch (\Throwable $e) { \Log::warning('Admin\SyllabusController::update failed to persist AT data', ['error' => $e->getMessage()]); }
        }

        return redirect()->route('admin.syllabi.show', $syllabus->id)->with('success', 'Syllabus updated successfully.');
    }

    public function saveAssessmentTasks(Request $request, $syllabus)
    {
        try { \Log::info('Admin saveAssessmentTasks called', ['syllabus_param' => $syllabus, 'incoming' => $request->all()]); } catch (\Throwable $__e) {}
        $sy = Syllabus::findOrFail($syllabus);
        $data = $request->input('rows');
        if (!is_array($data)) { $decoded = json_decode((string)$data, true); $data = is_array($decoded) ? $decoded : []; }
        try {
            $sy->assessmentTasks()->delete();
            $created = 0;
            foreach ($data as $pos => $row) {
                $section = $row['section'] ?? null; $code = $row['code'] ?? null; $task = $row['task'] ?? null; $ird = $row['ird'] ?? null; $percent = isset($row['percent']) ? (float) trim(str_replace('%','', $row['percent'])) : null; $iloFlags = $row['iloFlags'] ?? $row['ilo_flags'] ?? [];
                $c = isset($row['c']) && (string)$row['c'] !== '' ? (string)$row['c'] : null; $p = isset($row['p']) && (string)$row['p'] !== '' ? (string)$row['p'] : null; $a = isset($row['a']) && (string)$row['a'] !== '' ? (string)$row['a'] : null;
                $sy->assessmentTasks()->create(['section' => $section, 'code' => $code, 'task' => $task, 'ird' => $ird, 'percent' => $percent, 'ilo_flags' => $iloFlags, 'c' => $c, 'p' => $p, 'a' => $a, 'position' => $pos]);
                $created++;
            }
            try { $rawJson = json_encode($data); $sy->assessment_tasks_data = $rawJson; $sy->save(); } catch (\Throwable $__ex) { \Log::warning('Admin saveAssessmentTasks failed to persist assessment_tasks_data', ['error' => $__ex->getMessage()]); }
            return response()->json(['success' => true, 'count' => $created, 'saved' => true]);
        } catch (\Throwable $e) { \Log::error('Admin saveAssessmentTasks failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'syllabus_id' => $sy->id ?? $syllabus]); return response()->json(['success' => false, 'error' => $e->getMessage()], 500); }
    }

    public function saveAssessmentMappings(Request $request, $syllabus)
    {
        try { \Log::info('Admin saveAssessmentMappings called', ['syllabus' => $syllabus, 'incoming' => $request->all()]); } catch (\Throwable $__e) {}
        $sy = Syllabus::findOrFail($syllabus);
        $data = $request->input('mappings');
        if (!is_array($data)) { $decoded = json_decode((string)$data, true); $data = is_array($decoded) ? $decoded : []; }
        try {
            $sy->assessmentMappings()->delete();
            $created = 0;
            foreach ($data as $pos => $m) {
                $name = $m['name'] ?? null; $weekMarks = $m['week_marks'] ?? ($m['weeks'] ?? []);
                $sy->assessmentMappings()->create(['name' => $name, 'week_marks' => is_array($weekMarks) ? $weekMarks : json_decode((string)$weekMarks, true) ?? [], 'position' => $pos]);
                $created++;
            }
            return response()->json(['success' => true, 'created' => $created]);
        } catch (\Throwable $e) { \Log::error('Admin saveAssessmentMappings failed', ['error' => $e->getMessage()]); return response()->json(['success' => false, 'error' => $e->getMessage()], 500); }
    }

    public function destroy($id)
    {
        $syllabus = Syllabus::findOrFail($id);
        $syllabus->delete();
        return redirect()->route('admin.syllabi.index')->with('success', 'Syllabus deleted successfully.');
    }
}
