{{-- Add CDIO Modal (Faculty) --}}
<div class="modal sv-faculty-dept-modal" id="addCdioModal" tabindex="-1" aria-labelledby="addCdioModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form id="addCdioForm" method="POST" class="modal-content" action="{{ url('/faculty/master-data/cdio') }}">
      @csrf
      <style>
        #addCdioModal { --sv-bg:#FAFAFA; --sv-bdr:#E3E3E3; --sv-acct:#EE6F57; --sv-danger:#CB3737; }
        #addCdioModal .modal-header{ padding:.85rem 1rem; border-bottom:1px solid var(--sv-bdr); background:#fff; }
        #addCdioModal .modal-title{ font-weight:600; font-size:1rem; display:inline-flex; align-items:center; gap:.5rem; }
        #addCdioModal .modal-content{ border-radius:16px; border:1px solid var(--sv-bdr); background:#fff; box-shadow:0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06); overflow:hidden; }
        #addCdioModal .form-control, #addCdioModal .form-select{ border-radius:12px; border:1px solid var(--sv-bdr); background:#fff; }
        #addCdioModal .form-label{ margin-bottom:.35rem; font-size:.8rem; letter-spacing:.02em; }
        #addCdioModal .form-control.form-control-sm,
        #addCdioModal .form-select.form-select-sm,
        #addCdioModal textarea.form-control.form-control-sm{ font-size:.875rem; line-height:1.4; padding:.35rem .75rem; }
        #addCdioModal textarea.form-control.form-control-sm{ resize:vertical; }
        #addCdioModal .form-control:focus, #addCdioModal .form-select:focus{ border-color: var(--sv-acct); box-shadow:0 0 0 3px rgba(238,111,87,.16); }
        #addCdioModal .btn-danger{ background:#fff; border:none; color:#000; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #addCdioModal .btn-danger:hover, #addCdioModal .btn-danger:focus{ background:linear-gradient(135deg, rgba(255,240,235,.88), rgba(255,255,255,.46)); box-shadow:0 4px 10px rgba(204,55,55,.12); color:#CB3737; }
        #addCdioModal .btn-light{ background:#fff; border:none; color:#6c757d; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #addCdioModal .btn-light:hover, #addCdioModal .btn-light:focus{ background:linear-gradient(135deg, rgba(220,220,220,.88), rgba(240,240,240,.46)); box-shadow:0 4px 10px rgba(108,117,125,.12); color:#495057; }
      </style>

      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2" id="addCdioModalLabel">
          <i data-feather="plus-circle"></i>
          <span>Add CDIO</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="addCdioErrors" class="alert alert-danger d-none small mb-3" role="alert"></div>
        <div class="mb-3">
          <label for="cdioTitle" class="form-label small fw-medium text-muted">Title</label>
          <input type="text" class="form-control form-control-sm" id="cdioTitle" name="title" maxlength="255" required />
        </div>
        <div class="mb-0">
          <label for="cdioDescription" class="form-label small fw-medium text-muted">Description</label>
          <textarea class="form-control form-control-sm" id="cdioDescription" name="description" rows="4" maxlength="2000" required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i data-feather="x"></i> Cancel</button>
        <button type="submit" class="btn btn-danger" id="addCdioSubmit"><i data-feather="plus"></i> Create</button>
      </div>

      <style>
        /* bounce feedback when hide prevented */
        #addCdioModal.modal-static .modal-dialog { transform: scale(1.02); transition: transform .2s ease-in-out; }
        #addCdioModal.modal-static .modal-content { box-shadow: 0 8px 24px rgba(0,0,0,.12), 0 4px 12px rgba(0,0,0,.08); }
      </style>
    </form>
  </div>
</div>
