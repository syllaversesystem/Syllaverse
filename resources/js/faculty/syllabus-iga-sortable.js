// -----------------------------------------------------------------------------
// File: resources/js/faculty/syllabus-iga-sortable.js
// Description: Enables drag-reorder, auto-code update, and inline add/delete for IGA module — mirrors ILO behavior
// -----------------------------------------------------------------------------

import Sortable from 'sortablejs';
import { initAutosize, markDirty, updateUnsavedCount } from './syllabus';

document.addEventListener('DOMContentLoaded', () => {
  const list = document.getElementById('syllabus-iga-sortable');
  if (!list) return;

  let previousIgaIds = null;

  function updateVisibleCodes() {
    const rows = Array.from(list.querySelectorAll('tr.iga-row'));
    const currentIds = rows.map((r) => r.getAttribute('data-id') || `client-${Math.random().toString(36).slice(2,8)}`);
    rows.forEach((row, index) => {
      const newCode = `IGA${index + 1}`;
      const badge = row.querySelector('.iga-badge'); if (badge) badge.textContent = newCode;
      const codeInput = row.querySelector('input[name="code[]"]'); if (codeInput) codeInput.value = newCode;
      const btn = row.querySelector('.btn-delete-iga');
      const rowId = row.getAttribute('data-id');
      if (btn) btn.style.display = (rowId && rowId.startsWith('new-')) ? 'none' : '';
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

  // expose the renumbering function so other scripts (inline clones) can call it
  try { window.updateIgaVisibleCodes = updateVisibleCodes; } catch (e) { /* noop */ }

  // Enable sortable on single-row IGAs – mirror ILO config
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

  // Observe DOM changes under the list and renumber when rows are added/removed.
  // This ensures inline-clone behaviors (from the blade partial) immediately get correct numbering.
  try {
    if (window.MutationObserver) {
      const mo = new MutationObserver((mutations) => {
        let shouldUpdate = false;
        for (const m of mutations) {
          if (m.type === 'childList' && (m.addedNodes.length || m.removedNodes.length)) {
            shouldUpdate = true;
            break;
          }
        }
        if (shouldUpdate) {
          // small microtask delay so any cloned nodes finish initializing
          Promise.resolve().then(() => {
            try { initAutosize(); } catch (e) { /* noop */ }
            updateVisibleCodes();
            try { updateUnsavedCount(); } catch (e) { /* noop */ }
          });
        }
      });
      mo.observe(list, { childList: true, subtree: false });
    }
  } catch (e) { /* noop */ }

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
      
      // Update data-id for newly saved rows and show delete buttons
      if (data.ids && Array.isArray(data.ids)) {
        rows.forEach((row, index) => {
          const rawId = row.getAttribute('data-id');
          if (rawId && rawId.startsWith('new-') && data.ids[index]) {
            row.setAttribute('data-id', data.ids[index]);
            const deleteBtn = row.querySelector('.btn-delete-iga');
            if (deleteBtn) deleteBtn.style.display = '';
          }
        });
      }
      
      return data;
    } catch (err) {
      console.error('saveIga failed', err);
      throw err;
    }
  };

  // Add/delete/keyboard handlers
  function createNewRow() {
    const timestamp = Date.now();
    const newRow = document.createElement('tr');
    newRow.className = 'iga-row';
    newRow.setAttribute('data-id', `new-${timestamp}`);
    newRow.innerHTML = `
      <td class="text-center align-middle">
        <div class="iga-badge fw-semibold"></div>
      </td>
      <td>
        <div class="d-flex align-items-center gap-2">
          <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab; display:flex; align-items:center;">
            <i class="bi bi-grip-vertical"></i>
          </span>
          <div class="flex-grow-1 w-100">
            <textarea name="iga_titles[]" class="cis-textarea cis-field autosize" placeholder="-" rows="1" style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;font-weight:700;" required></textarea>
            <textarea name="igas[]" class="cis-textarea cis-field autosize" placeholder="Description" rows="1" style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;" required></textarea>
          </div>
          <input type="hidden" name="code[]" value="">
          <button type="button" class="btn btn-sm btn-outline-danger btn-delete-iga ms-2" title="Delete IGA" style="display: none;"><i class="bi bi-trash"></i></button>
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

  // Keyboard: Ctrl/Cmd+Backspace at caret 0 on an empty textarea removes the row (match ILO)
  list.addEventListener('keydown', (e) => {
    const el = e.target; if (!el || el.tagName !== 'TEXTAREA') return;
    if (e.key === 'Backspace' && (e.ctrlKey || e.metaKey)) {
      const val = el.value || ''; const selStart = (typeof el.selectionStart === 'number') ? el.selectionStart : 0;
      if (val.trim() === '' && selStart === 0) {
        e.preventDefault(); e.stopPropagation();
        const row = el.closest('tr.iga-row');
        const allRows = Array.from(list.querySelectorAll('tr.iga-row'));
        const id = row.getAttribute('data-id');
        if (!id || id.startsWith('new-')) {
          const prev = row.previousElementSibling;
          try { const rows = Array.from(list.querySelectorAll('tr.iga-row')); const idx = rows.indexOf(row); row.remove(); } catch (e) { row.remove(); }
          updateVisibleCodes();
          if (prev) { const prevTa = prev.querySelector('textarea.autosize'); if (prevTa) { prevTa.focus(); prevTa.selectionStart = prevTa.value.length; } }
          return;
        }
        if (!confirm('This IGA exists on the server. Press OK to delete it.')) return;
  fetch((window.syllabusBasePath || '/faculty/syllabi') + `/igas/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
        .then(res => res.json()).then(data => { alert(data.message || 'IGA deleted.'); location.reload(); }).catch(err => { console.error(err); alert('Failed to delete IGA.'); });
      }
    }
  });

  // Click delete button
  list.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-delete-iga'); if (!btn) return;
    const row = btn.closest('tr.iga-row');
    const id = row.getAttribute('data-id'); if (!id || id.startsWith('new-')) { try { row.remove(); } catch (e) { row.remove(); } updateVisibleCodes(); return; }
    if (!confirm('Are you sure you want to delete this IGA?')) return;
  fetch((window.syllabusBasePath || '/faculty/syllabi') + `/igas/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
    .then(res => res.json()).then(data => { alert(data.message || 'IGA deleted.'); location.reload(); }).catch(err => { console.error(err); alert('Failed to delete IGA.'); });
  });

  // initialize
  updateVisibleCodes();
  try { initAutosize(); } catch (e) {}

  function bindGlobalUnsaved() {
    function checkAnyChanged() {
      const anyChanged = Array.from(list.querySelectorAll('textarea.autosize')).some(t => (t.value || '') !== (t.getAttribute('data-original') || ''));
      const top = document.getElementById('unsaved-igas'); if (top) top.classList.toggle('d-none', !anyChanged);
      if (anyChanged) { try { markDirty('unsaved-igas'); } catch (e) {} }
      updateUnsavedCount();
    }
    list.querySelectorAll('textarea.autosize').forEach((ta) => { ta.addEventListener('input', checkAnyChanged); ta.addEventListener('change', checkAnyChanged); });
  }
  bindGlobalUnsaved();
});
