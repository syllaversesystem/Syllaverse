{{-- 
-------------------------------------------------------------------------------
* File: resources/views/superadmin/pending-accounts/index.blade.php
* Description: Super Admin Pending Accounts module (extracted from Approvals tab)
-------------------------------------------------------------------------------
📜 Log:
[2025-12-03] Initial creation – module view reusing Approvals content.
-------------------------------------------------------------------------------
--}}

@extends('layouts.superadmin')

@section('title', 'Pending Accounts • Super Admin • Syllaverse')
@section('page-title', 'Pending Accounts')

@section('content')
  <div class="manage-account">
    {{-- Inlined Approvals content migrated into Pending Accounts module --}}
@php
  use App\Models\ChairRequest;

  // Guard + build groups
  $pendingAdmins        = ($pendingAdmins ?? collect());
  $pendingFaculty       = ($pendingFaculty ?? collect());
  $pendingChairRequests = ($pendingChairRequests ?? collect());
  $groups               = collect();

  // Seed with pending admin signups
  foreach ($pendingAdmins as $u) {
    $groups[$u->id] = [
      'user'          => $u,
      'signupPending' => true,
      'requests'      => collect(),
      'latest_at'     => $u->created_at,
    ];
  }

  // Seed with pending faculty signups
  foreach ($pendingFaculty as $u) {
    $groups[$u->id] = [
      'user'          => $u,
      'signupPending' => true,
      'requests'      => collect(),
      'latest_at'     => $u->created_at,
    ];
  }

  // Merge pending chair requests (even if signup already approved)
  // Include all role requests including faculty for centralized superadmin management
  foreach ($pendingChairRequests as $r) {
    $uid   = $r->user_id;
    // Dynamic label rules per new mapping
    $deptName = strtolower(trim(optional($r->department)->name ?? ''));
    if ($r->requested_role === ChairRequest::ROLE_DEPT_HEAD || $r->requested_role === ChairRequest::ROLE_DEAN) {
      // Department Head (Dean/Head/Principal) display rules
      if ($deptName !== '' && (
            str_contains($deptName, 'lab school') ||
            str_contains($deptName, 'laboratory school')
          )) {
        $label = 'Principal';
      } elseif ($deptName !== '' && str_contains($deptName, 'general education')) {
        $label = 'Head';
      } else {
        $label = 'Dean';
      }
    } else {
      $label = match ($r->requested_role) {
        ChairRequest::ROLE_CHAIR, ChairRequest::ROLE_DEPT => (optional($r->department)->programs()->count() ?? 0) >= 2 ? 'Dept Chair' : 'Program Chair',
        ChairRequest::ROLE_PROG => 'Program Chair',
        ChairRequest::ROLE_VCAA => 'VCAA',
        ChairRequest::ROLE_ASSOC_VCAA => 'Associate VCAA',
        ChairRequest::ROLE_ASSOC_DEAN => 'Associate Dean',
        ChairRequest::ROLE_FACULTY => 'Faculty',
        default => $r->requested_role,
      };
    }
    $dept  = optional($r->department)->name;
    $prog  = optional($r->program)->name;
    
    // Show "Institution-wide" for VCAA and Associate VCAA roles
    if (!$dept && in_array($r->requested_role, [ChairRequest::ROLE_VCAA, ChairRequest::ROLE_ASSOC_VCAA])) {
      $dept = 'Institution-wide';
    }

    if (!isset($groups[$uid])) {
      $groups[$uid] = [
        'user'          => $r->user,
        'signupPending' => false,
        'requests'      => collect(),
        'latest_at'     => $r->created_at,
      ];
    }

    $entry = $groups[$uid];
    $entry['requests'] = $entry['requests']->push([
      'id'           => $r->id,
      'label'        => $label,
      'dept'         => $dept,
      'prog'         => $prog,
      'submitted_at' => $r->created_at,
    ]);
    if ($r->created_at > $entry['latest_at']) $entry['latest_at'] = $r->created_at;
    $groups[$uid] = $entry;
  }

  // Newest first
  $groups = $groups->sortByDesc('latest_at');
