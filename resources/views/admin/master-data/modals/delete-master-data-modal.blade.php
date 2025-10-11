{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/master-data/modals/delete-master-data-modal.blade.php
* Description: Shared Delete confirmation modal for SDG/IGA/CDIO – submitted with AJAX
-------------------------------------------------------------------------------
📜 Log:
[2025-08-12] Initial creation – standalone shared Delete modal; JS populates action/labels on open.
-------------------------------------------------------------------------------
--}}

{{-- ░░░ START: Delete Confirm Modal (Shared) ░░░ --}}
<div class="modal fade"
     id="deleteMasterDataModal"
     tabindex="-1"
     aria-labelledby="deleteMasterDataModalLabel"
     aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="deleteMasterDataForm"
            action="" {{-- set by JS --}}
            method="POST"
            class="delete-master-data-form">
        @csrf
        @method('DELETE')

        <div class="modal-header">
          <h5 class="modal-title fw-semibold" id="deleteMasterDataModalLabel">
            Delete <span id="mdDeleteLabel">Item</span>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <p class="mb-0">
            Are you sure you want to delete <strong id="mdDeleteWhat">this item</strong>? This action cannot be undone.
          </p>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i data-feather="trash"></i> Delete
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
{{-- ░░░ END: Delete Confirm Modal (Shared) ░░░ --}}
