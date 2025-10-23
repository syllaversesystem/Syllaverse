{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/master-data/modals/add-ilo-modal.blade.php
* Description: Add ILO (AJAX) – code/position auto-set
-------------------------------------------------------------------------------
📜 Log:
[2025-01-20] Copied from admin master-data for Faculty module
-------------------------------------------------------------------------------
--}}

<div class="modal fade" id="addIloModal" tabindex="-1" aria-labelledby="addIloLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="addIloForm" action="{{ route('faculty.ilo.store') }}" method="POST" class="modal-content">
      @csrf
      <input type="hidden" name="course_id" id="addIloCourseId">

      <div class="modal-header">
        <h5 class="modal-title fw-semibold" id="addIloLabel">Add ILO</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="small text-muted mb-2">
          Code (ILO1, ILO2, …) and position are assigned automatically.
        </div>
        <div class="mb-3">
          <label class="form-label small fw-medium text-muted" for="addIloDescription">Description</label>
          <textarea class="form-control" id="addIloDescription" name="description" rows="5" required></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger">
          <i data-feather="plus"></i> Create ILO
        </button>
      </div>
    </form>
  </div>
</div>