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

@php
  // Determine faculty department (from active Faculty appointment)
  $facultyDeptId = optional($activeAppointments->firstWhere('role', \App\Models\Appointment::ROLE_FACULTY))->scope_id;
  // Determine active leadership department (Dept Head / Dean / Assoc Dean)
  $leadershipRoles = [\App\Models\Appointment::ROLE_DEPT_HEAD, \App\Models\Appointment::ROLE_DEAN, \App\Models\Appointment::ROLE_ASSOC_DEAN];
  $leadershipAppt = $activeAppointments->first(function($a) use ($leadershipRoles) { return in_array($a->role, $leadershipRoles, true); });
  $leadershipDeptId = optional($leadershipAppt)->scope_id;
  // Active role codes for option filtering in Edit selects
  $activeRoleCodes = $activeAppointments->pluck('role')->toArray();
  // Only Faculty active? (no leadership roles among actives)
  $onlyFacultyActive = $activeAppointments->count() > 0 && $activeAppointments->every(function($a){
    return $a->role === \App\Models\Appointment::ROLE_FACULTY;
  });
@endphp
<div class="modal fade sv-appt-modal" id="manageFaculty-{{ $faculty->id }}" tabindex="-1" aria-hidden="true" data-leadership-dept-id="{{ (int) ($leadershipDeptId ?? 0) }}" data-only-faculty-active="{{ $onlyFacultyActive ? 1 : 0 }}" data-user-id="{{ $faculty->id }}">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content">

      {{-- ░░░ START: Modal Header ░░░ --}}
      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2">
          <i data-feather="user-check"></i>
          <span>Manage Faculty — {{ $faculty->name }}</span>
        </h5>
      </div>
      {{-- ░░░ END: Modal Header ░░░ --}}

      <div class="modal-body">

        {{-- ░░░ START: Add Appointment (AJAX) ░░░ --}}
        <div class="mb-3">
          <h6 class="mb-2 d-flex align-items-center gap-2">
            <i data-feather="plus-circle"></i><span>Set Appointment</span>
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
                <option value="{{ \App\Models\Appointment::ROLE_DEPT_HEAD }}">Department Head (Dean/Head/Principal)</option>
                <option value="{{ \App\Models\Appointment::ROLE_ASSOC_DEAN }}">Associate Dean</option>
                <option value="{{ \App\Models\Appointment::ROLE_CHAIR }}">Chairperson</option>
              </select>
            </div>

            <div class="col-md-7">
              <label class="form-label small">Department</label>
              <select name="department_id" class="form-select form-select-sm sv-dept" required>
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
            <i data-feather="briefcase"></i><span>Current Appointment</span>
          </h6>

          <div class="sv-request-list" id="sv-appt-list-{{ $faculty->id }}">
            @if ($activeAppointments->count())
              @foreach ($activeAppointments as $appt)
                @php
                  // Determine department & program count for dynamic DEPT_HEAD mapping
                  $department = $departments->firstWhere('id', $appt->scope_id);
                  $programCount = 0;
                  if ($department) {
                    if (method_exists($department, 'programs')) {
                      $programCount = $department->relationLoaded('programs') ? $department->programs->count() : \App\Models\Program::where('department_id', $department->id)->count();
                    }
                  }

                  // Dynamic role label
                  if ($appt->role === \App\Models\Appointment::ROLE_DEPT_HEAD) {
                    // Default: Dean; special cases: Principal for Lab School; Head for General Education
                    $deptNameLower = $department ? strtolower($department->name) : '';
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
                  } elseif ($appt->role === \App\Models\Appointment::ROLE_CHAIR) {
                    // Chairperson label depends on program count
                    $roleLabel = $programCount >= 2 ? 'Department Chairperson' : 'Program Chair';
                  } else {
                    $roleLabel = match($appt->role) {
                      \App\Models\Appointment::ROLE_FACULTY => 'Faculty',
                      \App\Models\Appointment::ROLE_DEPT => 'Department Chairperson',
                      \App\Models\Appointment::ROLE_PROG => 'Program Chair',
                      \App\Models\Appointment::ROLE_DEAN => 'Dean',
                      \App\Models\Appointment::ROLE_ASSOC_DEAN => 'Associate Dean',
                      \App\Models\Appointment::ROLE_VCAA => 'VCAA',
                      \App\Models\Appointment::ROLE_ASSOC_VCAA => 'Associate VCAA',
                      default => $appt->role ?? 'Appointment'
                    };
                  }

                  // Department display
                  $deptName = $department ? $department->name : 'Institution-wide';
                @endphp

                <div class="sv-request-item">
                  <div class="sv-request-meta">
                    <span class="sv-pill is-accent sv-pill--sm">{{ $roleLabel }}</span>
                    <span class="sv-pill is-muted sv-pill--sm">{{ $deptName }}</span>
                  </div>

                  <div class="sv-request-actions">
                    <button class="action-btn edit" type="button"
                            data-bs-toggle="collapse" data-bs-target="#sv-fac-appt-edit-{{ $appt->id }}"
                            aria-expanded="false" aria-controls="sv-fac-appt-edit-{{ $appt->id }}"
                            title="Edit appointment" aria-label="Edit appointment">
                      <i data-feather="edit-3"></i>
                    </button>
                    <form method="POST"
                          action="{{ route('superadmin.appointments.destroy', $appt) }}"
                          class="d-inline"
                          data-ajax="true">
                      @csrf
                      @method('DELETE')
                      <button class="action-btn reject" type="submit" title="Delete appointment" aria-label="Delete appointment" {{ $appt->role === \App\Models\Appointment::ROLE_FACULTY ? 'disabled aria-disabled=true' : '' }}>
                        <i data-feather="trash-2"></i>
                      </button>
                    </form>
                  </div>
                </div>

                <div id="sv-fac-appt-edit-{{ $appt->id }}" class="collapse sv-details" data-bs-parent="#sv-appt-list-{{ $faculty->id }}" data-current-role="{{ $appt->role }}" data-current-dept-id="{{ (int)$appt->scope_id }}">
                  <form method="POST"
                        action="{{ route('superadmin.appointments.update', $appt->id) }}"
                        class="row g-2 align-items-end sv-appt-form"
                        data-sv-scope="edit-{{ $appt->id }}"
                        data-ajax="true">
                    @csrf
                    @method('PUT')

                    <div class="col-md-4">
                      <label class="form-label small">Role</label>
                      <select name="role" class="form-select form-select-sm" required>
                        <option value="">— Select Role —</option>
                        @if($activeAppointments->count() < 2 && (!in_array(\App\Models\Appointment::ROLE_FACULTY, $activeRoleCodes, true) || $appt->role === \App\Models\Appointment::ROLE_FACULTY))
                          <option value="{{ \App\Models\Appointment::ROLE_FACULTY }}" {{ $appt->role === \App\Models\Appointment::ROLE_FACULTY ? 'selected' : '' }}>Faculty</option>
                        @endif
                        @if(!in_array(\App\Models\Appointment::ROLE_DEPT_HEAD, $activeRoleCodes, true) || $appt->role === \App\Models\Appointment::ROLE_DEPT_HEAD)
                          <option value="{{ \App\Models\Appointment::ROLE_DEPT_HEAD }}" {{ $appt->role === \App\Models\Appointment::ROLE_DEPT_HEAD ? 'selected' : '' }}>Department Head (Dean/Head/Principal)</option>
                        @endif
                        @php $multiActive = $activeAppointments->count() >= 2; @endphp
                        @if(!in_array(\App\Models\Appointment::ROLE_ASSOC_DEAN, $activeRoleCodes, true) || $appt->role === \App\Models\Appointment::ROLE_ASSOC_DEAN)
                          <option value="{{ \App\Models\Appointment::ROLE_ASSOC_DEAN }}" {{ $appt->role === \App\Models\Appointment::ROLE_ASSOC_DEAN ? 'selected' : '' }}>Associate Dean</option>
                        @endif
                        @if(!in_array(\App\Models\Appointment::ROLE_CHAIR, $activeRoleCodes, true) || $appt->role === \App\Models\Appointment::ROLE_CHAIR)
                          <option value="{{ \App\Models\Appointment::ROLE_CHAIR }}" {{ $appt->role === \App\Models\Appointment::ROLE_CHAIR ? 'selected' : '' }}>Chairperson</option>
                        @endif
                      </select>
                    </div>

                    <div class="col-md-7">
                      <label class="form-label small">Department</label>
                      <select name="department_id" class="form-select form-select-sm sv-dept" required>
                        <option value="">— Select Department —</option>
                        @foreach (($departments ?? []) as $dept)
                          <option value="{{ $dept->id }}" {{ (int)$appt->scope_id === (int)$dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
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
              <div class="sv-appt-placeholder rounded border border-2 border-dashed p-3 text-center text-muted">
                <div class="mb-2">
                  <i data-feather="briefcase"></i>
                </div>
                <div class="fw-semibold">No active appointments</div>
                <div class="small">Use the form above to add Department Head, Associate Dean, or Chairperson.</div>
              </div>
            @endif
          </div>
        </div>
        {{-- ░░░ END: Active Appointments ░░░ --}}

      </div>

      {{-- ░░░ START: Modal Footer ░░░ --}}
      <style>
        /* Placeholder styling */
        #manageFaculty-{{ $faculty->id }} .border-dashed {
          border-style: dashed !important;
        }
        /* Faculty modal button styling */
        #manageFaculty-{{ $faculty->id }} .btn-danger {
          background: var(--sv-card-bg, #fff);
          border: none;
          color: #000;
          transition: all 0.2s ease-in-out;
          box-shadow: none;
          display: inline-flex;
          align-items: center;
          gap: 0.5rem;
          padding: 0.5rem 1rem;
          border-radius: 0.375rem;
        }
        #manageFaculty-{{ $faculty->id }} .btn-danger:hover,
        #manageFaculty-{{ $faculty->id }} .btn-danger:focus {
          background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(204, 55, 55, 0.12);
          color: #CB3737;
        }
        #manageFaculty-{{ $faculty->id }} .btn-danger:hover i,
        #manageFaculty-{{ $faculty->id }} .btn-danger:hover svg,
        #manageFaculty-{{ $faculty->id }} .btn-danger:focus i,
        #manageFaculty-{{ $faculty->id }} .btn-danger:focus svg {
          stroke: #CB3737;
        }
        #manageFaculty-{{ $faculty->id }} .btn-danger:active {
          background: linear-gradient(135deg, rgba(255, 230, 225, 0.98), rgba(255, 255, 255, 0.62));
          box-shadow: 0 1px 8px rgba(204, 55, 55, 0.16);
        }
        #manageFaculty-{{ $faculty->id }} .btn-danger:active i,
        #manageFaculty-{{ $faculty->id }} .btn-danger:active svg {
          stroke: #CB3737;
        }
        /* Close button styling */
        #manageFaculty-{{ $faculty->id }} .btn-light {
          background: var(--sv-card-bg, #fff);
          border: none;
          color: #000;
          transition: all 0.2s ease-in-out;
          box-shadow: none;
          display: inline-flex;
          align-items: center;
          gap: 0.5rem;
          padding: 0.5rem 1rem;
          border-radius: 0.375rem;
        }
        #manageFaculty-{{ $faculty->id }} .btn-light:hover,
        #manageFaculty-{{ $faculty->id }} .btn-light:focus {
          background: linear-gradient(135deg, rgba(220, 220, 220, 0.88), rgba(240, 240, 240, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
          color: #000;
        }
        #manageFaculty-{{ $faculty->id }} .btn-light:hover i,
        #manageFaculty-{{ $faculty->id }} .btn-light:hover svg,
        #manageFaculty-{{ $faculty->id }} .btn-light:focus i,
        #manageFaculty-{{ $faculty->id }} .btn-light:focus svg {
          stroke: #000;
        }
        #manageFaculty-{{ $faculty->id }} .btn-light:active {
          background: linear-gradient(135deg, rgba(240, 242, 245, 0.98), rgba(255, 255, 255, 0.62));
          box-shadow: 0 1px 8px rgba(0, 0, 0, 0.16);
          color: #000;
        }
        #manageFaculty-{{ $faculty->id }} .btn-light:active i,
        #manageFaculty-{{ $faculty->id }} .btn-light:active svg {
          stroke: #000;
        }
      </style>
      <div class="modal-footer d-flex justify-content-between">
        <div>
          {{-- Revoke Faculty Access: opens confirmation modal (no direct submit here) --}}
          <button type="button" class="btn btn-danger btn-sm" title="Revoke faculty access" data-bs-toggle="modal" data-bs-target="#revokeFacultyModal-{{ $faculty->id }}">
            <i data-feather="user-x" class="me-1"></i>
            Revoke Access
          </button>
        </div>
        <div>
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">
            <i data-feather="x" class="me-1"></i>
            Close
          </button>
        </div>
      </div>
      {{-- ░░░ END: Modal Footer ░░░ --}}

    </div>
  </div>
