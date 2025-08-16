// -----------------------------------------------------------------------------
// * File: resources/js/admin/master-data/ilo-sortable.js
// * Description: Drag-and-drop reorder for ILO table + AJAX dropdown loader
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Initial (list-based).
// [2025-08-18] Refactor – table-based Sortable + Save Order AJAX.
// [2025-08-18] NEW – AJAX course dropdown: fetch ILOs, rebuild table, rewire Sortable.
// -----------------------------------------------------------------------------

import Sortable from 'sortablejs';

const $  = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

function csrf() {
  return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function setSaveEnabled(btn, enabled) {
  if (!btn) return;
  if (enabled) {
    btn.removeAttribute('disabled');
    btn.setAttribute('aria-disabled', 'false');
  } else {
    btn.setAttribute('disabled', 'disabled');
    btn.setAttribute('aria-disabled', 'true');
  }
}

function updateVisibleIloCodes(tbody) {
  const rows = $$('tr[data-id]', tbody);
  rows.forEach((tr, idx) => {
    const codeCell = tr.querySelector('.sv-code') || tr.querySelector('td:nth-child(2)');
    if (codeCell) codeCell.textContent = `ILO${idx + 1}`;
  });
}

function collectOrderedIds(tbody) {
  return $$('tr[data-id]', tbody).map(tr => parseInt(tr.getAttribute('data-id'), 10));
}

// Keep a single Sortable instance so we can destroy/re-init when the table is rebuilt
window.SV_ILO_SORTABLE = window.SV_ILO_SORTABLE || null;

function initSortableForTable(tbody, saveBtn) {
  // Destroy any previous instance
  if (window.SV_ILO_SORTABLE && window.SV_ILO_SORTABLE.el && document.body.contains(window.SV_ILO_SORTABLE.el)) {
    try { window.SV_ILO_SORTABLE.destroy(); } catch (e) {}
    window.SV_ILO_SORTABLE = null;
  }

  let dirty = false;
  window.SV_ILO_SORTABLE = Sortable.create(tbody, {
    animation: 150,
    handle: '.sv-row-grip',
    ghostClass: 'bg-light',
    onEnd: () => {
      updateVisibleIloCodes(tbody);
      dirty = true;
      setSaveEnabled(saveBtn, true);
    },
  });

  // Attach (or re-attach) Save handler
  // Remove existing listener by cloning (simple pattern to avoid double-binding)
  const newSaveBtn = saveBtn.cloneNode(true);
  saveBtn.parentNode.replaceChild(newSaveBtn, saveBtn);

  newSaveBtn.addEventListener('click', async () => {
    if (!dirty) return;
    const table = $('#svIloTable');
    const courseId = table?.getAttribute('data-course-id');
    const orderedIds = collectOrderedIds(tbody);

    setSaveEnabled(newSaveBtn, false);
    const orig = newSaveBtn.innerHTML;
    newSaveBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving…`;

    try {
      const res = await fetch('/admin/master-data/reorder/ilo', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf(),
          'Accept': 'application/json',
        },
        body: JSON.stringify({ ids: orderedIds, course_id: courseId }),
      });

      const ok = res.ok;
      const data = await res.json().catch(() => ({}));

      if (!ok) {
        const msg = data?.message || 'Failed to save ILO order.';
        window.showAlertOverlay?.('error', msg) || alert(msg);
        setSaveEnabled(newSaveBtn, true);
        newSaveBtn.innerHTML = orig;
        return;
      }

      dirty = false;
      setSaveEnabled(newSaveBtn, false);
      newSaveBtn.innerHTML = orig;
      updateVisibleIloCodes(tbody);
      window.showAlertOverlay?.('success', data.message || 'ILO order saved successfully!');
    } catch (err) {
      console.error('ILO reorder error:', err);
      window.showAlertOverlay?.('error', 'Network error while saving ILO order.') || alert('Network error while saving ILO order.');
      setSaveEnabled(newSaveBtn, true);
      newSaveBtn.innerHTML = orig;
    }
  });

  // Initial state
  updateVisibleIloCodes(tbody);
  setSaveEnabled(newSaveBtn, false);
}

/** Build a single ILO row */
function rowHtml(ilo) {
  // Keep your existing action structure (Edit button + form delete) if you’re still using it.
  // If you moved ILO CRUD elsewhere, adjust actions accordingly.
  const deleteAction = `
    <form method="POST" action="/admin/master-data/ilo/${ilo.id}" class="d-inline" onsubmit="return confirm('Delete this ILO?')">
      <input type="hidden" name="_token" value="${csrf()}">
      <input type="hidden" name="_method" value="DELETE">
      <button type="submit" class="btn action-btn rounded-circle delete">
        <i data-feather="trash"></i>
      </button>
    </form>`;

  return `
    <tr data-id="${ilo.id}">
      <td class="text-muted"><i class="sv-row-grip bi bi-grip-vertical fs-5" style="cursor:move;"></i></td>
      <td class="sv-code fw-semibold">${ilo.code}</td>
      <td class="text-muted">${(ilo.description ?? '').toString()}</td>
      <td class="text-end">
        <button type="button"
                class="btn action-btn rounded-circle edit me-2"
                data-bs-toggle="modal"
                data-bs-target="#editIloModal"
                data-id="${ilo.id}"
                data-description="${(ilo.description ?? '').toString()}">
          <i data-feather="edit"></i>
        </button>
        ${deleteAction}
      </td>
    </tr>`;
}

/** Rebuild the table body with fetched ILOs */
function rebuildIloTable(ilos, selectedCourseId) {
  const table = $('#svIloTable');
  const tbody = table?.querySelector('tbody');
  const saveBtn = $('#save-ilo-order');
  if (!table || !tbody || !saveBtn) return;

  // Update course context on elements
  table.setAttribute('data-course-id', selectedCourseId);
  saveBtn.setAttribute('data-course-id', selectedCourseId);

  if (!ilos.length) {
    tbody.innerHTML = `
      <tr class="sv-empty-row">
        <td colspan="4">
          <div class="sv-empty">
            <h6>No ILOs found</h6>
            <p>Select a course and click the <i data-feather="plus"></i> button to add one.</p>
          </div>
        </td>
      </tr>`;
  } else {
    tbody.innerHTML = ilos.map(rowHtml).join('');
  }

  // Re-render icons and rewire sortable
  if (window.feather) window.feather.replace();
  initSortableForTable(tbody, saveBtn);
}

/** Intercept the filter form submit to AJAX-load ILOs. Works even if Blade has onchange="this.form.submit()". */
function bootIloFilterAjax() {
  // Try to locate the exact filter form above the table:
  // it contains hidden tab/subtab and a select[name=course_id]
  const filterForm = (() => {
    // Prefer an explicit ID if you add one later (e.g., #iloFilterForm)
    const byId = document.getElementById('iloFilterForm');
    if (byId) return byId;

    // Fallback: find a form that posts to /admin/master-data (GET) and has course_id select
    const forms = $$('form[method="GET"]');
    return forms.find(f => f.querySelector('select[name="course_id"]')) || null;
  })();

  if (!filterForm) return;

  filterForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const select = filterForm.querySelector('select[name="course_id"]');
    const courseId = select?.value || '';
    if (!courseId) {
      // Clear the table if "Select a Course"
      rebuildIloTable([], '');
      return;
    }

    try {
      const url = `/admin/master-data/ilos?course_id=${encodeURIComponent(courseId)}`;
      const res = await fetch(url, { headers: { 'Accept': 'application/json' }});
      if (!res.ok) throw new Error('Failed to fetch ILOs.');
      const data = await res.json();

      rebuildIloTable(Array.isArray(data.ilos) ? data.ilos : [], courseId);
    } catch (err) {
      console.error('ILO fetch error:', err);
      window.showAlertOverlay?.('error', 'Unable to load ILOs for the selected course.') || alert('Unable to load ILOs.');
    }
  });

  // Also react to dropdown changes by submitting the form (captured by our submit listener)
  const select = filterForm.querySelector('select[name="course_id"]');
  if (select) {
    select.addEventListener('change', () => {
      // If Blade still has onchange="this.form.submit()", our submit handler will catch it and AJAX it.
      // If not, we trigger submit here so it works either way.
      filterForm.requestSubmit ? filterForm.requestSubmit() : filterForm.submit();
    });
  }
}

/** Boot */
document.addEventListener('DOMContentLoaded', () => {
  // Only boot on pages that have the ILO table
  if (!document.getElementById('svIloTable')) return;

  // Initialize Sortable on current table (if a course is already selected server-side)
  const table = $('#svIloTable');
  const tbody = table?.querySelector('tbody');
  const saveBtn = $('#save-ilo-order');
  if (table && tbody && saveBtn) {
    initSortableForTable(tbody, saveBtn);
  }

  // Wire AJAX filter
  bootIloFilterAjax();
});
