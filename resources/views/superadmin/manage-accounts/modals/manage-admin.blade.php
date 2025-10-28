{{-- 
-------------------------------------------------------------------------------
* File: resources/views/superadmin/manage-accounts/modals/manage-admin.blade.php
* Description: Per-admin “Manage” modal – add/edit/end appointments with in-place AJAX DOM updates
-------------------------------------------------------------------------------
📜 Log:
[2025-08-09] Initial creation – extracted from admins-approved tab into reusable partial.
[2025-08-09] Removed start date/time fields; new/updated appointments start immediately (server defaults to now()).
[2025-08-11] UI/UX refactor – Approvals-style list, pill labels, icon-only actions; inline edit collapse; fixed nested forms.
[2025-08-11] AJAX – forms marked with data-ajax="true"; added stable DOM IDs for in-place updates (sv-appt-list-<id>).
-------------------------------------------------------------------------------
--}}

@php
  // Build quick id->name maps for nicer labels
  $deptById = collect($departments ?? [])->keyBy('id');
  $progById = collect($programs ?? [])->keyBy('id');

  // Ensure appointments relation is available
  $admin->loadMissing('appointments');
  $activeAppointments = $admin->appointments ? $admin->appointments->where('status','active') : collect();
@endphp

<div class="modal fade sv-appt-modal" id="manageAdmin-{{ $admin->id }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">

      {{-- ░░░ START: Modal Header ░░░ --}}
      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2">
          <i data-feather="user-check"></i>
          <span>Manage Admin — {{ $admin->name }}</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      {{-- ░░░ END: Modal Header ░░░ --}}

      <div class="modal-body">

        {{-- ░░░ START: Add Appointment (AJAX) ░░░ --}}
        <div class="mb-3">
          <h6 class="mb-2 d-flex align-items-center gap-2">
            <i data-feather="plus-circle"></i><span>Add appointment</span>
          </h6>

          {{-- This creates a new appointment and stays in the modal (AJAX). --}}
          <form method="POST"
                action="{{ route('superadmin.appointments.store') }}"
                class="sv-appt-form row g-2 align-items-end"
                data-sv-scope="add-{{ $admin->id }}"
                data-ajax="true">
            @csrf
            <input type="hidden" name="user_id" value="{{ $admin->id }}">

            <div class="col-md-4">
              <label class="form-label small">Role</label>
              <select name="role" class="form-select form-select-sm sv-role" aria-label="Select role">
                <option value="">— Select Role —</option>
                <option value="{{ \App\Models\Appointment::ROLE_DEPT }}">Program/Department Chair</option>
                <option value="{{ \App\Models\Appointment::ROLE_DEAN }}">Dean</option>
                <option value="{{ \App\Models\Appointment::ROLE_ASSOC_DEAN }}">Associate Dean</option>
                <option value="{{ \App\Models\Appointment::ROLE_VCAA }}">VCAA</option>
                <option value="{{ \App\Models\Appointment::ROLE_ASSOC_VCAA }}">Associate VCAA</option>
              </select>
            </div>

            <div class="col-md-7">
              <label class="form-label small">Department</label>
              <select name="department_id" class="form-select form-select-sm sv-dept" aria-label="Select department" disabled>
                <option value="">— Select Role First —</option>
                @foreach (($departments ?? []) as $dept)
                  <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-1 d-flex">
              <button class="action-btn approve ms-auto" type="submit" title="Save appointment" aria-label="Save appointment">
                <i data-feather="check"></i>
              </button>
            </div>
          </form>
        </div>
        {{-- ░░░ END: Add Appointment ░░░ --}}

        {{-- ░░░ START: Active Appointments (inline edit + end) ░░░ --}}
        <div class="border-top pt-3">
          <h6 class="mb-2 d-flex align-items-center gap-2">
            <i data-feather="briefcase"></i><span>Active appointments</span>
          </h6>

          {{-- IMPORTANT: This wrapper ID is used by JS to re-render the list after AJAX --}}
          <div class="sv-request-list" id="sv-appt-list-{{ $admin->id }}">
            @if ($activeAppointments->count())
              @foreach ($activeAppointments as $appt)
                @php
                  $isDept        = $appt->role === \App\Models\Appointment::ROLE_DEPT;
                  $isProg        = $appt->role === \App\Models\Appointment::ROLE_PROG;
                  $isDean        = $appt->role === \App\Models\Appointment::ROLE_DEAN;
                  
                  // Show specific role labels based on stored role
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
                  } else {
                    $roleLabel = $appt->role ?? 'Appointment';
                  }
                  
                  // Handle scope label properly - show department for department-scoped roles
                  if ($appt->scope_id === null || $appt->scope_id == 0) {
                    $scopeLabel = 'Institution-wide';
                  } elseif ($isDept || $isProg || $isDean) {
                    // Chair types and Dean use department scope
                    $scopeLabel = $deptById[$appt->scope_id]->name ?? 'Unknown Department';
                  } else {
                    // For other appointment types (VCAA, Associate VCAA)
                    $scopeLabel = 'Institution-wide';
                  }
                  
                  $collapseId    = "sv-appt-edit-{$appt->id}";
                  $currentDeptId = $isDept
                    ? (int) $appt->scope_id
                    : (int) ($progById[$appt->scope_id]->department_id ?? 0);
                  $currentProgId = $isDept ? 0 : (int) $appt->scope_id;
                @endphp

                {{-- Row summary (pills + actions) --}}
                <div class="sv-request-item">
                  <div class="sv-request-meta">
                    <span class="sv-pill is-accent sv-pill--sm">{{ $roleLabel }}</span>
                    @if($scopeLabel) <span class="sv-pill is-muted sv-pill--sm">{{ $scopeLabel }}</span> @endif
                  </div>

                  <div class="sv-request-actions">
                    {{-- Edit (toggle inline collapse) --}}
                    <button
                      class="action-btn edit"
                      type="button"
                      data-bs-toggle="collapse"
                      data-bs-target="#{{ $collapseId }}"
                      aria-expanded="false"
                      aria-controls="{{ $collapseId }}"
                      title="Edit appointment"
                      aria-label="Edit appointment">
                      <i data-feather="edit-3"></i>
                    </button>

                    {{-- End (AJAX) --}}
                    <form method="POST"
                          action="{{ route('superadmin.appointments.end', $appt->id) }}"
                          class="d-inline"
                          data-ajax="true">
                      @csrf
                      <button class="action-btn reject" type="submit" title="Delete appointment" aria-label="Delete appointment">
                        <i data-feather="x"></i>
                      </button>
                    </form>
                  </div>
                </div>

                {{-- Inline edit panel (AJAX) --}}
                <div id="{{ $collapseId }}" class="collapse sv-details">
                  <form method="POST"
                        action="{{ route('superadmin.appointments.update', $appt->id) }}"
                        class="row g-2 align-items-end sv-appt-form"
                        data-sv-scope="edit-{{ $appt->id }}"
                        data-ajax="true">
                    @csrf
                    @method('PUT')

                    <div class="col-md-4">
                      <label class="form-label small">Role</label>
                      <select name="role" class="form-select form-select-sm sv-role">
                        <option value="{{ \App\Models\Appointment::ROLE_DEPT }}" {{ ($isDept || $isProg) ? 'selected' : '' }}>Program/Department Chair</option>
                        <option value="{{ \App\Models\Appointment::ROLE_DEAN }}" {{ $appt->role === \App\Models\Appointment::ROLE_DEAN ? 'selected' : '' }}>Dean</option>
                        <option value="{{ \App\Models\Appointment::ROLE_ASSOC_DEAN }}" {{ $appt->role === \App\Models\Appointment::ROLE_ASSOC_DEAN ? 'selected' : '' }}>Associate Dean</option>
                        <option value="{{ \App\Models\Appointment::ROLE_VCAA }}" {{ $appt->role === \App\Models\Appointment::ROLE_VCAA ? 'selected' : '' }}>VCAA</option>
                        <option value="{{ \App\Models\Appointment::ROLE_ASSOC_VCAA }}" {{ $appt->role === \App\Models\Appointment::ROLE_ASSOC_VCAA ? 'selected' : '' }}>Associate VCAA</option>
                      </select>
                    </div>

                    <div class="col-md-7">
                      <label class="form-label small">Department</label>
                      <select name="department_id" class="form-select form-select-sm sv-dept" 
                              {{ in_array($appt->role, [\App\Models\Appointment::ROLE_VCAA, \App\Models\Appointment::ROLE_ASSOC_VCAA]) ? 'disabled' : '' }}>
                        @if (in_array($appt->role, [\App\Models\Appointment::ROLE_VCAA, \App\Models\Appointment::ROLE_ASSOC_VCAA]))
                          <option value="">— Not Required —</option>
                        @endif
                        @foreach (($departments ?? []) as $dept)
                          <option value="{{ $dept->id }}" {{ (int)$dept->id === $currentDeptId ? 'selected' : '' }}>
                            {{ $dept->name }}
                          </option>
                        @endforeach
                      </select>
                    </div>

                    <div class="col-md-1 d-flex">
                      <button class="action-btn approve ms-auto" type="submit" title="Save changes" aria-label="Save changes">
                        <i data-feather="check"></i>
                      </button>
                    </div>
                  </form>
                </div>
              @endforeach
            @else
              <div class="text-muted">No active appointments for this admin.</div>
            @endif
          </div>
        </div>
        {{-- ░░░ END: Active Appointments ░░░ --}}

      </div>

      {{-- ░░░ START: Modal Footer ░░░ --}}
      <div class="modal-footer d-flex justify-content-between">
        {{-- This revokes admin access entirely (AJAX). --}}
        <form method="POST"
              action="{{ route('superadmin.reject.admin', $admin->id) }}"
              class="me-auto"
              data-ajax="true">
          @csrf
          <button class="btn btn-outline-danger btn-sm" type="submit">Revoke Admin</button>
        </form>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
      {{-- ░░░ END: Modal Footer ░░░ --}}

    </div>
  </div>
</div>


