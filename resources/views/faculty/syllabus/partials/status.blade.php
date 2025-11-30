{{--
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/status.blade.php
* Purpose: Syllabus status and signature section (Prepared by, Reviewed by, Approved by)
-------------------------------------------------------------------------------
--}}

<style>
  /* keep title typography consistent with other CIS modules */
  .status-left-title { font-weight: 700; padding: 0.75rem; font-family: Georgia, serif; vertical-align: top; box-sizing: border-box; line-height: 1.2; }
  table.cis-table { table-layout: fixed; }
  table.cis-table th.cis-label { white-space: normal; overflow-wrap: break-word; word-break: break-word; }
  table.cis-table td, table.cis-table th { overflow: hidden; }
  
  /* Make inner table fill the right cell container and sit flush */
  #status-right-wrap { padding: 0; margin: 0; }
  #status-right-wrap > table { width: 100%; height: 100%; margin: 0; border-spacing: 0; border-collapse: collapse; }
  
  /* inner table cell padding so content is flush with container */
  #status-right-wrap td, #status-right-wrap th { vertical-align: middle; padding: 0.5rem 0.5rem; }
  
  /* force header text style to Times New Roman 10pt black */
  #status-right-wrap > table thead th { color:#000 !important; font-family:'Times New Roman', Times, serif !important; font-size:10pt !important; }
  
  /* show cell borders for the inner status table – forced black */
  #status-right-wrap > table td { border: 1px solid #000; }
  
  /* add outer border to the table */
  #status-right-wrap > table { border: 1px solid #000; }
  
  .signature-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 0.5rem;
  }

  .signature-content input[type="text"] {
    max-width: 220px;
  }

  .signature-content input[type="date"] {
    font-size: 0.875rem;
  }

  .remarks-section {
    padding: 0.75rem;
  }

  .remarks-section textarea {
    resize: vertical;
    min-height: 60px;
  }

  @media print {
    #status-right-wrap input {
      border: none;
      background: transparent;
    }
  }
</style>

