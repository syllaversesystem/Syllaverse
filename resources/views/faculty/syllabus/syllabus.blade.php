{{--
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/syllabus.blade.php
* Description: Main Syllabus (CIS) edit page composed of modular partials
* Rationale: Recreated after accidental removal to satisfy SyllabusController@show
-------------------------------------------------------------------------------
--}}
@php($fullscreen = true)
@extends('layouts.faculty')

@php($routePrefix = 'faculty.syllabi')
@php($reviewMode = ($reviewMode ?? (request()->boolean('review') || request()->get('from') === 'approvals')))
@section('title')
  {{ $syllabus->course?->code ? $syllabus->course->code.' • ' : '' }}Syllabus
@endsection

@php($submissionStatus = $syllabus->submission_status ?? 'draft')
@php($isDraft = ($submissionStatus === 'draft'))
@php($isPendingReview = ($submissionStatus === 'pending_review'))
@php($isLockedSubmitted = in_array($submissionStatus, ['pending_review','approved','final_approval']))
@section('content')
<div class="syllabus-doc" id="syllabus-document" data-syllabus-id="{{ $syllabus->id }}">
  <!-- Canonical syllabus form used by module-level saves via form="syllabusForm" -->
  <form id="syllabusForm" name="syllabusForm" action="{{ route('faculty.syllabi.update', $syllabus->id) }}" method="POST" class="d-none">
    @csrf
    @method('PUT')
    <!-- This form intentionally stays empty; fields across partials reference it via form="syllabusForm" -->
  </form>
  {{-- Vertical toolbar (left side) --}}
  <div class="syllabus-vertical-toolbar" id="syllabusToolbar" aria-label="Syllabus editing toolbar">
    <div class="toolbar-inner">
      <button type="button" class="btn btn-outline-secondary d-flex flex-column align-items-center gap-1 toolbar-btn mb-3" onclick="handleExit('{{ route('faculty.syllabi.index') }}')" title="Exit">
        <i class="bi bi-arrow-left fs-5"></i>
        <span class="small">Exit</span>
      </button>
      @if(!$isPendingReview)
      <button id="syllabusSaveBtn" type="button" class="btn btn-danger d-flex flex-column align-items-center gap-1 toolbar-btn" title="Save">
        <i class="bi bi-save fs-5"></i>
        <span class="small">Save</span>
        <span id="unsaved-count-badge" class="badge bg-warning text-dark mt-1 w-100 text-center" style="display:none;">0</span>
      </button>
      @endif
      @if(!$reviewMode && !$isDraft && !$isLockedSubmitted)
      <button id="syllabusCommentsToggleBtn" type="button" class="btn btn-outline-secondary d-flex flex-column align-items-center gap-1 toolbar-btn mt-3" title="Toggle Reviewer Comments" data-disable-comments="0">
        <i class="bi bi-chat-left-text fs-5"></i>
        <span class="small">Comments</span>
        <span id="comments-count-badge" class="badge sv-comments-badge" style="display:none;">0</span>
      </button>
      @endif
      @if(!$reviewMode && !$isLockedSubmitted)
      <button id="syllabusSubmitBtn" type="button" class="btn btn-outline-primary d-flex flex-column align-items-center gap-1 toolbar-btn mt-3" title="Submit For Review">
        <i class="bi bi-send fs-5"></i>
        <span class="small">Submit</span>
      </button>
      @endif
    </div>
  </div>

  <div class="syllabus-content-wrapper">
    <div class="svx-card">
      <div class="svx-card-body">
        @include('faculty.syllabus.partials.header')

        <div class="sv-partial" data-partial-key="mission-vision">@include('faculty.syllabus.partials.mission-vision')</div>
        <div class="sv-partial" data-partial-key="course-info">@include('faculty.syllabus.partials.course-info')</div>
        <div class="sv-partial" data-partial-key="criteria-assessment">@include('faculty.syllabus.partials.criteria-assessment')</div>
        <div class="sv-partial" data-partial-key="tlas">@include('faculty.syllabus.partials.tlas')</div>
        
        <div class="sv-partial" data-partial-key="ilo">@include('faculty.syllabus.partials.ilo')</div>
        <div class="sv-partial" data-partial-key="assessment-tasks-distribution">@include('faculty.syllabus.partials.assessment-tasks-distribution')</div>
        <div class="sv-partial" data-partial-key="textbook-upload">@include('faculty.syllabus.partials.textbook-upload')</div>
        <div class="sv-partial" data-partial-key="iga">@include('faculty.syllabus.partials.iga')</div>
        <div class="sv-partial" data-partial-key="so">@include('faculty.syllabus.partials.so')</div>
        <div class="sv-partial" data-partial-key="cdio">@include('faculty.syllabus.partials.cdio')</div>
        <div class="sv-partial" data-partial-key="sdg">@include('faculty.syllabus.partials.sdg')</div>
        <div class="sv-partial" data-partial-key="course-policies">@include('faculty.syllabus.partials.course-policies')</div>
        <div class="sv-partial" data-partial-key="tla">@include('faculty.syllabus.partials.tla')</div>
        <div class="sv-partial" data-partial-key="assessment-mapping">@include('faculty.syllabus.partials.assessment-mapping')</div>
        <div class="sv-partial" data-partial-key="ilo-so-cpa-mapping">@include('faculty.syllabus.partials.ilo-so-cpa-mapping')</div>
        <div class="sv-partial" data-partial-key="ilo-iga-mapping">@include('faculty.syllabus.partials.ilo-iga-mapping')</div>
        <div class="sv-partial" data-partial-key="ilo-cdio-sdg-mapping">@include('faculty.syllabus.partials.mapping-ilo-cdio-sdg')</div>
        <div class="sv-partial" data-partial-key="status" data-non-commentable="1">@include('faculty.syllabus.partials.status')</div>
</div> {{--/.syllabus-content-wrapper--}}
  
</div>
<!-- Hidden proxy button to open submit modal with proper dataset -->
<button type="button" id="syllabusSubmitProxyBtn" class="d-none" data-bs-toggle="modal" data-bs-target="#submitSyllabusModal"></button>
<!-- Include submit modal so submit button works on this page -->
@includeIf('faculty.syllabus.modals.submit')
{{-- Right-side review toolbar (fixed on page right) --}}
@if(!$isDraft && (!$isLockedSubmitted || $reviewMode))
  @include('faculty.syllabus.partials.toolbar-syllabus')
