{{-- Edit ILO Modal (Faculty) --}}
<div class="modal sv-faculty-dept-modal" id="editIloModal" tabindex="-1" aria-labelledby="editIloModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form id="editIloForm" method="POST" class="modal-content" action="#">
      @csrf
      <input type="hidden" name="_method" value="PUT">
      <style>
        #editIloModal { --sv-bg:#FAFAFA; --sv-bdr:#E3E3E3; --sv-acct:#EE6F57; --sv-danger:#CB3737; }
        #editIloModal .modal-header{ padding:.85rem 1rem; border-bottom:1px solid var(--sv-bdr); background:#fff; }
        #editIloModal .modal-title{ font-weight:600; font-size:1rem; display:inline-flex; align-items:center; gap:.5rem; }
        #editIloModal .modal-content{ border-radius:16px; border:1px solid var(--sv-bdr); background:#fff; box-shadow:0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06); overflow:hidden; }
        #editIloModal .form-control, #editIloModal .form-select{ border-radius:12px; border:1px solid var(--sv-bdr); background:#fff; }
        #editIloModal .form-label{ margin-bottom:.35rem; font-size:.8rem; letter-spacing:.02em; }
        #editIloModal .form-control.form-control-sm,
        #editIloModal .form-select.form-select-sm,
        #editIloModal textarea.form-control.form-control-sm { font-size:.875rem; line-height:1.4; padding:.35rem .75rem; }
        #editIloModal textarea.form-control.form-control-sm{ resize:vertical; }
        #editIloModal .form-control:focus, #editIloModal .form-select:focus{ border-color: var(--sv-acct); box-shadow:0 0 0 3px rgba(238,111,87,.16); }
        #editIloModal .btn-danger{ background:#fff; border:none; color:#000; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #editIloModal .btn-danger:hover, #editIloModal .btn-danger:focus{ background:linear-gradient(135deg, rgba(255,240,235,.88), rgba(255,255,255,.46)); box-shadow:0 4px 10px rgba(204,55,55,.12); color:#CB3737; }
        #editIloModal .btn-light{ background:#fff; border:none; color:#6c757d; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #editIloModal .btn-light:hover, #editIloModal .btn-light:focus{ background:linear-gradient(135deg, rgba(220,220,220,.88), rgba(240,240,240,.46)); box-shadow:0 4px 10px rgba(108,117,125,.12); color:#495057; }
      </style>

      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2" id="editIloModalLabel">
          <i data-feather="edit-3" style="width:1.05rem;height:1.05rem;"></i>
          <span>Edit Intended Learning Outcome</span>
        </h5>
      </div>
      <div class="modal-body">
        <div id="editIloErrors" class="alert alert-danger d-none small mb-3" role="alert"></div>
        <div class="mb-3">
          <label for="editIloTitle" class="form-label small fw-medium text-muted">Title (optional)</label>
          <input type="text" class="form-control form-control-sm" id="editIloTitle" name="title" maxlength="255" placeholder="e.g., Identify fundamental concepts of data structures" />
        </div>
        <div class="mb-0">
          <label for="editIloDescription" class="form-label small fw-medium text-muted">Description</label>
          <textarea class="form-control form-control-sm" id="editIloDescription" name="description" rows="4" maxlength="2000" placeholder="Describe the ILO" required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i data-feather="x"></i> Cancel</button>
        <button type="submit" class="btn btn-danger" id="editIloSubmit"><i data-feather="save"></i> Update</button>
      </div>
      <style>
        #editIloModal.modal-static .modal-dialog { transform: scale(1.02); transition: transform .2s ease-in-out; }
        #editIloModal.modal-static .modal-content { box-shadow: 0 8px 24px rgba(0,0,0,.12), 0 4px 12px rgba(0,0,0,.08); }
      </style>
    </form>
  </div>
</div>
