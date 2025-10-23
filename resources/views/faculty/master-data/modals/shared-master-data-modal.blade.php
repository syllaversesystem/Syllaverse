{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/master-data/modals/shared-master-data-modal.blade.php
* Description: Shared modals for all master data types (SO, ILO, SDG, IGA, CDIO)
* Updated to match department module styling
-------------------------------------------------------------------------------
--}}

{{-- ░░░ START: Add Master Data Modal ░░░ --}}
<div class="modal fade sv-appt-modal" id="addMasterDataModal" tabindex="-1" aria-labelledby="addMasterDataModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form id="addMasterDataForm" method="POST" action="#" class="modal-content">
      @csrf

      {{-- ░░░ START: Local styles (scoped to this modal) ░░░ --}}
      <style>
        /* Brand tokens */
        #addMasterDataModal {
          --sv-bg:   #FAFAFA;   /* light bg */
          --sv-bdr:  #E3E3E3;   /* borders */
          --sv-acct: #EE6F57;   /* accent/focus */
          --sv-danger:#CB3737;  /* primary action (danger style) */
        }
        #addMasterDataModal .modal-header {
          border-bottom: 1px solid var(--sv-bdr);
          background: var(--sv-bg);
        }
        #addMasterDataModal .sv-card {
          border: 1px solid var(--sv-bdr);
          background: #fff;
          border-radius: .75rem;
        }
        #addMasterDataModal .sv-section-title {
          font-size: .8rem;
          letter-spacing: .02em;
          color: #6c757d;
        }
        #addMasterDataModal .input-group-text {
          background: var(--sv-bg);
          border-color: var(--sv-bdr);
        }
        #addMasterDataModal .form-control,
        #addMasterDataModal .form-select {
          border-color: var(--sv-bdr);
        }
        #addMasterDataModal .form-control:focus,
        #addMasterDataModal .form-select:focus {
          border-color: var(--sv-acct);
          box-shadow: 0 0 0 .2rem rgb(238 111 87 / 15%);
        }
        #addMasterDataModal .btn-danger {
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
        #addMasterDataModal .btn-danger:hover,
        #addMasterDataModal .btn-danger:focus {
          background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(204, 55, 55, 0.12);
          color: #CB3737;
        }
        #addMasterDataModal .btn-danger:hover i,
        #addMasterDataModal .btn-danger:hover svg,
        #addMasterDataModal .btn-danger:focus i,
        #addMasterDataModal .btn-danger:focus svg {
          stroke: #CB3737;
        }
        #addMasterDataModal .btn-danger:active {
          background: linear-gradient(135deg, rgba(255, 230, 225, 0.98), rgba(255, 255, 255, 0.62));
          box-shadow: 0 1px 8px rgba(204, 55, 55, 0.16);
        }
        #addMasterDataModal .btn-danger:active i,
        #addMasterDataModal .btn-danger:active svg {
          stroke: #CB3737;
        }
        /* Cancel button styling */
        #addMasterDataModal .btn-light {
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
        #addMasterDataModal .btn-light:hover,
        #addMasterDataModal .btn-light:focus {
          background: linear-gradient(135deg, rgba(220, 220, 220, 0.88), rgba(240, 240, 240, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(108, 117, 125, 0.12);
          color: #495057;
        }
        #addMasterDataModal .btn-light:hover i,
        #addMasterDataModal .btn-light:hover svg,
        #addMasterDataModal .btn-light:focus i,
        #addMasterDataModal .btn-light:focus svg {
          stroke: #495057;
        }
        #addMasterDataModal .btn-light:active {
          background: linear-gradient(135deg, rgba(240, 242, 245, 0.98), rgba(255, 255, 255, 0.62));
          box-shadow: 0 1px 8px rgba(108, 117, 125, 0.16);
        }
        #addMasterDataModal .btn-light:active i,
        #addMasterDataModal .btn-light:active svg {
          stroke: #495057;
        }
        #addMasterDataModal .sv-divider {
          height: 1px;
          background: var(--sv-bdr);
          margin: .75rem 0;
        }
        /* Enhanced Alert Styles */
        #addMasterDataModal .alert-danger {
          background: rgba(255, 235, 235, 0.9);
          border: 1px solid rgba(220, 53, 69, 0.3);
          border-radius: 0.5rem;
          color: #721c24;
          font-size: 0.875rem;
          padding: 0.75rem 1rem;
          margin-bottom: 1rem;
        }
        #addMasterDataModal .alert-danger ul {
          margin-bottom: 0;
          padding-left: 1.25rem;
        }
        #addMasterDataModal .alert-danger li {
          margin-bottom: 0.25rem;
        }
      </style>
      {{-- ░░░ END: Local styles ░░░ --}}

      {{-- ░░░ START: Header ░░░ --}}
      <div class="modal-header">
        <h5 class="modal-title fw-semibold" id="addMasterDataModalLabel">Add New Item</h5>
      </div>
      {{-- ░░░ END: Header ░░░ --}}

      {{-- ░░░ START: Body ░░░ --}}
      <div class="modal-body">
        {{-- Inline error box (filled by JS on 422) --}}
        <div id="addMasterDataErrors" class="alert alert-danger d-none small mb-3" role="alert"></div>

        <div class="mb-3" id="titleField">
          <label for="masterDataTitle" class="form-label small fw-medium text-muted">Title</label>
          <input type="text" class="form-control form-control-sm" id="masterDataTitle" name="title" placeholder="Enter title" required>
        </div>

        <div class="mb-3" id="departmentField" style="display: none;">
          <label for="masterDataDepartment" class="form-label small fw-medium text-muted">Department</label>
          <select class="form-select form-select-sm" id="masterDataDepartment" name="department_id">
            <option value="">Select a department</option>
            @foreach($departments as $dept)
              <option value="{{ $dept->id }}">{{ $dept->code }} - {{ $dept->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="mb-3">
          <label for="masterDataDescription" class="form-label small fw-medium text-muted">Description</label>
          <textarea class="form-control form-control-sm" id="masterDataDescription" name="description" rows="4" placeholder="Enter description" required></textarea>
        </div>
      </div>
      {{-- ░░░ END: Body ░░░ --}}

      {{-- ░░░ START: Footer ░░░ --}}
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
          <i data-feather="x"></i> Cancel
        </button>
        <button type="submit" class="btn btn-danger" id="addMasterDataSubmit">
          <i data-feather="plus"></i> <span id="addButtonText">Create</span>
        </button>
      </div>
      {{-- ░░░ END: Footer ░░░ --}}
    </form>
  </div>
</div>
{{-- ░░░ END: Add Master Data Modal ░░░ --}}

{{-- ░░░ START: Edit Master Data Modal ░░░ --}}
<div class="modal fade sv-appt-modal" id="editMasterDataModal" tabindex="-1" aria-labelledby="editMasterDataModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form id="editMasterDataForm" method="POST" action="#" class="modal-content">
      @csrf
      @method('PUT')
      <input type="hidden" id="editMasterDataId" name="id" value="">

      {{-- ░░░ START: Local styles (scoped to this modal) ░░░ --}}
      <style>
        /* Brand tokens */
        #editMasterDataModal {
          --sv-bg:   #FAFAFA;   /* light bg */
          --sv-bdr:  #E3E3E3;   /* borders */
          --sv-acct: #EE6F57;   /* accent/focus */
          --sv-danger:#CB3737;  /* primary action (danger style) */
        }
        #editMasterDataModal .modal-header {
          border-bottom: 1px solid var(--sv-bdr);
          background: var(--sv-bg);
        }
        #editMasterDataModal .sv-card {
          border: 1px solid var(--sv-bdr);
          background: #fff;
          border-radius: .75rem;
        }
        #editMasterDataModal .sv-section-title {
          font-size: .8rem;
          letter-spacing: .02em;
          color: #6c757d;
        }
        #editMasterDataModal .input-group-text {
          background: var(--sv-bg);
          border-color: var(--sv-bdr);
        }
        #editMasterDataModal .form-control,
        #editMasterDataModal .form-select {
          border-color: var(--sv-bdr);
        }
        #editMasterDataModal .form-control:focus,
        #editMasterDataModal .form-select:focus {
          border-color: var(--sv-acct);
          box-shadow: 0 0 0 .2rem rgb(238 111 87 / 15%);
        }
        #editMasterDataModal .btn-danger {
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
        #editMasterDataModal .btn-danger:hover,
        #editMasterDataModal .btn-danger:focus {
          background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(204, 55, 55, 0.12);
          color: #CB3737;
        }
        #editMasterDataModal .btn-danger:hover i,
        #editMasterDataModal .btn-danger:hover svg,
        #editMasterDataModal .btn-danger:focus i,
        #editMasterDataModal .btn-danger:focus svg {
          stroke: #CB3737;
        }
        /* Cancel button styling */
        #editMasterDataModal .btn-light {
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
        #editMasterDataModal .btn-light:hover,
        #editMasterDataModal .btn-light:focus {
          background: linear-gradient(135deg, rgba(220, 220, 220, 0.88), rgba(240, 240, 240, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(108, 117, 125, 0.12);
          color: #495057;
        }
        #editMasterDataModal .btn-light:hover i,
        #editMasterDataModal .btn-light:hover svg,
        #editMasterDataModal .btn-light:focus i,
        #editMasterDataModal .btn-light:focus svg {
          stroke: #495057;
        }
        #editMasterDataModal .sv-divider {
          height: 1px;
          background: var(--sv-bdr);
          margin: .75rem 0;
        }
        /* Enhanced Alert Styles */
        #editMasterDataModal .alert-danger {
          background: rgba(255, 235, 235, 0.9);
          border: 1px solid rgba(220, 53, 69, 0.3);
          border-radius: 0.5rem;
          color: #721c24;
          font-size: 0.875rem;
          padding: 0.75rem 1rem;
          margin-bottom: 1rem;
        }
        #editMasterDataModal .alert-danger ul {
          margin-bottom: 0;
          padding-left: 1.25rem;
        }
        #editMasterDataModal .alert-danger li {
          margin-bottom: 0.25rem;
        }
      </style>
      {{-- ░░░ END: Local styles ░░░ --}}

      {{-- ░░░ START: Header ░░░ --}}
      <div class="modal-header">
        <h5 class="modal-title fw-semibold" id="editMasterDataModalLabel">Edit Item</h5>
      </div>
      {{-- ░░░ END: Header ░░░ --}}

      {{-- ░░░ START: Body ░░░ --}}
      <div class="modal-body">
        {{-- Inline error box (filled by JS on 422) --}}
        <div id="editMasterDataErrors" class="alert alert-danger d-none small mb-3" role="alert"></div>

        <div class="mb-3" id="editTitleField">
          <label for="editMasterDataTitle" class="form-label small fw-medium text-muted">Title</label>
          <input type="text" class="form-control form-control-sm" id="editMasterDataTitle" name="title" placeholder="Enter title" required>
        </div>

        <div class="mb-3" id="editDepartmentField" style="display: none;">
          <label for="editMasterDataDepartment" class="form-label small fw-medium text-muted">Department</label>
          <select class="form-select form-select-sm" id="editMasterDataDepartment" name="department_id">
            <option value="">Select a department</option>
            @foreach($departments as $dept)
              <option value="{{ $dept->id }}">{{ $dept->code }} - {{ $dept->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="mb-3">
          <label for="editMasterDataDescription" class="form-label small fw-medium text-muted">Description</label>
          <textarea class="form-control form-control-sm" id="editMasterDataDescription" name="description" rows="4" placeholder="Enter description" required></textarea>
        </div>
      </div>
      {{-- ░░░ END: Body ░░░ --}}

      {{-- ░░░ START: Footer ░░░ --}}
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
          <i data-feather="x"></i> Cancel
        </button>
        <button type="submit" class="btn btn-danger" id="editMasterDataSubmit">
          <i data-feather="save"></i> <span id="editButtonText">Update</span>
        </button>
      </div>
      {{-- ░░░ END: Footer ░░░ --}}
    </form>
  </div>
</div>
{{-- ░░░ END: Edit Master Data Modal ░░░ --}}

{{-- ░░░ START: Delete Master Data Modal ░░░ --}}
<div class="modal fade sv-appt-modal" id="deleteMasterDataModal" tabindex="-1" aria-labelledby="deleteMasterDataModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content">
      {{-- ░░░ START: Local styles (scoped to this modal) ░░░ --}}
      <style>
        /* Brand tokens */
        #deleteMasterDataModal {
          --sv-bg:   #FAFAFA;   /* light bg */
          --sv-bdr:  #E3E3E3;   /* borders */
          --sv-acct: #EE6F57;   /* accent/focus */
          --sv-danger:#CB3737;  /* danger red */
        }
        #deleteMasterDataModal .modal-header {
          border-bottom: 1px solid var(--sv-bdr);
          background: var(--sv-bg);
        }
        #deleteMasterDataModal .modal-title {
          color: var(--sv-danger);
        }
        /* Delete button styling */
        #deleteMasterDataModal .btn-danger {
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
        #deleteMasterDataModal .btn-danger:hover,
        #deleteMasterDataModal .btn-danger:focus {
          background: linear-gradient(135deg, rgba(255, 235, 235, 0.88), rgba(255, 245, 245, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(203, 55, 55, 0.15);
          color: var(--sv-danger);
        }
        #deleteMasterDataModal .btn-danger:hover i,
        #deleteMasterDataModal .btn-danger:hover svg,
        #deleteMasterDataModal .btn-danger:focus i,
        #deleteMasterDataModal .btn-danger:focus svg {
          stroke: var(--sv-danger);
        }
        /* Cancel button styling */
        #deleteMasterDataModal .btn-light {
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
        #deleteMasterDataModal .btn-light:hover,
        #deleteMasterDataModal .btn-light:focus {
          background: linear-gradient(135deg, rgba(220, 220, 220, 0.88), rgba(240, 240, 240, 0.46));
          backdrop-filter: blur(7px);
          -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(108, 117, 125, 0.12);
          color: #495057;
        }
        #deleteMasterDataModal .btn-light:hover i,
        #deleteMasterDataModal .btn-light:hover svg,
        #deleteMasterDataModal .btn-light:focus i,
        #deleteMasterDataModal .btn-light:focus svg {
          stroke: #495057;
        }
        #deleteMasterDataModal .alert-warning {
          background: rgba(255, 245, 235, 0.9);
          border: 1px solid rgba(255, 193, 7, 0.3);
          color: #856404;
        }
        #deleteMasterDataModal .alert-danger {
          background: rgba(255, 235, 235, 0.9);
          border: 1px solid rgba(220, 53, 69, 0.3);
          color: #721c24;
        }
        #deleteMasterDataModal .alert-success {
          background: rgba(235, 255, 235, 0.9);
          border: 1px solid rgba(40, 167, 69, 0.3);
          color: #155724;
        }
        /* Enhanced Alert Styles for Error Messages */
        #deleteMasterDataModal .alert-danger {
          background: rgba(255, 235, 235, 0.9);
          border: 1px solid rgba(220, 53, 69, 0.3);
          border-radius: 0.5rem;
          color: #721c24;
          font-size: 0.875rem;
          padding: 0.75rem 1rem;
          margin-bottom: 1rem;
        }
        #deleteMasterDataModal .alert-danger ul {
          margin-bottom: 0;
          padding-left: 1.25rem;
        }
        #deleteMasterDataModal .alert-danger li {
          margin-bottom: 0.25rem;
        }
      </style>
      {{-- ░░░ END: Local styles ░░░ --}}

      {{-- ░░░ START: Body ░░░ --}}
      <div class="modal-body">
        {{-- Item Information Card --}}
        <div class="text-center mb-4">
          <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 rounded-circle mb-3" style="width: 64px; height: 64px;">
            <i data-feather="trash-2" class="text-danger" style="width: 28px; height: 28px;"></i>
          </div>
          <h6 class="fw-semibold mb-2" id="deleteMasterDataModalLabel">Delete Item</h6>
          <p class="text-muted mb-0">Are you sure you want to permanently delete this item?</p>
        </div>

        {{-- Item Details --}}
        <div class="bg-light rounded-3 p-3 mb-4">
          <div class="small text-muted mb-1">You are about to delete:</div>
          <div class="fw-semibold mb-1" id="deleteMasterDataName">Loading...</div>
          <div class="small text-muted" id="deleteMasterDataType">Type: <span class="fw-medium">Loading...</span></div>
        </div>

        {{-- Warning Information --}}
        <div class="alert alert-warning border-0 mb-0" style="background: rgba(255, 193, 7, 0.1);">
          <div class="d-flex align-items-start gap-3">
            <i data-feather="alert-triangle" class="text-warning flex-shrink-0 mt-1" style="width: 18px; height: 18px;"></i>
            <div class="small">
              <div class="fw-medium text-dark mb-2">This action cannot be undone</div>
              <ul class="list-unstyled mb-0 text-muted">
                <li class="mb-1">• This item will be permanently removed</li>
                <li class="mb-0">• Any associated data will be affected</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      {{-- ░░░ END: Body ░░░ --}}

      {{-- ░░░ START: Footer ░░░ --}}
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
          <i data-feather="x"></i> Cancel
        </button>
        <form id="deleteMasterDataForm" method="POST" action="#" class="d-inline">
          @csrf
          @method('DELETE')
          <input type="hidden" id="deleteMasterDataId" name="id" value="">
          <button type="submit" id="deleteMasterDataSubmit" class="btn btn-danger">
            <i data-feather="trash-2"></i> Delete
          </button>
        </form>
      </div>
      {{-- ░░░ END: Footer ░░░ --}}
    </div>
  </div>
