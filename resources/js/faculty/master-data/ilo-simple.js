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
  const courseFilter = document.getElementById('iloCourseFilter');
  const addBtn = document.getElementById('iloAddBtn');
  const codeInput = document.getElementById('iloCode');
  const editCodeInput = document.getElementById('editIloCode');
  const hiddenCourseInput = document.getElementById('iloCourseId');
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

  // Auto-populate add modal code field on open
  function getSelectedCourseId() {
    const val = courseFilter?.value || 'all';
    return (val && val !== 'all') ? val : null;
  }
  function updateAddBtnState() {
    const hasCourse = !!getSelectedCourseId();
    if (addBtn) {
      // Keep clickable; just adjust the tooltip/title to guide the user
      addBtn.title = hasCourse ? 'Add ILO' : 'Select a course to add an ILO';
    }
  }
  updateAddBtnState();
  courseFilter?.addEventListener('change', () => {
    courseFilter.classList.add('is-loading');
    setTimeout(() => {
      courseFilter.classList.remove('is-loading');
      updateAddBtnState();
    }, 300);
  });
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

  // Placeholder listeners for future data-loading logic
  deptFilter?.addEventListener('change', () => {
    deptFilter.classList.add('is-loading');
    setTimeout(() => deptFilter.classList.remove('is-loading'), 300); // will be replaced by real fetch
  });
  courseFilter?.addEventListener('change', () => {
    courseFilter.classList.add('is-loading');
    setTimeout(() => courseFilter.classList.remove('is-loading'), 300); // will be replaced by real fetch
  });

  // Require a picked course before opening Add ILO modal
  const isCoursePicked = () => {
    const val = courseFilter?.value || 'all';
    return val !== 'all' && val !== '' && val != null;
  };

  function nudgeCourseFilter(message = 'Pick a course first') {
    // Visual nudge on the course filter
    if (courseFilter) {
      courseFilter.classList.add('is-loading');
      setTimeout(() => courseFilter.classList.remove('is-loading'), 700);
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
});
