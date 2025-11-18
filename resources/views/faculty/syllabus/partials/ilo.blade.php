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
  {{-- Using new batch upsert endpoint (PUT /{syllabus}/ilos); JS handles method override. --}}
  <script>
    // Prevent native form submission; rely solely on JS bulk save (window.saveIlo)
    document.addEventListener('DOMContentLoaded', function(){
      var f = document.getElementById('iloForm');
      if (f) {
        f.addEventListener('submit', function(ev){ ev.preventDefault(); ev.stopPropagation(); });
      }
    });
  </script>

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
  /* force header text style (ILO code + description) to Times New Roman 10pt black */
  #ilo-right-wrap > table thead th.cis-label { color:#000 !important; font-family:'Times New Roman', Times, serif !important; font-size:10pt !important; }
  /* Make the first header cell ("ILO") shrink to content and avoid growing */
  #ilo-right-wrap > table thead th.cis-label:first-child { white-space: nowrap; width:1%; }
  /* show cell borders for the inner ILO table (internal grid only) – now forced black */
    #ilo-right-wrap > table th, #ilo-right-wrap > table td { border: 1px solid #000; }
  /* hide outer edges so only internal dividers remain */
  #ilo-right-wrap > table thead th { border-top: 0; }
  #ilo-right-wrap > table th:first-child, #ilo-right-wrap > table td:first-child { border-left: 0; }
  #ilo-right-wrap > table th:last-child, #ilo-right-wrap > table td:last-child { border-right: 0; }
  /* remove the bottom-most outer border line of the inner table (no visible border under last row) */
  #ilo-right-wrap > table tbody tr:last-child td { border-bottom: 0 !important; }
  /* Ensure badge/code cell and grip align and don't push width */
  /* ILO badge: allow auto sizing (no forced min-width) so column shrinks to content */
  .ilo-badge { display: inline-block; min-width: 0; width: auto; padding: 0; text-align: center; color:#000; white-space: normal; line-height: 1.1; }
  
    /* tighten header and first column padding to reduce visual height */
    /* Uniform 6.4px padding for ILO code header and code cells */
  #ilo-right-wrap > table thead th.cis-label:first-child { padding: 6.4px !important; }
  #ilo-right-wrap > table td:first-child, #ilo-right-wrap > table th:first-child { padding: 6.4px !important; }
  .drag-handle { width: 28px; display: inline-flex; justify-content: center; }
  /* Make textarea fill remaining space and autosize */
  .cis-textarea { width: 100%; box-sizing: border-box; resize: none; }
  /* Align ILO textareas styling with Course Title textareas (single-line autosize) */
  #ilo-right-wrap textarea.cis-textarea.autosize { overflow: hidden; }
  /* Ensure the left header cell aligns with other CIS module headers */
  table.cis-table th.cis-label, table.cis-table th { vertical-align: top; }
  /* Icon-only header buttons styled like Add Dept / syllabus toolbar */
  .ilo-header-actions .btn {
    position: relative; padding: 0 !important;
    width: 2.2rem; height: 2.2rem; min-width: 2.2rem; min-height: 2.2rem;
    border-radius: 50% !important;
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--sv-card-bg, #fff);
    border: none; box-shadow: none; color: #000;
    transition: all 0.2s ease-in-out;
    line-height: 0; /* eliminate baseline gap for perfect centering */
  }
  .ilo-header-actions .btn .bi { font-size: 1rem; width: 1rem; height: 1rem; line-height: 1; color: var(--sv-text, #000); }
  /* Center Feather SVG icons and give them a consistent size */
  .ilo-header-actions .btn svg {
    width: 1.05rem; height: 1.05rem;
    display: block; margin: 0; vertical-align: middle;
    stroke: currentColor; /* inherit button/text color */
  }
  .ilo-header-actions .btn:hover, .ilo-header-actions .btn:focus {
    background: linear-gradient(135deg, rgba(255,240,235,.88), rgba(255,255,255,.46));
    backdrop-filter: blur(7px); -webkit-backdrop-filter: blur(7px);
    box-shadow: 0 4px 10px rgba(204,55,55,.12);
    color: #CB3737;
  }
  .ilo-header-actions .btn:hover .bi, .ilo-header-actions .btn:focus .bi { color: #CB3737; }
  .ilo-header-actions .btn:active { transform: scale(.97); filter: brightness(.98); }
  </style>

  <table class="table table-bordered mb-4 cis-table">
    <colgroup>
      <col style="width:16%">
      <col style="width:84%">
    </colgroup>
    <tbody>
            <tr>
        <th id="ilo-left-title" class="align-top text-start cis-label">Intended Learning Outcomes (ILO)</th>
        <td id="ilo-right-wrap">
          <table class="table mb-0" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.4; border: none;">
            <colgroup>
              <col style="width:70px"> <!-- ILO code column fixed -->
              <col style="width:auto"> <!-- Description column flexes remaining -->
            </colgroup>
            <thead>
              <tr class="table-light">
                <th class="text-center align-middle cis-label">ILO</th>
                <th class="text-start align-middle cis-label">
                  <div class="d-flex justify-content-between align-items-center gap-2">
                    <span>Upon completion of this course, the students should be able to:</span>
                    <span class="ilo-header-actions d-inline-flex gap-1" style="white-space:nowrap;">
                        <button type="button" class="btn btn-sm" id="ilo-load-predefined" title="Load Predefined ILOs" aria-label="Load Predefined ILOs" style="background:transparent;">
                          <i data-feather="download"></i>
                          <span class="visually-hidden">Load Predefined ILOs</span>
                        </button>
                        <button type="button" class="btn btn-sm" id="ilo-add-header" title="Add ILO" aria-label="Add ILO" style="background:transparent;">
                          <i data-feather="plus"></i>
                          <span class="visually-hidden">Add ILO</span>
                        </button>
                    </span>
                  </div>
                </th>
              </tr>
            </thead>
            <tbody id="syllabus-ilo-sortable" data-syllabus-id="{{ $default['id'] }}">
              @forelse($ilosSorted as $index => $ilo)
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
                        class="cis-textarea cis-field autosize flex-grow-1"
                        placeholder="-"
                        rows="1"
                        style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;"
                        required>{{ old("ilos.$index", $ilo->description) }}</textarea>
                      <input type="hidden" name="code[]" value="{{ $seqCode }}">
                      <button type="button" class="btn btn-sm btn-outline-danger btn-delete-ilo ms-2" title="Delete ILO">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              @empty
                <tr id="ilo-placeholder">
                  <td colspan="2" class="text-center text-muted py-4">
                    <p class="mb-2">No ILOs added yet.</p>
                    <p class="mb-0"><small>Click the <strong>+</strong> button above to add an ILO or <strong>Load Predefined</strong> to import ILOs.</small></p>
                  </td>
                </tr>
              @endforelse
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

      // Header Add / Remove buttons
      function renumberIloRows(){
        const list = document.getElementById('syllabus-ilo-sortable');
        if(!list) return;
        const rows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="ilos[]"]') || r.querySelector('.ilo-badge'));
        rows.forEach((r,i)=>{
          const code = `ILO${i+1}`;
          const badge = r.querySelector('.ilo-badge'); if(badge) badge.textContent = code;
          const codeInput = r.querySelector('input[name="code[]"]'); if(codeInput) codeInput.value = code;
        });
        try { window.markAsUnsaved && window.markAsUnsaved('ilos'); } catch {}
      }

      // Load Predefined ILOs button - opens modal
      const loadPredefinedBtn = document.getElementById('ilo-load-predefined');
      const loadPredefinedModal = document.getElementById('loadPredefinedIlosModal');
      const confirmLoadBtn = document.getElementById('confirmLoadPredefinedIlos');
      
      if (loadPredefinedBtn && loadPredefinedModal) {
        loadPredefinedBtn.addEventListener('click', function() {
          const modal = new bootstrap.Modal(loadPredefinedModal);
          modal.show();
        });
      }

      // Confirm button in modal
      const listRef = document.getElementById('syllabus-ilo-sortable');
      if (confirmLoadBtn && listRef) {
        confirmLoadBtn.addEventListener('click', async function() {
          const syllabusId = listRef.dataset.syllabusId;
          if (!syllabusId) {
            if (window.showAlertOverlay) {
              window.showAlertOverlay('error', 'Syllabus ID not found');
            } else {
              alert('Syllabus ID not found');
            }
            return;
          }

          try {
            confirmLoadBtn.disabled = true;
            const response = await fetch(`/faculty/syllabi/${syllabusId}/load-predefined-ilos`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
              }
            });

            if (!response.ok) {
              const errorData = await response.json().catch(() => ({}));
              throw new Error(errorData.message || 'Failed to load predefined ILOs');
            }

            const data = await response.json();
            
            if (!data.ilos || data.ilos.length === 0) {
              if (window.showAlertOverlay) {
                window.showAlertOverlay('error', 'No predefined ILOs found for this course.');
              } else {
                alert('No predefined ILOs found for this course.');
              }
              return;
            }

            // Clear existing rows
            while (listRef.firstChild) {
              listRef.removeChild(listRef.firstChild);
            }

            // Add new rows from predefined ILOs (now preserving server IDs via data-id attribute)
            data.ilos.forEach((ilo, index) => {
              const code = `ILO${index + 1}`;
              const newRow = document.createElement('tr');
              newRow.setAttribute('data-id', ilo.id); // Preserve ID so subsequent saves perform updates, not recreates
              newRow.innerHTML = `
                <td class="text-center align-middle">
                  <div class="ilo-badge fw-semibold">${code}</div>
                </td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;">
                      <i class="bi bi-grip-vertical"></i>
                    </span>
                    <textarea
                      name="ilos[]"
                      class="cis-textarea cis-field autosize flex-grow-1"
                      placeholder="-"
                      rows="1"
                      style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;"
                      data-original="${(ilo.description || '').replace(/"/g, '&quot;')}"
                      required>${ilo.description || ''}</textarea>
                    <input type="hidden" name="code[]" value="${code}">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-ilo ms-2" title="Delete ILO" style="display:${ilo.id ? '' : 'none'};">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              `;
              listRef.appendChild(newRow);
              
              // Bind autosize to the new textarea
              const textarea = newRow.querySelector('textarea.autosize');
              if (textarea) bindAutosize(textarea);
            });

            try { window.markAsUnsaved && window.markAsUnsaved('ilos'); } catch {}
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(loadPredefinedModal);
            if (modal) modal.hide();
            
            // Show success notification
            if (window.showAlertOverlay) {
              window.showAlertOverlay('success', `Successfully loaded ${data.ilos.length} predefined ILO(s)`);
            }
            
          } catch (error) {
            console.error('Error loading predefined ILOs:', error);
            if (window.showAlertOverlay) {
              window.showAlertOverlay('error', error.message || 'An error occurred while loading predefined ILOs');
            } else {
              alert(error.message || 'An error occurred while loading predefined ILOs');
            }
          } finally {
            confirmLoadBtn.disabled = false;
          }
        });
      }
      // Note: Add/Remove button bindings and keyboard handlers are handled by syllabus-ilo.js
      // to avoid duplicate event listeners. This inline script only handles modal loading
      // and table renumbering helpers.
    });
  })();
