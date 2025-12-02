{{-- 
-------------------------------------------------------------------------------
* File: resources/views/superadmin/manage-accounts/tabs/admins-approved.blade.php
* Description: Approved Admins tab with Approvals-style table (icon-only actions, pill appointments) – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-08-08] Reworked UI: list active appointments; allow Add/Edit/End (inline logic).
[2025-08-09] Modularized: moved modal into manage-accounts/modals/manage-admin.blade.php and included per row.
[2025-08-09] Fix: push modals to a Blade stack so they render outside <table>; add z-index overrides for proper layering.
[2025-08-11] Refactor – applied Approvals-style table wrapper, header icons, icon-only actions, pill labels; removed Bootstrap card scaffolding.
[2025-08-11] Fix – moved modal stack to layout; removed local @stack('modals') to resolve “modal behind card”.
-------------------------------------------------------------------------------
--}}

@php
  // Load appointments for both admin and faculty users
  if (isset($approvedAdmins) && method_exists($approvedAdmins, 'load')) {
    $approvedAdmins->loadMissing('appointments');
  }
  if (isset($approvedFaculty) && method_exists($approvedFaculty, 'load')) {
    $approvedFaculty->loadMissing('appointments');
  }
  
  // Merge approved admins and faculty for unified display
  $allApprovedUsers = collect();
  if (isset($approvedAdmins)) {
    $allApprovedUsers = $allApprovedUsers->concat($approvedAdmins);
  }
  if (isset($approvedFaculty)) {
    $allApprovedUsers = $allApprovedUsers->concat($approvedFaculty);
  }
  
  $deptById = collect($departments ?? [])->keyBy('id');
  $progById = collect($programs ?? [])->keyBy('id');
@endphp

