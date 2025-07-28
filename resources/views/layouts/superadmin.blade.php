{{-- 
------------------------------------------------------------------------------
* File: resources/views/layouts/superadmin.blade.php
* Description: Base layout with drawer and desktop collapse sidebar (Syllaverse)
------------------------------------------------------------------------------
📜 Log:
[2025-07-28] Initial layout with sidebar/nav includes and responsive scripts.
[2025-07-28] Removed inline JS; added `layout.js` for sidebar, collapse, theme.
------------------------------------------------------------------------------
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
  <meta name="apple-mobile-web-app-capable" content="yes" />
  {{-- END: Meta & Core Setup --}}

  {{-- START: CDN & Fonts --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  {{-- END: CDN & Fonts --}}

  {{-- START: Custom Vite CSS --}}
  @vite('resources/css/superadmin/superadmin-sidebar.css')
  @vite('resources/css/superadmin/superadmin-navbar.css')
  @vite('resources/css/superadmin/superadmin-layout.css')
  @vite('resources/css/syllaverse-colors.css')
  @vite('resources/css/superadmin/manage-account.css')
  @vite('resources/css/superadmin/master-data.css')
  {{-- END: Custom Vite CSS --}}

  @stack('styles')
</head>
<body class="bg-sv-light">
  <div class="d-flex" id="wrapper">
    @include('includes.superadmin-sidebar')

    <div id="page-content-wrapper" class="w-100">
      @include('includes.superadmin-navbar')

      {{-- Backdrop overlay for mobile drawer --}}
      <div id="sidebar-backdrop" class="sidebar-backdrop d-none"></div>

      <main class="container-fluid px-4 py-4">
        @yield('content')
      </main>
    </div>
  </div>

  @stack('scripts')

  {{-- START: Vite JS --}}
  @vite('resources/js/superadmin/layout.js')
  {{-- END: Vite JS --}}
</body>
</html>
