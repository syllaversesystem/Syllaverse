{{-- Add SDG Modal (Faculty) --}}
<div class="modal sv-faculty-dept-modal" id="addSdgModal" tabindex="-1" aria-labelledby="addSdgModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form id="addSdgForm" method="POST" class="modal-content" action="{{ url('/faculty/master-data/sdg') }}">
      @csrf
      <style>
        #addSdgModal { --sv-bg:#FAFAFA; --sv-bdr:#E3E3E3; --sv-acct:#EE6F57; --sv-danger:#CB3737; }
        #addSdgModal .modal-header{ padding:.85rem 1rem; border-bottom:1px solid var(--sv-bdr); background:#fff; }
        #addSdgModal .modal-title{ font-weight:600; font-size:1rem; display:inline-flex; align-items:center; gap:.5rem; }
        #addSdgModal .modal-title i, #addSdgModal .modal-title svg { width:1.05rem; height:1.05rem; stroke: var(--sv-text-muted,#777); }
        #addSdgModal .modal-content{ border-radius:16px; border:1px solid var(--sv-bdr); background:#fff; box-shadow:0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06); overflow:hidden; }
        #addSdgModal .form-control, #addSdgModal .form-select{ border-radius:12px; border:1px solid var(--sv-bdr); background:#fff; }
        /* Align typography and spacing across Title and Description fields */
        #addSdgModal .form-label{ margin-bottom:.35rem; font-size:.8rem; letter-spacing:.02em; }
        #addSdgModal .form-control.form-control-sm,
        #addSdgModal .form-select.form-select-sm,
        #addSdgModal textarea.form-control.form-control-sm {
          font-size:.875rem; /* match small input */
          line-height:1.4;
          padding:.35rem .75rem;
        }
        #addSdgModal textarea.form-control.form-control-sm{ resize:vertical; }
        #addSdgModal .form-control:focus, #addSdgModal .form-select:focus{ border-color: var(--sv-acct); box-shadow:0 0 0 3px rgba(238,111,87,.16); }
        #addSdgModal .btn-danger{ background:#fff; border:none; color:#000; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #addSdgModal .btn-danger i, #addSdgModal .btn-danger svg { stroke:#000; }
        #addSdgModal .btn-danger:hover, #addSdgModal .btn-danger:focus{ background:linear-gradient(135deg, rgba(235,235,235,.88), rgba(250,250,250,.46)); box-shadow:0 4px 10px rgba(0,0,0,.10); color:#000; }
        #addSdgModal .btn-danger:hover i, #addSdgModal .btn-danger:hover svg, #addSdgModal .btn-danger:focus i, #addSdgModal .btn-danger:focus svg { stroke:#000; }
        #addSdgModal .btn-light{ background:#fff; border:none; color:#000; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #addSdgModal .btn-light i, #addSdgModal .btn-light svg { stroke:#000; }
        #addSdgModal .btn-light:hover, #addSdgModal .btn-light:focus{ background:linear-gradient(135deg, rgba(225,225,225,.88), rgba(240,240,240,.46)); box-shadow:0 4px 10px rgba(0,0,0,.08); color:#000; }
        #addSdgModal .btn-light:hover i, #addSdgModal .btn-light:hover svg, #addSdgModal .btn-light:focus i, #addSdgModal .btn-light:focus svg { stroke:#000; }
      </style>

      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2" id="addSdgModalLabel">
          <i data-feather="plus-circle"></i>
          <span>Add Sustainable Development Goal</span>
        </h5>
      </div>

      <div class="modal-body">
        <div id="addSdgErrors" class="alert alert-danger d-none small mb-3" role="alert"></div>

        <div class="mb-3">
          <label for="sdgTitle" class="form-label small fw-medium text-muted">Title</label>
          <input type="text" class="form-control form-control-sm" id="sdgTitle" name="title" placeholder="e.g., No Poverty" required />
        </div>

        {{-- SDG has no department --}}

        <div class="mb-3">
          <label for="sdgDescription" class="form-label small fw-medium text-muted">Description</label>
          <textarea id="sdgDescription" name="description" class="form-control form-control-sm" rows="4" placeholder="Describe the SDG" required></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i data-feather="x"></i> Cancel</button>
        <button type="submit" class="btn btn-danger" id="addSdgSubmit"><i data-feather="plus"></i> Create</button>
      </div>
    </form>
  </div>
</div>
