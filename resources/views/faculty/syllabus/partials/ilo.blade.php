{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/ilo.blade.php
* Description: CIS-style ILO layout with drag-safe structure – no rowspan – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-07-29] Aligned layout with SO structure.
[2025-07-29] Fixed broken drag-reorder issue by removing rowspan and using a static header row.
-------------------------------------------------------------------------------
--}}

<form id="iloForm" method="POST" action="{{ route('faculty.syllabi.ilos.update', $default['id']) }}">
  @csrf
  @method('PUT')

  <table class="table table-bordered mb-4" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.4;">
    <style>
      .cis-input { font-weight: 400; font-size: 0.93rem; line-height: 1.15; font-family: inherit; }
    </style>
    <colgroup>
      <col style="width: 10%;">
      <col style="width: 5%;">
      <col style="width: 85%;">
    </colgroup>
    <thead>
      <tr>
        <th colspan="3" class="text-start fw-bold">Intended Learning Outcomes (ILO)</th>
      </tr>
    </thead>
    <tbody id="syllabus-ilo-sortable" data-syllabus-id="{{ $default['id'] }}">
      @forelse ($ilos->sortBy('position') as $index => $ilo)
        <tr data-id="{{ $ilo->id }}">
          <td class="text-center align-middle fw-bold">
            {{ $ilo->code ?? "ILO" . ($index + 1) }}
          </td>
          <td class="text-center align-middle">
            <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;">
              <i class="bi bi-grip-vertical"></i>
            </span>
          </td>
          <td>
            <div class="d-flex align-items-start gap-2">
              <textarea 
                name="ilos[]" 
                class="form-control border-0 p-0 bg-transparent"
                style="min-height: 60px; flex: 1;"
                required>{{ old("ilos.$index", $ilo->description) }}</textarea>
              <input type="hidden" name="code[]" value="{{ $ilo->code }}">
              <button type="button" 
                      class="btn btn-sm btn-outline-danger btn-delete-ilo mt-1" 
                      title="Delete ILO">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="3" class="text-center text-muted">No ILOs found.</td></tr>
      @endforelse
    </tbody>
  </table>

  {{-- ░░░ START: ILO Action Buttons ░░░ --}}
  <div class="d-flex gap-2">
    <button type="button" class="btn btn-outline-secondary btn-sm" id="add-ilo-row">➕ Add Row</button>
    <button type="button" class="btn btn-outline-danger btn-sm" id="save-syllabus-ilo-order">Save Order</button>
    <button type="submit" class="btn btn-danger btn-sm ms-auto">💾 Save All</button>
  </div>
  {{-- ░░░ END: ILO Action Buttons ░░░ --}}
</form>

@push('scripts')
  @vite('resources/js/faculty/syllabus-ilo-sortable.js')
@endpush
  