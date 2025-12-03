{{-- 
-------------------------------------------------------------------------------
* File: resources/views/superadmin/approved-accounts/index.blade.php
* Description: Super Admin Approved Accounts module (extracted from Approved tab)
-------------------------------------------------------------------------------
📜 Log:
[2025-12-03] Initial creation – module view reusing Approved tab content.
-------------------------------------------------------------------------------
--}}

@extends('layouts.superadmin')

@section('title', 'Approved Accounts • Super Admin • Syllaverse')
@section('page-title', 'Approved Accounts')

@section('content')
  <div class="manage-account">
    {{-- Inlined Approved tab content migrated into Approved Accounts module --}}
@php
  // Load appointments for both admin and faculty users
  if (isset($approvedAdmins) && method_exists($approvedAdmins, 'load')) {
    $approvedAdmins->loadMissing('appointments');
  }
  if (isset($approvedFaculty) && method_exists($approvedFaculty, 'load')) {
    $approvedFaculty->loadMissing('appointments');
  }
  
  // Faculty-only display
  $allApprovedUsers = collect();
  if (isset($approvedFaculty)) {
    $allApprovedUsers = $allApprovedUsers->concat($approvedFaculty);
  }
  
  $deptById = collect($departments ?? [])->keyBy('id');
  $progById = collect($programs ?? [])->keyBy('id');
