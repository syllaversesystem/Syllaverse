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

  @php
    $ilosSorted = $ilos->sortBy('position')->values();
    $iloCount = max(1, $ilosSorted->count());
  @endphp

  <style>
    /* keep title typography consistent with other CIS modules */
    .ilo-left-title { font-weight: 700; padding: 0.75rem; font-family: Georgia, serif; vertical-align: top; box-sizing: border-box; line-height: 1.2; }
    .ilo-table-wrap { width: 100%; min-width: 0; }
    /* ensure consistent fixed layout so colgroup widths are respected and labels wrap */
    table.cis-table { table-layout: fixed; }
    table.cis-table th.cis-label { white-space: normal; overflow-wrap: break-word; word-break: break-word; }
    /* prevent cell contents from overflowing the cell box */
    table.cis-table td, table.cis-table th { overflow: hidden; }
  /* Make inner table fill the right cell container and sit flush */
  #ilo-right-wrap { padding: 0; margin: 0; }
  #ilo-right-wrap > table { width: 100%; height: 100%; margin: 0; border-spacing: 0; border-collapse: collapse; }
  /* inner table cell padding so content is flush with container */
  #ilo-right-wrap td, #ilo-right-wrap th { vertical-align: middle; padding: 0.5rem 0.5rem; }
  /* show cell borders for the inner ILO table (internal grid only) */
  #ilo-right-wrap > table th, #ilo-right-wrap > table td { border: 1px solid #dee2e6; }
  /* hide outer edges so only internal dividers remain */
  #ilo-right-wrap > table thead th { border-top: 0; }
  #ilo-right-wrap > table th:first-child, #ilo-right-wrap > table td:first-child { border-left: 0; }
  #ilo-right-wrap > table th:last-child, #ilo-right-wrap > table td:last-child { border-right: 0; }
  /* remove the bottom-most outer border line of the inner table (no visible border under last row) */
  #ilo-right-wrap > table tbody tr:last-child td { border-bottom: 0 !important; }
  /* Ensure badge/code cell and grip align and don't push width */
  .ilo-badge { display: inline-block; min-width: 48px; text-align: center; }
  .drag-handle { width: 28px; display: inline-flex; justify-content: center; }
  /* Make textarea fill remaining space and autosize */
  .cis-textarea { width: 100%; box-sizing: border-box; resize: none; }
  /* Allow ILO textareas to collapse to a single-line look (match Course Title feel) */
  #ilo-right-wrap textarea.cis-textarea.autosize { min-height: 34px; overflow: hidden; }
  /* Ensure the left header cell aligns with other CIS module headers */
  table.cis-table th.cis-label, table.cis-table th { vertical-align: top; }
  </style>

  <table class="table table-bordered mb-4 cis-table">
    <colgroup>
      <col style="width:16%">
      <col style="width:84%">
    </colgroup>
    <tbody>
      <tr>
        <th id="ilo-left-title" class="align-top text-start cis-label">Intended Learning Outcomes (ILO)
          <span id="unsaved-ilos" class="unsaved-pill d-none">Unsaved</span>
        </th>
        <td id="ilo-right-wrap">
          <table class="table mb-0" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.4; border: none;">
            <colgroup>
              <col style="width: 10%"> <!-- ILO code column -->
              <col style="width: 90%"> <!-- Description column -->
            </colgroup>
            <thead>
              <tr class="table-light">
                <th class="text-center cis-label">ILO</th>
                <th class="text-start cis-label">Upon completion of this course, the students should be able to:</th>
              </tr>
            </thead>
            <tbody id="syllabus-ilo-sortable" data-syllabus-id="{{ $default['id'] }}">
              @if($ilosSorted->count())
                @foreach ($ilosSorted as $index => $ilo)
                  <tr data-id="{{ $ilo->id }}">
                    <td class="text-center align-middle">
                      <div class="ilo-badge fw-semibold">{{ $ilo->code ?? "ILO" . ($index + 1) }}</div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;">
                          <i class="bi bi-grip-vertical"></i>
                        </span>
                        <textarea
                          name="ilos[]"
                          class="form-control cis-textarea autosize flex-grow-1"
                          data-original="{{ old("ilos.$index", $ilo->description) }}"
                          required>{{ old("ilos.$index", $ilo->description) }}</textarea>
                        <input type="hidden" name="code[]" value="{{ $ilo->code }}">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-ilo ms-2" title="Delete ILO">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                @endforeach
              @else
                <tr>
                  <td colspan="2" class="text-center text-muted">No ILOs found.</td>
                </tr>
              @endif
            </tbody>
          </table>
        </td>
      </tr>
    </tbody>
  </table>

  {{-- ░░░ START: ILO Action Buttons ░░░ --}}
  <div class="d-flex gap-2">
    {{-- Add Row, Save Order, and local Save All buttons removed; saving is handled by the main toolbar Save button. --}}
  </div>
  {{-- ░░░ END: ILO Action Buttons ░░░ --}}
</form>

<script>
  // Inline autosize helper for ILO textareas — runs without requiring bundled JS rebuild
  (function(){
    function autosizeEl(el){ try { el.style.height = 'auto'; el.style.height = (el.scrollHeight || 0) + 'px'; } catch(e) { /* noop */ } }
    function bindAutosize(ta){ if (!ta) return; autosizeEl(ta); ta.addEventListener('input', () => autosizeEl(ta)); }

    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('textarea.autosize').forEach(bindAutosize);

      // Observe newly added rows (e.g., Add Row button) and initialize autosize on any new textarea.autosize
      const list = document.getElementById('syllabus-ilo-sortable');
      if (list && window.MutationObserver) {
        const mo = new MutationObserver((mutations) => {
          for (const m of mutations) {
            for (const node of m.addedNodes) {
              if (node && node.querySelectorAll) {
                node.querySelectorAll('textarea.autosize').forEach(bindAutosize);
              }
            }
          }
        });
        mo.observe(list, { childList: true, subtree: true });
      }
    });
  })();
</script>

@push('scripts')
  @vite('resources/js/faculty/syllabus-ilo-sortable.js')
@endpush
  