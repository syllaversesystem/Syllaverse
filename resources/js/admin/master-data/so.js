// -------------------------------------------------------------------------------
// * File: resources/js/admin/master-data/so.js
// * Description: AJAX add, edit, and delete for Student Outcomes (SO) table
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-18] Initial – JSON submit + dynamic row injection + feather refresh.
// [2025-08-18] Update – Added Edit (prefill + AJAX submit) and Delete (AJAX form intercept).
// -------------------------------------------------------------------------------

import feather from 'feather-icons';

/* Plain-English: tiny query helpers so code is shorter. */
const $  = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

/* Plain-English: grab CSRF token from the page so our POST/PUT/DELETE work. */
function csrf() {
  return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

/* Plain-English: close a Bootstrap modal safely if it’s currently open. */
function closeModal(id) {
  const el = document.getElementById(id);
  const inst = window.bootstrap?.Modal?.getInstance(el);
  if (inst) inst.hide();
}

/* Plain-English: quick escape (we’ll set textContent anyway). */
function h(text) {
  return (text ?? '').toString();
}

/* Plain-English: builds a <tr> HTML string for one SO row based on your table structure.
   Table columns = [0: grip][1: code][2: description][3: actions] */
function rowHtml(so) {
  const destroyUrl = `/admin/master-data/so/${so.id}`; // uses MasterDataController destroy route
  return `
    <tr data-id="${so.id}">
      <td class="text-muted">
        <i class="sv-row-grip bi bi-grip-vertical fs-5" title="Drag to reorder"></i>
      </td>
      <td class="sv-code fw-semibold">${h(so.code)}</td>
      <td class="text-muted">${h(so.description)}</td>
      <td class="text-end">
        <button type="button"
                class="btn action-btn rounded-circle edit me-2"
                data-bs-toggle="modal"
                data-bs-target="#editSoModal"
                data-sv-id="${so.id}"
                data-sv-code="${h(so.code)}"
                data-sv-description="${h(so.description)}"
                title="Edit SO" aria-label="Edit SO">
          <i data-feather="edit"></i>
        </button>
        <form action="${destroyUrl}" method="POST" class="d-inline so-delete-form">
          <input type="hidden" name="_token" value="${csrf()}">
          <input type="hidden" name="_method" value="DELETE">
          <button type="submit" class="btn action-btn rounded-circle delete" title="Delete SO" aria-label="Delete SO">
            <i data-feather="trash"></i>
          </button>
        </form>
      </td>
    </tr>
  `;
}

/* ──────────────────────────────────────────────────────────────────────────────
   START: ADD (Create) – posts description, appends new row, closes modal
   ────────────────────────────────────────────────────────────────────────────── */
function bootSoAdd() {
  const form = $('#addSoForm');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    // Optional: prevent double-submit
    const submitBtn = form.querySelector('[type="submit"]');
    submitBtn?.setAttribute('disabled', 'disabled');

    try {
      const res = await fetch(form.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf(),
          'Accept': 'application/json'
        },
        body: new FormData(form),
      });

      const contentType = res.headers.get('content-type') || '';
      if (!contentType.includes('application/json')) {
        // Fallback (e.g., redirect) – just reload
        window.location.reload();
        return;
      }

      const data = await res.json();

      if (res.ok) {
        const tbody = $('#svTable-so tbody');
        if (tbody) {
          // Remove empty placeholder row if present
          tbody.querySelector('.sv-empty-row')?.remove();
          // Append the new row (or use 'afterbegin' if you want newest first)
          tbody.insertAdjacentHTML('beforeend', rowHtml(data.so));
          feather.replace();
        }

        form.reset();
        closeModal('addSoModal');
        window.showAlertOverlay?.('success', data.message || 'SO added successfully!');
      } else if (res.status === 422 && data?.errors) {
        const msg = Object.values(data.errors).flat().join('\n');
        window.showAlertOverlay?.('error', msg) || alert(msg);
      } else {
        const msg = data?.message || 'Failed to add Student Outcome.';
        window.showAlertOverlay?.('error', msg) || alert(msg);
      }
    } catch (err) {
      console.error(err);
      window.showAlertOverlay?.('error', 'Network error while adding SO.') || alert('Network error while adding SO.');
    } finally {
      submitBtn?.removeAttribute('disabled');
    }
  });
}
/* ──────────────────────────────────────────────────────────────────────────────
   END: ADD (Create)
   ────────────────────────────────────────────────────────────────────────────── */


/* ──────────────────────────────────────────────────────────────────────────────
   START: EDIT – prefill modal on click + PUT via AJAX and update row in place
   ────────────────────────────────────────────────────────────────────────────── */

/* Plain-English: fill the Edit modal with the data from the clicked row button. */
// 🔁 DROP-IN REPLACEMENT for bindEditOpener() in resources/js/admin/master-data/so.js
function bindEditOpener() {
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-bs-target="#editSoModal"][data-sv-id]');
    if (!btn) return;

    const id   = btn.getAttribute('data-sv-id');
    const code = btn.getAttribute('data-sv-code') || '';
    const desc = btn.getAttribute('data-sv-description') || '';

    const form = document.getElementById('editSoForm');
    if (!form) return;

    // Point the form to the correct SO
    form.setAttribute('action', `/admin/master-data/so/${id}`);

    // Set description textarea
    const descEl = document.getElementById('editSoDescription');
    if (descEl) descEl.value = desc;

    // 🔒 Show SO code as a static badge (no input)
    const badge = document.getElementById('editSoCodeBadge');
    if (badge) badge.textContent = code || 'SO?';

    // Update modal title
    const label = document.getElementById('editSoModalLabel');
    if (label) label.textContent = code ? `Edit ${code}` : 'Edit Student Outcome';
  });
}


