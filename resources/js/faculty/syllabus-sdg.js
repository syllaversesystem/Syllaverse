/**
 * ===========================================================================================
 * SDG Module – Dynamic Row Management, Inline Saving, and Drag-and-Drop Reordering
 * ===========================================================================================
 * Purpose:
 *   Manages Sustainable Development Goals (SDG) in the syllabus editing interface.
 *   Provides functionality for adding, deleting, inline updating, and reordering SDG entries.
 *
 * Features:
 *   - Add new SDG rows with auto-incrementing badges (SDG1, SDG2, etc.)
 *   - Delete rows with keyboard shortcut (Ctrl+Backspace) or click
 *   - Inline save on blur for title and description fields
 *   - Drag-and-drop reordering with Sortable.js
 *   - Automatic placeholder management (show when empty, hide when populated)
 *   - AJAX operations for all CRUD actions without page reload
 *
 * Dependencies:
 *   - Sortable.js (for drag-and-drop)
 *   - autosize (for textarea auto-resize)
 *   - Bootstrap 5 (for styling)
 *
 * API Endpoints:
 *   POST   /faculty/syllabi/{id}/sdgs           - Create new SDG
 *   PUT    /faculty/syllabi/{id}/sdgs/{sdgId}  - Update existing SDG
 *   DELETE /faculty/syllabi/{id}/sdgs/{sdgId}  - Delete SDG
 *   POST   /faculty/syllabi/{id}/sdgs/reorder  - Reorder SDGs
 * ===========================================================================================
 */

import Sortable from 'sortablejs';

