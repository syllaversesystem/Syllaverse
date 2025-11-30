{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/programs/partials/edit-program-modal.blade.php
* Description: Modal for editing existing programs with validation
-------------------------------------------------------------------------------
📜 Log:
[2025-01-16] Created faculty version based on admin edit program modal
-------------------------------------------------------------------------------
--}}

{{-- ░░░ START: Edit Program Modal ░░░ --}}
<div class="modal fade sv-faculty-program-modal" id="editProgramModal" tabindex="-1" aria-labelledby="editProgramModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <form id="editProgramForm" action="{{ route('faculty.programs.update', 0) }}" method="POST" class="modal-content program-form edit-program-form"
      @csrf
      @method('PUT')

      {{-- ░░░ START: Local styles (scoped to this modal) ░░░ --}}
      <style>
        /* Brand tokens */
        #editProgramModal {
          --sv-bg:   #FAFAFA;   /* light bg */
          --sv-bdr:  #E3E3E3;   /* borders */
          --sv-acct: #EE6F57;   /* accent/focus */
          --sv-danger:#CB3737;  /* primary action (danger style) */
        }
        #editProgramModal .modal-content {
          border-radius: 0.75rem;
        }
        /* Scroll behavior matching add modal */
        #editProgramModal .modal-body {
          max-height: 60vh;
          overflow-y: auto;
          scrollbar-width: thin;
        }
        #editProgramModal .modal-body::-webkit-scrollbar { width: 8px; }
        #editProgramModal .modal-body::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
        #editProgramModal .modal-body::-webkit-scrollbar-thumb { background: #c4c4c4; border-radius: 4px; }
        #editProgramModal .modal-body::-webkit-scrollbar-thumb:hover { background: #b0b0b0; }
        #editProgramModal .modal-header {
          border-bottom: 1px solid var(--sv-bdr);
          background: var(--sv-bg);
        }
        #editProgramModal .modal-title {
          font-weight: 600;
          font-size: 1rem;
          display: inline-flex;
          align-items: center;
          gap: .5rem;
        }
        #editProgramModal .modal-title i,
        #editProgramModal .modal-title svg {
          width: 1.05rem;
          height: 1.05rem;
          stroke: var(--sv-text-muted, #777777);
        }
        #editProgramModal .sv-card {
          border: 1px solid var(--sv-bdr);
          background: #fff;
          border-radius: .75rem;
        }
        #editProgramModal .sv-section-title {
          font-size: .8rem;
          letter-spacing: .02em;
          color: #6c757d;
        }
        #editProgramModal .input-group-text {
          background: var(--sv-bg);
          border-color: var(--sv-bdr);
        }
        #editProgramModal .form-control,
        #editProgramModal .form-select {
          border-color: var(--sv-bdr);
          border-radius: 12px;
        }
        #editProgramModal .form-control:focus,
        #editProgramModal .form-select:focus {
          border-color: var(--sv-acct);
          box-shadow: 0 0 0 .2rem rgb(238 111 87 / 15%);
          outline: none;
        }
        /* Remove browser default yellow/orange focus effects */
        #editProgramModal textarea.form-control:focus {
          border-color: var(--sv-bdr);
          box-shadow: none;
          outline: none;
          background-color: #fff;
        }
        #editProgramModal .btn-danger {
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
        #editProgramModal .btn-danger:hover,
        #editProgramModal .btn-danger:focus {
          background: linear-gradient(135deg, rgba(220, 220, 220, 0.88), rgba(240, 240, 240, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
          color: #000;
        }
        #editProgramModal .btn-danger:hover i,
        #editProgramModal .btn-danger:hover svg,
        #editProgramModal .btn-danger:focus i,
        #editProgramModal .btn-danger:focus svg {
          stroke: #000;
        }
        #editProgramModal .btn-danger:active {
          background: linear-gradient(135deg, rgba(240, 242, 245, 0.98), rgba(255, 255, 255, 0.62));
          box-shadow: 0 1px 8px rgba(0, 0, 0, 0.16);
          color: #000;
        }
        #editProgramModal .btn-danger:active i,
        #editProgramModal .btn-danger:active svg {
          stroke: #000;
        }
        /* Cancel button styling */
        #editProgramModal .btn-light {
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
        #editProgramModal .btn-light:hover,
        #editProgramModal .btn-light:focus {
          background: linear-gradient(135deg, rgba(220, 220, 220, 0.88), rgba(240, 240, 240, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
          color: #000;
        }
        #editProgramModal .btn-light:hover i,
        #editProgramModal .btn-light:hover svg,
        #editProgramModal .btn-light:focus i,
        #editProgramModal .btn-light:focus svg {
          stroke: #000;
        }
        #editProgramModal .btn-light:active {
          background: linear-gradient(135deg, rgba(240, 242, 245, 0.98), rgba(255, 255, 255, 0.62));
          box-shadow: 0 1px 8px rgba(0, 0, 0, 0.16);
          color: #000;
        }
        #editProgramModal .btn-light:active i,
        #editProgramModal .btn-light:active svg {
          stroke: #000;
        }
        #editProgramModal .sv-divider {
          height: 1px;
          background: var(--sv-bdr);
          margin: .75rem 0;
        }
      </style>
      {{-- ░░░ END: Local styles ░░░ --}}

      {{-- ░░░ START: Header ░░░ --}}
      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2" id="editProgramModalLabel">
          <i data-feather="edit-3"></i>
          <span>Edit <span id="programEditLabel">Program</span></span>
        </h5>
      </div>
      {{-- ░░░ END: Header ░░░ --}}

      {{-- ░░░ START: Body ░░░ --}}
      <div class="modal-body">
        {{-- Inline error box (filled by JS on 422) --}}
        <div id="editProgramErrors" class="alert alert-danger d-none small mb-3" role="alert"></div>

        <div class="program-field-group mb-3">
          <label for="editProgramName" class="form-label small">Program Name</label>
          <input type="text" name="name" id="editProgramName" class="form-control form-control-sm" required>
        </div>

        <div class="program-field-group mb-3">
          <label for="editProgramCode" class="form-label small">Program Code</label>
          <input type="text" name="code" id="editProgramCode" class="form-control form-control-sm" required>
        </div>

        @if($showEditDepartmentDropdown ?? true)
        <div class="program-field-group mb-3">
          <label for="editProgramDepartment" class="form-label small">Department</label>
          <select class="form-select form-select-sm" id="editProgramDepartment" name="department_id" required>
            <option value="">Select Department</option>
            @if(isset($departments))
              @foreach($departments as $department)
                <option value="{{ $department->id }}" 
                  {{ (isset($departmentFilter) && $departmentFilter == $department->id) || (isset($userDepartment) && $userDepartment == $department->id) ? 'selected' : '' }}>
                  {{ $department->name }}
                </option>
              @endforeach
            @endif
          </select>
        </div>
        @else
        <!-- Hidden field for department when user has specific role -->
        <input type="hidden" id="editProgramDepartment" name="department_id" value="{{ $userDepartment }}">
        @endif

        <div class="program-field-group mb-3">
          <label for="editProgramDescription" class="form-label small">Description (optional)</label>
          <textarea name="description" id="editProgramDescription" class="form-control" rows="5" placeholder="Enter a brief description of the program, its objectives, and key features..."></textarea>
          <div class="form-text text-muted mt-1">
            <i data-feather="info" class="me-1" style="width: 14px; height: 14px;"></i>
            This description will be used in AI prompts to generate more relevant and accurate syllabi content for this program.
          </div>
        </div>
      </div>
      {{-- ░░░ END: Body ░░░ --}}

      {{-- ░░░ START: Footer ░░░ --}}
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
          <i data-feather="x"></i> Cancel
        </button>
        <button type="submit" class="btn btn-danger" id="editProgramSubmit">
          <i data-feather="save"></i> Update
        </button>
      </div>
      {{-- ░░░ END: Footer ░░░ --}}
    </form>
  </div>
</div>
{{-- ░░░ END: Edit Program Modal ░░░ --}}