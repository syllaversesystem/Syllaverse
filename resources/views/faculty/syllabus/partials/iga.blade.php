{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/iga.blade.php
* Description: Institutional Graduate Attributes (IGA) — placeholder CIS-style table
-------------------------------------------------------------------------------
--}}

@php $rp = $routePrefix ?? 'faculty.syllabi'; @endphp
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
                <th class="text-center cis-label">IGA</th>
                <th class="text-center cis-label">
                  <div class="d-flex justify-content-between align-items-start gap-2">
                    <span class="flex-grow-1 text-center">Institutional Graduate Attributes (IGA) Statements</span>
                    <span class="iga-header-actions d-inline-flex gap-1" style="white-space:nowrap;">
                        <button type="button" class="btn btn-sm" id="iga-add-header" title="Add IGA" aria-label="Add IGA" style="background:transparent;">
                          <i data-feather="plus"></i>
                          <span class="visually-hidden">Add IGA</span>
                        </button>
                        <button type="button" class="btn btn-sm" id="iga-remove-header" title="Remove last IGA" aria-label="Remove last IGA" style="background:transparent;">
                          <i data-feather="minus"></i>
                          <span class="visually-hidden">Remove last IGA</span>
                        </button>
                    </span>
                  </div>
                </th>
              </tr>
            </thead>
            <tbody id="syllabus-iga-sortable" data-syllabus-id="{{ $default['id'] }}">
              @if($igasSorted->count())
                @foreach ($igasSorted as $index => $iga)
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
                            placeholder="-"
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
                @endforeach
              @else
                <tr class="iga-row">
                  <td class="text-center align-middle">
                    <div class="iga-badge fw-semibold">IGA1</div>
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span class="drag-handle text-muted" title="Drag to reorder"><i class="bi bi-grip-vertical"></i></span>
                      <div class="flex-grow-1 w-100">
                        <textarea
                          name="iga_titles[]"
                          class="cis-textarea cis-field autosize"
                          placeholder="-"
                          rows="1"
                          style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;font-weight:700;"
                          required></textarea>
                        <textarea
                          name="igas[]"
                          class="cis-textarea cis-field autosize"
                          placeholder="Description"
                          rows="1"
                          style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;"
                          required></textarea>
                      </div>
                      <input type="hidden" name="code[]" value="IGA1">
                      <button type="button" class="btn btn-sm btn-outline-danger btn-delete-iga ms-2" title="Delete IGA">
                        <i class="bi bi-trash"></i>
                      </button>
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

  {{-- Action area intentionally minimal; saving is handled by main syllabus Save button. --}}
</form>

<script>
  // minimal inline autosize for IGA
  (function(){
    function autosizeEl(el){ try { el.style.height = 'auto'; el.style.height = (el.scrollHeight || 0) + 'px'; } catch(e) {} }
    function bindAutosize(ta){ if (!ta) return; autosizeEl(ta); ta.addEventListener('input', () => autosizeEl(ta)); }

    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('#syllabus-iga-sortable textarea.autosize').forEach(bindAutosize);
      const list = document.getElementById('syllabus-iga-sortable');
      if (!list) return;
      if (window.MutationObserver) {
        const mo = new MutationObserver((mutations) => { for (const m of mutations) { for (const node of m.addedNodes) { if (node && node.querySelectorAll) node.querySelectorAll('textarea.autosize').forEach(bindAutosize); } } });
        mo.observe(list, { childList: true, subtree: true });
      }

      // Add IGA button (single-row structure)
      document.getElementById('iga-add-header')?.addEventListener('click', () => {
        const tbody = document.getElementById('syllabus-iga-sortable');
        if (!tbody) return;
        const currentCount = tbody.querySelectorAll('.iga-row').length;
        const newIndex = currentCount + 1;
        const newCode = 'IGA' + newIndex;
        const row = document.createElement('tr');
        row.className = 'iga-row';
        row.setAttribute('data-id', `new-${Date.now()}`);
        row.innerHTML = `
          <td class="text-center align-middle">
            <div class="iga-badge fw-semibold">${newCode}</div>
          </td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <span class="drag-handle text-muted" title="Drag to reorder"><i class="bi bi-grip-vertical"></i></span>
              <div class="flex-grow-1 w-100">
                <textarea name="iga_titles[]" class="cis-textarea cis-field autosize" placeholder="-" rows="1" style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;font-weight:700;" required></textarea>
                <textarea name="igas[]" class="cis-textarea cis-field autosize" placeholder="Description" rows="1" style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;" required></textarea>
              </div>
              <input type="hidden" name="code[]" value="${newCode}">
              <button type="button" class="btn btn-sm btn-outline-danger btn-delete-iga ms-2" title="Delete IGA"><i class="bi bi-trash"></i></button>
            </div>
          </td>
        `;
        tbody.appendChild(row);
        row.querySelectorAll('textarea.autosize').forEach(bindAutosize);
        renumberIGAs();
      });

      // Remove last IGA button
      document.getElementById('iga-remove-header')?.addEventListener('click', () => {
        const tbody = document.getElementById('syllabus-iga-sortable');
        if (!tbody) return;
        const rows = tbody.querySelectorAll('.iga-row');
        if (rows.length >= 1) {
          rows[rows.length - 1].remove();
          renumberIGAs();
        }
      });

      // Delete is handled by external JS (confirmation + server delete if needed)

      // Renumber IGAs after add/remove
      function renumberIGAs() {
        const tbody = document.getElementById('syllabus-iga-sortable');
        if (!tbody) return;
        const rows = tbody.querySelectorAll('.iga-row');
        rows.forEach((row, index) => {
          const newCode = 'IGA' + (index + 1);
          const badge = row.querySelector('.iga-badge');
          if (badge) badge.textContent = newCode;
          const hiddenInput = row.querySelector('input[name="code[]"]');
          if (hiddenInput) hiddenInput.value = newCode;
        });
      }
    });
  })();
</script>

@push('scripts')
  @vite('resources/js/faculty/syllabus-iga-sortable.js')
@endpush
