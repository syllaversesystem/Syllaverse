// -----------------------------------------------------------------------------
// * File: resources/js/superadmin/master-data/assessment-tasks.js
// * Description: CRUD for Assessment Tasks (LEC/LAB) – no drag, no description,
// *              with Bootstrap validation + modal stacking fixes.
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-08-17] Initial creation – wires modals, AJAX submit, DOM patching, feather refresh.
// [2025-08-17] Update – Added Bootstrap validation (checkValidity + was-validated + error mapping).
//              Ensured modals are moved to <body> on show (backdrop stacking fix). Hardened errors.
// -----------------------------------------------------------------------------

(() => {
  // ░░░ START: Bootstrap + Helpers ░░░

  // This grabs the CSRF token from the layout meta tag for all fetch calls.
  const csrf =
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  /**
   * This wraps fetch and returns JSON; it also throws on non-ok responses with the parsed payload.
   */
  async function fetchJson(url, opts = {}) {
    const headers = {
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': csrf,
      ...(opts.body instanceof FormData ? {} : { 'Content-Type': 'application/json' }),
      ...(opts.headers || {}),
    };

    const res = await fetch(url, { credentials: 'same-origin', headers, ...opts });
    let data = {};
    try {
      data = await res.json();
    } catch (_) {
      // ignore parse error; keep data as {}
    }
    if (!res.ok || data?.ok === false) {
      const err = new Error(data?.message || `Request failed: ${res.status}`);
      err.data = data;
      throw err;
    }
    return data;
  }

  /**
   * This builds the RESTful route for UPDATE.
   */
  function routeUpdate(id) {
    return `/superadmin/master-data/assessment-task/${id}`;
  }

  /**
   * This builds the RESTful route for DELETE.
   */
  function routeDestroy(id) {
    return `/superadmin/master-data/assessment-task/${id}`;
  }

  /**
   * This refreshes Feather icons after DOM mutations.
   */
  function applyFeather() {
    if (window.feather) window.feather.replace();
  }

  /**
   * This displays a toast/alert; swap with your global alert if present.
   */
  function showToast(msg, type = 'success') {
    if (window.showAlertOverlay) return window.showAlertOverlay(type, msg);
    // Fallback alert
    console[type === 'error' ? 'error' : 'log'](msg);
  }

  /**
   * This clears Bootstrap validation state for a given form.
   */
  function clearFieldErrors(form) {
    form.classList.remove('was-validated');
    form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
    // Keep original feedback text unless server returns specific one.
  }

  /**
   * This applies Laravel 422 errors to inputs (adds .is-invalid + sets .invalid-feedback).
   */
  function applyFieldErrors(form, errors = {}) {
    Object.entries(errors).forEach(([name, msgs]) => {
      const input = form.querySelector(`[name="${name}"]`);
      if (!input) return;
      input.classList.add('is-invalid');
      const fb = input.closest('.mb-3, .mb-0, .form-group')?.querySelector('.invalid-feedback');
      if (fb) fb.textContent = Array.isArray(msgs) ? msgs[0] : String(msgs);
    });
    form.classList.add('was-validated');
  }

  /**
   * This ensures any .modal is appended directly under <body> on show,
   * preventing z-index/stacking issues with backdrops.
   */
  document.addEventListener('show.bs.modal', (ev) => {
    const el = ev.target;
    if (!(el instanceof HTMLElement)) return;
    if (el.classList.contains('modal') && el.parentElement !== document.body) {
      document.body.appendChild(el);
    }

    // Reset validation state each time a modal opens
    const form = el.querySelector('form.needs-validation');
    if (form) clearFieldErrors(form);
  });

  // ░░░ END: Bootstrap + Helpers ░░░


  // ░░░ START: Add Task Modal ░░░

  /**
   * This pre-fills the Add modal with the clicked group's id and title.
   */
  document.addEventListener('show.bs.modal', (ev) => {
    const modal = ev.target;
    if (!(modal instanceof HTMLElement)) return;
    if (modal.id !== 'addAssessmentTaskModal') return;

    const btn = ev.relatedTarget;
    const groupId = btn?.getAttribute('data-group-id') || '';
    const groupTitle = btn?.getAttribute('data-group-title') || '';

    modal.querySelector('#add_at_group_id')?.setAttribute('value', groupId);
    const titleEl = modal.querySelector('#add_at_group_title');
    if (titleEl) {
      titleEl.value = groupTitle;
      titleEl.setAttribute('value', groupTitle);
    }
  });

  /**
   * This handles the Add form submit with HTML5 validity + AJAX and patches the table.
   */
  document.getElementById('addAssessmentTaskForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.currentTarget;
    const submitBtn = form.querySelector('button[type="submit"]');

    // Gate with HTML5 validity and Bootstrap styling
    if (!form.checkValidity()) {
      e.stopPropagation();
      form.classList.add('was-validated');
      return;
    }

    submitBtn.disabled = true;
    try {
      const formData = new FormData(form);
      const url = form.getAttribute('action');

      const res = await fetch(url, {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
      });

      const data = await res.json();
      if (!res.ok || data.ok === false) {
        if (data.errors) applyFieldErrors(form, data.errors);
        throw new Error(data.message || 'Add failed');
      }

      // Patch DOM: add new row to the correct group's table
      const groupId = formData.get('group_id');
      const table = document.querySelector(`table.at-table[data-group-id="${groupId}"] tbody`);
      if (table) {
        // Remove "empty" row if present
        table.querySelector('.sv-empty-row')?.remove();

        const tr = document.createElement('tr');
        tr.setAttribute('data-id', data.id);
        tr.innerHTML = `
          <td class="sv-code fw-semibold">${data.code}</td>
          <td class="fw-medium">${data.title}</td>
          <td class="text-end">
            <button type="button" class="btn action-btn rounded-circle edit me-2"
                    data-bs-toggle="modal" data-bs-target="#editAssessmentTaskModal"
                    data-id="${data.id}" data-group-id="${groupId}"
                    data-code="${data.code}" data-title="${data.title}" title="Edit" aria-label="Edit">
              <i data-feather="edit"></i>
            </button>
            <button type="button" class="btn action-btn rounded-circle delete"
                    data-bs-toggle="modal" data-bs-target="#deleteAssessmentTaskModal"
                    data-id="${data.id}" data-label="${data.code} — ${data.title}"
                    title="Delete" aria-label="Delete">
              <i data-feather="trash"></i>
            </button>
          </td>`;
        table.appendChild(tr);
        applyFeather();
      }

      // Close + reset form
      bootstrap.Modal.getInstance(document.getElementById('addAssessmentTaskModal'))?.hide();
      form.reset();
      clearFieldErrors(form);
      showToast('Assessment task added successfully!');
    } catch (err) {
      showToast(err.message || 'Add failed', 'error');
    } finally {
      submitBtn.disabled = false;
    }
  });

  // ░░░ END: Add Task Modal ░░░


  // ░░░ START: Edit Task Modal ░░░

  /**
   * This hydrates the Edit modal inputs and stores the dynamic form action.
   */
  document.addEventListener('show.bs.modal', (ev) => {
    const modal = ev.target;
    if (!(modal instanceof HTMLElement)) return;
    if (modal.id !== 'editAssessmentTaskModal') return;

    const btn = ev.relatedTarget;
    const id = btn?.getAttribute('data-id');
    const groupId = btn?.getAttribute('data-group-id');
    const code = btn?.getAttribute('data-code') || '';
    const title = btn?.getAttribute('data-title') || '';

    modal.querySelector('#edit_at_group_id')?.setAttribute('value', groupId || '');
    const codeEl = modal.querySelector('#edit_at_code');
    if (codeEl) {
      codeEl.value = code;
      codeEl.setAttribute('value', code);
    }
    const titleEl = modal.querySelector('#edit_at_title');
    if (titleEl) {
      titleEl.value = title;
      titleEl.setAttribute('value', title);
    }

    // Stash dynamic action route on the form (debug-friendly)
    const form = modal.querySelector('#editAssessmentTaskForm');
    form?.setAttribute('data-action', routeUpdate(id));
  });

  /**
   * This handles Edit form submit with validity + AJAX and patches the row in place.
   */
  document.getElementById('editAssessmentTaskForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.currentTarget;
    const submitBtn = form.querySelector('button[type="submit"]');

    if (!form.checkValidity()) {
      e.stopPropagation();
      form.classList.add('was-validated');
      return;
    }

    submitBtn.disabled = true;
    try {
      const idFromAttr = form.getAttribute('data-action')?.split('/').pop();
      const id = idFromAttr || '';
      const url = routeUpdate(id);

      const payload = {
        group_id: form.querySelector('#edit_at_group_id')?.value,
        code: form.querySelector('#edit_at_code')?.value,
        title: form.querySelector('#edit_at_title')?.value,
      };

      const data = await fetchJson(url, { method: 'PUT', body: JSON.stringify(payload) });

      // Patch row in the table
      const row = document.querySelector(`table.at-table tbody tr[data-id="${data.id}"]`);
      if (row) {
        const codeCell = row.querySelector('.sv-code');
        if (codeCell) codeCell.textContent = data.code;

        const titleCell = row.querySelector('.fw-medium');
        if (titleCell) titleCell.textContent = data.title;

        const editBtn = row.querySelector('button.edit');
        editBtn?.setAttribute('data-code', data.code);
        editBtn?.setAttribute('data-title', data.title);
        editBtn?.setAttribute('data-group-id', data.group_id);

        const delBtn = row.querySelector('button.delete');
        delBtn?.setAttribute('data-label', `${data.code} — ${data.title}`);
      }

      bootstrap.Modal.getInstance(document.getElementById('editAssessmentTaskModal'))?.hide();
      clearFieldErrors(form);
      showToast('Assessment task updated successfully!');
    } catch (err) {
      // Map Laravel field errors if any
      if (err?.data?.errors) applyFieldErrors(form, err.data.errors);
      showToast(err.message || 'Update failed', 'error');
    } finally {
      submitBtn.disabled = false;
    }
  });

  // ░░░ END: Edit Task Modal ░░░


  // ░░░ START: Delete Task Modal ░░░

  /**
   * This sets the friendly label and dynamic route for the Delete modal.
   */
  document.addEventListener('show.bs.modal', (ev) => {
    const modal = ev.target;
    if (!(modal instanceof HTMLElement)) return;
    if (modal.id !== 'deleteAssessmentTaskModal') return;

    const btn = ev.relatedTarget;
    const id = btn?.getAttribute('data-id') || '';
    const label = btn?.getAttribute('data-label') || 'this task';

    const labelEl = modal.querySelector('#delete_at_label');
    if (labelEl) labelEl.textContent = label;

    modal.querySelector('#deleteAssessmentTaskForm')?.setAttribute('data-action', routeDestroy(id));
  });

  /**
   * This calls DELETE and removes the row from the table; if table becomes empty, shows the empty state.
   */
  document.getElementById('deleteAssessmentTaskForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.currentTarget;
    const url = form.getAttribute('data-action');

    try {
      const data = await fetchJson(url, { method: 'DELETE' });

      // Remove row if we know the id (controller returns { id })
      const id = data?.id;
      const row = document.querySelector(`table.at-table tbody tr[data-id="${id}"]`);
      if (row) {
        const tbody = row.parentElement;
        row.remove();

        // If table becomes empty, append empty state
        if (!tbody.querySelector('tr')) {
          const empty = document.createElement('tr');
          empty.className = 'sv-empty-row';
          empty.innerHTML =
            `<td colspan="3"><div class="sv-empty"><h6>No tasks</h6><p>Click the <i data-feather="plus"></i> button to add one.</p></div></td>`;
          tbody.appendChild(empty);
          applyFeather();
        }
      }

      bootstrap.Modal.getInstance(document.getElementById('deleteAssessmentTaskModal'))?.hide();
      showToast(data.message || 'Assessment task deleted successfully!');
    } catch (err) {
      showToast(err.message || 'Delete failed', 'error');
    }
  });

  // ░░░ END: Delete Task Modal ░░░

  // Refresh icons once on load (in case the tab renders icons before any action)
  document.addEventListener('DOMContentLoaded', applyFeather);
})();
