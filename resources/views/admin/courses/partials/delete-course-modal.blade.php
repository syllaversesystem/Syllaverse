{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/courses/partials/delete-course-modal.blade.php
* Description: Modal for deleting a course - Standalone courses module
-------------------------------------------------------------------------------
--}}
<div class="modal fade" id="deleteCourseModal" tabindex="-1" aria-labelledby="deleteCourseModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="deleteCourseForm" action="{{ route('admin.courses.destroy', 0) }}" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-header">
          <h5 class="modal-title fw-semibold" id="deleteCourseModalLabel">Delete Course</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete this course?</p>
          <p class="text-muted">Course deletion modal - Implementation in progress</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Delete</button>
        </div>
      </form>
    </div>
  </div>
</div>