@endphp

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
                  // Derive primary appointment (prefer first active) and compute label
                  $primaryAppt = $activeAppointments->count() ? $activeAppointments->first() : null;
                  $primaryRoleRaw = $primaryAppt?->role ?? $user->role;
                  $deptName = null;
                  $programCount = null;
                  if ($primaryAppt && $primaryAppt->scope_id) {
                    $deptName = optional($deptById->get($primaryAppt->scope_id))->name;
                    $programCount = isset($primaryAppt->scope_id)
                      ? (isset($programs) ? collect($programs)->where('department_id', $primaryAppt->scope_id)->count() : null)
                      : null;
                  }

                  $dn = strtolower(trim((string)($deptName ?? '')));
                  $isLabSchool = $dn === 'labschool' || $dn === 'lab school' || $dn === 'laboratory school';
                  $isGenEd = $dn === 'general education' || $dn === 'gened' || $dn === 'gen ed';

                  $primaryRole = $primaryRoleRaw;
                  if ($primaryRoleRaw === 'DEPT_HEAD') {
                    if ($isLabSchool) {
                      $primaryRole = 'Principal';
                    } elseif ($isGenEd) {
                      $primaryRole = 'Head';
                    } else {
                      $primaryRole = 'Dean';
                    }
                  } elseif ($primaryRoleRaw === 'CHAIR') {
                    if (is_numeric($programCount)) {
                      $primaryRole = ($programCount >= 2) ? 'Dept Chair' : 'Prog Chair';
                    } else {
                      $primaryRole = 'Dept Chair';
                    }
                  } elseif ($primaryRoleRaw === 'ASSOC_DEAN') {
                    $primaryRole = 'Associate Dean';
                  } elseif ($primaryRoleRaw === 'FACULTY') {
                    $primaryRole = 'Faculty';
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
                    $first = $activeAppointments->first();
                    // Attempt to resolve department name from appointment scope
                    if (isset($first->scope_id) && $first->scope_id) {
                      $departmentName = optional($deptById->get($first->scope_id))->name ?? '—';
                    }
                  }
                @endphp
                <span class="text-muted">{{ $departmentName }}</span>
              </td>

              <td class="text-end">
                <div class="d-flex justify-content-end align-items-center gap-2">
                  @if($activeAppointments->count() > 1)
                    <button
                      class="action-btn edit sv-row-toggle"
                      type="button"
                      data-bs-toggle="collapse"
                      data-bs-target="#sv-appt-{{ $user->id }}"
                      aria-expanded="false"
                      aria-controls="sv-appt-{{ $user->id }}"
                      title="Show appointments"
                      aria-label="Show appointments">
                      <i data-feather="chevron-down"></i>
                    </button>
                  @endif

                  {{-- Manage button (faculty only) --}}
                  @if($user->role === 'faculty')
                    <button class="action-btn edit" data-bs-toggle="modal" data-bs-target="#manageFaculty-{{ $user->id }}" title="Manage faculty" aria-label="Manage faculty">
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
                  <div id="sv-appt-{{ $user->id }}" class="collapse sv-details mt-2">
                    <div class="p-2">
                      @foreach($activeAppointments as $appt)
                        <div class="py-2 border-top mb-2">
                          <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                              @php
                                $deptName2 = $appt->scope_id ? optional($deptById->get($appt->scope_id))->name : null;
                                $dn2 = strtolower(trim((string)($deptName2 ?? '')));
                                $isLabSchool2 = $dn2 === 'labschool' || $dn2 === 'lab school' || $dn2 === 'laboratory school';
                                $isGenEd2 = $dn2 === 'general education' || $dn2 === 'gened' || $dn2 === 'gen ed';
                                $progCount2 = isset($programs) && $appt->scope_id ? collect($programs)->where('department_id', $appt->scope_id)->count() : null;
                                $roleLabel2 = $appt->role;
                                if ($appt->role === 'DEPT_HEAD') {
                                  if ($isLabSchool2) {
                                    $roleLabel2 = 'Principal';
                                  } elseif ($isGenEd2) {
                                    $roleLabel2 = 'Head';
                                  } else {
                                    $roleLabel2 = 'Dean';
                                  }
                                } elseif ($appt->role === 'CHAIR') {
                                  if (is_numeric($progCount2)) {
                                    $roleLabel2 = ($progCount2 >= 2) ? 'Dept Chair' : 'Prog Chair';
                                  } else {
                                    $roleLabel2 = 'Dept Chair';
                                  }
                                } elseif ($appt->role === 'ASSOC_DEAN') {
                                  $roleLabel2 = 'Associate Dean';
                                } elseif ($appt->role === 'FACULTY') {
                                  $roleLabel2 = 'Faculty';
                                }
                              @endphp
                              <span class="sv-pill is-primary sv-pill--sm">{{ $roleLabel2 }}</span>
                              @if($appt->scope_id)
                                <small class="text-muted">{{ optional($deptById->get($appt->scope_id))->name }}</small>
                              @endif
                            </div>
                            <div class="d-flex gap-2">
                              <form method="POST" action="{{ route('superadmin.appointments.end', $appt->id) }}" class="d-inline">@csrf
                                <button type="submit" class="action-btn reject" title="End appointment" aria-label="End appointment">
                                  <i data-feather="slash"></i>
                                </button>
                              </form>
                              <form method="POST" action="{{ route('superadmin.appointments.destroy', $appt->id) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn reject" title="Delete appointment" aria-label="Delete appointment">
                                  <i data-feather="trash"></i>
                                </button>
                              </form>
                            </div>
                          </div>
                        </div>
                      @endforeach
                    </div>
                  </div>
                </td>
              </tr>
            @endif

            @if($user->role === 'faculty')
              @push('modals')
                @include('superadmin.approved-accounts.modals.manage-faculty', [
                  'faculty' => $user,
                  'departments' => $departments ?? [],
                  'programs' => $programs ?? []
                ])
              @endpush
            @endif

          @empty
            {{-- No approved users --}}
          @endforelse

          {{-- Empty State --}}
          @if($allApprovedUsers->isEmpty())
            <tr class="superadmin-manage-account-empty-row">
              <td colspan="4">
                <div class="sv-empty">
                  <h6>No approved accounts</h6>
                  <p>Once accounts are approved, they will appear here.</p>
                </div>
              </td>
            </tr>
          @endif
        </tbody>
      </table>
    </div>
  </div>
  </div>
@endsection
