{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/courses/index.blade.php
* Description: Faculty Courses Management Page - Faculty module adaptation
-------------------------------------------------------------------------------
📜 Log:
[2025-01-XX] Adapted from admin courses module for faculty use
[2025-01-XX] Updated styling classes to use faculty prefixes
[2025-01-XX] Added role-based permissions and department restrictions
-------------------------------------------------------------------------------
--}}

@extends('layouts.faculty')

@section('title', 'Courses • Faculty • Syllaverse')
@section('page-title', 'Courses')

@push('styles')
<style>
/* Link new courses-specific classes to existing UI styles */

/* ============================================================================
   COURSES MANAGEMENT CARD - Link to department card styling
   ============================================================================ */
.courses-management-card {
  position: relative;
  background: rgba(255, 255, 255, 0.65);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border-radius: 0.75rem;
  padding: clamp(1.25rem, 3vw, 2rem);
  border: 1px solid rgba(200, 200, 200, 0.35);
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
  font-family: 'Poppins', sans-serif;
  animation: fadeInCard 0.5s ease-in-out both;
}

@keyframes fadeInCard {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* ============================================================================
   TOOLBAR STYLES - Link courses-toolbar to existing superadmin toolbar styles
   ============================================================================ */
.courses-toolbar {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.25rem;
  margin-bottom: 1.5rem;
}

.courses-toolbar .input-group {
  flex: 1;
  max-width: 320px;
  background: var(--sv-bg);
  border: 1px solid var(--sv-border);
  border-radius: 6px;
  overflow: hidden;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
}

.courses-toolbar .input-group .form-control {
  padding: 0.4rem 0.75rem;
  font-size: 0.88rem;
  font-family: 'Poppins', sans-serif;
  border: none;
  background: transparent;
  color: var(--sv-text);
  height: 2.2rem;
}

.courses-toolbar .input-group .form-control::placeholder {
  color: var(--sv-text-muted);
  font-size: 0.87rem;
}

.courses-toolbar .input-group .form-control:focus {
  outline: none;
  box-shadow: none;
  background: transparent;
}

.courses-toolbar .input-group .input-group-text {
  background: transparent;
  border: none;
  padding-left: 0.7rem;
  padding-right: 0.4rem;
  display: flex;
  align-items: center;
}

.courses-toolbar .input-group-text i,
.courses-toolbar .input-group-text svg {
  stroke: var(--sv-text-muted);
  width: 0.95rem;
  height: 0.95rem;
}

/* ============================================================================
   TABLE STYLES - Link courses-table to existing sv-accounts-table styles (NO HOVER)
   ============================================================================ */
.courses-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 0.85rem;
  font-family: 'Poppins', sans-serif;
  background: #fff;
}

.courses-table thead {
  background: var(--sv-bg);
}

.courses-table thead th {
  font-weight: 600;
  color: var(--sv-text-muted);
  padding: 0.65rem 0.9rem;
  text-align: left;
  vertical-align: middle;
  background-color: #fff;
  white-space: nowrap;
}

.courses-table thead th i,
.courses-table thead th svg {
  margin-right: 0.45rem;
  stroke: var(--sv-text-muted);
  width: 1rem;
  height: 1rem;
  vertical-align: text-bottom;
}

.courses-table tbody tr {
  background-color: #fff;
}

.courses-table td {
  color: var(--sv-text);
  padding: 0.01rem 0.75rem;
  border-bottom: 0px solid var(--sv-border);
  vertical-align: middle;
  white-space: nowrap;
}

.courses-table td:first-child {
  color: var(--sv-text, #333) !important;
  font-weight: 500;
}

.courses-table td:last-child {
  text-align: right;
  white-space: nowrap;
}

/* ============================================================================
   BUTTON STYLES - Link courses action buttons to existing action-btn styles (FACULTY GREEN)
   ============================================================================ */
.courses-add-btn {
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

.courses-add-btn i,
.courses-add-btn svg {
  width: 1.25rem;
  height: 1.25rem;
  stroke: var(--sv-text);
  stroke-width: 2px;
  transition: stroke 0.2s ease-in-out;
}

.courses-add-btn:hover,
.courses-add-btn:focus {
  background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46));
  backdrop-filter: blur(7px);
  -webkit-backdrop-filter: blur(7px);
  box-shadow: 0 4px 10px rgba(204, 55, 55, 0.12);
  color: #CB3737;
}

