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
  font-size: 14px;
}

.department-filter-wrapper .form-select:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
</style>
@endpush

@section('content')
<div class="container-fluid">

  {{-- ░░░ START: Course Management Section ░░░ --}}
  <div class="sv-section-wrap">
    <div class="sv-section-header">
      <h3 class="sv-section-title">
        <i data-feather="book"></i>
        Course Management
      </h3>
      <p class="sv-section-subtitle">Manage academic courses and their details</p>
    </div>

    <div class="sv-section-body">
      @include('admin.courses.partials.courses-table')
    </div>
  </div>
  {{-- ░░░ END: Course Management Section ░░░ --}}

</div>

{{-- Include Course Modals --}}
@include('admin.courses.partials.add-course-modal')
@include('admin.courses.partials.edit-course-modal')
@include('admin.courses.partials.delete-course-modal')

@endsection

@push('scripts')
<script src="{{ asset('js/admin/master-data/courses.js') }}"></script>
@endpush