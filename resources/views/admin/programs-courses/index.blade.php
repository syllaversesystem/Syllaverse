{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/programs-courses/index.blade.php
* Description: Admin Programs & Courses Page – dedicated interface with two main tabs
-------------------------------------------------------------------------------
📜 Log:
[2025-10-04] Created dedicated Programs & Courses page with Programs and Courses as main tabs
-------------------------------------------------------------------------------
--}}

@extends('layouts.admin')

@section('title', 'Programs & Courses • Admin • Syllaverse')
@section('page-title', 'Programs & Courses')

@push('styles')
<style>
/* Empty state styles for programs/courses tables */
.sv-accounts-table .sv-empty-row td {
  padding: 0;
  background-color: #fff;
  border-radius: 0 0 12px 12px;
  border-top: 1px solid #dee2e6;
  height: 220px;
  text-align: center;
  vertical-align: middle;
}

.sv-accounts-table .sv-empty {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100%;
  max-width: 360px;
  margin: 1.5rem auto 0 auto;
  padding: 0 1rem;
}

.sv-accounts-table .sv-empty h6 {
  font-size: 1rem;
  font-weight: 600;
  color: #CB3737;
  margin-bottom: 0.3rem;
  font-family: 'Poppins', sans-serif;
}

.sv-accounts-table .sv-empty p {
  font-size: 0.85rem;
  color: #777;
  line-height: 1.4;
  margin-bottom: 0;
}

/* Override any red color in program names - use normal text color */
#svProgramsTable td:first-child {
  color: #333333 !important;
  font-weight: 500;
}

/* Ensure normal text color for all table cells */
#svProgramsTable tbody td {
  color: #333333;
}
</style>
@endpush

@section('content')
<div class="manage-accounts">
  <div class="department-card"><!-- Reuses the polished glass card container -->

    {{-- ░░░ START: Main Tabs (Programs vs Courses) ░░░ --}}
    <ul class="nav sv-tabs" id="programsCoursesMainTabs" role="tablist" aria-label="Programs and Courses tabs">
      <li class="nav-item" role="presentation">
        <button class="nav-link sv-tab active" id="programs-main-tab"
                data-bs-toggle="tab" data-bs-target="#programs-main"
                type="button" role="tab" aria-controls="programs-main" aria-selected="true">
          Programs
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link sv-tab" id="courses-main-tab"
                data-bs-toggle="tab" data-bs-target="#courses-main"
                type="button" role="tab" aria-controls="courses-main" aria-selected="false">
          Courses
        </button>
      </li>
    </ul>
    {{-- ░░░ END: Main Tabs ░░░ --}}

    {{-- ░░░ START: Tab Content ░░░ --}}
    <div class="tab-content">

      {{-- ░░░ START: Programs Section ░░░ --}}
      <div class="tab-pane fade show active" id="programs-main" role="tabpanel" aria-labelledby="programs-main-tab">
        @include('admin.master-data.tabs.programs-tab')
      </div>
      {{-- ░░░ END: Programs Section ░░░ --}}

      {{-- ░░░ START: Courses Section ░░░ --}}
      <div class="tab-pane fade" id="courses-main" role="tabpanel" aria-labelledby="courses-main-tab">
        @include('admin.master-data.tabs.courses-tab')
      </div>
      {{-- ░░░ END: Courses Section ░░░ --}}

    </div>
    {{-- ░░░ END: Tab Content ░░░ --}}

  </div><!-- END: department-card -->

</div>

{{-- ░░░ START: Modal Fix CSS ░░░ --}}
<style>
  /* Ensure modals appear properly and clean up correctly */
  .modal {
    z-index: 1055 !important;
  }
  .modal-backdrop {
    z-index: 1050 !important;
    transition: opacity 0.15s linear;
  }
  .modal.show .modal-dialog {
    z-index: 1056 !important;
  }
  
  /* Ensure body cleanup when modal is closed */
  body:not(.modal-open) {
    overflow: visible !important;
    padding-right: 0 !important;
  }
  
  /* Allow Bootstrap to handle backdrop visibility naturally */
  .modal-backdrop.show {
    opacity: 0.5;
  }
  .modal-backdrop:not(.show) {
    opacity: 0;
    /* Removed display: none !important to let Bootstrap control visibility */
  }
</style>
{{-- ░░░ END: Modal Fix CSS ░░░ --}}

{{-- ░░░ START: Modals for Programs & Courses ░░░ --}}
@include('admin.master-data.modals.add-program-modal')
@include('admin.master-data.modals.edit-program-modal')
@include('admin.master-data.modals.add-course-modal')
@include('admin.master-data.modals.edit-course-modal')
@include('admin.master-data.modals.delete-program-modal')
{{-- ░░░ END: Modals for Programs & Courses ░░░ --}}

{{-- JavaScript --}}
@push('scripts')
@vite('resources/js/admin/master-data/programs.js')
@vite('resources/js/admin/master-data/courses.js')

<script>
// Inline empty state function to ensure it's available
function showEmptyStateInline() {
  const tbody = document.querySelector('#svProgramsTable tbody');
  if (!tbody) {
    console.error('Could not find programs table tbody');
    return;
  }
  
  console.log('Showing empty state for programs table (inline version)');
  
  tbody.innerHTML = `
    <tr class="sv-empty-row">
      <td colspan="3">
        <div class="sv-empty">
          <h6>No programs found</h6>
          <p>Click the <i data-feather="plus"></i> button to add one.</p>
        </div>
      </td>
    </tr>
  `;
  
  // Re-initialize feather icons
  if (typeof feather !== 'undefined') {
    setTimeout(() => feather.replace(), 100);
  }
}

// Make it globally available for testing
window.testInlineEmptyState = showEmptyStateInline;

// Override the external showEmptyState if it doesn't exist
if (typeof window.showEmptyState === 'undefined') {
  window.showEmptyState = showEmptyStateInline;
}

// Add event listener for program deletion to ensure empty state is shown
document.addEventListener('DOMContentLoaded', function() {
  // Listen for successful program deletions
  document.addEventListener('submit', async function(e) {
    if (e.target?.id !== 'deleteProgramForm') return;
    
    // Wait for the external deletion handler to complete, then check empty state
    setTimeout(() => {
      const tbody = document.querySelector('#svProgramsTable tbody');
      const dataRows = tbody?.querySelectorAll('tr:not(.sv-empty-row)');
      
      console.log('Inline check - tbody:', tbody);
      console.log('Inline check - data rows:', dataRows?.length);
      
      if (tbody && dataRows && dataRows.length === 0) {
        console.log('Inline check - showing empty state');
        showEmptyStateInline();
      }
    }, 500); // Wait a bit longer to ensure external handler completes
  });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
        
        // Let Bootstrap handle backdrop creation naturally - no interference on show
    });
    
    // Global backdrop cleanup on any click outside modal - DISABLED to prevent interference
    // document.addEventListener('click', function(event) {
    //     // If clicking outside any visible modal, clean up backdrops
    //     const visibleModals = document.querySelectorAll('.modal.show');
    //     if (visibleModals.length === 0) {
    //         const backdrops = document.querySelectorAll('.modal-backdrop');
    //         if (backdrops.length > 0) {
    //             backdrops.forEach(function(backdrop) {
    //                 backdrop.remove();
    //             });
    //             document.body.classList.remove('modal-open');
    //             document.body.style.overflow = '';
    //             document.body.style.paddingRight = '';
    //         }
    //     }
    // });
    
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
</script>
@endpush

@endsection