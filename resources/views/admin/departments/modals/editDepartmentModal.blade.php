{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/departments/modals/editDepartmentModal.blade.php
* Description: Modal for editing a department (Admin version)
-------------------------------------------------------------------------------
📜 Log:
[2025-10-04] Created admin version based on superadmin edit department modal
-------------------------------------------------------------------------------
--}}

{{-- ░░░ START: Edit Department Modal ░░░ --}}
<div class="modal fade sv-appt-modal" id="editDepartmentModal" tabindex="-1" aria-labelledby="editDepartmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form id="editDepartmentForm" method="POST" class="modal-content">
      @csrf
      @method('PUT')
      <input type="hidden" id="editDepartmentId" name="id" value="">

      {{-- ░░░ START: Local styles (scoped to this modal) ░░░ --}}
      <style>
        /* Brand tokens */
        #editDepartmentModal {
          --sv-bg:   #FAFAFA;   /* light bg */
          --sv-bdr:  #E3E3E3;   /* borders */
          --sv-acct: #EE6F57;   /* accent/focus */
          --sv-danger:#CB3737;  /* primary action (danger style) */
        }
        #editDepartmentModal .modal-header {
          border-bottom: 1px solid var(--sv-bdr);
          background: var(--sv-bg);
        }
        #editDepartmentModal .sv-card {
          border: 1px solid var(--sv-bdr);
          background: #fff;
          border-radius: .75rem;
        }
        #editDepartmentModal .sv-section-title {
          font-size: .8rem;
          letter-spacing: .02em;
          color: #6c757d;
        }
        #editDepartmentModal .input-group-text {
          background: var(--sv-bg);
          border-color: var(--sv-bdr);
        }
        #editDepartmentModal .form-control,
        #editDepartmentModal .form-select {
          border-color: var(--sv-bdr);
        }
        #editDepartmentModal .form-control:focus,
        #editDepartmentModal .form-select:focus {
          border-color: var(--sv-bdr);
          box-shadow: none;
          outline: none;
        }
        /* Remove browser default yellow/orange focus effects */
        #editDepartmentModal textarea.form-control:focus {
          border-color: var(--sv-bdr);
          box-shadow: none;
          outline: none;
          background-color: #fff;
        }
        #editDepartmentModal .btn-danger {
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
        #editDepartmentModal .btn-danger:hover,
        #editDepartmentModal .btn-danger:focus {
          background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(204, 55, 55, 0.12);
          color: #CB3737;
        }
        #editDepartmentModal .btn-danger:hover i,
        #editDepartmentModal .btn-danger:hover svg,
        #editDepartmentModal .btn-danger:focus i,
        #editDepartmentModal .btn-danger:focus svg {
          stroke: #CB3737;
        }
        /* Cancel button styling */
        #editDepartmentModal .btn-light {
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
        #editDepartmentModal .btn-light:hover,
        #editDepartmentModal .btn-light:focus {
          background: linear-gradient(135deg, rgba(220, 220, 220, 0.88), rgba(240, 240, 240, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(108, 117, 125, 0.12);
          color: #495057;
        }
        #editDepartmentModal .btn-light:hover i,
        #editDepartmentModal .btn-light:hover svg,
        #editDepartmentModal .btn-light:focus i,
        #editDepartmentModal .btn-light:focus svg {
          stroke: #495057;
        }
        #editDepartmentModal .sv-divider {
          height: 1px;
          background: var(--sv-bdr);
          margin: .75rem 0;
        }
      </style>
      {{-- ░░░ END: Local styles ░░░ --}}

      {{-- ░░░ START: Header ░░░ --}}
      <div class="modal-header">
        <h5 class="modal-title fw-semibold" id="editDepartmentModalLabel">Edit Department</h5>
      </div>
      {{-- ░░░ END: Header ░░░ --}}

      {{-- ░░░ START: Body ░░░ --}}
      <div class="modal-body">
        {{-- Inline error box (filled by JS on 422) --}}
        <div id="editDepartmentErrors" class="alert alert-danger d-none small mb-3" role="alert"></div>

        <div class="mb-3">
          <label for="editDepartmentName" class="form-label small fw-medium text-muted">Department Name</label>
          <input type="text" class="form-control form-control-sm" id="editDepartmentName" name="name" placeholder="e.g., College of Information and Computing Sciences" required>
        </div>

        <div class="mb-3">
          <label for="editDepartmentCode" class="form-label small fw-medium text-muted">Department Code</label>
          <input type="text" class="form-control form-control-sm" id="editDepartmentCode" name="code" placeholder="e.g., CICS" required>
        </div>
      </div>
      {{-- ░░░ END: Body ░░░ --}}

      {{-- ░░░ START: Footer ░░░ --}}
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
          <i data-feather="x"></i> Cancel
        </button>
        <button type="submit" class="btn btn-danger" id="editDepartmentSubmit">
          <i data-feather="save"></i> Update
        </button>
      </div>
      {{-- ░░░ END: Footer ░░░ --}}
    </form>
  </div>
</div>
{{-- ░░░ END: Edit Department Modal ░░░ --}}