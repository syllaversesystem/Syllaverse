{{-- 
-------------------------------------------------------------------------------
* File: resources/views/superadmin/manage-accounts/tabs/chair-requests.blade.php
* Description: Superadmin review panel for chair-role requests (approve/reject with overrides) – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-08-08] Initial creation – pending/approved/rejected sections, approve/reject forms with optional overrides.
-------------------------------------------------------------------------------
--}}

{{-- ░░░ START: Pending Requests ░░░ --}}
<div class="card mb-4">
  <div class="card-header bg-light">
    <strong>Pending Chair Requests</strong>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-striped align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Admin</th>
            <th>Requested Role</th>
            <th>Requested Scope</th>
            <th>Submitted</th>
            <th style="width: 380px;">Decision</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($chairRequestsPending as $req)
            <tr>
              <td>{{ $req->id }}</td>
              <td>
                <div class="fw-semibold">{{ $req->user->name }}</div>
                <div class="text-muted" style="font-size:12px;">{{ $req->user->email }}</div>
              </td>
              <td>
                @if($req->requested_role === \App\Models\ChairRequest::ROLE_DEPT)
                  <span class="badge bg-secondary">Department Chair</span>
                @else
                  <span class="badge bg-primary">Program Chair</span>
                @endif
              </td>
              <td>
                <div><strong>Department:</strong> {{ $req->department->name ?? '—' }}</div>
                <div><strong>Program:</strong> {{ $req->program->name ?? '—' }}</div>
              </td>
              <td>{{ $req->created_at->format('Y-m-d H:i') }}</td>
              <td>
                {{-- Approve (with optional overrides) --}}
                <form class="row g-2" method="POST" action="{{ route('superadmin.chair-requests.approve', $req->id) }}">
                  @csrf
                  <div class="col-md-4">
                    <select name="department_id" class="form-select form-select-sm">
                      <option value="">— Dept (keep) —</option>
                      @foreach(($departments ?? []) as $dept)
                        <option value="{{ $dept->id }}"
                          {{ (isset($req->department_id) && (int) $req->department_id === (int) $dept->id) ? 'selected' : '' }}>
                          {{ $dept->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-4">
                    <select name="program_id" class="form-select form-select-sm">
                      <option value="">— Program (keep/none) —</option>
                      @foreach(($programs ?? []) as $prog)
                        <option value="{{ $prog->id }}"
                                data-dept="{{ $prog->department_id }}"
                                {{ (isset($req->program_id) && (int) $req->program_id === (int) $prog->id) ? 'selected' : '' }}>
                          {{ $prog->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-4">
                    <input type="datetime-local" name="start_at" class="form-control form-control-sm" placeholder="Start now (default)">
                  </div>

                  <div class="col-12">
                    <input type="text" name="notes" class="form-control form-control-sm" placeholder="Notes (optional)">
                  </div>

                  <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                    {{-- Reject --}}
                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="collapse" data-bs-target="#reject-{{ $req->id }}">
                      Reject
                    </button>
                  </div>
                </form>

                {{-- Reject form (collapsible) --}}
                <div class="collapse mt-2" id="reject-{{ $req->id }}">
                  <form method="POST" action="{{ route('superadmin.chair-requests.reject', $req->id) }}" class="d-flex gap-2">
                    @csrf
                    <input type="text" name="notes" class="form-control form-control-sm" placeholder="Reason (optional)">
                    <button type="submit" class="btn btn-danger btn-sm">Confirm Reject</button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted py-4">No pending requests.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
{{-- ░░░ END: Pending Requests ░░░ --}}


{{-- ░░░ START: Approved & Rejected (history) ░░░ --}}
<div class="row g-4">
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header bg-light"><strong>Recently Approved</strong></div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Admin</th>
                <th>Role</th>
                <th>Scope</th>
                <th>Decided By</th>
                <th>When</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($chairRequestsApproved as $req)
                <tr>
                  <td>{{ $req->id }}</td>
                  <td>
                    <div class="fw-semibold">{{ $req->user->name }}</div>
                    <div class="text-muted" style="font-size:12px;">{{ $req->user->email }}</div>
                  </td>
                  <td>{{ $req->requested_role === \App\Models\ChairRequest::ROLE_DEPT ? 'Dept Chair' : 'Prog Chair' }}</td>
                  <td>
                    <div><strong>D:</strong> {{ $req->department->name ?? '—' }}</div>
                    <div><strong>P:</strong> {{ $req->program->name ?? '—' }}</div>
                  </td>
                  <td>{{ $req->decidedBy->name ?? '—' }}</td>
                  <td>{{ optional($req->decided_at)->format('Y-m-d H:i') ?? '—' }}</td>
                </tr>
              @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No approvals yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header bg-light"><strong>Recently Rejected</strong></div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Admin</th>
                <th>Role</th>
                <th>Scope</th>
                <th>Decided By</th>
                <th>When</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($chairRequestsRejected as $req)
                <tr>
                  <td>{{ $req->id }}</td>
                  <td>
                    <div class="fw-semibold">{{ $req->user->name }}</div>
                    <div class="text-muted" style="font-size:12px;">{{ $req->user->email }}</div>
                  </td>
                  <td>{{ $req->requested_role === \App\Models\ChairRequest::ROLE_DEPT ? 'Dept Chair' : 'Prog Chair' }}</td>
                  <td>
                    <div><strong>D:</strong> {{ $req->department->name ?? '—' }}</div>
                    <div><strong>P:</strong> {{ $req->program->name ?? '—' }}</div>
                  </td>
                  <td>{{ $req->decidedBy->name ?? '—' }}</td>
                  <td>{{ optional($req->decided_at)->format('Y-m-d H:i') ?? '—' }}</td>
                </tr>
              @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No rejections yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
{{-- ░░░ END: Approved & Rejected (history) ░░░ --}}