<div class="mb-4" style="position: relative;">
  <div style="position: absolute; top: -24px; left: 0;">
    <span id="unsaved-prepared_by_name" class="unsaved-pill d-none">Unsaved</span>
    <span id="unsaved-prepared_by_title" class="unsaved-pill d-none">Unsaved</span>
    <span id="unsaved-prepared_by_date" class="unsaved-pill d-none">Unsaved</span>
    <span id="unsaved-reviewed_by_name" class="unsaved-pill d-none">Unsaved</span>
    <span id="unsaved-reviewed_by_title" class="unsaved-pill d-none">Unsaved</span>
    <span id="unsaved-reviewed_by_date" class="unsaved-pill d-none">Unsaved</span>
    <span id="unsaved-approved_by_name" class="unsaved-pill d-none">Unsaved</span>
    <span id="unsaved-approved_by_title" class="unsaved-pill d-none">Unsaved</span>
    <span id="unsaved-approved_by_date" class="unsaved-pill d-none">Unsaved</span>
    <span id="unsaved-status_remarks" class="unsaved-pill d-none">Unsaved</span>
  </div>
  
  <div id="status-right-wrap">
        <table>
          <tbody>
            <tr>
              <td class="text-center">
                <div class="signature-content">
                  <input type="text" 
                         class="form-control form-control-sm text-center fw-semibold mb-2" 
                         placeholder="Prepared by:"
                         style="border: none; background: transparent; font-size: 10pt;">
                  <input type="text" 
                         name="prepared_by_name" 
                         class="form-control form-control-sm text-center mb-1" 
                         placeholder="Name"
                         value="{{ old('prepared_by_name', $syllabus->prepared_by_name ?? '') }}"
                         data-original="{{ $syllabus->prepared_by_name ?? '' }}">
                  <input type="text" 
                         name="prepared_by_title" 
                         class="form-control form-control-sm text-center" 
                         placeholder="Title"
                         value="{{ old('prepared_by_title', $syllabus->prepared_by_title ?? '') }}"
                         data-original="{{ $syllabus->prepared_by_title ?? '' }}">
                  <small class="text-muted d-block mt-1">
                    Date: <input type="date" 
                                 name="prepared_by_date" 
                                 class="form-control form-control-sm d-inline-block" 
                                 style="width: auto; min-width: 140px;"
                                 value="{{ old('prepared_by_date', $syllabus->prepared_by_date ?? '') }}"
                                 data-original="{{ $syllabus->prepared_by_date ?? '' }}">
                  </small>
                </div>
              </td>
              <td class="text-center">
                <div class="signature-content">
                  <input type="text" 
                         class="form-control form-control-sm text-center fw-semibold mb-2" 
                         placeholder="Reviewed by:"
                         style="border: none; background: transparent; font-size: 10pt;">
                      @php
                        // If submission workflow assigned a reviewer (user id), prefer their name
                        $autoReviewerName = optional($syllabus->reviewer)->name;
                        $reviewerNameValue = old('reviewed_by_name', ($syllabus->reviewed_by_name ?? $autoReviewerName) ?? '');
                        $reviewerNameOriginal = ($syllabus->reviewed_by_name ?? $autoReviewerName) ?? '';
                      @endphp
                      <input type="text" 
                        name="reviewed_by_name" 
                        class="form-control form-control-sm text-center mb-1" 
                        placeholder="Name"
                        value="{{ $reviewerNameValue }}"
                        data-original="{{ $reviewerNameOriginal }}">
                  <input type="text" 
                         name="reviewed_by_title" 
                         class="form-control form-control-sm text-center" 
                         placeholder="Title"
                         value="{{ old('reviewed_by_title', $syllabus->reviewed_by_title ?? '') }}"
                         data-original="{{ $syllabus->reviewed_by_title ?? '' }}">
                  <small class="text-muted d-block mt-1">
                    Date: <input type="date" 
                                 name="reviewed_by_date" 
                                 class="form-control form-control-sm d-inline-block" 
                                 style="width: auto; min-width: 140px;"
                                 value="{{ old('reviewed_by_date', $syllabus->reviewed_by_date ?? '') }}"
                                 data-original="{{ $syllabus->reviewed_by_date ?? '' }}">
                  </small>
                </div>
              </td>
              <td class="text-center">
                <div class="signature-content">
                  <input type="text" 
                         class="form-control form-control-sm text-center fw-semibold mb-2" 
                         placeholder="Approved by:"
                         style="border: none; background: transparent; font-size: 10pt;">
                  <input type="text" 
                         name="approved_by_name" 
                         class="form-control form-control-sm text-center mb-1" 
                         placeholder="Name"
                         value="{{ old('approved_by_name', $syllabus->approved_by_name ?? '') }}"
                         data-original="{{ $syllabus->approved_by_name ?? '' }}">
                  <input type="text" 
                         name="approved_by_title" 
                         class="form-control form-control-sm text-center" 
                         placeholder="Title"
                         value="{{ old('approved_by_title', $syllabus->approved_by_title ?? '') }}"
                         data-original="{{ $syllabus->approved_by_title ?? '' }}">
                  <small class="text-muted d-block mt-1">
                    Date: <input type="date" 
                                 name="approved_by_date" 
                                 class="form-control form-control-sm d-inline-block" 
                                 style="width: auto; min-width: 140px;"
                                 value="{{ old('approved_by_date', $syllabus->approved_by_date ?? '') }}"
                                 data-original="{{ $syllabus->approved_by_date ?? '' }}">
                  </small>
                </div>
              </td>
            </tr>
            <tr>
              <td colspan="3">
                <div class="remarks-section">
                  <label class="form-label fw-semibold mb-1">Remarks:</label>
                  <textarea name="status_remarks" 
                            class="form-control form-control-sm" 
                            rows="2" 
                            placeholder="Enter any remarks or comments about this syllabus..."
                            data-original="{{ $syllabus->status_remarks ?? '' }}">{{ old('status_remarks', $syllabus->status_remarks ?? '') }}</textarea>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
</div>
