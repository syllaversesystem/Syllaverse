{{-- 
-------------------------------------------------------------------------------
* File: resources/views/superadmin/manage-accounts/tabs/admins-approvals.blade.php
* Description: Merged approvals table (New Admin Signups + Chair Role Requests) – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-08-09] Initial creation – single table showing both signup approvals and chair-role request approvals.
[2025-08-09] Fix: wrapper id set to "admins-approvals" to match tab button target.
-------------------------------------------------------------------------------
--}}

@php
  use App\Models\ChairRequest;

  // Build a unified collection for one-table rendering
  $approvals = collect();

  // 1) Pending Admin Signups (role=admin, status=pending)
  foreach ($pendingAdmins ?? [] as $u) {
    $approvals->push([
      'type'         => 'signup',
      'id'           => $u->id,
      'name'         => $u->name,
      'email'        => $u->email,
      'role_label'   => '—',
      'dept_label'   => '—',
      'prog_label'   => '—',
      'submitted_at' => $u->created_at,
    ]);
  }

  // 2) Pending Chair Requests (status=pending)
  foreach (($pendingChairRequests ?? []) as $r) {
    $isDept    = $r->requested_role === ChairRequest::ROLE_DEPT;
    $roleLabel = $isDept ? 'Dept Chair' : 'Program Chair';
    $deptName  = $r->department->name ?? '—';
    $progName  = $r->program->name ?? '—';

    $approvals->push([
      'type'         => 'chair',
      'id'           => $r->id,
      'name'         => $r->user->name ?? '—',
      'email'        => $r->user->email ?? '—',
      'role_label'   => $roleLabel,
      'dept_label'   => $deptName,
      'prog_label'   => $isDept ? '—' : $progName,
      'submitted_at' => $r->created_at,
    ]);
  }

  // Newest first
  $approvals = $approvals->sortByDesc('submitted_at');
@endphp

{{-- ✅ must match data-bs-target="#admins-approvals" --}}
<div class="tab-pane fade show active" id="admins-approvals" role="tabpanel">
  <div class="card border-0 shadow-sm p-4">
    <div class="d-flex justify-content-between flex-wrap gap-3 mb-3">
      <div>
        <h5 class="mb-0">Approvals</h5>
        <div class="text-muted small">New admin signups and chair role requests in one place.</div>
      </div>
      <div class="input-group" style="max-width:320px;">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="search" class="form-control" placeholder="Search…" oninput="svFilterMergedApprovals(this.value)">
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle" id="svMergedApprovalsTable">
        <thead class="table-light">
          <tr>
            <th style="width:22%;">Name</th>
            <th style="width:24%;">Email</th>
            <th style="width:12%;">Type</th>
            <th style="width:14%;">Role</th>
            <th style="width:14%;">Department</th>
            <th style="width:14%;">Program</th>
            <th style="width:140px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($approvals as $row)
            <tr>
              <td class="fw-semibold">{{ $row['name'] }}</td>
              <td class="text-muted">{{ $row['email'] }}</td>
              <td>
                <span class="badge {{ $row['type'] === 'signup' ? 'bg-secondary' : 'bg-primary' }}">
                  {{ $row['type'] === 'signup' ? 'Signup' : 'Chair Request' }}
                </span>
              </td>
              <td>{{ $row['role_label'] }}</td>
              <td>{{ $row['dept_label'] }}</td>
              <td>{{ $row['prog_label'] }}</td>
              <td>
                @if ($row['type'] === 'signup')
                  <form method="POST" action="{{ route('superadmin.approve.admin', $row['id']) }}" class="d-inline">@csrf
                    <button class="btn btn-success btn-sm">Approve</button>
                  </form>
                  <form method="POST" action="{{ route('superadmin.reject.admin', $row['id']) }}" class="d-inline ms-1">@csrf
                    <button class="btn btn-outline-danger btn-sm">Reject</button>
                  </form>
                @else
                  <form method="POST" action="{{ route('superadmin.chair-requests.approve', $row['id']) }}" class="d-inline">@csrf
                    <button class="btn btn-success btn-sm">Approve</button>
                  </form>
                  <form method="POST" action="{{ route('superadmin.chair-requests.reject',  $row['id']) }}" class="d-inline ms-1">@csrf
                    <button class="btn btn-outline-danger btn-sm">Reject</button>
                  </form>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-4">Nothing pending. 🎉</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@push('scripts')
<script>
  function svFilterMergedApprovals(q) {
    const needle = (q || '').toLowerCase().trim();
    document.querySelectorAll('#svMergedApprovalsTable tbody tr').forEach(tr => {
      tr.style.display = tr.innerText.toLowerCase().includes(needle) ? '' : 'none';
    });
  }
</script>
@endpush
