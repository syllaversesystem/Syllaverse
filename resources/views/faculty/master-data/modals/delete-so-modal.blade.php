{{-- Clean Delete SO Modal (Faculty) with UI matching Departments --}}
<div class="modal sv-faculty-dept-modal" id="deleteSoModal" tabindex="-1" aria-labelledby="deleteSoModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content">
      <style>
        #deleteSoModal {
          --sv-bg:   #FAFAFA;
          --sv-bdr:  #E3E3E3;
          --sv-danger:#CB3737;
        }
        #deleteSoModal .modal-content { border-radius:16px; border:1px solid var(--sv-bdr); background:#fff; box-shadow:0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06); overflow:hidden; }
        #deleteSoModal .modal-header { border-bottom:1px solid var(--sv-bdr); background: var(--sv-bg); }
        #deleteSoModal .modal-title { color: var(--sv-danger); }
        #deleteSoModal .btn-danger { background:#fff; border:none; color: var(--sv-danger); transition: all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #deleteSoModal .btn-danger:hover, #deleteSoModal .btn-danger:focus { background: linear-gradient(135deg, rgba(255,235,235,.88), rgba(255,245,245,.46)); box-shadow:0 4px 10px rgba(203,55,55,.15); color: var(--sv-danger); }
        #deleteSoModal .btn-light { background:#fff; border:none; color:#6c757d; transition: all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #deleteSoModal .btn-light:hover, #deleteSoModal .btn-light:focus { background: linear-gradient(135deg, rgba(220,220,220,.88), rgba(240,240,240,.46)); box-shadow:0 4px 10px rgba(108,117,125,.12); color:#495057; }
        /* Prevent long descriptions from overflowing; show ellipsis instead */
        #deleteSoModal #deleteSoTitle {
          max-width: 100%;
          overflow: hidden;
          display: -webkit-box;
          -webkit-box-orient: vertical;
          -webkit-line-clamp: 2; /* clamp to 2 lines */
          word-break: break-word; /* break long words */
          overflow-wrap: anywhere; /* allow break inside long tokens */
        }
      </style>

      <div class="modal-header">
        <h5 class="modal-title fw-semibold d-flex align-items-center gap-2" id="deleteSoModalLabel">
          <i data-feather="trash-2"></i>
          <span>Delete Student Outcome</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="text-center mb-4">
          <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 rounded-circle mb-3" style="width:64px; height:64px;">
            <i data-feather="trash-2" class="text-danger" style="width:28px; height:28px;"></i>
          </div>
          <h6 class="fw-semibold mb-2">Delete Student Outcome</h6>
          <p class="text-muted mb-0">Are you sure you want to permanently delete this Student Outcome?</p>
        </div>

        <div class="bg-light rounded-3 p-3 mb-2">
          <div class="small text-muted mb-1">You are about to delete:</div>
          <div class="fw-semibold mb-0" id="deleteSoTitle">Loading...</div>
        </div>

        <div class="alert alert-warning border-0 mb-0" style="background: rgba(255, 193, 7, 0.1);">
          <div class="d-flex align-items-start gap-3">
            <i data-feather="alert-triangle" class="text-warning flex-shrink-0 mt-1" style="width:18px; height:18px;"></i>
            <div class="small">
              <div class="fw-medium text-dark">This action cannot be undone</div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i data-feather="x"></i> Cancel</button>
        <form id="deleteSoForm" method="POST" class="d-inline" action="#">
          @csrf
          @method('DELETE')
          <input type="hidden" id="deleteSoId" name="id" value="">
          <button type="submit" class="btn btn-danger" id="deleteSoSubmit"><i data-feather="trash-2"></i> Delete</button>
        </form>
      </div>
    </div>
  </div>
</div>
