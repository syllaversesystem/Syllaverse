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
  function rowHtml({ id, code, title, lec, lab, prereqIds = [], description = '', course_category = '' }) {
    const total = (Number(lec) || 0) + (Number(lab) || 0);
    const categoryBadge = course_category ? `<div class="small text-muted">${course_category}</div>` : '';
    return `
      <tr id="course-row-${id}"
          data-id="${id}"
          data-code="${String(code).replace(/"/g,'&quot;')}"
          data-title="${String(title).replace(/"/g,'&quot;')}"
          data-course-category="${String(course_category).replace(/"/g,'&quot;')}"
          data-description="${String(description).replace(/"/g,'&quot;')}"
          data-contact-hours-lec="${Number(lec) || 0}"
          data-contact-hours-lab="${Number(lab) || 0}"
          data-prereq='${JSON.stringify(prereqIds)}'>
        <td class="fw-semibold">${code}</td>
        <td class="fw-medium">${title}${categoryBadge}</td>
        <td class="text-muted prereq-cell"><span class="js-prereq-preview">—</span></td>
        <td class="text-muted">
          ${lec} Lec${lab ? ' + ' + lab + ' Lab' : ''}
          <span class="ms-1 text-secondary small">(${total} hrs)</span>
        </td>
        <td class="text-end">
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

      // Ensure there is a prereq-cell in 3rd column even on server-render
      const third = tr.children[2];
      if (third && !third.classList.contains('prereq-cell')) {
        third.classList.add('prereq-cell');
        if (!third.querySelector('.js-prereq-preview')) {
          const span = document.createElement('span');
          span.className = 'js-prereq-preview';
          span.textContent = third.textContent.trim() || '—';
          third.innerHTML = '';
          third.appendChild(span);
        }
      }
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
          <td colspan="5">
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

          if (tbody) {
            tbody.querySelector('.sv-empty-row')?.remove();
            tbody.insertAdjacentHTML('afterbegin', rowHtml({
              id: data.id, code, title, lec, lab, description, prereqIds: chosenPrereqIds, course_category
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
            old.insertAdjacentHTML('afterend', rowHtml({
              id: p.id, code: p.code, title: p.title, lec: p.lec, lab: p.lab,
              description: p.description, prereqIds: p.prereqIds, course_category: p.course_category
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
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('[data-action="delete-course"]');
      if (!btn) return;
      const tr = btn.closest('tr[id^="course-row-"]');
      if (!tr) return;
      if (!tr.closest('#svCoursesTable')) return;

      const id = tr.dataset.id;
      const code = tr.dataset.code || 'this course';
      if (!window.confirm(`Delete ${code}?`)) return;

      try {
        const fd = new FormData();
        fd.append('_method', 'DELETE');
        fd.append('_token', csrf());

        const res = await fetch(`${getUpdateBase()}/${id}`, {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
          body: fd,
        });

        if (res.ok) {
          tr.remove();
          ensureEmptyRow();
          // ✅ a deleted course may have been used as a prereq elsewhere; recompute previews
          refreshPrereqColumnForAllRows();
          rebuildAddPrereqList();
          toastSuccess('Course deleted successfully!');
        } else if (res.status === 403) {
          const data = await res.json().catch(() => ({}));
          toastError(data.message || 'Not allowed to delete this course.');
        } else {
          const text = await res.text();
          console.error(text);
          toastError('Unexpected error deleting course.');
        }
      } catch (err) {
        console.error(err);
        toastError('Network error. Please try again.');
      }
    });
  }
  // ░░░ END: INIT – Delete ░░░

  // ░░░ START: Boot ░░░
  (function boot() {
    wireModalLifecycle('addCourseModal');
    wireModalLifecycle('editCourseModal');
    wireAddOpener();

    initAdd();
    initEdit();
    initDelete();

    // Normalize server-rendered table & compute initial prerequisites previews
    hydrateRows();
    refreshPrereqColumnForAllRows();

    // Clean any stray backdrops
    document.querySelectorAll('.modal-backdrop').forEach((b) => b.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('padding-right');
  })();
  // ░░░ END: Boot ░░░
}
