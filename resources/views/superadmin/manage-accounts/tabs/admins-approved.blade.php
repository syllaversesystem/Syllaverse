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
            <th><i data-feather="shield"></i> Role</th>
            <th><i data-feather="mail"></i> Email</th>
            <th><i data-feather="award"></i> Active Appointments</th>
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
                @if($user->role === 'admin')
                  <span class="sv-pill is-primary sv-pill--sm">Admin</span>
                @elseif($user->role === 'faculty')
                  <span class="sv-pill is-success sv-pill--sm">Faculty</span>
                @else
                  <span class="sv-pill is-secondary sv-pill--sm">{{ ucfirst($user->role) }}</span>
                @endif
              </td>
              
              <td class="text-muted">{{ $user->email }}</td>

              <td>
                @if ($activeAppointments->count())
                  <div class="d-flex flex-wrap gap-2">
                    @foreach ($activeAppointments as $appt)
                      @php
                        $isDept = $appt->role === \App\Models\Appointment::ROLE_DEPT;
                        $isProg = $appt->role === \App\Models\Appointment::ROLE_PROG;
                        $isDean = $appt->role === \App\Models\Appointment::ROLE_DEAN;
                        $isFaculty = $appt->role === \App\Models\Appointment::ROLE_FACULTY;
                        
                        // Show specific chair type based on stored role
                        if ($isDept) {
                          $roleLabel = 'Department Chair';
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
                          // Show department for faculty appointments
                          if ($appt->scope_id && isset($deptById[$appt->scope_id])) {
                            $roleLabel .= ' - ' . $deptById[$appt->scope_id]->name;
                          }
                        } else {
                          $roleLabel = $appt->role ?? 'Appointment';
                        }
                      @endphp
                      <span class="sv-pill is-accent sv-pill--sm">{{ $roleLabel }}</span>
                    @endforeach
                  </div>
                @else
                  @if($user->role === 'faculty')
                    <span class="text-muted">No department appointment</span>
                  @else
                    <span class="text-muted">—</span>
                  @endif
                @endif
              </td>

              <td class="text-end">
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
              </td>
            </tr>

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
            <tr class="superladmin-manage-account-empty-row">
              <td colspan="5">
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
