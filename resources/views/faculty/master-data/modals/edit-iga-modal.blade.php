{{-- Edit IGA Modal (Faculty) --}}
<div class="modal sv-faculty-dept-modal" id="editIgaModal" tabindex="-1" aria-labelledby="editIgaModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form id="editIgaForm" method="POST" class="modal-content" action="#">
      @csrf
      <input type="hidden" name="_method" value="PUT">
      <style>
        #editIgaModal { --sv-bg:#FAFAFA; --sv-bdr:#E3E3E3; --sv-acct:#EE6F57; --sv-danger:#CB3737; }
        #editIgaModal .modal-header{ padding:.85rem 1rem; border-bottom:1px solid var(--sv-bdr); background:#fff; }
        #editIgaModal .modal-title{ font-weight:600; font-size:1rem; display:inline-flex; align-items:center; gap:.5rem; }
        #editIgaModal .modal-title i, #editIgaModal .modal-title svg { width:1.05rem; height:1.05rem; stroke: var(--sv-text-muted,#777); }
        #editIgaModal .modal-content{ border-radius:16px; border:1px solid var(--sv-bdr); background:#fff; box-shadow:0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06); overflow:hidden; }
        #editIgaModal .form-control, #editIgaModal .form-select{ border-radius:12px; border:1px solid var(--sv-bdr); background:#fff; }
        #editIgaModal .form-label{ margin-bottom:.35rem; font-size:.8rem; letter-spacing:.02em; }
        #editIgaModal .form-control.form-control-sm,
        #editIgaModal textarea.form-control.form-control-sm{ font-size:.875rem; line-height:1.4; padding:.35rem .75rem; }
        #editIgaModal textarea.form-control.form-control-sm{ resize: vertical; }
        #editIgaModal .form-control:focus, #editIgaModal .form-select:focus{ border-color: var(--sv-acct); box-shadow:0 0 0 3px rgba(238,111,87,.16); }
        #editIgaModal .btn-danger{ background:#fff; border:none; color:#000; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #editIgaModal .btn-danger i, #editIgaModal .btn-danger svg { stroke:#000; }
        #editIgaModal .btn-danger:hover, #editIgaModal .btn-danger:focus{ background:linear-gradient(135deg, rgba(235,235,235,.88), rgba(250,250,250,.46)); box-shadow:0 4px 10px rgba(0,0,0,.10); color:#000; }
        #editIgaModal .btn-danger:hover i, #editIgaModal .btn-danger:hover svg, #editIgaModal .btn-danger:focus i, #editIgaModal .btn-danger:focus svg { stroke:#000; }
        #editIgaModal .btn-light{ background:#fff; border:none; color:#000; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #editIgaModal .btn-light i, #editIgaModal .btn-light svg { stroke:#000; }
        #editIgaModal .btn-light:hover, #editIgaModal .btn-light:focus{ background:linear-gradient(135deg, rgba(225,225,225,.88), rgba(240,240,240,.46)); box-shadow:0 4px 10px rgba(0,0,0,.08); color:#000; }
        #editIgaModal .btn-light:hover i, #editIgaModal .btn-light:hover svg, #editIgaModal .btn-light:focus i, #editIgaModal .btn-light:focus svg { stroke:#000; }
      </style>

      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2" id="editIgaModalLabel">
          <i data-feather="edit-3"></i>
          <span>Edit Institutional Graduate Attribute</span>
        </h5>
      </div>

      <div class="modal-body">
        <div id="editIgaErrors" class="alert alert-danger d-none small mb-3" role="alert"></div>

        <div class="mb-3">
          <label for="editIgaTitle" class="form-label small fw-medium text-muted">Title</label>
          <input type="text" class="form-control form-control-sm" id="editIgaTitle" name="title" required />
        </div>

        <div class="mb-3">
          <label for="editIgaDescription" class="form-label small fw-medium text-muted">Description</label>
          <textarea id="editIgaDescription" name="description" class="form-control form-control-sm" rows="4" required></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i data-feather="x"></i> Cancel</button>
        <button type="submit" class="btn btn-danger" id="editIgaSubmit"><i data-feather="save"></i> Update</button>
      </div>
    </form>
  </div>
</div>
