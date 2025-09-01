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
    
    if (syllabusForm && saveBtn) {
      // Track unsaved changes
      let hasUnsavedChanges = false;
      let unsavedModules = new Set();
      
      // Listen for unsaved changes from various modules
      document.addEventListener('change', function(e) {
        if (e.target.closest('#syllabusForm')) {
          markAsUnsaved('general');
        }
      });
      
      document.addEventListener('input', function(e) {
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
        updateSaveButton();
      }
      
      function markAsSaved() {
        hasUnsavedChanges = false;
        unsavedModules.clear();
        updateSaveButton();
      }
      
      function updateSaveButton() {
        if (hasUnsavedChanges) {
          saveBtn.classList.add('btn-warning');
          saveBtn.classList.remove('btn-danger');
          if (unsavedCountBadge) {
            unsavedCountBadge.textContent = unsavedModules.size;
            unsavedCountBadge.style.display = 'inline-block';
          }
        } else {
          saveBtn.classList.remove('btn-warning');
          saveBtn.classList.add('btn-danger');
          if (unsavedCountBadge) {
            unsavedCountBadge.style.display = 'none';
          }
        }
      }
      
      // Handle form submission
      syllabusForm.addEventListener('submit', function(e) {
        // Ensure all modules serialize their data before submission
        
        // Trigger criteria serialization if criteria module is loaded
        if (window.serializeCriteriaData && typeof window.serializeCriteriaData === 'function') {
          window.serializeCriteriaData();
        }
        
        // Show saving state
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> <span class="ms-1">Saving...</span>';
        saveBtn.disabled = true;
        
        // Note: Form will submit normally, page will reload, so we don't need to reset button state
      });
      
      // Warning for unsaved changes when leaving page
      window.addEventListener('beforeunload', function(e) {
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
