{{-- 
-------------------------------------------------------------------------------
* File: resources/views/superadmin/manage-accounts/tabs/unified-accounts.blade.php
* Description: Unified accounts table for all user management (Admin and Faculty) – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-10-19] Created unified table combining admin and faculty account management
-------------------------------------------------------------------------------
--}}

{{-- ░░░ START: Unified Accounts Table ░░░ --}}
<div class="table-wrapper position-relative">
    <div class="table-responsive">
        <table class="table superadmin-manage-account-table table-hover">
            <thead class="superadmin-manage-account-table-header table-light">
                <tr>
                    <th><i data-feather="user"></i> Name</th>
                    <th><i data-feather="shield"></i> Role</th>
                    <th><i data-feather="mail"></i> Email</th>
                    <th><i data-feather="briefcase"></i> Details</th>
                    <th><i data-feather="alert-circle"></i> Status</th>
                    <th class="text-end"><i data-feather="more-vertical"></i> Actions</th>
                </tr>
            </thead>
            <tbody>
                {{-- Pending Admins --}}
                @if(isset($pendingAdmins) && $pendingAdmins->isNotEmpty())
                    @foreach ($pendingAdmins as $user)
                        <tr>
                            <td>
                                <strong>{{ $user->name }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-primary">Admin</span>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td class="text-muted small">
                                {{ $user->designation ?? 'N/A' }}<br>
                                <small>{{ $user->employee_code ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-warning">Pending</span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="action-btn approve" 
                                        onclick="approveAdmin({{ $user->id }})" 
                                        title="Approve Admin">
                                    <i data-feather="check"></i>
                                </button>
                                <button type="button" class="action-btn reject" 
                                        onclick="rejectAdmin({{ $user->id }})" 
                                        title="Reject Admin">
                                    <i data-feather="x"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @endif

                {{-- Pending Chair Requests (Admin Leadership Roles) --}}
                @if(isset($pendingChairRequests) && $pendingChairRequests->isNotEmpty())
                    @foreach ($pendingChairRequests as $request)
                        <tr>
                            <td>
                                <strong>{{ $request->user->name }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    {{ ucwords(str_replace('_', ' ', $request->requested_role)) }}
                                </span>
                            </td>
                            <td>{{ $request->user->email }}</td>
                            <td class="text-muted small">
                                @if($request->department)
                                    Dept: {{ $request->department->name }}
                                @else
                                    Institution-wide
                                @endif
                                <br>
                                <small>Requested: {{ $request->created_at->format('M j, Y') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-warning">Chair Request</span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="action-btn approve" 
                                        onclick="approveChairRequest({{ $request->id }})" 
                                        title="Approve Chair Request">
                                    <i data-feather="check"></i>
                                </button>
                                <button type="button" class="action-btn reject" 
                                        onclick="rejectChairRequest({{ $request->id }})" 
                                        title="Reject Chair Request">
                                    <i data-feather="x"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @endif

                {{-- Faculty Users (All Status) --}}
                @if(isset($faculty) && $faculty->isNotEmpty())
                    @foreach ($faculty as $user)
                        <tr>
                            <td>
                                <strong>{{ $user->name }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-success">Faculty</span>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td class="text-muted small">
                                {{ $user->designation ?? 'N/A' }}<br>
                                <small>{{ $user->employee_code ?? 'N/A' }}</small>
                            </td>
                            <td>
                                @switch($user->status)
                                    @case('active')
                                        <span class="badge bg-success">Active</span>
                                        @break
                                    @case('pending')
                                        <span class="badge bg-warning">Pending</span>
                                        @break
                                    @case('rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ ucfirst($user->status) }}</span>
                                @endswitch
                            </td>
                            <td class="text-end">
                                @if($user->status === 'pending')
                                    <button type="button" class="action-btn approve" 
                                            onclick="approveFaculty({{ $user->id }})" 
                                            title="Approve Faculty">
                                        <i data-feather="check"></i>
                                    </button>
                                    <button type="button" class="action-btn reject" 
                                            onclick="rejectFaculty({{ $user->id }})" 
                                            title="Reject Faculty">
                                        <i data-feather="x"></i>
                                    </button>
                                @elseif($user->status === 'active')
                                    <button type="button" class="action-btn reject" 
                                            onclick="suspendFaculty({{ $user->id }})" 
                                            title="Suspend Faculty">
                                        <i data-feather="user-x"></i>
                                    </button>
                                @elseif($user->status === 'rejected')
                                    <button type="button" class="action-btn approve" 
                                            onclick="reactivateFaculty({{ $user->id }})" 
                                            title="Reactivate Faculty">
                                        <i data-feather="user-check"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endif

                {{-- Approved Admins --}}
                @if(isset($approvedAdmins) && $approvedAdmins->isNotEmpty())
                    @foreach ($approvedAdmins as $user)
                        <tr>
                            <td>
                                <strong>{{ $user->name }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-primary">Admin</span>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td class="text-muted small">
                                {{ $user->designation ?? 'N/A' }}<br>
                                <small>{{ $user->employee_code ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-success">Active</span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="action-btn reject" 
                                        onclick="suspendAdmin({{ $user->id }})" 
                                        title="Suspend Admin">
                                    <i data-feather="user-x"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @endif

                {{-- Empty State --}}
                @if(
                    (!isset($pendingAdmins) || $pendingAdmins->isEmpty()) && 
                    (!isset($pendingChairRequests) || $pendingChairRequests->isEmpty()) && 
                    (!isset($faculty) || $faculty->isEmpty()) && 
                    (!isset($approvedAdmins) || $approvedAdmins->isEmpty())
                )
                    <tr class="superadmin-manage-account-empty-row">
                        <td colspan="6">
                            <div class="empty-table">
                                <h6>No accounts found</h6>
                                <p>User accounts and requests will appear here when they register.</p>
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

{{-- ░░░ START: JavaScript for Actions ░░░ --}}
<script>
// Admin approval functions
function approveAdmin(id) {
    if (confirm('Approve this admin account?')) {
        fetch(`/superadmin/manage-accounts/admin/${id}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  location.reload();
              } else {
                  alert('Failed to approve admin');
              }
          });
    }
}

function rejectAdmin(id) {
    if (confirm('Reject this admin account?')) {
        fetch(`/superadmin/manage-accounts/admin/${id}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  location.reload();
              } else {
                  alert('Failed to reject admin');
              }
          });
    }
}

// Chair request functions
function approveChairRequest(id) {
    if (confirm('Approve this chair request?')) {
        fetch(`/superadmin/chair-requests/${id}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  location.reload();
              } else {
                  alert('Failed to approve chair request');
              }
          });
    }
}

function rejectChairRequest(id) {
    if (confirm('Reject this chair request?')) {
        fetch(`/superadmin/chair-requests/${id}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  location.reload();
              } else {
                  alert('Failed to reject chair request');
              }
          });
    }
}

// Faculty management functions
function approveFaculty(id) {
    if (confirm('Approve this faculty account?')) {
        fetch(`/superadmin/manage-accounts/faculty/${id}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  location.reload();
              } else {
                  alert('Failed to approve faculty');
              }
          });
    }
}

function rejectFaculty(id) {
    if (confirm('Reject this faculty account?')) {
        fetch(`/superadmin/manage-accounts/faculty/${id}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  location.reload();
              } else {
                  alert('Failed to reject faculty');
              }
          });
    }
}

function suspendFaculty(id) {
    if (confirm('Suspend this faculty account?')) {
        fetch(`/superadmin/manage-accounts/faculty/${id}/suspend`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  location.reload();
              } else {
                  alert('Failed to suspend faculty');
              }
          });
    }
}

function reactivateFaculty(id) {
    if (confirm('Reactivate this faculty account?')) {
        fetch(`/superadmin/manage-accounts/faculty/${id}/reactivate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  location.reload();
              } else {
                  alert('Failed to reactivate faculty');
              }
          });
    }
}

function suspendAdmin(id) {
    if (confirm('Suspend this admin account?')) {
        fetch(`/superadmin/manage-accounts/admin/${id}/suspend`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  location.reload();
              } else {
                  alert('Failed to suspend admin');
              }
          });
    }
}
</script>
{{-- ░░░ END: JavaScript for Actions ░░░ --}}