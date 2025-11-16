// -----------------------------------------------------------------------------
// File: resources/js/faculty/syllabus-ilo.js
// Description: Minimal ILO behaviors (add/delete/renumber + autosize + drag reorder). Saving
//              is handled by window.saveIlo defined in syllabus.js.
// -----------------------------------------------------------------------------

import { initAutosize, updateUnsavedCount } from './syllabus';
import Sortable from 'sortablejs';

document.addEventListener('DOMContentLoaded', () => {
  const list = document.getElementById('syllabus-ilo-sortable');
  if (!list) return; // tolerate pages without ILO list

  function getIloRows() {
    return Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="ilos[]"]') || r.querySelector('.ilo-badge'));
  }

  function getCsrfToken() {
    try {
      return document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('#iloForm input[name="_token"], #syllabusForm input[name="_token"]')?.value
        || '';
    } catch (e) {
      return '';
    }
  }

  async function requestBackendDeletion(iloId) {
    const headers = { 'Accept': 'application/json' };
    const token = getCsrfToken();
    if (token) headers['X-CSRF-TOKEN'] = token;

    const res = await fetch(`/faculty/syllabi/ilos/${encodeURIComponent(iloId)}`, {
      method: 'DELETE',
      headers,
      credentials: 'same-origin',
    });

    if (!res.ok) {
      let message = 'Failed to delete ILO.';
      try {
        const ct = res.headers.get('content-type') || '';
        if (ct.includes('application/json')) {
          const body = await res.json();
          message = body?.message || message;
        } else {
          message = await res.text() || message;
        }
      } catch (e) { /* ignored */ }
      throw new Error(message);
    }

    return res.json().catch(() => ({}));
  }

  async function deleteRowAndPersist(row) {
    if (!row) return false;
    const rows = getIloRows();
    if (rows.length <= 1) return false; // keep at least one row visible

    const rawId = row.getAttribute('data-id');
    const hasServerId = rawId && /^\d+$/.test(rawId);

    if (hasServerId) {
      try {
        await requestBackendDeletion(rawId);
      } catch (err) {
        console.error('Failed to delete ILO on server', err);
        alert(err?.message || 'Failed to delete ILO.');
        return false;
      }
    }

    row.remove();
    renumber();
    return true;
  }

  function renumber() {
    const rows = getIloRows();
    const codes = [];
    rows.forEach((row, i) => {
      const code = `ILO${i + 1}`;
      codes.push(code);
      const badge = row.querySelector('.ilo-badge'); if (badge) badge.textContent = code;
      const codeInput = row.querySelector('input[name="code[]"]'); if (codeInput) codeInput.value = code;
    });
    // mark ILOs as unsaved and update unsaved counter
    try { const pill = document.getElementById('unsaved-ilos'); if (pill) pill.classList.remove('d-none'); } catch (e) { /* noop */ }
    try { updateUnsavedCount(); } catch (e) { /* noop */ }
    
    // Dispatch event for AT module to sync columns
    try {
      document.dispatchEvent(new CustomEvent('ilo:changed', { 
        detail: { 
          count: rows.length,
          codes: codes
        } 
      }));
    } catch (e) { /* noop */ }
  }

  function createRow() {
    const tr = document.createElement('tr');
    tr.setAttribute('data-id', `new-${Date.now()}`);
    tr.innerHTML = `
      <td class="text-center align-middle">
        <div class="ilo-badge fw-semibold"></div>
      </td>
      <td>
        <div class="d-flex align-items-center gap-2">
          <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;">
            <i class="bi bi-grip-vertical"></i>
          </span>
          <textarea name="ilos[]" class="cis-textarea cis-field autosize flex-grow-1" placeholder="-" rows="1" style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;"></textarea>
          <input type="hidden" name="code[]" value="">
          <button type="button" class="btn btn-sm btn-outline-danger btn-delete-ilo ms-2" title="Delete ILO" style="display:none;">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </td>`;
    return tr;
  }

  function addRow(afterRow = null) {
    const row = createRow();
    if (afterRow && afterRow.parentElement) {
      if (afterRow.nextSibling) afterRow.parentElement.insertBefore(row, afterRow.nextSibling);
      else afterRow.parentElement.appendChild(row);
    } else {
      list.appendChild(row);
    }
    try { initAutosize(); } catch (e) { /* noop */ }
    renumber();
    const ta = row.querySelector('textarea.autosize'); if (ta) ta.focus();
    try { updateUnsavedCount(); } catch (e) { /* noop */ }
    return row;
  }

  // Header buttons (optional)
  const addBtn = document.getElementById('ilo-add-header');
  if (addBtn) addBtn.addEventListener('click', () => addRow(null));

  const removeBtn = document.getElementById('ilo-remove-header');
  if (removeBtn) removeBtn.addEventListener('click', async () => {
    const rows = getIloRows();
    if (!rows.length) return;
    // Restrict: only remove unsaved rows (non-numeric data-id). Find the last unsaved one.
    let target = null;
    for (let i = rows.length - 1; i >= 0; i--) {
      const id = rows[i].getAttribute('data-id');
      if (!id || !/^\d+$/.test(id)) { target = rows[i]; break; }
    }
    if (!target) {
      // No unsaved row to remove; ignore.
      return;
    }
    // Just remove the DOM row without calling backend (it's not saved yet)
    target.remove();
    renumber();
  });

  // Delegated delete button handling
  list.addEventListener('click', async (ev) => {
    const btn = ev.target && ev.target.closest && ev.target.closest('.btn-delete-ilo');
    if (!btn) return;
    const row = btn.closest('tr');
    await deleteRowAndPersist(row);
  });

  // After a successful save, reveal delete buttons on rows that now have numeric ids
  document.addEventListener('syllabusSaved', () => {
    try {
      getIloRows().forEach((r, i) => {
        const id = r.getAttribute('data-id');
        const del = r.querySelector('.btn-delete-ilo');
        if (!del) return;
        // Show delete for saved rows (numeric id); hide for unsaved rows
        del.style.display = (id && /^\d+$/.test(id)) ? '' : 'none';
      });
    } catch (e) { /* noop */ }
  });

  // Keyboard: Backspace on empty textarea at caret 0 removes row (not first, and keep >= 1)
  list.addEventListener('keydown', async (e) => {
    const el = e.target;
    if (!el || el.tagName !== 'TEXTAREA') return;
    if (e.key !== 'Backspace') return;
    const val = el.value || '';
    const selStart = (typeof el.selectionStart === 'number') ? el.selectionStart : 0;
    if (val.trim() !== '' || selStart !== 0) return;
    const row = el.closest('tr');
    const rows = getIloRows();
    const idx = rows.indexOf(row);
    if (rows.length <= 1) return;
    e.preventDefault();
    const prev = rows[idx - 1] || rows[idx + 1] || null;
    const deleted = await deleteRowAndPersist(row);
    if (!deleted) return;
    const targetRow = prev || getIloRows()[0];
    const pta = targetRow ? targetRow.querySelector('textarea') : null;
    if (pta) setTimeout(() => pta.focus(), 10);
  });

  // Initialize Sortable for drag-and-drop reordering
  if (list && typeof Sortable !== 'undefined') {
    Sortable.create(list, {
      handle: '.drag-handle',
      animation: 150,
      ghostClass: 'sortable-ghost',
      onEnd: function() {
        renumber();
        try { updateUnsavedCount(); } catch (e) { /* noop */ }
      }
    });
  }

  // Initial run
  renumber();
  try { initAutosize(); } catch (e) { /* noop */ }
});

