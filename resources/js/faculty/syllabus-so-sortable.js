// -----------------------------------------------------------------------------
// File: resources/js/faculty/syllabus-so-sortable.js
// Description: Enables drag-reorder and auto-code update for CIS-style SO layout – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Adapted from ILO sortable – added auto-code and save order.
// -----------------------------------------------------------------------------

import Sortable from 'sortablejs';
import { initAutosize, markDirty, updateUnsavedCount } from './syllabus';

document.addEventListener('DOMContentLoaded', () => {
  const list = document.getElementById('syllabus-so-sortable');

  if (!list) return;

  let previousSoIds = null;

  function updateVisibleCodes() {
    const rows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="sos[]"]') || r.querySelector('.so-badge'));
    const currentIds = rows.map((r) => r.getAttribute('data-id') || `client-${Math.random().toString(36).slice(2,8)}`);
    rows.forEach((row, index) => {
      const newCode = `SO${index + 1}`;
      const badge = row.querySelector('.so-badge'); if (badge) badge.textContent = newCode;
      const codeInput = row.querySelector('input[name="code[]"]'); if (codeInput) codeInput.value = newCode;
      const btn = row.querySelector('.btn-delete-so');
      const rowId = row.getAttribute('data-id');
      if (btn) btn.style.display = (rowId && rowId.startsWith('new-')) ? 'none' : '';
    });

    try {
      if (Array.isArray(previousSoIds)) {
        const prev = previousSoIds;
        const added = currentIds.filter(id => !prev.includes(id));
        const removed = prev.filter(id => !currentIds.includes(id));
        added.forEach((id) => { const idx = currentIds.indexOf(id); document.dispatchEvent(new CustomEvent('so:changed', { detail: { action: 'add', id, index: idx, count: currentIds.length } })); });
        removed.forEach((id) => { const prevIndex = prev.indexOf(id); document.dispatchEvent(new CustomEvent('so:changed', { detail: { action: 'remove', id, index: prevIndex, count: currentIds.length } })); });
        if (added.length === 0 && removed.length === 0 && currentIds.join('|') !== prev.join('|')) {
          const mapping = currentIds.map((id, to) => ({ id, from: prev.indexOf(id), to }));
          document.dispatchEvent(new CustomEvent('so:changed', { detail: { action: 'reorder', mapping, count: currentIds.length } }));
        }
      }
    } catch (e) { /* noop */ }

    previousSoIds = currentIds.slice();

    try { if (window.markAsUnsaved) window.markAsUnsaved('sos'); } catch (e) { }
  }

  try { window.updateSoVisibleCodes = updateVisibleCodes; } catch (e) { /* noop */ }

  Sortable.create(list, {
    handle: '.drag-handle',
    animation: 150,
    fallbackOnBody: true,
    draggable: 'tr',
    swapThreshold: 0.65,
    onEnd: function (evt) {
      updateVisibleCodes();
      try { markDirty('unsaved-sos'); } catch (e) { }
      try { updateUnsavedCount(); } catch (e) { }
    }
  });

  // Observe DOM changes and renumber after small microtask so clones initialize
  try {
    if (window.MutationObserver) {
      const mo = new MutationObserver((mutations) => {
        let shouldUpdate = false;
        for (const m of mutations) {
          if (m.type === 'childList' && (m.addedNodes.length || m.removedNodes.length)) { shouldUpdate = true; break; }
        }
        if (shouldUpdate) {
          Promise.resolve().then(() => { try { initAutosize(); } catch (e) {} updateVisibleCodes(); try { updateUnsavedCount(); } catch (e) {} });
        }
      });
      mo.observe(list, { childList: true, subtree: false });
    }
  } catch (e) { /* noop */ }

  // Create new row helper
  function createNewRow() {
    const timestamp = Date.now();
    const newRow = document.createElement('tr');
    newRow.setAttribute('data-id', `new-${timestamp}`);
    newRow.innerHTML = `
      <td class="text-center align-middle"><div class="so-badge fw-semibold"></div></td>
      <td>
        <div class="d-flex align-items-center gap-2">
          <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab; display:flex; align-items:center;">
            <i class="bi bi-grip-vertical"></i>
          </span>
          <div class="flex-grow-1 w-100">
            <textarea name="so_titles[]" class="cis-textarea cis-field autosize" placeholder="-" rows="1" style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;font-weight:700;" required></textarea>
            <textarea name="sos[]" class="cis-textarea cis-field autosize" placeholder="Description" rows="1" style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;" required></textarea>
          </div>
          <input type="hidden" name="code[]" value="">
          <button type="button" class="btn btn-sm btn-outline-danger btn-delete-so ms-2" title="Delete SO" style="display: none;"><i class="bi bi-trash"></i></button>
        </div>
      </td>
    `;
    return newRow;
  }

  // Add row function
  function addRow() {
    const newRow = createNewRow();
    list.appendChild(newRow);
    try { initAutosize(); } catch (e) {}
    updateVisibleCodes();
    const ta = newRow.querySelector('textarea.autosize');
    if (ta) ta.focus();
    return newRow;
  }

  // Remove last row function - only removes unsaved rows
  function removeLastRow() {
    const rows = Array.from(list.querySelectorAll('tr'));
    if (!rows.length) return;
    
    let target = null;
    for (let i = rows.length - 1; i >= 0; i--) {
      const id = rows[i].getAttribute('data-id');
      if (id && id.startsWith('new-')) {
        target = rows[i];
        break;
      }
    }
    
    if (!target) return;
    
    target.remove();
    updateVisibleCodes();
  }

  // Header button listeners
  const addBtn = document.getElementById('so-add-header');
  const removeBtn = document.getElementById('so-remove-header');
  
  if (addBtn) {
    addBtn.addEventListener('click', () => addRow());
  }
  
  if (removeBtn) {
    removeBtn.addEventListener('click', () => removeLastRow());
  }

  // initialize
  updateVisibleCodes();
  try { initAutosize(); } catch (e) {}

  function bindGlobalUnsaved() {
    function checkAnyChanged() {
      const anyChanged = Array.from(list.querySelectorAll('textarea.autosize')).some(t => (t.value || '') !== (t.getAttribute('data-original') || ''));
      const top = document.getElementById('unsaved-sos'); if (top) top.classList.toggle('d-none', !anyChanged);
      if (anyChanged) { try { markDirty('unsaved-sos'); } catch (e) {} }
      updateUnsavedCount();
    }
    list.querySelectorAll('textarea.autosize').forEach((ta) => { ta.addEventListener('input', checkAnyChanged); ta.addEventListener('change', checkAnyChanged); });
  }
  bindGlobalUnsaved();
});
