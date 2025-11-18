// Frontend helpers for syllabus CDIO list: sortable, renumbering, keyboard shortcuts
import Sortable from 'sortablejs';
import { initAutosize, markDirty, updateUnsavedCount } from './syllabus';

document.addEventListener('DOMContentLoaded', () => {
  const list = document.getElementById('syllabus-cdio-sortable');
  if (!list) return;

  function updateVisibleCodes() {
    const rows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="cdios[]"]') || r.querySelector('.cdio-badge'));
    rows.forEach((row, index) => {
      const newCode = `CDIO${index + 1}`;
      const badge = row.querySelector('.cdio-badge'); if (badge) badge.textContent = newCode;
      const codeInput = row.querySelector('input[name="code[]"]'); if (codeInput) codeInput.value = newCode;
    });

    try { if (window.markAsUnsaved) window.markAsUnsaved('cdios'); } catch (e) {}
    try { updateUnsavedCount(); } catch (e) {}
  }

  Sortable.create(list, {
    handle: '.drag-handle',
    animation: 150,
    fallbackOnBody: true,
    draggable: 'tr',
    swapThreshold: 0.65,
    onEnd() { updateVisibleCodes(); try { markDirty('unsaved-cdios'); } catch (e) {} }
  });

  // Observe DOM changes and renumber after microtask
  try {
    if (window.MutationObserver) {
      const mo = new MutationObserver((mutations) => {
        let shouldUpdate = false;
        for (const m of mutations) {
          if (m.type === 'childList' && (m.addedNodes.length || m.removedNodes.length)) { shouldUpdate = true; break; }
        }
        if (shouldUpdate) Promise.resolve().then(() => { try { initAutosize(); } catch (e) {} updateVisibleCodes(); try { updateUnsavedCount(); } catch (e) {} });
      });
      mo.observe(list, { childList: true, subtree: false });
    }
  } catch (e) { /* noop */ }

  // Keyboard handlers: match ILO/SO/IGA — Ctrl/Cmd+Backspace removes empty; no Ctrl+Enter add
  list.addEventListener('keydown', (e) => {
    const el = e.target; if (!el || el.tagName !== 'TEXTAREA') return;
    if (e.key === 'Backspace' && (e.ctrlKey || e.metaKey)) {
      const val = el.value || ''; const selStart = (typeof el.selectionStart === 'number') ? el.selectionStart : 0;
      if (val.trim() === '' && selStart === 0) {
        e.preventDefault(); e.stopPropagation();
        const row = el.closest('tr');
        const id = row.getAttribute('data-id');
        if (!id || id.startsWith('new-')) {
          const prev = row.previousElementSibling;
          try { row.remove(); } catch (e) { row.remove(); }
          
          // Check if any rows remain, if not show placeholder
          const remainingRows = list.querySelectorAll('tr:not(#cdio-placeholder)');
          if (remainingRows.length === 0) {
            const placeholder = document.createElement('tr');
            placeholder.id = 'cdio-placeholder';
            placeholder.innerHTML = `
              <td colspan="2" class="text-center text-muted py-4">
                <p class="mb-2">No CDIOs added yet.</p>
                <p class="mb-0"><small>Click the <strong>+</strong> button above to add a CDIO.</small></p>
              </td>
            `;
            list.appendChild(placeholder);
          } else {
            updateVisibleCodes();
          }
          
          if (prev) { const prevTa = prev.querySelector('textarea.autosize'); if (prevTa) { prevTa.focus(); prevTa.selectionStart = prevTa.value.length; } }
          return;
        }
        
        fetch((window.syllabusBasePath || '/faculty/syllabi') + `/cdios/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
        .then(res => res.json()).then(data => { 
          if (window.showAlertOverlay) {
            window.showAlertOverlay('success', data.message || 'CDIO deleted.');
          }
          
          // Remove the row from DOM
          const prev = row.previousElementSibling;
          row.remove();
          
          // Check if any rows remain, if not show placeholder
          const remainingRows = list.querySelectorAll('tr:not(#cdio-placeholder)');
          if (remainingRows.length === 0) {
            const placeholder = document.createElement('tr');
            placeholder.id = 'cdio-placeholder';
            placeholder.innerHTML = `
              <td colspan="2" class="text-center text-muted py-4">
                <p class="mb-2">No CDIOs added yet.</p>
                <p class="mb-0"><small>Click the <strong>+</strong> button above to add a CDIO.</small></p>
              </td>
            `;
            list.appendChild(placeholder);
          } else {
            updateVisibleCodes();
          }
          
          if (prev) { const prevTa = prev.querySelector('textarea.autosize'); if (prevTa) { prevTa.focus(); prevTa.selectionStart = prevTa.value.length; } }
        }).catch(err => { 
          console.error(err); 
          if (window.showAlertOverlay) {
            window.showAlertOverlay('error', 'Failed to delete CDIO.');
          }
        });
      }
    }
  });

  // Click delete button
  list.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-delete-cdio'); if (!btn) return;
    const row = btn.closest('tr');
    const id = row.getAttribute('data-id');
    
    if (!id || id.startsWith('new-')) {
      try { row.remove(); } catch (e) { row.remove(); }
      
      // Check if any rows remain, if not show placeholder
      const remainingRows = list.querySelectorAll('tr:not(#cdio-placeholder)');
      if (remainingRows.length === 0) {
        const placeholder = document.createElement('tr');
        placeholder.id = 'cdio-placeholder';
        placeholder.innerHTML = `
          <td colspan="2" class="text-center text-muted py-4">
            <p class="mb-2">No CDIOs added yet.</p>
            <p class="mb-0"><small>Click the <strong>+</strong> button above to add a CDIO.</small></p>
          </td>
        `;
        list.appendChild(placeholder);
      } else {
        updateVisibleCodes();
      }
      return;
    }
    
    fetch((window.syllabusBasePath || '/faculty/syllabi') + `/cdios/${id}`, { method: 'DELETE', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
    .then(res => res.json()).then(data => { 
      if (window.showAlertOverlay) {
        window.showAlertOverlay('success', data.message || 'CDIO deleted.');
      }
      
      // Remove the row from DOM
      row.remove();
      
      // Check if any rows remain, if not show placeholder
      const remainingRows = list.querySelectorAll('tr:not(#cdio-placeholder)');
      if (remainingRows.length === 0) {
        const placeholder = document.createElement('tr');
        placeholder.id = 'cdio-placeholder';
        placeholder.innerHTML = `
          <td colspan="2" class="text-center text-muted py-4">
            <p class="mb-2">No CDIOs added yet.</p>
            <p class="mb-0"><small>Click the <strong>+</strong> button above to add a CDIO.</small></p>
          </td>
        `;
        list.appendChild(placeholder);
      } else {
        updateVisibleCodes();
      }
    }).catch(err => { 
      console.error(err); 
      if (window.showAlertOverlay) {
        window.showAlertOverlay('error', 'Failed to delete CDIO.');
      }
    });
  });

  // Inline save of a single row (send title + description)
  list.addEventListener('blur', (e) => {
    const ta = e.target; if (!ta || ta.tagName !== 'TEXTAREA') return;
    const row = ta.closest('tr'); if (!row) return;
    const id = row.getAttribute('data-id');
    if (!id || id.startsWith('new-')) return; // not persisted
    // send inline update to server
  fetch((window.syllabusBasePath || '/faculty/syllabi') + `/${list.dataset.syllabusId}/cdios/${id}`, {
      method: 'PUT', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
      body: JSON.stringify({ title: row.querySelector('textarea[name="cdio_titles[]"]').value || '', description: row.querySelector('textarea[name="cdios[]"]').value || '' })
    }).then(r => r.json()).then(d => { if (d && d.message) console.debug('CDIO inline save:', d.message); else console.debug('CDIO inline save response', d); })
    .catch(err => { console.error('CDIO inline save error', err); });
  }, true);

  function createNewRow() {
    const timestamp = Date.now();
    const newRow = document.createElement('tr');
    newRow.setAttribute('data-id', `new-${timestamp}`);
    newRow.innerHTML = `
      <td class="text-center align-middle"><div class="cdio-badge"></div></td>
      <td>
        <div class="d-flex align-items-center gap-2">
          <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab; display:flex; align-items:center;"><i class="bi bi-grip-vertical"></i></span>
          <div class="flex-grow-1 w-100">
            <textarea name="cdio_titles[]" class="cis-textarea cis-field autosize" placeholder="Title" rows="1" style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;font-weight:700;" required></textarea>
            <textarea name="cdios[]" class="cis-textarea cis-field autosize" placeholder="Description" rows="1" style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;" required></textarea>
          </div>
          <input type="hidden" name="code[]" value="">
          <button type="button" class="btn btn-sm btn-outline-danger btn-delete-cdio ms-2" title="Delete CDIO"><i class="bi bi-trash"></i></button>
        </div>
      </td>
    `;
    return newRow;
  }

  function addRow() {
    // Remove placeholder if it exists
    const placeholder = document.getElementById('cdio-placeholder');
    if (placeholder) placeholder.remove();
    
    const newRow = createNewRow();
    list.appendChild(newRow);
    try { initAutosize(); } catch (e) {}
    updateVisibleCodes();
    const ta = newRow.querySelector('textarea.autosize');
    if (ta) ta.focus();
    return newRow;
  }

  // Reorder API helper: send positions array [{id, position},...]
  window.saveCdioOrder = async function() {
    const rows = Array.from(list.querySelectorAll('tr')).filter(r => r.getAttribute('data-id'));
    const positions = rows.map((r, idx) => ({ id: r.getAttribute('data-id'), position: idx + 1 }));
    const syllabusId = list.dataset.syllabusId;
    if (!syllabusId) return { ok: false };
  const res = await fetch((window.syllabusBasePath || '/faculty/syllabi') + `/${syllabusId}/cdios/reorder`, {
      method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
      body: JSON.stringify({ positions })
    });
    return res.json();
  };

  // Wire add row button in header
  const addHeaderBtn = document.getElementById('cdio-add-header');
  if (addHeaderBtn) {
    addHeaderBtn.addEventListener('click', () => addRow());
  }

  // Load Predefined CDIOs functionality
  const loadPredefinedBtn = document.getElementById('cdio-load-predefined');
  const loadPredefinedModal = document.getElementById('loadPredefinedCdiosModal');
  const confirmLoadBtn = document.getElementById('confirmLoadPredefinedCdios');
  const cdioSelectionList = document.getElementById('cdioSelectionList');
  const selectAllCheckbox = document.getElementById('selectAllCdios');
  let availableCdios = [];

  if (loadPredefinedBtn && loadPredefinedModal) {
    loadPredefinedBtn.addEventListener('click', async function() {
      const modal = new bootstrap.Modal(loadPredefinedModal);
      modal.show();

      // Get syllabus data to filter by department
      const syllabusId = list?.dataset.syllabusId;
      const departmentId = document.querySelector('[data-department-id]')?.dataset.departmentId;
      
      // Load available CDIOs from master data
      try {
        const params = new URLSearchParams();
        if (departmentId) params.append('department', departmentId);
        
        const response = await fetch(`/faculty/master-data/cdio/filter?${params.toString()}`, {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        if (!response.ok) throw new Error('Failed to load CDIOs');

        const data = await response.json();
        availableCdios = data.cdios || [];

        if (availableCdios.length === 0) {
          cdioSelectionList.innerHTML = '<div class="text-center text-muted py-3"><p class="mb-0">No predefined CDIOs found.</p></div>';
          confirmLoadBtn.disabled = true;
          return;
        }

        // Render checkboxes
        cdioSelectionList.innerHTML = availableCdios.map((cdio, index) => `
          <div class="form-check mb-2">
            <input class="form-check-input cdio-checkbox" type="checkbox" value="${cdio.id}" id="cdio_${cdio.id}" checked>
            <label class="form-check-label" for="cdio_${cdio.id}">
              <strong>CDIO${index + 1}:</strong> ${cdio.title || 'Untitled'}
              ${cdio.description ? `<br><small class="text-muted">${cdio.description.substring(0, 80)}${cdio.description.length > 80 ? '...' : ''}</small>` : ''}
            </label>
          </div>
        `).join('');

        confirmLoadBtn.disabled = false;

        // Re-initialize feather icons
        if (typeof feather !== 'undefined') feather.replace();

      } catch (error) {
        console.error('Error loading CDIOs:', error);
        cdioSelectionList.innerHTML = '<div class="text-center text-danger py-3"><p class="mb-0">Failed to load CDIOs</p></div>';
        confirmLoadBtn.disabled = true;
      }
    });
  }

  // Select All functionality
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function() {
      const checkboxes = document.querySelectorAll('.cdio-checkbox');
      checkboxes.forEach(cb => cb.checked = this.checked);
    });
  }

  // Update Select All when individual checkboxes change
  if (cdioSelectionList) {
    cdioSelectionList.addEventListener('change', function(e) {
      if (e.target.classList.contains('cdio-checkbox')) {
        const checkboxes = document.querySelectorAll('.cdio-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        if (selectAllCheckbox) selectAllCheckbox.checked = allChecked;
      }
    });
  }

  // Confirm button in modal
  if (confirmLoadBtn && list) {
    confirmLoadBtn.addEventListener('click', async function() {
      const syllabusId = list.dataset.syllabusId;
      if (!syllabusId) {
        if (window.showAlertOverlay) {
          window.showAlertOverlay('error', 'Syllabus ID not found');
        } else {
          alert('Syllabus ID not found');
        }
        return;
      }

      // Get selected CDIO IDs
      const selectedCheckboxes = document.querySelectorAll('.cdio-checkbox:checked');
      const selectedIds = Array.from(selectedCheckboxes).map(cb => parseInt(cb.value));

      if (selectedIds.length === 0) {
        if (window.showAlertOverlay) {
          window.showAlertOverlay('error', 'Please select at least one CDIO to load.');
        } else {
          alert('Please select at least one CDIO to load.');
        }
        return;
      }

      try {
        confirmLoadBtn.disabled = true;
        const response = await fetch(`/faculty/syllabi/${syllabusId}/load-predefined-cdios`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
          },
          body: JSON.stringify({ cdio_ids: selectedIds })
        });

        if (!response.ok) {
          const errorData = await response.json().catch(() => ({}));
          throw new Error(errorData.message || 'Failed to load predefined CDIOs');
        }

        const data = await response.json();

        if (!data.cdios || data.cdios.length === 0) {
          if (window.showAlertOverlay) {
            window.showAlertOverlay('error', 'No predefined CDIOs found.');
          } else {
            alert('No predefined CDIOs found.');
          }
          confirmLoadBtn.disabled = false;
          return;
        }

        // Remove placeholder if it exists
        const placeholder = document.getElementById('cdio-placeholder');
        if (placeholder) placeholder.remove();

        // Clear existing rows
        while (list.firstChild) {
          list.removeChild(list.firstChild);
        }

        // Add new rows from predefined CDIOs
        data.cdios.forEach((cdio, index) => {
          const code = `CDIO${index + 1}`;
          const newRow = document.createElement('tr');
          newRow.setAttribute('data-id', cdio.id);
          newRow.innerHTML = `
            <td class="text-center align-middle">
              <div class="cdio-badge">${code}</div>
            </td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;"><i class="bi bi-grip-vertical"></i></span>
                <div class="flex-grow-1 w-100">
                  <textarea
                    name="cdio_titles[]"
                    class="cis-textarea cis-field autosize"
                    data-original="${cdio.title || ''}"
                    placeholder="-"
                    rows="1"
                    style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;font-weight:700;"
                    required>${cdio.title || ''}</textarea>
                  <textarea
                    name="cdios[]"
                    class="cis-textarea cis-field autosize"
                    data-original="${cdio.description || ''}"
                    placeholder="Description"
                    rows="1"
                    style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;"
                    required>${cdio.description || ''}</textarea>
                </div>
                <input type="hidden" name="code[]" value="${code}">
                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-cdio ms-2" title="Delete CDIO"><i class="bi bi-trash"></i></button>
              </div>
            </td>
          `;
          list.appendChild(newRow);
        });

        // Initialize autosize on new textareas
        try { initAutosize(); } catch (e) {}
        
        // Update codes and unsaved tracking
        updateVisibleCodes();
        bindGlobalUnsaved();

        // Re-initialize feather icons if available
        if (typeof feather !== 'undefined') feather.replace();

        // Show success message
        if (window.showAlertOverlay) {
          window.showAlertOverlay('success', data.message || 'CDIOs loaded successfully');
        }

        // Close modal
        const modalInstance = bootstrap.Modal.getInstance(loadPredefinedModal);
        if (modalInstance) modalInstance.hide();

        confirmLoadBtn.disabled = false;

      } catch (error) {
        console.error('Error loading predefined CDIOs:', error);
        if (window.showAlertOverlay) {
          window.showAlertOverlay('error', error.message || 'Failed to load predefined CDIOs');
        } else {
          alert(error.message || 'Failed to load predefined CDIOs');
        }
        confirmLoadBtn.disabled = false;
      }
    });
  }

  // initial
  updateVisibleCodes();
  try { initAutosize(); } catch (e) {}

  function bindGlobalUnsaved() {
    function checkAnyChanged() {
      const anyChanged = Array.from(list.querySelectorAll('textarea.autosize')).some(t => (t.value || '') !== (t.getAttribute('data-original') || ''));
      const top = document.getElementById('unsaved-cdios'); if (top) top.classList.toggle('d-none', !anyChanged);
      if (anyChanged) { try { markDirty('unsaved-cdios'); } catch (e) {} }
      updateUnsavedCount();
    }
    list.querySelectorAll('textarea.autosize').forEach((ta) => { ta.addEventListener('input', checkAnyChanged); ta.addEventListener('change', checkAnyChanged); });
  }
  bindGlobalUnsaved();

  // replace original saveCdio to build from DOM nodes (keeps API)
  window.saveCdio = async function() {
    const form = document.getElementById('cdioForm');
    if (! form) return { ok: true };
    const tbody = document.querySelector('#syllabus-cdio-sortable');
    const items = [];
    Array.from(tbody.querySelectorAll('tr')).forEach((tr, idx) => {
      const title = tr.querySelector('textarea[name="cdio_titles[]"]')?.value || '';
      const desc = tr.querySelector('textarea[name="cdios[]"]')?.value || '';
      const code = tr.querySelector('input[name="code[]"]')?.value || (`CDIO${idx+1}`);
      items.push({ id: tr.getAttribute('data-id') || null, code, title, description: desc, position: idx + 1 });
    });
    try {
      console.debug('saveCdio payload', items, 'form.action=', form.action);
      const res = await fetch(form.action, {
        method: 'PUT',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
        body: JSON.stringify({ cdios: items }),
      });
      const data = await res.json();
      if (data && data.ok) {
        // mark inputs as saved
        Array.from(tbody.querySelectorAll('textarea[name="cdios[]"]')).forEach((ta, idx) => {
          ta.setAttribute('data-original', ta.value || '');
        });
        // hide unsaved pill
        const pill = document.getElementById('unsaved-cdios'); if (pill) pill.classList.add('d-none');
        try { updateUnsavedCount(); } catch (e) {}
      }
      return data;
    } catch (err) {
      console.error('saveCdio error', err);
      return { ok: false, message: err && err.message ? err.message : 'Network error' };
    }
  };
});
