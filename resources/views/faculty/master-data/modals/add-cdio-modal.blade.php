{{-- Add CDIO Modal (Faculty) --}}
<div class="modal sv-faculty-dept-modal" id="addCdioModal" tabindex="-1" aria-labelledby="addCdioModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form id="addCdioForm" method="POST" class="modal-content" action="{{ url('/faculty/master-data/cdio') }}">
      @csrf
      <style>
        #addCdioModal { --sv-bg:#FAFAFA; --sv-bdr:#E3E3E3; --sv-acct:#EE6F57; --sv-danger:#CB3737; }
        #addCdioModal .modal-header{ padding:.85rem 1rem; border-bottom:1px solid var(--sv-bdr); background:#fff; }
        #addCdioModal .modal-title{ font-weight:600; font-size:1rem; display:inline-flex; align-items:center; gap:.5rem; }
        #addCdioModal .modal-title i, #addCdioModal .modal-title svg { width:1.05rem; height:1.05rem; stroke: var(--sv-text-muted,#777); }
        #addCdioModal .modal-content{ border-radius:16px; border:1px solid var(--sv-bdr); background:#fff; box-shadow:0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06); overflow:hidden; }
        #addCdioModal .form-control, #addCdioModal .form-select{ border-radius:12px; border:1px solid var(--sv-bdr); background:#fff; }
        #addCdioModal .form-label{ margin-bottom:.35rem; font-size:.8rem; letter-spacing:.02em; }
        #addCdioModal .form-control.form-control-sm,
        #addCdioModal .form-select.form-select-sm,
        #addCdioModal textarea.form-control.form-control-sm{ font-size:.875rem; line-height:1.4; padding:.35rem .75rem; }
        #addCdioModal textarea.form-control.form-control-sm{ resize:vertical; }
        #addCdioModal .form-control:focus, #addCdioModal .form-select:focus{ border-color: var(--sv-acct); box-shadow:0 0 0 3px rgba(238,111,87,.16); }
        #addCdioModal .btn-danger{ background:#fff; border:none; color:#000; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #addCdioModal .btn-danger i, #addCdioModal .btn-danger svg { stroke:#000; }
        #addCdioModal .btn-danger:hover, #addCdioModal .btn-danger:focus{ background:linear-gradient(135deg, rgba(235,235,235,.88), rgba(250,250,250,.46)); box-shadow:0 4px 10px rgba(0,0,0,.10); color:#000; }
        #addCdioModal .btn-danger:hover i, #addCdioModal .btn-danger:hover svg, #addCdioModal .btn-danger:focus i, #addCdioModal .btn-danger:focus svg { stroke:#000; }
        #addCdioModal .btn-light{ background:#fff; border:none; color:#000; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
        #addCdioModal .btn-light i, #addCdioModal .btn-light svg { stroke:#000; }
        #addCdioModal .btn-light:hover, #addCdioModal .btn-light:focus{ background:linear-gradient(135deg, rgba(225,225,225,.88), rgba(240,240,240,.46)); box-shadow:0 4px 10px rgba(0,0,0,.08); color:#000; }
        #addCdioModal .btn-light:hover i, #addCdioModal .btn-light:hover svg, #addCdioModal .btn-light:focus i, #addCdioModal .btn-light:focus svg { stroke:#000; }
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

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('addCdioForm');
    const submitBtn = document.getElementById('addCdioSubmit');
    const modalEl = document.getElementById('addCdioModal');
    const errorsEl = document.getElementById('addCdioErrors');
    const Modal = window.bootstrap?.Modal;

    function getCsrf() {
      return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    if (form && submitBtn) {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        errorsEl?.classList.add('d-none');
        errorsEl && (errorsEl.innerHTML = '');
        submitBtn.disabled = true;
        try {
          const fd = new FormData(form);
          const res = await fetch(form.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': getCsrf(), 'Accept': 'application/json' },
            body: fd,
          });
          let data = {};
          try { data = await res.json(); } catch {}
          if (res.ok) {
            // Close modal and notify
            try { const mm = Modal ? Modal.getOrCreateInstance(modalEl) : null; mm && mm.hide(); } catch {}
            if (window.showAlertOverlay) window.showAlertOverlay('success', 'CDIO created'); else alert('CDIO created');
            // Optionally refresh the page or list
            setTimeout(() => { window.location.reload(); }, 300);
          } else if (res.status === 422 && data?.errors) {
            const lines = [];
            Object.values(data.errors).forEach(arr => { (arr||[]).forEach(msg => lines.push(msg)); });
            if (errorsEl) {
              errorsEl.innerHTML = lines.join('<br>');
              errorsEl.classList.remove('d-none');
            } else {
              alert('Please fix errors before submitting.');
            }
          } else {
            const msg = data?.message || 'Failed to create CDIO';
            if (window.showAlertOverlay) window.showAlertOverlay('error', msg); else alert(msg);
          }
        } catch (err) {
          console.error(err);
          alert('Unexpected error');
        } finally {
          submitBtn.disabled = false;
        }
      });
    }
  });
</script>
@endpush
