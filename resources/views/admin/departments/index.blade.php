{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/departments/index.blade.php
* Description: Manage Departments Page (Admin version) – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-10-04] Created admin version based on superladmin departments management
-------------------------------------------------------------------------------
--}}

@extends('layouts.admin')

@section('title', 'Departments • Admin • Syllaverse')
@section('page-title', 'Manage Departments')

@push('styles')
<style>
/* Empty state styles for departments table */
.superadmin-manage-department-table .sv-empty-row td {
  padding: 0;
  background-color: #fff;
  border-radius: 0 0 12px 12px;
  border-top: 1px solid #dee2e6;
  height: 220px;
  text-align: center;
  vertical-align: middle;
}

.superadmin-manage-department-table .sv-empty {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100%;
  max-width: 360px;
  margin: 1.5rem auto 0 auto;
  padding: 0 1rem;
}

.superadmin-manage-department-table .sv-empty h6 {
  font-size: 1rem;
  font-weight: 600;
  color: #CB3737;
  margin-bottom: 0.3rem;
  font-family: 'Poppins', sans-serif;
}

.superadmin-manage-department-table .sv-empty p {
  font-size: 0.85rem;
  color: #777;
  margin-bottom: 0;
}

.superadmin-manage-department-table .sv-empty i {
  width: 16px;
  height: 16px;
  color: #CB3737;
}
</style>
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
                    @include('admin.departments.partials.table-content', ['departments' => $departments])
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
{{-- Include Modals --}}
@include('admin.departments.modals.addDepartmentModal')
@include('admin.departments.modals.editDepartmentModal')
@include('admin.departments.modals.deleteDepartmentModal')

{{-- Include Modals --}}
@include('admin.departments.modals.addDepartmentModal')
@include('admin.departments.modals.editDepartmentModal')
@include('admin.departments.modals.deleteDepartmentModal')

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

{{-- JavaScript --}}
@push('scripts')
@vite('resources/js/admin/departments.js')
@endpush

@endsection