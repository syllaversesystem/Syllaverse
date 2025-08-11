{{-- 
-------------------------------------------------------------------------------
* File: resources/views/superadmin/manage-accounts/index.blade.php
* Description: Super Admin Manage Accounts page with Syllaverse module layout & custom tabs
-------------------------------------------------------------------------------
📜 Log:
[2025-08-08] Removed top-level "Chair Requests" tab; merged into Admins → Approvals sub-tab.
[2025-08-11] Removed search bar/toolbar; tightened header spacing and simplified layout.
-------------------------------------------------------------------------------
--}}

@extends('layouts.superadmin')

@section('title', 'Manage Accounts • Super Admin • Syllaverse')
@section('page-title', 'Manage Accounts')

@section('content')
  <div class="department-card"><!-- Reuses the polished glass card container -->

    {{-- ░░░ START: Top Tabs (Admins / Faculty / Students) ░░░ --}}
    <ul class="nav sv-tabs" id="accountTabs" role="tablist" aria-label="Account type tabs">
      <li class="nav-item" role="presentation">
        <button class="nav-link sv-tab active" id="admins-tab" data-bs-toggle="tab" data-bs-target="#admins" type="button" role="tab" aria-controls="admins" aria-selected="true">
          Admins
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link sv-tab" id="faculty-tab" data-bs-toggle="tab" data-bs-target="#faculty" type="button" role="tab" aria-controls="faculty" aria-selected="false">
          Faculty
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link sv-tab" id="students-tab" data-bs-toggle="tab" data-bs-target="#students" type="button" role="tab" aria-controls="students" aria-selected="false">
          Students
        </button>
      </li>
    </ul>
    {{-- ░░░ END: Top Tabs ░░░ --}}

    {{-- ░░░ START: Tab Content ░░░ --}}
    <div class="tab-content">
      {{-- ░░░ START: Admins Tab ░░░ --}}
      <div class="tab-pane fade show active" id="admins" role="tabpanel" aria-labelledby="admins-tab">
        @include('superadmin.manage-accounts.tabs.admins')
      </div>
      {{-- ░░░ END: Admins Tab ░░░ --}}

      {{-- ░░░ START: Faculty Tab ░░░ --}}
      <div class="tab-pane fade" id="faculty" role="tabpanel" aria-labelledby="faculty-tab">
        @include('superadmin.manage-accounts.tabs.faculty')
      </div>
      {{-- ░░░ END: Faculty Tab ░░░ --}}

      {{-- ░░░ START: Students Tab ░░░ --}}
      <div class="tab-pane fade" id="students" role="tabpanel" aria-labelledby="students-tab">
        @include('superadmin.manage-accounts.tabs.students')
      </div>
      {{-- ░░░ END: Students Tab ░░░ --}}
    </div>
    {{-- ░░░ END: Tab Content ░░░ --}}

  </div>
@endsection
