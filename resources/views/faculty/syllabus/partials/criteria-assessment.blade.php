@php
  $local = $syllabus->courseInfo ?? null;
  // load normalized criteria (preferred) — collection of App\Models\SyllabusCriteria
  $criteriaCollection = $syllabus->relationLoaded('criteria') ? $syllabus->criteria : ($syllabus->criteria ?? collect());

  // helper: build a string suitable for legacy `criteria_lecture` / `criteria_laboratory` fields
  $buildLegacyText = function($row) {
    if (! $row) return null;
    $lines = [];
    // use heading as first line when present
    if (!empty($row->heading)) $lines[] = $row->heading;
    if (!empty($row->value) && is_array($row->value)) {
      foreach ($row->value as $item) {
        $desc = trim($item['description'] ?? '');
        $pct  = trim($item['percent'] ?? '');
        if ($desc === '' && $pct === '') continue;
        $line = $desc;
        if ($pct !== '') {
          $norm = str_ends_with($pct, '%') ? $pct : (preg_match('/^\d+$/', $pct) ? $pct . '%' : $pct);
          $line = $line ? ($line . ' ' . $norm) : $norm;
        }
        $lines[] = $line;
      }
    }
    return implode("\n", $lines);
  };

  // compute legacy texts: prefer old() -> courseInfo columns -> normalized rows (by key)
  $lectureRow = $criteriaCollection->firstWhere('key', 'lecture') ?: $criteriaCollection->firstWhere('heading', 'Lecture');
  $labRow = $criteriaCollection->firstWhere('key', 'laboratory') ?: $criteriaCollection->firstWhere('heading', 'Laboratory');

  $legacyLectureFromRows = $buildLegacyText($lectureRow);
  $legacyLabFromRows = $buildLegacyText($labRow);

  $lectureText = old('criteria_lecture', $local?->criteria_lecture ?? $legacyLectureFromRows ?? '');
  $labText     = old('criteria_laboratory', $local?->criteria_laboratory ?? $legacyLabFromRows ?? '');

  // sections to render in the UI: prefer normalized rows if present, otherwise fallback to two default sections
  $sections = [];
  if ($criteriaCollection && $criteriaCollection->isNotEmpty()) {
    foreach ($criteriaCollection as $c) {
      $sections[] = [
        'key' => $c->key ?? (Str::slug($c->heading ?: 'section') ?: 'section'),
        'heading' => $c->heading ?? '',
        'value' => is_array($c->value) ? $c->value : (is_string($c->value) ? json_decode($c->value, true) ?? [] : []),
      ];
    }
  } else {
    // fallback: single editable section — preserve existing legacy text where available (prefer lecture)
    $sections = [
      ['key' => 'lecture', 'heading' => preg_replace('/\s*\(?\d+%?\)?$/','', explode('\n', trim($lectureText))[0] ?? ''), 'value' => []],
    ];
  }
  // Don't override sections - render all saved sections
@endphp

