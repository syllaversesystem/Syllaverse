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
                  <div class="d-flex justify-content-between align-items-start gap-2">
                    <span class="flex-grow-1 text-center">CDIO Framework Skills Statements</span>
                    <span class="cdio-header-actions d-inline-flex gap-1" style="white-space:nowrap;">
                      <button type="button" class="btn btn-sm" id="cdio-add-header" title="Add CDIO" aria-label="Add CDIO" style="background:transparent;">
                        <i data-feather="plus"></i>
                        <span class="visually-hidden">Add CDIO</span>
                      </button>
                      <button type="button" class="btn btn-sm" id="cdio-remove-header" title="Remove last CDIO" aria-label="Remove last CDIO" style="background:transparent;">
                        <i data-feather="minus"></i>
                        <span class="visually-hidden">Remove last CDIO</span>
                      </button>
                    </span>
                  </div>
                </th>
              </tr>
            </thead>
            <tbody id="syllabus-cdio-sortable" data-syllabus-id="{{ $default['id'] }}">
              @if($cdiosSorted->count())
                @foreach ($cdiosSorted as $index => $cdio)
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
                            placeholder="-"
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
                @endforeach
              @else
                <tr>
                  <td class="text-center align-middle"><div class="cdio-badge">CDIO1</div></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;"><i class="bi bi-grip-vertical"></i></span>
                      <div class="flex-grow-1 w-100">
                        <textarea
                          name="cdio_titles[]"
                          class="cis-textarea cis-field autosize"
                          placeholder="-"
                          rows="1"
                          style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;font-weight:700;"
                          required></textarea>
                        <textarea
                          name="cdios[]"
                          class="cis-textarea cis-field autosize"
                          placeholder="Description"
                          rows="1"
                          style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;"
                          required></textarea>
                      </div>
                      <input type="hidden" name="code[]" value="CDIO1">
                      <button type="button" class="btn btn-sm btn-outline-danger btn-delete-cdio ms-2" title="Delete CDIO"><i class="bi bi-trash"></i></button>
                    </div>
                  </td>
                </tr>
              @endif
            </tbody>
          </table>
        </td>
      </tr>
    </tbody>
  </table>

</form>

@push('scripts')
  @vite([
  'resources/js/faculty/syllabus-cdio.js'
  ])
@endpush

