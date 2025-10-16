// -------------------------------------------------------------------------------
// * File: resources/js/admin/master-data/courses.js
// * Description: AJAX add/edit/delete for Courses with searchable checkbox
//                prerequisites (Add + Edit modals). Robust after-refresh delegation.
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-16] Extracted initial AJAX handlers.
// [2025-08-17] Hardened modal lifecycle (CDN Bootstrap only) + checkbox prerequisites.
// [2025-08-17] Sync lists after add/edit/delete; fixed multi-click to open.
// [2025-08-17] Fix after refresh – robust delegation on document + hydrateRows().
// [2025-08-17] Edit modal prereqs – ALL courses shown, self excluded, prechecked current.
// [2025-08-17] ✅ Prereq column refresh – recompute table “Prerequisites” preview
//              after add/edit/delete and on boot; rowHtml updated to include col.
// -------------------------------------------------------------------------------



/* IMPORTANT:
   Do NOT `import 'bootstrap'` here. The layout already loads Bootstrap 5 bundle via CDN.
*/

// Department cells should remain untouched by prerequisite logic

if (!window.__svCoursesInit) {
  window.__svCoursesInit = true;

  // ░░░ START: Tiny DOM helpers ░░░
  const $  = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  const hasBS = () => typeof window.bootstrap?.Modal === 'function';

  function csrf() {
    return $('meta[name="csrf-token"]')?.content || $('[name="_token"]')?.value || '';
  }
  function toastSuccess(msg) {
    if (window.showAlertOverlay) window.showAlertOverlay('success', msg);
    else console.log('[SUCCESS]', msg);
  }
  function toastError(msg) {
    if (window.showAlertOverlay) window.showAlertOverlay('error', msg);
    else console.error('[ERROR]', msg);
  }
  function renderErrors(box, errors) {
    if (!box) return;
    let html = '<ul class="mb-0 ps-3">';
    Object.keys(errors || {}).forEach((k) => (errors[k] || []).forEach((m) => (html += `<li>${m}</li>`)));
    html += '</ul>';
    box.innerHTML = html;
    box.classList.remove('d-none');
  }
  function getUpdateBase() {
    const hidden = $('#editCourseUpdateUrlBase')?.value;
    if (hidden) return hidden;
    const action = $('#editCourseForm')?.getAttribute('action') || '';
    const m = action.match(/^(.*)\/\d+$/);
    return m ? m[1] : '/admin/courses';
  }
  function getTbody() {
    return $('#svCoursesTbody') || $('#svCoursesTable tbody');
  }
  // ░░░ END: Tiny DOM helpers ░░░

  // ░░░ START: Modal lifecycle & opener ░░░
  function wireModalLifecycle(id) {
    const el = document.getElementById(id);
    if (!el) return;

    el.addEventListener('shown.bs.modal', () => {
      const first = el.querySelector('input, select, textarea');
      first?.focus();
    });

    el.addEventListener('hidden.bs.modal', () => {
      setTimeout(() => {
        if (!document.querySelector('.modal.show')) {
          document.querySelectorAll('.modal-backdrop').forEach((b) => b.remove());
          document.body.classList.remove('modal-open');
          document.body.style.removeProperty('padding-right');
        }
        if (hasBS()) window.bootstrap.Modal.getInstance(el)?.dispose();
      }, 60);
    });
  }

  // Dispose stale instance & open Add reliably
  function wireAddOpener() {
    document.addEventListener('click', (e) => {
      const opener = e.target.closest('[data-bs-toggle="modal"][data-bs-target="#addCourseModal"]');
      if (!opener) return;

      e.preventDefault();
      e.stopPropagation();

      const modalEl = document.getElementById('addCourseModal');
      if (!modalEl || !hasBS()) return;

      window.bootstrap.Modal.getInstance(modalEl)?.dispose();

      const err = document.getElementById('addCourseErrors');
      if (err) { err.classList.add('d-none'); err.innerHTML = ''; }

      new window.bootstrap.Modal(modalEl).show();
    });
  }
  // ░░░ END: Modal lifecycle & opener ░░░

  // ░░░ START: Table helpers (rows + prerequisites column) ░░░
  // Build row HTML (now includes prerequisites column placeholder)
  function rowHtml({ id, code, title, lec, lab, prereqIds = [], description = '', course_category = '', department_code = '', department_id = '', department_name = '' }) {
    const total = (Number(lec) || 0) + (Number(lab) || 0);
    
    // Check if department column should be hidden
    const isDepartmentFiltered = window.coursesConfig?.departmentFilter;
    const departmentColumnStyle = isDepartmentFiltered ? 'display: none;' : '';
    
    return `
      <tr id="course-row-${id}"
          data-id="${id}"
          data-code="${String(code).replace(/"/g,'&quot;')}"
          data-title="${String(title).replace(/"/g,'&quot;')}"
          data-course-category="${String(course_category).replace(/"/g,'&quot;')}"
          data-description="${String(description).replace(/"/g,'&quot;')}"
          data-contact-hours-lec="${Number(lec) || 0}"
          data-contact-hours-lab="${Number(lab) || 0}"
          data-department-id="${department_id || ''}"
          data-department-name="${String(department_name || '').replace(/"/g,'&quot;')}"
          data-prereq='${JSON.stringify(prereqIds)}'>
        <td class="course-title-cell">${title}</td>
        <td class="course-code-cell">${code}</td>
        <td class="course-department-cell department-column" style="${departmentColumnStyle}" data-dept-code="${department_code || 'N/A'}">${department_code || 'N/A'}</td>
        <td class="course-prerequisites-cell text-muted prereq-cell"><span class="js-prereq-preview">—</span></td>
        <td class="course-contact-hours-cell text-muted">
          ${lec} Lec${lab ? ' + ' + lab + ' Lab' : ''}
          <span class="ms-1 text-secondary small">(${total} hrs)</span>
        </td>
        <td class="course-actions-cell text-end">
          <button type="button" class="btn action-btn rounded-circle me-2" data-action="edit-course"  title="Edit"   aria-label="Edit">
            <i data-feather="edit"></i>
          </button>
          <button type="button" class="btn action-btn rounded-circle"        data-action="delete-course" title="Delete" aria-label="Delete">
            <i data-feather="trash"></i>
          </button>
        </td>
      </tr>
    `;
  }

  // Normalize server-rendered rows so delegated handlers recognize buttons
  function hydrateRows() {
    const tbody = getTbody();
    if (!tbody) return;

    tbody.querySelectorAll('tr[id^="course-row-"]').forEach((tr) => {
      const actionCell = tr.querySelector('td:last-child');
      if (!actionCell) return;
      const btns = actionCell.querySelectorAll('button.btn');
      if (btns[0]) { btns[0].dataset.action = 'edit-course';   btns[0].type = 'button'; btns[0].removeAttribute('disabled'); }
      if (btns[1]) { btns[1].dataset.action = 'delete-course';  btns[1].type = 'button'; btns[1].removeAttribute('disabled'); }

      // Ensure there is a prereq-cell (find by class name instead of position)
      // IMPORTANT: Only target cells that have EXACTLY the prerequisites class, not department
      const prereqCells = tr.querySelectorAll('.course-prerequisites-cell:not(.course-department-cell)');
      
      prereqCells.forEach((prereqCell) => {
        if (prereqCell && !prereqCell.classList.contains('prereq-cell')) {
          // Double-check: ensure this is NOT a department cell
          if (prereqCell.classList.contains('course-department-cell')) {
            return;
          }
          
          prereqCell.classList.add('prereq-cell');
          if (!prereqCell.querySelector('.js-prereq-preview')) {
            const span = document.createElement('span');
            span.className = 'js-prereq-preview';
            span.textContent = prereqCell.textContent.trim() || '—';
            prereqCell.innerHTML = '';
            prereqCell.appendChild(span);
          }
        }
      });
    });

    if (window.feather) window.feather.replace();
  }

  // Build quick lookup map: courseId -> {code, title}
  function courseMapFromTable() {
    const map = new Map();
    $$('#svCoursesTable tr[id^="course-row-"]').forEach((tr) => {
      const id = String(tr.dataset.id || '');
      if (!id) return;
      map.set(id, {
        code:  tr.dataset.code  || tr.cells?.[0]?.innerText?.trim() || '',
        title: tr.dataset.title || tr.cells?.[1]?.innerText?.trim() || '',
      });
    });
    return map;
  }

  // Format prerequisites preview text: first 3 codes + “+n”
  function formatPrereqPreview(prereqIds, cmap) {
    const codes = [];
    (prereqIds || []).forEach((pid) => {
      const c = cmap.get(String(pid));
      if (c && c.code) codes.push(c.code);
    });
    codes.sort((a, b) => a.localeCompare(b));
    const top = codes.slice(0, 3);
    const extra = Math.max(codes.length - 3, 0);
    return { text: top.length ? top.join(', ') : '—', extra };
  }

  // Refresh the prerequisites column for a single row based on its data-prereq
  function refreshPrereqColumnForRow(tr, cmap = null) {
    if (!tr) return;
    const map = cmap || courseMapFromTable();
    let ids = [];
    try { ids = JSON.parse(tr.dataset.prereq || '[]'); } catch (_) { ids = []; }

    const { text, extra } = formatPrereqPreview(ids, map);
    const cell = tr.querySelector('.prereq-cell .js-prereq-preview') || tr.querySelector('.prereq-cell');
    if (!cell) return;

    // Render text + optional badge
    const parent = cell.closest('.prereq-cell') || cell.parentElement;
    parent.innerHTML = '';
    const span = document.createElement('span');
    span.className = 'js-prereq-preview';
    span.textContent = text;
    parent.appendChild(span);
    if (extra > 0) {
      const badge = document.createElement('span');
      badge.className = 'badge rounded-pill text-bg-light ms-1';
      badge.textContent = `+${extra}`;
      parent.appendChild(badge);
    }
  }

  // Refresh prerequisites column for all rows (e.g., after an edit that changed a code)
  function refreshPrereqColumnForAllRows() {
    const cmap = courseMapFromTable();
    $$('#svCoursesTable tr[id^="course-row-"]').forEach((tr) => refreshPrereqColumnForRow(tr, cmap));
  }

  function ensureEmptyRow() {
    const tbody = getTbody();
    if (!tbody) return;
    const hasRows = !!tbody.querySelector('tr[id^="course-row-"]');
    if (!hasRows) {
      tbody.insertAdjacentHTML('beforeend', `
        <tr class="sv-empty-row">
          <td colspan="6">
            <div class="sv-empty">
              <h6>No courses found</h6>
              <p>Click the <i data-feather="plus"></i> button to add one.</p>
            </div>
          </td>
        </tr>
      `);
      if (window.feather) window.feather.replace();
    }
  }

  // For building lists elsewhere
  function collectCoursesFromTable() {
    const arr = [];
    $$('#svCoursesTable tr[id^="course-row-"]').forEach((tr) => {
      const id = tr.dataset.id;
      if (!id) return;
      arr.push({
        id,
        code:  tr.dataset.code  || tr.cells?.[0]?.innerText?.trim() || '',
        title: tr.dataset.title || tr.cells?.[1]?.innerText?.trim() || '',
      });
    });
    return arr;
  }
  // ░░░ END: Table helpers ░░░

  // ░░░ START: Add modal – checkbox prerequisites + search ░░░
  function rebuildAddPrereqList() {
    const list = $('#addPrereqList');
    if (!list) return;

    const keepChecked = new Set(
      Array.from(list.querySelectorAll('input[type="checkbox"]:checked')).map((i) => i.value)
    );

    const courses = collectCoursesFromTable();
    courses.sort((a, b) => a.code.localeCompare(b.code) || a.title.localeCompare(b.title));

    const frag = document.createDocumentFragment();
    courses.forEach((c) => {
      const wrap = document.createElement('div');
      wrap.className = 'form-check form-check-sm py-1 px-1 prereq-item';
      wrap.dataset.label = `${String(c.code).toUpperCase()} ${String(c.title).toUpperCase()}`;

      const input = document.createElement('input');
      input.className = 'form-check-input';
      input.type = 'checkbox';
      input.value = String(c.id);
      input.id = `addPrereqChk-${c.id}`;
      input.name = 'prerequisite_ids[]';
      input.checked = keepChecked.has(String(c.id));

      const label = document.createElement('label');
      label.className = 'form-check-label small';
      label.htmlFor = input.id;
      label.innerHTML = `<span class="sv-chip fw-semibold">${c.code}</span> – ${c.title}`;

      wrap.appendChild(input);
      wrap.appendChild(label);
      frag.appendChild(wrap);
    });

    list.innerHTML = '';
    if (courses.length) list.appendChild(frag);
    else list.innerHTML = `<div class="text-center text-muted small py-4">No existing courses yet.</div>`;
  }

  function wireAddPrereqSearch() {
    const search = $('#addPrereqSearch');
    const list = $('#addPrereqList');
    if (!search || !list || search._svBound) return;

    const apply = () => {
      const q = (search.value || '').trim().toUpperCase();
      list.querySelectorAll('.prereq-item').forEach((el) => {
        const label = el.dataset.label || '';
        el.style.display = !q || label.includes(q) ? '' : 'none';
      });
    };

    search.addEventListener('input', apply);
    search._svBound = true;
    search.value = '';
    apply();
  }
  // ░░░ END: Add modal – checkbox prerequisites + search ░░░

  // ░░░ START: EDIT modal – ALL courses, self excluded, precheck current + search ░░░
  function rebuildEditPrereqCheckboxesFromRow(tr) {
    const list = $('#editPrereqList');
    if (!list) return;

    const currentId = String(tr.dataset.id || '');
    let currentPrereqIds = [];
    try { currentPrereqIds = JSON.parse(tr.dataset.prereq || '[]').map(String); } catch (_) {}

    const all = collectCoursesFromTable()
      .filter(c => String(c.id) !== currentId)
      .sort((a, b) => a.code.localeCompare(b.code) || a.title.localeCompare(b.title));

    if (!all.length) {
      list.innerHTML = `<div class="text-muted small py-2">No other courses available to set as prerequisites.</div>`;
      return;
    }

    const previouslyChecked = new Set(
      Array.from(list.querySelectorAll('input[type="checkbox"]:checked')).map(i => i.value)
    );

    const frag = document.createDocumentFragment();
    all.forEach(c => {
      const wrap = document.createElement('div');
      wrap.className = 'form-check form-check-sm py-1 px-1 prereq-item';
      wrap.dataset.label = `${String(c.code).toUpperCase()} ${String(c.title).toUpperCase()}`;

      const input = document.createElement('input');
      input.className = 'form-check-input';
      input.type = 'checkbox';
      input.name = 'prerequisite_ids[]';
      input.value = String(c.id);
      input.id = `editPrereqChk-${c.id}`;
      input.checked = previouslyChecked.has(String(c.id)) || currentPrereqIds.includes(String(c.id));

      const label = document.createElement('label');
      label.className = 'form-check-label small';
      label.htmlFor = input.id;
      label.innerHTML = `<span class="sv-chip fw-semibold">${c.code}</span> – ${c.title}`;

      wrap.appendChild(input);
      wrap.appendChild(label);
      frag.appendChild(wrap);
    });

    list.innerHTML = '';
    list.appendChild(frag);
  }

  function wireEditPrereqSearch() {
    const search = $('#editPrereqSearch');
    const list = $('#editPrereqList');
    if (!search || !list || search._svBound) return;

    const apply = () => {
      const q = (search.value || '').trim().toUpperCase();
      list.querySelectorAll('.prereq-item').forEach((el) => {
        const label = el.dataset.label || '';
        el.style.display = !q || label.includes(q) ? '' : 'none';
      });
    };

    search.addEventListener('input', apply);
    search._svBound = true;
    search.value = '';
    apply();
  }

  function prefillEditModalFromRow(tr) {
    const id    = tr.dataset.id;
    const code  = tr.dataset.code || '';
    const title = tr.dataset.title || '';
    const lec   = tr.dataset.contactHoursLec || '0';
    const lab   = tr.dataset.contactHoursLab || '0';
    const category = tr.dataset.courseCategory || '';
    const desc  = tr.dataset.description || '';
    const departmentId = tr.dataset.departmentId || '';

    const form = $('#editCourseForm');
    if (!form) return;

    const base = getUpdateBase();
    form.dataset.id = id;
    form.setAttribute('action', `${base}/${id}`);

    $('#editCourseCode').value         = code;
    $('#editCourseTitle').value        = title;
    $('#editCourseCategory').value     = category;
    $('#editContactHoursLec').value    = lec;
    $('#editContactHoursLab').value    = lab;
    const d = $('#editCourseDescription'); if (d) d.value = desc;
    
    // Set the department dropdown
    const deptSelect = $('#editCourseDepartment');
    if (deptSelect && departmentId) {
      deptSelect.value = departmentId;
    }

    rebuildEditPrereqCheckboxesFromRow(tr);
    wireEditPrereqSearch();
  }

  function getEditPayload() {
    const id    = $('#editCourseForm')?.dataset.id;
    const code  = $('#editCourseCode').value.trim();
    const title = $('#editCourseTitle').value.trim();
  const course_category = $('#editCourseCategory')?.value?.trim() || '';
    const lec   = Number($('#editContactHoursLec').value || 0);
    const lab   = Number($('#editContactHoursLab').value || 0);
    const description = $('#editCourseDescription')?.value || '';
    const prereqIds = Array.from($('#editPrereqList')?.querySelectorAll('input[type="checkbox"]:checked') || [])
      .map(i => Number(i.value));
  return { id, code, title, lec, lab, description, prereqIds, course_category };
  }
  // ░░░ END: EDIT modal ░░░

  // ░░░ START: INIT – Add ░░░
  function initAdd() {
    const form = $('#addCourseForm');
    const modalEl = $('#addCourseModal');
    const tbody = getTbody();
    const errorsBox = $('#addCourseErrors');
    const submitBtn = $('#addCourseSubmit');
    if (!form || !modalEl) return;

    modalEl.addEventListener('shown.bs.modal', () => {
      rebuildAddPrereqList();
      wireAddPrereqSearch();
      setupDeletedCourseSearch();
      resetCourseFormUI();
    });

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      errorsBox?.classList.add('d-none');
      if (errorsBox) errorsBox.innerHTML = '';
      submitBtn?.setAttribute('disabled', 'disabled');

      try {
        const res = await fetch(form.getAttribute('action'), {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
          body: new FormData(form),
        });

        const ct = res.headers.get('content-type') || '';
        if (!ct.includes('application/json')) { window.location.reload(); return; }

        if (res.status === 201) {
          const data = await res.json();

          const code = $('#addCourseCode').value.trim();
          const title = $('#addCourseTitle').value.trim();
          const course_category = $('#addCourseCategory')?.value?.trim() || '';
          const lec = Number($('#addContactHoursLec').value || 0);
          const lab = Number($('#addContactHoursLab').value || 0);
          const description = $('#addCourseDescription')?.value || '';
          const chosenPrereqIds = Array
            .from($('#addPrereqList')?.querySelectorAll('input[type="checkbox"]:checked') || [])
            .map(i => Number(i.value));
          
          // Get department information from form or response
          const deptSelect = $('#addCourseDepartment');
          const department_id = data.department_id || deptSelect?.value || '';
          const department_code = data.department_code || 
                                (deptSelect?.options[deptSelect.selectedIndex]?.textContent?.trim()) || '';
          const department_name = data.department_name || department_code;
          
          if (tbody) {
            tbody.querySelector('.sv-empty-row')?.remove();
            tbody.insertAdjacentHTML('afterbegin', rowHtml({
              id: data.id, code, title, lec, lab, description, prereqIds: chosenPrereqIds, course_category, 
              department_code, department_id, department_name
            }));
            const newTr = document.getElementById(`course-row-${data.id}`);
            hydrateRows();
            // ✅ render the prereq preview text for this new row
            refreshPrereqColumnForRow(newTr);
          }

          // Include the new course in the Add prerequisites list immediately
          rebuildAddPrereqList();

          form.reset();
          if (hasBS()) window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
          toastSuccess(data.message || 'Course added successfully!');
        } else if (res.status === 422) {
          const data = await res.json(); renderErrors(errorsBox, data.errors || {});
        } else if (res.status === 403) {
          const data = await res.json().catch(() => ({}));
          renderErrors(errorsBox, { _general: [data.message || 'Forbidden'] });
        } else {
          const text = await res.text();
          renderErrors(errorsBox, { _general: ['Unexpected error.', text] });
        }
      } catch (err) {
        renderErrors(errorsBox, { _general: ['Network error. Please try again.'] });
        console.error(err);
      } finally {
        submitBtn?.removeAttribute('disabled');
      }
    });
  }
  // ░░░ END: INIT – Add ░░░

  // ░░░ START: INIT – Edit ░░░
  function initEdit() {
    const form = $('#editCourseForm');
    const modalEl = $('#editCourseModal');
    const errorsBox = $('#editCourseErrors');
    const submitBtn = $('#editCourseSubmit');
    if (!form || !modalEl) return;

    document.addEventListener('click', (e) => {
      // Locate the edit button even if the click target is the inner icon
      const btn = e.target.closest('[data-action="edit-course"]');
      if (!btn) return;
      const tr = btn.closest('tr[id^="course-row-"]');
      if (!tr) return;
      // ensure this row belongs to our courses table
      if (!tr.closest('#svCoursesTable')) return;

      prefillEditModalFromRow(tr);
      if (hasBS()) window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      errorsBox?.classList.add('d-none');
      if (errorsBox) errorsBox.innerHTML = '';
      submitBtn?.setAttribute('disabled', 'disabled');

      try {
        const res = await fetch(form.getAttribute('action'), {
          method: 'POST', // _method=PUT in the form
          headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
          body: new FormData(form),
        });

        const ct = res.headers.get('content-type') || '';
        if (!ct.includes('application/json')) { window.location.reload(); return; }

        if (res.ok) {
          await res.json().catch(() => ({}));
          const p = getEditPayload();

          // Replace row HTML with updated data, including updated prereqIds
          const tbody = getTbody();
          const old = document.getElementById(`course-row-${p.id}`);
          if (tbody && old) {
            // Get department code from form or existing row
            const deptSelect = $('#editCourseDepartment');
            const department_id = p.department_id || deptSelect?.value || '';
            const department_code = p.department_code || 
                                  (deptSelect?.options[deptSelect.selectedIndex]?.textContent?.trim()) || 
                                  old.querySelector('.course-department-cell')?.textContent?.trim() || '';
            const department_name = p.department_name || department_code;
            
            old.insertAdjacentHTML('afterend', rowHtml({
              id: p.id, code: p.code, title: p.title, lec: p.lec, lab: p.lab,
              description: p.description, prereqIds: p.prereqIds, course_category: p.course_category, 
              department_code, department_id, department_name
            }));
            const newTr = old.nextElementSibling;
            old.remove();
            hydrateRows();
            // ✅ refresh the prereq preview for the updated row
            refreshPrereqColumnForRow(newTr);
            // Also recompute all previews in case a course code changed
            refreshPrereqColumnForAllRows();
          }

          // Keep Add modal list labels in sync post-edit
          rebuildAddPrereqList();

          if (hasBS()) window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
          toastSuccess('Course updated successfully!');
        } else if (res.status === 422) {
          const data = await res.json(); renderErrors(errorsBox, data.errors || {});
        } else if (res.status === 403) {
          const data = await res.json().catch(() => ({}));
          renderErrors(errorsBox, { _general: [data.message || 'Forbidden'] });
        } else {
          const text = await res.text();
          renderErrors(errorsBox, { _general: ['Unexpected error.', text] });
        }
      } catch (err) {
        console.error(err);
        renderErrors(errorsBox, { _general: ['Network error. Please try again.'] });
      } finally {
        submitBtn?.removeAttribute('disabled');
      }
    });

    modalEl.addEventListener('shown.bs.modal', () => {
      wireEditPrereqSearch();
    });
  }
  // ░░░ END: INIT – Edit ░░░

  // ░░░ START: INIT – Delete ░░░
  function initDelete() {
    const form = $('#deleteCourseForm');
    const modal = $('#deleteCourseModal');
    const titleSpan = $('#deleteCourseTitle');
    const codeSpan = $('#deleteCourseCode');
    const idInput = $('#deleteCourseId');
    const actionInput = $('#actionType');
    const confirmBtn = $('#confirmActionBtn');
    const removeRadio = $('#removeCourse');
    const deleteRadio = $('#deleteCourse');

    // Handle delete button clicks to open modal
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-action="delete-course"]');
      if (!btn) return;
      const tr = btn.closest('tr[id^="course-row-"]');
      if (!tr) return;
      if (!tr.closest('#svCoursesTable')) return;

      const id = tr.dataset.id;
      const code = tr.dataset.code || 'Unknown Course';
      const title = tr.dataset.title || 'Unknown Course';

      // Update modal content
      if (titleSpan) titleSpan.textContent = title;
      if (codeSpan) codeSpan.textContent = code;
      if (idInput) idInput.value = id;
      
      // Update form action
      if (form) {
        const base = getUpdateBase();
        form.setAttribute('action', `${base}/${id}`);
        form.dataset.courseId = id;
      }

      // Reset to remove option
      if (removeRadio) removeRadio.checked = true;
      if (actionInput) actionInput.value = 'remove';
      
      // Update button to remove style
      if (confirmBtn) {
        confirmBtn.className = 'btn btn-warning';
        confirmBtn.innerHTML = '<i data-feather="minus-circle"></i> Remove';
        // Replace feather icons with proper timing
        setTimeout(() => {
          if (window.feather) {
            window.feather.replace();
          } else if (typeof feather !== 'undefined') {
            feather.replace();
          }
        }, 10);
      }

      // Show modal
      if (hasBS()) window.bootstrap.Modal.getOrCreateInstance(modal).show();
    });

    // Handle radio button changes to update button styling
    if (removeRadio && deleteRadio && confirmBtn && actionInput) {
      removeRadio.addEventListener('change', () => {
        if (removeRadio.checked) {
          actionInput.value = 'remove';
          confirmBtn.className = 'btn btn-warning';
          confirmBtn.innerHTML = '<i data-feather="minus-circle"></i> Remove';
          // Replace feather icons with proper timing
          setTimeout(() => {
            if (window.feather) {
              window.feather.replace();
            } else if (typeof feather !== 'undefined') {
              feather.replace();
            }
          }, 10);
        }
      });

      deleteRadio.addEventListener('change', () => {
        if (deleteRadio.checked) {
          actionInput.value = 'delete';
          confirmBtn.className = 'btn btn-danger';
          confirmBtn.innerHTML = '<i data-feather="trash-2"></i> Delete';
          // Replace feather icons with proper timing
          setTimeout(() => {
            if (window.feather) {
              window.feather.replace();
            } else if (typeof feather !== 'undefined') {
              feather.replace();
            }
          }, 10);
        }
      });
    }

    // Handle form submission
    if (form) {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const courseId = form.dataset.courseId;
        if (!courseId) return;

        try {
          const formData = new FormData(form);
          
          const res = await fetch(form.getAttribute('action'), {
            method: 'POST',
            headers: { 
              'X-Requested-With': 'XMLHttpRequest', 
              'Accept': 'application/json' 
            },
            body: formData,
          });

          if (res.ok) {
            const tr = document.getElementById(`course-row-${courseId}`);
            if (tr) {
              tr.remove();
              ensureEmptyRow();
            }
            
            // Hide modal
            if (hasBS()) window.bootstrap.Modal.getOrCreateInstance(modal).hide();
            
            // Refresh prerequisites and lists
            refreshPrereqColumnForAllRows();
            rebuildAddPrereqList();
            toastSuccess('Course managed successfully!');
          } else if (res.status === 403) {
            const errorText = await res.text().catch(() => 'Access denied.');
            toastError(`Error: ${errorText}`);
          } else {
            const errorText = await res.text().catch(() => 'An error occurred.');
            toastError(`Error: ${errorText}`);
          }
        } catch (err) {
          console.error('Delete course error:', err);
          toastError('Failed to process request.');
        }
      });
    }
  }
  // ░░░ END: INIT – Delete ░░░

  // ░░░ START: Deleted Course Suggestions Functionality ░░░
  let courseSuggestionTimeout = null;

  async function searchDeletedCourses(query) {
    if (query.length < 2) return [];
    
    try {
      const response = await fetch(`/admin/courses/search-deleted?q=${encodeURIComponent(query)}`, {
        headers: {
          'X-CSRF-TOKEN': csrf(),
          'Accept': 'application/json'
        }
      });
      
      if (response.ok) {
        return await response.json();
      }
    } catch (error) {
      console.error('Error searching deleted courses:', error);
    }
    
    return [];
  }

  function showCourseSuggestions(inputElement, suggestions, suggestionsContainer) {
    if (suggestions.length === 0) {
      suggestionsContainer.style.display = 'none';
      return;
    }
    
    const html = suggestions.map(course => `
      <div class="suggestion-item" data-course='${JSON.stringify(course)}'>
        <div class="suggestion-main">
          ${course.title} (${course.code})
          <span class="suggestion-restore-badge">RESTORE</span>
        </div>
        <div class="suggestion-meta">${course.department_name}</div>
      </div>
    `).join('');
    
    suggestionsContainer.innerHTML = html;
    suggestionsContainer.style.display = 'block';
    
    // Add click handlers for suggestions
    suggestionsContainer.querySelectorAll('.suggestion-item').forEach(item => {
      item.addEventListener('click', function() {
        const course = JSON.parse(this.dataset.course);
        populateAddFormWithDeletedCourse(course);
        hideCourseSuggestions();
      });
    });
  }

  function hideCourseSuggestions() {
    const titleSuggestions = $('#courseTitleSuggestions');
    const codeSuggestions = $('#courseCodeSuggestions');
    if (titleSuggestions) titleSuggestions.style.display = 'none';
    if (codeSuggestions) codeSuggestions.style.display = 'none';
  }

  function populateAddFormWithDeletedCourse(course) {
    // Populate form fields
    const titleField = $('#addCourseTitle');
    const codeField = $('#addCourseCode');
    const categoryField = $('#addCourseCategory');
    const descField = $('#addCourseDescription');
    const lecHoursField = $('#addContactHoursLec');
    const labHoursField = $('#addContactHoursLab');
    const igaToggle = $('#addIgaToggle');
    const deptField = $('#addCourseDepartment');

    if (titleField) titleField.value = course.title;
    if (codeField) codeField.value = course.code;
    if (categoryField) categoryField.value = course.course_category || '';
    if (descField) descField.value = course.description || '';
    if (lecHoursField) lecHoursField.value = course.contact_hours_lec || 0;
    if (labHoursField) labHoursField.value = course.contact_hours_lab || 0;
    if (igaToggle) igaToggle.checked = course.has_iga || false;
    
    // Set department if dropdown exists
    if (deptField && course.department_id) {
      deptField.value = course.department_id;
    }
    
    // Show confirmation
    toastSuccess(`Populated with deleted course "${course.title}". Creating this will restore the course.`);
  }

  function setupCourseSuggestionListeners() {
    const courseTitle = $('#addCourseTitle');
    const courseCode = $('#addCourseCode');
    const titleSuggestions = $('#courseTitleSuggestions');
    const codeSuggestions = $('#courseCodeSuggestions');
    
    if (!courseTitle || !courseCode) return;
    
    // Course title input handler
    courseTitle.addEventListener('input', function() {
      const query = this.value.trim();
      
      clearTimeout(courseSuggestionTimeout);
      
      if (query.length < 2) {
        if (titleSuggestions) titleSuggestions.style.display = 'none';
        return;
      }
      
      courseSuggestionTimeout = setTimeout(async () => {
        const suggestions = await searchDeletedCourses(query);
        if (titleSuggestions) showCourseSuggestions(courseTitle, suggestions, titleSuggestions);
      }, 300);
    });
    
    // Course code input handler
    courseCode.addEventListener('input', function() {
      const query = this.value.trim();
      
      clearTimeout(courseSuggestionTimeout);
      
      if (query.length < 2) {
        if (codeSuggestions) codeSuggestions.style.display = 'none';
        return;
      }
      
      courseSuggestionTimeout = setTimeout(async () => {
        const suggestions = await searchDeletedCourses(query);
        if (codeSuggestions) showCourseSuggestions(courseCode, suggestions, codeSuggestions);
      }, 300);
    });
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.position-relative')) {
        hideCourseSuggestions();
      }
    });
    
    // Hide suggestions when modal is closed
    const addModal = $('#addCourseModal');
    if (addModal) {
      addModal.addEventListener('hidden.bs.modal', function() {
        hideCourseSuggestions();
      });
    }
  }
  // ░░░ END: Deleted Course Suggestions Functionality ░░░

  // ░░░ START: Department Filter Function ░░░
  function filterByDepartment(departmentId) {
    const url = new URL(window.location);
    if (departmentId === 'all') {
      url.searchParams.delete('department_filter');
    } else {
      url.searchParams.set('department_filter', departmentId);
    }
    window.location.href = url.toString();
  }

  // Helper function to toggle department column visibility
  function toggleDepartmentColumnVisibility(shouldHide) {
    const departmentColumns = document.querySelectorAll('.department-column');
    departmentColumns.forEach(column => {
      column.style.display = shouldHide ? 'none' : '';
    });
    
    // Update the global config
    if (window.coursesConfig) {
      window.coursesConfig.departmentFilter = shouldHide ? 'filtered' : null;
    }
  }

  // Make functions globally available
  window.filterByDepartment = filterByDepartment;
  window.toggleDepartmentColumnVisibility = toggleDepartmentColumnVisibility;
  // ░░░ END: Department Filter Function ░░░

  // ░░░ START: Deleted Course Search & Restore ░░░
  function setupDeletedCourseSearch() {
    const courseCodeInput = $('#addCourseCode');
    const courseTitleInput = $('#addCourseTitle');
    const codeContainer = $('#courseCodeSuggestions');
    const titleContainer = $('#courseTitleSuggestions');
    
    if (!courseCodeInput || !courseTitleInput || !codeContainer || !titleContainer) {
      console.log('Course search elements not found - skipping setup');
      return;
    }
    
    let codeSearchTimeout, titleSearchTimeout;
    
    console.log('Setting up deleted course search functionality');
    
    // Setup search for course code field
    setupCourseSearch(courseCodeInput, codeContainer, 'code', codeSearchTimeout);
    
    // Setup search for course title field
    setupCourseSearch(courseTitleInput, titleContainer, 'title', titleSearchTimeout);
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', (e) => {
      if (!courseCodeInput.contains(e.target) && !codeContainer.contains(e.target)) {
        hideCoursesuggestions(codeContainer);
      }
      if (!courseTitleInput.contains(e.target) && !titleContainer.contains(e.target)) {
        hideCoursesuggestions(titleContainer);
      }
    });
  }
  
  function setupCourseSearch(inputElement, containerElement, searchType, timeoutRef) {
    inputElement.addEventListener('input', (e) => {
      const query = e.target.value.trim();
      
      // Clear previous timeout
      if (timeoutRef) {
        clearTimeout(timeoutRef);
      }
      
      // Hide suggestions if query is too short
      if (query.length < 2) {
        hideCoursesuggestions(containerElement);
        return;
      }
      
      // Debounce search requests
      timeoutRef = setTimeout(() => {
        searchDeletedCourses(query, containerElement, searchType);
      }, 300);
    });
    
    // Handle suggestion clicks
    containerElement.addEventListener('click', (e) => {
      const suggestionItem = e.target.closest('.suggestion-item');
      if (!suggestionItem) return;
      
      const courseData = JSON.parse(suggestionItem.dataset.courseData);
      restoreDeletedCourse(courseData);
    });
  }
  
  function searchDeletedCourses(query, containerElement, searchType) {
    console.log(`Searching deleted courses by ${searchType}:`, query);
    
    fetch(`/admin/courses/search-deleted?q=${encodeURIComponent(query)}`, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      }
    })
    .then(response => response.json())
    .then(courses => {
      console.log('Found deleted courses:', courses);
      displayCourseSuggestions(courses, containerElement);
    })
    .catch(error => {
      console.error('Error searching deleted courses:', error);
      hideCoursesuggestions(containerElement);
    });
  }
  
  function displayCourseSuggestions(courses, containerElement) {
    if (courses.length === 0) {
      hideCoursesuggestions(containerElement);
      return;
    }
    
    const suggestionHtml = courses.map(course => `
      <div class="suggestion-item" data-course-data='${JSON.stringify(course)}'>
        <div class="suggestion-main">
          ${course.code} - ${course.title}
          <span class="suggestion-restore-badge">Restore</span>
        </div>
        <div class="suggestion-meta">
          Department: ${course.department_name} | Category: ${course.course_category}
        </div>
      </div>
    `).join('');
    
    containerElement.innerHTML = suggestionHtml;
    containerElement.style.display = 'block';
  }
  
  function hideCoursesuggestions(containerElement) {
    containerElement.style.display = 'none';
    containerElement.innerHTML = '';
  }
  
  function restoreDeletedCourse(courseData) {
    console.log('Restoring deleted course:', courseData);
    
    // Fill the form fields with the deleted course data
    const form = $('#addCourseForm');
    if (!form) return;
    
    // Populate form fields
    const codeField = $('#addCourseCode');
    const titleField = $('#addCourseTitle');
    const categoryField = $('#addCourseCategory');
    const descriptionField = $('#addCourseDescription');
    const lecHoursField = $('#addContactHoursLec');
    const labHoursField = $('#addContactHoursLab');
    const departmentField = $('#addCourseDepartment') || $('[name="department_id"]');
    
    if (codeField) codeField.value = courseData.code;
    if (titleField) titleField.value = courseData.title;
    if (categoryField && courseData.course_category) categoryField.value = courseData.course_category;
    if (descriptionField && courseData.description) descriptionField.value = courseData.description;
    if (lecHoursField && courseData.contact_hours_lec) lecHoursField.value = courseData.contact_hours_lec;
    if (labHoursField && courseData.contact_hours_lab) labHoursField.value = courseData.contact_hours_lab;
    if (departmentField && courseData.department_id) departmentField.value = courseData.department_id;
    
    // Hide all suggestions
    hideCoursesuggestions($('#courseCodeSuggestions'));
    hideCoursesuggestions($('#courseTitleSuggestions'));
    
    // Show confirmation message
    toastSuccess(`Course "${courseData.code} - ${courseData.title}" data loaded. Click "Create" to restore it.`);
    
    // Change submit button text to indicate restoration
    const submitButton = $('#addCourseSubmit');
    if (submitButton) {
      submitButton.innerHTML = '<i data-feather="refresh-cw"></i> Restore Course';
      // Re-initialize feather icons if available
      if (typeof feather !== 'undefined') {
        setTimeout(() => feather.replace(), 10);
      }
    }
  }
  
  function resetCourseFormUI() {
    // Reset button text when modal opens fresh
    const submitButton = $('#addCourseSubmit');
    if (submitButton) {
      submitButton.innerHTML = '<i data-feather="plus"></i> Create Course';
      // Re-initialize feather icons if available
      if (typeof feather !== 'undefined') {
        setTimeout(() => feather.replace(), 10);
      }
    }
    
    // Clear form
    const form = $('#addCourseForm');
    if (form) {
      form.reset();
    }
    
    // Hide any open suggestions
    hideCoursesuggestions($('#courseCodeSuggestions'));
    hideCoursesuggestions($('#courseTitleSuggestions'));
  }
  // ░░░ END: Deleted Course Search & Restore ░░░

  // ░░░ START: Boot ░░░
  (function boot() {
    wireModalLifecycle('addCourseModal');
    wireModalLifecycle('editCourseModal');
    wireAddOpener();

    initAdd();
    initEdit();
    initDelete();
    setupCourseSuggestionListeners();

    // Normalize server-rendered table & compute initial prerequisites previews
    hydrateRows();
    refreshPrereqColumnForAllRows();

    // Department cells should display the department code correctly

    // Clean any stray backdrops
    document.querySelectorAll('.modal-backdrop').forEach((b) => b.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('padding-right');

    // Department cells are now protected from prerequisite logic interference
  })();
  // ░░░ END: Boot ░░░
}