<style>
  /* keep typography and spacing consistent with course-info and mission-vision */
  .cis-criteria { font-size: 13px; }
  .cis-criteria .section { padding: 6px 8px; border:0; border-radius:6px; background:#fff; display:flex; flex-direction:column }
  /* textarea look unified */
  .cis-criteria textarea { width:100%; border:none; background:transparent; padding:0; font-weight:400; font-family: inherit; font-size: inherit; line-height:1.15; color:#000; resize:none; overflow:hidden; }
  .cis-criteria .sub-list { margin-top:6px; flex: 1 1 auto; }
  .cis-criteria .sub-line { margin-left:18px; display:flex; gap:8px; align-items:flex-start; }
  .cis-criteria .sub-input { flex:1 1 auto; max-width: 50%; }
  .cis-criteria .sub-percent { flex:0 0 64px; width:64px; text-align:right; font-family: 'Times New Roman', Times, serif; font-size: 10pt; font-weight: 400; line-height: 1.15; }
  .cis-criteria textarea:focus { outline: none; box-shadow: none; background-color: transparent; }
  .cis-criteria .section-head { display:flex; justify-content:flex-start; align-items:flex-start; gap:8px; }
  /* allow add button to sit on the right of the main heading */
  .cis-criteria .section-head .main-input { width: auto; flex: 1 1 auto; }
  .cis-criteria .placeholder-muted { color:#6c757d; }
  /* remove all padding of the Criteria cell */
  .cis-criteria td { position: relative; }
  .cis-criteria .cis-table td { padding: 0 !important; }
  /* bottom action row under the sub-list */
  .cis-criteria .criteria-actions-row {
    display: flex;
    gap: 8px;
    margin-top: auto; /* anchor at bottom of section */
    padding-top: 8px; /* keep visual spacing from content */
  }
  /* board layout with fixed side controls and adaptable sections */
  .cis-criteria .criteria-board { display: flex; align-items: stretch; gap: 0; }
  .cis-criteria .sections-container { flex: 1 1 auto; display: flex; gap: 8px; }
  .cis-criteria .sections-container .section { flex: 1 1 0; min-width: 240px; }
  .cis-criteria .criteria-side-btn {
    padding: 0 8px;
    line-height: 1;
    font-weight: 600;
    border: none !important;
    background: #fff !important;
    color: #212529;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    align-self: stretch;
    height: auto;
  }
  .cis-criteria .criteria-side-btn i,
  .cis-criteria .criteria-side-btn svg { width: 14px; height: 14px; }
  .cis-criteria .criteria-add-btn {
    padding: 4px 10px;
    line-height: 1;
    font-weight: 600;
    border: none !important;
    background: #fff !important;
    color: #212529;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    height: auto;
  }
  .cis-criteria .criteria-remove-btn {
    padding: 4px 10px;
    line-height: 1;
    font-weight: 600;
    border: none !important;
    background: #fff !important;
    color: #212529;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    height: auto;
  }
  /* hover/active for side controls */
  .cis-criteria .criteria-side-btn:hover,
  .cis-criteria .criteria-side-btn:focus {
    background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46)) !important;
    backdrop-filter: blur(7px);
    -webkit-backdrop-filter: blur(7px);
    box-shadow: 0 4px 10px rgba(204, 55, 55, 0.12);
    color: #CB3737;
  }
  .cis-criteria .criteria-side-btn:active { transform: scale(0.97); filter: brightness(0.98); }
  .cis-criteria .criteria-side-btn:disabled { opacity: 0.5; cursor: not-allowed; }
  /* inside bottom action row, split equally 50/50 */
  .cis-criteria .criteria-actions-row .criteria-add-btn,
  .cis-criteria .criteria-actions-row .criteria-remove-btn {
    flex: 1 1 50%;
  }
  @media print { .cis-criteria .criteria-add-btn { display: none !important; } }
  @media print { .cis-criteria .criteria-remove-btn { display: none !important; } }
  @media print { .cis-criteria .criteria-side-btn { display: none !important; } }
    /* Match syllabus toolbar hover effect */
    .cis-criteria .criteria-add-btn:hover,
    .cis-criteria .criteria-add-btn:focus {
      background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46)) !important;
      backdrop-filter: blur(7px);
      -webkit-backdrop-filter: blur(7px);
      box-shadow: 0 4px 10px rgba(204, 55, 55, 0.12);
      color: #CB3737;
    }
    .cis-criteria .criteria-add-btn:hover i,
    .cis-criteria .criteria-add-btn:hover svg,
    .cis-criteria .criteria-add-btn:focus i,
    .cis-criteria .criteria-add-btn:focus svg {
      color: #CB3737;
    }
    .cis-criteria .criteria-add-btn:active { transform: scale(0.97); filter: brightness(0.98); }
  /* smaller icon inside add button */
  .cis-criteria .criteria-add-btn i,
  .cis-criteria .criteria-add-btn svg { width: 14px; height: 14px; }
  /* match remove button icon size to add button */
  .cis-criteria .criteria-remove-btn i,
  .cis-criteria .criteria-remove-btn svg { width: 14px; height: 14px; }
  .cis-criteria .criteria-remove-btn:hover,
  .cis-criteria .criteria-remove-btn:focus {
    background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46)) !important;
    backdrop-filter: blur(7px);
    -webkit-backdrop-filter: blur(7px);
    box-shadow: 0 4px 10px rgba(204, 55, 55, 0.12);
    color: #CB3737;
  }
  .cis-criteria .criteria-remove-btn:hover i,
  .cis-criteria .criteria-remove-btn:hover svg,
  .cis-criteria .criteria-remove-btn:focus i,
  .cis-criteria .criteria-remove-btn:focus svg {
    color: #CB3737;
  }
  .cis-criteria .criteria-remove-btn:active { transform: scale(0.97); filter: brightness(0.98); }
