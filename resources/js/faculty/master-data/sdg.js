/* Faculty Master Data • SDG tab logic */
import axios from 'axios';

document.addEventListener('DOMContentLoaded', () => {
  const sdgTable = document.getElementById('sdgTable');
  const tableBody = document.getElementById('sdgTableBody');
  const searchInput = document.getElementById('sdgSearch');
  const addForm = document.getElementById('addSdgForm');
  const addModalEl = document.getElementById('addSdgModal');
  const addErrors = document.getElementById('addSdgErrors');
  const addSubmitBtn = document.getElementById('addSdgSubmit');
  // SDG has no department; no dynamic header/colgroup

  async function loadSdg(options = {}) {
    const showLoadingRow = options.showLoading !== false; // default true
    try {
      if (showLoadingRow) {
        showLoading();
      }
      const resp = await axios.get(`/faculty/master-data/sdg/filter`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
      });
      const data = resp.data?.sdgs || [];
      renderRows(data);
    } catch (err) {
      renderEmpty('Failed to load SDGs');
      console.error('SDG load error:', err);
    } finally {
      // no-op
    }
  }

  function renderRows(items) {
    const q = (searchInput?.value || '').toLowerCase().trim();
    const filtered = q
      ? items.filter(it => `${it.title || ''} ${it.description || ''} ${it?.department?.code || ''} ${it?.department?.name || ''}`.toLowerCase().includes(q))
      : items;

    if (!filtered.length) {
      const isSearch = !!q;
      return renderEmpty(isSearch ? 'No matching SDGs' : 'No SDGs found', isSearch);
    }

    const rowsHtml = filtered.map(it => {
      const title = (it?.title || '').trim() || '—';
      const cells = [];
      cells.push(`<td class="sdg-title text-wrap">${escapeHtml(title)}</td>`);
      cells.push(`<td class="sdg-desc-cell text-wrap text-break">${escapeHtml(it.description || '')}</td>`);
      cells.push(`
        <td class="sdg-actions text-end">
          <button type="button" class="btn action-btn edit me-2" data-action="edit-sdg" data-id="${it.id}" title="Edit"><i data-feather="edit"></i></button>
          <button type="button" class="btn action-btn delete" data-action="delete-sdg" data-id="${it.id}" title="Delete"><i data-feather="trash"></i></button>
        </td>
      `);
      return `
      <tr data-sdg-id="${it.id}" data-title="${escapeAttr(it.title || '')}" data-description="${escapeAttr(it.description || '')}">
        ${cells.join('')}
      </tr>`;
    }).join('');

    tableBody.innerHTML = rowsHtml;
    if (typeof feather !== 'undefined') feather.replace();
  }

  function renderEmpty(message, isSearch = false) {
    const sub = isSearch ? '<p>Try a different search term.</p>' : ' <p>Click the <i data-feather="plus"></i> button to add one.</p>';
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
    if (typeof feather !== 'undefined') feather.replace();
  }

  function showLoading() {
    if (!tableBody) return;
    tableBody.innerHTML = `
      <tr class="sdg-loading-row">
        <td colspan="3" class="text-center py-4">
          <div class="d-flex flex-column align-items-center">
            <i data-feather="loader" class="spinner mb-2" style="width:32px;height:32px;"></i>
            <p class="mb-0 text-muted">Loading SDGs...</p>
          </div>
        </td>
      </tr>
    `;
    if (typeof feather !== 'undefined') feather.replace();
  }

  function escapeHtml(str) { return String(str || '').replace(/[&<>"]+/g, s => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;' }[s])); }
  function escapeAttr(str) { return String(str || '').replace(/["'&<>]/g, (s) => ({ '"': '&quot;', "'": '&#39;', '&': '&amp;', '<': '&lt;', '>': '&gt;' }[s])); }

  searchInput?.addEventListener('input', loadSdg);

  const sdgTab = document.getElementById('sdg-main-tab');
  if (sdgTab) {
    if (sdgTab.classList.contains('active')) { loadSdg(); }
    sdgTab.addEventListener('shown.bs.tab', () => { loadSdg(); });
  } else { loadSdg(); }

  // Add SDG modal wiring
  function clearAddErrors() { if (!addErrors) return; addErrors.classList.add('d-none'); addErrors.innerHTML = ''; }
  function renderAddErrors(errors) { if (!addErrors) return; const list = []; Object.keys(errors || {}).forEach(k => (errors[k] || []).forEach(m => list.push(`<li>${m}</li>`))); addErrors.innerHTML = `<ul class="mb-0 ps-3">${list.join('')}</ul>`; addErrors.classList.remove('d-none'); }
  if (addModalEl) {
    addModalEl.addEventListener('shown.bs.modal', () => {
      clearAddErrors();
      const t = document.getElementById('sdgTitle'); t && t.focus();
    });
  }
  addForm?.addEventListener('submit', async (e) => {
    e.preventDefault(); clearAddErrors(); if (addSubmitBtn) addSubmitBtn.disabled = true;
    try {
      const formData = new FormData(addForm);
      const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const resp = await axios.post(addForm.getAttribute('action'), formData, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf } });
      if (resp?.data?.success) {
        if (typeof bootstrap !== 'undefined') bootstrap.Modal.getOrCreateInstance(addModalEl).hide();
        addForm.reset();
        if (window.showAlertOverlay) window.showAlertOverlay('success', resp.data.message || 'SDG created');
        await loadSdg({ showLoading: false });
      }
    } catch (err) {
      if (err?.response?.status === 422) { renderAddErrors(err.response.data?.errors || { _general: ['Validation failed'] }); }
      else { renderAddErrors({ _general: ['Failed to create SDG'] }); }
    } finally { if (addSubmitBtn) addSubmitBtn.disabled = false; }
  });

  // Edit SDG modal wiring
  const editModalEl = document.getElementById('editSdgModal');
  const editForm = document.getElementById('editSdgForm');
  const editErrors = document.getElementById('editSdgErrors');
  const editSubmitBtn = document.getElementById('editSdgSubmit');
  function clearEditErrors() { if (!editErrors) return; editErrors.classList.add('d-none'); editErrors.innerHTML = ''; }
  function renderEditErrors(errors) { if (!editErrors) return; const list = []; Object.keys(errors || {}).forEach(k => (errors[k] || []).forEach(m => list.push(`<li>${m}</li>`))); editErrors.innerHTML = `<ul class=\"mb-0 ps-3\">${list.join('')}</ul>`; editErrors.classList.remove('d-none'); }

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action="edit-sdg"]'); if (!btn) return;
    const id = btn.getAttribute('data-id'); const row = document.querySelector(`tr[data-sdg-id="${CSS.escape(id)}"]`);
    if (!row || !editModalEl || !editForm) return;
    const title = row.getAttribute('data-title') || ''; const desc = row.getAttribute('data-description') || ''; const deptId = row.getAttribute('data-department-id') || '';
    const titleInput = document.getElementById('editSdgTitle'); const descInput = document.getElementById('editSdgDescription'); const deptSelect = document.getElementById('editSdgDepartment');
    if (titleInput) titleInput.value = title; if (descInput) descInput.value = desc; if (deptSelect) deptSelect.value = deptId || '';
    editForm.setAttribute('action', `/faculty/master-data/sdg/${encodeURIComponent(id)}`);
    if (typeof bootstrap !== 'undefined') bootstrap.Modal.getOrCreateInstance(editModalEl).show();
  });

  editForm?.addEventListener('submit', async (e) => {
    e.preventDefault(); clearEditErrors(); if (editSubmitBtn) editSubmitBtn.disabled = true;
    try {
      const formData = new FormData(editForm); formData.set('_method', 'PUT');
      const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const resp = await axios.post(editForm.getAttribute('action'), formData, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf } });
      if (resp?.data?.success) { if (typeof bootstrap !== 'undefined') bootstrap.Modal.getOrCreateInstance(editModalEl).hide(); if (window.showAlertOverlay) window.showAlertOverlay('success', resp.data.message || 'SDG updated'); await loadSdg({ showLoading: false }); }
    } catch (err) {
      if (err?.response?.status === 422) renderEditErrors(err.response.data?.errors || { _general: ['Validation failed'] });
      else renderEditErrors({ _general: ['Failed to update SDG'] });
    } finally { if (editSubmitBtn) editSubmitBtn.disabled = false; }
  });

  // Delete SDG modal wiring
  const deleteModalEl = document.getElementById('deleteSdgModal');
  const deleteForm = document.getElementById('deleteSdgForm');
  const deleteTitleEl = document.getElementById('deleteSdgTitle');
  const deleteSubmitBtn = document.getElementById('deleteSdgSubmit');

  function openDeleteModalFromRow(id) {
    const row = document.querySelector(`tr[data-sdg-id="${CSS.escape(id)}"]`); if (!row || !deleteModalEl || !deleteForm) return;
    const title = (row.getAttribute('data-title') || '').trim(); const desc = (row.getAttribute('data-description') || '').trim();
    const display = title || (desc ? (desc.length > 80 ? desc.slice(0, 77) + '…' : desc) : `SDG #${id}`);
    if (deleteTitleEl) deleteTitleEl.textContent = display; deleteForm.setAttribute('action', `/faculty/master-data/sdg/${encodeURIComponent(id)}`);
    const hiddenId = document.getElementById('deleteSdgId'); if (hiddenId) hiddenId.value = id;
    if (typeof bootstrap !== 'undefined') bootstrap.Modal.getOrCreateInstance(deleteModalEl).show();
    if (typeof feather !== 'undefined') feather.replace();
  }

  document.addEventListener('click', (e) => { const btn = e.target.closest('[data-action="delete-sdg"]'); if (!btn) return; const id = btn.getAttribute('data-id'); if (!id) return; openDeleteModalFromRow(id); });
  deleteForm?.addEventListener('submit', async (e) => {
    e.preventDefault(); if (deleteSubmitBtn) deleteSubmitBtn.disabled = true;
    try {
      const url = deleteForm.getAttribute('action'); const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const resp = await axios.post(url, { _method: 'DELETE' }, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf } });
      if (resp?.data?.success) { if (typeof bootstrap !== 'undefined') bootstrap.Modal.getOrCreateInstance(deleteModalEl).hide(); if (window.showAlertOverlay) window.showAlertOverlay('success', resp.data.message || 'SDG deleted'); await loadSdg({ showLoading: false }); }
      else { if (window.showAlertOverlay) window.showAlertOverlay('danger', 'Failed to delete SDG'); }
    } catch (err) { if (window.showAlertOverlay) window.showAlertOverlay('danger', 'Failed to delete SDG'); }
    finally { if (deleteSubmitBtn) deleteSubmitBtn.disabled = false; }
  });

  // Backdrop click restriction animation (static bounce)
  [addModalEl, editModalEl, deleteModalEl].forEach((el) => {
    if (!el) return;
    el.addEventListener('hidePrevented.bs.modal', (e) => {
      e.preventDefault();
      el.classList.add('modal-static');
      setTimeout(() => el.classList.remove('modal-static'), 200);
    });
  });
});
