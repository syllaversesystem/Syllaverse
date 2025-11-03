{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/programs/partials/add-program-modal.blade.php
* Description: Modal for adding new programs with validation
-------------------------------------------------------------------------------
📜 Log:
[2025-01-16] Created faculty version based on admin add program modal
-------------------------------------------------------------------------------
--}}

{{-- ░░░ START: Add Program Modal ░░░ --}}
<div class="modal fade sv-program-modal" id="addProgramModal" tabindex="-1" aria-labelledby="addProgramModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form id="addProgramForm" action="{{ route('faculty.programs.store') }}" method="POST" class="modal-content program-form" style="border-radius: 16px;"
      @csrf

      {{-- ░░░ START: Local styles (scoped to this modal) ░░░ --}}
      <style>
        /* Brand tokens */
        #addProgramModal {
          --sv-bg:   #FAFAFA;   /* light bg */
          --sv-bdr:  #E3E3E3;   /* borders */
          --sv-acct: #EE6F57;   /* accent/focus */
          --sv-danger:#CB3737;  /* primary action (danger style) */
        }
        #addProgramModal .modal-header {
          border-bottom: 1px solid var(--sv-bdr);
          background: var(--sv-bg);
        }
        #addProgramModal .sv-card {
          border: 1px solid var(--sv-bdr);
          background: #fff;
          border-radius: .75rem;
        }
        #addProgramModal .sv-section-title {
          font-size: .8rem;
          letter-spacing: .02em;
          color: #6c757d;
        }
        #addProgramModal .input-group-text {
          background: var(--sv-bg);
          border-color: var(--sv-bdr);
        }
        #addProgramModal .form-control,
        #addProgramModal .form-select {
          border-color: var(--sv-bdr);
          border-radius: 12px;
        }
        #addProgramModal .form-control:focus,
        #addProgramModal .form-select:focus {
          border-color: var(--sv-acct);
          box-shadow: 0 0 0 .2rem rgb(238 111 87 / 15%);
          outline: none;
        }
        /* Remove browser default yellow/orange focus effects */
        #addProgramModal textarea.form-control:focus {
          border-color: var(--sv-bdr);
          box-shadow: none;
          outline: none;
          background-color: #fff;
        }
        #addProgramModal .btn-danger {
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
        #addProgramModal .btn-danger:hover,
        #addProgramModal .btn-danger:focus {
          background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(204, 55, 55, 0.12);
          color: #CB3737;
        }
        #addProgramModal .btn-danger:hover i,
        #addProgramModal .btn-danger:hover svg,
        #addProgramModal .btn-danger:focus i,
        #addProgramModal .btn-danger:focus svg {
          stroke: #CB3737;
        }
        #addProgramModal .btn-danger:active {
          background: linear-gradient(135deg, rgba(255, 230, 225, 0.98), rgba(255, 255, 255, 0.62));
          box-shadow: 0 1px 8px rgba(204, 55, 55, 0.16);
        }
        #addProgramModal .btn-danger:active i,
        #addProgramModal .btn-danger:active svg {
          stroke: #CB3737;
        }
        /* Cancel button styling */
        #addProgramModal .btn-light {
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
        #addProgramModal .btn-light:hover,
        #addProgramModal .btn-light:focus {
          background: linear-gradient(135deg, rgba(220, 220, 220, 0.88), rgba(240, 240, 240, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(108, 117, 125, 0.12);
          color: #495057;
        }
        #addProgramModal .btn-light:hover i,
        #addProgramModal .btn-light:hover svg,
        #addProgramModal .btn-light:focus i,
        #addProgramModal .btn-light:focus svg {
          stroke: #495057;
        }
        #addProgramModal .btn-light:active {
          background: linear-gradient(135deg, rgba(240, 242, 245, 0.98), rgba(255, 255, 255, 0.62));
          box-shadow: 0 1px 8px rgba(108, 117, 125, 0.16);
        }
        #addProgramModal .btn-light:active i,
        #addProgramModal .btn-light:active svg {
          stroke: #495057;
        }
        #addProgramModal .sv-divider {
          height: 1px;
          background: var(--sv-bdr);
          margin: .75rem 0;
        }
      </style>
      {{-- ░░░ END: Local styles ░░░ --}}

      {{-- ░░░ START: Header ░░░ --}}
      <div class="modal-header">
        <h5 class="modal-title fw-semibold d-flex align-items-center gap-2" id="addProgramModalLabel" style="font-size: 1rem;">
          <i data-feather="plus-circle"></i>
          Add New Program
        </h5>
      </div>
      {{-- ░░░ END: Header ░░░ --}}

      {{-- ░░░ START: Body ░░░ --}}
      <div class="modal-body">
        {{-- Inline error box (filled by JS on 422) --}}
        <div id="addProgramErrors" class="alert alert-danger d-none small mb-3" role="alert"></div>

        <div class="program-field-group mb-3 position-relative">
          <label for="programName" class="form-label small fw-medium text-muted">Program Name</label>
          <input type="text" class="form-control form-control-sm" id="programName" name="name" placeholder="e.g., Bachelor of Science in Computer Science" required autocomplete="off">
          <div id="programNameSuggestions" class="suggestions-dropdown" style="display: none;"></div>
        </div>

        <div class="program-field-group mb-3 position-relative">
          <label for="programCode" class="form-label small fw-medium text-muted">Program Code</label>
          <input type="text" class="form-control form-control-sm" id="programCode" name="code" placeholder="e.g., BSCS, BSIT, BSEE" required autocomplete="off">
          <div id="programCodeSuggestions" class="suggestions-dropdown" style="display: none;"></div>
        </div>

        @if($showAddDepartmentDropdown ?? true)
        <div class="program-field-group mb-3">
          <label for="programDepartment" class="form-label small fw-medium text-muted">Department</label>
          <select class="form-select form-select-sm" id="programDepartment" name="department_id" required>
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
        <input type="hidden" name="department_id" value="{{ $userDepartment }}">
        @endif

        <div class="program-field-group mb-3">
          <label for="programDescription" class="form-label small fw-medium text-muted">Description (optional)</label>
          <textarea class="form-control" id="programDescription" name="description" rows="5" placeholder="Enter a brief description of the program, its objectives, and key features..."></textarea>
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
        <button type="submit" class="btn btn-danger" id="addProgramSubmit">
          <i data-feather="plus"></i> Create
        </button>
      </div>
      {{-- ░░░ END: Footer ░░░ --}}
    </form>
  </div>
