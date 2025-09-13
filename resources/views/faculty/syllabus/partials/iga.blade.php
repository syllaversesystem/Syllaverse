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
  /* show cell borders for the inner ILO table (internal grid only) */
  #iga-right-wrap > table th, #iga-right-wrap > table td { border: 1px solid #dee2e6; }
  /* hide outer edges so only internal dividers remain */
  #iga-right-wrap > table thead th { border-top: 0; }
  #iga-right-wrap > table th:first-child, #iga-right-wrap > table td:first-child { border-left: 0; }
  #iga-right-wrap > table th:last-child, #iga-right-wrap > table td:last-child { border-right: 0; }
  /* remove the bottom-most outer border line of the inner table (no visible border under last row) */
  #iga-right-wrap > table tbody tr:last-child td { border-bottom: 0 !important; }
  /* Ensure badge/code cell and grip align and don't push width */
  .iga-badge { display: inline-block; min-width: 48px; text-align: center; }
  .drag-handle { width: 28px; display: inline-flex; justify-content: center; }
  /* Make textarea fill remaining space and autosize */
  .cis-textarea { width: 100%; box-sizing: border-box; resize: none; }
  /* Allow IGA textareas to collapse to a single-line look (match Course Title feel) */
  #iga-right-wrap textarea.cis-textarea.autosize { min-height: 34px; overflow: hidden; }
  /* Ensure the left header cell aligns with other CIS module headers */
  table.cis-table th.cis-label, table.cis-table th { vertical-align: top; }
  </style>

  <table class="table table-bordered mb-4 cis-table">
    <colgroup>
      <col style="width:16%">
      <col style="width:84%">
    </colgroup>
    <tbody>
      <tr>
  <th id="iga-left-title" class="align-top text-start cis-label iga-left-title">Institutional Graduate Attributes (IGA)
          <span id="unsaved-igas" class="unsaved-pill d-none">Unsaved</span>
        </th>
        <td id="iga-right-wrap">
          <table class="table mb-0" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.4; border: none;">
            <colgroup>
              <col style="width: 10%"> <!-- code column -->
              <col style="width: 90%"> <!-- description -->
            </colgroup>
            <thead>
              <tr class="table-light">
                <th class="text-center cis-label">IGA</th>
                <th class="text-start cis-label">Institutional Graduate Attribute / Notes</th>
              </tr>
            </thead>
            <tbody id="syllabus-iga-sortable" data-syllabus-id="{{ $default['id'] }}">
              @if($igasSorted->count())
                @foreach ($igasSorted as $index => $iga)
                  @php $seqCode = 'IGA' . ($index + 1); @endphp
                  <tr data-id="{{ $iga->id }}">
                    <td class="text-center align-middle">
                      <div class="iga-badge fw-semibold">{{ $seqCode }}</div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;">
                          <i class="bi bi-grip-vertical"></i>
                        </span>
                        <textarea name="igas[]" class="form-control cis-textarea autosize flex-grow-1" data-original="{{ old("igas.$index", $iga->description) }}" required>{{ old("igas.$index", $iga->description) }}</textarea>
                        <input type="hidden" name="code[]" value="{{ $seqCode }}" data-original-code="{{ $iga->code }}">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-iga ms-2" title="Delete IGA">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                @endforeach
              @else
                <tr>
                  <td class="text-center align-middle">
                    <div class="iga-badge fw-semibold">IGA1</div>
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;">
                        <i class="bi bi-grip-vertical"></i>
                      </span>
                      <textarea name="igas[]" class="form-control cis-textarea autosize flex-grow-1" required></textarea>
                      <input type="hidden" name="code[]" value="IGA1" data-original-code="">
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
  // minimal inline autosize and keyboard hooks for IGA matching ILO behaviors
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

      // Support Ctrl+Enter to add row (clone behavior similar to ILO but without AT sync)
      list.addEventListener('keydown', function(ev){
        const target = ev.target; if (!target || target.tagName !== 'TEXTAREA') return;
        if (ev.key === 'Enter' && (ev.ctrlKey || ev.metaKey)) {
          ev.preventDefault();
          const tr = target.closest('tr'); if (!tr) return;
          const newRow = tr.cloneNode(true);
          newRow.removeAttribute('data-id');
          newRow.querySelectorAll('textarea').forEach(t => t.value = '');
          newRow.querySelectorAll('input[type="hidden"]').forEach(i => i.value = '');
          tr.parentNode.insertBefore(newRow, tr.nextSibling);
          newRow.querySelectorAll('textarea.autosize').forEach(bindAutosize);
          const nta = newRow.querySelector('textarea'); if (nta) { setTimeout(() => nta.focus(), 10); }
        }
      });
    });
  })();
</script>

@push('scripts')
  @vite('resources/js/faculty/syllabus-iga-sortable.js')
@endpush
