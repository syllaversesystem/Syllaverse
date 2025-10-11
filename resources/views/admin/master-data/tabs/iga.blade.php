{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/master-data/tabs/iga.blade.php
* Description: IGA (Institutional Graduate Attributes) tab - matches SO/ILO UI structure
-------------------------------------------------------------------------------
--}}

{{-- Toolbar --}}
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
  <h6 class="mb-0 fw-semibold" style="font-size:.95rem;">Institutional Graduate Attributes</h6>

  <div class="d-flex align-items-center gap-2">
    {{-- Save Order --}}
    <button type="button"
            class="btn btn-light btn-sm border rounded-pill sv-save-order-btn"
            data-sv-type="iga"
            disabled
            title="Save current order">
      <i data-feather="save"></i><span class="d-none d-md-inline ms-1">Save Order</span>
    </button>

    {{-- Add --}}
    <button type="button"
            class="btn-brand-sm"
            data-bs-toggle="modal"
            data-bs-target="#addIgaModal"
            aria-label="Add IGA"
            title="Add IGA">
      <i data-feather="plus"></i>
    </button>
  </div>
</div>

{{-- Table --}}
<div class="table-wrapper position-relative">
  <div class="table-responsive">
    <table class="table mb-0" id="svTable-iga" data-sv-type="iga">
      <thead>
        <tr>
          <th></th>
          <th><i data-feather="code"></i> Code</th>
          <th><i data-feather="align-left"></i> Description</th>
          <th class="text-end"><i data-feather="more-vertical"></i></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($igas->sortBy('sort_order') as $iga)
          <tr data-id="{{ $iga->id }}">
            <td class="text-muted">
              <i class="sv-row-grip bi bi-grip-vertical fs-5" title="Drag to reorder"></i>
            </td>
            <td class="sv-code fw-semibold">{{ $iga->code }}</td>
            <td class="text-muted">{{ $iga->description }}</td>
            <td class="text-end">
              {{-- Edit --}}
              <button type="button"
                      class="btn action-btn rounded-circle edit me-2"
                      data-bs-toggle="modal"
                      data-bs-target="#editMasterDataModal"
                      onclick="setupEditModal('iga', {{ $iga->id }}, '{{ addslashes($iga->description) }}')"
                      title="Edit">
                <i data-feather="edit"></i>
              </button>

              {{-- Delete --}}
              <button type="button"
                      class="btn action-btn rounded-circle delete"
                      data-bs-toggle="modal"
                      data-bs-target="#deleteMasterDataModal"
                      onclick="setupDeleteModal('iga', {{ $iga->id }}, '{{ addslashes($iga->description) }}')"
                      title="Delete">
                <i data-feather="trash"></i>
              </button>
            </td>
          </tr>
        @empty
          <tr class="sv-empty-row">
            <td colspan="4">
              <div class="sv-empty">
                <h6>No Institutional Graduate Attributes</h6>
                <p>Click <i data-feather="plus"></i> to add one.</p>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>