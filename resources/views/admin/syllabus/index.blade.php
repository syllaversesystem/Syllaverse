@extends('layouts.admin')

@section('page-title', 'Syllabi')
@section('content')

  @push('styles')
    @vite('resources/css/faculty/syllabus-index.css')
  @endpush

  <div class="svx-fullbleed">

    {{-- Alerts (full-bleed) --}}
    @if (session('success'))
      <div class="alert alert-success alert-dismissible rounded-0 mb-0 fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif
    @if ($errors->any())
      <div class="alert alert-danger alert-dismissible rounded-0 mb-0 fade show" role="alert">
        {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <div class="container-fluid px-3 py-3">

      <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#selectSyllabusMetaModal">
          <i class="bi bi-plus-lg me-1"></i> Create Syllabus
        </button>
      </div>

      @if ($syllabi->isEmpty())
        <div class="svx-empty">
          <div class="ico"><i class="bi bi-journal-text"></i></div>
          <h5 class="fw-bold mb-1">No syllabi yet</h5>
          <p class="text-muted mb-3">There are no syllabi available. Use the Create button to add the first syllabus.</p>
          <button class="btn btn-danger fw-semibold" data-bs-toggle="modal" data-bs-target="#selectSyllabusMetaModal">
            <i class="bi bi-plus-lg me-1"></i> Create Syllabus
          </button>
          @include('admin.syllabus.modals.create', [
            'programs' => $programs ?? collect(),
            'courses' => $courses ?? collect(),
            'routePrefix' => 'admin.syllabi'
          ])
      @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 svx-grid">
          @foreach ($syllabi as $syllabus)
            <div class="col">
              <article class="svx-card shadow-sm">
                <div class="svx-card-body">
                  <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="svx-course-pill">
                      <i class="bi bi-book"></i>{{ $syllabus->course->code ?? '-' }}
                    </span>
                    <span class="svx-stamp">Updated {{ optional($syllabus->updated_at)->diffForHumans() ?? '-' }}</span>
                  </div>
                  <h3 class="svx-title-lg mb-0">{{ $syllabus->title }}</h3>
                  @if(!empty($syllabus->course?->title))
                    <div class="svx-subtle">{{ $syllabus->course->title }}</div>
                  @endif
                  <div class="svx-meta mt-1">
                    <span class="chip"><i class="bi bi-calendar3"></i> AY {{ $syllabus->academic_year }}</span>
                    <span class="chip"><i class="bi bi-collection"></i> {{ $syllabus->semester }}</span>
                    <span class="chip"><i class="bi bi-people"></i> {{ $syllabus->year_level ?? '-' }}</span>
                  </div>
                </div>
                <div class="svx-card-footer text-end">
                  <a href="{{ route('admin.syllabi.show', $syllabus->id) }}" class="btn btn-outline-primary btn-sm" title="Open syllabus" aria-label="Open syllabus">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Open
                  </a>
                  <a href="{{ route('admin.syllabi.export.pdf', $syllabus->id) }}" class="btn btn-outline-secondary btn-sm ms-1">PDF</a>
                  <a href="{{ route('admin.syllabi.export.word', $syllabus->id) }}" class="btn btn-outline-secondary btn-sm ms-1">Word</a>
                </div>
              </article>
            </div>
          @endforeach
        </div>
      @endif
      @include('admin.syllabus.modals.create', [
        'programs' => $programs ?? collect(),
        'courses' => $courses ?? collect(),
        'faculties' => $faculties ?? collect(),
        'routePrefix' => 'admin.syllabi'
      ])

    </div>
  </div>
@endsection
