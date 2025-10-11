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

  {{-- Toolbar --}}
  <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <h6 class="mb-0 fw-semibold" style="font-size:.95rem;">Student Outcomes</h6>

    <div class="d-flex align-items-center gap-2">
      {{-- Save Order --}}
      <button type="button"
              class="btn btn-light btn-sm border rounded-pill sv-save-order-btn"
              data-sv-type="so"
              disabled
              title="Save current order">
        <i data-feather="save"></i><span class="d-none d-md-inline ms-1">Save Order</span>
      </button>

      {{-- Add --}}
      <button type="button"
              class="btn-brand-sm"
              data-bs-toggle="modal"
              data-bs-target="#addSoModal"
              aria-label="Add SO"
              title="Add SO">
        <i data-feather="plus"></i>
      </button>
    </div>
  </div>

  {{-- Table --}}
  <div class="table-wrapper position-relative">
    <div class="table-responsive">
      <table class="table mb-0" id="svTable-so" data-sv-type="so">
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
            <tr class="sv-empty-row">
              <td colspan="4">
                <div class="sv-empty">
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
</div>