// Persist ILOs (create/update + order). Inserts new rows typed before save.
// Exposed globally so the main Save button can call it first.
window.saveIlo = async function saveIlo() {
  const list = document.getElementById('syllabus-ilo-sortable');
  if (!list) return { message: 'No ILO list present' };

  function getSyllabusId() {
    try { const id = list.getAttribute('data-syllabus-id'); if (id) return id; } catch (e) {}
    try {
      const form = document.getElementById('syllabusForm') || document.getElementById('iloForm');
      const act = (form && form.action) ? form.action : '';
      const m = act.match(/\/faculty\/syllabi\/([^\/?#]+)/);
      if (m) return decodeURIComponent(m[1]);
    } catch (e) {}
    try {
      const idInput = document.querySelector('[name="id"], input[name="syllabus_id"], input[name="syllabus"]');
      if (idInput && idInput.value) return idInput.value;
    } catch (e) {}
    return '';
  }

  const syllabusId = getSyllabusId();
  if (!syllabusId) throw new Error('Cannot determine syllabus id for ILO save');

  // Collect visible rows and ensure sequential codes before reading values
  const rows = Array.from(list.querySelectorAll('tr'))
    .filter(r => r.querySelector('textarea[name="ilos[]"]') || r.querySelector('.ilo-badge'));

  rows.forEach((row, i) => {
    const code = `ILO${i + 1}`;
    const badge = row.querySelector('.ilo-badge'); if (badge) badge.textContent = code;
    const codeInput = row.querySelector('input[name="code[]"]'); if (codeInput) codeInput.value = code;
  });

  const descriptors = rows.map((row, index) => {
    const rawId = row.getAttribute('data-id') || '';
    const id = (/^\d+$/.test(rawId)) ? Number(rawId) : null;
    const code = row.querySelector('input[name="code[]"]')?.value || `ILO${index + 1}`;
    const ta = row.querySelector('textarea[name="ilos[]"]');
    const description = ta ? (ta.value || '') : '';
    const hasContent = (description.trim().length > 0);
    return { row, entry: { id, code, description, position: index + 1 }, hasContent };
  });

  const payloadIlos = descriptors
    .filter(d => d.entry.id || d.hasContent)
    .map(d => d.entry);

  // If there is nothing to save, short-circuit
  if (!payloadIlos.length) return { message: 'No ILO changes' };

  const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
  try {
    const token = document.querySelector('meta[name="csrf-token"]')?.content
      || document.querySelector('#iloForm input[name="_token"], #syllabusForm input[name="_token"]')?.value
      || '';
    if (token) headers['X-CSRF-TOKEN'] = token;
  } catch (e) { /* noop */ }

  const url = (window.syllabusBasePath || '/faculty/syllabi') + `/${encodeURIComponent(syllabusId)}/ilos`;

  const pendingNew = descriptors.filter(d => !d.entry.id && d.hasContent).map(d => d.row);

  const res = await fetch(url, {
    method: 'PUT',
    headers,
    credentials: 'same-origin',
    body: JSON.stringify({ ilos: payloadIlos })
  });
  if (!res.ok) {
    let body = null; let text = '';
    try {
      const ct = res.headers.get('content-type') || '';
      if (ct.includes('application/json')) { body = await res.json(); }
      else { text = await res.text(); }
    } catch (e) { /* noop */ }
    const msg = (body && (body.message || (body.errors && JSON.stringify(body.errors)))) || text || 'Failed to save ILOs';
    throw new Error(msg);
  }
  const data = await res.json().catch(() => ({}));

  // Assign server IDs back to newly-created rows in DOM
  if (Array.isArray(data.created_ids) && data.created_ids.length) {
    const apply = pendingNew.slice(0, data.created_ids.length);
    apply.forEach((row, i) => {
      const nid = data.created_ids[i];
      if (row && nid) {
        row.setAttribute('data-id', String(nid));
        // Immediately reveal the delete button for newly saved rows
        const delBtn = row.querySelector('.btn-delete-ilo');
        if (delBtn) delBtn.style.display = '';
      }
    });
  }

  // Update originals and unsaved indicators
  try { document.getElementById('unsaved-ilos')?.classList.add('d-none'); } catch (e) {}
  try { list.querySelectorAll('textarea[name="ilos[]"]').forEach(ta => ta.setAttribute('data-original', ta.value || '')); } catch (e) {}
  try { updateUnsavedCount(); } catch (e) {}
  return data;
};
