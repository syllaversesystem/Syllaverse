{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/courses/partials/courses-table.blade.php
* Description: Courses table with original styling from master-data design
-------------------------------------------------------------------------------
--}}

<div class="table-wrapper position-relative">

  {{-- ░░░ START: Toolbar ░░░ --}}
  <div class="superadmin-manage-department-toolbar">
    <div class="input-group">
      <span class="input-group-text"><i data-feather="search"></i></span>
      <input type="search" class="form-control" placeholder="Search courses..." aria-label="Search courses" id="coursesSearch">
    </div>

    <span class="flex-spacer"></span>

    @php
      $canManageCourses = Auth::user()->role === 'admin' 
        || (method_exists(Auth::user(), 'isDeptChair') && Auth::user()->isDeptChair())
        || (method_exists(Auth::user(), 'isProgChair') && Auth::user()->isProgChair());
    @endphp

    @if ($canManageCourses)
      <button type="button"
              class="btn-brand-sm d-none d-md-inline-flex"
              data-bs-toggle="modal"
              data-bs-target="#addCourseModal"
              aria-label="Add Course"
              title="Add Course">
        <i data-feather="plus"></i>
      </button>
    @else
      <button type="button"
              class="btn-brand-sm d-none d-md-inline-flex disabled"
              aria-label="Add Course"
              title="Add Course (disabled)">
        <i data-feather="lock"></i>
      </button>
    @endif
  </div>
  {{-- ░░░ END: Toolbar ░░░ --}}

  {{-- Helper for JS update/delete base URL --}}
  <input type="hidden" id="editCourseUpdateUrlBase" value="{{ url('/admin/courses') }}"/>

  {{-- ░░░ START: Table ░░░ --}}
  <div class="table-responsive">
    <table class="table mb-0 sv-accounts-table" id="svCoursesTable">
      <thead>
        <tr>
          <th><i data-feather="hash"></i> Code</th>
          <th><i data-feather="book"></i> Title</th>
          <th><i data-feather="git-branch"></i> Prerequisites</th>
          <th><i data-feather="clock"></i> Contact Hours</th>
          <th class="text-end"><i data-feather="more-vertical"></i></th>
        </tr>
      </thead>
      <tbody id="svCoursesTbody">
        @forelse ($courses ?? [] as $course)
          @php
            $preReqCodes   = ($course->prerequisites ?? collect())->pluck('code')->filter()->values();
            $preReqPreview = $preReqCodes->take(3)->implode(', ');
            $preReqExtra   = max($preReqCodes->count() - 3, 0);
          @endphp
          <tr id="course-row-{{ $course->id }}"
              data-id="{{ $course->id }}"
              data-code="{{ $course->code }}"
              data-title="{{ $course->title }}"
              data-description="{{ $course->description }}"
              data-contact-hours-lec="{{ $course->contact_hours_lec }}"
              data-contact-hours-lab="{{ $course->contact_hours_lab }}"
              data-prereq='@json(($course->prerequisites ?? collect())->pluck("id"))'>
            <td class="fw-semibold">{{ $course->code }}</td>
            <td class="fw-medium">{{ $course->title }}</td>

            {{-- Prerequisites column --}}
            <td class="text-muted">
              @if($preReqCodes->isEmpty())
                <span class="text-secondary">—</span>
              @else
                <span>{{ $preReqPreview }}</span>
                @if($preReqExtra > 0)
                  <span class="badge rounded-pill text-bg-light ms-1">+{{ $preReqExtra }}</span>
                @endif
              @endif
            </td>

            {{-- Contact hours --}}
            <td class="text-muted">
              {{ $course->contact_hours_lec }} Lec
              @if($course->contact_hours_lab) + {{ $course->contact_hours_lab }} Lab @endif
            </td>

            {{-- Actions --}}
            <td class="text-end">
              @if ($canManageCourses)
                <button type="button"
                        class="btn action-btn rounded-circle edit me-2 editCourseBtn"
                        data-bs-toggle="modal"
                        data-bs-target="#editCourseModal"
                        data-id="{{ $course->id }}"
                        title="Edit"
                        aria-label="Edit">
                  <i data-feather="edit"></i>
                </button>
              @endif
            </td>
          </tr>
        @empty
          <tr class="sv-empty-row">
            <td colspan="5">
              <div class="sv-empty">
                <h6>No courses found</h6>
                <p>Click the <i data-feather="plus"></i> button to add one.</p>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{-- ░░░ END: Table ░░░ --}}

</div>