{{-- ░░░ START: Table Section (Approvals-style wrapper) ░░░ --}}
  <div class="table-wrapper position-relative">
    <div class="table-responsive">
      <table class="table superadmin-manage-account-table mb-0" id="svApprovedAdminsTable">
        <thead class="superadmin-manage-account-table-header">
          <tr>
            <th><i data-feather="user"></i> Name</th>
            <th><i data-feather="briefcase"></i> Role</th>
            <th><i data-feather="briefcase"></i> Department</th>
            <th class="text-end"><i data-feather="more-vertical"></i></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($allApprovedUsers as $user)
            @php
              $activeAppointments = $user->appointments
                ? $user->appointments->where('status', 'active')
                : collect();
            @endphp

            <tr>
              <td>{{ $user->name }}</td>
              
              <td>
                @php
                  // Derive primary role: prefer first active appointment; else use user->role mapping
                  if ($activeAppointments->count()) {
                    $primaryAppt = $activeAppointments->first();
                    switch ($primaryAppt->role) {
                      case \App\Models\Appointment::ROLE_DEPT_HEAD:
                      case \App\Models\Appointment::ROLE_DEPT:
                        // Dynamic Dept Head labeling: Dept Chair if department has 2+ programs, else Program Chair
                        $deptObj = $deptById[$primaryAppt->scope_id] ?? null;
                        $programCount = $deptObj ? ($programs->where('department_id', $deptObj->id)->count()) : 0;
                        $primaryRole = $programCount >= 2 ? 'Dept Chair' : 'Program Chair';
                        break;
                      case \App\Models\Appointment::ROLE_PROG:
                        $primaryRole = 'Program Chair';
                        break;
                      case \App\Models\Appointment::ROLE_DEAN:
                        $primaryRole = 'Dean';
                        break;
                      case \App\Models\Appointment::ROLE_ASSOC_DEAN:
                        $primaryRole = 'Associate Dean';
                        break;
                      case \App\Models\Appointment::ROLE_VCAA:
                        $primaryRole = 'VCAA';
                        break;
                      case \App\Models\Appointment::ROLE_ASSOC_VCAA:
                        $primaryRole = 'Associate VCAA';
                        break;
                      case \App\Models\Appointment::ROLE_FACULTY:
                        $primaryRole = 'Faculty';
                        break;
                      default:
                        $primaryRole = $primaryAppt->role ?: 'Role';
                    }
                  } else {
                    // Map base user role when no active appointments
                    $primaryRole = match ($user->role) {
                      'admin'      => 'Admin',
                      'faculty'    => 'Faculty',
                      'student'    => 'Student',
                      'superadmin' => 'Superadmin',
                      default      => ucfirst(strtolower($user->role ?? 'User')),
                    };
                  }

                  $additionalRolesCount = $activeAppointments->count() - 1;
                @endphp
                <div class="d-flex align-items-center gap-2">
                  <span class="sv-pill is-primary sv-pill--sm">{{ $primaryRole }}</span>
                  @if($additionalRolesCount > 0)
                    <span class="text-muted small">+{{ $additionalRolesCount }}</span>
                  @endif
                </div>
              </td>

              <td>
                @php
                  $departmentName = '—';
                  if ($activeAppointments->count()) {
                    $primaryAppt = $activeAppointments->first();
                    if ($primaryAppt->scope_id && isset($deptById[$primaryAppt->scope_id])) {
                      $departmentName = $deptById[$primaryAppt->scope_id]->name;
                    } elseif ($primaryAppt->role === \App\Models\Appointment::ROLE_VCAA || 
                             $primaryAppt->role === \App\Models\Appointment::ROLE_ASSOC_VCAA) {
                      $departmentName = 'Institution-wide';
                    }
                  }
                @endphp
                <span class="text-muted">{{ $departmentName }}</span>
              </td>

              <td class="text-end">
                <div class="d-flex justify-content-end align-items-center gap-2">
                  @if($activeAppointments->count() > 1)
                    {{-- Toggle button for multiple roles --}}
                    @php $collapseId = "sv-roles-{$user->id}"; @endphp
                    <button
                      class="action-btn edit sv-row-toggle"
                      type="button"
                      data-bs-toggle="collapse"
                      data-bs-target="#{{ $collapseId }}"
                      aria-expanded="false"
                      aria-controls="{{ $collapseId }}"
                      title="Show all roles for {{ $user->name }}"
                      aria-label="Show all roles for {{ $user->name }}">
                      <i data-feather="chevron-down"></i>
                    </button>
                  @endif

                  {{-- Manage button --}}
                  @if($user->role === 'admin')
                    <button
                      class="action-btn edit"
                      type="button"
                      data-bs-toggle="modal"
                      data-bs-target="#manageAdmin-{{ $user->id }}"
                      title="Manage appointments for {{ $user->name }}"
                      aria-label="Manage appointments for {{ $user->name }}">
                      <i data-feather="settings"></i>
                    </button>
                  @elseif($user->role === 'faculty')
                    <button
                      class="action-btn edit"
                      type="button"
                      data-bs-toggle="modal"
                      data-bs-target="#manageFaculty-{{ $user->id }}"
                      title="Manage faculty for {{ $user->name }}"
                      aria-label="Manage faculty for {{ $user->name }}">
                      <i data-feather="settings"></i>
                    </button>
                  @endif
                </div>
              </td>
            </tr>

            {{-- Detail Row (only when multiple appointments) --}}
            @if($activeAppointments->count() > 1)
              <tr class="sv-detail-row">
                <td class="sv-details-cell p-0" colspan="4">
                  <div id="{{ $collapseId }}" class="collapse sv-details mt-2">
                    <div class="sv-request-list p-3">
                      <div class="mb-2 text-start">
                        <h6 class="text-muted mb-3 text-start" style="font-size: 0.85rem; font-weight: 600;">All Active Roles</h6>
                      </div>
                      @foreach ($activeAppointments as $appt)
                        @php
                          $isDept = $appt->role === \App\Models\Appointment::ROLE_DEPT;
                          $isProg = $appt->role === \App\Models\Appointment::ROLE_PROG;
                          $isDean = $appt->role === \App\Models\Appointment::ROLE_DEAN;
                          $isFaculty = $appt->role === \App\Models\Appointment::ROLE_FACULTY;
                          
                          if ($isDept || $appt->role === \App\Models\Appointment::ROLE_DEPT_HEAD) {
                            $deptObj = $deptById[$appt->scope_id] ?? null;
                            $programCount = $deptObj ? ($programs->where('department_id', $deptObj->id)->count()) : 0;
                            $roleLabel = $programCount >= 2 ? 'Dept Chair' : 'Program Chair';
                          } elseif ($isProg) {
                            $roleLabel = 'Program Chair';
                          } elseif ($isDean) {
                            $roleLabel = 'Dean';
                          } elseif ($appt->role === \App\Models\Appointment::ROLE_VCAA) {
                            $roleLabel = 'VCAA';
                          } elseif ($appt->role === \App\Models\Appointment::ROLE_ASSOC_VCAA) {
                            $roleLabel = 'Associate VCAA';
                          } elseif ($appt->role === \App\Models\Appointment::ROLE_ASSOC_DEAN) {
                            $roleLabel = 'Associate Dean';
                          } elseif ($isFaculty) {
                            $roleLabel = 'Faculty';
                          } else {
                            $roleLabel = $appt->role ?? 'Appointment';
                          }
                          
                          $deptName = '—';
                          if ($appt->scope_id && isset($deptById[$appt->scope_id])) {
                            $deptName = $deptById[$appt->scope_id]->name;
                          } elseif ($appt->role === \App\Models\Appointment::ROLE_VCAA || 
                                   $appt->role === \App\Models\Appointment::ROLE_ASSOC_VCAA) {
                            $deptName = 'Institution-wide';
                          }
                        @endphp
                        <div class="sv-request-item py-2 mb-2">
                          <div class="row align-items-center gx-3">
                            <div class="col-auto" style="min-width: 200px;">
                              <div class="d-flex align-items-center gap-2">
                                <span class="sv-pill is-primary sv-pill--sm">{{ $roleLabel }}</span>
                              </div>
                            </div>
                            <div class="col">
                              <span class="text-muted">{{ $deptName }}</span>
                            </div>
                            <div class="col-auto">
                              <small class="text-muted">
                                Active since {{ $appt->created_at ? $appt->created_at->format('M j, Y') : '—' }}
                              </small>
                            </div>
                          </div>
                        </div>
                      @endforeach
                    </div>
                  </div>
                </td>
              </tr>
            @endif

            @if($user->role === 'admin')
              @push('modals')
                @include('superadmin.manage-accounts.modals.manage-admin', [
                  'admin' => $user,
                  'departments' => $departments ?? [],
                  'programs' => $programs ?? []
                ])
              @endpush
            @elseif($user->role === 'faculty')
              @push('modals')
                @include('superadmin.manage-accounts.modals.manage-faculty', [
                  'faculty' => $user,
                  'departments' => $departments ?? [],
                  'programs' => $programs ?? []
                ])
              @endpush
            @endif

          @empty
            {{-- No approved users --}}
          @endforelse

          {{-- ░░░ START: Empty State ░░░ --}}
          @if($allApprovedUsers->isEmpty())
            <tr class="superadmin-manage-account-empty-row">
              <td colspan="4">
                <div class="sv-empty">
                  <h6>No approved accounts</h6>
                  <p>Approved admins and faculty will appear here once accounts are verified.</p>
                </div>
              </td>
            </tr>
          @endif
          {{-- ░░░ END: Empty State ░░░ --}}
        </tbody>
      </table>
    </div>
  </div>
  {{-- ░░░ END: Table Section ░░░ --}}
