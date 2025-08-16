{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/manage-accounts/tabs/approved.blade.php
* Description: Approved Faculty – Super Admin style table (no Active Appointments column)
-------------------------------------------------------------------------------
--}}

<div class="tab-pane fade" id="faculty-approved" role="tabpanel" aria-labelledby="faculty-approved-tab">

  <div class="table-wrapper position-relative">
    <div class="table-responsive">
      <table class="table mb-0 sv-accounts-table" id="svApprovedFacultyTable">
        <thead>
          <tr>
            <th><i data-feather="user"></i> Name</th>
            <th><i data-feather="mail"></i> Email</th>
            <th class="text-end"><i data-feather="more-vertical"></i></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($approvedFaculty ?? [] as $faculty)
            <tr>
              <td>{{ $faculty->name }}</td>
              <td class="text-muted">{{ $faculty->email }}</td>
              <td class="text-end">
                <form method="POST" action="{{ route('admin.manage-accounts.reject', $faculty->id) }}" class="d-inline">@csrf
                  <button type="submit" class="action-btn reject" title="Reject"><i data-feather="x"></i></button>
                </form>
              </td>
            </tr>
          @empty
            <tr class="sv-empty-row">
              <td colspan="3">
                <div class="sv-empty">
                  <h6>No approved faculty</h6>
                  <p>Approved faculty will appear here once verified.</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
