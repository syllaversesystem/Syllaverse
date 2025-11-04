<div class="so-section" id="soSection">
  <!-- Tab Title -->
  <div class="so-header mb-2" id="soHeader">
    <h5 class="mb-0 so-tab-title">Student Outcomes</h5>
  </div>

  <div class="programs-toolbar" id="soToolbar">
    <div class="input-group">
      <span class="input-group-text" id="soSearchIcon"><i data-feather="search"></i></span>
      <input type="search" class="form-control" id="soSearch" placeholder="Search SO..." aria-label="Search SO" />
    </div>

    @if(!empty($showDepartmentFilter))
      <div class="department-filter-wrapper" id="soDepartmentFilterWrapper">
        <select class="form-select form-select-sm" id="soDepartmentFilter" aria-label="Filter by department">
          <option value="all">All Departments</option>
          @foreach(($departments ?? collect()) as $dept)
            <option value="{{ $dept->id }}">{{ $dept->code }}</option>
          @endforeach
        </select>
      </div>
    @endif

    <span class="flex-spacer"></span>

    <button type="button" class="btn programs-add-btn d-none d-md-inline-flex" id="soAddBtn" data-bs-toggle="modal" data-bs-target="#addSoModal" title="Add Student Outcome" aria-label="Add Student Outcome">
      <i data-feather="plus"></i>
    </button>
  </div>

  <div class="so-table-wrapper" id="soTableWrapper">
    <div class="table-responsive">
      <table class="table mb-0 align-middle so-table" id="soTable" data-role-can-see-dept-col="{{ !empty($showDepartmentFilter) ? '1' : '0' }}">
        <colgroup>
          @if(!empty($showDepartmentFilter))
            <col style="width:24%;" />
            <col style="width:1%;" />
            <col />
            <col style="width:1%;" />
          @else
            <col style="width:28%;" />
            <col />
            <col style="width:1%;" />
          @endif
        </colgroup>
        <thead>
          <tr>
            <th scope="col"><i data-feather="type"></i> Title</th>
            @if(!empty($showDepartmentFilter))
              <th scope="col" class="th-dept"><i class="bi bi-building"></i> Department</th>
            @endif
            <th scope="col"><i data-feather="file-text"></i> Description</th>
            <th scope="col" class="text-end"><i data-feather="more-vertical"></i></th>
          </tr>
        </thead>
        <tbody id="soTableBody">
          <tr class="superadmin-manage-department-empty-row">
            <td colspan="{{ !empty($showDepartmentFilter) ? 4 : 3 }}">
              <div class="empty-table">
                <h6>No student outcomes found</h6>
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
  /* Tab title area */
  .so-header { margin: 1rem 0 1.5rem 1rem; }
  .so-tab-title {
    font-weight: 600;
    color: var(--sv-text, #333);
    font-family: 'Poppins', sans-serif;
    font-size: 0.9rem; /* decreased title size */
    line-height: 1.2;
  }

  /* Programs-like toolbar styling (mirrors faculty programs module) */
  .programs-toolbar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.25rem;
    margin-bottom: 1.5rem;
  }
  .programs-toolbar .input-group {
    flex: 1;
    max-width: 320px;
    background: var(--sv-bg, #FAFAFA);
    border: 1px solid var(--sv-border, #E3E3E3);
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
  }
  .programs-toolbar .input-group .form-control {
    padding: 0.4rem 0.75rem;
    font-size: 0.88rem;
    border: none;
    background: transparent;
    height: 2.2rem;
  }
  .programs-toolbar .input-group .form-control::placeholder { color: var(--sv-text-muted, #666); }
  .programs-toolbar .input-group .form-control:focus { outline: none; box-shadow: none; background: transparent; }
  .programs-toolbar .input-group .input-group-text {
    background: transparent;
    border: none;
    padding-left: 0.7rem;
    padding-right: 0.4rem;
    display: flex;
    align-items: center;
  }
  .programs-toolbar .input-group-text i,
  .programs-toolbar .input-group-text svg { width: 0.95rem !important; height: 0.95rem !important; }
  .flex-spacer { flex: 1 1 auto; }
  .department-filter-wrapper { margin-left: 10px; margin-right: 10px; }
  .department-filter-wrapper .form-select { min-width: 200px; transition: transform 0.15s ease, box-shadow 0.2s ease, border-color 0.2s ease; }
  /* Loading visual state for filter (match Programs focus color) */
  .department-filter-wrapper .form-select.is-loading {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    cursor: progress;
  }

  /* Add button style to match programs module */
  .programs-add-btn {
    padding: 0;
    width: 2.75rem;
    height: 2.75rem;
    min-width: 2.75rem;
    min-height: 2.75rem;
    border-radius: 50%;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    background: var(--sv-card-bg, #f8f9fa);
    border: none;
    transition: all 0.2s ease-in-out;
    box-shadow: none;
    color: #000;
  }
  .programs-add-btn i, .programs-add-btn svg { width: 1.25rem; height: 1.25rem; }
  .programs-add-btn:hover, .programs-add-btn:focus {
    background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46));
    backdrop-filter: blur(7px);
    -webkit-backdrop-filter: blur(7px);
    box-shadow: 0 4px 10px rgba(204, 55, 55, 0.12);
    color: #CB3737;
  }

  /* Table header icon alignment (match Programs) */
  #soTable thead th { font-weight: 600; color: var(--sv-text-muted, #666); }
  #soTable thead th i[data-feather],
  #soTable thead th svg[data-feather] {
    width: 1rem !important;
    height: 1rem !important;
    vertical-align: text-bottom;
    margin-right: 0.45rem;
    display: inline-block !important;
    stroke: var(--sv-text-muted, #666) !important;
    color: var(--sv-text-muted, #666) !important;
  }
  #soTable thead th i.bi {
    font-size: 1rem;
    line-height: 1;
    vertical-align: text-bottom;
    margin-right: 0.45rem;
    display: inline-block;
    color: var(--sv-text-muted, #666);
  }

  /* Loading spinner and row (mirror Programs) */
  .spinner { animation: spin 1s linear infinite; }
  @keyframes spin { from { transform: rotate(0deg);} to { transform: rotate(360deg);} }
  .so-loading-row td { background-color: rgba(248,249,250,0.8); }

  /* Ensure long descriptions wrap and rows grow to fit content */
  #soTable { table-layout: auto; }
  /* Ensure table wrapper height fits content without extra scrollbars */
  #soTableWrapper, .so-table-wrapper { height: auto; }
  .so-table-wrapper .table-responsive { max-height: none; overflow-y: visible; }
  /* Make Department column compact */
  #soTable td.so-dept { white-space: nowrap; width: 1%; }
  /* Title column: keep compact and ellipsize long titles */
  #soTable td.so-title { color: #000 !important; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  #soTable td.so-desc-cell { white-space: normal; overflow-wrap: anywhere; word-break: break-word; }
  #soTable td.so-actions { white-space: nowrap; width: 1%; }
  /* Remove any residual hover background from external styles */
  #soTable tbody tr:hover, #soTable tbody tr:hover > * { background-color: transparent !important; }
  /* On small screens, allow department to wrap too */
  @media (max-width: 768px) {
    .programs-toolbar { gap: 0.5rem; }
    .programs-toolbar .input-group { max-width: 100%; }
    #soTable td.so-dept { white-space: normal; width: auto; }
  }

  /* Backdrop click restriction animation (Bootstrap applies .modal-static on prevent) */
  .modal.modal-static .modal-dialog {
    transform: scale(1.02);
    transition: transform 0.2s ease-in-out;
  }
  .modal.modal-static .modal-content {
    box-shadow: 0 8px 24px rgba(0,0,0,.12), 0 4px 12px rgba(0,0,0,.08);
  }
</style>
@endpush
