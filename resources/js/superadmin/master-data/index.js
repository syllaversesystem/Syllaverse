// -------------------------------------------------------------------------------
// * File: resources/js/superadmin/master-data/index.js
// * Description: Orchestrates Master Data page – sortable, Save Order, AJAX add/edit/delete,
//                shared modals, general-info AJAX, and partial table refresh – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-12] Initial creation – split from monolith; integrates sortable.js,
//              per-type Save Order, JSON/HTML-tolerant form AJAX, partial refresh.
// [2025-08-12] Fix – Save Order now uses table’s data-reorder-url (named route) with
//              safe normalization; removed hard-coded paths that caused 405s.
// [2025-08-12] Add – AJAX Delete via `.delete-master-data-form` with table refresh.
// [2025-08-12] Add – Shared modals: prefill Edit/Delete on open; removed per-row inline modals.
// [2025-08-12] Fix – Keep `_method` and send POST for edits (avoid PUT body parsing issues).
// [2025-08-12] UX  – Close Delete modal automatically on successful removal.
// [2025-08-12] Add – AJAX save for General Academic Information (`.general-info-form`) with inline toast.
// -------------------------------------------------------------------------------

import {
  initAllSortable,
  initSortable,
  destroySortable,
  getOrder,
  setDirty,
  isDirty,
} from './sortable.js';

/** Feather refresh (safe if missing). */
const refreshIcons = () => { try { window.feather?.replace?.(); } catch {} };

/** Global alert overlay (fallback to alert). */
const notify = (message, type = 'success') => {
  try { window.dispatchEvent(new CustomEvent('sv:alert', { detail: { type, message } })); }
  catch { alert(message); }
};

/** Shorthands. */
const $  = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
const csrf = () => $('meta[name="csrf-token"]')?.content || '';

/** REST helpers. */
const BASE = '/superadmin/master-data';
const updateUrl = (type, id) => `${BASE}/${type}/${id}`;
const deleteUrl = (type, id) => `${BASE}/${type}/${id}`;

/** Extract 'sdg' | 'iga' | 'cdio' from an action URL. */
const typeFromAction = (action) => {
  const m = String(action || '').match(/\/master-data\/(sdg|iga|cdio)\b/i);
  return m ? m[1].toLowerCase() : null;
};

/** Fetch current page HTML to mine updated table markup (for partial refresh). */
async function fetchDocHTML() {
  const res  = await fetch(window.location.href, { headers: { 'Accept': 'text/html' } });
  const text = await res.text();
  return new DOMParser().parseFromString(text, 'text/html');
}

/** Replace just the <tbody> for a type and rebind sortable. */
async function refreshTypeTable(type) {
  const tableSel = `#svTable-${type}`;
  const table    = $(tableSel);
  if (!table) return false;

  // Detach sortable before replace
  destroySortable(table);

  const doc      = await fetchDocHTML();
  const newTbody = doc.querySelector(`${tableSel} tbody`);
  const oldTbody = table.tBodies && table.tBodies[0];
  if (!newTbody || !oldTbody) return false;

  oldTbody.replaceWith(newTbody);

  // Rebind sortable and clear dirty
  initSortable(table);
  setDirty(table, false);
  refreshIcons();
  return true;
}

/** Toggle Save buttons on custom dirty events from sortable. */
function wireDirtyListeners() {
  $$('table[id^="svTable-"][data-sv-type]').forEach((table) => {
    table.addEventListener('sv:sortable:dirtychange', (ev) => {
      const type = table.getAttribute('data-sv-type');
      const btn  = $(`.sv-save-order-btn[data-sv-type="${type}"]`);
      if (btn) btn.disabled = !ev.detail?.dirty;
    });
  });
}

