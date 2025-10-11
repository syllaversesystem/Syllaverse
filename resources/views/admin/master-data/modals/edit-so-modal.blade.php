{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/master-data/modals/edit-so-modal.blade.php
* Description: Modal for editing a Student Outcome (SO) – display-only code, AJAX-ready
-------------------------------------------------------------------------------
📜 Log:
[2025-08-18] UI/UX align – matched Admin modals (labels, spacing, buttons).
[2025-08-18] Change – SO code is display-only; reordering controls code/position.
[2025-08-18] Route fix – form action set by JS to /admin/master-data/so/{id} (PUT via _method).
-------------------------------------------------------------------------------
--}}

<div class="modal fade" id="editSoModal" tabindex="-1" aria-labelledby="editSoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="editSoForm" action="" method="POST" class="modal-content">
      @csrf
      @method('PUT')

      {{-- Header --}}
      <div class="modal-header">
        <h5 class="modal-title fw-semibold" id="editSoModalLabel">Edit Student Outcome</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      {{-- Body --}}
      <div class="modal-body">
        {{-- SO code badge (display-only) --}}
        <div class="mb-3 d-flex align-items-center gap-2">
          <span class="small fw-medium text-muted">SO Code:</span>
          <span id="editSoCodeBadge" class="badge text-bg-light px-2 py-1">SO?</span>
        </div>
        <div class="small text-muted mb-3">
          Code and position are managed by the “Save Order” control after dragging items.
        </div>

        <div class="mb-3">
          <label for="editSoDescription" class="form-label small fw-medium text-muted">Description</label>
          <textarea id="editSoDescription"
                    name="description"
                    class="form-control"
                    rows="5"
                    placeholder="Update the student outcome description."
                    required></textarea>
        </div>
      </div>

      {{-- Footer --}}
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
          <i data-feather="save"></i> Save Changes
        </button>
      </div>
    </form>
  </div>
</div>
