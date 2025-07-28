{{-- 
------------------------------------------------
* File: resources/views/admin/master-data/index.blade.php
* Description: Admin Master Data Page with separate sections for SO/ILO and Program/Course (Syllaverse)
------------------------------------------------ 
--}}
@extends('layouts.admin')

@section('title', 'Master Data • Admin • Syllaverse')
@section('page-title', 'Master Data')

@section('content')
<div class="container">
    {{-- Main Tabs --}}
    <ul class="nav nav-pills mb-4" id="mainMasterTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ request('tab', 'soilo') === 'soilo' ? 'active' : '' }}" href="{{ route('admin.master-data.index', ['tab' => 'soilo'] + request()->except('page')) }}">
                Student & Intended Learning Outcomes
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('tab') === 'programcourse' ? 'active' : '' }}" href="{{ route('admin.master-data.index', ['tab' => 'programcourse'] + request()->except('page')) }}">
                Programs & Courses
            </a>
        </li>
    </ul>

    <div class="tab-content" id="mainMasterTabsContent">
        {{-- SO & ILO Section --}}
        <div class="tab-pane fade {{ request('tab', 'soilo') === 'soilo' ? 'show active' : '' }}" id="soilo" role="tabpanel">
            <ul class="nav nav-tabs mb-3" id="soIloSubTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ request('subtab', 'so') === 'so' ? 'active' : '' }}" href="{{ route('admin.master-data.index', ['tab' => 'soilo', 'subtab' => 'so'] + request()->except('page')) }}">
                        Student Outcomes (SO)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request('subtab') === 'ilo' ? 'active' : '' }}" href="{{ route('admin.master-data.index', ['tab' => 'soilo', 'subtab' => 'ilo'] + request()->except('page')) }}">
                        Intended Learning Outcomes (ILO)
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="soIloTabContent">
                <div class="tab-pane fade {{ request('subtab', 'so') === 'so' ? 'show active' : '' }}" id="so" role="tabpanel">
                    @include('admin.master-data.tabs.so')
                </div>
                <div class="tab-pane fade {{ request('subtab') === 'ilo' ? 'show active' : '' }}" id="ilo" role="tabpanel">
                    @include('admin.master-data.tabs.ilo')
                </div>
            </div>
        </div>

        {{-- Program & Course Section --}}
        <div class="tab-pane fade {{ request('tab') === 'programcourse' ? 'show active' : '' }}" id="programcourse" role="tabpanel">
            <ul class="nav nav-tabs mb-3" id="progCourseSubTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ request('subtab', 'programs') === 'programs' ? 'active' : '' }}" href="{{ route('admin.master-data.index', ['tab' => 'programcourse', 'subtab' => 'programs'] + request()->except('page')) }}">
                        Programs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request('subtab') === 'courses' ? 'active' : '' }}" href="{{ route('admin.master-data.index', ['tab' => 'programcourse', 'subtab' => 'courses'] + request()->except('page')) }}">
                        Courses
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="progCourseTabContent">
                <div class="tab-pane fade {{ request('subtab', 'programs') === 'programs' ? 'show active' : '' }}" id="programs" role="tabpanel">
                    @include('admin.master-data.tabs.programs-tab')
                </div>
                <div class="tab-pane fade {{ request('subtab') === 'courses' ? 'show active' : '' }}" id="courses" role="tabpanel">
                    @include('admin.master-data.tabs.courses-tab')
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modals --}}
@include('admin.master-data.modals.add-program-modal')
@include('admin.master-data.modals.edit-program-modal')
@include('admin.master-data.modals.add-course-modal')
@include('admin.master-data.modals.edit-course-modal')
@endsection
