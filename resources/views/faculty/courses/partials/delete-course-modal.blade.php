{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/courses/partials/delete-course-modal.blade.php
* Description: Delete confirmation modal for Courses (AJAX-ready) - Faculty version
-------------------------------------------------------------------------------
📜 Log:
[2025-01-XX] Adapted from admin courses module for faculty use
[2025-01-XX] Updated styling to use faculty theme colors
[2025-01-XX] Modified action routing for faculty controllers
-------------------------------------------------------------------------------
--}}
{{-- ░░░ START: Delete Course Modal ░░░ --}}
<div class="modal fade sv-faculty-course-modal" id="deleteCourseModal" tabindex="-1" aria-labelledby="deleteCourseModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content course-delete-form">

      {{-- ░░░ START: Local styles (scoped to this modal) ░░░ --}}
      <style>
        /* Faculty brand tokens */
        #deleteCourseModal {
          --sv-bg:   #FAFAFA;   /* light bg */
          --sv-bdr:  #E3E3E3;   /* borders */
          --sv-acct: #22c55e;   /* faculty accent (green) */
          --sv-danger:#ef4444;  /* danger action (red) */
        }
        #deleteCourseModal .modal-header {
          border-bottom: 1px solid var(--sv-bdr);
          background: var(--sv-bg);
        }
        #deleteCourseModal .sv-card {
          border: 1px solid var(--sv-bdr);
          background: #fff;
          border-radius: .75rem;
        }
        #deleteCourseModal .sv-section-title {
          font-size: .8rem;
          letter-spacing: .02em;
          color: #6c757d;
        }
        #deleteCourseModal .input-group-text {
          background: var(--sv-bg);
          border-color: var(--sv-bdr);
        }
        #deleteCourseModal .form-control,
        #deleteCourseModal .form-select {
          border-color: var(--sv-bdr);
        }
        #deleteCourseModal .form-control:focus,
        #deleteCourseModal .form-select:focus {
          border-color: var(--sv-acct);
          box-shadow: 0 0 0 .2rem rgb(34 197 94 / 15%);
        }
        #deleteCourseModal .btn-danger {
          background: var(--sv-card-bg, #fff);
          border: none;
          color: var(--sv-danger);
          transition: all 0.2s ease-in-out;
          box-shadow: none;
          display: inline-flex;
          align-items: center;
          gap: 0.5rem;
          padding: 0.5rem 1rem;
          border-radius: 0.375rem;
        }
        #deleteCourseModal .btn-danger:hover,
        #deleteCourseModal .btn-danger:focus {
          background: linear-gradient(135deg, rgba(254, 242, 242, 0.88), rgba(255, 248, 248, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(239, 68, 68, 0.15);
          color: var(--sv-danger);
        }
        #deleteCourseModal .btn-danger:hover i,
        #deleteCourseModal .btn-danger:hover svg,
        #deleteCourseModal .btn-danger:focus i,
        #deleteCourseModal .btn-danger:focus svg {
          stroke: var(--sv-danger);
        }
        #deleteCourseModal .btn-danger:active {
          background: linear-gradient(135deg, rgba(254, 235, 235, 0.98), rgba(255, 248, 248, 0.62));
          box-shadow: 0 1px 8px rgba(239, 68, 68, 0.16);
        }
        #deleteCourseModal .btn-danger:active i,
        #deleteCourseModal .btn-danger:active svg {
          stroke: var(--sv-danger);
        }
        /* Warning button styling for remove action */
        #deleteCourseModal .btn-warning {
          background: var(--sv-card-bg, #fff);
          border: none;
          color: #f59e0b;
          transition: all 0.2s ease-in-out;
          box-shadow: none;
          display: inline-flex;
          align-items: center;
          gap: 0.5rem;
          padding: 0.5rem 1rem;
          border-radius: 0.375rem;
        }
        #deleteCourseModal .btn-warning:hover,
        #deleteCourseModal .btn-warning:focus {
          background: linear-gradient(135deg, rgba(255, 251, 235, 0.88), rgba(255, 252, 245, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(245, 158, 11, 0.12);
          color: #f59e0b;
        }
        #deleteCourseModal .btn-warning:hover i,
        #deleteCourseModal .btn-warning:hover svg,
        #deleteCourseModal .btn-warning:focus i,
        #deleteCourseModal .btn-warning:focus svg {
          stroke: #f59e0b;
        }
        #deleteCourseModal .btn-warning:active {
          background: linear-gradient(135deg, rgba(255, 248, 220, 0.98), rgba(255, 251, 235, 0.62));
          box-shadow: 0 1px 8px rgba(245, 158, 11, 0.16);
        }
        #deleteCourseModal .btn-warning:active i,
        #deleteCourseModal .btn-warning:active svg {
          stroke: #f59e0b;
        }
        /* Cancel button styling */
        #deleteCourseModal .btn-light {
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
        #deleteCourseModal .btn-light:hover,
        #deleteCourseModal .btn-light:focus {
          background: linear-gradient(135deg, rgba(220, 220, 220, 0.88), rgba(240, 240, 240, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(108, 117, 125, 0.12);
          color: #495057;
        }
        #deleteCourseModal .btn-light:hover i,
        #deleteCourseModal .btn-light:hover svg,
        #deleteCourseModal .btn-light:focus i,
        #deleteCourseModal .btn-light:focus svg {
          stroke: #495057;
        }
        #deleteCourseModal .btn-light:active {
          background: linear-gradient(135deg, rgba(240, 242, 245, 0.98), rgba(255, 255, 255, 0.62));
          box-shadow: 0 1px 8px rgba(108, 117, 125, 0.16);
        }
        #deleteCourseModal .btn-light:active i,
        #deleteCourseModal .btn-light:active svg {
          stroke: #495057;
        }
        #deleteCourseModal .alert-warning {
          background: rgba(255, 251, 235, 0.9);
          border: 1px solid rgba(245, 158, 11, 0.3);
          color: #92400e;
        }
        #deleteCourseModal .alert-danger {
          background: rgba(254, 242, 242, 0.9);
          border: 1px solid rgba(239, 68, 68, 0.3);
          color: #991b1b;
        }
        #deleteCourseModal .alert-success {
          background: rgba(240, 253, 244, 0.9);
          border: 1px solid rgba(34, 197, 94, 0.3);
          color: #166534;
        }
      </style>
      {{-- ░░░ END: Local styles ░░░ --}}

      {{-- ░░░ START: Body ░░░ --}}
      <div class="modal-body">
        {{-- Course Information Card --}}
        <div class="text-center mb-4">
          <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 rounded-circle mb-3" style="width: 64px; height: 64px;">
            <i data-feather="trash-2" class="text-danger" style="width: 28px; height: 28px;"></i>
          </div>
          <h6 class="fw-semibold mb-2">Manage Course</h6>
          <p class="text-muted mb-0">What would you like to do with this course?</p>
        </div>

        {{-- Course Details --}}
        <div class="bg-light rounded-3 p-3 mb-4">
          <div class="small text-muted mb-1">You are managing:</div>
          <div class="fw-semibold mb-1" id="deleteCourseTitle">Loading...</div>
          <div class="small text-muted">Code: <span id="deleteCourseCode" class="fw-medium">Loading...</span></div>
        </div>

        {{-- Action Options --}}
        <div class="mb-4">
          <div class="form-check mb-3">
            <input class="form-check-input" type="radio" name="action_type" id="removeCourse" value="remove" checked>
            <label class="form-check-label" for="removeCourse">
              <div class="fw-medium text-dark">Remove Course</div>
              <div class="small text-muted">Hide from listings but keep data (can be restored later)</div>
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="action_type" id="deleteCourse" value="delete">
            <label class="form-check-label" for="deleteCourse">
              <div class="fw-medium text-dark">Delete Course</div>
              <div class="small text-muted">Permanently delete from database (cannot be undone)</div>
            </label>
          </div>
        </div>

      </div>
      {{-- ░░░ END: Body ░░░ --}}

      {{-- ░░░ START: Footer ░░░ --}}
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
          <i data-feather="x"></i> Cancel
        </button>
        <form id="deleteCourseForm" action="{{ route('faculty.courses.destroy', 0) }}" method="POST" class="d-inline delete-course-form">
          @csrf
          @method('DELETE')
          {{-- Hidden field for the delete URL base (used by JavaScript) --}}
          <input type="hidden" id="deleteCourseUrlBase" value="/faculty/courses">
          <input type="hidden" id="deleteCourseId" name="id" value="">
          <input type="hidden" id="actionType" name="action_type" value="remove">
          <button type="submit" id="confirmActionBtn" class="btn btn-warning">
            <i data-feather="minus-circle"></i> Remove
          </button>
        </form>
      </div>
      {{-- ░░░ END: Footer ░░░ --}}
    </div>
  </div>
</div>
{{-- ░░░ END: Delete Course Modal ░░░ --}}