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
  $local = $syllabus->courseInfo ?? null; // per-syllabus saved data

  // Only render from database (courseInfo), use empty string as fallback
  $contactHoursRaw = trim((string) ($local?->contact_hours ?? ''));
  $contactHoursOld = old('contact_hours');
  if ($contactHoursOld !== null) {
    $contactHoursValue = preg_replace('/;\s*/', "\n", (string) $contactHoursOld);
  } else {
    $contactHoursValue = $contactHoursRaw !== '' ? preg_replace('/;\s*/', "\n", $contactHoursRaw) : '';
  }
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
      </th>
      <td>
  <textarea name="course_title"
      class="cis-textarea cis-field autosize"
    placeholder="-"
      rows="1"
      style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('course_title', $local?->course_title ?? '') }}</textarea>
      </td>
      <th class="align-top text-start cis-label">Course Code
      </th>
      <td>
  <textarea name="course_code"
      class="cis-textarea cis-field autosize"
    placeholder="-"
      rows="1"
      style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('course_code', $local?->course_code ?? '') }}</textarea>
      </td>
    </tr>

    <tr>
      <th class="align-top text-start cis-label">Course Category
      </th>
      <td>
  <textarea name="course_category"
                  class="cis-textarea cis-field autosize"
      placeholder="-"
                  rows="1"
                  style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('course_category', $local?->course_category ?? '') }}</textarea>
      </td>
      <th class="align-top text-start cis-label">Pre-requisite(s)
      </th>
    <td>
  <textarea name="course_prerequisites" class="cis-textarea cis-field autosize"
    placeholder="-"
    rows="1"
    style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('course_prerequisites', $local?->course_prerequisites ?? '') }}</textarea>
    </td>
    </tr>

    <tr>
      <th class="align-top text-start cis-label">Semester/Year
      </th>
      <td>
        <div class="d-flex gap-2 align-items-stretch instructor-split" style="width:100%;">
  <textarea name="semester"
            class="cis-textarea cis-field autosize flex-grow-1"
            placeholder="-"
            rows="1"
            style="flex:1 1 0;min-width:0;width:auto !important;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('semester', $local?->semester ?? '') }}</textarea>
          <div class="employee-code-col" style="flex:0 0 160px;min-width:140px;">
  <textarea name="year_level"
            class="cis-textarea cis-field autosize"
            placeholder="-"
            rows="1"
            style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('year_level', $local?->year_level ?? '') }}</textarea>
          </div>
        </div>
      </td>
      <th class="align-top text-start cis-label">Credit Hours
      </th>
      <td>
  <textarea id="credit_hours_text" name="credit_hours_text" class="cis-textarea cis-field autosize"
            placeholder="-" aria-live="polite" rows="1"
            style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('credit_hours_text', $local?->credit_hours_text ?? '') }}</textarea>
      </td>
    </tr>

    {{-- ░░░ START: Course Instructor (3 rows) ░░░ --}}
    <tr>
      <th class="align-top text-start cis-label" rowspan="3">Course Instructor
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
          placeholder="-" rows="1"
          style="flex:1 1 0;min-width:0;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('instructor_name', $local?->instructor_name ?? '') }}</textarea>
        <div class="employee-code-col" style="flex:0 0 160px;min-width:140px;">
          <label class="visually-hidden" for="employee_code">Employee No.</label>
          <textarea name="employee_code" class="cis-textarea cis-field autosize instructor-field"
            placeholder="-" rows="1"
            style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('employee_code', $local?->employee_code ?? '') }}</textarea>
        </div>
              </div>
            </td>
          </tr>
        </table>
      </td>
      <th class="align-top text-start cis-label">Reference CMO
      </th>
      <td>
     <textarea name="reference_cmo" class="cis-textarea cis-field autosize"
                 placeholder="-" rows="1"
                 style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('reference_cmo', $local?->reference_cmo ?? '') }}</textarea>
      </td>
    </tr>
    <tr>
      <td class="designation-cell">
        <label class="visually-hidden" for="instructor_designation">Designation</label>
        <textarea name="instructor_designation" class="cis-textarea cis-field autosize instructor-field"
          placeholder="-" rows="1"
          style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('instructor_designation', $local?->instructor_designation ?? '') }}</textarea>
      </td>
      <th class="align-top text-start cis-label">Date Prepared
      </th>
      <td>
  <textarea name="date_prepared" class="cis-textarea cis-field autosize"
         placeholder="-" rows="1"
         style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('date_prepared', $local?->date_prepared ?? '') }}</textarea>
      </td>
    </tr>
    <tr>
      <td>
        <label class="visually-hidden" for="instructor_email">Email</label>
        <textarea name="instructor_email" class="cis-textarea cis-field autosize instructor-field"
          placeholder="-" rows="1"
          style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('instructor_email', $local?->instructor_email ?? '') }}</textarea>
      </td>
      <th class="align-top text-start cis-label">Revision No.
      </th>
      <td>
  <textarea name="revision_no" class="cis-textarea cis-field autosize"
         placeholder="-" rows="1"
         style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('revision_no', $local?->revision_no ?? '') }}</textarea>
      </td>
    </tr>
    {{-- ░░░ END: Course Instructor ░░░ --}}

    <tr>
      <th class="align-top text-start cis-label">Period of Study
      </th>
      <td>
  <textarea name="academic_year" class="cis-textarea cis-field autosize"
         placeholder="-" rows="1"
         style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('academic_year', $local?->academic_year ?? '') }}</textarea>
      </td>
      <th class="align-top text-start cis-label">Revision Date
      </th>
      <td>
  <textarea name="revision_date" class="cis-textarea cis-field autosize"
         placeholder="-" rows="1"
         style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ old('revision_date', $local?->revision_date ?? '') }}</textarea>
      </td>
    </tr>

    {{-- ░░░ START: Course Rationale & Description (Editable) ░░░ --}}
    <tr>
      <th class="align-top text-start cis-label">
        Course Rationale<br>and Description
      </th>
      <td colspan="3">
        <textarea
          name="course_description"
          class="cis-textarea cis-field autosize"
          placeholder="-"
          rows="1"
          style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;"
        >{{ old('course_description', $local?->course_description ?? '') }}</textarea>
      </td>
    </tr>
    {{-- ░░░ END: Course Rationale & Description ░░░ --}}

    {{-- Contact Hours UI: single textarea (autosize) --}}
