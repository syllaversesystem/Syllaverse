{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/so.blade.php
* Description: CIS-style SO layout with drag-safe table structure – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-07-29] Updated layout to match ILO format (flat rows, sortable-safe, delete button included).
-------------------------------------------------------------------------------
--}}

<form id="soForm" method="POST" action="{{ route('faculty.syllabi.sos.update', $default['id']) }}">
  @csrf
  @method('PUT')

  <table class="table table-bordered mb-4" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.4;">
    <colgroup>
      <col style="width: 10%;">
      <col style="width: 5%;">
      <col style="width: 85%;">
    </colgroup>
    <thead>
      <tr>
        <th colspan="3" class="text-start fw-bold">Student Outcomes (SO)</th>
      </tr>
    </thead>
    <tbody id="syllabus-so-sortable" data-syllabus-id="{{ $default['id'] }}">
      @forelse ($sos->sortBy('position') as $index => $so)
        <tr data-id="{{ $so->id }}">
          <td class="text-center align-middle fw-bold">
            {{ $so->code ?? "SO" . ($index + 1) }}
          </td>
          <td class="text-center align-middle">
            <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;">
              <i class="bi bi-grip-vertical"></i>
            </span>
          </td>
          <td>
            <div class="d-flex align-items-start gap-2">
              <textarea 
                name="sos[]" 
                class="form-control border-0 p-0 bg-transparent"
                style="min-height: 60px; flex: 1;"
                required>{{ old("sos.$index", $so->description) }}</textarea>
              <input type="hidden" name="code[]" value="{{ $so->code }}">
              <button type="button" 
                      class="btn btn-sm btn-outline-danger btn-delete-so mt-1" 
                      title="Delete SO">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="3" class="text-center text-muted">No SOs found.</td></tr>
      @endforelse
    </tbody>
  </table>

  {{-- ░░░ START: SO Action Buttons ░░░ --}}
  <div class="d-flex gap-2">
    <button type="button" class="btn btn-outline-secondary btn-sm" id="add-so-row">➕ Add Row</button>
    <button type="button" class="btn btn-outline-danger btn-sm" id="save-syllabus-so-order">Save Order</button>
    <button type="submit" class="btn btn-danger btn-sm ms-auto">💾 Save All</button>
  </div>
  {{-- ░░░ END: SO Action Buttons ░░░ --}}
</form>

@push('scripts')
  @vite([
    'resources/js/faculty/syllabus-so.js',
    'resources/js/faculty/syllabus-so-sortable.js'
  ])
@endpush


