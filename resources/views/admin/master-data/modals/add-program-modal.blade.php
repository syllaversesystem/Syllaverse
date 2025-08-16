{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/master-data/modals/add-program-modal.blade.php
* Description: Modal for adding a new program (AJAX-ready)
-------------------------------------------------------------------------------
📜 Log:
[2025-08-17] Added form id=addProgramForm for AJAX submit.
-------------------------------------------------------------------------------
--}}
<div class="modal fade sv-appt-modal" id="addProgramModal" tabindex="-1" aria-labelledby="addProgramModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form id="addProgramForm" action="{{ route('admin.programs.store') }}" method="POST" class="modal-content">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title fw-semibold" id="addProgramModalLabel">Add New Program</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label for="programName" class="form-label small fw-medium text-muted">Program Name</label>
          <input type="text" class="form-control form-control-sm" id="programName" name="name" required>
        </div>

        <div class="mb-3">
          <label for="programCode" class="form-label small fw-medium text-muted">Program Code</label>
          <input type="text" class="form-control form-control-sm" id="programCode" name="code" required>
        </div>

        <div class="mb-3">
          <label for="programDescription" class="form-label small fw-medium text-muted">Description (optional)</label>
          <textarea class="form-control" id="programDescription" name="description" rows="5"></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger">
          <i data-feather="plus"></i> Create Program
        </button>
      </div>
    </form>
  </div>
</div>
