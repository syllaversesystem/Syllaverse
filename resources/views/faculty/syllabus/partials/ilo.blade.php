{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/ilo.blade.php
* Description: CIS-style ILO layout with drag-safe structure – no rowspan – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-07-29] Aligned layout with SO structure.
[2025-07-29] Fixed broken drag-reorder issue by removing rowspan and using a static header row.
-------------------------------------------------------------------------------
--}}

@php $rp = $routePrefix ?? 'faculty.syllabi'; @endphp
<form id="iloForm" method="POST" action="{{ route($rp . '.ilos.update', $default['id']) }}">
  @csrf
  @method('PUT')

  @php
    $ilosSorted = $ilos->sortBy('position')->values();
    $iloCount = max(1, $ilosSorted->count());
  @endphp

  <style>
    /* keep title typography consistent with other CIS modules */
    .ilo-left-title { font-weight: 700; padding: 0.75rem; font-family: Georgia, serif; vertical-align: top; box-sizing: border-box; line-height: 1.2; }
    .ilo-table-wrap { width: 100%; min-width: 0; }
    /* ensure consistent fixed layout so colgroup widths are respected and labels wrap */
    table.cis-table { table-layout: fixed; }
    table.cis-table th.cis-label { white-space: normal; overflow-wrap: break-word; word-break: break-word; }
    /* prevent cell contents from overflowing the cell box */
    table.cis-table td, table.cis-table th { overflow: hidden; }
  /* Make inner table fill the right cell container and sit flush */
  #ilo-right-wrap { padding: 0; margin: 0; }
  #ilo-right-wrap > table { width: 100%; height: 100%; margin: 0; border-spacing: 0; border-collapse: collapse; }
  /* inner table cell padding so content is flush with container */
  #ilo-right-wrap td, #ilo-right-wrap th { vertical-align: middle; padding: 0.5rem 0.5rem; }
  /* show cell borders for the inner ILO table (internal grid only) */
  #ilo-right-wrap > table th, #ilo-right-wrap > table td { border: 1px solid #dee2e6; }
  /* hide outer edges so only internal dividers remain */
  #ilo-right-wrap > table thead th { border-top: 0; }
  #ilo-right-wrap > table th:first-child, #ilo-right-wrap > table td:first-child { border-left: 0; }
  #ilo-right-wrap > table th:last-child, #ilo-right-wrap > table td:last-child { border-right: 0; }
  /* remove the bottom-most outer border line of the inner table (no visible border under last row) */
  #ilo-right-wrap > table tbody tr:last-child td { border-bottom: 0 !important; }
  /* Ensure badge/code cell and grip align and don't push width */
  .ilo-badge { display: inline-block; min-width: 48px; text-align: center; }
  .drag-handle { width: 28px; display: inline-flex; justify-content: center; }
  /* Make textarea fill remaining space and autosize */
  .cis-textarea { width: 100%; box-sizing: border-box; resize: none; }
  /* Allow ILO textareas to collapse to a single-line look (match Course Title feel) */
  #ilo-right-wrap textarea.cis-textarea.autosize { min-height: 34px; overflow: hidden; }
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
        <th id="ilo-left-title" class="align-top text-start cis-label">Intended Learning Outcomes (ILO)
          <span id="unsaved-ilos" class="unsaved-pill d-none">Unsaved</span>
          <!-- Save handled by main syllabus Save button in the page toolbar -->
        </th>
        <td id="ilo-right-wrap">
          <table class="table mb-0" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.4; border: none;">
            <colgroup>
              <col style="width: 10%"> <!-- ILO code column -->
              <col style="width: 90%"> <!-- Description column -->
            </colgroup>
            <thead>
              <tr class="table-light">
                <th class="text-center cis-label">ILO</th>
                <th class="text-start cis-label">Upon completion of this course, the students should be able to:</th>
              </tr>
            </thead>
            <tbody id="syllabus-ilo-sortable" data-syllabus-id="{{ $default['id'] }}">
              @if($ilosSorted->count())
                @foreach ($ilosSorted as $index => $ilo)
                  @php $seqCode = 'ILO' . ($index + 1); @endphp
                  <tr data-id="{{ $ilo->id }}">
                    <td class="text-center align-middle">
                      <div class="ilo-badge fw-semibold">{{ $seqCode }}</div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;">
                          <i class="bi bi-grip-vertical"></i>
                        </span>
                        <textarea
                          name="ilos[]"
                          class="form-control cis-textarea autosize flex-grow-1"
                          data-original="{{ old("ilos.$index", $ilo->description) }}"
                          required>{{ old("ilos.$index", $ilo->description) }}</textarea>
                        <input type="hidden" name="code[]" value="{{ $seqCode }}" data-original-code="{{ $ilo->code }}">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-ilo ms-2" title="Delete ILO">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                @endforeach
              @else
                <tr>
                  <td class="text-center align-middle">
                    <div class="ilo-badge fw-semibold">ILO1</div>
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;">
                        <i class="bi bi-grip-vertical"></i>
                      </span>
                      <textarea
                        name="ilos[]"
                        class="form-control cis-textarea autosize flex-grow-1"
                        required></textarea>
                      <input type="hidden" name="code[]" value="ILO1" data-original-code="">
                      <button type="button" class="btn btn-sm btn-outline-danger btn-delete-ilo ms-2" title="Delete ILO">
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

  {{-- ░░░ START: ILO Action Buttons ░░░ --}}
  <div class="d-flex gap-2">
    {{-- Add Row, Save Order, and local Save All buttons removed; saving is handled by the main toolbar Save button. --}}
  </div>
  {{-- ░░░ END: ILO Action Buttons ░░░ --}}
