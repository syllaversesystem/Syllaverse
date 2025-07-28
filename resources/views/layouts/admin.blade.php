{{-- 
------------------------------------------------
* File: resources/views/layouts/admin.blade.php
* Description: Base layout with drawer + collapsible sidebar for Admin (Syllaverse)
------------------------------------------------ 
--}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'Admin • Syllaverse')</title>
  <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png" />

  {{-- Theme Meta --}}
  <meta name="theme-color" content="#EE6F57" />
  <meta name="apple-mobile-web-app-capable" content="yes" />

    {{-- ✅ CSRF Token for AJAX --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  

  {{-- Bootstrap + Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" defer></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />

  {{-- Feather Icons --}}
  <script src="https://unpkg.com/feather-icons"></script>

  {{-- Custom CSS --}}
  @vite('resources/css/admin/admin-sidebar.css')
  @vite('resources/css/admin/admin-navbar.css')
  @vite('resources/css/admin/admin-layout.css')
  @vite('resources/css/syllaverse-colors.css')
   @vite('resources/js/admin/master-data/ilo-sortable.js')

  @stack('styles')
</head>
<body class="bg-sv-light">
  <div class="d-flex" id="wrapper">
    @include('includes.admin-sidebar')

    <div id="page-content-wrapper" class="w-100">
      @include('includes.admin-navbar')

      {{-- Backdrop overlay for mobile drawer --}}
      <div id="sidebar-backdrop" class="sidebar-backdrop d-none"></div>

      <main class="container-fluid px-4 py-4">
        @yield('content')
      </main>
    </div>
  </div>

  @stack('scripts')

  {{-- Feather icon replacement --}}
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (typeof feather !== 'undefined') {
        feather.replace();
      }
    });
  </script>

  {{-- Sidebar & collapsible script --}}
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const sidebar = document.getElementById('sidebar');
      const backdrop = document.getElementById('sidebar-backdrop');
      const mobileToggleBtn = document.getElementById('sidebarToggle');
      const desktopCollapseBtn = document.getElementById('sidebarCollapseBtn');
      const headers = document.querySelectorAll(".collapsible-header");

      // Restore collapse state
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

      // Optional collapsible headers (if used)
      headers.forEach(header => {
        const targetId = header.getAttribute("data-target");
        const body = document.getElementById(targetId);

        header.setAttribute('aria-expanded', String(!body.classList.contains('collapsed')));
        header.setAttribute('aria-controls', targetId);

        header.addEventListener("click", () => {
          const isCollapsed = body.classList.contains("collapsed");
          body.classList.toggle("collapsed");
          header.setAttribute("aria-expanded", String(!isCollapsed));
        });
      });
    });
  </script>
</body>
</html>