/** Persist current order for a given type. */
async function saveOrder(type) {
  const table = $(`#svTable-${type}`);
  if (!table) return;

  const ids = getOrder(table);
  if (!ids.length) return;

  const btn = $(`.sv-save-order-btn[data-sv-type="${type}"]`);
  btn?.setAttribute('disabled', 'disabled');

  // Prefer the server-generated named route
  let url = table.getAttribute('data-reorder-url')?.trim() || `/superadmin/master-data/reorder/${type}`;
  if (!/^https?:\/\//i.test(url) && !url.startsWith('/')) url = `/${url}`;

  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrf(),
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ ids }),
      credentials: 'same-origin',
    });

    const payload = await res.json().catch(() => ({}));
    if (!res.ok || payload?.ok === false) {
      throw new Error(payload?.message || `Reorder failed (${res.status})`);
    }

    // Optional: sync codes from server truth
    if (Array.isArray(payload.items)) {
      const map = new Map(payload.items.map(x => [String(x.id), x.code]));
      table.querySelectorAll('tbody tr').forEach(tr => {
        const id = tr.getAttribute('data-id');
        const cell = tr.querySelector('.sv-code');
        if (id && cell && map.has(id)) cell.textContent = map.get(id);
      });
    }

    setDirty(table, false);
    notify(payload?.message || `${type.toUpperCase()} order saved.`);
    refreshIcons();

  } catch (e) {
    notify(e.message || 'Could not save order.', 'error');
    await refreshTypeTable(type); // revert to server state on failure
  } finally {
    const dirty = isDirty($(`#svTable-${type}`));
    if (btn) btn.disabled = !dirty;
  }
}

/** Bind click handlers for Save buttons. */
function wireSaveButtons() {
  $$('.sv-save-order-btn[data-sv-type]').forEach(btn => {
    btn.addEventListener('click', async () => {
      await saveOrder(btn.getAttribute('data-sv-type'));
    });

    // Initialize disabled based on current table state
    const type  = btn.getAttribute('data-sv-type');
    const table = $(`#svTable-${type}`);
    btn.disabled = !table || !isDirty(table);
  });
}

/** AJAX Add/Edit/General-Info (keep _method for PUT/PATCH; always send as POST when spoofing). */
function wireFormAjax() {
  document.addEventListener('submit', async (ev) => {
    const form = ev.target;
    if (!(form instanceof HTMLFormElement)) return;

    const isAddEdit = form.matches('.add-master-data-form, .edit-master-data-form');
    const isGeneral = form.matches('.general-info-form');
    if (!isAddEdit && !isGeneral) return;

    ev.preventDefault();

    const actionType = typeFromAction(form.action); // null for general-info
    const modalEl    = form.closest('.modal');
    const submitBtn  = form.querySelector('button[type="submit"]');

    submitBtn?.setAttribute('disabled', 'disabled');

    try {
      const fd = new FormData(form);

      // If spoofing method, KEEP _method and send as POST (some stacks ignore PUT bodies)
      let method = (form.getAttribute('method') || 'POST').toUpperCase();
      if (fd.has('_method')) method = 'POST';

      const res = await fetch(form.action, {
        method,
        headers: {
          'X-CSRF-TOKEN': csrf(),
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json', // ask for JSON validation errors
        },
        body: fd,
        credentials: 'same-origin',
      });

      const ct = res.headers.get('content-type') || '';
      const isJSON = ct.includes('application/json');
      const payload = isJSON ? await res.json() : null;

      // Laravel validation (422) with JSON errors if Accept: application/json
      if (res.status === 422 && payload?.errors) {
        const first = Object.values(payload.errors)[0];
        notify(Array.isArray(first) ? first[0] : 'Please fix the errors.', 'error');
        return; // keep modal (if any) open
      }

      // Non-OK and not a validation error → generic failure
      if (!res.ok) {
        throw new Error((payload && (payload.message || payload.error)) || `Request failed (${res.status})`);
      }

      // Close modal if present (add/edit)
      if (modalEl) {
        try {
          const Modal = window.bootstrap?.Modal;
          (Modal.getInstance(modalEl) || new Modal(modalEl)).hide();
        } catch {}
      }

      // Different post-success behaviors
      if (isGeneral) {
        // Inline forms: just toast success; no refresh needed
        notify((payload && payload.message) || 'Saved.');
      } else if (actionType) {
        // Master-data tables: refresh only the affected table’s tbody and reset dirty state
        await refreshTypeTable(actionType);
        notify((payload && payload.message) || 'Saved.');
        if (form.classList.contains('add-master-data-form')) form.reset();
      } else {
        // Fallback (shouldn’t happen)
        window.location.reload();
      }

    } catch (e) {
      notify(e.message || 'Something went wrong.', 'error');
    } finally {
      submitBtn?.removeAttribute('disabled');
    }
  });
}

