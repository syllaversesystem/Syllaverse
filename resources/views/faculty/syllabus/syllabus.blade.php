{{-- 
------------------------------------------------
* File: resources/views/faculty/syllabus/syllabus.blade.php
* Description: CIS-format editable syllabus page with export, aligned header, and modular assets (Syllaverse)
------------------------------------------------ 
--}}

@extends('layouts.faculty')

@section('content')
  {{-- ✅ Vite Assets --}}
  @vite([
    'resources/css/faculty/syllabus.css',
    'resources/js/faculty/syllabus.js',
    'resources/js/faculty/syllabus-ilo.js',
    'resources/js/faculty/syllabus-so.js',
    'resources/js/faculty/syllabus-tla-ai.js',
  ])

  {{-- ✅ Global JS Variables --}}
  <script>
    const syllabusExitUrl = @json(route('faculty.syllabi.index'));
    const syllabusId = @json($default['id']);
  </script>

  <div class="container my-4 syllabus-doc">
    {{-- ░░░ START: Main General Syllabus Form ░░░ --}}
    <form id="syllabusForm"
          method="POST"
          action="{{ route('faculty.syllabi.update', $default['id']) }}"
          enctype="multipart/form-data">
      @csrf
      @method('PUT')

      {{-- ░░░ START: Action Bar ░░░ --}}
      <div class="form-actions mb-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
          <button type="submit" class="btn btn-danger fw-semibold">
            <i class="bi bi-save"></i> Save
          </button>
          <button type="button" class="btn btn-outline-secondary fw-semibold" onclick="handleExit()">
            <i class="bi bi-box-arrow-left"></i> Exit
          </button>
        </div>

        {{-- Export Buttons --}}
        <div class="btn-group">
          <a href="{{ route('faculty.syllabi.export.pdf', $default['id']) }}" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-filetype-pdf"></i> PDF
          </a>
          <a href="{{ route('faculty.syllabi.export.word', $default['id']) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-file-earmark-word"></i> Word
          </a>
        </div>
      </div>
      {{-- ░░░ END: Action Bar ░░░ --}}

      {{-- ░░░ START: Section I – Header ░░░ --}}
      @include('faculty.syllabus.partials.header')

      {{-- ░░░ START: Section II – Mission and Vision ░░░ --}}
      @include('faculty.syllabus.partials.mission-vision')

      {{-- ░░░ START: Section III – Textbook Upload ░░░ --}}
      @include('faculty.syllabus.partials.textbook-upload')
    </form>
    {{-- ░░░ END: Main Form ░░░ --}}

    {{-- ░░░ START: Section IV – ILO Mapping ░░░ --}}
    @include('faculty.syllabus.partials.ilo')

    {{-- ░░░ START: Section V – SO Mapping ░░░ --}}
    @include('faculty.syllabus.partials.so')

    {{-- ░░░ START: Section VI – Teaching, Learning, and Assessment ░░░ --}}
    @include('faculty.syllabus.partials.tla')

    {{-- ░░░ START: Section VII – SDG Mapping ░░░ --}}
    @include('faculty.syllabus.partials.sdg')
  </div>
@endsection

