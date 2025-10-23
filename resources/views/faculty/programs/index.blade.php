{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/programs/index.blade.php
* Description: Faculty Programs Management Page - Standalone programs module
-------------------------------------------------------------------------------
📜 Log:
[2025-01-16] Created faculty version based on admin programs management
-------------------------------------------------------------------------------
--}}

@extends('layouts.faculty')

@section('title', 'Programs • Faculty • Syllaverse')
@section('page-title', 'Programs')

@push('styles')
<style>
/* Link new program-specific classes to existing UI styles */

/* ============================================================================
   FORCE FEATHER ICONS TO BE VISIBLE - Global override
   ============================================================================ */
[data-feather] {
  display: inline-block !important;
  vertical-align: middle !important;
  stroke: currentColor !important;
  stroke-width: 2 !important;
  stroke-linecap: round !important;
  stroke-linejoin: round !important;
  fill: none !important;
}

/* Ensure SVG icons render correctly */
svg[data-feather] {
  display: inline-block !important;
}

/* Force visibility on any Feather icon */
i[data-feather], svg[data-feather] {
  opacity: 1 !important;
  visibility: visible !important;
}

/* ============================================================================
   PROGRAMS MANAGEMENT CARD - Link to department card styling
   ============================================================================ */
.programs-management-card {
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
   TOOLBAR STYLES - Link programs-toolbar to existing superadmin toolbar styles
   ============================================================================ */
.programs-toolbar {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.25rem;
  margin-bottom: 1.5rem;
}

.programs-toolbar .input-group {
  flex: 1;
  max-width: 320px;
  background: var(--sv-bg);
  border: 1px solid var(--sv-border);
  border-radius: 6px;
  overflow: hidden;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
}

.programs-toolbar .input-group .form-control {
  padding: 0.4rem 0.75rem;
  font-size: 0.88rem;
  font-family: 'Poppins', sans-serif;
  border: none;
  background: transparent;
  color: var(--sv-text);
  height: 2.2rem;
}

.programs-toolbar .input-group .form-control::placeholder {
  color: var(--sv-text-muted);
  font-size: 0.87rem;
}

.programs-toolbar .input-group .form-control:focus {
  outline: none;
  box-shadow: none;
  background: transparent;
}

.programs-toolbar .input-group .input-group-text {
  background: transparent;
  border: none;
  padding-left: 0.7rem;
  padding-right: 0.4rem;
  display: flex;
  align-items: center;
}

.programs-toolbar .input-group-text i,
.programs-toolbar .input-group-text svg {
  stroke: var(--sv-text-muted, #666) !important;
  color: var(--sv-text-muted, #666) !important;
  width: 0.95rem !important;
  height: 0.95rem !important;
  display: inline-block !important;
}

/* ============================================================================
   TABLE STYLES - Link programs-table to existing sv-accounts-table styles
   ============================================================================ */
.programs-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 0.85rem;
  font-family: 'Poppins', sans-serif;
  background: #fff;
}

.programs-table thead {
  background: var(--sv-bg);
}

.programs-table thead th {
  font-weight: 600;
  color: var(--sv-text-muted);
  padding: 0.65rem 0.9rem;
  text-align: left;
  vertical-align: middle;
  background-color: #fff;
  white-space: nowrap;
}

.programs-table thead th i,
.programs-table thead th svg {
  margin-right: 0.45rem;
  stroke: var(--sv-text-muted, #666) !important;
  color: var(--sv-text-muted, #666) !important;
  width: 1rem !important;
  height: 1rem !important;
  display: inline-block !important;
  vertical-align: text-bottom;
}

.programs-table tbody tr {
  transition: background-color 0.2s ease;
  background-color: #fff;
}

.programs-table tbody tr:hover {
  background-color: #fff5f4;
  border-left: 4px solid var(--sv-primary);
}

/* Loading spinner animation for AJAX operations */
.spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Disabled button states */
.btn:disabled {
  opacity: 0.6 !important;
  cursor: not-allowed !important;
}

/* Row transition animations */
.programs-table tbody tr {
  transition: all 0.3s ease;
}

.programs-table td {
  color: var(--sv-text);
  padding: 0.01rem 0.75rem;
  border-bottom: 0px solid var(--sv-border);
  vertical-align: middle;
  white-space: nowrap;
}

.programs-table td:first-child {
  color: var(--sv-text, #333) !important;
  font-weight: 500;
}

.programs-table td:last-child {
  text-align: right;
  white-space: nowrap;
}

/* ============================================================================
   BUTTON STYLES - Link program action buttons to existing action-btn styles
   ============================================================================ */
.programs-add-btn {
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

.programs-add-btn i,
.programs-add-btn svg {
  width: 1.25rem;
  height: 1.25rem;
  stroke: var(--sv-text);
  stroke-width: 2px;
  transition: stroke 0.2s ease-in-out;
}

.programs-add-btn:hover,
.programs-add-btn:focus {
  background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46));
  backdrop-filter: blur(7px);
  -webkit-backdrop-filter: blur(7px);
  box-shadow: 0 4px 10px rgba(204, 55, 55, 0.12);
  color: #CB3737;
}

.programs-add-btn:hover i,
.programs-add-btn:hover svg,
.programs-add-btn:focus i,
.programs-add-btn:focus svg {
  stroke: #CB3737;
}

.programs-action-btn {
  padding: 0;
  width: 2.75rem;
  height: 2.75rem;
  min-width: 2.75rem;
  min-height: 2.75rem;
  border-radius: 50%;
  display: inline-flex;
  justify-content: center;
  align-items: center;
  background: var(--sv-card-bg, #f8f9fa);
  border: none !important;
  outline: none !important;
  transition: all 0.2s ease-in-out;
  box-shadow: none;
  color: #000;
  cursor: pointer;
}

.programs-action-btn i,
.programs-action-btn svg {
  width: 1.05rem;
  height: 1.05rem;
  stroke: var(--sv-text, #333);
  stroke-width: 2px;
  transition: stroke 0.2s ease-in-out;
}

.programs-action-btn.edit-btn:hover,
.programs-action-btn.edit-btn:focus {
  background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46));
  backdrop-filter: blur(7px);
  -webkit-backdrop-filter: blur(7px);
  box-shadow: 0 4px 10px rgba(204, 55, 55, 0.12);
  color: #CB3737;
}

.programs-action-btn.edit-btn:hover i,
.programs-action-btn.edit-btn:focus i,
.programs-action-btn.edit-btn:hover svg,
.programs-action-btn.edit-btn:focus svg {
  stroke: #CB3737;
}

.programs-action-btn.edit-btn:active {
  background: linear-gradient(135deg, rgba(255, 230, 225, 0.98), rgba(255, 255, 255, 0.62));
  box-shadow: 0 1px 8px rgba(204, 55, 55, 0.16);
}

.programs-action-btn.delete-btn:hover,
.programs-action-btn.delete-btn:focus {
  background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46));
  backdrop-filter: blur(7px);
  -webkit-backdrop-filter: blur(7px);
  box-shadow: 0 4px 10px rgba(204, 55, 55, 0.12);
  color: #CB3737;
}

.programs-action-btn.delete-btn:hover i,
.programs-action-btn.delete-btn:focus i,
.programs-action-btn.delete-btn:hover svg,
.programs-action-btn.delete-btn:focus svg {
  stroke: #CB3737;
}

.programs-action-btn.delete-btn:active {
  background: linear-gradient(135deg, rgba(255, 230, 225, 0.98), rgba(255, 255, 255, 0.62));
  box-shadow: 0 1px 8px rgba(204, 55, 55, 0.16);
}

.programs-action-btn:active {
  transform: scale(0.95);
  filter: brightness(0.98);
}

/* ============================================================================
   TABLE WRAPPER STYLES - Link programs-table-wrapper to existing table containers
   ============================================================================ */
.programs-table-wrapper .table-responsive {
  border: 1px solid var(--sv-border, #dee2e6) !important;
  border-radius: 14px;
  overflow: hidden;
  background: #fff;
  position: relative;
  z-index: 1;
}

/* Empty state styles for programs table */
.programs-table .programs-empty-row td {
  padding: 0;
  background-color: #fff;
  border-radius: 0 0 12px 12px;
  border-top: 1px solid #dee2e6;
  height: 220px;
  text-align: center;
  vertical-align: middle;
}

.programs-table .programs-empty {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100%;
  max-width: 360px;
  margin: 1.5rem auto 0 auto;
  padding: 0 1rem;
}

.programs-table .programs-empty h6 {
  font-size: 1rem;
  font-weight: 600;
  color: #CB3737;
  margin-bottom: 0.3rem;
  font-family: 'Poppins', sans-serif;
}

.programs-table .programs-empty p {
  font-size: 0.85rem;
  color: #777;
  margin-bottom: 0;
}

.programs-table .programs-empty i {
  width: 16px;
  height: 16px;
  color: #CB3737;
}

/* ============================================================================
   MODAL STYLES - Link sv-program-modal to existing modal styles  
   ============================================================================ */
.sv-program-modal .modal-header {
  border-bottom: 1px solid var(--sv-bdr);
  background: var(--sv-bg);
}

.sv-program-modal .program-form {
  --sv-bg: #FAFAFA;
  --sv-bdr: #E3E3E3;
  --sv-acct: #EE6F57;
  --sv-danger: #CB3737;
}

.sv-program-modal .form-control,
.sv-program-modal .form-select {
  border-color: var(--sv-bdr);
}

.sv-program-modal .form-control:focus,
.sv-program-modal .form-select:focus {
  border-color: var(--sv-bdr);
  box-shadow: none;
  outline: none;
}

/* Remove browser default yellow/orange focus effects from textareas */
.sv-program-modal textarea.form-control:focus {
  border-color: var(--sv-bdr);
  box-shadow: none;
  outline: none;
  background-color: #fff;
}

/* ============================================================================
   FORM FIELD STYLES - Link program-field-group to consistent form styling
   ============================================================================ */
.program-field-group {
  margin-bottom: 1rem;
}

.program-field-group .form-label {
  font-weight: 500;
  color: var(--sv-text-muted);
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.program-field-group .form-control,
.program-field-group .form-select {
  border: 1px solid var(--sv-bdr, #E3E3E3);
  border-radius: 0.375rem;
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.program-field-group .form-control:focus,
.program-field-group .form-select:focus {
  border-color: var(--sv-bdr, #E3E3E3);
  box-shadow: none;
  outline: none;
}

/* Remove browser default yellow/orange focus effects from textareas */
.program-field-group textarea.form-control:focus {
  border-color: var(--sv-bdr, #E3E3E3);
  box-shadow: none;
  outline: none;
  background-color: #fff;
}

.program-field-group .suggestions-dropdown {
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

/* ============================================================================
   RESPONSIVE DESIGN - Maintain mobile responsiveness
   ============================================================================ */
@media (max-width: 768px) {
  .programs-management-card {
    padding: clamp(1rem, 2.5vw, 1.5rem);
    border-radius: 0.5rem;
    margin: 0.5rem;
  }
  
  .programs-toolbar {
    gap: 0.5rem;
  }
  
  .programs-toolbar .input-group {
    max-width: 100%;
  }
  
  .programs-table thead th {
    padding: 0.5rem 0.6rem;
    font-size: 0.8rem;
  }
  
  .programs-table td {
    padding: 0.01rem 0.6rem;
    font-size: 0.8rem;
  }
}

@media (max-width: 480px) {
  .programs-management-card {
    padding: 1rem;
    margin: 0.25rem;
  }
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
{{-- ░░░ START: Programs Management Card ░░░ --}}
<div class="programs-management-card">
  @include('faculty.programs.partials.programs-table')
</div>
{{-- ░░░ END: Programs Management Card ░░░ --}}

{{-- Include Program Modals --}}
@include('faculty.programs.partials.add-program-modal')
@include('faculty.programs.partials.edit-program-modal')
@include('faculty.programs.partials.delete-program-modal')

@endsection

@push('scripts')
@vite('resources/js/faculty/programs/programs.js')

<script>
// Pass role-based configuration to JavaScript
window.programsConfig = {
    showDepartmentColumn: {{ $showDepartmentColumn ? 'true' : 'false' }},
    showAddDepartmentDropdown: {{ $showAddDepartmentDropdown ? 'true' : 'false' }},
    showEditDepartmentDropdown: {{ $showEditDepartmentDropdown ? 'true' : 'false' }},
    userDepartment: {{ $userDepartment ? $userDepartment : 'null' }}
};

// Ensure Feather icons are initialized for programs page
document.addEventListener('DOMContentLoaded', function() {
    function initFeatherForPrograms() {
        if (typeof feather !== 'undefined' && feather.replace) {
            feather.replace();
            console.log('Feather icons force-initialized from programs view');
        } else {
            setTimeout(initFeatherForPrograms, 50);
        }
    }
    
    // Multiple initialization attempts with more aggressive timing
    setTimeout(initFeatherForPrograms, 0);
    setTimeout(initFeatherForPrograms, 50);
    setTimeout(initFeatherForPrograms, 100);
    setTimeout(initFeatherForPrograms, 250);
    setTimeout(initFeatherForPrograms, 500);
    setTimeout(initFeatherForPrograms, 1000);
    setTimeout(initFeatherForPrograms, 2000);
    
    // Also try with window.onload
    window.addEventListener('load', initFeatherForPrograms);
});
</script>
@endpush