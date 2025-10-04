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

@push('modals')
  {{-- ░░░ START: Modals for Programs & Courses ░░░ --}}
  @include('admin.master-data.modals.add-program-modal')
  @include('admin.master-data.modals.edit-program-modal')
  @include('admin.master-data.modals.add-course-modal')
  @include('admin.master-data.modals.edit-course-modal')
  @include('admin.master-data.modals.delete-program-modal')
  {{-- ░░░ END: Modals for Programs & Courses ░░░ --}}
@endpush

{{-- JavaScript --}}
@push('scripts')
<script src="{{ asset('js/admin/programs-courses-search.js') }}"></script>
<script src="{{ asset('js/admin/master-data/programs.js') }}"></script>
<script src="{{ asset('js/admin/master-data/courses.js') }}"></script>
@endpush

@endsection