/* Faculty Master Data • ILO tab placeholder (UI only) */
document.addEventListener('DOMContentLoaded', () => {
  const replaceIcons = () => { if (typeof feather !== 'undefined') feather.replace(); };
  replaceIcons();

  // Ensure icons are replaced when tab becomes visible
  const iloTab = document.getElementById('ilo-main-tab');
  if (iloTab) iloTab.addEventListener('shown.bs.tab', replaceIcons);

  // Optional: focus search on add modal hide/show for smoother UX later
  const addModalEl = document.getElementById('addIloModal');
  const searchInput = document.getElementById('iloSearch');
  const deptFilter = document.getElementById('iloDepartmentFilter');
    // Simple select-based course filter (UI parity with department filter)
    const courseSelect = document.getElementById('iloCourseFilter');
  const addBtn = document.getElementById('iloAddBtn');
  const codeInput = document.getElementById('iloCode');
  const editCodeInput = document.getElementById('editIloCode');
  const hiddenCourseInput = document.getElementById('iloCourseId');
  // CRUD-related form and feedback elements
  const addForm = document.getElementById('addIloForm');
  const editForm = document.getElementById('editIloForm');
  const deleteForm = document.getElementById('deleteIloForm');
  const addErrors = document.getElementById('addIloErrors');
  const editErrors = document.getElementById('editIloErrors');
  const deleteIdInput = document.getElementById('deleteIloId');
  const deleteTitle = document.getElementById('deleteIloTitle');
  const deleteDesc = document.getElementById('deleteIloDesc');
  if (addModalEl && searchInput) {
    addModalEl.addEventListener('hidden.bs.modal', () => searchInput.focus());
  }

  // Compute next ILO code by scanning current table rows (e.g., ILO1..ILO12)
  function computeNextIloCode() {
    const rows = Array.from(document.querySelectorAll('#iloTable tbody tr'));
    let maxN = 0;
    rows.forEach((tr) => {
      const codeCell = tr.querySelector('td.ilo-code');
      if (!codeCell) return;
      const m = String(codeCell.textContent || '').trim().match(/^ILO(\d+)$/i);
      if (m) {
        const n = parseInt(m[1], 10);
        if (!Number.isNaN(n)) maxN = Math.max(maxN, n);
      }
    });
    return `ILO${maxN + 1}`;
  }

  // --- Course-driven ILO loading ---
  let currentIlos = [];
  const tableBody = document.getElementById('iloTableBody');
  const searchInputLocal = searchInput; // reuse search input for filtering

  function escapeHtml(str) { return String(str || '').replace(/[&<>"]/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[s])); }

  function renderEmpty(message, isSearch = false) {
    const sub = isSearch ? '<p>Try a different search term.</p>' : '<p>Select a course to view ILOs.</p>';
    if (tableBody) tableBody.innerHTML = `
      <tr class="superadmin-manage-department-empty-row">
        <td colspan="4">
          <div class="empty-table">
            <h6>${escapeHtml(message)}</h6>
            ${sub}
          </div>
        </td>
      </tr>`;
    if (typeof feather !== 'undefined') feather.replace();
  }

  function showLoading() {
    if (!tableBody) return;
    tableBody.innerHTML = `
      <tr>
        <td colspan="4" class="text-center py-4">
          <div class="d-flex flex-column align-items-center">
            <i data-feather="loader" class="spinner mb-2" style="width:32px;height:32px;"></i>
            <p class="mb-0 text-muted">Loading ILOs...</p>
          </div>
        </td>
      </tr>`;
    if (typeof feather !== 'undefined') feather.replace();
  }

  function renderRows(items) {
    if (!tableBody) return;
    const q = (searchInputLocal?.value || '').toLowerCase().trim();
    let list = items || [];
    if (q) list = list.filter(it => `${it.code || ''} ${it.description || ''}`.toLowerCase().includes(q));
    if (!list.length) return renderEmpty(q ? 'No matching ILO' : 'No ILOs found', !!q);
    const rowsHtml = list.map(it => `
      <tr data-ilo-id="${it.id}" data-code="${escapeHtml(it.code || '')}" data-description="${escapeHtml(it.description || '')}">
        <td class="ilo-drag text-muted" aria-label="Drag to reorder">
          <svg class="grip-icon" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
            <g fill="currentColor">
              <circle cx="4" cy="4" r="1" />
              <circle cx="12" cy="4" r="1" />
              <circle cx="4" cy="8" r="1" />
              <circle cx="12" cy="8" r="1" />
              <circle cx="4" cy="12" r="1" />
              <circle cx="12" cy="12" r="1" />
            </g>
          </svg>
        </td>
        <td class="ilo-code" title="${escapeHtml(it.code || '')}">${escapeHtml(it.code || '')}</td>
        <td class="ilo-desc text-wrap text-break">${escapeHtml(it.description || '')}</td>
        <td class="ilo-actions text-end">
          <button type="button" class="btn action-btn edit me-2" data-action="edit-ilo" data-id="${it.id}" title="Edit"><i data-feather="edit"></i></button>
          <button type="button" class="btn action-btn delete" data-action="delete-ilo" data-id="${it.id}" title="Delete"><i data-feather="trash"></i></button>
        </td>
      </tr>`).join('');
    tableBody.innerHTML = rowsHtml;
    if (typeof feather !== 'undefined') feather.replace();
    enableDragAndDrop();
  }

  async function loadIlos(opts = {}) {
    const { show = true } = opts;
    const cid = getSelectedCourseId();
    if (!cid) { currentIlos = []; return renderEmpty('No course selected'); }
    try {
      if (show) showLoading();
      const resp = await fetch(`/faculty/master-data/ilo/filter?course_id=${encodeURIComponent(cid)}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
      if (!resp.ok) throw new Error('Failed');
      const data = await resp.json();
      currentIlos = Array.isArray(data?.ilos) ? data.ilos : [];
      renderRows(currentIlos);
    } catch (e) {
      currentIlos = [];
      renderEmpty('Failed to load ILOs');
      // eslint-disable-next-line no-console
      console.error('ILO load error', e);
    }
  }

  // Search filters current loaded list (no server call)
  searchInputLocal?.addEventListener('input', () => renderRows(currentIlos));

  // Auto-populate add modal code field on open
  function getSelectedCourseId() {
      const val = courseSelect?.value || '';
      return val ? val : null;
    }
  function updateAddBtnState() {
    const hasCourse = !!getSelectedCourseId();
    if (addBtn) {
      // Keep clickable; just adjust the tooltip/title to guide the user
      addBtn.title = hasCourse ? 'Add ILO' : 'Select a course to add an ILO';
    }
  }
  updateAddBtnState();
  // (dropdown selection will trigger updates instead of change event)
  if (addModalEl) {
    addModalEl.addEventListener('show.bs.modal', (e) => {
      const cid = getSelectedCourseId();
      if (!cid) {
        e.preventDefault();
        e.stopPropagation();
        if (window.showAlertOverlay) window.showAlertOverlay('warning', 'Please select a course to add an ILO.');
        return;
      }
      if (hiddenCourseInput) hiddenCourseInput.value = cid;
      if (codeInput) codeInput.value = computeNextIloCode();
    });
  }

  // Ensure edit code remains readonly
  if (editCodeInput) {
    editCodeInput.setAttribute('readonly', 'readonly');
  }

  // Department filter only refines course dropdown list, not ILO table directly
  deptFilter?.addEventListener('change', () => {
    deptFilter.classList.add('is-loading');
    setTimeout(() => deptFilter.classList.remove('is-loading'), 300);
      filterCourseSelectByDept();
  });

    function filterCourseSelectByDept() {
      if (!courseSelect) return;
      const deptId = deptFilter?.value || 'all';
      let selectedVisible = true;
      const currentVal = courseSelect.value;
      Array.from(courseSelect.options).forEach((opt, idx) => {
        if (idx === 0) return; // placeholder always visible
        const od = opt.getAttribute('data-dept-id');
        const matchesDept = (deptId === 'all') || (od === deptId);
        opt.hidden = !matchesDept;
        opt.disabled = !matchesDept;
        if (currentVal && opt.value === currentVal && !matchesDept) selectedVisible = false;
      });
      if (!selectedVisible && currentVal) {
        courseSelect.value = '';
        updateAddBtnState();
        renderEmpty('No course selected');
      }
    }

    courseSelect?.addEventListener('change', () => {
      updateAddBtnState();
      loadIlos({ show: true });
    });

    // Initial state
    filterCourseSelectByDept();

  // Removed legacy dropdown filtering code (now using simple select); ensure initial empty state set above.
  renderEmpty('No course selected');

  // Require a picked course before opening Add ILO modal
  const isCoursePicked = () => !!getSelectedCourseId();

  function nudgeCourseFilter(message = 'Pick a course first') {
    // Visual nudge on the course filter
    if (courseSelect) {
      courseSelect.classList.add('is-loading');
      setTimeout(() => courseSelect.classList.remove('is-loading'), 700);
    }
    // Prefer global alert overlay if available
    if (window.showAlertOverlay) {
      window.showAlertOverlay('warning', message);
      return;
    }
    // Fallback: Bootstrap tooltip on Add button
    if (typeof bootstrap !== 'undefined' && addBtn) {
      const tip = bootstrap.Tooltip.getOrCreateInstance(addBtn, { title: message, trigger: 'manual', placement: 'top' });
      tip.show();
      setTimeout(() => tip.hide(), 1200);
    } else {
      // Last resort: alert
      // eslint-disable-next-line no-alert
      alert(message);
    }
  }

  // Intercept clicks on the Add button
  addBtn?.addEventListener('click', (e) => {
    if (!isCoursePicked()) {
      e.preventDefault();
      e.stopPropagation();
      nudgeCourseFilter();
    }
  }, true);

  // Also guard modal show (in case opened via keyboard or programmatically)
  addModalEl?.addEventListener('show.bs.modal', (e) => {
    if (!isCoursePicked()) {
      e.preventDefault();
      nudgeCourseFilter();
    }
  });

  // Backdrop click restriction animation (static bounce)
  [document.getElementById('addIloModal'), document.getElementById('editIloModal'), document.getElementById('deleteIloModal')]
    .forEach((el) => {
      if (!el) return;
      el.addEventListener('hidePrevented.bs.modal', (e) => {
        e.preventDefault();
        el.classList.add('modal-static');
        setTimeout(() => el.classList.remove('modal-static'), 200);
      });
    });

  // ---------------------------- CRUD Wiring ----------------------------
  function showFormErrors(container, msg) {
    if (!container) return;
    container.classList.remove('d-none');
    container.textContent = typeof msg === 'string' ? msg : 'Please check your input.';
  }
  function clearFormErrors(container) {
    if (!container) return;
    container.classList.add('d-none');
    container.textContent = '';
  }
  function getCsrfToken() {
    // Prefer meta tag
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta?.getAttribute('content')) return meta.getAttribute('content');
    // Fallback to hidden _token input in any of the forms
    const tokenInput = document.querySelector('input[name="_token"]');
    if (tokenInput?.value) return tokenInput.value;
    // Fallback to global
    return (window.Laravel && window.Laravel.csrfToken) || '';
  }
  async function postJson(url, payload) {
    const resp = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': getCsrfToken() },
      body: JSON.stringify(payload || {})
    });
    const data = await resp.json().catch(() => ({}));
    if (!resp.ok) throw new Error(data?.message || 'Request failed');
    return data;
  }
  async function putJson(url, payload) {
    const resp = await fetch(url, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': getCsrfToken() },
      body: JSON.stringify(payload || {})
    });
    const data = await resp.json().catch(() => ({}));
    if (!resp.ok) throw new Error(data?.message || 'Request failed');
    return data;
  }
  async function deleteReq(url) {
    const resp = await fetch(url, {
      method: 'DELETE',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': getCsrfToken() },
    });
    const data = await resp.json().catch(() => ({}));
    if (!resp.ok) throw new Error(data?.message || 'Request failed');
    return data;
  }

  // Create ILO
  addForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearFormErrors(addErrors);
    const cid = getSelectedCourseId();
    if (!cid) { showFormErrors(addErrors, 'Please select a course.'); return; }
    const payload = {
      course_id: cid,
      code: codeInput?.value || '',
      description: document.getElementById('iloDescription')?.value || ''
    };
    try {
      await postJson('/faculty/master-data/ilo', payload);
      if (typeof bootstrap !== 'undefined' && addModalEl) bootstrap.Modal.getOrCreateInstance(addModalEl).hide();
      const descEl = document.getElementById('iloDescription');
      if (descEl) descEl.value = '';
      await loadIlos({ show: false });
      if (window.showAlertOverlay) window.showAlertOverlay('success', 'ILO created');
    } catch (err) {
      showFormErrors(addErrors, err.message || 'Failed to create ILO.');
      if (window.showAlertOverlay) window.showAlertOverlay('danger', err.message || 'Failed to create ILO');
    }
  });

  // Open edit modal
  document.getElementById('iloTableBody')?.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-action="edit-ilo"]');
    if (!btn) return;
    const tr = btn.closest('tr');
    const id = tr?.getAttribute('data-ilo-id');
    if (!id) return;
    const code = tr.getAttribute('data-code') || '';
    const desc = tr.getAttribute('data-description') || '';
    const editCodeEl = document.getElementById('editIloCode');
    const editDescEl = document.getElementById('editIloDescription');
    if (editCodeEl) editCodeEl.value = code;
    if (editDescEl) editDescEl.value = desc;
    editForm?.setAttribute('data-id', id);
    if (typeof bootstrap !== 'undefined') bootstrap.Modal.getOrCreateInstance(document.getElementById('editIloModal')).show();
  });

  // Submit edit
  editForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearFormErrors(editErrors);
    const id = editForm.getAttribute('data-id');
    const payload = { description: document.getElementById('editIloDescription')?.value || '' };
    try {
      await putJson(`/faculty/master-data/ilo/${encodeURIComponent(id)}`, payload);
      if (typeof bootstrap !== 'undefined') bootstrap.Modal.getOrCreateInstance(document.getElementById('editIloModal')).hide();
      await loadIlos({ show: false });
      if (window.showAlertOverlay) window.showAlertOverlay('success', 'ILO updated');
    } catch (err) {
      showFormErrors(editErrors, err.message || 'Failed to update ILO.');
      if (window.showAlertOverlay) window.showAlertOverlay('danger', err.message || 'Failed to update ILO');
    }
  });

  // Open delete modal
  document.getElementById('iloTableBody')?.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-action="delete-ilo"]');
    if (!btn) return;
    const tr = btn.closest('tr');
    const id = tr?.getAttribute('data-ilo-id');
    if (!id) return;
    if (deleteIdInput) deleteIdInput.value = id;
    if (deleteTitle) deleteTitle.textContent = tr.getAttribute('data-code') || '';
    if (deleteDesc) deleteDesc.textContent = tr.getAttribute('data-description') || '';
    if (typeof bootstrap !== 'undefined') bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteIloModal')).show();
  });

  // Submit delete
  deleteForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = deleteIdInput?.value || '';
    try {
      await deleteReq(`/faculty/master-data/ilo/${encodeURIComponent(id)}`);
      if (typeof bootstrap !== 'undefined') bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteIloModal')).hide();
      await loadIlos({ show: false });
      if (window.showAlertOverlay) window.showAlertOverlay('success', 'ILO deleted');
    } catch (err) {
      console.error('Delete ILO failed', err);
      if (window.showAlertOverlay) window.showAlertOverlay('danger', err.message || 'Failed to delete ILO');
    }
  });

  // ---------------------------- Drag & Drop Reorder ----------------------------
  function enableDragAndDrop() {
    const rows = Array.from(tableBody?.querySelectorAll('tr') || []);
    let dragged = null;
    rows.forEach(row => {
      row.draggable = true;
      row.addEventListener('dragstart', (e) => {
        dragged = row;
        row.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
      });
      row.addEventListener('dragend', () => {
        if (dragged) dragged.classList.remove('dragging');
        dragged = null;
      });
      row.addEventListener('dragover', (e) => {
        e.preventDefault();
        const target = row;
        if (!dragged || dragged === target) return;
        const rect = target.getBoundingClientRect();
        const midY = rect.top + rect.height / 2;
        if (e.clientY < midY) {
          target.parentNode.insertBefore(dragged, target);
        } else {
          target.parentNode.insertBefore(dragged, target.nextSibling);
        }
      });
    });

    // Persist order on drop end with small debounce
    tableBody?.addEventListener('drop', debouncePersistOrder, { once: true });
  }

  function debounce(fn, ms) {
    let t = null;
    return function(...args) {
      clearTimeout(t);
      t = setTimeout(() => fn.apply(this, args), ms);
    };
  }
  const debouncePersistOrder = debounce(() => {
    const cid = getSelectedCourseId();
    if (!cid) return;
    // Visible IDs in their new DOM order
    const visibleIds = Array.from(tableBody?.querySelectorAll('tr[data-ilo-id]') || [])
      .map(tr => parseInt(tr.getAttribute('data-ilo-id'), 10))
      .filter(n => !Number.isNaN(n));
    if (!visibleIds.length) return;
    // Build a complete order using the full list from currentIlos: visible first, then the rest
    const allIds = (currentIlos || []).map(it => parseInt(it.id, 10)).filter(n => !Number.isNaN(n));
    const ids = [...visibleIds, ...allIds.filter(id => !visibleIds.includes(id))];
    if (!ids.length) return;
    postJson('/faculty/master-data/ilo/reorder', { course_id: cid, order: ids })
      .then(() => {
        // Reload to reflect updated sequential codes after reorder
        loadIlos({ show: false });
        if (window.showAlertOverlay) window.showAlertOverlay('success', 'Order & codes updated');
      })
      .catch((err) => { if (window.showAlertOverlay) window.showAlertOverlay('danger', err.message || 'Failed to save order'); });
  }, 250);
});
