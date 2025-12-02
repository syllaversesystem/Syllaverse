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
    <style>
      /* Ensure modals are above table/backdrop and not clipped */
      .table-wrapper, .table-responsive { overflow: visible !important; }
      .modal.fade.show { z-index: 2006 !important; }
      .modal-backdrop.show { z-index: 2005 !important; }
    </style>
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

                  {{-- Delete – open confirmation modal before permanent removal --}}
                  <button
                    class="action-btn reject"
                    type="button"
                    title="Delete {{ $user->name }}"
                    aria-label="Delete {{ $user->name }}"
                    data-bs-toggle="modal"
                    data-bs-target="#svDeleteRejectedAdminModal-{{ $user->id }}">
                    <i data-feather="trash-2"></i>
                  </button>
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
                  {{-- Delete – open confirmation modal before permanent removal --}}
                  <button
                    class="action-btn reject"
                    type="button"
                    title="Delete {{ $user->name }}"
                    aria-label="Delete {{ $user->name }}"
                    data-bs-toggle="modal"
                    data-bs-target="#svDeleteRejectedFacultyModal-{{ $user->id }}">
                    <i data-feather="trash-2"></i>
                  </button>
                @endif
              </td>
            </tr>
            {{-- Confirmation Modal: Delete Rejected Admin --}}
            @if($user->role === 'admin')
              <div class="modal fade" id="svDeleteRejectedAdminModal-{{ $user->id }}" tabindex="-1" aria-labelledby="svDeleteRejectedAdminLabel-{{ $user->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                    <style>
                      #svDeleteRejectedAdminModal-{{ $user->id }} .modal-content { border-radius: 0.75rem; }
                      #svDeleteRejectedAdminModal-{{ $user->id }} .btn-danger,
                      #svDeleteRejectedAdminModal-{{ $user->id }} .btn-light {
                        background: var(--sv-card-bg, #fff);
                        border: none;
                        color: #000;
                        transition: all 0.2s ease-in-out;
                        box-shadow: none;
                        display: inline-flex;
                        align-items: center;
                        gap: 0.5rem;
                        padding: 0.5rem 1rem;
                        border-radius: 0.375rem;
                      }
                      #svDeleteRejectedAdminModal-{{ $user->id }} .btn-danger:hover,
                      #svDeleteRejectedAdminModal-{{ $user->id }} .btn-danger:focus { background: linear-gradient(135deg, rgba(255,240,235,.88), rgba(255,255,255,.46)); box-shadow: 0 4px 10px rgba(204,55,55,.12); color: #CB3737; }
                      #svDeleteRejectedAdminModal-{{ $user->id }} .btn-danger:hover i,
                      #svDeleteRejectedAdminModal-{{ $user->id }} .btn-danger:hover svg,
                      #svDeleteRejectedAdminModal-{{ $user->id }} .btn-danger:focus i,
                      #svDeleteRejectedAdminModal-{{ $user->id }} .btn-danger:focus svg { stroke: #CB3737; }
                      #svDeleteRejectedAdminModal-{{ $user->id }} .btn-light:hover,
                      #svDeleteRejectedAdminModal-{{ $user->id }} .btn-light:focus { background: linear-gradient(135deg, rgba(220,220,220,.88), rgba(240,240,240,.46)); box-shadow: 0 4px 10px rgba(0,0,0,.12); }
                    </style>
                    <div class="modal-header border-0 pb-2">
                      <h5 class="modal-title d-flex align-items-center gap-2 text-danger" id="svDeleteRejectedAdminLabel-{{ $user->id }}">
                        <i data-feather="alert-triangle"></i>
                        Delete Account
                      </h5>
                    </div>
                    <div class="modal-body pt-0">
                      <div class="alert alert-warning d-flex align-items-start gap-2 mb-4">
                        <i data-feather="info" class="text-warning mt-1 flex-shrink-0"></i>
                        <div>
                          <strong>Warning:</strong> This action cannot be undone.
                        </div>
                      </div>
                      <p class="mb-3">Are you sure you want to permanently delete <strong>{{ $user->name }}</strong>?</p>
                      <div class="bg-light rounded p-3 mb-3">
                        <h6 class="mb-2 text-muted">This will:</h6>
                        <ul class="mb-0 text-sm">
                          <li>Remove all appointments and chair requests</li>
                          <li>Delete their account record</li>
                          <li>Prevent any future login</li>
                        </ul>
                      </div>
                    </div>
                    <div class="modal-footer border-0 pt-2">
                      <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i data-feather="x" class="me-1"></i>
                        Cancel
                      </button>
                      <form method="POST" action="{{ route('superadmin.delete.admin', $user->id) }}" class="d-inline" data-ajax="true" data-sv-delete="true">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                          <i data-feather="trash-2" class="me-1"></i>
                          Delete Account
                        </button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            @endif

            {{-- Confirmation Modal: Delete Rejected Faculty --}}
            @if($user->role === 'faculty')
              <div class="modal fade" id="svDeleteRejectedFacultyModal-{{ $user->id }}" tabindex="-1" aria-labelledby="svDeleteRejectedFacultyLabel-{{ $user->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                    <style>
                      #svDeleteRejectedFacultyModal-{{ $user->id }} .modal-content { border-radius: 0.75rem; }
                      #svDeleteRejectedFacultyModal-{{ $user->id }} .btn-danger,
                      #svDeleteRejectedFacultyModal-{{ $user->id }} .btn-light {
                        background: var(--sv-card-bg, #fff);
                        border: none;
                        color: #000;
                        transition: all 0.2s ease-in-out;
                        box-shadow: none;
                        display: inline-flex;
                        align-items: center;
                        gap: 0.5rem;
                        padding: 0.5rem 1rem;
                        border-radius: 0.375rem;
                      }
                      #svDeleteRejectedFacultyModal-{{ $user->id }} .btn-danger:hover,
                      #svDeleteRejectedFacultyModal-{{ $user->id }} .btn-danger:focus { background: linear-gradient(135deg, rgba(255,240,235,.88), rgba(255,255,255,.46)); box-shadow: 0 4px 10px rgba(204,55,55,.12); color: #CB3737; }
                      #svDeleteRejectedFacultyModal-{{ $user->id }} .btn-danger:hover i,
                      #svDeleteRejectedFacultyModal-{{ $user->id }} .btn-danger:hover svg,
                      #svDeleteRejectedFacultyModal-{{ $user->id }} .btn-danger:focus i,
                      #svDeleteRejectedFacultyModal-{{ $user->id }} .btn-danger:focus svg { stroke: #CB3737; }
                      #svDeleteRejectedFacultyModal-{{ $user->id }} .btn-light:hover,
                      #svDeleteRejectedFacultyModal-{{ $user->id }} .btn-light:focus { background: linear-gradient(135deg, rgba(220,220,220,.88), rgba(240,240,240,.46)); box-shadow: 0 4px 10px rgba(0,0,0,.12); }
                    </style>
                    <div class="modal-header border-0 pb-2">
                      <h5 class="modal-title d-flex align-items-center gap-2 text-danger" id="svDeleteRejectedFacultyLabel-{{ $user->id }}">
                        <i data-feather="alert-triangle"></i>
                        Delete Account
                      </h5>
                    </div>
                    <div class="modal-body pt-0">
                      <div class="alert alert-warning d-flex align-items-start gap-2 mb-4">
                        <i data-feather="info" class="text-warning mt-1 flex-shrink-0"></i>
                        <div>
                          <strong>Warning:</strong> This action cannot be undone.
                        </div>
                      </div>
                      <p class="mb-3">Are you sure you want to permanently delete <strong>{{ $user->name }}</strong>?</p>
                      <div class="bg-light rounded p-3 mb-3">
                        <h6 class="mb-2 text-muted">This will:</h6>
                        <ul class="mb-0 text-sm">
                          <li>Remove all appointments and chair requests</li>
                          <li>Delete their account record</li>
                          <li>Prevent any future login</li>
                        </ul>
                      </div>
                    </div>
                    <div class="modal-footer border-0 pt-2">
                      <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i data-feather="x" class="me-1"></i>
                        Cancel
                      </button>
                      <form method="POST" action="{{ route('superadmin.delete.faculty', $user->id) }}" class="d-inline" data-ajax="true" data-sv-delete="true">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                          <i data-feather="trash-2" class="me-1"></i>
                          Delete Account
                        </button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            @endif
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

  <script>
    // Ensure confirmation modals are appended to <body> to avoid table stacking/overflow issues
    document.addEventListener('DOMContentLoaded', function() {
      try {
        const ids = [
          ...Array.from(document.querySelectorAll('[id^="svDeleteRejectedAdminModal-"]')).map(el => el.id),
          ...Array.from(document.querySelectorAll('[id^="svDeleteRejectedFacultyModal-"]')).map(el => el.id)
        ];
        ids.forEach(id => {
          const modal = document.getElementById(id);
          if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
          }
        });
      } catch (_) {}
    });
  </script>
