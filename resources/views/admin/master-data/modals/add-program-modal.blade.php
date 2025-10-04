{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/master-data/modals/add-program-modal.blade.php
* Description: Modal for adding a new program (AJAX-ready)
-------------------------------------------------------------------------------
📜 Log:
[2025-08-17] Added form id=addProgramForm for AJAX submit.
-------------------------------------------------------------------------------
--}}
<div class="modal fade" id="addProgramModal" tabindex="-1" aria-labelledby="addProgramModalLabel" aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <style>
        #addProgramModal {
          z-index: 1055 !important;
        }
        #addProgramModal .modal-backdrop {
          z-index: 1054 !important;
        }
        #addProgramModal .modal-dialog {
          z-index: 1056 !important;
        }
      </style>
      <form id="addProgramForm" action="{{ route('admin.programs.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title fw-semibold" id="addProgramModalLabel">Add New Program</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

      <div class="modal-body">
        <div class="mb-3">
          <label for="programName" class="form-label small fw-medium text-muted">Program Name</label>
          <input type="text" class="form-control form-control-sm" id="programName" name="name" placeholder="e.g., Bachelor of Science in Computer Science" required>
        </div>

        <div class="mb-3">
          <label for="programCode" class="form-label small fw-medium text-muted">Program Code</label>
          <input type="text" class="form-control form-control-sm" id="programCode" name="code" placeholder="e.g., BSCS, BSIT, BSEE" required>
        </div>

        <div class="mb-3">
          <label for="programDescription" class="form-label small fw-medium text-muted">Description (optional)</label>
          <textarea class="form-control" id="programDescription" name="description" rows="5" placeholder="Enter a brief description of the program, its objectives, and key features..."></textarea>
          <div class="form-text text-muted mt-1">
            <i data-feather="info" class="me-1" style="width: 14px; height: 14px;"></i>
            This description will be used in AI prompts to generate more relevant and accurate syllabi content for this program.
          </div>
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
</div>