</div>
{{-- ░░░ END: Add Program Modal ░░░ --}}

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

<script>
document.addEventListener('DOMContentLoaded', function() {
  const programNameInput = document.getElementById('programName');
  const programCodeInput = document.getElementById('programCode');
  const programNameSuggestions = document.getElementById('programNameSuggestions');
  const programCodeSuggestions = document.getElementById('programCodeSuggestions');
  const programDepartmentSelect = document.getElementById('programDepartment');
  const programDescriptionTextarea = document.getElementById('programDescription');
  const addProgramForm = document.getElementById('addProgramForm');

  let debounceTimer;

  // Search for removed programs
  function searchRemovedPrograms(query, field, suggestionsContainer) {
    if (query.length < 2) {
      suggestionsContainer.style.display = 'none';
      return;
    }

    // Clear previous debounce timer
    clearTimeout(debounceTimer);

    // Debounce the search
    debounceTimer = setTimeout(() => {
      fetch(`{{ route('faculty.programs.search-removed') }}?query=${encodeURIComponent(query)}&field=${field}`)
        .then(response => response.json())
        .then(data => {
          displaySuggestions(data, suggestionsContainer, field);
        })
        .catch(error => {
          console.error('Error searching removed programs:', error);
          suggestionsContainer.style.display = 'none';
        });
    }, 300);
  }

  // Display suggestions
  function displaySuggestions(programs, container, field) {
    container.innerHTML = '';
    
    if (programs.length === 0) {
      container.style.display = 'none';
      return;
    }

    programs.forEach(program => {
      const suggestionItem = document.createElement('div');
      suggestionItem.className = 'suggestion-item';
      
      suggestionItem.innerHTML = `
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="suggestion-main">${program.name}</div>
            <div class="suggestion-meta">Code: ${program.code} | Dept: ${program.department_name || 'Unknown'}</div>
          </div>
          <span class="suggestion-restore-badge">Restore</span>
        </div>
      `;

      suggestionItem.addEventListener('click', () => {
        restoreProgram(program);
        container.style.display = 'none';
      });

      container.appendChild(suggestionItem);
    });

    container.style.display = 'block';
  }

  // Restore program by populating fields
  function restoreProgram(program) {
    // Populate form fields
    programNameInput.value = program.name;
    programCodeInput.value = program.code;
    
    // Set department if available
    if (program.department_id && programDepartmentSelect) {
      programDepartmentSelect.value = program.department_id;
    }
    
    // Set description if available
    if (program.description && programDescriptionTextarea) {
      programDescriptionTextarea.value = program.description;
    }

    // Add hidden field to indicate restoration
    let restoreIdInput = document.getElementById('restore_program_id');
    if (!restoreIdInput) {
      restoreIdInput = document.createElement('input');
      restoreIdInput.type = 'hidden';
      restoreIdInput.id = 'restore_program_id';
      restoreIdInput.name = 'restore_program_id';
      addProgramForm.appendChild(restoreIdInput);
    }
    restoreIdInput.value = program.id;

    // Update submit button text
    const submitBtn = document.getElementById('addProgramSubmit');
    submitBtn.innerHTML = '<i data-feather="refresh-cw"></i> Restore Program';
    
    // Reinitialize feather icons
    if (typeof feather !== 'undefined') {
      feather.replace();
    }
  }

  // Event listeners for input fields
  if (programNameInput) {
    programNameInput.addEventListener('input', function() {
      searchRemovedPrograms(this.value, 'name', programNameSuggestions);
    });

    programNameInput.addEventListener('focus', function() {
      if (this.value.length >= 2) {
        searchRemovedPrograms(this.value, 'name', programNameSuggestions);
      }
    });
  }

  if (programCodeInput) {
    programCodeInput.addEventListener('input', function() {
      searchRemovedPrograms(this.value, 'code', programCodeSuggestions);
    });

    programCodeInput.addEventListener('focus', function() {
      if (this.value.length >= 2) {
        searchRemovedPrograms(this.value, 'code', programCodeSuggestions);
      }
    });
  }

  // Hide suggestions when clicking outside
  document.addEventListener('click', function(event) {
    if (!event.target.closest('.program-field-group')) {
      programNameSuggestions.style.display = 'none';
      programCodeSuggestions.style.display = 'none';
    }
  });

  // Reset form when modal is closed
  document.getElementById('addProgramModal').addEventListener('hidden.bs.modal', function() {
    // Reset form fields
    addProgramForm.reset();
    
    // Remove restore ID input
    const restoreIdInput = document.getElementById('restore_program_id');
    if (restoreIdInput) {
      restoreIdInput.remove();
    }
    
    // Reset submit button
    const submitBtn = document.getElementById('addProgramSubmit');
    submitBtn.innerHTML = '<i data-feather="plus"></i> Create';
    
    // Hide suggestions
    programNameSuggestions.style.display = 'none';
    programCodeSuggestions.style.display = 'none';
    
    // Reinitialize feather icons
    if (typeof feather !== 'undefined') {
      feather.replace();
    }
  });
});
</script>