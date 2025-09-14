{{-- 
------------------------------------------------
* File: resources/views/faculty/syllabus/syllabus.blade.php
* Description: CIS-format editable syllabus page with export, aligned header, and modular assets (Syllaverse)
------------------------------------------------ 
--}}

@extends($layout ?? 'layouts.faculty')

@section('content')
  {{-- Assets --}}
  @vite([
    'resources/css/faculty/syllabus.css',
    'resources/js/faculty/syllabus.js',
    'resources/js/faculty/syllabus-ilo.js',
    'resources/js/faculty/syllabus-so.js',
    'resources/js/faculty/syllabus-tla-ai.js',
    'resources/js/faculty/syllabus-textbook.js',
    'resources/js/faculty/syllabus-iga-sortable.js',
  ])

  <style>
    /* Main container to group primary syllabus modules visually */
    .syllabus-main-container { display:block; border: 1px solid transparent; padding: 0; margin-bottom: 1rem; }
  </style>

  {{-- Global JS Variables --}}
  <script>
  @php $rp = $routePrefix ?? 'faculty.syllabi'; @endphp
  const syllabusExitUrl = @json(route($rp . '.index'));
  const syllabusBasePath = @json(route($rp . '.index', [], false));
  window.syllabusId = @json($default['id']);
  </script>

  @php
    // expose per-syllabus courseInfo to all partials so they render current values
    $local = $syllabus->courseInfo ?? null;
  @endphp

  <div class="container-fluid px-0 my-3 syllabus-doc">
    {{-- ===== START: Main Syllabus Form (Sections 1–4) ===== --}}
    <form id="syllabusForm"
      method="POST"
      action="{{ route(($routePrefix ?? 'faculty.syllabi') . '.update', $default['id']) }}"
          enctype="multipart/form-data">
      @csrf
      @method('PUT')

      {{-- ===== TOP TOOLBAR (sticky ribbon-style toolbar) ===== --}}
      <div class="syllabus-toolbar-wrap mb-4">
        <div class="syllabus-toolbar p-2 bg-white border rounded d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
          <button type="submit" class="btn btn-danger fw-semibold" id="syllabusSaveBtn">
            <i class="bi bi-save"></i>
            <span class="ms-1">Save</span>
            <span class="badge bg-light text-danger ms-2" id="unsaved-count-badge" style="display:none;">0</span>
          </button>

          <a href="{{ route(($routePrefix ?? 'faculty.syllabi') . '.index') }}" class="btn btn-outline-secondary fw-semibold" onclick="event.preventDefault(); try { if (typeof handleExit === 'function') { handleExit(this.href); } else { window.location.href = this.href; } } catch(e){ window.location.href = this.href; }">
            <i class="bi bi-box-arrow-left"></i> Exit
          </a>
        </div>

        <div class="text-muted small">Syllabus Editor</div>

        <div class="d-flex align-items-center gap-2">
          <div class="btn-group">
            <a href="{{ route(($routePrefix ?? 'faculty.syllabi') . '.export.pdf', $default['id']) }}" class="btn btn-outline-danger btn-sm">
              <i class="bi bi-filetype-pdf"></i> PDF
            </a>
            <a href="{{ route(($routePrefix ?? 'faculty.syllabi') . '.export.word', $default['id']) }}" class="btn btn-outline-primary btn-sm">
              <i class="bi bi-file-earmark-word"></i> Word
            </a>
          </div>

          <div class="btn-group ms-2">
            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
              Tools
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item disabled" href="#">Placeholder</a></li>
              <li><a class="dropdown-item disabled" href="#">More tools</a></li>
            </ul>
          </div>
        </div>
        </div>
      </div>
      {{-- ===== END: TOP TOOLBAR ===== --}}

  {{-- Lightweight toast for save feedback (hidden by default) --}}
  <div id="svToast" class="sv-toast" role="status" aria-live="polite">Saved</div>

  {{-- ===== Section 1: Header Information =====
           Includes: Vision, Mission, Course Title, Code, Category,
           Instructor, Semester/Year, Credit Hours, Reference CMO,
           Period of Study (via partials below).
      --}}
  <div class="syllabus-main-container">
  @includeIf('faculty.syllabus.partials.header')
  @includeIf('faculty.syllabus.partials.mission-vision')
  @includeIf('faculty.syllabus.partials.course-info')

      {{-- ===== Section 2: Course Rationale and Description =====
           Covered inside course-info partial.
      --}}

      {{-- ===== Section 3: Contact Hours =====
           Covered inside course-info partial.
      --}}

  {{-- ===== Section 4: Criteria for Assessment =====
       NOTE: This partial is included inside the `course-info` partial to keep
       the course information and criteria visually grouped. The duplicate
       include here was removed to avoid rendering the block twice. --}}
      {{-- ===== Section 5: Teaching, Learning, and Assessment Strategies ===== --}}
      {{-- Move TLA strategies inside the main form so its textarea is submitted with the Save button --}}
      @includeIf('faculty.syllabus.partials.tla-strategies')

    </form>
    {{-- ===== END: Main Syllabus Form ===== --}}

  {{-- (removed duplicate textarea; the AT partial provides a hidden textarea with form="syllabusForm") --}}

  {{-- ===== Section 6: Intended Learning Outcomes (ILO) ===== --}}
  @includeIf('faculty.syllabus.partials.ilo')

  {{-- ===== Section 7: Assessment Tasks Distribution ===== --}}
  @includeIf('faculty.syllabus.partials.assessment-tasks-distribution')
  </div>

    {{-- ===== Section 8: Textbook and References ===== --}}
    @includeIf('faculty.syllabus.partials.textbook-upload')

    {{-- ===== Section 9: Institutional Graduate Attributes (IGA) ===== --}}
    @includeIf('faculty.syllabus.partials.iga')

    {{-- ===== Section 10: Student Outcomes (SO) ===== --}}
    @includeIf('faculty.syllabus.partials.so')

    {{-- ===== Section 11: CDIO & SDG Mapping ===== --}}
    @includeIf('faculty.syllabus.partials.cdio')
    @includeIf('faculty.syllabus.partials.sdg')

    {{-- ===== Section 12: Course Policies ===== --}}
    @includeIf('faculty.syllabus.partials.course-policies')

    {{-- ===== Section 13: Teaching, Learning, and Assessment (TLA) Activities ===== --}}
    @includeIf('faculty.syllabus.partials.tla')

    {{-- ===== Section 14: Assessment Schedule ===== --}}
    @includeIf('faculty.syllabus.partials.assessment-schedule')

    {{-- ===== Section 15: SO Mapping of Assessment Tasks (AT) ===== --}}
    @includeIf('faculty.syllabus.partials.mapping-so-at')

    {{-- ===== Section 16: IGA Mapping of Assessment Tasks (AT) ===== --}}
    @includeIf('faculty.syllabus.partials.mapping-iga-at')

    {{-- ===== Section 17: ILO to CDIO/SDG Mapping ===== --}}
    {{-- mapping-ilo-cdio-sdg moved to bottom to avoid visual clutter; rendered later in the document --}}

  {{-- ===== Assessment Mapping (bottom) ===== --}}
  @includeIf('faculty.syllabus.partials.assessment-mapping')

  {{-- ===== ILO → SO → CPA partial (small 2×1 box) ===== --}}
  @includeIf('faculty.syllabus.partials.ilo-so-cpa-mapping')

  {{-- ===== ILO → IGA partial (small 2×1 box) ===== --}}
  @includeIf('faculty.syllabus.partials.ilo-iga-mapping')

  {{-- ===== ILO to CDIO/SDG Mapping (moved to bottom) ===== --}}
  @includeIf('faculty.syllabus.partials.mapping-ilo-cdio-sdg')

  {{-- ===== Footer: Signatories ===== --}}
  @includeIf('faculty.syllabus.partials.footers-prepared')
  </div>

  {{-- Enhanced Save Functionality --}}
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Sticky toolbar behavior: add 'sticky' class when scrolled past its top
    try {
      const wrap = document.querySelector('.syllabus-toolbar-wrap');
      if (wrap) {
        const orig = wrap.getBoundingClientRect().top + window.scrollY;
        const onScroll = () => {
          if (window.scrollY > orig - 12) wrap.classList.add('sticky'); else wrap.classList.remove('sticky');
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        // initial check
        onScroll();
      }
    } catch (e) {}

    // Keyboard shortcut: Ctrl/Cmd+S to save like Word
    try {
      document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
          const btn = document.getElementById('syllabusSaveBtn');
          if (btn) {
            e.preventDefault();
            btn.click();
          }
        }
      });
    } catch (e) {}
    const syllabusForm = document.getElementById('syllabusForm');
    const saveBtn = document.getElementById('syllabusSaveBtn');
    const unsavedCountBadge = document.getElementById('unsaved-count-badge');
    
  // small guard to prevent programmatic updates right after save from re-marking the form
  // Use the central `_syllabusSaveLock` used by `resources/js/faculty/syllabus.js`
  window._syllabusSaveLock = window._syllabusSaveLock || false;
  // Flag set when a programmatic submit is about to occur so beforeunload can ignore it
  window._syllabusSubmitting = window._syllabusSubmitting || false;

  if (syllabusForm && saveBtn) {
      // Track unsaved changes
      let hasUnsavedChanges = false;
      let unsavedModules = new Set();
      
      // Listen for unsaved changes from various modules
      document.addEventListener('change', function(e) {
  // Ignore synthetic/programmatic events while a save lock is active
  if (window._syllabusSaveLock) return;
        if (!e.isTrusted) return;
        if (e.target.closest('#syllabusForm')) {
          markAsUnsaved('general');
        }
      });

      document.addEventListener('input', function(e) {
  // Ignore synthetic/programmatic events while a save lock is active
  if (window._syllabusSaveLock) return;
        if (!e.isTrusted) return;
        if (e.target.closest('#syllabusForm')) {
          markAsUnsaved('general');
        }
      });
      
      // Listen for criteria module changes
      document.addEventListener('criteriaChanged', function() {
        markAsUnsaved('criteria');
      });
      
      function markAsUnsaved(module) {
        hasUnsavedChanges = true;
        unsavedModules.add(module);
        // If criteria module changed, reveal its unsaved pill so global counter picks it up
        if (module === 'criteria') {
          const critPill = document.getElementById('unsaved-criteria');
          if (critPill) critPill.classList.remove('d-none');
          // also ensure the top Save button is enabled immediately
          if (saveBtn) saveBtn.disabled = false;
        }
        updateSaveButton();
      }
      
      function markAsSaved() {
  // set save lock while we reconcile originals (use central lock)
  try { window._syllabusSaveLock = true; } catch (e) { /* noop */ }

        // Update original snapshot for all fields inside the form
        try {
          const fields = syllabusForm.querySelectorAll('input, textarea, select');
          fields.forEach((f) => {
            if (f.type === 'checkbox' || f.type === 'radio') {
              f.dataset.original = f.checked ? '1' : '0';
            } else {
              f.dataset.original = f.value ?? '';
            }
          });
        } catch (e) { /* noop */ }

        // Also update elements that are associated with the form but live outside it
        // (for example the hidden textarea `assessment_tasks_data` which uses form="syllabusForm").
        try {
          const external = Array.from(document.querySelectorAll('[form="syllabusForm"]')).filter(el => el && ['INPUT','TEXTAREA','SELECT'].includes(el.tagName));
          external.forEach((f) => {
            try {
              if (f.type === 'checkbox' || f.type === 'radio') {
                f.dataset.original = f.checked ? '1' : '0';
              } else {
                f.dataset.original = f.value ?? '';
              }
            } catch (e) { /* noop */ }
          });
        } catch (e) { /* noop */ }

        // hide unsaved pills and reset counters
        hasUnsavedChanges = false;
        unsavedModules.clear();
        document.querySelectorAll('.unsaved-pill').forEach(p => p.classList.add('d-none'));
        // hide criteria unsaved pill when saved
        const critPill = document.getElementById('unsaved-criteria');
        if (critPill) critPill.classList.add('d-none');
        updateSaveButton();

  // release save lock after a short debounce so subsequent programmatic updates don't re-mark
  setTimeout(() => { try { window._syllabusSaveLock = false; } catch (e) { /* noop */ } }, 600);
      }
      
      function updateSaveButton() {
        if (hasUnsavedChanges) {
          saveBtn.classList.add('btn-warning');
          saveBtn.classList.remove('btn-danger');
          if (unsavedCountBadge) {
            unsavedCountBadge.textContent = unsavedModules.size;
            unsavedCountBadge.style.display = 'inline-block';
          }
            // enable Save when there are unsaved changes
            try { saveBtn.disabled = false; } catch (e) { /* noop */ }
        } else {
          saveBtn.classList.remove('btn-warning');
          saveBtn.classList.add('btn-danger');
          if (unsavedCountBadge) {
            unsavedCountBadge.style.display = 'none';
          }
            // disable Save when there are no unsaved changes
            try { saveBtn.disabled = true; } catch (e) { /* noop */ }
        }
      }

      // expose helpers for other modules / debug
      try {
        window.markAsSaved = markAsSaved;
        window.markAsUnsaved = markAsUnsaved;
        window.updateSaveButton = updateSaveButton;
      } catch (e) { /* noop */ }
  // React to SDG reorder event dispatched by the sortable module as a reliable
  // cross-module channel in case load order prevents direct function calls.
  document.addEventListener('sdg:reordered', function() { try { markAsUnsaved('sdgs'); } catch (e) { /* noop */ } });
      // Extra fallback: force the top Save UI into dirty state in case other helpers
      // are not available due to load order or a save-lock race.
      document.addEventListener('sdg:reordered', function() {
        try { if (window.setSyllabusSaveState) window.setSyllabusSaveState('dirty'); } catch (e) {}
        try { const pill = document.getElementById('unsaved-sdgs'); if (pill) pill.classList.remove('d-none'); } catch (e) {}
        try { const saveBtn = document.getElementById('syllabusSaveBtn'); if (saveBtn) { saveBtn.disabled = false; saveBtn.classList.add('btn-warning'); saveBtn.classList.remove('btn-danger'); saveBtn.style.pointerEvents = 'auto'; } } catch (e) {}
      });
      
      // Handle form submission
      syllabusForm.addEventListener('submit', function(e) {
        // Allow top Save flow to bypass the pre-save logic by setting a temporary flag
        if (window._syllabusSkipPreSave) {
          // clear the flag and allow native submission to continue
          try { window._syllabusSkipPreSave = false; } catch (e) {}
          return;
        }

        // If another handler (like the AJAX Save click) already prevented submission,
        // assume an async save is in progress and avoid toggling the UI here.
        if (e.defaultPrevented) return;

        // Ensure all modules serialize their data before submission
        // Trigger criteria serialization if criteria module is loaded
        if (window.serializeCriteriaData && typeof window.serializeCriteriaData === 'function') {
          window.serializeCriteriaData();
        }

        // Ensure AT module has serialized into its hidden textarea before the form is submitted.
        // If the AT module exposes `saveAssessmentTasks`, await it (it will call serializeAT()).
        try {
          if (window.saveAssessmentTasks && typeof window.saveAssessmentTasks === 'function') {
            // prevent immediate submission until AT serialization completes
            e.preventDefault();
            window.setSyllabusSaveState && window.setSyllabusSaveState('saving');
            Promise.resolve(window.saveAssessmentTasks()).then(async () => {
              // After serialization, attempt to persist AT rows to DB using the dedicated endpoint
              try {
                if (window.postAssessmentTasksRows && typeof window.postAssessmentTasksRows === 'function') {
                  await window.postAssessmentTasksRows(syllabusId);
                }
              } catch (postErr) {
                // If posting rows fails, log to console and continue with form submit so other syllabus fields still persist
                console.warn('postAssessmentTasksRows failed, continuing with form submit', postErr);
              }
              // small tick to allow inputs to dispatch events
                setTimeout(() => {
                  // mark skip flag so the programmatic submit doesn't re-run the pre-save flow
                  try { window._syllabusSkipPreSave = true; } catch (e) {}
                  try { window._syllabusSubmitting = true; } catch (e) {}
                  syllabusForm.submit();
                }, 20);
            }).catch(() => { syllabusForm.submit(); });
            return;
          }
        } catch (err) { /* noop - fall through to normal submit */ }

  // Show saving state (this path is only used when the form submits normally)
  console.debug('submit listener: delegating save UI to setSyllabusSaveState', new Date(), { defaultPrevented: e.defaultPrevented });
  try { if (window.setSyllabusSaveState) window.setSyllabusSaveState('saving'); } catch (e) { /* noop */ }

        // Note: Form will submit normally, page will reload, so we don't need to reset button state
      });

      // Ensure clicking the top Save button explicitly runs AT save flow before submit
      try {
        saveBtn.addEventListener('click', function(ev){
          // If another handler prevented default, bail
          if (ev.defaultPrevented) return;
          // prevent the native submit so we can run AT save first
          ev.preventDefault();
          // run async save sequence
          (async function(){
            try {
              // set UI saving state
              try { window.setSyllabusSaveState && window.setSyllabusSaveState('saving'); } catch (e) {}
              // set a save lock so beforeunload won't prompt while our save is in progress
              try { window._syllabusSaveLock = true; } catch (e) {}

              // Ensure AT module serializes its data
              if (window.saveAssessmentTasks && typeof window.saveAssessmentTasks === 'function') {
                await Promise.resolve(window.saveAssessmentTasks());
              }

              // Attempt to persist AT rows to server before submitting main form
              try {
                if (window.postAssessmentTasksRows && typeof window.postAssessmentTasksRows === 'function') {
                  await window.postAssessmentTasksRows(syllabusId).catch(()=>{});
                  // clear AT unsaved pill and update global button state
                  try { const leftPill = document.getElementById('unsaved-assessment_tasks_left'); if (leftPill) leftPill.classList.add('d-none'); } catch(e){}
                  try { if (window.updateSaveButton) window.updateSaveButton(); } catch (e) {}
                }
              } catch (postErr) {
                // Swallow any errors; proceed to submit so remainder of the syllabus still saves
                console.warn('postAssessmentTasksRows failed on top Save (ignored), continuing with form submit', postErr);
              }

              // Persist SOs via their module (if present) before saving main form
              try {
                if (window.saveSo && typeof window.saveSo === 'function') {
                  await Promise.resolve(window.saveSo());
                  try { const soPill = document.getElementById('unsaved-sos'); if (soPill) soPill.classList.add('d-none'); } catch(e){}
                }
              } catch (soErr) {
                // Ignore SO save failures so the rest of the syllabus can still save
                console.warn('saveSo failed on top Save (ignored), continuing with form submit', soErr);
              }

              // Persist IGAs via their module (if present) before saving main form
              try {
                if (window.saveIga && typeof window.saveIga === 'function') {
                  await Promise.resolve(window.saveIga());
                  try { const igaPill = document.getElementById('unsaved-igas'); if (igaPill) igaPill.classList.add('d-none'); } catch(e){}
                }
              } catch (igaErr) {
                // Ignore IGA save failures so the rest of the syllabus can still save
                console.warn('saveIga failed on top Save (ignored), continuing with form submit', igaErr);
              }

              // Persist CDIOs via their module (if present) before saving main form
              try {
                if (window.saveCdio && typeof window.saveCdio === 'function') {
                  await Promise.resolve(window.saveCdio());
                  try { const cdioPill = document.getElementById('unsaved-cdios'); if (cdioPill) cdioPill.classList.add('d-none'); } catch(e){}
                }
              } catch (cdioErr) {
                console.warn('saveCdio failed on top Save (ignored), continuing with form submit', cdioErr);
              }

              // Persist SOs via their module (if present) before IGAs/main form
              try {
                if (window.saveSo && typeof window.saveSo === 'function') {
                  await Promise.resolve(window.saveSo());
                  try { const soPill = document.getElementById('unsaved-sos'); if (soPill) soPill.classList.add('d-none'); } catch(e){}
                }
              } catch (soErr) {
                console.warn('saveSo failed on top Save (ignored), continuing with form submit', soErr);
              }

              // Mark as saved to avoid beforeunload prompts and re-marking while we programmatically submit
              try { if (window.markAsSaved && typeof window.markAsSaved === 'function') window.markAsSaved(); } catch (e) {}

              // mark skip flag so the programmatic submit doesn't re-run the pre-save flow
              try { window._syllabusSkipPreSave = true; } catch (e) {}

              // Instead of submitting the page and refreshing, perform an AJAX save of the main form.
              try {
                // If the AT partial exposes a server-save helper, prefer it
                if (window.saveAssessmentTasksToServer && typeof window.saveAssessmentTasksToServer === 'function') {
                  await window.saveAssessmentTasksToServer(syllabusId).catch(()=>{});
                } else {
                  // Fallback: serialize the form into FormData and POST via fetch
                  const fd = new FormData(syllabusForm);
                  // Some inputs (for example module partials rendered outside the <form>
                  // but using the `form` attribute) may not be reliably included by
                  // FormData(form) in every environment; append them explicitly.
                  try {
                    const externals = document.querySelectorAll('[form="syllabusForm"]');
                    externals.forEach((el) => {
                      try {
                        if (!el.name) return;
                        // For checkboxes/radios, only append if checked
                        const tag = el.tagName.toUpperCase();
                        const type = (el.type || '').toLowerCase();
                        if ((type === 'checkbox' || type === 'radio')) {
                          if (el.checked) fd.append(el.name, el.value);
                          return;
                        }
                        if (tag === 'SELECT' && el.multiple) {
                          Array.from(el.options).forEach(opt => { if (opt.selected) fd.append(el.name, opt.value); });
                          return;
                        }
                        // Standard input/textarea/select
                        fd.append(el.name, el.value ?? '');
                      } catch (inner) { /* noop */ }
                    });
                  } catch (e) { /* noop */ }

                  fd.append('_method','PUT');
                  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                  if (token) fd.append('_token', token);
                  // Debug: list keys being sent so we can verify `course_policies[]` exists
                  try {
                    const keys = [];
                    for (const k of fd.keys()) keys.push(k);
                    console.debug('Top Save FormData keys', keys.slice(0,200));
                  } catch (e) { /* noop */ }
                  const resp = await fetch(syllabusForm.action, { method: 'POST', credentials: 'same-origin', body: fd });
                  if (!resp.ok) throw new Error('Server returned ' + resp.status);
                }
                // Persist TLA rows (if the module is loaded)
                try {
                  if (window.postTlaRows && typeof window.postTlaRows === 'function') {
                    await window.postTlaRows(syllabusId).catch(()=>{});
                    try { const tlaPill = document.getElementById('unsaved-tla'); if (tlaPill) tlaPill.classList.add('d-none'); } catch(e){}
                  }
                } catch (tlaErr) {
                  console.warn('postTlaRows failed during top Save (ignored)', tlaErr);
                }
                // mark as saved and update UI without reloading
                  try {
                    // Persist SDG edits (title/description/order) before finalizing save
                    if (window.saveSdg && typeof window.saveSdg === 'function') {
                      try { await Promise.resolve(window.saveSdg()); } catch (sdgErr) { console.warn('saveSdg failed during top Save (ignored)', sdgErr); }
                    }
                  } catch (e) {}
                  try { window.markAsSaved && window.markAsSaved(); } catch (e) {}
                  try { window.dispatchEvent(new CustomEvent('syllabusSaved')); } catch (e) {}
              } catch (ajaxErr) {
                console.warn('AJAX save failed, falling back to full submit', ajaxErr);
                // If AJAX fails, do the full submit as a last resort
                try { window._syllabusSubmitting = true; } catch (e) {}
                syllabusForm.submit();
              }
            } catch (err) {
              console.error('Top Save flow failed, falling back to normal submit', err);
              try { window._syllabusSubmitting = true; } catch (e) {}
              syllabusForm.submit();
            }
          })();
        });
      } catch (e) { /* noop */ }

        // When the main JS finishes saving, it dispatches 'syllabusSaved' — ensure we clear UI state
        window.addEventListener('syllabusSaved', function(){
          try {
            // rely on the central save lock; markAsSaved will set/clear the lock itself
            markAsSaved();
          } catch (e) { console.warn('syllabusSaved handler failed', e); }
        });
      
      // Warning for unsaved changes when leaving page
      window.addEventListener('beforeunload', function(e) {
        // If a programmatic save is in progress or was just triggered, do not prompt (avoid blocking save flows)
        if (window._syllabusSkipPreSave || window._syllabusSaveLock || window._syllabusSubmitting) return;
        if (hasUnsavedChanges) {
          e.preventDefault();
          e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
          return e.returnValue;
        }
      });
      
      // Initialize button state
      updateSaveButton();
    }
  });
  </script>
@endsection
