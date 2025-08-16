// -------------------------------------------------------------------------------
// * File: resources/js/faculty/layout.js
// * Description: Faculty layout behavior (drawer, collapse, ARIA, feather icons) – mirrors Admin/Super Admin
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-16] Initial creation – cloned from admin/layout.js, adjusted description for Faculty; 
//              preserves logo switching (text vs favicon), mobile drawer, collapse persistence.
// -------------------------------------------------------------------------------

document.addEventListener('DOMContentLoaded', () => {
  if (typeof feather !== 'undefined' && feather.replace) { feather.replace(); }

  const body = document.body;
  const sidebar = document.getElementById('sidebar');
  const backdrop = document.getElementById('sidebar-backdrop');
  const mobileToggleBtn = document.getElementById('sidebarToggle');
  const desktopCollapseBtn = document.getElementById('sidebarCollapseBtn');
  const headers = document.querySelectorAll('.collapsible-header');

  // Restore collapse state (desktop)
  try { if (localStorage.getItem('sidebar') === 'collapsed') body.classList.add('sidebar-collapsed'); } catch (_) {}

  // --- Logo sync (text vs favicon) ---
  const logoText = document.querySelector('.sidebar-logo-expanded');
  const logoIcon = document.querySelector('.sidebar-logo-collapsed');
  function syncSidebarLogo() {
    if (!logoText || !logoIcon) return;
    const isMobile = window.matchMedia('(max-width: 991.98px)').matches;
    const isCollapsed = body.classList.contains('sidebar-collapsed');

    // Reset
    logoText.classList.remove('d-none');
    logoIcon.classList.remove('d-none');

    if (isMobile) {
      // Mobile → favicon only
      logoText.classList.add('d-none');
      logoIcon.classList.remove('d-none');
    } else {
      // Desktop → text when expanded, favicon when collapsed
      if (isCollapsed) {
        logoText.classList.add('d-none');
        logoIcon.classList.remove('d-none');
      } else {
        logoText.classList.remove('d-none');
        logoIcon.classList.add('d-none');
      }
    }
  }
  syncSidebarLogo();

  // Debounced resize sync
  let resizeTO;
  window.addEventListener('resize', () => {
    clearTimeout(resizeTO);
    resizeTO = setTimeout(syncSidebarLogo, 120);
  });

  // Mobile drawer toggle
  function toggleMobileSidebar() {
    if (!sidebar || !backdrop) return;
    sidebar.classList.toggle('collapsed');
    backdrop.classList.toggle('d-none');
  }
  function closeMobileSidebar() {
    if (!sidebar || !backdrop) return;
    sidebar.classList.remove('collapsed');
    backdrop.classList.add('d-none');
    if (mobileToggleBtn) mobileToggleBtn.setAttribute('aria-expanded', 'false');
  }
  if (mobileToggleBtn && backdrop) {
    mobileToggleBtn.setAttribute('aria-controls', 'sidebar');
    mobileToggleBtn.setAttribute('aria-expanded', 'false');
    mobileToggleBtn.addEventListener('click', () => {
      toggleMobileSidebar();
      const expanded = mobileToggleBtn.getAttribute('aria-expanded') === 'true';
      mobileToggleBtn.setAttribute('aria-expanded', String(!expanded));
    });
    backdrop.addEventListener('click', closeMobileSidebar);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeMobileSidebar(); });
  }

  // Desktop collapse toggle
  if (desktopCollapseBtn) {
    desktopCollapseBtn.setAttribute('aria-controls', 'sidebar');
    desktopCollapseBtn.setAttribute('aria-expanded', String(!body.classList.contains('sidebar-collapsed')));
    desktopCollapseBtn.addEventListener('click', () => {
      body.classList.toggle('sidebar-collapsed');
      const isCollapsed = body.classList.contains('sidebar-collapsed');
      try { localStorage.setItem('sidebar', isCollapsed ? 'collapsed' : 'expanded'); } catch (_) {}
      desktopCollapseBtn.setAttribute('aria-expanded', String(!isCollapsed));
      syncSidebarLogo(); // keep logos correct
    });
  }

  // Optional collapsible headers
  headers.forEach((header) => {
    const targetId = header.getAttribute('data-target');
    const bodyEl = targetId ? document.getElementById(targetId) : null;
    if (!bodyEl) return;
    header.setAttribute('aria-controls', targetId);
    header.setAttribute('aria-expanded', String(!bodyEl.classList.contains('collapsed')));
    header.addEventListener('click', () => {
      const willShow = bodyEl.classList.contains('collapsed');
      bodyEl.classList.toggle('collapsed');
      header.setAttribute('aria-expanded', String(willShow));
    });
  });
});
