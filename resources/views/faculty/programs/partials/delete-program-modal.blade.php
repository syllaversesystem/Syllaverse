{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/programs/partials/delete-program-modal.blade.php
* Description: Confirmation modal for program deletion
-------------------------------------------------------------------------------
📜 Log:
[2025-01-16] Created faculty version based on admin delete program modal
-------------------------------------------------------------------------------
--}}

{{-- ░░░ START: Delete Program Modal ░░░ --}}
<div class="modal fade sv-faculty-program-modal" id="deleteProgramModal" tabindex="-1" aria-labelledby="deleteProgramModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content program-delete-form" style="border-radius: 16px;">

      {{-- ░░░ START: Local styles (scoped to this modal) ░░░ --}}
      <style>
        /* Brand tokens */
        #deleteProgramModal {
          --sv-bg:   #FAFAFA;   /* light bg */
          --sv-bdr:  #E3E3E3;   /* borders */
          --sv-acct: #EE6F57;   /* accent/focus */
          --sv-danger:#CB3737;  /* primary action (danger style) */
        }
        #deleteProgramModal .modal-header {
          border-bottom: 1px solid var(--sv-bdr);
          background: var(--sv-bg);
        }
        #deleteProgramModal .sv-card {
          border: 1px solid var(--sv-bdr);
          background: #fff;
          border-radius: .75rem;
        }
        #deleteProgramModal .sv-section-title {
          font-size: .8rem;
          letter-spacing: .02em;
          color: #6c757d;
        }
        #deleteProgramModal h6.fw-semibold {
          font-weight: 600;
          font-size: 1rem;
        }

        #deleteProgramModal .btn-danger {
          background: var(--sv-card-bg, #fff);
          border: none;
          color: var(--sv-danger);
          transition: all 0.2s ease-in-out;
          box-shadow: none;
          display: inline-flex;
          align-items: center;
          gap: 0.5rem;
          padding: 0.5rem 1rem;
          border-radius: 0.375rem;
        }
        #deleteProgramModal .btn-danger:hover,
        #deleteProgramModal .btn-danger:focus {
          background: linear-gradient(135deg, rgba(255, 235, 235, 0.88), rgba(255, 245, 245, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(203, 55, 55, 0.15);
          color: var(--sv-danger);
        }
        #deleteProgramModal .btn-danger:hover i,
        #deleteProgramModal .btn-danger:hover svg,
        #deleteProgramModal .btn-danger:focus i,
        #deleteProgramModal .btn-danger:focus svg {
          stroke: var(--sv-danger);
        }
        #deleteProgramModal .btn-danger:active {
          background: linear-gradient(135deg, rgba(255, 230, 225, 0.98), rgba(255, 255, 255, 0.62));
          box-shadow: 0 1px 8px rgba(203, 55, 55, 0.16);
        }
        #deleteProgramModal .btn-danger:active i,
        #deleteProgramModal .btn-danger:active svg {
          stroke: var(--sv-danger);
        }
        /* Warning button styling for remove action */
        #deleteProgramModal .btn-warning {
          background: var(--sv-card-bg, #fff);
          border: none;
          color: #856404;
          transition: all 0.2s ease-in-out;
          box-shadow: none;
          display: inline-flex;
          align-items: center;
          gap: 0.5rem;
          padding: 0.5rem 1rem;
          border-radius: 0.375rem;
        }
        #deleteProgramModal .btn-warning:hover,
        #deleteProgramModal .btn-warning:focus {
          background: linear-gradient(135deg, rgba(255, 245, 235, 0.88), rgba(255, 248, 225, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(255, 193, 7, 0.12);
          color: #856404;
        }
        #deleteProgramModal .btn-warning:hover i,
        #deleteProgramModal .btn-warning:hover svg,
        #deleteProgramModal .btn-warning:focus i,
        #deleteProgramModal .btn-warning:focus svg {
          stroke: #856404;
        }
        #deleteProgramModal .btn-warning:active {
          background: linear-gradient(135deg, rgba(255, 240, 220, 0.98), rgba(255, 248, 235, 0.62));
          box-shadow: 0 1px 8px rgba(255, 193, 7, 0.16);
        }
        #deleteProgramModal .btn-warning:active i,
        #deleteProgramModal .btn-warning:active svg {
          stroke: #856404;
        }
        /* Cancel button styling */
        #deleteProgramModal .btn-light {
          background: var(--sv-card-bg, #fff);
          border: none;
          color: #6c757d;
          transition: all 0.2s ease-in-out;
          box-shadow: none;
          display: inline-flex;
          align-items: center;
          gap: 0.5rem;
          padding: 0.5rem 1rem;
          border-radius: 0.375rem;
        }
        #deleteProgramModal .btn-light:hover,
        #deleteProgramModal .btn-light:focus {
          background: linear-gradient(135deg, rgba(220, 220, 220, 0.88), rgba(240, 240, 240, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(108, 117, 125, 0.12);
          color: #495057;
        }
        #deleteProgramModal .btn-light:hover i,
        #deleteProgramModal .btn-light:hover svg,
        #deleteProgramModal .btn-light:focus i,
        #deleteProgramModal .btn-light:focus svg {
          stroke: #495057;
        }
        #deleteProgramModal .btn-light:active {
          background: linear-gradient(135deg, rgba(240, 242, 245, 0.98), rgba(255, 255, 255, 0.62));
          box-shadow: 0 1px 8px rgba(108, 117, 125, 0.16);
        }
        #deleteProgramModal .btn-light:active i,
        #deleteProgramModal .btn-light:active svg {
          stroke: #495057;
        }
        #deleteProgramModal .alert-warning {
          background: rgba(255, 245, 235, 0.9);
          border: 1px solid rgba(255, 193, 7, 0.3);
          color: #856404;
        }
        #deleteProgramModal .alert-danger {
          background: rgba(255, 235, 235, 0.9);
          border: 1px solid rgba(220, 53, 69, 0.3);
          color: #721c24;
        }
        #deleteProgramModal .alert-success {
          background: rgba(235, 255, 235, 0.9);
          border: 1px solid rgba(40, 167, 69, 0.3);
          color: #155724;
        }
        /* Loading spinner animation */
        #deleteProgramModal .spinner {
          animation: spin 1s linear infinite;
        }
        @keyframes spin {
          from { transform: rotate(0deg); }
          to { transform: rotate(360deg); }
        }
        /* Disabled button styles */
        #deleteProgramModal .btn:disabled {
          opacity: 0.6;
          cursor: not-allowed;
        }
      </style>
      {{-- ░░░ END: Local styles ░░░ --}}

      {{-- ░░░ START: Body ░░░ --}}
      <div class="modal-body">
        {{-- Program Information Card --}}
        <div class="text-center mb-4">
          <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 rounded-circle mb-3" style="width: 64px; height: 64px;">
            <i data-feather="trash-2" class="text-danger" style="width: 28px; height: 28px;"></i>
          </div>
          <h6 class="fw-semibold mb-2">Manage Program</h6>
          <p class="text-muted mb-0">What would you like to do with this program?</p>
        </div>

        {{-- Program Details --}}
        <div class="bg-light rounded-3 p-3 mb-4">
          <div class="small text-muted mb-1">You are managing:</div>
          <div class="fw-semibold mb-1" id="deleteProgramName">Loading...</div>
          <div class="small text-muted">Code: <span id="deleteProgramCode" class="fw-medium">Loading...</span></div>
        </div>

        {{-- Action Options --}}
        <div class="mb-4">
          <div class="form-check mb-3">
            <input class="form-check-input" type="radio" name="action_type" id="removeProgram" value="remove" checked>
            <label class="form-check-label" for="removeProgram">
              <div class="fw-medium text-dark">Remove Program</div>
              <div class="small text-muted">Hide from listings but keep data (can be restored later)</div>
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="action_type" id="deleteProgram" value="delete">
            <label class="form-check-label" for="deleteProgram">
              <div class="fw-medium text-dark">Delete Program</div>
              <div class="small text-muted">Permanently delete from database (cannot be undone)</div>
            </label>
          </div>
        </div>

      </div>
      {{-- ░░░ END: Body ░░░ --}}

      {{-- ░░░ START: Footer ░░░ --}}
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
          <i data-feather="x"></i> Cancel
        </button>
        <form id="deleteProgramForm" action="{{ route('faculty.programs.destroy', 0) }}" method="POST" class="d-inline delete-program-form" data-ajax="true">
          @csrf
          @method('DELETE')
          <input type="hidden" id="deleteProgramId" name="id" value="">
          <input type="hidden" id="actionType" name="action_type" value="remove">
          <button type="submit" id="confirmActionBtn" class="btn btn-warning">
            <i data-feather="minus-circle"></i> Remove
          </button>
        </form>
      </div>
      {{-- ░░░ END: Footer ░░░ --}}
    </div>
  </div>
