{{-- 
-------------------------------------------------------------------------------
* File: resources/views/superadmin/manage-accounts/modals/manage-faculty.blade.php
* Description: Clean faculty management modal - fresh start
-------------------------------------------------------------------------------
--}}

@php
  // Simple setup - no complex logic
  $faculty->loadMissing('appointments');
  $activeAppointments = $faculty->appointments ? $faculty->appointments->where('status','active') : collect();
@endphp

<div class="modal fade sv-appt-modal" id="manageFaculty-{{ $faculty->id }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">

      {{-- ░░░ START: Modal Header ░░░ --}}
      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2">
          <i data-feather="user-check"></i>
          <span>Manage Faculty — {{ $faculty->name }}</span>
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
                data-sv-scope="add-{{ $faculty->id }}"
                data-ajax="true">
            @csrf
            <input type="hidden" name="user_id" value="{{ $faculty->id }}">

            <div class="col-md-4">
              <label class="form-label small">Role</label>
              <select name="role" class="form-select form-select-sm" required>
                <option value="">— Select Role —</option>
                <option value="{{ \App\Models\Appointment::ROLE_FACULTY }}">Faculty</option>
                <option value="{{ \App\Models\Appointment::ROLE_DEPT }}">Department Chair</option>
                <option value="{{ \App\Models\Appointment::ROLE_DEAN }}">Dean</option>
                <option value="{{ \App\Models\Appointment::ROLE_ASSOC_DEAN }}">Associate Dean</option>
                <option value="{{ \App\Models\Appointment::ROLE_VCAA }}">VCAA</option>
                <option value="{{ \App\Models\Appointment::ROLE_ASSOC_VCAA }}">Associate VCAA</option>
              </select>
            </div>

            <div class="col-md-7">
              <label class="form-label small">Department</label>
              <select name="department_id" class="form-select form-select-sm" required>
                <option value="">— Select Department —</option>
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

        {{-- ░░░ START: Active Appointments (simple list) ░░░ --}}
        <div class="border-top pt-3">
          <h6 class="mb-2 d-flex align-items-center gap-2">
            <i data-feather="briefcase"></i><span>Active appointments</span>
          </h6>

          <div class="sv-request-list" id="sv-appt-list-{{ $faculty->id }}">
            @if ($activeAppointments->count())
              @foreach ($activeAppointments as $appt)
                @php
                  // Simple role display using proper constants
                  $roleLabel = match($appt->role) {
                    \App\Models\Appointment::ROLE_FACULTY => 'Faculty',
                    \App\Models\Appointment::ROLE_DEPT => 'Department Chair',
                    \App\Models\Appointment::ROLE_DEAN => 'Dean',
                    \App\Models\Appointment::ROLE_ASSOC_DEAN => 'Associate Dean',
                    \App\Models\Appointment::ROLE_VCAA => 'VCAA',
                    \App\Models\Appointment::ROLE_ASSOC_VCAA => 'Associate VCAA',
                    default => $appt->role ?? 'Appointment'
                  };
                  
                  // Simple department display
                  $department = $departments->firstWhere('id', $appt->scope_id);
                  $deptName = $department ? $department->name : 'Institution-wide';
                @endphp

                <div class="sv-request-item">
                  <div class="sv-request-meta">
                    <span class="sv-pill is-accent sv-pill--sm">{{ $roleLabel }}</span>
                    <span class="sv-pill is-muted sv-pill--sm">{{ $deptName }}</span>
                  </div>

                  <div class="sv-request-actions">
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
              @endforeach
            @else
              <div class="text-muted">No active appointments for this faculty member.</div>
            @endif
          </div>
        </div>
        {{-- ░░░ END: Active Appointments ░░░ --}}

      </div>

      {{-- ░░░ START: Modal Footer ░░░ --}}
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
      {{-- ░░░ END: Modal Footer ░░░ --}}

    </div>
  </div>
</div>