</div>
{{-- ░░░ END: Delete Master Data Modal ░░░ --}}

<script>
// JavaScript functions to setup the modals - defined globally
window.setupAddModal = function(type) {
  const modal = document.getElementById('addMasterDataModal');
  const form = document.getElementById('addMasterDataForm');
  const title = document.getElementById('addMasterDataModalLabel');
  const buttonText = document.getElementById('addButtonText');
  const titleField = document.getElementById('titleField');
  const departmentField = document.getElementById('departmentField');
  const departmentSelect = document.getElementById('masterDataDepartment');
  
  // Get user role and department from blade variables
  const userRole = @json($currentUser->role ?? 'guest');
  const userDepartmentId = @json($userPrimaryDepartmentId ?? null);
  
  // Configure based on type
  switch(type) {
    case 'so':
      form.action = '/faculty/master-data/so';
      title.textContent = 'Add Student Outcome';
      buttonText.textContent = 'Create';
      titleField.style.display = 'block';
      
      // Show department field only for admin/chairs, hide for faculty
      if (userRole === 'faculty') {
        departmentField.style.display = 'none';
        departmentSelect.required = false;
        // Remove the department field from form if it exists
        if (departmentSelect.name) departmentSelect.name = '';
      } else {
        departmentField.style.display = 'block';
        departmentSelect.required = true;
        departmentSelect.name = 'department_id';
      }
      break;
    case 'sdg':
      form.action = '/faculty/master-data/sdg';
      title.textContent = 'Add Sustainable Development Goal';
      buttonText.textContent = 'Create';
      titleField.style.display = 'block';
      departmentField.style.display = 'none';
      departmentSelect.required = false;
      break;
    case 'iga':
      form.action = '/faculty/master-data/iga';
      title.textContent = 'Add Institutional Graduate Attribute';
      buttonText.textContent = 'Create';
      titleField.style.display = 'block';
      departmentField.style.display = 'none';
      departmentSelect.required = false;
      break;
    case 'cdio':
      form.action = '/faculty/master-data/cdio';
      title.textContent = 'Add CDIO Outcome';
      buttonText.textContent = 'Create';
      titleField.style.display = 'block';
      departmentField.style.display = 'none';
      departmentSelect.required = false;
      break;
  }
  
  console.log(`🔧 Setup add modal for ${type}, action: ${form.action}, userRole: ${userRole}`);
  
  // Clear form
  form.reset();
  // Clear any previous errors
  const errorContainer = document.getElementById('addMasterDataErrors');
  errorContainer.classList.add('d-none');
  errorContainer.innerHTML = '';
};

