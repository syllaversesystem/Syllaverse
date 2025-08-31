@extends('layouts.faculty')

@section('page-title', 'Syllabi')
@section('content')

  @push('styles')
    @vite('resources/css/faculty/syllabus-index.css')
  @endpush

  {{-- Create Syllabus Modal --}}
  @include('faculty.syllabus.modals.create')

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

    {{-- Content --}}
    <div class="container-fluid px-3 py-3">

      <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#selectSyllabusMetaModal" aria-label="Create syllabus">
          <i class="bi bi-plus-lg me-1"></i> Create Syllabus
        </button>
      </div>

      @if ($syllabi->isEmpty())
        <div class="svx-empty">
          <div class="ico"><i class="bi bi-journal-text"></i></div>
          <h5 class="fw-bold mb-1">No syllabi yet</h5>
          <p class="text-muted mb-3">Generate your first syllabus by selecting the course and term details.</p>
          <button type="button" class="btn btn-danger fw-semibold" data-bs-toggle="modal" data-bs-target="#selectSyllabusMetaModal">
            <i class="bi bi-plus-lg me-1"></i> Create Syllabus
          </button>
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
                <div class="svx-card-footer">
                  <a href="{{ route('faculty.syllabi.show', $syllabus->id) }}" class="btn btn-outline-primary btn-sm" title="Open syllabus" aria-label="Open syllabus">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Open
                  </a>
                  <form action="{{ route('faculty.syllabi.destroy', $syllabus->id) }}" method="POST" onsubmit="return confirm('Delete this syllabus? This action cannot be undone.');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete syllabus" aria-label="Delete syllabus">
                      <i class="bi bi-trash me-1"></i> Delete
                    </button>
                  </form>
                </div>
              </article>
            </div>
          @endforeach
        </div>
      @endif

    </div>
  </div>
@endsection





