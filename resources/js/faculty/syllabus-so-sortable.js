// -----------------------------------------------------------------------------
// File: resources/js/faculty/syllabus-so-sortable.js
// Description: Enables drag-reorder, auto-code update, and inline add/delete for CIS-style SO layout – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Adapted from ILO sortable – added auto-code, add/delete, and save order.
// -----------------------------------------------------------------------------

import Sortable from 'sortablejs';
import { initAutosize, markDirty, updateUnsavedCount } from './syllabus';

document.addEventListener('DOMContentLoaded', () => {
  const list = document.getElementById('syllabus-so-sortable');
  const saveBtn = document.getElementById('save-syllabus-so-order');
  const addBtn = document.getElementById('add-so-row');

  if (!list) return;

  let previousSoIds = null;

  function updateVisibleCodes() {
    const rows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="sos[]"]') || r.querySelector('.so-badge'));
    const currentIds = rows.map((r) => r.getAttribute('data-id') || `client-${Math.random().toString(36).slice(2,8)}`);
    rows.forEach((row, index) => {
      const newCode = `SO${index + 1}`;
      const badge = row.querySelector('.so-badge'); if (badge) badge.textContent = newCode;
      const codeInput = row.querySelector('input[name="code[]"]'); if (codeInput) codeInput.value = newCode;
    });

    // show/hide delete buttons
    rows.forEach((row, index) => {
      const btn = row.querySelector('.btn-delete-so'); if (!btn) return; btn.style.display = index === 0 ? 'none' : '';
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

  // Backspace deletion at start of empty textarea
  list.addEventListener('keydown', (e) => {
    const el = e.target; if (!el || el.tagName !== 'TEXTAREA') return;
    // Ctrl/Cmd+Enter: clone/add empty row after current
    if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
      e.preventDefault();
      const tr = el.closest('tr'); if (!tr) return;
      const newRow = createNewRow();
      if (tr.parentElement) {
        if (tr.nextSibling) tr.parentElement.insertBefore(newRow, tr.nextSibling);
        else tr.parentElement.appendChild(newRow);
      } else {
        list.appendChild(newRow);
      }
      try { initAutosize(); } catch (e) {}
      updateVisibleCodes();
      const nta = newRow.querySelector('textarea.autosize') || newRow.querySelector('textarea');
      if (nta) { setTimeout(() => nta.focus(), 10); }
      return;
    }
    if (e.key === 'Backspace') {
      const val = el.value || ''; const selStart = (typeof el.selectionStart === 'number') ? el.selectionStart : 0;
      if (val.trim() === '' && selStart === 0) {
        e.preventDefault(); e.stopPropagation();
        const row = el.closest('tr');
        const allRows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="sos[]"]') || r.querySelector('.so-badge'));
        const rowIndex = allRows.indexOf(row);
        if (rowIndex === 0) { el.value = ''; try { initAutosize(); } catch (e) {} return; }
        if (allRows.length === 1) { el.value = ''; try { initAutosize(); } catch (e) {} return; }
        const id = row.getAttribute('data-id');
        if (!id || id.startsWith('new-')) {
          const prev = row.previousElementSibling;
          try { row.remove(); } catch (e) { row.remove(); }
          updateVisibleCodes();
          if (prev) { const prevTa = prev.querySelector('textarea.autosize'); if (prevTa) { prevTa.focus(); prevTa.selectionStart = prevTa.value.length; } }
          return;
        }
        if (!confirm('This SO exists on the server. Press OK to delete it.')) return;
  fetch((window.syllabusBasePath || '/faculty/syllabi') + `/sos/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
        .then(res => res.json()).then(data => { alert(data.message || 'SO deleted.'); location.reload(); }).catch(err => { console.error(err); alert('Failed to delete SO.'); });
      }
    }
  });

  // Click delete button
  list.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-delete-so'); if (!btn) return;
    const row = btn.closest('tr'); const allRows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="sos[]"]') || r.querySelector('.so-badge'));
    const rowIndex = allRows.indexOf(row); if (rowIndex === 0) { alert('At least one SO must be present.'); return; }
    const id = row.getAttribute('data-id'); if (!id || id.startsWith('new-')) { try { row.remove(); } catch (e) { row.remove(); } updateVisibleCodes(); return; }
    if (!confirm('Are you sure you want to delete this SO?')) return;
  fetch((window.syllabusBasePath || '/faculty/syllabi') + `/sos/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
    .then(res => res.json()).then(data => { alert(data.message || 'SO deleted.'); location.reload(); }).catch(err => { console.error(err); alert('Failed to delete SO.'); });
  });

  function createNewRow() {
    const timestamp = Date.now();
    const newRow = document.createElement('tr');
    newRow.setAttribute('data-id', `new-${timestamp}`);
    newRow.innerHTML = `
      <td class="text-center align-middle"><div class="so-badge fw-semibold"></div></td>
      <td>
        <div class="d-flex align-items-center gap-2">
          <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab; display:flex; align-items:center;"><i class="bi bi-grip-vertical"></i></span>
          <textarea name="sos[]" class="form-control cis-textarea autosize flex-grow-1"></textarea>
          <input type="hidden" name="code[]" value="">
          <button type="button" class="btn btn-sm btn-outline-danger btn-delete-so ms-2" title="Delete SO"><i class="bi bi-trash"></i></button>
        </div>
      </td>
    `;
    return newRow;
  }

  function addRow(afterRow = null) {
    const newRow = createNewRow();
    if (afterRow && afterRow.parentElement) {
      if (afterRow.nextSibling) afterRow.parentElement.insertBefore(newRow, afterRow.nextSibling);
      else afterRow.parentElement.appendChild(newRow);
    } else {
      list.appendChild(newRow);
    }
    try { initAutosize(); } catch (e) {}
    updateVisibleCodes();
    const ta = newRow.querySelector('textarea.autosize'); if (ta) ta.focus();
    return newRow;
  }


  // expose add/remove helpers (programmatic)
  try {
    window.addSoRow = function(afterRowSelector = null) {
      const after = afterRowSelector ? document.querySelector(afterRowSelector) : null;
      return addRow(after);
    };
  } catch (e) {}

  try {
    window.removeSoRow = function(rowSelector) {
      const row = (typeof rowSelector === 'string') ? document.querySelector(rowSelector) : rowSelector;
      if (!row) return false;
      const id = row.getAttribute && row.getAttribute('data-id');
      if (!id || id.startsWith('new-')) {
        row.remove(); updateVisibleCodes(); return Promise.resolve({ message: 'row removed' });
      }
      return fetch(`/faculty/syllabi/sos/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } }).then(res => res.json()).then(data => { location.reload(); });
    };
  } catch (e) {}

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
