{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/courses/partials/add-course-modal.blade.php
* Description: Modal for adding a new course (AJAX) - Faculty version
-------------------------------------------------------------------------------
📜 Log:
[2025-01-XX] Adapted from admin courses module for faculty use
[2025-01-XX] Updated styling to use faculty theme colors
[2025-01-XX] Added role-based permissions and department handling
-------------------------------------------------------------------------------
--}}

{{-- ░░░ START: Add Course Modal ░░░ --}}
<div class="modal fade sv-course-modal" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <form id="addCourseForm" action="{{ route('faculty.courses.store') }}" method="POST" class="modal-content course-form">
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
        #addCourseModal .modal-content {
          border-radius: 16px;
          border: none;
        }
        #addCourseModal .modal-header {
          border-bottom: 1px solid var(--sv-bdr);
          background: var(--sv-bg);
          border-radius: 16px 16px 0 0;
        }
        #addCourseModal .modal-body {
          max-height: 70vh;
          overflow-y: auto;
        }
        #addCourseModal .modal-title {
          font-weight: 600;
          font-size: 1rem;
          display: inline-flex;
          align-items: center;
          gap: .5rem;
        }
        #addCourseModal .modal-title i,
        #addCourseModal .modal-title svg {
          width: 1.05rem;
          height: 1.05rem;
          stroke: var(--sv-text-muted, #777777);
        }
        #addCourseModal .modal-footer {
          border-radius: 0 0 16px 16px;
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
          border-radius: 12px 0 0 12px;
        }
        #addCourseModal .input-group .form-control {
          border-radius: 0 12px 12px 0;
        }
        #addCourseModal .input-group .form-control:only-child {
          border-radius: 12px;
        }
        #addCourseModal .form-control,
        #addCourseModal .form-select {
          border-color: var(--sv-bdr);
          border-radius: 12px;
        }
        #addCourseModal .form-control-sm {
          font-size: 0.875rem;
          border-radius: 12px;
        }
        /* Form Label Typography */
        #addCourseModal .form-label {
          font-size: 0.8125rem;
          font-weight: 500;
          color: #6c757d;
          letter-spacing: 0.025em;
          margin-bottom: 0.375rem;
          text-transform: none;
        }
        /* Input Field Typography */
        #addCourseModal .form-control,
        #addCourseModal .form-control-sm {
          font-size: 0.875rem;
          font-weight: 400;
          line-height: 1.4;
          color: #495057;
        }
        #addCourseModal .form-control::placeholder,
        #addCourseModal .form-control-sm::placeholder {
          color: var(--sv-text-muted, #6c757d);
          font-size: 0.87rem;
        }
        /* Textarea specific styling - Enhanced to match admin exactly */
        #addCourseModal textarea.form-control {
          font-size: 0.875rem;
          line-height: 1.5;
          resize: vertical;
          min-height: 120px;
          padding: 0.75rem;
          border-radius: 12px;
          border: 1px solid var(--sv-bdr);
          background-color: #fff;
          font-family: 'Poppins', sans-serif;
        }
        #addCourseModal textarea.form-control::placeholder {
          color: var(--sv-text-muted, #6c757d);
          font-size: 0.87rem;
          font-style: italic;
        }
        #addCourseModal textarea.form-control:focus {
          border-color: var(--sv-acct);
          box-shadow: 0 0 0 .2rem rgb(238 111 87 / 15%);
          outline: none;
        }
        /* Course Field Group */
        .course-field-group {
          position: relative;
          margin-bottom: 0;
        }
        .course-field-group textarea.form-control {
          transition: all 0.2s ease;
        }
        .course-field-group textarea.form-control:hover {
          border-color: rgba(238, 111, 87, 0.5);
        }
        /* Section Title Typography */
        #addCourseModal .sv-section-title {
          font-size: 0.875rem;
          font-weight: 600;
          color: #495057;
          letter-spacing: 0.025em;
        }
        #addCourseModal .form-control:focus,
        #addCourseModal .form-select:focus {
          border-color: var(--sv-acct);
          box-shadow: 0 0 0 .2rem rgb(238 111 87 / 15%);
        }
        #addCourseModal .btn-danger {
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
        #addCourseModal .btn-danger:hover,
        #addCourseModal .btn-danger:focus {
          background: linear-gradient(135deg, rgba(220, 220, 220, 0.88), rgba(240, 240, 240, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
          color: #000;
        }
        #addCourseModal .btn-danger:hover i,
        #addCourseModal .btn-danger:hover svg,
        #addCourseModal .btn-danger:focus i,
        #addCourseModal .btn-danger:focus svg {
          stroke: #000;
        }
        #addCourseModal .btn-danger:active {
          background: linear-gradient(135deg, rgba(240, 242, 245, 0.98), rgba(255, 255, 255, 0.62));
          box-shadow: 0 1px 8px rgba(0, 0, 0, 0.16);
          color: #000;
        }
        #addCourseModal .btn-danger:active i,
        #addCourseModal .btn-danger:active svg {
          stroke: #000;
        }
        /* Cancel button styling */
        #addCourseModal .btn-light {
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
        #addCourseModal .btn-light:hover,
        #addCourseModal .btn-light:focus {
          background: linear-gradient(135deg, rgba(220, 220, 220, 0.88), rgba(240, 240, 240, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
          color: #000;
        }
        #addCourseModal .btn-light:hover i,
        #addCourseModal .btn-light:hover svg,
        #addCourseModal .btn-light:focus i,
        #addCourseModal .btn-light:focus svg {
          stroke: #000;
        }
        #addCourseModal .btn-light:active {
          background: linear-gradient(135deg, rgba(240, 242, 245, 0.98), rgba(255, 255, 255, 0.62));
          box-shadow: 0 1px 8px rgba(0, 0, 0, 0.16);
          color: #000;
        }
        #addCourseModal .btn-light:active i,
        #addCourseModal .btn-light:active svg {
          stroke: #000;
        }
        #addCourseModal .sv-divider {
          height: 1px;
          background: var(--sv-bdr);
          margin: .75rem 0;
        }

        /* Prereq list */
        #addCourseModal .prereq-list {
          max-height: 280px;
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
            input.value = '';
          } else {
            input.disabled = true;
            input.required = false;
            input.value = '';
          }
        }


      </script>

      {{-- ░░░ START: Header ░░░ --}}
      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2" id="addCourseModalLabel">
          <i data-feather="plus-circle"></i>
          <span>Add New Course</span>
        </h5>
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

            <div class="course-field-group mb-3 position-relative">
              <label for="addCourseCode" class="form-label small fw-medium text-muted">Course Code</label>
              <input type="text" class="form-control form-control-sm" id="addCourseCode" name="code" placeholder="e.g., IT 221" required autocomplete="off">
              <div id="courseCodeSuggestions" class="suggestions-dropdown" style="display: none;"></div>
            </div>

            <div class="course-field-group mb-3 position-relative">
              <label for="addCourseTitle" class="form-label small fw-medium text-muted">Course Title</label>
              <input type="text" class="form-control form-control-sm" id="addCourseTitle" name="title" placeholder="e.g., Fundamentals of Enterprise Data Management" required autocomplete="off">
              <div id="courseTitleSuggestions" class="suggestions-dropdown" style="display: none;"></div>
            </div>

            @if($showAddDepartmentDropdown ?? false)
            <!-- Show department dropdown for admin faculty -->
            <div class="course-field-group mb-3">
              <label for="addCourseDepartment" class="form-label small fw-medium text-muted">Department</label>
              <select class="form-select form-select-sm" id="addCourseDepartment" name="department_id" required>
                <option value="">Select Department</option>
                @if(isset($departments))
                  @foreach($departments as $department)
                    <option value="{{ $department->id }}" 
                            @if($departmentFilter && $departmentFilter == $department->id) 
                              selected 
                            @elseif(!$departmentFilter && ($userDepartment ?? null) == $department->id) 
                              selected 
                            @endif>
                      {{ $department->name }}
                    </option>
                  @endforeach
                @endif
              </select>
              @if(isset($departmentFilter) && $departmentFilter)
                <small class="text-muted">Pre-selected based on current department filter, but you can change it.</small>
              @endif
            </div>
            @else
            <!-- Hidden field for department - auto-assigned based on faculty role -->
            <input type="hidden" name="department_id" value="{{ $userDepartment ?? '' }}">
            @endif

            <div class="course-field-group mb-3">
              <label for="addCourseCategory" class="form-label small fw-medium text-muted">Course Category</label>
              <input type="text" class="form-control form-control-sm" id="addCourseCategory" name="course_category" placeholder="e.g., Core, Elective, General Education" required>
            </div>

            <div class="row g-3 mb-4">
              <div class="col-sm-6">
                <div class="d-flex align-items-center mb-2">
                  <input type="checkbox" class="form-check-input me-2" id="addLecCheckbox" checked onchange="toggleContactHours('addContactHoursLec', this)">
                  <label for="addContactHoursLec" class="form-label small fw-medium text-muted mb-0">Contact Hours (Lecture)</label>
                </div>
                <input type="number" class="form-control form-control-sm" id="addContactHoursLec" name="contact_hours_lec" placeholder="e.g., 2" min="0" required>
              </div>
              <div class="col-sm-6">
                <div class="d-flex align-items-center mb-2">
                  <input type="checkbox" class="form-check-input me-2" id="addLabCheckbox" onchange="toggleContactHours('addContactHoursLab', this)">
                  <label for="addContactHoursLab" class="form-label small fw-medium text-muted mb-0">Contact Hours (Lab)</label>
                </div>
                <input type="number" class="form-control form-control-sm" id="addContactHoursLab" name="contact_hours_lab" placeholder="e.g., 3" min="0" disabled>
              </div>
            </div>

            <div class="sv-divider"></div>

            <div class="mt-3">
              <label for="addCourseDescription" class="form-label small fw-medium text-muted">Course Rationale and Description <span class="text-muted">(Optional)</span></label>
              <div class="course-field-group">
                <textarea class="form-control" id="addCourseDescription" name="description" rows="4" style="min-height:120px" placeholder="Explain the course rationale and provide a short description (topics, scope, etc.)"></textarea>
              </div>
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

            </div>
          </div>
          {{-- ░░░ END: Right – Prerequisites (searchable checkbox list) ░░░ --}}
        </div>
      </div>
      {{-- ░░░ END: Body ░░░ --}}

      {{-- ░░░ START: Footer ░░░ --}}
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
          <i data-feather="x"></i> Cancel
        </button>
        <button type="submit" class="btn btn-danger" id="addCourseSubmit">
          <i data-feather="plus"></i> Create
        </button>
      </div>
      {{-- ░░░ END: Footer ░░░ --}}
    </form>
  </div>
