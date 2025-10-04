{{-- 
-------------------------------------------------------------------------------
* File: resources/views/includes/superadmin-sidebar.blade.php
* Description: Sidebar with logo toggle (text vs. favicon), persistent separator on mobile, and minimized collapse button – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-08-05] Updated to conditionally show favicon only when sidebar is collapsed; title logo shown only when expanded.
[2025-08-06] Made top separator line visible on mobile; collapsed button kept desktop-only.
[2025-08-06] Reduced collapse button height via CSS.
[2025-08-06] Updated mobile sidebar to always use favicon logo only.
[2025-08-06] Moved top separator line into the collapse button container.
[2025-08-06] Ensured top separator remains visible on mobile version.
[2025-08-06] Restored bottom separator and kept top separator visible even on mobile.
-------------------------------------------------------------------------------
--}}  
<nav id="sidebar" class="bg-sv-light shadow-sm d-flex flex-column" role="navigation" aria-label="Main sidebar">  

  {{-- START: Logo Header --}}  
  <div class="sidebar-header">
    <img src="{{ asset('images/syllaverse-text-logo.png') }}"  
         alt="Syllaverse Logo"  
         class="sidebar-logo-expanded fade-logo" />

    <img src="{{ asset('images/favicon.png') }}"  
         alt="Syllaverse Icon"  
         class="sidebar-logo-collapsed fade-logo" />
  </div>  
  {{-- END: Logo Header --}}

  {{-- START: Collapse Button Container (top separator always visible) --}}  
  <div class="px-3 py-2 flex-column align-items-center mt-auto d-flex">  
    <div class="sidebar-separator" style="display: block !important;"></div>  

    <button id="sidebarCollapseBtn"  
            class="btn btn-sm text-sv-primary w-100 d-none d-lg-flex justify-content-center align-items-center ripple-btn"  
            title="Toggle Sidebar"  
            aria-controls="sidebar" aria-expanded="true">  
      <i class="bi bi-chevron-left rotate-icon"></i>  
    </button>  

    <div class="sidebar-separator"></div>  
  </div>  
  {{-- END: Collapse Button Container --}}

  {{-- START: Navigation Links --}}  
  <div class="flex-grow-1">  
    <ul class="nav flex-column px-3">  
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center @if(request()->is('superadmin/dashboard')) active @endif"
           href="{{ route('superadmin.dashboard') }}"
           aria-current="@if(request()->is('superadmin/dashboard')) page @endif">
          <i class="bi bi-speedometer2"></i>
          <span class="label">Dashboard</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link d-flex align-items-center @if(request()->is('superadmin/manage-accounts')) active @endif"
           href="{{ route('superadmin.manage-accounts') }}"
           aria-current="@if(request()->is('superadmin/manage-accounts')) page @endif">
          <i class="bi bi-people"></i>
          <span class="label">Manage Accounts</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center @if(request()->is('superadmin/master-data')) active @endif"
           href="{{ route('superadmin.master-data') }}"
           aria-current="@if(request()->is('superadmin/master-data')) page @endif">
          <i class="bi bi-journals"></i>
          <span class="label">Master Data</span>
        </a>
      </li>
    </ul>  
  </div>  
  {{-- END: Navigation Links --}}  
</nav>
