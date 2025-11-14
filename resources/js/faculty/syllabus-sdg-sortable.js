// Frontend helpers for syllabus SDG list: sortable, renumbering, keyboard shortcuts
import Sortable from 'sortablejs';
import { apiFetch, showToast } from '../lib/api';
import { initAutosize, markDirty, updateUnsavedCount } from './syllabus';

document.addEventListener('DOMContentLoaded', () => {
  const list = document.getElementById('syllabus-sdg-sortable');
  if (!list) return;

  // Ensure a safe shim exists for window.markAsUnsaved in case the main syllabus
  // script hasn't exposed it due to load order. The real implementation updates
  // the global unsaved modules set; our shim performs a minimal UI update so
  // the top Save button becomes clickable.
  try {
    if (!window.markAsUnsaved) {
      window.markAsUnsaved = function(module) {
        try { console.debug && console.debug('markAsUnsaved shim called for', module); } catch (e) {}
        try { const pill = document.getElementById('unsaved-' + (module || 'sdgs')); if (pill) pill.classList.remove('d-none'); } catch (e) {}
        try { if (window.updateUnsavedCount) window.updateUnsavedCount(); } catch (e) { try { updateUnsavedCount && updateUnsavedCount(); } catch (ee) {} }
        try { const saveBtn = document.getElementById('syllabusSaveBtn'); if (saveBtn) { saveBtn.disabled = false; saveBtn.classList.add('btn-warning'); saveBtn.classList.remove('btn-danger'); } } catch (e) {}
      };
    }
  } catch (e) { /* noop */ }

  function updateVisibleCodes() {
    // Only count rows that are direct children of tbody and contain SDG textarea or badge and are visible
    const rows = Array.from(list.children).filter(r => {
      if (!r || r.nodeType !== Node.ELEMENT_NODE) return false;
      if (r.classList && r.classList.contains('d-none')) return false;
      if (r.id && r.id === 'sdg-template-row') return false;
      if (r.querySelector && (r.querySelector('textarea[name="sdgs[]"]') || r.querySelector('.cdio-badge'))) return true;
      return false;
    });
    rows.forEach((row, index) => {
      const newCode = `SDG${index + 1}`;
      const badge = row.querySelector('.cdio-badge'); if (badge) badge.textContent = newCode;
      const codeInput = row.querySelector('input[name="code[]"]'); if (codeInput) codeInput.value = newCode;
      // also store the computed code on the row for quick lookup
      try { row.setAttribute('data-code', newCode); } catch (e) {}
    });

    // hide delete button on first row (match CDIO behavior)
    rows.forEach((row, idx) => {
      const btn = row.querySelector('.btn-delete-cdio'); if (!btn) return; btn.style.display = idx === 0 ? 'none' : '';
    });

    try { if (window.markAsUnsaved) window.markAsUnsaved('sdgs'); } catch (e) {}
    try { updateUnsavedCount(); } catch (e) {}
  }

  // Make renumber function available globally so other modules can call it
  try { window.updateVisibleCodes = updateVisibleCodes; } catch (e) {}

  // SDG numbering is auto-managed by updateVisibleCodes() and persistent order APIs; no header sorting.

  Sortable.create(list, {
    handle: '.drag-handle',
    animation: 150,
    fallbackOnBody: true,
    draggable: 'tr',
    swapThreshold: 0.65,
    onStart(evt) {
      try { evt.item && evt.item.classList.add('dragging'); } catch (e) {}
  try { console.debug && console.debug('sdg-sortable:onStart', evt && evt.item && evt.item.getAttribute && evt.item.getAttribute('data-id')); } catch (e) {}
    },
    onEnd(evt) {
  try { console.debug && console.debug('sdg-sortable:onEnd', evt && evt.item && evt.item.getAttribute && evt.item.getAttribute('data-id')); } catch (e) {}
      try { evt.item && evt.item.classList.remove('dragging'); } catch (e) {}
      // Update numbering visually and mark as unsaved so user can Save from the top button.
      // Use the higher-level markAsUnsaved helper when available because markDirty
      // is intentionally suppressed while a save-lock (_syllabusSaveLock) is active
      // which can cause reorders to be ignored. markAsUnsaved adds the module to
      // the global unsaved set and enables the top Save button reliably.
      updateVisibleCodes();
      try {
        // follow CDIO pattern: prefer markAsUnsaved, then markDirty for UI parity
        if (window.markAsUnsaved) window.markAsUnsaved('sdgs');
      } catch (e) {}
      try { if (typeof markDirty === 'function') markDirty('unsaved-sdgs'); } catch (e) {}
      try {
        // ensure Unsaved pill is visible and update the unsaved count
        const pill = document.getElementById('unsaved-sdgs'); if (pill) pill.classList.remove('d-none');
        try { if (window.updateUnsavedCount) window.updateUnsavedCount(); else updateUnsavedCount && updateUnsavedCount(); } catch (e) {}
        try { console.debug && console.debug('sdg-sortable:onEnd: markAsUnsaved/maskDirty called', { hasMarkAsUnsaved: !!window.markAsUnsaved, hasMarkDirty: (typeof markDirty === 'function'), hasUpdateUnsavedCount: !!window.updateUnsavedCount }); } catch (e) {}
        try { const saveBtn = document.getElementById('syllabusSaveBtn'); if (saveBtn) { saveBtn.disabled = false; saveBtn.classList.add('btn-warning'); saveBtn.classList.remove('btn-danger'); } } catch (e) {}
      } catch (e) {}
      // Persist order immediately so DB reflects the new sort order
      try {
        if (window.saveSdgOrder && typeof window.saveSdgOrder === 'function') {
          // fire-and-forget; we don't want to block UI but log failures
          window.saveSdgOrder().catch((err) => { try { console.warn('saveSdgOrder failed', err); } catch(e){} });
        }
      } catch (e) {}

      // safety: try again after a short delay in case global helpers are initialized shortly after this module
      try {
        setTimeout(() => {
          try { console.debug && console.debug('sdg-sortable: delayed markAsUnsaved check (start)', { hasMarkAsUnsaved: !!window.markAsUnsaved, hasMarkDirty: (typeof markDirty === 'function'), hasUpdateUnsavedCount: !!window.updateUnsavedCount }); } catch (e) {}
          try { if (window.markAsUnsaved) window.markAsUnsaved('sdgs'); } catch (e) {}
          try { if (typeof markDirty === 'function') markDirty('unsaved-sdgs'); } catch (e) {}
          try { if (window.updateUnsavedCount) window.updateUnsavedCount(); else updateUnsavedCount && updateUnsavedCount(); } catch (e) {}
          try { const saveBtn = document.getElementById('syllabusSaveBtn'); if (saveBtn) { saveBtn.disabled = false; saveBtn.classList.add('btn-warning'); saveBtn.classList.remove('btn-danger'); } } catch (e) {}
          try { console.debug && console.debug('sdg-sortable: delayed markAsUnsaved check (end)'); } catch (e) {}
        }, 60);
      } catch (e) {}
    // also emit a cross-module event so the main syllabus script can always react
    try { document.dispatchEvent(new CustomEvent('sdg:reordered', { detail: { source: 'syllabus-sdg-sortable' } })); } catch (e) {}
  // ultimate fallback: ensure the central save state is set to dirty so the button becomes clickable
  try { if (window.setSyllabusSaveState && typeof window.setSyllabusSaveState === 'function') { window.setSyllabusSaveState('dirty'); console.debug && console.debug('sdg-sortable: invoked setSyllabusSaveState(dirty)'); } } catch (e) {}
  try { const pill = document.getElementById('unsaved-sdgs'); if (pill) pill.classList.remove('d-none'); } catch (e) {}
  try { const saveBtn = document.getElementById('syllabusSaveBtn'); if (saveBtn) { saveBtn.disabled = false; saveBtn.classList.add('btn-warning'); saveBtn.classList.remove('btn-danger'); saveBtn.style.pointerEvents = ''; } } catch (e) {}

  // Defensive: clear any stale save-lock that may prevent markDirty/markAsUnsaved from taking effect
  try { if (typeof window._syllabusSaveLock !== 'undefined') { window._syllabusSaveLock = false; console.debug && console.debug('sdg-sortable: cleared _syllabusSaveLock'); } } catch (e) {}

  // Force-enable save again after short delays to handle load-order races or other handlers
  try {
    setTimeout(() => {
      try { const sb = document.getElementById('syllabusSaveBtn'); if (sb) { sb.disabled = false; sb.style.pointerEvents = 'auto'; sb.classList.add('btn-warning'); sb.classList.remove('btn-danger'); } } catch (e) {}
      try { if (window.setSyllabusSaveState && typeof window.setSyllabusSaveState === 'function') window.setSyllabusSaveState('dirty'); } catch (e) {}
      try { if (window.updateUnsavedCount) window.updateUnsavedCount(); } catch (e) {}
    }, 120);
  } catch (e) {}

  try {
    setTimeout(() => {
      try { const sb = document.getElementById('syllabusSaveBtn'); if (sb) { sb.disabled = false; sb.style.pointerEvents = 'auto'; } } catch (e) {}
      try { if (window.setSyllabusSaveState && typeof window.setSyllabusSaveState === 'function') window.setSyllabusSaveState('dirty'); } catch (e) {}
    }, 400);
  } catch (e) {}
    }
  });

  try {
    if (window.MutationObserver) {
      const mo = new MutationObserver((mutations) => {
        let shouldUpdate = false;
        for (const m of mutations) {
          if (m.type === 'childList' && (m.addedNodes.length || m.removedNodes.length)) { shouldUpdate = true; break; }
        }
        if (shouldUpdate) Promise.resolve().then(() => { try { initAutosize(); } catch (e) {} updateVisibleCodes(); try { updateUnsavedCount(); } catch (e) {} });
          // ensure new rows' title inputs are wired for unsaved detection
          if (shouldUpdate) Promise.resolve().then(() => {
            try { initAutosize(); } catch (e) {}
            updateVisibleCodes();
            try { updateUnsavedCount(); } catch (e) {}
            // wire title inputs
            Array.from(list.querySelectorAll('input.sdg-title-input')).forEach((ti) => {
              if (!ti.__sdgBound) { ti.addEventListener('input', () => { try { markDirty('unsaved-sdgs'); } catch (e) {} try { updateUnsavedCount(); } catch (e) {} }); ti.__sdgBound = true; }
            });
          });
      });
      mo.observe(list, { childList: true, subtree: false });
    }
  } catch (e) { /* noop */ }

  // Removed keyboard shortcut handler (Ctrl/Cmd+Backspace row delete) per request.

  list.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-delete-cdio'); if (!btn) return; const row = btn.closest('tr'); const allRows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="sdgs[]"]') || r.querySelector('.cdio-badge'));
    const id = row.getAttribute('data-id'); if (!id || id.startsWith('new-')) { try { row.remove(); } catch (e) { row.remove(); } updateVisibleCodes(); return; }
    if (!confirm('Are you sure you want to delete this SDG?')) return;
    const entryId = row.getAttribute('data-id');
    const sdgId = row.getAttribute('data-sdg-id');
    const syllabusId = list.dataset.syllabusId;
    let deleteUrl;
    if (entryId && syllabusId) {
      deleteUrl = `/faculty/syllabi/${syllabusId}/sdgs/entry/${entryId}`;
    } else if (sdgId && syllabusId) {
      deleteUrl = `/faculty/syllabi/${syllabusId}/sdgs/${sdgId}`;
    } else {
      deleteUrl = `/faculty/syllabi/sdgs/${entryId}`;
    }
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
  // Persist order after delete
  try { if (window.saveSdgOrder) window.saveSdgOrder(); else if (window.persistSdgOrder) window.persistSdgOrder(); } catch (e) {}
  try {
    const titleEl = row.querySelector('input[name="title[]"]') || row.querySelector('.sdg-title') || row.querySelector('textarea[name="sdgs[]"]');
    const title = titleEl ? (titleEl.value || titleEl.textContent || '') : null;
    document.dispatchEvent(new CustomEvent('sdg:detached', { detail: { sdg_id: sdgId, title, pivot: id } }));
  } catch (e) {}
  try { updateUnsavedCount(); } catch (e) {}
  alert(data.message || 'SDG deleted.');
    })
    .catch(err => { console.error(err); alert('Failed to delete SDG.'); });
  });

  // Removed blur-based inline save for SDG textareas. Saving is now handled
  // by the debounced autosave bound to input events above, or by an explicit
  // user-initiated save action. This prevents accidental saves when users
  // accidentally blur a field.

  function createNewRow() {
    const timestamp = Date.now(); const newRow = document.createElement('tr'); newRow.setAttribute('data-id', `new-${timestamp}`);
    newRow.innerHTML = `
      <td class="text-center align-middle"><div class="cdio-badge"></div></td>
      <td>
        <div class="d-flex align-items-center gap-2">
          <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab; display:flex; align-items:center;"><i class="bi bi-grip-vertical"></i></span>
          <div class="flex-grow-1 sdg-box sdg-box-strong">
            <input type="text" name="title[]" class="form-control form-control-sm sdg-title-input fw-semibold" value="" data-original="" style="background: transparent; border: none; padding: 0; margin-bottom: .15rem;" />
            <textarea name="sdgs[]" class="form-control cis-textarea autosize flex-grow-1"></textarea>
          </div>
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
    const rows = Array.from(list.querySelectorAll('tr')).map(r => r.getAttribute('data-id')).filter(Boolean);
    // Only keep numeric persisted ids (exclude 'new-' prefixed or other non-numeric values)
    const ids = rows.map(id => String(id || '')).filter(id => /^[0-9]+$/.test(id)).map(id => parseInt(id, 10));
    const syllabusId = list.dataset.syllabusId; if (!syllabusId) return { ok: false, message: 'No syllabus id' };

    if (!ids.length) {
      try { showToast('Info', 'No persisted SDGs to reorder', false); } catch (e) {}
      return { ok: false, message: 'No valid persisted SDG ids to reorder' };
    }

    try {
      const res = await apiFetch(`/faculty/syllabi/${syllabusId}/sdgs/reorder`, { method: 'POST', body: { ids } });
      try { list.dataset.orderSnapshot = JSON.stringify(ids); } catch (e) {}
      try { showToast('Saved', 'SDG order saved'); } catch (e) {}
      return res;
    } catch (err) {
      try { console.error('saveSdgOrder error', err); } catch (e) {}
      try { showToast('Error', err && err.payload && err.payload.message ? err.payload.message : (err.message || 'Failed to save SDG order'), true); } catch (e) {}
      throw err;
    }
  };

  // Centralized removal handler used by other modules: removes row, renumbers, persists, shows toast and re-adds option
  try {
    window.handleSdgRowRemoval = function(row, sdgId = null, message = 'SDG removed') {
      try {
        // keep title for re-adding to select
        const titleEl = row.querySelector('input[name="title[]"]') || row.querySelector('.sdg-title') || row.querySelector('textarea[name="sdgs[]"]');
        const title = titleEl ? (titleEl.value || titleEl.textContent || '') : '';
        // remove row from DOM
        try { row.remove(); } catch (e) { row.parentElement && row.parentElement.removeChild(row); }
        // renumber visible rows
        try { if (window.updateVisibleCodes) window.updateVisibleCodes(); } catch (e) {}
        // persist order
        try { if (window.saveSdgOrder) window.saveSdgOrder(); else if (window.persistSdgOrder) window.persistSdgOrder(); } catch (e) {}
        // re-add to modal checkbox list (if present)
        try {
          const list = document.querySelector('.sdg-checkbox-list');
          if (list && sdgId) {
            // avoid duplicate
            if (!list.querySelector(`#sdg_check_${sdgId}`)) {
              const wrapper = document.createElement('div'); wrapper.className = 'form-check mb-1';
              const input = document.createElement('input'); input.name = 'sdg_ids[]'; input.className = 'form-check-input sdg-checkbox'; input.type = 'checkbox'; input.id = `sdg_check_${sdgId}`; input.value = sdgId;
              const label = document.createElement('label'); label.className = 'form-check-label small'; label.htmlFor = input.id; label.textContent = title || `SDG ${sdgId}`;
              wrapper.appendChild(input); wrapper.appendChild(label); list.appendChild(wrapper);
            }
          }
        } catch (e) {}
        // toast and event
        try { if (window.showSdgToast) window.showSdgToast(message, ''); else if (window.showToast) window.showToast('SDG removed', message); } catch (e) {}
        try { document.dispatchEvent(new CustomEvent('sdg:detached', { detail: { sdg_id: sdgId, title, pivot: row.getAttribute && row.getAttribute('data-id') } })); } catch (e) {}
      } catch (e) { console.error('handleSdgRowRemoval error', e); }
    };
  } catch (e) {}

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

  // Do not auto-insert an empty SDG row when none exist.
  // Previously this IIFE would call addRow() if the list was empty; removing that behavior
  // ensures no empty SDG1 placeholder appears on initial load. The template row (`#sdg-template-row`)
  // is kept for user-initiated adds (modal/keyboard).

  updateVisibleCodes(); try { initAutosize(); } catch (e) {}

  let _lastAdd = 0;
  document.addEventListener('click', (ev) => {
    const btn = ev.target.closest && ev.target.closest('.add-cdio'); if (!btn) return; const now = Date.now(); if (now - _lastAdd < 300) return; _lastAdd = now; const focused = document.activeElement; const after = focused ? focused.closest && focused.closest('tr') : null; addRow(after);
  });

  function bindGlobalUnsaved() {
    function checkAnyChanged() {
      const descChanged = Array.from(list.querySelectorAll('textarea.autosize')).some(t => (t.value || '') !== (t.getAttribute('data-original') || ''));
      const titleChanged = Array.from(list.querySelectorAll('input.sdg-title-input')).some(i => (i.value || '') !== (i.getAttribute('data-original') || ''));
      const anyChanged = descChanged || titleChanged;
      const top = document.getElementById('unsaved-sdgs'); if (top) top.classList.toggle('d-none', !anyChanged);
      if (anyChanged) { try { markDirty('unsaved-sdgs'); } catch (e) {} }
      updateUnsavedCount();
    }
    // wire existing textareas and title inputs
    list.querySelectorAll('textarea.autosize').forEach((ta) => { ta.addEventListener('input', checkAnyChanged); ta.addEventListener('change', checkAnyChanged); });
    list.querySelectorAll('input.sdg-title-input').forEach((ti) => { ti.addEventListener('input', checkAnyChanged); ti.addEventListener('change', checkAnyChanged); });
    // observe DOM changes to bind newly added title inputs/textarea
    if (window.MutationObserver) {
      const mo = new MutationObserver((mutations) => {
        let added = false;
        for (const m of mutations) { if (m.addedNodes && m.addedNodes.length) { added = true; break; } }
        if (added) Promise.resolve().then(() => {
          Array.from(list.querySelectorAll('textarea.autosize')).forEach((ta) => { if (!ta.__sdgBound) { ta.addEventListener('input', checkAnyChanged); ta.addEventListener('change', checkAnyChanged); ta.__sdgBound = true; } });
          Array.from(list.querySelectorAll('input.sdg-title-input')).forEach((ti) => { if (!ti.__sdgBound) { ti.addEventListener('input', checkAnyChanged); ti.addEventListener('change', checkAnyChanged); ti.__sdgBound = true; } });
          checkAnyChanged();
        });
      });
      try { mo.observe(list, { childList: true, subtree: true }); } catch (e) {}
    }
  }
  bindGlobalUnsaved();

  // Header buttons: if add button is configured to open modal, do not addRow here
  try {
    const addBtn = document.getElementById('sdg-add-header');
    if (addBtn && addBtn.getAttribute('data-bs-toggle') === 'modal') {
      // modal-managed; no inline add
    } else if (addBtn) {
      addBtn.addEventListener('click', () => addRow(null));
    }
    // remove header may not exist anymore; keep a safe handler if present
    const remBtn = document.getElementById('sdg-remove-header');
    if (remBtn) remBtn.addEventListener('click', () => {
      const rows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="sdgs[]"]') || r.querySelector('.cdio-badge'));
      if (rows.length > 0) { rows[rows.length - 1].remove(); updateVisibleCodes(); }
    });
  } catch (e) {}

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

  // End autosave binding
})();