</div>
{{-- ░░░ END: Delete Program Modal ░░░ --}}

<script>
document.addEventListener('DOMContentLoaded', function() {
  const removeRadio = document.getElementById('removeProgram');
  const deleteRadio = document.getElementById('deleteProgram');
  const confirmBtn = document.getElementById('confirmActionBtn');
  const actionTypeInput = document.getElementById('actionType');

  function updateButtonAndWarning() {
    if (deleteRadio && deleteRadio.checked) {
      // Delete option selected
      confirmBtn.innerHTML = '<i data-feather="trash-2"></i> Delete';
      confirmBtn.className = 'btn btn-danger';
      actionTypeInput.value = 'delete';
    } else {
      // Remove option selected (default)
      confirmBtn.innerHTML = '<i data-feather="minus-circle"></i> Remove';
      confirmBtn.className = 'btn btn-warning';
      actionTypeInput.value = 'remove';
    }
    // Re-initialize feather icons
    if (typeof feather !== 'undefined') {
      setTimeout(() => feather.replace(), 10);
    }
  }

  // Event listeners for radio buttons
  if (removeRadio && deleteRadio) {
    removeRadio.addEventListener('change', updateButtonAndWarning);
    deleteRadio.addEventListener('change', updateButtonAndWarning);
  }
  
  // Initialize on modal show
  const modal = document.getElementById('deleteProgramModal');
  if (modal) {
    modal.addEventListener('shown.bs.modal', function() {
      // Reset to remove option by default
      if (removeRadio) {
        removeRadio.checked = true;
      }
      updateButtonAndWarning();
    });
  }
});
</script>