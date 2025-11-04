{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/master-data/modals/add-so-modal.blade.php
* Description: Add Student Outcome (SO) modal – UI mirrors Faculty Departments modal
-------------------------------------------------------------------------------
📜 Log:
[2025-11-04] Created add SO modal matching department modal styling
-------------------------------------------------------------------------------
--}}

{{-- ░░░ START: Add SO Modal ░░░ --}}
<div class="modal sv-faculty-dept-modal" id="addSoModal" tabindex="-1" aria-labelledby="addSoModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form id="addSoForm" method="POST" class="modal-content" action="{{ url('/faculty/master-data/so') }}">
      @csrf

      {{-- ░░░ START: Local styles (scoped to this modal) ░░░ --}}
  <style>
        #addSoModal {
          --sv-bg:   #FAFAFA;  /* light bg */
          --sv-bdr:  #E3E3E3;  /* borders */
          --sv-acct: #EE6F57;  /* accent/focus */
          --sv-danger:#CB3737; /* primary action */
        }
        #addSoModal .modal-header {
          padding: .85rem 1rem;
          border-bottom: 1px solid var(--sv-bdr);
          background: #fff;
        }
        #addSoModal .modal-title { font-weight: 600; font-size: 1rem; display:inline-flex; align-items:center; gap:.5rem; }
        #addSoModal .modal-title i, #addSoModal .modal-title svg { width:1.05rem; height:1.05rem; stroke: var(--sv-text-muted,#777); }
        #addSoModal .modal-content { border-radius:16px; border:1px solid var(--sv-bdr); background:#fff; box-shadow:0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06); overflow:hidden; }
        #addSoModal .form-control, #addSoModal .form-select { border-radius:12px; border:1px solid var(--sv-bdr); background:#fff; }
        /* Align typography and spacing across Title and Description fields */
        #addSoModal .form-label { margin-bottom: .35rem; font-size: .8rem; letter-spacing: .02em; }
        #addSoModal .form-control.form-control-sm,
        #addSoModal .form-select.form-select-sm,
        #addSoModal textarea.form-control.form-control-sm {
          font-size: .875rem; /* same as small input */
          line-height: 1.4;
          padding: .35rem .75rem;
        }
        #addSoModal textarea.form-control.form-control-sm {
          /* Ensure textarea typography matches Title input */
          resize: vertical;
        }
        #addSoModal .form-control:focus, #addSoModal .form-select:focus { border-color: var(--sv-acct); box-shadow: 0 0 0 3px rgba(238,111,87,.16); outline:none; }
        #addSoModal .btn-danger { background: #fff; border:none; color:#000; transition: all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #addSoModal .btn-danger:hover, #addSoModal .btn-danger:focus { background: linear-gradient(135deg, rgba(255,240,235,.88), rgba(255,255,255,.46)); box-shadow:0 4px 10px rgba(204,55,55,.12); color:#CB3737; }
        #addSoModal .btn-light { background:#fff; border:none; color:#6c757d; transition: all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #addSoModal .btn-light:hover, #addSoModal .btn-light:focus { background: linear-gradient(135deg, rgba(220,220,220,.88), rgba(240,240,240,.46)); box-shadow:0 4px 10px rgba(108,117,125,.12); color:#495057; }
      </style>
      {{-- ░░░ END: Local styles ░░░ --}}

      {{-- ░░░ START: Header ░░░ --}}
      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2" id="addSoModalLabel">
          <i data-feather="plus-circle"></i>
          <span>Add Student Outcome</span>
        </h5>
      </div>
      {{-- ░░░ END: Header ░░░ --}}

      {{-- ░░░ START: Body ░░░ --}}
      <div class="modal-body">
        <div id="addSoErrors" class="alert alert-danger d-none small mb-3" role="alert"></div>

        <div class="mb-3">
          <label for="soTitle" class="form-label small fw-medium text-muted">Title (optional)</label>
          <input type="text" class="form-control form-control-sm" id="soTitle" name="title" placeholder="e.g., Apply knowledge of computing" />
        </div>

        @if(!empty($showDepartmentFilter))
        <div class="mb-3">
          <label for="soDepartment" class="form-label small fw-medium text-muted">Department</label>
          <select id="soDepartment" name="department_id" class="form-select form-select-sm" required>
            <option value="" disabled selected>Select department</option>
            @foreach(($departments ?? collect()) as $dept)
              <option value="{{ $dept->id }}">{{ $dept->name }}</option>
            @endforeach
          </select>
        </div>
        @endif

        <div class="mb-3">
          <label for="soDescription" class="form-label small fw-medium text-muted">Description</label>
          <textarea id="soDescription" name="description" class="form-control form-control-sm" rows="4" placeholder="Describe the Student Outcome" required></textarea>
        </div>
      </div>
      {{-- ░░░ END: Body ░░░ --}}

      {{-- ░░░ START: Footer ░░░ --}}
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i data-feather="x"></i> Cancel</button>
        <button type="submit" class="btn btn-danger" id="addSoSubmit"><i data-feather="plus"></i> Create</button>
      </div>
      {{-- ░░░ END: Footer ░░░ --}}
    </form>
  </div>
</div>
{{-- ░░░ END: Add SO Modal ░░░ --}}
 
