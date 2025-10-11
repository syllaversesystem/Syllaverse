{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/master-data/tabs/sdg.blade.php
* Description: SDG (Sustainable Development Goals) tab - matches SO/ILO UI structure
-------------------------------------------------------------------------------
--}}

{{-- Toolbar --}}
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
  <h6 class="mb-0 fw-semibold" style="font-size:.95rem;">Sustainable Development Goals</h6>

  <div class="d-flex align-items-center gap-2">
    {{-- Save Order --}}
    <button type="button"
            class="btn btn-light btn-sm border rounded-pill sv-save-order-btn"
            data-sv-type="sdg"
            disabled
            title="Save current order">
      <i data-feather="save"></i><span class="d-none d-md-inline ms-1">Save Order</span>
    </button>

    {{-- Add --}}
    <button type="button"
            class="btn-brand-sm"
            data-bs-toggle="modal"
            data-bs-target="#addSdgModal"
            aria-label="Add SDG"
            title="Add SDG">
      <i data-feather="plus"></i>
    </button>
  </div>
</div>

{{-- Table --}}
<div class="table-wrapper position-relative">
  <div class="table-responsive">
    <table class="table mb-0" id="svTable-sdg" data-sv-type="sdg">
      <thead>
        <tr>
          <th></th>
          <th><i data-feather="code"></i> Code</th>
          <th><i data-feather="align-left"></i> Description</th>
          <th class="text-end"><i data-feather="more-vertical"></i></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($sdgs->sortBy('sort_order') as $sdg)
          <tr data-id="{{ $sdg->id }}">
            <td class="text-muted">
              <i class="sv-row-grip bi bi-grip-vertical fs-5" title="Drag to reorder"></i>
            </td>
            <td class="sv-code fw-semibold">{{ $sdg->code }}</td>
            <td class="text-muted">{{ $sdg->description }}</td>
            <td class="text-end">
              {{-- Edit --}}
              <button type="button"
                      class="btn action-btn rounded-circle edit me-2"
                      data-bs-toggle="modal"
                      data-bs-target="#editMasterDataModal"
                      onclick="setupEditModal('sdg', {{ $sdg->id }}, '{{ addslashes($sdg->description) }}')"
                      title="Edit">
                <i data-feather="edit"></i>
              </button>

              {{-- Delete --}}
              <button type="button"
                      class="btn action-btn rounded-circle delete"
                      data-bs-toggle="modal"
                      data-bs-target="#deleteMasterDataModal"
                      onclick="setupDeleteModal('sdg', {{ $sdg->id }}, '{{ addslashes($sdg->description) }}')"
                      title="Delete">
                <i data-feather="trash"></i>
              </button>
            </td>
          </tr>
        @empty
          <tr class="sv-empty-row">
            <td colspan="4">
              <div class="sv-empty">
                <h6>No Sustainable Development Goals</h6>
                <p>Click <i data-feather="plus"></i> to add one.</p>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>