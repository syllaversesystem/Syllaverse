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

@section('content')
<div class="manage-accounts">
  <div class="department-card"><!-- Reuses the polished glass card container -->

    {{-- ░░░ START: Main Tabs (SO/ILO vs Programs/Courses) ░░░ --}}
    <ul class="nav sv-tabs" id="masterDataTabs" role="tablist" aria-label="Master Data tabs">
      <li class="nav-item" role="presentation">
        <button class="nav-link sv-tab active" id="soilo-tab"
                data-bs-toggle="tab" data-bs-target="#soilo"
                type="button" role="tab" aria-controls="soilo" aria-selected="true">
          Student & Intended Learning Outcomes
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link sv-tab" id="programcourse-tab"
                data-bs-toggle="tab" data-bs-target="#programcourse"
                type="button" role="tab" aria-controls="programcourse" aria-selected="false">
          Programs & Courses
        </button>
      </li>
    </ul>
    {{-- ░░░ END: Main Tabs ░░░ --}}

    {{-- ░░░ START: SO/ILO Subtabs ░░░ --}}
    <div id="soilo-subtabs" class="subtab-container">
      <ul class="nav mb-4" id="soIloSubTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link sv-subtab active" id="so-tab"
                  data-bs-toggle="tab" data-bs-target="#so"
                  type="button" role="tab" aria-controls="so" aria-selected="true">
            Student Outcomes
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link sv-subtab" id="ilo-tab"
                  data-bs-toggle="tab" data-bs-target="#ilo"
                  type="button" role="tab" aria-controls="ilo" aria-selected="false">
            Intended Learning Outcomes
          </button>
        </li>
      </ul>
    </div>
    {{-- ░░░ END: SO/ILO Subtabs ░░░ --}}

    {{-- ░░░ START: Programs/Courses Subtabs ░░░ --}}
    <div id="programcourse-subtabs" class="subtab-container d-none">
      <ul class="nav mb-4" id="progCourseSubTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link sv-subtab active" id="programs-tab"
                  data-bs-toggle="tab" data-bs-target="#programs"
                  type="button" role="tab" aria-controls="programs" aria-selected="true">
            Programs
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link sv-subtab" id="courses-tab"
                  data-bs-toggle="tab" data-bs-target="#courses"
                  type="button" role="tab" aria-controls="courses" aria-selected="false">
            Courses
          </button>
        </li>
      </ul>
    </div>
    {{-- ░░░ END: Programs/Courses Subtabs ░░░ --}}

    {{-- ░░░ START: Tab Content ░░░ --}}
    <div class="tab-content">

      {{-- ░░░ START: SO & ILO Section ░░░ --}}
      <div class="tab-pane fade show active p-4" id="soilo" role="tabpanel" aria-labelledby="soilo-tab">
        <div class="tab-content" id="soIloTabContent">
          <div class="tab-pane fade show active" id="so" role="tabpanel" aria-labelledby="so-tab">
            @include('admin.master-data.tabs.so')
          </div>
          <div class="tab-pane fade" id="ilo" role="tabpanel" aria-labelledby="ilo-tab">
            @include('admin.master-data.tabs.ilo')
          </div>
        </div>
      </div>
      {{-- ░░░ END: SO & ILO Section ░░░ --}}

      {{-- ░░░ START: Programs & Courses Section ░░░ --}}
      <div class="tab-pane fade p-4" id="programcourse" role="tabpanel" aria-labelledby="programcourse-tab">
        <div class="tab-content" id="progCourseTabContent">
          <div class="tab-pane fade show active" id="programs" role="tabpanel" aria-labelledby="programs-tab">
            @include('admin.master-data.tabs.programs-tab')
          </div>
          <div class="tab-pane fade" id="courses" role="tabpanel" aria-labelledby="courses-tab">
            @include('admin.master-data.tabs.courses-tab')
          </div>
        </div>
      </div>
      {{-- ░░░ END: Programs & Courses Section ░░░ --}}

    </div>
    {{-- ░░░ END: Tab Content ░░░ --}}

  </div><!-- END: department-card -->

</div>

@push('modals')
  {{-- ░░░ START: Modals for Programs & Courses ░░░ --}}
  @include('admin.master-data.modals.add-program-modal')
  @include('admin.master-data.modals.edit-program-modal')
  @include('admin.master-data.modals.add-course-modal')
  @include('admin.master-data.modals.edit-course-modal')
  @include('admin.master-data.modals.delete-program-modal')

  @include('admin.master-data.modals.add-so-modal')
  @include('admin.master-data.modals.edit-so-modal')
  {{-- ░░░ END: Modals for Programs & Courses ░░░ --}}
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const soIloSubtabs = document.getElementById('soilo-subtabs');
    const programCourseSubtabs = document.getElementById('programcourse-subtabs');
    
    // Show/hide subtabs based on main tab selection
    document.querySelectorAll('#masterDataTabs button[data-bs-toggle="tab"]').forEach(function (tab) {
      tab.addEventListener('shown.bs.tab', function (event) {
        const targetId = event.target.getAttribute('data-bs-target');
        
        if (targetId === '#soilo') {
          soIloSubtabs.classList.remove('d-none');
          programCourseSubtabs.classList.add('d-none');
        } else if (targetId === '#programcourse') {
          soIloSubtabs.classList.add('d-none');
          programCourseSubtabs.classList.remove('d-none');
        }
      });
    });

    // Reset subtabs to first option when switching main tabs
    document.getElementById('soilo-tab').addEventListener('shown.bs.tab', function () {
      // Activate first SO/ILO subtab
      document.getElementById('so-tab').click();
    });

    document.getElementById('programcourse-tab').addEventListener('shown.bs.tab', function () {
      // Activate first Programs/Courses subtab
      document.getElementById('programs-tab').click();
    });
  });
</script>
@endpush

@endsection