/** AJAX Delete (closes modal on success). */
function wireDeleteAjax() {
  document.addEventListener('submit', async (ev) => {
    const form = ev.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (!form.matches('.delete-master-data-form')) return;

    ev.preventDefault();

    const actionType = typeFromAction(form.action);
    const submitBtn  = form.querySelector('button[type="submit"]');
    const modalEl    = form.closest('.modal'); // shared delete modal

    submitBtn?.setAttribute('disabled', 'disabled');

    try {
      const fd = new FormData(form); // includes _token + _method=DELETE
      const res = await fetch(form.action, {
        method: 'POST', // Laravel honors _method=DELETE
        headers: {
          'X-CSRF-TOKEN': csrf(),
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
        },
        body: fd,
        credentials: 'same-origin',
      });

      const ct = res.headers.get('content-type') || '';
      const isJSON = ct.includes('application/json');
      const payload = isJSON ? await res.json() : null;

      if (!res.ok) throw new Error((payload && (payload.message || payload.error)) || `Delete failed (${res.status})`);

      // Close the modal on success
      if (modalEl) {
        try {
          const Modal = window.bootstrap?.Modal;
          (Modal.getInstance(modalEl) || new Modal(modalEl)).hide();
        } catch {}
      }

      // Refresh the affected table
      if (actionType) {
        await refreshTypeTable(actionType);
        notify((payload && payload.message) || 'Deleted.');
      } else {
        window.location.reload();
      }
    } catch (e) {
      notify(e.message || 'Could not delete.', 'error');
    } finally {
      submitBtn?.removeAttribute('disabled');
    }
  });
}

/** Prefill shared Edit/Delete modals on open. */
function wireSharedModals() {
  // Edit modal
  const editModal = $('#editMasterDataModal');
  if (editModal) {
    editModal.addEventListener('show.bs.modal', (ev) => {
      const btn = ev.relatedTarget;
      if (!btn) return;

      const type  = btn.getAttribute('data-sv-type');
      const id    = btn.getAttribute('data-sv-id');
      const title = btn.getAttribute('data-sv-title') || '';
      const desc  = btn.getAttribute('data-sv-description') || '';
      const label = btn.getAttribute('data-sv-label') || `${type?.toUpperCase()}${id}`;

      const form      = $('#editMasterDataForm', editModal);
      const titleEl   = $('#mdEditTitle', editModal);
      const descEl    = $('#mdEditDescription', editModal);
      const labelSpan = $('#mdEditLabel', editModal);

      form?.setAttribute('action', updateUrl(type, id));
      if (titleEl) titleEl.value = title;
      if (descEl)  descEl.value  = desc;
      if (labelSpan) labelSpan.textContent = label;

      refreshIcons();
    });
  }

  // Delete modal
  const delModal = $('#deleteMasterDataModal');
  if (delModal) {
    delModal.addEventListener('show.bs.modal', (ev) => {
      const btn = ev.relatedTarget;
      if (!btn) return;

      const type  = btn.getAttribute('data-sv-type');
      const id    = btn.getAttribute('data-sv-id');
      const label = btn.getAttribute('data-sv-label') || `${type?.toUpperCase()}${id}`;

      const form  = $('#deleteMasterDataForm', delModal);
      const hdr   = $('#mdDeleteLabel', delModal);
      const what  = $('#mdDeleteWhat', delModal);

      form?.setAttribute('action', deleteUrl(type, id));
      if (hdr)  hdr.textContent  = label;
      if (what) what.textContent = label;

      refreshIcons();
    });
  }
}

/** Boot the page scripts. */
function init() {
  initAllSortable();
  wireDirtyListeners();
  wireSaveButtons();
  wireFormAjax();       // add/edit/general-info
  wireDeleteAjax();
  wireSharedModals();
  refreshIcons();

  // Feather icons in modals need a nudge after open
  document.addEventListener('shown.bs.modal', () => refreshIcons());

  // Auto-resize mission/vision (and other general-info) textareas
  try {
    const autosize = (el) => {
      if (!el) return;
      el.style.overflowY = 'hidden';
      el.style.height = 'auto';
      el.style.height = (el.scrollHeight || 0) + 'px';
    };
    const areas = document.querySelectorAll('.general-info-form textarea.autosize');
    areas.forEach((ta) => {
      autosize(ta);
      ta.addEventListener('input', () => autosize(ta));
    });
  } catch {}
}

document.readyState === 'loading'
  ? document.addEventListener('DOMContentLoaded', init)
  : init();
