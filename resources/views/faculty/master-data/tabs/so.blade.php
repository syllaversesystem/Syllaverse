{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/master-data/tabs/so.blade.php
* Description: Student Outcomes (SO) Tab – matches SDG UI structure
-------------------------------------------------------------------------------
--}}

<style>
/* Empty state styles for SO table - matches courses module */
.table .so-empty-row td {
  padding: 0;
  background-color: #fff;
  border-radius: 0 0 12px 12px;
  border-top: 1px solid #dee2e6;
  height: 220px;
  text-align: center;
  vertical-align: middle;
}

.table .so-empty {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100%;
  max-width: 360px;
  margin: 1.5rem auto 0 auto;
  padding: 0 1rem;
}

.table .so-empty h6 {
  font-size: 1rem;
  font-weight: 600;
  color: #CB3737;
  margin-bottom: 0.3rem;
  font-family: 'Poppins', sans-serif;
}

.table .so-empty p {
  font-size: 0.85rem;
  color: #777;
  margin-bottom: 0;
}

.table .so-empty i {
  width: 16px;
  height: 16px;
  color: #CB3737;
}
</style>

{{-- Toolbar --}}
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
  <h6 class="mb-0 fw-semibold" style="font-size:.95rem;">Student Outcomes</h6>

  <div class="d-flex align-items-center gap-2">
    {{-- Add --}}
    <button type="button"
            class="btn-brand-sm"
            data-bs-toggle="modal"
            data-bs-target="#addMasterDataModal"
            onclick="setupAddModal('so')"
            aria-label="Add SO"
            title="Add Student Outcome">
      <i data-feather="plus"></i>
    </button>
  </div>
</div>

{{-- Table --}}
<div class="table-wrapper position-relative">
  <div class="table-responsive" style="border: 1px solid var(--sv-border, #E3E3E3); border-radius: 14px; overflow: hidden; background: #fff;">
    <table class="table mb-0 table-layout-fixed" id="svTable-so" data-sv-type="so" style="table-layout: fixed; width: 100%;">
      <thead>
        <tr>
          <th class="text-truncate" style="width: 20%;"><i data-feather="type"></i> Title</th>
          <th class="text-truncate" style="width: 45%;"><i data-feather="align-left"></i> Description</th>
          <th class="text-truncate" style="width: 20%;"><i data-feather="building"></i> Department</th>
          <th class="text-end" style="width: 15%; min-width: 100px;"><i data-feather="more-vertical"></i></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($studentOutcomes as $so)
          <tr data-id="{{ $so->id }}">
            <td class="fw-semibold text-truncate" style="max-width: 0;" title="{{ $so->title ?? 'Untitled' }}">{{ $so->title ?? 'Untitled' }}</td>
            <td class="text-muted" style="word-wrap: break-word; white-space: normal;">{{ $so->description }}</td>
            <td class="text-muted text-truncate" style="max-width: 0;" title="{{ $so->department->name ?? 'No Department' }}">
              <small class="badge bg-light text-dark">{{ $so->department->code ?? 'N/A' }}</small>
            </td>
            <td class="text-end text-nowrap" style="min-width: 100px;">
              {{-- Edit --}}
              <button type="button"
                      class="btn action-btn rounded-circle edit me-2"
                      data-bs-toggle="modal"
                      data-bs-target="#editMasterDataModal"
                      onclick="setupEditModal('so', {{ $so->id }}, '{{ addslashes($so->description) }}', '{{ addslashes($so->title ?? '') }}', {{ $so->department_id ?? 'null' }})"
                      title="Edit">
                <i data-feather="edit"></i>
              </button>

              {{-- Delete --}}
              <button type="button"
                      class="btn action-btn rounded-circle delete"
                      data-bs-toggle="modal"
                      data-bs-target="#deleteMasterDataModal"
                      onclick="setupDeleteModal('so', {{ $so->id }}, '{{ addslashes($so->title ?? 'Student Outcome') }}')"
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
                <p>Click the <i data-feather="plus"></i> button to add one.</p>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>