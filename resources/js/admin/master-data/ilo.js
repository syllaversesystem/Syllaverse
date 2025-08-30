// -----------------------------------------------------------------------------
// * File: resources/js/admin/master-data/ilo.js
// * Description: ILO – SO-style table + AJAX dropdown + draggable reorder + Save Order + CRUD
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-08-18] Initial creation – consolidated list fetch + CRUD + Sortable with Save Order.
// [2025-08-18] FIX – Save Order didn't enable after drag (clone/handler mismatch). Now toggles the
//              active (cloned) save button used for the current DOM.
// -----------------------------------------------------------------------------

import Sortable from 'sortablejs';
import feather from 'feather-icons';

const $  = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

let currentCourseId = null;
let sortableInstance = null;

/* -------------------------- Table Rendering -------------------------- */

function rowHtml(ilo) {
  const desc = (ilo.description ?? '').toString().replace(/"/g, '&quot;');
  return `
    <tr data-id="${ilo.id}">
      <td class="text-muted">
        <i class="sv-row-grip bi bi-grip-vertical fs-5" title="Drag to reorder"></i>
      </td>
      <td class="sv-code fw-semibold">${ilo.code}</td>
      <td class="text-muted">${desc}</td>
      <td class="text-end">
        <button type="button"
                class="btn action-btn rounded-circle edit me-2 editIloBtn"
                data-bs-toggle="modal"
                data-bs-target="#editIloModal"
                data-id="${ilo.id}"
                data-code="${ilo.code}"
                data-description="${desc}"
                title="Edit" aria-label="Edit">
          <i data-feather="edit"></i>
        </button>
        <button type="button"
                class="btn action-btn rounded-circle delete deleteIloBtn"
                data-bs-toggle="modal"
                data-bs-target="#deleteIloModal"
                data-id="${ilo.id}"
                data-code="${ilo.code}"
                title="Delete" aria-label="Delete">
          <i data-feather="trash"></i>
        </button>
      </td>
    </tr>`;
}

function rebuildTable(ilos) {
  const table = $('#svTable-ilo');
  const tbody = table?.querySelector('tbody');
  const saveBtn = $('.sv-save-order-btn[data-sv-type="ilo"]');
  if (!table || !tbody || !saveBtn) return;

  table.setAttribute('data-course-id', currentCourseId || '');

  if (!ilos.length) {
    tbody.innerHTML = `
      <tr class="sv-empty-row">
        <td colspan="4">
          <div class="sv-empty">
            <h6>No ILOs found</h6>
            <p>Select a course and click the <i data-feather="plus"></i> button to add one.</p>
          </div>
        </td>
      </tr>`;
  } else {
    tbody.innerHTML = ilos.map(rowHtml).join('');
  }

  feather.replace();
  initSortable(tbody, saveBtn);
}

function updateVisibleCodes(tbody) {
  $$('.sv-code', tbody).forEach((cell, idx) => {
    cell.textContent = `ILO${idx + 1}`;
  });
}

function collectOrderedIds(tbody) {
  return $$('tr[data-id]', tbody).map(tr => Number(tr.getAttribute('data-id')));
}

/* --------------------- Sortable + Save Order (SO-style) --------------------- */

function setSaveEnabled(btn, enabled) {
  if (!btn) return;
  if (enabled) {
    btn.removeAttribute('disabled'); btn.setAttribute('aria-disabled', 'false');
  } else {
    btn.setAttribute('disabled', 'disabled'); btn.setAttribute('aria-disabled', 'true');
  }
}

function initSortable(tbody, saveBtn) {
  // destroy prior instance to avoid dup handlers
  try { sortableInstance?.destroy(); } catch (_) {}
  sortableInstance = null;

  // 🔁 Rebind Save (clone trick prevents multiple listeners)
  const activeSaveBtn = saveBtn.cloneNode(true);
  saveBtn.parentNode.replaceChild(activeSaveBtn, saveBtn);
  setSaveEnabled(activeSaveBtn, false);

  let dirty = false;

  // ✅ Make rows draggable; after DROP, enable Save on the ACTIVE button
  sortableInstance = Sortable.create(tbody, {
    animation: 150,
    handle: '.sv-row-grip',
    ghostClass: 'bg-light',
    onEnd: () => {
      updateVisibleCodes(tbody);
      dirty = true;
      setSaveEnabled(activeSaveBtn, true);  // << enable the correct (cloned) button
    },
  });

  // ✅ Save order
  activeSaveBtn.addEventListener('click', async () => {
    if (!dirty || !currentCourseId) return;

    const orderedIds = collectOrderedIds(tbody);
    const orig = activeSaveBtn.innerHTML;
    setSaveEnabled(activeSaveBtn, false);
    activeSaveBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span>Saving…`;

    try {
      const reorderUrl = activeSaveBtn.getAttribute('data-reorder-url') || '/admin/master-data/reorder/ilo';
      const res = await fetch(reorderUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf(),
          'Accept': 'application/json',
        },
        body: JSON.stringify({ ids: orderedIds, course_id: currentCourseId }),
      });

      const ok = res.ok;
      const data = await res.json().catch(() => ({}));
      if (!ok) throw new Error(data?.message || 'Failed to save ILO order.');

      dirty = false;
      window.showAlertOverlay?.('success', data.message || 'ILO order saved.');
    } catch (err) {
      console.error(err);
      window.showAlertOverlay?.('error', err.message || 'Network error saving order.');
      setSaveEnabled(activeSaveBtn, true);
    } finally {
      activeSaveBtn.innerHTML = orig;
    }
  });

  // initial sync
  updateVisibleCodes(tbody);
}

/* ---------------------------- Fetch + Filter ---------------------------- */

async function fetchIlos(courseId) {
  const res = await fetch(`/admin/master-data/ilos?course_id=${encodeURIComponent(courseId)}`, {
    headers: { 'Accept': 'application/json' }
  });
  if (!res.ok) throw new Error('Failed to load ILOs.');
  const data = await res.json();
  rebuildTable(Array.isArray(data.ilos) ? data.ilos : []);
}

function bootFilter() {
  const form = $('#iloFilterForm');
  if (!form) return;

  const select = form.querySelector('select[name="course_id"]');
  if (!select) return;

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const cid = select.value;
    currentCourseId = cid || null;
    if (!cid) { rebuildTable([]); return; }
    fetchIlos(cid).catch(err => {
      console.error(err);
      window.showAlertOverlay?.('error', 'Unable to load ILOs for the selected course.');
    });
  });

  select.addEventListener('change', () => form.requestSubmit ? form.requestSubmit() : form.submit());
}

/* ------------------------------- CRUD hooks ------------------------------- */

function bootAdd() {
  // Ensure the add modal has the course id
  document.addEventListener('show.bs.modal', (e) => {
    if (e.target?.id !== 'addIloModal') return;
    const input = $('#addIloCourseId');
    if (input) input.value = currentCourseId || '';
  });

  const form = $('#addIloForm');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!currentCourseId) {
      window.showAlertOverlay?.('error', 'Please select a course first.');
      return;
    }
    const submitBtn = form.querySelector('[type="submit"]');
    submitBtn?.setAttribute('disabled', 'disabled');

    try {
      const res = await fetch(form.action, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
        body: new FormData(form),
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(data?.message || 'Failed to add ILO.');

      await fetchIlos(currentCourseId);
      form.reset();
      window.bootstrap?.Modal.getInstance($('#addIloModal'))?.hide();
      window.showAlertOverlay?.('success', data.message || 'ILO added.');
    } catch (err) {
      console.error(err);
      window.showAlertOverlay?.('error', err.message || 'Network error while adding ILO.');
    } finally {
      submitBtn?.removeAttribute('disabled');
    }
  });
}

function bootEdit() {
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.editIloBtn');
    if (!btn) return;

    const id   = btn.getAttribute('data-id');
    const code = btn.getAttribute('data-code') || 'ILO?';
    const desc = btn.getAttribute('data-description') || '';

    $('#editIloForm')?.setAttribute('action', `/admin/master-data/ilo/${id}`);
    $('#editIloDescription').value = desc;
    const badge = $('#editIloCodeBadge'); if (badge) badge.textContent = code;
    const title = $('#editIloLabel'); if (title) title.textContent = `Edit ${code}`;
  });

  const form = $('#editIloForm');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const submitBtn = form.querySelector('[type="submit"]');
    submitBtn?.setAttribute('disabled', 'disabled');

    try {
      const res = await fetch(form.getAttribute('action'), {
        method: 'POST', // PUT via _method
        headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
        body: new FormData(form),
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(data?.message || 'Failed to update ILO.');

      await fetchIlos(currentCourseId);
      window.bootstrap?.Modal.getInstance($('#editIloModal'))?.hide();
      window.showAlertOverlay?.('success', data.message || 'ILO updated.');
    } catch (err) {
      console.error(err);
      window.showAlertOverlay?.('error', err.message || 'Network error while updating ILO.');
    } finally {
      submitBtn?.removeAttribute('disabled');
    }
  });
}

function bootDelete() {
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.deleteIloBtn');
    if (!btn) return;

    const id   = btn.getAttribute('data-id');
    const code = btn.getAttribute('data-code') || 'ILO?';

    $('#deleteIloForm')?.setAttribute('action', `/admin/master-data/ilo/${id}`);
    const badge = $('#deleteIloCode'); if (badge) badge.textContent = code;
    const title = $('#deleteIloLabel'); if (title) title.textContent = `Delete ${code}`;
  });

  const form = $('#deleteIloForm');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const submitBtn = form.querySelector('[type="submit"]');
    submitBtn?.setAttribute('disabled', 'disabled');

    try {
      const res = await fetch(form.getAttribute('action'), {
        method: 'POST', // DELETE via _method
        headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
        body: new FormData(form),
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(data?.message || 'Failed to delete ILO.');

      await fetchIlos(currentCourseId);
      window.bootstrap?.Modal.getInstance($('#deleteIloModal'))?.hide();
      window.showAlertOverlay?.('success', data.message || 'ILO deleted.');
    } catch (err) {
      console.error(err);
      window.showAlertOverlay?.('error', err.message || 'Network error while deleting ILO.');
    } finally {
      submitBtn?.removeAttribute('disabled');
    }
  });
}

/* ------------------------------- Boot ------------------------------- */

document.addEventListener('DOMContentLoaded', () => {
  // get course if server-rendered
  currentCourseId = $('#svTable-ilo')?.getAttribute('data-course-id') || null;

  bootFilter();
  bootAdd();
  bootEdit();
  bootDelete();

  // wire sortable immediately when server rendered list exists
  const table = $('#svTable-ilo');
  if (table) {
    const tbody = table.querySelector('tbody');
    const saveBtn = $('.sv-save-order-btn[data-sv-type="ilo"]');
    if (tbody && saveBtn) initSortable(tbody, saveBtn);
  }

  feather.replace();
});
