{{-- 
-------------------------------------------------------------------------------
* File: resources/views/layouts/faculty.blade.php
* Description: Base layout with drawer + collapsible sidebar for Faculty – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-08-16] Synced structure with Admin layout; added <x-alert-overlay />, standardized Vite includes, moved sidebar logic to faculty/layout.js.
[2025-08-18] UI tweak – matched Super Admin content spacing (container-fluid px-4 py-4).
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
  <meta name="mobile-web-app-capable" content="yes" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  {{-- ░░░ END: Meta & Core Setup ░░░ --}}

  {{-- ░░░ START: CDN & Fonts ░░░ --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" defer></script>
  {{-- ░░░ END: CDN & Fonts ░░░ --}}

  {{-- ░░░ START: Custom Vite CSS ░░░ --}}
  @vite('resources/css/syllaverse-colors.css')
  @vite('resources/css/faculty/faculty-sidebar.css')
  @vite('resources/css/faculty/faculty-navbar.css')
  @vite('resources/css/faculty/faculty-layout.css')
  {{-- shared alert styles: only include if Vite manifest contains the entry to avoid 404s when manifest is stale --}}
  @php
    $manifestPath = public_path('build/manifest.json');
    $manifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : null;
  @endphp
  @if (is_array($manifest) && array_key_exists('resources/css/superadmin/alerts.css', $manifest))
    @vite('resources/css/superadmin/alerts.css')
  @endif
  @vite('resources/css/components/alert-overlay.css')

  {{-- Faculty-specific modules --}}
  @vite('resources/css/faculty/syllabus.css')
  @vite('resources/css/superadmin/departments/departments.css')
  @vite('resources/css/superadmin/manage-accounts/manage-accounts.css')
  {{-- ░░░ END: Custom Vite CSS ░░░ --}}

  @stack('styles')
</head>
<body class="bg-sv-light {{ isset($fullscreen) && $fullscreen ? 'fullscreen-mode' : '' }}">
  {{-- ░░░ START: Floating Alert Overlay (Shared Component) ░░░ --}}
  <x-alert-overlay />
  {{-- ░░░ END: Floating Alert Overlay ░░░ --}}

  @php $fullscreen = $fullscreen ?? (isset($__env) && $__env->yieldContent('fullscreen')); @endphp
  @if(isset($fullscreen) && $fullscreen)
    {{-- Fullscreen content (no sidebar/navbar) no outer padding; inner views handle spacing --}}
    <main class="w-100" style="min-height:100vh; margin:0;">
      <div class="fullscreen-inner" style="max-width:1400px; margin:0 auto;">
        @yield('content')
      </div>
    </main>
  @else
    <div class="d-flex" id="wrapper">
      @include('includes.faculty-sidebar')
      <div id="page-content-wrapper" class="w-100">
        @include('includes.faculty-navbar')
        <div id="sidebar-backdrop" class="sidebar-backdrop d-none"></div>

        {{-- ✅ Reduced container padding to 16px --}}
        <main class="container-fluid" style="padding: 16px;">
          @yield('content')
        </main>
      </div>
    </div>
  @endif

  @stack('scripts')

  {{-- ░░░ START: Vite JS ░░░ --}}
  @vite('resources/js/faculty/layout.js')              {{-- Sidebar/drawer logic --}}
  @vite('resources/js/superadmin/alert-timer.js')      {{-- Shared alert auto-hide --}}
  @vite('resources/js/components/alert-overlay.js')    {{-- Overlay controller (listens to sv:alert) --}}
  @vite('resources/js/faculty/syllabus.js')
  @vite('resources/js/faculty/syllabus-course-info.js')
  @vite('resources/js/faculty/syllabus-sdg.js')
  @vite('resources/js/faculty/syllabus-textbook.js')
  @vite('resources/js/faculty/syllabus-tla.js')
  @vite('resources/js/faculty/syllabus-assessment-mapping.js')
  @vite('resources/js/faculty/syllabus-ai-chat.js')
  @vite('resources/js/faculty/ai/prompts.js')
  @vite('resources/js/faculty/ai/snapshot.js')
  @vite('resources/js/faculty/ai/assessment-map.js')
  @vite('resources/js/faculty/ai/ilo-so-cpa-mapping.js')
  @vite('resources/js/faculty/ai/assessment-schedule.js')
  @vite('resources/js/faculty/ai/ilo-iga-mapping.js')
  @vite('resources/js/faculty/ai/ilo-cdio-sdg-mapping.js')
  @vite('resources/js/faculty/ai/chat-panel.js')
  {{-- ░░░ END: Vite JS ░░░ --}}
  @if(isset($fullscreen) && $fullscreen)
  <style>
    /* Prevent page scroll; inner view controls its own scrolling */
    body.fullscreen-mode { overflow:hidden !important; }
    /* Remove padding from main in fullscreen (override layout CSS) */
    body.fullscreen-mode main { padding: 0 !important; overflow: hidden !important; }
    /* Make fullscreen container span full width (no centered max-width gap) */
    body.fullscreen-mode .fullscreen-inner { max-width: none !important; margin: 0 !important; }
  </style>
  @endif
</body>
</html>

