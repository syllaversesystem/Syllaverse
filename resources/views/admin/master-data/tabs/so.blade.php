{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/master-data/tabs/so.blade.php
* Description: Student Outcomes (SO) Tab – aligned with modal UX (Edit/Delete)
-------------------------------------------------------------------------------
📜 Log:
[2025-08-16] Synced markup with JS (svTable-so, .sv-row-grip, sv-save-order-btn).
[2025-08-18] Route fix – delete now uses admin.so.destroy; switched to Delete modal.
-------------------------------------------------------------------------------
--}}

<div class="tab-pane fade show active" id="so" role="tabpanel" aria-labelledby="so-tab">

  {{-- ░░░ START: Student Outcomes Toolbar ░░░ --}}
  <div class="so-toolbar">
    <div class="input-group">
      <span class="input-group-text"><i data-feather="search"></i></span>
      <input type="search" class="form-control" placeholder="Search student outcomes..." aria-label="Search student outcomes" id="soSearch">
    </div>

    <div class="department-filter-wrapper">
      <select class="form-select form-select-sm" id="soDepartmentFilter" onchange="filterSOByDepartment(this.value)">
        <option value="all">All Departments</option>
        @foreach($departments ?? [] as $department)
          <option value="{{ $department->id }}">{{ $department->code }}</option>
        @endforeach
      </select>
    </div>

    <span class="flex-spacer"></span>

    {{-- Save Order Button --}}
    <button type="button"
            class="btn btn-light btn-sm border rounded-pill sv-save-order-btn"
            data-sv-type="so"
            disabled
            title="Save current order">
      <i data-feather="save"></i><span class="d-none d-md-inline ms-1">Save Order</span>
    </button>

    {{-- Add SO Button --}}
    <button type="button"
            class="btn so-add-btn"
            data-bs-toggle="modal"
            data-bs-target="#addSoModal"
            aria-label="Add SO"
            title="Add Student Outcome">
      <i data-feather="plus"></i>
    </button>
  </div>
  {{-- ░░░ END: Student Outcomes Toolbar ░░░ --}}

  {{-- ░░░ START: Student Outcomes Table ░░░ --}}
  <div class="so-table-wrapper position-relative">
    <div class="table-responsive">
      <table class="table mb-0 so-table" id="svTable-so" data-sv-type="so">
        <thead>
          <tr>
            <th></th>
            <th><i data-feather="code"></i> Code</th>
            <th><i data-feather="align-left"></i> Description</th>
            <th class="text-end"><i data-feather="more-vertical"></i></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($studentOutcomes->sortBy('position') as $so)
            <tr data-id="{{ $so->id }}">
              <td class="text-muted">
                <i class="sv-row-grip bi bi-grip-vertical fs-5" title="Drag to reorder"></i>
              </td>
              <td class="sv-code fw-semibold">{{ $so->code }}</td>
              <td class="text-muted">{{ $so->description }}</td>
              <td class="text-end">
                {{-- Edit --}}
                <button type="button"
                        class="btn action-btn rounded-circle edit me-2"
                        data-bs-toggle="modal"
                        data-bs-target="#editSoModal"
                        data-sv-id="{{ $so->id }}"
                        data-sv-code="{{ $so->code }}"
                        data-sv-description="{{ $so->description }}"
                        title="Edit">
                  <i data-feather="edit"></i>
                </button>

                {{-- Delete (opens modal) --}}
                <button type="button"
                        class="btn action-btn rounded-circle delete deleteSoBtn"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteSoModal"
                        data-id="{{ $so->id }}"
                        data-code="{{ $so->code }}"
                        title="Delete">
                  <i data-feather="trash"></i>
                </button>
              </td>
            </tr>
          @empty
            <tr class="so-empty-row">
              <td colspan="4">
                <div class="so-empty">
                  <h6>No Student Outcomes</h6>
                  <p>Click <i data-feather="plus"></i> to add one.</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  {{-- ░░░ END: Student Outcomes Table ░░░ --}}
</div>
