{{-- 
------------------------------------------------
* File: resources/views/superadmin/manage-accounts/index.blade.php
* Description: Super Admin Manage Accounts Tab Container Page (Syllaverse)
------------------------------------------------ 
📜 Log:
[2025-08-08] Removed top-level "Chair Requests" tab; merged into Admins → Approvals sub-tab.
--}}

@extends('layouts.superadmin')

@section('title', 'Manage Accounts • Super Admin • Syllaverse')
@section('page-title', 'Manage Accounts')

@section('content')
<ul class="nav nav-tabs mb-4" id="accountTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="admins-tab" data-bs-toggle="tab" data-bs-target="#admins" type="button" role="tab">
      Admins
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="faculty-tab" data-bs-toggle="tab" data-bs-target="#faculty" type="button" role="tab">
      Faculty
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="students-tab" data-bs-toggle="tab" data-bs-target="#students" type="button" role="tab">
      Students
    </button>
  </li>
</ul>

<div class="tab-content">
  {{-- ░░░ START: Admins Tab ░░░ --}}
  <div class="tab-pane fade show active" id="admins" role="tabpanel">
    @include('superadmin.manage-accounts.tabs.admins')
  </div>
  {{-- ░░░ END: Admins Tab ░░░ --}}

  {{-- ░░░ START: Faculty Tab ░░░ --}}
  <div class="tab-pane fade" id="faculty" role="tabpanel">
    @include('superadmin.manage-accounts.tabs.faculty')
  </div>
  {{-- ░░░ END: Faculty Tab ░░░ --}}

  {{-- ░░░ START: Students Tab ░░░ --}}
  <div class="tab-pane fade" id="students" role="tabpanel">
    @include('superadmin.manage-accounts.tabs.students')
  </div>
  {{-- ░░░ END: Students Tab ░░░ --}}
</div>
@endsection
