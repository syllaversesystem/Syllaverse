{{-- Delete IGA Modal (Faculty) --}}
<div class="modal" id="deleteIgaModal" tabindex="-1" aria-labelledby="deleteIgaModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <form id="deleteIgaForm" method="POST" class="modal-content" action="#">
      @csrf
      <input type="hidden" name="_method" value="DELETE">
      <input type="hidden" id="deleteIgaId" name="id" value="">
      <style>
        #deleteIgaModal .modal-header{ padding:.85rem 1rem; border-bottom:1px solid #E3E3E3; }
        #deleteIgaModal .modal-title{ font-weight:600; font-size:1rem; display:inline-flex; align-items:center; gap:.5rem; }
        #deleteIgaModal .modal-content{ border-radius:12px; border:1px solid #E3E3E3; }
        #deleteIgaModal .btn-danger{ background:#fff; border:none; color:#000; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #deleteIgaModal .btn-danger:hover, #deleteIgaModal .btn-danger:focus{ background:linear-gradient(135deg, rgba(255,240,235,.88), rgba(255,255,255,.46)); box-shadow:0 4px 10px rgba(204,55,55,.12); color:#CB3737; }
        #deleteIgaModal .btn-light{ background:#fff; border:none; color:#6c757d; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #deleteIgaModal .btn-light:hover, #deleteIgaModal .btn-light:focus{ background:linear-gradient(135deg, rgba(220,220,220,.88), rgba(240,240,240,.46)); box-shadow:0 4px 10px rgba(108,117,125,.12); color:#495057; }
        #deleteIgaModal .delete-title{ display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
      </style>

      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2" id="deleteIgaModalLabel">
          <i data-feather="trash-2"></i>
          <span>Delete IGA</span>
        </h5>
      </div>

      <div class="modal-body">
        <p>Are you sure you want to delete:</p>
        <p class="mb-0 fw-semibold delete-title" id="deleteIgaTitle">(title)</p>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i data-feather="x"></i> Cancel</button>
        <button type="submit" class="btn btn-danger" id="deleteIgaSubmit"><i data-feather="trash"></i> Delete</button>
      </div>
    </form>
  </div>
</div>
