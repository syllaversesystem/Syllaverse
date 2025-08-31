{{-- 
File: resources/views/faculty/syllabus/exports/pdf.blade.php
Description: Printable PDF layout for exporting a syllabus (Syllaverse – CIS style)
--}}

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <style>
    body {
      font-family: Georgia, serif;
      font-size: 12px;
      line-height: 1.6;
      color: #000;
    }
    .header {
      text-align: center;
      margin-bottom: 10px;
    }
    .header .title {
      font-size: 14px;
      font-weight: bold;
    }
    .header .sub {
      font-size: 13px;
    }
    .section {
      margin-top: 20px;
    }
    .label {
      font-weight: bold;
      text-transform: uppercase;
      font-size: 13px;
      margin-bottom: 5px;
    }
    .text-block {
      border: 1px solid #999;
      padding: 10px;
      min-height: 80px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    .table td, .table th {
      border: 1px solid #999;
      padding: 6px;
      vertical-align: top;
    }
    .table th {
      text-align: left;
      width: 20%;
    }
  </style>
</head>
<body>

  {{-- HEADER --}}
  <div class="header">
    <div class="title">BATANGAS STATE UNIVERSITY</div>
    <div class="sub">The National Engineering University</div>
    <div class="sub">ARASOF–Nasugbu Campus</div>
    <div class="sub">COURSE INFORMATION SYLLABUS (CIS)</div>
  </div>

  {{-- SECTION I & II --}}
  <div class="section">
    <div class="label">I. Vision</div>
    <div class="text-block">
      {!! nl2br(e($syllabus->missionVision?->vision ?? '')) !!}
    </div>
  </div>

  <div class="section">
    <div class="label">II. Mission</div>
    <div class="text-block">
      {!! nl2br(e($syllabus->missionVision?->mission ?? '')) !!}
    </div>
  </div>

  {{-- SECTION III - CIS Course Information --}}
  @php
    $course  = $syllabus->course ?? null;
    $program = $syllabus->program ?? null;
    $faculty = $syllabus->faculty ?? auth()->user();

  $contactText = trim((string) ($syllabus->courseInfo?->contact_hours ?? ''));
  if ($contactText !== '') {
    $lec = 0; $lab = 0; $total = 0;
  } else {
    $lec = (int) ($course?->contact_hours_lec ?? 0);
    $lab = (int) ($course?->contact_hours_lab ?? 0);
    $total = $lec + $lab;
    $contactText = $total ? "{$total} ({$lec} hrs lec; {$lab} hrs lab)" : '-';
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

    $courseCategory = $course->category ?? $course->type ?? $program?->name ?? '';

    $employeeCode = $faculty->employee_code
      ?? $faculty->employee_no
      ?? $faculty->emp_no
      ?? $faculty->code
      ?? $faculty->id_no
      ?? '';

    $designation = trim((string) ($faculty->designation ?? ''));
    $facultyDetails = trim(collect([$designation, $faculty->email])->filter()->implode("\n"));

    $referenceCMO = $course->reference_cmo ?? '';
    $datePrepared = optional($syllabus->created_at)->format('F d, Y');
    $periodOfStudy = $syllabus->academic_year ?? '';
    $revisionNo = $syllabus->revision_no ?? '-';
    $revisionDate = optional($syllabus->revision_date)->format('F d, Y') ?? '-';
  @endphp

  <div class="section">
    <div class="label">III. Course Information</div>
    <table class="table" style="table-layout: fixed; width: 100%;">
      <colgroup>
        <col style="width: 18%">
        <col style="width: 32%">
        <col style="width: 18%">
        <col style="width: 32%">
      </colgroup>
      <tr>
        <th>Course Title</th>
        <td>{{ $course->title ?? '' }}</td>
        <th>Course Code</th>
        <td>{{ $course->code ?? '' }}</td>
      </tr>
      <tr>
        <th>Course Category</th>
        <td>{{ $courseCategory }}</td>
        <th>Pre-requisite(s)</th>
        <td>{!! nl2br(e($prereqStr)) !!}</td>
      </tr>
      <tr>
        <th>Semester/Year</th>
        <td>{{ trim(($syllabus->semester ?? '') . (isset($syllabus->year_level) ? ' / ' . $syllabus->year_level : '')) }}</td>
        <th>Credit Hours</th>
        <td>{{ $total }} ({{ $lec }} hrs lec; {{ $lab }} hrs lab)</td>
      </tr>
      <tr>
        <th rowspan="3">Course Instructor</th>
        <td>
          <table style="width:100%; border-collapse:collapse; table-layout:fixed;">
            <colgroup>
              <col style="width:75%">
              <col style="width:25%">
            </colgroup>
            <tr>
              <td style="padding:0; vertical-align:top;">{{ $faculty->name ?? '' }}</td>
              <td style="padding:0; border-left:1px solid #999; text-align:center; white-space:nowrap; vertical-align:top;">{{ $employeeCode }}</td>
            </tr>
          </table>
        </td>
        <th>Reference CMO</th>
        <td>{{ $referenceCMO ?: '-' }}</td>
      </tr>
      <tr>
        <td>
          <table style="width:100%; border-collapse:collapse; table-layout:fixed;">
            <colgroup>
              <col style="width:75%">
              <col style="width:25%">
            </colgroup>
            <tr>
              <td style="padding:0; vertical-align:top; font-size:11px; color:#555; white-space:pre-line;">{{ $designation }}</td>
              <td style="padding:0; border-left:1px solid #999; text-align:center; white-space:nowrap; vertical-align:top;">&nbsp;</td>
            </tr>
          </table>
        </td>
        <th>Date Prepared</th>
        <td>{{ $datePrepared ?: '-' }}</td>
      </tr>
      <tr>
        <td>
          <table style="width:100%; border-collapse:collapse; table-layout:fixed;">
            <colgroup>
              <col style="width:75%">
              <col style="width:25%">
            </colgroup>
            <tr>
              <td style="padding:0; font-size:11px; color:#555;">{{ $faculty->email ?? '' }}</td>
              <td style="padding:0; border-left:1px solid #999;">&nbsp;</td>
            </tr>
          </table>
        </td>
        <th>Revision No.</th>
        <td>{{ $revisionNo ?: '-' }}</td>
      </tr>
      <tr>
        <th>Period of Study</th>
        <td>{{ $periodOfStudy ?: '-' }}</td>
        <th>Revision Date</th>
        <td>{{ $revisionDate ?: '-' }}</td>
      </tr>
      <tr>
        <th>Course Rationale and Description</th>
        <td colspan="3">{!! nl2br(e($course->description ?? '-')) !!}</td>
      </tr>
      <tr>
        <th>Contact Hours</th>
        <td colspan="3">
              @if(trim($contactText) !== '')
                {!! nl2br(e($contactText)) !!}
              @else
                @if($lec) {{ $lec }} hours lecture @endif
                @if($lec && $lab)<br>@endif
                @if($lab) {{ $lab }} hours laboratory @endif
                @if(!$lec && !$lab)-@endif
              @endif
        </td>
      </tr>
    </table>
  </div>

</body>
</html>