</div>

{{-- Scripts moved to resources/js/superadmin/manage-accounts/manage-accounts.js --}}

{{-- ░░░ START: Revoke Faculty Confirmation Modal ░░░ --}}
<div class="modal fade" id="revokeFacultyModal-{{ $faculty->id }}" tabindex="-1" aria-labelledby="revokeFacultyModalLabel-{{ $faculty->id }}" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-2">
        <h5 class="modal-title d-flex align-items-center gap-2 text-danger" id="revokeFacultyModalLabel-{{ $faculty->id }}">
          <i data-feather="alert-triangle"></i>
          Confirm Revoke
        </h5>
      </div>
      <div class="modal-body pt-0">
        <p class="mb-3">Are you sure you want to revoke access for <strong>{{ $faculty->name }}</strong>?</p>
        <div class="bg-light rounded p-3 mb-0">
          <ul class="mb-0 text-sm">
            <li>Removes active appointments</li>
            <li>Sets status to rejected</li>
            <li>Blocks system access</li>
          </ul>
        </div>
      </div>
      <div class="modal-footer border-0 pt-2">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
          <i data-feather="x" class="me-1"></i>
          Cancel
        </button>
        <form method="POST" 
              action="{{ route('superadmin.accounts.revoke', $faculty->id) }}"
              class="d-inline"
              data-ajax="true"
              data-sv-revoke="true">
          @csrf
          @method('PATCH')
          <button type="submit" class="btn btn-danger">
            <i data-feather="user-x" class="me-1"></i>
            Confirm Revoke
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
{{-- ░░░ END: Revoke Faculty Confirmation Modal ░░░ --}}

<script>
  // Ensure confirmation modal is appended to body (avoid clipping/stuck stacking)
  document.addEventListener('DOMContentLoaded', function() {
    try {
      var mId = 'revokeFacultyModal-{{ $faculty->id }}';
      var modal = document.getElementById(mId);
      if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
      }
      // Use Bootstrap defaults by removing explicit static constraints
      if (modal) {
        modal.removeAttribute('data-bs-backdrop');
        modal.removeAttribute('data-bs-keyboard');
      }
    } catch (e) {}
  });
</script>


