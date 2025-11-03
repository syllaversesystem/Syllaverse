{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/courses/partials/courses-table.blade.php
* Description: Faculty • Courses Module – AJAX add/edit/delete with prerequisites
-------------------------------------------------------------------------------
📜 Log:
[2025-01-XX] Adapted from admin courses module for faculty use
[2025-01-XX] Updated to use faculty routes and permissions
[2025-01-XX] Modified role-based access control for faculty users
-------------------------------------------------------------------------------
--}}

<div class="courses-table-wrapper position-relative">

  {{-- ░░░ START: Toolbar ░░░ --}}
  <div class="courses-toolbar">
    <div class="input-group">
      <span class="input-group-text"><i data-feather="search"></i></span>
      <input type="search" class="form-control" placeholder="Search courses..." aria-label="Search courses" id="coursesSearch">
    </div>

    @if($showDepartmentFilter ?? false)
    <div class="department-filter-wrapper">
      <select class="form-select form-select-sm" id="departmentFilter" onchange="filterByDepartment(this.value)">
        <option value="all" {{ ($departmentFilter ?? 'all') == 'all' ? 'selected' : '' }}>All Departments</option>
        @foreach($departments as $department)
          <option value="{{ $department->id }}" {{ ($departmentFilter ?? '') == $department->id ? 'selected' : '' }}>
            {{ $department->code }}
          </option>
        @endforeach
      </select>
    </div>
    @endif

    <span class="flex-spacer"></span>

    @php
      $canManageCourses = Auth::user()->role === 'faculty' 
        || (method_exists(Auth::user(), 'isDeptChair') && Auth::user()->isDeptChair())
        || (method_exists(Auth::user(), 'isProgChair') && Auth::user()->isProgChair());
    @endphp

    @if ($canManageCourses)
      <button type="button"
              class="btn courses-add-btn d-none d-md-inline-flex"
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
  <input type="hidden" id="editCourseUpdateUrlBase" value="{{ url('/faculty/courses') }}"/>

  {{-- ░░░ START: Table ░░░ --}}
  <div class="table-responsive">
    <table class="table mb-0 courses-table" id="svCoursesTable">
      <thead>
        <tr>
          <th><i data-feather="type"></i> Name</th>
          <th><i data-feather="hash"></i> Code</th>
          @if(($showDepartmentColumn ?? true) && ($departmentFilter ?? 'all') == 'all')
            <th class="department-column"><i class="bi bi-building" style="vertical-align: baseline;"></i> Department</th>
          @endif
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
              data-course-category="{{ $course->course_category ?? '' }}"
              data-course-type="{{ $course->course_type ?? '' }}"
              data-has-iga="{{ $course->has_iga ? 'true' : 'false' }}"
              data-description="{{ $course->description }}"
              data-contact-hours-lec="{{ $course->contact_hours_lec }}"
              data-contact-hours-lab="{{ $course->contact_hours_lab }}"
              data-department-id="{{ $course->department_id ?? '' }}"
              data-department-name="{{ $course->department->name ?? '' }}"
              data-prereq='@json(($course->prerequisites ?? collect())->pluck("id"))'>
            <td class="course-title-cell">
              {{ $course->title }}
            </td>
            <td class="course-code-cell">{{ $course->code }}</td>
            @if(($showDepartmentColumn ?? true) && ($departmentFilter ?? 'all') == 'all')
              <td class="course-department-cell department-column" data-dept-code="{{ $course->department->code ?? 'N/A' }}">{{ $course->department->code ?? 'N/A' }}</td>
            @endif

            {{-- Prerequisites column --}}
            <td class="course-prerequisites-cell text-muted">
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
            <td class="course-contact-hours-cell text-muted">
              {{ $course->contact_hours_lec }} Lec
              @if($course->contact_hours_lab) + {{ $course->contact_hours_lab }} Lab @endif
              <span class="ms-1 text-secondary small">
                ({{ ($course->contact_hours_lec ?? 0) + ($course->contact_hours_lab ?? 0) }} hrs)
              </span>
            </td>

            {{-- ░░░ START: Actions (mirror Programs tab structure) ░░░ --}}
            <td class="course-actions-cell text-end">
              @if ($canManageCourses)
                {{-- Edit --}}
                <button type="button"
                        class="btn courses-action-btn edit-btn rounded-circle me-2 editCourseBtn"
                        data-bs-toggle="modal"
                        data-bs-target="#editCourseModal"
                        data-id="{{ $course->id }}"
                        data-code="{{ $course->code }}"
                        data-title="{{ $course->title }}"
                        data-description="{{ $course->description }}"
                        data-contact_hours_lec="{{ $course->contact_hours_lec }}"
                        data-contact_hours_lab="{{ $course->contact_hours_lab }}"
                        data-prereq='@json(($course->prerequisites ?? collect())->pluck("id"))'
                        data-action="edit-course"
                        title="Edit"
                        aria-label="Edit">
                  <i data-feather="edit"></i>
                </button>

                {{-- Delete (opens a confirm modal like Programs tab) --}}
                <button type="button"
                        class="btn courses-action-btn delete-btn rounded-circle deleteCourseBtn"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteCourseModal"
                        data-id="{{ $course->id }}"
                        data-code="{{ $course->code }}"
                        data-title="{{ $course->title }}"
                        data-action="delete-course"
                        title="Delete"
                        aria-label="Delete">
                  <i data-feather="trash"></i>
                </button>
              @else
                <button class="btn courses-action-btn disabled-btn rounded-circle me-2"
                        title="Edit disabled" aria-label="Edit disabled">
                  <i data-feather="lock"></i>
                </button>
                <button class="btn courses-action-btn disabled-btn rounded-circle"
                        title="Delete disabled" aria-label="Delete disabled">
                  <i data-feather="lock"></i>
                </button>
              @endif
            </td>
            {{-- ░░░ END: Actions ░░░ --}}
          </tr>
        @empty
          <tr class="courses-empty-row">
            <td colspan="{{ (($showDepartmentColumn ?? true) && ($departmentFilter ?? 'all') == 'all') ? '6' : '5' }}">
              <div class="courses-empty">
                <h6>No courses found</h6>
                @if ($canManageCourses)
                  <p>Click the <i data-feather="plus"></i> button to add one.</p>
                @endif
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{-- ░░░ END: Table ░░░ --}}
</div>

<script>
// Pass department filter state and permissions to JavaScript
window.coursesConfig = {
  departmentFilter: @json($departmentFilter ?? null),
  canManageCourses: @json($canManageCourses),
  showDepartmentColumn: @json($showDepartmentColumn ?? true)
};
</script>