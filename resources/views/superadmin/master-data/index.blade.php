{{-- 
-------------------------------------------------------------------------------
* File: resources/views/superadmin/master-data/index.blade.php
* Description: Master Data page using Syllaverse module layout + underline tabs (matches Manage Accounts UI)
-------------------------------------------------------------------------------
📜 Log:
[2025-08-12] Realigned structure to Manage Accounts: added `.sv-tabs`, wrapped panes, standardized IDs/ARIA, kept includes.
[2025-08-12] Moved “Information” include inside its own tab-pane; set “Skills & Outcomes” as default active pane.
[2025-08-12] Fix – restored `@include('superadmin.master-data.modals.add-modals')` so Add buttons open modals.
[2025-08-12] Add – included Vite script `resources/js/superadmin/master-data/sortable.js`.
[2025-08-12] Add – included Vite styles `resources/css/superadmin/master-data/master-data.css` to apply shared UI/UX.
[2025-08-17] Add – new top tab “Assessment Tasks” with include `tabs.assessment-tasks`.
[2025-08-17] Fix – corrected asset name to `assessment-tasks.js` (plural) to match generated file.
-------------------------------------------------------------------------------
--}}

@extends('layouts.superadmin')

@section('title', 'Master Data • Super Admin • Syllaverse')
@section('page-title', 'Master Data')

@section('content')
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <div class="master-data-card"><!-- Master data content card container -->

    {{-- ░░░ START: Top Tabs (Skills & Outcomes / Assessment Tasks / Information) ░░░ --}}
    <ul class="nav master-data-tabs" id="masterDataTabs" role="tablist" aria-label="Master Data tabs">
      <li class="nav-item" role="presentation">
        <button
          class="nav-link master-data-tab active"
          id="skills-outcomes-tab"
          data-bs-toggle="tab"
          data-bs-target="#skills-outcomes"
          type="button"
          role="tab"
          aria-controls="skills-outcomes"
          aria-selected="true">
          Skills & Outcomes
        </button>
      </li>
  {{-- Assessment Tasks tab removed for this deployment (table not present) --}}
      <li class="nav-item" role="presentation">
        <button
          class="nav-link master-data-tab"
          id="information-tab"
          data-bs-toggle="tab"
          data-bs-target="#information"
          type="button"
          role="tab"
          aria-controls="information"
          aria-selected="false">
          Information
        </button>
      </li>
    </ul>
    {{-- ░░░ END: Top Tabs ░░░ --}}

    {{-- ░░░ START: Tab Content ░░░ --}}
    <div class="tab-content">
      {{-- ░░░ START: Skills & Outcomes Pane ░░░ --}}
      <div class="tab-pane fade show active" id="skills-outcomes" role="tabpanel" aria-labelledby="skills-outcomes-tab">
        @include('superadmin.master-data.tabs.skills-outcomes')
      </div>
      {{-- ░░░ END: Skills & Outcomes Pane ░░░ --}}

  {{-- Assessment Tasks pane removed for this deployment --}}

      {{-- ░░░ START: Information Pane ░░░ --}}
      <div class="tab-pane fade" id="information" role="tabpanel" aria-labelledby="information-tab">
        @include('superadmin.master-data.tabs.information')
      </div>
      {{-- ░░░ END: Information Pane ░░░ --}}
    </div>
    {{-- ░░░ END: Tab Content ░░░ --}}

  </div><!-- END: master-data-card -->

  @push('modals')
    @include('superadmin.master-data.modals.add-modals')
    @include('superadmin.master-data.modals.edit-modal')
    @include('superadmin.master-data.modals.delete-modal')
  @endpush

  {{-- ░░░ START: Module Assets (CSS + JS) ░░░ --}}
  @vite('resources/css/superadmin/master-data/master-data.css')
  @vite('resources/js/superadmin/master-data/sortable.js')
  {{-- Assessment Tasks JS removed because AssessmentTaskGroup table is not present in this deployment --}}
  {{-- ░░░ END: Module Assets ░░░ --}}

@endsection