.courses-add-btn:hover i,
.courses-add-btn:hover svg,
.courses-add-btn:focus i,
.courses-add-btn:focus svg {
  stroke: #CB3737;
}

.courses-action-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.75rem;
  height: 2.75rem;
  background: var(--sv-card-bg);
  border: none;
  border-radius: 50%;
  transition: background 0.23s, color 0.23s, box-shadow 0.23s, filter 0.23s;
  cursor: pointer;
}

.courses-action-btn i,
.courses-action-btn svg {
  stroke-width: 2px;
  width: 1.05rem;
  height: 1.05rem;
  stroke: var(--sv-text);
  transition: stroke 0.2s, color 0.2s;
}

.courses-action-btn.edit-btn:hover,
.courses-action-btn.edit-btn:focus {
  background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46));
  backdrop-filter: blur(7px);
  -webkit-backdrop-filter: blur(7px);
  color: #CB3737;
  box-shadow: 0 4px 10px rgba(204, 55, 55, 0.12);
}

.courses-action-btn.edit-btn:hover i,
.courses-action-btn.edit-btn:focus i,
.courses-action-btn.edit-btn:hover svg,
.courses-action-btn.edit-btn:focus svg {
  stroke: #CB3737;
}

.courses-action-btn.edit-btn:active {
  background: linear-gradient(135deg, rgba(255, 230, 225, 0.98), rgba(255, 255, 255, 0.62));
  box-shadow: 0 1px 8px rgba(204, 55, 55, 0.16);
}

.courses-action-btn.delete-btn:hover,
.courses-action-btn.delete-btn:focus {
  background: linear-gradient(135deg, rgba(255, 220, 220, 0.92), rgba(255, 255, 255, 0.5));
  backdrop-filter: blur(7px);
  -webkit-backdrop-filter: blur(7px);
  color: #CB3737;
  box-shadow: 0 4px 12px rgba(203, 55, 55, 0.13);
}

.courses-action-btn.delete-btn:hover i,
.courses-action-btn.delete-btn:focus i,
.courses-action-btn.delete-btn:hover svg,
.courses-action-btn.delete-btn:focus svg {
  stroke: #CB3737;
}

.courses-action-btn.delete-btn:active {
  background: linear-gradient(135deg, rgba(255, 230, 230, 0.99), rgba(255, 255, 255, 0.62));
  box-shadow: 0 1px 9px rgba(203, 55, 55, 0.15);
}

.courses-action-btn.disabled-btn {
  opacity: 0.5;
  cursor: not-allowed;
}

.courses-action-btn.disabled-btn:hover,
.courses-action-btn.disabled-btn:focus {
  background: var(--sv-card-bg);
  box-shadow: none;
}

.courses-action-btn:active {
  transform: scale(0.95);
  filter: brightness(0.98);
}

/* ============================================================================
   TABLE WRAPPER STYLES - Link courses-table-wrapper to existing table containers
   ============================================================================ */
.courses-table-wrapper .table-responsive {
  border: 1px solid var(--sv-border);
  border-radius: 14px;
  overflow: hidden;
  background: #fff;
  position: relative;
  z-index: 1;
}

/* Empty state styles for courses table */
.courses-table .courses-empty-row td {
  padding: 0;
  background-color: #fff;
  border-radius: 0 0 12px 12px;
  border-top: 1px solid #dee2e6;
  height: 220px;
  text-align: center;
  vertical-align: middle;
}

.courses-table .courses-empty {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100%;
  max-width: 360px;
  margin: 1.5rem auto 0 auto;
  padding: 0 1rem;
}

.courses-table .courses-empty h6 {
  font-size: 1rem;
  font-weight: 600;
  color: #CB3737;
  margin-bottom: 0.3rem;
  font-family: 'Poppins', sans-serif;
}

