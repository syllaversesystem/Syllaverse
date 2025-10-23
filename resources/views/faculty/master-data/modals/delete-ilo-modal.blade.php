{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/master-data/modals/delete-ilo-modal.blade.php
* Description: Delete ILO (AJAX confirm)
-------------------------------------------------------------------------------
📜 Log:
[2025-01-20] Copied from admin master-data for Faculty module
-------------------------------------------------------------------------------
--}}

<div class="modal fade" id="deleteIloModal" tabindex="-1" aria-labelledby="deleteIloLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="deleteIloForm" action="" method="POST" class="modal-content">
      @csrf
      @method('DELETE')

      <div class="modal-header">
        <h5 class="modal-title fw-semibold" id="deleteIloLabel">Delete ILO</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <p class="mb-1">You are about to delete:</p>
        <div class="d-flex align-items-center gap-2">
          <span class="small text-muted">ILO Code:</span>
          <span id="deleteIloCode" class="badge text-bg-light px-2 py-1">ILO?</span>
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