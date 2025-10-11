{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/master-data/modals/add-course-modal.blade.php
* Description: Modal for adding a new course (AJAX) with searchable checkbox
*              prerequisite picker; contact-hours-only; brand-aligned UI.
-------------------------------------------------------------------------------
📜 Log:
[2025-08-17] Initial creation – modal-xl, two-column layout, searchable
             checkbox prerequisites; brand colors; JS hooks aligned to courses.js.
-------------------------------------------------------------------------------
--}}

{{-- ░░░ START: Add Course Modal ░░░ --}}
<div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <form id="addCourseForm" action="{{ route('admin.courses.store') }}" method="POST" class="modal-content">
      @csrf

      {{-- ░░░ START: Local styles (scoped to this modal) ░░░ --}}
      <style>
        /* Brand tokens */
        #addCourseModal {
          --sv-bg:   #FAFAFA;   /* light bg */
          --sv-bdr:  #E3E3E3;   /* borders */
          --sv-acct: #EE6F57;   /* accent/focus */
          --sv-danger:#CB3737;  /* primary action (danger style) */
        }
        #addCourseModal .modal-header {
          border-bottom: 1px solid var(--sv-bdr);
          background: var(--sv-bg);
        }
        #addCourseModal .sv-card {
          border: 1px solid var(--sv-bdr);
          background: #fff;
          border-radius: .75rem;
        }
        #addCourseModal .sv-section-title {
          font-size: .8rem;
          letter-spacing: .02em;
          color: #6c757d;
        }
        #addCourseModal .input-group-text {
          background: var(--sv-bg);
          border-color: var(--sv-bdr);
        }
        #addCourseModal .form-control,
        #addCourseModal .form-select {
          border-color: var(--sv-bdr);
        }
        #addCourseModal .form-control:focus,
        #addCourseModal .form-select:focus {
          border-color: var(--sv-acct);
          box-shadow: 0 0 0 .2rem rgb(238 111 87 / 15%);
        }
        #addCourseModal .btn-danger {
          background-color: var(--sv-danger);
          border-color: var(--sv-danger);
        }
        #addCourseModal .btn-danger:hover {
          background-color: #b52d2d;
          border-color: #b52d2d;
        }
        #addCourseModal .sv-divider {
          height: 1px;
          background: var(--sv-bdr);
          margin: .75rem 0;
        }

        /* Prereq list */
        #addCourseModal .prereq-list {
          max-height: 360px;
          overflow: auto;
          background: var(--sv-bg);
          border: 1px dashed var(--sv-bdr);
          border-radius: .5rem;
        }
        #addCourseModal .prereq-item {
          display: flex;
          align-items: center;
          gap: .5rem;
          padding: .5rem .6rem;
          border-radius: .5rem;
          transition: background-color .15s ease;
        }
        #addCourseModal .prereq-item:hover {
          background: #fff;
        }
        /* Fix checkbox alignment inside flex row (override Bootstrap) */
        #addCourseModal .prereq-item.form-check { padding-left: .25rem; margin: 0; }
        #addCourseModal .prereq-item .form-check-input {
          float: none;
          margin-left: 0;
          margin-right: .5rem;
          position: static;
          width: 1rem; height: 1rem;
          border-color: var(--sv-bdr);
          cursor: pointer;
        }
        #addCourseModal .prereq-item .form-check-input:checked {
          background-color: var(--sv-acct);
          border-color: var(--sv-acct);
        }
        #addCourseModal .prereq-item .form-check-label {
          margin: 0;
          display: flex;
          align-items: center;
          gap: .35rem;
          font-size: .9rem;
        }
        #addCourseModal .sv-chip {
          display: inline-block;
          font-size: .72rem;
          padding: .12rem .5rem;
          border: 1px solid var(--sv-bdr);
          border-radius: 999px;
          background: #fff;
        }
        #addCourseModal .form-check-input:checked ~ .form-check-label .sv-chip {
          background: rgba(238,111,87,.12);
          color: #b44a38;
          border-color: rgba(238,111,87,.35);
        }
      </style>
      {{-- ░░░ END: Local styles ░░░ --}}

      {{-- ░░░ START: Header ░░░ --}}
      <div class="modal-header">
        <h5 class="modal-title fw-semibold" id="addCourseModalLabel">Add New Course</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      {{-- ░░░ END: Header ░░░ --}}

      {{-- ░░░ START: Body ░░░ --}}
      <div class="modal-body">
        {{-- Inline error box (filled by JS on 422) --}}
        <div id="addCourseErrors" class="alert alert-danger d-none small mb-3" role="alert"></div>

        <div class="row g-4">
          {{-- ░░░ START: Left – Core details ░░░ --}}
          <div class="col-lg-7">
            {{-- ░░░ START: Course Details Section ░░░ --}}
            <div class="mb-2 d-flex align-items-center justify-content-between">
              <span class="sv-section-title fw-semibold">Course Details</span>
            </div>

            <div class="mb-3">
              <label for="addCourseCode" class="form-label small fw-medium text-muted">Course Code</label>
              <input type="text" class="form-control form-control-sm" id="addCourseCode" name="code" placeholder="e.g., IT 221" required>
            </div>

            <div class="mb-3">
              <label for="addCourseTitle" class="form-label small fw-medium text-muted">Course Title</label>
              <input type="text" class="form-control form-control-sm" id="addCourseTitle" name="title" placeholder="e.g., Fundamentals of Enterprise Data Management" required>
            </div>

            <div class="mb-3">
              <label for="addCourseCategory" class="form-label small fw-medium text-muted">Course Category</label>
              <input type="text" class="form-control form-control-sm" id="addCourseCategory" name="course_category" placeholder="e.g., Core, Elective, General Education" required>
            </div>

            <div class="row g-3">
              <div class="col-sm-6">
                <label for="addContactHoursLec" class="form-label small fw-medium text-muted">Contact Hours (Lecture)</label>
                <input type="number" class="form-control form-control-sm" id="addContactHoursLec" name="contact_hours_lec" placeholder="e.g., 2" min="0" required>
              </div>
              <div class="col-sm-6">
                <label for="addContactHoursLab" class="form-label small fw-medium text-muted">Contact Hours (Lab)</label>
                <input type="number" class="form-control form-control-sm" id="addContactHoursLab" name="contact_hours_lab" placeholder="e.g., 3" min="0">
              </div>
            </div>

            <div class="sv-divider"></div>

            <div class="mt-3">
              <label for="addCourseDescription" class="form-label small fw-medium text-muted">Course Rationale and Description</label>
              <textarea class="form-control" id="addCourseDescription" name="description" rows="6" style="min-height:160px" placeholder="Explain the course rationale and provide a short description (topics, scope, etc.)" required></textarea>
            </div>
            {{-- ░░░ END: Course Details Section ░░░ --}}
          </div>
          {{-- ░░░ END: Left – Core details ░░░ --}}

          {{-- ░░░ START: Right – Prerequisites (searchable checkbox list) ░░░ --}}
          <div class="col-lg-5">
            <div class="sv-card p-3 h-100 d-flex flex-column">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <label class="sv-section-title fw-semibold mb-0">Prerequisite(s)</label>
                <span class="text-muted small">Check all that apply</span>
              </div>

              {{-- ░░░ START: Search Bar ░░░ --}}
              <div class="input-group input-group-sm mb-2">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" id="addPrereqSearch" class="form-control" placeholder="Search by code or title...">
              </div>
              {{-- ░░░ END: Search Bar ░░░ --}}

              {{-- ░░░ START: Checkbox List (server-rendered fallback; JS keeps in sync) ░░░ --}}
              <div id="addPrereqList" class="prereq-list p-2">
                @forelse(($courses ?? []) as $existingCourse)
                  <div class="form-check form-check-sm py-1 px-1 prereq-item"
                       data-label="{{ strtoupper($existingCourse->code) }} {{ strtoupper($existingCourse->title) }}">
                    <input class="form-check-input" type="checkbox"
                           value="{{ $existingCourse->id }}"
                           id="addPrereqChk-{{ $existingCourse->id }}"
                           name="prerequisite_ids[]">
                    <label class="form-check-label small" for="addPrereqChk-{{ $existingCourse->id }}">
                      <span class="sv-chip fw-semibold">{{ $existingCourse->code }}</span> – {{ $existingCourse->title }}
                    </label>
                  </div>
                @empty
                  <div class="text-center text-muted small py-4">No existing courses yet.</div>
                @endforelse
              </div>
              {{-- ░░░ END: Checkbox List ░░░ --}}

              <small class="text-muted d-block mt-2">
                Tip: Use the search box to filter quickly. Newly added or deleted courses stay in sync.
              </small>
            </div>
          </div>
          {{-- ░░░ END: Right – Prerequisites (searchable checkbox list) ░░░ --}}
        </div>
      </div>
      {{-- ░░░ END: Body ░░░ --}}

      {{-- ░░░ START: Footer ░░░ --}}
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger" id="addCourseSubmit">
          <i data-feather="plus"></i> Create Course
        </button>
      </div>
      {{-- ░░░ END: Footer ░░░ --}}
    </form>
  </div>
</div>
{{-- ░░░ END: Add Course Modal ░░░ --}}
