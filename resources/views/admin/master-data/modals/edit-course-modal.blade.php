{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/master-data/modals/edit-course-modal.blade.php
* Description: Modal for editing a course (AJAX) with clean, brand-aligned UI;
*              contact-hours-only; shows ONLY current prerequisites as
*              searchable checkboxes (uncheck to remove).
-------------------------------------------------------------------------------
📜 Log:
[2025-08-17] UI/UX refresh – modal-xl, two-column layout; searchable checkbox
             list for CURRENT prerequisites only; synced with courses.js prefill.
-------------------------------------------------------------------------------
--}}

<div class="modal fade sv-appt-modal" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <form id="editCourseForm" method="POST" class="modal-content">
      @csrf
      @method('PUT')

      {{-- ░░░ START: Local styles (scoped to this modal) ░░░ --}}
      <style>
        /* Brand tokens */
        #editCourseModal {
          --sv-bg:   #FAFAFA;   /* light bg */
          --sv-bdr:  #E3E3E3;   /* borders */
          --sv-acct: #EE6F57;   /* accent/focus */
          --sv-danger:#CB3737;  /* destructive / main action */
        }
        #editCourseModal .modal-header {
          border-bottom: 1px solid var(--sv-bdr);
          background: var(--sv-bg);
        }
        #editCourseModal .sv-card {
          border: 1px solid var(--sv-bdr);
          background: #fff;
          border-radius: .75rem;
        }
        #editCourseModal .sv-section-title {
          font-size: .8rem;
          letter-spacing: .02em;
          color: #6c757d;
        }
        #editCourseModal .input-group-text {
          background: var(--sv-bg);
          border-color: var(--sv-bdr);
        }
        #editCourseModal .form-control,
        #editCourseModal .form-select {
          border-color: var(--sv-bdr);
        }
        #editCourseModal .form-control:focus,
        #editCourseModal .form-select:focus {
          border-color: var(--sv-acct);
          box-shadow: 0 0 0 .2rem rgb(238 111 87 / 15%);
        }
        #editCourseModal .btn-primary,
        #editCourseModal .btn-danger {
          background-color: var(--sv-danger);
          border-color: var(--sv-danger);
        }
        #editCourseModal .btn-primary:hover,
        #editCourseModal .btn-danger:hover {
          background-color: #b52d2d;
          border-color: #b52d2d;
        }
        #editCourseModal .sv-divider {
          height: 1px;
          background: var(--sv-bdr);
          margin: .75rem 0;
        }

        /* Prereq list (CURRENT prereqs only) */
        #editCourseModal .prereq-list {
          max-height: 360px;
          overflow: auto;
          background: var(--sv-bg);
          border: 1px dashed var(--sv-bdr);
          border-radius: .5rem;
        }
        #editCourseModal .prereq-item {
          display: flex;
          align-items: center;
          gap: .5rem;
          padding: .5rem .6rem;
          border-radius: .5rem;
          transition: background-color .15s ease;
        }
        #editCourseModal .prereq-item:hover { background: #fff; }

        /* Fix checkbox alignment in flex row (override Bootstrap) */
        #editCourseModal .prereq-item.form-check { padding-left: .25rem; margin: 0; }
        #editCourseModal .prereq-item .form-check-input {
          float: none;
          margin-left: 0;
          margin-right: .5rem;
          position: static;
          width: 1rem; height: 1rem;
          border-color: var(--sv-bdr);
          cursor: pointer;
        }
        #editCourseModal .prereq-item .form-check-input:checked {
          background-color: var(--sv-acct);
          border-color: var(--sv-acct);
        }
        #editCourseModal .prereq-item .form-check-label {
          margin: 0;
          display: flex;
          align-items: center;
          gap: .35rem;
          font-size: .9rem;
        }
        #editCourseModal .sv-chip {
          display: inline-block;
          font-size: .72rem;
          padding: .12rem .5rem;
          border: 1px solid var(--sv-bdr);
          border-radius: 999px;
          background: #fff;
        }
        #editCourseModal .form-check-input:checked ~ .form-check-label .sv-chip {
          background: rgba(238,111,87,.12);
          color: #b44a38;
          border-color: rgba(238,111,87,.35);
        }
      </style>
      {{-- ░░░ END: Local styles ░░░ --}}

      {{-- ░░░ START: Header ░░░ --}}
      <div class="modal-header">
        <h5 class="modal-title fw-semibold" id="editCourseModalLabel">Edit Course</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      {{-- ░░░ END: Header ░░░ --}}

      {{-- ░░░ START: Body ░░░ --}}
      <div class="modal-body">
        {{-- Inline error box (filled by JS on 422) --}}
        <div id="editCourseErrors" class="alert alert-danger d-none small mb-3" role="alert"></div>

        <div class="row g-4">
          {{-- ░░░ START: Left – Core details ░░░ --}}
          <div class="col-lg-7">
            <div class="mb-2 d-flex align-items-center justify-content-between">
              <span class="sv-section-title fw-semibold">Course Details</span>
            </div>

            <div class="mb-3">
              <label for="editCourseCode" class="form-label small fw-medium text-muted">Course Code</label>
              <input type="text" class="form-control form-control-sm" id="editCourseCode" name="code" required>
            </div>

            <div class="mb-3">
              <label for="editCourseTitle" class="form-label small fw-medium text-muted">Course Title</label>
              <input type="text" class="form-control form-control-sm" id="editCourseTitle" name="title" required>
            </div>

            <div class="mb-3">
              <label for="editCourseCategory" class="form-label small fw-medium text-muted">Course Category</label>
              <input type="text" class="form-control form-control-sm" id="editCourseCategory" name="course_category" required>
            </div>

            <div class="row g-3">
              <div class="col-sm-6">
                <label for="editContactHoursLec" class="form-label small fw-medium text-muted">Contact Hours (Lecture)</label>
                <input type="number" class="form-control form-control-sm" id="editContactHoursLec" name="contact_hours_lec" min="0" required>
              </div>
              <div class="col-sm-6">
                <label for="editContactHoursLab" class="form-label small fw-medium text-muted">Contact Hours (Lab)</label>
                <input type="number" class="form-control form-control-sm" id="editContactHoursLab" name="contact_hours_lab" min="0">
              </div>
            </div>

            <div class="sv-divider"></div>

            <div class="mt-3">
              <label for="editCourseDescription" class="form-label small fw-medium text-muted">Course Rationale and Description</label>
              <textarea class="form-control" id="editCourseDescription" name="description" rows="6" style="min-height:160px" required></textarea>
            </div>
          </div>
          {{-- ░░░ END: Left – Core details ░░░ --}}

          {{-- ░░░ START: Right – CURRENT Prerequisites (searchable checkboxes) ░░░ --}}
          <div class="col-lg-5">
            <div class="sv-card p-3 h-100 d-flex flex-column">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <label class="sv-section-title fw-semibold mb-0">Current Prerequisite(s)</label>
                <span class="text-muted small">Uncheck to remove</span>
              </div>

              {{-- Search (filters within the CURRENT list only) --}}
              <div class="input-group input-group-sm mb-2">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" id="editPrereqSearch" class="form-control" placeholder="Filter by code or title...">
              </div>

              {{-- List built by JS from the selected row’s data-prereq --}}
              <div id="editPrereqList" class="prereq-list p-2">
                <div class="text-center text-muted small py-4">Will load when course is selected…</div>
              </div>

              <small class="text-muted d-block mt-2">
                Only the course’s current prerequisites are shown here. Uncheck to remove. (Adding new prerequisites is done via the Add Course flow.)
              </small>
            </div>
          </div>
          {{-- ░░░ END: Right – CURRENT Prerequisites (searchable checkboxes) ░░░ --}}
        </div>
      </div>
      {{-- ░░░ END: Body ░░░ --}}

      {{-- ░░░ START: Footer ░░░ --}}
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" id="editCourseSubmit">
          Save Changes
        </button>
      </div>
      {{-- ░░░ END: Footer ░░░ --}}
    </form>
  </div>
</div>
