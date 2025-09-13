@extends('layouts.admin')

@section('page-title', 'Create Syllabus')
@section('content')

<div class="container-fluid px-3 py-3">
  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <div class="card-body">
          <h4 class="mb-3">Create Syllabus</h4>

          @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
          @endif

          <form method="POST" action="{{ route('admin.syllabi.store') }}">
            @csrf

            <div class="mb-3">
              <label class="form-label">Faculty</label>
              <select name="faculty_id" class="form-select">
                <option value="">(Use current admin)</option>
                @foreach($faculties as $f)
                  <option value="{{ $f->id }}">{{ $f->name }} &mdash; {{ $f->email }}</option>
                @endforeach
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Course</label>
              <select name="course_id" class="form-select" required>
                @foreach($courses as $c)
                  <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->title }}</option>
                @endforeach
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Program (optional)</label>
              <select name="program_id" class="form-select">
                <option value="">(none)</option>
                @foreach($programs as $p)
                  <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Title</label>
              <input type="text" name="title" class="form-control" required />
            </div>

            <div class="row">
              <div class="col-md-4 mb-3">
                <label class="form-label">Academic Year</label>
                <input type="text" name="academic_year" class="form-control" required />
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Semester</label>
                <input type="text" name="semester" class="form-control" required />
              </div>
              <div class="col-md-4 mb-3">
                <label class="form-label">Year Level</label>
                <input type="text" name="year_level" class="form-control" required />
              </div>
            </div>

            <div class="d-flex gap-2">
              <button class="btn btn-danger" type="submit">Create</button>
              <a href="{{ route('admin.syllabi.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
