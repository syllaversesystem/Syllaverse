{{-- 
-------------------------------------------------------------------------------
* File: resources/views/superadmin/departments/index.blade.php
* Description: Manage Departments Page (with responsive table, modals, and FAB) – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-07-28] Initial creation – department management page with responsive layout and modals.
[2025-08-06] Added “Handled By” and “Programs” columns; removed “Created On” column.
[2025-08-06] Removed alert block – now handled globally via <x-alert-overlay /> component.
[2025-08-07] Fully customized mobile card layout with split dropdown column – refined spacing & typography.
-------------------------------------------------------------------------------
--}}

@extends('layouts.superadmin')

@section('title', 'Departments • Super Admin • Syllaverse')
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
                <tbody>
                    @if($departments->isEmpty())
                    <tr class="superadmin-manage-department-empty-row">
                        <td colspan="6">
                            <div class="empty-table">
                                <h6>No departments found</h6>
                                <p>Click the <i data-feather="plus"></i> button to add one.</p>
                            </div>
                        </td>
                    </tr>
                    @else
                        @foreach($departments as $department)
                        <tr>

                            {{-- ░░░ START: Mobile Card Layout ░░░ --}}
                            <td colspan="6" class="d-table-cell d-md-none p-0 border-0">
                                <div class="dept-card-rowed">

                                    <div class="dept-header-row">
                                        <div class="dept-code">{{ $department->code }}</div>
                                        <div class="dept-programs">
                                            {{ $department->programs->count() }} {{ Str::plural('program', $department->programs->count()) }}
                                        </div>
                                        <div class="dropdown dept-card-dropdown">
                                            <button class="btn btn-action-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i data-feather="more-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <button class="dropdown-item"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editDepartmentModal"
                                                        data-id="{{ $department->id }}"
                                                        data-name="{{ $department->name }}"
                                                        data-code="{{ $department->code }}"
                                                        onclick="setEditDepartment(this)">
                                                        <i data-feather="edit" class="me-2"></i> Edit
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item text-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteDepartmentModal"
                                                        data-id="{{ $department->id }}"
                                                        data-name="{{ $department->name }}"
                                                        data-code="{{ $department->code }}"
                                                        onclick="setDeleteDepartment(this)">
                                                        <i data-feather="trash" class="me-2"></i> Delete
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="dept-name">{{ $department->name }}</div>
                                </div>
                            </td>
                            {{-- ░░░ END: Mobile Card Layout ░░░ --}}

                            {{-- ░░░ START: Desktop Row Layout (hidden on mobile) ░░░ --}}
                            <td class="d-none d-md-table-cell">{{ $department->code }}</td>
                            <td class="d-none d-md-table-cell">{{ $department->name }}</td>
                            <td class="d-none d-md-table-cell">
                                {{ $department->programs->count() }} {{ Str::plural('program', $department->programs->count()) }}
                            </td>
                            <td class="d-none d-md-table-cell text-end">
                                <button class="btn action-btn edit me-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editDepartmentModal"
                                    data-id="{{ $department->id }}"
                                    data-name="{{ $department->name }}"
                                    data-code="{{ $department->code }}"
                                    onclick="setEditDepartment(this)">
                                    <i data-feather="edit"></i>
                                </button>
                                <button class="btn action-btn delete"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteDepartmentModal"
                                    data-id="{{ $department->id }}"
                                    data-name="{{ $department->name }}"
                                    data-code="{{ $department->code }}"
                                    onclick="setDeleteDepartment(this)">
                                    <i data-feather="trash"></i>
                                </button>
                            </td>
                            {{-- ░░░ END: Desktop Row Layout ░░░ --}}

                        </tr>
                        @endforeach
                    @endif
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
@include('superadmin.departments.modals.addDepartmentModal')
@include('superadmin.departments.modals.editDepartmentModal')
@include('superadmin.departments.modals.deleteDepartmentModal')

{{-- ░░░ END: Modals Section ░░░ --}}
@endsection
