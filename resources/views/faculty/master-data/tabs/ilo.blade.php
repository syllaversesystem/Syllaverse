<div class="ilo-section" id="iloSection">
  <!-- Header / Title -->
  <div class="so-header mb-2" id="iloHeader">
    <h5 class="mb-0 so-tab-title">Intended Learning Outcomes</h5>
  </div>

  <!-- Unified toolbar (search + optional department filter + add button) -->
  <div class="programs-toolbar" id="iloToolbar">
    <div class="input-group">
      <span class="input-group-text" id="iloSearchIcon"><i data-feather="search"></i></span>
      <input type="search" class="form-control" id="iloSearch" placeholder="Search ILO..." aria-label="Search ILO" />
    </div>
    @if(!empty($courses) && ($courses->count() > 0))
      <div class="course-filter-wrapper">
        <select class="form-select form-select-sm" id="iloCourseFilter" aria-label="Filter ILO by course">
          <option value="">Select course</option>
          @foreach(($courses ?? collect()) as $course)
            <option value="{{ $course->id }}" data-dept-id="{{ $course->department_id }}">{{ $course->code }}</option>
          @endforeach
        </select>
      </div>
    @endif
    <span class="flex-spacer"></span>
    <button type="button"
            class="btn programs-add-btn d-none d-md-inline-flex"
            id="iloAddBtn"
            data-bs-toggle="modal"
            data-bs-target="#addIloModal"
            title="Add ILO"
            aria-label="Add ILO">
      <i data-feather="plus"></i>
    </button>
  </div>

  <!-- Table wrapper -->
  <div class="so-table-wrapper" id="iloTableWrapper">
    <div class="table-responsive">
      <table class="table mb-0 align-middle so-table" id="iloTable">
        <colgroup>
          <col style="width:1%;" />
          <col />
          <col style="width:1%;" />
        </colgroup>
        <thead>
          <tr>
            <th scope="col" style="width:1%;">
              <svg class="grip-icon" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                <g fill="currentColor">
                  <circle cx="4" cy="4" r="1" />
                  <circle cx="12" cy="4" r="1" />
                  <circle cx="4" cy="8" r="1" />
                  <circle cx="12" cy="8" r="1" />
                  <circle cx="4" cy="12" r="1" />
                  <circle cx="12" cy="12" r="1" />
                </g>
              </svg>
            </th>
            <th scope="col"><i data-feather="hash"></i> Code</th>
            <th scope="col"><i data-feather="file-text"></i> Description</th>
            <th scope="col" class="text-end"><i data-feather="more-vertical"></i></th>
          </tr>
        </thead>
        <tbody id="iloTableBody">
          <tr class="superadmin-manage-department-empty-row">
            <td colspan="4">
              <div class="empty-table">
                <h6>No ILOs found</h6>
                <p>Click the <i data-feather="plus"></i> button to add one.</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

@push('styles')
<style>
  /* Reuse common master-data styles already defined in other tabs. Add only ILO-specific tweaks as needed. */
  #iloTable thead th { font-weight:600; color: var(--sv-text-muted,#666); }
  #iloTable thead th i[data-feather], #iloTable thead th svg[data-feather] { width:1rem !important; height:1rem !important; vertical-align:text-bottom; margin-right:.45rem; stroke: var(--sv-text-muted,#666) !important; }
  #iloTable td.ilo-code { color:#000 !important; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; min-width:140px; max-width:260px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
  #iloTable td.ilo-desc { white-space:normal; overflow-wrap:anywhere; word-break:break-word; }
  #iloTable td.ilo-actions { white-space:nowrap; width:1%; }
  #iloTable td.ilo-drag { cursor: grab; width:1%; white-space:nowrap; }
  #iloTable tr.dragging { opacity: .6; }
  #iloTable tbody tr:hover, #iloTable tbody tr:hover > * { background-color:transparent !important; }
  /* Department filter removed */
  /* Course filter styling mirrors department filter */
  .course-filter-wrapper { margin-left:10px; margin-right:10px; }
  .course-filter-wrapper .form-select {
    min-width:200px;
    transition: transform 0.15s ease, box-shadow 0.2s ease, border-color 0.2s ease;
  }
  .course-filter-wrapper .form-select.is-loading {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    cursor: progress;
  }
  /* Department filter removed */
  /* Grip icon styling */
  .grip-icon { width:16px; height:16px; display:inline-block; opacity:.6; }
  .ilo-drag .grip-icon { width:14px; height:14px; }
  .ilo-drag:hover .grip-icon { opacity:.9; }
</style>
@endpush
