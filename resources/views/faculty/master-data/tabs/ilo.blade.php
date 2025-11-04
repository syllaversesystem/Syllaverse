<div class="ilo-section" id="iloSection">
  <form id="iloFilterForm" method="GET" action="{{ route('faculty.master-data.index') }}" class="mb-3">
    <div class="row g-2 align-items-center">
      <div class="col-auto"><strong>ILO</strong></div>
      <div class="col-auto">
        <select class="form-select" id="iloDepartmentFilter" aria-label="Filter ILO by department">
          <option value="all">All Departments</option>
          @foreach(($departments ?? collect()) as $dept)
            <option value="{{ $dept->id }}">{{ $dept->code }} — {{ $dept->name }}</option>
          @endforeach
        </select>
      </div>
    </div>
  </form>
  <div class="table-responsive">
    <table class="table table-hover align-middle" id="iloTable">
      <thead>
        <tr>
          <th scope="col">Code</th>
          <th scope="col">Title</th>
          <th scope="col">Description</th>
          <th scope="col" class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody id="iloTableBody">
        <tr><td colspan="4" class="text-center text-muted py-4">ILO master list is based on per-syllabus ILOs. Coming soon.</td></tr>
      </tbody>
    </table>
  </div>
</div>
