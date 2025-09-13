{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/so.blade.php
* Description: CIS-style SO layout with drag-safe table structure – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-07-29] Updated layout to match ILO format (flat rows, sortable-safe, delete button included).
-------------------------------------------------------------------------------
--}}

@php $rp = $routePrefix ?? 'faculty.syllabi'; @endphp
<form id="soForm" method="POST" action="{{ route($rp . '.sos.update', $default['id']) }}">
  @csrf
  @method('PUT')

  @php
    $sosSorted = ($sos ?? collect())->sortBy('position')->values();
  @endphp

  <style>
    /* Tight layout to match IGA: remove outer padding/margins and make inner table flush */
    .so-left-title { font-weight: 700; padding: 0.5rem; font-family: Georgia, serif; vertical-align: top; box-sizing: border-box; line-height: 1.2; font-size: 0.97rem; }
    table.cis-table { table-layout: fixed; margin: 0; }
    /* Make the right inner table sit flush in its cell */
    #so-right-wrap { padding: 0; margin: 0; }
    #so-right-wrap > table { width: 100%; height: 100%; margin: 0; border-spacing: 0; border-collapse: collapse; }
    /* inner table cell padding so content is flush with container */
    #so-right-wrap td, #so-right-wrap th { vertical-align: middle; padding: 0.45rem 0.5rem; }
    /* show internal grid lines only */
    #so-right-wrap > table th, #so-right-wrap > table td { border: 1px solid #dee2e6; }
    #so-right-wrap > table thead th { border-top: 0; }
    #so-right-wrap > table th:first-child, #so-right-wrap > table td:first-child { border-left: 0; }
    #so-right-wrap > table th:last-child, #so-right-wrap > table td:last-child { border-right: 0; }
    #so-right-wrap > table tbody tr:last-child td { border-bottom: 0 !important; }
    .so-badge { display: inline-block; min-width: 48px; text-align: center; }
    .drag-handle { width: 28px; display: inline-flex; justify-content: center; }
    .cis-textarea { width: 100%; box-sizing: border-box; resize: none; padding: 0.25rem 0.4rem; border-radius: 4px; }
    textarea.autosize { min-height: 42px; overflow: hidden; }
    .btn-delete-so { margin-left: 0.25rem; }
  </style>

  <table class="table table-bordered mb-4 cis-table">
    <colgroup>
      <col style="width:16%">
      <col style="width:84%">
    </colgroup>
    <tbody>
      <tr>
        <th class="align-top text-start cis-label so-left-title">Student Outcomes (SO)
          <span id="unsaved-sos" class="unsaved-pill d-none">Unsaved</span>
        </th>
  <td id="so-right-wrap">
          <table class="table mb-0" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.4; border: none;">
            <colgroup>
              <col style="width: 10%">
              <col style="width: 90%">
            </colgroup>
            <thead>
              <tr class="table-light">
                <th class="text-center cis-label">SO</th>
                <th class="text-start cis-label">Student Outcome / Notes</th>
              </tr>
            </thead>
            <tbody id="syllabus-so-sortable" data-syllabus-id="{{ $default['id'] }}">
              @if($sosSorted->count())
                @foreach ($sosSorted as $index => $so)
                  @php $seqCode = $so->code ?? 'SO' . ($index + 1); @endphp
                  <tr data-id="{{ $so->id }}">
                    <td class="text-center align-middle">
                      <div class="so-badge fw-semibold">{{ $seqCode }}</div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;"><i class="bi bi-grip-vertical"></i></span>
                        <textarea name="sos[]" class="form-control cis-textarea autosize flex-grow-1" data-original="{{ old("sos.$index", $so->description) }}" required>{{ old("sos.$index", $so->description) }}</textarea>
                        <input type="hidden" name="code[]" value="{{ $seqCode }}">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-so ms-2" title="Delete SO"><i class="bi bi-trash"></i></button>
                      </div>
                    </td>
                  </tr>
                @endforeach
              @else
                <tr>
                  <td class="text-center align-middle"><div class="so-badge fw-semibold">SO1</div></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;"><i class="bi bi-grip-vertical"></i></span>
                      <textarea name="sos[]" class="form-control cis-textarea autosize flex-grow-1" required></textarea>
                      <input type="hidden" name="code[]" value="SO1">
                      <button type="button" class="btn btn-sm btn-outline-danger btn-delete-so ms-2" title="Delete SO"><i class="bi bi-trash"></i></button>
                    </div>
                  </td>
                </tr>
              @endif
            </tbody>
          </table>
        </td>
      </tr>
    </tbody>
  </table>

  {{-- Controls removed: Add Row / Save Order / Save All are handled via top Save and programmatic APIs now --}}
</form>

@push('scripts')
  @vite([
    'resources/js/faculty/syllabus-so.js',
    'resources/js/faculty/syllabus-so-sortable.js'
  ])
@endpush


