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
          /* Explicit override for rows that should have no horizontal separator */
          .course-policies tr.no-sep td {
            border-top: 0 !important;
            border-bottom: 0 !important;
            box-shadow: none !important;
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
        </style>
        <table class="table mb-0 cis-inner" style="border:none; margin:0; padding:0; border-collapse:collapse; width:100%;">
          <colgroup>
            <col style="width:13.33%">
            <col style="width:13.33%">
            <col style="width:13.33%">
            <col style="width:60%">
          </colgroup>
          <tbody>
            <tr>
              <th colspan="3" class="text-start cis-label" style="border-bottom:0; padding:0.25rem 0;">Grading System</th>
            </tr>
            <tr>
              <td colspan="3" class="small text-muted" style="padding:0 0 0.5rem 0; border-bottom:0 !important;">The grading system adopted by this course is as follows:</td>
            </tr>

            <tr>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">Excellent</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">1.00</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40;">98 - 100</td>
              <td rowspan="11" style="padding:0; height:28px; line-height:28px; vertical-align:middle; border-left:1px solid #343a40; text-align:center"></td>
            </tr>
            <tr>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">Superior</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">1.25</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40;">94 - 97</td>
            </tr>
            <tr>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">Very Good</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">1.50</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40;">90 - 93</td>
            </tr>
            <tr>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">Good</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">1.75</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40;">88 - 89</td>
            </tr>
            <tr>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">Meritorious</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">2.00</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40;">85 - 87</td>
            </tr>
            <tr>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">Very Satisfactory</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">2.25</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40;">83 - 84</td>
            </tr>
            <tr>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">Satisfactory</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">2.50</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40;">80 - 82</td>
            </tr>
            <tr>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">Fairly Satisfactory</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">2.75</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40;">78 - 79</td>
            </tr>
            <tr>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">Passing</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">3.00</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40;">75 - 77</td>
            </tr>
            <tr>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">Failure</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">5.00</td>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40;">Below 70</td>
            </tr>
            <tr>
              <td style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; border-right:1px solid #343a40;">Incomplete</td>
              <td colspan="2" style="padding:0 .5rem; height:28px; line-height:28px; vertical-align:middle; border-top:1px solid #343a40; text-align:center;">INC</td>
            </tr>
            <tr>
              <td colspan="4" class="small text-muted" style="padding:0; vertical-align:top; border-bottom:0 !important;">
                <div style="display:block; width:100%; box-sizing:border-box; padding:0.25rem 0.5rem; margin:0; white-space:normal; word-break:break-word; line-height:1.2;">*Students who got a computed grade of 70-74 will be given an appropriate remedial activity in which the final grade should be either passing (3.0) or failure (5.0).</div>
              </td>
            </tr>
          </tbody>
        </table>
      </td>
    </tr>
  <!-- extra blank rows for additional policy lines -->
  <tr><td class="text-center align-top" style="padding-top:0.35rem;">B.</td><td class="text-start">Class policy</td></tr>
  <tr><td></td><td>
    <textarea name="course_policies[]" class="form-control cis-textarea autosize" rows="1" data-original="{{ old('course_policies.0', '') }}" placeholder="Enter policy details"></textarea>
  </td></tr>
  <tr><td></td><td class="text-start">missed examinations</td></tr>
  <tr><td></td><td>
    <textarea name="course_policies[]" class="form-control cis-textarea autosize" rows="1" data-original="{{ old('course_policies.1', '') }}" placeholder="Enter policy details"></textarea>
  </td></tr>
  <tr><td></td><td class="text-start">academic dishonesty</td></tr>
  <tr><td></td><td>
    <textarea name="course_policies[]" class="form-control cis-textarea autosize" rows="1" data-original="{{ old('course_policies.2', '') }}" placeholder="Enter policy details"></textarea>
  </td></tr>
  <tr class="no-sep">
    <td style="border-bottom:0 !important;"></td>
    <td class="text-start" style="border-bottom:0 !important;">dropping</td>
  </tr>
  <tr class="no-sep">
    <td style="border-top:0 !important;"></td>
    <td style="border-top:0 !important;">
  <textarea name="course_policies[]" class="form-control cis-textarea autosize" style="border:0; border-radius:0;" rows="1" data-original="{{ old('course_policies.3', '') }}" placeholder="Enter policy details"></textarea>
    </td>
  </tr>
  <tr class="no-sep">
    <td class="text-center align-top" style="padding-top:0.35rem; border-bottom:0 !important;">C.</td>
    <td class="text-start" style="border-bottom:0 !important;">OTHER COURSE POLICIES AND REQUIREMENTS</td>
  </tr>
  <tr class="no-sep">
    <td style="border-top:0 !important;"></td>
    <td style="border-top:0 !important;">
  <textarea name="course_policies[]" class="form-control cis-textarea autosize" style="border:0; border-radius:0;" rows="1" data-original="{{ old('course_policies.4', '') }}" placeholder="Enter policy details"></textarea>
    </td>
  </tr>
  </tbody>
</table>
