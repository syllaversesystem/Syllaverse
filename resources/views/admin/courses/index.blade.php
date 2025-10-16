{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/courses/index.blade.php
* Description: Admin Courses Management Page - Standalone courses module
-------------------------------------------------------------------------------
📜 Log:
[2025-10-11] Created as separate module from combined programs-courses view
-------------------------------------------------------------------------------
--}}

@extends('layouts.admin')

@section('title', 'Courses • Admin • Syllaverse')
@section('page-title', 'Courses')

@push('styles')
<style>
/* Empty state styles for courses table */
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
  margin-bottom: 0;
}

.sv-accounts-table .sv-empty i {
  width: 16px;
  height: 16px;
  color: #CB3737;
}

/* Department filter styles */
.department-filter-wrapper {
  margin-left: 10px;
  margin-right: 10px;
}

.department-filter-wrapper .form-select {
  min-width: 200px;
  border: 1px solid #ddd;
  border-radius: 5px;
  padding: 5px 10px;
}

/* Override any red coloring for the first column (title) */
#svCoursesTable tbody tr td:first-child {
  color: #333 !important;
}
  font-size: 14px;
}

.department-filter-wrapper .form-select:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
</style>
@endpush

@section('content')
{{-- ░░░ START: Department Card (Original Style) ░░░ --}}
<div class="department-card">
  {{-- ░░░ START: Single Course Tab ░░░ --}}
  <div class="tab-content" id="courseTabContent">
    {{-- ░░░ START: Courses Section ░░░ --}}
    <div class="tab-pane fade show active" id="courses-main" role="tabpanel" aria-labelledby="courses-main-tab">
      @include('admin.courses.partials.courses-table')
    </div>
    {{-- ░░░ END: Courses Section ░░░ --}}
  </div>
  {{-- ░░░ END: Tab Content ░░░ --}}

</div><!-- END: department-card -->

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

{{-- ░░░ START: Modals for Courses ░░░ --}}
@include('admin.courses.partials.add-course-modal')
@include('admin.courses.partials.edit-course-modal')
@include('admin.courses.partials.delete-course-modal')
{{-- ░░░ END: Modals for Courses ░░░ --}}

@endsection

{{-- JavaScript --}}
@push('scripts')
@vite('resources/js/admin/courses/courses.js')
@endpush