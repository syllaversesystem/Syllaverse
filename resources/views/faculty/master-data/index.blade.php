{{-- Faculty Master Data: SO, SDG, IGA, CDIO, ILO --}}
@extends('layouts.faculty')

@section('title', 'Master Data • Faculty • Syllaverse')

@section('content')
<div class="master-data-management manage-account" id="masterDataContainer">
  <ul class="nav superadmin-manage-account-main-tabs" id="masterDataMainTabs" role="tablist" aria-label="Master Data tabs">
    <li class="nav-item" role="presentation">
      <button class="nav-link superadmin-manage-account-main-tab active" id="so-main-tab" data-bs-toggle="pill" data-bs-target="#so-main" type="button" role="tab" aria-controls="so-main" aria-selected="true">SO</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link superadmin-manage-account-main-tab" id="sdg-main-tab" data-bs-toggle="pill" data-bs-target="#sdg-main" type="button" role="tab" aria-controls="sdg-main" aria-selected="false">SDG</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link superadmin-manage-account-main-tab" id="iga-main-tab" data-bs-toggle="pill" data-bs-target="#iga-main" type="button" role="tab" aria-controls="iga-main" aria-selected="false">IGA</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link superadmin-manage-account-main-tab" id="cdio-main-tab" data-bs-toggle="pill" data-bs-target="#cdio-main" type="button" role="tab" aria-controls="cdio-main" aria-selected="false">CDIO</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link superadmin-manage-account-main-tab" id="ilo-main-tab" data-bs-toggle="pill" data-bs-target="#ilo-main" type="button" role="tab" aria-controls="ilo-main" aria-selected="false">ILO</button>
    </li>
  </ul>

  <div class="tab-content" id="masterDataTabContent">
    <div class="tab-pane fade show active" id="so-main" role="tabpanel" aria-labelledby="so-main-tab">
      @include('faculty.master-data.tabs.so')
    </div>
    <div class="tab-pane fade" id="sdg-main" role="tabpanel" aria-labelledby="sdg-main-tab">
      @include('faculty.master-data.tabs.sdg')
    </div>
    <div class="tab-pane fade" id="iga-main" role="tabpanel" aria-labelledby="iga-main-tab">
      @include('faculty.master-data.tabs.iga')
    </div>
    <div class="tab-pane fade" id="cdio-main" role="tabpanel" aria-labelledby="cdio-main-tab">
      @include('faculty.master-data.tabs.cdio')
    </div>
    <div class="tab-pane fade" id="ilo-main" role="tabpanel" aria-labelledby="ilo-main-tab">
      @include('faculty.master-data.tabs.ilo')
    </div>
  </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/faculty/master-data/so.js')
@vite('resources/js/faculty/master-data/sdg.js')
@vite('resources/js/faculty/master-data/iga.js')
@vite('resources/js/faculty/master-data/ilo-simple.js')
@vite('resources/js/faculty/master-data/shared-crud.js')
@endpush

@include('faculty.master-data.modals.add-so-modal')
@include('faculty.master-data.modals.edit-so-modal')
@include('faculty.master-data.modals.delete-so-modal')
@include('faculty.master-data.modals.add-sdg-modal')
@include('faculty.master-data.modals.edit-sdg-modal')
@include('faculty.master-data.modals.delete-sdg-modal')
@include('faculty.master-data.modals.add-iga-modal')
@include('faculty.master-data.modals.edit-iga-modal')
@include('faculty.master-data.modals.delete-iga-modal')