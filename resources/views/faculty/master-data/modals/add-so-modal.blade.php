{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/master-data/modals/add-so-modal.blade.php
* Description: Modal for adding a new Student Outcome (SO) – aligned UI/UX
-------------------------------------------------------------------------------
📜 Log:
[2025-01-20] Copied from admin master-data for Faculty module
-------------------------------------------------------------------------------
--}}

<div class="modal fade" id="addSoModal" tabindex="-1" aria-labelledby="addSoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="addSoForm" action="{{ route('faculty.so.store') }}" method="POST" class="modal-content">
      @csrf

      <div class="modal-header">
        <h5 class="modal-title fw-semibold" id="addSoModalLabel">Add Student Outcome</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="small text-muted mb-2">
          Code (<strong>SO1, SO2, …</strong>) and position are assigned automatically.
        </div>

        <div class="mb-3">
          <label for="addSoDescription" class="form-label small fw-medium text-muted">Description</label>
          <textarea id="addSoDescription"
                    name="description"
                    class="form-control"
                    rows="5"
                    placeholder="Describe the student outcome clearly and succinctly."
                    required></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger">
          <i data-feather="plus"></i> Create SO
        </button>
      </div>
    </form>
  </div>
</div>