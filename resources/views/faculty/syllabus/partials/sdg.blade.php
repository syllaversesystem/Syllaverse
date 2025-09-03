{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/sdg.blade.php
* Description: SDG Mapping (CIS-style layout, compact height and narrow actions) – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-07-29] Refactored layout to match CIS format.
[2025-07-29] Reduced textarea and button height; made action column smaller; icon-only compact buttons.
-------------------------------------------------------------------------------
--}}

<form id="sdgForm" action="{{ route('faculty.syllabi.sdgs.save', $default['id']) }}" method="POST">
  @csrf
  <table class="table table-bordered mb-4 cis-table sdg-module" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.4;">
    <style>
      /* Reuse SO/CDIO layout styles for visual parity */
      .so-left-title { font-weight: 700; padding: 0.5rem; font-family: Georgia, serif; vertical-align: top; box-sizing: border-box; line-height: 1.2; font-size: 0.97rem; }
      table.cis-table { table-layout: fixed; margin: 0; }
      #sdg-right-wrap { padding: 0; margin: 0; }
      #sdg-right-wrap > table { width: 100%; height: 100%; margin: 0; border-spacing: 0; border-collapse: collapse; }
      #sdg-right-wrap td, #sdg-right-wrap th { vertical-align: middle; padding: 0.45rem 0.5rem; }
      #sdg-right-wrap > table th, #sdg-right-wrap > table td { border: 1px solid #dee2e6; }
      #sdg-right-wrap > table thead th { border-top: 0; }
      #sdg-right-wrap > table th:first-child, #sdg-right-wrap > table td:first-child { border-left: 0; }
      #sdg-right-wrap > table th:last-child, #sdg-right-wrap > table td:last-child { border-right: 0; }
      #sdg-right-wrap > table tbody tr:last-child td { border-bottom: 0 !important; }
      .cdio-badge { display: inline-block; min-width: 48px; text-align: center; font-weight: 700; }
      .drag-handle { width: 28px; display: inline-flex; justify-content: center; }
      .cis-textarea { width: 100%; box-sizing: border-box; resize: none; padding: 0.25rem 0.4rem; border-radius: 4px; }
      textarea.autosize { min-height: 42px; overflow: hidden; }
      .btn-delete-cdio { margin-left: 0.25rem; }
      .cdio-code-box { display: inline-block; padding: 6px 8px; border: 1px solid #111; background:#fff; font-weight:700; }
      </style>

      <style>
  /* Clean professional Add button in table header */
        .sdg-add-btn { display: inline-flex; align-items: center; gap: .35rem; padding: .28rem .5rem; border-radius: 6px; }
        .sdg-add-btn i { width: 16px; height: 16px; }

  /* Outer SDG module border: top and right lines */
  /* Use Bootstrap's .table-bordered for the separation lines to match CDIO module */
  .sdg-module { border: none; }

        /* SDG bordered container for title + description */
  /* Remove surrounding box for the description, keep title underline */
  .sdg-box { border: none; padding: .25rem 0; background: transparent; }
  .sdg-box .sdg-title { margin-bottom: .15rem; }
  /* Strong title underline without an outer box */
  .sdg-box-strong { border: none; padding: .15rem 0; }
  .sdg-box-strong .sdg-title { padding-bottom: .15rem; border-bottom: 2px solid #111; display: block; margin-bottom: .4rem; }
  .sdg-cell { vertical-align: middle; }
  /* Make the SDG description textarea appear borderless inside the boxed container */
  .sdg-box .cis-textarea { border: none !important; box-shadow: none !important; background: transparent !important; padding: 0 !important; margin: 0 !important; }
  .sdg-box .cis-textarea:focus { outline: none !important; box-shadow: none !important; border: none !important; background: transparent !important; }
  /* Left SDG code cell (badge) styling - boxed container */
  .sdg-code-cell { padding: .35rem .5rem; border: 2px solid #111; border-right: 2px solid #111; border-radius: 6px; background: #fff; vertical-align: middle; }
  .sdg-code-cell .cdio-badge { font-family: Georgia, serif; font-weight:700; display:block; }
      </style>

    <colgroup>
      <col style="width:16%">
      <col style="width:84%">
    </colgroup>
    <tbody>
      <tr>
        <th class="align-top text-start cis-label so-left-title">Sustainable Development Goals (SDG)
          <div class="d-flex align-items-center gap-2">
            <span id="unsaved-sdgs" class="unsaved-pill d-none">Unsaved</span>
          </div>
        </th>
        <td id="sdg-right-wrap">
          <table class="table mb-0" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.4; border: none;">
            <colgroup>
              <col style="width: 10%">
              <col style="width: 90%">
            </colgroup>
            <thead>
              <tr class="table-light">
                  <th class="text-center cis-label">SDG</th>
                  <th class="text-start cis-label">
                    <div class="d-flex justify-content-between align-items-center">
                      <span>SDG Skills</span>
                      <div>
      <button type="button" class="btn btn-sm btn-outline-primary sdg-add-btn" data-bs-toggle="modal" data-bs-target="#addSdgModal" title="Add SDG from master data">
                          <i data-feather="plus"></i>
                          <span class="ms-1 d-none d-md-inline">Add SDG</span>
                        </button>
                      </div>
                    </div>
    </th>
                </tr>
            </thead>
            <tbody id="syllabus-sdg-sortable" data-syllabus-id="{{ $default['id'] }}">
              @php
                $sdgsSorted = ($default['sdgs'] ?? collect())->sortBy(function($s){ return $s->pivot->position ?? 0; })->values();
              @endphp
              @if($sdgsSorted->count())
                @foreach($sdgsSorted as $index => $sdg)
                  @php $seqCode = $sdg->pivot->code ?? ('SDG' . ($index + 1)); @endphp
                  <tr data-id="{{ $sdg->pivot->id }}" data-sdg-id="{{ $sdg->id }}">
                    <td class="text-center align-middle sdg-code-cell">
                      <div class="cdio-badge">{{ $seqCode }}</div>
                    </td>
                    <td class="sdg-cell">
                      <div class="d-flex align-items-center gap-2">
                        <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;"><i class="bi bi-grip-vertical"></i></span>
                        <div class="flex-grow-1 sdg-box sdg-box-strong">
                          <div class="sdg-title small fw-semibold">{{ $sdg->pivot->title ?? $sdg->title }}</div>
                          <input type="hidden" name="title[]" value="{{ $sdg->pivot->title ?? $sdg->title }}">
                          <textarea name="sdgs[]" class="form-control cis-textarea autosize" data-original="{{ old("sdgs.$index", $sdg->pivot->description) }}">{{ old("sdgs.$index", $sdg->pivot->description) }}</textarea>
                        </div>
                        <input type="hidden" name="code[]" value="{{ $seqCode }}">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-cdio ms-2" title="Delete SDG"><i class="bi bi-trash"></i></button>
                      </div>
                    </td>
                  </tr>
                @endforeach
              @else
                <tr>
                  <td class="text-center align-middle sdg-code-cell"><div class="cdio-badge">SDG1</div></td>
                  <td class="sdg-cell">
                    <div class="d-flex align-items-center gap-2">
                      <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;"><i class="bi bi-grip-vertical"></i></span>
                      <div class="flex-grow-1 sdg-box sdg-box-strong">
                        <div class="sdg-title small fw-semibold">SDG1</div>
                        <input type="hidden" name="title[]" value="SDG1">
                        <textarea name="sdgs[]" class="form-control cis-textarea autosize" required></textarea>
                      </div>
                      <input type="hidden" name="code[]" value="SDG1">
                      <button type="button" class="btn btn-sm btn-outline-danger btn-delete-cdio ms-2" title="Delete SDG"><i class="bi bi-trash"></i></button>
                    </div>
                  </td>
                </tr>
              @endif

              {{-- Hidden template row for JS clone --}}
              <tr id="sdg-template-row" class="d-none">
                <td class="text-center align-middle sdg-code-cell"><div class="cdio-badge">SDG#</div></td>
                <td class="sdg-cell">
                  <div class="d-flex align-items-center gap-2">
                    <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;"><i class="bi bi-grip-vertical"></i></span>
                    <div class="flex-grow-1 sdg-box sdg-box-strong">
                      <div class="sdg-title small fw-semibold">SDG Title</div>
                      <input type="hidden" name="title[]" value="">
                      <textarea name="sdgs[]" class="form-control cis-textarea autosize" rows="1" required></textarea>
                    </div>
                    <input type="hidden" name="code[]" value="SDG#">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-cdio ms-2" title="Delete SDG"><i class="bi bi-trash"></i></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>
    </tbody>
  </table>
</form>

{{-- ➕ Add SDG Modal --}}
<div class="modal fade" id="addSdgModal" tabindex="-1" aria-labelledby="addSdgModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('faculty.syllabi.sdgs.attach', $default['id']) }}">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addSdgModalLabel">Add Sustainable Development Goal</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <label for="sdg_id" class="form-label">Select SDG</label>
          <select name="sdg_id" id="sdg_id" class="form-select" required>
            <option value="">-- Choose SDG --</option>
            @foreach ($sdgs as $sdg)
              @if (!$default['sdgs']->contains($sdg))
                <option value="{{ $sdg->id }}">{{ $sdg->title }}</option>
              @endif
            @endforeach
          </select>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger">Add</button>
        </div>
      </div>
    </form>
  </div>
</div>
