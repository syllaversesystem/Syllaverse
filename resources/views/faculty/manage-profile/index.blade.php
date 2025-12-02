{{--
-------------------------------------------------------------------------------
* File: resources/views/faculty/manage-profile/index.blade.php
* Description: Faculty Manage Profile module (separate from existing profile)
-------------------------------------------------------------------------------
--}}
@extends('layouts.faculty')
@section('title', 'Manage Profile')

@section('content')
<div class="container py-3" style="max-width: 880px;">
  <div class="d-flex align-items-center gap-2 mb-3">
    <h4 class="mb-0">Manage Profile</h4>
  </div>

  <div class="d-flex flex-column align-items-center gap-4">
    <!-- Profile Info -->
    <div class="w-100" style="max-width: 720px;">
      <div class="card shadow-sm" id="mpCardInfo">
        <div class="card-body">
          <h6 class="fw-bold mb-3">Profile Information</h6>
          <form id="manageProfileForm" class="modal-content" style="border:none; box-shadow:none;">
            @csrf
            <style>
              /* Align inputs/buttons with Add CDIO modal theme */
              #mpCardInfo .form-control { border-radius:12px; border:1px solid var(--sv-bdr,#E3E3E3); background:#fff; }
              #mpCardInfo .form-control:focus { border-color: var(--sv-acct,#EE6F57); box-shadow:0 0 0 3px rgba(238,111,87,.16); }
              #mpCardInfo .form-label { margin-bottom:.35rem; font-size:.8rem; letter-spacing:.02em; }
              #mpCardInfo .btn-danger { background:#fff; border:none; color:#000; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
              #mpCardInfo .btn-danger:hover, #mpCardInfo .btn-danger:focus { background:linear-gradient(135deg, rgba(235,235,235,.88), rgba(250,250,250,.46)); box-shadow:0 4px 10px rgba(0,0,0,.10); color:#000; }
              #mpCardInfo .btn-light { background:#fff; border:none; color:#000; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
              #mpCardInfo .btn-light:hover, #mpCardInfo .btn-light:focus { background:linear-gradient(135deg, rgba(225,225,225,.88), rgba(240,240,240,.46)); box-shadow:0 4px 10px rgba(0,0,0,.08); color:#000; }
            </style>
            <div class="mb-3">
              <label class="form-label small fw-medium text-muted">Name</label>
              <input type="text" class="form-control form-control-sm" name="name" value="{{ $user->name }}" required>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-medium text-muted">Employee Code</label>
              <input type="text" class="form-control form-control-sm" name="employee_code" value="{{ $user->employee_code ?? '' }}">
            </div>
            <div class="mb-3">
              <label class="form-label small fw-medium text-muted">Designation</label>
              <input type="text" class="form-control form-control-sm" name="designation" value="{{ $user->designation ?? '' }}">
            </div>
            <div class="d-flex justify-content-center">
              <button type="submit" class="btn btn-danger" id="mpSaveBtn"><i data-feather="save"></i> Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Separation line -->
    <div style="width: 100%; max-width: 720px; height: 2px; background-color: #e2e5e9;"></div>

    <!-- Role Requests -->
    <div class="w-100" style="max-width: 720px;">
      <div class="card shadow-sm" id="mpCardRole">
        <div class="card-body">
          @php
            // Track presence of active roles and any associated department
            $activeRolePresent = ['dean'=>false,'assoc_dean'=>false,'dept_chair'=>false,'faculty'=>false];
            $activeRoleDept    = ['dean'=>null,'assoc_dean'=>null,'dept_chair'=>null,'faculty'=>null];
            $pendingRole       = ['dean'=>false,'assoc_dean'=>false,'dept_chair'=>false,'faculty'=>false];
            if (isset($activeAppointments)) {
              foreach ($activeAppointments as $appt) {
                $roleVal = (string) ($appt->role ?? '');
                // Normalize to our UI keys
                switch ($roleVal) {
                  case 'DEPT_HEAD':
                  case 'dept_head':
                  case 'department_head':
                    $key = 'dept_chair';
                    break;
                  case 'DEAN':
                  case 'dean':
                    $key = 'dean';
                    break;
                  case 'ASSOC_DEAN':
                  case 'assoc_dean':
                    $key = 'assoc_dean';
                    break;
                  case 'FACULTY':
                  case 'faculty':
                    $key = 'faculty';
                    break;
                  default:
                    $key = null;
                }
                if ($key && array_key_exists($key, $activeRolePresent)) {
                  $activeRolePresent[$key] = true;
                  // Use dept if available (may be null for institution-scoped roles like DEAN)
                  if (!is_null($appt->scope_id)) {
                    $activeRoleDept[$key] = (int) $appt->scope_id;
                  }
                }
              }
            }
            // Determine pending requests per role
            if (isset($roleRequests)) {
              foreach ($roleRequests as $req) {
                $r = strtolower((string)($req->role ?? ''));
                $st = strtolower((string)($req->status ?? ''));
                if (in_array($r, ['dean','assoc_dean','dept_chair','faculty'], true) && $st === 'pending') {
                  $pendingRole[$r] = true;
                }
              }
            }
            $hasPending = in_array(true, $pendingRole, true);
          @endphp
          <h6 class="fw-bold mb-3">Request Role</h6>
          <form id="mpRoleRequestForm">
            @csrf
            <div class="mb-2">
              <div class="row g-2 align-items-center mb-2">
                <div class="col-md-6">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="mpRole_dean" name="roles[]" value="dean" data-pending="{{ $pendingRole['dean'] ? '1' : '0' }}" data-current="{{ $activeRolePresent['dean'] ? '1' : '0' }}" {{ $activeRolePresent['dean'] ? 'checked' : '' }} {{ $pendingRole['assoc_dean'] ? 'disabled' : '' }} title="{{ $pendingRole['assoc_dean'] ? 'Disabled: pending Associate Dean request exists' : '' }}">
                    <label class="form-check-label fw-semibold" for="mpRole_dean">Dean @if($activeRolePresent['dean']) <span class="text-success small ms-1">(Current)</span>@endif</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <select class="form-select form-select-sm sv-dept" id="mpDept_dean" name="department_id[dean]" {{ $activeRolePresent['dean'] ? '' : 'disabled' }}>
                    <option value="">— Select Department —</option>
                    @foreach (($departments ?? []) as $dept)
                      <option value="{{ $dept->id }}" {{ (int)$activeRoleDept['dean'] === (int)$dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="row g-2 align-items-center mb-2">
                <div class="col-md-6">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="mpRole_assoc_dean" name="roles[]" value="assoc_dean" data-pending="{{ $pendingRole['assoc_dean'] ? '1' : '0' }}" data-current="{{ $activeRolePresent['assoc_dean'] ? '1' : '0' }}" {{ $activeRolePresent['assoc_dean'] ? 'checked' : '' }} {{ $pendingRole['dean'] ? 'disabled' : '' }} title="{{ $pendingRole['dean'] ? 'Disabled: pending Dean request exists' : '' }}">
                    <label class="form-check-label fw-semibold" for="mpRole_assoc_dean">Associate Dean @if($activeRolePresent['assoc_dean']) <span class="text-success small ms-1">(Current)</span>@endif</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <select class="form-select form-select-sm sv-dept" id="mpDept_assoc_dean" name="department_id[assoc_dean]" {{ $activeRolePresent['assoc_dean'] ? '' : 'disabled' }}>
                    <option value="">— Select Department —</option>
                    @foreach (($departments ?? []) as $dept)
                      <option value="{{ $dept->id }}" {{ (int)$activeRoleDept['assoc_dean'] === (int)$dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="row g-2 align-items-center mb-2">
                <div class="col-md-6">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="mpRole_dept_chair" name="roles[]" value="dept_chair" data-pending="{{ $pendingRole['dept_chair'] ? '1' : '0' }}" data-current="{{ $activeRolePresent['dept_chair'] ? '1' : '0' }}" {{ $activeRolePresent['dept_chair'] ? 'checked' : '' }} {{ $pendingRole['dept_chair'] ? 'disabled' : '' }} title="{{ $pendingRole['dept_chair'] ? 'Disabled: pending Department Chair request exists' : '' }}">
                    <label class="form-check-label fw-semibold" for="mpRole_dept_chair">Department Chairperson @if($activeRolePresent['dept_chair']) <span class="text-success small ms-1">(Current)</span>@endif</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <select class="form-select form-select-sm sv-dept" id="mpDept_dept_chair" name="department_id[dept_chair]" {{ $activeRolePresent['dept_chair'] ? '' : 'disabled' }}>
                    <option value="">— Select Department —</option>
                    @foreach (($departments ?? []) as $dept)
                      <option value="{{ $dept->id }}" {{ (int)$activeRoleDept['dept_chair'] === (int)$dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="row g-2 align-items-center mb-3">
                <div class="col-md-6">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="mpRole_faculty" name="roles[]" value="faculty" data-pending="{{ $pendingRole['faculty'] ? '1' : '0' }}" data-current="{{ $activeRolePresent['faculty'] ? '1' : '0' }}" {{ $activeRolePresent['faculty'] ? 'checked' : '' }} {{ $pendingRole['faculty'] ? 'disabled' : '' }} title="{{ $pendingRole['faculty'] ? 'Disabled: pending Faculty request exists' : '' }}">
                    <label class="form-check-label fw-semibold" for="mpRole_faculty">Faculty @if($activeRolePresent['faculty']) <span class="text-success small ms-1">(Current)</span>@endif</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <select class="form-select form-select-sm sv-dept" id="mpDept_faculty" name="department_id[faculty]" {{ $activeRolePresent['faculty'] ? '' : 'disabled' }}>
                    <option value="">— Select Department —</option>
                    @foreach (($departments ?? []) as $dept)
                      <option value="{{ $dept->id }}" {{ (int)$activeRoleDept['faculty'] === (int)$dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-center">
              <button type="submit" class="btn btn-danger mp-request-btn" id="mpSubmitRoleBtn" title="Submit role request" aria-label="Submit role request" {{ $hasPending ? 'disabled' : '' }}>
                <i data-feather="send" class="me-1"></i>
                Request
              </button>
            </div>
            @if($hasPending)
              <div class="text-center small text-muted mt-2">You have a pending request. Please wait until it is approved or rejected to send another.</div>
            @endif
          </form>
          {{-- Confirmation Modal (styled similar to Add CDIO) --}}
          <div class="modal sv-faculty-dept-modal" id="mpConfirmModal" tabindex="-1" aria-labelledby="mpConfirmModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-md">
              <div class="modal-content" style="border-radius:16px; border:1px solid #E3E3E3; background:#fff; box-shadow:0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06); overflow:hidden;">
                <div class="modal-header" style="padding:.85rem 1rem; border-bottom:1px solid #E3E3E3; background:#fff;">
                  <h5 class="modal-title d-flex align-items-center gap-2" id="mpConfirmModalLabel" style="font-weight:600; font-size:1rem;">
                    <i data-feather="send"></i>
                    <span>Submit Role Request</span>
                  </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <p class="mb-0 small text-muted">Please confirm you want to submit the selected role request. You won’t be able to send another until this is approved or rejected.</p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i data-feather="x"></i> Cancel</button>
                  <button type="button" class="btn btn-danger" id="mpConfirmSubmitBtn"><i data-feather="check"></i> Confirm</button>
                  <style>
                    #mpConfirmModal .btn-danger,#mpConfirmModal .btn-light{ background:#fff; border:none; color:#000; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
                    #mpConfirmModal .btn-danger:hover,#mpConfirmModal .btn-danger:focus{ background:linear-gradient(135deg, rgba(235,235,235,.88), rgba(250,250,250,.46)); box-shadow:0 4px 10px rgba(0,0,0,.10); color:#000; }
                    #mpConfirmModal .btn-light:hover,#mpConfirmModal .btn-light:focus{ background:linear-gradient(135deg, rgba(225,225,225,.88), rgba(240,240,240,.46)); box-shadow:0 4px 10px rgba(0,0,0,.08); color:#000; }
                  </style>
                </div>
              </div>
            </div>
          </div>
          <div class="mt-3">
            <h6 class="fw-bold mb-2 d-flex align-items-center gap-2"><i data-feather="clipboard"></i><span>Status</span></h6>
            <div id="mpRoleStatusList" class="d-flex flex-column gap-2">
              @if(isset($roleRequests) && count($roleRequests))
                @php
                  $latest = $roleRequests instanceof \Illuminate\Support\Collection ? $roleRequests->first() : (is_array($roleRequests) ? $roleRequests[0] : null);
                @endphp
                @if($latest)
                  @php
                    $labelMap = ['dean'=>'Dean','assoc_dean'=>'Associate Dean','dept_chair'=>'Department Chairperson'];
                    $label = $labelMap[$latest->role] ?? $latest->role;
                    $deptName = isset($departments) ? optional(collect($departments)->firstWhere('id', $latest->department_id))->name : null;
                  @endphp
                  <div class="d-flex justify-content-between align-items-center border rounded px-2 py-2 mp-role-status-item" data-id="{{ $latest->id }}" data-status="{{ $latest->status }}" data-created="{{ $latest->created_at }}">
                    <div class="d-flex align-items-center gap-2">
                      <span class="badge rounded-pill bg-light text-dark">{{ $label }}</span>
                      @if($deptName)
                        <span class="badge rounded-pill bg-secondary-subtle text-dark">{{ $deptName }}</span>
                      @endif
                      <span class="small text-muted">{{ ucfirst($latest->status) }}</span>
                    </div>
                    <div class="small text-muted">{{ \Carbon\Carbon::parse($latest->created_at)->diffForHumans() }}</div>
                  </div>
                @endif
              @else
                <div class="text-muted small">No role requests yet.</div>
              @endif
            </div>
          </div>
          <style>
            /* Mimic manage-faculty modal button styling */
            #mpCardRole .action-btn.approve { background: var(--sv-card-bg, #fff); border: none; color: #000; transition: all .2s ease-in-out; box-shadow: none; display: inline-flex; align-items: center; gap: .5rem; padding: .5rem 1rem; border-radius: .375rem; }
            #mpCardRole .action-btn.approve:hover, #mpCardRole .action-btn.approve:focus { background: linear-gradient(135deg, rgba(220,220,220,.88), rgba(240,240,240,.46)); backdrop-filter: blur(7px); -webkit-backdrop-filter: blur(7px); box-shadow: 0 4px 10px rgba(0,0,0,.12); color: #000; }
            #mpCardRole .action-btn.approve:hover svg, #mpCardRole .action-btn.approve:focus svg { stroke: #000; }
            #mpCardRole .mp-role-status-item.approved, #mpCardRole .mp-role-status-item.rejected { border-color:#e2e5e9; }
            #mpCardRole .mp-request-btn { width: 50%; justify-content: center; }
            /* Match Save Changes button sizing and hover */
            #mpCardRole .btn-danger { background:#fff; border:none; color:#000; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
            #mpCardRole .btn-danger:hover, #mpCardRole .btn-danger:focus { background:linear-gradient(135deg, rgba(235,235,235,.88), rgba(250,250,250,.46)); box-shadow:0 4px 10px rgba(0,0,0,.10); color:#000; }
            /* Dropdown UI: rounded + hover/focus accent like modal */
            #mpCardRole .form-select.form-select-sm.sv-dept { border-radius: 12px; border:1px solid var(--sv-bdr,#E3E3E3); background:#fff; transition: box-shadow .15s ease, border-color .15s ease; }
            #mpCardRole .form-select.form-select-sm.sv-dept:hover { border-color: #E8A298; }
            #mpCardRole .form-select.form-select-sm.sv-dept:focus { border-color: var(--sv-acct,#EE6F57); box-shadow: 0 0 0 3px rgba(238,111,87,.16); }
          </style>
        </div>
      </div>
    </div>

    <!-- Separation line -->
    <div style="width: 100%; max-width: 720px; height: 2px; background-color: #e2e5e9;"></div>

    <!-- Danger Zone: Delete Account -->
    <div class="w-100" style="max-width: 720px;">
      <div class="card shadow-sm" id="mpCardDanger">
        <div class="card-body">
          <h6 class="fw-bold mb-3 text-danger">Danger Zone</h6>
          <p class="text-muted">Deleting your account is irreversible. Proceed with caution.</p>
          <div class="d-flex justify-content-center">
            <button class="btn btn-danger" id="mpDeleteBtn"><i data-feather="trash"></i> Delete Account</button>
          </div>
          <style>
            #mpCardDanger .btn-danger{ background:#fff; border:none; color:#000; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
            #mpCardDanger .btn-danger:hover, #mpCardDanger .btn-danger:focus{ background:linear-gradient(135deg, rgba(235,235,235,.88), rgba(250,250,250,.46)); box-shadow:0 4px 10px rgba(0,0,0,.10); color:#000; }
          </style>
          <!-- Delete Account Confirmation Modal -->
          <div class="modal" id="mpDeleteConfirmModal" tabindex="-1" aria-labelledby="mpDeleteConfirmLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-md">
              <div class="modal-content" style="border-radius:16px; border:1px solid #E3E3E3; background:#fff; box-shadow:0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06); overflow:hidden;">
                <style>
                  #mpDeleteConfirmModal { --sv-bdr:#E3E3E3; --sv-danger:#CB3737; --sv-bg:#FAFAFA; }
                  #mpDeleteConfirmModal .modal-header{ border-bottom:1px solid var(--sv-bdr); background: var(--sv-bg); }
                  #mpDeleteConfirmModal .modal-title{ color: var(--sv-danger); }
                  #mpDeleteConfirmModal .btn-danger{ background:#fff; border:none; color: var(--sv-danger); transition: all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
                  #mpDeleteConfirmModal .btn-danger:hover, #mpDeleteConfirmModal .btn-danger:focus{ background: linear-gradient(135deg, rgba(255,235,235,.88), rgba(255,245,245,.46)); box-shadow:0 4px 10px rgba(203,55,55,.15); color: var(--sv-danger); }
                  #mpDeleteConfirmModal .btn-light{ background:#fff; border:none; color:#000; transition: all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
                  #mpDeleteConfirmModal .btn-light:hover, #mpDeleteConfirmModal .btn-light:focus{ background: linear-gradient(135deg, rgba(225,225,225,.88), rgba(240,240,240,.46)); box-shadow:0 4px 10px rgba(0,0,0,.08); color:#000; }
                </style>
                <div class="modal-header" style="padding:.85rem 1rem;">
                  <h5 class="modal-title d-flex align-items-center gap-2" id="mpDeleteConfirmLabel" style="font-weight:600; font-size:1rem;">
                    <i data-feather="trash-2"></i>
                    <span>Confirm Account Deletion</span>
                  </h5>
                </div>
                <div class="modal-body">
                  <p class="mb-2 small text-muted">This action permanently deletes your account and all associated data. This cannot be undone.</p>
                  <div class="alert alert-warning py-2 px-3 mb-0" style="border-radius:12px;">
                    <div class="d-flex align-items-center gap-2">
                      <i data-feather="info"></i>
                      <span class="small">If you proceed, you will be signed out immediately.</span>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i data-feather="x"></i> Cancel</button>
                  <button type="button" class="btn btn-danger" id="mpDeleteConfirmBtn"><i data-feather="trash-2"></i> Delete</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/faculty/manageprofile.js')
@include('components.alert-overlay')
@endpush
