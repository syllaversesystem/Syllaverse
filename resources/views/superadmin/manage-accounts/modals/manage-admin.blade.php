{{-- 
-------------------------------------------------------------------------------
* File: resources/views/superadmin/manage-accounts/modals/manage-admin.blade.php
* Description: Per-admin “Manage” modal (Add / Edit / End appointments; Revoke) – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-08-09] Initial creation – extracted from admins-approved tab into reusable partial.
[2025-08-09] Removed start date/time fields; new/updated appointments start immediately (server defaults to now()).
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
        <h5 class="modal-title">Manage Admin — {{ $admin->name }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      {{-- ░░░ END: Modal Header ░░░ --}}

      <div class="modal-body">

        {{-- ░░░ START: Add Appointment (no time fields) ░░░ --}}
        <div class="mb-4">
          <h6 class="mb-2">Add Appointment</h6>

          <form method="POST"
                action="{{ route('superadmin.appointments.store') }}"
                class="sv-appt-form row g-2 align-items-end"
                data-sv-scope="add-{{ $admin->id }}">
            @csrf
            <input type="hidden" name="user_id" value="{{ $admin->id }}">

            <div class="col-md-3">
              <label class="form-label">Role</label>
              <select name="role" class="form-select form-select-sm sv-role">
                <option value="{{ \App\Models\Appointment::ROLE_DEPT }}">Department Chair</option>
                <option value="{{ \App\Models\Appointment::ROLE_PROG }}">Program Chair</option>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label">Department</label>
              <select name="department_id" class="form-select form-select-sm sv-dept">
                <option value="">— Select —</option>
                @foreach (($departments ?? []) as $dept)
                  <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-5">
              <label class="form-label">Program</label>
              <select name="program_id" class="form-select form-select-sm sv-prog" disabled>
                <option value="">— Select —</option>
                @foreach (($programs ?? []) as $prog)
                  <option value="{{ $prog->id }}" data-dept="{{ $prog->department_id }}">{{ $prog->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-12">
              <button class="btn btn-success btn-sm">Save Appointment</button>
            </div>
          </form>
        </div>
        {{-- ░░░ END: Add Appointment ░░░ --}}

        {{-- ░░░ START: Active Appointments (Edit/End) – no time fields ░░░ --}}
        <div class="border-top pt-3">
          <h6 class="mb-2">Active Appointments</h6>

          @if ($activeAppointments->count())
            @foreach ($activeAppointments as $appt)
              @php
                $isDept = $appt->role === \App\Models\Appointment::ROLE_DEPT;
                $currentDeptId = $isDept
                  ? (int) $appt->scope_id
                  : (int) ($progById[$appt->scope_id]->department_id ?? 0);
              @endphp

              <div class="border rounded p-3 mb-3">
                <div class="small text-muted mb-2">
                  Current:
                  <strong>{{ $isDept ? 'Dept Chair' : 'Program Chair' }}</strong> —
                  {{ $isDept
                      ? ($deptById[$appt->scope_id]->name ?? ('Dept #'.$appt->scope_id))
                      : ($progById[$appt->scope_id]->name ?? ('Prog #'.$appt->scope_id)) }}
                </div>

                <form method="POST"
                      action="{{ route('superadmin.appointments.update', $appt->id) }}"
                      class="sv-appt-form row g-2 align-items-end"
                      data-sv-scope="edit-{{ $appt->id }}">
                  @csrf
                  @method('PUT')

                  <div class="col-md-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select form-select-sm sv-role">
                      <option value="{{ \App\Models\Appointment::ROLE_DEPT }}" {{ $isDept ? 'selected' : '' }}>Department Chair</option>
                      <option value="{{ \App\Models\Appointment::ROLE_PROG }}" {{ !$isDept ? 'selected' : '' }}>Program Chair</option>
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-select form-select-sm sv-dept">
                      <option value="">— Select —</option>
                      @foreach (($departments ?? []) as $dept)
                        <option value="{{ $dept->id }}" {{ (int)$dept->id === $currentDeptId ? 'selected' : '' }}>
                          {{ $dept->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-5">
                    <label class="form-label">Program</label>
                    <select name="program_id" class="form-select form-select-sm sv-prog" {{ $isDept ? 'disabled' : '' }}>
                      <option value="">— Select —</option>
                      @foreach (($programs ?? []) as $prog)
                        <option value="{{ $prog->id }}"
                                data-dept="{{ $prog->department_id }}"
                                {{ (!$isDept && (int)$prog->id === (int)$appt->scope_id) ? 'selected' : '' }}>
                          {{ $prog->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-12 d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm">Update</button>

                    <form method="POST" action="{{ route('superadmin.appointments.end', $appt->id) }}">
                      @csrf
                      <button class="btn btn-outline-danger btn-sm" type="submit">End</button>
                    </form>
                  </div>
                </form>
              </div>
            @endforeach
          @else
            <div class="text-muted">No active appointments for this admin.</div>
          @endif
        </div>
        {{-- ░░░ END: Active Appointments (Edit/End) ░░░ --}}

      </div>

      {{-- ░░░ START: Modal Footer ░░░ --}}
      <div class="modal-footer d-flex justify-content-between">
        <form method="POST" action="{{ route('superadmin.reject.admin', $admin->id) }}" class="me-auto">
          @csrf
          <button class="btn btn-outline-danger btn-sm">Revoke Admin</button>
        </form>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
      {{-- ░░░ END: Modal Footer ░░░ --}}

    </div>
  </div>
</div>
