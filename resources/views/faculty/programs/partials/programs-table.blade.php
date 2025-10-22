{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/programs/partials/programs-table.blade.php  
* Description: Programs table component with search, filter, and CRUD controls
-------------------------------------------------------------------------------
📜 Log:
[2025-01-16] Created faculty version based on admin programs table
-------------------------------------------------------------------------------
--}}

{{-- ░░░ START: Programs Table Wrapper ░░░ --}}
<div class="programs-table-wrapper position-relative">

  {{-- ░░░ START: Toolbar Section ░░░ --}}
  <div class="programs-toolbar">
    <div class="input-group">
      <span class="input-group-text"><i data-feather="search"></i></span>
      <input type="search" class="form-control" placeholder="Search programs..." aria-label="Search programs" id="programsSearch">
    </div>

    @if($showDepartmentFilter ?? false)
    <div class="department-filter-wrapper">
      <select class="form-select form-select-sm" id="departmentFilter" onchange="filterByDepartment(this.value)">
        <option value="all" {{ ($departmentFilter ?? 'all') == 'all' ? 'selected' : '' }}>All Departments</option>
        @foreach($departments as $department)
          <option value="{{ $department->id }}" {{ ($departmentFilter ?? '') == $department->id ? 'selected' : '' }}>
            {{ $department->code }}
          </option>
        @endforeach
      </select>
    </div>
    @endif

    <span class="flex-spacer"></span>

    <button type="button"
            class="btn programs-add-btn d-none d-md-inline-flex"
            data-bs-toggle="modal"
            data-bs-target="#addProgramModal"
            aria-label="Add Program"
            title="Add Program">
      <i data-feather="plus"></i>
    </button>
  </div>
  {{-- ░░░ END: Toolbar Section ░░░ --}}

  {{-- ░░░ START: Alerts Section ░░░ --}}
  <div id="programAlerts" class="mb-2"></div>
  {{-- ░░░ END: Alerts Section ░░░ --}}

  {{-- ░░░ START: Table ░░░ --}}
  <div class="table-responsive">
    <table class="table mb-0 programs-table" id="svProgramsTable">
      <thead>
        <tr>
          <th><i data-feather="type"></i> Program Name</th>
          <th><i data-feather="code"></i> Code</th>
          @if(($showDepartmentColumn ?? true) && ($departmentFilter ?? 'all') == 'all')
            <th><i data-feather="layers"></i> Department</th>
          @endif
          <th class="text-end"><i data-feather="more-vertical"></i></th>
        </tr>
      </thead>
      <tbody>
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
          <tr class="programs-empty-row">
            <td colspan="{{ (($showDepartmentColumn ?? true) && ($departmentFilter ?? 'all') == 'all') ? '4' : '3' }}">
              <div class="programs-empty">
                <h6>No programs found</h6>
                <p>Click the <i data-feather="plus"></i> button to add one.</p>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{-- ░░░ END: Table ░░░ --}}

</div>

<script>
// Pass department filter state and column visibility to JavaScript
window.programsConfig = {
  departmentFilter: @json($departmentFilter ?? null),
  showDepartmentColumn: @json($showDepartmentColumn ?? true)
};
</script>