</div>

{{-- ░░░ START: Suggestions Dropdown Styles ░░░ --}}
<style>
.suggestions-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #ddd;
  border-top: none;
  border-radius: 0 0 0.375rem 0.375rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  z-index: 1060;
  max-height: 200px;
  overflow-y: auto;
}

.suggestion-item {
  padding: 8px 12px;
  border-bottom: 1px solid #f0f0f0;
  cursor: pointer;
  transition: background-color 0.15s ease;
}

.suggestion-item:hover {
  background-color: #f8f9fa;
}

.suggestion-item:last-child {
  border-bottom: none;
}

.suggestion-item .suggestion-main {
  font-weight: 500;
  color: #333;
  margin-bottom: 2px;
}

.suggestion-item .suggestion-meta {
  font-size: 0.85em;
  color: #666;
}

.suggestion-restore-badge {
  background-color: #ffc107;
  color: #000;
  font-size: 0.75em;
  padding: 2px 6px;
  border-radius: 0.25rem;
  font-weight: 500;
  margin-left: 8px;
}

.form-control:focus + .suggestions-dropdown {
  border-color: var(--sv-acct, #EE6F57);
}

.position-relative {
  position: relative;
}
</style>
{{-- ░░░ END: Suggestions Dropdown Styles ░░░ --}}

{{-- ░░░ END: Add Course Modal ░░░ --}}