{{-- 
-------------------------------------------------------------------------------
* File: resources/views/superadmin/manage-accounts/tabs/admins-rejected.blade.php
* Description: Rejected Admins tab – Approvals-style table, icon-only re-approve (AJAX), stable DOM IDs
-------------------------------------------------------------------------------
📜 Log:
[2025-08-11] UI/UX refresh – Approvals-style wrapper, header icons, icon-only actions,
             added #svRejectedAdminsTable and per-row IDs for JS refresh; empty state added.
-------------------------------------------------------------------------------
--}}

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
          @forelse (($rejectedAdmins ?? []) as $admin)
            <tr id="sv-rejected-row-{{ $admin->id }}">
              <td>{{ $admin->name }}</td>
              <td class="text-muted">{{ $admin->email }}</td>
              <td class="text-end">
                {{-- Re-approve (AJAX) – moves admin back to Approved --}}
                <form method="POST"
                      action="{{ route('superadmin.approve.admin', $admin->id) }}"
                      class="d-inline"
                      data-ajax="true"
                      data-sv-reapprove="true"  {{-- optional flag if you want to branch in JS --}}
                      aria-label="Re-approve {{ $admin->name }}">
                  @csrf
                  <button
                    class="action-btn approve"
                    type="submit"
                    title="Re-approve {{ $admin->name }}"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top">
                    <i data-feather="check-circle"></i>
                  </button>
                </form>
              </td>
            </tr>
          @empty
            {{-- No rejected admins --}}
          @endforelse

          {{-- ░░░ START: Empty State ░░░ --}}
          @if(($rejectedAdmins ?? collect())->isEmpty())
            <tr class="superadmin-manage-account-empty-row">
              <td colspan="3">
                <div class="sv-empty">
                  <h6>No rejected accounts</h6>
                  <p>When admins are rejected, they will appear here. You can re-approve them anytime.</p>
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
