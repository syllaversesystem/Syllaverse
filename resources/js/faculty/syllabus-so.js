// File: resources/js/faculty/syllabus-so.js
// Description: Handles AJAX save for Student Outcomes (SO) – Syllaverse

import Sortable from 'sortablejs';

document.addEventListener('DOMContentLoaded', () => {
  const soForm = document.querySelector('#soForm');
  if (!soForm) return;

  soForm.addEventListener('submit', async function (e) {
    e.preventDefault();
    try {
      await window.saveSo();
      alert('✅ SOs updated successfully.');
    } catch (error) {
      console.error('SO Save Error:', error);
      alert('❌ Failed to update SOs:\n' + (error.message || 'Unknown error'));
    }
  });

  // Save button handler
  const saveBtn = document.getElementById('save-so-btn');
  if (saveBtn) {
    saveBtn.addEventListener('click', async function() {
      const originalText = this.innerHTML;
      this.disabled = true;
      this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
      
      try {
        await window.saveSo();
        this.innerHTML = '<i class="bi bi-check-circle me-1"></i>Saved!';
        setTimeout(() => {
          this.innerHTML = originalText;
          this.disabled = false;
        }, 2000);
      } catch (error) {
        console.error('SO Save Error:', error);
        alert('❌ Failed to save SOs:\n' + (error.message || 'Unknown error'));
        this.innerHTML = originalText;
        this.disabled = false;
      }
    });
  }
  
  // Expose an async save function so top-level syllabus Save can await SO persistence
  window.saveSo = async function() {
    const form = document.querySelector('#soForm');
    if (!form) return { message: 'No SO form present' };
    
    const list = document.getElementById('syllabus-so-sortable');
    if (!list) return { message: 'No SO list present' };
    
    // Build sos array from table rows
    const rows = Array.from(list.querySelectorAll('tr'));
    const sos = rows.map((row, index) => {
      const dataId = row.getAttribute('data-id') || '';
      const id = (dataId && !dataId.startsWith('new-')) ? parseInt(dataId) : null;
      const codeInput = row.querySelector('input[name="code[]"]');
      const titleTextarea = row.querySelector('textarea[name="so_titles[]"]');
      const descTextarea = row.querySelector('textarea[name="sos[]"]');
      
      return {
        id: id,
        code: codeInput ? codeInput.value : `SO${index + 1}`,
        title: titleTextarea ? titleTextarea.value : '',
        description: descTextarea ? descTextarea.value : '',
        position: index + 1
      };
    });
    
    const url = form.getAttribute('action');
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    const headers = { 
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    };
    if (tokenMeta) headers['X-CSRF-TOKEN'] = tokenMeta.getAttribute('content');
    
    try {
      const resp = await fetch(url, { 
        method: 'PUT', 
        headers, 
        body: JSON.stringify({ sos }), 
        credentials: 'same-origin' 
      });
      
      if (!resp.ok) {
        let data = null;
        try { data = await resp.json(); } catch (e) { /* noop */ }
        throw new Error((data && data.message) ? data.message : ('Server returned ' + resp.status));
      }
      
      const result = await resp.json().catch(() => ({ message: 'SOs saved' }));
      
      // Update data-id attributes for newly created SOs and show delete buttons
      if (result.ids && Array.isArray(result.ids)) {
        rows.forEach((row, index) => {
          if (result.ids[index]) {
            row.setAttribute('data-id', result.ids[index]);
            const deleteBtn = row.querySelector('.btn-delete-so');
            if (deleteBtn) deleteBtn.style.display = '';
          }
        });
      }
      
      return result;
    } catch (err) {
      throw err;
    }
  };

  // Helper function to renumber SO codes
  function renumberSOs() {
    const list = document.getElementById('syllabus-so-sortable');
    if (!list) return;
    
    const rows = Array.from(list.querySelectorAll('tr'));
    rows.forEach((row, index) => {
      const newCode = `SO${index + 1}`;
      const badge = row.querySelector('.so-badge');
      if (badge) badge.textContent = newCode;
      const codeInput = row.querySelector('input[name="code[]"]');
      if (codeInput) codeInput.value = newCode;
    });
  }

  // Add SO button handler
  const addBtn = document.getElementById('so-add-header');
  if (addBtn) {
    addBtn.addEventListener('click', () => {
      const list = document.getElementById('syllabus-so-sortable');
      if (!list) return;
      
      const currentCount = list.querySelectorAll('tr').length;
      const newCode = `SO${currentCount + 1}`;
      
      const newRow = document.createElement('tr');
      newRow.setAttribute('data-id', `new-${Date.now()}`);
      newRow.innerHTML = `
        <td class="text-center align-middle">
          <div class="so-badge fw-semibold">${newCode}</div>
        </td>
        <td>
          <div class="d-flex align-items-center gap-2">
            <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;">
              <i class="bi bi-grip-vertical"></i>
            </span>
            <div class="flex-grow-1 w-100">
              <textarea
                name="so_titles[]"
                class="cis-textarea cis-field autosize"
                placeholder="-"
                rows="1"
                style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;font-weight:700;"
                required></textarea>
              <textarea
                name="sos[]"
                class="cis-textarea cis-field autosize"
                placeholder="Description"
                rows="1"
                style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;"
                required></textarea>
              <input type="hidden" name="code[]" value="${newCode}">
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-so ms-2" title="Delete SO"><i class="bi bi-trash"></i></button>
          </div>
        </td>
      `;
      
      list.appendChild(newRow);
      
      // Initialize autosize for new textareas if initAutosize is available
      if (window.initAutosize) {
        newRow.querySelectorAll('textarea.autosize').forEach(ta => window.initAutosize(ta));
      }
    });
  }

  // Initialize Sortable for drag and drop
  const soList = document.getElementById('syllabus-so-sortable');
  if (soList && typeof Sortable !== 'undefined') {
    Sortable.create(soList, {
      handle: '.drag-handle',
      animation: 150,
      draggable: 'tr',
      onEnd: function(evt) {
        renumberSOs();
      }
    });
  }

  // Delete button handler
  if (soList) {
    soList.addEventListener('click', async (e) => {
      const btn = e.target.closest('.btn-delete-so');
      if (!btn) return;
      
      const row = btn.closest('tr');
      const id = row.getAttribute('data-id');
      
      // If unsaved row, just remove it
      if (!id || id.startsWith('new-')) {
        row.remove();
        renumberSOs();
        return;
      }
      
      // For saved rows, call backend to delete immediately
      btn.disabled = true;
      
      try {
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const headers = { 'Accept': 'application/json' };
        if (tokenMeta) headers['X-CSRF-TOKEN'] = tokenMeta.getAttribute('content');
        
        const response = await fetch(`/faculty/syllabi/sos/${id}`, {
          method: 'DELETE',
          headers: headers
        });
        
        if (!response.ok) {
          const data = await response.json().catch(() => ({}));
          throw new Error(data.message || 'Failed to delete SO');
        }
        
        // Remove row from UI after successful deletion
        row.remove();
        renumberSOs();
        
      } catch (error) {
        console.error('Error deleting SO:', error);
        if (window.showAlertOverlay) {
          window.showAlertOverlay('danger', error.message || 'Failed to delete SO.');
        } else {
          alert('Failed to delete SO: ' + error.message);
        }
        btn.disabled = false;
      }
    });
  }
});