</script>

@push('scripts')
  @vite('resources/js/faculty/syllabus-ilo.js')
@endpush
<!-- Local ILO Save button removed — saving is handled by the main syllabus Save button -->

{{-- ░░░ START: Load Predefined ILOs Modal ░░░ --}}
<div class="modal fade sv-ilo-modal" id="loadPredefinedIlosModal" tabindex="-1" aria-labelledby="loadPredefinedIlosModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      @csrf

      {{-- ░░░ START: Local styles (scoped to this modal) ░░░ --}}
      <style>
        /* Brand tokens */
        #loadPredefinedIlosModal {
          --sv-bg:   #FAFAFA;   /* light bg */
          --sv-bdr:  #E3E3E3;   /* borders */
          --sv-acct: #EE6F57;   /* accent/focus */
          --sv-danger:#CB3737;  /* primary action (danger style) */
        }
        #loadPredefinedIlosModal .modal-header {
          padding: .85rem 1rem;
          border-bottom: 1px solid var(--sv-bdr);
          background: #fff;
        }
        #loadPredefinedIlosModal .modal-title {
          font-weight: 600;
          font-size: 1rem;
          display: inline-flex;
          align-items: center;
          gap: .5rem;
        }
        #loadPredefinedIlosModal .modal-title i,
        #loadPredefinedIlosModal .modal-title svg {
          width: 1.05rem;
          height: 1.05rem;
          stroke: var(--sv-text-muted, #777777);
        }
        #loadPredefinedIlosModal .modal-content {
          border-radius: 16px;
          border: 1px solid var(--sv-bdr);
          background: #fff;
          box-shadow: 0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06);
          overflow: hidden;
        }
        #loadPredefinedIlosModal .alert {
          border-radius: 12px;
          padding: .75rem 1rem;
          font-size: .875rem;
        }
        #loadPredefinedIlosModal .alert-warning {
          background: linear-gradient(135deg, rgba(255, 243, 205, 0.88), rgba(255, 255, 255, 0.46));
          border: 1px solid rgba(255, 193, 7, 0.3);
          color: #856404;
        }
        #loadPredefinedIlosModal .btn-danger {
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
        #loadPredefinedIlosModal .btn-danger:hover,
        #loadPredefinedIlosModal .btn-danger:focus {
          background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(204, 55, 55, 0.12);
          color: #CB3737;
        }
        #loadPredefinedIlosModal .btn-danger:hover i,
        #loadPredefinedIlosModal .btn-danger:hover svg,
        #loadPredefinedIlosModal .btn-danger:focus i,
        #loadPredefinedIlosModal .btn-danger:focus svg {
          stroke: #CB3737;
        }
        #loadPredefinedIlosModal .btn-danger:active {
          background: linear-gradient(135deg, rgba(255, 230, 225, 0.98), rgba(255, 255, 255, 0.62));
          box-shadow: 0 1px 8px rgba(204, 55, 55, 0.16);
        }
        #loadPredefinedIlosModal .btn-danger:active i,
        #loadPredefinedIlosModal .btn-danger:active svg {
          stroke: #CB3737;
        }
        #loadPredefinedIlosModal .btn-danger:disabled {
          opacity: 0.6;
          cursor: not-allowed;
        }
        /* Cancel button styling */
        #loadPredefinedIlosModal .btn-light {
          background: var(--sv-card-bg, #fff);
          border: none;
          color: #6c757d;
          transition: all 0.2s ease-in-out;
          box-shadow: none;
          display: inline-flex;
          align-items: center;
          gap: 0.5rem;
          padding: 0.5rem 1rem;
          border-radius: 0.375rem;
        }
        #loadPredefinedIlosModal .btn-light:hover,
        #loadPredefinedIlosModal .btn-light:focus {
          background: linear-gradient(135deg, rgba(220, 220, 220, 0.88), rgba(240, 240, 240, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(108, 117, 125, 0.12);
          color: #495057;
        }
        #loadPredefinedIlosModal .btn-light:hover i,
        #loadPredefinedIlosModal .btn-light:hover svg,
        #loadPredefinedIlosModal .btn-light:focus i,
        #loadPredefinedIlosModal .btn-light:focus svg {
          stroke: #495057;
        }
        #loadPredefinedIlosModal .btn-light:active {
          background: linear-gradient(135deg, rgba(240, 242, 245, 0.98), rgba(255, 255, 255, 0.62));
          box-shadow: 0 1px 8px rgba(108, 117, 125, 0.16);
        }
        #loadPredefinedIlosModal .btn-light:active i,
        #loadPredefinedIlosModal .btn-light:active svg {
          stroke: #495057;
        }
      </style>
      {{-- ░░░ END: Local styles ░░░ --}}

      {{-- ░░░ START: Header ░░░ --}}
      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2" id="loadPredefinedIlosModalLabel">
          <i data-feather="download"></i>
          <span>Load Predefined ILOs</span>
        </h5>
      </div>
      {{-- ░░░ END: Header ░░░ --}}

      {{-- ░░░ START: Body ░░░ --}}
      <div class="modal-body">
        <div class="alert alert-warning d-flex align-items-start gap-2" role="alert">
          <i data-feather="alert-triangle" style="width: 1.25rem; height: 1.25rem; flex-shrink: 0; margin-top: 0.125rem;"></i>
          <div>
            <strong>Warning:</strong> This will replace all existing ILOs with predefined ILOs from the master data for this course.
          </div>
        </div>
        <p class="mb-0 text-muted small">
          This action cannot be undone. Make sure you want to proceed before confirming.
        </p>
      </div>
      {{-- ░░░ END: Body ░░░ --}}

      {{-- ░░░ START: Footer ░░░ --}}
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
          <i data-feather="x"></i> Cancel
        </button>
        <button type="button" class="btn btn-danger" id="confirmLoadPredefinedIlos">
          <i data-feather="download"></i> Load ILOs
        </button>
      </div>
      {{-- ░░░ END: Footer ░░░ --}}
    </div>
  </div>
</div>
{{-- ░░░ END: Load Predefined ILOs Modal ░░░ --}}
  