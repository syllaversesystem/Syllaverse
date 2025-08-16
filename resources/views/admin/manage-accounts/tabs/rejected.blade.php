{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/manage-accounts/tabs/rejected.blade.php
* Description: Rejected Faculty – Super Admin style table
-------------------------------------------------------------------------------
--}}

<div class="tab-pane fade" id="faculty-rejected" role="tabpanel" aria-labelledby="faculty-rejected-tab">

  <div class="table-wrapper position-relative">
    <div class="table-responsive">
      <table class="table mb-0 sv-accounts-table" id="svRejectedFacultyTable">
        <thead>
          <tr>
            <th><i data-feather="user-x"></i> Name</th>
            <th><i data-feather="mail"></i> Email</th>
            <th class="text-end"><i data-feather="more-vertical"></i></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($rejectedFaculty ?? [] as $faculty)
            <tr>
              <td>{{ $faculty->name }}</td>
              <td class="text-muted">{{ $faculty->email }}</td>
              <td class="text-end">
                <form method="POST" action="{{ route('admin.manage-accounts.approve', $faculty->id) }}" class="d-inline">@csrf
                  <button type="submit" class="action-btn approve" title="Re-approve">
                    <i data-feather="check-circle"></i>
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr class="sv-empty-row">
              <td colspan="3">
                <div class="sv-empty">
                  <h6>No rejected faculty</h6>
                  <p>When a faculty is rejected, they will appear here. You can re-approve them anytime.</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
