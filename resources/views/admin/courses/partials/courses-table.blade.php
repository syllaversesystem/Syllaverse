{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/courses/partials/courses-table.blade.php
* Description: Courses table with filtering and CRUD operations
-------------------------------------------------------------------------------
--}}

<div class="table-wrapper position-relative">

  {{-- ░░░ START: Toolbar Section ░░░ --}}
  <div class="superadmin-manage-department-toolbar">
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

    <button type="button"
            class="btn-brand-sm d-none d-md-inline-flex"
            data-bs-toggle="modal"
            data-bs-target="#addCourseModal"
            aria-label="Add Course"
            title="Add Course">
      <i data-feather="plus"></i>
    </button>
  </div>
  {{-- ░░░ END: Toolbar Section ░░░ --}}

  {{-- ░░░ START: Alerts Section ░░░ --}}
  <div id="courseAlerts" class="mb-2"></div>
  {{-- ░░░ END: Alerts Section ░░░ --}}

  {{-- Helper for JS update/delete base URL --}}
  <input type="hidden" id="editCourseUpdateUrlBase" value="{{ url('/admin/courses') }}"/>

  {{-- ░░░ START: Table ░░░ --}}
  <div class="table-responsive">
    <table class="table mb-0 sv-accounts-table" id="svCoursesTable">
      <thead>
        <tr>
          <th><i data-feather="hash"></i> Code</th>
          <th><i data-feather="book"></i> Title</th>
          @if(($departmentFilter ?? 'all') == 'all')
            <th><i data-feather="layers"></i> Department</th>
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
              data-description="{{ $course->description }}"
              data-contact-hours-lec="{{ $course->contact_hours_lec }}"
              data-contact-hours-lab="{{ $course->contact_hours_lab }}"
              data-department-id="{{ $course->department_id }}"
              data-prereq='@json(($course->prerequisites ?? collect())->pluck("id"))'>
            <td class="fw-semibold">{{ $course->code }}</td>
            <td class="fw-medium">{{ $course->title }}</td>

            @if(($departmentFilter ?? 'all') == 'all')
              <td>{{ $course->department->code ?? 'N/A' }}</td>
            @endif

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
              <button type="button"
                      class="btn action-btn rounded-circle edit me-2 editCourseBtn"
                      data-bs-toggle="modal"
                      data-bs-target="#editCourseModal"
                      data-id="{{ $course->id }}"
                      title="Edit"
                      aria-label="Edit">
                <i data-feather="edit"></i>
              </button>

              <button type="button"
                      class="btn action-btn rounded-circle delete deleteCourseBtn"
                      data-bs-toggle="modal"
                      data-bs-target="#deleteCourseModal"
                      data-id="{{ $course->id }}"
                      data-title="{{ $course->title }}"
                      title="Delete"
                      aria-label="Delete">
                <i data-feather="trash"></i>
              </button>
            </td>
          </tr>
        @empty
          <tr class="sv-empty-row">
            <td colspan="{{ ($departmentFilter ?? 'all') == 'all' ? '6' : '5' }}">
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