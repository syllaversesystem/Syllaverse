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
          /* Keep background transparent even when focused */
          .course-policies textarea.cis-textarea.cis-field:focus {
            background-color: transparent !important;
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
            /* Load Predefined Policy button styles */
            #policy-load-predefined:hover,
            #policy-load-predefined:focus {
              background: linear-gradient(135deg, rgba(255,240,235,.88), rgba(255,255,255,.46)) !important;
              backdrop-filter: blur(7px);
              -webkit-backdrop-filter: blur(7px);
              box-shadow: 0 4px 10px rgba(204,55,55,.12) !important;
              color: #CB3737 !important;
            }
            #policy-load-predefined:hover svg,
            #policy-load-predefined:focus svg {
              stroke: #CB3737;
            }
            #policy-load-predefined:active {
              transform: scale(.97);
              filter: brightness(.98);
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
  <tr>
    <td class="text-center align-top" style="padding-top:0.35rem;">B.</td>
    <td class="text-start">
      <div class="d-flex justify-content-between align-items-center">
        <span>Class policy</span>
        <button type="button" class="btn btn-sm" id="policy-load-predefined" title="Load Predefined Policies" aria-label="Load Predefined Policies" style="background:transparent; padding: 0; width: 2rem; height: 2rem; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s ease-in-out;">
          <i data-feather="download" style="width: 1rem; height: 1rem;"></i>
        </button>
      </div>
    </td>
  </tr>
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

{{-- ░░░ BEGIN: Load Predefined Policy Modal ░░░ --}}
<div class="modal fade sv-policy-modal" id="loadPredefinedPolicyModal" tabindex="-1" aria-labelledby="loadPredefinedPolicyModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <style>
        #loadPredefinedPolicyModal {
          font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
          -webkit-font-smoothing: antialiased;
          -moz-osx-font-smoothing: grayscale;
        }
        #loadPredefinedPolicyModal .modal-header {
          background: linear-gradient(135deg, #F8F9FA 0%, #FFFFFF 100%);
          border-bottom: 1px solid rgba(0,0,0,.08);
          padding: 1.25rem 1.5rem;
        }
        #loadPredefinedPolicyModal .modal-title {
          font-weight: 600;
          font-size: 1.125rem;
          color: #212529;
          display: flex;
          align-items: center;
          gap: 0.625rem;
        }
        #loadPredefinedPolicyModal .modal-title i,
        #loadPredefinedPolicyModal .modal-title svg {
          width: 1.25rem;
          height: 1.25rem;
        }
        #loadPredefinedPolicyModal .modal-content {
          border: none;
          border-radius: 0.75rem;
          box-shadow: 0 10px 40px rgba(0,0,0,.12), 0 2px 8px rgba(0,0,0,.06);
          overflow: hidden;
        }
        #loadPredefinedPolicyModal .alert {
          margin-bottom: 1.5rem;
        }
        #loadPredefinedPolicyModal .alert-warning {
          background: #FFF3CD;
          border-color: #FFE69C;
        }
        #loadPredefinedPolicyModal .btn-danger {
          background: linear-gradient(135deg, #CB3737 0%, #A82828 100%);
          border: none;
          color: #fff;
          font-weight: 500;
          padding: 0.625rem 1.5rem;
          border-radius: 0.5rem;
          transition: all 0.2s ease;
          display: inline-flex;
          align-items: center;
          gap: 0.5rem;
          box-shadow: 0 2px 8px rgba(203, 55, 55, 0.2);
        }
        #loadPredefinedPolicyModal .btn-danger i,
        #loadPredefinedPolicyModal .btn-danger svg {
          width: 1rem;
          height: 1rem;
          stroke: #fff;
        }
        #loadPredefinedPolicyModal .btn-danger:hover,
        #loadPredefinedPolicyModal .btn-danger:focus {
          background: linear-gradient(135deg, #A82828 0%, #8B1F1F 100%);
          box-shadow: 0 4px 12px rgba(203, 55, 55, 0.3);
          transform: translateY(-1px);
        }
        #loadPredefinedPolicyModal .btn-danger:hover i,
        #loadPredefinedPolicyModal .btn-danger:hover svg,
        #loadPredefinedPolicyModal .btn-danger:focus i,
        #loadPredefinedPolicyModal .btn-danger:focus svg {
          stroke: #fff;
        }
        #loadPredefinedPolicyModal .btn-danger:active {
          transform: scale(0.98);
        }
        #loadPredefinedPolicyModal .btn-danger:active i,
        #loadPredefinedPolicyModal .btn-danger:active svg {
          stroke: #fff;
        }
        #loadPredefinedPolicyModal .btn-light {
          background: #fff;
          border: none;
          color: #6c757d;
          transition: all 0.2s ease;
          display: inline-flex;
          align-items: center;
          gap: 0.5rem;
          padding: 0.625rem 1.5rem;
          border-radius: 0.5rem;
        }
        #loadPredefinedPolicyModal .btn-light:hover,
        #loadPredefinedPolicyModal .btn-light:focus {
          background: linear-gradient(135deg, rgba(220, 220, 220, 0.88), rgba(240, 240, 240, 0.46));
          box-shadow: 0 4px 10px rgba(108, 117, 125, 0.12);
          color: #495057;
        }
        #loadPredefinedPolicyModal .policy-content {
          background: #F8F9FA;
          border: 1px solid #DEE2E6;
          border-radius: 0.5rem;
          padding: 1rem;
          max-height: 300px;
          overflow-y: auto;
          font-family: Georgia, serif;
          font-size: 13px;
          line-height: 1.6;
          white-space: pre-wrap;
        }
      </style>

      <div class="modal-header">
        <h5 class="modal-title" id="loadPredefinedPolicyModalLabel">
          <i data-feather="download"></i>
          <span>Load Predefined Course Policies</span>
        </h5>
      </div>

      <div class="modal-body">
        <div class="alert alert-warning" role="alert">
          <strong>Warning:</strong> This will replace all current course policies (Class policy, Missed examinations, Academic dishonesty, Dropping, and Other policies) with the predefined ones. This action cannot be undone.
        </div>
        
        <div class="mb-3">
          <label class="form-label fw-semibold">Preview:</label>
          <div id="policyPreviewContent">
            <div class="text-center text-muted py-3">
              <i data-feather="loader" class="spinner"></i>
              <p class="mb-0 mt-2">Loading policies...</p>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
          <i data-feather="x"></i>
          Cancel
        </button>
        <button type="button" class="btn btn-danger" id="confirmLoadPredefinedPolicy">
          <i data-feather="download"></i>
          Load All Policies
        </button>
      </div>
    </div>
  </div>
