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

  {{-- ░░░ START: Main Tabs (SO/ILO vs Programs/Courses) ░░░ --}}
  <ul class="nav nav-tabs sv-tabs" id="mainMasterTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="soilo-tab"
              data-bs-toggle="tab" data-bs-target="#soilo"
              type="button" role="tab" aria-controls="soilo" aria-selected="true">
        <i data-feather="layers"></i> Student & Intended Learning Outcomes
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="programcourse-tab"
              data-bs-toggle="tab" data-bs-target="#programcourse"
              type="button" role="tab" aria-controls="programcourse" aria-selected="false">
        <i data-feather="book-open"></i> Programs & Courses
      </button>
    </li>
  </ul>
  {{-- ░░░ END: Main Tabs ░░░ --}}

  {{-- ░░░ START: Tab Content ░░░ --}}
  <div class="tab-content mt-3" id="mainMasterTabsContent">

    {{-- ░░░ START: SO & ILO Section ░░░ --}}
    <div class="tab-pane fade show active" id="soilo" role="tabpanel" aria-labelledby="soilo-tab">
      <ul class="nav nav-tabs sv-subtabs mb-3" id="soIloSubTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="so-tab"
                  data-bs-toggle="tab" data-bs-target="#so"
                  type="button" role="tab" aria-controls="so" aria-selected="true">
            <i data-feather="target"></i> Student Outcomes
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="ilo-tab"
                  data-bs-toggle="tab" data-bs-target="#ilo"
                  type="button" role="tab" aria-controls="ilo" aria-selected="false">
            <i data-feather="flag"></i> Intended Learning Outcomes
          </button>
        </li>
      </ul>

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
    <div class="tab-pane fade" id="programcourse" role="tabpanel" aria-labelledby="programcourse-tab">
      <ul class="nav nav-tabs sv-subtabs mb-3" id="progCourseSubTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="programs-tab"
                  data-bs-toggle="tab" data-bs-target="#programs"
                  type="button" role="tab" aria-controls="programs" aria-selected="true">
            <i data-feather="layers"></i> Programs
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="courses-tab"
                  data-bs-toggle="tab" data-bs-target="#courses"
                  type="button" role="tab" aria-controls="courses" aria-selected="false">
            <i data-feather="book"></i> Courses
          </button>
        </li>
      </ul>

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

</div>

{{-- ░░░ START: Modals for Programs & Courses ░░░ --}}
@include('admin.master-data.modals.add-program-modal')
@include('admin.master-data.modals.edit-program-modal')
@include('admin.master-data.modals.add-course-modal')
@include('admin.master-data.modals.edit-course-modal')
@include('admin.master-data.modals.delete-program-modal')

@include('admin.master-data.modals.add-so-modal')
@include('admin.master-data.modals.edit-so-modal')


{{-- ░░░ END: Modals for Programs & Courses ░░░ --}}


@endsection
