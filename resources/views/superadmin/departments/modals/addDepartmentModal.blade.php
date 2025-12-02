{{-- 
-------------------------------------------------------------------------------
* File: resources/views/superadmin/departments/modals/addDepartmentModal.blade.php
* Description: Modal for adding a new department (Superadmin)
-------------------------------------------------------------------------------
--}}

<div class="modal fade sv-superadmin-dept-modal" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form id="addDepartmentForm" method="POST" class="modal-content">
      @csrf

      <style>
        #addDepartmentModal { --sv-bg:#FAFAFA; --sv-bdr:#E3E3E3; --sv-acct:#EE6F57; --sv-danger:#CB3737; }
        #addDepartmentModal .modal-header { padding:.85rem 1rem; border-bottom:1px solid var(--sv-bdr); background:#fff; }
        #addDepartmentModal .modal-title { font-weight:600; font-size:1rem; display:inline-flex; align-items:center; gap:.5rem; }
        #addDepartmentModal .modal-title i, #addDepartmentModal .modal-title svg { width:1.05rem; height:1.05rem; stroke:var(--sv-text-muted,#777); }
        #addDepartmentModal .modal-content { border-radius:16px; border:1px solid var(--sv-bdr); background:#fff; box-shadow:0 10px 30px rgba(0,0,0,.08),0 2px 12px rgba(0,0,0,.06); overflow:hidden; }
        #addDepartmentModal .input-group-text { background:var(--sv-bg); border-color:var(--sv-bdr); }
        #addDepartmentModal .form-control, #addDepartmentModal .form-select { border-radius:12px; border:1px solid var(--sv-bdr); background:#fff; }
        #addDepartmentModal .form-control:focus, #addDepartmentModal .form-select:focus { border-color:var(--sv-acct); box-shadow:0 0 0 3px rgba(238,111,87,.16); outline:none; }
        #addDepartmentModal textarea.form-control:focus { border-color:var(--sv-bdr); box-shadow:none; outline:none; background:#fff; }
        #addDepartmentModal .btn-danger { background:#fff; border:none; color:#000; transition:all .2s; box-shadow:none; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #addDepartmentModal .btn-danger:hover, #addDepartmentModal .btn-danger:focus { background:linear-gradient(135deg, rgba(255,240,235,.88), rgba(255,255,255,.46)); box-shadow:0 4px 10px rgba(204,55,55,.12); color:#CB3737; }
        #addDepartmentModal .btn-light { background:#fff; border:none; color:#6c757d; transition:all .2s; box-shadow:none; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
      </style>

      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2" id="addDepartmentModalLabel">
          <i data-feather="plus-circle"></i>
          <span>Add New Department</span>
        </h5>
      </div>

      <div class="modal-body">
        <div id="addDepartmentErrors" class="alert alert-danger d-none small mb-3" role="alert"></div>

        <div class="department-field-group mb-3">
          <label for="departmentName" class="form-label small fw-medium text-muted">Department Name</label>
          <input type="text" class="form-control form-control-sm" id="departmentName" name="name" placeholder="e.g., College of Information and Computing Sciences" required>
        </div>

        <div class="department-field-group mb-3">
          <label for="departmentCode" class="form-label small fw-medium text-muted">Department Code</label>
          <input type="text" class="form-control form-control-sm" id="departmentCode" name="code" placeholder="e.g., CICS" required>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
          <i data-feather="x"></i> Cancel
        </button>
        <button type="submit" class="btn btn-danger" id="addDepartmentSubmit">
          <i data-feather="plus"></i> Create
        </button>
      </div>
    </form>
  </div>
  </div>
