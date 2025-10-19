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
  <div class="manage-account"><!-- Updated from department-card to manage-account -->

    {{-- ░░░ START: Main Tabs (Approvals, Approved, Rejected) ░░░ --}}
    <ul class="nav superadmin-manage-account-main-tabs" id="accountTabs" role="tablist" aria-label="Account management tabs">
      <li class="nav-item" role="presentation">
        <button class="nav-link superadmin-manage-account-main-tab active"
                id="approvals-tab"
                data-bs-toggle="pill"
                data-bs-target="#approvals"
                type="button"
                role="tab">
          Approvals
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link superadmin-manage-account-main-tab"
                id="approved-tab"
                data-bs-toggle="pill"
                data-bs-target="#approved"
                type="button"
                role="tab">
          Approved
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link superadmin-manage-account-main-tab"
                id="rejected-tab"
                data-bs-toggle="pill"
                data-bs-target="#rejected"
                type="button"
                role="tab">
          Rejected
        </button>
      </li>
    </ul>
    {{-- ░░░ END: Main Tabs ░░░ --}}

    {{-- ░░░ START: Tab Content ░░░ --}}
    <div class="tab-content">
      {{-- ░░░ START: Approvals Tab ░░░ --}}
      <div class="tab-pane fade show active" id="approvals" role="tabpanel" aria-labelledby="approvals-tab">
        @include('superadmin.manage-accounts.tabs.admins-approvals')
      </div>
      {{-- ░░░ END: Approvals Tab ░░░ --}}

      {{-- ░░░ START: Approved Tab ░░░ --}}
      <div class="tab-pane fade" id="approved" role="tabpanel" aria-labelledby="approved-tab">
        @include('superadmin.manage-accounts.tabs.admins-approved')
      </div>
      {{-- ░░░ END: Approved Tab ░░░ --}}

      {{-- ░░░ START: Rejected Tab ░░░ --}}
      <div class="tab-pane fade" id="rejected" role="tabpanel" aria-labelledby="rejected-tab">
        @include('superadmin.manage-accounts.tabs.admins-rejected')
      </div>
      {{-- ░░░ END: Rejected Tab ░░░ --}}
    </div>
    {{-- ░░░ END: Tab Content ░░░ --}}

  </div>
@endsection
