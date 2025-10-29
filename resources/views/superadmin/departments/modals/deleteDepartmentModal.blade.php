{{-- 
-------------------------------------------------------------------------------
* File: resources/views/superadmin/departments/modals/deleteDepartmentModal.blade.php
* Description: Delete confirmation modal for departments with brand-aligned UI styling
-------------------------------------------------------------------------------
📜 Log:
[2025-10-02] Updated to match add department modal UI design - brand colors, improved styling, glass morphism buttons.
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
      <div class="modal-header">
        <h5 class="modal-title fw-semibold d-flex align-items-center gap-2" id="deleteDepartmentModalLabel">
          <i data-feather="trash-2"></i> Confirm Delete
        </h5>
      </div>
      {{-- ░░░ END: Header ░░░ --}}

      {{-- ░░░ START: Body ░░░ --}}
      <div class="modal-body">
        <div class="alert alert-danger d-flex align-items-start gap-3 mb-4" role="alert">
          <i data-feather="alert-triangle" class="flex-shrink-0 mt-1" style="width: 1.2rem; height: 1.2rem;"></i>
          <div>
            <div class="fw-semibold mb-2">Permanent Deletion Warning</div>
            <p class="mb-2 small">This action will permanently delete the department and cannot be undone.</p>
            <div class="small mb-2">
              <p class="mb-1">
                <i data-feather="layers" class="me-1" style="width: 0.9rem; height: 0.9rem;"></i>
                <strong>All programs within this department will be deleted.</strong>
              </p>
              <p class="mb-1">
                <i data-feather="book" class="me-1" style="width: 0.9rem; height: 0.9rem;"></i>
                <strong>All courses within those programs will be deleted.</strong>
              </p>
            </div>
            <div class="alert alert-success py-2 px-3 mb-0 small" role="alert">
              <i data-feather="shield-check" class="me-1" style="width: 0.9rem; height: 0.9rem;"></i>
              <strong>User syllabi will remain safe</strong> and will not be affected by this deletion.
            </div>
          </div>
        </div>
        
        <div class="border rounded p-3 bg-light">
          <div class="small text-muted mb-1">You are about to delete:</div>
          <div class="fw-medium"><span id="deleteDepartmentName">Loading...</span></div>
          <div class="small text-muted">Code: <span id="deleteDepartmentCode" class="fw-medium">Loading...</span></div>
        </div>
      </div>
      {{-- ░░░ END: Body ░░░ --}}

      {{-- ░░░ START: Footer ░░░ --}}
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
          <i data-feather="x"></i> Cancel
        </button>
        <form id="deleteDepartmentForm" method="POST" action="" class="d-inline">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">
            <i data-feather="trash-2"></i> Delete
          </button>
        </form>
      </div>
      {{-- ░░░ END: Footer ░░░ --}}
    </div>
  </div>
</div>
{{-- ░░░ END: Delete Department Modal ░░░ --}}
