{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/manage-accounts/tabs/rejected.blade.php
* Description: Rejected Faculty – Faculty style table
-------------------------------------------------------------------------------
--}}

<div class="tab-pane fade" id="faculty-rejected" role="tabpanel" aria-labelledby="faculty-rejected-tab">

  {{-- ░░░ START: Table Section (Superadmin-style wrapper) ░░░ --}}
  <div class="table-wrapper position-relative">
    <div class="table-responsive">
      <table class="table faculty-manage-account-table mb-0" id="svRejectedFacultyTable">
        <thead class="faculty-manage-account-table-header">
          <tr>
            <th><i data-feather="user-x"></i> Name</th>
            <th><i data-feather="mail"></i> Email</th>
            <th class="text-end"><i data-feather="more-vertical"></i></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($rejectedRequests ?? [] as $request)
            <tr>
              <td>
                {{ $request->user->name }}
              </td>
              <td class="text-muted">
                {{ $request->user->email }}
              </td>
              <td class="text-end">
                <span class="badge bg-danger">
                  <i data-feather="x" class="me-1"></i> Rejected
                </span>
              </td>
            </tr>
          @empty
            <tr class="sv-empty-row">
              <td colspan="3">
                <div class="sv-empty">
                  <h6>No rejected faculty requests</h6>
                  <p>Rejected faculty requests will appear here for reference.</p>
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
