{{-- 
-------------------------------------------------------------------------------
* File: resources/views/superadmin/master-data/tabs/assessment-tasks.blade.php
* Description: Assessment Tasks module – LEC/LAB subtabs with CRUD (no description, no drag)
-------------------------------------------------------------------------------
📜 Log:
[2025-08-17] Initial creation – Lecture/Laboratory subtabs, tables, and scoped modals (Add/Edit/Delete).
[2025-08-17] Update – Removed drag-to-reorder UI and related controls (no grips, no "Save Order" button).
[2025-08-17] Update – Removed Description column and all description inputs from forms.
[2025-08-17] Fix – Moved modals into @push('modals') so they render at <body> level (no backdrop overlap).
-------------------------------------------------------------------------------
--}}

@php
  // Prefer passing $taskGroups from controller:
  // AssessmentTaskGroup::with(['tasks' => fn($q)=>$q->orderBy('sort_order')->orderBy('id')])->ordered()->get()
  $taskGroups = $taskGroups 
    ?? \App\Models\AssessmentTaskGroup::with(['tasks' => fn($q) => $q->orderBy('sort_order')->orderBy('id')])
        ->orderBy('sort_order')->orderBy('id')->get();
@endphp

{{-- ░░░ START: Subtabs (Lecture / Laboratory) ░░░ --}}
<ul class="nav mb-4" id="assessmentTaskSubTabs" role="tablist">
  @foreach ($taskGroups as $g)
    <li class="nav-item" role="presentation">
      <button class="nav-link sv-subtab @if ($loop->first) active @endif"
              id="at-{{ strtolower($g->code) }}-tab"
              data-bs-toggle="pill"
              data-bs-target="#at-{{ strtolower($g->code) }}"
              type="button"
              role="tab">
        {{ $g->title }}
      </button>
    </li>
  @endforeach
</ul>
{{-- ░░░ END: Subtabs ░░░ --}}

{{-- ░░░ START: Subtab Content ░░░ --}}
<div class="tab-content" id="assessment-tasks">
  @foreach ($taskGroups as $g)
    @php
      $paneId  = 'at-'.strtolower($g->code);
      $tableId = 'svTable-at-'.strtolower($g->code);
    @endphp

    <div class="tab-pane fade @if ($loop->first) show active @endif"
         id="{{ $paneId }}" role="tabpanel" aria-labelledby="{{ $paneId }}-tab">

      {{-- ░░░ START: Toolbar Section (Add only) ░░░ --}}
      <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
        <h6 class="mb-0 fw-semibold" style="font-size:.95rem;">
          Assessment Tasks – {{ $g->title }}
        </h6>

        <div class="d-flex align-items-center gap-2">
          {{-- Add (icon-only circular) --}}
          <button type="button"
                  class="btn-brand-sm"
                  data-bs-toggle="modal"
                  data-bs-target="#addAssessmentTaskModal"
                  data-group-id="{{ $g->id }}"
                  data-group-title="{{ $g->title }}"
                  aria-label="Add Task"
                  title="Add Task">
            <i data-feather="plus"></i>
          </button>
        </div>
      </div>
      {{-- ░░░ END: Toolbar Section ░░░ --}}

      {{-- ░░░ START: Table Wrapper (Columns: Code, Title, Actions) ░░░ --}}
      <div class="table-wrapper position-relative">
        <div class="table-responsive">
          <table class="table mb-0 at-table" id="{{ $tableId }}" data-group-id="{{ $g->id }}">
            <colgroup>
              <col style="width:110px;">  {{-- code --}}
              <col>                        {{-- title --}}
              <col style="width:140px;">  {{-- actions --}}
            </colgroup>

            <thead>
              <tr>
                <th><i data-feather="code"></i> Code</th>
                <th><i data-feather="type"></i> Title</th>
                <th class="text-end"><i data-feather="more-vertical"></i></th>
              </tr>
            </thead>

            <tbody>
              @forelse ($g->tasks as $task)
                <tr data-id="{{ $task->id }}">
                  {{-- Code --}}
                  <td class="sv-code fw-semibold">{{ $task->code }}</td>

                  {{-- Title --}}
                  <td class="fw-medium">{{ $task->title }}</td>

                  {{-- Actions --}}
                  <td class="text-end">
                    <button type="button"
                            class="btn action-btn rounded-circle edit me-2"
                            data-bs-toggle="modal"
                            data-bs-target="#editAssessmentTaskModal"
                            data-id="{{ $task->id }}"
                            data-group-id="{{ $g->id }}"
                            data-code="{{ $task->code }}"
                            data-title="{{ $task->title }}"
                            title="Edit" aria-label="Edit">
                      <i data-feather="edit"></i>
                    </button>

                    <button type="button"
                            class="btn action-btn rounded-circle delete"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteAssessmentTaskModal"
                            data-id="{{ $task->id }}"
                            data-label="{{ $task->code }} — {{ $task->title }}"
                            title="Delete" aria-label="Delete">
                      <i data-feather="trash"></i>
                    </button>
                  </td>
                </tr>
              @empty
                <tr class="sv-empty-row">
                  <td colspan="3">
                    <div class="sv-empty">
                      <h6>No tasks in {{ $g->title }}</h6>
                      <p>Click the <i data-feather="plus"></i> button to add one.</p>
                    </div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      {{-- ░░░ END: Table Wrapper ░░░ --}}

    </div>
  @endforeach
