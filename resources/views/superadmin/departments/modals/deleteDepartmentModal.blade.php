{{-- 
-------------------------------------------------------------------------------
* File: resources/views/superadmin/departments/modals/deleteDepartmentModal.blade.php
* Description: Delete confirmation modal for departments (Superadmin)
-------------------------------------------------------------------------------
--}}

<div class="modal fade sv-superadmin-dept-modal" id="deleteDepartmentModal" tabindex="-1" aria-labelledby="deleteDepartmentModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content">
      <style>
        #deleteDepartmentModal { --sv-bg:#FAFAFA; --sv-bdr:#E3E3E3; --sv-acct:#EE6F57; --sv-danger:#CB3737; }
        #deleteDepartmentModal .modal-content { border-radius:16px; border:1px solid var(--sv-bdr); background:#fff; box-shadow:0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06); overflow:hidden; }
        #deleteDepartmentModal .modal-header { border-bottom:1px solid var(--sv-bdr); background:var(--sv-bg); }
        #deleteDepartmentModal .modal-title { color: var(--sv-danger); }
        #deleteDepartmentModal .btn-danger { background:#fff; border:none; color: var(--sv-danger); transition:all .2s; box-shadow:none; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #deleteDepartmentModal .btn-light { background:#fff; border:none; color:#6c757d; transition:all .2s; box-shadow:none; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #deleteDepartmentModal .alert-warning { background: rgba(255,245,235,.9); border:1px solid rgba(255,193,7,.3); color:#856404; }
      </style>

      <div class="modal-body">
        <div class="text-center mb-4">
          <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 rounded-circle mb-3" style="width:64px; height:64px;">
            <i data-feather="trash-2" class="text-danger" style="width:28px; height:28px;"></i>
          </div>
          <h6 class="fw-semibold mb-2">Delete Department</h6>
          <p class="text-muted mb-0">Are you sure you want to permanently delete this department?</p>
        </div>

        <div class="bg-light rounded-3 p-3 mb-4">
          <div class="small text-muted mb-1">You are about to delete:</div>
          <div class="fw-semibold mb-1" id="deleteDepartmentName">Loading...</div>
          <div class="small text-muted">Code: <span id="deleteDepartmentCode" class="fw-medium">Loading...</span></div>
        </div>

        <div class="alert alert-warning border-0 mb-0" style="background: rgba(255, 193, 7, 0.1);">
          <div class="d-flex align-items-start gap-3">
            <i data-feather="alert-triangle" class="text-warning flex-shrink-0 mt-1" style="width:18px; height:18px;"></i>
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
    </div>
  </div>
</div>
