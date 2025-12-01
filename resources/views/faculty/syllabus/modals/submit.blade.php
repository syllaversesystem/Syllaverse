{{-- Modal: Submit Syllabus for Review/Approval --}}
<div class="modal fade sv-faculty-syllabus-modal" id="submitSyllabusModal" tabindex="-1" aria-labelledby="submitSyllabusModalLabel" aria-hidden="true" data-bs-backdrop="static" style="z-index:20010;">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      {{-- Local styles matching create modal --}}
      <style>
        #submitSyllabusModal {
          --sv-bg:   #FAFAFA;
          --sv-bdr:  #E3E3E3;
          --sv-acct: #EE6F57;
          --sv-danger:#CB3737;
          --sv-text: #333333;
          --sv-muted:#777777;
        }
        #submitSyllabusModal .modal-header {
          padding: .85rem 1rem;
          border-bottom: 1px solid var(--sv-bdr);
          background: #fff;
        }
        /* Ensure this specific modal always appears above any backdrop overrides from other partials */
        #submitSyllabusModal { z-index:20010 !important; }
        /* Raise dialog/content explicitly */
        #submitSyllabusModal .modal-dialog, #submitSyllabusModal .modal-content { z-index:20011 !important; }
        #submitSyllabusModal .modal-title {
          font-weight: 600;
          font-size: 1rem;
          display: inline-flex;
          align-items: center;
          gap: .5rem;
        }
        #submitSyllabusModal .modal-title i,
        #submitSyllabusModal .modal-title svg {
          width: 1.05rem;
          height: 1.05rem;
          stroke: var(--sv-text-muted, #777777);
          color: var(--sv-text-muted, #777777);
        }
        /* Scrollable modal layout parity with IGA modal */
        #submitSyllabusModal .modal-dialog { max-width: 680px; }
        #submitSyllabusModal .modal-content { max-height: 85vh; display: flex; flex-direction: column; }
        #submitSyllabusModal .modal-body { flex: 1 1 auto; overflow-y: auto; overscroll-behavior: contain; padding: 1.15rem 1.1rem; }
        #submitSyllabusModal .modal-content {
          border-radius: 16px;
          border: 1px solid var(--sv-bdr);
          background: #fff;
          box-shadow: 0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06);
          overflow: hidden;
        }
        #submitSyllabusModal .form-control,
        #submitSyllabusModal .form-select {
          background: #ffffff;
          border: 1px solid var(--sv-bdr); /* ensure visible border */
          border-radius: 12px;
          padding: 0.625rem 0.875rem;
          font-family: inherit;
          color: var(--sv-text);
        }
        /* Explicitly style search inputs to ensure consistent border & radius */
        #submitSyllabusModal #reviewerSearchInput,
        #submitSyllabusModal #finalApproverSearchInput {
          border: 1px solid var(--sv-bdr) !important;
          border-radius: 12px !important;
          background: #ffffff !important;
        }
        #submitSyllabusModal .form-control:focus,
        #submitSyllabusModal .form-select:focus {
          border-color: var(--sv-acct);
          box-shadow: 0 0 0 .2rem rgb(238 111 87 / 15%);
          outline: none;
        }
        #submitSyllabusModal .form-control::placeholder {
          color: var(--sv-muted);
          opacity: 0.85;
        }
        #submitSyllabusModal .form-label {
          font-weight: 500;
          color: var(--sv-muted);
          margin-bottom: 0.5rem;
          font-size: 0.875rem;
          font-family: inherit;
        }
        #submitSyllabusModal .text-muted {
          color: #6c757d !important;
          font-size: 0.875rem;
        }
        #submitSyllabusModal .modal-footer {
          border-top: 1px solid var(--sv-bdr);
          background: #fff;
          padding: .75rem 1rem;
          display: flex; gap: .5rem;
        }
        /* Neutral button styling parity with IGA load modal */
        #submitSyllabusModal .btn-light,
        #submitSyllabusModal .btn-danger {
          background: var(--sv-card-bg, #fff);
          border: none;
          color: #000;
          transition: all 0.2s ease-in-out;
          box-shadow: none;
          display: inline-flex;
          align-items: center;
          gap: .5rem;
          padding: .5rem 1rem;
          border-radius: .375rem;
          font-size: .95rem;
        }
        #submitSyllabusModal .btn-light i,
        #submitSyllabusModal .btn-light svg,
        #submitSyllabusModal .btn-danger i,
        #submitSyllabusModal .btn-danger svg { width:1.05rem; height:1.05rem; }
        #submitSyllabusModal .btn-light:hover,
        #submitSyllabusModal .btn-light:focus,
        #submitSyllabusModal .btn-danger:hover,
        #submitSyllabusModal .btn-danger:focus {
          background: linear-gradient(135deg, rgba(220,220,220,.88), rgba(240,240,240,.46));
          backdrop-filter: blur(7px); -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(0,0,0,.12);
          color:#000;
        }
        #submitSyllabusModal .btn-light:active,
        #submitSyllabusModal .btn-danger:active {
          background: linear-gradient(135deg, rgba(240,242,245,.98), rgba(255,255,255,.62));
          box-shadow: 0 1px 8px rgba(0,0,0,.16);
          color:#000;
        }
        #submitSyllabusModal .btn-danger:disabled { opacity:.6; cursor:not-allowed; }
        #submitSyllabusModal .info-box {
          background: #f8f9fa;
          border-left: 3px solid var(--sv-acct);
          padding: 0.75rem 1rem;
          border-radius: 6px;
          margin-bottom: 1.25rem;
        }
        #submitSyllabusModal .info-box p {
          margin: 0;
        }

        /* Final Approval card (match reviewer card visuals) */
        .approval-card-label {
          display: flex;
          align-items: center;
          gap: 0.75rem;
          padding: 0.875rem;
          border: 2px solid var(--sv-bdr);
          border-radius: 8px;
          background: white;
          transition: all 0.2s ease;
          margin: 0;
          width: 100%;
        }
        .approval-card-label:hover {
          border-color: var(--sv-acct);
          background: #fff5f5;
          transform: translateY(-2px);
          box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .approval-avatar {
          flex-shrink: 0;
          width: 40px;
          height: 40px;
          border-radius: 50%;
          background: linear-gradient(135deg, var(--sv-danger), var(--sv-acct));
          color: #fff;
          display: flex;
          align-items: center;
          justify-content: center;
        }
        .approval-info {
          flex: 1;
          min-width: 0;
        }
        .approval-title {
          font-weight: 600;
          font-size: 0.95rem;
          color: #333;
        }
        .approval-subtext {
          font-size: 0.8rem;
          color: #666;
          margin-top: 0.125rem;
        }

        /* Reviewer Cards */
        .reviewer-cards-container {
          display: grid;
          grid-template-columns: 1fr; /* make cards fill container width */
          gap: 0.75rem;
          max-height: 300px;
          overflow-y: auto;
          padding: 0.5rem;
          border: 1px solid var(--sv-bdr);
          border-radius: 8px;
          background: #fafafa;
        }
        .reviewer-card {
          position: relative;
        }
        .reviewer-radio {
          position: absolute;
          opacity: 0;
          pointer-events: none;
        }
        .reviewer-card-label {
          display: flex;
          align-items: center;
          gap: 0.75rem;
          padding: 0.875rem;
          border: 2px solid var(--sv-bdr);
          border-radius: 8px;
          background: white;
          cursor: pointer;
          transition: all 0.2s ease;
          margin: 0;
          width: 100%; /* fill the grid column */
        }
        .reviewer-card-label:hover {
          border-color: var(--sv-acct);
          background: #fff5f5;
          transform: translateY(-2px);
          box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .reviewer-radio:checked + .reviewer-card-label {
          border-color: var(--sv-danger);
          background: linear-gradient(135deg, rgba(255, 235, 235, 0.5), rgba(255, 245, 245, 0.3));
        }
        .reviewer-avatar {
          flex-shrink: 0;
          width: 40px;
          height: 40px;
          border-radius: 50%;
          background: linear-gradient(135deg, #f0f0f0, #e0e0e0);
          display: flex;
          align-items: center;
          justify-content: center;
          color: #666;
        }
        .reviewer-radio:checked + .reviewer-card-label .reviewer-avatar {
          background: linear-gradient(135deg, var(--sv-danger), var(--sv-acct));
          color: white;
        }
        .reviewer-info {
          flex: 1;
          min-width: 0;
        }
        .reviewer-name {
          font-weight: 600;
          font-size: 0.9rem;
          color: #333;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
        }
        .reviewer-role {
          font-size: 0.75rem;
          color: #666;
          margin-top: 0.125rem;
        }
        .reviewer-email {
          font-size: 0.7rem;
          color: #888;
          font-style: italic;
          margin-top: 0.125rem;
        }
        .reviewer-check {
          flex-shrink: 0;
          width: 24px;
          height: 24px;
          border-radius: 50%;
          border: 2px solid var(--sv-bdr);
          display: flex;
          align-items: center;
          justify-content: center;
          color: transparent;
          transition: all 0.2s ease;
        }
        .reviewer-radio:checked + .reviewer-card-label .reviewer-check {
          background: var(--sv-danger);
          border-color: var(--sv-danger);
          color: white;
        }
        .reviewer-check i {
          font-size: 14px;
        }
      </style>

      <div class="modal-header">
        <h5 class="modal-title" id="submitSyllabusModalLabel">
          <i class="bi bi-send"></i>
          <span id="modalTitleText">Submit for Review</span>
        </h5>
      </div>
      
      <form id="submitSyllabusForm" method="POST">
        @csrf
        <input type="hidden" name="syllabus_id" id="syllabusIdInput">
        <input type="hidden" name="action_type" id="actionTypeInput">
        
        <div class="modal-body">
          {{-- Review stage: Select reviewer (Program/Department Chairperson) --}}
          <div id="reviewStageSection">
            <label class="form-label">Select Reviewer (Program/Department Chairperson) <span class="text-danger">*</span></label>
            <input type="text" id="reviewerSearchInput" class="form-control mb-3" placeholder="Search by name or email...">
            
            <div id="reviewerCardsContainer" class="reviewer-cards-container">
              {{-- Cards will be populated via JavaScript --}}
              <div class="text-center text-muted py-4 w-100">
                <small>Loading reviewers...</small>
              </div>
            </div>
            
            <small class="text-muted d-block mt-2">Select one reviewer to submit this syllabus</small>
          </div>

          {{-- Approval stage: Select approver (Dean / Associate Dean) --}}
          <div id="approvalStageSection" style="display: none;">
            <label class="form-label">Select Final Approver (Dean/Associate Dean) <span class="text-danger">*</span></label>
            <input type="text" id="finalApproverSearchInput" class="form-control mb-3" placeholder="Search by name or email...">

            <div id="finalApproverCardsContainer" class="reviewer-cards-container">
              <div class="text-center text-muted py-4 w-100">
                <small>Loading final approvers...</small>
              </div>
            </div>

            <small class="text-muted d-block mt-2">Select one approver to submit for final approval</small>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">
            <i class="bi bi-x"></i> Cancel
          </button>
          <button type="submit" class="btn btn-danger" id="submitBtn">
            <i class="bi bi-send"></i> <span id="submitBtnText">Submit</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const CURRENT_USER_ID = {{ json_encode(Auth::id()) }};
  const modal = document.getElementById('submitSyllabusModal');
  const form = document.getElementById('submitSyllabusForm');
  const modalTitle = document.getElementById('modalTitleText');
  const syllabusIdInput = document.getElementById('syllabusIdInput');
  const actionTypeInput = document.getElementById('actionTypeInput');
  const reviewStageSection = document.getElementById('reviewStageSection');
  const approvalStageSection = document.getElementById('approvalStageSection');
  const finalApproverCardsContainer = document.getElementById('finalApproverCardsContainer');
  const finalApproverSearchInput = document.getElementById('finalApproverSearchInput');
  const reviewerCardsContainer = document.getElementById('reviewerCardsContainer');
  const reviewerSearchInput = document.getElementById('reviewerSearchInput');
  const submitBtn = document.getElementById('submitBtn');
  const submitBtnText = document.getElementById('submitBtnText');

  // When modal opens, configure it based on syllabus status
  modal.addEventListener('show.bs.modal', async function(event) {
    try {
      // Re-append to body & enforce stacking order
      if (modal.parentElement !== document.body) document.body.appendChild(modal);
      modal.style.zIndex = '20010';
      const backdrops = document.querySelectorAll('.modal-backdrop');
      backdrops.forEach(b => { b.style.zIndex = '20005'; });
    } catch(e) {}
    const button = event.relatedTarget || null;
    // Resolve attributes robustly: from trigger, proxy button, or modal dataset
    const proxyBtn = document.getElementById('syllabusSubmitProxyBtn');
    const syllabusId = (button && button.getAttribute('data-syllabus-id'))
                    || (proxyBtn && proxyBtn.getAttribute('data-syllabus-id'))
                    || modal.getAttribute('data-syllabus-id')
                    || '';
    const status = (button && button.getAttribute('data-status'))
                || (proxyBtn && proxyBtn.getAttribute('data-status'))
                || modal.getAttribute('data-status')
                || '';
    const departmentId = (button && button.getAttribute('data-department-id'))
                       || (proxyBtn && proxyBtn.getAttribute('data-department-id'))
                       || modal.getAttribute('data-department-id')
                       || '';
    const programId    = (button && button.getAttribute('data-program-id'))
                       || (proxyBtn && proxyBtn.getAttribute('data-program-id'))
                       || modal.getAttribute('data-program-id')
                       || '';

    // Cache on modal for subsequent shows when no relatedTarget is provided
    try {
      if (syllabusId) modal.setAttribute('data-syllabus-id', syllabusId);
      if (status) modal.setAttribute('data-status', status);
      if (departmentId) modal.setAttribute('data-department-id', departmentId);
      if (programId) modal.setAttribute('data-program-id', programId);
    } catch(e) {}

    if (!syllabusId) {
      // Missing context; show a friendly error instead of endless loading
      reviewerCardsContainer.innerHTML = '<div class="text-center text-danger py-4 w-100"><small>Missing syllabus context. Close and try again.</small></div>';
      if (finalApproverCardsContainer) {
        finalApproverCardsContainer.innerHTML = '<div class="text-center text-danger py-4 w-100"><small>Missing syllabus context. Close and try again.</small></div>';
      }
      return;
    }

    syllabusIdInput.value = syllabusId;
    // Reset loading states for lists every time modal opens
    if (reviewerCardsContainer) {
      reviewerCardsContainer.innerHTML = '<div class="text-center text-muted py-4 w-100"><small>Loading reviewers...</small></div>';
    }
    if (finalApproverCardsContainer) {
      finalApproverCardsContainer.innerHTML = '<div class="text-center text-muted py-4 w-100"><small>Loading final approvers...</small></div>';
    }

    // Determine if this is review or approval stage
    const isApprovalStage = status === 'approved';

    if (isApprovalStage) {
      // Final approval stage
      modalTitle.textContent = 'Submit for Final Approval';
      submitBtnText.textContent = 'Submit for Approval';
      actionTypeInput.value = 'final_approval';
      reviewStageSection.style.display = 'none';
      approvalStageSection.style.display = 'block';

      // Fetch Dean / Associate Dean final approvers
      if (departmentId) {
        try {
          const url = new URL(`/faculty/syllabus/${syllabusId}/final-approvers`, window.location.origin);
          url.searchParams.set('department_id', departmentId);
          const response = await fetch(url);
          const data = await response.json();

          finalApproverCardsContainer.innerHTML = '';

          if (data.success) {
            const filtered = (data.approvers || []).filter(a => a && a.id !== CURRENT_USER_ID);
            if (filtered.length > 0) {
            filtered.forEach(approver => {
              const card = document.createElement('div');
              card.className = 'reviewer-card';
              card.setAttribute('data-approver-id', approver.id);
              card.setAttribute('data-name', approver.name.toLowerCase());
              card.setAttribute('data-email', (approver.email || '').toLowerCase());

              card.innerHTML = `
                <input type="radio" name="approver_id" value="${approver.id}" id="approver_${approver.id}" class="reviewer-radio" required>
                <label for="approver_${approver.id}" class="reviewer-card-label">
                  <div class="reviewer-avatar">
                      <i class="bi bi-person"></i>
                  </div>
                  <div class="reviewer-info">
                    <div class="reviewer-name">${approver.name}</div>
                    <div class="reviewer-role">${approver.role_label}</div>
                    ${approver.email ? `<div class="reviewer-email">${approver.email}</div>` : ''}
                  </div>
                  <div class="reviewer-check">
                    <i class="bi bi-check"></i>
                  </div>
                </label>
              `;
              finalApproverCardsContainer.appendChild(card);
            });

            // Setup search functionality for final approvers
            setupFinalApproverSearch();
            } else {
            finalApproverCardsContainer.innerHTML = '<div class="text-center text-muted py-4 w-100"><small>No final approvers available</small></div>';
            }
          }
        } catch (error) {
          console.error('Error fetching final approvers:', error);
          finalApproverCardsContainer.innerHTML = '<div class="text-center text-danger py-4 w-100"><small>Error loading final approvers</small></div>';
        }
        } else {
          // No department context – avoid endless loading
          finalApproverCardsContainer.innerHTML = '<div class="text-center text-muted py-4 w-100"><small>No department found; cannot load final approvers.</small></div>';
        }
    } else {
      // Review stage (draft, revision)
      modalTitle.textContent = 'Submit for Review';
      submitBtnText.textContent = 'Submit for Review';
      actionTypeInput.value = 'review';
      reviewStageSection.style.display = 'block';
      approvalStageSection.style.display = 'none';

      // Fetch Program/Department Chairperson users (fallback to backend defaults if no dept/program provided)
      try {
        const url = new URL(`/faculty/syllabus/${syllabusId}/reviewers`, window.location.origin);
        if (departmentId) url.searchParams.set('department_id', departmentId);
        if (programId) url.searchParams.set('program_id', programId);
        const response = await fetch(url);
        const data = await response.json();

        reviewerCardsContainer.innerHTML = '';

        if (data && data.success) {
          const list = Array.isArray(data.reviewers) ? data.reviewers : [];
          const filtered = list.filter(r => r && r.id !== CURRENT_USER_ID);
          if (filtered.length > 0) {
            filtered.forEach(reviewer => {
              const card = document.createElement('div');
              card.className = 'reviewer-card';
              card.setAttribute('data-reviewer-id', reviewer.id);
              card.setAttribute('data-name', (reviewer.name || '').toLowerCase());
              card.setAttribute('data-email', (reviewer.email || '').toLowerCase());

              card.innerHTML = `
                <input type="radio" name="reviewer_id" value="${reviewer.id}" id="reviewer_${reviewer.id}" class="reviewer-radio" required>
                <label for="reviewer_${reviewer.id}" class="reviewer-card-label">
                  <div class="reviewer-avatar">
                    <i class="bi bi-person"></i>
                  </div>
                  <div class="reviewer-info">
                    <div class="reviewer-name">${reviewer.name || ''}</div>
                    <div class="reviewer-role">${reviewer.role_label || ''}</div>
                    ${reviewer.email ? `<div class="reviewer-email">${reviewer.email}</div>` : ''}
                  </div>
                  <div class="reviewer-check">
                    <i class="bi bi-check"></i>
                  </div>
                </label>
              `;
              reviewerCardsContainer.appendChild(card);
            });
            setupReviewerSearch();
          } else {
            reviewerCardsContainer.innerHTML = '<div class="text-center text-muted py-4 w-100"><small>No reviewers available</small></div>';
          }
        } else {
          reviewerCardsContainer.innerHTML = '<div class="text-center text-danger py-4 w-100"><small>Failed to load reviewers</small></div>';
        }
      } catch (error) {
        console.error('Error fetching reviewers:', error);
        reviewerCardsContainer.innerHTML = '<div class="text-center text-danger py-4 w-100"><small>Error loading reviewers</small></div>';
      }
    }
  });

  // Setup reviewer search functionality
  function setupReviewerSearch() {
    if (!reviewerSearchInput) return;

    reviewerSearchInput.addEventListener('input', function() {
      const searchQuery = this.value.toLowerCase().trim();
      const cards = reviewerCardsContainer.querySelectorAll('.reviewer-card');

      cards.forEach(card => {
        const name = card.getAttribute('data-name') || '';
        const email = card.getAttribute('data-email') || '';
        
        const matches = name.includes(searchQuery) || email.includes(searchQuery);
        card.style.display = matches ? '' : 'none';
      });
    });
  }

  function setupFinalApproverSearch() {
    if (!finalApproverSearchInput) return;
    finalApproverSearchInput.addEventListener('input', function() {
      const searchQuery = this.value.toLowerCase().trim();
      const cards = finalApproverCardsContainer.querySelectorAll('.reviewer-card');
      cards.forEach(card => {
        const name = card.getAttribute('data-name') || '';
        const email = card.getAttribute('data-email') || '';
        const matches = name.includes(searchQuery) || email.includes(searchQuery);
        card.style.display = matches ? '' : 'none';
      });
    });
  }

  // Reset form when modal closes
  modal.addEventListener('hidden.bs.modal', function() {
    form.reset();
    reviewerCardsContainer.innerHTML = '<div class="text-center text-muted py-4 w-100"><small>Loading reviewers...</small></div>';
    if (finalApproverCardsContainer) {
      finalApproverCardsContainer.innerHTML = '<div class="text-center text-muted py-4 w-100"><small>Loading final approvers...</small></div>';
    }
    if (reviewerSearchInput) {
      reviewerSearchInput.value = '';
    }
    if (finalApproverSearchInput) {
      finalApproverSearchInput.value = '';
    }
  });

  // Handle form submission
  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(form);
    const syllabusId = formData.get('syllabus_id');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';

    try {
      const response = await fetch(`/faculty/syllabus/${syllabusId}/submit`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json',
        },
        body: formData
      });

      const data = await response.json();
      try {
        // After close, restore backdrop default for other modals if needed
        document.querySelectorAll('.modal-backdrop').forEach(b => { b.style.zIndex = ''; });
      } catch(e) {}

      if (data.success) {
        // Close modal
        const bsModal = bootstrap.Modal.getInstance(modal);
        bsModal.hide();
        
        // Redirect back to syllabi list (close the syllabus)
        window.location.href = "{{ route('faculty.syllabi.index') }}";
      } else {
        alert(data.message || 'Failed to submit syllabus');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-send"></i> <span id="submitBtnText">Submit</span>';
      }
    } catch (error) {
      console.error('Error submitting syllabus:', error);
      alert('An error occurred while submitting the syllabus');
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="bi bi-send"></i> <span id="submitBtnText">Submit</span>';
    }
  });
});
</script>
@endpush
