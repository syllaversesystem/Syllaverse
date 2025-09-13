{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/assessment-tasks-distribution.blade.php
* Description: Assessment Tasks Distribution — placeholder table (Blade only)
-------------------------------------------------------------------------------
--}}

@php $iloCols = range(1,8); @endphp
<style>
  /* Outer wrapper to mimic CIS two-column map: left label + right detail grid */
  /* outer wrapper: no outer border, only internal separators */
  .at-map-outer { width: 100%; margin-bottom: 0; border: none; border-radius: 0; background: #fff; }
  /* left label: no full box, only right divider to match CIS modules (match ILO) */
  .at-map-left { background: #fff; border: 0; border-right: none; vertical-align: middle; text-align: center; padding: 0.75rem; }
  /* add a subtle bottom borderline under the left module title to match other CIS module headers */
  #at-left-title { padding-bottom: 0.5rem; border-bottom: none; }
  /* vertical module label on the left column */
  .at-map-left .label-vertical {
    display: block;
    width: 1.75rem;
    margin: 0 auto;
    font-weight: 700;
    font-family: Georgia, serif;
    font-size: 0.95rem;
    line-height: 1;
    writing-mode: vertical-rl;
    text-orientation: upright;
    transform: rotate(180deg);
    transform-origin: center;
    white-space: nowrap;
  }
  /* Right detail area: inner table will provide the grid; match ILO inner-table style */
  .at-map-right { padding: 0 !important; border: none; background: #fff; overflow-x: auto; margin: 0 !important; }
  /* ensure the wrapper cell has no extra padding from table helpers */
  .at-map-outer td.at-map-right { padding: 0 !important; }
  .at-map-outer .cis-table { border-collapse: collapse; width: 100%; }
  /* inner table: show only single vertical separators between columns (no double borders) */
  .at-map-right > table { width: 100%; max-width: 100%; height: 100%; margin: 0; border-spacing: 0; border-collapse: collapse; min-width: 0; table-layout: fixed; font-family: Georgia, serif; font-size: 13px; line-height: 1.4; border: none; border-right: 1px solid #343a40; }
  .at-map-right > table th, .at-map-right > table td { border: none; padding: 0.12rem 0.18rem; vertical-align: middle; }
  /* single vertical separators: apply left border to every cell except the first so separators are clear */
  /* remove per-cell borders and add single separators between columns */
  .at-map-right > table th, .at-map-right > table td { border: none; }
  .at-map-right > table th + th, .at-map-right > table td + td { border-left: 1px solid #343a40 !important; }
  .at-map-right > table th:first-child, .at-map-right > table td:first-child { border-left: none !important; }
  /* section header rows (LEC / LAB) — show top and bottom separators */
  .at-map-right .section-header th, .at-map-right .section-header td {
    border-top: 1px solid #343a40 !important;
    border-bottom: 1px solid #343a40 !important;
  }
  /* also ensure section header rows (LEC/LAB) show separators */
  .at-map-right .section-header th + th, .at-map-right .section-header td + td { border-left: 1px solid #343a40 !important; }
  /* inner table right side edge removed to avoid duplicating the .at-map-right border */
  /* .at-map-right > table { border-right: 1px solid #343a40; } */
  /* keep a subtle header underline */
  .at-map-right .cis-table thead tr:first-child th { border-bottom: 1px solid #343a40; }
  /* ensure header cells match the compact input cell sizing */
  .at-map-right > table thead th {
    padding: 0.08rem 0.12rem;
    height: 24px;
    line-height: 24px;
    vertical-align: middle;
    box-sizing: border-box;
  }
  /* ensure outer-most inner table cell borders are present */
  /* ensure first/last column keep their outer vertical lines */
  /* last-child doesn't need an extra right border when using single separators */
  .at-map-right > table th:last-child, .at-map-right > table td:last-child { border-right: none; }
  /* bottom of AT table — handled by .at-note top border to avoid duplicate lines */
  /* removed duplicate bottom border on table rows so only one clean divider shows */
  .at-map-right .at-header { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0.6rem; }
  .at-map-right .at-title { font-family: Georgia, serif; font-weight: 700; font-size: 0.78rem; line-height: 24px; text-align: center; width:100%; display:block; }
  .at-map-right .unsaved-pill { margin-left: 0.5rem; }
  /* Inner table visual tweaks to match image */
  .at-map-right .cis-table thead th { background: #f8f9fa; font-weight: 700; vertical-align: middle; }
  /* make sure small header text centers horizontally for narrow columns */
  .at-map-right .cis-table thead th { text-align: center; }
  .at-map-right .cis-table thead th.text-start { text-align: left; }
  /* match header label font-size to compact input size so labels align visually */
  .at-map-right .cis-table thead th,
  .at-map-right .cis-table tbody .section-header th {
    font-size: 0.78rem;
  }
  .at-map-right .cis-table tbody .section-header th { background: #fff; font-weight: 700; text-transform: uppercase; font-size: 0.92rem; }
  /* smaller paddings for compact cells */
  .at-map-right .cis-table tbody .cis-input { padding: 0.12rem 0.18rem; }
  .at-map-right .cis-table tbody td, .at-map-right .cis-table tbody th { vertical-align: middle; }
  .at-map-right .cis-table .percent-total { text-align: center; font-weight: 700; }
  /* make inputs fully responsive to their narrow columns */
  .at-map-right input { width: 100%; min-width: 0; max-width: 100%; box-sizing: border-box; }
  .at-map-right th, .at-map-right td { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  /* align the left title container to match ILO title container */
  #at-left-title.cis-label {
    font-weight: 700;
    padding: 0.75rem;
    font-family: Georgia, serif;
    vertical-align: top;
    box-sizing: border-box;
    line-height: 1.2;
  border-left: 1px solid #dee2e6;
  border-right: 1px solid #dee2e6;
  }
  @media print { .at-map-left .label-vertical { transform: rotate(180deg); } }
  /* title row framing removed since outer border is disabled */
  .at-map-outer > tbody > tr:first-child th, .at-map-outer > tbody > tr:first-child td {
    border-top: none;
    border-left: none;
    border-right: none;
  }
  /* Container wrapping the table and the note: apply single border to this container */
  /* container should not provide the module border; keep spacing default */
  .at-map-container { border: none; border-radius: 0; overflow: visible; display: inline-block; width: fit-content; max-width: 100%; vertical-align: top; }
</style>

<!-- Outer two-column map: left label column, right detail column -->
<div class="at-map-container">
<table class="table table-bordered mb-4 at-map-outer cis-table" style="table-layout:fixed; border-collapse:collapse; border-spacing:0;">
  <colgroup>
    <col style="width:16%">
    <col style="width:84%">
  </colgroup>
  <tbody>
    <tr>
      <th id="at-left-title" class="at-map-left align-top text-start cis-label">Assessment Method and Distribution Map
        <div style="margin-top:6px;">
          <span id="unsaved-assessment_tasks_left" class="unsaved-pill d-none">Unsaved</span>
        </div>
      </th>
      <td class="at-map-right">
  <table class="table table-bordered mb-0 cis-table" style="table-layout: fixed; margin:0;">
          @php
            // code: fixed 40px, task: 40%, small fixed widths for control columns; ILO columns left flexible to fill remaining space
          @endphp
          <colgroup>
            <col style="width:40px;"> <!-- Code fixed -->
            <col style="width:40%;"> <!-- Task large -->
            <col style="width:48px;"> <!-- I/R/D -->
            <col style="width:48px;"> <!-- Percent -->
            @foreach ($iloCols as $c)
              <col> <!-- ILO flexible -->
            @endforeach
            <col style="width:48px;"> <!-- C -->
            <col style="width:48px;"> <!-- P -->
            <col style="width:48px;"> <!-- A -->
          </colgroup>
  <thead class="table-light">
    <tr>
  <th colspan="4" class="text-start cis-label">
  <div class="at-title fw-bold">Assessment Tasks (AT) Distribution
  </div>
      </th>
      <th class="text-center cis-label" colspan="{{ count($iloCols) }}">Intended Learning Outcomes</th>
    <th class="text-center cis-label" colspan="3">Domains</th>
    </tr>
    <tr class="text-center align-middle">
      <th>Code</th>
      <th class="text-start">Assessment Tasks</th>
  <th>I/R/D</th>
  <th>%</th>
      @foreach ($iloCols as $c)
        <th>{{ $c }}</th>
      @endforeach
      <th>C</th>
      <th>P</th>
      <th>A</th>
    </tr>
  </thead>
    <style>
  /* make inputs very compact (lower height and smaller font) */
  .cis-input { font-weight: 400; font-size: 0.78rem; line-height: 1.02; font-family: inherit; height: 24px; padding: 0.06rem 0.10rem; box-sizing: border-box; }
  /* ensure centered text inputs remain visually compact */
  .cis-input.text-center { padding-left: 0.06rem; padding-right: 0.06rem; }
    </style>
    <tbody>
    <tr class="section-header table-light">
    <th class="text-start">
      <input type="text" name="section_code[]" class="cis-input text-center" value="" placeholder="LEC" />
    </th>
    <th>
      <input type="text" name="section_name[]" class="cis-input" value="" placeholder="LECTURE" />
    </th>
  @for ($i = 1; $i < (3 + count($iloCols) + 3); $i++)
      <th></th>
    @endfor
  </tr>
    @for ($i = 1; $i <= 3; $i++)
      <tr>
        <td><input type="text" class="cis-input text-center" placeholder="ME"></td>
        <td><input type="text" class="cis-input" placeholder="Midterm Exam / Final Exam / Quizzes..."></td>
  <td><input type="text" class="cis-input text-center" placeholder="I/R/D"></td>
  <td><input type="text" class="cis-input text-center" placeholder="0"></td>
        @foreach ($iloCols as $c)
          <td class="text-center"><input type="text" class="cis-input text-center" placeholder="-"></td>
        @endforeach
        <td class="text-center"><input type="text" class="cis-input text-center" placeholder="-"></td>
        <td class="text-center"><input type="text" class="cis-input text-center" placeholder="-"></td>
        <td class="text-center"><input type="text" class="cis-input text-center" placeholder="-"></td>
      </tr>
    @endfor

    {{-- Sync AT Task column inputs (cis-input in second column) to mapping_name[] inputs in assessment-mapping --}}
    <script>
    document.addEventListener('DOMContentLoaded', function(){
      const atRoot = document.querySelector('.at-map-container');
      if (!atRoot) return;
      // delegate input events on AT Task column inputs (second column inside each tr, excluding section headers)
      atRoot.addEventListener('input', function(e){
        const target = e.target;
        if (!target || !target.classList.contains('cis-input')) return;
        const td = target.closest('td');
        if (!td) return;
        // Find the column index of this td within its row
        const tr = td.closest('tr');
        if (!tr) return;
        // Exclude section header rows that contain section_name inputs
        if (tr.classList.contains('section-header')) return;
        // Find the task column input (we consider the second cell <td> in the row)
        const cells = Array.from(tr.children).filter(n => n.tagName && n.tagName.toLowerCase() === 'td');
        if (cells.length < 2) return;
        const taskCell = cells[1];
        if (!taskCell.contains(target)) return; // only respond to inputs inside the task column

        // Determine the index among data rows (ignore section headers)
        const tbody = tr.parentNode;
        const dataRows = Array.from(tbody.querySelectorAll('tr')).filter(r => !r.classList.contains('section-header'));
        const rowIndex = dataRows.indexOf(tr);
        if (rowIndex === -1) return;

        // Ensure mapping row exists for this AT row; create if needed
        const mapRoot = document.querySelector('.assessment-mapping');
        if (!mapRoot) return;

        function dataMapRows(){ return Array.from(mapRoot.querySelectorAll('tbody tr')).filter(r => r.querySelector('.week-cell')); }

        function ensureMappingRowsAtLeast(n){
          let mappingRows = dataMapRows();
          while (mappingRows.length <= n){
            // find template row from mapping module (last data row or first tbody tr)
            const tplRows = Array.from(mapRoot.querySelectorAll('tbody tr'));
            const templateRow = tplRows.length ? tplRows[tplRows.length - 1] : null;
            if (!templateRow) break;
            const newRow = templateRow.cloneNode(true);
            const cloneMerge = newRow.querySelector('.merge-cell'); if (cloneMerge) cloneMerge.parentNode.removeChild(cloneMerge);
            // Clear mapping_name[] in cloned first cell and clear week-cell marks
            try {
              const nameInput = newRow.querySelector('input[name="mapping_name[]"]');
              if (nameInput){ nameInput.value = ''; nameInput.placeholder = (mapRoot.querySelector('input[name="mapping_name[]"]') || {}).placeholder || 'LE'; }
            } catch (e) { /* noop */ }
            newRow.querySelectorAll('.week-cell').forEach(function(c){ c.classList.remove('marked'); c.textContent = ''; });
            templateRow.parentNode.insertBefore(newRow, templateRow.nextSibling);
            // update merge-cell rowspan (create/move if necessary)
            const dataRows = dataMapRows();
            let merge = mapRoot.querySelector('.merge-cell');
            if (!merge){
              if (dataRows.length === 0) return;
              const firstData = dataRows[0];
              const insertRow = firstData.previousElementSibling || firstData;
              const td = document.createElement('td');
              td.className = 'merge-cell';
              td.rowSpan = dataRows.length + 1;
              td.setAttribute('style', 'border:1px solid #343a40; height:30px; width:10%;');
              insertRow.insertBefore(td, insertRow.firstChild);
            } else {
              merge.rowSpan = dataRows.length + 1;
            }
            mappingRows = dataMapRows();
          }
        }

        ensureMappingRowsAtLeast(rowIndex);

        const mapRows = dataMapRows();
        const mapRow = mapRows[rowIndex];
        if (!mapRow) return;
        const mapInput = mapRow.querySelector('input[name="mapping_name[]"]');
        if (!mapInput) return;

        const val = (target.value || '').trim();
        if (!val){
          // Remove mapping row when AT task input cleared. If only one data row remains, clear it instead.
          const mappingRows = dataMapRows();
          if (mappingRows.length <= 1){
            // clear the single mapping row
            const single = mappingRows[0];
            if (single){
              single.querySelectorAll('.week-cell').forEach(function(c){ c.classList.remove('marked'); c.textContent = ''; });
              const inp = single.querySelector('input[name="mapping_name[]"]'); if (inp) inp.value = '';
            }
          } else {
            // remove the row and maintain merge-cell
            const toRemove = mappingRows[rowIndex];
            if (toRemove){
              const mergeCell = toRemove.querySelector('.merge-cell');
              if (mergeCell){
                // move merge cell to next data row
                let next = toRemove.nextElementSibling;
                while (next && !next.querySelector('.week-cell')) next = next.nextElementSibling;
                if (next) next.insertBefore(mergeCell, next.firstChild);
              }
              toRemove.parentNode.removeChild(toRemove);
              // update rowspan
              const merge = mapRoot.querySelector('.merge-cell');
              if (merge){
                merge.rowSpan = dataMapRows().length + 1;
              }
            }
          }
          return;
        }

        // Normal sync when value present
        mapInput.value = target.value;
      });
    });
    </script>

  <tr class="section-header table-light">
    <th class="text-start">
      <input type="text" name="section_code[]" class="cis-input text-center" value="" placeholder="LAB" />
    </th>
    <th>
      <input type="text" name="section_name[]" class="cis-input" value="" placeholder="LABORATORY" />
    </th>
  @for ($i = 1; $i < (3 + count($iloCols) + 3); $i++)
      <th></th>
    @endfor
  </tr>
    @for ($i = 1; $i <= 2; $i++)
      <tr>
        <td><input type="text" class="cis-input text-center" placeholder="LE"></td>
        <td><input type="text" class="cis-input" placeholder="Laboratory Exercises / Exams..."></td>
  <td><input type="text" class="cis-input text-center" placeholder="I/R/D"></td>
  <td><input type="text" class="cis-input text-center" placeholder="0"></td>
        @foreach ($iloCols as $c)
          <td class="text-center"><input type="text" class="cis-input text-center" placeholder="-"></td>
        @endforeach
        <td class="text-center"><input type="text" class="cis-input text-center" placeholder="-"></td>
        <td class="text-center"><input type="text" class="cis-input text-center" placeholder="-"></td>
        <td class="text-center"><input type="text" class="cis-input text-center" placeholder="-"></td>
      </tr>
    @endfor

    <tr class="table-light footer-total">
      <th colspan="{{ 3 }}" class="text-end">Total</th>
      <th id="at-percent-total" class="percent-total text-center">100%</th>
  <th colspan="{{ count($iloCols) + 3 }}"></th>
    </tr>
  </tbody>
</table>

        {{-- Hidden serialized payload so the main Save can persist the AT distribution as JSON --}}
     {{-- Hidden serialized payload so the main Save can persist the AT distribution as JSON
       The `form="syllabusForm"` attribute associates this textarea with the main
       syllabus form even though the AT UI is rendered outside the <form> element.
     --}}
     <textarea id="assessment_tasks_data" name="assessment_tasks_data" form="syllabusForm" class="d-none" data-original="{{ old('assessment_tasks_data', $syllabus->assessment_tasks_data ?? '') }}">{{ old('assessment_tasks_data', $syllabus->assessment_tasks_data ?? '') }}</textarea>

        {{-- Note: match CIS wording from design image --}}
        <style>
          /* Note box: show a single top border to act as the divider between table and note; add right-side border to continue module vertical rule */
          .at-note { display:block; width:100%; box-sizing: border-box; padding: 0.5rem; background: #fff; border-top: 1px solid #343a40; border-right: 1px solid #343a40; border-left: none; border-bottom: none; border-radius: 0; margin-top: 0 !important; }
        </style>
        <div class="at-note small text-muted">
          <strong>Note:</strong> All internal assessments with feedback will be made available within 2 week after each assessment submission except Final Examination.
        </div>

 

<script>
  (function(){
    function toNumber(v) { const n = parseFloat(String(v).replace('%','').trim()); return Number.isFinite(n) ? n : 0; }

    function getATTable() {
      return document.querySelector('.at-map-right > table.cis-table') || document.querySelector('table.cis-table');
    }

    function serializeAT() {
      const table = getATTable();
      if (!table) return;
      const allRows = Array.from(table.querySelectorAll('tbody > tr'));
      const out = [];
      let percentTotal = 0;

      let sectionIndex = 0;
      let subCounter = 0;

      // iterate and produce structured output: numeric section and per-section positions for sub-rows
      for (let ri = 0; ri < allRows.length; ri++) {
        const r = allRows[ri];
        // Skip footer rows so they don't become serialized as data rows
        if (r.classList && r.classList.contains('footer-total')) continue;
        if (r.classList.contains('section-header')) {
          // start a new section
          sectionIndex++;
          subCounter = 0; // reset sub numbering for this section

          // read header inputs for main field (section code/name)
          const codeInp = r.querySelector('input[name="section_code[]"]');
          const nameInp = r.querySelector('input[name="section_name[]"]');
          const code = codeInp ? (codeInp.value || '').trim() : '';
          const name = nameInp ? (nameInp.value || '').trim() : '';

          // only add a main-field item when the user actually entered something
          if ((code !== '') || (name !== '')) {
            const mainItem = { section: sectionIndex, position: null, code: code, task: name, ird: '', percent: '', iloFlags: [], c: '', p: '', a: '' };
            out.push(mainItem);
          }
          continue;
        }

        // data row
        const cells = Array.from(r.children || []);
        if (!cells.length) continue;
        const ths = table.querySelectorAll('thead tr:nth-child(2) th');
        const iloFlagCount = Math.max(0, ths.length - (4 + 3));

        const cellValue = (cell) => {
          if (!cell) return '';
          const inp = cell.querySelector('input, textarea, select');
          return inp ? (inp.value || '') : (cell.textContent || '').trim();
        };

        const code = cellValue(cells[0]) || '';
        const task = cellValue(cells[1]) || '';
        const ird = cellValue(cells[2]) || '';
        const pct = cellValue(cells[3]) || '';

        const iloFlags = [];
        for (let i = 0; i < iloFlagCount; i++) {
          const idx = 4 + i;
          iloFlags.push(cellValue(cells[idx]) || '');
        }

        const trailingStart = 4 + iloFlagCount;
        const trailing = [cellValue(cells[trailingStart]), cellValue(cells[trailingStart + 1]), cellValue(cells[trailingStart + 2])];

        // treat as subfield (positioned row)
        // only include rows that have at least one non-empty meaningful field
        const hasContent = (code && String(code).trim() !== '') || (task && String(task).trim() !== '') || (ird && String(ird).trim() !== '') || (pct && String(pct).trim() !== '') || (iloFlags && Array.isArray(iloFlags) && iloFlags.some(x => String(x).trim() !== '')) || (trailing && ((trailing[0] && String(trailing[0]).trim() !== '') || (trailing[1] && String(trailing[1]).trim() !== '') || (trailing[2] && String(trailing[2]).trim() !== '')));
        if (hasContent) {
          subCounter++;
          const position = sectionIndex + '-' + subCounter;
          const item = { section: sectionIndex, position: position, code, task, ird, percent: pct, iloFlags, c: (trailing[0] || '').toString(), p: (trailing[1] || '').toString(), a: (trailing[2] || '').toString() };
          out.push(item);
          percentTotal += toNumber(pct);
        } else {
          // keep the empty UI row but do not serialize it
        }
      }

      // write to hidden textarea and dispatch input so global bindUnsavedIndicator picks it up
      const ta = document.querySelector('[name="assessment_tasks_data"]');
      if (ta) {
        try { ta.value = JSON.stringify(out); } catch (e) { ta.value = '[]'; }
        try { ta.dispatchEvent(new Event('input', { bubbles: true })); } catch (e) { /* noop */ }
      }

      // update total percent cell in the footer row if present
      try {
        const footerPctCell = document.getElementById('at-percent-total');
        if (footerPctCell) footerPctCell.textContent = (Math.round(percentTotal*100)/100) + '%';
      } catch (e) { /* noop */ }
    }

  document.addEventListener('DOMContentLoaded', function(){
  // initialize: bind input listeners for all inputs inside the AT table
  const table = getATTable();
  if (!table) return;

      // helper: set caret at end of an input
      function setCaretToEnd(input) {
        try {
          const len = (input.value || '').length;
          input.focus();
          if (typeof input.setSelectionRange === 'function') input.setSelectionRange(len, len);
        } catch (e) { try { input.focus(); } catch (e) {} }
      }

      // helper: focus an input in a row by column index (falls back to first input)
      function focusInputInRow(row, colIndex) {
        if (!row) return false;
        try {
          const cells = row.cells || row.children;
          if (cells && cells[colIndex]) {
            const inp = cells[colIndex].querySelector('input');
            if (inp) { setCaretToEnd(inp); inp.scrollIntoView({ block: 'nearest', inline: 'nearest' }); return true; }
          }
          // fallback: first input
          const first = row.querySelector('input');
          if (first) { setCaretToEnd(first); first.scrollIntoView({ block: 'nearest', inline: 'nearest' }); return true; }
        } catch (e) { /* noop */ }
        return false;
      }

      // helper: attach handlers to a single input element (for both existing and newly created rows)
      function attachATHandlersToInput(inp) {
        if (!inp) return;
        // input -> reserialize and mark unsaved
        inp.addEventListener('input', function(){
          serializeAT();
          try {
            // prefer the global helper when available
            if (window.markAsUnsaved) window.markAsUnsaved('assessment_tasks');
            // fallback: directly reveal the pills so they always appear when typing
            const p1 = document.getElementById('unsaved-assessment_tasks_left');
            if (p1) p1.classList.remove('d-none');
            // If markDirty is available, call it to keep global counters in sync
            if (window.markDirty && typeof window.markDirty === 'function') window.markDirty('unsaved-assessment_tasks_left');

            // Propagation: if this input is in the Task column (colIndex 1), copy its value
            // into the corresponding assessment mapping name input (by data-row index) when present.
            try {
              const cell = inp.closest('td,th');
              const colIndex = cell ? (cell.cellIndex || 0) : 0;
              if (colIndex === 1) {
                const tr = inp.closest('tr');
                if (tr) {
                  // determine AT data rows (exclude section headers and footer)
                  const atRows = Array.from(table.querySelectorAll('tbody > tr')).filter(r => !r.classList.contains('section-header') && !r.classList.contains('footer-total'));
                  const idx = atRows.indexOf(tr);
                  if (idx >= 0) {
                    const mappingRoot = document.querySelector('.assessment-mapping');
                    if (mappingRoot) {
                      const mappingRows = Array.from(mappingRoot.querySelectorAll('tbody tr')).filter(r => r.querySelector('.week-cell'));
                      if (idx < mappingRows.length) {
                        const mapInp = mappingRows[idx].querySelector('input[name="mapping_name[]"]');
                        if (mapInp) {
                          mapInp.value = inp.value || '';
                          // dispatch input event so mapping module can react (unsaved indicators, serialization)
                          try { mapInp.dispatchEvent(new Event('input', { bubbles: true })); } catch (e) {}
                          // mark mapping module unsaved if helper exists
                          try { if (window.markAsUnsaved) window.markAsUnsaved('assessment_mappings'); } catch (e) {}
                        }
                      }
                    }
                  }
                }
              }
            } catch (e) { /* noop propagation errors should not block user typing */ }

          } catch (e) { /* noop */ }
        });

        // key handlers: Ctrl+Enter on section header inputs to add a subfield; Backspace on empty subfield to remove
  inp.addEventListener('keydown', function(ev){
          try {
            const tr = inp.closest('tr');
            if (!tr) return;
            const isSectionHeader = tr.classList.contains('section-header');
      // determine the column index of this input's cell (0-based)
      const cell = inp.closest('td,th');
      const colIndex = cell ? (cell.cellIndex || 0) : 0;

            // Ctrl+Enter on header (any column) -> insert a new data row (subfield) immediately after header
            if (isSectionHeader && ev.key === 'Enter' && (ev.ctrlKey || ev.metaKey)) {
              ev.preventDefault();
              // attempt to clone an example data row (first non-header row) to keep column structure
              const sample = table.querySelector('tbody > tr:not(.section-header)');
              let newRow;
              if (sample) {
                newRow = sample.cloneNode(true);
                // clear all input values in cloned row
                newRow.querySelectorAll('input').forEach(i => i.value = '');
              } else {
                // fallback: create a minimal row matching column count
                const cols = table.querySelectorAll('thead tr:nth-child(2) th').length;
                newRow = document.createElement('tr');
                for (let c=0;c<cols;c++) {
                  const td = document.createElement('td');
                  const input = document.createElement('input');
                  input.type = 'text';
                  input.className = 'cis-input text-center';
                  td.appendChild(input);
                  newRow.appendChild(td);
                }
              }

              // insert after the header row
              tr.parentNode.insertBefore(newRow, tr.nextSibling);
              // attach handlers to new inputs
              newRow.querySelectorAll('input').forEach(attachATHandlersToInput);
              // focus input in same column where user pressed Ctrl+Enter
              focusInputInRow(newRow, colIndex);
              // reserialize
              serializeAT();
              try { if (window.markAsUnsaved) window.markAsUnsaved('assessment_tasks'); } catch (e) { /* noop */ }
              return;
            }

            // Backspace on empty input in a non-header data row -> remove the entire row
            // Only trigger removal when the empty input is in Code or Task column (first two columns)
            if (!isSectionHeader && ev.key === 'Backspace' && (colIndex === 0 || colIndex === 1)) {
              const val = (inp.value || '').trim();
              if (val === '') {
                // prevent default so browser doesn't navigate/backspace in other contexts
                ev.preventDefault();
                const rowToRemove = tr;
                // find previous non-header row to focus after removal
                let prev = rowToRemove.previousElementSibling;
                while (prev && prev.classList && prev.classList.contains('section-header')) {
                  prev = prev.previousElementSibling;
                }
                // remove the row
                rowToRemove.parentNode.removeChild(rowToRemove);
                // focus previous row's same column input if available, otherwise first input
                if (prev) {
                  if (!focusInputInRow(prev, colIndex)) {
                    const fi = prev.querySelector('input'); if (fi) setCaretToEnd(fi);
                  }
                }
                serializeAT();
                try { if (window.markAsUnsaved) window.markAsUnsaved('assessment_tasks'); } catch (e) { /* noop */ }
              }
            }
          } catch (e) { /* noop */ }
        });
      }

      // helper: add an ILO column into the AT table at the given ILO index (0-based), inserting to the right of that index if desired
      function addIloColumnInAT(atTable, iloIndex /* optional */, insertAfter = true) {
        if (!atTable) return null;
        try {
          const headerRow = atTable.querySelector('thead tr:nth-child(2)');
          const ths = Array.from(headerRow.querySelectorAll('th'));
          const totalTh = ths.length;
          const iloStart = 4; // after Code, Task, I/R/D, %
          const domainStart = totalTh - 3; // index where domain (C,P,A) starts

          // compute insertion index within the ILO block
          let insertAt;
          if (typeof iloIndex === 'number') {
            // clamp to existing ILO range
            const iloCount = Math.max(0, domainStart - iloStart);
            const clamped = Math.max(0, Math.min(iloIndex + (insertAfter ? 1 : 0), iloCount));
            insertAt = iloStart + clamped;
          } else {
            // append at end of ILOs (i.e., right before domainStart)
            insertAt = domainStart;
          }

          // create new TH header cell only in the second header row (numbered ILOs)
          const newTh = document.createElement('th');
          const currentIloCount = Math.max(0, domainStart - iloStart);
          newTh.textContent = (currentIloCount + 1);
          const refTh = ths[insertAt] || null;
          if (refTh) headerRow.insertBefore(newTh, refTh); else headerRow.appendChild(newTh);

          // adjust first thead row Intended Learning Outcomes colspan only (don't add actual THs there)
          const firstRow = atTable.querySelector('thead tr:first-child');
          const iloLabelTh = Array.from(firstRow.querySelectorAll('th')).find(th => /Intended Learning Outcomes/i.test(th.textContent || ''));
          if (iloLabelTh) {
            const current = parseInt(iloLabelTh.getAttribute('colspan') || '0', 10) || 0;
            iloLabelTh.setAttribute('colspan', current + 1);
          }

          // update colgroup: insert a col at the exact logical column index (aligned with second header)
          const colgroup = atTable.querySelector('colgroup');
          if (colgroup) {
            const newCol = document.createElement('col');
            newCol.style.width = '';
            const refCol = colgroup.children[insertAt] || null;
            if (refCol) colgroup.insertBefore(newCol, refCol); else colgroup.appendChild(newCol);
          }

          // insert cells into each tbody row at the same logical position
          // If a row contains a cell with a colspan that covers the insertion point,
          // increment that cell's colspan instead of inserting a new physical cell.
          atTable.querySelectorAll('tbody > tr').forEach((r) => {
            const cells = Array.from(r.children);
            // compute cumulative column index and detect spanning cells
            let cum = 0;
            let handled = false;
            for (let ci = 0; ci < cells.length; ci++) {
              const cell = cells[ci];
              const colspan = parseInt(cell.getAttribute('colspan') || '1', 10) || 1;
              const start = cum;
              const end = cum + colspan - 1;
              if (insertAt >= start && insertAt <= end) {
                // this cell spans the insertion point -> increase colspan
                if (colspan > 1) {
                  cell.setAttribute('colspan', colspan + 1);
                  handled = true;
                  break;
                }
                // colspan === 1: we'll insert before this cell
                break;
              }
              cum += colspan;
            }
            if (handled) return; // nothing else to do for this row

            // find reference cell position considering colspan values
            cum = 0; let refCell = null;
            for (let ci = 0; ci < cells.length; ci++) {
              const cell = cells[ci];
              const colspan = parseInt(cell.getAttribute('colspan') || '1', 10) || 1;
              const start = cum;
              const end = cum + colspan - 1;
              if (insertAt <= end) { refCell = cell; break; }
              cum += colspan;
            }

            const isHeader = r.classList && r.classList.contains('section-header');
            const newCell = document.createElement(isHeader ? 'th' : 'td');
            if (!isHeader) {
              const input = document.createElement('input'); input.type = 'text'; input.className = 'cis-input text-center';
              newCell.className = 'text-center';
              newCell.appendChild(input);
            }
            if (refCell) r.insertBefore(newCell, refCell); else r.appendChild(newCell);
            if (!isHeader) {
              const newInput = newCell.querySelector('input');
              if (newInput && typeof attachATHandlersToInput === 'function') attachATHandlersToInput(newInput);
            }
          });

          // renumber ILO header labels to keep them sequential (only within ILO block)
          (function(){
            try {
              const headerThs = Array.from(atTable.querySelectorAll('thead tr:nth-child(2) th'));
              const domainStartNow = headerThs.length - 3;
              let counter = 1;
              for (let i = iloStart; i < domainStartNow; i++) {
                const nth = headerThs[i]; if (nth) nth.textContent = counter++;
              }
            } catch (e) { /* noop */ }
          })();

          // normalize ILO col widths so they flex to fill remaining space
          (function(){
            try {
              const headerThs = Array.from(atTable.querySelectorAll('thead tr:nth-child(2) th'));
              const domainStartNow = headerThs.length - 3;
              const iloCount = Math.max(0, domainStartNow - iloStart);
              const cols = Array.from(atTable.querySelectorAll('colgroup col'));
              for (let i = 0; i < cols.length; i++) {
                if (i >= iloStart && i < iloStart + iloCount) {
                  cols[i].style.width = '';
                  cols[i].style.minWidth = '40px';
                }
              }
              atTable.style.tableLayout = 'fixed';
              atTable.style.width = '100%';
              void atTable.offsetWidth;
            } catch (e) { /* noop */ }
          })();

          // update footer colspan
          (function(){
            try {
              const footer = atTable.querySelector('tbody tr.footer-total');
              if (footer) {
                const iloCount = Math.max(0, atTable.querySelectorAll('thead tr:nth-child(2) th').length - 7);
                const lastTh = footer.querySelector('th:last-of-type');
                if (lastTh) lastTh.setAttribute('colspan', iloCount + 3);
              }
            } catch (e) { /* noop */ }
          })();

          return insertAt;
        } catch (e) { console.error('addIloColumnInAT error', e); return null; }
      }

      // helper: remove an ILO column (by ILO index 0-based) from the AT table
      function removeIloColumnInAT(atTable, iloIndex) {
        if (!atTable) return false;
        try {
          const headerRow = atTable.querySelector('thead tr:nth-child(2)');
          const ths = Array.from(headerRow.querySelectorAll('th'));
          const totalTh = ths.length;
          const iloStart = 4;
          const domainStart = totalTh - 3;
          const iloCount = Math.max(0, domainStart - iloStart);
          if (iloCount <= 1) return false; // keep at least one ILO

          const targetIlo = Math.max(0, Math.min(typeof iloIndex === 'number' ? iloIndex : (iloCount - 1), iloCount - 1));
          const targetAbsolute = iloStart + targetIlo;

          const targetTh = ths[targetAbsolute];
          if (targetTh) headerRow.removeChild(targetTh);

          // adjust first thead row colspan (only adjust the ILO label)
          const firstRow = atTable.querySelector('thead tr:first-child');
          const iloLabelTh = Array.from(firstRow.querySelectorAll('th')).find(th => /Intended Learning Outcomes/i.test(th.textContent || ''));
          if (iloLabelTh) {
            const current = parseInt(iloLabelTh.getAttribute('colspan') || '0', 10) || 0;
            iloLabelTh.setAttribute('colspan', Math.max(1, current - 1));
          }

          // remove col from colgroup at exact index
          const colgroup = atTable.querySelector('colgroup');
          if (colgroup) {
            const targetCol = colgroup.children[targetAbsolute] || null;
            if (targetCol) colgroup.removeChild(targetCol);
          }

          // remove each tbody cell at that index
          // If a row contains a colspan cell that covers the removal index, decrement its colspan
          atTable.querySelectorAll('tbody > tr').forEach((r) => {
            const cells = Array.from(r.children);
            let cum = 0;
            let handled = false;
            for (let ci = 0; ci < cells.length; ci++) {
              const cell = cells[ci];
              const colspan = parseInt(cell.getAttribute('colspan') || '1', 10) || 1;
              const start = cum;
              const end = cum + colspan - 1;
              if (targetAbsolute >= start && targetAbsolute <= end) {
                if (colspan > 1) {
                  cell.setAttribute('colspan', Math.max(1, colspan - 1));
                  handled = true;
                  break;
                }
                // colspan === 1 -> remove this cell
                r.removeChild(cell);
                handled = true;
                break;
              }
              cum += colspan;
            }
            if (!handled) {
              // fallback: naive index removal
              const naive = cells[targetAbsolute];
              if (naive) r.removeChild(naive);
            }
          });

          // renumber ILO headers within the ILO block
          (function(){
            try {
              const headerThs = Array.from(atTable.querySelectorAll('thead tr:nth-child(2) th'));
              const domainStartNow = headerThs.length - 3;
              let counter = 1;
              for (let i = iloStart; i < domainStartNow; i++) { const nth = headerThs[i]; if (nth) nth.textContent = counter++; }
            } catch (e) { /* noop */ }
          })();

          // update footer colspan
          (function(){
            try {
              const footer = atTable.querySelector('tbody tr.footer-total');
              if (footer) {
                const iloCountNow = Math.max(0, atTable.querySelectorAll('thead tr:nth-child(2) th').length - 7);
                const lastTh = footer.querySelector('th:last-of-type');
                if (lastTh) lastTh.setAttribute('colspan', iloCountNow + 3);
              }
            } catch (e) { /* noop */ }
          })();

          return true;
        } catch (e) { console.error('removeIloColumnInAT error', e); return false; }
      }
      // normalize ILO col widths to fill container (call after add/remove)
      function normalizeIloCols(atTable) {
        try {
          const headerThs = Array.from(atTable.querySelectorAll('thead tr:nth-child(2) th'));
          const totalTh = headerThs.length;
          const iloStart = 4;
          const iloCount = Math.max(0, totalTh - 7);
          const cols = Array.from(atTable.querySelectorAll('colgroup col'));
          for (let i = 0; i < cols.length; i++) {
            if (i >= iloStart && i < iloStart + iloCount) cols[i].style.width = '';
          }
          atTable.style.width = '100%';
          void atTable.offsetWidth; // force reflow
        } catch (e) { /* noop */ }
      }


      // attach to existing inputs (for input events)
      table.querySelectorAll('input').forEach((inp) => attachATHandlersToInput(inp));

      // delegated keydown handler on the table to ensure shortcuts work for dynamic rows
      table.addEventListener('keydown', function(ev){
        try {
          const target = ev.target;
          if (!target || target.tagName !== 'INPUT') return;
          // determine col index
          const cell = target.closest('td,th');
          const colIndex = cell ? (cell.cellIndex || 0) : 0;
          const tr = target.closest('tr');
          const isSectionHeader = tr && tr.classList && tr.classList.contains('section-header');

          // Ctrl/Cmd+Enter on header -> insert new row after header
          if (isSectionHeader && ev.key === 'Enter' && (ev.ctrlKey || ev.metaKey)) {
            ev.preventDefault();
            console.debug('AT: Ctrl+Enter detected on header, adding sub-row');
            const sample = table.querySelector('tbody > tr:not(.section-header)');
            let newRow;
            if (sample) {
              newRow = sample.cloneNode(true);
              newRow.querySelectorAll('input').forEach(i => i.value = '');
            } else {
              const cols = table.querySelectorAll('thead tr:nth-child(2) th').length;
              newRow = document.createElement('tr');
              for (let c=0;c<cols;c++) {
                const td = document.createElement('td');
                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'cis-input text-center';
                td.appendChild(input);
                newRow.appendChild(td);
              }
            }
            tr.parentNode.insertBefore(newRow, tr.nextSibling);
            // attach handlers to new inputs
            newRow.querySelectorAll('input').forEach(attachATHandlersToInput);
            const firstInp = newRow.querySelector('input');
            if (firstInp) firstInp.focus();
            serializeAT();
            try { if (window.markAsUnsaved) window.markAsUnsaved('assessment_tasks'); } catch (e) {}
            return;
          }

          // Ctrl+Enter inside an ILO column -> insert a new ILO column to the right of the current ILO
          // ILO columns are the columns after the first 4 and before the last 3
          if (ev.key === 'Enter' && (ev.ctrlKey || ev.metaKey)) {
            const theadThs = Array.from(table.querySelectorAll('thead tr:nth-child(2) th'));
            const totalTh = theadThs.length;
            const iloStart = 4; // after Code, Task, I/R/D, %
            const iloEnd = totalTh - 4; // index of last ILO
            if (colIndex >= iloStart && colIndex <= iloEnd) {
              ev.preventDefault();
              const iloIndex = colIndex - iloStart;
              const insertedAt = addIloColumnInAT(table, iloIndex, true);
              if (insertedAt !== null) {
                // focus the input in the same row and the new column
                const rowCells = Array.from(tr.children || []);
                const newCell = rowCells[insertedAt];
                if (newCell) {
                  const inp = newCell.querySelector('input');
                  if (inp) { setCaretToEnd(inp); inp.scrollIntoView({ block: 'nearest', inline: 'nearest' }); }
                }
                serializeAT();
                try { if (window.markAsUnsaved) window.markAsUnsaved('assessment_tasks'); } catch (e) {}
                // This AT-initiated column is local to AT (unsynced). Do not notify ILO module.
              }
              return;
            }
          }

          // Ctrl/Cmd+Enter on any data/sub row -> if inside an ILO column, add a new ILO column;
          // otherwise clone the current row and insert after it.
          if (!isSectionHeader && ev.key === 'Enter' && (ev.ctrlKey || ev.metaKey)) {
            // determine if focused column is an ILO column
            const theadThsCheck = Array.from(table.querySelectorAll('thead tr:nth-child(2) th'));
            const totalThCheck = theadThsCheck.length;
            const iloStartCheck = 4;
            const iloEndCheck = totalThCheck - 4;
            if (colIndex >= iloStartCheck && colIndex <= iloEndCheck) {
              ev.preventDefault();
              // add a new ILO column to the right of the current ILO
              const iloIndex = colIndex - iloStartCheck;
              const insertedAt = addIloColumnInAT(table, iloIndex, true);
              if (insertedAt !== null) {
                // focus the input in the same row and the new column
                const rowCells = Array.from(tr.children || []);
                const newCell = rowCells[insertedAt];
                if (newCell) {
                  const inp = newCell.querySelector('input');
                  if (inp) { setCaretToEnd(inp); inp.scrollIntoView({ block: 'nearest', inline: 'nearest' }); }
                }
                serializeAT();
                try { if (window.markAsUnsaved) window.markAsUnsaved('assessment_tasks'); } catch (e) {}
              }
              return;
            }

            // fallback: clone the current data row
            ev.preventDefault();
            console.debug('AT: Ctrl+Enter on data row, cloning row');
            const newRow = tr.cloneNode(true);
            newRow.querySelectorAll('input').forEach(i => i.value = '');
            tr.parentNode.insertBefore(newRow, tr.nextSibling);
            newRow.querySelectorAll('input').forEach(attachATHandlersToInput);
            const focusInp = newRow.querySelector('input'); if (focusInp) focusInp.focus();
            serializeAT();
            try { if (window.markAsUnsaved) window.markAsUnsaved('assessment_tasks'); } catch (e) {}
            return;
          }

          // Ctrl+Backspace inside an ILO column -> remove this ILO column
          if (ev.key === 'Backspace' && (ev.ctrlKey || ev.metaKey)) {
            const theadThs = Array.from(table.querySelectorAll('thead tr:nth-child(2) th'));
            const totalTh = theadThs.length;
            const iloStart = 4;
            const iloEnd = totalTh - 4;
            if (colIndex >= iloStart && colIndex <= iloEnd) {
              // Only treat Ctrl+Backspace as a column removal when the field is empty
              // and the caret is at the start. Otherwise allow normal Ctrl+Backspace.
              const selStart = (typeof target.selectionStart === 'number') ? target.selectionStart : 0;
              const selEnd = (typeof target.selectionEnd === 'number') ? target.selectionEnd : selStart;
              const val = (target.value || '').trim();
              if (!(val === '' && selStart === 0 && selEnd === 0)) {
                // let browser handle Ctrl+Backspace normally (word delete)
                return;
              }
              // Disallow removal of columns that are synced from the ILO module
              const headerTh = theadThs[colIndex];
              if (headerTh && headerTh.getAttribute('data-synced') === '1') {
                // Inform user that synced ILO columns must be removed from the ILO module
                try { alert('This ILO column is synced from the ILO module and cannot be removed here. Remove it from the ILO module instead.'); } catch (e) { /* noop */ }
                return;
              }
              ev.preventDefault();
              const iloIndex = colIndex - iloStart;
              const removed = removeIloColumnInAT(table, iloIndex);
              if (removed) {
                // focus the nearest cell in the same row (clamped)
                const newTotal = Array.from(table.querySelectorAll('thead tr:nth-child(2) th')).length;
                const newColIndex = Math.max(0, Math.min(colIndex, newTotal - 1));
                const rowCells = Array.from(tr.children || []);
                const cell = rowCells[newColIndex];
                if (cell) { const inp = cell.querySelector('input'); if (inp) { setCaretToEnd(inp); inp.scrollIntoView({ block: 'nearest', inline: 'nearest' }); } }
                serializeAT();
                try { if (window.markAsUnsaved) window.markAsUnsaved('assessment_tasks'); } catch (e) {}
                // Removal here affects only AT. Synced columns are blocked above.
              }
              return;
            }
          }

          // Backspace on empty input in first two columns of non-header row -> remove row
          if (!isSectionHeader && ev.key === 'Backspace' && (colIndex === 0 || colIndex === 1)) {
            const raw = target.value || '';
            const selStart = (typeof target.selectionStart === 'number') ? target.selectionStart : 0;
            const selEnd = (typeof target.selectionEnd === 'number') ? target.selectionEnd : selStart;
            const val = raw.trim();
            // Only remove when there's nothing to delete and caret is at the start
            if (val === '' && selStart === 0 && selEnd === 0) {
              ev.preventDefault();
              console.debug('AT: Backspace on empty input, removing row');
              const rowToRemove = tr;
              let prev = rowToRemove.previousElementSibling;
              while (prev && prev.classList && prev.classList.contains('section-header')) prev = prev.previousElementSibling;
              rowToRemove.parentNode.removeChild(rowToRemove);
              if (prev) {
                const fi = prev.querySelector('input'); if (fi) fi.focus();
              }
              serializeAT();
              try { if (window.markAsUnsaved) window.markAsUnsaved('assessment_tasks'); } catch (e) {}
            }
          }
        } catch (e) { console.error(e); }
      }, true);

      // Populate table from persisted data if available, then serialize
      function populateATFromData() {
        try {
          const table = getATTable();
          if (!table) return;
          const ta = document.querySelector('[name="assessment_tasks_data"]');
          let rows = [];
          if (ta) {
            const raw = (ta.value && ta.value.trim()) ? ta.value.trim() : (ta.getAttribute('data-original') || '').trim();
            if (raw) {
              try { rows = JSON.parse(raw); } catch (e) { rows = []; }
            }
          }
          if (!rows || !rows.length) return;

          // determine ilo count from header
          const ths = table.querySelectorAll('thead tr:nth-child(2) th');
          const iloStart = 4; // fixed
          const domainCount = 3; // C,P,A
          const iloCount = Math.max(0, ths.length - (iloStart + domainCount));

              // group rows by numeric section to recreate section headers
              const groups = [];
              const map = {};
              // rows may be either main-field objects (position null) or subfield objects with position like "1-2"
              rows.forEach(r => {
                const sec = (typeof r.section === 'number' || String(r.section).match(/^\d+$/)) ? Number(r.section) : null;
                const key = sec ? String(sec) : '0';
                if (!map[key]) { map[key] = []; groups.push(key); }
                map[key].push(r);
              });

              // Ensure default sections (1 and 2) exist and are rendered so users can always input into them
              const defaultSections = ['1','2'];
              defaultSections.forEach(ds => { if (!map[ds]) map[ds] = []; });
              // Build an ordered list: defaults first, then any other sections present
              const orderedGroups = defaultSections.concat(groups.filter(g => !defaultSections.includes(g)));

              // clear tbody and rebuild
              const tbody = table.querySelector('tbody');
              if (!tbody) return;
              while (tbody.firstChild) tbody.removeChild(tbody.firstChild);

              orderedGroups.forEach((secKey) => {
                const secNum = secKey === '0' ? null : Number(secKey);
                // create section header row
                const headerTr = document.createElement('tr'); headerTr.className = 'section-header table-light';
                const codeTh = document.createElement('th'); codeTh.className = 'text-start';
                const nameTh = document.createElement('th');
                // find a main-field item for this section (position null)
                const items = map[secKey] || [];
                const mainItem = items.find(it => it.position === null || it.position === undefined || it.position === '');
                const codeVal = mainItem ? (mainItem.code || '') : '';
                const nameVal = mainItem ? (mainItem.task || '') : '';
                const codeInp = document.createElement('input'); codeInp.type = 'text'; codeInp.name = 'section_code[]'; codeInp.className = 'cis-input text-center'; codeInp.value = codeVal; codeInp.placeholder = 'LEC';
                const nameInp = document.createElement('input'); nameInp.type = 'text'; nameInp.name = 'section_name[]'; nameInp.className = 'cis-input'; nameInp.value = nameVal; nameInp.placeholder = 'LECTURE';
                codeTh.appendChild(codeInp); nameTh.appendChild(nameInp);
                headerTr.appendChild(codeTh); headerTr.appendChild(nameTh);
                const totalCols = ths.length; for (let i = 2; i < totalCols; i++) { const th = document.createElement('th'); headerTr.appendChild(th); }
                tbody.appendChild(headerTr);

                // add sub-rows (positioned rows) in order based on position suffix
                const subItems = (map[secKey] || []).filter(it => it.position && String(it.position).indexOf('-') !== -1).slice();
                // sort by the numeric suffix after the dash (e.g., 1-2 -> 2)
                subItems.sort((a,b) => {
                  const sa = Number(String(a.position).split('-')[1] || 0);
                  const sb = Number(String(b.position).split('-')[1] || 0);
                  return sa - sb;
                });

                subItems.forEach(item => {
                  const tr = document.createElement('tr');
                  // code
                  const tdCode = document.createElement('td');
                  const inpCode = document.createElement('input'); inpCode.type='text'; inpCode.className='cis-input text-center'; inpCode.value = item.code || '';
                  tdCode.appendChild(inpCode); tr.appendChild(tdCode);
                  // task
                  const tdTask = document.createElement('td');
                  const inpTask = document.createElement('input'); inpTask.type='text'; inpTask.className='cis-input'; inpTask.value = item.task || '';
                  tdTask.appendChild(inpTask); tr.appendChild(tdTask);
                  // ird
                  const tdIrd = document.createElement('td');
                  const inpIrd = document.createElement('input'); inpIrd.type='text'; inpIrd.className='cis-input text-center'; inpIrd.value = item.ird || '';
                  tdIrd.appendChild(inpIrd); tr.appendChild(tdIrd);
                  // percent
              const tdPct = document.createElement('td');
                const inpPct = document.createElement('input'); inpPct.type='text'; inpPct.className='cis-input text-center'; inpPct.value = item.percent || '';
                tdPct.appendChild(inpPct); tr.appendChild(tdPct);
                  // ilo flags
                  const flags = Array.isArray(item.iloFlags) ? item.iloFlags : (item.ilo_flags || []);
                  for (let k = 0; k < iloCount; k++) {
                    const td = document.createElement('td'); td.className='text-center';
                    const inp = document.createElement('input'); inp.type='text'; inp.className='cis-input text-center'; inp.value = flags[k] || '';
                    td.appendChild(inp); tr.appendChild(td);
                  }
                  // C,P,A
                  const tdC = document.createElement('td'); const inpC = document.createElement('input'); inpC.type='text'; inpC.className='cis-input text-center'; inpC.value = item.c || '';
                  tdC.appendChild(inpC); tr.appendChild(tdC);
                  const tdP = document.createElement('td'); const inpP = document.createElement('input'); inpP.type='text'; inpP.className='cis-input text-center'; inpP.value = item.p || '';
                  tdP.appendChild(inpP); tr.appendChild(tdP);
                  const tdA = document.createElement('td'); const inpA = document.createElement('input'); inpA.type='text'; inpA.className='cis-input text-center'; inpA.value = item.a || '';
                  tdA.appendChild(inpA); tr.appendChild(tdA);

                  tbody.appendChild(tr);
                  [inpCode, inpTask, inpIrd, inpPct, inpC, inpP, inpA].forEach(i => { try { attachATHandlersToInput(i); } catch(e){} });
                  const iloInputs = tr.querySelectorAll('td:nth-child(n+5) input'); iloInputs.forEach(i => { try { attachATHandlersToInput(i); } catch(e){} });
                });

                // Always append one empty editable sub-row for user input (not saved if left empty)
                (function(){
                  const emptyTr = document.createElement('tr');
                  // create the same number of cells as a regular data row
                  const colsCount = ths.length;
                  // determine section-specific placeholders
                  const isLectureSection = (String(secKey) === '1');
                  const codePlaceholder = isLectureSection ? 'ME' : 'LE';
                  const taskPlaceholder = isLectureSection ? 'Midterm Exam / Final Exam / Quizzes...' : 'Laboratory Exercises / Exams...';
                  const iloStartIdx = 4; // after code,task,ird,percent
                  const domainStartIdx = ths.length - 3; // start index of C,P,A
                  for (let ci = 0; ci < colsCount; ci++) {
                    const td = document.createElement('td');
                    if (ci === 0) {
                      const inp = document.createElement('input'); inp.type = 'text'; inp.className = 'cis-input text-center'; inp.placeholder = codePlaceholder;
                      td.appendChild(inp);
                    } else if (ci === 1) {
                      const inp = document.createElement('input'); inp.type = 'text'; inp.className = 'cis-input'; inp.placeholder = taskPlaceholder;
                      td.appendChild(inp);
                    } else if (ci === 2) {
                      // I/R/D column
                      const inp = document.createElement('input'); inp.type = 'text'; inp.className = 'cis-input text-center'; inp.placeholder = 'I/R/D';
                      td.appendChild(inp);
                    } else if (ci === 3) {
                      // Percent column
                      const inp = document.createElement('input'); inp.type = 'text'; inp.className = 'cis-input text-center'; inp.placeholder = '0';
                      td.appendChild(inp);
                    } else if (ci >= iloStartIdx && ci < domainStartIdx) {
                      // ILO flags
                      const inp = document.createElement('input'); inp.type = 'text'; inp.className = 'cis-input text-center'; inp.placeholder = '-';
                      td.appendChild(inp);
                    } else {
                      // C,P,A columns
                      const inp = document.createElement('input'); inp.type = 'text'; inp.className = 'cis-input text-center'; inp.placeholder = '-';
                      td.appendChild(inp);
                    }
                    emptyTr.appendChild(td);
                  }
                  tbody.appendChild(emptyTr);
                  emptyTr.querySelectorAll('input').forEach(attachATHandlersToInput);
                })();
              });

          // add footer total row (computed percent from saved rows)
          const footerTr = document.createElement('tr'); footerTr.className = 'table-light footer-total';
          const thTotal = document.createElement('th'); thTotal.setAttribute('colspan', String(3)); thTotal.className='text-end'; thTotal.textContent = 'Total';
          // compute percent total from provided rows (rows variable defined earlier)
          let savedPercent = 0;
          try {
            savedPercent = Array.isArray(rows) ? rows.reduce((acc, r) => {
              const raw = (r && r.percent) ? String(r.percent).replace('%','').trim() : '';
              const n = parseFloat(raw);
              return acc + (Number.isFinite(n) ? n : 0);
            }, 0) : 0;
          } catch (e) { savedPercent = 0; }
          const thPct = document.createElement('th'); thPct.id = 'at-percent-total'; thPct.className='percent-total text-center'; thPct.textContent = (Math.round(savedPercent*100)/100) + '%';
          footerTr.appendChild(thTotal); footerTr.appendChild(thPct);
          const lastTh = document.createElement('th'); lastTh.setAttribute('colspan', String(iloCount + 3)); footerTr.appendChild(lastTh);
          tbody.appendChild(footerTr);

          // reserialize to update hidden textarea and percent total
          try { serializeAT(); } catch (e) { /* noop */ }
        } catch (e) { console.error('populateATFromData failed', e); }
      }

      // populate from existing saved data if any
      populateATFromData();
      // initial serialization
      serializeAT();

      // If the ILO module is present on the page, observe it for structural changes
      // and dispatch an ilo:renumber event so AT will sync even if the original
      // custom events were missed (timing/order issues). Debounce to avoid
      // noisy updates during bulk DOM work.
      (function(){
        try {
          const ilolist = document.getElementById('syllabus-ilo-sortable');
          if (!ilolist || !window.MutationObserver) return;
          let timer = null;
          const dispatchRenumber = () => {
            try {
              // compute simple numeric codes 1..N based on current ILO rows
              const rows = Array.from(ilolist.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="ilos[]"]') || r.querySelector('.ilo-badge'));
              const codes = rows.map((r, i) => String(i + 1));
              document.dispatchEvent(new CustomEvent('ilo:renumber', { detail: { codes } }));
            } catch (e) { /* noop */ }
          };

          const mo = new MutationObserver((mutations) => {
            // debounce a small window to merge multiple mutations
            if (timer) clearTimeout(timer);
            timer = setTimeout(() => { dispatchRenumber(); timer = null; }, 40);
          });
          mo.observe(ilolist, { childList: true, subtree: false });
        } catch (e) { /* noop */ }
      })();

      // Listen for ILO module requests to add/remove columns so AT performs the DOM update and normalization
      document.addEventListener('ilo:addColumn', function(ev){
        try {
          const idx = ev && ev.detail && typeof ev.detail.index === 'number' ? ev.detail.index : undefined;
          const at = getATTable();
          const inserted = addIloColumnInAT(at, idx);
          if (inserted !== null) {
            // mark the added header and col as synced so AT cannot remove them
            try {
              const headerThs = Array.from(at.querySelectorAll('thead tr:nth-child(2) th'));
              if (typeof inserted === 'number' && headerThs[inserted]) headerThs[inserted].setAttribute('data-synced','1');
              const colgroup = at.querySelector('colgroup');
              if (colgroup && colgroup.children[inserted]) colgroup.children[inserted].setAttribute('data-synced','1');
            } catch (e) { /* noop */ }
            normalizeIloCols(at);
            serializeAT();
            try { if (window.markAsUnsaved) window.markAsUnsaved('assessment_tasks'); } catch(e){}
          }
        } catch(e) { console.error(e); }
      });

      document.addEventListener('ilo:removeColumn', function(ev){
        try {
          const idx = ev && ev.detail && typeof ev.detail.index === 'number' ? ev.detail.index : undefined;
          const at = getATTable();
          const removed = removeIloColumnInAT(at, idx);
          if (removed) {
            normalizeIloCols(at);
            serializeAT();
            try { if (window.markAsUnsaved) window.markAsUnsaved('assessment_tasks'); } catch(e){}
          }
        } catch(e) { console.error(e); }
      });

      // Update header numbering when ILO ordering changes in ILO module
      document.addEventListener('ilo:renumber', function(ev){
        try {
          const codes = ev && ev.detail && Array.isArray(ev.detail.codes) ? ev.detail.codes : null;
          if (!codes) return;
          const table = getATTable();
          if (!table) return;
          const thRow = table.querySelector('thead tr:nth-child(2)');
          if (!thRow) return;
          // Ensure the AT table has the same number of ILO columns as the ILO module.
          // ILO headers live after first 4 th and before last 3 th.
          const iloStart = 4;
          let headerThs = Array.from(thRow.querySelectorAll('th'));
          const domainStart = headerThs.length - 3;
          // Count only the headers that are marked as synced (data-synced="1").
          const iloHeaders = headerThs.slice(iloStart, domainStart);
          const currentSyncedCount = iloHeaders.reduce((acc, th) => acc + ((th && th.getAttribute && th.getAttribute('data-synced') === '1') ? 1 : 0), 0);
          const desiredSyncedCount = codes.length;

          // Ensure the total number of ILO headers in AT matches the ILO module.
          // ILO headers live between iloStart and domainStart (exclusive).
          const totalIloCount = Math.max(0, domainStart - iloStart);
          const desiredIloCount = desiredSyncedCount;

          // Append columns until AT has desiredIloCount columns
          if (totalIloCount < desiredIloCount) {
            const toAdd = desiredIloCount - totalIloCount;
            for (let k = 0; k < toAdd; k++) {
              const insertedAt = addIloColumnInAT(table, undefined);
              // mark newly appended column as synced
              try {
                headerThs = Array.from(thRow.querySelectorAll('th'));
                const cols = table.querySelectorAll('colgroup col');
                const newDomainStart = headerThs.length - 3;
                const newIndex = iloStart + (newDomainStart - iloStart - 1); // last ILO header index
                const newTh = headerThs[newIndex];
                if (newTh) newTh.setAttribute('data-synced', '1');
                if (cols && cols[newIndex]) cols[newIndex].setAttribute('data-synced', '1');
              } catch (e) { /* noop */ }
            }
          }

          // If AT has more ILO columns than desired, remove right-most columns.
          if (totalIloCount > desiredIloCount) {
            let toRemove = totalIloCount - desiredIloCount;
            // Prefer removing synced columns first (right-most synced), otherwise remove right-most columns regardless.
            while (toRemove > 0) {
              headerThs = Array.from(thRow.querySelectorAll('th'));
              const dsNow = headerThs.length - 3;
              const ihNow = headerThs.slice(iloStart, dsNow);
              // find last synced index within ihNow
              let lastSynced = -1;
              for (let i = ihNow.length - 1; i >= 0; i--) {
                const th = ihNow[i];
                if (th && th.getAttribute && th.getAttribute('data-synced') === '1') { lastSynced = i; break; }
              }
              if (lastSynced >= 0) {
                removeIloColumnInAT(table, lastSynced);
              } else {
                // remove the right-most ILO column (index ihNow.length - 1)
                const removeIndex = ihNow.length - 1;
                if (removeIndex >= 0) removeIloColumnInAT(table, removeIndex);
              }
              toRemove--;
            }
          }

          // Refresh headerThs and relabel the first desiredIloCount headers to the provided codes
          headerThs = Array.from(thRow.querySelectorAll('th'));
          const domainNow = headerThs.length - 3;
          const iloNow = headerThs.slice(iloStart, domainNow);
          // relabel synced headers from left-to-right using codes
          for (let i = 0; i < iloNow.length; i++) {
            const th = iloNow[i];
            if (!th) continue;
            if (i < desiredIloCount) {
              th.textContent = (codes[i] !== undefined) ? codes[i] : String(i + 1);
              th.setAttribute('data-synced', '1');
              // mark colgroup
              try { const cols = table.querySelectorAll('colgroup col'); if (cols && cols[iloStart + i]) cols[iloStart + i].setAttribute('data-synced','1'); } catch(e){}
            } else {
              // these are AT-local columns beyond the desired synced set
              // leave their text as-is (or numeric) and remove synced flag
              if (th.getAttribute && th.getAttribute('data-synced') === '1') th.removeAttribute('data-synced');
              try { const cols = table.querySelectorAll('colgroup col'); if (cols && cols[iloStart + i]) cols[iloStart + i].removeAttribute('data-synced'); } catch(e){}
            }
          }

          // Mark ILO headers/cols that are synced from the ILO module so AT can't remove them.
          try {
            const colgroup = table.querySelector('colgroup');
            const cols = colgroup ? Array.from(colgroup.children) : [];
            const iloCountNow = Math.max(0, domainStart - iloStart);
            for (let i = 0; i < iloCountNow; i++) {
              const thIndex = iloStart + i;
              if (headerThs[thIndex]) {
                if (i < desiredIloCount) headerThs[thIndex].setAttribute('data-synced', '1');
                else headerThs[thIndex].removeAttribute('data-synced');
              }
              if (cols[thIndex]) {
                if (i < desiredIloCount) cols[thIndex].setAttribute('data-synced', '1');
                else cols[thIndex].removeAttribute('data-synced');
              }
            }
          } catch (e) { /* noop */ }

          // normalize widths and reserialize
          normalizeIloCols(table);
          serializeAT();
        } catch (e) { console.error('ilo:renumber handler error', e); }
      });

      // wire into global bindUnsavedIndicator if available
  try { if (window.bindUnsavedIndicator) window.bindUnsavedIndicator('assessment_tasks_data','assessment_tasks_left'); } catch (e) { /* noop */ }
      // Provide a lightweight global helper used by other modules to mark this module as unsaved
      try {
        window.markAsUnsaved = function(key) {
          try {
            const badgeId = 'unsaved-' + (key || 'assessment_tasks');
            if (window.markDirty && typeof window.markDirty === 'function') {
              window.markDirty(badgeId);
            } else {
              const el = document.getElementById(badgeId);
              if (el) el.classList.remove('d-none');
            }
          } catch (e) { /* noop */ }
        };

        // Expose a lightweight save function so the top Save can call it before performing the main form save.
        // This function ensures the serialized JSON is written into the hidden textarea.
        window.saveAssessmentTasks = async function() {
          try {
            // ensure latest UI state is serialized
            serializeAT();
            // small async pause to ensure any pending input events settle
            await new Promise(r => setTimeout(r, 10));
            // if the main syllabus form contains an in-form textarea for assessment_tasks_data,
            // copy the serialized value there so the browser includes it in the form submit.
            try {
              const taLegacy = document.getElementById('assessment_tasks_data');
              const taInForm = document.getElementById('assessment_tasks_data_inform');
              // prefer legacy ta (partial) for its value, but ensure both are synced
              const val = (taLegacy && taLegacy.value) ? taLegacy.value : (taInForm && taInForm.value) ? taInForm.value : '';
              if (taLegacy) taLegacy.value = val;
              if (taInForm) taInForm.value = val;
              // dispatch input so bindUnsavedIndicator picks it up
              if (taInForm) taInForm.dispatchEvent(new Event('input', { bubbles: true }));
              if (taLegacy) taLegacy.dispatchEvent(new Event('input', { bubbles: true }));
            } catch (e) { /* noop */ }

            return { success: true };
          } catch (e) {
            console.error('saveAssessmentTasks failed', e);
            throw e;
          }
        };

        // Optional helper: immediately persist AT payload to the server by calling the syllabus update route
        // Usage: await window.saveAssessmentTasksToServer(syllabusId)
        // Optional helper: immediately persist AT payload to the server by calling the syllabus update route
        // Usage: await window.saveAssessmentTasksToServer(syllabusId)
        window.saveAssessmentTasksToServer = async function(syllabusId) {
          if (!syllabusId) throw new Error('syllabusId required');
          // ensure serialized
          serializeAT();
            const ta = document.querySelector('[name="assessment_tasks_data"]');
          const payload = new FormData();
          payload.append('_method', 'PUT');
          payload.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
          payload.append('assessment_tasks_data', ta ? ta.value : '');

          const url = (window.syllabusBasePath || '/faculty/syllabi') + '/' + encodeURIComponent(syllabusId);
          const resp = await fetch(url, { method: 'POST', body: payload, credentials: 'same-origin' });
          if (!resp.ok) {
            const text = await resp.text().catch(()=>null);
            throw new Error('saveAssessmentTasksToServer failed: ' + resp.status + ' ' + (text || resp.statusText));
          }
          return resp;
        };

        // POST normalized AT rows to the dedicated endpoint (/faculty/syllabi/{id}/assessment-tasks)
        // Usage: await window.postAssessmentTasksRows(syllabusId)
        // POST normalized AT rows to the dedicated endpoint (/faculty/syllabi/{id}/assessment-tasks)
        // Usage: await window.postAssessmentTasksRows(syllabusId)
        window.postAssessmentTasksRows = async function(syllabusId) {
          if (!syllabusId) throw new Error('syllabusId required');
          // ensure latest UI state is serialized
          serializeAT();
          const ta = document.querySelector('[name="assessment_tasks_data"]');
          let rows = [];
          try {
            rows = ta && ta.value ? JSON.parse(ta.value) : [];
          } catch (e) {
            // fallback: try innerText or empty
            try {
              const raw = ta ? (ta.innerText || ta.value) : '';
              rows = raw ? JSON.parse(raw) : [];
            } catch (e2) { rows = []; }
          }

          // Normalize CPA fields to ensure they exist and are strings
          try {
            rows = Array.isArray(rows) ? rows.map(r => ({ ...r, c: (r.c ?? '')+'' , p: (r.p ?? '')+'' , a: (r.a ?? '')+'' })) : [];
          } catch (e) { /* noop */ }

          const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
          const url = (window.syllabusBasePath || '/faculty/syllabi') + '/' + encodeURIComponent(syllabusId) + '/assessment-tasks';

          // Try fetch with keepalive to allow the browser to complete the request during navigation.
          try {
            const resp = await fetch(url, {
              method: 'POST',
              credentials: 'same-origin',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
              },
              body: JSON.stringify({ rows }),
              keepalive: true,
            });

            if (!resp.ok) {
              const text = await resp.text().catch(()=>null);
              throw new Error('postAssessmentTasksRows failed: ' + resp.status + ' ' + (text || resp.statusText));
            }

            try { return await resp.json(); } catch (e) { return { success: true }; }
          } catch (fetchErr) {
            // If fetch throws (network error or aborted by navigation), attempt to fallback to sendBeacon.
            try {
                if (navigator && typeof navigator.sendBeacon === 'function') {
                try {
                  // include CSRF token in the beacon payload so Laravel's VerifyCsrfToken accepts it
                  const beaconPayload = JSON.stringify({ _token: token, rows });
                  const blob = new Blob([beaconPayload], { type: 'application/json' });
                  const beaconUrl = url;
                  const ok = navigator.sendBeacon(beaconUrl, blob);
                  // sendBeacon is fire-and-forget — treat as success when it returns true
                  if (ok) {
                    // suppress noisy console.error for the original fetch error since server likely processed it
                    console.debug('postAssessmentTasksRows: fetch failed but beacon fallback succeeded', fetchErr);
                    return { success: true, fallback: 'beacon' };
                  }
                } catch (be) { /* noop */ }
              }
            } catch (e) { /* noop */ }

            // If we reach here, rethrow original fetch error so callers can decide what to do.
            throw fetchErr;
          }
        };
  // No inline module-level Save button; use the top syllabus Save button which calls
  // window.saveAssessmentTasks() / window.postAssessmentTasksRows() before submitting.
      } catch (e) { /* noop */ }
    });
  })();
</script>
        </td>
      </tr>
    </tbody>
  </table>
