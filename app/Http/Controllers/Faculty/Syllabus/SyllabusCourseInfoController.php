<?php

namespace App\Http\Controllers\Faculty\Syllabus;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Syllabus;
use App\Models\SyllabusCourseInfo;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SyllabusCourseInfoController extends Controller
{
    /**
     * Seed per-syllabus course info from the master course record.
     */
    public function seedFromCourse(Syllabus $syllabus, Course $course, ?Authenticatable $faculty = null): void
    {
        $faculty ??= $syllabus->faculty ?? Auth::user();

        $payload = $this->buildSeedPayload($syllabus, $course, $faculty);

        $this->pruneLegacyColumns($payload);

        SyllabusCourseInfo::create($payload);
    }

    /**
     * Persist course-info edits coming from the main syllabus form.
     */
    public function syncFromRequest(Request $request, Syllabus $syllabus): void
    {
        if (! $request->hasAny($this->courseInfoKeys())) {
            return;
        }

        $payload = $request->only($this->courseInfoKeys());

        $payload = $this->normalizePayload($payload);

        $this->pruneLegacyColumns($payload);

        $courseInfo = $syllabus->courseInfo;
        if ($courseInfo) {
            $courseInfo->update($payload);
        } else {
            $payload['syllabus_id'] = $syllabus->id;
            SyllabusCourseInfo::create($payload);
        }
    }

    /**
     * Dedicated save endpoint for course info partial.
     */
    public function save(Request $request, $syllabusId)
    {
        try {
            $syllabus = Syllabus::where('faculty_id', Auth::id())->findOrFail($syllabusId);

            $payload = $request->only($this->courseInfoKeys());

            $payload = $this->normalizePayload($payload);

            $this->pruneLegacyColumns($payload);

            $courseInfo = $syllabus->courseInfo;
            if ($courseInfo) {
                $courseInfo->update($payload);
            } else {
                $payload['syllabus_id'] = $syllabus->id;
                SyllabusCourseInfo::create($payload);
            }

            return response()->json([
                'success' => true,
                'message' => 'Course info saved successfully',
                'data' => $courseInfo ?? SyllabusCourseInfo::where('syllabus_id', $syllabus->id)->first(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to save course info', [
                'syllabus_id' => $syllabusId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save course info: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Build the initial payload from the master course and faculty record.
     */
    protected function buildSeedPayload(Syllabus $syllabus, Course $course, ?Authenticatable $faculty = null): array
    {
        $sourceContactText = trim((string) ($course->contact_hours ?? $course->contact_hours_text ?? ''));

        $lec = (int) ($course->contact_hours_lec ?? 0);
        $lab = (int) ($course->contact_hours_lab ?? 0);
        $creditText = null;
        $contactText = null;

        if ($sourceContactText !== '') {
            $parsed = $this->parseContactTextForLecLab($sourceContactText);
            if ($parsed['lec'] !== null) {
                $lec = $parsed['lec'];
            }
            if ($parsed['lab'] !== null) {
                $lab = $parsed['lab'];
            }
            $contactText = $sourceContactText;
        } else {
            $total = $lec + $lab;
            $contactText = $this->formatContactText($lec, $lab);
            $creditText = $total ? ("{$total} ({$lec} hrs lec; {$lab} hrs lab)") : null;
        }

        $faculty ??= Auth::user();

        $courseInfoData = [
            'syllabus_id' => $syllabus->id,
            'course_title' => $course->title ?? null,
            'course_code' => $course->code ?? null,
            'course_category' => $course->course_category ?? $course->category ?? null,
            'course_prerequisites' => $course->relationLoaded('prerequisites')
                ? $course->prerequisites->map(fn ($c) => ($c->code ? ($c->code . ' - ') : '') . ($c->title ?? ''))->implode("\n")
                : null,
            'course_description' => $course->description ?? null,
            'credit_hours_text' => $creditText ?? null,
            'semester' => $syllabus->semester ?? null,
            'year_level' => $syllabus->year_level ?? null,
            'academic_year' => $syllabus->academic_year ?? null,
            'instructor_name' => $syllabus->instructor ?? ($faculty->name ?? null),
            'employee_code' => $faculty->employee_code ?? $faculty->employee_no ?? $faculty->emp_no ?? $faculty->code ?? $faculty->id_no ?? null,
            'instructor_designation' => $faculty->designation ?? null,
            'instructor_email' => $faculty->email ?? null,
            'reference_cmo' => $course->reference_cmo ?? null,
            'date_prepared' => optional($syllabus->created_at)->format('F d, Y') ?? null,
            'revision_no' => $syllabus->revision_no ?? null,
            'revision_date' => optional($syllabus->revision_date)->format('F d, Y') ?? null,
            'contact_hours' => $contactText,
            'contact_hours_lec' => $lec ? (string) ($lec . ' hours lecture') : null,
            'contact_hours_lab' => $lab ? (string) ($lab . ' hours laboratory') : null,
            'criteria_lecture' => $course->criteria_lecture ?? null,
            'criteria_laboratory' => $course->criteria_laboratory ?? null,
        ];

        return $courseInfoData;
    }

    /**
     * Normalize payload coming from the request before persisting.
     */
    protected function normalizePayload(array $payload): array
    {
        if (! empty($payload['contact_hours'])) {
            $parsed = $this->parseContactTextForLecLab((string) $payload['contact_hours']);
            if ($parsed['lec'] !== null) {
                $payload['contact_hours_lec'] = $parsed['lec'];
            }
            if ($parsed['lab'] !== null) {
                $payload['contact_hours_lab'] = $parsed['lab'];
            }
        }

        $lecText = $payload['contact_hours_lec'] ?? null;
        $labText = $payload['contact_hours_lab'] ?? null;

        $lecNum = $this->extractNumber($lecText);
        $labNum = $this->extractNumber($labText);

        if ($lecNum !== null || $labNum !== null) {
            $lecNum = $lecNum ?: 0;
            $labNum = $labNum ?: 0;
            $total = $lecNum + $labNum;
            $payload['credit_hours_text'] = $total ? "{$total} ({$lecNum} hrs lec; {$labNum} hrs lab)" : null;
        }

        return $payload;
    }

    /**
     * Remove legacy keys when the schema no longer contains those columns.
     */
    protected function pruneLegacyColumns(array &$payload): void
    {
        try {
            if (! Schema::hasColumn('syllabus_course_infos', 'criteria_lecture')) {
                unset($payload['criteria_lecture']);
                Log::info('CourseInfoController removed criteria_lecture because column missing');
            }
            if (! Schema::hasColumn('syllabus_course_infos', 'criteria_laboratory')) {
                unset($payload['criteria_laboratory']);
                Log::info('CourseInfoController removed criteria_laboratory because column missing');
            }
        } catch (\Throwable $e) {
            Log::warning('CourseInfoController schema check failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Parse a free-text contact hours string to find numeric lecture and lab values.
     */
    protected function parseContactTextForLecLab(string $text): array
    {
        $lower = strtolower($text);
        $lec = null;
        $lab = null;

        if (preg_match_all('/(\d{1,2})\s*(?:hours|hrs|hr)?\s*(lecture|lectures|lec|l)?/i', $lower, $matches)) {
            foreach ($matches[1] as $index => $num) {
                $unit = trim($matches[2][$index] ?? '');
                if ($unit === '' || strpos($unit, 'l') === 0) {
                    if ($lec === null) {
                        $lec = (int) $num;
                    }
                }
            }
        }

        if (preg_match_all('/(\d{1,2})\s*(?:hours|hrs|hr)?\s*(laboratory|laboratories|lab|l)?/i', $lower, $labMatches)) {
            foreach ($labMatches[1] as $index => $num) {
                $unit = trim($labMatches[2][$index] ?? '');
                if ($unit !== '') {
                    $lab = (int) $num;
                }
            }
        }

        if ($lec === null || $lab === null) {
            if (preg_match_all('/(\d{1,2})\s*(lec|lab)/i', $lower, $shortMatches)) {
                foreach ($shortMatches[1] as $index => $num) {
                    $token = strtolower($shortMatches[2][$index] ?? '');
                    if ($token === 'lec' && $lec === null) {
                        $lec = (int) $num;
                    } elseif ($token === 'lab' && $lab === null) {
                        $lab = (int) $num;
                    }
                }
            }
        }

        if ($lec === null && $lab === null) {
            if (preg_match('/(\d{1,2})/', $lower, $single)) {
                $num = (int) $single[1];
                if (strpos($lower, 'lab') !== false) {
                    $lab = $num;
                } else {
                    $lec = $num;
                }
            }
        }

        return ['lec' => $lec, 'lab' => $lab];
    }

    protected function formatContactText(int $lec, int $lab): ?string
    {
        if ($lec && $lab) {
            return "{$lec} hours lecture; {$lab} hours laboratory";
        }
        if ($lec) {
            return "{$lec} hours lecture";
        }
        if ($lab) {
            return "{$lab} hours laboratory";
        }

        return null;
    }

    protected function extractNumber($value): ?int
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        if (is_string($value) && preg_match('/(\d+)/', $value, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * The keys we expect for course info updates.
     */
    protected function courseInfoKeys(): array
    {
        return [
            'course_title', 'course_code', 'course_category', 'course_prerequisites', 'semester', 'year_level',
            'credit_hours_text', 'instructor_name', 'employee_code', 'reference_cmo', 'instructor_designation',
            'date_prepared', 'instructor_email', 'revision_no', 'academic_year', 'revision_date', 'course_description',
            'criteria_lecture', 'criteria_laboratory', 'contact_hours', 'contact_hours_lec', 'contact_hours_lab',
            'tla_strategies',
        ];
    }
}
