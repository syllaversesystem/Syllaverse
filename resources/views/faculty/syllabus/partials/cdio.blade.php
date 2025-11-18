{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/cdio.blade.php
* Description: CDIO Mapping — placeholder CIS-style table (Blade only)
-------------------------------------------------------------------------------
--}}

{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/cdio.blade.php
* Description: CDIO Mapping — placeholder CIS-style table (Blade only)
-------------------------------------------------------------------------------
--}}

@php $rp = $routePrefix ?? 'faculty.syllabi'; @endphp
<form id="cdioForm" method="POST" action="{{ route($rp . '.cdios.update', $default['id']) }}">
  @csrf
  @method('PUT')

  @php
    $cdiosSorted = ($cdios ?? collect())->sortBy('position')->values();
  @endphp

  <style>
    /* keep title typography consistent with other CIS modules */
    .cdio-left-title { font-weight: 700; padding: 0.75rem; font-family: Georgia, serif; vertical-align: top; box-sizing: border-box; line-height: 1.2; }
    table.cis-table { table-layout: fixed; }
    table.cis-table th.cis-label { white-space: normal; overflow-wrap: break-word; word-break: break-word; }
    table.cis-table td, table.cis-table th { overflow: hidden; }
    /* Make inner table fill the right cell container and sit flush */
    #cdio-right-wrap { padding: 0; margin: 0; }
    #cdio-right-wrap > table { width: 100%; height: 100%; margin: 0; border-spacing: 0; border-collapse: collapse; }
    /* inner table cell padding so content is flush with container */
    #cdio-right-wrap td, #cdio-right-wrap th { vertical-align: middle; padding: 0.5rem 0.5rem; }
    /* force header text style (CDIO code + description) to Times New Roman 10pt black */
    #cdio-right-wrap > table thead th.cis-label { color:#000 !important; font-family:'Times New Roman', Times, serif !important; font-size:10pt !important; }
    /* Make the first header cell ("CDIO") shrink to content and avoid growing */
    #cdio-right-wrap > table thead th.cis-label:first-child { white-space: nowrap; width:1%; }
    /* show cell borders for the inner CDIO table (internal grid only) – now forced black */
    #cdio-right-wrap > table th, #cdio-right-wrap > table td { border: 1px solid #000; }
    /* hide outer edges so only internal dividers remain */
    #cdio-right-wrap > table thead th { border-top: 0; }
    #cdio-right-wrap > table th:first-child, #cdio-right-wrap > table td:first-child { border-left: 0; }
    #cdio-right-wrap > table th:last-child, #cdio-right-wrap > table td:last-child { border-right: 0; }
    /* remove the bottom-most outer border line of the inner table (no visible border under last row) */
    #cdio-right-wrap > table tbody tr:last-child td { border-bottom: 0 !important; }
    /* CDIO badge: allow auto sizing (no forced min-width) so column shrinks to content */
    .cdio-badge { display: inline-block; min-width: 0; width: auto; padding: 0; text-align: center; color:#000; white-space: normal; line-height: 1.1; font-weight: 700; }
    
    /* tighten header and first column padding to reduce visual height */
    /* Uniform 6.4px padding for CDIO code header and code cells */
    #cdio-right-wrap > table thead th.cis-label:first-child { padding: 6.4px !important; }
    #cdio-right-wrap > table td:first-child, #cdio-right-wrap > table th:first-child { padding: 6.4px !important; }
    .drag-handle { width: 28px; display: inline-flex; justify-content: center; align-self: center; }
    /* Make textarea fill remaining space and autosize */
    .cis-textarea { width: 100%; box-sizing: border-box; resize: none; }
    /* Align CDIO textareas styling with Course Title textareas (single-line autosize) */
    #cdio-right-wrap textarea.cis-textarea.autosize { overflow: hidden; }
    /* Ensure the left header cell aligns with other CIS module headers */
    table.cis-table th.cis-label, table.cis-table th { vertical-align: top; }
    /* Icon-only header buttons styled like IGA/SO */
    .cdio-header-actions .btn {
      position: relative; padding: 0 !important;
      width: 2.2rem; height: 2.2rem; min-width: 2.2rem; min-height: 2.2rem;
      border-radius: 50% !important;
      display: inline-flex; align-items: center; justify-content: center;
      background: var(--sv-card-bg, #fff);
      border: none; box-shadow: none; color: #000;
      transition: all 0.2s ease-in-out; line-height: 0;
    }
    .cdio-header-actions .btn .bi { font-size: 1rem; width: 1rem; height: 1rem; line-height: 1; color: var(--sv-text, #000); }
    .cdio-header-actions .btn svg { width: 1.05rem; height: 1.05rem; display: block; margin: 0; vertical-align: middle; stroke: currentColor; }
    .cdio-header-actions .btn:hover, .cdio-header-actions .btn:focus { background: linear-gradient(135deg, rgba(255,240,235,.88), rgba(255,255,255,.46)); backdrop-filter: blur(7px); -webkit-backdrop-filter: blur(7px); box-shadow: 0 4px 10px rgba(204,55,55,.12); color: #CB3737; }
    .cdio-header-actions .btn:hover .bi, .cdio-header-actions .btn:focus .bi { color: #CB3737; }
    .cdio-header-actions .btn:active { transform: scale(.97); filter: brightness(.98); }
  </style>

  <table class="table table-bordered mb-4 cis-table">
    <colgroup>
      <col style="width:16%">
      <col style="width:84%">
    </colgroup>
    <tbody>
      <tr>
        <th class="align-top text-start cis-label cdio-left-title">CDIO Framework Skills (CDIO)
          <span id="unsaved-cdios" class="unsaved-pill d-none">Unsaved</span>
        </th>
        <td id="cdio-right-wrap">
          <table class="table mb-0" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.4; border: none; table-layout: fixed;">
            <colgroup>
              <col style="width:70px"> <!-- CDIO code column fixed -->
              <col style="width:auto"> <!-- Description column flexes remaining -->
            </colgroup>
            <thead>
              <tr class="table-light">
                <th class="text-center cis-label">CDIO</th>
                <th class="text-center cis-label">
                  <div class="d-flex justify-content-between align-items-center gap-2">
                    <span class="flex-grow-1 text-center">CDIO Framework Skills Statements</span>
                    <span class="cdio-header-actions d-inline-flex gap-1" style="white-space:nowrap;">
                      <button type="button" class="btn btn-sm" id="cdio-load-predefined" title="Load Predefined CDIOs" aria-label="Load Predefined CDIOs" style="background:transparent;">
                        <i data-feather="download"></i>
                        <span class="visually-hidden">Load Predefined CDIOs</span>
                      </button>
                      <button type="button" class="btn btn-sm" id="cdio-add-header" title="Add CDIO" aria-label="Add CDIO" style="background:transparent;">
                        <i data-feather="plus"></i>
                        <span class="visually-hidden">Add CDIO</span>
                      </button>
                    </span>
                  </div>
                </th>
              </tr>
            </thead>
            <tbody id="syllabus-cdio-sortable" data-syllabus-id="{{ $default['id'] }}" data-department-id="{{ $deptId ?? '' }}">
              @forelse ($cdiosSorted as $index => $cdio)
                @php $seqCode = $cdio->code ?? 'CDIO' . ($index + 1); @endphp
                <tr data-id="{{ $cdio->id }}">
                  <td class="text-center align-middle">
                    <div class="cdio-badge">{{ $seqCode }}</div>
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;"><i class="bi bi-grip-vertical"></i></span>
                      <div class="flex-grow-1 w-100">
                        <textarea
                          name="cdio_titles[]"
                          class="cis-textarea cis-field autosize"
                          data-original="{{ old("cdio_titles.$index", $cdio->title ?? '') }}"
                          placeholder="Title"
                          rows="1"
                          style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;font-weight:700;"
                          required>{{ old("cdio_titles.$index", $cdio->title ?? '') }}</textarea>
                        <textarea
                          name="cdios[]"
                          class="cis-textarea cis-field autosize"
                          data-original="{{ old("cdios.$index", $cdio->description) }}"
                          placeholder="Description"
                          rows="1"
                          style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;"
                          required>{{ old("cdios.$index", $cdio->description) }}</textarea>
                      </div>
                      <input type="hidden" name="code[]" value="{{ $seqCode }}">
                      <button type="button" class="btn btn-sm btn-outline-danger btn-delete-cdio ms-2" title="Delete CDIO"><i class="bi bi-trash"></i></button>
                    </div>
                  </td>
                </tr>
              @empty
                <tr id="cdio-placeholder">
                  <td colspan="2" class="text-center text-muted py-4">
                    <p class="mb-2">No CDIOs added yet.</p>
                    <p class="mb-0"><small>Click the <strong>+</strong> button above to add a CDIO or <strong>Load Predefined</strong> to import CDIOs.</small></p>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </td>
      </tr>
    </tbody>
  </table>

</form>

{{-- Load Predefined CDIOs Modal --}}
<div class="modal fade sv-cdio-modal" id="loadPredefinedCdiosModal" tabindex="-1" aria-labelledby="loadPredefinedCdiosModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      @csrf

      <style>
        #loadPredefinedCdiosModal {
          --sv-bg:   #FAFAFA;
          --sv-bdr:  #E3E3E3;
          --sv-acct: #EE6F57;
          --sv-danger:#CB3737;
        }
        #loadPredefinedCdiosModal .modal-header {
          padding: .85rem 1rem;
          border-bottom: 1px solid var(--sv-bdr);
          background: #fff;
        }
        #loadPredefinedCdiosModal .modal-title {
          font-weight: 600;
          font-size: 1rem;
          display: inline-flex;
          align-items: center;
          gap: .5rem;
        }
        #loadPredefinedCdiosModal .modal-title i,
        #loadPredefinedCdiosModal .modal-title svg {
          width: 1.05rem;
          height: 1.05rem;
          stroke: var(--sv-text-muted, #777777);
        }
        #loadPredefinedCdiosModal .modal-content {
          border-radius: 16px;
          border: 1px solid var(--sv-bdr);
          background: #fff;
          box-shadow: 0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06);
          overflow: hidden;
        }
        #loadPredefinedCdiosModal .alert {
          border-radius: 12px;
          padding: .75rem 1rem;
          font-size: .875rem;
        }
        #loadPredefinedCdiosModal .alert-warning {
          background: linear-gradient(135deg, rgba(255, 243, 205, 0.88), rgba(255, 255, 255, 0.46));
          border: 1px solid rgba(255, 193, 7, 0.3);
          color: #856404;
        }
        #loadPredefinedCdiosModal .btn-danger {
          background: var(--sv-card-bg, #fff);
          border: none;
          color: #000;
          transition: all 0.2s ease-in-out;
        }
        #loadPredefinedCdiosModal .btn-danger:hover {
          background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(204, 55, 55, 0.12);
          color: #CB3737;
        }
      </style>

      <div class="modal-header">
        <h5 class="modal-title" id="loadPredefinedCdiosModalLabel">
          <i data-feather="download"></i>
          <span>Load Predefined CDIOs</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning mb-3">
          <i data-feather="alert-triangle" style="width:16px;height:16px;vertical-align:text-bottom;"></i>
          <strong>Warning:</strong> Loading predefined CDIOs will replace all current CDIOs.
        </div>
        <p class="mb-2 fw-semibold">Select CDIOs to load:</p>
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="selectAllCdios" checked>
            <label class="form-check-label fw-semibold" for="selectAllCdios">
              Select All
            </label>
          </div>
          <hr class="my-2">
          <div id="cdioSelectionList" style="max-height: 300px; overflow-y: auto;">
            <div class="text-center text-muted py-3">
              <div class="spinner-border spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              Loading CDIOs...
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmLoadPredefinedCdios">Load Selected CDIOs</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
  @vite([
  'resources/js/faculty/syllabus-cdio.js'
  ])
@endpush

