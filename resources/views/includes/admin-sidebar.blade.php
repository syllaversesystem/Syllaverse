{{-- 
-------------------------------------------------------------------------------
* File: resources/views/includes/admin-sidebar.blade.php
* Description: Admin Sidebar – same structure as Super Admin (logo header ➜ collapse container with always-visible top separator ➜ nav links)
-------------------------------------------------------------------------------
📜 Log:
[2025-08-09] Synced structure with Super Admin: logo toggle (text vs favicon), always-visible top separator on mobile, desktop-only collapse button, zero gap between top separator and first nav link, smaller logo size handled by CSS.
-------------------------------------------------------------------------------
--}}
<nav id="sidebar" class="bg-sv-light shadow-sm d-flex flex-column" role="navigation" aria-label="Admin sidebar">

  {{-- ░░░ START: Logo Header ░░░ --}}
  <div class="sidebar-header">
    <img src="{{ asset('images/syllaverse-text-logo.png') }}"
         alt="Syllaverse Logo"
         class="sidebar-logo-expanded fade-logo" />
    <img src="{{ asset('images/favicon.png') }}"
         alt="Syllaverse Icon"
         class="sidebar-logo-collapsed fade-logo" />
  </div>
  {{-- ░░░ END: Logo Header ░░░ --}}

  {{-- ░░░ START: Collapse Button Container (top separator always visible) ░░░ --}}
  <div class="px-3 py-2 flex-column align-items-center mt-auto d-flex">
    <div class="sidebar-separator" style="display:block !important;"></div>

    <button id="sidebarCollapseBtn"
            class="btn btn-sm text-sv-primary w-100 d-none d-lg-flex justify-content-center align-items-center ripple-btn"
            title="Toggle Sidebar"
            aria-controls="sidebar" aria-expanded="true">
      <i class="bi bi-chevron-left rotate-icon"></i>
    </button>

    <div class="sidebar-separator"></div>
  </div>
  {{-- ░░░ END: Collapse Button Container ░░░ --}}

  {{-- ░░░ START: Navigation Links ░░░ --}}
  <div class="flex-grow-1">
    <ul class="nav flex-column px-3">

      {{-- Dashboard --}}
      @php $isActive = request()->routeIs('admin.dashboard'); @endphp
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center {{ $isActive ? 'active' : '' }}"
           href="{{ route('admin.dashboard') }}"
           aria-current="{{ $isActive ? 'page' : '' }}">
          <i class="bi bi-speedometer2"></i>
          <span class="label">Dashboard</span>
        </a>
      </li>

      {{-- Syllabi --}}
      @php $isActive = request()->routeIs('admin.syllabi.*'); @endphp
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center {{ $isActive ? 'active' : '' }}"
           href="{{ route('admin.syllabi.index') }}"
           aria-current="{{ $isActive ? 'page' : '' }}">
          <i class="bi bi-book"></i>
          <span class="label">Syllabi</span>
        </a>
      </li>

      {{-- Manage Accounts --}}
      @php $isActive = request()->routeIs('admin.manage-accounts'); @endphp
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center {{ $isActive ? 'active' : '' }}"
           href="{{ route('admin.manage-accounts') }}"
           aria-current="{{ $isActive ? 'page' : '' }}">
          <i class="bi bi-people"></i>
          <span class="label">Manage Accounts</span>
        </a>
      </li>

      {{-- Master Data --}}
      @php $isActive = request()->routeIs('admin.master-data.index'); @endphp
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center {{ $isActive ? 'active' : '' }}"
           href="{{ route('admin.master-data.index') }}"
           aria-current="{{ $isActive ? 'page' : '' }}">
          <i class="bi bi-journals"></i>
          <span class="label">Master Data</span>
        </a>
      </li>

    </ul>
  </div>
  {{-- ░░░ END: Navigation Links ░░░ --}}
</nav>
