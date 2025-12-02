{{-- 
-------------------------------------------------------------------------------
* File: resources/views/superadmin/departments/modals/editDepartmentModal.blade.php
* Description: Modal for editing a department (Superadmin)
-------------------------------------------------------------------------------
--}}

<div class="modal fade sv-superadmin-dept-modal" id="editDepartmentModal" tabindex="-1" aria-labelledby="editDepartmentModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form id="editDepartmentForm" method="POST" class="modal-content">
      @csrf
      @method('PUT')
      <input type="hidden" id="editDepartmentId" name="id" value="">

      <style>
        #editDepartmentModal { --sv-bg:#FAFAFA; --sv-bdr:#E3E3E3; --sv-acct:#EE6F57; --sv-danger:#CB3737; }
        #editDepartmentModal .modal-content { border-radius:16px; border:1px solid var(--sv-bdr); background:#fff; box-shadow:0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06); overflow:hidden; }
        #editDepartmentModal .modal-header { padding:.85rem 1rem; border-bottom:1px solid var(--sv-bdr); background:#fff; }
        #editDepartmentModal .modal-title { font-weight:600; font-size:1rem; display:inline-flex; align-items:center; gap:.5rem; }
        #editDepartmentModal .form-control, #editDepartmentModal .form-select { border-radius:12px; border:1px solid var(--sv-bdr); background:#fff; }
        #editDepartmentModal .form-control:focus, #editDepartmentModal .form-select:focus { border-color:var(--sv-acct); box-shadow:0 0 0 3px rgba(238,111,87,.16); outline:none; }
        #editDepartmentModal textarea.form-control:focus { border-color:var(--sv-bdr); box-shadow:none; outline:none; background:#fff; }
        #editDepartmentModal .btn-danger { background:#fff; border:none; color:#000; transition:all .2s; box-shadow:none; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #editDepartmentModal .btn-light { background:#fff; border:none; color:#6c757d; transition:all .2s; box-shadow:none; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #editDepartmentModal .sv-divider { height:1px; background:var(--sv-bdr); margin:.75rem 0; }
      </style>

      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2" id="editDepartmentModalLabel">
          <i data-feather="edit-3"></i>
          <span>Edit Department</span>
        </h5>
      </div>

      <div class="modal-body">
        <div id="editDepartmentErrors" class="alert alert-danger d-none small mb-3" role="alert"></div>

        <div class="department-field-group mb-3">
          <label for="editDepartmentName" class="form-label small fw-medium text-muted">Department Name</label>
          <input type="text" class="form-control form-control-sm" id="editDepartmentName" name="name" placeholder="e.g., College of Information and Computing Sciences" required>
        </div>

        <div class="department-field-group mb-3">
          <label for="editDepartmentCode" class="form-label small fw-medium text-muted">Department Code</label>
          <input type="text" class="form-control form-control-sm" id="editDepartmentCode" name="code" placeholder="e.g., CICS" required>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
          <i data-feather="x"></i> Cancel
        </button>
        <button type="submit" class="btn btn-danger" id="editDepartmentSubmit">
          <i data-feather="save"></i> Update
        </button>
      </div>
    </form>
  </div>
</div>
