{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/master-data/index.blade.php
* Description: Admin Master Data Page – aligned with Manage Accounts UI (SO/ILO + Programs/Courses)
-------------------------------------------------------------------------------
📜 Log:
[2025-08-16] Updated tab wiring – fixed ID/target mismatches for SO, ILO, Programs, Courses.
[2025-08-16] Simplified: Bootstrap tab behavior handles switching (no hidden content).
[2025-08-17] FIX: Added Program & Course modal includes at bottom so Add/Edit buttons work.
-------------------------------------------------------------------------------
--}}

@extends('layouts.admin')

@section('title', 'Master Data • Admin • Syllaverse')
@section('page-title', 'Master Data')

@push('styles')
<style>
/* ============================================================================
   SO TOOLBAR STYLES - Link so-toolbar to existing superadmin toolbar styles
   ============================================================================ */
.so-toolbar {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.25rem;
  margin-bottom: 1.5rem;
}

.so-toolbar .input-group {
  flex: 1;
  max-width: 320px;
  background: var(--sv-bg);
  border: 1px solid var(--sv-border);
  border-radius: 6px;
  overflow: hidden;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
}

.so-toolbar .input-group .form-control {
  padding: 0.4rem 0.75rem;
  font-size: 0.88rem;
  font-family: 'Poppins', sans-serif;
  border: none;
  background: transparent;
  color: var(--sv-text);
  height: 2.2rem;
}

.so-toolbar .input-group .form-control::placeholder {
  color: var(--sv-text-muted);
  font-size: 0.87rem;
}

.so-toolbar .input-group .form-control:focus {
  box-shadow: none;
  outline: none;
  background: transparent;
}

.so-toolbar .input-group .input-group-text {
  padding: 0.4rem 0.75rem;
  background: transparent;
  border: none;
  color: var(--sv-text-muted);
}

.so-toolbar .input-group-text i,
.so-toolbar .input-group-text svg {
  width: 1rem;
  height: 1rem;
  stroke: var(--sv-text-muted);
  stroke-width: 2px;
}

.so-toolbar .flex-spacer {
  flex: 1;
}

.so-toolbar .department-filter-wrapper {
  min-width: 160px;
}

.so-toolbar .department-filter-wrapper .form-select {
  padding: 0.4rem 2rem 0.4rem 0.75rem;
  font-size: 0.88rem;
  font-family: 'Poppins', sans-serif;
  border: 1px solid var(--sv-border);
  border-radius: 6px;
  background: var(--sv-bg);
  color: var(--sv-text);
  height: 2.2rem;
}

.so-toolbar .department-filter-wrapper .form-select:focus {
  border-color: var(--sv-primary);
  box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

/* SO Add Button */
.so-add-btn {
  padding: 0;
  width: 2.75rem;
  height: 2.75rem;
  min-width: 2.75rem;
  min-height: 2.75rem;
  border-radius: 50%;
  display: inline-flex;
  justify-content: center;
  align-items: center;
  background: var(--sv-card-bg);
  border: none;
  transition: all 0.2s ease-in-out;
  box-shadow: none;
  color: #000;
}

.so-add-btn i,
.so-add-btn svg {
  width: 1.25rem;
  height: 1.25rem;
  stroke: var(--sv-text);
  stroke-width: 2px;
  transition: stroke 0.2s ease-in-out;
}

.so-add-btn:hover,
.so-add-btn:focus {
  background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46));
  backdrop-filter: blur(7px);
  -webkit-backdrop-filter: blur(7px);
  color: #CB3737;
  box-shadow: 0 4px 12px rgba(203, 55, 55, 0.1);
}

.so-add-btn:hover i,
.so-add-btn:focus i,
.so-add-btn:hover svg,
.so-add-btn:focus svg {
  stroke: #CB3737;
}

.so-add-btn:active {
  background: linear-gradient(135deg, rgba(255, 230, 225, 0.98), rgba(255, 255, 255, 0.62));
  box-shadow: 0 1px 8px rgba(204, 55, 55, 0.16);
  transform: scale(0.95);
}

/* SO Table Wrapper */
.so-table-wrapper .table-responsive {
  border: 1px solid var(--sv-border);
  border-radius: 14px;
  overflow: hidden;
  background: #fff;
  position: relative;
  z-index: 1;
}

.so-table {
  margin-bottom: 0;
  font-family: 'Poppins', sans-serif;
}

.so-table thead th {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  color: var(--sv-text);
  font-weight: 600;
  font-size: 0.875rem;
  padding: 0.875rem 0.75rem;
  border-bottom: 2px solid var(--sv-border);
  white-space: nowrap;
}

