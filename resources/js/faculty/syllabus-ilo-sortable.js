// -----------------------------------------------------------------------------
// File: resources/js/faculty/syllabus-ilo-sortable.js
// Description: Enables drag-reorder, auto-code update, and inline add/delete for CIS-style ILO layout – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Adapted to match CIS-style two-column layout with <th> and textarea in <td>.
// [2025-07-29] Synced sortable logic with dynamic field naming and hidden ILO ID tracking.
// [2025-07-29] Referenced SO sortable logic for consistency and added structured payload.
// [2025-07-29] FIXED: ILO codes now always update correctly after drag/add/delete.
// -----------------------------------------------------------------------------

import Sortable from 'sortablejs';
import { initAutosize, markDirty, updateUnsavedCount } from './syllabus';

document.addEventListener('DOMContentLoaded', () => {
  const list = document.getElementById('syllabus-ilo-sortable');
  const saveBtn = document.getElementById('save-syllabus-ilo-order');
  const addBtn = document.getElementById('add-ilo-row');

  // tolerate pages where the add/save buttons were removed; behave gracefully
  if (!list) return;

  // 🔁 Re-number all ILO codes visibly and in hidden inputs
  function updateVisibleCodes() {
    const rows = list.querySelectorAll('tr[data-id]');
    rows.forEach((row, index) => {
      const newCode = `ILO${index + 1}`;

      // Update badge text for visible code
      const badge = row.querySelector('.ilo-badge');
      if (badge) badge.textContent = newCode;

      // Update hidden <input name="code[]">
      const codeInput = row.querySelector('input[name="code[]"]');
      if (codeInput) codeInput.value = newCode;
  });
  }

  // Title layout is handled by the table; dynamic JS syncing removed to avoid overlap.

  // 🧲 Enable sortable functionality
  Sortable.create(list, {
  handle: '.drag-handle',
  animation: 150,
  fallbackOnBody: true,
  draggable: 'tr',
  swapThreshold: 0.65,
  onEnd: function (evt) {
    updateVisibleCodes();
    // mark the ILO module as having unsaved changes so the top Save persists order
    try { markDirty('unsaved-ilos'); } catch (e) { /* noop */ }
    try { updateUnsavedCount(); } catch (e) { /* noop */ }
  }
  });

  // 💾 Save button logic (AJAX reorder) — only bind if the button exists
  if (saveBtn) {
    saveBtn.addEventListener('click', () => {
      const syllabusId = list.getAttribute('data-syllabus-id');
      const rows = list.querySelectorAll('tr[data-id]');

      const ordered = Array.from(rows).map((row, index) => {
        return {
          id: row.getAttribute('data-id'),
          code: row.querySelector('input[name="code[]"]')?.value,
          description: row.querySelector('textarea[name="ilos[]"]')?.value,
          position: index + 1
        };
      }).filter(item => item.id && !item.id.startsWith('new-'));

      fetch(`/faculty/syllabi/reorder/ilo`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ syllabus_id: syllabusId, positions: ordered })
      })
      .then(res => res.json())
      .then(data => {
        alert(data.message || 'ILO order saved.');
        location.reload();
      })
      .catch(err => {
        console.error(err);
        alert('Failed to save ILO order.');
      });
    });
  }

  // Expose a global save function so the top Save can call it before main save
  window.saveIloOrder = async function() {
    const syllabusId = list.getAttribute('data-syllabus-id');
    const rows = list.querySelectorAll('tr[data-id]');

    const ordered = Array.from(rows).map((row, index) => ({
      id: row.getAttribute('data-id'),
      code: row.querySelector('input[name="code[]"]')?.value,
      description: row.querySelector('textarea[name="ilos[]"]')?.value,
      position: index + 1
    })).filter(item => item.id && !item.id.startsWith('new-'));

    try {
      const res = await fetch(`/faculty/syllabi/reorder/ilo`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ syllabus_id: syllabusId, positions: ordered })
      });
      if (!res.ok) throw new Error('Failed to save ILO order');
      return await res.json();
    } catch (err) {
      console.error('saveIloOrder failed', err);
      throw err;
    }
  };

  // Composite save: save order + save form content (if any)
  window.saveIlo = async function() {
    // Build a structured payload matching controller expectations: { ilos: [{id, code, description, position}, ...] }
    const iloForm = document.getElementById('iloForm');
    if (!iloForm) return { message: 'No ILO form present' };

    const action = iloForm.action;
    const listRows = list.querySelectorAll('tr[data-id]');

    const payloadIlo = Array.from(listRows).map((row, index) => {
      const rawId = row.getAttribute('data-id');
      const id = rawId && !rawId.startsWith('new-') ? Number(rawId) : null;
      const code = row.querySelector('input[name="code[]"]')?.value || `ILO${index + 1}`;
      const description = row.querySelector('textarea[name="ilos[]"]')?.value || '';
      const position = index + 1;
      return { id, code, description, position };
    });

    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
    if (tokenMeta) headers['X-CSRF-TOKEN'] = tokenMeta.content;

    try {
      const res = await fetch(action, {
        method: 'PUT',
        headers,
        body: JSON.stringify({ ilos: payloadIlo })
      });

      if (!res.ok) {
        // attempt to surface validation errors if available
        let body = null;
        try { body = await res.json(); } catch (e) { /* ignore */ }
        const msg = (body && (body.message || JSON.stringify(body.errors || body))) || 'Failed to save ILOs';
        throw new Error(msg);
      }

      const data = await res.json();
      // success -> hide unsaved pill and update originals
      const top = document.getElementById('unsaved-ilos');
      if (top) top.classList.add('d-none');
      // update data-original attributes so subsequent edits are tracked correctly
      list.querySelectorAll('textarea.autosize').forEach((ta) => ta.setAttribute('data-original', ta.value || ''));
      return data;
    } catch (err) {
      console.error('saveIlo failed', err);
      throw err;
    }
  };

  // Create a new ILO row element
  function createNewRow() {
    const timestamp = Date.now();
    const newRow = document.createElement('tr');
    newRow.setAttribute('data-id', `new-${timestamp}`);

    newRow.innerHTML = `
      <td class="text-center align-middle">
        <div class="ilo-badge fw-semibold"></div>
      </td>
      <td>
        <div class="d-flex align-items-center gap-2">
          <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab; display:flex; align-items:center;">
            <i class="bi bi-grip-vertical"></i>
          </span>
          <textarea name="ilos[]" class="form-control cis-textarea autosize flex-grow-1"></textarea>
          <input type="hidden" name="code[]" value="">
          <button type="button" class="btn btn-sm btn-outline-danger btn-delete-ilo ms-2" title="Delete ILO"><i class="bi bi-trash"></i></button>
        </div>
      </td>
    `;

    return newRow;
  }

  // Insert a new row; if afterRow is null append to end
  function addRow(afterRow = null) {
    const newRow = createNewRow();
    if (afterRow && afterRow.parentElement) {
      if (afterRow.nextSibling) afterRow.parentElement.insertBefore(newRow, afterRow.nextSibling);
      else afterRow.parentElement.appendChild(newRow);
    } else {
      list.appendChild(newRow);
    }
    // initialize autosize for the new textarea(s)
    try { initAutosize(); } catch (e) { /* fallback: ignore autosize init failures */ }
    updateVisibleCodes();
    // focus the new textarea
    const ta = newRow.querySelector('textarea.autosize');
    if (ta) { ta.focus(); }
    return newRow;
  }

  // ➕ Add a new ILO row (button) — only bind if the add button is present
  if (addBtn) addBtn.addEventListener('click', () => addRow(null));

  // Keyboard shortcuts for textareas inside ILO list
  list.addEventListener('keydown', (e) => {
    const el = e.target;
    if (!el || el.tagName !== 'TEXTAREA') return;

    // BACKSPACE on an empty textarea at caret 0 -> remove the row
    if (e.key === 'Backspace') {
      const val = el.value || '';
      const selStart = (typeof el.selectionStart === 'number') ? el.selectionStart : 0;
      if (val.trim() === '' && selStart === 0) {
        e.preventDefault();
        e.stopPropagation();
        const row = el.closest('tr');
        const id = row.getAttribute('data-id');
        const rows = list.querySelectorAll('tr[data-id]');

        // If last remaining row, just clear it instead of removing
        if (rows.length === 1) {
          el.value = '';
          try { initAutosize(); } catch (e) { /* noop */ }
          return;
        }

        // If row is a new (not yet saved) row, remove it client-side
        if (!id || id.startsWith('new-')) {
          const prev = row.previousElementSibling;
          row.remove();
          updateVisibleCodes();
          if (prev) {
            const prevTa = prev.querySelector('textarea.autosize');
            if (prevTa) { prevTa.focus(); prevTa.selectionStart = prevTa.value.length; }
          } else {
            const firstTa = list.querySelector('textarea.autosize');
            if (firstTa) { firstTa.focus(); firstTa.selectionStart = firstTa.value.length; }
          }
          return;
        }

        // Persisted row: confirm and then delete via server call (same as delete button)
        if (!confirm('This ILO exists on the server. Press OK to delete it.')) return;
        fetch(`/faculty/syllabi/ilos/${id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          }
        })
        .then(res => res.json())
        .then(data => {
          alert(data.message || 'ILO deleted.');
          location.reload();
        })
        .catch(err => {
          console.error(err);
          alert('Failed to delete ILO.');
        });
        return;
      }
    }

    // Ctrl+Enter (or Cmd+Enter) inside any ILO textarea inserts a new row after current one
    if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
      e.preventDefault();
      e.stopPropagation();
      const currentRow = el.closest('tr');
      addRow(currentRow || null);
    }
  });

  // 🗑️ Delete ILO row (temporary or from DB)
  list.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-delete-ilo');
    if (!btn) return;

    const row = btn.closest('tr');
    const id = row.getAttribute('data-id');

    if (!id || id.startsWith('new-')) {
      row.remove();
      updateVisibleCodes();
      return;
    }

    if (!confirm('Are you sure you want to delete this ILO?')) return;

    fetch(`/faculty/syllabi/ilos/${id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
      }
    })
    .then(res => res.json())
    .then(data => {
      alert(data.message || 'ILO deleted.');
  location.reload();
    })
    .catch(err => {
      console.error(err);
      alert('Failed to delete ILO.');
    });
  });

  // ✅ Initialize code labels on load
  updateVisibleCodes();
  // ensure textareas size to their current content on load
  try { initAutosize(); } catch (e) { /* noop */ }

  // Bind unsaved behavior across all ILO textareas: show the title-level pill if any changed
  function bindGlobalUnsaved() {
    function checkAnyChanged() {
      const anyChanged = Array.from(list.querySelectorAll('textarea.autosize')).some(t => (t.value || '') !== (t.getAttribute('data-original') || ''));
      const top = document.getElementById('unsaved-ilos');
      if (top) top.classList.toggle('d-none', !anyChanged);
      if (anyChanged) {
        try { markDirty('unsaved-ilos'); } catch (e) { /* noop */ }
      }
      updateUnsavedCount();
    }

    list.querySelectorAll('textarea.autosize').forEach((ta) => {
      ta.addEventListener('input', checkAnyChanged);
      ta.addEventListener('change', checkAnyChanged);
    });
  }

  bindGlobalUnsaved();
});
