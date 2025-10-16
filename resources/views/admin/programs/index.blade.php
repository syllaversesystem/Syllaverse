{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/programs/index.blade.php
* Description: Admin Programs Management Page - Standalone programs module
-------------------------------------------------------------------------------
📜 Log:
[2025-10-11] Created as separate module from combined programs-courses view
-------------------------------------------------------------------------------
--}}

@extends('layouts.admin')

@section('title', 'Programs • Admin • Syllaverse')
@section('page-title', 'Programs')

@push('styles')
<style>
/* Empty state styles for programs table */
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

/* Override any red color styling on program names in table */
.sv-accounts-table td:first-child {
  color: var(--sv-text, #333) !important;
  font-weight: 500;
}

/* Suggestion styles */
.position-relative {
  position: relative;
}

.suggestion-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #ddd;
  border-top: none;
  border-radius: 0 0 5px 5px;
  max-height: 200px;
  overflow-y: auto;
  z-index: 1000;
  display: none;
}

.suggestion-item {
  padding: 10px 15px;
  cursor: pointer;
  border-bottom: 1px solid #f0f0f0;
}

.suggestion-item:hover {
  background-color: #f8f9fa;
}

.suggestion-item:last-child {
  border-bottom: none;
}

.suggestion-item .program-name {
  font-weight: 600;
  color: #333;
}

.suggestion-item .program-code {
  color: #666;
  font-size: 0.9em;
}

.suggestion-item .program-status {
  color: #999;
  font-size: 0.8em;
  font-style: italic;
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
<div class="department-card">
  @include('admin.programs.partials.programs-table')
</div>

{{-- Include Program Modals --}}
@include('admin.programs.partials.add-program-modal')
@include('admin.programs.partials.edit-program-modal')
@include('admin.programs.partials.delete-program-modal')

@endsection

@push('scripts')
@vite('resources/js/admin/programs/programs.js')
@endpush