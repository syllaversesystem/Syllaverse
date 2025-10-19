{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/manage-accounts/tabs/approved.blade.php
* Description: Approved Faculty – Faculty style table (no Active Appointments column)
-------------------------------------------------------------------------------
--}}

<div class="tab-pane fade" id="faculty-approved" role="tabpanel" aria-labelledby="faculty-approved-tab">

  {{-- ░░░ START: Table Section (Superadmin-style wrapper) ░░░ --}}
  <div class="table-wrapper position-relative">
    <div class="table-responsive">
      <table class="table faculty-manage-account-table mb-0" id="svApprovedFacultyTable">
        <thead class="faculty-manage-account-table-header">
          <tr>
            <th><i data-feather="user"></i> Name</th>
            <th><i data-feather="mail"></i> Email</th>
            <th class="text-end"><i data-feather="more-vertical"></i></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($approvedRequests ?? [] as $request)
            <tr>
              <td>
                {{ $request->user->name }}
              </td>
              <td class="text-muted">
                {{ $request->user->email }}
              </td>
              <td class="text-end">
                <span class="badge bg-success">
                  <i data-feather="check" class="me-1"></i> Approved
                </span>
              </td>
            </tr>
          @empty
            <tr class="sv-empty-row">
              <td colspan="3">
                <div class="sv-empty">
                  <h6>No approved faculty requests</h6>
                  <p>Approved faculty requests will appear here once processed.</p>
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
