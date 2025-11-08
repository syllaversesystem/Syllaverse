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
  if (addModalEl && searchInput) {
    addModalEl.addEventListener('hidden.bs.modal', () => searchInput.focus());
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
