/* Faculty Master Data • SO tab logic */
import axios from 'axios';

document.addEventListener('DOMContentLoaded', () => {
  const soTable = document.getElementById('soTable');
  const tableBody = document.getElementById('soTableBody');
  // Department filter removed – SO list now auto-scoped server-side to user's department.
  const searchInput = document.getElementById('soSearch');
  const addForm = document.getElementById('addSoForm');
  const addModalEl = document.getElementById('addSoModal');
  const addErrors = document.getElementById('addSoErrors');
  const addSubmitBtn = document.getElementById('addSoSubmit');
  const roleCanSeeDeptCol = false; // always false – department column removed
  let hasDeptCol = false;

  // rebuildHeaderAndColgroup no longer needed (single static column layout)

  async function loadSo(options = {}) {
    const showLoadingRow = options.showLoading !== false; // default true
    try {
      if (showLoadingRow) showLoading();

      const resp = await axios.get(`/faculty/master-data/so/filter`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
      });
      const data = resp.data?.studentOutcomes || [];
      renderRows(data);
    } catch (err) {
      renderEmpty('Failed to load Student Outcomes');
      // eslint-disable-next-line no-console
      console.error('SO load error:', err);
    } finally {
      // nothing to re-enable
    }
  }

  function renderRows(items) {
    const q = (searchInput?.value || '').toLowerCase().trim();
    const filtered = q
      ? items.filter(it => `${it.title || ''} ${it.description || ''} ${it?.department?.code || ''} ${it?.department?.name || ''}`.toLowerCase().includes(q))
      : items;

    if (!filtered.length) {
      // Use "no match" UI when searching; default empty UI otherwise
      const isSearch = !!q;
      return renderEmpty(isSearch ? 'No matching student outcomes' : 'No Student Outcomes found', isSearch);
    }

    const rowsHtml = filtered.map(it => {
      // Department no longer displayed – table scoped server-side
      const title = (it?.title || '').trim();
      const titleDisplay = title ? title : '—';
      const cells = [];
      // Title: remove text-wrap; add title attribute for hover visibility
      cells.push(`<td class="so-title" title="${escapeAttr(titleDisplay)}">${escapeHtml(titleDisplay)}</td>`);
      cells.push(`<td class="so-desc-cell text-wrap text-break">${escapeHtml(it.description || '')}</td>`);
      cells.push(`
        <td class="so-actions text-end">
          <button type="button" class="btn action-btn edit me-2" data-action="edit-so" data-id="${it.id}" title="Edit">
            <i data-feather="edit"></i>
          </button>
          <button type="button" class="btn action-btn delete" data-action="delete-so" data-id="${it.id}" title="Delete">
            <i data-feather="trash"></i>
          </button>
        </td>
      `);
      return `
      <tr data-so-id="${it.id}" data-title="${escapeAttr(it.title || '')}" data-description="${escapeAttr(it.description || '')}" data-department-id="${it.department_id || (it.department?.id || '')}">
        ${cells.join('')}
      </tr>`;
    }).join('');

    tableBody.innerHTML = rowsHtml;

    // Refresh feather icons for newly injected buttons
    if (typeof feather !== 'undefined') {
      feather.replace();
    }
  }

  function renderEmpty(message, isSearch = false) {
    const sub = isSearch
      ? '<p>Try a different search term.</p>'
      : ' <p>Click the <i data-feather="plus"></i> button to add one.</p>';
    tableBody.innerHTML = `
      <tr class="superadmin-manage-department-empty-row">
        <td colspan="3">
          <div class="empty-table">
            <h6>${escapeHtml(message)}</h6>
            ${sub}
          </div>
        </td>
      </tr>
    `;
    if (typeof feather !== 'undefined') {
      feather.replace();
    }
  }

  function showLoading() {
    if (!tableBody) return;
    tableBody.innerHTML = `
      <tr class="so-loading-row">
        <td colspan="3" class="text-center py-4">
          <div class="d-flex flex-column align-items-center">
            <i data-feather="loader" class="spinner mb-2" style="width:32px;height:32px;"></i>
            <p class="mb-0 text-muted">Loading student outcomes...</p>
          </div>
        </td>
      </tr>
    `;
    if (typeof feather !== 'undefined') {
      feather.replace();
    }
  }

  function escapeHtml(str) {
    return str.replace(/[&<>"]+/g, s => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;' }[s]));
  }
  function escapeAttr(str) {
    return String(str || '').replace(/["'&<>]/g, (s) => ({ '"': '&quot;', "'": '&#39;', '&': '&amp;', '<': '&lt;', '>': '&gt;' }[s]));
  }

  // Department filter removed; only bind search
  searchInput?.addEventListener('input', loadSo);

  // Initial load when SO tab is shown
  const soTab = document.getElementById('so-main-tab');
  if (soTab) {
    if (soTab.classList.contains('active')) loadSo();
    soTab.addEventListener('shown.bs.tab', () => { loadSo(); });
  } else {
    loadSo();
  }

  // ░░░ START: Add SO Modal wiring ░░░
  function clearAddErrors() {
    if (!addErrors) return;
    addErrors.classList.add('d-none');
    addErrors.innerHTML = '';
  }

  function renderAddErrors(errors) {
    if (!addErrors) return;
    const list = [];
    Object.keys(errors || {}).forEach(k => (errors[k] || []).forEach(m => list.push(`<li>${m}</li>`)));
    addErrors.innerHTML = `<ul class="mb-0 ps-3">${list.join('')}</ul>`;
    addErrors.classList.remove('d-none');
  }

  if (addModalEl) {
    addModalEl.addEventListener('shown.bs.modal', () => {
      clearAddErrors();
      // Prefill department removed – server scopes automatically
      const t = document.getElementById('soTitle');
      t && t.focus();
    });
  }

  addForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearAddErrors();
    if (addSubmitBtn) addSubmitBtn.disabled = true;
    try {
      const formData = new FormData(addForm);
      // Ensure CSRF header present
      const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const resp = await axios.post(addForm.getAttribute('action'), formData, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf }
      });
      if (resp?.data?.success) {
        // Hide modal
        if (typeof bootstrap !== 'undefined') {
          bootstrap.Modal.getOrCreateInstance(addModalEl).hide();
        }
        // Reset form
        addForm.reset();
        // Toast and reload list
        if (window.showAlertOverlay) window.showAlertOverlay('success', resp.data.message || 'Student Outcome created');
        await loadSo({ showLoading: false });
      }
    } catch (err) {
      if (err?.response?.status === 422) {
        renderAddErrors(err.response.data?.errors || { _general: ['Validation failed'] });
      } else {
        renderAddErrors({ _general: ['Failed to create Student Outcome'] });
      }
    } finally {
      if (addSubmitBtn) addSubmitBtn.disabled = false;
    }
  });
  // ░░░ END: Add SO Modal wiring ░░░

  // ░░░ START: Edit SO Modal wiring ░░░
  const editModalEl = document.getElementById('editSoModal');
  const editForm = document.getElementById('editSoForm');
  const editErrors = document.getElementById('editSoErrors');
  const editSubmitBtn = document.getElementById('editSoSubmit');

  function clearEditErrors() {
    if (!editErrors) return;
    editErrors.classList.add('d-none');
    editErrors.innerHTML = '';
  }
  function renderEditErrors(errors) {
    if (!editErrors) return;
    const list = [];
    Object.keys(errors || {}).forEach(k => (errors[k] || []).forEach(m => list.push(`<li>${m}</li>`)));
    editErrors.innerHTML = `<ul class="mb-0 ps-3">${list.join('')}</ul>`;
    editErrors.classList.remove('d-none');
  }

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action="edit-so"]');
    if (!btn) return;
    const id = btn.getAttribute('data-id');
    const row = document.querySelector(`tr[data-so-id="${CSS.escape(id)}"]`);
    if (!row || !editModalEl || !editForm) return;

    // Prefill
    const title = row.getAttribute('data-title') || '';
    const desc = row.getAttribute('data-description') || '';
    const deptId = row.getAttribute('data-department-id') || '';
    const titleInput = document.getElementById('editSoTitle');
    const descInput = document.getElementById('editSoDescription');
    const deptSelect = document.getElementById('editSoDepartment');
    if (titleInput) titleInput.value = title;
    if (descInput) descInput.value = desc;
    if (deptSelect) deptSelect.value = deptId || '';

    // Set action
    editForm.setAttribute('action', `/faculty/master-data/so/${encodeURIComponent(id)}`);

    // Show modal
    if (typeof bootstrap !== 'undefined') {
      bootstrap.Modal.getOrCreateInstance(editModalEl).show();
    }
  });

  editForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearEditErrors();
    if (editSubmitBtn) editSubmitBtn.disabled = true;
    try {
      const formData = new FormData(editForm);
      // Laravel-friendly PUT via POST
      formData.set('_method', 'PUT');
      const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const resp = await axios.post(editForm.getAttribute('action'), formData, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf }
      });
      if (resp?.data?.success) {
        if (typeof bootstrap !== 'undefined') bootstrap.Modal.getOrCreateInstance(editModalEl).hide();
        if (window.showAlertOverlay) window.showAlertOverlay('success', resp.data.message || 'Student Outcome updated');
        await loadSo({ showLoading: false });
      }
    } catch (err) {
      if (err?.response?.status === 422) {
        renderEditErrors(err.response.data?.errors || { _general: ['Validation failed'] });
      } else {
        renderEditErrors({ _general: ['Failed to update Student Outcome'] });
      }
    } finally {
      if (editSubmitBtn) editSubmitBtn.disabled = false;
    }
  });
  // ░░░ END: Edit SO Modal wiring ░░░

  // ░░░ START: Delete SO Modal wiring ░░░
  const deleteModalEl = document.getElementById('deleteSoModal');
  const deleteForm = document.getElementById('deleteSoForm');
  const deleteTitleEl = document.getElementById('deleteSoTitle');
  const deleteSubmitBtn = document.getElementById('deleteSoSubmit');

  // Backdrop click restriction animation (mirror Departments):
  // When backdrop click is prevented (static backdrop), briefly apply Bootstrap's
  // modal-static class to trigger the bounce feedback.
  [addModalEl, editModalEl, deleteModalEl].forEach((el) => {
    if (!el) return;
    el.addEventListener('hidePrevented.bs.modal', (e) => {
      // keep modal open and animate
      e.preventDefault();
      el.classList.add('modal-static');
      setTimeout(() => el.classList.remove('modal-static'), 200);
    });
  });

  function openDeleteModalFromRow(id) {
    const row = document.querySelector(`tr[data-so-id="${CSS.escape(id)}"]`);
    if (!row || !deleteModalEl || !deleteForm) return;

    const title = (row.getAttribute('data-title') || '').trim();
    const desc = (row.getAttribute('data-description') || '').trim();
    const display = title || (desc ? (desc.length > 80 ? desc.slice(0, 77) + '…' : desc) : `SO #${id}`);
    if (deleteTitleEl) deleteTitleEl.textContent = display;
    deleteForm.setAttribute('action', `/faculty/master-data/so/${encodeURIComponent(id)}`);
    const hiddenId = document.getElementById('deleteSoId');
    if (hiddenId) hiddenId.value = id;

    if (typeof bootstrap !== 'undefined') {
      bootstrap.Modal.getOrCreateInstance(deleteModalEl).show();
    }
    if (typeof feather !== 'undefined') feather.replace();
  }

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action="delete-so"]');
    if (!btn) return;
    const id = btn.getAttribute('data-id');
    if (!id) return;
    openDeleteModalFromRow(id);
  });

  deleteForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (deleteSubmitBtn) deleteSubmitBtn.disabled = true;
    try {
      const url = deleteForm.getAttribute('action');
      const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const resp = await axios.post(url, { _method: 'DELETE' }, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf }
      });
      if (resp?.data?.success) {
        if (typeof bootstrap !== 'undefined') bootstrap.Modal.getOrCreateInstance(deleteModalEl).hide();
        if (window.showAlertOverlay) window.showAlertOverlay('success', resp.data.message || 'Student Outcome deleted');
        await loadSo({ showLoading: false });
      } else {
        if (window.showAlertOverlay) window.showAlertOverlay('danger', 'Failed to delete Student Outcome');
      }
    } catch (err) {
      if (window.showAlertOverlay) window.showAlertOverlay('danger', 'Failed to delete Student Outcome');
    } finally {
      if (deleteSubmitBtn) deleteSubmitBtn.disabled = false;
    }
  });
  // ░░░ END: Delete SO Modal wiring ░░░
});
