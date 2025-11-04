{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/programs/partials/programs-table-content.blade.php  
* Description: Programs table tbody content for AJAX loading
-------------------------------------------------------------------------------
📜 Log:
[2025-11-04] Created partial for AJAX department filtering
-------------------------------------------------------------------------------
--}}

@forelse($programs as $program)
  <tr id="program-{{ $program->id }}">
    <td>{{ $program->name }}</td>
    <td>{{ $program->code }}</td>
    @if(($showDepartmentColumn ?? true) && ($departmentFilter ?? 'all') == 'all')
      <td>{{ $program->department->code ?? 'N/A' }}</td>
    @endif
    <td class="text-end">
      {{-- Edit --}}
      <button type="button"
              class="btn programs-action-btn edit-btn rounded-circle me-2 editProgramBtn"
              data-bs-toggle="modal"
              data-bs-target="#editProgramModal"
              data-id="{{ $program->id }}"
              data-name="{{ $program->name }}"
              data-code="{{ $program->code }}"
              data-description="{{ $program->description }}"
              data-department-id="{{ $program->department_id }}"
              title="Edit"
              aria-label="Edit">
        <i data-feather="edit"></i>
      </button>

      {{-- Delete --}}
      <button type="button"
              class="btn programs-action-btn delete-btn rounded-circle deleteProgramBtn"
              data-bs-toggle="modal"
              data-bs-target="#deleteProgramModal"
              data-id="{{ $program->id }}"
              data-name="{{ $program->name }}"
              data-code="{{ $program->code }}"
              title="Delete"
              aria-label="Delete">
        <i data-feather="trash"></i>
      </button>
    </td>
  </tr>
@empty
  @php($searching = isset($isSearch) && $isSearch)
  <tr class="programs-empty-row">
    <td colspan="{{ (($showDepartmentColumn ?? true) && ($departmentFilter ?? 'all') == 'all') ? '4' : '3' }}">
      <div class="programs-empty">
        @if($searching)
          <h6>No matching programs</h6>
          <p>Try a different search term.</p>
        @else
          <h6>No programs found</h6>
          <p>Click the <i data-feather="plus"></i> button to add one.</p>
        @endif
      </div>
    </td>
  </tr>
@endforelse