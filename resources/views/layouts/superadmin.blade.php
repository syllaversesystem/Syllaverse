{{-- 
-------------------------------------------------------------------------------
* File: resources/views/layouts/superadmin.blade.php
* Description: Base layout with drawer and desktop collapse sidebar (Syllaverse)
-------------------------------------------------------------------------------
📜 Log:
[2025-07-28] Initial layout with sidebar/nav includes and responsive scripts.
[2025-07-28] Removed inline JS; added `layout.js` for sidebar, collapse, theme.
[2025-08-06] Added floating alert component <x-alert-overlay /> above all content.
[2025-08-11] Fix – load Bootstrap JS via Vite (@vite('resources/js/app.js')) and render @stack('modals') at body end to avoid z-index traps.
[2025-08-12] Add – wire Master Data scripts via Vite (@vite('resources/js/superadmin/master-data/index.js')).
-------------------------------------------------------------------------------
--}}

<!DOCTYPE html>
<html lang="en">
<head>
  {{-- START: Meta & Core Setup --}}
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'Super Admin • Syllaverse')</title>
  <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png" />

  <meta name="theme-color" content="#EE6F57" />
  <meta name="mobile-web-app-capable" content="yes">

  {{-- END: Meta & Core Setup --}}

  {{-- START: CDN & Fonts (CSS only; JS is handled by Vite) --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  {{-- END: CDN & Fonts --}}

  {{-- START: Custom Vite CSS --}}
  @vite('resources/css/syllaverse-colors.css')
  @vite('resources/css/superadmin/layouts/superadmin-sidebar.css')
  @vite('resources/css/superadmin/layouts/superadmin-navbar.css')
  @vite('resources/css/superadmin/layouts/superadmin-layout.css')
 @vite('resources/css/components/alert-overlay.css')
  @vite('resources/css/superadmin/master-data.css')
  @vite('resources/css/superadmin/departments/departments.css')
  @vite('resources/css/superadmin/manage-accounts/manage-accounts.css')
  {{-- END: Custom Vite CSS --}}

  @stack('styles')
</head>
<body class="bg-sv-light">

  {{-- ✅ Floating Alert Overlay (Modular Blade Component) --}}
  <x-alert-overlay />

  <div class="d-flex" id="wrapper">
    @include('includes.superadmin-sidebar')

    <div id="page-content-wrapper" class="w-100">
      @include('includes.superadmin-navbar')

      {{-- Backdrop overlay for mobile drawer --}}
      <div id="sidebar-backdrop" class="sidebar-backdrop d-none"></div>

      <main class="container-fluid px-3 py-3">
        @yield('content')
      </main>
    </div>
  </div>

  {{-- ░░░ START: Core JS (Bootstrap via Vite) ░░░ --}}
  @vite('resources/js/app.js')
  {{-- ░░░ END: Core JS ░░░ --}}

  {{-- ░░░ START: Page bundles (must run after app.js so Bootstrap is ready) ░░░ --}}
  @vite('resources/js/superadmin/layout.js')
  @vite('resources/js/superadmin/departments.js')
  @vite('resources/js/superadmin/alert-timer.js')   {{-- ✅ Alert timer --}}
  @vite('resources/js/superadmin/chair-requests.js')
  @vite('resources/js/superadmin/appointments.js')
  @vite('resources/js/superadmin/manage-accounts/manage-accounts.js')
  @vite('resources/js/superadmin/master-data/sortable.js')
  @vite('resources/js/superadmin/master-data/index.js')
  {{-- Assessment Tasks JS removed because AssessmentTaskGroup table may be absent in this deployment --}}
  {{-- ✅ NEW: Master Data (tables + sortable split: index.js imports sortable.js) --}}
  @vite('resources/js/superadmin/master-data/index.js')
  {{-- ░░░ END: Page bundles ░░░ --}}

  {{-- Let pages push extra scripts after page bundles --}}
  @stack('scripts')

{{-- ░░░ START: Modal Outlet (placed at the end of <body>) ░░░ --}}
@stack('modals')
{{-- ░░░ END: Modal Outlet ░░░ --}}

</body>
</html>
