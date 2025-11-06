{{-- Edit SDG Modal (Faculty) --}}
<div class="modal sv-faculty-dept-modal" id="editSdgModal" tabindex="-1" aria-labelledby="editSdgModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form id="editSdgForm" method="POST" class="modal-content" action="#">
      @csrf
      <input type="hidden" name="_method" value="PUT">
      <style>
        #editSdgModal { --sv-bg:#FAFAFA; --sv-bdr:#E3E3E3; --sv-acct:#EE6F57; --sv-danger:#CB3737; }
        #editSdgModal .modal-header{ padding:.85rem 1rem; border-bottom:1px solid var(--sv-bdr); background:#fff; }
        #editSdgModal .modal-title{ font-weight:600; font-size:1rem; display:inline-flex; align-items:center; gap:.5rem; }
        #editSdgModal .modal-content{ border-radius:16px; border:1px solid var(--sv-bdr); background:#fff; box-shadow:0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06); overflow:hidden; }
        #editSdgModal .form-control, #editSdgModal .form-select{ border-radius:12px; border:1px solid var(--sv-bdr); background:#fff; }
        /* Align typography and spacing across Title and Description fields */
        #editSdgModal .form-label{ margin-bottom:.35rem; font-size:.8rem; letter-spacing:.02em; }
        #editSdgModal .form-control.form-control-sm,
        #editSdgModal .form-select.form-select-sm,
        #editSdgModal textarea.form-control.form-control-sm {
          font-size:.875rem; /* match small input */
          line-height:1.4;
          padding:.35rem .75rem;
        }
        #editSdgModal textarea.form-control.form-control-sm{ resize:vertical; }
        #editSdgModal .form-control:focus, #editSdgModal .form-select:focus{ border-color: var(--sv-acct); box-shadow:0 0 0 3px rgba(238,111,87,.16); }
        #editSdgModal .btn-danger{ background:#fff; border:none; color:#000; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #editSdgModal .btn-danger:hover, #editSdgModal .btn-danger:focus{ background:linear-gradient(135deg, rgba(255,240,235,.88), rgba(255,255,255,.46)); box-shadow:0 4px 10px rgba(204,55,55,.12); color:#CB3737; }
        #editSdgModal .btn-light{ background:#fff; border:none; color:#6c757d; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #editSdgModal .btn-light:hover, #editSdgModal .btn-light:focus{ background:linear-gradient(135deg, rgba(220,220,220,.88), rgba(240,240,240,.46)); box-shadow:0 4px 10px rgba(108,117,125,.12); color:#495057; }
      </style>

      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2" id="editSdgModalLabel">
          <i data-feather="edit-3"></i>
          <span>Edit Sustainable Development Goal</span>
        </h5>
      </div>

      <div class="modal-body">
        <div id="editSdgErrors" class="alert alert-danger d-none small mb-3" role="alert"></div>

        <div class="mb-3">
          <label for="editSdgTitle" class="form-label small fw-medium text-muted">Title</label>
          <input type="text" class="form-control form-control-sm" id="editSdgTitle" name="title" required />
        </div>

        {{-- SDG has no department --}}

        <div class="mb-3">
          <label for="editSdgDescription" class="form-label small fw-medium text-muted">Description</label>
          <textarea id="editSdgDescription" name="description" class="form-control form-control-sm" rows="4" required></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i data-feather="x"></i> Cancel</button>
        <button type="submit" class="btn btn-danger" id="editSdgSubmit"><i data-feather="save"></i> Update</button>
      </div>
    </form>
  </div>
</div>
