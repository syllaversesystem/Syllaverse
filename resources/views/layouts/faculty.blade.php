{{-- 
-------------------------------------------------------------------------------
* File: resources/views/layouts/faculty.blade.php
* Description: Base layout with drawer + collapsible sidebar for Faculty – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-08-16] Synced structure with Admin layout; added <x-alert-overlay />, standardized Vite includes, moved sidebar logic to faculty/layout.js.
-------------------------------------------------------------------------------
--}}
<!DOCTYPE html>
<html lang="en">
<head>
  {{-- ░░░ START: Meta & Core Setup ░░░ --}}
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'Faculty • Syllaverse')</title>
  <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png" />
  <meta name="theme-color" content="#EE6F57" />
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  {{-- ░░░ END: Meta & Core Setup ░░░ --}}

  {{-- ░░░ START: CDN & Fonts ░░░ --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <script src="https://unpkg.com/feather-icons" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" defer></script>
  {{-- ░░░ END: CDN & Fonts ░░░ --}}

  {{-- ░░░ START: Custom Vite CSS ░░░ --}}
  @vite('resources/css/syllaverse-colors.css')
  @vite('resources/css/faculty/faculty-sidebar.css')
  @vite('resources/css/faculty/faculty-navbar.css')
  @vite('resources/css/faculty/faculty-layout.css')
  @vite('resources/css/superadmin/alerts.css') {{-- shared alert styles --}}
  @vite('resources/css/components/alert-overlay.css')

  {{-- Faculty-specific modules --}}
  @vite('resources/css/faculty/syllabus.css')
  {{-- ░░░ END: Custom Vite CSS ░░░ --}}

  @stack('styles')
</head>
<body class="bg-sv-light">
  {{-- ░░░ START: Floating Alert Overlay (Shared Component) ░░░ --}}
  <x-alert-overlay />
  {{-- ░░░ END: Floating Alert Overlay ░░░ --}}

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

  {{-- ░░░ START: Vite JS ░░░ --}}
  @vite('resources/js/faculty/layout.js')              {{-- Sidebar/drawer logic --}}
  @vite('resources/js/superadmin/alert-timer.js')      {{-- Shared alert auto-hide --}}
  @vite('resources/js/faculty/syllabus.js')
  @vite('resources/js/faculty/syllabus-sdg.js')
  @vite('resources/js/faculty/syllabus-textbook.js')
  @vite('resources/js/faculty/syllabus-tla.js')
  @vite('resources/js/faculty/syllabus-tla-ai.js')
  {{-- ░░░ END: Vite JS ░░░ --}}
</body>
</html>
