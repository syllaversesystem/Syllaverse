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
use App\Models\SyllabusCriteria;
use App\Models\StudentOutcome;
use App\Models\Course;
use App\Models\Program;
use App\Models\SyllabusCourseInfo;
use App\Models\GeneralInformation;
use App\Models\Sdg;
use App\Models\Iga;
use App\Models\SyllabusIga;
use App\Models\Cdio;
use App\Models\SyllabusCdio;
use Illuminate\Support\Facades\Schema;
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
        ]);

    // Diagnostic: log created syllabus id so we can correlate model-created seeding
    try { \Log::info('SyllabusController::store created syllabus', ['syllabus_id' => $syllabus->id, 'faculty_id' => Auth::id()]); } catch (\Throwable $__e) {}

        // persist mission/vision into the dedicated table
        $syllabus->missionVision()->create([
            'mission' => $mission,
            'vision' => $vision,
        ]);

    $course = Course::with(['ilos', 'prerequisites'])->find($request->course_id);
        foreach ($course->ilos as $ilo) {
            SyllabusIlo::create([
                'syllabus_id' => $syllabus->id,
                'code' => $ilo->code,
                'description' => $ilo->description,
            ]);
        }

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

    // Copy relevant course master fields into per-syllabus course info so edits remain local
        // include instructor / employee defaults copied from faculty so edits stay per-syllabus
        $faculty = $syllabus->faculty ?? auth()->user();
        // compute contact hours text (human-readable) like the old UI used to show
        // Prefer parsing any free-text contact hours present on the Course (e.g., "3 hours lab")
        $sourceContactText = trim((string) ($course->contact_hours ?? $course->contact_hours_text ?? ''));

        // default numeric values from course master
        $lec = (int) ($course->contact_hours_lec ?? 0);
        $lab = (int) ($course->contact_hours_lab ?? 0);

        // attempt to parse free-text contact hours (e.g. "2 hours lecture; 3 hours laboratory" or "3 hours lab")
        if ($sourceContactText !== '') {
            $parsed = self::parseContactTextForLecLab($sourceContactText);
            // only override numeric parts if parser found values
            if ($parsed['lec'] !== null) {
                $lec = $parsed['lec'];
            }
            if ($parsed['lab'] !== null) {
                $lab = $parsed['lab'];
            }
            $contactText = $sourceContactText;
        } else {
            $total = $lec + $lab;
            $creditText = $total ? ("{$total} ({$lec} hrs lec; {$lab} hrs lab)") : null;

            // Contact hours text (for syllabus) should be human-readable and derived from lec/lab
            if ($lec && $lab) {
                $contactText = "{$lec} hours lecture; {$lab} hours laboratory";
            } elseif ($lec) {
                $contactText = "{$lec} hours lecture";
            } elseif ($lab) {
                $contactText = "{$lab} hours laboratory";
            } else {
                $contactText = null;
            }
        }

        $courseInfoData = [
            'syllabus_id' => $syllabus->id,
            'course_title' => $course->title ?? null,
            'course_code' => $course->code ?? null,
            'course_category' => $course->course_category ?? $course->category ?? null,
            'course_prerequisites' => $course->relationLoaded('prerequisites') ? $course->prerequisites->map(fn($c)=> ($c->code ? ($c->code . ' - ') : '') . ($c->title ?? ''))->implode("\n") : null,
            'course_description' => $course->description ?? null,
            // credit / contact hours
            'credit_hours_text' => $creditText,
            // copy semester/year/academic year from the newly created syllabus so the per-syllabus row has the same context
            'semester' => $syllabus->semester ?? null,
            'year_level' => $syllabus->year_level ?? null,
            'academic_year' => $syllabus->academic_year ?? null,
            // instructor defaults (copied from faculty/user) so editing syllabus won't mutate users table
            'instructor_name' => $syllabus->instructor ?? ($faculty->name ?? null),
            'employee_code' => $faculty->employee_code ?? $faculty->employee_no ?? $faculty->emp_no ?? $faculty->code ?? $faculty->id_no ?? null,
            'instructor_designation' => $faculty->designation ?? null,
            'instructor_email' => $faculty->email ?? null,
            // reference / revision fields
            'reference_cmo' => $course->reference_cmo ?? null,
            'date_prepared' => optional($syllabus->created_at)->format('F d, Y') ?? null,
            'revision_no' => $syllabus->revision_no ?? null,
            'revision_date' => optional($syllabus->revision_date)->format('F d, Y') ?? null,
            // store the human-readable contact hours; do NOT use credit_hours_text here
            'contact_hours' => $contactText,
            // store the human-readable lec/lab text derived from numeric values or parser
            'contact_hours_lec' => $lec ? (string) ($lec . ' hours lecture') : null,
            'contact_hours_lab' => $lab ? (string) ($lab . ' hours laboratory') : null,
            // criteria fields (lecture + laboratory) -- may be removed below if columns absent
            'criteria_lecture' => $course->criteria_lecture ?? null,
            'criteria_laboratory' => $course->criteria_laboratory ?? null,
        ];

        // If the underlying table no longer has the legacy criteria columns, don't attempt to write them
        try {
            if (! Schema::hasColumn('syllabus_course_infos', 'criteria_lecture')) {
                unset($courseInfoData['criteria_lecture']);
                \Log::info('Syllabus::store removed criteria_lecture from courseInfo create because column missing');
            }
            if (! Schema::hasColumn('syllabus_course_infos', 'criteria_laboratory')) {
                unset($courseInfoData['criteria_laboratory']);
                \Log::info('Syllabus::store removed criteria_laboratory from courseInfo create because column missing');
            }
        } catch (\Throwable $e) {
            // best-effort guard; if Schema check fails, proceed without throwing
            \Log::warning('Syllabus::store schema check for courseInfo failed', ['error' => $e->getMessage()]);
        }

        SyllabusCourseInfo::create($courseInfoData);

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

        return redirect()->route('faculty.syllabi.index')
            ->with('success', 'Syllabus created successfully.');
    }

    public function show($id)
    {
        $syllabus = Syllabus::with([
            'course', 'program', 'faculty', 'ilos', 'sos', 'sdgs', 'courseInfo', 'criteria', 'cdios',
            'tla.ilos:id,code',
            'tla.sos:id,code'
        ])->findOrFail($id);

        $programs = Program::all();
        $courses = Course::all();
        $sdgs = Sdg::all();

        // load mission/vision into defaults from the new relation so views remain unchanged
        $missionVision = $syllabus->missionVision;
        return view('faculty.syllabus.syllabus', [
            'syllabus' => $syllabus,
            'default' => array_merge(
                $syllabus->only([
                    'id', 'title', 'program_id', 'course_id',
                    'academic_year', 'semester', 'year_level',
                    'description', 'instructor', 'contact_hours'
                ]),
                [
                    'mission' => $missionVision?->mission ?? '',
                    'vision' => $missionVision?->vision ?? '',
                ],
                ['sdgs' => $syllabus->sdgs]
            ),
            'programs' => $programs,
            'courses' => $courses,
            'ilos' => $syllabus->ilos,
            'sos' => $syllabus->sos,
            'igas' => $syllabus->igas ?? collect(),
            'cdios' => $syllabus->cdios ?? collect(),
            'sdgs' => $sdgs,
        ]);
    }

    public function update(Request $request, $id)
    {
        $syllabus = Syllabus::where('faculty_id', Auth::id())->findOrFail($id);

        // Debug: log incoming keys and specific fields to help diagnose why some partials aren't persisting
        try {
            \Log::info('Syllabus::update called', ['syllabus_id' => $id, 'incoming_keys' => array_keys($request->all())]);
            \Log::info('Syllabus::update tla_strategies', ['tla_strategies' => $request->input('tla_strategies')]);
            \Log::info('Syllabus::update criteria_data', ['criteria_data' => $request->input('criteria_data')]);
            \Log::info('Syllabus::update criteria_lecture', ['criteria_lecture' => $request->input('criteria_lecture')]);
            \Log::info('Syllabus::update criteria_laboratory', ['criteria_laboratory' => $request->input('criteria_laboratory')]);
            // Debug: log full payload to help diagnose missing fields from the client
            try { \Log::debug('Syllabus::update full_payload', $request->all()); } catch (\Throwable $__e) { /* noop */ }
        } catch (\Throwable $e) {
            // ignore logging failures
        }

        $request->validate(array_merge([
            'mission' => 'required|string',
            'vision' => 'required|string',
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

        // update mission/vision in the dedicated table
        $missionVision = $syllabus->missionVision;
        if ($missionVision) {
            $missionVision->update([
                'mission' => $request->mission,
                'vision' => $request->vision,
            ]);
        } else {
            $syllabus->missionVision()->create([
                'mission' => $request->mission,
                'vision' => $request->vision,
            ]);
        }

        // Persist course-info overrides into syllabus_course_infos
        if ($request->hasAny([
            'course_title','course_code','course_category','course_prerequisites','semester','year_level',
            'credit_hours_text','instructor_name','employee_code','reference_cmo','instructor_designation',
            'date_prepared','instructor_email','revision_no','academic_year','revision_date','course_description',
            'criteria_lecture','criteria_laboratory',
            'contact_hours','contact_hours_lec','contact_hours_lab','tla_strategies'
        ])) {
            $data = $request->only([
                'course_title','course_code','course_category','course_prerequisites','semester','year_level',
                'credit_hours_text','instructor_name','employee_code','reference_cmo','instructor_designation',
                'date_prepared','instructor_email','revision_no','academic_year','revision_date','course_description',
                'criteria_lecture','criteria_laboratory',
                'contact_hours','contact_hours_lec','contact_hours_lab','tla_strategies'
            ]);

            // If the underlying table no longer has the legacy criteria columns, don't attempt to write them
            // (this prevents SQL errors when the columns were dropped during migration)
            try {
                if (! Schema::hasColumn('syllabus_course_infos', 'criteria_lecture')) {
                    unset($data['criteria_lecture']);
                    \Log::info('Syllabus::update removed criteria_lecture from courseInfo update because column missing');
                }
                if (! Schema::hasColumn('syllabus_course_infos', 'criteria_laboratory')) {
                    unset($data['criteria_laboratory']);
                    \Log::info('Syllabus::update removed criteria_laboratory from courseInfo update because column missing');
                }
            } catch (\Throwable $e) {
                // If Schema check fails for any reason, continue without blocking save — it's a best-effort guard
                \Log::warning('Syllabus::update schema check failed', ['error' => $e->getMessage()]);
            }

            // If user updated the free-text contact_hours (e.g. "3 hours lab"), try to parse numeric lec/lab
            if (!empty($data['contact_hours'])) {
                $parsed = self::parseContactTextForLecLab($data['contact_hours']);
                if ($parsed['lec'] !== null) {
                    $data['contact_hours_lec'] = $parsed['lec'];
                }
                if ($parsed['lab'] !== null) {
                    $data['contact_hours_lab'] = $parsed['lab'];
                }
            }

            // If the incoming lec/lab are human-readable text (e.g. "3 hours lecture"), extract numbers for credit_hours_text
            $lecText = $data['contact_hours_lec'] ?? null;
            $labText = $data['contact_hours_lab'] ?? null;
            $lecNum = null; $labNum = null;
            if (!empty($lecText) && !is_numeric($lecText)) {
                preg_match('/(\d+)/', $lecText, $m);
                if (!empty($m[1])) $lecNum = (int) $m[1];
            } elseif (is_numeric($lecText)) {
                $lecNum = (int) $lecText;
            }
            if (!empty($labText) && !is_numeric($labText)) {
                preg_match('/(\d+)/', $labText, $m2);
                if (!empty($m2[1])) $labNum = (int) $m2[1];
            } elseif (is_numeric($labText)) {
                $labNum = (int) $labText;
            }

            if ($lecNum !== null || $labNum !== null) {
                $lecNum = $lecNum ?: 0;
                $labNum = $labNum ?: 0;
                $total = $lecNum + $labNum;
                $data['credit_hours_text'] = $total ? "{$total} ({$lecNum} hrs lec; {$labNum} hrs lab)" : null;
            }

            $courseInfo = $syllabus->courseInfo;
            if ($courseInfo) {
                $courseInfo->update($data);
            } else {
                $data['syllabus_id'] = $syllabus->id;
                SyllabusCourseInfo::create($data);
            }
        }

        // If the frontend included an `ilos` array in the form (e.g., course-info partial now sends ILOs),
        // apply the same delete/upsert flow used by SyllabusIloController::update()
        if ($request->has('ilos') && is_array($request->ilos)) {
            $incomingIds = collect($request->ilos)->pluck('id')->filter();
            $existingIds = SyllabusIlo::where('syllabus_id', $syllabus->id)->pluck('id');

            // delete removed ILOs
            $toDelete = $existingIds->diff($incomingIds);
            if ($toDelete->isNotEmpty()) {
                SyllabusIlo::whereIn('id', $toDelete)->delete();
            }

            // upsert incoming ILOs
            foreach ($request->ilos as $iloData) {
                SyllabusIlo::updateOrCreate(
                    ['id' => $iloData['id'] ?? null],
                    [
                        'syllabus_id' => $syllabus->id,
                        'code' => $iloData['code'],
                        'description' => $iloData['description'],
                        'position' => $iloData['position'] ?? 0,
                    ]
                );
            }
        }

        // Handle criteria_data: expect an array of sections, each { key, heading, value: [{description, percent}] }
        if ($request->has('criteria_data')) {
            $incoming = $request->input('criteria_data');
            if (!is_array($incoming)) {
                // if it's a JSON string (from hidden input), attempt to decode
                $decoded = json_decode((string) $incoming, true);
                $incoming = is_array($decoded) ? $decoded : [];
            }

            // Delete existing criteria for this syllabus
            $syllabus->criteria()->delete();

            foreach ($incoming as $index => $section) {
                if (!is_array($section)) continue;
                $key = $section['key'] ?? null;
                $heading = $section['heading'] ?? null;
                $values = $section['value'] ?? [];
                // normalize values: expect array of {description, percent}
                $normalized = [];
                if (is_array($values)) {
                    foreach ($values as $v) {
                        if (!is_array($v)) continue;
                        $desc = trim($v['description'] ?? '');
                        $pct = trim($v['percent'] ?? '');
                        if ($desc === '' && $pct === '') continue;
                        $normalized[] = ['description' => $desc, 'percent' => $pct];
                    }
                }

                // only persist if there's meaningful data
                if ($key || $heading || count($normalized) > 0) {
                    SyllabusCriteria::create([
                        'syllabus_id' => $syllabus->id,
                        'key' => $key ?? ('section_' . $index),
                        'heading' => $heading,
                        'section' => $heading, // keep heading as the section label
                        'value' => $normalized,
                        'position' => $index,
                    ]);
                }
            }
        }

        // Persist the serialized Assessment Tasks payload (hidden textarea) into the syllabus table
        if ($request->has('assessment_tasks_data')) {
            try {
                $syllabus->assessment_tasks_data = $request->input('assessment_tasks_data');
                $syllabus->save();
                \Log::info('Syllabus::update persisted assessment_tasks_data', ['syllabus_id' => $syllabus->id]);
            } catch (\Throwable $e) {
                \Log::warning('Syllabus::update failed to persist assessment_tasks_data', ['error' => $e->getMessage(), 'syllabus_id' => $syllabus->id]);
            }
        }

    // criteria module removed; legacy `criteria_` fields in courseInfo are updated above from request

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

    /**
     * Save serialized Assessment Tasks rows for a syllabus (AJAX endpoint).
     */
    public function saveAssessmentTasks(Request $request, $syllabus)
    {
        // find syllabus scoped to current faculty and log request for debugging
        try {
            \Log::info('saveAssessmentTasks called', ['syllabus_param' => $syllabus, 'faculty_id' => Auth::id(), 'incoming' => $request->all()]);
        } catch (\Throwable $__e) { /* noop */ }

        $sy = Syllabus::where('faculty_id', Auth::id())->findOrFail($syllabus);

        $data = $request->input('rows');
        if (!is_array($data)) {
            // try decode if JSON string
            $decoded = json_decode((string) $data, true);
            $data = is_array($decoded) ? $decoded : [];
        }

        try {
            // delete existing assessment tasks for this syllabus and re-insert (simple approach)
            $sy->assessmentTasks()->delete();

            $created = 0;
            foreach ($data as $pos => $row) {
                try {
                    // log raw c/p/a values for debugging
                    \Log::debug('saveAssessmentTasks incoming row raw', ['pos' => $pos, 'c' => $row['c'] ?? null, 'p' => $row['p'] ?? null, 'a' => $row['a'] ?? null, 'row' => $row]);
                } catch (\Throwable $__logex) { /* noop */ }
                // normalize fields
                $section = $row['section'] ?? null;
                $code = $row['code'] ?? null;
                $task = $row['task'] ?? null;
                $ird = $row['ird'] ?? $row['ird'] ?? null;
                $percent = isset($row['percent']) ? (float) trim(str_replace('%','', $row['percent'])) : null;
                $iloFlags = $row['iloFlags'] ?? $row['ilo_flags'] ?? [];
                // CPA: store the raw textual value when provided, else null.
                // This allows storing marks like 'x', '✓', '3', or short notes in the CPA columns.
                $c = isset($row['c']) && (string) $row['c'] !== '' ? (string) $row['c'] : null;
                $p = isset($row['p']) && (string) $row['p'] !== '' ? (string) $row['p'] : null;
                $a = isset($row['a']) && (string) $row['a'] !== '' ? (string) $row['a'] : null;

                $sy->assessmentTasks()->create([
                    'section' => $section,
                    'code' => $code,
                    'task' => $task,
                    'ird' => $ird,
                    'percent' => $percent,
                    'ilo_flags' => $iloFlags,
                    'c' => $c,
                    'p' => $p,
                    'a' => $a,
                    'position' => $pos,
                ]);
                $created++;
            }

            \Log::info('saveAssessmentTasks persisted rows', ['syllabus_id' => $sy->id, 'created' => $created]);

            // Also persist the raw serialized AT payload into the syllabus record so the AT module
            // can render textual CPA values on next load (this avoids needing a schema change).
            try {
                $rawJson = json_encode($data);
                $sy->assessment_tasks_data = $rawJson;
                $sy->save();
                \Log::info('saveAssessmentTasks persisted syllabus.assessment_tasks_data', ['syllabus_id' => $sy->id]);
            } catch (\Throwable $__ex) {
                \Log::warning('saveAssessmentTasks failed to persist assessment_tasks_data on syllabus', ['error' => $__ex->getMessage(), 'syllabus_id' => $sy->id]);
            }

            return response()->json(['success' => true, 'count' => $created, 'saved' => true]);
        } catch (\Throwable $e) {
            \Log::error('saveAssessmentTasks failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'syllabus_id' => $sy->id ?? $syllabus]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
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

    /**
     * Parse a free-text contact hours string to find numeric lecture and lab values.
     * Returns array with keys 'lec' and 'lab' each either int or null.
     * Examples:
     *  - "3 hours lab" => ['lec' => null, 'lab' => 3]
     *  - "2 hrs lecture; 3 hrs laboratory" => ['lec' => 2, 'lab' => 3]
     */
    private static function parseContactTextForLecLab(string $text): array
    {
        $lower = strtolower($text);
        $lec = null;
        $lab = null;

        // match patterns like '3 hours lecture', '2 hrs lec', '3 lab', etc.
        if (preg_match_all('/(\d{1,2})\s*(?:hours|hrs|hr)?\s*(lecture|lectures|lec|l)?/i', $lower, $m)) {
            foreach ($m[1] as $i => $num) {
                $unit = trim($m[2][$i] ?? '');
                if ($unit === '' || strpos($unit, 'l') === 0) {
                    // ambiguous or lec-like -> treat as lecture when 'lec' or 'lecture'
                    if ($lec === null) {
                        $lec = (int) $num;
                    }
                }
            }
        }

        // lab patterns
        if (preg_match_all('/(\d{1,2})\s*(?:hours|hrs|hr)?\s*(laboratory|laboratories|lab|l)?/i', $lower, $m2)) {
            foreach ($m2[1] as $i => $num) {
                $unit = trim($m2[2][$i] ?? '');
                if ($unit !== '') {
                    $lab = (int) $num;
                }
            }
        }

        // also handle shorthand like '3 lab' or '2 lec' by catching 'lab' and 'lec' tokens with numbers
        if ($lec === null || $lab === null) {
            if (preg_match_all('/(\d{1,2})\s*(lec|lab)/i', $lower, $m3)) {
                foreach ($m3[1] as $i => $num) {
                    $tok = strtolower($m3[2][$i] ?? '');
                    if ($tok === 'lec' && $lec === null) {
                        $lec = (int) $num;
                    } elseif ($tok === 'lab' && $lab === null) {
                        $lab = (int) $num;
                    }
                }
            }
        }

        // final fallback: if string contains 'lab' but only one number present, assign that to lab
        if (($lec === null && $lab === null)) {
            if (preg_match('/(\d{1,2})/', $lower, $m4)) {
                // ambiguous single number; if text contains 'lab' assign to lab else lec
                $num = (int) $m4[1];
                if (strpos($lower, 'lab') !== false) {
                    $lab = $num;
                } else {
                    $lec = $num;
                }
            }
        }

        return ['lec' => $lec, 'lab' => $lab];
    }
}
