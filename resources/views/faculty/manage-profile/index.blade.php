{{--
-------------------------------------------------------------------------------
* File: resources/views/faculty/manage-profile/index.blade.php
* Description: Faculty Manage Profile module (separate from existing profile)
-------------------------------------------------------------------------------
--}}
@extends('layouts.faculty')
@section('title', 'Manage Profile')
@section('page-title', 'Manage Profile')

@section('content')
<div class="container py-3 sv-mp-container">
  

  <div class="d-flex flex-column align-items-center gap-4 sv-mp-stack">
    <!-- Profile Info -->
    <div class="w-100 sv-mp-section">
      <div class="card shadow-sm sv-card" id="mpCardInfo">
        <div class="card-body">
          <h6 class="fw-bold mb-3 sv-card-title">Profile Information</h6>
          <form id="manageProfileForm" class="sv-form-flat">
            @csrf
            <div class="mb-3">
              <label class="form-label small fw-medium text-muted">Name</label>
              <input type="text" class="form-control form-control-sm sv-input" name="name" value="{{ $user->name }}" required>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-medium text-muted">Employee Code</label>
              <input type="text" class="form-control form-control-sm sv-input" name="employee_code" value="{{ $user->employee_code ?? '' }}">
            </div>
            <div class="mb-3">
              <label class="form-label small fw-medium text-muted">Designation</label>
              <input type="text" class="form-control form-control-sm sv-input" name="designation" value="{{ $user->designation ?? '' }}">
            </div>
            <div class="d-flex justify-content-center">
              <button type="submit" class="btn btn-danger sv-btn" id="mpSaveBtn"><i data-feather="save"></i> Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Separation line -->
    <hr class="sv-divider" />

    <!-- Role Requests -->
    <div class="w-100 sv-mp-section">
      <div class="card shadow-sm sv-card" id="mpCardRole">
        <div class="card-body">
          @php
            // Track presence of active roles and any associated department
            $activeRolePresent = ['dean'=>false,'assoc_dean'=>false,'dept_chair'=>false,'faculty'=>false];
            $activeRoleDept    = ['dean'=>null,'assoc_dean'=>null,'dept_chair'=>null,'faculty'=>null];
            $pendingRole       = ['dean'=>false,'assoc_dean'=>false,'dept_chair'=>false,'faculty'=>false];
            if (isset($activeAppointments)) {
              foreach ($activeAppointments as $appt) {
                $roleVal = strtoupper((string) ($appt->role ?? ''));
                // Normalize to our UI keys based on canonical roles
                switch ($roleVal) {
                  case 'DEPT_HEAD':
                    // Department Head (Dean/Head/Principal)
                    $key = 'dean';
                    break;
                  case 'ASSOC_DEAN':
                    $key = 'assoc_dean';
                    break;
                  case 'CHAIR':
                    $key = 'dept_chair';
                    break;
                  case 'FACULTY':
                    $key = 'faculty';
                    break;
                  default:
                    $key = null;
                }
                if ($key && array_key_exists($key, $activeRolePresent)) {
                  $activeRolePresent[$key] = true;
                  // Use dept if available (institution-scoped roles not used here)
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
          <h6 class="fw-bold mb-3 sv-card-title">Request Role</h6>
          <form id="mpRoleRequestForm" class="sv-form-flat">
            @csrf
            <div class="mb-2">
              <div class="row g-2 align-items-center mb-2">
                <div class="col-md-6">
                  <div class="form-check">
                    <input class="form-check-input sv-check" type="checkbox" id="mpRole_dean" name="roles[]" value="dean" data-pending="{{ $pendingRole['dean'] ? '1' : '0' }}" data-current="{{ $activeRolePresent['dean'] ? '1' : '0' }}" {{ $activeRolePresent['dean'] ? 'checked' : '' }} {{ $pendingRole['assoc_dean'] ? 'disabled' : '' }} title="{{ $pendingRole['assoc_dean'] ? 'Disabled: pending Associate Dean request exists' : '' }}">
                    <label class="form-check-label fw-semibold" for="mpRole_dean">Department Head (Dean/Head/Principal) @if($activeRolePresent['dean']) <span class="text-success small ms-1">(Current)</span>@endif</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <select class="form-select form-select-sm sv-dept sv-input" id="mpDept_dean" name="department_id[dean]" {{ $activeRolePresent['dean'] ? '' : 'disabled' }}>
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
                    <input class="form-check-input sv-check" type="checkbox" id="mpRole_assoc_dean" name="roles[]" value="assoc_dean" data-pending="{{ $pendingRole['assoc_dean'] ? '1' : '0' }}" data-current="{{ $activeRolePresent['assoc_dean'] ? '1' : '0' }}" {{ $activeRolePresent['assoc_dean'] ? 'checked' : '' }} {{ $pendingRole['dean'] ? 'disabled' : '' }} title="{{ $pendingRole['dean'] ? 'Disabled: pending Dean request exists' : '' }}">
                    <label class="form-check-label fw-semibold" for="mpRole_assoc_dean">Associate Dean @if($activeRolePresent['assoc_dean']) <span class="text-success small ms-1">(Current)</span>@endif</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <select class="form-select form-select-sm sv-dept sv-input" id="mpDept_assoc_dean" name="department_id[assoc_dean]" {{ $activeRolePresent['assoc_dean'] ? '' : 'disabled' }}>
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
                    <input class="form-check-input sv-check" type="checkbox" id="mpRole_dept_chair" name="roles[]" value="dept_chair" data-pending="{{ $pendingRole['dept_chair'] ? '1' : '0' }}" data-current="{{ $activeRolePresent['dept_chair'] ? '1' : '0' }}" {{ $activeRolePresent['dept_chair'] ? 'checked' : '' }} {{ $pendingRole['dept_chair'] ? 'disabled' : '' }} title="{{ $pendingRole['dept_chair'] ? 'Disabled: pending Department Chair request exists' : '' }}">
                    <label class="form-check-label fw-semibold" for="mpRole_dept_chair">Chairperson @if($activeRolePresent['dept_chair']) <span class="text-success small ms-1">(Current)</span>@endif</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <select class="form-select form-select-sm sv-dept sv-input" id="mpDept_dept_chair" name="department_id[dept_chair]" {{ $activeRolePresent['dept_chair'] ? '' : 'disabled' }}>
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
                    <input class="form-check-input sv-check" type="checkbox" id="mpRole_faculty" name="roles[]" value="faculty" data-pending="{{ $pendingRole['faculty'] ? '1' : '0' }}" data-current="{{ $activeRolePresent['faculty'] ? '1' : '0' }}" {{ $activeRolePresent['faculty'] ? 'checked' : '' }} {{ $pendingRole['faculty'] ? 'disabled' : '' }} title="{{ $pendingRole['faculty'] ? 'Disabled: pending Faculty request exists' : '' }}">
                    <label class="form-check-label fw-semibold" for="mpRole_faculty">Faculty @if($activeRolePresent['faculty']) <span class="text-success small ms-1">(Current)</span>@endif</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <select class="form-select form-select-sm sv-dept sv-input" id="mpDept_faculty" name="department_id[faculty]" {{ $activeRolePresent['faculty'] ? '' : 'disabled' }}>
                    <option value="">— Select Department —</option>
                    @foreach (($departments ?? []) as $dept)
                      <option value="{{ $dept->id }}" {{ (int)$activeRoleDept['faculty'] === (int)$dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-center">
              <button type="submit" class="btn btn-danger mp-request-btn sv-btn" id="mpSubmitRoleBtn" title="Submit role request" aria-label="Submit role request" {{ $hasPending ? 'disabled' : '' }}>
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
              <div class="modal-content sv-modal-content">
                <div class="modal-header sv-modal-header">
                  <h5 class="modal-title d-flex align-items-center gap-2" id="mpConfirmModalLabel" style="font-weight:600; font-size:1rem;">
                    <i data-feather="send"></i>
                    <span>Submit Role Request</span>
                  </h5>
                </div>
                <div class="modal-body">
                  <p class="mb-0 small text-muted">Please confirm you want to submit the selected role request. You won’t be able to send another until this is approved or rejected.</p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-light sv-btn" data-bs-dismiss="modal"><i data-feather="x"></i> Cancel</button>
                  <button type="button" class="btn btn-danger sv-btn" id="mpConfirmSubmitBtn"><i data-feather="check"></i> Confirm</button>
                </div>
              </div>
            </div>
          </div>
          <div class="mt-3">
            <h6 class="fw-bold mb-2 d-flex align-items-center gap-2 sv-card-title"><span>Status</span></h6>
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
                  <div class="d-flex justify-content-between align-items-center border rounded px-2 py-2 mp-role-status-item sv-status-item" data-id="{{ $latest->id }}" data-status="{{ $latest->status }}" data-created="{{ $latest->created_at }}">
                    <div class="d-flex align-items-center gap-2">
                      <span class="badge rounded-pill bg-light text-dark sv-pill">{{ $label }}</span>
                      @if($deptName)
                        <span class="badge rounded-pill bg-secondary-subtle text-dark sv-pill">{{ $deptName }}</span>
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
            #mpCardRole .action-btn.approve { background: var(--sv-card-bg, #fff); border: none; color: #000; transition: all .2s ease-in-out; box-shadow: none; display: inline-flex; align-items: center; gap: .5rem; padding: .5rem 1rem; border-radius: .375rem; }
            #mpCardRole .action-btn.approve:hover, #mpCardRole .action-btn.approve:focus { background: linear-gradient(135deg, rgba(220,220,220,.88), rgba(240,240,240,.46)); backdrop-filter: blur(7px); -webkit-backdrop-filter: blur(7px); box-shadow: 0 4px 10px rgba(0,0,0,.12); color: #000; }
            #mpCardRole .action-btn.approve:hover svg, #mpCardRole .action-btn.approve:focus svg { stroke: #000; }
            #mpCardRole .mp-role-status-item.approved, #mpCardRole .mp-role-status-item.rejected { border-color:#e2e5e9; }
            #mpCardRole .mp-request-btn { width: auto; justify-content: center; margin-top: .5rem; margin-bottom: .5rem; }
          </style>
        </div>
      </div>
    </div>

    <!-- Separation line -->
    <hr class="sv-divider" />

    <!-- Danger Zone: Delete Account -->
    <div class="w-100 sv-mp-section">
      <div class="card shadow-sm sv-card" id="mpCardDanger">
        <div class="card-body">
          <h6 class="fw-bold mb-3 text-danger sv-card-title">Danger Zone</h6>
          <p class="text-muted">Deleting your account is irreversible. Proceed with caution.</p>
          <div class="d-flex justify-content-center">
            <button class="btn btn-danger sv-btn sv-btn-danger-text" id="mpDeleteBtn" data-bs-toggle="modal" data-bs-target="#mpDeleteConfirmModal"><i data-feather="trash"></i> Delete Account</button>
          </div>
          <!-- Delete Account Confirmation Modal (aligned with CDIO modal theme) -->
          <div class="modal sv-faculty-dept-modal" id="mpDeleteConfirmModal" tabindex="-1" aria-labelledby="mpDeleteConfirmLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-md">
              <div class="modal-content sv-modal-content">
                <style>
                  #mpDeleteConfirmModal { --sv-bg:#FAFAFA; --sv-bdr:#E3E3E3; --sv-danger:#CB3737; }
                  #mpDeleteConfirmModal .modal-header{ border-bottom:1px solid var(--sv-bdr); background: var(--sv-bg); }
                  #mpDeleteConfirmModal .modal-title{ color: var(--sv-danger); }
                  #mpDeleteConfirmModal .btn-danger{ background:#fff; border:none; color: var(--sv-danger); transition: all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
                  #mpDeleteConfirmModal .btn-danger:hover, #mpDeleteConfirmModal .btn-danger:focus{ background: linear-gradient(135deg, rgba(255,235,235,.88), rgba(255,245,245,.46)); box-shadow:0 4px 10px rgba(203,55,55,.15); color: var(--sv-danger); }
                  #mpDeleteConfirmModal .btn-light{ background:#fff; border:none; color:#000; transition: all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
                  #mpDeleteConfirmModal .btn-light:hover, #mpDeleteConfirmModal .btn-light:focus{ background: linear-gradient(135deg, rgba(225,225,225,.88), rgba(240,240,240,.46)); box-shadow:0 4px 10px rgba(0,0,0,.08); color:#000; }
                </style>
                <div class="modal-header sv-modal-header" style="padding:.85rem 1rem;">
                  <h5 class="modal-title d-flex align-items-center gap-2" id="mpDeleteConfirmLabel" style="font-weight:600; font-size:1rem;">
                    <i data-feather="trash-2"></i>
                    <span>Confirm Account Deletion</span>
                  </h5>
                </div>
                <div class="modal-body">
                  <div class="text-center mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 rounded-circle mb-3" style="width:64px; height:64px;">
                      <i data-feather="trash-2" class="text-danger" style="width:28px; height:28px;"></i>
                    </div>
                    <h6 class="fw-semibold mb-2">Delete Account</h6>
                    <p class="text-muted mb-0 small">This action permanently deletes your account and all associated data. This cannot be undone.</p>
                  </div>
                  <div class="alert alert-warning border-0 mb-0" style="background: rgba(255, 193, 7, 0.1);">
                    <div class="d-flex align-items-start gap-3">
                      <i data-feather="alert-triangle" class="text-warning flex-shrink-0 mt-1" style="width:18px; height:18px;"></i>
                      <div class="small">
                        <div class="fw-medium text-dark">You will be signed out immediately</div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-light sv-btn" data-bs-dismiss="modal"><i data-feather="x"></i> Cancel</button>
                  <form id="mpDeleteAccountForm" method="POST" class="d-inline" action="#">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"><i data-feather="trash-2"></i> Delete</button>
                  </form>
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

@push('styles')
<style>
  .sv-mp-container { max-width: 880px; }
  .sv-mp-stack { gap: 1.5rem; }
  .sv-mp-section { max-width: 720px; }
  .sv-card { border-radius: 14px; border: 1px solid var(--sv-bdr,#E3E3E3); }
  .sv-card-title { letter-spacing: .01em; }
  .sv-form-flat { border: none; box-shadow: none; }
  .sv-input { border-radius:12px; border:1px solid var(--sv-bdr,#E3E3E3); background:#fff; }
  .sv-input:focus { border-color: var(--sv-acct,#EE6F57); box-shadow:0 0 0 3px rgba(238,111,87,.16); }
  .sv-check { width: 1rem; height: 1rem; }
  .sv-btn { background:#fff; border:none; color:#000; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
  .sv-btn:hover, .sv-btn:focus { background:linear-gradient(135deg, rgba(235,235,235,.88), rgba(250,250,250,.46)); box-shadow:0 4px 10px rgba(0,0,0,.10); color:#000; }
  /* Delete button: red text/icon and red hover effect on white background */
  #mpDeleteBtn { color: var(--sv-danger, #CB3737); }
  #mpDeleteBtn i { color: var(--sv-danger, #CB3737); }
  #mpDeleteBtn:hover, #mpDeleteBtn:focus { color: var(--sv-danger, #CB3737); background: #fff; box-shadow:0 4px 10px rgba(203,55,55,.18); }
  #mpDeleteBtn:hover i, #mpDeleteBtn:focus i { color: var(--sv-danger, #CB3737); }
  .sv-divider { width: 100%; max-width: 720px; border: none; height: 2px; background-color: #e2e5e9; margin: 0; }
  .sv-modal-content { border-radius:16px; border:1px solid #E3E3E3; background:#fff; box-shadow:0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06); overflow:hidden; }
  .sv-modal-header { padding:.85rem 1rem; border-bottom:1px solid #E3E3E3; background:#fff; }
  .sv-status-item { border-color:#e2e5e9; }
  .sv-pill { font-weight:500; }
  .sv-alert-rounded { border-radius:12px; }
  /* Dropdown accent */
  #mpCardRole .form-select.form-select-sm.sv-dept:hover { border-color: #E8A298; }
  #mpCardRole .form-select.form-select-sm.sv-dept:focus { border-color: var(--sv-acct,#EE6F57); box-shadow: 0 0 0 3px rgba(238,111,87,.16); }
</style>
@endpush