@endphp

  <div class="table-wrapper position-relative">
    <div class="table-responsive">
      <table class="table superadmin-manage-account-table mb-0" id="svMergedApprovalsTable">
        <thead class="superadmin-manage-account-table-header">
          <tr>
            <th><i data-feather="user"></i> Name</th>
            <th><i data-feather="mail"></i> Email</th>
            <th><i data-feather="tag"></i> Type</th>
            <th><i data-feather="briefcase"></i> Department</th>
            <th class="text-end"><i data-feather="more-vertical"></i></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($groups as $uid => $g)
            @php
              $user       = $g['user'];
              $reqCount   = $g['requests']->count();
              $collapseId = "sv-req-$uid";
            @endphp

            {{-- Master Row --}}
            <tr>
              <td>{{ $user->name ?? '—' }}</td>
              <td class="text-muted">{{ $user->email ?? '—' }}</td>

              <td>
                <div class="d-flex align-items-center gap-2">
                  @if ($g['signupPending'] && $reqCount === 0)
                    <span class="sv-pill is-muted sv-pill--sm">Signup</span>
                  @endif

                  @if ($reqCount === 1)
                    @php $req = $g['requests']->first(); @endphp
                    <span class="sv-pill is-primary sv-pill--sm">{{ $req['label'] }}</span>
                    @if ($g['signupPending'])
                      <span class="sv-pill is-muted sv-pill--sm">Signup</span>
                    @endif
                  @elseif ($reqCount > 1)
                    @php $primaryReq = $g['requests']->first(); @endphp
                    <span class="sv-pill is-primary sv-pill--sm">{{ $primaryReq['label'] }}</span>
                    <span class="text-muted small">+{{ $reqCount - 1 }}</span>
                    @if ($g['signupPending'])
                      <span class="sv-pill is-muted sv-pill--sm">Signup</span>
                    @endif
                  @endif
                </div>
              </td>

              @if ($reqCount === 1)
                <td><small class="text-muted">{{ $req['dept'] ?: '—' }}</small></td>
              @elseif ($reqCount > 1)
                <td>—</td>
              @elseif ($reqCount > 2)
                <td>—</td>
              @else
                <td>—</td>
              @endif

              <td class="text-end">
                @if ($reqCount === 0)
                  @if ($g['signupPending'])
                    @if ($user->role === 'admin')
                      <form method="POST" action="{{ route('superadmin.approve.admin', $user->id) }}" class="d-inline">@csrf
                        <button type="submit" class="action-btn approve" aria-label="Approve signup" title="Approve">
                          <i data-feather="check"></i>
                        </button>
                      </form>
                      <form method="POST" action="{{ route('superadmin.reject.admin', $user->id) }}" class="d-inline">@csrf
                        <button type="submit" class="action-btn reject" aria-label="Reject signup" title="Reject">
                          <i data-feather="x"></i>
                        </button>
                      </form>
                    @else
                      <form method="POST" action="{{ route('superadmin.approve.faculty', $user->id) }}" class="d-inline">@csrf
                        <button type="submit" class="action-btn approve" aria-label="Approve faculty signup" title="Approve">
                          <i data-feather="check"></i>
                        </button>
                      </form>
                      <form method="POST" action="{{ route('superadmin.reject.faculty', $user->id) }}" class="d-inline">@csrf
                        <button type="submit" class="action-btn reject" aria-label="Reject faculty signup" title="Reject">
                          <i data-feather="x"></i>
                        </button>
                      </form>
                    @endif
                  @endif
                @elseif ($reqCount === 1)
                  <form method="POST" action="{{ route('superadmin.chair-requests.approve', $req['id']) }}" class="d-inline">@csrf
                    <button type="submit" class="action-btn approve" aria-label="Approve {{ $req['label'] }}" title="Approve {{ $req['label'] }}">
                      <i data-feather="check"></i>
                    </button>
                  </form>
                  <form method="POST" action="{{ route('superadmin.chair-requests.reject', $req['id']) }}" class="d-inline">@csrf
                    <button type="submit" class="action-btn reject" aria-label="Reject {{ $req['label'] }}" title="Reject {{ $req['label'] }}">
                      <i data-feather="x"></i>
                    </button>
                  </form>
                @else
                  <button
                    class="action-btn edit sv-row-toggle"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#{{ $collapseId }}"
                    aria-expanded="false"
                    aria-controls="{{ $collapseId }}"
                    title="Show chair requests"
                    aria-label="Show chair requests">
                    <i data-feather="chevron-down"></i>
                  </button>
                @endif
              </td>
            </tr>

            {{-- Detail Row (only when multiple chair requests) --}}
            @if ($reqCount > 1)
              <tr class="sv-detail-row">
                <td class="sv-details-cell p-0" colspan="7">
                  <div id="{{ $collapseId }}" class="collapse sv-details mt-2">
                    <div class="sv-request-list p-2">
                      @foreach ($g['requests'] as $r)
                        <div class="sv-request-item py-2 border-top mb-2">
                          <div class="row align-items-center gx-3">
                            <div class="col-auto" style="min-width: 240px;">
                              <div class="d-flex align-items-center gap-2">
                                <span class="sv-pill is-primary sv-pill--sm">{{ $r['label'] }}</span>
                                @if($r['dept']) <small class="text-muted">{{ $r['dept'] }}</small> @endif
                                @if($r['prog']) <small class="text-muted">{{ $r['prog'] }}</small> @endif
                              </div>
                            </div>
                            <div class="col text-muted">
                              <small>Submitted {{ optional($r['submitted_at'])->diffForHumans() }}</small>
                            </div>
                            <div class="col-auto">
                              <div class="d-flex gap-2">
                                <form method="POST" action="{{ route('superadmin.chair-requests.approve', $r['id']) }}" class="d-inline">@csrf
                                  <button type="submit" class="action-btn approve" aria-label="Approve {{ $r['label'] }}" title="Approve {{ $r['label'] }}">
                                    <i data-feather="check"></i>
                                  </button>
                                </form>
                                <form method="POST" action="{{ route('superadmin.chair-requests.reject', $r['id']) }}" class="d-inline">@csrf
                                  <button type="submit" class="action-btn reject" aria-label="Reject {{ $r['label'] }}" title="Reject {{ $r['label'] }}">
                                    <i data-feather="x"></i>
                                  </button>
                                </form>
                              </div>
                            </div>
                          </div>
                        </div>
                      @endforeach
                      
                      {{-- Approve All & Reject All Buttons --}}
                      <div class="sv-approve-all-section mt-3 pt-3 border-top">
                        <div class="d-flex justify-content-center gap-3">
                          <form method="POST" action="{{ route('superadmin.chair-requests.approve-all', $user->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success d-flex align-items-center gap-2" 
                                    title="Approve all role requests">
                              <i data-feather="check"></i>
                              <span>Approve All {{ $reqCount }} Requests</span>
                            </button>
                          </form>
                          
                          <form method="POST" action="{{ route('superadmin.chair-requests.reject-all', $user->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm sv-reject-all d-flex align-items-center gap-2" 
                                    title="Reject all role requests">
                              <i data-feather="x"></i>
                              <span>Reject All {{ $reqCount }} Requests</span>
                            </button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            @endif

          @empty
            {{-- No admin groups --}}
          @endforelse

          {{-- ░░░ START: Empty State ░░░ --}}
          @if($groups->isEmpty())
            <tr class="superadmin-manage-account-empty-row">
              <td colspan="5">
                <div class="sv-empty">
                  <h6>No approvals pending</h6>
                  <p>New admin signups and administrative role requests will appear here.</p>
                </div>
              </td>
            </tr>
          @endif
          {{-- ░░░ END: Empty State ░░░ --}}
        </tbody>
      </table>
    </div>
  </div>
  </div>
@endsection
