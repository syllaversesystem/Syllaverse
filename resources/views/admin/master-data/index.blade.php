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

    {{-- ░░░ START: Main Tab (Student & Intended Learning Outcomes) ░░░ --}}
    <div class="text-center mb-3">
      <h5 class="mb-0 text-sv-primary fw-semibold">Student & Intended Learning Outcomes</h5>
    </div>
    {{-- ░░░ END: Main Tab ░░░ --}}

    {{-- ░░░ START: SO/ILO Subtabs ░░░ --}}
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
    {{-- ░░░ END: SO/ILO Subtabs ░░░ --}}

    {{-- ░░░ START: Tab Content ░░░ --}}
    <div class="tab-content" id="soIloTabContent">
      <div class="tab-pane fade show active" id="so" role="tabpanel" aria-labelledby="so-tab">
        @include('admin.master-data.tabs.so')
      </div>
      <div class="tab-pane fade" id="ilo" role="tabpanel" aria-labelledby="ilo-tab">
        @include('admin.master-data.tabs.ilo')
      </div>
    </div>
    {{-- ░░░ END: Tab Content ░░░ --}}

  </div><!-- END: department-card -->

</div>

@push('modals')
  {{-- ░░░ START: Modals for SO & ILO ░░░ --}}
  @include('admin.master-data.modals.add-so-modal')
  @include('admin.master-data.modals.edit-so-modal')
  {{-- ░░░ END: Modals for SO & ILO ░░░ --}}
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
