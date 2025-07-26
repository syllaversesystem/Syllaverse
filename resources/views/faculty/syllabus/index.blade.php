{{-- 
------------------------------------------------
* File: resources/views/faculty/syllabus/index.blade.php
* Description: Lists all syllabi in card format with view and delete actions; includes modal for new syllabus creation – Syllaverse
------------------------------------------------ 
--}}

@extends('layouts.faculty')

@section('content')
  {{-- Create Syllabus Modal --}}
  @include('faculty.syllabus.modals.create')

  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3>Your Syllabi</h3>
      <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#selectSyllabusMetaModal">
        + Create Syllabus
      </button>
    </div>

    {{-- Success Message --}}
    @if (session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    {{-- Syllabus List --}}
    @if ($syllabi->isEmpty())
      <p class="text-muted">You haven't created any syllabi yet.</p>
    @else
      <div class="row row-cols-1 row-cols-md-3 g-4">
        @foreach ($syllabi as $syllabus)
          <div class="col">
            <div class="card shadow-sm h-100">
              <div class="card-body">
                <h5 class="card-title">{{ $syllabus->title }}</h5>
                <p class="card-text mb-1">
                  <strong>Course:</strong> {{ $syllabus->course->code ?? '—' }} - {{ $syllabus->course->title ?? '' }}
                </p>
                <p class="card-text mb-1">
                  <strong>AY:</strong> {{ $syllabus->academic_year }}<br>
                  <strong>Semester:</strong> {{ $syllabus->semester }}
                </p>
                <p class="card-text text-muted small mt-2">Created on {{ $syllabus->created_at->format('F d, Y') }}</p>

                <div class="d-flex justify-content-between align-items-center mt-3">
                  <a href="{{ route('faculty.syllabi.show', $syllabus->id) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-eye"></i> View
                  </a>
                  <form action="{{ route('faculty.syllabi.destroy', $syllabus->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this syllabus?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                      <i class="bi bi-trash"></i> Delete
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>
@endsection
