<div class="cdio-section" id="cdioSection">
  <!-- Header / Title -->
  <div class="so-header mb-2" id="cdioHeader">
    <h5 class="mb-0 so-tab-title">Conceive Design Implement Operate</h5>
  </div>

  <!-- Unified toolbar (matches SDG / SO / IGA styling) -->
  <div class="programs-toolbar" id="cdioToolbar">
    <div class="input-group">
      <span class="input-group-text" id="cdioSearchIcon"><i data-feather="search"></i></span>
      <input type="search" class="form-control" id="cdioSearch" placeholder="Search CDIO..." aria-label="Search CDIO" />
    </div>
    <span class="flex-spacer"></span>
    <button type="button"
            class="btn programs-add-btn d-none d-md-inline-flex"
            id="cdioAddBtn"
            data-bs-toggle="modal"
            data-bs-target="#addCdioModal"
            title="Add CDIO"
            aria-label="Add CDIO">
      <i data-feather="plus"></i>
    </button>
  </div>

  <!-- Table wrapper -->
  <div class="so-table-wrapper" id="cdioTableWrapper">
    <div class="table-responsive">
      <table class="table mb-0 align-middle so-table" id="cdioTable">
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
        <tbody id="cdioTableBody">
          <tr class="superadmin-manage-department-empty-row">
            <td colspan="3">
              <div class="empty-table">
                <h6>No CDIO items found</h6>
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
  /* Reuse foundational header + toolbar styles used by other master-data tabs */
  .so-header { margin: 1rem 0 1.5rem 1rem; }
  .so-tab-title { font-weight: 600; color: var(--sv-text,#333); font-family: 'Poppins', sans-serif; font-size: .9rem; line-height:1.2; }
  .programs-toolbar { display:flex; align-items:center; flex-wrap:wrap; gap:.25rem; margin-bottom:1.5rem; }
  .programs-toolbar .input-group { flex:1; max-width:320px; background: var(--sv-bg,#FAFAFA); border:1px solid var(--sv-border,#E3E3E3); border-radius:6px; overflow:hidden; box-shadow:0 1px 2px rgba(0,0,0,.02); }
  .programs-toolbar .input-group .form-control { padding:.4rem .75rem; font-size:.88rem; border:none; background:transparent; height:2.2rem; }
  .programs-toolbar .input-group .form-control:focus { outline:none; box-shadow:none; }
  .programs-toolbar .input-group .input-group-text { background:transparent; border:none; padding-left:.7rem; padding-right:.4rem; display:flex; align-items:center; }
  .programs-toolbar .input-group-text i, .programs-toolbar .input-group-text svg { width:.95rem !important; height:.95rem !important; }
  .flex-spacer { flex:1 1 auto; }

  /* Add button (shared) */
  .programs-add-btn { padding:0; width:2.75rem; height:2.75rem; min-width:2.75rem; min-height:2.75rem; border-radius:50%; display:inline-flex; justify-content:center; align-items:center; background: var(--sv-card-bg,#f8f9fa); border:none; transition:all .2s ease-in-out; box-shadow:none; color:#000; }
  .programs-add-btn i, .programs-add-btn svg { width:1.25rem; height:1.25rem; }
  .programs-add-btn:hover, .programs-add-btn:focus { background:linear-gradient(135deg, rgba(255,240,235,.88), rgba(255,255,255,.46)); backdrop-filter:blur(7px); -webkit-backdrop-filter:blur(7px); box-shadow:0 4px 10px rgba(204,55,55,.12); color:#CB3737; }

  /* Table header styling */
  #cdioTable thead th { font-weight:600; color: var(--sv-text-muted,#666); }
  #cdioTable thead th i[data-feather], #cdioTable thead th svg[data-feather] { width:1rem !important; height:1rem !important; vertical-align:text-bottom; margin-right:.45rem; display:inline-block !important; stroke: var(--sv-text-muted,#666) !important; color: var(--sv-text-muted,#666) !important; }

  /* Title column: compact, ellipsis if needed */
  #cdioTable td.cdio-title { color:#000 !important; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; min-width:220px; max-width:480px; }
  /* Description fills remaining space */
  #cdioTable td.cdio-desc { white-space:normal; overflow-wrap:anywhere; word-break:break-word; }
  #cdioTable td.cdio-actions { white-space:nowrap; width:1%; }
  #cdioTable tbody tr:hover, #cdioTable tbody tr:hover > * { background-color:transparent !important; }

  /* Use the same global .action-btn look as SDG by not overriding here */

  /* Loading visual */
  .spinner { animation: spin 1s linear infinite; }
  @keyframes spin { from { transform:rotate(0deg);} to { transform:rotate(360deg);} }
  .cdio-loading-row td { background-color:rgba(248,249,250,.8); }

  /* Modal static bounce */
  .modal.modal-static .modal-dialog { transform:scale(1.02); transition:transform .2s ease-in-out; }
  .modal.modal-static .modal-content { box-shadow:0 8px 24px rgba(0,0,0,.12), 0 4px 12px rgba(0,0,0,.08); }

  @media (max-width:768px) { .programs-toolbar { gap:.5rem; } .programs-toolbar .input-group { max-width:100%; } }
</style>
@endpush
