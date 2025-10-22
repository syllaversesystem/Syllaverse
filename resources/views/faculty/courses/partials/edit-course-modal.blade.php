{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/courses/partials/edit-course-modal.blade.php
* Description: Modal for editing a course (AJAX) - Faculty version
-------------------------------------------------------------------------------
📜 Log:
[2025-01-XX] Adapted from admin courses module for faculty use
[2025-01-XX] Updated styling to use faculty theme colors
[2025-01-XX] Added role-based department handling for faculty users
-------------------------------------------------------------------------------
--}}

<div class="modal fade sv-course-modal" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <form id="editCourseForm" method="POST" class="modal-content course-form edit-course-form">
      @csrf
      @method('PUT')
      
      {{-- Hidden field for the update URL base (used by JavaScript) --}}
      <input type="hidden" id="editCourseUpdateUrlBase" value="{{ route('faculty.courses.update', ':id') }}">

      {{-- ░░░ START: Local styles (scoped to this modal) ░░░ --}}
      <style>
        /* Brand tokens */
        #editCourseModal {
          --sv-bg:   #FAFAFA;   /* light bg */
          --sv-bdr:  #E3E3E3;   /* borders */
          --sv-acct: #EE6F57;   /* accent/focus */
          --sv-danger:#CB3737;  /* primary action (danger style) */
        }
        #editCourseModal .modal-header {
          border-bottom: 1px solid var(--sv-bdr);
          background: var(--sv-bg);
        }
        #editCourseModal .modal-body {
          max-height: 70vh;
          overflow-y: auto;
        }
        #editCourseModal .modal-title {
          font-size: 1rem;
          font-weight: 600;
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
        #editCourseModal .form-control-sm {
          font-size: 0.875rem;
        }
        /* Form Label Typography */
        #editCourseModal .form-label {
          font-size: 0.8125rem;
          font-weight: 500;
          color: #6c757d;
          letter-spacing: 0.025em;
          margin-bottom: 0.375rem;
          text-transform: none;
        }
        /* Input Field Typography */
        #editCourseModal .form-control,
        #editCourseModal .form-control-sm {
          font-size: 0.875rem;
          font-weight: 400;
          line-height: 1.4;
          color: #495057;
        }
        #editCourseModal .form-control::placeholder,
        #editCourseModal .form-control-sm::placeholder {
          color: var(--sv-text-muted, #6c757d);
          font-size: 0.87rem;
        }
        /* Textarea specific styling - Enhanced to match admin exactly */
        #editCourseModal textarea.form-control {
          font-size: 0.875rem;
          line-height: 1.5;
          resize: vertical;
          min-height: 160px;
          padding: 0.75rem;
          border-radius: 0.375rem;
          border: 1px solid var(--sv-bdr);
          background-color: #fff;
          font-family: 'Poppins', sans-serif;
        }
        #editCourseModal textarea.form-control::placeholder {
          color: var(--sv-text-muted, #6c757d);
          font-size: 0.87rem;
          font-style: italic;
        }
        #editCourseModal textarea.form-control:focus {
          border-color: var(--sv-acct);
          box-shadow: 0 0 0 .2rem rgb(34 197 94 / 15%);
          outline: none;
        }
        /* Section Title Typography */
        #editCourseModal .sv-section-title {
          font-size: 0.875rem;
          font-weight: 600;
          color: #495057;
          letter-spacing: 0.025em;
        }
        #editCourseModal .form-control:focus,
        #editCourseModal .form-select:focus {
          border-color: var(--sv-acct);
          box-shadow: 0 0 0 .2rem rgb(238 111 87 / 15%);
        }
        #editCourseModal .btn-danger {
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
        #editCourseModal .btn-danger:hover,
        #editCourseModal .btn-danger:focus {
          background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(204, 55, 55, 0.12);
          color: #CB3737;
        }
        #editCourseModal .btn-danger:hover i,
        #editCourseModal .btn-danger:hover svg,
        #editCourseModal .btn-danger:focus i,
        #editCourseModal .btn-danger:focus svg {
          stroke: #CB3737;
        }
        #editCourseModal .btn-danger:active {
          background: linear-gradient(135deg, rgba(255, 230, 225, 0.98), rgba(255, 255, 255, 0.62));
          box-shadow: 0 1px 8px rgba(204, 55, 55, 0.16);
        }
        #editCourseModal .btn-danger:active i,
        #editCourseModal .btn-danger:active svg {
          stroke: #CB3737;
        }
        /* Cancel button styling */
        #editCourseModal .btn-light {
          background: var(--sv-card-bg, #fff);
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
        #editCourseModal .btn-light:hover,
        #editCourseModal .btn-light:focus {
          background: linear-gradient(135deg, rgba(220, 220, 220, 0.88), rgba(240, 240, 240, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(108, 117, 125, 0.12);
          color: #495057;
        }
        #editCourseModal .btn-light:hover i,
        #editCourseModal .btn-light:hover svg,
        #editCourseModal .btn-light:focus i,
        #editCourseModal .btn-light:focus svg {
          stroke: #495057;
        }
        #editCourseModal .btn-light:active {
          background: linear-gradient(135deg, rgba(240, 242, 245, 0.98), rgba(255, 255, 255, 0.62));
          box-shadow: 0 1px 8px rgba(108, 117, 125, 0.16);
        }
        #editCourseModal .btn-light:active i,
        #editCourseModal .btn-light:active svg {
          stroke: #495057;
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
          background: rgba(34,197,94,.12);
          color: #166534;
          border-color: rgba(34,197,94,.35);
        }
      </style>
      {{-- ░░░ END: Local styles ░░░ --}}

      <script>
        function toggleContactHours(inputId, checkbox) {
          const input = document.getElementById(inputId);
          if (checkbox.checked) {
            input.disabled = false;
            input.required = true;
            if (!input.value) {
              input.value = '';
            }
          } else {
            input.disabled = true;
            input.required = false;
            input.value = '';
          }
        }


      </script>

      {{-- ░░░ START: Header ░░░ --}}
      <div class="modal-header">
        <h5 class="modal-title fw-semibold" id="editCourseModalLabel">Edit Course</h5>
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

            <div class="course-field-group mb-3">
              <label for="editCourseCode" class="form-label small fw-medium text-muted">Course Code</label>
              <input type="text" class="form-control form-control-sm" id="editCourseCode" name="code" required>
            </div>

            <div class="course-field-group mb-3">
              <label for="editCourseTitle" class="form-label small fw-medium text-muted">Course Title</label>
              <input type="text" class="form-control form-control-sm" id="editCourseTitle" name="title" required>
            </div>

            @if($showEditDepartmentDropdown ?? false)
            <!-- Show department dropdown for admin faculty -->
            <div class="course-field-group mb-3">
              <label for="editCourseDepartment" class="form-label small fw-medium text-muted">Department</label>
              <select class="form-select form-select-sm" id="editCourseDepartment" name="department_id" required>
                <option value="">Select Department</option>
                @if(isset($departments))
                  @foreach($departments as $department)
                    <option value="{{ $department->id }}">
                      {{ $department->name }}
                    </option>
                  @endforeach
                @endif
              </select>
            </div>
            @else
            <!-- Department is hidden for basic faculty users -->
            <input type="hidden" id="editCourseDepartment" name="department_id">
            @endif

            <div class="course-field-group mb-3">
              <label for="editCourseCategory" class="form-label small fw-medium text-muted">Course Category</label>
              <input type="text" class="form-control form-control-sm" id="editCourseCategory" name="course_category" required>
            </div>

            <div class="row g-3 mb-4">
              <div class="col-sm-6">
                <div class="d-flex align-items-center mb-2">
                  <input type="checkbox" class="form-check-input me-2" id="editLecCheckbox" checked onchange="toggleContactHours('editContactHoursLec', this)">
                  <label for="editContactHoursLec" class="form-label small fw-medium text-muted mb-0">Contact Hours (Lecture)</label>
                </div>
                <input type="number" class="form-control form-control-sm" id="editContactHoursLec" name="contact_hours_lec" min="0" required>
              </div>
              <div class="col-sm-6">
                <div class="d-flex align-items-center mb-2">
                  <input type="checkbox" class="form-check-input me-2" id="editLabCheckbox" onchange="toggleContactHours('editContactHoursLab', this)">
                  <label for="editContactHoursLab" class="form-label small fw-medium text-muted mb-0">Contact Hours (Lab)</label>
                </div>
                <input type="number" class="form-control form-control-sm" id="editContactHoursLab" name="contact_hours_lab" min="0" disabled>
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

              {{-- List built by JS from the selected row's data-prereq --}}
              <div id="editPrereqList" class="prereq-list p-2">
                <div class="text-center text-muted small py-4">Will load when course is selected…</div>
              </div>
            </div>
          </div>
          {{-- ░░░ END: Right – CURRENT Prerequisites (searchable checkboxes) ░░░ --}}
        </div>
      </div>
      {{-- ░░░ END: Body ░░░ --}}

      {{-- ░░░ START: Footer ░░░ --}}
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
          <i data-feather="x"></i> Cancel
        </button>
        <button type="submit" class="btn btn-danger" id="editCourseSubmit">
          <i data-feather="save"></i> Save Changes
        </button>
      </div>
      {{-- ░░░ END: Footer ░░░ --}}
    </form>
  </div>
</div>