</style>

<div class="mt-3 cis-criteria">
  <table class="table table-bordered mb-4 cis-table">
    <colgroup>
      <col style="width: 16%">
      <col style="width: 84%">
    </colgroup>
    <tbody>
      <tr>
        <th class="align-top text-start cis-label">Criteria for Assessment</th>
        <td>
          <div class="criteria-board">
            <button type="button" class="btn btn-sm criteria-side-btn criteria-remove-section-btn" title="Remove last section" aria-label="Remove last section">
              <i data-feather="minus"></i>
            </button>
            <div class="sections-container" id="criteria-sections-container">
              @foreach($sections as $idx => $sec)
                <div class="section" data-section-key="{{ $sec['key'] ?? ('section_' . ($idx+1)) }}">
                  <div class="section-head">
                    <textarea rows="1" name="criteria_{{ $sec['key'] ?? ($idx+1) }}_display" data-section="{{ $sec['key'] ?? ('section_' . ($idx+1)) }}" class="main-input cis-input autosize" placeholder="Category">{{ old('criteria_section_heading.' . $idx, $sec['heading'] ?? '') }}</textarea>
                  </div>
                  <div class="sub-list" aria-live="polite" data-init='{{ json_encode($sec['value'] ?? []) }}'></div>
                  <div class="criteria-actions-row">
                    <button type="button" class="btn btn-sm criteria-remove-btn" title="Remove last sub-item" aria-label="Remove last sub-item">
                      <i data-feather="minus"></i>
                    </button>
                    <button type="button" class="btn btn-sm criteria-add-btn" title="Add sub-item" aria-label="Add sub-item">
                      <i data-feather="plus"></i>
                    </button>
                  </div>
                </div>
              @endforeach
            </div>
            <button type="button" class="btn btn-sm criteria-side-btn criteria-add-section-btn" title="Add section" aria-label="Add section">
              <i data-feather="plus"></i>
            </button>
          </div>

          {{-- Hidden inputs to submit serialized criteria lines (one per section) --}}
          <input type="hidden" name="criteria_lecture" id="criteria_lecture_input">
          <input type="hidden" name="criteria_laboratory" id="criteria_laboratory_input">
          {{-- New: structured JSON payload for normalized storage --}}
          <input type="hidden" name="criteria_data" id="criteria_data_input" value='{{ json_encode(array_map(function($s){ return ["key" => $s["key"], "heading" => $s["heading"], "value" => $s["value"] ?? []]; }, $sections)) }}'>
        </td>
      </tr>
    </tbody>
  </table>
</div>

{{-- Scripts moved to resources/js/faculty/syllabus-criteria.js via Vite import --}}

{{-- Local Criteria Save button removed; toolbar Save handles persistence --}}

@push('scripts')
<script>
  (function(){
    // Register criteria validation field
    function registerValidationField(){
      if (typeof window.addRequiredField === 'function') {
        window.addRequiredField('criteria_assessment', 'criteria_data', 'Criteria for Assessment');
        console.log('Criteria Assessment validation field registered');
      } else {
        setTimeout(registerValidationField, 500);
      }
    }
    
    // Also re-validate when criteria changes
    document.addEventListener('criteriaChanged', function(){
      if (typeof window.updateProgressBar === 'function') {
        // Force re-calculation by calling internal update
        try { window.getSyllabusValidationStatus(); } catch (e) { /* noop */ }
      }
    });
    
    registerValidationField();
  })();
</script>
@endpush
