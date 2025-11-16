/* Faculty Master Data • IGA tab logic */
import axios from 'axios';

document.addEventListener('DOMContentLoaded', () => {
  const table = document.getElementById('igaTable');
  const tableBody = document.getElementById('igaTableBody');
  const searchInput = document.getElementById('igaSearch');
  const addModalEl = document.getElementById('addIgaModal');
  const addForm = document.getElementById('addIgaForm');
  const addErrors = document.getElementById('addIgaErrors');
  const addSubmitBtn = document.getElementById('addIgaSubmit');

  async function loadIga(options = {}) {
    const showLoadingRow = options.showLoading !== false; // default true
    try {
      if (showLoadingRow) {
        showLoading();
      }
      const resp = await axios.get('/faculty/master-data/iga/filter', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
      });
      const data = resp.data?.igas || [];
      renderRows(data);
    } catch (err) {
      renderEmpty('Failed to load IGAs');
      console.error('IGA load error:', err);
    }
  }

  function renderRows(items) {
    const q = (searchInput?.value || '').toLowerCase().trim();
    const filtered = q
      ? items.filter(it => `${it.title || ''} ${it.description || ''}`.toLowerCase().includes(q))
      : items;

    if (!filtered.length) {
      const isSearch = !!q;
      return renderEmpty(isSearch ? 'No matching IGAs' : 'No IGAs found', isSearch);
    }

    const rowsHtml = filtered.map(it => {
      const title = (it?.title || '').trim() || '—';
      return `
        <tr data-iga-id="${it.id}" data-title="${escapeAttr(it.title || '')}" data-description="${escapeAttr(it.description || '')}">
          <td class="iga-title text-wrap" title="${escapeAttr(title)}">${escapeHtml(title)}</td>
          <td class="iga-desc-cell text-wrap text-break">${escapeHtml(it.description || '')}</td>
          <td class="iga-actions text-end">
            <button type="button" class="btn action-btn edit me-2" data-action="edit-iga" data-id="${it.id}" title="Edit"><i data-feather="edit"></i></button>
            <button type="button" class="btn action-btn delete" data-action="delete-iga" data-id="${it.id}" title="Delete"><i data-feather="trash"></i></button>
          </td>
        </tr>
      `;
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
    tableBody.innerHTML = `
      <tr class="iga-loading-row">
        <td colspan="3" class="text-center py-4">
          <div class="d-flex flex-column align-items-center">
            <i data-feather="loader" class="spinner mb-2" style="width:32px;height:32px;"></i>
            <p class="mb-0 text-muted">Loading IGAs...</p>
          </div>
        </td>
      </tr>
    `;
    if (typeof feather !== 'undefined') feather.replace();
  }

  function escapeHtml(str) { return String(str || '').replace(/[&<>"]+/g, s => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;' }[s])); }
  function escapeAttr(str) { return String(str || '').replace(/["'&<>]/g, (s) => ({ '"': '&quot;', "'": '&#39;', '&': '&amp;', '<': '&lt;', '>': '&gt;' }[s])); }

  // Search
  searchInput?.addEventListener('input', loadIga);

  // Tab hook
  const igaTab = document.getElementById('iga-main-tab');
  if (igaTab) {
    if (igaTab.classList.contains('active')) { loadIga(); }
    igaTab.addEventListener('shown.bs.tab', () => { loadIga(); });
  } else { loadIga(); }

  // Add modal wiring
  function clearAddErrors() { if (!addErrors) return; addErrors.classList.add('d-none'); addErrors.innerHTML = ''; }
  function renderAddErrors(errors) { if (!addErrors) return; const list = []; Object.keys(errors || {}).forEach(k => (errors[k] || []).forEach(m => list.push(`<li>${m}</li>`))); addErrors.innerHTML = `<ul class="mb-0 ps-3">${list.join('')}</ul>`; addErrors.classList.remove('d-none'); }
  addModalEl?.addEventListener('shown.bs.modal', () => {
    clearAddErrors();
    document.getElementById('igaTitle')?.focus();
  });
  addForm?.addEventListener('submit', async (e) => {
    e.preventDefault(); clearAddErrors(); if (addSubmitBtn) addSubmitBtn.disabled = true;
    try {
      const formData = new FormData(addForm);
      const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const resp = await axios.post(addForm.getAttribute('action'), formData, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf } });
      if (resp?.data?.success) {
        if (typeof bootstrap !== 'undefined') bootstrap.Modal.getOrCreateInstance(addModalEl).hide();
        addForm.reset();
        if (window.showAlertOverlay) window.showAlertOverlay('success', resp.data.message || 'IGA created');
        await loadIga({ showLoading: false });
      }
    } catch (err) {
      if (err?.response?.status === 422) { renderAddErrors(err.response.data?.errors || { _general: ['Validation failed'] }); }
      else { renderAddErrors({ _general: ['Failed to create IGA'] }); }
    } finally { if (addSubmitBtn) addSubmitBtn.disabled = false; }
  });

  // Edit modal wiring
  const editModalEl = document.getElementById('editIgaModal');
  const editForm = document.getElementById('editIgaForm');
  const editErrors = document.getElementById('editIgaErrors');
  const editSubmitBtn = document.getElementById('editIgaSubmit');
  function clearEditErrors() { if (!editErrors) return; editErrors.classList.add('d-none'); editErrors.innerHTML = ''; }
  function renderEditErrors(errors) { if (!editErrors) return; const list = []; Object.keys(errors || {}).forEach(k => (errors[k] || []).forEach(m => list.push(`<li>${m}</li>`))); editErrors.innerHTML = `<ul class=\"mb-0 ps-3\">${list.join('')}</ul>`; editErrors.classList.remove('d-none'); }

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action="edit-iga"]'); if (!btn) return;
    const id = btn.getAttribute('data-id'); const row = document.querySelector(`tr[data-iga-id="${CSS.escape(id)}"]`);
    if (!row || !editModalEl || !editForm) return;
    const title = row.getAttribute('data-title') || ''; const desc = row.getAttribute('data-description') || '';
    const titleInput = document.getElementById('editIgaTitle'); const descInput = document.getElementById('editIgaDescription');
    if (titleInput) titleInput.value = title; if (descInput) descInput.value = desc;
    editForm.setAttribute('action', `/faculty/master-data/iga/${encodeURIComponent(id)}`);
    if (typeof bootstrap !== 'undefined') bootstrap.Modal.getOrCreateInstance(editModalEl).show();
  });

  editForm?.addEventListener('submit', async (e) => {
    e.preventDefault(); clearEditErrors(); if (editSubmitBtn) editSubmitBtn.disabled = true;
    try {
      const formData = new FormData(editForm); formData.set('_method', 'PUT');
      const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const resp = await axios.post(editForm.getAttribute('action'), formData, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf } });
      if (resp?.data?.success) { if (typeof bootstrap !== 'undefined') bootstrap.Modal.getOrCreateInstance(editModalEl).hide(); if (window.showAlertOverlay) window.showAlertOverlay('success', resp.data.message || 'IGA updated'); await loadIga({ showLoading: false }); }
    } catch (err) {
      if (err?.response?.status === 422) renderEditErrors(err.response.data?.errors || { _general: ['Validation failed'] });
      else renderEditErrors({ _general: ['Failed to update IGA'] });
    } finally { if (editSubmitBtn) editSubmitBtn.disabled = false; }
  });

  // Delete modal wiring
  const deleteModalEl = document.getElementById('deleteIgaModal');
  const deleteForm = document.getElementById('deleteIgaForm');
  const deleteTitleEl = document.getElementById('deleteIgaTitle');
  const deleteSubmitBtn = document.getElementById('deleteIgaSubmit');

  function openDeleteModalFromRow(id) {
    const row = document.querySelector(`tr[data-iga-id="${CSS.escape(id)}"]`); if (!row || !deleteModalEl || !deleteForm) return;
    const title = (row.getAttribute('data-title') || '').trim(); const desc = (row.getAttribute('data-description') || '').trim();
    const display = title || (desc ? (desc.length > 80 ? desc.slice(0, 77) + '…' : desc) : `IGA #${id}`);
    if (deleteTitleEl) deleteTitleEl.textContent = display; deleteForm.setAttribute('action', `/faculty/master-data/iga/${encodeURIComponent(id)}`);
    const hiddenId = document.getElementById('deleteIgaId'); if (hiddenId) hiddenId.value = id;
    if (typeof bootstrap !== 'undefined') bootstrap.Modal.getOrCreateInstance(deleteModalEl).show();
    if (typeof feather !== 'undefined') feather.replace();
  }

  document.addEventListener('click', (e) => { const btn = e.target.closest('[data-action="delete-iga"]'); if (!btn) return; const id = btn.getAttribute('data-id'); if (!id) return; openDeleteModalFromRow(id); });
  deleteForm?.addEventListener('submit', async (e) => {
    e.preventDefault(); if (deleteSubmitBtn) deleteSubmitBtn.disabled = true;
    try {
      const url = deleteForm.getAttribute('action'); const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const resp = await axios.post(url, { _method: 'DELETE' }, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf } });
      if (resp?.data?.success) { if (typeof bootstrap !== 'undefined') bootstrap.Modal.getOrCreateInstance(deleteModalEl).hide(); if (window.showAlertOverlay) window.showAlertOverlay('success', resp.data.message || 'IGA deleted'); await loadIga({ showLoading: false }); }
      else { if (window.showAlertOverlay) window.showAlertOverlay('danger', 'Failed to delete IGA'); }
    } catch (err) { if (window.showAlertOverlay) window.showAlertOverlay('danger', 'Failed to delete IGA'); }
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
