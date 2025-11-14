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

    // hide delete button on first row
    rows.forEach((row, index) => {
      const btn = row.querySelector('.btn-delete-cdio'); if (!btn) return; btn.style.display = index === 0 ? 'none' : '';
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
        const allRows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="cdios[]"]') || r.querySelector('.cdio-badge'));
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
        if (!confirm('This CDIO exists on the server. Press OK to delete it.')) return;
  fetch((window.syllabusBasePath || '/faculty/syllabi') + `/cdios/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
        .then(res => res.json()).then(data => { alert(data.message || 'CDIO deleted.'); location.reload(); }).catch(err => { console.error(err); alert('Failed to delete CDIO.'); });
      }
    }
  });

  // Click delete button
  list.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-delete-cdio'); if (!btn) return;
    const row = btn.closest('tr'); const allRows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="cdios[]"]') || r.querySelector('.cdio-badge'));
    const rowIndex = allRows.indexOf(row); if (rowIndex === 0) { alert('At least one CDIO must be present.'); return; }
    const id = row.getAttribute('data-id'); if (!id || id.startsWith('new-')) { try { row.remove(); } catch (e) { row.remove(); } updateVisibleCodes(); return; }
    if (!confirm('Are you sure you want to delete this CDIO?')) return;
  fetch((window.syllabusBasePath || '/faculty/syllabi') + `/cdios/${id}`, { method: 'DELETE', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
  .then(res => res.json()).then(data => { alert(data.message || 'CDIO deleted.'); location.reload(); }).catch(err => { console.error(err); alert('Failed to delete CDIO.'); });
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
            <textarea name="cdio_titles[]" class="cis-textarea cis-field autosize" placeholder="-" rows="1" style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;font-weight:700;" required></textarea>
            <textarea name="cdios[]" class="cis-textarea cis-field autosize" placeholder="Description" rows="1" style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;" required></textarea>
          </div>
          <input type="hidden" name="code[]" value="">
          <button type="button" class="btn btn-sm btn-outline-danger btn-delete-cdio ms-2" title="Delete CDIO"><i class="bi bi-trash"></i></button>
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

  // expose add/remove helpers (programmatic)
  try { window.addCdioRow = function(afterRowSelector = null) { const after = afterRowSelector ? document.querySelector(afterRowSelector) : null; return addRow(after); }; } catch (e) {}
  try { window.removeCdioRow = function(rowSelector) { const row = (typeof rowSelector === 'string') ? document.querySelector(rowSelector) : rowSelector; if (!row) return false; const id = row.getAttribute && row.getAttribute('data-id'); if (!id || id.startsWith('new-')) { row.remove(); updateVisibleCodes(); return Promise.resolve({ message: 'row removed' }); } return fetch(`/faculty/syllabi/cdios/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } }).then(res => res.json()).then(data => { location.reload(); }); }; } catch (e) {}

  // Header add/remove buttons
  document.getElementById('cdio-add-header')?.addEventListener('click', () => addRow(null));
  document.getElementById('cdio-remove-header')?.addEventListener('click', () => {
    const rows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="cdios[]"]') || r.querySelector('.cdio-badge'));
    if (rows.length > 0) { rows[rows.length - 1].remove(); updateVisibleCodes(); }
  });

  // ensure at least one row exists
  (function ensureAtLeastOne() {
    const rows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="cdios[]"]') || r.querySelector('.cdio-badge'));
    if (rows.length === 0) {
      addRow();
    }
  })();

  // initial
  updateVisibleCodes();
  try { initAutosize(); } catch (e) {}

  // wire any .add-cdio buttons to add a single row (debounced)
  let _lastAdd = 0;
  document.addEventListener('click', (ev) => {
    const btn = ev.target.closest && ev.target.closest('.add-cdio'); if (!btn) return;
    const now = Date.now(); if (now - _lastAdd < 300) return; _lastAdd = now;
    // find currently focused row to insert after, otherwise append
    const focused = document.activeElement; const after = focused ? focused.closest && focused.closest('tr') : null;
    addRow(after);
  });

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