window.setupEditModal = function(type, id, description, title = '', departmentId = null) {
  const modal = document.getElementById('editMasterDataModal');
  const form = document.getElementById('editMasterDataForm');
  const modalTitle = document.getElementById('editMasterDataModalLabel');
  const buttonText = document.getElementById('editButtonText');
  const titleField = document.getElementById('editTitleField');
  const titleInput = document.getElementById('editMasterDataTitle');
  const descInput = document.getElementById('editMasterDataDescription');
  const departmentField = document.getElementById('editDepartmentField');
  const departmentSelect = document.getElementById('editMasterDataDepartment');
  
  // Get user role and department from blade variables
  const userRole = @json($currentUser->role ?? 'guest');
  const userDepartmentId = @json($userPrimaryDepartmentId ?? null);
  
  // Configure based on type
  switch(type) {
    case 'so':
      form.action = `/faculty/master-data/so/${id}`;
      modalTitle.textContent = 'Edit Student Outcome';
      buttonText.textContent = 'Update';
      titleField.style.display = 'block';
      
      // Show department field only for admin/chairs, hide for faculty
      if (userRole === 'faculty') {
        departmentField.style.display = 'none';
        departmentSelect.required = false;
        // Remove the department field from form if it exists
        if (departmentSelect.name) departmentSelect.name = '';
      } else {
        departmentField.style.display = 'block';
        departmentSelect.required = true;
        departmentSelect.name = 'department_id';
      }
      break;
    case 'sdg':
      form.action = `/faculty/master-data/sdg/${id}`;
      modalTitle.textContent = 'Edit Sustainable Development Goal';
      buttonText.textContent = 'Update';
      titleField.style.display = 'block';
      departmentField.style.display = 'none';
      departmentSelect.required = false;
      break;
    case 'iga':
      form.action = `/faculty/master-data/iga/${id}`;
      modalTitle.textContent = 'Edit Institutional Graduate Attribute';
      buttonText.textContent = 'Update';
      titleField.style.display = 'block';
      departmentField.style.display = 'none';
      departmentSelect.required = false;
      break;
    case 'cdio':
      form.action = `/faculty/master-data/cdio/${id}`;
      modalTitle.textContent = 'Edit CDIO Outcome';
      buttonText.textContent = 'Update';
      titleField.style.display = 'block';
      departmentField.style.display = 'none';
      departmentSelect.required = false;
      break;
  }
  
  // Set values
  titleInput.value = title;
  descInput.value = description;
  if (departmentId && userRole !== 'faculty') {
    departmentSelect.value = departmentId;
  }
  
  // Clear any previous errors
  const errorContainer = document.getElementById('editMasterDataErrors');
  errorContainer.classList.add('d-none');
  errorContainer.innerHTML = '';
};

window.setupDeleteModal = function(type, id, name) {
  const form = document.getElementById('deleteMasterDataForm');
  const itemName = document.getElementById('deleteMasterDataName');
  const itemType = document.getElementById('deleteMasterDataType');
  
  // Configure based on type
  let typeName = '';
  switch(type) {
    case 'so':
      form.action = `/faculty/master-data/so/${id}`;
      typeName = 'Student Outcome';
      break;
    case 'sdg':
      form.action = `/faculty/master-data/sdg/${id}`;
      typeName = 'Sustainable Development Goal';
      break;
    case 'iga':
      form.action = `/faculty/master-data/iga/${id}`;
      typeName = 'Institutional Graduate Attribute';
      break;
    case 'cdio':
      form.action = `/faculty/master-data/cdio/${id}`;
      typeName = 'CDIO Outcome';
      break;
  }
  
  itemName.textContent = name;
  itemType.innerHTML = `Type: <span class="fw-medium">${typeName}</span>`;
};
</script>