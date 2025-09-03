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
  // keep a stable list of ILO identifiers to detect adds/removes/reorders
  let previousIloIds = null;

  // tolerate pages where the add/save buttons were removed; behave gracefully
  if (!list) return;

  // 🔁 Re-number all ILO codes visibly and in hidden inputs
  function updateVisibleCodes() {
    // Only consider rows that actually represent ILOs (contain a textarea or badge)
    const rows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="ilos[]"]') || r.querySelector('.ilo-badge'));

    // Build current identifiers using row data-id (persisted id or new-... temporary id)
    const currentIds = rows.map((r) => r.getAttribute('data-id') || `client-${Math.random().toString(36).slice(2,8)}`);

    // Update visual labels and hidden inputs
    rows.forEach((row, index) => {
      const newCode = `ILO${index + 1}`;
      const badge = row.querySelector('.ilo-badge'); if (badge) badge.textContent = newCode;
      const codeInput = row.querySelector('input[name="code[]"]'); if (codeInput) codeInput.value = newCode;
    });

    // Hide delete button for the first ILO row and show for others
    try {
      rows.forEach((row, index) => {
        const btn = row.querySelector('.btn-delete-ilo');
        if (!btn) return;
        btn.style.display = index === 0 ? 'none' : '';
      });
    } catch (e) { /* noop */ }

    // Detect adds/removes/reorders by comparing previousIds -> currentIds
    try {
      // previousIloIds is a module-scoped variable (defined below)
      if (Array.isArray(previousIloIds)) {
        const prev = previousIloIds;
        // added ids are those present now but not previously
        const added = currentIds.filter(id => !prev.includes(id));
        const removed = prev.filter(id => !currentIds.includes(id));

        // dispatch add events for added ids with their new index
        added.forEach((id) => {
          const idx = currentIds.indexOf(id);
          document.dispatchEvent(new CustomEvent('ilo:changed', { detail: { action: 'add', id, index: idx, count: currentIds.length } }));
        });

        // dispatch remove events for removed ids with their previous index
        removed.forEach((id) => {
          const prevIndex = prev.indexOf(id);
          document.dispatchEvent(new CustomEvent('ilo:changed', { detail: { action: 'remove', id, index: prevIndex, count: currentIds.length } }));
        });

        // if same length but order changed, dispatch a single reorder event with mapping
        if (added.length === 0 && removed.length === 0 && currentIds.join('|') !== prev.join('|')) {
          const mapping = currentIds.map((id, to) => ({ id, from: prev.indexOf(id), to }));
          document.dispatchEvent(new CustomEvent('ilo:changed', { detail: { action: 'reorder', mapping, count: currentIds.length } }));
        }
      }
    } catch (e) { /* noop */ }

    // Save currentIds as previous for next comparison
    previousIloIds = currentIds.slice();

    // Notify AT module (and any other listeners) that ILO codes/order changed
    try {
      // Dispatch simple numeric labels (1..N) so AT headers display plain numbers
      const codes = rows.map((r, i) => String(i + 1));
      const evt = new CustomEvent('ilo:renumber', { detail: { codes } });
      document.dispatchEvent(evt);
  // When ILOs change, signal AT module unsaved state
  try { if (window.markAsUnsaved) window.markAsUnsaved('assessment_tasks'); } catch (e) { /* noop */ }
    } catch (e) { /* noop */ }
  }

  // Listen for cross-module add/remove events dispatched by the Blade partials.
  // This ensures rows added via the inline keyboard handler (which clones DOM
  // nodes) are renumbered and initialized the same as rows created through
  // the JS helpers.
  // Standalone ILO module: do not listen for cross-module AT events

  // Respond to AT module-initiated changes: when AT adds/removes an ILO column
  // Standalone ILO module: no AT-driven add/remove listeners

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
      // include all rows that look like ILO rows (may not have data-id when rendered server-side empty template)
      const rows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="ilos[]"]') || r.querySelector('.ilo-badge'));

      const ordered = rows.map((row, index) => {
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
    const rows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="ilos[]"]') || r.querySelector('.ilo-badge'));

    const ordered = rows.map((row, index) => ({
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
    const listRows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="ilos[]"]') || r.querySelector('.ilo-badge'));

    const payloadIlo = listRows.map((row, index) => {
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
  // Log full response for debugging
  try { console.error('ILO save failed response', body); } catch (e) {}
  const extracted = (body && body.message) ? body.message : (body && body.errors ? JSON.stringify(body.errors) : JSON.stringify(body));
  const msg = extracted || 'Failed to save ILOs';
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
  // Standalone: do not notify AT module about added rows
    return newRow;
  }

  // ➕ Add a new ILO row (button) — only bind if the add button is present
  if (addBtn) addBtn.addEventListener('click', () => addRow(null));

  // Keyboard shortcuts for textareas inside ILO list
  list.addEventListener('keydown', (e) => {
    const el = e.target;
    if (!el || el.tagName !== 'TEXTAREA') return;

    // Ctrl+Backspace (or Cmd+Backspace) on an empty textarea at caret 0 -> remove the row
    if (e.key === 'Backspace' && (e.ctrlKey || e.metaKey)) {
      const val = el.value || '';
      const selStart = (typeof el.selectionStart === 'number') ? el.selectionStart : 0;
      if (val.trim() === '' && selStart === 0) {
        e.preventDefault();
        e.stopPropagation();
        const row = el.closest('tr');
        const id = row.getAttribute('data-id');
        // If this is the first ILO row, disallow deletion (keep at least one ILO)
        const allRows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="ilos[]"]') || r.querySelector('.ilo-badge'));
        const rowIndex = allRows.indexOf(row);
        if (rowIndex === 0) {
          el.value = '';
          try { initAutosize(); } catch (e) { /* noop */ }
          return;
        }

        // If last remaining row, just clear it instead of removing
        if (allRows.length === 1) {
          el.value = '';
          try { initAutosize(); } catch (e) { /* noop */ }
          return;
        }

        // If row is a new (not yet saved) row, remove it client-side
        if (!id || id.startsWith('new-')) {
          const prev = row.previousElementSibling;
          // compute index for removal prior to removing the element
          try {
            const rows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="ilos[]"]') || r.querySelector('.ilo-badge'));
            const idx = rows.indexOf(row);
            row.remove();
            // Standalone: do not notify AT module about removed rows
          } catch (e) {
            row.remove();
          }
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

  // Note: keyboard-driven add (Ctrl/Cmd+Enter) is handled in the blade ILO partial
  // to coordinate AT column insertion; avoid duplicating add behavior here.
  });

  // 🗑️ Delete ILO row (temporary or from DB)
  list.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-delete-ilo');
    if (!btn) return;

    const row = btn.closest('tr');
    const id = row.getAttribute('data-id');

    // Prevent deleting the first ILO row
    const allRows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="ilos[]"]') || r.querySelector('.ilo-badge'));
    const rowIndex = allRows.indexOf(row);
    if (rowIndex === 0) {
      alert('At least one ILO must be present.');
      return;
    }

    if (!id || id.startsWith('new-')) {
      // compute index before removal
      try {
        const rows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="ilos[]"]') || r.querySelector('.ilo-badge'));
        const idx = rows.indexOf(row);
        row.remove();
  // Standalone: no AT notification
      } catch (e) {
        row.remove();
      }
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
