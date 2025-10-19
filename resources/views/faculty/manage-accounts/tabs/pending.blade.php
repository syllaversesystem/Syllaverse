{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/manage-accounts/tabs/pending.blade.php
* Description: Pending Faculty – Faculty style table
-------------------------------------------------------------------------------
--}}

<div class="tab-pane fade show active" id="faculty-pending" role="tabpanel" aria-labelledby="faculty-pending-tab">

  {{-- ░░░ START: Table Section (Superadmin-style wrapper) ░░░ --}}
  <div class="table-wrapper position-relative">
    <div class="table-responsive">
      <table class="table faculty-manage-account-table mb-0" id="svPendingFacultyTable">
        <thead class="faculty-manage-account-table-header">
          <tr>
            <th><i data-feather="user-plus"></i> Name</th>
            <th><i data-feather="mail"></i> Email</th>
            <th class="text-end"><i data-feather="more-vertical"></i></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($pendingRequests ?? [] as $request)
            <tr>
              <td>
                {{ $request->user->name }}
              </td>
              <td class="text-muted">
                {{ $request->user->email }}
              </td>
              <td class="text-end">
                <form method="POST" action="{{ route('faculty.manage-accounts.approve', $request->id) }}" class="d-inline">@csrf
                  <button type="submit" class="action-btn approve" title="Approve Faculty Request"><i data-feather="check"></i></button>
                </form>
                <form method="POST" action="{{ route('faculty.manage-accounts.reject', $request->id) }}" class="d-inline">@csrf
                  <button type="submit" class="action-btn reject" title="Reject Faculty Request"><i data-feather="x"></i></button>
                </form>
              </td>
            </tr>
          @empty
            <tr class="sv-empty-row">
              <td colspan="3">
                <div class="sv-empty">
                  <h6>No pending faculty requests</h6>
                  <p>New faculty role requests will appear here for approval.</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  {{-- ░░░ END: Table Section ░░░ --}}

</div>
