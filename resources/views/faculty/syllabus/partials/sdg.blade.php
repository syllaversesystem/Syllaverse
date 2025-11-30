{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/sdg.blade.php
* Description: CIS-style SDG layout with drag-safe table structure – Syllaverse
-------------------------------------------------------------------------------
--}}

@php 
  $rp = $routePrefix ?? 'faculty.syllabi';
@endphp

<style>
  /* keep title typography consistent with other CIS modules */
  .sdg-left-title { font-weight: 700; padding: 0.75rem; font-family: Georgia, serif; vertical-align: top; box-sizing: border-box; line-height: 1.2; }
  table.cis-table { table-layout: fixed; }
  table.cis-table th.cis-label { white-space: normal; overflow-wrap: break-word; word-break: break-word; }
  table.cis-table td, table.cis-table th { overflow: hidden; }
  /* Make inner table fill the right cell container and sit flush */
  #sdg-right-wrap { padding: 0; margin: 0; }
  #sdg-right-wrap > table { width: 100%; height: 100%; margin: 0; border-spacing: 0; border-collapse: collapse; }
  /* inner table cell padding so content is flush with container */
  #sdg-right-wrap td, #sdg-right-wrap th { vertical-align: middle; padding: 0.5rem 0.5rem; }
  /* force header text style (SDG code + description) to Times New Roman 10pt black */
  #sdg-right-wrap > table thead th.cis-label { color:#000 !important; font-family:'Times New Roman', Times, serif !important; font-size:10pt !important; }
  /* Make the first header cell ("SDG") shrink to content and avoid growing */
  #sdg-right-wrap > table thead th.cis-label:first-child { white-space: nowrap; width:1%; }
  /* show cell borders for the inner SDG table (internal grid only) – now forced black */
  #sdg-right-wrap > table th, #sdg-right-wrap > table td { border: 1px solid #000; }
  /* hide outer edges so only internal dividers remain */
  #sdg-right-wrap > table thead th { border-top: 0; }
  #sdg-right-wrap > table th:first-child, #sdg-right-wrap > table td:first-child { border-left: 0; }
  #sdg-right-wrap > table th:last-child, #sdg-right-wrap > table td:last-child { border-right: 0; }
  /* remove the bottom-most outer border line of the inner table (no visible border under last row) */
  #sdg-right-wrap > table tbody tr:last-child td { border-bottom: 0 !important; }
  /* SDG badge: allow auto sizing (no forced min-width) so column shrinks to content */
  .sdg-badge { display: inline-block; min-width: 0; width: auto; padding: 0; text-align: center; color:#000; white-space: normal; line-height: 1.1; }
  
  /* tighten header and first column padding to reduce visual height */
  /* Uniform 6.4px padding for SDG code header and code cells */
  #sdg-right-wrap > table thead th.cis-label:first-child { padding: 6.4px !important; }
  #sdg-right-wrap > table td:first-child, #sdg-right-wrap > table th:first-child { padding: 6.4px !important; }
  .drag-handle { width: 28px; display: inline-flex; justify-content: center; align-self: center; }
  /* Make textarea fill remaining space and autosize */
  .cis-textarea { width: 100%; box-sizing: border-box; resize: none; }
  /* Align SDG textareas styling with Course Title textareas (single-line autosize) */
  #sdg-right-wrap textarea.cis-textarea.autosize { overflow: hidden; }
  /* Ensure the left header cell aligns with other CIS module headers */
  table.cis-table th.cis-label, table.cis-table th { vertical-align: top; }
  /* Icon-only header buttons styled like IGA */
  .sdg-header-actions .btn {
    position: relative; padding: 0 !important;
    width: 2.2rem; height: 2.2rem; min-width: 2.2rem; min-height: 2.2rem;
    border-radius: 50% !important;
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--sv-card-bg, #fff);
    border: none; box-shadow: none; color: #000;
    transition: all 0.2s ease-in-out;
    line-height: 0;
  }
  .sdg-header-actions .btn .bi { font-size: 1rem; width: 1rem; height: 1rem; line-height: 1; color: var(--sv-text, #000); }
  .sdg-header-actions .btn svg {
    width: 1.05rem; height: 1.05rem;
    display: block; margin: 0; vertical-align: middle;
    stroke: currentColor;
  }
  .sdg-header-actions .btn:hover, .sdg-header-actions .btn:focus {
    background: linear-gradient(135deg, rgba(255,240,235,.88), rgba(255,255,255,.46));
    backdrop-filter: blur(7px); -webkit-backdrop-filter: blur(7px);
    box-shadow: 0 4px 10px rgba(204,55,55,.12);
    color: #CB3737;
  }
  .sdg-header-actions .btn:hover .bi, .sdg-header-actions .btn:focus .bi { color: #CB3737; }
  .sdg-header-actions .btn:active { transform: scale(.97); filter: brightness(.98); }
</style>

<table class="table table-bordered mb-4 cis-table">
  <colgroup>
    <col style="width:16%">
    <col style="width:84%">
  </colgroup>
  <tbody>
    <tr>
      <th class="align-top text-start cis-label sdg-left-title">Sustainable Development Goals (SDG)</th>
      <td id="sdg-right-wrap">
        <table class="table mb-0" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.4; border: none; table-layout: fixed;">
          <colgroup>
            <col style="width:70px"> <!-- SDG code column fixed -->
            <col style="width:auto"> <!-- Description column flexes remaining -->
          </colgroup>
          <thead>
            <tr class="table-light">
              <th class="text-center cis-label">SDG</th>
              <th class="text-center cis-label">
                <div class="d-flex justify-content-between align-items-center gap-2">
                  <span class="flex-grow-1 text-center">Sustainable Development Goals (SDG) Statements</span>
                  <span class="sdg-header-actions d-inline-flex gap-1" style="white-space:nowrap;">
                    <button type="button" class="btn btn-sm" id="sdg-load-predefined" title="Load Predefined SDGs" aria-label="Load Predefined SDGs" style="background:transparent;">
                      <i data-feather="download"></i>
                      <span class="visually-hidden">Load Predefined SDGs</span>
                    </button>
                    <button type="button" class="btn btn-sm" id="sdg-add-header" title="Add SDG" aria-label="Add SDG" style="background:transparent;">
                      <i data-feather="plus"></i>
                      <span class="visually-hidden">Add SDG</span>
                    </button>
                  </span>
                </div>
              </th>
            </tr>
          </thead>
          <tbody id="syllabus-sdg-sortable" data-syllabus-id="{{ $default['id'] }}">
            @forelse($syllabus->sdgs ?? [] as $index => $sdg)
              @php $seqCode = $sdg->code ?? 'SDG' . ($index + 1); @endphp
              <tr data-id="{{ $sdg->id }}">
                <td class="text-center align-middle">
                  <div class="sdg-badge fw-semibold">{{ $seqCode }}</div>
                </td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;">
                      <i class="bi bi-grip-vertical"></i>
                    </span>
                    <div class="flex-grow-1 w-100">
                      <textarea
                        name="sdg_titles[]"
                        class="cis-textarea cis-field autosize"
                        placeholder="Title"
                        rows="1"
                        style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;font-weight:700;"
                        required>{{ old("sdg_titles.$index", $sdg->title ?? '') }}</textarea>
                      <textarea
                        name="sdgs[]"
                        class="cis-textarea cis-field autosize"
                        placeholder="Description"
                        rows="1"
                        style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;"
                        required>{{ old("sdgs.$index", $sdg->description) }}</textarea>
                      <input type="hidden" name="code[]" value="{{ $seqCode }}">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-sdg ms-2" title="Delete SDG"><i class="bi bi-trash"></i></button>
                  </div>
                </td>
              </tr>
            @empty
              <tr id="sdg-placeholder">
                <td colspan="2" class="text-center text-muted py-4">
                  <p class="mb-2">No SDGs added yet.</p>
                  <p class="mb-0"><small>Click the <strong>+</strong> button above to add an SDG or <strong>Load Predefined</strong> to import SDGs.</small></p>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </td>
    </tr>
  </tbody>
</table>

{{-- ░░░ START: Load Predefined SDGs Modal ░░░ --}}
<div class="modal fade sv-sdg-modal" id="loadPredefinedSdgsModal" tabindex="-1" aria-labelledby="loadPredefinedSdgsModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <style>
        /* Synced with IGA load modal styling */
        #loadPredefinedSdgsModal {
          --sv-bg:   #FAFAFA;
          --sv-bdr:  #E3E3E3;
          --sv-acct: #EE6F57;
          --sv-danger:#CB3737;
          z-index: 10010 !important;
        }
        #loadPredefinedSdgsModal .modal-dialog,
        #loadPredefinedSdgsModal .modal-content { position: relative; z-index: 10011; }
        #loadPredefinedSdgsModal .modal-header { padding: .85rem 1rem; border-bottom: 1px solid var(--sv-bdr); background: #fff; }
        #loadPredefinedSdgsModal .modal-title {
          font-weight: 600;
          font-size: 1rem;
          display: inline-flex;
          align-items: center;
          gap: .5rem;
        }
        #loadPredefinedSdgsModal .modal-title i,
        #loadPredefinedSdgsModal .modal-title svg { width: 1.05rem; height: 1.05rem; stroke: var(--sv-text-muted, #777777); }
        /* Scrollable modal layout */
        #loadPredefinedSdgsModal .modal-dialog { max-width: 680px; }
        #loadPredefinedSdgsModal .modal-content { max-height: 85vh; display: flex; flex-direction: column; }
        #loadPredefinedSdgsModal .modal-body { flex: 1 1 auto; overflow-y: auto; overscroll-behavior: contain; }
        #loadPredefinedSdgsModal .modal-content {
          border-radius: 16px;
          border: 1px solid var(--sv-bdr);
          background: #fff;
          box-shadow: 0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06);
          overflow: hidden;
        }
        #loadPredefinedSdgsModal .alert { border-radius: 12px; padding: .75rem 1rem; font-size: .875rem; }
        #loadPredefinedSdgsModal .alert-warning { background: #FFF3CD; border: 1px solid #FFE69C; color: #856404; }
        /* Primary (Load) button neutral style */
        #loadPredefinedSdgsModal .btn-danger {
          background: var(--sv-card-bg, #fff);
          border: none;
          color: #000;
          transition: all 0.2s ease-in-out;
          box-shadow: none;
          display: inline-flex;
          align-items: center;
          gap: 0.5rem;
          padding: 0.5rem 1rem;
          border-radius: 0.375rem;
          font-size: 0.95rem;
        }
        #loadPredefinedSdgsModal .btn-danger i,
        #loadPredefinedSdgsModal .btn-danger svg { width: 1.05rem; height: 1.05rem; }
        #loadPredefinedSdgsModal .btn-danger:hover,
        #loadPredefinedSdgsModal .btn-danger:focus {
          background: linear-gradient(135deg, rgba(220, 220, 220, 0.88), rgba(240, 240, 240, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(0,0,0,.12);
          color: #000;
        }
        #loadPredefinedSdgsModal .btn-danger:active { background: linear-gradient(135deg, rgba(240,242,245,.98), rgba(255,255,255,.62)); box-shadow: 0 1px 8px rgba(0,0,0,.16); }
        #loadPredefinedSdgsModal .btn-danger:disabled { opacity: .6; cursor: not-allowed; }
        /* Cancel button */
        #loadPredefinedSdgsModal .btn-light {
          background: var(--sv-card-bg, #fff);
          border: none;
          color: #000;
          transition: all 0.2s ease-in-out;
          box-shadow: none;
          display: inline-flex;
          align-items: center;
          gap: 0.5rem;
          padding: 0.5rem 1rem;
          border-radius: 0.375rem;
          font-size: 0.95rem;
        }
        #loadPredefinedSdgsModal .btn-light i,
        #loadPredefinedSdgsModal .btn-light svg { stroke: #000; width: 1.05rem; height: 1.05rem; }
        #loadPredefinedSdgsModal .btn-light:hover,
        #loadPredefinedSdgsModal .btn-light:focus {
          background: linear-gradient(135deg, rgba(220,220,220,.88), rgba(240,240,240,.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(108,117,125,.12);
          color: #495057;
        }
        #loadPredefinedSdgsModal .btn-light:active { background: linear-gradient(135deg, rgba(240,242,245,.98), rgba(255,255,255,.62)); box-shadow: 0 1px 8px rgba(108,117,125,.16); }
        /* Checkbox styling consistent */
        #loadPredefinedSdgsModal .form-check-input { background-color: #E8E8E8; border-color: #CCCCCC; }
        #loadPredefinedSdgsModal .form-check-input:checked { background-color: #6C757D; border-color: #6C757D; }
        #loadPredefinedSdgsModal .form-check-input:focus { border-color: #999; box-shadow: 0 0 0 0.25rem rgba(108,117,125,.25); }
      </style>

      <div class="modal-header">
        <h5 class="modal-title" id="loadPredefinedSdgsModalLabel">
          <i data-feather="download"></i>
          <span>Load Predefined SDGs</span>
        </h5>
      </div>

      <div class="modal-body">
        <div class="alert alert-warning" role="alert">
          <strong>Warning:</strong> This will replace all current SDGs with the selected predefined ones from the master data. This action cannot be undone.
        </div>
        
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="selectAllSdgs" checked>
            <label class="form-check-label fw-semibold" for="selectAllSdgs">
              Select All
            </label>
          </div>
          <hr class="my-2">
          <div id="sdgSelectionList" style="max-height: 300px; overflow-y: auto;">
            <div class="text-center text-muted py-3">
              <i data-feather="loader" class="spinner"></i>
              <p class="mb-0 mt-2">Loading SDGs...</p>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
          <i data-feather="x"></i>
          Cancel
        </button>
        <button type="button" class="btn btn-danger" id="confirmLoadPredefinedSdgs">
          <i data-feather="download"></i>
          Load SDGs
        </button>
      </div>
    </div>
  </div>
</div>
{{-- ░░░ END: Load Predefined SDGs Modal ░░░ --}}

    <script>
      document.addEventListener('DOMContentLoaded', function(){
        try {
          const modal = document.getElementById('loadPredefinedSdgsModal');
          if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
            modal.style.zIndex = '10010';
            const dlg = modal.querySelector('.modal-dialog'); if (dlg) dlg.style.zIndex = '10011';
          }
        } catch(e){ console.error('SDG modal relocation failed', e); }
      });
    </script>

@push('scripts')
  @vite(['resources/js/faculty/syllabus-sdg.js'])
@endpush