@endif
@if($reviewMode)
  <!-- Approve Confirmation Modal (sidebar context) -->
  <div class="modal fade" id="approveConfirmModal" tabindex="-1" aria-labelledby="approveConfirmLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="approveConfirmLabel"><i class="bi bi-check-circle"></i> Confirm Approval</h5>
        </div>
        <div class="modal-body">Are you sure you want to approve this syllabus?</div>
        <div class="modal-footer d-flex align-items-center gap-2">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> Cancel</button>
          <button type="button" class="btn approve-confirm-btn" id="approveConfirmBtn" data-approve-url="">
            <i class="bi bi-check-circle"></i> Approve
          </button>
        </div>
      </div>
    </div>
    </div>

  <!-- Request Revision Modal (sidebar context) -->
  <div class="modal fade" id="revisionModal" tabindex="-1" aria-labelledby="revisionModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="revisionModalLabel"><i class="bi bi-arrow-clockwise"></i> Request Revision</h5>
        </div>
        <div class="modal-body">
          <div class="mb-2">Are you sure you want to return this syllabus for revision?</div>
        </div>
        <div class="modal-footer d-flex align-items-center gap-2">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> Cancel</button>
          <button type="button" class="btn review-revise-confirm-btn" id="revisionConfirmBtn" data-revision-url="">
            <i class="bi bi-arrow-clockwise"></i> Request Revision
          </button>
        </div>
      </div>
    </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
  // Quick section search: scroll to first matching CIS section label
  // (Search removed) – cleanup placeholder logic; leaving hook if reintroduced later.

  // -----------------------------
  // Undo / Redo & Auto-Save Logic
  // -----------------------------
  document.addEventListener('DOMContentLoaded', function(){
    // Ensure all key modals escape local stacking contexts by living under <body>
    (function(){
      try {
        ['submitSyllabusModal','approveConfirmModal','revisionModal'].forEach(id => {
          const el = document.getElementById(id);
          if (el && el.parentElement !== document.body) {
            document.body.appendChild(el);
          }
          if (el) {
            el.addEventListener('show.bs.modal', function(){
              try { if (el.parentElement !== document.body) document.body.appendChild(el); } catch(e){}
            });
          }
        });
      } catch(e) { /* noop */ }
    })();
    // (Removed) Left toolbar resize logic
    // Seed review flag for early conditionals
    const __isReviewCtx = !!({{ $reviewMode ? 'true' : 'false' }});
    // (Removed) Right review sidebar resize logic
    // Right toolbar (review panel) resize
    (function(){
      const doc = document.getElementById('syllabus-document');
      const rightBar = doc ? doc.querySelector('.syllabus-right-toolbar') : null;
      const handle = rightBar ? rightBar.querySelector('.right-resize-handle') : null;
      // Only enable resize behavior during review
      if (!__isReviewCtx) return;
      if (!doc || !rightBar || !handle) return;

      function getMaxPx(){ return Math.floor(doc.clientWidth * 0.25); }
      const MIN_PX = 240;
      function applyW(px){
        const maxPx = getMaxPx();
        const effectiveMin = Math.min(MIN_PX, maxPx);
        const w = Math.max(effectiveMin, Math.min(px, maxPx));
        doc.style.setProperty('--right-toolbar-w', w + 'px');
        rightBar.style.width = w + 'px';
        try { localStorage.setItem('sv_right_toolbar_w', String(w)); } catch(e){}
      }
      // Seed width
      try {
        const stored = parseInt(localStorage.getItem('sv_right_toolbar_w') || '0', 10);
        if (!isNaN(stored) && stored > 0) applyW(stored); else applyW(rightBar.getBoundingClientRect().width || 78);
      } catch(e){ applyW(rightBar.getBoundingClientRect().width || 78); }

      let dragging = false;
      function onMove(ev){
        if (!dragging) return;
        const x = ev.touches && ev.touches[0] ? ev.touches[0].clientX : ev.clientX;
        const rect = doc.getBoundingClientRect();
        const desired = rect.right - x; // anchored to document's right edge
        applyW(desired);
      }
      function onUp(){ dragging = false; document.body.style.userSelect=''; document.removeEventListener('mousemove', onMove); document.removeEventListener('mouseup', onUp); document.removeEventListener('touchmove', onMove); document.removeEventListener('touchend', onUp); }
      function onDown(ev){ dragging = true; ev.preventDefault(); ev.stopPropagation(); document.body.style.userSelect='none'; document.addEventListener('mousemove', onMove); document.addEventListener('mouseup', onUp); document.addEventListener('touchmove', function(e){ onMove(e); e.preventDefault(); }, { passive:false }); document.addEventListener('touchend', onUp); }
      handle.addEventListener('mousedown', onDown);
      handle.addEventListener('touchstart', onDown, { passive:true });
      window.addEventListener('resize', function(){ const varVal = getComputedStyle(doc).getPropertyValue('--right-toolbar-w').trim(); const w = parseInt(varVal || '78', 10) || 78; applyW(w); });
    })();
    const form = document.getElementById('syllabusForm');
    // Review mode: disable edits and enable comment selection UI
    const isReview = !!({{ $reviewMode ? 'true' : 'false' }});
    const isDraft = !!({{ $isDraft ? 'true' : 'false' }});
    const isLockedSubmitted = !!({{ $isLockedSubmitted ? 'true' : 'false' }});
    (function(){
      if (!isReview || isDraft) return; // allow comments in review mode even for submitted statuses
      const doc = document.getElementById('syllabus-document');
      if (doc) doc.classList.add('is-review');
      const rightBar = doc ? doc.querySelector('.syllabus-right-toolbar') : null;
      const titleEl = rightBar ? rightBar.querySelector('#svReviewTitle') : null;
      const tagEl = rightBar ? rightBar.querySelector('#svReviewColorTag') : null;
      const commentsEl = rightBar ? rightBar.querySelector('#svReviewComments') : null;
      const approveBtnRight = rightBar ? rightBar.querySelector('.review-approve-btn') : null;
      const reviseBtnRight = rightBar ? rightBar.querySelector('.review-revise-btn') : null;
      const approveConfirmBtn = document.getElementById('approveConfirmBtn');
      const approveConfirmModalEl = document.getElementById('approveConfirmModal');
      const revisionModalEl = document.getElementById('revisionModal');
      const revisionConfirmBtn = document.getElementById('revisionConfirmBtn');
      const revisionNumberInput = null;
      const revisionDateInput = null;
      const inputEl = rightBar ? rightBar.querySelector('#svCommentInput') : null;
      const addBtn = rightBar ? rightBar.querySelector('#svAddCommentBtn') : null;
      // Make inputs read-only/disabled inside the content area only (do not affect left toolbar)
      Array.from(document.querySelectorAll('.syllabus-content-wrapper input, .syllabus-content-wrapper textarea, .syllabus-content-wrapper select'))
        .forEach(el => {
          if (el.tagName === 'BUTTON') { el.disabled = true; return; }
          el.setAttribute('readonly', 'readonly');
          el.setAttribute('disabled', 'disabled');
        });
      // Color palette per partial key
      const palette = {
        'mission-vision': '#4e79a7',
        'course-info': '#f28e2b',
        'criteria-assessment': '#e15759',
        'tlas': '#76b7b2',
        'tla': '#59a14f',
        'ilo': '#edc949',
        'assessment-tasks-distribution': '#af7aa1',
        'textbook-upload': '#ff9da7',
        'iga': '#9c755f',
        'so': '#bab0ab',
        'cdio': '#2f4b7c',
        'sdg': '#b07aa1',
        'course-policies': '#3a77a3',
        'ilo-so-cpa-mapping': '#d37266',
        'ilo-iga-mapping': '#86bc86'
      };
      // Mark partial containers with keys
      const partials = [
        ['mission-vision', 'faculty.syllabus.partials.mission-vision'],
        ['course-info', 'faculty.syllabus.partials.course-info'],
        ['criteria-assessment', 'faculty.syllabus.partials.criteria-assessment'],
        ['tlas', 'faculty.syllabus.partials.tlas'],
        ['tla', 'faculty.syllabus.partials.tla'],
        ['ilo', 'faculty.syllabus.partials.ilo'],
        ['assessment-tasks-distribution', 'faculty.syllabus.partials.assessment-tasks-distribution'],
        ['textbook-upload', 'faculty.syllabus.partials.textbook-upload'],
        ['iga', 'faculty.syllabus.partials.iga'],
        ['so', 'faculty.syllabus.partials.so'],
        ['cdio', 'faculty.syllabus.partials.cdio'],
        ['sdg', 'faculty.syllabus.partials.sdg'],
        ['course-policies', 'faculty.syllabus.partials.course-policies'],
        ['ilo-so-cpa-mapping', 'faculty.syllabus.partials.ilo-so-cpa-mapping'],
        ['ilo-iga-mapping', 'faculty.syllabus.partials.ilo-iga-mapping']
      ];
      partials.forEach(([key]) => {
        const container = document.querySelector(`[data-partial-key="${key}"]`) || null;
        if (container) return; // already marked
      });
      // Auto-wrap direct siblings inside card body with markers
      const cardBody = document.querySelector('.svx-card-body');
      if (cardBody) {
        // Longer keys first to avoid picking 'ilo' inside 'ilo-so-cpa-mapping'
        const orderedKeys = Object.keys(palette).sort((a,b) => b.length - a.length);
        Array.from(cardBody.children).forEach(ch => {
          if (ch.hasAttribute('data-partial-key')) return; // keep explicit keys from Blade
          let key = null;
          const text = (ch.querySelector('th, h6, h5')?.textContent || '').toLowerCase();
          for (const k of orderedKeys) {
            const needle = k.replace(/-/g,' ');
            if (text.includes(needle)) { key = k; break; }
          }
          if (!key) return;
          ch.setAttribute('data-partial-key', key);
          ch.classList.add('sv-partial');
        });
      }
      // Click to select
      function selectPartial(el){
        const key = el.getAttribute('data-partial-key');
        if (!key) return;
        const color = palette[key] || '#4e79a7';
        // Outline selection
        document.querySelectorAll('.sv-partial').forEach(n => n.style.outline='');
        el.style.outline = `2px solid ${color}`;
        el.style.outlineOffset = '2px';
        // Compose section title (custom mapping for TLAS/TLA)
        const sectionTitleMap = {
          'tla': 'Teaching, Learning, and Assessment (TLA) Activities',
          'tlas': 'Teaching, Learning, and Assessment Strategies',
          'criteria-assessment': 'Criteria for Assessment',
          'ilo': 'Intended Learning Outcomes (ILO)',
          'assessment-tasks-distribution': 'Assessment Method and Distribution Map',
          'iga': 'Institutional Graduate Attributes (IGA)',
          'so': 'Student Outcomes (SO)',
          'cdio': 'CDIO Framework Skills (CDIO)',
          'sdg': 'Sustainable Development Goals (SDG)',
          'ilo-so-cpa-mapping': 'ILO-SO and ILO-CPA Mapping',
          'ilo-iga-mapping': 'ILO-IGA Mapping',
          'ilo-cdio-sdg-mapping': 'ILO-CDIO and ILO-SDG Mapping'
        };
        const sectionTitle = sectionTitleMap[key] || key.replace(/-/g,' ').replace(/\b\w/g, m=>m.toUpperCase());
        // Ensure one comment card per partial; allow multiple cards overall
        if (commentsEl) {
          // Ensure an empty placeholder exists when no comments are present
          const ensureEmptyPlaceholder = () => {
            const hasCards = !!commentsEl.querySelector('.sv-comment-card');
            let emptyEl = commentsEl.querySelector('.sv-comment-empty');
            if (!hasCards && !emptyEl) {
              emptyEl = document.createElement('div');
              emptyEl.className = 'sv-comment-empty';
              emptyEl.innerHTML = '<div class="sv-comment-empty-inner"><i class="bi bi-chat-left"></i><div class="sv-comment-empty-text">Select a section to add comments</div></div>';
              commentsEl.appendChild(emptyEl);
              commentsEl.classList.add('sv-comments-empty');
            }
            if (hasCards && emptyEl) { emptyEl.remove(); commentsEl.classList.remove('sv-comments-empty'); }
          };
          ensureEmptyPlaceholder();
          const existing = commentsEl.querySelector(`.sv-comment-card[data-partial-key="${key}"]`);
          if (existing) {
            existing.classList.add('sv-comment-card-active');
            existing.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            setTimeout(() => existing.classList.remove('sv-comment-card-active'), 600);
          } else {
            const card = document.createElement('div');
            card.className = 'sv-comment-card';
            card.setAttribute('data-partial-key', key);
            card.style.borderLeftColor = color;
            const closeId = 'svClose_'+Math.random().toString(36).slice(2);
            card.innerHTML = `
              <div class="sv-card-header">
                <span class="sv-card-title" contenteditable="true" spellcheck="false">${sectionTitle}</span>
                <button type="button" class="sv-card-close" id="${closeId}" aria-label="Close">✕</button>
              </div>
              <div class="sv-card-body">
                <textarea class="sv-comment-input" rows="3" placeholder="Write a comment…"></textarea>
              </div>`;
            commentsEl.appendChild(card);
            // Remove placeholder now that a card exists
            const emptyEl = commentsEl.querySelector('.sv-comment-empty');
            if (emptyEl) { emptyEl.remove(); commentsEl.classList.remove('sv-comments-empty'); }
            const closeBtn = card.querySelector('#'+closeId);
            if (closeBtn) closeBtn.addEventListener('click', function(){ 
              card.remove(); 
              // Restore placeholder if no cards remain
              const remaining = commentsEl.querySelectorAll('.sv-comment-card').length;
              if (remaining === 0) {
                ensureEmptyPlaceholder();
              }
            });
            // Auto-save on edit
            const titleElEditable = card.querySelector('.sv-card-title');
            const bodyEl = card.querySelector('.sv-comment-input');
            const syllabusId = document.getElementById('syllabus-document')?.getAttribute('data-syllabus-id');
            async function saveComment(){
              const token = getCsrfToken();
              const fd = new FormData();
              fd.append('partial_key', key);
              fd.append('title', titleElEditable?.textContent || '');
              fd.append('body', bodyEl?.value || '');
              try {
                await fetch(`/faculty/syllabi/${syllabusId}/comments`, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'Accept':'application/json' }, body: fd });
              } catch(e) { console.warn('Save comment failed', e); }
            }
            let saveTimer;
            function scheduleSave(){ clearTimeout(saveTimer); saveTimer = setTimeout(saveComment, 450); }
            if (titleElEditable) titleElEditable.addEventListener('input', scheduleSave);
            if (bodyEl) { bodyEl.addEventListener('input', scheduleSave); bodyEl.addEventListener('blur', saveComment); }
          }
        }
      }
      // Seed default placeholder on load
      if (commentsEl) {
        const hasCards = !!commentsEl.querySelector('.sv-comment-card');
        const hasPlaceholder = !!commentsEl.querySelector('.sv-comment-empty');
        if (!hasCards && !hasPlaceholder) {
          const emptyEl = document.createElement('div');
          emptyEl.className = 'sv-comment-empty';
          emptyEl.innerHTML = '<div class="sv-comment-empty-inner"><i class="bi bi-chat-left"></i><div class="sv-comment-empty-text">Select a section to add comments</div></div>';
          commentsEl.appendChild(emptyEl);
          commentsEl.classList.add('sv-comments-empty');
        }
      }
      document.addEventListener('click', function(ev){
        const el = ev.target.closest('.sv-partial');
        if (el) {
          const key = el.getAttribute('data-partial-key');
          if (el.hasAttribute('data-non-commentable') || key === 'status') return; // skip non-commentable sections
          ev.preventDefault(); ev.stopPropagation(); selectPartial(el);
        }
      });

      // -----------------------------
      // Wire right toolbar Approve / Revision
      // -----------------------------
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

      // Inject URLs from Blade for this syllabus
      const approveUrl = '{{ route('faculty.syllabi.approve',$syllabus->id) }}';
      const revisionUrl = '{{ route('faculty.syllabi.revision',$syllabus->id) }}';
      if (approveBtnRight) approveBtnRight.dataset.approveUrl = approveUrl;
      if (reviseBtnRight) reviseBtnRight.dataset.revisionUrl = revisionUrl;

      if (approveBtnRight) {
        approveBtnRight.addEventListener('click', function(){
          const url = approveBtnRight.dataset.approveUrl || '';
          if (!url || !approveConfirmBtn || !approveConfirmModalEl) return;
          approveConfirmBtn.dataset.approveUrl = url;
          // Move modal to body to avoid parent stacking contexts
          try { if (approveConfirmModalEl.parentElement !== document.body) document.body.appendChild(approveConfirmModalEl); } catch(e) {}
          let modal = (bootstrap.Modal && bootstrap.Modal.getInstance) ? bootstrap.Modal.getInstance(approveConfirmModalEl) : null;
          if (!modal) { modal = new bootstrap.Modal(approveConfirmModalEl); }
          modal.show();
        });
      }

      if (reviseBtnRight) {
        reviseBtnRight.addEventListener('click', function(){
          const url = reviseBtnRight.dataset.revisionUrl || '';
          if (!url || !revisionConfirmBtn || !revisionModalEl) return;
          revisionConfirmBtn.dataset.revisionUrl = url;
          // Move modal to body to avoid parent stacking contexts
          try { if (revisionModalEl.parentElement !== document.body) document.body.appendChild(revisionModalEl); } catch(e) {}
          let modal = (bootstrap.Modal && bootstrap.Modal.getInstance) ? bootstrap.Modal.getInstance(revisionModalEl) : null;
          if (!modal) { modal = new bootstrap.Modal(revisionModalEl); }
          // no client-side revision fields; handled by backend
          modal.show();
        });
      }

      // Modal confirm actions
      if (approveConfirmBtn && approveConfirmModalEl) {
        approveConfirmBtn.addEventListener('click', async function(){
          const url = approveConfirmBtn.dataset.approveUrl || '';
          if (!url) return;
          const token = getCsrfToken();
          try {
            setLoading(approveConfirmBtn, true);
            const res = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' } });
            if (!res.ok) {
              let msg = 'Approval failed';
              try { const j = await res.json(); if (j && j.message) msg = j.message; } catch(e) {}
              if (window.showAlertOverlay) { window.showAlertOverlay('error', msg); } else { alert(msg); }
              setLoading(approveConfirmBtn, false);
              return;
            }
            // hide modal
            try {
              let modal = (bootstrap.Modal && bootstrap.Modal.getInstance) ? bootstrap.Modal.getInstance(approveConfirmModalEl) : null;
              if (!modal) { modal = new bootstrap.Modal(approveConfirmModalEl); }
              modal.hide();
            } catch(e) {}
            if (window.showAlertOverlay) { window.showAlertOverlay('success', 'Syllabus approved successfully'); } else { alert('Approved'); }
            // Redirect back to approvals listing after successful approval
            try {
              setTimeout(() => { window.location.href = '{{ route('faculty.syllabi.approvals') }}'; }, 400);
            } catch(e) { window.location.href = '{{ route('faculty.syllabi.approvals') }}'; }
          } catch (err) {
            console.error(err);
            if (window.showAlertOverlay) { window.showAlertOverlay('error', 'Unexpected error while approving.'); } else { alert('Unexpected error while approving.'); }
          } finally {
            setLoading(approveConfirmBtn, false);
          }
        });
      }

      if (revisionConfirmBtn && revisionModalEl) {
        revisionConfirmBtn.addEventListener('click', async function(){
          const url = revisionConfirmBtn.dataset.revisionUrl || '';
          if (!url) return;
          const token = getCsrfToken();
          try {
            setLoading(revisionConfirmBtn, true);
            const fd = new FormData();
            const res = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }, body: fd });
            if (!res.ok) {
              let msg = 'Action failed';
              try { const j = await res.json(); if (j && j.message) msg = j.message; } catch(e) {}
              if (window.showAlertOverlay) { window.showAlertOverlay('error', msg); } else { alert(msg); }
              setLoading(revisionConfirmBtn, false);
              return;
            }
            // hide modal
            try {
              let modal = (bootstrap.Modal && bootstrap.Modal.getInstance) ? bootstrap.Modal.getInstance(revisionModalEl) : null;
              if (!modal) { modal = new bootstrap.Modal(revisionModalEl); }
              modal.hide();
            } catch(e) {}
            // Save non-empty comments for current batch
            try {
              const commentsWrap = document.getElementById('svReviewComments');
              const syllabusId = document.getElementById('syllabus-document')?.getAttribute('data-syllabus-id');
              const token2 = token;
              const cards = Array.from(commentsWrap ? commentsWrap.querySelectorAll('.sv-comment-card') : []);
              for (const card of cards) {
                const key = card.getAttribute('data-partial-key');
                const title = (card.querySelector('.sv-card-title')?.textContent || '').trim();
                const body = (card.querySelector('.sv-comment-input')?.value || '').trim();
                if ((!title || title === '') && (!body || body === '')) continue; // skip null/empty comments
                const fdComment = new FormData();
                fdComment.append('partial_key', key || '');
                if (title) fdComment.append('title', title);
                if (body) fdComment.append('body', body);
                await fetch(`/faculty/syllabi/${syllabusId}/comments`, { method: 'POST', headers: { 'X-CSRF-TOKEN': token2, 'Accept':'application/json' }, body: fdComment });
              }
            } catch(e) { console.warn('Saving comments on return failed', e); }
            // revision metadata is updated server-side
            if (window.showAlertOverlay) { window.showAlertOverlay('success', 'Revision requested successfully'); } else { alert('Revision requested'); }
            // Redirect back to approvals listing after successful revision request
            try {
              setTimeout(() => { window.location.href = '{{ route('faculty.syllabi.approvals') }}'; }, 400);
            } catch(e) { window.location.href = '{{ route('faculty.syllabi.approvals') }}'; }
          } catch (err) {
            console.error(err);
            if (window.showAlertOverlay) { window.showAlertOverlay('error', 'Unexpected error while processing action.'); } else { alert('Unexpected error while processing action.'); }
          } finally {
            setLoading(revisionConfirmBtn, false);
          }
        });
      }

      // Do not load previous batches; reviewers add new comments each round.
    })();
    // Helper: open submit modal via proxy (used in draft and non-draft)
    function openSubmitModal(){
      const syllabusId = document.getElementById('syllabus-document')?.getAttribute('data-syllabus-id');
      if (!syllabusId) return;
      try {
        const proxy = document.getElementById('syllabusSubmitProxyBtn');
        if (proxy) {
          proxy.setAttribute('data-syllabus-id', String(syllabusId));
          proxy.setAttribute('data-status', '{{ $syllabus->submission_status ?? 'draft' }}');
          proxy.setAttribute('data-department-id', '{{ $syllabus->program->department_id ?? '' }}');
          proxy.setAttribute('data-program-id', '{{ $syllabus->program->id ?? '' }}');
          try { proxy.click(); } catch(e){
            const modalEl = document.getElementById('submitSyllabusModal');
            if (modalEl) {
              try { if (modalEl.parentElement !== document.body) document.body.appendChild(modalEl); } catch(err){}
              if (window.bootstrap && bootstrap.Modal) { new bootstrap.Modal(modalEl).show(); }
            }
          }
        }
      } catch(e) {}
    }

    // -----------------------------
    // Non-review mode: toggle right toolbar to view reviewer comments (read-only)
    // -----------------------------
    if (!isReview && !isDraft && !isLockedSubmitted) {
      const toggleBtn = document.getElementById('syllabusCommentsToggleBtn');
      const rightBar = document.querySelector('.syllabus-right-toolbar');
      const commentsEl = rightBar ? rightBar.querySelector('#svReviewComments') : null;
      const syllabusId = document.getElementById('syllabus-document')?.getAttribute('data-syllabus-id');
      const docEl = document.getElementById('syllabus-document');
      const countBadge = document.getElementById('comments-count-badge');
      const submitBtn = document.getElementById('syllabusSubmitBtn');
      if (rightBar) {
        rightBar.style.display = 'none';
        rightBar.classList.add('viewer-only');
        const handle = rightBar.querySelector('.right-resize-handle');
        if (handle) handle.style.display = 'none';
      }
      if (docEl) {
        try { docEl.style.setProperty('--right-toolbar-w', '0px'); } catch(e) {}
      }
      function humanize(key){ return (key || '').replace(/-/g,' ').replace(/\b\w/g,m=>m.toUpperCase()); }
      function getCsrfToken() { const meta = document.querySelector('meta[name="csrf-token"]'); return meta ? meta.getAttribute('content') : ''; }
      async function submitForReview(){
        if (submitBtn) { submitBtn.disabled = true; submitBtn.classList.add('disabled'); }
        try { openSubmitModal(); } finally { if (submitBtn) { submitBtn.disabled = false; submitBtn.classList.remove('disabled'); } }
      }
      async function loadComments(){
        if (!commentsEl || rightBar.dataset.loaded) return;
        try {
          const res = await fetch(`/faculty/syllabi/${syllabusId}/comments`, { headers: { 'Accept':'application/json' } });
          if (!res.ok) return;
          const j = await res.json();
          const batch = j.currentBatch;
          const byBatch = j.commentsByBatch || {};
          const list = byBatch[String(batch)] || byBatch[batch] || [];
          if (countBadge) {
            const n = Array.isArray(list) ? list.length : 0;
            if (n > 0) { countBadge.textContent = String(n); countBadge.style.display = 'inline-block'; }
            else { countBadge.style.display = 'none'; }
          }
          if (list.length === 0) {
            commentsEl.innerHTML = '';
            const emptyEl = document.createElement('div');
            emptyEl.className = 'sv-comment-empty';
            emptyEl.innerHTML = '<div class="sv-comment-empty-inner"><i class="bi bi-chat-left"></i><div class="sv-comment-empty-text">No reviewer comments</div></div>';
            commentsEl.appendChild(emptyEl);
            commentsEl.classList.add('sv-comments-empty');
          } else {
            commentsEl.innerHTML = '';
            list.forEach(c => {
              const card = document.createElement('div');
              card.className = 'sv-comment-card';
              card.style.borderLeftColor = '#475569';
              card.setAttribute('data-partial-key', c.partial_key || '');
              card.innerHTML = `<div class="sv-card-header"><span class="sv-card-title">${humanize(c.partial_key)}</span></div><div class="sv-card-body"><div class="sv-comment-text small" style="white-space:pre-wrap;">${(c.body || '').replace(/[<>]/g,'')}</div></div>`;
              // Click to scroll to the corresponding partial in the document
              card.addEventListener('click', function(){
                const key = card.getAttribute('data-partial-key');
                if (!key) return;
                const target = document.querySelector(`.sv-partial[data-partial-key="${CSS.escape(key)}"]`);
                if (target) {
                  target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                  // brief highlight
                  target.style.outline = '2px solid var(--sv-accent, #3b82f6)';
                  target.style.outlineOffset = '2px';
                  setTimeout(() => { target.style.outline=''; target.style.outlineOffset=''; }, 1200);
                }
              });
              commentsEl.appendChild(card);
            });
          }
          rightBar.dataset.loaded = '1';
        } catch(e) { commentsEl.innerHTML = '<div class="text-danger small">Failed to load comments.</div>'; }
      }
      async function loadCommentsCount(){
        try {
          const res = await fetch(`/faculty/syllabi/${syllabusId}/comments`, { headers: { 'Accept':'application/json' } });
          if (!res.ok) return;
          const j = await res.json();
          const batch = j.currentBatch;
          const byBatch = j.commentsByBatch || {};
          const list = byBatch[String(batch)] || byBatch[batch] || [];
          // Fallback: total across all batches if current batch empty
          const total = Object.values(byBatch).reduce((acc, v) => acc + (Array.isArray(v) ? v.length : 0), 0);
          const n = Array.isArray(list) ? list.length : 0;
          const showCount = n > 0 ? n : total > 0 ? total : 0;
          if (countBadge) {
            if (showCount > 0) {
              countBadge.textContent = String(showCount);
              countBadge.style.display = 'inline-block';
              countBadge.classList.add('sv-badge-visible');
            } else {
              countBadge.style.display = 'none';
              countBadge.classList.remove('sv-badge-visible');
            }
          }
          // Debug: surface basic info in console for quick checks
          try { console.debug('[CommentsCount]', { batch, current: n, total }); } catch(e){}
        } catch(e) {}
      }
      // Prefetch count for badge on load (do this even if rightBar is missing)
      loadCommentsCount();

      // Lightweight resize initializer for viewer mode
      function initViewerResize(){
        if (!rightBar || rightBar.dataset.viewerResizeInit) return;
        const handle = rightBar.querySelector('.right-resize-handle');
        if (!handle) return;
        rightBar.dataset.viewerResizeInit = '1';
        function getMaxPx(){ return Math.floor((docEl?.clientWidth || window.innerWidth) * 0.25); }
        const MIN_PX = 240;
        function applyW(px){
          const maxPx = getMaxPx();
          const effectiveMin = Math.min(MIN_PX, maxPx);
          const w = Math.max(effectiveMin, Math.min(px, maxPx));
          if (docEl) docEl.style.setProperty('--right-toolbar-w', w + 'px');
          rightBar.style.width = w + 'px';
          try { localStorage.setItem('sv_right_toolbar_w', String(w)); } catch(e){}
        }
        let dragging = false;
        function onMove(ev){
          if (!dragging) return;
          const x = ev.touches && ev.touches[0] ? ev.touches[0].clientX : ev.clientX;
          const rect = (docEl || document.body).getBoundingClientRect();
          const desired = rect.right - x;
          applyW(desired);
        }
        function onUp(){ dragging = false; document.body.style.userSelect=''; document.removeEventListener('mousemove', onMove); document.removeEventListener('mouseup', onUp); document.removeEventListener('touchmove', onMove); document.removeEventListener('touchend', onUp); }
        function onDown(ev){ dragging = true; ev.preventDefault(); ev.stopPropagation(); document.body.style.userSelect='none'; document.addEventListener('mousemove', onMove); document.addEventListener('mouseup', onUp); document.addEventListener('touchmove', function(e){ onMove(e); e.preventDefault(); }, { passive:false }); document.addEventListener('touchend', onUp); }
        handle.addEventListener('mousedown', onDown);
        handle.addEventListener('touchstart', onDown, { passive:true });
        window.addEventListener('resize', function(){ const varVal = getComputedStyle(docEl || document.body).getPropertyValue('--right-toolbar-w').trim(); const w = parseInt(varVal || '300', 10) || 300; applyW(w); });
      }

      if (toggleBtn && rightBar) {
        toggleBtn.addEventListener('click', function(){
          const isHidden = rightBar.style.display === 'none';
          rightBar.style.display = isHidden ? 'flex' : 'none';
          if (isHidden) {
            // set width and document padding when showing
            let w = parseInt((() => { try { return localStorage.getItem('sv_right_toolbar_w') || '300'; } catch(e){ return '300'; } })(), 10);
            if (isNaN(w) || w <= 0) { w = rightBar.getBoundingClientRect().width || 300; }
            rightBar.style.width = w + 'px';
            if (docEl) { try { docEl.style.setProperty('--right-toolbar-w', w + 'px'); } catch(e) {} }
            // show resize handle and enable viewer resize
            const handle = rightBar.querySelector('.right-resize-handle');
            if (handle) { handle.style.display = 'block'; }
            initViewerResize();
            loadComments();
          } else {
            // remove reserved space when hiding
            if (docEl) { try { docEl.style.setProperty('--right-toolbar-w', '0px'); } catch(e) {} }
            const handle = rightBar.querySelector('.right-resize-handle');
            if (handle) { handle.style.display = 'none'; }
          }
          toggleBtn.classList.toggle('active', isHidden);
        });
        // Refresh count whenever toggled open
        // handled inside click handler using local isHidden
      }
      if (submitBtn) {
        submitBtn.addEventListener('click', submitForReview);
      }
    }
    // Draft mode: show submit button and wire it even though comments are hidden
    if (!isReview && isDraft && !isLockedSubmitted) {
      const submitBtn = document.getElementById('syllabusSubmitBtn');
      if (submitBtn) {
        submitBtn.addEventListener('click', function(){
          submitBtn.disabled = true; submitBtn.classList.add('disabled');
          try { openSubmitModal(); } finally { submitBtn.disabled = false; submitBtn.classList.remove('disabled'); }
        });
      }
    }
    // Draft mode: ensure right toolbar never shows (safety) and skip comment logic
    if (isDraft || (isLockedSubmitted && !isReview)) {
      const rt = document.querySelector('.syllabus-right-toolbar');
      if (rt) rt.remove();
    }
    // Locked submitted (pending_review/approved/final_approval): force read-only like review but without comments
    if (!isReview && isLockedSubmitted) {
      try {
        document.getElementById('syllabus-document')?.classList.add('is-submitted-locked');
        // Disable all form fields
        Array.from(document.querySelectorAll('.syllabus-content-wrapper input, .syllabus-content-wrapper textarea, .syllabus-content-wrapper select, .syllabus-content-wrapper button'))
          .forEach(el => {
            if (el.closest('.sv-partial')) {
              if (el.tagName === 'BUTTON') { el.disabled = true; }
              else { el.setAttribute('readonly','readonly'); el.setAttribute('disabled','disabled'); }
            }
          });
        // Hide Save button
        const saveBtn = document.getElementById('syllabusSaveBtn'); if (saveBtn) saveBtn.style.display='none';
        const submitBtn = document.getElementById('syllabusSubmitBtn'); if (submitBtn) submitBtn.style.display='none';
      } catch(e) {}
    }
    if (!form) return;
    const undoBtn = document.getElementById('undoBtn');
    const redoBtn = document.getElementById('redoBtn');
  const autoToggle = document.getElementById('autoSaveToggle');
    const saveBtn = document.getElementById('syllabusSaveBtn');

    const past = []; // previous states
    const future = []; // undone states
    const MAX_STACK = 25;
    let applyingSnapshot = false;
    let autoSaveEnabled = false;
    let autoSaveTimer = null;

    // Load persisted auto-save preference
    try { autoSaveEnabled = localStorage.getItem('sv_auto_save') === 'true'; } catch(e) {}
  updateAutoSaveUI();

    function captureSnapshot(){
      // Build a map name -> values; handles multiple fields with same name
      const groups = {};
      const radiosHandled = new Set();
      const elements = Array.from(form.querySelectorAll('input, textarea, select'));
      elements.forEach(el => {
        if (!el.name) return;
        const name = el.name;
        // Radio group: store selected value once
        if (el.type === 'radio') {
          if (radiosHandled.has(name)) return;
          radiosHandled.add(name);
          const selected = elements.find(e => e.type === 'radio' && e.name === name && e.checked);
          groups[name] = selected ? selected.value : null;
          return;
        }
        // Select (multiple)
        if (el.tagName === 'SELECT' && el.multiple) {
          const vals = Array.from(el.options).filter(o => o.selected).map(o => o.value);
          groups[name] = vals;
          return;
        }
        // For checkbox with multiple of same name, collect as array of booleans in DOM order
        const siblings = elements.filter(e2 => e2.name === name && e2 !== el && e2.type === el.type);
        const isMulti = siblings.length > 0;
        if (el.type === 'checkbox') {
          if (!isMulti && !(name in groups)) {
            groups[name] = !!el.checked;
          } else {
            if (!Array.isArray(groups[name])) groups[name] = [];
            groups[name].push(!!el.checked);
          }
        } else {
          if (isMulti) {
            if (!Array.isArray(groups[name])) groups[name] = [];
            groups[name].push(el.value);
          } else {
            groups[name] = el.value;
          }
        }
      });
      return groups;
    }

    function applySnapshot(snap){
      if (!snap) return;
      applyingSnapshot = true;
      Object.keys(snap).forEach(name => {
        const value = snap[name];
        const nodes = Array.from(form.elements).filter(function(n){ return n.name === name; });
        if (!nodes || nodes.length === 0) return;
        const first = nodes[0];
        if (first.type === 'radio') {
          nodes.forEach(n => { n.checked = (n.value === value); });
        } else if (first.type === 'checkbox') {
          if (Array.isArray(value)) {
            nodes.forEach((n, i) => { n.checked = !!value[i]; });
          } else {
            nodes.forEach((n, i) => { n.checked = (i === 0) ? !!value : n.checked; });
          }
        } else if (first.tagName === 'SELECT' && first.multiple) {
          const set = new Set(Array.isArray(value) ? value : []);
          nodes.forEach(n => { Array.from(n.options).forEach(o => o.selected = set.has(o.value)); });
        } else if (nodes.length > 1 && Array.isArray(value)) {
          nodes.forEach((n, i) => { n.value = (i < value.length) ? value[i] : n.value; n.dispatchEvent(new Event('input', { bubbles:true })); });
          return;
        } else {
          nodes.forEach(n => { n.value = (Array.isArray(value) ? value[0] : value); n.dispatchEvent(new Event('input', { bubbles:true })); });
        }
      });
      applyingSnapshot = false;
      updateButtons();
    }

    function pushPast(snap){
      past.push(snap);
      while (past.length > MAX_STACK) past.shift();
      future.length = 0; // clear redo on new change
      updateButtons();
    }

    function updateButtons(){
      if (undoBtn) undoBtn.disabled = past.length <= 1; // first snapshot is initial
      if (redoBtn) redoBtn.disabled = future.length === 0;
    }

    // Seed initial state
    pushPast(captureSnapshot());

    // Debounced change listener to snapshot and maybe auto-save
    let changeTimer = null;
    function onUserChange(){
      if (applyingSnapshot) return;
      try { window.isDirty = true; } catch(e){}
      clearTimeout(changeTimer);
      changeTimer = setTimeout(()=>{
        pushPast(captureSnapshot());
        scheduleAutoSave();
      }, 350);
    }
    form.addEventListener('input', onUserChange);
    form.addEventListener('change', onUserChange);

    // Undo / Redo buttons
    if (undoBtn) undoBtn.addEventListener('click', function(){
      if (past.length <= 1) return;
      const current = past.pop();
      future.push(current);
      const prev = past[past.length - 1];
      applySnapshot(prev);
    });
    if (redoBtn) redoBtn.addEventListener('click', function(){
      if (future.length === 0) return;
      const next = future.pop();
      past.push(next);
      applySnapshot(next);
    });

    // Keyboard shortcuts: Ctrl+Z / Ctrl+Y or Ctrl+Shift+Z
    document.addEventListener('keydown', function(e){
      const key = (e.key || '').toLowerCase();
      if (e.ctrlKey && !e.shiftKey && key === 'z') { e.preventDefault(); if (undoBtn && !undoBtn.disabled) undoBtn.click(); }
      else if ((e.ctrlKey && key === 'y') || (e.ctrlKey && e.shiftKey && key === 'z')) { e.preventDefault(); if (redoBtn && !redoBtn.disabled) redoBtn.click(); }
    });

    // Auto-Save toggle + UI (switch)
    if (autoToggle) autoToggle.addEventListener('change', function(){
      autoSaveEnabled = !!autoToggle.checked;
      try { localStorage.setItem('sv_auto_save', autoSaveEnabled ? 'true' : 'false'); } catch(e){}
      updateAutoSaveUI();
      if (autoSaveEnabled) scheduleAutoSave();
    });

    function updateAutoSaveUI(){
      if (!autoToggle) return;
      autoToggle.checked = !!autoSaveEnabled;
      autoToggle.setAttribute('aria-checked', autoSaveEnabled ? 'true' : 'false');
      autoToggle.title = autoSaveEnabled ? 'Auto-Save: On' : 'Auto-Save: Off';
    }

    function scheduleAutoSave(){
      if (!autoSaveEnabled) return;
      if (!saveBtn) return;
      if (window._syllabusSaveLock) return;
      clearTimeout(autoSaveTimer);
      autoSaveTimer = setTimeout(()=>{
        if (window._syllabusSaveLock) return;
        if (saveBtn) {
          try { saveBtn.click(); } catch(e){}
        }
      }, 1500);
    }

    // Expose for debugging
    try { window._svUndoRedo = { past, future, capture: captureSnapshot, apply: applySnapshot }; } catch(e){}
  });
