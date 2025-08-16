{{-- 
-------------------------------------------------------------------------------
* File: resources/views/layouts/admin.blade.php
* Description: Base layout with drawer + collapsible sidebar for Admin (mirrors Super Admin) – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-08-09] Aligned structure with Super Admin; added <x-alert-overlay />, included alerts CSS/JS, externalized sidebar logic to resources/js/admin/layout.js, moved page JS includes to bottom.
[2025-08-16] Fixed modal.js error – cleaned up Bootstrap imports, switched to single bootstrap.bundle.min.js (5.3.3).
-------------------------------------------------------------------------------
--}}
<!DOCTYPE html>
<html lang="en">
<head>
  {{-- ░░░ START: Meta & Core Setup ░░░ --}}
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'Admin • Syllaverse')</title>
  <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png" />
  <meta name="theme-color" content="#EE6F57" />
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  {{-- ░░░ END: Meta & Core Setup ░░░ --}}

  {{-- ░░░ START: CDN & Fonts ░░░ --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <script src="https://unpkg.com/feather-icons" defer></script>
  {{-- ✅ Use only bundle version (includes Popper) --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
  {{-- ░░░ END: CDN & Fonts ░░░ --}}

  {{-- ░░░ START: Custom Vite CSS ░░░ --}}
  @vite('resources/css/syllaverse-colors.css')
  @vite('resources/css/admin/admin-sidebar.css')
  @vite('resources/css/admin/admin-navbar.css')
  @vite('resources/css/admin/admin-layout.css')

  @vite('resources/css/superadmin/manage-accounts/manage-accounts.css')
  @vite('resources/css/superadmin/departments/departments.css')
  @vite('resources/css/components/alert-overlay.css')
  {{-- ░░░ END: Custom Vite CSS ░░░ --}}

  @stack('styles')
</head>
<body class="bg-sv-light">
  {{-- ░░░ START: Floating Alert Overlay (Shared Component) ░░░ --}}
  <x-alert-overlay />
  {{-- ░░░ END: Floating Alert Overlay ░░░ --}}

  <div class="d-flex" id="wrapper">
    {{-- ░░░ START: Sidebar Include ░░░ --}}
    @include('includes.admin-sidebar')
    {{-- ░░░ END: Sidebar Include ░░░ --}}

    <div id="page-content-wrapper" class="w-100">
      {{-- ░░░ START: Top Navbar Include ░░░ --}}
      @include('includes.admin-navbar')
      {{-- ░░░ END: Top Navbar Include ░░░ --}}

      {{-- ░░░ START: Backdrop overlay for mobile drawer ░░░ --}}
      <div id="sidebar-backdrop" class="sidebar-backdrop d-none"></div>
      {{-- ░░░ END: Backdrop overlay for mobile drawer ░░░ --}}

      {{-- ░░░ START: Page Content ░░░ --}}
      <main class="container-fluid px-4 py-4">
        @yield('content')
      </main>
      {{-- ░░░ END: Page Content ░░░ --}}
    </div>
  </div>

  @stack('scripts')

  {{-- ░░░ START: Vite JS (Global) ░░░ --}}
  @vite('resources/js/admin/layout.js')                {{-- Sidebar/drawer + ARIA + feather.replace --}}
  @vite('resources/js/superadmin/alert-timer.js')      {{-- Shared alert auto-hide --}}
  @vite('resources/js/admin/master-data/ilo-sortable.js') {{-- Example page-specific --}}
  @vite('resources/js/admin/master-data/so-sortable.js') {{-- Example page-specific --}}
  @vite('resources/js/admin/master-data/programs.js')
  @vite('resources/js/admin/master-data/courses.js')
  @vite('resources/js/admin/master-data/so.js')


  {{-- ░░░ END: Vite JS (Global) ░░░ --}}


</body>
</html>
