{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/manage-accounts/index.blade.php
* Description: Admin Manage Accounts – styled to match Super Admin's Manage Accounts
-------------------------------------------------------------------------------
--}}

@extends('layouts.admin')

@section('title', 'Manage Accounts • Admin • Syllaverse')
@section('page-title', 'Manage Faculty Accounts')

@section('content')

<div class="manage-accounts">

  {{-- ░░░ START: Tabs Navigation ░░░ --}}
  <ul class="nav nav-tabs sv-tabs" id="facultyAccountsTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="faculty-pending-tab" data-bs-toggle="tab" data-bs-target="#faculty-pending" type="button" role="tab" aria-controls="faculty-pending" aria-selected="true">
        <i data-feather="clock"></i> Pending
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="faculty-approved-tab" data-bs-toggle="tab" data-bs-target="#faculty-approved" type="button" role="tab" aria-controls="faculty-approved" aria-selected="false">
        <i data-feather="check-circle"></i> Approved
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="faculty-rejected-tab" data-bs-toggle="tab" data-bs-target="#faculty-rejected" type="button" role="tab" aria-controls="faculty-rejected" aria-selected="false">
        <i data-feather="x-circle"></i> Rejected
      </button>
    </li>
  </ul>
  {{-- ░░░ END: Tabs Navigation ░░░ --}}

  {{-- ░░░ START: Tab Contents ░░░ --}}
  <div class="tab-content mt-3" id="facultyAccountsTabsContent">
    
    {{-- Pending Tab --}}
    @include('admin.manage-accounts.tabs.pending')

    {{-- Approved Tab --}}
    @include('admin.manage-accounts.tabs.approved')

    {{-- Rejected Tab --}}
    @include('admin.manage-accounts.tabs.rejected')

  </div>
  {{-- ░░░ END: Tab Contents ░░░ --}}

</div>

@endsection
