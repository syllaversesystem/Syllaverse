// -----------------------------------------------------------------------------
// File: resources/js/superadmin/layout.js
// Description: Handles Super Admin layout behavior – toggles, theme, feather icons
// -----------------------------------------------------------------------------

// 📜 Log:
// [2025-07-28] Initial version – moved all inline layout JS into Vite-compatible external script
// -----------------------------------------------------------------------------

import feather from 'feather-icons';
import 'bootstrap';

// START: Feather Icon Replacement
document.addEventListener('DOMContentLoaded', function () {
  if (typeof feather !== 'undefined') {
    feather.replace();
  }
});
// END: Feather Icon Replacement

// START: Sidebar Behavior and Collapsible Logic
document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.getElementById('sidebar');
  const backdrop = document.getElementById('sidebar-backdrop');
  const mobileToggleBtn = document.getElementById('sidebarToggle');
  const desktopCollapseBtn = document.getElementById('sidebarCollapseBtn');
  const headers = document.querySelectorAll(".collapsible-header");

  // Restore collapse state from localStorage
  if (localStorage.getItem('sidebar') === 'collapsed') {
    document.body.classList.add('sidebar-collapsed');
  }

  // Mobile drawer toggle
  function toggleMobileSidebar() {
    sidebar.classList.toggle('collapsed');
    backdrop.classList.toggle('d-none');
  }

  if (mobileToggleBtn && backdrop) {
    mobileToggleBtn.setAttribute('aria-controls', 'sidebar');
    mobileToggleBtn.setAttribute('aria-expanded', 'false');

    mobileToggleBtn.addEventListener('click', () => {
      toggleMobileSidebar();
      const expanded = mobileToggleBtn.getAttribute('aria-expanded') === 'true';
      mobileToggleBtn.setAttribute('aria-expanded', String(!expanded));
    });

    backdrop.addEventListener('click', toggleMobileSidebar);
  }

  // Desktop collapse toggle
  if (desktopCollapseBtn) {
    desktopCollapseBtn.setAttribute('aria-controls', 'sidebar');
    desktopCollapseBtn.setAttribute('aria-expanded', String(!document.body.classList.contains('sidebar-collapsed')));

    desktopCollapseBtn.addEventListener('click', () => {
      document.body.classList.toggle('sidebar-collapsed');
      const isCollapsed = document.body.classList.contains('sidebar-collapsed');
      localStorage.setItem('sidebar', isCollapsed ? 'collapsed' : 'expanded');
      desktopCollapseBtn.setAttribute('aria-expanded', String(!isCollapsed));
    });
  }

  // Collapsible nav section headers
  headers.forEach(header => {
    const targetId = header.getAttribute("data-target");
    const body = document.getElementById(targetId);

    if (!body) return;

    header.setAttribute('aria-expanded', String(!body.classList.contains('collapsed')));
    header.setAttribute('aria-controls', targetId);

    header.addEventListener("click", () => {
      const isCollapsed = body.classList.contains("collapsed");
      body.classList.toggle("collapsed");
      header.setAttribute("aria-expanded", String(!isCollapsed));
    });
  });
});
// END: Sidebar Behavior and Collapsible Logic

// START: Theme Toggle Logic (Navbar)
document.addEventListener('DOMContentLoaded', () => {
  const themeBtn = document.getElementById('themeToggleBtn');
  if (!themeBtn) return;

  themeBtn.addEventListener('click', function () {
    document.body.classList.toggle('dark-theme');

    const icon = this.querySelector('i');
    icon?.classList.toggle('bi-moon');
    icon?.classList.toggle('bi-brightness-high');
    icon?.classList.add('theme-anim');

    setTimeout(() => {
      icon?.classList.remove('theme-anim');
    }, 300);
  });
});
// END: Theme Toggle Logic
