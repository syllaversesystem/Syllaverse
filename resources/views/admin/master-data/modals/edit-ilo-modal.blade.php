{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/master-data/modals/edit-ilo-modal.blade.php
* Description: Edit ILO (AJAX) – code display-only
-------------------------------------------------------------------------------
📜 Log:
[2025-08-18] Initial creation – shows code badge, updates description via PUT.
-------------------------------------------------------------------------------
--}}

<div class="modal fade sv-appt-modal" id="editIloModal" tabindex="-1" aria-labelledby="editIloLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="editIloForm" action="" method="POST" class="modal-content">
      @csrf
      @method('PUT')

      <div class="modal-header">
        <h5 class="modal-title fw-semibold" id="editIloLabel">Edit ILO</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3 d-flex align-items-center gap-2">
          <span class="small fw-medium text-muted">ILO Code:</span>
          <span id="editIloCodeBadge" class="badge text-bg-light px-2 py-1">ILO?</span>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-medium text-muted" for="editIloDescription">Description</label>
          <textarea class="form-control" id="editIloDescription" name="description" rows="5" required></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
          <i data-feather="save"></i> Save Changes
        </button>
      </div>
    </form>
  </div>
</div>
