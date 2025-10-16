{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/master-data/modals/add-program-modal.blade.php
* Description: Modal for adding a new program (AJAX-ready)
-------------------------------------------------------------------------------
📜 Log:
[2025-08-17] Added form id=addProgramForm for AJAX submit.
-------------------------------------------------------------------------------
--}}
<div class="modal fade" id="addProgramModal" tabindex="-1" aria-labelledby="addProgramModalLabel" aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form id="addProgramForm" action="{{ route('admin.programs.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title fw-semibold" id="addProgramModalLabel">Add New Program</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

      <div class="modal-body">
        <div class="mb-3 position-relative">
          <label for="programName" class="form-label small fw-medium text-muted">Program Name</label>
          <input type="text" class="form-control form-control-sm" id="programName" name="name" placeholder="e.g., Bachelor of Science in Computer Science" required autocomplete="off">
          <div id="programNameSuggestions" class="suggestions-dropdown" style="display: none;"></div>
        </div>

        <div class="mb-3 position-relative">
          <label for="programCode" class="form-label small fw-medium text-muted">Program Code</label>
          <input type="text" class="form-control form-control-sm" id="programCode" name="code" placeholder="e.g., BSCS, BSIT, BSEE" required autocomplete="off">
          <div id="programCodeSuggestions" class="suggestions-dropdown" style="display: none;"></div>
        </div>

        @if($showDepartmentDropdown ?? true)
        <div class="mb-3">
          <label for="programDepartment" class="form-label small fw-medium text-muted">Department</label>
          <select class="form-select form-select-sm" id="programDepartment" name="department_id" required>
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
        <input type="hidden" name="department_id" value="{{ $userDepartment ?? '' }}">
        @endif

        <div class="mb-3">
          <label for="programDescription" class="form-label small fw-medium text-muted">Description (optional)</label>
          <textarea class="form-control" id="programDescription" name="description" rows="5" placeholder="Enter a brief description of the program, its objectives, and key features..."></textarea>
          <div class="form-text text-muted mt-1">
            <i data-feather="info" class="me-1" style="width: 14px; height: 14px;"></i>
            This description will be used in AI prompts to generate more relevant and accurate syllabi content for this program.
          </div>
        </div>
      </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i data-feather="plus"></i> Create Program
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
.suggestions-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #dee2e6;
  border-top: none;
  border-radius: 0 0 0.375rem 0.375rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  z-index: 1050;
  max-height: 200px;
  overflow-y: auto;
}

.suggestion-item {
  padding: 0.5rem 0.75rem;
  cursor: pointer;
  border-bottom: 1px solid #f8f9fa;
  transition: background-color 0.15s ease-in-out;
}

.suggestion-item:hover {
  background-color: #f8f9fa;
}

.suggestion-item:last-child {
  border-bottom: none;
}

.suggestion-item .suggestion-main {
  font-weight: 500;
  color: #495057;
  margin-bottom: 0.25rem;
}

.suggestion-item .suggestion-meta {
  font-size: 0.75rem;
  color: #6c757d;
}

.suggestion-restore-badge {
  display: inline-block;
  background-color: #ffc107;
  color: #212529;
  font-size: 0.6875rem;
  padding: 0.125rem 0.375rem;
  border-radius: 0.25rem;
  font-weight: 500;
  margin-left: 0.5rem;
}
</style>
