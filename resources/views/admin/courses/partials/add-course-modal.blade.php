{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/courses/partials/add-course-modal.blade.php
* Description: Modal for adding a new course - Standalone courses module
-------------------------------------------------------------------------------
📜 Log:
[2025-10-11] Created placeholder for standalone courses module
-------------------------------------------------------------------------------
--}}
<div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form id="addCourseForm" action="{{ route('admin.courses.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title fw-semibold" id="addCourseModalLabel">Add New Course</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted">Course creation modal - Implementation in progress</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Create Course</button>
        </div>
      </form>
    </div>
  </div>
</div>