/* Plain-English: make an AJAX POST (with _method=PUT) to update the SO,
   then patch the existing table row’s code/description & data attributes. */
function bindEditSubmit() {
  const form = $('#editSoForm');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const submitBtn = form.querySelector('[type="submit"]');
    submitBtn?.setAttribute('disabled', 'disabled');

    try {
      const res = await fetch(form.getAttribute('action'), {
        method: 'POST', // Laravel will treat this as PUT due to @method('PUT')
        headers: {
          'X-CSRF-TOKEN': csrf(),
          'Accept': 'application/json'
        },
        body: new FormData(form),
      });

      const ct = res.headers.get('content-type') || '';
      if (!ct.includes('application/json')) {
        window.location.reload();
        return;
      }

      const data = await res.json();

      if (res.ok) {
        const so = data.so; // { id, code, description, ... }
        const row = document.querySelector(`#svTable-so tbody tr[data-id="${so.id}"]`);
        if (row) {
          // Update cells (0: grip, 1: code, 2: desc, 3: actions)
          const tds = row.querySelectorAll('td');
          if (tds[1]) tds[1].textContent = h(so.code);
          if (tds[2]) tds[2].textContent = h(so.description);

          // Update the action cell button data-* so next edit opens with fresh values
          if (tds[3]) {
            const editBtn = tds[3].querySelector('button.edit');
            if (editBtn) {
              editBtn.setAttribute('data-sv-code', h(so.code));
              editBtn.setAttribute('data-sv-description', h(so.description));
            }
          }
          feather.replace();
        }

        closeModal('editSoModal');
        window.showAlertOverlay?.('success', data.message || 'Student Outcome updated successfully!');
      } else if (res.status === 422 && data?.errors) {
        const msg = Object.values(data.errors).flat().join('\n');
        window.showAlertOverlay?.('error', msg) || alert(msg);
      } else {
        const msg = data?.message || 'Failed to update Student Outcome.';
        window.showAlertOverlay?.('error', msg) || alert(msg);
      }
    } catch (err) {
      console.error(err);
      window.showAlertOverlay?.('error', 'Network error while updating SO.') || alert('Network error while updating SO.');
    } finally {
      submitBtn?.removeAttribute('disabled');
    }
  });
}
/* ──────────────────────────────────────────────────────────────────────────────
   END: EDIT
   ────────────────────────────────────────────────────────────────────────────── */


/* ──────────────────────────────────────────────────────────────────────────────
   START: DELETE – intercept form submit and delete via AJAX (fallback-safe)
   ────────────────────────────────────────────────────────────────────────────── */

/* Plain-English: Intercept the delete form submit in the SO table, confirm,
   then POST with _method=DELETE and remove the row on success. */
function bindDelete() {
  document.addEventListener('submit', async (e) => {
    const form = e.target;
    if (!form.classList.contains('so-delete-form')) return; // only handle SO delete forms
    e.preventDefault();

    // Ask confirmation (your Blade also confirms; keeping once here is fine)
    const row = form.closest('tr[data-id]');
    const code = row?.querySelector('.sv-code')?.textContent?.trim() || 'this SO';
    if (!window.confirm(`Delete ${code}?`)) return;

    try {
      const res = await fetch(form.getAttribute('action'), {
        method: 'POST', // Laravel DELETE via _method
        headers: {
          'X-CSRF-TOKEN': csrf(),
          'Accept': 'application/json'
        },
        body: new FormData(form),
      });

      const ct = res.headers.get('content-type') || '';
      if (!ct.includes('application/json')) {
        window.location.reload();
        return;
      }

      const data = await res.json();

      if (res.ok) {
        row?.remove();
        // If table empty, you could inject the "No Student Outcomes" placeholder again if desired.
        window.showAlertOverlay?.('success', data.message || 'Student Outcome deleted successfully!');
      } else {
        const msg = data?.message || 'Failed to delete Student Outcome.';
        window.showAlertOverlay?.('error', msg) || alert(msg);
      }
    } catch (err) {
      console.error(err);
      window.showAlertOverlay?.('error', 'Network error while deleting SO.') || alert('Network error while deleting SO.');
    }
  });
}
/* ──────────────────────────────────────────────────────────────────────────────
   END: DELETE
   ────────────────────────────────────────────────────────────────────────────── */


   // 🔁 Bind delete opener for the SO modal (uses new routes)
function bindDeleteOpener() {
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.deleteSoBtn');
    if (!btn) return;

    const id   = btn.getAttribute('data-id');
    const code = btn.getAttribute('data-code') || 'SO?';

    // Point form to DELETE /admin/master-data/so/{id}
    const form = document.getElementById('deleteSoForm');
    if (form) form.setAttribute('action', `/admin/master-data/so/${id}`);

    // Fill code badge/label
    const badge = document.getElementById('deleteSoCode');
    if (badge) badge.textContent = code;

    // Update modal title text
    const title = document.getElementById('deleteSoLabel');
    if (title) title.textContent = `Delete ${code}`;
  });
}

/* ──────────────────────────────────────────────────────────────────────────────
   START: Boot
   ────────────────────────────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  // Wire features
  bootSoAdd();
  bindEditOpener();
  bindEditSubmit();
  bindDelete();
  bindDeleteOpener();

  // Ensure icons render at first load
  if (window.feather) window.feather.replace();
});
/* ──────────────────────────────────────────────────────────────────────────────
   END: Boot
   ────────────────────────────────────────────────────────────────────────────── */
