@extends('layouts.faculty')
@section('page-title','Syllabus Approvals')
@section('content')
  @push('styles')
    @vite('resources/css/faculty/syllabus-index.css')
  @endpush

  <div class="svx-fullbleed">
    <div class="container-fluid px-3 py-3">
      <!-- Toolbar card: search -->
      <div class="svx-card mb-3">
        <div class="svx-card-body">
          <div class="programs-toolbar mb-0" id="approvalsToolbar">
            <div class="input-group">
              <span class="input-group-text" id="approvalsSearchIcon"><i data-feather="search"></i></span>
              <input type="search" class="form-control" id="approvalsSearch" placeholder="Search pending approvals..." aria-label="Search approvals" />
            </div>
          </div>
        </div>
      </div>

      @if($syllabi->isEmpty())
        <div class="svx-empty text-center py-5">
          <div class="mb-3"><i class="bi bi-check-circle" style="font-size:2.5rem; opacity:.45;"></i></div>
          <h6 class="fw-bold mb-1">No pending approvals</h6>
          <p class="text-muted mb-3">There are no syllabi waiting for your review.</p>
        </div>
      @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3" id="approvalsGrid">
          @foreach($syllabi as $syllabus)
            <div class="col">
              <article class="svx-card shadow-sm h-100 d-flex flex-column syllabus-card" tabindex="0" role="button"
                       data-syllabus-id="{{ $syllabus->id }}"
                       data-open-url="{{ route('faculty.syllabi.show',$syllabus->id) }}?from=approvals&review=1"
                       aria-label="Review syllabus {{ $syllabus->title }}">
                <div class="svx-card-body flex-grow-1">
                  <div class="d-flex align-items-center justify-content-between mb-1 small text-muted">
                    <span class="svx-course-pill"><i class="bi bi-book"></i> {{ $syllabus->course->code ?? '-' }}</span>
                    <span class="badge bg-warning text-dark submission-status-badge-small">
                      <i class="bi bi-clock-history"></i>
                      Pending Review
                    </span>
                  </div>
                  <h6 class="fw-semibold mb-0 syllabus-title">{{ $syllabus->title }}</h6>
                  @if(!empty($syllabus->course?->title))
                    <div class="text-muted small syllabus-course-title">{{ $syllabus->course->title }}</div>
                  @endif
                  <div class="svx-meta mt-2 small">
                    <span class="chip"><i class="bi bi-calendar3"></i> AY {{ $syllabus->academic_year }}</span>
                    <span class="chip"><i class="bi bi-collection"></i> {{ $syllabus->semester }}</span>
                    <span class="chip"><i class="bi bi-people"></i> {{ $syllabus->year_level ?? '-' }}</span>
                  </div>
                  <div class="text-muted small mt-2">
                    <i class="bi bi-person"></i> Submitted by {{ $syllabus->faculty->name ?? 'Unknown' }}
                  </div>
                  
                </div>
                
              </article>
            </div>
          @endforeach
        </div>
        <!-- Hidden empty state placeholder to avoid layout shift when JS processes last card -->
        <div id="approvalsEmpty" class="svx-empty text-center py-5 d-none">
          <div class="mb-3"><i class="bi bi-check-circle" style="font-size:2.5rem; opacity:.45;"></i></div>
          <h6 class="fw-bold mb-1">No pending approvals</h6>
          <p class="text-muted mb-3">There are no syllabi waiting for your review.</p>
        </div>
      @endif
    </div>
  </div>
  <!-- Approve Confirmation Modal -->
  <div class="modal fade" id="approveConfirmModal" tabindex="-1" aria-labelledby="approveConfirmLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="approveConfirmLabel"><i class="bi bi-check-circle"></i> Confirm Approval</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to approve this syllabus?
        </div>
        <div class="modal-footer d-flex align-items-center gap-2">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn approve-confirm-btn" id="approveConfirmBtn"
                  data-syllabus-id="" data-approve-url="">
            <i class="bi bi-check-circle"></i> Approve
          </button>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('styles')
