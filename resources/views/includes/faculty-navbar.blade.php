{{-- Faculty Navbar (aligned with Admin/SuperAdmin) --}}
<nav class="navbar navbar-expand-lg shadow-sm glass-navbar sticky-top px-4 py-3" role="navigation" aria-label="Faculty Navbar" style="z-index:1000;">
  <div class="container-fluid d-flex justify-content-between align-items-center">

    {{-- Sidebar Toggle (Mobile Only) --}}
    <button id="sidebarToggle" class="btn d-lg-none hamburger-btn" type="button" aria-label="Toggle sidebar">
      <i class="bi bi-list fs-3 text-dark"></i>
    </button>

    {{-- Page Title + Optional Context --}}
    <div class="d-flex align-items-center gap-3 flex-grow-1">
      <h5 class="mb-0 fw-bold navbar-title">@yield('page-title', 'Dashboard')</h5>
      @hasSection('page-context')
        <div class="text-muted small">@yield('page-context')</div>
      @endif
    </div>

    {{-- Scope Pills - Centered (only for department-level roles) --}}
    @php
      // Get user scopes for navbar pills
      $user = Auth::user();
      $navbarScopes = [];
      $showDepartmentScope = false;
      
      if ($user && $user->appointments) {
        $activeAppointments = $user->appointments->where('status', 'active');
        $departments = collect();
        
        // Check if user has VCAA or Associate VCAA roles
        $hasInstitutionWideRole = $activeAppointments->whereIn('role', [
          \App\Models\Appointment::ROLE_VCAA, 
          \App\Models\Appointment::ROLE_ASSOC_VCAA
        ])->count() > 0;
        
        // Only show department scope if user doesn't have institution-wide roles
        if (!$hasInstitutionWideRole && $activeAppointments->count()) {
          $departmentIds = $activeAppointments->pluck('scope_id')->filter()->unique();
          if ($departmentIds->count()) {
            $departments = \App\Models\Department::whereIn('id', $departmentIds)->get();
          }
          
          foreach ($activeAppointments as $appt) {
            // Only include department-level roles
            if (!in_array($appt->role, [\App\Models\Appointment::ROLE_VCAA, \App\Models\Appointment::ROLE_ASSOC_VCAA])) {
              if ($appt->scope_id) {
                $department = $departments->firstWhere('id', $appt->scope_id);
                if ($department) {
                  $navbarScopes[] = $department->name;
                  $showDepartmentScope = true;
                }
              }
            }
          }
          $navbarScopes = array_unique($navbarScopes);
        }
      }
    @endphp
    
    @if($showDepartmentScope && count($navbarScopes) > 0)
      <div class="d-flex align-items-center">
        <div class="d-flex flex-wrap gap-2">
          @foreach($navbarScopes as $scope)
            <span class="badge rounded-pill d-flex align-items-center gap-1" style="background-color: rgba(108, 117, 125, 0.1); color: #6c757d; font-size: 0.75rem; padding: 0.3rem 0.7rem; border: 1px solid rgba(108, 117, 125, 0.2);">
              <i class="bi bi-building" style="font-size: 12px;"></i>
              {{ $scope }}
            </span>
          @endforeach
        </div>
        
        {{-- Separation line --}}
        <div style="width: 2px; height: 24px; background-color: #dee2e6; margin: 0 1rem;"></div>
      </div>
    @endif

    {{-- Profile Dropdown - Simplified --}}
    <div class="dropdown d-flex align-items-center">
      <a class="d-flex align-items-center text-decoration-none dropdown-toggle faculty-dropdown"
         href="#" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <div class="text-end d-none d-lg-block">
          @php
            // Get user roles for dropdown (simplified)
            $user = Auth::user();
            $roleData = [];
            
            if ($user && $user->appointments) {
              $activeAppointments = $user->appointments->where('status', 'active');
              
              if ($activeAppointments->count()) {
                // Preload departments once for counting programs
                $deptIds = $activeAppointments->pluck('scope_id')->filter()->unique();
                $departments = $deptIds->count() ? \App\Models\Department::withCount('programs')->whereIn('id', $deptIds)->get() : collect();

                foreach ($activeAppointments as $appt) {
                  $roleLabel = '';

                  if ($appt->role === \App\Models\Appointment::ROLE_VCAA) {
                    $roleLabel = 'VCAA';
                  } elseif ($appt->role === \App\Models\Appointment::ROLE_ASSOC_VCAA) {
                    $roleLabel = 'Assoc VCAA';
                  } elseif ($appt->role === \App\Models\Appointment::ROLE_DEPT_HEAD) {
                    // Department Head umbrella label; show Dean/Head/Principal based on department name
                    $dept = $departments->firstWhere('id', $appt->scope_id);
                    $deptNameLower = $dept ? strtolower($dept->name) : '';
                    if ($deptNameLower && (
                      str_contains($deptNameLower, 'laboratory school') ||
                      str_contains($deptNameLower, 'lab school') ||
                      str_contains($deptNameLower, 'labschool')
                    )) {
                      $roleLabel = 'Principal';
                    } elseif ($deptNameLower && str_contains($deptNameLower, 'general education')) {
                      $roleLabel = 'Head';
                    } else {
                      $roleLabel = 'Dean';
                    }
                  } elseif ($appt->role === \App\Models\Appointment::ROLE_ASSOC_DEAN) {
                    $roleLabel = 'Assoc Dean';
                  } elseif ($appt->role === \App\Models\Appointment::ROLE_CHAIR) {
                    // Chairperson label depends on program count
                    $dept = $departments->firstWhere('id', $appt->scope_id);
                    $programsCount = $dept ? (int) ($dept->programs_count ?? 0) : 0;
                    $roleLabel = $programsCount >= 2 ? 'Dept Chair' : 'Prog Chair';
                  } elseif ($appt->role === \App\Models\Appointment::ROLE_PROG) {
                    $roleLabel = 'Prog Chair';
                  } elseif ($appt->role === \App\Models\Appointment::ROLE_FACULTY) {
                    $roleLabel = 'Faculty';
                  }

                  if ($roleLabel) {
                    $roleData[] = [
                      'role' => $roleLabel,
                      'order' => array_search($roleLabel, ['VCAA', 'Assoc VCAA', 'Dean', 'Assoc Dean', 'Dept Chair', 'Prog Chair', 'Faculty'])
                    ];
                  }
                }
                
                // Sort roles by importance
                usort($roleData, function($a, $b) {
                  return $a['order'] - $b['order'];
                });
              } else {
                $roleData[] = ['role' => 'Faculty', 'order' => 6];
              }
            } else {
              $roleData[] = ['role' => 'Faculty', 'order' => 6];
            }
          @endphp
          
          <div class="d-flex align-items-center gap-2 mb-1">
            {{-- User name with icon --}}
            <i data-feather="user" style="width: 16px; height: 16px; color: #495057;"></i>
            <span class="fw-semibold text-dark" style="font-size: 1rem;">{{ Auth::user()->name ?? 'Faculty' }}</span>
          </div>
          
          {{-- Roles --}}
          <div class="d-flex flex-wrap gap-2 justify-content-end">
            @foreach($roleData as $data)
              <span class="badge rounded-pill" style="background-color: #EE6F57; color: white; font-size: 0.75rem; padding: 0.3rem 0.7rem; box-shadow: 0 1px 3px rgba(238, 111, 87, 0.2);">
                {{ $data['role'] }}
              </span>
            @endforeach
          </div>
        </div>
      </a>
      <ul class="dropdown-menu dropdown-menu-end shadow-sm animate__animated animate__fadeIn" aria-labelledby="profileDropdown" style="min-width: 180px;">
        <li>
          <a class="dropdown-item d-flex align-items-center" href="{{ route('faculty.manage-profile') }}">
            <i class="bi bi-person me-2"></i> Profile
          </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
          <form action="{{ route('faculty.logout') }}" method="POST" class="d-inline w-100">
            @csrf
            <button type="submit" class="dropdown-item d-flex align-items-center text-danger w-100">
              <i class="bi bi-box-arrow-right me-2"></i> Logout
            </button>
          </form>
        </li>
      </ul>
    </div>

  </div>
</nav>

