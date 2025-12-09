/* Faculty Master Data • CDIO tab logic */
import axios from 'axios';

document.addEventListener('DOMContentLoaded', () => {
  const table = document.getElementById('cdioTable');
  const tableBody = document.getElementById('cdioTableBody');
  const searchInput = document.getElementById('cdioSearch');

  const addModalEl = document.getElementById('addCdioModal');
  const addForm = document.getElementById('addCdioForm');
  const addErrors = document.getElementById('addCdioErrors');
  const addSubmitBtn = document.getElementById('addCdioSubmit');

  const editModalEl = document.getElementById('editCdioModal');
  const editForm = document.getElementById('editCdioForm');
  const editErrors = document.getElementById('editCdioErrors');
  const editSubmitBtn = document.getElementById('editCdioSubmit');

  const deleteModalEl = document.getElementById('deleteCdioModal');
  const deleteForm = document.getElementById('deleteCdioForm');
  const deleteTitleEl = document.getElementById('deleteCdioTitle');
  const deleteDescEl = document.getElementById('deleteCdioDesc');
  const deleteSubmitBtn = document.getElementById('deleteCdioSubmit');

  async function loadCdio(options = {}) {
    const showLoadingRow = options.showLoading !== false;
    const q = (searchInput?.value || '').toLowerCase().trim();
    try {
      if (showLoadingRow) showLoading();
      const resp = await axios.get('/faculty/master-data/cdio/filter', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
      });
      let items = resp.data?.cdios || [];
      if (q) {
        items = items.filter(it => `${it.title || ''} ${it.description || ''}`.toLowerCase().includes(q));
      }
      renderRows(items, !!q);
    } catch (err) {
      renderEmpty('Failed to load CDIO');
      // eslint-disable-next-line no-console
      console.error('CDIO load error:', err);
    }
  }

  function renderRows(items, isSearch = false) {
    if (!items.length) {
      return renderEmpty(isSearch ? 'No matching CDIO' : 'No CDIO items found', isSearch);
    }
    const rowsHtml = items.map(it => {
      const title = (it?.title || '').trim() || '—';
      const desc = it?.description || '';
      return `
        <tr data-cdio-id="${it.id}" data-title="${escapeAttr(it.title || '')}" data-description="${escapeAttr(desc)}">
          <td class="cdio-title" title="${escapeAttr(title)}">${escapeHtml(title)}</td>
          <td class="cdio-desc text-wrap text-break">${escapeHtml(desc)}</td>
          <td class="cdio-actions text-end">
            <button type="button" class="btn action-btn edit me-2" data-action="edit-cdio" data-id="${it.id}" title="Edit"><i data-feather="edit"></i></button>
            <button type="button" class="btn action-btn delete" data-action="delete-cdio" data-id="${it.id}" title="Delete"><i data-feather="trash"></i></button>
          </td>
        </tr>`;
    }).join('');
    tableBody.innerHTML = rowsHtml;
    if (typeof feather !== 'undefined') feather.replace();
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
      </tr>`;
    if (typeof feather !== 'undefined') feather.replace();
  }

  function showLoading() {
    tableBody.innerHTML = `
      <tr class="cdio-loading-row">
        <td colspan="3" class="text-center py-4">
          <div class="d-flex flex-column align-items-center">
            <i data-feather="loader" class="spinner mb-2" style="width:32px;height:32px;"></i>
            <p class="mb-0 text-muted">Loading CDIO...</p>
          </div>
        </td>
      </tr>`;
    if (typeof feather !== 'undefined') feather.replace();
  }

  function escapeHtml(str) { return String(str || '').replace(/[&<>"]+/g, s => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;' }[s])); }
  function escapeAttr(str) { return String(str || '').replace(/["'&<>]/g, (s) => ({ '"': '&quot;', "'": '&#39;', '&': '&amp;', '<': '&lt;', '>': '&gt;' }[s])); }

  // Search
  searchInput?.addEventListener('input', () => loadCdio());

  // Tab hooks
  const cdioTab = document.getElementById('cdio-main-tab');
  if (cdioTab) {
    if (cdioTab.classList.contains('active')) loadCdio();
    cdioTab.addEventListener('shown.bs.tab', () => loadCdio());
  } else { loadCdio(); }

  // Add modal
  function clearAddErrors() { if (!addErrors) return; addErrors.classList.add('d-none'); addErrors.innerHTML=''; }
  function renderAddErrors(errors) { if (!addErrors) return; const list=[]; Object.keys(errors||{}).forEach(k => (errors[k]||[]).forEach(m => list.push(`<li>${m}</li>`))); addErrors.innerHTML = `<ul class="mb-0 ps-3">${list.join('')}</ul>`; addErrors.classList.remove('d-none'); }
  addModalEl?.addEventListener('shown.bs.modal', () => { clearAddErrors(); document.getElementById('cdioTitle')?.focus(); });
  addForm?.addEventListener('submit', async (e) => {
    e.preventDefault(); clearAddErrors(); if (addSubmitBtn) addSubmitBtn.disabled = true;
    try {
      const formData = new FormData(addForm);
      const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const resp = await axios.post(addForm.getAttribute('action'), formData, { headers: { 'Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN': csrf } });
      if (resp?.data?.success) {
        if (typeof bootstrap !== 'undefined') bootstrap.Modal.getOrCreateInstance(addModalEl).hide();
        addForm.reset();
        if (window.showAlertOverlay) window.showAlertOverlay('success', resp.data.message || 'CDIO created');
        await loadCdio({ showLoading: false });
      }
    } catch (err) {
      if (err?.response?.status === 422) renderAddErrors(err.response.data?.errors || { _general: ['Validation failed'] });
      else renderAddErrors({ _general: ['Failed to create CDIO'] });
    } finally { if (addSubmitBtn) addSubmitBtn.disabled = false; }
  });

  // Edit modal
  function clearEditErrors() { if (!editErrors) return; editErrors.classList.add('d-none'); editErrors.innerHTML=''; }
  function renderEditErrors(errors) { if (!editErrors) return; const list=[]; Object.keys(errors||{}).forEach(k => (errors[k]||[]).forEach(m => list.push(`<li>${m}</li>`))); editErrors.innerHTML = `<ul class=\"mb-0 ps-3\">${list.join('')}</ul>`; editErrors.classList.remove('d-none'); }
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action="edit-cdio"]'); if (!btn) return;
    const id = btn.getAttribute('data-id'); const row = document.querySelector(`tr[data-cdio-id="${CSS.escape(id)}"]`);
    if (!row || !editModalEl || !editForm) return;
    const title = row.getAttribute('data-title') || ''; const desc = row.getAttribute('data-description') || '';
    const titleInput = document.getElementById('editCdioTitle'); const descInput = document.getElementById('editCdioDescription');
    if (titleInput) titleInput.value = title; if (descInput) descInput.value = desc;
    editForm.setAttribute('action', `/faculty/master-data/cdio/${encodeURIComponent(id)}`);
    if (typeof bootstrap !== 'undefined') bootstrap.Modal.getOrCreateInstance(editModalEl).show();
  });
  editForm?.addEventListener('submit', async (e) => {
    e.preventDefault(); clearEditErrors(); if (editSubmitBtn) editSubmitBtn.disabled = true;
    try {
      const formData = new FormData(editForm); formData.set('_method','PUT');
      const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const resp = await axios.post(editForm.getAttribute('action'), formData, { headers: { 'Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN': csrf } });
      if (resp?.data?.success) {
        if (typeof bootstrap !== 'undefined') bootstrap.Modal.getOrCreateInstance(editModalEl).hide();
        if (window.showAlertOverlay) window.showAlertOverlay('success', resp.data.message || 'CDIO updated');
        await loadCdio({ showLoading: false });
      }
    } catch (err) {
      if (err?.response?.status === 422) renderEditErrors(err.response.data?.errors || { _general: ['Validation failed'] });
      else renderEditErrors({ _general: ['Failed to update CDIO'] });
    } finally { if (editSubmitBtn) editSubmitBtn.disabled = false; }
  });

  // Delete modal
  function openDeleteModalFromRow(id) {
    const row = document.querySelector(`tr[data-cdio-id="${CSS.escape(id)}"]`); if (!row || !deleteModalEl || !deleteForm) return;
    const title = (row.getAttribute('data-title') || '').trim(); const desc = (row.getAttribute('data-description') || '').trim();
    if (deleteTitleEl) deleteTitleEl.textContent = title || `CDIO #${id}`;
    if (deleteDescEl) deleteDescEl.textContent = desc;
    deleteForm.setAttribute('action', `/faculty/master-data/cdio/${encodeURIComponent(id)}`);
    const hiddenId = document.getElementById('deleteCdioId'); if (hiddenId) hiddenId.value = id;
    if (typeof bootstrap !== 'undefined') bootstrap.Modal.getOrCreateInstance(deleteModalEl).show();
    if (typeof feather !== 'undefined') feather.replace();
  }
  document.addEventListener('click', (e) => { const btn = e.target.closest('[data-action="delete-cdio"]'); if (!btn) return; const id = btn.getAttribute('data-id'); if (!id) return; openDeleteModalFromRow(id); });
  deleteForm?.addEventListener('submit', async (e) => {
    e.preventDefault(); if (deleteSubmitBtn) deleteSubmitBtn.disabled = true;
    try {
      const url = deleteForm.getAttribute('action'); const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const resp = await axios.post(url, { _method:'DELETE' }, { headers: { 'Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN': csrf } });
      if (resp?.data?.success) {
        if (typeof bootstrap !== 'undefined') bootstrap.Modal.getOrCreateInstance(deleteModalEl).hide();
        if (window.showAlertOverlay) window.showAlertOverlay('success', resp.data.message || 'CDIO deleted');
        await loadCdio({ showLoading: false });
      } else { if (window.showAlertOverlay) window.showAlertOverlay('danger', 'Failed to delete CDIO'); }
    } catch (err) { if (window.showAlertOverlay) window.showAlertOverlay('danger', 'Failed to delete CDIO'); }
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
