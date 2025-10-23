{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/departments/index.blade.php
* Description: Manage Departments Page (Faculty version) – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-10-19] Created faculty version based on admin departments management
-------------------------------------------------------------------------------
--}}

@extends('layouts.faculty')

@section('title', 'Departments • Faculty • Syllaverse')
@section('page-title', 'Manage Departments')

@push('styles')
@vite('resources/css/superadmin/departments/departments.css')
@endpush

@section('content')
<div class="department-card">

    {{-- ░░░ START: Toolbar Section ░░░ --}}
    <div class="superadmin-manage-department-toolbar">
        <div class="input-group">
            <span class="input-group-text"><i data-feather="search"></i></span>
            <input type="search" class="form-control" placeholder="Search departments..." aria-label="Search departments">
        </div>

        <span class="flex-spacer"></span>

        <button class="btn-brand-sm d-none d-md-inline-flex"
            data-bs-toggle="modal"
            data-bs-target="#addDepartmentModal"
            aria-label="Add Department"
            title="Add Department">
            <i data-feather="plus"></i>
        </button>
    </div>
    {{-- ░░░ END: Toolbar Section ░░░ --}}

    {{-- ░░░ START: Table Section ░░░ --}}
    <div class="table-wrapper position-relative">
        <div class="table-responsive">
            <table class="table superadmin-manage-department-table mb-0">
                <thead class="superadmin-manage-department-table-header d-none d-md-table-header-group">
                    <tr>
                        <th><i data-feather="code"></i> Code</th>
                        <th><i data-feather="briefcase"></i> Name</th>
                        <th><i data-feather="layers"></i> Programs</th>
                        <th class="text-end"><i data-feather="more-vertical"></i></th>
                    </tr>
                </thead>
                <tbody id="departmentsTableBody">
                    @include('faculty.departments.partials.table-content', ['departments' => $departments])
                </tbody>
            </table>
        </div>
    </div>
    {{-- ░░░ END: Table Section ░░░ --}}
</div>

{{-- ░░░ START: Floating Action Button (Mobile Only) ░░░ --}}
<button class="btn-brand-sm add-dept-fab d-md-none"
    data-bs-toggle="modal"
    data-bs-target="#addDepartmentModal"
    aria-label="Add Department"
    title="Add Department">
    <i data-feather="plus"></i>
</button>
{{-- ░░░ END: Floating Action Button (Mobile Only) ░░░ --}}

{{-- ░░░ START: Modals Section ░░░ --}}
@include('faculty.departments.modals.addDepartmentModal')
@include('faculty.departments.modals.editDepartmentModal')
@include('faculty.departments.modals.deleteDepartmentModal')

{{-- ░░░ START: Enhanced Department Field Group Styling ░░░ --}}
<style>
/* ============================================================================
   FORM FIELD STYLES - Department field group styling matching program modals
   ============================================================================ */
.department-field-group {
  margin-bottom: 1rem;
}

.department-field-group .form-label {
  font-weight: 500;
  color: var(--sv-text-muted);
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.department-field-group .form-control,
.department-field-group .form-select {
  border: 1px solid var(--sv-bdr, #E3E3E3);
  border-radius: 0.375rem;
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.department-field-group .form-control:focus,
.department-field-group .form-select:focus {
  border-color: var(--sv-bdr, #E3E3E3);
  box-shadow: none;
  outline: none;
}

/* Remove browser default yellow/orange focus effects from textareas */
.department-field-group textarea.form-control:focus {
  border-color: var(--sv-bdr, #E3E3E3);
  box-shadow: none;
  outline: none;
  background-color: #fff;
}
</style>
{{-- ░░░ END: Enhanced Department Field Group Styling ░░░ --}}

{{-- ░░░ START: Enhanced Modal Backdrop CSS ░░░ --}}
<style>
  /* Ensure modals appear properly and clean up correctly */
  .modal {
    z-index: 1055 !important;
  }
  .modal-backdrop {
    z-index: 1050 !important;
    transition: opacity 0.15s linear;
  }
  .modal.show .modal-dialog {
    z-index: 1056 !important;
  }
  
  /* Ensure body cleanup when modal is closed */
  body:not(.modal-open) {
    overflow: visible !important;
    padding-right: 0 !important;
  }
  
  /* Allow Bootstrap to handle backdrop visibility naturally */
  .modal-backdrop.show {
    opacity: 0.5;
  }
  .modal-backdrop:not(.show) {
    opacity: 0;
  }
</style>
{{-- ░░░ END: Enhanced Modal Backdrop CSS ░░░ --}}
{{-- ░░░ END: Modals Section ░░░ --}}
@endsection

{{-- JavaScript --}}
@push('scripts')
@vite('resources/js/faculty/departments.js')
@endpush
