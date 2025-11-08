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
    @if(!empty($departments) && ($departments->count() > 0))
      <div class="department-filter-wrapper">
        <select class="form-select form-select-sm" id="iloDepartmentFilter" aria-label="Filter ILO by department">
          <option value="all">All Departments</option>
          @foreach(($departments ?? collect()) as $dept)
            <option value="{{ $dept->id }}">{{ $dept->code }} — {{ $dept->name }}</option>
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
            <th scope="col"><i data-feather="type"></i> Title</th>
            <th scope="col"><i data-feather="file-text"></i> Description</th>
            <th scope="col" class="text-end"><i data-feather="more-vertical"></i></th>
          </tr>
        </thead>
        <tbody id="iloTableBody">
          <tr class="superadmin-manage-department-empty-row">
            <td colspan="3">
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
  #iloTable td.ilo-title { color:#000 !important; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; min-width:220px; max-width:480px; }
  #iloTable td.ilo-desc { white-space:normal; overflow-wrap:anywhere; word-break:break-word; }
  #iloTable td.ilo-actions { white-space:nowrap; width:1%; }
  #iloTable tbody tr:hover, #iloTable tbody tr:hover > * { background-color:transparent !important; }
  /* Department filter parity with SDG tab */
  .department-filter-wrapper { margin-left:10px; margin-right:10px; }
  .department-filter-wrapper .form-select {
    min-width:200px;
    transition: transform 0.15s ease, box-shadow 0.2s ease, border-color 0.2s ease;
  }
  .department-filter-wrapper .form-select.is-loading {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    cursor: progress;
  }
</style>
@endpush
