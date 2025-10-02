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
  if (isset($approvedAdmins) && method_exists($approvedAdmins, 'load')) {
    $approvedAdmins->loadMissing('appointments');
  }
  $deptById = collect($departments ?? [])->keyBy('id');
  $progById = collect($programs ?? [])->keyBy('id');
@endphp

<div class="tab-pane fade" id="admins-approved" role="tabpanel" aria-labelledby="admins-approved-tab">

  {{-- ░░░ START: Table Section (Approvals-style wrapper) ░░░ --}}
  <div class="table-wrapper position-relative">
    <div class="table-responsive">
      <table class="table superadmin-manage-account-table mb-0" id="svApprovedAdminsTable">
        <thead class="superadmin-manage-account-table-header">
          <tr>
            <th><i data-feather="user"></i> Name</th>
            <th><i data-feather="mail"></i> Email</th>
            <th><i data-feather="award"></i> Active Appointments</th>
            <th class="text-end"><i data-feather="more-vertical"></i></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($approvedAdmins ?? [] as $admin)
            @php
              $activeAppointments = $admin->appointments
                ? $admin->appointments->where('status', 'active')
                : collect();
            @endphp

            <tr>
              <td>{{ $admin->name }}</td>
              <td class="text-muted">{{ $admin->email }}</td>

              <td>
                @if ($activeAppointments->count())
                  <div class="d-flex flex-wrap gap-2">
                    @foreach ($activeAppointments as $appt)
                      @php
                        $isDept     = $appt->role === \App\Models\Appointment::ROLE_DEPT;
                        $roleLabel  = $isDept ? 'Dept Chair' : ($appt->role ?? 'Appointment');
                        $scopeLabel = $isDept
                          ? ($deptById[$appt->scope_id]->name ?? ('Dept #'.$appt->scope_id))
                          : ($appt->scope_type ? ($appt->scope_type . ' #' . $appt->scope_id) : 'Institution');
                      @endphp
                      <span class="sv-pill is-accent sv-pill--sm">{{ $roleLabel }}</span>
                      <span class="sv-pill is-muted sv-pill--sm">{{ $scopeLabel }}</span>
                    @endforeach
                  </div>
                @else
                  <span class="text-muted">No active appointment.</span>
                @endif
              </td>

              <td class="text-end">
                <button
                  class="action-btn edit"
                  type="button"
                  data-bs-toggle="modal"
                  data-bs-target="#manageAdmin-{{ $admin->id }}"
                  title="Manage appointments for {{ $admin->name }}"
                  aria-label="Manage appointments for {{ $admin->name }}">
                  <i data-feather="settings"></i>
                </button>
              </td>
            </tr>

            @push('modals')
              @include('superadmin.manage-accounts.modals.manage-admin', [
                'admin' => $admin,
                'departments' => $departments ?? [],
                'programs' => $programs ?? []
              ])
            @endpush

          @empty
            <tr class="superadmin-manage-account-empty-row">
              <td colspan="4">
                <div class="sv-empty">
                  <h6>No approved admins</h6>
                  <p>Approved admins will appear here once accounts are verified.</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  {{-- ░░░ END: Table Section ░░░ --}}

</div>
