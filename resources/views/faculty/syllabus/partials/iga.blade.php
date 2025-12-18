{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/iga.blade.php
* Description: Institutional Graduate Attributes (IGA) — placeholder CIS-style table
-------------------------------------------------------------------------------
--}}

@php $rp = $routePrefix ?? 'faculty.syllabi'; @endphp
<div class="sv-partial" data-partial-key="iga">
<form id="igaForm" method="POST" action="{{ route($rp . '.iga.update', $default['id'] ?? $syllabus->id ?? null) }}">
  @csrf
  @method('PUT')

  @php
    $igasSorted = ($igas ?? collect())->sortBy('sort_order')->values();
  @endphp

  <style>
    /* keep title typography consistent with other CIS modules */
    .iga-left-title { font-weight: 700; padding: 0.75rem; font-family: Georgia, serif; vertical-align: top; box-sizing: border-box; line-height: 1.2; }
    .ilo-table-wrap { width: 100%; min-width: 0; }
    /* ensure consistent fixed layout so colgroup widths are respected and labels wrap */
    table.cis-table { table-layout: fixed; }
    table.cis-table th.cis-label { white-space: normal; overflow-wrap: break-word; word-break: break-word; }
    /* prevent cell contents from overflowing the cell box */
    table.cis-table td, table.cis-table th { overflow: hidden; }
  /* Make inner table fill the right cell container and sit flush */
  #iga-right-wrap { padding: 0; margin: 0; }
  #iga-right-wrap > table { width: 100%; height: 100%; margin: 0; border-spacing: 0; border-collapse: collapse; }
  /* inner table cell padding so content is flush with container */
  #iga-right-wrap td, #iga-right-wrap th { vertical-align: middle; padding: 0.5rem 0.5rem; }
  /* force header text style (IGA code + description) to Times New Roman 10pt black */
  #iga-right-wrap > table thead th.cis-label { color:#000 !important; font-family:'Times New Roman', Times, serif !important; font-size:10pt !important; }
  /* Make the first header cell ("IGA") shrink to content and avoid growing */
  #iga-right-wrap > table thead th.cis-label:first-child { white-space: nowrap; width:1%; }
  /* show cell borders for the inner IGA table (internal grid only) – now forced black */
  #iga-right-wrap > table th, #iga-right-wrap > table td { border: 1px solid #000; }
  /* hide outer edges so only internal dividers remain */
  #iga-right-wrap > table thead th { border-top: 0; }
  #iga-right-wrap > table th:first-child, #iga-right-wrap > table td:first-child { border-left: 0; }
  #iga-right-wrap > table th:last-child, #iga-right-wrap > table td:last-child { border-right: 0; }
  /* remove the bottom-most outer border line of the inner table (no visible border under last row) */
  #iga-right-wrap > table tbody tr:last-child td { border-bottom: 0 !important; }
  /* Ensure badge/code cell and grip align and don't push width */
  /* IGA badge: allow auto sizing (no forced min-width) so column shrinks to content */
  .iga-badge { display: inline-block; min-width: 0; width: auto; padding: 0; text-align: center; color:#000; white-space: normal; line-height: 1.1; }
  
  /* tighten header and first column padding to reduce visual height */
  /* Uniform 6.4px padding for IGA code header and code cells */
  #iga-right-wrap > table thead th.cis-label:first-child { padding: 6.4px !important; }
  #iga-right-wrap > table td:first-child, #iga-right-wrap > table th:first-child { padding: 6.4px !important; }
  /* Remove bottom border from IGA code cells with rowspan */
  #iga-right-wrap > table tbody tr.iga-title-row td:first-child { border-bottom: 0 !important; }
  /* Drag handle styling for IGA reorder */
  .drag-handle { width: 28px; display: inline-flex; justify-content: center; cursor: grab; align-self: center; }
  /* Make textarea fill remaining space and autosize */
  .cis-textarea { width: 100%; box-sizing: border-box; resize: none; }
  /* Align IGA textareas styling with Course Title textareas (single-line autosize) */
  #iga-right-wrap textarea.cis-textarea.autosize { overflow: hidden; }
  /* Ensure the left header cell aligns with other CIS module headers */
  table.cis-table th.cis-label, table.cis-table th { vertical-align: top; }
  /* Icon-only header buttons styled like Add Dept / syllabus toolbar */
  .iga-header-actions .btn {
    position: relative; padding: 0 !important;
    width: 2.2rem; height: 2.2rem; min-width: 2.2rem; min-height: 2.2rem;
    border-radius: 50% !important;
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--sv-card-bg, #fff);
    border: none; box-shadow: none; color: #000;
    transition: all 0.2s ease-in-out;
    line-height: 0; /* eliminate baseline gap for perfect centering */
  }
  .iga-header-actions .btn .bi { font-size: 1rem; width: 1rem; height: 1rem; line-height: 1; color: var(--sv-text, #000); }
  /* Center Feather SVG icons and give them a consistent size */
  .iga-header-actions .btn svg {
    width: 1.05rem; height: 1.05rem;
    display: block; margin: 0; vertical-align: middle;
    stroke: currentColor; /* inherit button/text color */
  }
  .iga-header-actions .btn:hover, .iga-header-actions .btn:focus {
    background: linear-gradient(135deg, rgba(255,240,235,.88), rgba(255,255,255,.46));
    backdrop-filter: blur(7px); -webkit-backdrop-filter: blur(7px);
    box-shadow: 0 4px 10px rgba(204,55,55,.12);
    color: #CB3737;
  }
  .iga-header-actions .btn:hover .bi, .iga-header-actions .btn:focus .bi { color: #CB3737; }
  .iga-header-actions .btn:active { transform: scale(.97); filter: brightness(.98); }
  </style>

  <table class="table table-bordered mb-4 cis-table">
    <colgroup>
      <col style="width:16%">
      <col style="width:84%">
    </colgroup>
    <tbody>
      <tr>
  <th id="iga-left-title" class="align-top text-start cis-label iga-left-title">Institutional Graduate Attributes (IGA)</th>
        <td id="iga-right-wrap">
          <table class="table mb-0" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.4; border: none; table-layout: fixed;">
            <colgroup>
              <col style="width:70px"> <!-- IGA code column fixed -->
              <col style="width:auto"> <!-- Content column flexes remaining -->
            </colgroup>
            <thead>
              <tr class="table-light">
                <th class="text-center align-middle cis-label">IGA</th>
                <th class="text-center align-middle cis-label">
                  <div class="d-flex justify-content-between align-items-center gap-2">
                    <span class="flex-grow-1 text-center">Institutional Graduate Attributes (IGA) Statements</span>
                    <span class="iga-header-actions d-inline-flex gap-1" style="white-space:nowrap;">
                        <button type="button" class="btn btn-sm" id="iga-load-predefined" title="Load Predefined IGAs" aria-label="Load Predefined IGAs" style="background:transparent;">
                          <i data-feather="download"></i>
                          <span class="visually-hidden">Load Predefined IGAs</span>
                        </button>
                        <button type="button" class="btn btn-sm" id="iga-add-header" title="Add IGA" aria-label="Add IGA" style="background:transparent;">
                          <i data-feather="plus"></i>
                          <span class="visually-hidden">Add IGA</span>
                        </button>
                    </span>
                  </div>
                </th>
              </tr>
            </thead>
            <tbody id="syllabus-iga-sortable" data-syllabus-id="{{ $default['id'] }}">
              @forelse($igasSorted as $index => $iga)
                @php $seqCode = 'IGA' . ($index + 1); @endphp
                <tr data-id="{{ $iga->id }}" class="iga-row">
                  <td class="text-center align-middle">
                    <div class="iga-badge fw-semibold">{{ $seqCode }}</div>
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span class="drag-handle text-muted" title="Drag to reorder"><i class="bi bi-grip-vertical"></i></span>
                      <div class="flex-grow-1 w-100">
                        <textarea
                          name="iga_titles[]"
                          class="cis-textarea cis-field autosize"
                          placeholder="Title"
                          rows="1"
                          style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;font-weight:700;"
                          required>{{ old("iga_titles.$index", $iga->title ?? '') }}</textarea>
                        <textarea
                          name="igas[]"
                          class="cis-textarea cis-field autosize"
                          placeholder="Description"
                          rows="1"
                          style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;"
                          required>{{ old("igas.$index", $iga->description) }}</textarea>
                      </div>
                      <input type="hidden" name="code[]" value="{{ $seqCode }}">
                      <button type="button" class="btn btn-sm btn-outline-danger btn-delete-iga ms-2" title="Delete IGA">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              @empty
                <tr id="iga-placeholder">
                  <td colspan="2" class="text-center text-muted py-4">
                    <p class="mb-2">No IGAs added yet.</p>
                    <p class="mb-0"><small>Click the <strong>+</strong> button above to add an IGA or <strong>Load Predefined</strong> to import IGAs.</small></p>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </td>
      </tr>
    </tbody>
  </table>

  {{-- Action area intentionally minimal; saving is handled by main syllabus Save button. --}}
</form>

</div>

{{-- Local IGA quick-save button removed; toolbar Save handles persistence --}}

@push('scripts')
  @vite('resources/js/faculty/syllabus-iga.js')
@endpush

{{-- Load Predefined IGAs Modal --}}
<div class="modal fade sv-ilo-modal" id="loadPredefinedIgasModal" tabindex="-1" aria-labelledby="loadPredefinedIgasModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <style>
        /* Synced with ILO load modal styling */
        #loadPredefinedIgasModal {
          --sv-bg:   #FAFAFA;
          --sv-bdr:  #E3E3E3;
          --sv-acct: #EE6F57;
          --sv-danger:#CB3737;
          z-index: 10010 !important;
        }
        #loadPredefinedIgasModal .modal-dialog,
        #loadPredefinedIgasModal .modal-content { position: relative; z-index: 10011; }
        #loadPredefinedIgasModal .modal-header {
          padding: .85rem 1rem;
          border-bottom: 1px solid var(--sv-bdr);
          background: #fff;
        }
        #loadPredefinedIgasModal .modal-title {
          font-weight: 600;
          font-size: 1rem;
          display: inline-flex;
          align-items: center;
          gap: .5rem;
        }
        #loadPredefinedIgasModal .modal-title i,
        #loadPredefinedIgasModal .modal-title svg {
          width: 1.05rem;
          height: 1.05rem;
          stroke: var(--sv-text-muted, #777777);
        }
        /* Scrollable modal layout */
        #loadPredefinedIgasModal .modal-dialog { max-width: 680px; }
        #loadPredefinedIgasModal .modal-content { max-height: 85vh; display: flex; flex-direction: column; }
        #loadPredefinedIgasModal .modal-body { flex: 1 1 auto; overflow-y: auto; overscroll-behavior: contain; }
        #loadPredefinedIgasModal .modal-content {
          border-radius: 16px;
          border: 1px solid var(--sv-bdr);
          background: #fff;
          box-shadow: 0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06);
          overflow: hidden;
        }
        #loadPredefinedIgasModal .alert {
          border-radius: 12px;
          padding: .75rem 1rem;
          font-size: .875rem;
        }
        #loadPredefinedIgasModal .alert-warning {
          background: #FFF3CD; /* solid warning background */
          border: 1px solid #FFE69C;
          color: #856404;
        }
        /* Primary (Load) button adopts grey accent like ILO modal */
        #loadPredefinedIgasModal .btn-danger {
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
        }
        #loadPredefinedIgasModal .btn-danger { font-size: 0.95rem; }
        #loadPredefinedIgasModal .btn-danger i,
        #loadPredefinedIgasModal .btn-danger svg { width: 1.05rem; height: 1.05rem; }
        #loadPredefinedIgasModal .btn-danger:hover,
        #loadPredefinedIgasModal .btn-danger:focus {
          background: linear-gradient(135deg, rgba(220, 220, 220, 0.88), rgba(240, 240, 240, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
          color: #000;
        }
        #loadPredefinedIgasModal .btn-danger:hover i,
        #loadPredefinedIgasModal .btn-danger:hover svg,
        #loadPredefinedIgasModal .btn-danger:focus i,
        #loadPredefinedIgasModal .btn-danger:focus svg { stroke: #000; }
        #loadPredefinedIgasModal .btn-danger:active {
          background: linear-gradient(135deg, rgba(240, 242, 245, 0.98), rgba(255, 255, 255, 0.62));
          box-shadow: 0 1px 8px rgba(0, 0, 0, 0.16);
          color: #000;
        }
        #loadPredefinedIgasModal .btn-danger:disabled { opacity: .6; cursor: not-allowed; }
        /* Cancel button */
        #loadPredefinedIgasModal .btn-light {
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
        }
        #loadPredefinedIgasModal .btn-light { font-size: 0.95rem; }
        #loadPredefinedIgasModal .btn-light i,
        #loadPredefinedIgasModal .btn-light svg { stroke: #000; width: 1.05rem; height: 1.05rem; }
        #loadPredefinedIgasModal .btn-light:hover,
        #loadPredefinedIgasModal .btn-light:focus {
          background: linear-gradient(135deg, rgba(220,220,220,.88), rgba(240,240,240,.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(108,117,125,.12);
          color: #495057;
        }
        #loadPredefinedIgasModal .btn-light:active {
          background: linear-gradient(135deg, rgba(240,242,245,.98), rgba(255,255,255,.62));
          box-shadow: 0 1px 8px rgba(108,117,125,.16);
        }
        /* Checkbox styling consistent */
        #loadPredefinedIgasModal .form-check-input { background-color: #E8E8E8; border-color: #CCCCCC; }
        #loadPredefinedIgasModal .form-check-input:checked { background-color: #6C757D; border-color: #6C757D; }
        #loadPredefinedIgasModal .form-check-input:focus { border-color: #999; box-shadow: 0 0 0 0.25rem rgba(108,117,125,.25); }
      </style>

      <div class="modal-header">
        <h5 class="modal-title" id="loadPredefinedIgasModalLabel">
          <i data-feather="download"></i>
          <span>Load Predefined IGAs</span>
        </h5>
      </div>

      <div class="modal-body">
        <div class="alert alert-warning" role="alert">
          <strong>Warning:</strong> This will replace all current IGAs with the selected predefined ones from the master data. This action cannot be undone.
        </div>
        
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="selectAllIgas" checked>
            <label class="form-check-label fw-semibold" for="selectAllIgas">
              Select All
            </label>
          </div>
          <hr class="my-2">
          <div id="igaSelectionList" style="max-height: 300px; overflow-y: auto;">
            <div class="text-center text-muted py-3">
              <i data-feather="loader" class="spinner"></i>
              <p class="mb-0 mt-2">Loading IGAs...</p>
            </div>
          </div>
        </div>

            <script>
              document.addEventListener('DOMContentLoaded', function(){
                try {
                  const modal = document.getElementById('loadPredefinedIgasModal');
                  if (modal && modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                    modal.style.zIndex = '10010';
                    const dlg = modal.querySelector('.modal-dialog'); if (dlg) dlg.style.zIndex = '10011';
                  }
                } catch(e){ console.error('IGA modal relocation failed', e); }
              });
            </script>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
          <i data-feather="x"></i>
          Cancel
        </button>
        <button type="button" class="btn btn-danger" id="confirmLoadPredefinedIgas">
          <i data-feather="download"></i>
          Load IGAs
        </button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  /**
   * Register IGAs with validation system
   */
  function registerValidationField() {
    if (typeof window.addRequiredField === 'function') {
      window.addRequiredField('iga', 'igas[]', 'Institutional Graduate Attributes');
      setupTableMutationObserver();
    } else {
      // Retry if validation system not ready
      setTimeout(registerValidationField, 500);
    }
  }

  /**
   * Monitor table mutations for IGA changes
   */
  function setupTableMutationObserver() {
    const igaList = document.getElementById('syllabus-iga-sortable');
    if (!igaList) return;

    const observer = new MutationObserver(() => {
      if (typeof window.updateProgressBar === 'function') {
        window.updateProgressBar();
      }
    });

    observer.observe(igaList, {
      childList: true,
      subtree: true,
      characterData: true,
      attributes: false,
      attributeOldValue: false,
      characterDataOldValue: false,
    });
  }

  // Register field and setup mutation observer on page load
  document.addEventListener('DOMContentLoaded', registerValidationField);
</script>
@endpush