</script>
@endpush

@push('styles')
<style>
  /* Toolbar look – align with index page */
  .programs-toolbar { display:flex; align-items:center; flex-wrap:wrap; gap:.25rem; }
  .programs-toolbar .input-group {
    flex:1; background: var(--sv-bg, #FAFAFA);
    border: 1px solid var(--sv-border, #E3E3E3);
    border-radius: 6px; overflow: hidden;
    box-shadow: 0 1px 2px rgba(0,0,0,0.02);
  }
  .programs-toolbar .input-group .form-control { padding:.4rem .75rem; font-size:.88rem; border:none; background:transparent; height:2.2rem; }
  .programs-toolbar .input-group .form-control:focus { outline:none; box-shadow:none; background:transparent; }
  .programs-toolbar .input-group .input-group-text { background:transparent; border:none; padding-left:.7rem; padding-right:.4rem; display:flex; align-items:center; }
  .svx-card { background:#fff; border:1px solid var(--sv-border,#E3E3E3); border-radius:10px; }
  .svx-card-body { padding: .85rem; }
  @keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }
  /* Layout: toolbar left, content right; only content scrolls */
  .syllabus-doc { display:flex; flex-wrap:nowrap; align-items:stretch; height:100vh; width:100%; min-width:0; position:relative; padding-right: var(--right-toolbar-w, 0px); }
  .syllabus-vertical-toolbar { position:relative; width: var(--toolbar-w, 78px); flex: 0 0 var(--toolbar-w, 78px); flex-shrink:0; order:0; box-sizing:border-box; background:#fff; border-right:1px solid #e2e5e9; padding:10px 8px; display:flex; flex-direction:column; }
  .syllabus-vertical-toolbar .toolbar-inner { display:flex; flex-direction:column; align-items:center; }
  .syllabus-vertical-toolbar .toolbar-btn { width:100%; }
  /* Ensure comments badge becomes visible when count > 0 */
  #syllabusCommentsToggleBtn { position: relative; }
  /* Absolute, top-right yellow badge with black text */
  #syllabusCommentsToggleBtn #comments-count-badge,
  #syllabusCommentsToggleBtn .sv-comments-badge {
    position: absolute;
    top: 4px;
    right: 6px;
    background: #ffc107; /* yellow */
    color: #111; /* black-ish for readability */
    border-radius: 999px;
    padding: 0 6px;
    line-height: 18px;
    height: 18px;
    min-width: 18px;
    font-size: 11px;
    font-weight: 700;
    text-align: center;
    box-shadow: 0 1px 2px rgba(0,0,0,0.12);
  }
  #syllabusCommentsToggleBtn #comments-count-badge.sv-badge-visible { display: inline-block !important; }
  .syllabus-content-wrapper { flex:1 1 auto; order:1; padding:16px 20px; overflow-y:auto; overflow-x:auto; min-height:0; min-width:0; position: relative; z-index: 1; }
  .syllabus-right-toolbar { position:fixed; right:0; top:0; height:100vh; width: var(--right-toolbar-w, 78px); box-sizing:border-box; background:#fff; border-left:1px solid #e2e5e9; padding:10px 8px; display:flex; flex-direction:column; z-index: 10; margin:0 !important; }
  .sv-partial { transition: outline-color .15s ease; }
  /* Hover affordance for partials in review mode */
  .is-review .syllabus-content-wrapper .sv-partial:not([data-partial-key="status"]):hover {
    outline: 2px solid var(--sv-accent, #3b82f6);
    background-color: rgba(59,130,246,0.06);
    cursor: pointer;
  }
  /* Improve click targeting: prevent inner controls from hijacking clicks */
  .is-review .syllabus-content-wrapper .sv-partial input,
  .is-review .syllabus-content-wrapper .sv-partial textarea,
  .is-review .syllabus-content-wrapper .sv-partial select,
  .is-review .syllabus-content-wrapper .sv-partial button {
    pointer-events: none;
  }
  /* Review mode: hide action buttons inside content partials only */
  .is-review .syllabus-content-wrapper .sv-partial .btn,
  .is-review .syllabus-content-wrapper .sv-partial button,
  .is-review .syllabus-content-wrapper .sv-partial a.btn {
    display: none !important;
  }
  /* Hide Save button in left toolbar during review mode */
  .is-review #syllabusSaveBtn { display: none !important; }
  /* Review panel layout */
  .sv-review-panel { gap: 12px; }
    .sv-review-panel { padding: 20px; gap: 12px; display:flex; flex-direction:column; height:100%; }
    .sv-review-header { padding: 10px 8px; border-bottom: 1px solid #e6e9ed; }
    .sv-review-title { font-size: 1rem; font-weight: 600; color: #333; }
  .sv-review-tag { display:inline-block; width:14px; height:14px; border-radius:3px; border:1px solid rgba(0,0,0,0.1); }
  .sv-toolbar-comment-section { display:flex; flex-direction:column; gap:12px; flex:1 1 auto; min-height:0; }
  .sv-review-comments { overflow-y:auto; padding: 4px 2px; display:flex; flex-direction:column; gap:8px; flex:1 1 auto; min-height:0; }
  /* Center placeholder when empty */
  .sv-review-comments.sv-comments-empty { align-items:center; justify-content:center; }
  .sv-toolbar-button-section { margin-top:auto; }
  /* Make toolbar action buttons equal width and fill section */
  .sv-toolbar-button-section { display:flex; gap:8px; }
  .sv-toolbar-button-section .btn { flex: 1 1 0; width: auto; }
  .sv-comment-card { border:1px solid #e6e9ed; border-left-width:4px; border-radius:8px; background:#fff; box-shadow: 0 1px 2px rgba(0,0,0,0.03); }
  /* Adaptive height for non-review comment cards */
  .sv-review-comments .sv-comment-card { width: 100%; align-self: stretch; }
  .sv-review-comments .sv-comment-card .sv-card-body { padding:8px; display:block; max-height: 240px; overflow: auto; resize: vertical; }
  .sv-review-comments .sv-comment-card .sv-comment-text { white-space: pre-wrap; word-break: break-word; }
  .sv-comment-card:hover { 
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transform: translateY(-1px);
    border-color: #d9dee5;
    cursor: pointer;
  }
  .sv-comment-card-active { animation: svCardFlash 600ms ease; }
  @keyframes svCardFlash {
    0% { box-shadow: 0 0 0 rgba(0,0,0,0); }
    25% { box-shadow: 0 0 0.35rem rgba(0,0,0,0.08); }
    100% { box-shadow: 0 0 0 rgba(0,0,0,0); }
  }
  /* Modern clean comment card header */
  .sv-comment-card .sv-card-header {
    display:flex; align-items:center; justify-content:space-between;
    padding:8px 10px; border-top-left-radius:8px; border-top-right-radius:8px;
    background: linear-gradient(180deg, #ffffff, #f8f9fb);
    border-bottom:1px solid #e6e9ed;
  }
  .sv-comment-card .sv-card-title { font-size:.82rem; font-weight:600; color:#333; outline:none; }
  .sv-comment-card .sv-card-title[contenteditable="true"]:focus { box-shadow: inset 0 -2px 0 #d0d6dc; }
  .sv-comment-card .sv-card-close { background:transparent; border:none; color:#666; font-weight:700; line-height:1; cursor:pointer; border-radius:6px; padding:0 6px; font-size:.85rem; }
  .sv-comment-card .sv-card-close:hover { background:#f0f2f5; color:#333; }
  .sv-comment-card .sv-card-body { padding:8px; }
  .sv-comment-card .sv-comment-meta { font-size:.75rem; color:#6c757d; margin-bottom:4px; }
  .sv-comment-card .sv-comment-input { width:100%; border:1px solid #e6e9ed; border-radius:8px; padding:8px; font-size:.85rem; color:#333; resize: vertical; box-sizing: border-box; }
  .sv-comment-card .sv-comment-input:focus { outline:none; border-color:#cdd3d9; box-shadow: 0 0 0 2px rgba(61,126,255,0.12); }
  /* Empty state placeholder: professional and clean */
  .sv-comment-empty { border: none; border-radius: 0; padding: 0.5rem 0.75rem; background: transparent; color: #6b7280; }
  .sv-comment-empty-inner { display:flex; align-items:center; gap:.5rem; justify-content:center; }
  .sv-comment-empty-inner i { color:#9aa0a6; font-size:1rem; }
  .sv-comment-empty-text { font-size:.88rem; }
  .sv-review-input { border-top:1px solid #e6e9ed; padding:8px; }
  .sv-review-input .form-label { font-size:.78rem; color:#555; margin-bottom:6px; }
  .sv-review-input-actions { display:flex; justify-content:flex-end; gap:8px; margin-top:8px; }
  .sv-review-actions { margin-top:auto; padding:8px; border-top:1px solid #e6e9ed; display:flex; gap:8px; }
  /* Right toolbar resize handle */
  .syllabus-right-toolbar .right-resize-handle { position:absolute; left:0; top:0; bottom:0; width:12px; cursor:ew-resize; z-index:5; background:transparent; touch-action:none; }
  /* In-toolbar grip visuals */
  .syllabus-right-toolbar .right-resize-handle::after {
    content:'';
    position:absolute;
    left:10px;
    top:0;
    bottom:0;
    width:2px;
    background:rgba(0,0,0,0.06);
  }
  .syllabus-right-toolbar .right-resize-handle::before {
    content:'';
    position:absolute;
    left:2px;
    top:50%;
    transform:translateY(-50%);
    width:8px;
    height:40px;
    background-image: radial-gradient(#b7b7b7 1px, transparent 1px);
    background-size:4px 6px;
    background-repeat:repeat;
    opacity:.9;
  }
  .syllabus-right-toolbar .right-resize-handle:hover::before { background-image: radial-gradient(#8c8c8c 1px, transparent 1px); }
  .syllabus-right-toolbar .right-resize-handle:hover::after { background: rgba(0,0,0,0.15); }
  /* (Removed) Review sidebar styles */
  
  /* Match approvals card button UI */
  .review-approve-btn, .review-revise-btn {
    padding: 0.4rem 0.7rem;
    font-size: 0.78rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    border-radius: 0.375rem;
    transition: all 0.2s ease-in-out;
    border: 1px solid transparent;
    background: #fff;
    box-shadow: none;
  }
  .review-approve-btn i, .review-revise-btn i { font-size: 0.9rem; }
  .review-approve-btn { color: #28a745; border-color: #28a745; }
  .review-approve-btn:hover {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.92), rgba(255, 255, 255, 0.66));
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(40, 167, 69, 0.15);
    border-color: #218838; color: #218838;
  }
  .review-approve-btn:focus { outline: 0; background: #fff; border-color: #228a35; box-shadow: 0 0 0 0.2rem rgba(40,167,69,0.25); color: #228a35; }
  .review-approve-btn:active { transform: translateY(0); background: #fff; border-color: #1e7e34; box-shadow: 0 2px 6px rgba(30, 126, 52, 0.15); color: #1e7e34; }
  .review-revise-btn { color: #e0a800; border-color: #ffc107; }
  .review-revise-btn:hover {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.92), rgba(255, 255, 255, 0.66));
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(255, 193, 7, 0.15);
    border-color: #e0a800; color: #e0a800;
  }
  .review-revise-btn:focus { outline: 0; background: #fff; border-color: #d39e00; box-shadow: 0 0 0 0.2rem rgba(255,193,7,0.25); color: #d39e00; }
  .review-revise-btn:active { transform: translateY(0); background: #fff; border-color: #c69500; box-shadow: 0 2px 6px rgba(198, 149, 0, 0.15); color: #c69500; }
  .review-approve-btn:disabled, .review-revise-btn:disabled { opacity: 0.6; cursor: not-allowed; pointer-events: none; }
  /* Approve confirm modal: neutral styling parity with ILO load modal */
  #approveConfirmModal { --sv-bg:#FAFAFA; --sv-bdr:#E3E3E3; }
  #approveConfirmModal .modal-content { border:1px solid var(--sv-bdr); border-radius:16px; background:#fff; box-shadow:0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06); overflow:hidden; position:relative; }
  #approveConfirmModal .modal-header { border-bottom:1px solid var(--sv-bdr); background:#fff; padding:.85rem 1rem; }
  #approveConfirmModal .modal-title { font-weight:600; font-size:1rem; display:inline-flex; align-items:center; gap:.5rem; }
  #approveConfirmModal .approve-confirm-btn,
  #approveConfirmModal .btn-light { background:#fff; border:none; color:#000; transition:all .2s ease-in-out; box-shadow:none; display:inline-flex; align-items:center; gap:.5rem; padding:0.5rem 1rem; border-radius:0.375rem; font-weight:500; }
  #approveConfirmModal .approve-confirm-btn:hover,
  #approveConfirmModal .approve-confirm-btn:focus,
  #approveConfirmModal .btn-light:hover,
  #approveConfirmModal .btn-light:focus { background:linear-gradient(135deg, rgba(220,220,220,0.88), rgba(240,240,240,0.46)); box-shadow:0 4px 10px rgba(0,0,0,0.12); color:#495057; }
  #approveConfirmModal .approve-confirm-btn:active,
  #approveConfirmModal .btn-light:active { background:linear-gradient(135deg, rgba(240,242,245,0.98), rgba(255,255,255,0.62)); box-shadow:0 1px 8px rgba(0,0,0,0.16); }
  #approveConfirmModal .approve-confirm-btn:disabled,
  #approveConfirmModal .btn-light:disabled { opacity:.6; cursor:not-allowed; }
  /* Revision modal styling parity */
  #revisionModal { --sv-bg:#FAFAFA; --sv-bdr:#E3E3E3; z-index:10010 !important; }
  #revisionModal .modal-content { border:1px solid var(--sv-bdr); border-radius:16px; background:#fff; box-shadow:0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06); overflow:hidden; position:relative; z-index:10011; }
  #revisionModal .modal-header { border-bottom:1px solid var(--sv-bdr); background:#fff; padding:.85rem 1rem; }
  #revisionModal .modal-title { font-weight:600; font-size:1rem; display:inline-flex; align-items:center; gap:.5rem; }
  #revisionModal .modal-title i { width:1.05rem; height:1.05rem; }
  #revisionModal .btn-light, #revisionModal .review-revise-confirm-btn { background:#fff; border:none; color:#000; transition:all .2s ease-in-out; box-shadow:none; display:inline-flex; align-items:center; gap:.5rem; padding:0.5rem 1rem; border-radius:0.375rem; }
  #revisionModal .btn-light:hover, #revisionModal .btn-light:focus,
  #revisionModal .review-revise-confirm-btn:hover, #revisionModal .review-revise-confirm-btn:focus { background:linear-gradient(135deg, rgba(220,220,220,0.88), rgba(240,240,240,0.46)); box-shadow:0 4px 10px rgba(0,0,0,0.12); color:#495057; }
  #revisionModal .btn-light:active,
  #revisionModal .review-revise-confirm-btn:active { background:linear-gradient(135deg, rgba(240,242,245,0.98), rgba(255,255,255,0.62)); box-shadow:0 1px 8px rgba(0,0,0,0.16); }
  #revisionModal .btn-light:disabled,
  #revisionModal .review-revise-confirm-btn:disabled { opacity:.6; cursor:not-allowed; }
  /* Ensure Bootstrap modals appear above any local stacking contexts */
  .modal { z-index: 10560 !important; position: fixed; }
  .modal-backdrop { z-index: 10550 !important; }
  /* Neutralize parent stacking contexts that can trap the modal */
  .syllabus-doc,
  .syllabus-content-wrapper,
  .svx-card,
  .svx-card-body {
    transform: none !important;
    filter: none !important;
    perspective: none !important;
  }
  .syllabus-doc { position: static !important; z-index: auto !important; }
  /* (Removed) Left toolbar resize handle and drag shield */
  @media (max-width: 768px) {
    /* Keep three-column layout even on small screens; allow inner scrolls */
    .syllabus-vertical-toolbar { border-right:1px solid #e2e5e9; }
    .syllabus-vertical-toolbar .toolbar-inner { gap:12px; }
    .syllabus-content-wrapper { padding:16px; }
    /* (Removed) Left toolbar resize handle on mobile */
  }
</style>
@endpush
