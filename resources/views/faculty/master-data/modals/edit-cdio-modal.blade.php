{{-- Edit CDIO Modal (Faculty) --}}
<div class="modal sv-faculty-dept-modal" id="editCdioModal" tabindex="-1" aria-labelledby="editCdioModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form id="editCdioForm" method="POST" class="modal-content" action="#">
      @csrf
      <input type="hidden" name="_method" value="PUT">
      <style>
        #editCdioModal { --sv-bg:#FAFAFA; --sv-bdr:#E3E3E3; --sv-acct:#EE6F57; --sv-danger:#CB3737; }
        #editCdioModal .modal-header{ padding:.85rem 1rem; border-bottom:1px solid var(--sv-bdr); background:#fff; }
        #editCdioModal .modal-title{ font-weight:600; font-size:1rem; display:inline-flex; align-items:center; gap:.5rem; }
        #editCdioModal .modal-title i, #editCdioModal .modal-title svg { width:1.05rem; height:1.05rem; stroke: var(--sv-text-muted,#777); }
        #editCdioModal .modal-content{ border-radius:16px; border:1px solid var(--sv-bdr); background:#fff; box-shadow:0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06); overflow:hidden; }
        #editCdioModal .form-control, #editCdioModal .form-select{ border-radius:12px; border:1px solid var(--sv-bdr); background:#fff; }
        #editCdioModal .form-label{ margin-bottom:.35rem; font-size:.8rem; letter-spacing:.02em; }
        #editCdioModal .form-control.form-control-sm,
        #editCdioModal .form-select.form-select-sm,
        #editCdioModal textarea.form-control.form-control-sm { font-size:.875rem; line-height:1.4; padding:.35rem .75rem; }
        #editCdioModal textarea.form-control.form-control-sm{ resize:vertical; }
        #editCdioModal .form-control:focus, #editCdioModal .form-select:focus{ border-color: var(--sv-acct); box-shadow:0 0 0 3px rgba(238,111,87,.16); }
        #editCdioModal .btn-danger{ background:#fff; border:none; color:#000; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #editCdioModal .btn-danger i, #editCdioModal .btn-danger svg { stroke:#000; }
        #editCdioModal .btn-danger:hover, #editCdioModal .btn-danger:focus{ background:linear-gradient(135deg, rgba(235,235,235,.88), rgba(250,250,250,.46)); box-shadow:0 4px 10px rgba(0,0,0,.10); color:#000; }
        #editCdioModal .btn-danger:hover i, #editCdioModal .btn-danger:hover svg, #editCdioModal .btn-danger:focus i, #editCdioModal .btn-danger:focus svg { stroke:#000; }
        #editCdioModal .btn-light{ background:#fff; border:none; color:#000; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #editCdioModal .btn-light i, #editCdioModal .btn-light svg { stroke:#000; }
        #editCdioModal .btn-light:hover, #editCdioModal .btn-light:focus{ background:linear-gradient(135deg, rgba(225,225,225,.88), rgba(240,240,240,.46)); box-shadow:0 4px 10px rgba(0,0,0,.08); color:#000; }
        #editCdioModal .btn-light:hover i, #editCdioModal .btn-light:hover svg, #editCdioModal .btn-light:focus i, #editCdioModal .btn-light:focus svg { stroke:#000; }
      </style>

      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2" id="editCdioModalLabel">
          <i data-feather="edit-3"></i>
          <span>Edit CDIO</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="editCdioErrors" class="alert alert-danger d-none small mb-3" role="alert"></div>
        <div class="mb-3">
          <label for="editCdioTitle" class="form-label small fw-medium text-muted">Title</label>
          <input type="text" class="form-control form-control-sm" id="editCdioTitle" name="title" maxlength="255" required />
        </div>
        <div class="mb-0">
          <label for="editCdioDescription" class="form-label small fw-medium text-muted">Description</label>
          <textarea class="form-control form-control-sm" id="editCdioDescription" name="description" rows="4" maxlength="2000" required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i data-feather="x"></i> Cancel</button>
        <button type="submit" class="btn btn-danger" id="editCdioSubmit"><i data-feather="save"></i> Update</button>
      </div>
      <style>
        #editCdioModal.modal-static .modal-dialog { transform: scale(1.02); transition: transform .2s ease-in-out; }
        #editCdioModal.modal-static .modal-content { box-shadow: 0 8px 24px rgba(0,0,0,.12), 0 4px 12px rgba(0,0,0,.08); }
      </style>
    </form>
  </div>
</div>
