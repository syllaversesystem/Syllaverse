{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/modals/delete.blade.php
* Description: Custom Delete Syllabus confirmation modal (styled like ILO modal)
-------------------------------------------------------------------------------
--}}

<div class="modal fade sv-syllabus-delete-modal" id="deleteSyllabusModal" tabindex="-1" aria-labelledby="deleteSyllabusModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      @csrf

      <style>
        /* Ensure backdrop sits below this modal */
        .modal-backdrop { z-index: 10008 !important; }
        /* Brand tokens */
        #deleteSyllabusModal {
          --sv-bg:   #FAFAFA;
          --sv-bdr:  #E3E3E3;
          --sv-danger:#CB3737;
          z-index: 10010 !important;
        }
        #deleteSyllabusModal .modal-dialog,
        #deleteSyllabusModal .modal-content { position: relative; z-index: 10011; }
        #deleteSyllabusModal .modal-header {
          padding: .85rem 1rem;
          border-bottom: 1px solid var(--sv-bdr);
          background: #fff;
        }
        #deleteSyllabusModal .modal-title {
          font-weight: 600;
          font-size: 1rem;
          display: inline-flex;
          align-items: center;
          gap: .5rem;
        }
        #deleteSyllabusModal .modal-title i,
        #deleteSyllabusModal .modal-title svg {
          width: 1.05rem;
          height: 1.05rem;
          stroke: var(--sv-text-muted, #777777);
        }
        #deleteSyllabusModal .modal-content {
          border-radius: 16px;
          border: 1px solid var(--sv-bdr);
          background: #fff;
          box-shadow: 0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06);
          overflow: hidden;
        }
        #deleteSyllabusModal .alert {
          border-radius: 12px;
          padding: .75rem 1rem;
          font-size: .875rem;
        }
        #deleteSyllabusModal .alert-warning {
          background: #FFF3CD;
          border: 1px solid #FFE69C;
          color: #856404;
        }
        #deleteSyllabusModal .btn-danger {
          background: var(--sv-card-bg, #fff);
          border: none;
          color: #CB3737;
          transition: all 0.2s ease-in-out;
          box-shadow: none;
          display: inline-flex;
          align-items: center;
          gap: 0.5rem;
          padding: 0.5rem 1rem;
          border-radius: 0.375rem;
        }
        #deleteSyllabusModal .btn-danger:hover,
        #deleteSyllabusModal .btn-danger:focus {
          background: linear-gradient(135deg, rgba(255, 235, 235, 0.9), rgba(255, 245, 245, 0.6));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(203, 55, 55, 0.15);
          color: #CB3737;
        }
        #deleteSyllabusModal .btn-danger:hover i,
        #deleteSyllabusModal .btn-danger:hover svg,
        #deleteSyllabusModal .btn-danger:focus i,
        #deleteSyllabusModal .btn-danger:focus svg {
          stroke: #CB3737;
        }
        #deleteSyllabusModal .btn-danger:active {
          background: linear-gradient(135deg, rgba(203, 55, 55, 0.98), rgba(203, 55, 55, 0.9));
          box-shadow: 0 1px 8px rgba(203, 55, 55, 0.25);
          color: #fff;
        }
        #deleteSyllabusModal .btn-danger:active i,
        #deleteSyllabusModal .btn-danger:active svg {
          stroke: #fff;
        }
        #deleteSyllabusModal .btn-light {
          background: var(--sv-card-bg, #fff);
          border: none;
          color: #000;
          transition: all 0.2s ease-in-out;
          box-shadow: none;
          display: inline-flex;
          align-items: center;
          gap: 0.5rem;
          padding: 0.5rem 1rem;
          border-radius: 0.375rem;
        }
        #deleteSyllabusModal .btn-light:hover,
        #deleteSyllabusModal .btn-light:focus {
          background: linear-gradient(135deg, rgba(220, 220, 220, 0.88), rgba(240, 240, 240, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(108, 117, 125, 0.12);
          color: #495057;
        }
      </style>

      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2" id="deleteSyllabusModalLabel">
          <i data-feather="trash-2"></i>
          <span>Delete Syllabus</span>
        </h5>
      </div>

      <div class="modal-body">
        <div class="alert alert-warning d-flex align-items-start gap-2" role="alert">
          <i data-feather="alert-triangle" style="width: 1.25rem; height: 1.25rem; flex-shrink: 0; margin-top: 0.125rem;"></i>
          <div>
            <strong>Warning:</strong> This will permanently delete the syllabus “<span id="deleteSyllabusTitle"></span>”. This action cannot be undone.
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
          <i data-feather="x"></i> Cancel
        </button>
        <button type="button" class="btn btn-danger" id="confirmDeleteSyllabus">
          <i data-feather="trash-2"></i> Delete
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  // Ensure modal escapes stacking contexts by moving under <body>
  document.addEventListener('DOMContentLoaded', function(){
    try {
      const modal = document.getElementById('deleteSyllabusModal');
      if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
        modal.style.zIndex = '10012';
        const dlg = modal.querySelector('.modal-dialog');
        if (dlg) dlg.style.zIndex = '10013';
      }
    } catch (e) {
      console.error('Delete syllabus modal relocation failed', e);
    }
  });
</script>
