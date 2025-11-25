// File: resources/js/faculty/syllabus-iga.js
// Description: Handles IGA functionality for syllabus - add, remove, load predefined, autosize, sortable, delete

import Sortable from 'sortablejs';
import { initAutosize as syllabusInitAutosize, markDirty, updateUnsavedCount } from './syllabus';

document.addEventListener('DOMContentLoaded', () => {
  const list = document.getElementById('syllabus-iga-sortable');
  if (!list) return;

  let previousIgaIds = null;

  // Autosize helper functions
  function autosizeEl(el) {
    try {
      el.style.height = 'auto';
      el.style.height = (el.scrollHeight || 0) + 'px';
    } catch (e) {
      // noop
    }
  }

  function bindAutosize(ta) {
    if (!ta) return;
    autosizeEl(ta);
    ta.addEventListener('input', () => autosizeEl(ta));
  }

  // Initialize autosize for existing textareas
  document.querySelectorAll('#syllabus-iga-sortable textarea.autosize').forEach(bindAutosize);

  // Observe for new textareas added dynamically
  if (window.MutationObserver) {
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

  // Update visible codes and track changes
  function updateVisibleCodes() {
    const rows = Array.from(list.querySelectorAll('tr.iga-row'));
    const currentIds = rows.map((r) => r.getAttribute('data-id') || `client-${Math.random().toString(36).slice(2,8)}`);
    rows.forEach((row, index) => {
      const newCode = `IGA${index + 1}`;
      const badge = row.querySelector('.iga-badge'); if (badge) badge.textContent = newCode;
      const codeInput = row.querySelector('input[name="code[]"]'); if (codeInput) codeInput.value = newCode;
    });

    // detect adds/removes/reorders and dispatch events if needed
    try {
      if (Array.isArray(previousIgaIds)) {
        const prev = previousIgaIds;
        const added = currentIds.filter(id => !prev.includes(id));
        const removed = prev.filter(id => !currentIds.includes(id));
        added.forEach((id) => { const idx = currentIds.indexOf(id); document.dispatchEvent(new CustomEvent('iga:changed', { detail: { action: 'add', id, index: idx, count: currentIds.length } })); });
        removed.forEach((id) => { const prevIndex = prev.indexOf(id); document.dispatchEvent(new CustomEvent('iga:changed', { detail: { action: 'remove', id, index: prevIndex, count: currentIds.length } })); });
        if (added.length === 0 && removed.length === 0 && currentIds.join('|') !== prev.join('|')) {
          const mapping = currentIds.map((id, to) => ({ id, from: prev.indexOf(id), to }));
          document.dispatchEvent(new CustomEvent('iga:changed', { detail: { action: 'reorder', mapping, count: currentIds.length } }));
        }
      }
    } catch (e) { /* noop */ }

    previousIgaIds = currentIds.slice();

    // mark unsaved
    try { if (window.markAsUnsaved) window.markAsUnsaved('assessment_tasks'); } catch (e) { }
  }

  // expose the renumbering function
  try { window.updateIgaVisibleCodes = updateVisibleCodes; } catch (e) { /* noop */ }
  
  // Alias for backward compatibility
  const renumberIGAs = updateVisibleCodes;

  // Add IGA button
  const addBtn = document.getElementById('iga-add-header');
  if (addBtn) {
    addBtn.addEventListener('click', () => {
      const tbody = document.getElementById('syllabus-iga-sortable');
      if (!tbody) return;
      
      // Remove placeholder if it exists
      const placeholder = document.getElementById('iga-placeholder');
      if (placeholder) placeholder.remove();
      
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
              <textarea name="iga_titles[]" class="cis-textarea cis-field autosize" placeholder="Title" rows="1" style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;font-weight:700;" required></textarea>
              <textarea name="igas[]" class="cis-textarea cis-field autosize" placeholder="Description" rows="1" style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;" required></textarea>
            </div>
            <input type="hidden" name="code[]" value="${newCode}">
            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-iga ms-2" title="Delete IGA"><i class="bi bi-trash"></i></button>
          </div>
        </td>
      `;
      tbody.appendChild(row);
      row.querySelectorAll('textarea.autosize').forEach(bindAutosize);
      updateVisibleCodes();
    });
  }

  // Enable sortable drag-reorder
  Sortable.create(list, {
    handle: '.drag-handle',
    animation: 150,
    fallbackOnBody: true,
    draggable: 'tr',
    swapThreshold: 0.65,
    onEnd: function(evt) {
      updateVisibleCodes();
      try { markDirty('unsaved-igas'); } catch (e) { }
      try { updateUnsavedCount(); } catch (e) { }
    }
  });

  // Expose save function for top-level save
  window.saveIga = async function() {
    const form = document.getElementById('igaForm');
    if (!form) return { message: 'No IGA form present' };
    const rows = Array.from(list.querySelectorAll('tr.iga-row'));
    const payload = rows.map((row, index) => {
      const rawId = row.getAttribute('data-id');
      const id = rawId && !rawId.startsWith('new-') ? Number(rawId) : null;
      const code = `IGA${index + 1}`;
      const title = row.querySelector('textarea[name="iga_titles[]"]')?.value || '';
      const description = row.querySelector('textarea[name="igas[]"]')?.value || '';
      
      const position = index + 1;
      return { id, code, title, description, position };
    });
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
    if (tokenMeta) headers['X-CSRF-TOKEN'] = tokenMeta.content;
    try {
      const res = await fetch(form.action, { method: 'PUT', headers, body: JSON.stringify({ igas: payload }) });
      if (!res.ok) throw new Error('Failed to save IGAs');
      const data = await res.json();
      const top = document.getElementById('unsaved-igas'); if (top) top.classList.add('d-none');
      list.querySelectorAll('textarea.autosize').forEach((ta) => ta.setAttribute('data-original', ta.value || ''));
      
      // Update data-id for newly saved rows
      if (data.ids && Array.isArray(data.ids)) {
        rows.forEach((row, index) => {
          const rawId = row.getAttribute('data-id');
          if (rawId && rawId.startsWith('new-') && data.ids[index]) {
            row.setAttribute('data-id', data.ids[index]);
          }
        });
      }
      
      return data;
    } catch (err) {
      console.error('saveIga failed', err);
      throw err;
    }
  };

  // Keyboard: Ctrl/Cmd+Backspace at caret 0 on an empty textarea removes the row
  list.addEventListener('keydown', async (e) => {
    const el = e.target; if (!el || el.tagName !== 'TEXTAREA') return;
    if (e.key === 'Backspace' && (e.ctrlKey || e.metaKey)) {
      const val = el.value || ''; const selStart = (typeof el.selectionStart === 'number') ? el.selectionStart : 0;
      if (val.trim() === '' && selStart === 0) {
        e.preventDefault(); e.stopPropagation();
        const row = el.closest('tr.iga-row');
        const id = row.getAttribute('data-id');
        if (!id || id.startsWith('new-')) {
          const prev = row.previousElementSibling;
          try { row.remove(); } catch (e) { row.remove(); }
          updateVisibleCodes();
          if (prev) { const prevTa = prev.querySelector('textarea.autosize'); if (prevTa) { prevTa.focus(); prevTa.selectionStart = prevTa.value.length; } }
          return;
        }
        
        try {
          const res = await fetch((window.syllabusBasePath || '/faculty/syllabi') + `/igas/${id}`, { 
            method: 'DELETE', 
            headers: { 
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 
              'Accept': 'application/json' 
            } 
          });
          
          if (!res.ok) throw new Error('Failed to delete IGA');
          
          const data = await res.json();
          const prev = row.previousElementSibling;
          
          // Remove the row from DOM
          row.remove();
          
          // Update remaining IGAs with new positions and codes
          await updateIgaPositions();
          
          // Focus previous row
          if (prev) { 
            const prevTa = prev.querySelector('textarea.autosize'); 
            if (prevTa) { 
              prevTa.focus(); 
              prevTa.selectionStart = prevTa.value.length; 
            } 
          }
          
          if (window.showAlertOverlay) {
            window.showAlertOverlay('success', data.message || 'IGA deleted successfully');
          }
        } catch (err) {
          console.error('Failed to delete IGA:', err);
          alert('Failed to delete IGA. Please try again.');
        }
      }
    }
  });

  // Click delete button
  list.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btn-delete-iga'); if (!btn) return;
    const row = btn.closest('tr.iga-row');
    const id = row.getAttribute('data-id'); 
    if (!id || id.startsWith('new-')) { 
      try { row.remove(); } catch (e) { row.remove(); } 
      
      // Check if any rows remain, if not show placeholder
      const rows = list.querySelectorAll('tr.iga-row');
      if (rows.length === 0) {
        const placeholder = document.createElement('tr');
        placeholder.id = 'iga-placeholder';
        placeholder.innerHTML = `
          <td colspan="2" class="text-center text-muted py-4">
            <p class="mb-2">No IGAs added yet.</p>
            <p class="mb-0"><small>Click the <strong>+</strong> button above to add an IGA or <strong>Load Predefined</strong> to import IGAs.</small></p>
          </td>
        `;
        list.appendChild(placeholder);
      } else {
        updateVisibleCodes();
      }
      return; 
    }
    
    try {
      const res = await fetch((window.syllabusBasePath || '/faculty/syllabi') + `/igas/${id}`, { 
        method: 'DELETE', 
        headers: { 
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 
          'Accept': 'application/json' 
        } 
      });
      
      if (!res.ok) throw new Error('Failed to delete IGA');
      
      const data = await res.json();
      
      // Remove the row from DOM
      row.remove();
      
      // Check if any rows remain, if not show placeholder
      const remainingRows = list.querySelectorAll('tr.iga-row');
      if (remainingRows.length === 0) {
        const placeholder = document.createElement('tr');
        placeholder.id = 'iga-placeholder';
        placeholder.innerHTML = `
          <td colspan="2" class="text-center text-muted py-4">
            <p class="mb-2">No IGAs added yet.</p>
            <p class="mb-0"><small>Click the <strong>+</strong> button above to add an IGA or <strong>Load Predefined</strong> to import IGAs.</small></p>
          </td>
        `;
        list.appendChild(placeholder);
      } else {
        // Update remaining IGAs with new positions and codes
        await updateIgaPositions();
      }
      
      if (window.showAlertOverlay) {
        window.showAlertOverlay('success', data.message || 'IGA deleted successfully');
      } else {
        alert(data.message || 'IGA deleted successfully');
      }
      
    } catch (err) {
      console.error('Failed to delete IGA:', err);
      alert('Failed to delete IGA. Please try again.');
    }
  });

  // Update IGA positions and codes on server after deletion or reorder
  async function updateIgaPositions() {
    const form = document.getElementById('igaForm');
    if (!form) return;
    
    const rows = Array.from(list.querySelectorAll('tr.iga-row'));
    const payload = rows.map((row, index) => {
      const rawId = row.getAttribute('data-id');
      const id = rawId && !rawId.startsWith('new-') ? Number(rawId) : null;
      const code = `IGA${index + 1}`;
      const title = row.querySelector('textarea[name="iga_titles[]"]')?.value || '';
      const description = row.querySelector('textarea[name="igas[]"]')?.value || '';
      const position = index + 1;
      
      return { id, code, title, description, position };
    }).filter(item => item.id); // Only update existing IGAs
    
    if (payload.length === 0) {
      updateVisibleCodes();
      return;
    }
    
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
    if (tokenMeta) headers['X-CSRF-TOKEN'] = tokenMeta.content;
    
    try {
      const res = await fetch(form.action, { 
        method: 'PUT', 
        headers, 
        body: JSON.stringify({ igas: payload }) 
      });
      
      if (!res.ok) throw new Error('Failed to update IGA positions');
      
      updateVisibleCodes();
    } catch (err) {
      console.error('Failed to update IGA positions:', err);
      updateVisibleCodes();
    }
  }

  // Bind global unsaved tracking
  function bindGlobalUnsaved() {
    function checkAnyChanged() {
      const anyChanged = Array.from(list.querySelectorAll('textarea.autosize')).some(t => (t.value || '') !== (t.getAttribute('data-original') || ''));
      const top = document.getElementById('unsaved-igas'); if (top) top.classList.toggle('d-none', !anyChanged);
      if (anyChanged) { try { markDirty('unsaved-igas'); } catch (e) {} }
      try { updateUnsavedCount(); } catch (e) { }
    }
    list.querySelectorAll('textarea.autosize').forEach((ta) => { ta.addEventListener('input', checkAnyChanged); ta.addEventListener('change', checkAnyChanged); });
  }

  // Initialize
  updateVisibleCodes();
  bindGlobalUnsaved();

  // Wire save function to toolbar save button
  const toolbarSaveBtn = document.getElementById('syllabusSaveBtn');
  if (toolbarSaveBtn) {
    toolbarSaveBtn.addEventListener('click', async (e) => {
      // Check if this is the main save button click
      if (e.isTrusted && window.saveIga) {
        try {
          await window.saveIga();
        } catch (err) {
          console.error('Failed to save IGAs from toolbar:', err);
        }
      }
    }, true); // Use capture phase to run before other handlers
  }

  // Load Predefined IGAs functionality
  const loadPredefinedBtn = document.getElementById('iga-load-predefined');
  const loadPredefinedModal = document.getElementById('loadPredefinedIgasModal');
  const confirmLoadBtn = document.getElementById('confirmLoadPredefinedIgas');
  const igaSelectionList = document.getElementById('igaSelectionList');
  const selectAllCheckbox = document.getElementById('selectAllIgas');
  let availableIgas = [];

  if (loadPredefinedBtn && loadPredefinedModal) {
    loadPredefinedBtn.addEventListener('click', async function() {
      const modal = new bootstrap.Modal(loadPredefinedModal);
      modal.show();

      // Load available IGAs from master data
      try {
        const response = await fetch('/faculty/master-data/iga/filter', {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        if (!response.ok) throw new Error('Failed to load IGAs');

        const data = await response.json();
        availableIgas = data.igas || [];

        if (availableIgas.length === 0) {
          igaSelectionList.innerHTML = '<div class="text-center text-muted py-3"><p class="mb-0">No predefined IGAs found.</p></div>';
          confirmLoadBtn.disabled = true;
          return;
        }

        // Render checkboxes
        igaSelectionList.innerHTML = availableIgas.map((iga, index) => `
          <div class="form-check mb-2">
            <input class="form-check-input iga-checkbox" type="checkbox" value="${iga.id}" id="iga_${iga.id}" checked>
            <label class="form-check-label" for="iga_${iga.id}">
              <strong>IGA${index + 1}:</strong> ${iga.title || 'Untitled'}
              ${iga.description ? `<br><small class="text-muted">${iga.description.substring(0, 80)}${iga.description.length > 80 ? '...' : ''}</small>` : ''}
            </label>
          </div>
        `).join('');

        confirmLoadBtn.disabled = false;

        // Re-initialize feather icons
        if (typeof feather !== 'undefined') feather.replace();

      } catch (error) {
        console.error('Error loading IGAs:', error);
        igaSelectionList.innerHTML = '<div class="text-center text-danger py-3"><p class="mb-0">Failed to load IGAs</p></div>';
        confirmLoadBtn.disabled = true;
      }
    });
  }

  // Select All functionality
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function() {
      const checkboxes = document.querySelectorAll('.iga-checkbox');
      checkboxes.forEach(cb => cb.checked = this.checked);
    });
  }

  // Update Select All when individual checkboxes change
  if (igaSelectionList) {
    igaSelectionList.addEventListener('change', function(e) {
      if (e.target.classList.contains('iga-checkbox')) {
        const checkboxes = document.querySelectorAll('.iga-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        if (selectAllCheckbox) selectAllCheckbox.checked = allChecked;
      }
    });
  }

  // Confirm button in modal
  const listRef = document.getElementById('syllabus-iga-sortable');
  if (confirmLoadBtn && listRef) {
    confirmLoadBtn.addEventListener('click', async function() {
      const syllabusId = listRef.dataset.syllabusId;
      if (!syllabusId) {
        alert('Syllabus ID not found');
        return;
      }

      // Get selected IGA IDs
      const selectedCheckboxes = document.querySelectorAll('.iga-checkbox:checked');
      const selectedIds = Array.from(selectedCheckboxes).map(cb => parseInt(cb.value));

      if (selectedIds.length === 0) {
        alert('Please select at least one IGA to load.');
        return;
      }

      try {
        confirmLoadBtn.disabled = true;
        const response = await fetch(`/faculty/syllabi/${syllabusId}/load-predefined-igas`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
          },
          body: JSON.stringify({
            iga_ids: selectedIds
          })
        });

        if (!response.ok) {
          const errorData = await response.json().catch(() => ({}));
          throw new Error(errorData.message || 'Failed to load predefined IGAs');
        }

        const data = await response.json();

        if (!data.igas || data.igas.length === 0) {
          alert('No predefined IGAs found.');
          return;
        }

        // Clear existing rows
        while (listRef.firstChild) {
          listRef.removeChild(listRef.firstChild);
        }

        // Add new rows from predefined data
        data.igas.forEach((iga, index) => {
          const row = document.createElement('tr');
          row.className = 'iga-row';
          row.setAttribute('data-id', iga.id || `new-${Date.now()}-${index}`);
          row.innerHTML = `
            <td class="text-center align-middle">
              <div class="iga-badge fw-semibold">${iga.code || ('IGA' + (index + 1))}</div>
            </td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <span class="drag-handle text-muted" title="Drag to reorder"><i class="bi bi-grip-vertical"></i></span>
                <div class="flex-grow-1 w-100">
                  <textarea name="iga_titles[]" class="cis-textarea cis-field autosize" placeholder="Title" rows="1" style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;font-weight:700;" required>${iga.title || ''}</textarea>
                  <textarea name="igas[]" class="cis-textarea cis-field autosize" placeholder="Description" rows="1" style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;" required>${iga.description || ''}</textarea>
                </div>
                <input type="hidden" name="code[]" value="${iga.code || ('IGA' + (index + 1))}">
                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-iga ms-2" title="Delete IGA"><i class="bi bi-trash"></i></button>
              </div>
            </td>
          `;
          listRef.appendChild(row);
          row.querySelectorAll('textarea.autosize').forEach(bindAutosize);
        });

        // Close modal
        const modalInstance = bootstrap.Modal.getInstance(loadPredefinedModal);
        if (modalInstance) modalInstance.hide();

        // Show success message
        if (window.showAlertOverlay) {
          window.showAlertOverlay('success', data.message || 'Predefined IGAs loaded successfully');
        } else {
          alert(data.message || 'Predefined IGAs loaded successfully');
        }

      } catch (error) {
        console.error('Error loading predefined IGAs:', error);
        alert(error.message || 'Failed to load predefined IGAs');
      } finally {
        confirmLoadBtn.disabled = false;
      }
    });
  }
});
