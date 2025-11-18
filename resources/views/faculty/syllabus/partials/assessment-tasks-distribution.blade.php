{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/assessment-tasks-distribution.blade.php
* Description: Assessment Tasks Distribution — Static table (server-rendered only)
-------------------------------------------------------------------------------
--}}

@php
  // Static ILO column count based on available ILOs (fallback to 5)
  $iloColsCount = max(5, ($ilos ?? collect([]))->count());
@endphp

<style>
  /* Outer wrapper to mimic CIS two-column map: left label + right detail grid */
  .at-map-outer { width: 100%; margin-bottom: 0; border: 1px solid #000; border-bottom: none; border-left: 1px solid #000; border-right: none; border-radius: 0; background: #fff; }
  .at-map-outer th, .at-map-outer td { border: none !important; }
  .at-map-left { background: #fff; border: 0; border-right: none; vertical-align: middle; text-align: center; padding: 0.75rem; }
  #at-left-title { padding-bottom: 0.5rem; border-bottom: none; }
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
  .at-map-right { padding: 0 !important; border: none; background: #fff; overflow-x: auto; margin: 0 !important; }
  .at-map-outer td.at-map-right { padding: 0 !important; }
  .at-map-outer .cis-table { border-collapse: collapse; width: 100%; }
  .at-map-right > table { width: 100%; max-width: 100%; height: 100%; margin: 0; border-spacing: 0; border-collapse: collapse; min-width: 0; table-layout: fixed; font-family: Georgia, serif; font-size: 0.78rem; line-height: 1.4; border: none; border-right: none !important; }
  .at-map-right > table th, .at-map-right > table td { border: none; padding: 0.12rem 0.18rem; vertical-align: middle; }
  .at-map-right > table tbody th, .at-map-right > table tbody td { padding-top: 0; padding-bottom: 0; }
  .at-map-right > table thead tr:nth-child(2) th:nth-child(1),
  .at-map-right > table thead tr:nth-child(2) th:nth-child(3) { padding-left: 0.06rem; padding-right: 0.06rem; }
  .at-map-right > table tbody td:nth-child(1) textarea.cis-textarea,
  .at-map-right > table tbody td:nth-child(3) textarea.cis-textarea { padding-left: 0.06rem; padding-right: 0.06rem; text-align: center; }
  .at-map-right > table tbody td:nth-child(1) textarea.cis-textarea,
  .at-map-right > table tbody td:nth-child(3) textarea.cis-textarea { max-width: 100%; min-width: 0; }
  .at-map-right > table th, .at-map-right > table td { border: none; }
  .at-map-right > table th + th, .at-map-right > table td + td { border-left: 1px solid #000 !important; }
  .at-map-right > table th:first-child, .at-map-right > table td:first-child { border-left: 1px solid #000 !important; }
  .at-map-right .cis-table thead tr:first-child th { border-bottom: 1px solid #000 !important; }
  .at-map-right .cis-table thead tr:nth-child(2) th { border-bottom: 1px solid #000 !important; }
  .at-map-right > table thead th {
    padding: 0.08rem 0.12rem;
    height: 24px;
    line-height: 24px;
    vertical-align: middle;
    box-sizing: border-box;
  }
  .at-map-right > table th:last-child, .at-map-right > table td:last-child { border-right: none; }
  .at-map-right .at-header { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0.6rem; }
  .at-map-right .at-title { font-family: Georgia, serif; font-weight: 700; font-size: 0.78rem; line-height: 24px; text-align: center; width:100%; display:block; }
  .at-map-right .cis-table thead th { background: #fff; font-weight: 700; vertical-align: middle; color: #000; }
  .at-map-right .cis-table th, .at-map-right .cis-table td { background: #fff !important; }
  .at-map-right .cis-table .table-light { background: #fff !important; --bs-table-bg: #fff; }
  .at-map-right .cis-table .table-light > th,
  .at-map-right .cis-table .table-light > td { background: #fff !important; }
  .at-map-right .cis-table thead th { text-align: center; }
  .at-map-right .cis-table thead th.text-start { text-align: left; }
  .at-map-right .cis-table thead th { font-size: 0.78rem; }
  .at-map-right .cis-table tbody textarea {
    display: block;
    width: 100%;
    padding: 0 0.10rem;
    border: none;
    line-height: 1;
    min-height: 0;
    margin: 0;
    transition: height 0.12s ease;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
    word-break: break-word;
    overflow-y: hidden;
    overflow-x: hidden;
  }
  .at-map-right .cis-table tbody td, .at-map-right .cis-table tbody th { vertical-align: top; }
  .at-map-right .cis-table .percent-total { text-align: center; font-weight: 700; }
  .at-map-right input, .at-map-right textarea { width: 100%; min-width: 0; max-width: 100%; box-sizing: border-box; }
  .at-map-right .cis-table thead th { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .at-map-right .cis-table tbody th, .at-map-right .cis-table tbody td {
    white-space: normal;
    overflow: visible;
    text-overflow: unset;
    word-break: break-word;
    overflow-wrap: anywhere;
    height: auto;
    line-height: 1;
  }
  #at-left-title.cis-label {
    font-weight: 700;
    font-size: 10pt;
    font-family: 'Times New Roman', Times, serif;
    padding: 0.2rem 0.4rem;
    vertical-align: top;
    box-sizing: border-box;
    line-height: 1.2;
    border-left: none;
    border-right: none;
  }
  @media print { .at-map-left .label-vertical { transform: rotate(180deg); } }
  .at-map-outer > tbody > tr:first-child th, .at-map-outer > tbody > tr:first-child td {
    border-top: none;
    border-left: none;
    border-right: none;
  }
  textarea.cis-textarea { font-weight: 400; font-size: 0.78rem; line-height: 1; font-family: inherit; height: auto; min-height: 0; margin: 0; padding: 0 0.10rem; box-sizing: border-box; resize: none; overflow-y: hidden; overflow-x: hidden; display:block; width:100%; white-space: pre-wrap; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; border: none; }
  textarea.main-input.cis-textarea { font-weight: 700; }
  textarea.main-input.cis-textarea::placeholder { font-weight: 700; }
  textarea.main-input.cis-textarea::-webkit-input-placeholder { font-weight: 700; }
  textarea.main-input.cis-textarea:-ms-input-placeholder { font-weight: 700; }
  textarea.main-input.cis-textarea::-ms-input-placeholder { font-weight: 700; }
  textarea.sub-input.cis-textarea { font-weight: 400; }
  textarea.cis-textarea.text-center { text-align: center; padding-left: 0.06rem; padding-right: 0.06rem; }
  .at-sub-row td { background: #fafafa !important; }
</style>

<!-- Outer two-column map: left label column, right detail column -->
<table class="table table-bordered mb-4 at-map-outer cis-table" style="table-layout:fixed; border-collapse:collapse; border-spacing:0;">
  <colgroup>
    <col style="width:16%">
    <col style="width:84%">
  </colgroup>
  <tbody>
    <tr>
      <th id="at-left-title" class="align-top text-start cis-label">Assessment Method and Distribution Map</th>
      <td class="at-map-right">
        <table class="table table-bordered mb-0 cis-table" style="table-layout: fixed; margin:0;">
          <colgroup>
            <col style="width:70px;"> <!-- Code -->
            <col style="width:40%;"> <!-- Task -->
            <col style="width:48px;"> <!-- I/R/D -->
            <col style="width:48px;"> <!-- Percent -->
            @for ($c = 1; $c <= $iloColsCount; $c++)
              <col> <!-- ILO flexible -->
            @endfor
            <col style="width:48px;"> <!-- C -->
            <col style="width:48px;"> <!-- P -->
            <col style="width:48px;"> <!-- A -->
          </colgroup>
          <thead class="table-light">
            <tr>
              <th colspan="4" class="text-center cis-label">Assessment Tasks (AT) Distribution</th>
              <th class="text-center cis-label" colspan="{{ $iloColsCount }}">Intended Learning Outcomes</th>
              <th class="text-center cis-label" colspan="3">Domains</th>
            </tr>
            <tr class="text-center align-middle">
              <th>Code</th>
              <th class="text-center">Assessment Tasks</th>
              <th>I/R/D</th>
              <th>%</th>
              @for ($c = 1; $c <= $iloColsCount; $c++)
                <th>{{ $c }}</th>
              @endfor
              <th>C</th>
              <th>P</th>
              <th>A</th>
            </tr>
          </thead>
          <tbody id="at-tbody">
            {{-- Sections with main rows and sub rows --}}
            @for ($section = 1; $section <= 1; $section++)
              {{-- Main Row --}}
              <tr class="at-main-row" data-section="{{ $section }}">
                <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;">
                  <textarea class="cis-textarea main-input text-center" placeholder="-" rows="1"></textarea>
                </td>
                <td style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#f8f9fa;">
                  <textarea class="cis-textarea main-input" rows="1" readonly style="cursor:not-allowed;"></textarea>
                </td>
                <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;"></td>
                <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#f8f9fa;">
                  <textarea class="cis-textarea main-input text-center percent-input" rows="1" readonly style="cursor:not-allowed;"></textarea>
                </td>
                @for ($c = 1; $c <= $iloColsCount; $c++)
                <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;"></td>
                @endfor
                <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;"></td>
                <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;"></td>
                <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;"></td>
              </tr>
              
              {{-- Sub Rows (1 per section) --}}
              @for ($sub = 1; $sub <= 1; $sub++)
              <tr class="at-sub-row" data-section="{{ $section }}">
                <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#fafafa;">
                  <textarea class="cis-textarea sub-input text-center" placeholder="-" rows="1"></textarea>
                </td>
                <td style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#f8f9fa;">
                  <textarea class="cis-textarea sub-input" rows="1" readonly style="cursor:not-allowed;"></textarea>
                </td>
                <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#fafafa;">
                  <textarea class="cis-textarea sub-input text-center" placeholder="-" rows="1"></textarea>
                </td>
                <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#f8f9fa;">
                  <textarea class="cis-textarea sub-input text-center percent-input" rows="1" readonly style="cursor:not-allowed;"></textarea>
                </td>
                @for ($c = 1; $c <= $iloColsCount; $c++)
                <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#fafafa;">
                  <textarea class="cis-textarea sub-input text-center" placeholder="-" rows="1"></textarea>
                </td>
                @endfor
                <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#fafafa;">
                  <textarea class="cis-textarea sub-input text-center" placeholder="-" rows="1"></textarea>
                </td>
                <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#fafafa;">
                  <textarea class="cis-textarea sub-input text-center" placeholder="-" rows="1"></textarea>
                </td>
                <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#fafafa;">
                  <textarea class="cis-textarea sub-input text-center" placeholder="-" rows="1"></textarea>
                </td>
              </tr>
              @endfor
            @endfor
          </tbody>
          <tfoot>
            <tr class="table-light footer-total">
              <th colspan="3" class="text-end">Total</th>
              <th id="at-percent-total" class="percent-total text-center">0%</th>
              <th colspan="{{ $iloColsCount + 3 }}"></th>
            </tr>
          </tfoot>
        </table>
      </td>
    </tr>
  </tbody>
</table>

{{-- Hidden serialized payload --}}
<textarea id="assessment_tasks_data" name="assessment_tasks_data" form="syllabusForm" class="d-none">{{ old('assessment_tasks_data', $syllabus->assessment_tasks_data ?? '') }}</textarea>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const table = document.querySelector('.at-map-outer .cis-table');
  if (!table) return;
  
  const tbody = table.querySelector('#at-tbody');
  const iloColsCount = {{ $iloColsCount }};

  // Storage for removed row data
  const removedDataStore = {
    sections: [], // Stack of removed sections with all their data
    subRows: {} // Key: sectionNum, Value: stack of removed sub rows
  };

  // Helper to extract all data from a row
  function extractRowData(row) {
    if (!row) return null;
    
    const data = {
      cells: []
    };
    
    Array.from(row.children).forEach(cell => {
      const textarea = cell.querySelector('textarea');
      data.cells.push(textarea ? textarea.value : '');
    });
    
    return data;
  }

  // Helper to restore data to a row
  function restoreRowData(row, data) {
    if (!row || !data || !data.cells) return;
    
    Array.from(row.children).forEach((cell, index) => {
      if (index < data.cells.length) {
        const textarea = cell.querySelector('textarea');
        if (textarea) {
          textarea.value = data.cells[index];
        }
      }
    });
  }

  function calculatePercentTotal() {
    const inputs = table.querySelectorAll('textarea.percent-input');
    let total = 0;
    
    // Get section count
    const criteriaContainer = document.getElementById('criteria-sections-container');
    const sectionCount = criteriaContainer ? criteriaContainer.querySelectorAll('.section').length : 0;
    
    inputs.forEach(input => {
      // Skip hidden rows
      const row = input.closest('tr');
      if (row && row.style.display === 'none') return;
      
      // Skip sub row percents when 2+ sections
      if (sectionCount >= 2 && row && row.classList.contains('at-sub-row')) return;
      
      const value = input.value.trim().replace('%', '');
      const num = parseFloat(value);
      if (!isNaN(num)) {
        total += num;
      }
    });
    
    const totalCell = document.getElementById('at-percent-total');
    if (totalCell) {
      totalCell.textContent = Math.round(total) + '%';
    }
  }

  // Toggle main row visibility and sub row percent visibility based on section count
  function updateMainRowVisibility() {
    const criteriaContainer = document.getElementById('criteria-sections-container');
    if (!criteriaContainer) return;
    
    const criteriaSections = criteriaContainer.querySelectorAll('.section');
    const sectionCount = criteriaSections.length;
    
    const allMainRows = tbody.querySelectorAll('.at-main-row');
    const allSubRows = tbody.querySelectorAll('.at-sub-row');
    
    // Toggle main row visibility
    allMainRows.forEach((mainRow) => {
      if (sectionCount === 1) {
        mainRow.style.display = 'none';
      } else {
        mainRow.style.display = '';
      }
    });
    
    // Toggle sub row percent visibility (clear/restore values)
    allSubRows.forEach((subRow) => {
      const percentCell = subRow.querySelector('.percent-input');
      if (percentCell) {
        if (sectionCount >= 2) {
          // Store original value if not already stored
          if (!percentCell.dataset.hiddenValue) {
            percentCell.dataset.hiddenValue = percentCell.value;
          }
          percentCell.value = '';
        } else {
          // Restore value when going back to 1 section
          if (percentCell.dataset.hiddenValue) {
            percentCell.value = percentCell.dataset.hiddenValue;
            delete percentCell.dataset.hiddenValue;
          }
        }
      }
    });
    
    calculatePercentTotal();
  }

  // Get current ILO column count by counting columns in header
  function getCurrentIloCount() {
    const headerRow = table.querySelector('thead tr:nth-child(2)');
    if (!headerRow) return iloColsCount;
    // Total columns - (Code, Task, I/R/D, %, C, P, A) = ILO count
    return headerRow.children.length - 7;
  }

  // Get next section number
  function getNextSectionNumber() {
    const sections = tbody.querySelectorAll('.at-main-row');
    return sections.length + 1;
  }

  // Add a new section (main row + 1 sub row)
  window.addATSection = function() {
    const sectionNum = getNextSectionNumber();
    const currentIloCount = getCurrentIloCount();
    
    // Check if we have removed section data to restore
    const restoredSection = removedDataStore.sections.pop();
    
    // Create main row
    const mainRow = document.createElement('tr');
    mainRow.className = 'at-main-row';
    mainRow.dataset.section = sectionNum;
    
    mainRow.innerHTML = `
      <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;">
        <textarea class="cis-textarea main-input text-center" placeholder="-" rows="1"></textarea>
      </td>
      <td style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#f8f9fa;">
        <textarea class="cis-textarea main-input" rows="1" readonly style="cursor:not-allowed;"></textarea>
      </td>
      <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;"></td>
      <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#f8f9fa;">
        <textarea class="cis-textarea main-input text-center percent-input" rows="1" readonly style="cursor:not-allowed;"></textarea>
      </td>
      ${Array(currentIloCount).fill(0).map(() => '<td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;"></td>').join('')}
      <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;"></td>
      <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;"></td>
      <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;"></td>
    `;
    
    // Create sub row
    const subRow = document.createElement('tr');
    subRow.className = 'at-sub-row';
    subRow.dataset.section = sectionNum;
    
    subRow.innerHTML = `
      <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#fafafa;">
        <textarea class="cis-textarea sub-input text-center" placeholder="-" rows="1"></textarea>
      </td>
      <td style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#f8f9fa;">
        <textarea class="cis-textarea sub-input" rows="1" readonly style="cursor:not-allowed;"></textarea>
      </td>
      <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#fafafa;">
        <textarea class="cis-textarea sub-input text-center" placeholder="-" rows="1"></textarea>
      </td>
      <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#f8f9fa;">
        <textarea class="cis-textarea sub-input text-center percent-input" rows="1" readonly style="cursor:not-allowed;"></textarea>
      </td>
      ${Array(currentIloCount).fill(0).map(() => '<td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#fafafa;"><textarea class="cis-textarea sub-input text-center" placeholder="-" rows="1"></textarea></td>').join('')}
      <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#fafafa;">
        <textarea class="cis-textarea sub-input text-center" placeholder="-" rows="1"></textarea>
      </td>
      <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#fafafa;">
        <textarea class="cis-textarea sub-input text-center" placeholder="-" rows="1"></textarea>
      </td>
      <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#fafafa;">
        <textarea class="cis-textarea sub-input text-center" placeholder="-" rows="1"></textarea>
      </td>
    `;
    
    tbody.appendChild(mainRow);
    tbody.appendChild(subRow);
    
    // Restore data if available
    if (restoredSection) {
      if (restoredSection.mainRow) {
        restoreRowData(mainRow, restoredSection.mainRow);
      }
      if (restoredSection.subRows && restoredSection.subRows.length > 0) {
        restoreRowData(subRow, restoredSection.subRows[0]);
        
        // Add additional sub rows if there were more
        for (let i = 1; i < restoredSection.subRows.length; i++) {
          window.addATSubRow(sectionNum);
          const allSubRows = tbody.querySelectorAll(`.at-sub-row[data-section="${sectionNum}"]`);
          const newSubRow = allSubRows[allSubRows.length - 1];
          restoreRowData(newSubRow, restoredSection.subRows[i]);
        }
      }
    }
    
    calculatePercentTotal();
  };

  // Remove last section
  window.removeATSection = function() {
    const sections = tbody.querySelectorAll('.at-main-row');
    if (sections.length <= 1) {
      alert('Cannot remove the last section');
      return;
    }
    
    const lastSection = sections[sections.length - 1];
    const sectionNum = lastSection.dataset.section;
    
    // Save section data before removing
    const mainRow = tbody.querySelector(`.at-main-row[data-section="${sectionNum}"]`);
    const subRows = tbody.querySelectorAll(`.at-sub-row[data-section="${sectionNum}"]`);
    
    const sectionData = {
      sectionNum: sectionNum,
      mainRow: extractRowData(mainRow),
      subRows: Array.from(subRows).map(row => extractRowData(row))
    };
    
    removedDataStore.sections.push(sectionData);
    
    // Remove all rows with this section number
    const rowsToRemove = tbody.querySelectorAll(`[data-section="${sectionNum}"]`);
    rowsToRemove.forEach(row => row.remove());
    calculatePercentTotal();
  };

  // Add sub row to a specific section (defaults to last section)
  window.addATSubRow = function(sectionNum) {
    if (!sectionNum) {
      const sections = tbody.querySelectorAll('.at-main-row');
      if (sections.length === 0) return;
      sectionNum = sections[sections.length - 1].dataset.section;
    }
    
    // Check if we have removed sub row data to restore
    if (!removedDataStore.subRows[sectionNum]) {
      removedDataStore.subRows[sectionNum] = [];
    }
    const restoredSubRow = removedDataStore.subRows[sectionNum].pop();
    
    const currentIloCount = getCurrentIloCount();
    const subRow = document.createElement('tr');
    subRow.className = 'at-sub-row';
    subRow.dataset.section = sectionNum;
    
    subRow.innerHTML = `
      <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#fafafa;">
        <textarea class="cis-textarea sub-input text-center" placeholder="-" rows="1"></textarea>
      </td>
      <td style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#f8f9fa;">
        <textarea class="cis-textarea sub-input" rows="1" readonly style="cursor:not-allowed;"></textarea>
      </td>
      <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#fafafa;">
        <textarea class="cis-textarea sub-input text-center" placeholder="-" rows="1"></textarea>
      </td>
      <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#f8f9fa;">
        <textarea class="cis-textarea sub-input text-center percent-input" rows="1" readonly style="cursor:not-allowed;"></textarea>
      </td>
      ${Array(currentIloCount).fill(0).map(() => '<td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#fafafa;"><textarea class="cis-textarea sub-input text-center" placeholder="-" rows="1"></textarea></td>').join('')}
      <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#fafafa;">
        <textarea class="cis-textarea sub-input text-center" placeholder="-" rows="1"></textarea>
      </td>
      <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#fafafa;">
        <textarea class="cis-textarea sub-input text-center" placeholder="-" rows="1"></textarea>
      </td>
      <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#fafafa;">
        <textarea class="cis-textarea sub-input text-center" placeholder="-" rows="1"></textarea>
      </td>
    `;
    
    // Find last row of this section and insert after
    const allRows = Array.from(tbody.querySelectorAll(`[data-section="${sectionNum}"]`));
    if (allRows.length > 0) {
      const lastRow = allRows[allRows.length - 1];
      lastRow.after(subRow);
    }
    
    // Restore data if available
    if (restoredSubRow) {
      restoreRowData(subRow, restoredSubRow);
    }
    
    calculatePercentTotal();
  };

  // Remove last sub row from a section (defaults to last section)
  window.removeATSubRow = function(sectionNum) {
    if (!sectionNum) {
      const sections = tbody.querySelectorAll('.at-main-row');
      if (sections.length === 0) return;
      sectionNum = sections[sections.length - 1].dataset.section;
    }
    
    const subRows = tbody.querySelectorAll(`.at-sub-row[data-section="${sectionNum}"]`);
    if (subRows.length <= 1) {
      alert('Cannot remove the last sub row from a section');
      return;
    }
    
    // Save sub row data before removing
    const lastSubRow = subRows[subRows.length - 1];
    const subRowData = extractRowData(lastSubRow);
    
    if (!removedDataStore.subRows[sectionNum]) {
      removedDataStore.subRows[sectionNum] = [];
    }
    removedDataStore.subRows[sectionNum].push(subRowData);
    
    lastSubRow.remove();
    calculatePercentTotal();
  };

  // Add ILO column
  window.addATIloColumn = function() {
    const headerRow1 = table.querySelector('thead tr:nth-child(1)');
    const headerRow2 = table.querySelector('thead tr:nth-child(2)');
    const colgroup = table.querySelector('colgroup');
    
    // Update colspan in first header row
    const iloHeaderCell = headerRow1.children[1];
    const currentColspan = parseInt(iloHeaderCell.getAttribute('colspan')) || 0;
    iloHeaderCell.setAttribute('colspan', currentColspan + 1);
    
    // Add numbered header in second row (insert before C column)
    const newIloCount = getCurrentIloCount() + 1;
    const newTh = document.createElement('th');
    newTh.textContent = newIloCount;
    headerRow2.insertBefore(newTh, headerRow2.children[headerRow2.children.length - 3]);
    
    // Add col to colgroup (insert before C column)
    const newCol = document.createElement('col');
    colgroup.insertBefore(newCol, colgroup.children[colgroup.children.length - 3]);
    
    // Add cells to all rows (insert before C column which is 3rd from end)
    tbody.querySelectorAll('tr').forEach(row => {
      const newCell = document.createElement('td');
      newCell.className = 'text-center';
      const isSubRow = row.classList.contains('at-sub-row');
      newCell.style.cssText = `padding:4px 0.15rem;border:1px solid #dee2e6;${isSubRow ? 'background:#fafafa;' : ''}`;
      
      if (isSubRow) {
        newCell.innerHTML = '<textarea class="cis-textarea sub-input text-center" placeholder="-" rows="1"></textarea>';
      }
      
      row.insertBefore(newCell, row.children[row.children.length - 3]);
    });
    
    // Update footer colspan
    const footerLastCell = table.querySelector('tfoot th:last-child');
    if (footerLastCell) {
      const currentFooterColspan = parseInt(footerLastCell.getAttribute('colspan')) || 0;
      footerLastCell.setAttribute('colspan', currentFooterColspan + 1);
    }
  };

  // Remove ILO column
  window.removeATIloColumn = function() {
    const currentIloCount = getCurrentIloCount();
    if (currentIloCount <= 1) {
      console.log('Cannot remove the last ILO column');
      return;
    }
    
    const headerRow1 = table.querySelector('thead tr:nth-child(1)');
    const headerRow2 = table.querySelector('thead tr:nth-child(2)');
    const colgroup = table.querySelector('colgroup');
    
    // Update colspan in first header row
    const iloHeaderCell = headerRow1.children[1];
    const currentColspan = parseInt(iloHeaderCell.getAttribute('colspan')) || 0;
    iloHeaderCell.setAttribute('colspan', currentColspan - 1);
    
    // Remove last ILO header (4th from end: ILO, C, P, A)
    headerRow2.children[headerRow2.children.length - 4].remove();
    
    // Remove col from colgroup (4th from end)
    colgroup.children[colgroup.children.length - 4].remove();
    
    // Remove cells from all rows (4th from end)
    tbody.querySelectorAll('tr').forEach(row => {
      if (row.children.length > 4) {
        row.children[row.children.length - 4].remove();
      }
    });
    
    // Update footer colspan
    const footerLastCell = table.querySelector('tfoot th:last-child');
    if (footerLastCell) {
      const currentFooterColspan = parseInt(footerLastCell.getAttribute('colspan')) || 0;
      footerLastCell.setAttribute('colspan', currentFooterColspan - 1);
    }
  };

  // Calculate on input
  table.addEventListener('input', function(e) {
    if (e.target && e.target.classList.contains('percent-input')) {
      calculatePercentTotal();
    }
  });

  // Sync ILO columns with ILO partial
  window.syncATWithILO = function() {
    const iloList = document.getElementById('syllabus-ilo-sortable');
    if (!iloList) {
      console.warn('ILO list not found');
      return;
    }
    
    const iloRows = Array.from(iloList.querySelectorAll('tr')).filter(r => 
      r.querySelector('textarea[name="ilos[]"]') || r.querySelector('.ilo-badge')
    );
    
    const targetIloCount = iloRows.length;
    const currentIloCount = getCurrentIloCount();
    
    if (targetIloCount > currentIloCount) {
      // Add columns
      const toAdd = targetIloCount - currentIloCount;
      for (let i = 0; i < toAdd; i++) {
        window.addATIloColumn();
      }
      console.log(`Added ${toAdd} ILO column(s)`);
    } else if (targetIloCount < currentIloCount) {
      // Remove columns
      const toRemove = currentIloCount - targetIloCount;
      for (let i = 0; i < toRemove; i++) {
        window.removeATIloColumn();
      }
      console.log(`Removed ${toRemove} ILO column(s)`);
    } else {
      console.log('ILO columns already synced');
    }
  };

  // Listen for ILO changes and auto-sync
  const iloList = document.getElementById('syllabus-ilo-sortable');
  if (iloList && window.MutationObserver) {
    const iloObserver = new MutationObserver(function() {
      // Debounce to avoid multiple rapid syncs
      clearTimeout(window._iloSyncTimeout);
      window._iloSyncTimeout = setTimeout(() => {
        window.syncATWithILO();
      }, 300);
    });
    
    iloObserver.observe(iloList, { childList: true, subtree: false });
  }

  // Load saved assessment tasks from database
  window.loadAssessmentTasks = function() {
    const syllabusId = {{ $syllabus->id }};
    const url = `/faculty/syllabi/${syllabusId}/assessment-tasks`;
    
    fetch(url, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
    })
    .then(response => response.json())
    .then(data => {
      if (data.success && data.sections && data.sections.length > 0) {
        console.log('Loading assessment tasks:', data.sections);
        
        // Clear existing rows first
        tbody.querySelectorAll('tr').forEach(row => row.remove());
        
        // Rebuild table from loaded data
        data.sections.forEach((section, index) => {
          const sectionNum = section.section_num || (index + 1);
          
          // Create main row
          const mainRow = document.createElement('tr');
          mainRow.className = 'at-main-row';
          mainRow.dataset.section = sectionNum;
          
          const currentIloCount = getCurrentIloCount();
          mainRow.innerHTML = `
            <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;">
              <textarea class="cis-textarea main-input text-center" placeholder="-" rows="1">${section.main_row?.code || ''}</textarea>
            </td>
            <td style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#f8f9fa;">
              <textarea class="cis-textarea main-input" rows="1" readonly style="cursor:not-allowed;">${section.main_row?.task || ''}</textarea>
            </td>
            <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;"></td>
            <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#f8f9fa;">
              <textarea class="cis-textarea main-input text-center percent-input" rows="1" readonly style="cursor:not-allowed;">${section.main_row?.percent ? Math.round(section.main_row.percent) : ''}</textarea>
            </td>
            ${Array(currentIloCount).fill(0).map((_, i) => {
              const val = section.main_ilo_columns?.[i] || '';
              return `<td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;"><textarea class="cis-textarea main-input text-center" placeholder="-" rows="1">${val}</textarea></td>`;
            }).join('')}
            <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;"></td>
            <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;"></td>
            <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;"></td>
          `;
          tbody.appendChild(mainRow);
          
          // Create sub rows
          const subRows = section.sub_rows || [];
          subRows.forEach((subRow) => {
            const subRowEl = document.createElement('tr');
            subRowEl.className = 'at-sub-row';
            subRowEl.dataset.section = sectionNum;
            
            subRowEl.innerHTML = `
              <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#fafafa;">
                <textarea class="cis-textarea sub-input text-center" placeholder="-" rows="1">${subRow.code || ''}</textarea>
              </td>
              <td style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#f8f9fa;">
                <textarea class="cis-textarea sub-input" rows="1" readonly style="cursor:not-allowed;">${subRow.task || ''}</textarea>
              </td>
              <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#fafafa;">
                <textarea class="cis-textarea sub-input text-center" placeholder="-" rows="1">${subRow.ird || ''}</textarea>
              </td>
              <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#f8f9fa;">
                <textarea class="cis-textarea sub-input text-center percent-input" rows="1" readonly style="cursor:not-allowed;">${subRow.percent ? Math.round(subRow.percent) : ''}</textarea>
              </td>
              ${Array(currentIloCount).fill(0).map((_, i) => {
                const val = subRow.ilo_columns?.[i] || '';
                return `<td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#fafafa;"><textarea class="cis-textarea sub-input text-center" placeholder="-" rows="1">${val}</textarea></td>`;
              }).join('')}
              <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#fafafa;">
                <textarea class="cis-textarea sub-input text-center" placeholder="-" rows="1">${subRow.cpa_columns?.[0] || ''}</textarea>
              </td>
              <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#fafafa;">
                <textarea class="cis-textarea sub-input text-center" placeholder="-" rows="1">${subRow.cpa_columns?.[1] || ''}</textarea>
              </td>
              <td class="text-center" style="padding:4px 0.15rem;border:1px solid #dee2e6;background:#fafafa;">
                <textarea class="cis-textarea sub-input text-center" placeholder="-" rows="1">${subRow.cpa_columns?.[2] || ''}</textarea>
              </td>
            `;
            tbody.appendChild(subRowEl);
          });
        });
        
        // Apply visibility logic after loading
        updateMainRowVisibility();
        
        // Recalculate total after loading
        calculatePercentTotal();
        
        console.log('Assessment tasks loaded successfully');
      } else {
        console.log('No saved assessment tasks found, keeping default structure');
      }
    })
    .catch(error => {
      console.error('Error loading assessment tasks:', error);
    });
  };

  // Load saved data first, then do initial syncs
  setTimeout(() => {
    window.loadAssessmentTasks();
  }, 300);

  // Initial sync on page load
  setTimeout(() => {
    window.syncATWithILO();
  }, 500);

  // Sync sections and sub rows with Criteria for Assessment
  window.syncATWithCriteria = function() {
    const criteriaContainer = document.getElementById('criteria-sections-container');
    if (!criteriaContainer) {
      console.warn('Criteria container not found');
      return;
    }
    
    const criteriaSections = criteriaContainer.querySelectorAll('.section');
    const targetSectionCount = criteriaSections.length;
    const currentSections = tbody.querySelectorAll('.at-main-row');
    const currentSectionCount = currentSections.length;
    
    // Sync section count
    if (targetSectionCount > currentSectionCount) {
      const toAdd = targetSectionCount - currentSectionCount;
      for (let i = 0; i < toAdd; i++) {
        window.addATSection();
      }
      console.log(`Added ${toAdd} section(s)`);
    } else if (targetSectionCount < currentSectionCount) {
      const toRemove = currentSectionCount - targetSectionCount;
      for (let i = 0; i < toRemove; i++) {
        window.removeATSection();
      }
      console.log(`Removed ${toRemove} section(s)`);
    }
    
    // Sync sub rows for each section
    criteriaSections.forEach((criteriaSection, index) => {
      const subLines = criteriaSection.querySelectorAll('.sub-list .sub-line');
      const targetSubCount = Math.max(1, subLines.length); // At least 1 sub row
      
      const sectionNum = index + 1;
      const atSubRows = tbody.querySelectorAll(`.at-sub-row[data-section="${sectionNum}"]`);
      const currentSubCount = atSubRows.length;
      
      if (targetSubCount > currentSubCount) {
        const toAdd = targetSubCount - currentSubCount;
        for (let i = 0; i < toAdd; i++) {
          window.addATSubRow(sectionNum);
        }
        console.log(`Section ${sectionNum}: Added ${toAdd} sub row(s)`);
      } else if (targetSubCount < currentSubCount) {
        const toRemove = currentSubCount - targetSubCount;
        for (let i = 0; i < toRemove; i++) {
          window.removeATSubRow(sectionNum);
        }
        console.log(`Section ${sectionNum}: Removed ${toRemove} sub row(s)`);
      }
    });
    
    console.log('AT synced with Criteria');
    
    // Update main row visibility after sync
    updateMainRowVisibility();
  };

  // Listen for Criteria changes and auto-sync
  const criteriaContainer = document.getElementById('criteria-sections-container');
  if (criteriaContainer && window.MutationObserver) {
    const criteriaObserver = new MutationObserver(function() {
      // Debounce to avoid multiple rapid syncs
      clearTimeout(window._criteriaSyncTimeout);
      window._criteriaSyncTimeout = setTimeout(() => {
        window.syncATWithCriteria();
        updateMainRowVisibility();
      }, 300);
    });
    
    criteriaObserver.observe(criteriaContainer, { 
      childList: true, 
      subtree: true,
      attributes: false 
    });
  }

  // Initial criteria sync on page load
  setTimeout(() => {
    window.syncATWithCriteria();
  }, 600);

  // Sync Criteria main input values to AT main row Task column
  window.syncCriteriaValuesToAT = function() {
    const criteriaContainer = document.getElementById('criteria-sections-container');
    if (!criteriaContainer) return;
    
    const criteriaSections = criteriaContainer.querySelectorAll('.section');
    
    criteriaSections.forEach((criteriaSection, index) => {
      const sectionNum = index + 1;
      const mainInput = criteriaSection.querySelector('.section-head .main-input');
      if (!mainInput) return;
      
      let mainValue = mainInput.value || '';
      
      // Remove numbers, %, and parentheses from the text (numbers will go to percent column instead)
      mainValue = mainValue.replace(/\d+|%|\(|\)/g, '').trim();
      
      // Find corresponding AT main row
      const atMainRow = tbody.querySelector(`.at-main-row[data-section="${sectionNum}"]`);
      if (!atMainRow) return;
      
      // Update Task column (2nd column, index 1)
      const taskCell = atMainRow.children[1];
      if (!taskCell) return;
      
      const taskTextarea = taskCell.querySelector('textarea.main-input');
      if (taskTextarea && taskTextarea.value !== mainValue) {
        taskTextarea.value = mainValue;
      }
    });
  };

  // Sync sub-line values to AT sub rows
  window.syncCriteriaSubLinesToAT = function() {
    const criteriaContainer = document.getElementById('criteria-sections-container');
    if (!criteriaContainer) return;
    
    const criteriaSections = criteriaContainer.querySelectorAll('.section');
    
    criteriaSections.forEach((criteriaSection, index) => {
      const sectionNum = index + 1;
      const subLines = criteriaSection.querySelectorAll('.sub-list .sub-line');
      
      subLines.forEach((subLine, subIndex) => {
        const subInput = subLine.querySelector('.sub-input');
        if (!subInput) return;
        
        const subValue = subInput.value || '';
        
        // Find corresponding AT sub row
        const atSubRows = tbody.querySelectorAll(`.at-sub-row[data-section="${sectionNum}"]`);
        const atSubRow = atSubRows[subIndex];
        if (!atSubRow) return;
        
        // Update Task column (2nd column, index 1)
        const taskCell = atSubRow.children[1];
        if (!taskCell) return;
        
        const taskTextarea = taskCell.querySelector('textarea.sub-input');
        if (taskTextarea && taskTextarea.value !== subValue) {
          taskTextarea.value = subValue;
        }
      });
    });
  };

  // Sync numeric values from Criteria to AT percent columns
  window.syncCriteriaPercentsToAT = function() {
    const criteriaContainer = document.getElementById('criteria-sections-container');
    if (!criteriaContainer) return;
    
    const criteriaSections = criteriaContainer.querySelectorAll('.section');
    
    criteriaSections.forEach((criteriaSection, index) => {
      const sectionNum = index + 1;
      
      // Sync main input numbers to main row percent (extract numbers only)
      const mainInput = criteriaSection.querySelector('.section-head .main-input');
      if (mainInput) {
        const mainValue = mainInput.value || '';
        const numberMatch = mainValue.match(/\d+/);
        const numberOnly = numberMatch ? numberMatch[0] : '';
        
        const atMainRow = tbody.querySelector(`.at-main-row[data-section="${sectionNum}"]`);
        if (atMainRow) {
          const percentCell = atMainRow.children[3]; // 4th column (percent)
          if (percentCell) {
            const percentTextarea = percentCell.querySelector('textarea.percent-input');
            if (percentTextarea && percentTextarea.value !== numberOnly) {
              percentTextarea.value = numberOnly;
              calculatePercentTotal();
            }
          }
        }
      }
      
      // Sync sub percent inputs to sub row percents
      const subLines = criteriaSection.querySelectorAll('.sub-list .sub-line');
      subLines.forEach((subLine, subIndex) => {
        const subPercent = subLine.querySelector('.sub-percent');
        if (subPercent) {
          let percentValue = (subPercent.value || '').replace('%', '').trim();
          
          const atSubRows = tbody.querySelectorAll(`.at-sub-row[data-section="${sectionNum}"]`);
          const atSubRow = atSubRows[subIndex];
          if (atSubRow) {
            const percentCell = atSubRow.children[3]; // 4th column (percent)
            if (percentCell) {
              const percentTextarea = percentCell.querySelector('textarea.percent-input');
              if (percentTextarea && percentTextarea.value !== percentValue) {
                percentTextarea.value = percentValue;
                calculatePercentTotal();
              }
            }
          }
        }
      });
    });
  };

  // Listen for input changes in Criteria and sync to AT
  if (criteriaContainer) {
    criteriaContainer.addEventListener('input', function(e) {
      if (e.target && (e.target.classList.contains('main-input') || e.target.classList.contains('sub-input'))) {
        clearTimeout(window._criteriaValueSyncTimeout);
        window._criteriaValueSyncTimeout = setTimeout(() => {
          window.syncCriteriaValuesToAT();
          window.syncCriteriaSubLinesToAT();
          updateMainRowVisibility();
        }, 150);
      }
      
      // Also sync percents when main-input or sub-percent changes
      if (e.target && (e.target.classList.contains('main-input') || e.target.classList.contains('sub-percent'))) {
        clearTimeout(window._criteriaPercentSyncTimeout);
        window._criteriaPercentSyncTimeout = setTimeout(() => {
          window.syncCriteriaPercentsToAT();
          updateMainRowVisibility();
        }, 150);
      }
    });
  }

  // Initial sync of Criteria values
  setTimeout(() => {
    window.syncCriteriaValuesToAT();
    window.syncCriteriaSubLinesToAT();
    window.syncCriteriaPercentsToAT();
    updateMainRowVisibility();
  }, 700);

  // Initial calculation
  calculatePercentTotal();



  // Save Assessment Tasks function
  window.saveAssessmentTasks = function() {
    return new Promise((resolve, reject) => {
      const statusEl = document.getElementById('at-save-status');
      if (statusEl) {
        statusEl.textContent = 'Saving...';
        statusEl.className = 'ms-2 text-primary';
      }

    const cleanText = (value) => (value || '').toString().trim();
    const numericOrNull = (value) => {
      const num = parseFloat((value || '').toString().replace(/[^0-9.\-]/g, ''));
      return Number.isFinite(num) ? num : null;
    };

    // Serialize table data
    const sections = [];
    const allMainRows = tbody.querySelectorAll('.at-main-row');
    
    allMainRows.forEach((mainRow) => {
      const sectionNum = mainRow.dataset.section;
      const mainCells = Array.from(mainRow.children);
      
      // Extract main row data
      const mainRowData = {
        code: cleanText(mainCells[0]?.querySelector('textarea')?.value),
        task: cleanText(mainCells[1]?.querySelector('textarea')?.value),
        percent: numericOrNull(mainCells[3]?.querySelector('textarea.percent-input')?.value),
      };
      
      // Extract main row ILO columns (starting from column 4, before C/P/A which are last 3)
      const mainIloColumns = [];
      const totalCols = mainCells.length;
      const iloStartIdx = 4;
      const iloEndIdx = totalCols - 3;
      
      for (let i = iloStartIdx; i < iloEndIdx; i++) {
        const val = mainCells[i]?.querySelector('textarea')?.value || '';
        mainIloColumns.push(val);
      }
      
      // Extract sub rows for this section
      const subRows = [];
      const allSubRows = tbody.querySelectorAll(`.at-sub-row[data-section="${sectionNum}"]`);
      
      allSubRows.forEach((subRow) => {
        const subCells = Array.from(subRow.children);
        
        const iloColumns = [];
        for (let i = iloStartIdx; i < iloEndIdx; i++) {
          const val = subCells[i]?.querySelector('textarea')?.value || '';
          iloColumns.push(val);
        }
        
        // Extract C, P, A from the last 3 columns
        const cTextarea = subCells[totalCols - 3]?.querySelector('textarea');
        const pTextarea = subCells[totalCols - 2]?.querySelector('textarea');
        const aTextarea = subCells[totalCols - 1]?.querySelector('textarea');
        
        const cValue = cTextarea ? (cTextarea.value || '').trim() : '';
        const pValue = pTextarea ? (pTextarea.value || '').trim() : '';
        const aValue = aTextarea ? (aTextarea.value || '').trim() : '';
        
        // Parse as integers, but only if the value is numeric
        const parseIntSafe = (val) => {
          if (!val) return null;
          const parsed = parseInt(val, 10);
          return isNaN(parsed) ? null : parsed;
        };
        
        const cpaColumns = [
          parseIntSafe(cValue),
          parseIntSafe(pValue),
          parseIntSafe(aValue),
        ];
        
        subRows.push({
          code: cleanText(subCells[0]?.querySelector('textarea')?.value),
          task: cleanText(subCells[1]?.querySelector('textarea')?.value),
          ird: cleanText(subCells[2]?.querySelector('textarea')?.value),
          percent: numericOrNull(subCells[3]?.querySelector('textarea.percent-input')?.value),
          ilo_columns: iloColumns,
          cpa_columns: cpaColumns,
        });
      });
      
      sections.push({
        section_num: sectionNum ? parseInt(sectionNum, 10) : null,
        section_label: mainRowData.task || null,
        main_row: mainRowData,
        main_ilo_columns: mainIloColumns,
        sub_rows: subRows,
      });
    });
    
    const payload = { sections: sections };
    const hiddenInput = document.getElementById('assessment_tasks_data');
    if (hiddenInput) {
      hiddenInput.value = JSON.stringify(payload);
      hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
    
    // Send to server
    const syllabusId = {{ $syllabus->id }};
    const url = `/faculty/syllabi/${syllabusId}/assessment-tasks`;
    
    fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Accept': 'application/json',
      },
      body: JSON.stringify(payload),
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          if (statusEl) {
            statusEl.textContent = '✓ Saved successfully!';
            statusEl.className = 'ms-2 text-success';
            setTimeout(() => {
              statusEl.textContent = '';
            }, 3000);
          }
          console.log('Assessment tasks saved:', data);
          resolve(data);
        } else {
          if (statusEl) {
            statusEl.textContent = '✗ Save failed: ' + (data.message || 'Unknown error');
            statusEl.className = 'ms-2 text-danger';
          }
          console.error('Save failed:', data);
          reject(new Error(data.message || 'Save failed'));
        }
      })
      .catch(error => {
        if (statusEl) {
          statusEl.textContent = '✗ Error: ' + error.message;
          statusEl.className = 'ms-2 text-danger';
        }
        console.error('Error saving assessment tasks:', error);
        reject(error);
      });
    });
  };
});
</script>
