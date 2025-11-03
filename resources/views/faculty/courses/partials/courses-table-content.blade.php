{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/courses/partials/courses-table-content.blade.php  
* Description: Courses table tbody content for AJAX loading
-------------------------------------------------------------------------------
📜 Log:
[2025-11-04] Created partial for AJAX department filtering
-------------------------------------------------------------------------------
--}}

@forelse($courses as $course)
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

    {{-- Actions --}}
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

        {{-- Delete --}}
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