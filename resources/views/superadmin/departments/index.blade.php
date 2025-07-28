{{-- 
-------------------------------------------------------------------------------
* File: resources/views/superadmin/departments/index.blade.php
* Description: Manage Departments Page (mobile: cards, icon labels, larger actions, 1s hold-to-drag FAB) – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-07-28] Initial creation – department management page with responsive layout and modals.
[2025-07-28] Extracted inline scripts to external JS, added Vite asset loading.
-------------------------------------------------------------------------------
--}}

@extends('layouts.superadmin')

@section('title', 'Departments • Super Admin • Syllaverse')
@section('page-title', 'Manage Departments')


@section('content')
<div class="department-card">

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ░░░ START: Toolbar Section ░░░ --}}
    <div class="toolbar mb-4">
        <div class="input-group">
            <span class="input-group-text"><i data-feather="search"></i></span>
            <input type="search" class="form-control" placeholder="Search departments..." aria-label="Search departments">
        </div>
        <button class="btn-brand btn-brand-sm d-none d-md-inline-flex"
            data-bs-toggle="modal"
            data-bs-target="#addDepartmentModal"
            aria-label="Add Department"
            title="Add Department">
            <i data-feather="plus"></i>
        </button>
    </div>
    {{-- ░░░ END: Toolbar Section ░░░ --}}

    {{-- ░░░ START: Departments Table ░░░ --}}
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th><i data-feather="hash"></i></th>
                    <th><i data-feather="briefcase"></i> Name</th>
                    <th><i data-feather="code"></i> Code</th>
                    <th><i data-feather="calendar"></i> Created On</th>
                    <th class="text-end"><i data-feather="more-vertical"></i></th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $index => $department)
                <tr>
                    <td data-label-icon="hash">{{ $index + 1 }}</td>
                    <td data-label-icon="briefcase">{{ $department->name }}</td>
                    <td data-label-icon="code">{{ $department->code }}</td>
                    <td data-label-icon="calendar">{{ $department->created_at->format('Y-m-d') }}</td>
                    <td data-label-icon="more-vertical" class="text-end">
                        <button class="btn action-btn edit me-2"
                            data-bs-toggle="modal"
                            data-bs-target="#editDepartmentModal"
                            data-id="{{ $department->id }}"
                            data-name="{{ $department->name }}"
                            data-code="{{ $department->code }}"
                            aria-label="Edit {{ $department->name }}"
                            onclick="setEditDepartment(this)">
                            <i data-feather="edit"></i>
                        </button>
                        <button class="btn action-btn delete"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteDepartmentModal"
                            data-id="{{ $department->id }}"
                            aria-label="Delete {{ $department->name }}"
                            onclick="setDeleteDepartment(this)">
                            <i data-feather="trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="empty-state">No departments available.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{-- ░░░ END: Departments Table ░░░ --}}
</div>

{{-- ░░░ START: Floating Action Button (Mobile Only) ░░░ --}}
<button class="btn-brand btn-brand-sm add-dept-fab d-md-none"
    id="draggableAddFab"
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

