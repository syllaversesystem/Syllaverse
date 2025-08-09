{{-- ------------------------------------------------
* File: resources/views/superadmin/manage-accounts/tabs/admins.blade.php
* Description: Admins tab with Approvals (merged), Approved, and Rejected sub-tabs – Syllaverse
------------------------------------------------ 
📜 Log:
[2025-08-08] Replaced "Pending Requests" with merged "Approvals" (accounts + chair requests).
--}}

<ul class="nav mb-4" id="adminsSubTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link sv-subtab active" id="admins-approvals-tab" data-bs-toggle="pill" data-bs-target="#admins-approvals" type="button" role="tab">
      Approvals
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link sv-subtab" id="admins-approved-tab" data-bs-toggle="pill" data-bs-target="#admins-approved" type="button" role="tab">
      Approved Admins
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link sv-subtab" id="admins-rejected-tab" data-bs-toggle="pill" data-bs-target="#admins-rejected" type="button" role="tab">
      Rejected Admins
    </button>
  </li>
</ul>

<div class="tab-content">
  @include('superadmin.manage-accounts.tabs.admins-approvals') {{-- ✅ NEW: merged queue --}}
  @include('superadmin.manage-accounts.tabs.admins-approved')
  @include('superadmin.manage-accounts.tabs.admins-rejected')
</div>
