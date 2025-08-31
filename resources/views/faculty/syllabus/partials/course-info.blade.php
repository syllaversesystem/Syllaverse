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

  // human-readable contact hours text
  if ($lec && $lab) {
    $contactText = "{$lec} hours lecture; {$lab} hours laboratory";
  } elseif ($lec) {
    $contactText = "{$lec} hours lecture";
  } elseif ($lab) {
    $contactText = "{$lab} hours laboratory";
  } else {
    $contactText = '-';
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
@endphp

<table class="table table-bordered mb-4 cis-table">
  <colgroup>
    <col style="width: 16%">
    <col style="width: 34%">
    <col style="width: 16%">
    <col style="width: 34%">
  </colgroup>
  <tbody>
    <tr>
      <th class="align-top text-start cis-label">Course Title
        <span id="unsaved-course_title" class="unsaved-pill d-none">Unsaved</span>
      </th>
      <td>
  <input type="text" name="course_title" class="cis-input"
         value="{{ old('course_title', $local?->course_title ?? $course->title ?? '') }}"
         data-original="{{ $local?->course_title ?? $course->title ?? '' }}"
         placeholder="Enter course title">
      </td>
      <th class="align-top text-start cis-label">Course Code
        <span id="unsaved-course_code" class="unsaved-pill d-none">Unsaved</span>
      </th>
      <td>
  <input type="text" name="course_code" class="cis-input"
         value="{{ old('course_code', $local?->course_code ?? $course->code ?? '') }}"
         data-original="{{ $local?->course_code ?? $course->code ?? '' }}"
         placeholder="Enter course code">
      </td>
    </tr>

    <tr>
      <th class="align-top text-start cis-label">Course Category
        <span id="unsaved-course_category" class="unsaved-pill d-none">Unsaved</span>
      </th>
      <td>
  <input type="text" name="course_category" class="cis-input"
         value="{{ old('course_category', $local?->course_category ?? $courseCategory) }}"
         data-original="{{ $local?->course_category ?? $courseCategory }}"
         placeholder="e.g., Professional Elective: Business Analytics Track">
      </td>
      <th class="align-top text-start cis-label">Pre-requisite(s)
        <span id="unsaved-course_prerequisites" class="unsaved-pill d-none">Unsaved</span>
      </th>
      <td>
  <textarea name="course_prerequisites" class="cis-field autosize"
      data-original="{{ $local?->course_prerequisites ?? $prereqStr }}"
      placeholder="IT 221 - Information Mngt & IT&#10;IT 222 - Advanced Database Management Systems">{{ old('course_prerequisites', $local?->course_prerequisites ?? $prereqStr) }}</textarea>
      </td>
    </tr>

    <tr>
      <th class="align-top text-start cis-label">Semester/Year
        <span id="unsaved-semester" class="unsaved-pill d-none">Unsaved</span>
        <span id="unsaved-year_level" class="unsaved-pill d-none">Unsaved</span>
      </th>
      <td>
        <div class="d-flex align-items-center gap-2">
     <input type="text" name="semester" class="cis-input"
       value="{{ old('semester', $local?->semester ?? $syllabus->semester ?? '') }}"
       data-original="{{ $local?->semester ?? $syllabus->semester ?? '' }}"
       placeholder="First Semester">
          <span>/</span>
     <input type="text" name="year_level" class="cis-input"
       value="{{ old('year_level', $local?->year_level ?? $syllabus->year_level ?? '') }}"
       data-original="{{ $local?->year_level ?? $syllabus->year_level ?? '' }}"
       placeholder="Third Year">
        </div>
      </td>
      <th class="align-top text-start cis-label">Credit Hours
        <span id="unsaved-credit_hours_text" class="unsaved-pill d-none">Unsaved</span>
      </th>
      <td>
  <input id="credit_hours_text" type="text" name="credit_hours_text" class="cis-input"
         value="{{ old('credit_hours_text', $local?->credit_hours_text ?? ($total ? ($total . ' (' . $lec . ' hrs lec; ' . $lab . ' hrs lab)') : '-')) }}"
         data-original="{{ $local?->credit_hours_text ?? ($total ? ($total . ' (' . $lec . ' hrs lec; ' . $lab . ' hrs lab)') : '-') }}"
               placeholder="e.g., 5 (2 hrs lec; 3 hrs lab)" aria-live="polite">
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
            <td>
    <input type="text" name="instructor_name" class="cis-input"
      value="{{ old('instructor_name', $local?->instructor_name ?? $nameDisplay) }}"
      data-original="{{ $local?->instructor_name ?? $nameDisplay }}"
      placeholder="Enter instructor's name" style="text-transform:none;">
            </td>
            <td>
              <label class="visually-hidden" for="employee_code">Employee No.</label>
    <input type="text" name="employee_code" class="cis-input"
      value="{{ old('employee_code', $local?->employee_code ?? $employeeCode) }}"
      data-original="{{ $local?->employee_code ?? $employeeCode }}"
      placeholder="Emp No.">
            </td>
          </tr>
        </table>
      </td>
      <th class="align-top text-start cis-label">Reference CMO
        <span id="unsaved-reference_cmo" class="unsaved-pill d-none">Unsaved</span>
      </th>
      <td>
        <input type="text" name="reference_cmo" class="cis-input"
               value="{{ old('reference_cmo', $referenceCMO ?: '') }}"
               data-original="{{ $referenceCMO ?: '' }}"
               placeholder="e.g., 25 Series of 2015, 12 Series of 2013">
      </td>
    </tr>
    <tr>
      <td>
        <table class="cis-inline-split">
          <colgroup>
            <col style="width: 75%">
            <col style="width: 25%">
          </colgroup>
          <tr class="no-vline">
            <td>
              <div class="text-muted" style="font-size: 12px;">
                <label class="visually-hidden" for="instructor_designation">Designation</label>
      <input type="text" name="instructor_designation" class="cis-input"
        value="{{ old('instructor_designation', $local?->instructor_designation ?? $designation) }}"
        data-original="{{ $local?->instructor_designation ?? $designation }}"
        placeholder="Designation (e.g., Assistant Professor)">
              </div>
            </td>
            <td>&nbsp;</td>
          </tr>
        </table>
      </td>
      <th class="align-top text-start cis-label">Date Prepared
        <span id="unsaved-date_prepared" class="unsaved-pill d-none">Unsaved</span>
      </th>
      <td>
  <input type="text" name="date_prepared" class="cis-input"
         value="{{ old('date_prepared', $local?->date_prepared ?? $datePrepared ?: '') }}"
         data-original="{{ $local?->date_prepared ?? $datePrepared ?: '' }}"
         placeholder="July 26, 2024">
      </td>
    </tr>
    <tr>
      <td>
        <table class="cis-inline-split">
          <colgroup>
            <col style="width: 75%">
            <col style="width: 25%">
          </colgroup>
          <tr class="no-vline">
            <td>
              <div class="text-muted" style="font-size: 12px;">
                <label class="visually-hidden" for="instructor_email">Email</label>
      <input type="email" name="instructor_email" class="cis-input"
        value="{{ old('instructor_email', $local?->instructor_email ?? $faculty->email ?? '') }}"
        data-original="{{ $local?->instructor_email ?? $faculty->email ?? '' }}"
                       placeholder="email@domain.tld">
              </div>
            </td>
            <td>&nbsp;</td>
          </tr>
        </table>
      </td>
      <th class="align-top text-start cis-label">Revision No.
        <span id="unsaved-revision_no" class="unsaved-pill d-none">Unsaved</span>
      </th>
      <td>
  <input type="text" name="revision_no" class="cis-input"
         value="{{ old('revision_no', $local?->revision_no ?? $revisionNo ?: '') }}"
         data-original="{{ $local?->revision_no ?? $revisionNo ?: '' }}"
         placeholder="-">
      </td>
    </tr>
    {{-- ░░░ END: Course Instructor ░░░ --}}

    <tr>
      <th class="align-top text-start cis-label">Period of Study
        <span id="unsaved-academic_year" class="unsaved-pill d-none">Unsaved</span>
      </th>
      <td>
  <input type="text" name="academic_year" class="cis-input"
         value="{{ old('academic_year', $local?->academic_year ?? $periodOfStudy ?: '') }}"
         data-original="{{ $local?->academic_year ?? $periodOfStudy ?: '' }}"
         placeholder="AY 2024 - 2025">
      </td>
      <th class="align-top text-start cis-label">Revision Date
        <span id="unsaved-revision_date" class="unsaved-pill d-none">Unsaved</span>
      </th>
      <td>
  <input type="text" name="revision_date" class="cis-input"
         value="{{ old('revision_date', $local?->revision_date ?? $revisionDate ?: '') }}"
         data-original="{{ $local?->revision_date ?? $revisionDate ?: '' }}"
         placeholder="-">
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
          placeholder="Enter a concise course rationale and description based on the official CIS."
        >{{ old('course_description', $local?->course_description ?? $courseDescription) }}</textarea>
      </td>
    </tr>
    {{-- ░░░ END: Course Rationale & Description ░░░ --}}

    {{-- Contact Hours UI: two-column lecture/lab and combined text below --}}
<tr>
  <th class="align-top text-start cis-label">Contact Hours
    <span id="unsaved-contact_hours" class="unsaved-pill d-none">Unsaved</span>
  </th>
  <td colspan="3">
    <div class="d-flex flex-column gap-2">
      <div>
        <label class="visually-hidden" for="contact_hours_lec">Lecture Hours</label>
        <input id="contact_hours_lec" type="text" name="contact_hours_lec" class="cis-input w-100"
               value="{{ old('contact_hours_lec', $local?->contact_hours_lec ?? ($lec ? ($lec . ' hours lecture') : '')) }}"
               data-original="{{ $local?->contact_hours_lec ?? ($lec ? ($lec . ' hours lecture') : '') }}"
               placeholder="e.g., 3 hours lecture">
      </div>

      <div>
        <label class="visually-hidden" for="contact_hours_lab">Lab Hours</label>
        <input id="contact_hours_lab" type="text" name="contact_hours_lab" class="cis-input w-100"
               value="{{ old('contact_hours_lab', $local?->contact_hours_lab ?? ($lab ? ($lab . ' hours laboratory') : '')) }}"
               data-original="{{ $local?->contact_hours_lab ?? ($lab ? ($lab . ' hours laboratory') : '') }}"
               placeholder="e.g., 3 hours laboratory">
      </div>
    </div>
  </td>
</tr>
</table>
</td>
</tr>
{{-- contact hours are now shown as lec/lab columns with combined text; editing still available via the text field --}}
