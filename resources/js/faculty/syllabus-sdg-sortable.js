// Frontend helpers for syllabus SDG list: sortable, renumbering, keyboard shortcuts
import Sortable from 'sortablejs';
import { initAutosize, markDirty, updateUnsavedCount } from './syllabus';

document.addEventListener('DOMContentLoaded', () => {
  const list = document.getElementById('syllabus-sdg-sortable');
  if (!list) return;

  function updateVisibleCodes() {
    const rows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="sdgs[]"]') || r.querySelector('.cdio-badge'));
    rows.forEach((row, index) => {
      const newCode = `SDG${index + 1}`;
      const badge = row.querySelector('.cdio-badge'); if (badge) badge.textContent = newCode;
      const codeInput = row.querySelector('input[name="code[]"]'); if (codeInput) codeInput.value = newCode;
    });

    // show delete button on all rows (allow deleting first SDG too)
    rows.forEach((row) => {
      const btn = row.querySelector('.btn-delete-cdio'); if (!btn) return; btn.style.display = '';
    });

    try { if (window.markAsUnsaved) window.markAsUnsaved('sdgs'); } catch (e) {}
    try { updateUnsavedCount(); } catch (e) {}
  }

  Sortable.create(list, {
    handle: '.drag-handle',
    animation: 150,
    fallbackOnBody: true,
    draggable: 'tr',
    swapThreshold: 0.65,
    onEnd() { updateVisibleCodes(); try { markDirty('unsaved-sdgs'); } catch (e) {} }
  });

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

  list.addEventListener('keydown', (e) => {
    const el = e.target; if (!el || el.tagName !== 'TEXTAREA') return;
    if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
      e.preventDefault(); const tr = el.closest('tr'); if (!tr) return; const newRow = createNewRow();
      if (tr.parentElement) { if (tr.nextSibling) tr.parentElement.insertBefore(newRow, tr.nextSibling); else tr.parentElement.appendChild(newRow); } else { list.appendChild(newRow); }
      try { initAutosize(); } catch (e) {}
      updateVisibleCodes(); const nta = newRow.querySelector('textarea.autosize') || newRow.querySelector('textarea'); if (nta) { setTimeout(() => nta.focus(), 10); }
      return;
    }

    if (e.key === 'Backspace') {
      const val = el.value || ''; const selStart = (typeof el.selectionStart === 'number') ? el.selectionStart : 0;
      if (val.trim() === '' && selStart === 0) {
        e.preventDefault(); e.stopPropagation();
        const row = el.closest('tr'); const allRows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="sdgs[]"]') || r.querySelector('.cdio-badge'));
        const rowIndex = allRows.indexOf(row); if (rowIndex === 0) { el.value = ''; try { initAutosize(); } catch (e) {} return; }
        if (allRows.length === 1) { el.value = ''; try { initAutosize(); } catch (e) {} return; }
        const id = row.getAttribute('data-id'); if (!id || id.startsWith('new-')) { const prev = row.previousElementSibling; try { row.remove(); } catch (e) { row.remove(); } updateVisibleCodes(); if (prev) { const prevTa = prev.querySelector('textarea.autosize'); if (prevTa) { prevTa.focus(); prevTa.selectionStart = prevTa.value.length; } } return; }
  if (!confirm('This SDG exists on the server. Press OK to delete it.')) return;
  // Prefer using syllabus + sdg id for the detach route; fallback to pivot-id-only endpoint
  const sdgId = row.getAttribute('data-sdg-id');
  const syllabusId = list.dataset.syllabusId;
  const deleteUrl = sdgId && syllabusId ? `/faculty/syllabi/${syllabusId}/sdgs/${sdgId}` : `/faculty/syllabi/sdgs/${id}`;
  fetch(deleteUrl, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
  .then(async (res) => {
    if (!res.ok) {
      const msg = await res.text().catch(() => 'Delete failed');
      throw new Error(msg);
    }
    return res.json().catch(() => ({}));
  })
  .then((data) => {
    // remove row and update UI
    try { row.remove(); } catch (e) { row.parentElement && row.parentElement.removeChild(row); }
    if (window.updateVisibleCodes) try { window.updateVisibleCodes(); } catch (e) {}
    // toast
    try { const ev = new CustomEvent('sdg:detached', { detail: { sdg_id: sdgId, pivot: id } }); document.dispatchEvent(ev); } catch (e) {}
    alert(data.message || 'SDG deleted.');
  })
  .catch(err => { console.error(err); alert('Failed to delete SDG.'); });
      }
    }
  });

  list.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-delete-cdio'); if (!btn) return; const row = btn.closest('tr'); const allRows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="sdgs[]"]') || r.querySelector('.cdio-badge'));
    const id = row.getAttribute('data-id'); if (!id || id.startsWith('new-')) { try { row.remove(); } catch (e) { row.remove(); } updateVisibleCodes(); return; }
    if (!confirm('Are you sure you want to delete this SDG?')) return;
    const sdgId = row.getAttribute('data-sdg-id');
    const syllabusId = list.dataset.syllabusId;
    const deleteUrl = sdgId && syllabusId ? `/faculty/syllabi/${syllabusId}/sdgs/${sdgId}` : `/faculty/syllabi/sdgs/${id}`;
    fetch(deleteUrl, { method: 'DELETE', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
    .then(async (res) => {
      if (!res.ok) {
        const msg = await res.text().catch(() => 'Delete failed');
        throw new Error(msg);
      }
      return res.json().catch(() => ({}));
    })
    .then((data) => {
  try { row.remove(); } catch (e) { row.parentElement && row.parentElement.removeChild(row); }
  try { updateVisibleCodes(); } catch (e) {}
  try { document.dispatchEvent(new CustomEvent('sdg:detached', { detail: { sdg_id: sdgId, pivot: id } })); } catch (e) {}
  try { updateUnsavedCount(); } catch (e) {}
  alert(data.message || 'SDG deleted.');
    })
    .catch(err => { console.error(err); alert('Failed to delete SDG.'); });
  });

  list.addEventListener('blur', (e) => {
    const ta = e.target; if (!ta || ta.tagName !== 'TEXTAREA') return; const row = ta.closest('tr'); if (!row) return; const id = row.getAttribute('data-id');
    if (!id || id.startsWith('new-')) return; // not persisted
    fetch(`/faculty/syllabi/${list.dataset.syllabusId}/sdgs/update/${id}`, {
      method: 'PUT', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
      body: JSON.stringify({ description: ta.value })
    }).then(r => r.json()).then(d => { if (d && d.message) console.debug('SDG inline save:', d.message); else console.debug('SDG inline save response', d); })
    .catch(err => { console.error('SDG inline save error', err); });
  }, true);

  function createNewRow() {
    const timestamp = Date.now(); const newRow = document.createElement('tr'); newRow.setAttribute('data-id', `new-${timestamp}`);
    newRow.innerHTML = `
      <td class="text-center align-middle"><div class="cdio-badge"></div></td>
      <td>
        <div class="d-flex align-items-center gap-2">
          <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab; display:flex; align-items:center;"><i class="bi bi-grip-vertical"></i></span>
          <textarea name="sdgs[]" class="form-control cis-textarea autosize flex-grow-1"></textarea>
          <input type="hidden" name="code[]" value="">
          <button type="button" class="btn btn-sm btn-outline-danger btn-delete-cdio ms-2" title="Delete SDG"><i class="bi bi-trash"></i></button>
        </div>
      </td>
    `;
    return newRow;
  }

  function addRow(afterRow = null) {
    const newRow = createNewRow(); if (afterRow && afterRow.parentElement) { if (afterRow.nextSibling) afterRow.parentElement.insertBefore(newRow, afterRow.nextSibling); else afterRow.parentElement.appendChild(newRow); } else { list.appendChild(newRow); }
    try { initAutosize(); } catch (e) {}
    updateVisibleCodes(); const ta = newRow.querySelector('textarea.autosize'); if (ta) ta.focus(); return newRow;
  }

  window.saveSdgOrder = async function() {
    const rows = Array.from(list.querySelectorAll('tr')).filter(r => r.getAttribute('data-id'));
    const positions = rows.map((r, idx) => ({ id: r.getAttribute('data-id'), position: idx + 1 }));
    const syllabusId = list.dataset.syllabusId; if (!syllabusId) return { ok: false };
    const res = await fetch(`/faculty/syllabi/${syllabusId}/sdgs/reorder`, {
      method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
      body: JSON.stringify({ positions })
    });
    return res.json();
  };

  try { window.addSdgRow = function(afterRowSelector = null) { const after = afterRowSelector ? document.querySelector(afterRowSelector) : null; return addRow(after); }; } catch (e) {}
  try { window.removeSdgRow = function(rowSelector) {
    const row = (typeof rowSelector === 'string') ? document.querySelector(rowSelector) : rowSelector; if (!row) return false;
    const id = row.getAttribute && row.getAttribute('data-id');
    if (!id || id.startsWith('new-')) { row.remove(); updateVisibleCodes(); return Promise.resolve({ message: 'row removed' }); }
    const sdgId = row.getAttribute('data-sdg-id'); const syllabusId = list.dataset.syllabusId;
    const deleteUrl = sdgId && syllabusId ? `/faculty/syllabi/${syllabusId}/sdgs/${sdgId}` : `/faculty/syllabi/sdgs/${id}`;
    return fetch(deleteUrl, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
      .then(async (res) => {
        if (!res.ok) {
          const msg = await res.text().catch(() => 'Delete failed'); throw new Error(msg);
        }
        return res.json().catch(() => ({}));
      })
      .then((data) => {
        try { row.remove(); } catch (e) { row.parentElement && row.parentElement.removeChild(row); }
        try { updateVisibleCodes(); } catch (e) {}
        try { document.dispatchEvent(new CustomEvent('sdg:detached', { detail: { sdg_id: sdgId, pivot: id } })); } catch (e) {}
        try { if (window.showSdgToast) window.showSdgToast('SDG removed', data.message || 'SDG removed.'); else alert(data.message || 'SDG removed.'); } catch (e) {}
        return data;
      });
  }; } catch (e) {}

  (function ensureAtLeastOne() { const rows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="sdgs[]"]') || r.querySelector('.cdio-badge')); if (rows.length === 0) { addRow(); } })();

  updateVisibleCodes(); try { initAutosize(); } catch (e) {}

  let _lastAdd = 0;
  document.addEventListener('click', (ev) => {
    const btn = ev.target.closest && ev.target.closest('.add-cdio'); if (!btn) return; const now = Date.now(); if (now - _lastAdd < 300) return; _lastAdd = now; const focused = document.activeElement; const after = focused ? focused.closest && focused.closest('tr') : null; addRow(after);
  });

  function bindGlobalUnsaved() {
    function checkAnyChanged() {
      const anyChanged = Array.from(list.querySelectorAll('textarea.autosize')).some(t => (t.value || '') !== (t.getAttribute('data-original') || ''));
      const top = document.getElementById('unsaved-sdgs'); if (top) top.classList.toggle('d-none', !anyChanged);
      if (anyChanged) { try { markDirty('unsaved-sdgs'); } catch (e) {} }
      updateUnsavedCount();
    }
    list.querySelectorAll('textarea.autosize').forEach((ta) => { ta.addEventListener('input', checkAnyChanged); ta.addEventListener('change', checkAnyChanged); });
  }
  bindGlobalUnsaved();

  // Debounced autosave for SDG textarea changes (per-row)
  (function bindAutosave() {
    const timers = new WeakMap();
    function scheduleSave(row, ta) {
      // only save persisted rows
      const pivotId = row.getAttribute('data-id'); if (!pivotId || pivotId.startsWith('new-')) return;
      // clear existing timer
      if (timers.has(row)) clearTimeout(timers.get(row));
      const t = setTimeout(async () => {
        try {
          const syllabusId = list.dataset.syllabusId;
          const url = `/faculty/syllabi/${syllabusId}/sdgs/update/${pivotId}`;
          const res = await fetch(url, {
            method: 'PUT', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: JSON.stringify({ description: ta.value })
          });
          if (!res.ok) {
            const txt = await res.text().catch(() => 'Save failed'); throw new Error(txt);
          }
          const json = await res.json().catch(() => ({}));
          // update original snapshot and unsaved UI
          ta.setAttribute('data-original', ta.value || '');
          try { const pill = document.getElementById('unsaved-sdgs'); if (pill) pill.classList.add('d-none'); } catch (e) {}
          try { if (window.showSdgToast) window.showSdgToast('Saved', 'SDG saved'); } catch (e) {}
          try { updateUnsavedCount(); } catch (e) {}
          try { document.dispatchEvent(new CustomEvent('sdg:updated', { detail: { pivot: pivotId, sdg_id: row.getAttribute('data-sdg-id') } })); } catch (e) {}
        } catch (err) {
          console.error('Autosave SDG failed', err);
          try { if (window.showSdgToast) window.showSdgToast('Error', err.message || 'Save failed', true); } catch (e) {}
        }
      }, 800);
      timers.set(row, t);
    }

    list.addEventListener('input', (e) => {
      const ta = e.target; if (!ta || ta.tagName !== 'TEXTAREA') return;
      const row = ta.closest('tr'); if (!row) return;
      scheduleSave(row, ta);
    });
  })();

  window.saveSdg = async function() {
    const form = document.getElementById('sdgForm'); if (! form) return { ok: true };
    const tbody = document.querySelector('#syllabus-sdg-sortable');
    const items = [];
    Array.from(tbody.querySelectorAll('tr')).forEach((tr, idx) => {
      const desc = tr.querySelector('textarea[name="sdgs[]"]')?.value || '';
      const code = tr.querySelector('input[name="code[]"]')?.value || (`SDG${idx+1}`);
      items.push({ id: tr.getAttribute('data-id') || null, code, description: desc, position: idx + 1 });
    });
    try {
      console.debug('saveSdg payload', items, 'form.action=', form.action);
      const res = await fetch(form.action, {
        method: 'PUT', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
        body: JSON.stringify({ sdgs: items }),
      });
      const data = await res.json();
      if (data && data.ok) {
        Array.from(tbody.querySelectorAll('textarea[name="sdgs[]"]')).forEach((ta, idx) => { ta.setAttribute('data-original', ta.value || ''); });
        const pill = document.getElementById('unsaved-sdgs'); if (pill) pill.classList.add('d-none'); try { updateUnsavedCount(); } catch (e) {}
      }
      return data;
    } catch (err) {
      console.error('saveSdg error', err); return { ok: false, message: err && err.message ? err.message : 'Network error' };
    }
  };
});