</div>
{{-- ░░░ END: Subtab Content ░░░ --}}

{{-- ░░░ START: Modals (pushed to <body>) ░░░ --}}
@push('modals')
  {{-- Add Modal --}}
  <div class="modal fade" id="addAssessmentTaskModal" tabindex="-1" aria-labelledby="addAssessmentTaskLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form class="modal-content needs-validation" novalidate
            id="addAssessmentTaskForm"
            action="{{ route('superadmin.master-data.store', ['type' => 'assessment-task']) }}"
            method="post">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="addAssessmentTaskLabel">Add Assessment Task</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="group_id" id="add_at_group_id">

          <div class="mb-3">
            <label class="form-label">Group</label>
            <input type="text" class="form-control" id="add_at_group_title" readonly>
          </div>

          <div class="mb-3">
            <label class="form-label">Code <small class="text-muted">(e.g., ME, FE)</small></label>
            <input type="text" class="form-control" name="code" maxlength="16" required>
            <div class="invalid-feedback">Please enter a unique code.</div>
          </div>

          <div class="mb-0">
            <label class="form-label">Title</label>
            <input type="text" class="form-control" name="title" maxlength="150" required>
            <div class="invalid-feedback">Please enter a title.</div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">
            <i data-feather="plus"></i> <span class="ms-1">Add Task</span>
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Edit Modal --}}
  <div class="modal fade" id="editAssessmentTaskModal" tabindex="-1" aria-labelledby="editAssessmentTaskLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form class="modal-content needs-validation" novalidate
            id="editAssessmentTaskForm"
            method="post">
        @csrf
        @method('PUT')

        <div class="modal-header">
          <h5 class="modal-title" id="editAssessmentTaskLabel">Edit Assessment Task</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="group_id" id="edit_at_group_id">

          <div class="mb-3">
            <label class="form-label">Code</label>
            <input type="text" class="form-control" name="code" id="edit_at_code" maxlength="16" required>
          </div>

          <div class="mb-0">
            <label class="form-label">Title</label>
            <input type="text" class="form-control" name="title" id="edit_at_title" maxlength="150" required>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">
            <i data-feather="save"></i> <span class="ms-1">Save Changes</span>
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Delete Modal --}}
  <div class="modal fade" id="deleteAssessmentTaskModal" tabindex="-1" aria-labelledby="deleteAssessmentTaskLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form class="modal-content"
            id="deleteAssessmentTaskForm"
            method="post">
        @csrf
        @method('DELETE')

        <div class="modal-header">
          <h5 class="modal-title" id="deleteAssessmentTaskLabel">Delete Task</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <p class="mb-0">Are you sure you want to delete <span class="fw-semibold" id="delete_at_label">this task</span>?</p>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-danger">
            <i data-feather="trash-2"></i> <span class="ms-1">Delete</span>
          </button>
        </div>
      </form>
    </div>
  </div>
@endpush
{{-- ░░░ END: Modals (pushed to <body>) ░░░ --}}
