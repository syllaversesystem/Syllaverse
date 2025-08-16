{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/master-data/modals/delete-so-modal.blade.php
* Description: Confirmation modal for deleting a Student Outcome (SO)
-------------------------------------------------------------------------------
📜 Log:
[2025-08-18] Initial creation – posts DELETE to admin.so.destroy via JS-set action.
-------------------------------------------------------------------------------
--}}

<div class="modal fade" id="deleteSoModal" tabindex="-1" aria-labelledby="deleteSoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="deleteSoForm" action="" method="POST" class="modal-content so-delete-form">
      @csrf
      @method('DELETE')

      <div class="modal-header">
        <h5 class="modal-title fw-semibold" id="deleteSoLabel">Delete Student Outcome</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <p class="mb-1">You are about to delete:</p>
        <div class="d-flex align-items-center gap-2">
          <span class="small text-muted">SO Code:</span>
          <span id="deleteSoCode" class="badge text-bg-light px-2 py-1">SO?</span>
        </div>
        <div class="mt-3 small text-muted">
          This action cannot be undone.
        </div>
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
