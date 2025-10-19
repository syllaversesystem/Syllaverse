{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/departments/partials/table-content.blade.php
* Description: Table content partial for AJAX refresh
-------------------------------------------------------------------------------
--}}

@if($departments->isEmpty())
<tr class="superadmin-manage-department-empty-row">
    <td colspan="4">
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
        <td colspan="4" class="d-table-cell d-md-none p-0 border-0">
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
