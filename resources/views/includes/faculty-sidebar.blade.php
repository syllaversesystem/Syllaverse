{{-- 
-------------------------------------------------------------------------------
* File: resources/views/includes/faculty-sidebar.blade.php
* Description: Faculty Sidebar – aligned with Admin, only Dashboard + Syllabi
-------------------------------------------------------------------------------
📜 Log:
[2025-08-16] Removed unused "My Classes" and "Profile" links (no module yet). Sidebar now includes only Dashboard and Syllabi.
-------------------------------------------------------------------------------
--}}
<nav id="sidebar" class="bg-sv-light shadow-sm d-flex flex-column" role="navigation" aria-label="Faculty sidebar">
  <style>
    /* Stabilize nav link sizing across modules */
    #sidebar .nav-link {
      font-size: 0.95rem;
      line-height: 1.25rem;
      padding: 0.5rem 0.75rem;
    }
    #sidebar .nav-link .label {
      font-size: 0.95rem;
      line-height: 1.25rem;
    }
    /* Prevent external tab styles from shrinking sidebar links */
    #sidebar .nav-link.active,
    #sidebar .nav-link:focus {
      font-size: 0.95rem;
    }
  </style>

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

  {{-- ░░░ START: Collapse Button + Separators ░░░ --}}
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
  {{-- ░░░ END: Collapse Button + Separators ░░░ --}}

  {{-- ░░░ START: Navigation Links ░░░ --}}
  <div class="flex-grow-1">
    <ul class="nav flex-column px-3">

      {{-- Syllabi --}}
      @php $isActive = request()->routeIs('faculty.syllabi*') && !request()->routeIs('faculty.syllabi.approvals*'); @endphp
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center {{ $isActive ? 'active' : '' }}"
           href="{{ route('faculty.syllabi.index') }}"
           aria-current="{{ $isActive ? 'page' : '' }}">
          <i class="bi bi-files-alt"></i>
          <span class="label">Syllabi</span>
        </a>
      </li>

      {{-- Approvals (visible only to Dean/Associate Dean and Program/Department Chairpersons) --}}
      @php 
        $isActive = request()->routeIs('faculty.syllabi.approvals*'); 
        $user = Auth::guard('faculty')->user() ?? auth()->user();
        $hasApprovalsAccess = $user && $user->appointments()
          ->active()
          ->whereIn('role', [
            \App\Models\Appointment::ROLE_DEPT,
            \App\Models\Appointment::ROLE_DEPT_HEAD,
            \App\Models\Appointment::ROLE_DEAN,
            \App\Models\Appointment::ROLE_ASSOC_DEAN,
          ])->exists();
      @endphp
      @if($hasApprovalsAccess)
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center {{ $isActive ? 'active' : '' }}"
           href="{{ route('faculty.syllabi.approvals') }}"
           aria-current="{{ $isActive ? 'page' : '' }}">
          <i class="bi bi-clipboard-check"></i>
          <span class="label">Approvals</span>
        </a>
      </li>
      @endif

      {{-- Separator --}}
      <li class="nav-item">
        <div class="sidebar-separator"></div>
      </li>

      {{-- Departments removed from Faculty (module moved to Superadmin) --}}

      {{-- Programs --}}
      @php $isActive = request()->routeIs('faculty.programs*'); @endphp
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center {{ $isActive ? 'active' : '' }}"
           href="{{ route('faculty.programs.index') }}"
           aria-current="{{ $isActive ? 'page' : '' }}">
          <i class="bi bi-mortarboard"></i>
          <span class="label">Programs</span>
        </a>
      </li>

      {{-- Courses --}}
      @php $isActive = request()->routeIs('faculty.courses*'); @endphp
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center {{ $isActive ? 'active' : '' }}"
           href="{{ route('faculty.courses.index') }}"
           aria-current="{{ $isActive ? 'page' : '' }}">
          <i class="bi bi-book"></i>
          <span class="label">Courses</span>
        </a>
      </li>

      {{-- Master Data --}}
      @php $isActive = request()->routeIs('faculty.master-data*'); @endphp
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center {{ $isActive ? 'active' : '' }}"
           href="{{ route('faculty.master-data.index') }}"
           aria-current="{{ $isActive ? 'page' : '' }}">
          <i class="bi bi-database"></i>
          <span class="label">Master Data</span>
        </a>
      </li>



    </ul>
  </div>
  {{-- ░░░ END: Navigation Links ░░░ --}}
</nav>
