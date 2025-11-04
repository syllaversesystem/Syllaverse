/* Faculty Master Data • SDG tab logic */
import axios from 'axios';

document.addEventListener('DOMContentLoaded', () => {
  const sdgTable = document.getElementById('sdgTable');
  const tableBody = document.getElementById('sdgTableBody');
  const deptFilter = document.getElementById('sdgDepartmentFilter');
  const searchInput = document.getElementById('sdgSearch');
  const addForm = document.getElementById('addSdgForm');
  const addModalEl = document.getElementById('addSdgModal');
  const addErrors = document.getElementById('addSdgErrors');
  const addSubmitBtn = document.getElementById('addSdgSubmit');
  const roleCanSeeDeptCol = (sdgTable?.dataset?.roleCanSeeDeptCol === '1');
  let hasDeptCol = roleCanSeeDeptCol && (!deptFilter || deptFilter.value === 'all');

  function rebuildHeaderAndColgroup() {
    if (!sdgTable) return;
    hasDeptCol = roleCanSeeDeptCol && (!deptFilter || deptFilter.value === 'all');
    const colgroup = sdgTable.querySelector('colgroup');
    if (colgroup) {
      colgroup.innerHTML = hasDeptCol
        ? '<col style="width:24%;" />\n<col style="width:1%;" />\n<col />\n<col style="width:1%;" />'
        : '<col style="width:28%;" />\n<col />\n<col style="width:1%;" />';
    }
    const theadRow = sdgTable.querySelector('thead tr');
    if (theadRow) {
      const deptTh = theadRow.querySelector('.th-dept');
      if (hasDeptCol) {
        if (!deptTh) {
          const th = document.createElement('th');
          th.scope = 'col';
          th.className = 'th-dept';
          th.innerHTML = '<i class="bi bi-building"></i> Department';
          theadRow.insertBefore(th, theadRow.children[1] || null);
        }
      } else if (deptTh) {
        theadRow.removeChild(deptTh);
      }
    }
  }

  async function loadSdg(options = {}) {
    const showLoadingRow = options.showLoading !== false; // default true
    const department = deptFilter?.value || 'all';
    try {
      if (document.activeElement === deptFilter && searchInput && searchInput.value) {
        searchInput.value = '';
      }
      if (showLoadingRow) {
        showLoading();
        if (deptFilter) {
          deptFilter.disabled = true;
          deptFilter.classList.add('is-loading');
        }
      }
      const resp = await axios.get(`/faculty/master-data/sdg/filter`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        params: { department }
      });
      const data = resp.data?.sdgs || [];
      renderRows(data);
    } catch (err) {
      renderEmpty('Failed to load SDGs');
      console.error('SDG load error:', err);
    } finally {
      if (deptFilter && showLoadingRow) {
        deptFilter.disabled = false;
        deptFilter.classList.remove('is-loading');
      }
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
      const dept = it?.department ? `${it.department.code || ''}` : '';
      const title = (it?.title || '').trim() || '—';
      const cells = [];
      cells.push(`<td class="sdg-title text-wrap">${escapeHtml(title)}</td>`);
      if (hasDeptCol) cells.push(`<td class="sdg-dept text-wrap">${escapeHtml(dept)}</td>`);
      cells.push(`<td class="sdg-desc-cell text-wrap text-break">${escapeHtml(it.description || '')}</td>`);
      cells.push(`
        <td class="sdg-actions text-end">
          <button type="button" class="btn action-btn edit me-2" data-action="edit-sdg" data-id="${it.id}" title="Edit"><i data-feather="edit"></i></button>
          <button type="button" class="btn action-btn delete" data-action="delete-sdg" data-id="${it.id}" title="Delete"><i data-feather="trash"></i></button>
        </td>
      `);
      return `
      <tr data-sdg-id="${it.id}" data-title="${escapeAttr(it.title || '')}" data-description="${escapeAttr(it.description || '')}" data-department-id="${it.department_id || (it.department?.id || '')}">
        ${cells.join('')}
      </tr>`;
    }).join('');

    tableBody.innerHTML = rowsHtml;
    if (typeof feather !== 'undefined') feather.replace();
  }

  function renderEmpty(message, isSearch = false) {
    const sub = isSearch ? '<p>Try a different search term.</p>' : ' <p>Click the <i data-feather="plus"></i> button to add one.</p>';
    tableBody.innerHTML = `
      <tr class="sdg-empty-row">
        <td colspan="${hasDeptCol ? 4 : 3}">
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
        <td colspan="${hasDeptCol ? 4 : 3}" class="text-center py-4">
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

  function handleDeptFilterChange() { rebuildHeaderAndColgroup(); loadSdg(); }
  deptFilter?.addEventListener('change', handleDeptFilterChange);
  deptFilter?.addEventListener('change', function (e) { e.target.style.transform = 'scale(0.98)'; setTimeout(() => { e.target.style.transform = ''; }, 150); });
  searchInput?.addEventListener('input', loadSdg);

  const sdgTab = document.getElementById('sdg-main-tab');
  if (sdgTab) {
    if (sdgTab.classList.contains('active')) { rebuildHeaderAndColgroup(); loadSdg(); }
    sdgTab.addEventListener('shown.bs.tab', () => { rebuildHeaderAndColgroup(); loadSdg(); });
  } else { rebuildHeaderAndColgroup(); loadSdg(); }

  // Add SDG modal wiring
  function clearAddErrors() { if (!addErrors) return; addErrors.classList.add('d-none'); addErrors.innerHTML = ''; }
  function renderAddErrors(errors) { if (!addErrors) return; const list = []; Object.keys(errors || {}).forEach(k => (errors[k] || []).forEach(m => list.push(`<li>${m}</li>`))); addErrors.innerHTML = `<ul class="mb-0 ps-3">${list.join('')}</ul>`; addErrors.classList.remove('d-none'); }
  if (addModalEl) {
    addModalEl.addEventListener('shown.bs.modal', () => {
      clearAddErrors();
      const sel = document.getElementById('sdgDepartment');
      if (sel && deptFilter && deptFilter.value && deptFilter.value !== 'all') sel.value = deptFilter.value;
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
