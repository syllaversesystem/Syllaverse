{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/departments/modals/deleteDepartmentModal.blade.php
* Description: Delete confirmation modal for departments with brand-aligned UI styling (Admin version)
-------------------------------------------------------------------------------
📜 Log:
[2025-10-04] Reverted to original superadmin modal structure with admin routes
-------------------------------------------------------------------------------
--}}

{{-- ░░░ START: Delete Department Modal ░░░ --}}
<div class="modal fade sv-appt-modal" id="deleteDepartmentModal" tabindex="-1" aria-labelledby="deleteDepartmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content">
      {{-- ░░░ START: Local styles (scoped to this modal) ░░░ --}}
      <style>
        /* Brand tokens */
        #deleteDepartmentModal {
          --sv-bg:   #FAFAFA;   /* light bg */
          --sv-bdr:  #E3E3E3;   /* borders */
          --sv-acct: #EE6F57;   /* accent/focus */
          --sv-danger:#CB3737;  /* danger red */
        }
        #deleteDepartmentModal .modal-header {
          border-bottom: 1px solid var(--sv-bdr);
          background: var(--sv-bg);
        }
        #deleteDepartmentModal .modal-title {
          color: var(--sv-danger);
        }
        /* Delete button styling */
        #deleteDepartmentModal .btn-danger {
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
        #deleteDepartmentModal .btn-danger:hover,
        #deleteDepartmentModal .btn-danger:focus {
          background: linear-gradient(135deg, rgba(255, 235, 235, 0.88), rgba(255, 245, 245, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(203, 55, 55, 0.15);
          color: var(--sv-danger);
        }
        #deleteDepartmentModal .btn-danger:hover i,
        #deleteDepartmentModal .btn-danger:hover svg,
        #deleteDepartmentModal .btn-danger:focus i,
        #deleteDepartmentModal .btn-danger:focus svg {
          stroke: var(--sv-danger);
        }
        /* Cancel button styling */
        #deleteDepartmentModal .btn-light {
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
        #deleteDepartmentModal .btn-light:hover,
        #deleteDepartmentModal .btn-light:focus {
          background: linear-gradient(135deg, rgba(220, 220, 220, 0.88), rgba(240, 240, 240, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(108, 117, 125, 0.12);
          color: #495057;
        }
        #deleteDepartmentModal .btn-light:hover i,
        #deleteDepartmentModal .btn-light:hover svg,
        #deleteDepartmentModal .btn-light:focus i,
        #deleteDepartmentModal .btn-light:focus svg {
          stroke: #495057;
        }
        #deleteDepartmentModal .alert-warning {
          background: rgba(255, 245, 235, 0.9);
          border: 1px solid rgba(255, 193, 7, 0.3);
          color: #856404;
        }
        #deleteDepartmentModal .alert-danger {
          background: rgba(255, 235, 235, 0.9);
          border: 1px solid rgba(220, 53, 69, 0.3);
          color: #721c24;
        }
        #deleteDepartmentModal .alert-success {
          background: rgba(235, 255, 235, 0.9);
          border: 1px solid rgba(40, 167, 69, 0.3);
          color: #155724;
        }
      </style>
      {{-- ░░░ END: Local styles ░░░ --}}

      {{-- ░░░ START: Header ░░░ --}}
      <div class="modal-header border-0 pb-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      {{-- ░░░ END: Header ░░░ --}}

      {{-- ░░░ START: Body ░░░ --}}
      <div class="modal-body">
        {{-- Department Information Card --}}
        <div class="text-center mb-4">
          <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 rounded-circle mb-3" style="width: 64px; height: 64px;">
            <i data-feather="trash-2" class="text-danger" style="width: 28px; height: 28px;"></i>
          </div>
          <h6 class="fw-semibold mb-2">Delete Department</h6>
          <p class="text-muted mb-0">Are you sure you want to permanently delete this department?</p>
        </div>

        {{-- Department Details --}}
        <div class="bg-light rounded-3 p-3 mb-4">
          <div class="small text-muted mb-1">You are about to delete:</div>
          <div class="fw-semibold mb-1" id="deleteDepartmentName">Loading...</div>
          <div class="small text-muted">Code: <span id="deleteDepartmentCode" class="fw-medium">Loading...</span></div>
        </div>

        {{-- Warning Information --}}
        <div class="alert alert-warning border-0 mb-0" style="background: rgba(255, 193, 7, 0.1);">
          <div class="d-flex align-items-start gap-3">
            <i data-feather="alert-triangle" class="text-warning flex-shrink-0 mt-1" style="width: 18px; height: 18px;"></i>
            <div class="small">
              <div class="fw-medium text-dark mb-2">This action cannot be undone</div>
              <ul class="list-unstyled mb-0 text-muted">
                <li class="mb-1">• All programs in this department will be removed</li>
                <li class="mb-1">• All courses in those programs will be removed</li>
                <li class="mb-0">• User syllabi will remain safe and unaffected</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      {{-- ░░░ END: Body ░░░ --}}

      {{-- ░░░ START: Footer ░░░ --}}
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
          <i data-feather="x"></i> Cancel
        </button>
        <form id="deleteDepartmentForm" method="POST" class="d-inline">
          @csrf
          @method('DELETE')
          <input type="hidden" id="deleteDepartmentId" name="id" value="">
          <button type="submit" id="deleteDepartmentSubmit" class="btn btn-danger">
            <i data-feather="trash-2"></i> Delete
          </button>
        </form>
      </div>
      {{-- ░░░ END: Footer ░░░ --}}
    </div>
  </div>
</div>
{{-- ░░░ END: Delete Department Modal ░░░ --}}