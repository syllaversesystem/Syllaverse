<div class="sdg-section" id="sdgSection">
  <div class="so-header mb-2" id="sdgHeader">
    <h5 class="mb-0 so-tab-title">Sustainable Development Goals</h5>
  </div>

  <div class="programs-toolbar" id="sdgToolbar">
    <div class="input-group">
      <span class="input-group-text" id="sdgSearchIcon"><i data-feather="search"></i></span>
      <input type="search" class="form-control" id="sdgSearch" placeholder="Search SDG..." aria-label="Search SDG" />
    </div>
    <span class="flex-spacer"></span>
    <button type="button" class="btn programs-add-btn d-none d-md-inline-flex" id="sdgAddBtn" data-bs-toggle="modal" data-bs-target="#addSdgModal" title="Add SDG" aria-label="Add SDG">
      <i data-feather="plus"></i>
    </button>
  </div>

  <div class="so-table-wrapper" id="sdgTableWrapper">
    <div class="table-responsive">
      <table class="table mb-0 align-middle so-table" id="sdgTable">
        <colgroup>
          <!-- Default Title column width set to 200px for consistent layout -->
          <col style="width:200px;" />
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
        <tbody id="sdgTableBody">
          <tr class="superadmin-manage-department-empty-row">
            <td colspan="3">
              <div class="empty-table">
                <h6>No SDGs found</h6>
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
  .so-header { margin: 1rem 0 1.5rem 1rem; }
  .so-tab-title { font-weight: 600; color: var(--sv-text, #333); font-family: 'Poppins', sans-serif; font-size: 0.9rem; line-height: 1.2; }
  .programs-toolbar { display: flex; align-items: center; flex-wrap: wrap; gap: 0.25rem; margin-bottom: 1.5rem; }
  .programs-toolbar .input-group { flex: 1; max-width: 320px; background: var(--sv-bg, #FAFAFA); border: 1px solid var(--sv-border, #E3E3E3); border-radius: 6px; overflow: hidden; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02); }
  .programs-toolbar .input-group .form-control { padding: 0.4rem 0.75rem; font-size: 0.88rem; border: none; background: transparent; height: 2.2rem; }
  .programs-toolbar .input-group .form-control::placeholder { color: var(--sv-text-muted, #666); }
  .programs-toolbar .input-group .form-control:focus { outline: none; box-shadow: none; background: transparent; }
  .programs-toolbar .input-group .input-group-text { background: transparent; border: none; padding-left: 0.7rem; padding-right: 0.4rem; display: flex; align-items: center; }
  .programs-toolbar .input-group-text i, .programs-toolbar .input-group-text svg { width: 0.95rem !important; height: 0.95rem !important; }
  .flex-spacer { flex: 1 1 auto; }
  .department-filter-wrapper { margin-left: 10px; margin-right: 10px; }
  .department-filter-wrapper .form-select { min-width: 200px; transition: transform 0.15s ease, box-shadow 0.2s ease, border-color 0.2s ease; }
  .department-filter-wrapper .form-select.is-loading { border-color: #007bff; box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25); cursor: progress; }
  .programs-add-btn { padding: 0; width: 2.75rem; height: 2.75rem; min-width: 2.75rem; min-height: 2.75rem; border-radius: 50%; display: inline-flex; justify-content: center; align-items: center; background: var(--sv-card-bg, #f8f9fa); border: none; transition: all 0.2s ease-in-out; box-shadow: none; color: #000; }
  .programs-add-btn i, .programs-add-btn svg { width: 1.25rem; height: 1.25rem; }
  .programs-add-btn:hover, .programs-add-btn:focus { background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46)); backdrop-filter: blur(7px); -webkit-backdrop-filter: blur(7px); box-shadow: 0 4px 10px rgba(204, 55, 55, 0.12); color: #CB3737; }
  #sdgTable thead th { font-weight: 600; color: var(--sv-text-muted, #666); }
  #sdgTable thead th i[data-feather], #sdgTable thead th svg[data-feather] { width: 1rem !important; height: 1rem !important; vertical-align: text-bottom; margin-right: 0.45rem; display: inline-block !important; stroke: var(--sv-text-muted, #666) !important; color: var(--sv-text-muted, #666) !important; }
  #sdgTable thead th i.bi { font-size: 1rem; line-height: 1; vertical-align: text-bottom; margin-right: 0.45rem; display: inline-block; color: var(--sv-text-muted, #666); }
  .spinner { animation: spin 1s linear infinite; }
  @keyframes spin { from { transform: rotate(0deg);} to { transform: rotate(360deg);} }
  .sdg-loading-row td { background-color: rgba(248,249,250,0.8); }
  #sdgTable { table-layout: auto; }
  #sdgTableWrapper, .so-table-wrapper { height: auto; }
  .so-table-wrapper .table-responsive { max-height: none; overflow-y: visible; }
  #sdgTable td.sdg-dept { white-space: nowrap; width: 1%; }
  /* Title column: compact with wrapping and 200px cap */
  #sdgTable td.sdg-title,
  #sdgTable td:first-child {
    color: #000 !important;
    white-space: normal;        /* allow wrapping */
    overflow-wrap: anywhere;    /* break long tokens */
    word-break: break-word;     /* legacy fallback */
    min-width: 200px;
    max-width: 200px;
  }
  #sdgTable td.sdg-desc-cell { white-space: normal; overflow-wrap: anywhere; word-break: break-word; }
  #sdgTable td.sdg-actions { white-space: nowrap; width: 1%; }
  #sdgTable tbody tr:hover, #sdgTable tbody tr:hover > * { background-color: transparent !important; }
  @media (max-width: 768px) { .programs-toolbar { gap: 0.5rem; } .programs-toolbar .input-group { max-width: 100%; } #sdgTable td.sdg-dept { white-space: normal; width: auto; } }
  .modal.modal-static .modal-dialog { transform: scale(1.02); transition: transform 0.2s ease-in-out; }
  .modal.modal-static .modal-content { box-shadow: 0 8px 24px rgba(0,0,0,.12), 0 4px 12px rgba(0,0,0,.08); }
</style>
@endpush
