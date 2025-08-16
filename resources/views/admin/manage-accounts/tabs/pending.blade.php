{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/manage-accounts/tabs/pending.blade.php
* Description: Pending Faculty – Super Admin style table
-------------------------------------------------------------------------------
--}}

<div class="tab-pane fade show active" id="faculty-pending" role="tabpanel" aria-labelledby="faculty-pending-tab">

  <div class="table-wrapper position-relative">
    <div class="table-responsive">
      <table class="table mb-0 sv-accounts-table" id="svPendingFacultyTable">
        <thead>
          <tr>
            <th><i data-feather="user-plus"></i> Name</th>
            <th><i data-feather="mail"></i> Email</th>
            <th class="text-end"><i data-feather="more-vertical"></i></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($pendingFaculty ?? [] as $faculty)
            <tr>
              <td>{{ $faculty->name }}</td>
              <td class="text-muted">{{ $faculty->email }}</td>
              <td class="text-end">
                <form method="POST" action="{{ route('admin.manage-accounts.approve', $faculty->id) }}" class="d-inline">@csrf
                  <button type="submit" class="action-btn approve" title="Approve"><i data-feather="check"></i></button>
                </form>
                <form method="POST" action="{{ route('admin.manage-accounts.reject', $faculty->id) }}" class="d-inline">@csrf
                  <button type="submit" class="action-btn reject" title="Reject"><i data-feather="x"></i></button>
                </form>
              </td>
            </tr>
          @empty
            <tr class="sv-empty-row">
              <td colspan="3">
                <div class="sv-empty">
                  <h6>No pending faculty</h6>
                  <p>New faculty signups will appear here for approval.</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
