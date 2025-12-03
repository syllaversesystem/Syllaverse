{{-- 
-------------------------------------------------------------------------------
* File: resources/views/superadmin/rejected-accounts/index.blade.php
* Description: Super Admin Rejected Accounts module (extracted from Rejected tab)
-------------------------------------------------------------------------------
📜 Log:
[2025-12-03] Initial creation – module view reusing Rejected tab content.
-------------------------------------------------------------------------------
--}}

@extends('layouts.superadmin')

@section('title', 'Rejected Accounts • Super Admin • Syllaverse')
@section('page-title', 'Rejected Accounts')

@section('content')
  <div class="manage-account">
    @php
      $allRejectedUsers = collect();
      if (isset($rejectedAdmins)) {
        $allRejectedUsers = $allRejectedUsers->concat($rejectedAdmins);
      }
      if (isset($rejectedFaculty)) {
        $allRejectedUsers = $allRejectedUsers->concat($rejectedFaculty);
      }
    @endphp

    <div class="table-wrapper position-relative">
      <style>
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
                      <button class="action-btn approve" type="submit" title="Re-approve {{ $user->name }}" data-bs-toggle="tooltip" data-bs-placement="top">
                        <i data-feather="check-circle"></i>
                      </button>
                    </form>
                    {{-- Delete – uses shared confirmation modal --}}
                    <button
                      class="action-btn reject"
                      type="button"
                      title="Delete {{ $user->name }}"
                      aria-label="Delete {{ $user->name }}"
                      data-sv-delete-open
                      data-sv-delete-id="{{ $user->id }}"
                      data-sv-delete-name="{{ $user->name }}"
                      data-sv-delete-action="{{ route('superadmin.delete.admin', $user->id) }}">
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
                      <button class="action-btn approve" type="submit" title="Re-approve {{ $user->name }}" data-bs-toggle="tooltip" data-bs-placement="top">
                        <i data-feather="check-circle"></i>
                      </button>
                    </form>
                    {{-- Delete – uses shared confirmation modal --}}
                    <button
                      class="action-btn reject"
                      type="button"
                      title="Delete {{ $user->name }}"
                      aria-label="Delete {{ $user->name }}"
                      data-sv-delete-open
                      data-sv-delete-id="{{ $user->id }}"
                      data-sv-delete-name="{{ $user->name }}"
                      data-sv-delete-action="{{ route('superadmin.delete.faculty', $user->id) }}">
                      <i data-feather="trash-2"></i>
                    </button>
                  @endif
                </td>
              </tr>
              
            @empty
              {{-- No rejected users --}}
            @endforelse

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
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  // Shared delete confirmation modal: populate and show dynamically
  document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('svSharedDeleteModal');
    const nameEl = document.getElementById('svSharedDeleteName');
    const formEl = document.getElementById('svSharedDeleteForm');
    if (!modalEl || !formEl) return;
    // Ensure modal is attached to body to avoid z-index issues
    if (modalEl.parentElement !== document.body) {
      document.body.appendChild(modalEl);
    }
    const openers = document.querySelectorAll('[data-sv-delete-open]');
    openers.forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-sv-delete-id');
        const name = btn.getAttribute('data-sv-delete-name');
        const action = btn.getAttribute('data-sv-delete-action');
        if (nameEl) nameEl.textContent = name || '';
        formEl.setAttribute('action', action);
        formEl.setAttribute('data-sv-row', `#sv-rejected-row-${id}`);
        formEl.dataset.ajax = 'true';
        formEl.dataset.svDelete = 'true';
        // Prefer Bootstrap's Data API like in Approved module
        if (!modalEl._svTrigger) {
          const t = document.createElement('button');
          t.type = 'button';
          t.setAttribute('data-bs-toggle', 'modal');
          t.setAttribute('data-bs-target', '#svSharedDeleteModal');
          t.style.display = 'none';
          document.body.appendChild(t);
          modalEl._svTrigger = t;
        }
        modalEl._svTrigger.click();
      });
    });

    // Clean up when hidden
    modalEl.addEventListener('hidden.bs.modal', function() {
      try {
        formEl.removeAttribute('data-sv-row');
        formEl.removeAttribute('action');
        if (nameEl) nameEl.textContent = '';
      } catch (_) {}
    });
  });
  </script>
