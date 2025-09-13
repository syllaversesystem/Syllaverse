{{-- 
------------------------------------------------
* File: resources/views/faculty/syllabus/modals/create.blade.php
* Description: Modal for creating a syllabus with metadata fields only – Syllaverse
------------------------------------------------ 
--}}

<div class="modal fade" id="selectSyllabusMetaModal" tabindex="-1" aria-labelledby="selectSyllabusMetaModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content shadow">
  @php $rp = $routePrefix ?? 'faculty.syllabi'; @endphp
  <form action="{{ route($rp . '.store') }}" method="POST">
        @csrf
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="selectSyllabusMetaModalLabel">Create Syllabus</h5>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-12">
              <label class="form-label">Syllabus Title</label>
              <input type="text" name="title" class="form-control" placeholder="e.g., Syllabus in BAT 403" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Program (optional)</label>
              <select name="program_id" class="form-select">
                <option value="">-- Select Program --</option>
                @foreach($programs as $program)
                  <option value="{{ $program->id }}">{{ $program->code }} - {{ $program->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Course</label>
              <select name="course_id" class="form-select" required>
                <option value="">-- Select Course --</option>
                @foreach($courses as $course)
                  <option value="{{ $course->id }}">{{ $course->code }} - {{ $course->title }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Academic Year</label>
              <input type="text" name="academic_year" class="form-control" placeholder="e.g., 2024-2025" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Semester</label>
              <select name="semester" class="form-select" required>
                <option value="">-- Select Semester --</option>
                <option value="1st Semester">1st Semester</option>
                <option value="2nd Semester">2nd Semester</option>
                <option value="Summer">Summer</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Year Level</label>
              <select name="year_level" class="form-select" required>
                <option value="">-- Select Year Level --</option>
                <option value="1st Year">1st Year</option>
                <option value="2nd Year">2nd Year</option>
                <option value="3rd Year">3rd Year</option>
                <option value="4th Year">4th Year</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger w-100">Create Syllabus</button>
        </div>
      </form>
    </div>
  </div>
</div>