<style>
  .programs-toolbar { display:flex; align-items:center; flex-wrap:wrap; gap:.25rem; margin-bottom:1.0rem; }
  .programs-toolbar .input-group {
    flex:1; max-width: 420px;
    background: var(--sv-bg, #FAFAFA);
    border: 1px solid var(--sv-border, #E3E3E3);
    border-radius: 6px; overflow: hidden;
    box-shadow: 0 1px 2px rgba(0,0,0,0.02);
  }
  .programs-toolbar .input-group .form-control { padding:.4rem .75rem; font-size:.88rem; border:none; background:transparent; height:2.2rem; }
  .programs-toolbar .input-group .form-control::placeholder { color: var(--sv-text-muted, #666); }
  .programs-toolbar .input-group .form-control:focus { outline:none; box-shadow:none; background:transparent; }
  .programs-toolbar .input-group .input-group-text { background:transparent; border:none; padding-left:.7rem; padding-right:.4rem; display:flex; align-items:center; }
  .programs-toolbar .input-group-text i, .programs-toolbar .input-group-text svg { width:.95rem !important; height:.95rem !important; }

  @media (max-width: 768px) {
    .programs-toolbar { gap:.5rem; }
    .programs-toolbar .input-group { max-width:100%; }
  }

  /* Syllabus cards */
  .syllabus-card { 
    cursor: pointer;
    transition: all 0.3s ease;
    border: 1px solid #e0e0e0;
  }
  .syllabus-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15) !important;
    border-color: #CB3737;
  }
  .syllabus-card:hover .syllabus-title {
    color: #CB3737;
  }
  .syllabus-card:focus { 
    outline: 2px solid rgba(203,55,55,.45); 
    outline-offset: 2px; 
  }
  .syllabus-card:active {
    transform: translateY(-2px);
  }

  /* Status badge */
  .submission-status-badge-small {
    font-size: 0.65rem;
    padding: 0.25rem 0.5rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    border-radius: 4px;
  }
  .submission-status-badge-small i {
    font-size: 0.7rem;
  }

  /* Review action buttons – align with submit button shape/behavior */
  .review-approve-btn, .review-revise-btn {
    padding: 0.5rem 0.875rem;
    font-size: 0.875rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    border-radius: 0.375rem;
    transition: all 0.2s ease-in-out;
    border: 1px solid transparent;
  }
  .review-approve-btn {
    background: #fff; /* white background like submit */
    color: #28a745;   /* green text */
    border-color: #28a745; /* green border */
    box-shadow: none;
  }
  .review-approve-btn:hover {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.92), rgba(255, 255, 255, 0.66));
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(40, 167, 69, 0.15);
    border-color: #218838; /* darker green border */
    color: #218838; /* hover text color */
  }
  .review-approve-btn:focus {
    outline: 0;
    background: #fff;
    border-color: #228a35;
    box-shadow: 0 0 0 0.2rem rgba(40,167,69,0.25);
    color: #228a35;
  }
  .review-approve-btn:active {
    transform: translateY(0);
    background: #fff;
    border-color: #1e7e34;
    box-shadow: 0 2px 6px rgba(30, 126, 52, 0.15);
    color: #1e7e34;
  }
  .review-revise-btn {
    background: #fff; /* white background like submit */
    color: #e0a800;   /* yellow-ish text */
    border-color: #ffc107; /* yellow border */
    box-shadow: none;
  }
  .review-revise-btn:hover {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.92), rgba(255, 255, 255, 0.66));
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(255, 193, 7, 0.15);
    border-color: #e0a800; /* darker yellow border */
    color: #e0a800; /* hover text color */
  }
  .review-revise-btn:focus {
    outline: 0;
    background: #fff;
    border-color: #d39e00;
    box-shadow: 0 0 0 0.2rem rgba(255,193,7,0.25);
    color: #d39e00;
  }
  .review-revise-btn:active {
    transform: translateY(0);
    background: #fff;
    border-color: #c69500;
    box-shadow: 0 2px 6px rgba(198, 149, 0, 0.15);
    color: #c69500;
  }
  .review-approve-btn:disabled,
  .review-revise-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    pointer-events: none;
  }

  /* Approve confirm modal: button styling (white bg, green outline) */
  #approveConfirmModal .approve-confirm-btn {
    background: #fff;
    color: #28a745;
    border: 1px solid #28a745;
    padding: 0.5rem 0.875rem;
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.2s ease-in-out;
  }
  #approveConfirmModal .approve-confirm-btn:hover {
    color: #218838;
    border-color: #218838;
    box-shadow: 0 4px 10px rgba(40,167,69,0.12);
    transform: translateY(-1px);
  }
  #approveConfirmModal .approve-confirm-btn:focus {
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(40,167,69,0.25);
  }
  #approveConfirmModal .approve-confirm-btn:disabled {
    opacity: 0.7; cursor: not-allowed; pointer-events: none;
  }

  /* Align modal UI with Add Program modal tokens */
  #approveConfirmModal {
    --sv-bg:   #FAFAFA;
    --sv-bdr:  #E3E3E3;
  }
  #approveConfirmModal .modal-content {
    border: 1px solid var(--sv-bdr);
    border-radius: 16px;
  }
  #approveConfirmModal .modal-header {
    border-bottom: 1px solid var(--sv-bdr);
    background: var(--sv-bg);
  }
  #approveConfirmModal .modal-title {
    font-weight: 600;
    font-size: 1rem;
    display: inline-flex;
    align-items: center;
    gap: .5rem;
  }
  #approveConfirmModal .btn-light {
    background: #fff;
    border: none;
    color: #6c757d;
    transition: all 0.2s ease-in-out;
    box-shadow: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
  }
  #approveConfirmModal .btn-light:hover,
  #approveConfirmModal .btn-light:focus {
    background: linear-gradient(135deg, rgba(220, 220, 220, 0.88), rgba(240, 240, 240, 0.46));
    backdrop-filter: blur(7px);
    -webkit-backdrop-filter: blur(7px);
    box-shadow: 0 4px 10px rgba(108, 117, 125, 0.12);
    color: #495057;
  }
  #approveConfirmModal .btn-light:active {
    background: linear-gradient(135deg, rgba(240, 242, 245, 0.98), rgba(255, 255, 255, 0.62));
    box-shadow: 0 1px 8px rgba(108, 117, 125, 0.16);
  }

  /* Ensure Bootstrap modals appear above any local stacking contexts */
  .modal { z-index: 9999 !important; position: fixed; }
  .modal-backdrop { z-index: 9990 !important; }
  /* Neutralize parent stacking contexts that can trap the modal */
  .svx-fullbleed,
  .svx-card,
  .container-fluid {
    transform: none !important;
    filter: none !important;
    perspective: none !important;
  }
  .svx-fullbleed { position: static !important; z-index: auto !important; }
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function(){
    const search = document.getElementById('approvalsSearch');
    const grid = document.getElementById('approvalsGrid');
    if (!search || !grid) return;
    const cards = () => Array.from(grid.querySelectorAll('.col'));
    function matches(txt, q){
      return txt.toLowerCase().indexOf(q) !== -1;
    }
    search.addEventListener('input', function(){
      const q = (search.value || '').trim().toLowerCase();
      cards().forEach(col => {
        const card = col.querySelector('.svx-card');
        const title = col.querySelector('.syllabus-title')?.textContent || '';
        const courseTitle = col.querySelector('.syllabus-course-title')?.textContent || '';
        const courseCode = col.querySelector('.svx-course-pill')?.textContent || '';
        const text = [title, courseTitle, courseCode].join(' \u2022 ');
        col.style.display = q ? (matches(text, q) ? '' : 'none') : '';
      });
    });

    // Card click to open syllabus (ignore button clicks)
    const gridEl = document.getElementById('approvalsGrid');
    if (gridEl) {
      gridEl.addEventListener('click', function(ev){
        const target = ev.target;
        if (!target) return;
        if (target.closest('button')) return;
        const card = target.closest('.syllabus-card');
        if (!card) return;
        const url = card.getAttribute('data-open-url');
        if (url) { window.location.href = url; }
      });
      gridEl.addEventListener('keydown', function(ev){
        const card = ev.target.closest && ev.target.closest('.syllabus-card');
        if (!card) return;
        if (ev.key === 'Enter' || ev.key === ' ') {
          ev.preventDefault();
          const url = card.getAttribute('data-open-url');
          if (url) { window.location.href = url; }
        }
      });
    }

    // Approve / Request Revision handlers
    function getCsrfToken() {
      const meta = document.querySelector('meta[name="csrf-token"]');
      return meta ? meta.getAttribute('content') : '';
    }

    function setLoading(btn, isLoading) {
      if (!btn) return;
      btn.disabled = !!isLoading;
      if (isLoading) {
        btn.dataset._origHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
      } else if (btn.dataset._origHtml) {
        btn.innerHTML = btn.dataset._origHtml;
        delete btn.dataset._origHtml;
      }
    }

    // Approve opens modal; Revision posts immediately with optional remarks
    grid.addEventListener('click', async (ev) => {
      const approveBtn = ev.target.closest && ev.target.closest('.review-approve-btn');
      const reviseBtn = ev.target.closest && ev.target.closest('.review-revise-btn');
      if (!approveBtn && !reviseBtn) return;

      const card = ev.target.closest('.syllabus-card');
      const col = ev.target.closest('.col');
      if (!card) return;
      const syllabusId = card.getAttribute('data-syllabus-id');

      if (approveBtn) {
        const modalEl = document.getElementById('approveConfirmModal');
        const confirmBtn = document.getElementById('approveConfirmBtn');
        confirmBtn.dataset.syllabusId = syllabusId;
        confirmBtn.dataset.approveUrl = approveBtn.getAttribute('data-approve-url') || '';
        let modal = (bootstrap.Modal && bootstrap.Modal.getInstance) ? bootstrap.Modal.getInstance(modalEl) : null;
        if (!modal) { modal = new bootstrap.Modal(modalEl); }
        modal.show();
        return;
      }

      if (reviseBtn) {
        const url = reviseBtn.getAttribute('data-revision-url');
        if (!url) return;
        const token = getCsrfToken();
        const r = prompt('Enter revision remarks (optional):');
        try {
          setLoading(reviseBtn, true);
          const fd = new FormData();
          if (r !== null && r !== undefined) fd.append('remarks', r);
          const res = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }, body: fd });
          if (!res.ok) {
            let msg = 'Action failed';
            try { const j = await res.json(); if (j && j.message) msg = j.message; } catch(e) {}
            if (window.showAlertOverlay) { window.showAlertOverlay('error', msg); } else { alert(msg); }
            setLoading(reviseBtn, false);
            return;
          }
          if (col) col.remove();
          if (window.showAlertOverlay) { window.showAlertOverlay('success', 'Revision requested successfully'); }
          const remaining = grid.querySelectorAll('.col').length;
          if (remaining === 0) {
            // Hide grid and show the placeholder empty state to keep layout consistent
            grid.style.display = 'none';
            const emptyEl = document.getElementById('approvalsEmpty');
            if (emptyEl) emptyEl.classList.remove('d-none');
          }
        } catch (err) {
          console.error(err);
          if (window.showAlertOverlay) { window.showAlertOverlay('error', 'Unexpected error while processing action.'); } else { alert('Unexpected error while processing action.'); }
          setLoading(reviseBtn, false);
        }
      }
    });

    // Modal confirm: POST approve
    const confirmBtn = document.getElementById('approveConfirmBtn');
    const modalEl = document.getElementById('approveConfirmModal');
    if (confirmBtn && modalEl) {
      confirmBtn.addEventListener('click', async function(){
        const url = confirmBtn.dataset.approveUrl || '';
        const syllabusId = confirmBtn.dataset.syllabusId || '';
        if (!url || !syllabusId) return;
        const token = getCsrfToken();
        try {
          setLoading(confirmBtn, true);
          const res = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' } });
          if (!res.ok) {
            let msg = 'Approval failed';
            try { const j = await res.json(); if (j && j.message) msg = j.message; } catch(e) {}
            if (window.showAlertOverlay) { window.showAlertOverlay('error', msg); } else { alert(msg); }
            setLoading(confirmBtn, false);
            return;
          }
          // hide modal
          try {
            let modal = (bootstrap.Modal && bootstrap.Modal.getInstance) ? bootstrap.Modal.getInstance(modalEl) : null;
            if (!modal) { modal = new bootstrap.Modal(modalEl); }
            modal.hide();
          } catch(e) {}
          // remove card
          const card = grid.querySelector(`.syllabus-card[data-syllabus-id="${syllabusId}"]`);
          const col = card ? card.closest('.col') : null;
          if (col) col.remove();
          if (window.showAlertOverlay) { window.showAlertOverlay('success', 'Syllabus approved successfully'); }
          const remaining = grid.querySelectorAll('.col').length;
          if (remaining === 0) {
            grid.style.display = 'none';
            const emptyEl = document.getElementById('approvalsEmpty');
            if (emptyEl) emptyEl.classList.remove('d-none');
          }
        } catch (err) {
          console.error(err);
          if (window.showAlertOverlay) { window.showAlertOverlay('error', 'Unexpected error while approving.'); } else { alert('Unexpected error while approving.'); }
          setLoading(confirmBtn, false);
        }
      });
    }
  });
</script>
@endpush
