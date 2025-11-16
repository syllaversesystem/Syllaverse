{{--
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/course-policies.blade.php
* Description: Course Policies — one-row module with two side-by-side policy columns
-------------------------------------------------------------------------------
--}}

<table class="table table-bordered mb-4 cis-table course-policies">
  <colgroup>
    <col style="width:6%">
    <col style="width:94%">
  </colgroup>
  <tbody>
    <tr>
      <th colspan="2" class="text-start cis-label">Course Policies</th>
    </tr>
    <tr>
      <td class="text-center align-top" style="padding-top:0.35rem;">A.</td>
      <td style="padding:0;">
        <style>
          /* CIS typography for Course Policies (no structural changes) */
          .course-policies,
          .course-policies .cis-inner,
          .course-policies .cis-inner th,
          .course-policies .cis-inner td {
            font-family: Georgia, serif !important;
            font-size: 13px !important;
            line-height: 1.4 !important;
            color: #111 !important;
          }

          /* Keep compact padding but allow normal wrapping */
          .course-policies .cis-inner th,
          .course-policies .cis-inner td {
            padding: 0.25rem 0.5rem !important;
            vertical-align: middle;
            box-sizing: border-box;
            white-space: normal;
          }

          .course-policies .cis-inner td.small { padding: 0.25rem 0.5rem !important; }
          .course-policies th.cis-label { font-weight: 700; }
          /* Style plain right-column headings as uppercase, bold (matches sheet) */
          .course-policies td.text-start { text-transform: uppercase; font-weight: 700; }

          /* Ensure editable textarea bodies remain normal weight and normal case
             and render without their own chrome so they sit flush inside table cells */
          .course-policies textarea.cis-textarea,
          .course-policies textarea.cis-textarea::placeholder {
            font-weight: 400;
            text-transform: none;
            font-family: Georgia, serif;
            font-size: 13px;
            line-height:1.4;
            border: 0 !important;
            box-shadow: none !important;
            outline: 0 !important;
            background: transparent !important;
            resize: vertical;
          }
          /* Allow textareas that opt-in with .cis-field to show the yellow focus BG
             (override the transparent !important above for focused state) */
          .course-policies textarea.cis-textarea.cis-field:focus {
            background-color: #fffbe6 !important;
            outline: none !important;
            box-shadow: none !important;
          }
          /* Center the numeric grades column (second column) in the grading subtable */
          .course-policies .cis-inner td:nth-child(2),
          .course-policies .cis-inner th:nth-child(2) {
            text-align: center;
          }
          /* Explicit override for rows that should have no horizontal separator */
          .course-policies tr.no-sep td {
            border-top: 0 !important;
            border-bottom: 0 !important;
            box-shadow: none !important;
          }
            /* targeted rule to remove any borderline (horizontal) between marked rows */
            .course-policies tr.no-borderline td {
              border-top: 0 !important;
              border-bottom: 0 !important;
              box-shadow: none !important;
              background-clip: padding-box !important;
            }
          /* Extra-specific overrides to catch Bootstrap / global selectors */
          table.table.table-bordered.course-policies > tbody > tr.no-sep > td,
          table.table.table-bordered.course-policies > tbody > tr.no-sep + tr > td {
            border-top: 0 !important;
            border-bottom: 0 !important;
            box-shadow: none !important;
            background-clip: padding-box !important;
          }
          /* Defensive: remove any pseudo-element lines */
          table.table.table-bordered.course-policies > tbody > tr.no-sep > td::before,
          table.table.table-bordered.course-policies > tbody > tr.no-sep > td::after,
          table.table.table-bordered.course-policies > tbody > tr.no-sep + tr > td::before,
          table.table.table-bordered.course-policies > tbody > tr.no-sep + tr > td::after {
            content: none !important;
            display: none !important;
          }
          /* Overlay to hide any remaining 1px lines coming from collapsed borders */
          .course-policies tr.no-sep + tr td { position: relative; }
          .course-policies tr.no-sep + tr td::before {
            content: "" !important;
            position: absolute !important;
            left: 0 !important; right: 0 !important; top: 0 !important;
            height: 2px !important; /* slightly larger to be safe */
            background: #fff !important; /* matches page background */
            z-index: 5 !important;
            pointer-events: none !important;
          }
          /* Small yellow unsaved dot used by Course Policies module */
          .unsaved-pill.unsaved-dot {
            display: inline-block !important;
            width: 10px !important;
            height: 10px !important;
            background: #f7e053 !important; /* pale yellow */
            border-radius: 50% !important;
            margin-left: 0.5rem !important;
            vertical-align: middle !important;
            box-shadow: 0 0 0 4px rgba(247,224,83,0.12) !important;
          }
          /* Remove bottom border line of the outer Course Policies table and last row cells */
          table.table.table-bordered.course-policies {
            border-bottom: 0 !important;
          }
          table.table.table-bordered.course-policies > tbody > tr:last-child > td,
          table.table.table-bordered.course-policies > tbody > tr:last-child > th {
            border-bottom: 0 !important;
          }
          /* Seam adjustment: remove outer right border of 'A.' label cell, keep inner table left border */
          table.table.table-bordered.course-policies > tbody > tr:nth-child(2) > td:first-child {
            border-right: 0 !important;
          }
            /* Ensure the left label column (A., B., C.) renders black */
            table.table.table-bordered.course-policies > tbody > tr > td:first-child {
              color: #000 !important;
            }
          /* Utility: remove all borders for targeted inner header/lead cells */
          .course-policies .cis-inner th.no-borderline,
          .course-policies .cis-inner td.no-borderline {
            border: 0 !important;
          }
            /* Utility: remove only left/right borders for specific inner cells */
            .course-policies .cis-inner td.no-border-sides {
              border-left: 0 !important;
              border-right: 0 !important;
            }
            /* Remove left border for selected grade label cells */
            .course-policies .cis-inner td.grade-label-noleft {
              border-left: 0 !important;
            }
            /* Force left alignment for grading system description row */
            .course-policies .cis-inner td.grade-desc-left { text-align: left !important; padding-left: 0.5rem !important; }
            /* Left-align remedial activity note with consistent padding */
            .course-policies .cis-inner td.note-left { text-align:left !important; padding:0.25rem 0.5rem !important; }
        </style>
        <table class="table mb-0 cis-inner" style="border:none; margin:0; padding:0; border-collapse:collapse; width:100%;">
          <colgroup>
            <col style="width:12%">
            <col style="width:8%"> <!-- numeric grades column made narrower -->
            <col style="width:20%">
            <col style="width:60%">
          </colgroup>
          <tbody>
            <tr>
              <th colspan="3" class="text-start cis-label no-borderline" style="border:0 !important; padding:0.25rem 0;">Grading System</th>
            </tr>
            <tr>
              <td colspan="3" class="small text-muted no-borderline grade-desc-left" style="padding:0.25rem 0; border:0 !important;">The grading system adopted by this course is as follows:</td>
            </tr>

            <tr>
              <td class="grade-label-noleft" style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">Excellent</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">1.00</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40;">98 - 100</td>
              <td rowspan="11" style="padding:0; height:28px; line-height:28px; vertical-align:middle; border-left:1px solid #343a40; text-align:center"></td>
            </tr>
            <tr>
              <td class="grade-label-noleft" style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">Superior</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">1.25</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40;">94 - 97</td>
            </tr>
            <tr>
              <td class="grade-label-noleft" style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">Very Good</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">1.50</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40;">90 - 93</td>
            </tr>
            <tr>
              <td class="grade-label-noleft" style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">Good</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">1.75</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40;">88 - 89</td>
            </tr>
            <tr>
              <td class="grade-label-noleft" style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">Meritorious</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">2.00</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40;">85 - 87</td>
            </tr>
            <tr>
              <td class="grade-label-noleft" style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">Very Satisfactory</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">2.25</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40;">83 - 84</td>
            </tr>
            <tr>
              <td class="grade-label-noleft" style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">Satisfactory</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">2.50</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40;">80 - 82</td>
            </tr>
            <tr>
              <td class="grade-label-noleft" style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">Fairly Satisfactory</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">2.75</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40;">78 - 79</td>
            </tr>
            <tr>
              <td class="grade-label-noleft" style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">Passing</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">3.00</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40;">75 - 77</td>
            </tr>
            <tr>
              <td class="grade-label-noleft" style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">Failure</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">5.00</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40;">Below 70</td>
            </tr>
            <tr>
              <td class="grade-label-noleft" style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">Incomplete</td>
              <td colspan="2" style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; text-align:center;">INC</td>
            </tr>
            <tr>
              <td colspan="4" class="small text-muted no-border-sides note-left" style="vertical-align:top; border-bottom:0 !important;">
                *Students who got a computed grade of 70-74 will be given an appropriate remedial activity in which the final grade should be either passing (3.0) or failure (5.0).
              </td>
            </tr>
          </tbody>
        </table>
      </td>
    </tr>
  @php
    // collect policies by section for easy lookup; controller passes a Collection or array
    $policies = isset($coursePolicies) ? collect($coursePolicies) : collect();
    $bySection = $policies->keyBy('section');
    // canonical sections mapped to the UI rows (index order)
    $uiSections = [
      'policy',    // B. Class policy
      'exams',     // missed examinations
      'dishonesty',// ACADEMIC DISHONESTY
      'dropping',  // dropping
    'other',  // C. Other course policies and requirements
    ];
  @endphp

  {{-- Render fixed rows and only show the textarea content (no section labels) --}}
  <tr><td class="text-center align-top" style="padding-top:0.35rem;">B.</td><td class="text-start">Class policy</td></tr>
  <tr><td></td><td>
  <textarea name="course_policies[]" form="syllabusForm" class="form-control cis-textarea autosize cis-field" rows="1" data-original="{{ old('course_policies.0', optional($bySection->get('policy'))->content ?? '') }}" placeholder="Enter policy details">{{ old('course_policies.0', optional($bySection->get('policy'))->content ?? '') }}</textarea>
  </td></tr>

  <tr><td></td><td class="text-start">missed examinations</td></tr>
  <tr><td></td><td>
  <textarea name="course_policies[]" form="syllabusForm" class="form-control cis-textarea autosize cis-field" rows="1" data-original="{{ old('course_policies.1', optional($bySection->get('exams'))->content ?? '') }}" placeholder="Enter policy details">{{ old('course_policies.1', optional($bySection->get('exams'))->content ?? '') }}</textarea>
  </td></tr>

  <tr><td></td><td class="text-start">ACADEMIC DISHONESTY</td></tr>
  <tr><td></td><td>
  <textarea name="course_policies[]" form="syllabusForm" class="form-control cis-textarea autosize cis-field" rows="1" data-original="{{ old('course_policies.2', optional($bySection->get('dishonesty'))->content ?? '') }}" placeholder="Enter policy details">{{ old('course_policies.2', optional($bySection->get('dishonesty'))->content ?? '') }}</textarea>
  </td></tr>

  <tr class="no-sep no-borderline" style="border-bottom:0 !important;">
    <td style="border-bottom:0 !important;"></td>
    <td class="text-start no-borderline" style="border-bottom:0 !important;">dropping</td>
  </tr>
  <tr class="no-sep no-borderline" style="border-top:0 !important;">
    <td style="border-top:0 !important;"></td>
    <td style="border-top:0 !important;">
  <textarea name="course_policies[]" form="syllabusForm" class="form-control cis-textarea autosize cis-field" style="border:0; border-radius:0;" rows="1" data-original="{{ old('course_policies.3', optional($bySection->get('dropping'))->content ?? '') }}" placeholder="Enter policy details">{{ old('course_policies.3', optional($bySection->get('dropping'))->content ?? '') }}</textarea>
    </td>
  </tr>

  <tr class="no-sep" style="border-bottom:0 !important;">
    <td class="text-center align-top" style="padding-top:0.35rem; border-bottom:0 !important;">C.</td>
    <td class="text-start" style="border-bottom:0 !important;">OTHER COURSE POLICIES AND REQUIREMENTS</td>
  </tr>
  <tr class="no-sep" style="border-top:0 !important;">
    <td style="border-top:0 !important; "></td>
    <td style="border-top:0 !important;">
  <textarea name="course_policies[]" form="syllabusForm" class="form-control cis-textarea autosize cis-field" style="border:0; border-radius:0;" rows="1" data-original="{{ old('course_policies.4', optional($bySection->get('other'))->content ?? '') }}" placeholder="Enter policy details">{{ old('course_policies.4', optional($bySection->get('other'))->content ?? '') }}</textarea>
    </td>
  </tr>
  </tbody>
</table>

@push('scripts')
<script>
// Bind Course Policies textareas to the module unsaved pill
document.addEventListener('DOMContentLoaded', function () {
  try {
    const selector = '.course-policies textarea.autosize';
    const areas = document.querySelectorAll(selector);
    if (!areas || areas.length === 0) return;

    areas.forEach((ta) => {
      // ensure original snapshot exists for bindUnsavedIndicator-style comparisons
      // Removed unsaved tracking logic
    });
  } catch (e) { /* noop */ }
});
</script>
@endpush