</form>

<script>
  // Inline autosize helper for ILO textareas — runs without requiring bundled JS rebuild
  (function(){
    function autosizeEl(el){ try { el.style.height = 'auto'; el.style.height = (el.scrollHeight || 0) + 'px'; } catch(e) { /* noop */ } }
    function bindAutosize(ta){ if (!ta) return; autosizeEl(ta); ta.addEventListener('input', () => autosizeEl(ta)); }

    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('textarea.autosize').forEach(bindAutosize);

      // Observe newly added rows (e.g., Add Row button) and initialize autosize on any new textarea.autosize
      const list = document.getElementById('syllabus-ilo-sortable');
      if (list && window.MutationObserver) {
        const mo = new MutationObserver((mutations) => {
          for (const m of mutations) {
            for (const node of m.addedNodes) {
              if (node && node.querySelectorAll) {
                node.querySelectorAll('textarea.autosize').forEach(bindAutosize);
              }
            }
          }
        });
        mo.observe(list, { childList: true, subtree: true });
      }
      // --- ILO keyboard behaviors: Ctrl+Enter to add ILO (row + AT column), Backspace on empty to remove ---
      function getATTable() {
        return document.querySelector('.at-map-right > table.cis-table') || document.querySelector('table.cis-table');
      }

      function addIloColumnInAT(atTable, insertAtIndex) {
        if (!atTable) return;
        try {
          const theadRow = atTable.querySelector('thead tr:nth-child(2)');
          const ths = Array.from(theadRow.querySelectorAll('th'));
          // fixed trailing domain columns are the last 3 (C,P,A)
          const insertIndex = (typeof insertAtIndex === 'number') ? (4 + insertAtIndex) : (ths.length - 3);

          // create header th
          const newTh = document.createElement('th');
          // compute new ILO number
          const iloCount = Math.max(0, ths.length - 7); // 4 + 3 fixed -> total -7 == iloCount
          newTh.textContent = (iloCount + 1);
          const refTh = ths[insertIndex];
          theadRow.insertBefore(newTh, refTh);

          // adjust first thead row colspan for the Intended Learning Outcomes label
          const firstRow = atTable.querySelector('thead tr:first-child');
          const iloLabelTh = Array.from(firstRow.querySelectorAll('th')).find(th => /Intended Learning Outcomes/i.test(th.textContent || ''));
          if (iloLabelTh) {
            const current = parseInt(iloLabelTh.getAttribute('colspan') || '0', 10) || 0;
            iloLabelTh.setAttribute('colspan', current + 1);
          }

          // update colgroup: insert a new col before last 3 cols
          const colgroup = atTable.querySelector('colgroup');
          if (colgroup) {
            const cols = colgroup.children;
            const refCol = cols[cols.length - 3];
            const newCol = document.createElement('col');
            newCol.style.width = cols[0] ? cols[0].style.width : '';
            colgroup.insertBefore(newCol, refCol);
          }

          // for each tbody row, insert td (or th for section headers) before last 3 cells
          atTable.querySelectorAll('tbody > tr').forEach((r) => {
            const cells = Array.from(r.children);
            const ref = cells[cells.length - 3];
            if (!ref) { r.appendChild(document.createElement(r.querySelector('th') ? 'th' : 'td')); return; }
            const isHeader = r.classList && r.classList.contains('section-header');
            const newCell = document.createElement(isHeader ? 'th' : 'td');
            if (!isHeader) {
              const input = document.createElement('input'); input.type = 'text'; input.className = 'cis-input text-center';
              newCell.className = 'text-center';
              newCell.appendChild(input);
            }
            r.insertBefore(newCell, ref);
          });
        } catch (e) { console.error('addIloColumnInAT error', e); }
      }

      function removeIloColumnInAT(atTable, iloIndex) {
        if (!atTable) return;
        try {
          const theadRow = atTable.querySelector('thead tr:nth-child(2)');
          const ths = Array.from(theadRow.querySelectorAll('th'));
          const targetIndex = 4 + iloIndex; // after fixed 4 columns
          if (targetIndex < 0 || targetIndex >= ths.length) return;
          // remove header th
          theadRow.removeChild(ths[targetIndex]);

          // adjust first thead row colspan
          const firstRow = atTable.querySelector('thead tr:first-child');
          const iloLabelTh = Array.from(firstRow.querySelectorAll('th')).find(th => /Intended Learning Outcomes/i.test(th.textContent || ''));
          if (iloLabelTh) {
            const current = parseInt(iloLabelTh.getAttribute('colspan') || '0', 10) || 0;
            iloLabelTh.setAttribute('colspan', Math.max(1, current - 1));
          }

          // remove col from colgroup
          const colgroup = atTable.querySelector('colgroup');
          if (colgroup) {
            const cols = Array.from(colgroup.children);
            const targetCol = cols[targetIndex];
            if (targetCol) colgroup.removeChild(targetCol);
          }

          // remove each tbody cell at that index
          atTable.querySelectorAll('tbody > tr').forEach((r) => {
            const cells = Array.from(r.children);
            const cell = cells[targetIndex];
            if (cell) r.removeChild(cell);
          });
        } catch (e) { console.error('removeIloColumnInAT error', e); }
      }

      // delegated keyboard handlers for ILO textareas
      const ilolist = document.getElementById('syllabus-ilo-sortable');
      if (ilolist) {
        ilolist.addEventListener('keydown', function(ev){
          const target = ev.target;
          if (!target || target.tagName !== 'TEXTAREA') return;
          // Ctrl/Cmd+Enter -> clone current ILO row and add AT column at same position (append)
          if (ev.key === 'Enter' && (ev.ctrlKey || ev.metaKey)) {
            ev.preventDefault();
            const tr = target.closest('tr');
            if (!tr) return;
            // Clone the row, but ensure the clone does not keep duplicate data-id values
            const newRow = tr.cloneNode(true);
            newRow.removeAttribute('data-id');
            // clear textarea and hidden code input
            newRow.querySelectorAll('textarea').forEach(t => t.value = '');
            newRow.querySelectorAll('input[type="hidden"]').forEach(i => i.value = '');
            tr.parentNode.insertBefore(newRow, tr.nextSibling);

            // After insertion, compute its sequential index and set badge + hidden code immediately
            try {
              const list = document.getElementById('syllabus-ilo-sortable');
              const rows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="ilos[]"]') || r.querySelector('.ilo-badge'));
              const idx = rows.indexOf(newRow);
              const code = `ILO${(idx >= 0 ? idx + 1 : rows.length)}`;
              const badge = newRow.querySelector('.ilo-badge'); if (badge) badge.textContent = code;
              const codeInput = newRow.querySelector('input[type="hidden"][name="code[]"]'); if (codeInput) codeInput.value = code;
            } catch (e) { /* noop */ }

            // initialize autosize on new textarea
            newRow.querySelectorAll('textarea.autosize').forEach(bindAutosize);
            // (Standalone mode) do not dispatch cross-module events for AT syncing
            // focus new textarea
            const nta = newRow.querySelector('textarea'); if (nta) { setTimeout(() => nta.focus(), 10); }
            return;
          }

          // Backspace on empty textarea at caret 0 -> remove this ILO row and remove corresponding AT column
          if (ev.key === 'Backspace') {
            const raw = target.value || '';
            const selStart = (typeof target.selectionStart === 'number') ? target.selectionStart : 0;
            const selEnd = (typeof target.selectionEnd === 'number') ? target.selectionEnd : selStart;
            const trimmed = raw.trim();
            // Only intercept when there is nothing meaningful to delete (trimmed empty)
            // AND caret is at the start (selection at 0). Otherwise allow normal Backspace behavior.
            if (trimmed === '' && selStart === 0 && selEnd === 0) {
              ev.preventDefault();
              const tr = target.closest('tr');
              if (!tr) return;
              const list = Array.from(ilolist.querySelectorAll('tr'));
              if (list.length <= 1) return; // keep at least one ILO
              const index = list.indexOf(tr);
              tr.parentNode.removeChild(tr);
              // focus previous textarea if present
              const prev = list[index - 1] || list[0];
              const pta = prev ? prev.querySelector('textarea') : null;
              if (pta) setTimeout(() => pta.focus(), 10);
            }
          }
        });
      }
    });
  })();
</script>

@push('scripts')
  @vite('resources/js/faculty/syllabus-ilo-sortable.js')
@endpush
<!-- Local ILO Save button removed — saving is handled by the main syllabus Save button -->
  