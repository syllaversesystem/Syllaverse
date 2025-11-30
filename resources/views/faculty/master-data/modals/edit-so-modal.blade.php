{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/master-data/modals/edit-so-modal.blade.php
* Description: Edit Student Outcome (SO) modal – matches Add modal/Departments UI
-------------------------------------------------------------------------------
📜 Log:
[2025-11-04] Created edit SO modal with consistent styling and fields
-------------------------------------------------------------------------------
--}}

<div class="modal sv-faculty-dept-modal" id="editSoModal" tabindex="-1" aria-labelledby="editSoModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form id="editSoForm" method="POST" class="modal-content" action="#">
      @csrf
      <input type="hidden" name="_method" value="PUT">

  <style>
        #editSoModal {
          --sv-bg:   #FAFAFA;
          --sv-bdr:  #E3E3E3;
          --sv-acct: #EE6F57;
          --sv-danger:#CB3737;
        }
        #editSoModal .modal-header { padding:.85rem 1rem; border-bottom:1px solid var(--sv-bdr); background:#fff; }
        #editSoModal .modal-title { font-weight:600; font-size:1rem; display:inline-flex; align-items:center; gap:.5rem; }
        #editSoModal .modal-title svg { width:1.05rem; height:1.05rem; stroke: var(--sv-text-muted,#777); }
        #editSoModal .modal-content { border-radius:16px; border:1px solid var(--sv-bdr); background:#fff; box-shadow:0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06); overflow:hidden; }
        #editSoModal .form-control, #editSoModal .form-select { border-radius:12px; border:1px solid var(--sv-bdr); background:#fff; }
        #editSoModal .form-control:focus, #editSoModal .form-select:focus { border-color: var(--sv-acct); box-shadow: 0 0 0 3px rgba(238,111,87,.16); outline:none; }
        #editSoModal .form-label { margin-bottom:.35rem; font-size:.8rem; letter-spacing:.02em; }
        #editSoModal .form-control.form-control-sm, #editSoModal .form-select.form-select-sm, #editSoModal textarea.form-control.form-control-sm { font-size:.875rem; line-height:1.4; padding:.35rem .75rem; }
        /* Match Add modal button UI */
        #editSoModal .btn-danger { background:#fff; border:none; color:#000; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #editSoModal .btn-danger i, #editSoModal .btn-danger svg { stroke:#000; }
        #editSoModal .btn-danger:hover, #editSoModal .btn-danger:focus { background:linear-gradient(135deg, rgba(235,235,235,.88), rgba(250,250,250,.46)); box-shadow:0 4px 10px rgba(0,0,0,.10); color:#000; }
        #editSoModal .btn-danger:hover i, #editSoModal .btn-danger:hover svg, #editSoModal .btn-danger:focus i, #editSoModal .btn-danger:focus svg { stroke:#000; }
        #editSoModal .btn-light { background:#fff; border:none; color:#000; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #editSoModal .btn-light i, #editSoModal .btn-light svg { stroke:#000; }
        #editSoModal .btn-light:hover, #editSoModal .btn-light:focus { background:linear-gradient(135deg, rgba(225,225,225,.88), rgba(240,240,240,.46)); box-shadow:0 4px 10px rgba(0,0,0,.08); color:#000; }
        #editSoModal .btn-light:hover i, #editSoModal .btn-light:hover svg, #editSoModal .btn-light:focus i, #editSoModal .btn-light:focus svg { stroke:#000; }
      </style>

      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2" id="editSoModalLabel">
          <i data-feather="edit-3"></i>
          <span>Edit Student Outcome</span>
        </h5>
      </div>

      <div class="modal-body">
        <div id="editSoErrors" class="alert alert-danger d-none small mb-3" role="alert"></div>

        <div class="mb-3">
          <label for="editSoTitle" class="form-label small fw-medium text-muted">Title (optional)</label>
          <input type="text" class="form-control form-control-sm" id="editSoTitle" name="title" placeholder="e.g., Apply knowledge of computing" />
        </div>

        @if(!empty($showDepartmentFilter))
        <div class="mb-3">
          <label for="editSoDepartment" class="form-label small fw-medium text-muted">Department</label>
          <select id="editSoDepartment" name="department_id" class="form-select form-select-sm">
            @foreach(($departments ?? collect()) as $dept)
              <option value="{{ $dept->id }}">{{ $dept->name }}</option>
            @endforeach
          </select>
        </div>
        @endif

        <div class="mb-3">
          <label for="editSoDescription" class="form-label small fw-medium text-muted">Description</label>
          <textarea id="editSoDescription" name="description" class="form-control form-control-sm" rows="4" placeholder="Describe the Student Outcome" required></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i data-feather="x"></i> Cancel</button>
        <button type="submit" class="btn btn-danger" id="editSoSubmit"><i data-feather="save"></i> Update</button>
      </div>
    </form>
  </div>
  </div>
</div>
 
