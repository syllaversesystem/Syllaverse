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
  .at-map-right > table { width: auto; max-width: 100%; height: 100%; margin: 0; border-spacing: 0; border-collapse: collapse; min-width: 0; table-layout: fixed; font-family: Georgia, serif; font-size: 13px; line-height: 1.4; border: none; border-right: 1px solid #343a40; }
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
        <span id="unsaved-assessment_tasks_left" class="unsaved-pill d-none">Unsaved</span>
      </th>
      <td class="at-map-right">
  <table class="table table-bordered mb-0 cis-table" style="table-layout: fixed; margin:0;">
          @php
            // allocate widths so columns fill the inner table container.
            // code: fixed 40px, task: 40%, remaining share: 60% divided equally among (I/R/D, %, ILOs, C,P,A)
            $remainingCols = count($iloCols) + 5; // I/R/D + % + ILOs + C,P,A
            $share = 60 / $remainingCols;
          @endphp
          <colgroup>
            <col style="width:40px;"> <!-- Code fixed -->
            <col style="width:40%;"> <!-- Task large -->
            <col style="width:{{ $share }}%;"> <!-- I/R/D -->
            <col style="width:{{ $share }}%;"> <!-- Percent -->
            @foreach ($iloCols as $c)
              <col style="width:{{ $share }}%;"> <!-- ILO -->
            @endforeach
            <col style="width:{{ $share }}%;"> <!-- C -->
            <col style="width:{{ $share }}%;"> <!-- P -->
            <col style="width:{{ $share }}%;"> <!-- A -->
          </colgroup>
  <thead class="table-light">
    <tr>
  <th colspan="4" class="text-start">
        <div class="at-title fw-bold cis-label">Assessment Tasks (AT) Distribution
          <span id="unsaved-assessment_tasks" class="unsaved-pill d-none">Unsaved</span>
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
          <td class="text-center"><input type="text" class="cis-input text-center" placeholder=""></td>
        @endforeach
        <td class="text-center"><input type="text" class="cis-input text-center" placeholder=""></td>
        <td class="text-center"><input type="text" class="cis-input text-center" placeholder=""></td>
        <td class="text-center"><input type="text" class="cis-input text-center" placeholder=""></td>
      </tr>
    @endfor

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
          <td class="text-center"><input type="text" class="cis-input text-center" placeholder=""></td>
        @endforeach
        <td class="text-center"><input type="text" class="cis-input text-center" placeholder=""></td>
        <td class="text-center"><input type="text" class="cis-input text-center" placeholder=""></td>
        <td class="text-center"><input type="text" class="cis-input text-center" placeholder=""></td>
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
        <textarea id="assessment_tasks_data" name="assessment_tasks_data" class="d-none" data-original="{{ old('assessment_tasks_data', $syllabus->assessment_tasks_data ?? '') }}">{{ old('assessment_tasks_data', $syllabus->assessment_tasks_data ?? '') }}</textarea>

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

    function serializeAT() {
      const table = document.querySelector('table.cis-table');
      if (!table) return;
      const rows = Array.from(table.querySelectorAll('tbody > tr')).filter(r => !r.classList.contains('table-light'));
      const sections = [];
      // Determine section by scanning for section header rows (with .table-light)
      // We'll group rows under their most recent section header (e.g., LEC, LAB)
      let currentSection = 'General';
      const out = [];
      let percentTotal = 0;
      // Walk rows including headers to keep section context
      const allRows = Array.from(table.querySelectorAll('tbody > tr'));
      allRows.forEach((r) => {
        if (r.classList.contains('table-light') && r.querySelector('th')) {
          // try to read editable section inputs if present
          const codeInp = r.querySelector('input[name="section_code[]"]');
          const nameInp = r.querySelector('input[name="section_name[]"]');
          if (codeInp || nameInp) {
            const code = codeInp ? (codeInp.value || '').trim() : '';
            const name = nameInp ? (nameInp.value || '').trim() : '';
            // fallback to placeholder text when value is empty so visual placeholders become the serialized section label
            const codeLabel = code || (codeInp && codeInp.getAttribute('placeholder')) || '';
            const nameLabel = name || (nameInp && nameInp.getAttribute('placeholder')) || '';
            if (codeLabel || nameLabel) {
              currentSection = (codeLabel && nameLabel) ? (codeLabel + ' — ' + nameLabel) : (codeLabel || nameLabel);
            }
          } else {
            currentSection = (r.innerText || '').trim();
          }
          return;
        }
        // data rows
        const inputs = Array.from(r.querySelectorAll('input'));
        if (!inputs.length) return;
  // map columns with new I/R/D column added:
  // 0: code, 1: task, 2:  I/R/D, 3: %, next iloCols, then T,C,P,A
  const code = inputs[0] ? inputs[0].value : '';
  const task = inputs[1] ? inputs[1].value : '';
  const ird = inputs[2] ? inputs[2].value : '';
  const pct = inputs[3] ? inputs[3].value : '';
  // ilo flags are next N inputs; compute count from header (subtract 4 fixed columns)
  const iloFlagCount = (function(){ const ths = table.querySelectorAll('thead tr:nth-child(2) th'); return Math.max(0, ths.length - (4)); })();
        const iloFlags = [];
        for (let i=0;i<iloFlagCount;i++) {
          const idx = 4 + i;
          if (inputs[idx]) iloFlags.push(inputs[idx].value || '');
        }
    // trailing C,P,A
    const trailing = [];
  const trailingStart = 4 + iloFlagCount;
    for (let j=0;j<3;j++) {
      const idx = trailingStart + j;
      trailing.push(inputs[idx] ? inputs[idx].value || '' : '');
    }

  const item = { section: currentSection, code, task, ird, percent: pct, iloFlags, c: trailing[0], p: trailing[1], a: trailing[2] };
        out.push(item);
        percentTotal += toNumber(pct);
      });

      // write to hidden textarea and dispatch input so global bindUnsavedIndicator picks it up
      const ta = document.getElementById('assessment_tasks_data');
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
      const table = document.querySelector('table.cis-table');
      if (!table) return;
      table.querySelectorAll('input').forEach((inp) => {
        inp.addEventListener('input', function(){
          serializeAT();
          // if a global helper exists, mark module unsaved
          try { if (window.markAsUnsaved) window.markAsUnsaved('assessment_tasks'); } catch (e) { /* noop */ }
        });
      });

      // initial serialization
      serializeAT();

      // wire into global bindUnsavedIndicator if available
      try { if (window.bindUnsavedIndicator) window.bindUnsavedIndicator('assessment_tasks_data','assessment_tasks'); } catch (e) { /* noop */ }
    });
  })();
</script>
        </td>
      </tr>
    </tbody>
  </table>
