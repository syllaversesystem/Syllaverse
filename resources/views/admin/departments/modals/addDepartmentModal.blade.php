{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/departments/modals/addDepartmentModal.blade.php
* Description: Modal for adding a new department (Admin version)
-------------------------------------------------------------------------------
📜 Log:
[2025-10-04] Created admin version based on superadmin add department modal
-------------------------------------------------------------------------------
--}}

{{-- ░░░ START: Add Department Modal ░░░ --}}
<div class="modal fade sv-appt-modal" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form id="addDepartmentForm" method="POST" class="modal-content">
      @csrf

      {{-- ░░░ START: Local styles (scoped to this modal) ░░░ --}}
      <style>
        /* Brand tokens */
        #addDepartmentModal {
          --sv-bg:   #FAFAFA;   /* light bg */
          --sv-bdr:  #E3E3E3;   /* borders */
          --sv-acct: #EE6F57;   /* accent/focus */
          --sv-danger:#CB3737;  /* primary action (danger style) */
        }
        #addDepartmentModal .modal-header {
          border-bottom: 1px solid var(--sv-bdr);
          background: var(--sv-bg);
        }
        #addDepartmentModal .sv-card {
          border: 1px solid var(--sv-bdr);
          background: #fff;
          border-radius: .75rem;
        }
        #addDepartmentModal .sv-section-title {
          font-size: .8rem;
          letter-spacing: .02em;
          color: #6c757d;
        }
        #addDepartmentModal .input-group-text {
          background: var(--sv-bg);
          border-color: var(--sv-bdr);
        }
        #addDepartmentModal .form-control,
        #addDepartmentModal .form-select {
          border-color: var(--sv-bdr);
        }
        #addDepartmentModal .form-control:focus,
        #addDepartmentModal .form-select:focus {
          border-color: var(--sv-bdr);
          box-shadow: none;
          outline: none;
        }
        /* Remove browser default yellow/orange focus effects */
        #addDepartmentModal textarea.form-control:focus {
          border-color: var(--sv-bdr);
          box-shadow: none;
          outline: none;
          background-color: #fff;
        }
        #addDepartmentModal .btn-danger {
          background: var(--sv-card-bg, #fff);
          border: none;
          color: #000;
          transition: all 0.2s ease-in-out;
          box-shadow: none;
          display: inline-flex;
          align-items: center;
          gap: 0.5rem;
          padding: 0.5rem 1rem;
          border-radius: 0.375rem;
        }
        #addDepartmentModal .btn-danger:hover,
        #addDepartmentModal .btn-danger:focus {
          background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(204, 55, 55, 0.12);
          color: #CB3737;
        }
        #addDepartmentModal .btn-danger:hover i,
        #addDepartmentModal .btn-danger:hover svg,
        #addDepartmentModal .btn-danger:focus i,
        #addDepartmentModal .btn-danger:focus svg {
          stroke: #CB3737;
        }
        #addDepartmentModal .btn-danger:active {
          background: linear-gradient(135deg, rgba(255, 230, 225, 0.98), rgba(255, 255, 255, 0.62));
          box-shadow: 0 1px 8px rgba(204, 55, 55, 0.16);
        }
        #addDepartmentModal .btn-danger:active i,
        #addDepartmentModal .btn-danger:active svg {
          stroke: #CB3737;
        }
        /* Cancel button styling */
        #addDepartmentModal .btn-light {
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
        #addDepartmentModal .btn-light:hover,
        #addDepartmentModal .btn-light:focus {
          background: linear-gradient(135deg, rgba(220, 220, 220, 0.88), rgba(240, 240, 240, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(108, 117, 125, 0.12);
          color: #495057;
        }
        #addDepartmentModal .btn-light:hover i,
        #addDepartmentModal .btn-light:hover svg,
        #addDepartmentModal .btn-light:focus i,
        #addDepartmentModal .btn-light:focus svg {
          stroke: #495057;
        }
        #addDepartmentModal .btn-light:active {
          background: linear-gradient(135deg, rgba(240, 242, 245, 0.98), rgba(255, 255, 255, 0.62));
          box-shadow: 0 1px 8px rgba(108, 117, 125, 0.16);
        }
        #addDepartmentModal .btn-light:active i,
        #addDepartmentModal .btn-light:active svg {
          stroke: #495057;
        }
        #addDepartmentModal .sv-divider {
          height: 1px;
          background: var(--sv-bdr);
          margin: .75rem 0;
        }
      </style>
      {{-- ░░░ END: Local styles ░░░ --}}

      {{-- ░░░ START: Header ░░░ --}}
      <div class="modal-header">
        <h5 class="modal-title fw-semibold" id="addDepartmentModalLabel">Add New Department</h5>
      </div>
      {{-- ░░░ END: Header ░░░ --}}

      {{-- ░░░ START: Body ░░░ --}}
      <div class="modal-body">
        {{-- Inline error box (filled by JS on 422) --}}
        <div id="addDepartmentErrors" class="alert alert-danger d-none small mb-3" role="alert"></div>

        <div class="mb-3">
          <label for="departmentName" class="form-label small fw-medium text-muted">Department Name</label>
          <input type="text" class="form-control form-control-sm" id="departmentName" name="name" placeholder="e.g., College of Information and Computing Sciences" required>
        </div>

        <div class="mb-3">
          <label for="departmentCode" class="form-label small fw-medium text-muted">Department Code</label>
          <input type="text" class="form-control form-control-sm" id="departmentCode" name="code" placeholder="e.g., CICS" required>
        </div>
      </div>
      {{-- ░░░ END: Body ░░░ --}}

      {{-- ░░░ START: Footer ░░░ --}}
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
          <i data-feather="x"></i> Cancel
        </button>
        <button type="submit" class="btn btn-danger" id="addDepartmentSubmit">
          <i data-feather="plus"></i> Create
        </button>
      </div>
      {{-- ░░░ END: Footer ░░░ --}}
    </form>
  </div>
</div>
{{-- ░░░ END: Add Department Modal ░░░ --}}