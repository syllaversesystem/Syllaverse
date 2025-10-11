{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/master-data/tabs/ilo.blade.php
* Description: Intended Learning Outcomes (ILO) Tab – matches Program/Course table UI (with drag + Save Order)
-------------------------------------------------------------------------------
📜 Log:
[2025-08-18] Refactor – table columns now mirror Program/Course tabs (Code, Description, Created, Actions).
[2025-08-18] Kept drag-to-reorder via small grip in Code cell; Save Order uses ILO controller route.
-------------------------------------------------------------------------------
--}}

<div class="table-wrapper position-relative">

  {{-- ░░░ START: Course Filter ░░░ --}}
  <form id="iloFilterForm" method="GET" action="{{ route('admin.master-data.index') }}" class="mb-3">
    <input type="hidden" name="tab" value="soilo">
    <input type="hidden" name="subtab" value="ilo">

    {{-- Clean course selector --}}
    <div class="d-flex flex-wrap align-items-end gap-2 mb-2">
      <div class="form-floating" style="flex:1 1 320px; max-width:520px; min-width:260px;">
        <select id="iloCourseSelect" name="course_id" class="form-select" aria-label="Select course">
          <option value="" {{ request('course_id') ? '' : 'selected' }}>Select a course</option>
          @foreach ($courses as $course)
            <option value="{{ $course->id }}" {{ (string)request('course_id') === (string)$course->id ? 'selected' : '' }}>
              {{ $course->code }} — {{ $course->title }}
            </option>
          @endforeach
        </select>
        <label for="iloCourseSelect"><i data-feather="book-open" class="me-1"></i> Course</label>
      </div>
      <button type="button" class="btn btn-light border" id="iloFilterReset" title="Clear selection">Clear</button>
    </div>

    <div class="input-group input-group-sm" style="display:none; max-width:320px;">
      <span class="input-group-text"><i data-feather="book-open"></i></span>
      <select name="course_id" class="form-select form-select-sm">
        <option value="">Select a Course</option>
        @foreach ($courses as $course)
          <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
            {{ $course->code }} – {{ $course->title }}
          </option>
        @endforeach
      </select>
    </div>
  </form>
  {{-- ░░░ END: Course Filter ░░░ --}}

  @php /* always render table skeleton; JS will (re)build rows */ @endphp
    {{-- ░░░ START: Toolbar (aligned with Program/Course tabs) ░░░ --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <h6 class="mb-0 fw-semibold" style="font-size:.95rem;">Intended Learning Outcomes</h6>

      <div class="d-flex align-items-center gap-2">
        {{-- Save Order (wired to ILO controller) --}}
        <button type="button"
                class="btn btn-light btn-sm border rounded-pill sv-save-order-btn"
                data-sv-type="ilo"
                data-reorder-url="{{ route('admin.ilo.reorder') }}"
                disabled
                title="Save current order">
          <i data-feather="save"></i><span class="d-none d-md-inline ms-1">Save Order</span>
        </button>

        {{-- Add ILO --}}
        <button type="button"
                class="btn-brand-sm"
                data-bs-toggle="modal"
                data-bs-target="#addIloModal"
                title="Add ILO">
          <i data-feather="plus"></i>
        </button>
      </div>
    </div>
    {{-- ░░░ END: Toolbar ░░░ --}}

    {{-- ░░░ START: Table (mirrors Program/Course structure) ░░░ --}}
    <div class="table-responsive">
      <table class="table mb-0 sv-accounts-table" id="svTable-ilo" data-sv-type="ilo" data-course-id="{{ request('course_id') }}">
        <thead>
          <tr>
            <th></th>
            <th><i data-feather="code"></i> Code</th>
            <th><i data-feather="align-left"></i> Description</th>
            <th class="text-end"><i data-feather="more-vertical"></i></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($intendedLearningOutcomes->sortBy('position') as $ilo)
            <tr data-id="{{ $ilo->id }}">
              <td class="text-muted"><i class="sv-row-grip bi bi-grip-vertical fs-5" title="Drag to reorder"></i></td>
              <td class="sv-code fw-semibold">{{ $ilo->code }}</td>
              <td class="text-muted">{{ $ilo->description }}</td>
              <td class="text-end">
                {{-- Edit --}}
                <button type="button"
                        class="btn action-btn rounded-circle edit me-2 editIloBtn"
                        data-bs-toggle="modal"
                        data-bs-target="#editIloModal"
                        data-id="{{ $ilo->id }}"
                        data-code="{{ $ilo->code }}"
                        data-description="{{ $ilo->description }}"
                        title="Edit" aria-label="Edit">
                  <i data-feather="edit"></i>
                </button>

                {{-- Delete --}}
                <button type="button"
                        class="btn action-btn rounded-circle delete deleteIloBtn"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteIloModal"
                        data-id="{{ $ilo->id }}"
                        data-code="{{ $ilo->code }}"
                        title="Delete" aria-label="Delete">
                  <i data-feather="trash"></i>
                </button>
              </td>
            </tr>
          @empty
            <tr class="sv-empty-row">
              <td colspan="4">
                <div class="sv-empty">
                  <h6>No ILOs found</h6>
                  <p>Select a course and click the <i data-feather="plus"></i> button to add one.</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    {{-- ░░░ END: Table ░░░ --}}
  {{-- table always present; when no course is selected, tbody shows a placeholder row --}}
</div>

@push('scripts')
  @vite('resources/js/admin/master-data/ilo.js')
@endpush
