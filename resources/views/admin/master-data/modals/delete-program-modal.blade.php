{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/master-data/modals/delete-program-modal.blade.php
* Description: Delete confirmation modal for Programs (AJAX-ready)
-------------------------------------------------------------------------------
📜 Log:
[2025-08-17] Added form id=deleteProgramForm for AJAX submit.
-------------------------------------------------------------------------------
--}}
<div class="modal fade sv-appt-modal" id="deleteProgramModal" tabindex="-1" aria-labelledby="deleteProgramModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="deleteProgramForm" action="{{ route('admin.programs.destroy', 0) }}" method="POST" class="delete-program-form">
        @csrf
        @method('DELETE')

        <div class="modal-header">
          <h5 class="modal-title fw-semibold" id="deleteProgramModalLabel">
            Delete <span id="programDeleteLabel">Program</span>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <p class="mb-0">
            Are you sure you want to delete <strong id="programDeleteWhat">this program</strong>? 
            This action cannot be undone.
          </p>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i data-feather="trash"></i> Delete
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
