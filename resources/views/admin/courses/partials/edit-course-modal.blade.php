{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/courses/partials/edit-course-modal.blade.php
* Description: Modal for editing a course - Standalone courses module
-------------------------------------------------------------------------------
--}}
<div class="modal fade" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form id="editCourseForm" action="{{ route('admin.courses.update', 0) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title fw-semibold" id="editCourseModalLabel">Edit Course</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted">Course editing modal - Implementation in progress</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>