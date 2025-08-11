/* 
-------------------------------------------------------------------------------
* File: resources/js/superadmin/layout.js
* Description: Handles Super Admin layout behavior – toggles, theme, feather icons
-------------------------------------------------------------------------------
📜 Log:
[2025-07-28] Initial version – moved all inline layout JS into Vite-compatible external script
[2025-08-05] Added sidebar logo toggle logic to remove logo gap when collapsed.
[2025-08-11] Feather – Vite import + global init; refresh on tabs/modals/collapses and custom sv:dom:update; expose helpers.
-------------------------------------------------------------------------------
*/

import feather from 'feather-icons';
import 'bootstrap';

// ░░░ START: Feather Icons (global) ░░░
// Plain-English: Make <i data-feather="..."> swap to SVG on load and after UI changes.
function replaceFeatherIcons() {
  try { feather.replace(); } catch { /* noop */ }
}

/** Public helpers for other modules */
window.feather = feather;                 // allow window.feather?.replace?.() in feature files
window.svRefreshIcons = replaceFeatherIcons; // optional hook other scripts can call

function initFeather() {
  const run = () => setTimeout(replaceFeatherIcons, 0);

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run, { once: true });
  } else {
    run();
  }

  // Refresh after common Bootstrap lifecycle events
  ['shown.bs.tab', 'shown.bs.modal', 'hidden.bs.modal', 'shown.bs.collapse', 'hidden.bs.collapse']
    .forEach(evt => document.addEventListener(evt, run));

  // Custom hook for any AJAX DOM updates in the app
  window.addEventListener('sv:dom:update', run);
}
// ░░░ END: Feather Icons (global) ░░░


// ░░░ START: Sidebar Behavior and Collapsible Logic ░░░
function initSidebarAndCollapsibles() {
  const sidebar = document.getElementById('sidebar');
  const backdrop = document.getElementById('sidebar-backdrop');
  const mobileToggleBtn = document.getElementById('sidebarToggle');
  const desktopCollapseBtn = document.getElementById('sidebarCollapseBtn');
  const headers = document.querySelectorAll('.collapsible-header');

  // Logo visibility toggle based on sidebar state
  function updateSidebarLogos() {
    const expandedLogo = document.querySelector('.sidebar-logo-expanded');
    const collapsedLogo = document.querySelector('.sidebar-logo-collapsed');
    const isCollapsed = document.body.classList.contains('sidebar-collapsed');

    if (expandedLogo && collapsedLogo) {
      if (isCollapsed) {
        expandedLogo.classList.add('d-none');
        collapsedLogo.classList.remove('d-none');
      } else {
        expandedLogo.classList.remove('d-none');
        collapsedLogo.classList.add('d-none');
      }
    }
  }

  // Restore collapse state from localStorage
  if (localStorage.getItem('sidebar') === 'collapsed') {
    document.body.classList.add('sidebar-collapsed');
  }

  // Initial logo setup on load
  updateSidebarLogos();

  // Mobile drawer toggle
  function toggleMobileSidebar() {
    if (!sidebar || !backdrop) return;
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

      // Update logos immediately on toggle
      updateSidebarLogos();
      // Icons may shift; refresh just in case
      replaceFeatherIcons();
    });
  }

  // Collapsible nav section headers
  headers.forEach(header => {
    const targetId = header.getAttribute('data-target');
    const body = document.getElementById(targetId);
    if (!body) return;

    header.setAttribute('aria-expanded', String(!body.classList.contains('collapsed')));
    header.setAttribute('aria-controls', targetId);

    header.addEventListener('click', () => {
      const isCollapsed = body.classList.contains('collapsed');
      body.classList.toggle('collapsed');
      header.setAttribute('aria-expanded', String(!isCollapsed));
    });
  });
}
// ░░░ END: Sidebar Behavior and Collapsible Logic ░░░


// ░░░ START: Theme Toggle Logic (Navbar) ░░░
function initThemeToggle() {
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

    // Theme change may alter contrast around icons; refresh to be safe
    replaceFeatherIcons();
  });
}
// ░░░ END: Theme Toggle Logic (Navbar) ░░░


// ░░░ START: Bootstrapper ░░░
function start() {
  initFeather();
  initSidebarAndCollapsibles();
  initThemeToggle();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', start);
} else {
  start();
}
// ░░░ END: Bootstrapper ░░░
