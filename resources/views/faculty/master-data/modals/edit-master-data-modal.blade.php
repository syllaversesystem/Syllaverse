{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/master-data/modals/edit-master-data-modal.blade.php
* Description: Shared Edit modal for SDG/IGA/CDIO – prefilled via JS and submitted with AJAX
-------------------------------------------------------------------------------
📜 Log:
[2025-01-20] Copied from admin master-data for Faculty module
-------------------------------------------------------------------------------
--}}

{{-- ░░░ START: Edit Modal (Shared) ░░░ --}}
<div class="modal fade"
     id="editMasterDataModal"
     tabindex="-1"
     aria-labelledby="editMasterDataModalLabel"
     aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg"><!-- widened for comfortable editing -->
    <div class="modal-content">
      <form id="editMasterDataForm"
            action="" {{-- set by JS --}}
            method="POST"
            class="edit-master-data-form">
        @csrf
        @method('PUT')

        <div class="modal-header">
          <h5 class="modal-title fw-semibold" id="editMasterDataModalLabel">
            Edit <span id="mdEditLabel">Item</span>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          {{-- Title stays compact --}}
          <div class="mb-3">
            <label class="form-label small" for="mdEditTitle">Title</label>
            <input type="text" name="title" id="mdEditTitle" class="form-control form-control-sm" value="">
          </div>

          {{-- Description is larger and easier to write/read --}}
          <div class="mb-2">
            <label class="form-label small" for="mdEditDescription">Description <span class="text-danger">*</span></label>
            <textarea
              name="description"
              id="mdEditDescription"
              class="form-control"
              rows="6"
              style="min-height: 160px"
              placeholder="Provide a clear, concise description (2–4 sentences)."
              required
            ></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i data-feather="save"></i> Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
{{-- ░░░ END: Edit Modal (Shared) ░░░ --}}