.so-table td {
  color: var(--sv-text);
  padding: 0.875rem 0.75rem;
  border-bottom: 1px solid var(--sv-border);
  vertical-align: middle;
  white-space: nowrap;
}

.so-table td:first-child {
  color: var(--sv-text, #333) !important;
  font-weight: 500;
}

.so-table td:last-child {
  text-align: right;
  white-space: nowrap;
}

/* Empty state styles for SO table */
.so-table .so-empty-row td {
  padding: 0;
  background-color: #fff;
  border-radius: 0 0 12px 12px;
  border-top: 1px solid #dee2e6;
  height: 220px;
  text-align: center;
  vertical-align: middle;
}

.so-table .so-empty {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100%;
  max-width: 360px;
  margin: 1.5rem auto 0 auto;
  padding: 0 1rem;
}

.so-table .so-empty h6 {
  font-size: 1rem;
  font-weight: 600;
  color: #CB3737;
  margin-bottom: 0.3rem;
  font-family: 'Poppins', sans-serif;
}

.so-table .so-empty p {
  font-size: 0.85rem;
  color: #777;
  margin-bottom: 0;
}

.so-table .so-empty i {
  width: 16px;
  height: 16px;
  color: #CB3737;
}

/* Responsive Design */
@media (max-width: 768px) {
  .so-toolbar {
    flex-direction: column;
    align-items: stretch;
    gap: 0.75rem;
  }

  .so-toolbar .input-group {
    max-width: 100%;
  }

  .so-toolbar .flex-spacer {
    display: none;
  }

  .so-toolbar .department-filter-wrapper {
    min-width: 100%;
  }
}
</style>
@endpush

@section('content')
<div class="manage-accounts">
  <div class="department-card"><!-- Reuses the polished glass card container -->

    {{-- ░░░ START: Master Data Main Tabs ░░░ --}}
    <ul class="nav sv-tabs" id="masterDataMainTabs" role="tablist" aria-label="Master Data tabs">
      <li class="nav-item" role="presentation">
        <button class="nav-link sv-tab active" id="so-main-tab"
                data-bs-toggle="tab" data-bs-target="#so-main"
                type="button" role="tab" aria-controls="so-main" aria-selected="true">
          SO
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link sv-tab" id="ilo-main-tab"
                data-bs-toggle="tab" data-bs-target="#ilo-main"
                type="button" role="tab" aria-controls="ilo-main" aria-selected="false">
          ILO
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link sv-tab" id="sdg-main-tab"
                data-bs-toggle="tab" data-bs-target="#sdg-main"
                type="button" role="tab" aria-controls="sdg-main" aria-selected="false">
          SDG
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link sv-tab" id="iga-main-tab"
                data-bs-toggle="tab" data-bs-target="#iga-main"
                type="button" role="tab" aria-controls="iga-main" aria-selected="false">
          IGA
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link sv-tab" id="cdio-main-tab"
                data-bs-toggle="tab" data-bs-target="#cdio-main"
                type="button" role="tab" aria-controls="cdio-main" aria-selected="false">
          CDIO
        </button>
      </li>
    </ul>
    {{-- ░░░ END: Master Data Main Tabs ░░░ --}}

    {{-- ░░░ START: Tab Content ░░░ --}}
    <div class="tab-content">

      {{-- ░░░ START: Student Outcomes Section ░░░ --}}
      <div class="tab-pane fade show active" id="so-main" role="tabpanel" aria-labelledby="so-main-tab">
        @include('admin.master-data.tabs.so')
      </div>
      {{-- ░░░ END: Student Outcomes Section ░░░ --}}

      {{-- ░░░ START: Intended Learning Outcomes Section ░░░ --}}
      <div class="tab-pane fade" id="ilo-main" role="tabpanel" aria-labelledby="ilo-main-tab">
        @include('admin.master-data.tabs.ilo')
      </div>
      {{-- ░░░ END: Intended Learning Outcomes Section ░░░ --}}

      {{-- ░░░ START: SDG Section ░░░ --}}
      <div class="tab-pane fade" id="sdg-main" role="tabpanel" aria-labelledby="sdg-main-tab">
        @include('admin.master-data.tabs.sdg')
      </div>
      {{-- ░░░ END: SDG Section ░░░ --}}

      {{-- ░░░ START: IGA Section ░░░ --}}
      <div class="tab-pane fade" id="iga-main" role="tabpanel" aria-labelledby="iga-main-tab">
        @include('admin.master-data.tabs.iga')
      </div>
      {{-- ░░░ END: IGA Section ░░░ --}}

      {{-- ░░░ START: CDIO Section ░░░ --}}
      <div class="tab-pane fade" id="cdio-main" role="tabpanel" aria-labelledby="cdio-main-tab">
        @include('admin.master-data.tabs.cdio')
      </div>
      {{-- ░░░ END: CDIO Section ░░░ --}}

    </div>
    {{-- ░░░ END: Tab Content ░░░ --}}

  </div><!-- END: department-card -->

</div>

{{-- ░░░ START: Modals for SO & ILO ░░░ --}}
@include('admin.master-data.modals.add-so-modal')
@include('admin.master-data.modals.edit-so-modal')
@include('admin.master-data.modals.delete-so-modal')
@include('admin.master-data.modals.add-ilo-modal')
@include('admin.master-data.modals.edit-ilo-modal')
@include('admin.master-data.modals.delete-ilo-modal')
{{-- ░░░ END: Modals for SO & ILO ░░░ --}}

{{-- ░░░ NOTE: Program & Course modals moved to standalone modules ░░░ --}}
{{-- Programs: @see resources/views/admin/programs/index.blade.php --}}
{{-- Courses: @see resources/views/admin/courses/index.blade.php --}}

{{-- ░░░ START: Modals for SDG/IGA/CDIO ░░░ --}}
@include('admin.master-data.modals.add-master-data-modals')
@include('admin.master-data.modals.edit-master-data-modal')
@include('admin.master-data.modals.delete-master-data-modal')
{{-- ░░░ END: Modals for SDG/IGA/CDIO ░░░ --}}

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Initialize feather icons
    if (typeof feather !== 'undefined') {
      feather.replace();
    }
    
    // Tab change handler to refresh icons
    document.querySelectorAll('#masterDataMainTabs button[data-bs-toggle="tab"]').forEach(function (tab) {
      tab.addEventListener('shown.bs.tab', function (event) {
        if (typeof feather !== 'undefined') {
          feather.replace();
        }
      });
    });
    
    // Fix modal backdrop cleanup issue
    const modals = document.querySelectorAll('.modal');
    
    modals.forEach(function(modal) {
        modal.addEventListener('hidden.bs.modal', function () {
            // Remove any lingering backdrops
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(function(backdrop) {
                backdrop.remove();
            });
            
            // Remove modal-open class from body
            document.body.classList.remove('modal-open');
            
            // Reset body styling
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
        
        // Additional cleanup on modal show
        modal.addEventListener('show.bs.modal', function () {
            // Clean up any existing backdrops before showing new modal
            const existingBackdrops = document.querySelectorAll('.modal-backdrop');
            existingBackdrops.forEach(function(backdrop) {
                backdrop.remove();
            });
        });
    });
    
    // Global backdrop cleanup on any click outside modal
    document.addEventListener('click', function(event) {
        // If clicking outside any visible modal, clean up backdrops
        const visibleModals = document.querySelectorAll('.modal.show');
        if (visibleModals.length === 0) {
            const backdrops = document.querySelectorAll('.modal-backdrop');
            if (backdrops.length > 0) {
                backdrops.forEach(function(backdrop) {
                    backdrop.remove();
                });
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }
        }
    });
    
    // Escape key cleanup
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            setTimeout(function() {
                const visibleModals = document.querySelectorAll('.modal.show');
                if (visibleModals.length === 0) {
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(function(backdrop) {
                        backdrop.remove();
                    });
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }
            }, 300); // Small delay to let Bootstrap finish its cleanup
        }
    });
  });

  // Modal setup functions for SDG/IGA/CDIO - only handle data setup, Bootstrap handles showing
  function setupAddModal(type, label) {
    // This function is called before the modal opens to prepare data if needed
    // For add modals, no setup needed as they're blank forms
    console.log(`Setting up add modal for ${type}`);
  }

  function setupEditModal(type, id, description) {
    // Set form data for edit modal
    try {
      document.getElementById('editMasterDataModalLabel').textContent = `Edit ${type.toUpperCase()}`;
      document.getElementById('editMasterDataForm').action = `/admin/master-data/${type}/${id}`;
      document.getElementById('mdEditDescription').value = description;
      console.log(`Edit modal setup complete for ${type} ID ${id}`);
    } catch (error) {
      console.error('Error setting up edit modal:', error);
    }
  }

  function setupDeleteModal(type, id, description) {
    // Set form data for delete modal
    try {
      document.getElementById('deleteMasterDataModalLabel').textContent = `Delete ${type.toUpperCase()}`;
      document.getElementById('deleteMasterDataForm').action = `/admin/master-data/${type}/${id}`;
      document.getElementById('mdDeleteWhat').textContent = description;
      console.log(`Delete modal setup complete for ${type} ID ${id}`);
    } catch (error) {
      console.error('Error setting up delete modal:', error);
    }
  }
</script>
@vite('resources/js/admin/master-data/so.js')
@endpush

@endsection
