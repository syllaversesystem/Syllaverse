{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/master-data/modals/edit-program-modal.blade.php
* Description: Edit Program modal (AJAX-ready)
-------------------------------------------------------------------------------
📜 Log:
[2025-08-17] Updated with form id=editProgramForm for AJAX submit.
-------------------------------------------------------------------------------
--}}
<div class="modal fade" id="editProgramModal" tabindex="-1" aria-labelledby="editProgramModalLabel" aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form id="editProgramForm" action="{{ route('admin.programs.update', 0) }}" method="POST" class="edit-program-form">
        @csrf
        @method('PUT')

        <div class="modal-header">
          <h5 class="modal-title fw-semibold" id="editProgramModalLabel">
            Edit <span id="programEditLabel">Program</span>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label for="editProgramName" class="form-label small">Program Name</label>
            <input type="text" name="name" id="editProgramName" class="form-control form-control-sm" required>
          </div>

          <div class="mb-3">
            <label for="editProgramCode" class="form-label small">Program Code</label>
            <input type="text" name="code" id="editProgramCode" class="form-control form-control-sm" required>
          </div>

          @if($showDepartmentDropdown ?? true)
          <div class="mb-3">
            <label for="editProgramDepartment" class="form-label small">Department</label>
            <select class="form-select form-select-sm" id="editProgramDepartment" name="department_id" required>
              <option value="">Select Department</option>
              @if(isset($departments))
                @foreach($departments as $department)
                  <option value="{{ $department->id }}">{{ $department->name }}</option>
                @endforeach
              @endif
            </select>
          </div>
          @else
          <!-- Hidden field for department when user has specific role -->
          <input type="hidden" id="editProgramDepartment" name="department_id" value="{{ $userDepartment ?? '' }}">
          @endif

          <div class="mb-2">
            <label for="editProgramDescription" class="form-label small">Description</label>
            <textarea name="description" id="editProgramDescription" class="form-control" rows="5"></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i data-feather="save"></i> Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
