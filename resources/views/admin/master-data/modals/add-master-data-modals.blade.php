{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/master-data/modals/add-master-data-modals.blade.php
* Description: Add modals for SDG / IGA / CDIO – Admin version
-------------------------------------------------------------------------------
📜 Log:
[2025-10-04] Created admin version from superadmin add modals
-------------------------------------------------------------------------------
--}}

{{-- ░░░ START: Add SDG Modal ░░░ --}}
<div class="modal fade"
     id="addSdgModal"
     tabindex="-1"
     aria-labelledby="addSdgModalLabel"
     aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg"><!-- wider -->
    <div class="modal-content">
      <form action="{{ route('admin.master-data.store', 'sdg') }}"
            method="POST"
            class="add-master-data-form">
        @csrf

        <div class="modal-header">
          <h5 class="modal-title fw-semibold" id="addSdgModalLabel">Add Sustainable Development Goal</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small" for="sdgTitle">Title</label>
            <input type="text"
                   id="sdgTitle"
                   name="title"
                   class="form-control form-control-sm"
                   placeholder="e.g., No Poverty">
          </div>

          <div class="mb-2">
            <label class="form-label small" for="sdgDesc">Description <span class="text-danger">*</span></label>
            <textarea id="sdgDesc"
                      name="description"
                      class="form-control"
                      rows="6"
                      style="min-height: 160px"
                      placeholder="Short explanation of the SDG (2–4 sentences)."
                      required></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i data-feather="plus"></i> Add SDG
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
{{-- ░░░ END: Add SDG Modal ░░░ --}}

{{-- ░░░ START: Add IGA Modal ░░░ --}}
<div class="modal fade"
     id="addIgaModal"
     tabindex="-1"
     aria-labelledby="addIgaModalLabel"
     aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg"><!-- wider -->
    <div class="modal-content">
      <form action="{{ route('admin.master-data.store', 'iga') }}"
            method="POST"
            class="add-master-data-form">
        @csrf

        <div class="modal-header">
          <h5 class="modal-title fw-semibold" id="addIgaModalLabel">Add Institutional Graduate Attribute</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small" for="igaTitle">Title</label>
            <input type="text"
                   id="igaTitle"
                   name="title"
                   class="form-control form-control-sm"
                   placeholder="e.g., Critical Thinking">
          </div>

          <div class="mb-2">
            <label class="form-label small" for="igaDesc">Description <span class="text-danger">*</span></label>
            <textarea id="igaDesc"
                      name="description"
                      class="form-control"
                      rows="6"
                      style="min-height: 160px"
                      placeholder="What competency or behavior defines this attribute?"
                      required></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i data-feather="plus"></i> Add IGA
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
{{-- ░░░ END: Add IGA Modal ░░░ --}}

{{-- ░░░ START: Add CDIO Modal ░░░ --}}
<div class="modal fade"
     id="addCdioModal"
     tabindex="-1"
     aria-labelledby="addCdioModalLabel"
     aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg"><!-- wider -->
    <div class="modal-content">
      <form action="{{ route('admin.master-data.store', 'cdio') }}"
            method="POST"
            class="add-master-data-form">
        @csrf

        <div class="modal-header">
          <h5 class="modal-title fw-semibold" id="addCdioModalLabel">Add CDIO Outcome</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small" for="cdioTitle">Title</label>
            <input type="text"
                   id="cdioTitle"
                   name="title"
                   class="form-control form-control-sm"
                   placeholder="e.g., Design">
          </div>

          <div class="mb-2">
            <label class="form-label small" for="cdioDesc">Description <span class="text-danger">*</span></label>
            <textarea id="cdioDesc"
                      name="description"
                      class="form-control"
                      rows="6"
                      style="min-height: 160px"
                      placeholder="Describe the CDIO outcome (scope, intent, typical evidence)."
                      required></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i data-feather="plus"></i> Add CDIO
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
{{-- ░░░ END: Add CDIO Modal ░░░ --}}