</div>
{{-- ░░░ END: Load Predefined Policy Modal ░░░ --}}

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

  // Load Predefined Policy functionality
  const loadPolicyBtn = document.getElementById('policy-load-predefined');
  const loadPolicyModal = document.getElementById('loadPredefinedPolicyModal');
  const confirmLoadBtn = document.getElementById('confirmLoadPredefinedPolicy');
  const syllabusId = document.getElementById('syllabus-document')?.dataset?.syllabusId;

  if (loadPolicyBtn && loadPolicyModal && syllabusId) {
    loadPolicyBtn.addEventListener('click', async function() {
      const modal = new bootstrap.Modal(loadPolicyModal);
      const previewContent = document.getElementById('policyPreviewContent');
      
      // Show loading state
      previewContent.innerHTML = `
        <div class="text-center text-muted py-3">
          <i data-feather="loader" class="spinner"></i>
          <p class="mb-0 mt-2">Loading policies...</p>
        </div>
      `;
      feather.replace();
      
      modal.show();
      
      try {
        // Fetch all predefined policies from server
        const response = await fetch(`/faculty/syllabi/${syllabusId}/predefined-policies`);
        const data = await response.json();
        
        if (data.success && data.policies) {
          // Display all policies in a formatted view
          let html = '';
          const sectionLabels = {
            policy: 'Class Policy',
            exams: 'Missed Examinations',
            dishonesty: 'Academic Dishonesty',
            dropping: 'Dropping',
            other: 'Other Course Policies and Requirements'
          };
          
          Object.entries(data.policies).forEach(([section, content]) => {
            if (content) {
              html += `
                <div class="mb-3">
                  <div class="fw-semibold text-uppercase" style="font-size: 0.875rem; color: #6c757d; margin-bottom: 0.5rem;">${sectionLabels[section] || section}</div>
                  <div class="policy-content">${content}</div>
                </div>
              `;
            }
          });
          
          if (html) {
            previewContent.innerHTML = html;
          } else {
            previewContent.innerHTML = `
              <div class="text-center text-muted py-3">
                <i data-feather="alert-circle"></i>
                <p class="mb-0 mt-2">No predefined policies found.</p>
              </div>
            `;
            feather.replace();
          }
        } else {
          previewContent.innerHTML = `
            <div class="text-center text-muted py-3">
              <i data-feather="alert-circle"></i>
              <p class="mb-0 mt-2">${data.message || 'No predefined policies found.'}</p>
            </div>
          `;
          feather.replace();
        }
      } catch (error) {
        console.error('Error loading predefined policies:', error);
        previewContent.innerHTML = `
          <div class="text-center text-danger py-3">
            <i data-feather="alert-triangle"></i>
            <p class="mb-0 mt-2">Failed to load policies. Please try again.</p>
          </div>
        `;
        feather.replace();
      }
    });

    // Handle confirm load button
    if (confirmLoadBtn) {
      confirmLoadBtn.addEventListener('click', async function() {
        try {
          // Fetch policies again to get raw data
          const response = await fetch(`/faculty/syllabi/${syllabusId}/predefined-policies`);
          const data = await response.json();
          
          if (data.success && data.policies) {
            // Get all policy textareas in order
            const textareas = document.querySelectorAll('.course-policies textarea[name="course_policies[]"]');
            const sections = ['policy', 'exams', 'dishonesty', 'dropping', 'other'];
            
            // Populate each textarea with corresponding policy
            sections.forEach((section, index) => {
              if (textareas[index] && data.policies[section]) {
                textareas[index].value = data.policies[section];
                
                // Trigger autosize if available
                if (window.autosize) {
                  autosize.update(textareas[index]);
                }
                
                // Trigger change event for unsaved tracking
                textareas[index].dispatchEvent(new Event('input', { bubbles: true }));
              }
            });
            
            // Close modal
            bootstrap.Modal.getInstance(loadPolicyModal).hide();
          }
        } catch (error) {
          console.error('Error loading policies:', error);
        }
      });
    }
  }
});
</script>
@endpush
