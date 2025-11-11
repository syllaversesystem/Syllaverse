{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/course-info.blade.php
* Description: CIS-faithful Course Information (editable, WYSIWYG-like but print-clean)
-------------------------------------------------------------------------------
📜 Log:
[2025-08-31] UI clean-up – made "Course Rationale and Description" editable; added editable numeric inputs for Contact Hours (lec/lab); preserved CIS look and unsaved-pill pattern.
[2025-08-31] CIS visual pass – tightened padding, removed helper lines, stacked contact-hour lines, kept 4-column widths (16/34/16/34) to mirror printed CIS.
[2025-08-31] Contact Hours – CIS-faithful editable lines with dynamic dash when 0/0; hooks for unsaved logic and auto "Credit Hours" recompute.
-------------------------------------------------------------------------------
--}}

@php
  $course  = $syllabus->course ?? null;
  $program = $syllabus->program ?? null;
  $faculty = $syllabus->faculty ?? auth()->user();

  $local = $syllabus->courseInfo ?? null; // per-syllabus overrides

  $lec = (int) ($local?->contact_hours_lec ?? $course?->contact_hours_lec ?? 0);
  $lab = (int) ($local?->contact_hours_lab ?? $course?->contact_hours_lab ?? 0);
  $total = $lec + $lab;

  // human-readable contact hours text (newline between entries when both exist)
  if ($lec && $lab) {
    $contactText = "{$lec} hours lecture\n{$lab} hours laboratory";
  } elseif ($lec) {
    $contactText = "{$lec} hours lecture";
  } elseif ($lab) {
    $contactText = "{$lab} hours laboratory";
  } else {
    $contactText = '-';
  }

  // Normalize any saved/old contact_hours that used semicolons into newlines for display
  $contactHoursRaw = trim((string) ($local?->contact_hours ?? ''));
  $contactHoursDisplay = $contactHoursRaw !== '' ? preg_replace('/;\s*/', "\n", $contactHoursRaw) : $contactText;
  $contactHoursOld = old('contact_hours');
  if ($contactHoursOld !== null) {
    $contactHoursValue = preg_replace('/;\s*/', "\n", (string) $contactHoursOld);
  } else {
    $contactHoursValue = $contactHoursDisplay;
  }

  $prereqs = collect();
  if ($course) {
    $prereqs = $course->relationLoaded('prerequisites') ? $course->prerequisites : $course->prerequisites()->get();
  }
  $prereqStr = $prereqs->map(function($c){
      $code = trim((string) ($c->code ?? ''));
      $title = trim((string) ($c->title ?? ''));
      return $title ? ($code . ' - ' . $title) : $code;
    })
    ->filter()
    ->values()
    ->implode("\n");

  $courseCategory = $local?->course_category ?? $course->category ?? $course->type ?? $program?->name ?? '';

  $employeeCode = $local?->employee_code
    ?? $faculty->employee_code
    ?? $faculty->employee_no
    ?? $faculty->emp_no
    ?? $faculty->code
    ?? $faculty->id_no
    ?? '';

  $designation = trim((string) ($faculty->designation ?? ''));
  $nameRaw = $local?->instructor_name ?: $syllabus->instructor ?: ($faculty->name ?? '');
  $nameDisplay = '';
  if ($nameRaw) {
    try { $nameDisplay = mb_convert_case(mb_strtolower($nameRaw), MB_CASE_TITLE, 'UTF-8'); }
    catch (\Throwable $e) { $nameDisplay = ucwords(strtolower($nameRaw)); }
  }

  $referenceCMO = $local?->reference_cmo ?? $course->reference_cmo ?? '';
  $datePrepared = $local?->date_prepared ?? optional($syllabus->created_at)->format('F d, Y');
  $periodOfStudy = $local?->academic_year ?? $syllabus->academic_year ?? '';
  $revisionNo = $local?->revision_no ?? $syllabus->revision_no ?? '-';
  $revisionDate = $local?->revision_date ?? optional($syllabus->revision_date)->format('F d, Y') ?? '-';
  $courseDescription = trim((string) ($local?->course_description ?? $course->description ?? ''));
  // compute criteria percentage totals for display in headers (e.g., "Lecture (40%)")
  $lecturePercentSum = 0;
  if (!empty(trim((string) ($local?->criteria_lecture ?? '')))) {
    $lines = preg_split('/\r?\n/', trim((string) $local?->criteria_lecture));
    foreach ($lines as $l) {
      if (preg_match('/(\d+)%/', $l, $m)) { $lecturePercentSum += (int) $m[1]; }
    }
  }
  $labPercentSum = 0;
  if (!empty(trim((string) ($local?->criteria_laboratory ?? '')))) {
    $lines = preg_split('/\r?\n/', trim((string) $local?->criteria_laboratory));
    foreach ($lines as $l) {
      if (preg_match('/(\d+)%/', $l, $m)) { $labPercentSum += (int) $m[1]; }
    }
  }
@endphp

