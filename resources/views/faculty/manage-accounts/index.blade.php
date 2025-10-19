{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/manage-accounts/index.blade.php
* Description: Faculty Manage Accounts – styled to match Admin's Manage Accounts
-------------------------------------------------------------------------------
--}}

@extends('layouts.faculty')

@section('title', 'Manage Accounts • Faculty • Syllaverse')
@section('page-title', 'Manage Faculty Accounts')

@push('styles')
  @vite('resources/css/faculty/manage-accounts/manage-accounts.css')
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Force resize Feather icons in faculty manage accounts to match superadmin exact size
  setTimeout(function() {
    const actionButtons = document.querySelectorAll('.faculty-manage-account-table .action-btn svg, .manage-account .action-btn svg');
    actionButtons.forEach(function(svg) {
      svg.setAttribute('width', '17');
      svg.setAttribute('height', '17');
      svg.style.width = '1.05rem';
      svg.style.height = '1.05rem';
      svg.style.strokeWidth = '2';
    });
  }, 100);
});
</script>
@endpush

@section('content')

<div class="manage-account">

  {{-- ░░░ START: Main Tabs (Superadmin Style) ░░░ --}}
  <ul class="nav faculty-manage-account-main-tabs" id="facultyAccountsTabs" role="tablist" aria-label="Faculty account type tabs">
    <li class="nav-item" role="presentation">
      <button class="nav-link faculty-manage-account-main-tab active" id="faculty-pending-tab" data-bs-toggle="tab" data-bs-target="#faculty-pending" type="button" role="tab" aria-controls="faculty-pending" aria-selected="true">
        Pending
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link faculty-manage-account-main-tab" id="faculty-approved-tab" data-bs-toggle="tab" data-bs-target="#faculty-approved" type="button" role="tab" aria-controls="faculty-approved" aria-selected="false">
        Approved
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link faculty-manage-account-main-tab" id="faculty-rejected-tab" data-bs-toggle="tab" data-bs-target="#faculty-rejected" type="button" role="tab" aria-controls="faculty-rejected" aria-selected="false">
        Rejected
      </button>
    </li>
  </ul>
  {{-- ░░░ END: Main Tabs ░░░ --}}

  {{-- ░░░ START: Tab Contents ░░░ --}}
  <div class="tab-content mt-3" id="facultyAccountsTabsContent">
    
    {{-- Pending Tab --}}
    @include('faculty.manage-accounts.tabs.pending')

    {{-- Approved Tab --}}
    @include('faculty.manage-accounts.tabs.approved')

    {{-- Rejected Tab --}}
    @include('faculty.manage-accounts.tabs.rejected')

  </div>
  {{-- ░░░ END: Tab Contents ░░░ --}}

</div>

@endsection
