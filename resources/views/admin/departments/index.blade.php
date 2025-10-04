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

{{-- JavaScript --}}
@push('scripts')
<script src="{{ asset('js/admin/departments.js') }}"></script>
@endpush

@endsection