<table class="table table-bordered mb-4 cis-table">
  <colgroup>
    <col style="width: 16%">
    <col style="width: 34%">
    <col style="width: 16%">
    <col style="width: 34%">
  </colgroup>
  <tbody>
  <!-- styles moved to resources/css/faculty/syllabus.css (.cis-input and criteria rules) -->
    <tr>
      <th class="align-top text-start cis-label">Course Title
        <span id="unsaved-course_title" class="unsaved-pill d-none">Unsaved</span>
      </th>
      <td>
  <textarea name="course_title"
      class="cis-textarea cis-field autosize"
      data-original="{{ $local?->course_title ?? $course->title ?? '' }}"
    placeholder="-"
      rows="1"
      style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('course_title', $local?->course_title ?? $course->title ?? '') }}</textarea>
      </td>
      <th class="align-top text-start cis-label">Course Code
        <span id="unsaved-course_code" class="unsaved-pill d-none">Unsaved</span>
      </th>
      <td>
  <textarea name="course_code"
      class="cis-textarea cis-field autosize"
      data-original="{{ $local?->course_code ?? $course->code ?? '' }}"
    placeholder="-"
      rows="1"
      style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('course_code', $local?->course_code ?? $course->code ?? '') }}</textarea>
      </td>
    </tr>

    <tr>
      <th class="align-top text-start cis-label">Course Category
        <span id="unsaved-course_category" class="unsaved-pill d-none">Unsaved</span>
      </th>
      <td>
  <textarea name="course_category"
                  class="cis-textarea cis-field autosize"
                  data-original="{{ $local?->course_category ?? $courseCategory }}"
      placeholder="-"
                  rows="1"
                  style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('course_category', $local?->course_category ?? $courseCategory) }}</textarea>
      </td>
      <th class="align-top text-start cis-label">Pre-requisite(s)
        <span id="unsaved-course_prerequisites" class="unsaved-pill d-none">Unsaved</span>
      </th>
    <td>
  <textarea name="course_prerequisites" class="cis-textarea cis-field autosize"
    data-original="{{ $local?->course_prerequisites ?? $prereqStr }}"
    placeholder="-"
    rows="1"
    style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('course_prerequisites', $local?->course_prerequisites ?? $prereqStr) }}</textarea>
    </td>
    </tr>

    <tr>
      <th class="align-top text-start cis-label">Semester/Year
        <span id="unsaved-semester" class="unsaved-pill d-none">Unsaved</span>
        <span id="unsaved-year_level" class="unsaved-pill d-none">Unsaved</span>
      </th>
      <td>
        <div class="d-flex gap-2 align-items-stretch instructor-split" style="width:100%;">
  <textarea name="semester"
            class="cis-textarea cis-field autosize flex-grow-1"
            data-original="{{ $local?->semester ?? $syllabus->semester ?? '' }}"
            placeholder="-"
            rows="1"
            style="flex:1 1 0;min-width:0;width:auto !important;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('semester', $local?->semester ?? $syllabus->semester ?? '') }}</textarea>
          <div class="employee-code-col" style="flex:0 0 160px;min-width:140px;">
  <textarea name="year_level"
            class="cis-textarea cis-field autosize"
            data-original="{{ $local?->year_level ?? $syllabus->year_level ?? '' }}"
            placeholder="-"
            rows="1"
            style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('year_level', $local?->year_level ?? $syllabus->year_level ?? '') }}</textarea>
          </div>
        </div>
      </td>
      <th class="align-top text-start cis-label">Credit Hours
        <span id="unsaved-credit_hours_text" class="unsaved-pill d-none">Unsaved</span>
      </th>
      <td>
  <textarea id="credit_hours_text" name="credit_hours_text" class="cis-textarea cis-field autosize"
            data-original="{{ $local?->credit_hours_text ?? ($total ? ($total . ' (' . $lec . ' hrs lec; ' . $lab . ' hrs lab)') : '-') }}"
            placeholder="-" aria-live="polite" rows="1"
            style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('credit_hours_text', $local?->credit_hours_text ?? ($total ? ($total . ' (' . $lec . ' hrs lec; ' . $lab . ' hrs lab)') : '-')) }}</textarea>
      </td>
    </tr>

    {{-- ░░░ START: Course Instructor (3 rows) ░░░ --}}
    <tr>
      <th class="align-top text-start cis-label" rowspan="3">Course Instructor
        <span id="unsaved-instructor_name" class="unsaved-pill d-none">Unsaved</span>
      </th>
      <td>
        <table class="cis-inline-split">
          <colgroup>
            <col style="width: 75%">
            <col style="width: 25%">
          </colgroup>
          <tr>
            <td colspan="2">
              <div class="d-flex gap-2 align-items-stretch instructor-split" style="width:100%;">
        <textarea name="instructor_name"
          class="cis-textarea cis-field autosize instructor-field flex-grow-1"
          data-original="{{ $local?->instructor_name ?? $nameDisplay }}"
          placeholder="-" rows="1"
          style="flex:1 1 0;min-width:0;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('instructor_name', $local?->instructor_name ?? $nameDisplay) }}</textarea>
        <div class="employee-code-col" style="flex:0 0 160px;min-width:140px;">
          <label class="visually-hidden" for="employee_code">Employee No.</label>
          <textarea name="employee_code" class="cis-textarea cis-field autosize instructor-field"
            data-original="{{ $local?->employee_code ?? $employeeCode }}"
            placeholder="-" rows="1"
            style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('employee_code', $local?->employee_code ?? $employeeCode) }}</textarea>
        </div>
              </div>
            </td>
          </tr>
        </table>
      </td>
      <th class="align-top text-start cis-label">Reference CMO
        <span id="unsaved-reference_cmo" class="unsaved-pill d-none">Unsaved</span>
      </th>
      <td>
     <textarea name="reference_cmo" class="cis-textarea cis-field autosize"
                 data-original="{{ $referenceCMO ?: '' }}" placeholder="-" rows="1"
                 style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('reference_cmo', $referenceCMO ?: '') }}</textarea>
      </td>
    </tr>
    <tr>
      <td class="designation-cell">
        <label class="visually-hidden" for="instructor_designation">Designation</label>
        <textarea name="instructor_designation" class="cis-textarea cis-field autosize instructor-field"
          data-original="{{ $local?->instructor_designation ?? $designation }}"
          placeholder="-" rows="1"
          style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('instructor_designation', $local?->instructor_designation ?? $designation) }}</textarea>
      </td>
      <th class="align-top text-start cis-label">Date Prepared
        <span id="unsaved-date_prepared" class="unsaved-pill d-none">Unsaved</span>
      </th>
      <td>
  <textarea name="date_prepared" class="cis-textarea cis-field autosize"
         data-original="{{ $local?->date_prepared ?? $datePrepared ?: '' }}" placeholder="-" rows="1"
         style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('date_prepared', $local?->date_prepared ?? $datePrepared ?: '') }}</textarea>
      </td>
    </tr>
    <tr>
      <td>
        <label class="visually-hidden" for="instructor_email">Email</label>
        <textarea name="instructor_email" class="cis-textarea cis-field autosize instructor-field"
          data-original="{{ $local?->instructor_email ?? $faculty->email ?? '' }}" placeholder="-" rows="1"
          style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('instructor_email', $local?->instructor_email ?? $faculty->email ?? '') }}</textarea>
      </td>
      <th class="align-top text-start cis-label">Revision No.
        <span id="unsaved-revision_no" class="unsaved-pill d-none">Unsaved</span>
      </th>
      <td>
  <textarea name="revision_no" class="cis-textarea cis-field autosize"
         data-original="{{ $local?->revision_no ?? $revisionNo ?: '' }}" placeholder="-" rows="1"
         style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('revision_no', $local?->revision_no ?? $revisionNo ?: '') }}</textarea>
      </td>
    </tr>
    {{-- ░░░ END: Course Instructor ░░░ --}}

    <tr>
      <th class="align-top text-start cis-label">Period of Study
        <span id="unsaved-academic_year" class="unsaved-pill d-none">Unsaved</span>
      </th>
      <td>
  <textarea name="academic_year" class="cis-textarea cis-field autosize"
         data-original="{{ $local?->academic_year ?? $periodOfStudy ?: '' }}" placeholder="-" rows="1"
         style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('academic_year', $local?->academic_year ?? $periodOfStudy ?: '') }}</textarea>
      </td>
      <th class="align-top text-start cis-label">Revision Date
        <span id="unsaved-revision_date" class="unsaved-pill d-none">Unsaved</span>
      </th>
      <td>
  <textarea name="revision_date" class="cis-textarea cis-field autosize"
         data-original="{{ $local?->revision_date ?? $revisionDate ?: '' }}" placeholder="-" rows="1"
         style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('revision_date', $local?->revision_date ?? $revisionDate ?: '') }}</textarea>
      </td>
    </tr>

    {{-- ░░░ START: Course Rationale & Description (Editable) ░░░ --}}
    <tr>
      <th class="align-top text-start cis-label">
        Course Rationale<br>and Description
        <span id="unsaved-course_description" class="unsaved-pill d-none">Unsaved</span>
      </th>
      <td colspan="3">
        <textarea
          name="course_description"
          class="cis-textarea cis-field autosize"
          data-original="{{ $local?->course_description ?? $courseDescription }}"
          placeholder="-"
          rows="1"
          style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;"
        >{{ old('course_description', $local?->course_description ?? $courseDescription) }}</textarea>
      </td>
    </tr>
    {{-- ░░░ END: Course Rationale & Description ░░░ --}}

    {{-- Contact Hours UI: single textarea (autosize) --}}
<tr>
  <th class="align-top text-start cis-label">Contact Hours
    <span id="unsaved-contact_hours" class="unsaved-pill d-none">Unsaved</span>
  </th>
  <td colspan="3">
  <textarea name="contact_hours" class="cis-textarea cis-field autosize"
        data-original="{{ $contactHoursDisplay }}"
        placeholder="-" rows="1"
        style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ $contactHoursValue }}</textarea>
  </td>
</tr>
</table>
</td>
</tr>
{{-- contact hours are now shown as lec/lab columns with combined text; editing still available via the text field --}}

  {{-- Criteria for Assessment partial (editable inline) --}}
  @include('faculty.syllabus.partials.criteria-assessment')
