{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/cdio.blade.php
* Description: CDIO Mapping — placeholder CIS-style table (Blade only)
-------------------------------------------------------------------------------
--}}

{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/cdio.blade.php
* Description: CDIO Mapping — placeholder CIS-style table (Blade only)
-------------------------------------------------------------------------------
--}}

@php $rp = $routePrefix ?? 'faculty.syllabi'; @endphp
<form id="cdioForm" method="POST" action="{{ route($rp . '.cdios.update', $default['id']) }}">
  @csrf
  @method('PUT')

  @php
    $cdiosSorted = ($cdios ?? collect())->sortBy('position')->values();
  @endphp

  <style>
    /* Reuse SO layout styles for visual parity */
    .so-left-title { font-weight: 700; padding: 0.5rem; font-family: Georgia, serif; vertical-align: top; box-sizing: border-box; line-height: 1.2; font-size: 0.97rem; }
    table.cis-table { table-layout: fixed; margin: 0; }
    #cdio-right-wrap { padding: 0; margin: 0; }
    #cdio-right-wrap > table { width: 100%; height: 100%; margin: 0; border-spacing: 0; border-collapse: collapse; }
    #cdio-right-wrap td, #cdio-right-wrap th { vertical-align: middle; padding: 0.45rem 0.5rem; }
    #cdio-right-wrap > table th, #cdio-right-wrap > table td { border: 1px solid #dee2e6; }
    #cdio-right-wrap > table thead th { border-top: 0; }
    #cdio-right-wrap > table th:first-child, #cdio-right-wrap > table td:first-child { border-left: 0; }
    #cdio-right-wrap > table th:last-child, #cdio-right-wrap > table td:last-child { border-right: 0; }
    #cdio-right-wrap > table tbody tr:last-child td { border-bottom: 0 !important; }
    .cdio-badge { display: inline-block; min-width: 48px; text-align: center; font-weight: 700; }
    .drag-handle { width: 28px; display: inline-flex; justify-content: center; }
    .cis-textarea { width: 100%; box-sizing: border-box; resize: none; padding: 0.25rem 0.4rem; border-radius: 4px; }
    textarea.autosize { min-height: 42px; overflow: hidden; }
    .btn-delete-cdio { margin-left: 0.25rem; }
    .cdio-code-box { display: inline-block; padding: 6px 8px; border: 1px solid #111; background:#fff; font-weight:700; }
  </style>

  <table class="table table-bordered mb-4 cis-table">
    <colgroup>
      <col style="width:16%">
      <col style="width:84%">
    </colgroup>
    <tbody>
      <tr>
        <th class="align-top text-start cis-label so-left-title">CDIO Framework Skills (CDIO)
          <span id="unsaved-cdios" class="unsaved-pill d-none">Unsaved</span>
        </th>
        <td id="cdio-right-wrap">
          <table class="table mb-0" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.4; border: none;">
            <colgroup>
              <col style="width: 10%">
              <col style="width: 90%">
            </colgroup>
            <thead>
              <tr class="table-light">
                <th class="text-center cis-label">CDIO</th>
                <th class="text-start cis-label">CDIO Skills</th>
              </tr>
            </thead>
            <tbody id="syllabus-cdio-sortable" data-syllabus-id="{{ $default['id'] }}">
              @if($cdiosSorted->count())
                @foreach ($cdiosSorted as $index => $cdio)
                  @php $seqCode = $cdio->code ?? 'CDIO' . ($index + 1); @endphp
                  <tr data-id="{{ $cdio->id }}">
                    <td class="text-center align-middle">
                      <div class="cdio-badge">{{ $seqCode }}</div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;"><i class="bi bi-grip-vertical"></i></span>
                        <textarea name="cdios[]" class="form-control cis-textarea autosize flex-grow-1" data-original="{{ old("cdios.$index", $cdio->description) }}">{{ old("cdios.$index", $cdio->description) }}</textarea>
                        <input type="hidden" name="code[]" value="{{ $seqCode }}">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-cdio ms-2" title="Delete CDIO"><i class="bi bi-trash"></i></button>
                      </div>
                    </td>
                  </tr>
                @endforeach
              @else
                <tr>
                  <td class="text-center align-middle"><div class="cdio-badge">CDIO1</div></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;"><i class="bi bi-grip-vertical"></i></span>
                      <textarea name="cdios[]" class="form-control cis-textarea autosize flex-grow-1" required></textarea>
                      <input type="hidden" name="code[]" value="CDIO1">
                      <button type="button" class="btn btn-sm btn-outline-danger btn-delete-cdio ms-2" title="Delete CDIO"><i class="bi bi-trash"></i></button>
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

</form>

@push('scripts')
  @vite([
  'resources/js/faculty/syllabus-cdio.js'
  ])
@endpush

