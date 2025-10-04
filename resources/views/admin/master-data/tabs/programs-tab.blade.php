{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/master-data/tabs/programs-tab.blade.php
* Description: Programs list tab – aligned with Superadmin modal UX, Dept Chair controls
-------------------------------------------------------------------------------
📜 Log:
[2025-08-17] Synced with ProgramController routes (store/update/destroy).
              Added placeholders, success/error flash handling.
[2025-08-17] AJAX-ready: added #programAlerts container, row IDs, switched Delete to modal.
[2025-08-18] UI: Removed leading number column; adjusted table headers and colspans.
-------------------------------------------------------------------------------
--}}

<div class="table-wrapper position-relative">

  {{-- ░░░ START: Toolbar Section ░░░ --}}
  <div class="superadmin-manage-department-toolbar">
    <div class="input-group">
      <span class="input-group-text"><i data-feather="search"></i></span>
      <input type="search" class="form-control" placeholder="Search programs..." aria-label="Search programs" id="programsSearch">
    </div>

    <span class="flex-spacer"></span>

    <button type="button"
            class="btn-brand-sm d-none d-md-inline-flex"
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
    <table class="table mb-0 sv-accounts-table" id="svProgramsTable">
      <thead>
        <tr>
          <th><i data-feather="type"></i> Program Name</th>
          <th><i data-feather="code"></i> Code</th>
          <th><i data-feather="calendar"></i> Created</th>
          <th class="text-end"><i data-feather="more-vertical"></i></th>
        </tr>
      </thead>
      <tbody>
        @forelse($programs as $program)
          <tr id="program-{{ $program->id }}">
            <td>{{ $program->name }}</td>
            <td>{{ $program->code }}</td>
            <td>{{ $program->created_at->format('Y-m-d') }}</td>
            <td class="text-end">
              {{-- Edit --}}
              <button type="button"
                      class="btn action-btn rounded-circle edit me-2 editProgramBtn"
                      data-bs-toggle="modal"
                      data-bs-target="#editProgramModal"
                      data-id="{{ $program->id }}"
                      data-name="{{ $program->name }}"
                      data-code="{{ $program->code }}"
                      data-description="{{ $program->description }}"
                      title="Edit"
                      aria-label="Edit">
                <i data-feather="edit"></i>
              </button>

              {{-- Delete --}}
              <button type="button"
                      class="btn action-btn rounded-circle delete deleteProgramBtn"
                      data-bs-toggle="modal"
                      data-bs-target="#deleteProgramModal"
                      data-id="{{ $program->id }}"
                      data-name="{{ $program->name }}"
                      title="Delete"
                      aria-label="Delete">
                <i data-feather="trash"></i>
              </button>
            </td>
          </tr>
        @empty
          <tr class="sv-empty-row">
            <td colspan="4">
              <div class="sv-empty">
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

{{-- ░░░ START: Include Modals ░░░ --}}
@include('admin.master-data.modals.add-program-modal')
@include('admin.master-data.modals.edit-program-modal')
@include('admin.master-data.modals.delete-program-modal')
{{-- ░░░ END: Include Modals ░░░ --}}