document.addEventListener('DOMContentLoaded', function () {
  const sdgTbody = document.getElementById('syllabus-sdg-sortable');
  const addSdgBtn = document.getElementById('sdg-add-header');

  if (!sdgTbody) {
    return;
  }

  const syllabusId = sdgTbody.getAttribute('data-syllabus-id');

  /**
   * ===========================================================================================
   * updateVisibleCodes()
   * ===========================================================================================
   * Renumbers all SDG badges sequentially (SDG1, SDG2, SDG3, etc.)
   * Called after adding, deleting, or reordering rows
   */
  function updateVisibleCodes() {
    const rows = sdgTbody.querySelectorAll('tr[data-id]');
    rows.forEach((row, index) => {
      const badge = row.querySelector('.sdg-badge');
      if (badge) {
        badge.textContent = `SDG${index + 1}`;
      }
    });
  }

  /**
   * ===========================================================================================
   * Sortable.js – Drag-and-Drop Reordering
   * ===========================================================================================
   * Enables drag-and-drop reordering of SDG rows
   * Updates badge numbers after reordering
   * Saves new order to backend
   */
  if (sdgTbody) {
    Sortable.create(sdgTbody, {
      animation: 150,
      handle: '.drag-handle',
      ghostClass: 'sortable-ghost',
      onEnd: function () {
        updateVisibleCodes();
        saveSdgOrder();
      }
    });
  }

  /**
   * ===========================================================================================
   * saveSdgOrder()
   * ===========================================================================================
   * Updates visible badge numbers after reordering
   * Actual save happens via main save button
   */
  function saveSdgOrder() {
    // Just update visual badges, no backend save
    updateVisibleCodes();
  }

  /**
   * ===========================================================================================
   * Event Delegation – Delete SDG
   * ===========================================================================================
   * Handles delete button clicks and keyboard shortcut (Ctrl+Backspace)
   * - If row is blank (empty title and description): just remove from DOM
   * - If row has temp ID: just remove from DOM
   * - If row has backend ID: delete via AJAX
   */
  sdgTbody.addEventListener('click', async function (e) {
    const deleteBtn = e.target.closest('.btn-delete-sdg');
    if (!deleteBtn) return;

    const row = deleteBtn.closest('tr');
    const sdgId = row.getAttribute('data-id');

    if (!sdgId) return;

    // Get title and description values
    // Check if this is a frontend-only row (temp ID starts with 'temp-')
    const isFrontendOnly = sdgId.startsWith('temp-');

    if (isFrontendOnly) {
      // Frontend only: just remove from DOM, no backend call
      row.remove();
      updateVisibleCodes();

      // Update all code inputs to match badges
      const rows = sdgTbody.querySelectorAll('tr[data-id]');
      rows.forEach((row, index) => {
        const codeInput = row.querySelector('input[name="code[]"]');
        if (codeInput) {
          codeInput.value = `SDG${index + 1}`;
        }
      });

      // Show placeholder if no rows left
      const remainingRows = sdgTbody.querySelectorAll('tr[data-id]');
      if (remainingRows.length === 0) {
        const placeholder = document.createElement('tr');
        placeholder.id = 'sdg-placeholder';
        placeholder.innerHTML = `
          <td colspan="2" class="text-center text-muted py-4">
            <p class="mb-2">No SDGs added yet.</p>
            <p class="mb-0"><small>Click the <strong>+</strong> button above to add an SDG.</small></p>
          </td>
        `;
        sdgTbody.appendChild(placeholder);
      }
    } else {
      // Backend row: delete via AJAX (regardless of blank or not)
      if (!syllabusId) return;

      try {
        const response = await fetch(`/faculty/syllabi/${syllabusId}/sdgs/entry/${sdgId}`, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });

        if (!response.ok) {
          throw new Error('Failed to delete SDG');
        }

        const data = await response.json();
        
        row.remove();
        updateVisibleCodes();

        // Update all code inputs to match badges
        const rows = sdgTbody.querySelectorAll('tr[data-id]');
        rows.forEach((row, index) => {
          const codeInput = row.querySelector('input[name="code[]"]');
          if (codeInput) {
            codeInput.value = `SDG${index + 1}`;
          }
        });

        // Show placeholder if no rows left
        const remainingRows = sdgTbody.querySelectorAll('tr[data-id]');
        if (remainingRows.length === 0) {
          const placeholder = document.createElement('tr');
          placeholder.id = 'sdg-placeholder';
          placeholder.innerHTML = `
            <td colspan="2" class="text-center text-muted py-4">
              <p class="mb-2">No SDGs added yet.</p>
              <p class="mb-0"><small>Click the <strong>+</strong> button above to add an SDG.</small></p>
            </td>
          `;
          sdgTbody.appendChild(placeholder);
        }

        if (window.showAlertOverlay) {
          window.showAlertOverlay('success', 'SDG deleted successfully');
        }
      } catch (error) {
        console.error('Error deleting SDG:', error);
        if (window.showAlertOverlay) {
          window.showAlertOverlay('error', error.message || 'Failed to delete SDG');
        }
      }
    }
  });

  /**
   * ===========================================================================================
   * Keyboard Shortcut – Delete SDG (Ctrl+Backspace)
   * ===========================================================================================
   */
  sdgTbody.addEventListener('keydown', async function (e) {
    if (e.ctrlKey && e.key === 'Backspace') {
      e.preventDefault();
      const row = e.target.closest('tr[data-id]');
      if (!row) return;

      const deleteBtn = row.querySelector('.btn-delete-sdg');
      if (deleteBtn) {
        deleteBtn.click();
      }
    }
  });

  // Inline save removed - all saves happen via main save button

  /**
   * ===========================================================================================
   * createNewRow()
   * ===========================================================================================
   * Generates HTML for a new SDG row with temporary ID
   * Returns row element ready to be appended to tbody
   */
  function createNewRow() {
    const tempId = `temp-${Date.now()}`;
    const nextNumber = sdgTbody.querySelectorAll('tr[data-id]').length + 1;

    const tr = document.createElement('tr');
    tr.setAttribute('data-id', tempId);

    tr.innerHTML = `
      <td class="text-center align-middle">
        <div class="sdg-badge fw-semibold">SDG${nextNumber}</div>
      </td>
      <td>
        <div class="d-flex align-items-center gap-2">
          <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;">
            <i class="bi bi-grip-vertical"></i>
          </span>
          <div class="flex-grow-1 w-100">
            <textarea
              name="sdg_titles[]"
              class="cis-textarea cis-field autosize"
              placeholder="Title"
              rows="1"
              style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;font-weight:700;"></textarea>
            <textarea
              name="sdgs[]"
              class="cis-textarea cis-field autosize"
              placeholder="Description"
              rows="1"
              style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;"></textarea>
            <input type="hidden" name="code[]" value="SDG${nextNumber}">
          </div>
          <button type="button" class="btn btn-sm btn-outline-danger btn-delete-sdg ms-2" title="Delete SDG"><i class="bi bi-trash"></i></button>
        </div>
      </td>
    `;

    return tr;
  }

  /**
   * ===========================================================================================
   * addRow()
   * ===========================================================================================
   * Adds a new SDG row to the table (frontend only)
   * Removes placeholder if present
   * Initializes autosize for textarea
   * Does not save to backend - saving happens via main save button
   */
  function addRow() {
    // Remove placeholder if exists
    const placeholder = sdgTbody.querySelector('#sdg-placeholder');
    if (placeholder) {
      placeholder.remove();
    }

    // Create and append new row
    const newRow = createNewRow();
    sdgTbody.appendChild(newRow);

    // Initialize autosize for textareas
    const textareas = newRow.querySelectorAll('textarea.autosize');
    if (window.autosize) {
      textareas.forEach(textarea => {
        window.autosize(textarea);
      });
    }

    // Focus on title textarea
    const titleTextarea = newRow.querySelector('textarea[name="sdg_titles[]"]');
    if (titleTextarea) {
      titleTextarea.focus();
    }

    updateVisibleCodes();

    // Update all code inputs to match badges
    const rows = sdgTbody.querySelectorAll('tr[data-id]');
    rows.forEach((row, index) => {
      const codeInput = row.querySelector('input[name="code[]"]');
      if (codeInput) {
        codeInput.value = `SDG${index + 1}`;
      }
    });
  }

  /**
   * ===========================================================================================
   * saveSdg()
   * ===========================================================================================
   * Saves all SDG rows to the backend
   * Collects data from all rows and sends bulk update
   */
  async function saveSdg() {
    if (!syllabusId) {
      if (window.showAlertOverlay) {
        window.showAlertOverlay('error', 'Syllabus ID not found');
      }
      return;
    }

    const rows = sdgTbody.querySelectorAll('tr[data-id]');
    const sdgs = [];

    rows.forEach((row, index) => {
      const titleTextarea = row.querySelector('textarea[name="sdg_titles[]"]');
      const descTextarea = row.querySelector('textarea[name="sdgs[]"]');
      const codeInput = row.querySelector('input[name="code[]"]');
      
      // Don't send IDs - let backend recreate all from scratch
      sdgs.push({
        title: titleTextarea ? titleTextarea.value : '',
        description: descTextarea ? descTextarea.value : '',
        code: codeInput ? codeInput.value : `SDG${index + 1}`,
        position: index + 1
      });
    });

    if (sdgs.length === 0) {
      if (window.showAlertOverlay) {
        window.showAlertOverlay('info', 'No SDGs to save');
      }
      return;
    }

    try {
      const response = await fetch(`/faculty/syllabi/${syllabusId}/sdgs`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ sdgs })
      });
      
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to save SDGs');
      }

      const data = await response.json();

      if (window.showAlertOverlay) {
        window.showAlertOverlay('success', 'SDGs saved successfully');
      }

      // Update DOM with fresh data from backend
      if (data.sdgs && Array.isArray(data.sdgs)) {
        // Clear existing rows
        sdgTbody.innerHTML = '';
        
        if (data.sdgs.length === 0) {
          // Show placeholder if no SDGs
          const placeholder = document.createElement('tr');
          placeholder.id = 'sdg-placeholder';
          placeholder.innerHTML = `
            <td colspan="2" class="text-center text-muted py-4">
              <p class="mb-2">No SDGs added yet.</p>
              <p class="mb-0"><small>Click the <strong>+</strong> button above to add an SDG.</small></p>
            </td>
          `;
          sdgTbody.appendChild(placeholder);
        } else {
          // Rebuild rows with fresh backend data
          data.sdgs.forEach((sdg, index) => {
            const tr = document.createElement('tr');
            tr.setAttribute('data-id', sdg.id);
            
            tr.innerHTML = `
              <td class="text-center align-middle">
                <div class="sdg-badge fw-semibold">SDG${index + 1}</div>
              </td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;">
                    <i class="bi bi-grip-vertical"></i>
                  </span>
                  <div class="flex-grow-1 w-100">
                    <textarea
                      name="sdg_titles[]"
                      class="cis-textarea cis-field autosize"
                      placeholder="Title"
                      rows="1"
                      style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;font-weight:700;">${sdg.title || ''}</textarea>
                    <textarea
                      name="sdgs[]"
                      class="cis-textarea cis-field autosize"
                      placeholder="Description"
                      rows="1"
                      style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">${sdg.description || ''}</textarea>
                    <input type="hidden" name="code[]" value="${sdg.code}">
                  </div>
                  <button type="button" class="btn btn-sm btn-outline-danger btn-delete-sdg ms-2" title="Delete SDG"><i class="bi bi-trash"></i></button>
                </div>
              </td>
            `;
            
            sdgTbody.appendChild(tr);
          });
          
          // Reinitialize autosize for new textareas
          if (typeof autosize !== 'undefined') {
            const textareas = sdgTbody.querySelectorAll('textarea.autosize');
            textareas.forEach(textarea => autosize(textarea));
          }
        }
      }
      
    } catch (error) {
      console.error('Error saving SDGs:', error);
      if (window.showAlertOverlay) {
        window.showAlertOverlay('error', error.message || 'Failed to save SDGs');
      }
    }
  }

  /**
   * ===========================================================================================
   * Add Button Event Listener
   * ===========================================================================================
   */
  if (addSdgBtn) {
    addSdgBtn.addEventListener('click', addRow);
  }

  /**
   * ===========================================================================================
   * Load Predefined SDGs functionality
   * ===========================================================================================
   */
  const loadPredefinedBtn = document.getElementById('sdg-load-predefined');
  const loadPredefinedModal = document.getElementById('loadPredefinedSdgsModal');
  const confirmLoadBtn = document.getElementById('confirmLoadPredefinedSdgs');
  const sdgSelectionList = document.getElementById('sdgSelectionList');
  const selectAllCheckbox = document.getElementById('selectAllSdgs');
  let availableSdgs = [];

  if (loadPredefinedBtn && loadPredefinedModal) {
    loadPredefinedBtn.addEventListener('click', async function() {
      const modal = new bootstrap.Modal(loadPredefinedModal);
      modal.show();

      try {
        const response = await fetch('/faculty/master-data/sdg/filter', {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        if (!response.ok) throw new Error('Failed to load SDGs');

        const data = await response.json();
        availableSdgs = data.sdgs || [];

        if (availableSdgs.length === 0) {
          sdgSelectionList.innerHTML = '<div class="text-center text-muted py-3"><p class="mb-0">No predefined SDGs found.</p></div>';
          confirmLoadBtn.disabled = true;
          return;
        }

        // Render checkboxes
        sdgSelectionList.innerHTML = availableSdgs.map((sdg, index) => `
          <div class="form-check mb-2">
            <input class="form-check-input sdg-checkbox" type="checkbox" value="${sdg.id}" id="sdg_${sdg.id}" checked>
            <label class="form-check-label" for="sdg_${sdg.id}">
              <strong>SDG${index + 1}:</strong> ${sdg.title || 'Untitled'}
              ${sdg.description ? `<br><small class="text-muted">${sdg.description.substring(0, 80)}${sdg.description.length > 80 ? '...' : ''}</small>` : ''}
            </label>
          </div>
        `).join('');

        confirmLoadBtn.disabled = false;

        // Re-initialize feather icons
        if (typeof feather !== 'undefined') feather.replace();

      } catch (error) {
        console.error('Error loading SDGs:', error);
        sdgSelectionList.innerHTML = '<div class="text-center text-danger py-3"><p class="mb-0">Failed to load SDGs</p></div>';
        confirmLoadBtn.disabled = true;
      }
    });
  }

  // Select All functionality
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function() {
      const checkboxes = document.querySelectorAll('.sdg-checkbox');
      checkboxes.forEach(cb => cb.checked = this.checked);
    });
  }

  // Update Select All when individual checkboxes change
  if (sdgSelectionList) {
    sdgSelectionList.addEventListener('change', function(e) {
      if (e.target.classList.contains('sdg-checkbox')) {
        const checkboxes = document.querySelectorAll('.sdg-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        if (selectAllCheckbox) selectAllCheckbox.checked = allChecked;
      }
    });
  }

  // Confirm button in modal
  if (confirmLoadBtn) {
    confirmLoadBtn.addEventListener('click', async function() {
      if (!syllabusId) {
        if (window.showAlertOverlay) {
          window.showAlertOverlay('error', 'Syllabus ID not found');
        }
        return;
      }

      // Get selected SDG IDs
      const selectedCheckboxes = document.querySelectorAll('.sdg-checkbox:checked');
      const selectedIds = Array.from(selectedCheckboxes).map(cb => parseInt(cb.value));

      if (selectedIds.length === 0) {
        if (window.showAlertOverlay) {
          window.showAlertOverlay('warning', 'Please select at least one SDG');
        }
        return;
      }

      try {
        const response = await fetch(`/faculty/syllabi/${syllabusId}/sdgs`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({ sdg_ids: selectedIds })
        });

        if (!response.ok) {
          const errorData = await response.json();
          throw new Error(errorData.message || 'Failed to load SDGs');
        }

        const data = await response.json();

        // Close modal
        bootstrap.Modal.getInstance(loadPredefinedModal).hide();

        if (window.showAlertOverlay) {
          window.showAlertOverlay('success', 'SDGs loaded successfully');
        }

        // Update DOM with fresh data
        if (data.sdgs && Array.isArray(data.sdgs)) {
          // Clear existing rows
          sdgTbody.innerHTML = '';
          
          if (data.sdgs.length === 0) {
            // Show placeholder if no SDGs
            const placeholder = document.createElement('tr');
            placeholder.id = 'sdg-placeholder';
            placeholder.innerHTML = `
              <td colspan="2" class="text-center text-muted py-4">
                <p class="mb-2">No SDGs added yet.</p>
                <p class="mb-0"><small>Click the <strong>+</strong> button above to add an SDG or <strong>Load Predefined</strong> to import SDGs.</small></p>
              </td>
            `;
            sdgTbody.appendChild(placeholder);
          } else {
            // Rebuild rows with fresh backend data
            data.sdgs.forEach((sdg, index) => {
              const tr = document.createElement('tr');
              tr.setAttribute('data-id', sdg.id);
              
              tr.innerHTML = `
                <td class="text-center align-middle">
                  <div class="sdg-badge fw-semibold">SDG${index + 1}</div>
                </td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;">
                      <i class="bi bi-grip-vertical"></i>
                    </span>
                    <div class="flex-grow-1 w-100">
                      <textarea
                        name="sdg_titles[]"
                        class="cis-textarea cis-field autosize"
                        placeholder="Title"
                        rows="1"
                        style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;font-weight:700;">${sdg.title || ''}</textarea>
                      <textarea
                        name="sdgs[]"
                        class="cis-textarea cis-field autosize"
                        placeholder="Description"
                        rows="1"
                        style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">${sdg.description || ''}</textarea>
                      <input type="hidden" name="code[]" value="${sdg.code}">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-sdg ms-2" title="Delete SDG"><i class="bi bi-trash"></i></button>
                  </div>
                </td>
              `;
              
              sdgTbody.appendChild(tr);
            });
            
            // Reinitialize autosize for new textareas
            if (typeof autosize !== 'undefined') {
              const textareas = sdgTbody.querySelectorAll('textarea.autosize');
              textareas.forEach(textarea => autosize(textarea));
            }
          }
        }

      } catch (error) {
        console.error('Error loading SDGs:', error);
        if (window.showAlertOverlay) {
          window.showAlertOverlay('error', error.message || 'Failed to load SDGs');
        }
      }
    });
  }

  /**
   * ===========================================================================================
   * Initialize autosize for existing textareas
   * ===========================================================================================
   */
  if (window.autosize) {
    const textareas = sdgTbody.querySelectorAll('textarea.autosize');
    textareas.forEach(textarea => {
      window.autosize(textarea);
    });
  }

  // Expose saveSdg globally for main save button integration
  window.saveSdg = saveSdg;
});
