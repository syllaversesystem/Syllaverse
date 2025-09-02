{{-- 
------------------------------------------------
* File: resources/views/faculty/syllabus/syllabus.blade.php
* Description: CIS-format editable syllabus page with export, aligned header, and modular assets (Syllaverse)
------------------------------------------------ 
--}}

@extends('layouts.faculty')

@section('content')
  {{-- Assets --}}
  @vite([
    'resources/css/faculty/syllabus.css',
    'resources/js/faculty/syllabus.js',
    'resources/js/faculty/syllabus-ilo.js',
    'resources/js/faculty/syllabus-so.js',
    'resources/js/faculty/syllabus-tla-ai.js',
    'resources/js/faculty/syllabus-textbook.js',
  ])

  {{-- Global JS Variables --}}
  <script>
    const syllabusExitUrl = @json(route('faculty.syllabi.index'));
    const syllabusId = @json($default['id']);
  </script>

  @php
    // expose per-syllabus courseInfo to all partials so they render current values
    $local = $syllabus->courseInfo ?? null;
  @endphp

  <div class="container-fluid px-0 my-3 syllabus-doc">
    {{-- ===== START: Main Syllabus Form (Sections 1–4) ===== --}}
    <form id="syllabusForm"
          method="POST"
          action="{{ route('faculty.syllabi.update', $default['id']) }}"
          enctype="multipart/form-data">
      @csrf
      @method('PUT')

      {{-- ===== TOP TOOLBAR (visual only, no extra JS behavior) ===== --}}
      <div class="syllabus-toolbar mb-4 p-2 bg-white border rounded d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
          <button type="submit" class="btn btn-danger fw-semibold" id="syllabusSaveBtn">
            <i class="bi bi-save"></i>
            <span class="ms-1">Save</span>
            <span class="badge bg-light text-danger ms-2" id="unsaved-count-badge" style="display:none;">0</span>
          </button>

          <a href="{{ route('faculty.syllabi.index') }}" class="btn btn-outline-secondary fw-semibold">
            <i class="bi bi-box-arrow-left"></i> Exit
          </a>
        </div>

        <div class="text-muted small">Syllabus Editor</div>

        <div class="d-flex align-items-center gap-2">
          <div class="btn-group">
            <a href="{{ route('faculty.syllabi.export.pdf', $default['id']) }}" class="btn btn-outline-danger btn-sm">
              <i class="bi bi-filetype-pdf"></i> PDF
            </a>
            <a href="{{ route('faculty.syllabi.export.word', $default['id']) }}" class="btn btn-outline-primary btn-sm">
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
      {{-- ===== END: TOP TOOLBAR ===== --}}

  {{-- Lightweight toast for save feedback (hidden by default) --}}
  <div id="svToast" class="sv-toast" role="status" aria-live="polite">Saved</div>

      {{-- ===== Section 1: Header Information =====
           Includes: Vision, Mission, Course Title, Code, Category,
           Instructor, Semester/Year, Credit Hours, Reference CMO,
           Period of Study (via partials below).
      --}}
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

    {{-- ===== Section 6: Intended Learning Outcomes (ILO) ===== --}}
    @includeIf('faculty.syllabus.partials.ilo')

    {{-- ===== Section 7: Assessment Tasks Distribution ===== --}}
    @includeIf('faculty.syllabus.partials.assessment-tasks-distribution')

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
    @includeIf('faculty.syllabus.partials.mapping-ilo-cdio-sdg')

    {{-- ===== Footer: Signatories ===== --}}
    @includeIf('faculty.syllabus.partials.footers-prepared')
  </div>

  {{-- Enhanced Save Functionality --}}
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const syllabusForm = document.getElementById('syllabusForm');
    const saveBtn = document.getElementById('syllabusSaveBtn');
    const unsavedCountBadge = document.getElementById('unsaved-count-badge');
    
  // small guard to prevent programmatic updates right after save from re-marking the form
  // Use the central `_syllabusSaveLock` used by `resources/js/faculty/syllabus.js`
  window._syllabusSaveLock = window._syllabusSaveLock || false;

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
      
      // Handle form submission
      syllabusForm.addEventListener('submit', function(e) {
        // If another handler (like the AJAX Save click) already prevented submission,
        // assume an async save is in progress and avoid toggling the UI here.
        if (e.defaultPrevented) return;

        // Ensure all modules serialize their data before submission
        // Trigger criteria serialization if criteria module is loaded
        if (window.serializeCriteriaData && typeof window.serializeCriteriaData === 'function') {
          window.serializeCriteriaData();
        }

  // Show saving state (this path is only used when the form submits normally)
  console.debug('submit listener: delegating save UI to setSyllabusSaveState', new Date(), { defaultPrevented: e.defaultPrevented });
  try { if (window.setSyllabusSaveState) window.setSyllabusSaveState('saving'); } catch (e) { /* noop */ }

        // Note: Form will submit normally, page will reload, so we don't need to reset button state
      });

        // When the main JS finishes saving, it dispatches 'syllabusSaved' — ensure we clear UI state
        window.addEventListener('syllabusSaved', function(){
          try {
            // rely on the central save lock; markAsSaved will set/clear the lock itself
            markAsSaved();
          } catch (e) { console.warn('syllabusSaved handler failed', e); }
        });
      
      // Warning for unsaved changes when leaving page
      window.addEventListener('beforeunload', function(e) {
        if (hasUnsavedChanges) {
            // if there are no unsaved changes, do nothing and keep the button disabled
            if (!hasUnsavedChanges) {
              ev.preventDefault();
              ev.stopPropagation();
              return;
            }
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