@endpush

@push('modals')
  {{-- Themed Shared Confirmation Modal --}}
  <div class="modal sv-faculty-dept-modal fade" id="svSharedDeleteModal" tabindex="-1" aria-labelledby="svSharedDeleteLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-md">
      <div class="modal-content">
        <style>
          #svSharedDeleteModal { --sv-bg:#FAFAFA; --sv-bdr:#E3E3E3; --sv-danger:#CB3737; }
          #svSharedDeleteModal .modal-content{ border-radius:16px; border:1px solid var(--sv-bdr); background:#fff; box-shadow:0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06); overflow:hidden; }
          #svSharedDeleteModal .modal-header{ border-bottom:1px solid var(--sv-bdr); background: var(--sv-bg); padding:.85rem 1rem; }
          #svSharedDeleteModal .modal-title{ color: var(--sv-danger); font-weight:600; font-size:1rem; display:inline-flex; align-items:center; gap:.5rem; }
          #svSharedDeleteModal .modal-title i, #svSharedDeleteModal .modal-title svg { width:1.05rem; height:1.05rem; }
          #svSharedDeleteModal .btn-danger{ background:#fff; border:none; color: var(--sv-danger); transition: all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
          #svSharedDeleteModal .btn-danger:hover, #svSharedDeleteModal .btn-danger:focus{ background: linear-gradient(135deg, rgba(255,235,235,.88), rgba(255,245,245,.46)); box-shadow:0 4px 10px rgba(203,55,55,.15); color: var(--sv-danger); }
          #svSharedDeleteModal .btn-light{ background:#fff; border:none; color:#000; transition: all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
          #svSharedDeleteModal .btn-light i, #svSharedDeleteModal .btn-light svg { stroke:#000; }
          #svSharedDeleteModal .btn-light:hover, #svSharedDeleteModal .btn-light:focus{ background: linear-gradient(135deg, rgba(225,225,225,.88), rgba(240,240,240,.46)); box-shadow:0 4px 10px rgba(0,0,0,.08); color:#000; }
          #svSharedDeleteModal .text-truncate-2 { display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; overflow: hidden; }
          /* Layering to match other modules */
          #svSharedDeleteModal.modal { z-index: 2006 !important; }
          .modal-backdrop.show { z-index: 2005 !important; }
          /* bounce feedback when hide prevented */
          #svSharedDeleteModal.modal-static .modal-dialog { transform: scale(1.02); transition: transform .2s ease-in-out; }
          #svSharedDeleteModal.modal-static .modal-content { box-shadow: 0 8px 24px rgba(0,0,0,.12), 0 4px 12px rgba(0,0,0,.08); }
        </style>

        <div class="modal-header">
          <h5 class="modal-title fw-semibold d-flex align-items-center gap-2" id="svSharedDeleteLabel">
            <i data-feather="trash-2"></i>
            <span>Delete Account</span>
          </h5>
        </div>

        <div class="modal-body">
          <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 rounded-circle mb-3" style="width:64px; height:64px;">
              <i data-feather="trash-2" class="text-danger" style="width:28px; height:28px;"></i>
            </div>
            <h6 class="fw-semibold mb-2">Delete Account</h6>
            <p class="text-muted mb-0">Are you sure you want to permanently delete this account?</p>
          </div>

          <div class="bg-light rounded-3 p-3 mb-2">
            <div class="small text-muted mb-1">You are about to delete:</div>
            <div class="fw-semibold mb-0" id="svSharedDeleteName">Loading...</div>
          </div>

          <div class="alert alert-warning border-0 mb-0" style="background: rgba(255, 193, 7, 0.1);">
            <div class="d-flex align-items-start gap-3">
              <i data-feather="alert-triangle" class="text-warning flex-shrink-0 mt-1" style="width:18px; height:18px;"></i>
              <div class="small">
                <div class="fw-medium text-dark">This action cannot be undone</div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i data-feather="x"></i> Cancel</button>
          <form method="POST" id="svSharedDeleteForm" class="d-inline" data-ajax="true" data-sv-delete="true" data-sv-row="">
            @csrf
            <button type="submit" class="btn btn-danger"><i data-feather="trash-2"></i> Delete</button>
          </form>
        </div>
      </div>
    </div>
  </div>
@endpush
