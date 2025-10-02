{{-- ------------------------------------------------
* File: resources/views/superadmin/manage-accounts/tabs/faculty.blade.php
* Description: Faculty Accounts Tab (Syllaverse)
------------------------------------------------ --}}
<div class="superadmin-manage-account-faculty-toolbar">
    <div class="input-group">
        <span class="input-group-text"><i data-feather="search"></i></span>
        <input type="search" class="form-control" placeholder="Search faculty..." aria-label="Search faculty">
    </div>
</div>

<div class="table-wrapper position-relative">
    <div class="table-responsive">
        <table class="table superadmin-manage-account-table table-hover">
            <thead class="superadmin-manage-account-table-header table-light">
        <tr>
            <th><i data-feather="user"></i> Name</th>
            <th><i data-feather="mail"></i> Email</th>
            <th class="text-end"><i data-feather="more-vertical"></i></th>
        </tr>
    </thead>
    <tbody>
        @if($faculty->isEmpty())
        <tr class="superadmin-manage-account-empty-row">
            <td colspan="3">
                <div class="empty-table">
                    <h6>No faculty found</h6>
                    <p>Faculty members will appear here when they register.</p>
                </div>
            </td>
        </tr>
        @else
        @foreach ($faculty as $user)
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td></td>
        </tr>
        @endforeach
        @endif
    </tbody>
        </table>
    </div>
</div>