<tr>
  <th class="align-top text-start cis-label">Contact Hours
  </th>
  <td colspan="3">
  <textarea name="contact_hours" class="cis-textarea cis-field autosize"
        placeholder="-" rows="1"
        style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">{{ $contactHoursValue }}</textarea>
  </td>
</tr>
</table>
<!-- Local Course Info save button removed; toolbar Save handles persistence -->
</td>
</tr>
{{-- contact hours are now shown as lec/lab columns with combined text; editing still available via the text field --}}

  {{-- (Removed) Inline Criteria for Assessment to avoid duplication; use main page include. --}}

@push('scripts')
<script>
  (function(){
    function sanitize(val){
      if (val == null) return '-';
      const s = String(val).trim();
      return s.length ? s : '-';
    }
    function getVal(name){
      const el = document.querySelector(`[name="${name}"]`);
      return sanitize(el ? el.value : '-');
    }
    const fieldPairs = [
      ['Course Title','course_title'],
      ['Course Code','course_code'],
      ['Course Category','course_category'],
      ['Pre-requisite(s)','course_prerequisites'],
      ['Semester','semester'],
      ['Year Level','year_level'],
      ['Credit Hours','credit_hours_text'],
      ['Instructor Name','instructor_name'],
      ['Employee No.','employee_code'],
      ['Reference CMO','reference_cmo'],
      ['Instructor Designation','instructor_designation'],
      ['Date Prepared','date_prepared'],
      ['Instructor Email','instructor_email'],
      ['Revision No.','revision_no'],
      ['Period of Study','academic_year'],
      ['Revision Date','revision_date'],
      ['Course Rationale and Description','course_description'],
      ['Contact Hours','contact_hours']
    ];
    function buildCourseInfoBlock(){
      const lines = [];
      lines.push('PARTIAL_BEGIN:course_info');
      lines.push('TITLE: Course Information');
      lines.push('COLUMNS: Field | Value');
      for (const [label, name] of fieldPairs){
        const val = getVal(name);
        lines.push(`ROW: ${label} | ${val}`);
      }
      lines.push('PARTIAL_END:course_info');
      return lines.join('\n');
    }
    function updateRealtime(){
      const ci = buildCourseInfoBlock();
      const existing = window._svRealtimeContext || '';
      const others = existing
        .split(/\n{2,}/)
        .filter(s => s && !/PARTIAL_BEGIN:course_info[\s\S]*PARTIAL_END:course_info/.test(s))
        .join('\n\n');
      const merged = others ? (others + '\n\n' + ci) : ci;
      window._svRealtimeContext = merged;
    }
    // Bind input/change for all related fields
    function bind(){
      fieldPairs.forEach(([label, name]) => {
        const el = document.querySelector(`[name="${name}"]`);
        if (el){
          ['input','change'].forEach(evt => el.addEventListener(evt, updateRealtime, {capture:true}));
        }
      });
    }
    document.addEventListener('DOMContentLoaded', function(){ bind(); updateRealtime(); });
    window.addEventListener('load', function(){ bind(); updateRealtime(); });
    // Initial run
    updateRealtime();
  })();
</script>
@endpush
