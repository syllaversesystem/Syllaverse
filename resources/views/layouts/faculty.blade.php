{{-- 
------------------------------------------------
* File: resources/views/layouts/faculty.blade.php
* Description: Base layout with drawer and desktop collapse sidebar (Faculty – Syllaverse)
------------------------------------------------ 
--}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'Faculty • Syllaverse')</title>
  <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png" />

  {{-- Theme Meta --}}
  <meta name="theme-color" content="#EE6F57" />
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- Bootstrap + Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" defer></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />

  {{-- Feather Icons --}}
  <script src="https://unpkg.com/feather-icons"></script>

  {{-- Faculty Styles --}}
  @vite('resources/css/faculty/faculty-navbar.css')
  @vite('resources/css/faculty/faculty-sidebar.css')
  @vite('resources/css/faculty/faculty-layout.css')
  @vite('resources/css/syllaverse-colors.css')

  {{-- Syllabus Module Styles & Scripts --}}
  @vite('resources/css/faculty/syllabus.css')
  @vite('resources/js/faculty/syllabus.js')
  @vite('resources/js/faculty/syllabus-tla.js')
@vite([
  'resources/js/faculty/syllabus-sdg.js',
])

  @vite('resources/js/faculty/syllabus-textbook.js') {{-- AJAX upload handler --}}
  @vite('resources/js/faculty/syllabus-tla.js')

  @stack('styles')
</head>
<body class="bg-sv-light">
  <div class="d-flex" id="wrapper">
    @include('includes.faculty-sidebar')

    <div id="page-content-wrapper" class="w-100">
      @include('includes.faculty-navbar')

      <div id="sidebar-backdrop" class="sidebar-backdrop d-none"></div>

      <main class="container-fluid px-4 py-4">
        @yield('content')
      </main>
    </div>
  </div>

  @stack('scripts')

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (typeof feather !== 'undefined') {
        feather.replace();
      }

      const sidebar = document.getElementById('sidebar');
      const backdrop = document.getElementById('sidebar-backdrop');
      const mobileToggleBtn = document.getElementById('sidebarToggle');
      const desktopCollapseBtn = document.getElementById('sidebarCollapseBtn');
      const headers = document.querySelectorAll(".collapsible-header");

      if (localStorage.getItem('sidebar') === 'collapsed') {
        document.body.classList.add('sidebar-collapsed');
      }

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
