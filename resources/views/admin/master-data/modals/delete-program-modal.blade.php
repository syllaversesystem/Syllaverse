{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/master-data/modals/delete-program-modal.blade.php
* Description: Delete confirmation modal for Programs (AJAX-ready)
-------------------------------------------------------------------------------
📜 Log:
[2025-08-17] Added form id=deleteProgramForm for AJAX submit.
-------------------------------------------------------------------------------
--}}
<div class="modal fade" id="deleteProgramModal" tabindex="-1" aria-labelledby="deleteProgramModalLabel" aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="deleteProgramForm" action="{{ route('admin.programs.destroy', 0) }}" method="POST" class="delete-program-form">
        @csrf
        @method('DELETE')

        <div class="modal-header">
          <h5 class="modal-title fw-semibold" id="deleteProgramModalLabel">
            Manage <span id="programDeleteLabel">Program</span>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <p class="mb-3">
            What would you like to do with <strong id="programDeleteWhat">this program</strong>?
          </p>
          
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="action_type" id="removeProgram" value="remove" checked>
              <label class="form-check-label" for="removeProgram">
                <strong>Remove</strong> - Hide from listings but keep data (can be restored later)
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="action_type" id="deleteProgram" value="delete">
              <label class="form-check-label" for="deleteProgram">
                <strong>Delete</strong> - Permanently delete from database (cannot be undone)
              </label>
            </div>
          </div>

          <div class="alert alert-warning alert-sm" id="deleteWarning" style="display: none;">
            <i data-feather="alert-triangle" class="me-1"></i>
            <strong>Warning:</strong> Permanent deletion cannot be undone!
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger" id="confirmActionBtn">
            <i data-feather="minus-circle"></i> Remove
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const removeRadio = document.getElementById('removeProgram');
  const deleteRadio = document.getElementById('deleteProgram');
  const confirmBtn = document.getElementById('confirmActionBtn');
  const deleteWarning = document.getElementById('deleteWarning');

  function updateButtonAndWarning() {
    if (deleteRadio.checked) {
      confirmBtn.innerHTML = '<i data-feather="trash"></i> Delete Permanently';
      confirmBtn.className = 'btn btn-danger';
      deleteWarning.style.display = 'block';
    } else {
      confirmBtn.innerHTML = '<i data-feather="minus-circle"></i> Remove';
      confirmBtn.className = 'btn btn-warning';
      deleteWarning.style.display = 'none';
    }
    // Re-initialize feather icons
    if (typeof feather !== 'undefined') {
      setTimeout(() => feather.replace(), 10);
    }
  }

  removeRadio.addEventListener('change', updateButtonAndWarning);
  deleteRadio.addEventListener('change', updateButtonAndWarning);
  
  // Initialize on modal show
  document.getElementById('deleteProgramModal').addEventListener('shown.bs.modal', function() {
    updateButtonAndWarning();
  });
});
</script>
