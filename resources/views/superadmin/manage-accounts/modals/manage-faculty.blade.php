{{-- 
-------------------------------------------------------------------------------
* File: resources/views/superadmin/manage-accounts/modals/manage-faculty.blade.php
* Description: Per-faculty "Manage" modal – add/edit/end faculty appointments with department assignment
-------------------------------------------------------------------------------
📜 Log:
[2025-10-23] Initial creation – faculty appointment management modal for superadmin.
-------------------------------------------------------------------------------
--}}

@php
  // Build quick id->name maps for nicer labels
  $deptById = collect($departments ?? [])->keyBy('id');

  // Ensure appointments relation is available
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
              <select name="role" class="form-select form-select-sm sv-role" aria-label="Select role">
                <option value="">— Select Role —</option>
                <option value="{{ \App\Models\Appointment::ROLE_FACULTY }}">Faculty</option>
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
          <div class="sv-request-list" id="sv-appt-list-{{ $faculty->id }}">
            @if ($activeAppointments->count())
              @foreach ($activeAppointments as $appt)
                @php
                  $isDept = $appt->role === \App\Models\Appointment::ROLE_DEPT;
                  $isProg = $appt->role === \App\Models\Appointment::ROLE_PROG;
                  $isDean = $appt->role === \App\Models\Appointment::ROLE_DEAN;
                  $isFaculty = $appt->role === \App\Models\Appointment::ROLE_FACULTY;
                  
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
                  } elseif ($isFaculty) {
                    $roleLabel = 'Faculty';
                  } else {
                    $roleLabel = $appt->role ?? 'Appointment';
                  }
                  
                  // Handle scope label properly
                  if ($appt->scope_id === null || $appt->scope_id == 0) {
                    $scopeLabel = 'Institution-wide';
                  } elseif ($isFaculty || $isDept || $isProg || $isDean || $appt->role === \App\Models\Appointment::ROLE_ASSOC_DEAN) {
                    // These roles use department scope
                    $scopeLabel = $deptById[$appt->scope_id]->name ?? 'Unknown Department';
                  } else {
                    // For other appointment types (VCAA, Associate VCAA)
                    $scopeLabel = 'Institution-wide';
                  }
                  
                  $collapseId = "sv-appt-edit-{$appt->id}";
                  $currentDeptId = (int) $appt->scope_id;
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
                        <option value="{{ \App\Models\Appointment::ROLE_FACULTY }}" {{ $isFaculty ? 'selected' : '' }}>Faculty</option>
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
              <div class="text-muted">No active appointments for this faculty member.</div>
            @endif
          </div>
        </div>
        {{-- ░░░ END: Active Appointments ░░░ --}}

      </div>

      {{-- ░░░ START: Modal Footer ░░░ --}}
      <div class="modal-footer d-flex justify-content-between">
        {{-- Account management actions --}}
        <div class="d-flex gap-2">
          @if($faculty->status === 'active')
            <form method="POST"
                  action="{{ route('superadmin.suspend.faculty', $faculty->id) }}"
                  class="d-inline"
                  data-ajax="true">
              @csrf
              <button class="btn btn-outline-warning btn-sm" type="submit">Suspend Faculty</button>
            </form>
            
            <form method="POST"
                  action="{{ route('superadmin.reject.faculty', $faculty->id) }}"
                  class="d-inline"
                  data-ajax="true">
              @csrf
              <button class="btn btn-outline-danger btn-sm" type="submit">Reject Faculty</button>
            </form>
          @elseif($faculty->status === 'suspended')
            <form method="POST"
                  action="{{ route('superadmin.reactivate.faculty', $faculty->id) }}"
                  class="d-inline"
                  data-ajax="true">
              @csrf
              <button class="btn btn-outline-success btn-sm" type="submit">Reactivate Faculty</button>
            </form>
          @endif
        </div>
        
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
      {{-- ░░░ END: Modal Footer ░░░ --}}

    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Handle department dropdown accessibility based on role selection for faculty
  const modal = document.getElementById('manageFaculty-{{ $faculty->id }}');
  if (modal) {
    const roleSelects = modal.querySelectorAll('.sv-role');
    const deptSelects = modal.querySelectorAll('.sv-dept');
    
    // Define roles that require department selection
    const deptRequiredRoles = [
      '{{ \App\Models\Appointment::ROLE_FACULTY }}',
      '{{ \App\Models\Appointment::ROLE_DEPT }}', 
      '{{ \App\Models\Appointment::ROLE_DEAN }}',
      '{{ \App\Models\Appointment::ROLE_ASSOC_DEAN }}'
    ];
    
    function updateDeptDropdownState(roleSelect, deptSelect) {
      const selectedRole = roleSelect.value;
      const requiresDept = deptRequiredRoles.includes(selectedRole);
      
      if (requiresDept) {
        deptSelect.disabled = false;
        deptSelect.querySelector('option[value=""]').textContent = '— Select Department —';
      } else {
        deptSelect.disabled = true;
        deptSelect.value = '';
        deptSelect.querySelector('option[value=""]').textContent = '— Not Required —';
      }
    }
    
    // Initialize and add event listeners for each role/dept pair
    roleSelects.forEach((roleSelect, index) => {
      const deptSelect = deptSelects[index];
      if (deptSelect) {
        // Initialize on page load
        updateDeptDropdownState(roleSelect, deptSelect);
        
        // Add change listener for role
        roleSelect.addEventListener('change', function() {
          updateDeptDropdownState(roleSelect, deptSelect);
        });
      }
    });
  }
});
</script>
