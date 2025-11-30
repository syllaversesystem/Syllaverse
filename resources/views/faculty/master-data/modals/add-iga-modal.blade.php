{{-- Add IGA Modal (Faculty) --}}
<div class="modal sv-faculty-dept-modal" id="addIgaModal" tabindex="-1" aria-labelledby="addIgaModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form id="addIgaForm" method="POST" class="modal-content" action="{{ url('/faculty/master-data/iga') }}">
      @csrf
      <style>
        #addIgaModal { --sv-bg:#FAFAFA; --sv-bdr:#E3E3E3; --sv-acct:#EE6F57; --sv-danger:#CB3737; }
        #addIgaModal .modal-header{ padding:.85rem 1rem; border-bottom:1px solid var(--sv-bdr); background:#fff; }
        #addIgaModal .modal-title{ font-weight:600; font-size:1rem; display:inline-flex; align-items:center; gap:.5rem; }
        #addIgaModal .modal-title i, #addIgaModal .modal-title svg { width:1.05rem; height:1.05rem; stroke: var(--sv-text-muted,#777); }
        #addIgaModal .modal-content{ border-radius:16px; border:1px solid var(--sv-bdr); background:#fff; box-shadow:0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06); overflow:hidden; }
        #addIgaModal .form-control, #addIgaModal .form-select{ border-radius:12px; border:1px solid var(--sv-bdr); background:#fff; }
        #addIgaModal .form-label{ margin-bottom:.35rem; font-size:.8rem; letter-spacing:.02em; }
        #addIgaModal .form-control.form-control-sm,
        #addIgaModal textarea.form-control.form-control-sm{ font-size:.875rem; line-height:1.4; padding:.35rem .75rem; }
        #addIgaModal textarea.form-control.form-control-sm{ resize: vertical; }
        #addIgaModal .form-control:focus, #addIgaModal .form-select:focus{ border-color: var(--sv-acct); box-shadow:0 0 0 3px rgba(238,111,87,.16); }
        #addIgaModal .btn-danger{ background:#fff; border:none; color:#000; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #addIgaModal .btn-danger i, #addIgaModal .btn-danger svg { stroke:#000; }
        #addIgaModal .btn-danger:hover, #addIgaModal .btn-danger:focus{ background:linear-gradient(135deg, rgba(235,235,235,.88), rgba(250,250,250,.46)); box-shadow:0 4px 10px rgba(0,0,0,.10); color:#000; }
        #addIgaModal .btn-danger:hover i, #addIgaModal .btn-danger:hover svg, #addIgaModal .btn-danger:focus i, #addIgaModal .btn-danger:focus svg { stroke:#000; }
        #addIgaModal .btn-light{ background:#fff; border:none; color:#000; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #addIgaModal .btn-light i, #addIgaModal .btn-light svg { stroke:#000; }
        #addIgaModal .btn-light:hover, #addIgaModal .btn-light:focus{ background:linear-gradient(135deg, rgba(225,225,225,.88), rgba(240,240,240,.46)); box-shadow:0 4px 10px rgba(0,0,0,.08); color:#000; }
        #addIgaModal .btn-light:hover i, #addIgaModal .btn-light:hover svg, #addIgaModal .btn-light:focus i, #addIgaModal .btn-light:focus svg { stroke:#000; }
      </style>

      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2" id="addIgaModalLabel">
          <i data-feather="plus-circle"></i>
          <span>Add Institutional Graduate Attribute</span>
        </h5>
      </div>

      <div class="modal-body">
        <div id="addIgaErrors" class="alert alert-danger d-none small mb-3" role="alert"></div>

        <div class="mb-3">
          <label for="igaTitle" class="form-label small fw-medium text-muted">Title</label>
          <input type="text" class="form-control form-control-sm" id="igaTitle" name="title" placeholder="e.g., Lifelong Learning" required />
        </div>

        <div class="mb-3">
          <label for="igaDescription" class="form-label small fw-medium text-muted">Description</label>
          <textarea id="igaDescription" name="description" class="form-control form-control-sm" rows="4" placeholder="Describe the IGA" required></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i data-feather="x"></i> Cancel</button>
        <button type="submit" class="btn btn-danger" id="addIgaSubmit"><i data-feather="plus"></i> Create</button>
      </div>
    </form>
  </div>
</div>
