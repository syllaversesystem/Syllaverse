{{-- 
-------------------------------------------------------------------------------
* File: resources/views/superadmin/manage-accounts/tabs/admins-rejected.blade.php
* Description: Rejected Accounts tab – Approvals-style table, icon-only re-approve (AJAX), stable DOM IDs
-------------------------------------------------------------------------------
📜 Log:
[2025-08-11] UI/UX refresh – Approvals-style wrapper, header icons, icon-only actions,
             added #svRejectedAdminsTable and per-row IDs for JS refresh; empty state added.
[2025-10-23] Updated to include both rejected admins and faculty accounts for unified management.
-------------------------------------------------------------------------------
--}}

@php
  // Merge rejected admins and faculty for unified display
  $allRejectedUsers = collect();
  if (isset($rejectedAdmins)) {
    $allRejectedUsers = $allRejectedUsers->concat($rejectedAdmins);
  }
  if (isset($rejectedFaculty)) {
    $allRejectedUsers = $allRejectedUsers->concat($rejectedFaculty);
  }
@endphp

{{-- ░░░ START: Table Section (Approvals-style wrapper) ░░░ --}}
  <div class="table-wrapper position-relative">
    <div class="table-responsive">
      <table class="table superadmin-manage-account-table mb-0" id="svRejectedAdminsTable">
        <thead class="superadmin-manage-account-table-header">
          <tr>
            <th><i data-feather="user-x"></i> Name</th>
            <th><i data-feather="mail"></i> Email</th>
            <th class="text-end"><i data-feather="more-vertical"></i></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($allRejectedUsers as $user)
            <tr id="sv-rejected-row-{{ $user->id }}">
              <td>{{ $user->name }}</td>
              
              <td class="text-muted">{{ $user->email }}</td>
              <td class="text-end">
                {{-- Re-approve (AJAX) – moves user back to Approved --}}
                @if($user->role === 'admin')
                  <form method="POST"
                        action="{{ route('superadmin.approve.admin', $user->id) }}"
                        class="d-inline"
                        data-ajax="true"
                        data-sv-reapprove="true"
                        aria-label="Re-approve {{ $user->name }}">
                    @csrf
                    <button
                      class="action-btn approve"
                      type="submit"
                      title="Re-approve {{ $user->name }}"
                      data-bs-toggle="tooltip"
                      data-bs-placement="top">
                      <i data-feather="check-circle"></i>
                    </button>
                  </form>
                @elseif($user->role === 'faculty')
                  <form method="POST"
                        action="{{ route('superadmin.approve.faculty', $user->id) }}"
                        class="d-inline"
                        data-ajax="true"
                        data-sv-reapprove="true"
                        aria-label="Re-approve {{ $user->name }}">
                    @csrf
                    <button
                      class="action-btn approve"
                      type="submit"
                      title="Re-approve {{ $user->name }}"
                      data-bs-toggle="tooltip"
                      data-bs-placement="top">
                      <i data-feather="check-circle"></i>
                    </button>
                  </form>
                @endif
              </td>
            </tr>
          @empty
            {{-- No rejected users --}}
          @endforelse

          {{-- ░░░ START: Empty State ░░░ --}}
          @if($allRejectedUsers->isEmpty())
            <tr class="superadmin-manage-account-empty-row">
              <td colspan="4">
                <div class="sv-empty">
                  <h6>No rejected accounts</h6>
                  <p>When accounts are rejected, they will appear here. You can re-approve them anytime.</p>
                </div>
              </td>
            </tr>
          @endif
          {{-- ░░░ END: Empty State ░░░ --}}
        </tbody>
      </table>
    </div>
  </div>
  {{-- ░░░ END: Table Section ░░░ --}}