.courses-table .courses-empty p {
  font-size: 0.85rem;
  color: #777;
  margin-bottom: 0;
}

.courses-table .courses-empty i {
  width: 16px;
  height: 16px;
  color: #CB3737;
}

/* ============================================================================
   MODAL STYLES - Link sv-course-modal to existing modal styles  
   ============================================================================ */
.sv-course-modal .modal-header {
  border-bottom: 1px solid var(--sv-bdr);
  background: var(--sv-bg);
}

.sv-course-modal .course-form {
  --sv-bg: #FAFAFA;
  --sv-bdr: #E3E3E3;
  --sv-acct: #EE6F57;
  --sv-danger: #CB3737;
}

.sv-course-modal .form-control,
.sv-course-modal .form-select {
  border-color: var(--sv-bdr);
}

.sv-course-modal .form-control:focus,
.sv-course-modal .form-select:focus {
  border-color: var(--sv-acct);
  box-shadow: 0 0 0 .2rem rgb(238 111 87 / 15%);
}

/* ============================================================================
   FORM FIELD STYLES - Link course-field-group to consistent form styling
   ============================================================================ */
.course-field-group {
  margin-bottom: 1rem;
}

.course-field-group .form-label {
  font-weight: 500;
  color: var(--sv-text-muted);
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.course-field-group .form-control,
.course-field-group .form-select {
  border: 1px solid var(--sv-bdr, #E3E3E3);
  border-radius: 0.375rem;
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.course-field-group .form-control:focus,
.course-field-group .form-select:focus {
  border-color: var(--sv-acct, #EE6F57);
  box-shadow: 0 0 0 0.2rem rgba(238, 111, 87, 0.15);
  outline: 0;
}

.course-field-group .suggestions-dropdown {
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

/* Loading state: make the department filter glow red during AJAX */
.department-filter-wrapper .form-select.is-loading {
  border-color: #CB3737 !important;
  box-shadow: 0 0 0 0.2rem rgba(203, 55, 55, 0.18) !important;
  transition: border-color .2s ease, box-shadow .2s ease, transform .12s ease;
}

/* ============================================================================
   RESPONSIVE DESIGN - Maintain mobile responsiveness
   ============================================================================ */
@media (max-width: 768px) {
  .courses-management-card {
    padding: clamp(1rem, 2.5vw, 1.5rem);
    border-radius: 0.5rem;
    margin: 0.5rem;
  }
  
  .courses-toolbar {
    gap: 0.5rem;
  }
  
  .courses-toolbar .input-group {
    max-width: 100%;
  }
  
  .courses-table thead th {
    padding: 0.5rem 0.6rem;
    font-size: 0.8rem;
  }
  
  .courses-table td {
    padding: 0.01rem 0.6rem;
    font-size: 0.8rem;
  }
}

@media (max-width: 480px) {
  .courses-management-card {
    padding: 1rem;
    margin: 0.25rem;
  }
}
</style>
@endpush

@section('content')
{{-- ░░░ START: Courses Management Card ░░░ --}}
<div class="courses-management-card">
  {{-- ░░░ START: Single Course Tab ░░░ --}}
  <div class="tab-content" id="courseTabContent">
    {{-- ░░░ START: Courses Section ░░░ --}}
    <div class="tab-pane fade show active" id="courses-main" role="tabpanel" aria-labelledby="courses-main-tab">
      @include('faculty.courses.partials.courses-table')
    </div>
    {{-- ░░░ END: Courses Section ░░░ --}}
  </div>
  {{-- ░░░ END: Tab Content ░░░ --}}

</div><!-- END: courses-management-card -->

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
  }
</style>
{{-- ░░░ END: Modal Fix CSS ░░░ --}}

{{-- ░░░ START: Modals for Faculty Courses ░░░ --}}
@include('faculty.courses.partials.add-course-modal')
@include('faculty.courses.partials.edit-course-modal')
@include('faculty.courses.partials.delete-course-modal')
{{-- ░░░ END: Modals for Faculty Courses ░░░ --}}

@endsection

{{-- JavaScript --}}
@push('scripts')
@vite('resources/js/faculty/courses/courses.js')
@endpush