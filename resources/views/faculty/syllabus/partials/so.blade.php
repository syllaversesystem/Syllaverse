{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/so.blade.php
* Description: CIS-style SO layout with drag-safe table structure – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-07-29] Updated layout to match ILO format (flat rows, sortable-safe, delete button included).
-------------------------------------------------------------------------------
--}}

@php $rp = $routePrefix ?? 'faculty.syllabi'; @endphp
<form id="soForm" method="POST" action="{{ route($rp . '.sos.update', $default['id']) }}">
  @csrf
  @method('PUT')

  @php
    $sosSorted = ($sos ?? collect())->sortBy('position')->values();
  @endphp

  <style>
    /* keep title typography consistent with other CIS modules */
    .so-left-title { font-weight: 700; padding: 0.75rem; font-family: Georgia, serif; vertical-align: top; box-sizing: border-box; line-height: 1.2; }
    table.cis-table { table-layout: fixed; }
    table.cis-table th.cis-label { white-space: normal; overflow-wrap: break-word; word-break: break-word; }
    table.cis-table td, table.cis-table th { overflow: hidden; }
    /* Make inner table fill the right cell container and sit flush */
    #so-right-wrap { padding: 0; margin: 0; }
    #so-right-wrap > table { width: 100%; height: 100%; margin: 0; border-spacing: 0; border-collapse: collapse; }
    /* inner table cell padding so content is flush with container */
    #so-right-wrap td, #so-right-wrap th { vertical-align: middle; padding: 0.5rem 0.5rem; }
    /* force header text style (SO code + description) to Times New Roman 10pt black */
    #so-right-wrap > table thead th.cis-label { color:#000 !important; font-family:'Times New Roman', Times, serif !important; font-size:10pt !important; }
    /* Make the first header cell ("SO") shrink to content and avoid growing */
    #so-right-wrap > table thead th.cis-label:first-child { white-space: nowrap; width:1%; }
    /* show cell borders for the inner SO table (internal grid only) – now forced black */
    #so-right-wrap > table th, #so-right-wrap > table td { border: 1px solid #000; }
    /* hide outer edges so only internal dividers remain */
    #so-right-wrap > table thead th { border-top: 0; }
    #so-right-wrap > table th:first-child, #so-right-wrap > table td:first-child { border-left: 0; }
    #so-right-wrap > table th:last-child, #so-right-wrap > table td:last-child { border-right: 0; }
    /* remove the bottom-most outer border line of the inner table (no visible border under last row) */
    #so-right-wrap > table tbody tr:last-child td { border-bottom: 0 !important; }
    /* SO badge: allow auto sizing (no forced min-width) so column shrinks to content */
    .so-badge { display: inline-block; min-width: 0; width: auto; padding: 0; text-align: center; color:#000; white-space: normal; line-height: 1.1; }
    
    /* tighten header and first column padding to reduce visual height */
    /* Uniform 6.4px padding for SO code header and code cells */
    #so-right-wrap > table thead th.cis-label:first-child { padding: 6.4px !important; }
    #so-right-wrap > table td:first-child, #so-right-wrap > table th:first-child { padding: 6.4px !important; }
    .drag-handle { width: 28px; display: inline-flex; justify-content: center; align-self: center; }
    /* Make textarea fill remaining space and autosize */
    .cis-textarea { width: 100%; box-sizing: border-box; resize: none; }
    /* Align SO textareas styling with Course Title textareas (single-line autosize) */
    #so-right-wrap textarea.cis-textarea.autosize { overflow: hidden; }
    /* Ensure the left header cell aligns with other CIS module headers */
    table.cis-table th.cis-label, table.cis-table th { vertical-align: top; }
    /* Icon-only header buttons styled like IGA */
    .so-header-actions .btn {
      position: relative; padding: 0 !important;
      width: 2.2rem; height: 2.2rem; min-width: 2.2rem; min-height: 2.2rem;
      border-radius: 50% !important;
      display: inline-flex; align-items: center; justify-content: center;
      background: var(--sv-card-bg, #fff);
      border: none; box-shadow: none; color: #000;
      transition: all 0.2s ease-in-out;
      line-height: 0;
    }
    .so-header-actions .btn .bi { font-size: 1rem; width: 1rem; height: 1rem; line-height: 1; color: var(--sv-text, #000); }
    .so-header-actions .btn svg {
      width: 1.05rem; height: 1.05rem;
      display: block; margin: 0; vertical-align: middle;
      stroke: currentColor;
    }
    .so-header-actions .btn:hover, .so-header-actions .btn:focus {
      background: linear-gradient(135deg, rgba(255,240,235,.88), rgba(255,255,255,.46));
      backdrop-filter: blur(7px); -webkit-backdrop-filter: blur(7px);
      box-shadow: 0 4px 10px rgba(204,55,55,.12);
      color: #CB3737;
    }
    .so-header-actions .btn:hover .bi, .so-header-actions .btn:focus .bi { color: #CB3737; }
    .so-header-actions .btn:active { transform: scale(.97); filter: brightness(.98); }
  </style>

  <table class="table table-bordered mb-4 cis-table">
    <colgroup>
      <col style="width:16%">
      <col style="width:84%">
    </colgroup>
    <tbody>
      <tr>
        <th class="align-top text-start cis-label so-left-title">Student Outcomes (SO)
          <span id="unsaved-sos" class="unsaved-pill d-none">Unsaved</span>
        </th>
  <td id="so-right-wrap">
          <table class="table mb-0" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.4; border: none; table-layout: fixed;">
            <colgroup>
              <col style="width:70px"> <!-- SO code column fixed -->
              <col style="width:auto"> <!-- Description column flexes remaining -->
            </colgroup>
            <thead>
              <tr class="table-light">
                <th class="text-center cis-label">SO</th>
                <th class="text-center cis-label">
                  <div class="d-flex justify-content-between align-items-start gap-2">
                    <span class="flex-grow-1 text-center">Student Outcomes (SO) Statements</span>
                    <span class="so-header-actions d-inline-flex gap-1" style="white-space:nowrap;">
                      <button type="button" class="btn btn-sm" id="so-add-header" title="Add SO" aria-label="Add SO" style="background:transparent;">
                        <i data-feather="plus"></i>
                        <span class="visually-hidden">Add SO</span>
                      </button>
                      <button type="button" class="btn btn-sm" id="so-remove-header" title="Remove last SO" aria-label="Remove last SO" style="background:transparent;">
                        <i data-feather="minus"></i>
                        <span class="visually-hidden">Remove last SO</span>
                      </button>
                    </span>
                  </div>
                </th>
              </tr>
            </thead>
            <tbody id="syllabus-so-sortable" data-syllabus-id="{{ $default['id'] }}">
              @if($sosSorted->count())
                @foreach ($sosSorted as $index => $so)
                  @php $seqCode = $so->code ?? 'SO' . ($index + 1); @endphp
                  <tr data-id="{{ $so->id }}">
                    <td class="text-center align-middle">
                      <div class="so-badge fw-semibold">{{ $seqCode }}</div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;"><i class="bi bi-grip-vertical"></i></span>
                        <div class="flex-grow-1 w-100">
                          <textarea
                            name="so_titles[]"
                            class="cis-textarea cis-field autosize"
                            data-original="{{ old("so_titles.$index", $so->title ?? '') }}"
                            placeholder="-"
                            rows="1"
                            style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;font-weight:700;"
                            required>{{ old("so_titles.$index", $so->title ?? '') }}</textarea>
                          <textarea
                            name="sos[]"
                            class="cis-textarea cis-field autosize"
                            data-original="{{ old("sos.$index", $so->description) }}"
                            placeholder="Description"
                            rows="1"
                            style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;"
                            required>{{ old("sos.$index", $so->description) }}</textarea>
                        </div>
                        <input type="hidden" name="code[]" value="{{ $seqCode }}">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-so ms-2" title="Delete SO"><i class="bi bi-trash"></i></button>
                      </div>
                    </td>
                  </tr>
                @endforeach
              @else
                <tr>
                  <td class="text-center align-middle"><div class="so-badge fw-semibold">SO1</div></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;"><i class="bi bi-grip-vertical"></i></span>
                      <div class="flex-grow-1 w-100">
                        <textarea
                          name="so_titles[]"
                          class="cis-textarea cis-field autosize"
                          placeholder="-"
                          rows="1"
                          style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;font-weight:700;"
                          required></textarea>
                        <textarea
                          name="sos[]"
                          class="cis-textarea cis-field autosize"
                          placeholder="Description"
                          rows="1"
                          style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;"
                          required></textarea>
                      </div>
                      <input type="hidden" name="code[]" value="SO1">
                      <button type="button" class="btn btn-sm btn-outline-danger btn-delete-so ms-2" title="Delete SO"><i class="bi bi-trash"></i></button>
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

  {{-- Controls removed: Add Row / Save Order / Save All are handled via top Save and programmatic APIs now --}}
</form>

@push('scripts')
  @vite([
    'resources/js/faculty/syllabus-so.js',
    'resources/js/faculty/syllabus-so-sortable.js'
  ])
@endpush


