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
    // fallback: two editable sections (lecture + laboratory) — preserve existing legacy text where available
    $sections = [
      ['key' => 'lecture', 'heading' => preg_replace('/\s*\(?\d+%?\)?$/','', explode('\n', trim($lectureText))[0] ?? ''), 'value' => []],
      ['key' => 'laboratory', 'heading' => preg_replace('/\s*\(?\d+%?\)?$/','', explode('\n', trim($labText))[0] ?? ''), 'value' => []],
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
                    <textarea rows="1" name="criteria_{{ $sec['key'] ?? ($idx+1) }}_display" data-section="{{ $sec['key'] ?? ('section_' . ($idx+1)) }}" class="main-input cis-input autosize" placeholder="-">{{ old('criteria_section_heading.' . $idx, $sec['heading'] ?? '') }}</textarea>
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

<script>
document.addEventListener('DOMContentLoaded', function(){
  const removedState = window.__criteriaRemovedState = window.__criteriaRemovedState || {
    sections: [],
    rows: {}
  };

  function getSectionKey(sectionEl) {
    if (!sectionEl) return '';
    return sectionEl.dataset.sectionKey
      || (sectionEl.querySelector('.main-input')?.dataset.section ?? '')
      || '';
  }

  function stashRemovedRow(sectionKey, rowData) {
    if (!sectionKey || !rowData) return;
    if (!removedState.rows[sectionKey]) {
      removedState.rows[sectionKey] = [];
    }
    removedState.rows[sectionKey].push({
      description: (rowData.description ?? '').trim(),
      percent: (rowData.percent ?? '').trim(),
    });
  }

  function popRemovedRow(sectionKey) {
    if (!sectionKey) return null;
    const stack = removedState.rows[sectionKey];
    if (!stack || !stack.length) return null;
    return stack.pop();
  }

  function stashRemovedSection(sectionData) {
    if (!sectionData) return;
    removedState.sections.push(sectionData);
  }

  function popRemovedSection() {
    if (!removedState.sections.length) return null;
    return removedState.sections.pop();
  }

  function sanitizeSectionKey(rawKey, fallbackIndex) {
    let key = (rawKey || '').toString().trim().toLowerCase();
    key = key.replace(/[^a-z0-9\s-_]/g, '').replace(/[\s-_]+/g, '_').replace(/^_|_$/g, '');
    if (!key) {
      key = 'section_' + (Number.isFinite(fallbackIndex) ? fallbackIndex : Date.now());
    }
    return key;
  }

  function collectSectionData(sectionEl) {
    if (!sectionEl) return null;
    const key = getSectionKey(sectionEl);
    const heading = (sectionEl.querySelector('.main-input')?.value || '').trim();
    const values = [];
    sectionEl.querySelectorAll('.sub-list .sub-line').forEach(function(line){
      const desc = (line.querySelector('.sub-input')?.value || '').trim();
      const percent = (line.querySelector('.sub-percent')?.value || '').trim();
      if (desc === '' && percent === '') return;
      values.push({ description: desc, percent: percent });
    });

    if (!heading && values.length === 0) {
      return null;
    }

    return { key, heading, values };
  }

  function updateSectionRemoveState(sectionEl) {
    if (!sectionEl) return;
    const removeBtn = sectionEl.querySelector('.criteria-actions-row .criteria-remove-btn');
    if (!removeBtn) return;
    const subList = sectionEl.querySelector('.sub-list');
    const count = subList ? subList.querySelectorAll('.sub-line').length : 0;
    const disable = count <= 1;
    removeBtn.disabled = disable;
    removeBtn.setAttribute('aria-disabled', disable ? 'true' : 'false');
  }
  // Debounce helpers to smooth typing performance
  let __critChangedTimer = null;
  function fireCriteriaChangedDebounced(delay){
    try {
      const d = Number.isFinite(delay) ? delay : 60;
      if (__critChangedTimer) clearTimeout(__critChangedTimer);
      __critChangedTimer = setTimeout(() => { try { fireCriteriaChanged(); } catch (e) { /* noop */ } }, d);
    } catch (e) { /* noop */ }
  }
  // helper to create a sub input line
  function createSubLine(initial) {
    const el = document.createElement('div');
    el.className = 'sub-line';
    // description textarea
    const ta = document.createElement('textarea');
    ta.rows = 1;
    ta.className = 'sub-input cis-input autosize';
  ta.placeholder = '-';
    let descValue = '';
    let pct = '';
    if (initial && typeof initial === 'object') {
      descValue = (initial.description ?? '').toString();
      pct = (initial.percent ?? '').toString();
    } else if (typeof initial === 'string' && initial !== '') {
      const m = initial.match(/^(.*?)\s*(?:\(?([0-9]{1,3}%?)\)?)\s*$/);
      if (m) {
        descValue = (m[1] || '').trim();
        pct = (m[2] || '').trim();
      } else {
        descValue = initial;
      }
    }
    ta.value = descValue;
    el.appendChild(ta);
    // percent textarea (keep small width via existing class styles)
    const p = document.createElement('textarea');
    p.rows = 1;
    p.className = 'sub-percent cis-number autosize';
  p.placeholder = '%';
    p.value = pct || '';
    el.appendChild(p);
    return el;
  }

  // initialize each section: parse main textarea into first line and remaining sublines
  document.querySelectorAll('.cis-criteria .sections-container .section').forEach(function(section){
    const main = section.querySelector('.main-input');
    const subList = section.querySelector('.sub-list');

    function syncFromMain() {
      // split lines; first token becomes the main header (may be blank), others are sublines
      const raw = (main.value || '').split(/\r?\n/).map(s => s.trim());
      main.value = raw[0] || '';
      subList.innerHTML = '';
      for (let i=1;i<raw.length;i++) {
        if (raw[i]) { subList.appendChild(createSubLine(raw[i])); }
      }
      // ensure a single default subline if none exist
      if (subList.children.length === 0) {
        subList.appendChild(createSubLine());
      }
      attachSubHandlers(subList, main);
      updateSectionRemoveState(section);
      fireCriteriaChanged();
    }

    // main typing: emit detailed event for fast AT sync + debounced generic criteriaChanged for others
    // Removed Enter key handler that auto-added sub-lines from main heading to simplify UX
    main.addEventListener('input', function(){
      try {
        const container = document.getElementById('criteria-sections-container');
        const sections = Array.from(container ? container.querySelectorAll('.section') : []);
        const sectionEl = main.closest('.section');
        const sectionIndex = Math.max(1, sections.indexOf(sectionEl) + 1);
        document.dispatchEvent(new CustomEvent('criteria:sectionMainChanged', { detail: { section: sectionIndex, value: main.value || '' } }));
      } catch (e) { /* noop */ }
      fireCriteriaChangedDebounced(60);
    });

    // initial sync: prefer `data-init` on .sub-list (JSON array of {description,percent}), else fallback to legacy main.value newline parsing
    try {
      const initData = subList.dataset.init ? JSON.parse(subList.dataset.init) : null;
      if (Array.isArray(initData) && initData.length > 0) {
        subList.innerHTML = '';
        initData.forEach(function(item){
          const line = createSubLine((item.description || '') + (item.percent ? (' ' + item.percent) : ''));
          subList.appendChild(line);
        });
        attachSubHandlers(subList, main);
        updateSectionRemoveState(section);
      } else if ((main.value || '').indexOf('\n') !== -1) {
        syncFromMain();
      } else {
        // default: one blank sub input so user fills it; placeholder shows example
        subList.innerHTML = '';
        subList.appendChild(createSubLine());
        attachSubHandlers(subList, main);
        updateSectionRemoveState(section);
      }
    } catch (e) {
      // if JSON parsing fails, fallback to previous behavior
      if ((main.value || '').indexOf('\n') !== -1) { syncFromMain(); }
  else { subList.innerHTML = ''; subList.appendChild(createSubLine()); attachSubHandlers(subList, main); updateSectionRemoveState(section); }
    }
  });

  // attach handlers to sub textareas: Enter creates a new sibling sub, Backspace on empty removes it
  function attachSubHandlers(listEl, mainEl) {
    // Only basic input listeners retained; removed Enter add and Backspace remove shortcuts
    Array.from(listEl.querySelectorAll('.sub-line .sub-input')).forEach(function(inp){
      inp.addEventListener('input', function(){
        try {
          const sectionEl = inp.closest('.section');
          const container = document.getElementById('criteria-sections-container');
          const sections = Array.from(container ? container.querySelectorAll('.section') : []);
          const sectionIndex = Math.max(1, sections.indexOf(sectionEl) + 1);
          const subLines = Array.from(sectionEl.querySelectorAll('.sub-list .sub-line'));
          const thisLine = inp.closest('.sub-line');
          const subIndex = Math.max(1, subLines.indexOf(thisLine) + 1);
          document.dispatchEvent(new CustomEvent('criteria:subChanged', { detail: { section: sectionIndex, subIndex: subIndex, value: inp.value || '' } }));
        } catch (e) { /* noop */ }
        fireCriteriaChangedDebounced(60);
      });
    });
    Array.from(listEl.querySelectorAll('.sub-percent')).forEach(function(pin){
      pin.addEventListener('blur', function(){
        let v = (pin.value || '').toString().trim();
        if (v === '') return;
        v = v.replace(/%+/g, '%').replace(/\s+/g, '');
        if (/^\d+(?:\.\d+)?$/.test(v)) {
          pin.value = v + '%';
        } else if (/^\d+(?:\.\d+)?%$/.test(v)) {
          pin.value = v;
        } else {
          pin.value = v;
        }
        fireCriteriaChanged();
      });
      pin.addEventListener('input', function(){ fireCriteriaChangedDebounced(80); });
    });
  }

  // Public helpers to add/remove a sub-line within a specific section
  function addSubLineToSection(sectionEl) {
    if (!sectionEl) return;
    const subList = sectionEl.querySelector('.sub-list');
    if (!subList) return;
    const sectionKey = getSectionKey(sectionEl);
    const restoredRow = popRemovedRow(sectionKey);
    const newLine = createSubLine(restoredRow || undefined);
    subList.appendChild(newLine);
    attachSubHandlers(subList, sectionEl.querySelector('.main-input'));
    newLine.querySelectorAll('textarea.autosize').forEach(function(ta){
      try { ta.style.height = 'auto'; ta.style.height = (ta.scrollHeight || 0) + 'px'; } catch (e) { /* noop */ }
    });
    updateSectionRemoveState(sectionEl);
    if (restoredRow) {
      fireCriteriaChanged();
    }
    const ta = newLine.querySelector('.sub-input');
    if (ta) ta.focus();
    fireCriteriaChanged();
    try { recomputeAutosizeAll(); } catch (e) { /* noop */ }
  }

  function removeSubLineFromSection(sectionEl) {
    if (!sectionEl) return;
    const subList = sectionEl.querySelector('.sub-list');
    if (!subList) return;
    const lines = Array.from(subList.querySelectorAll('.sub-line'));
    if (!lines.length) return;
    const sectionKey = getSectionKey(sectionEl);
    if (lines.length === 1) {
      const onlyLine = lines[0];
      if (onlyLine) {
        const subInput = onlyLine.querySelector('.sub-input');
        const pctInput = onlyLine.querySelector('.sub-percent');
        if (subInput && pctInput && (subInput.value || pctInput.value)) {
          try { subInput.focus(); } catch (e) { /* noop */ }
        }
      }
      updateSectionRemoveState(sectionEl);
      return;
    }
    let target = null;
    for (let i = lines.length - 1; i >= 0; i--) {
      const desc = (lines[i].querySelector('.sub-input')?.value || '').trim();
      const pct  = (lines[i].querySelector('.sub-percent')?.value || '').trim();
      if (desc !== '' || pct !== '') { target = lines[i]; break; }
      if (desc === '' && pct === '' && lines.length > 1) { target = lines[i]; break; }
    }
    if (!target) {
      if (lines.length > 1) { lines[lines.length - 1].remove(); fireCriteriaChanged(); }
      try { recomputeAutosizeAll(); } catch (e) { /* noop */ }
      updateSectionRemoveState(sectionEl);
      return;
    }
    const descVal = (target.querySelector('.sub-input')?.value || '').trim();
    const pctVal = (target.querySelector('.sub-percent')?.value || '').trim();
    if (descVal !== '' || pctVal !== '') {
      stashRemovedRow(sectionKey, { description: descVal, percent: pctVal });
    }
    const prev = target.previousElementSibling;
    target.remove();
    if (prev) {
      const prevDesc = prev.querySelector('.sub-input');
      if (prevDesc) prevDesc.focus();
    }
    fireCriteriaChanged();
    try { recomputeAutosizeAll(); } catch (e) { /* noop */ }
    updateSectionRemoveState(sectionEl);
  }

  // Optional: expose helpers on window for external triggers/debug
  window.addCriteriaSubLine = addSubLineToSection;
  window.removeCriteriaSubLine = removeSubLineFromSection;

  function serializeSectionEl(section) {
    if (!section) return '';
    const main = section.querySelector('.main-input');
    const subLines = section.querySelectorAll('.sub-list .sub-line');
    let lines = [];
    if (main && (main.value || '').trim() !== '') lines.push((main.value || '').trim());
    subLines.forEach(function(line){
      const ta = line.querySelector('.sub-input');
      const p = line.querySelector('.sub-percent');
      const tval = ta ? (ta.value || '').trim() : '';
      const pval = p ? (p.value || '').trim() : '';
      if (tval !== '' || pval !== '') {
        let combined = tval;
        if (pval !== '') {
          const norm = pval.endsWith('%') ? pval : (pval.match(/^\d+$/) ? pval + '%' : pval);
          combined = (combined ? (combined + ' ' + norm) : norm);
        }
        if (combined) lines.push(combined);
      }
    });
    return lines.join('\n');
  }

  window.serializeCriteriaData = function(){
  const sectionEls = document.querySelectorAll('.cis-criteria .sections-container .section');
  const lecture = sectionEls[0] ? serializeSectionEl(sectionEls[0]) : '';
  const laboratory = sectionEls[1] ? serializeSectionEl(sectionEls[1]) : '';
  document.getElementById('criteria_lecture_input').value = lecture;
  document.getElementById('criteria_laboratory_input').value = laboratory;
    // build structured payload: array of { key, heading, value: [{description,percent}] }
    const payload = [];
    function slugify(s) {
      if (!s) return '';
      return s.toString().toLowerCase().trim()
        .replace(/[^a-z0-9\s-_]/g, '')
        .replace(/[\s-_]+/g, '_')
        .replace(/^_|_$/g, '');
    }
    // iterate all configured sections so this is not tied to 'lecture'/'laboratory'
  const sections = document.querySelectorAll('.cis-criteria .sections-container .section');
    sections.forEach(function(sectionEl, idx){
      const main = sectionEl.querySelector('.main-input');
      const heading = main ? (main.value || '').trim() : '';
      const subLines = sectionEl.querySelectorAll('.sub-list .sub-line');
      const values = [];
      subLines.forEach(function(line){
        const ta = line.querySelector('.sub-input');
        const p = line.querySelector('.sub-percent');
        const tval = ta ? (ta.value || '').trim() : '';
        const pval = p ? (p.value || '').trim() : '';
        if (tval !== '' || pval !== '') {
          values.push({ description: tval, percent: pval });
        }
      });
      // derive key: prefer slugified heading, else fallback to data-section attribute, else index-based key
      let key = slugify(heading);
      if (!key && main && main.dataset && main.dataset.section) key = main.dataset.section;
      if (!key) key = 'section_' + (idx + 1);
      payload.push({ key: key, heading: heading, value: values });
    });
    document.getElementById('criteria_data_input').value = JSON.stringify(payload);
    // notify listeners that the serialized payload changed (covers programmatic updates)
    const __critEl = document.getElementById('criteria_data_input');
    if (__critEl) {
      try { __critEl.dispatchEvent(new Event('input', { bubbles: true })); } catch (e) { /* noop */ }
    }
  };

  function fireCriteriaChanged(){
    document.dispatchEvent(new Event('criteriaChanged'));
  }

  // Ensure the serialized criteria initial value is recorded so unsaved logic can compare later
  try {
    const __critInit = document.getElementById('criteria_data_input');
    if (__critInit) __critInit.dataset.original = __critInit.value || '[]';
  } catch (e) { /* noop */ }

  // Recompute autosize heights for all textareas in this component
  function recomputeAutosizeAll(){
    try {
      document.querySelectorAll('.cis-criteria textarea.autosize').forEach(function(ta){
        ta.style.height = 'auto';
        ta.style.height = (ta.scrollHeight || 0) + 'px';
      });
    } catch (e) { /* noop */ }
  }
  // Initial autosize pass
  recomputeAutosizeAll();

  // Keep hidden fields in sync whenever criteria changes (debounced)
  let __critSerializeTimer = null;
  document.addEventListener('criteriaChanged', function(){
    try {
      if (__critSerializeTimer) clearTimeout(__critSerializeTimer);
      __critSerializeTimer = setTimeout(function(){ try { window.serializeCriteriaData(); } catch (e) { /* noop */ } }, 80);
    } catch (e) { /* noop */ }
  });

  // Limit: maximum number of sections allowed
  function updateAddSectionState(){
    const container = document.getElementById('criteria-sections-container');
    const btn = document.querySelector('.cis-criteria .criteria-add-section-btn');
    if (!container || !btn) return;
    const count = container.querySelectorAll('.section').length;
    const disabled = count >= 3;
    btn.disabled = disabled;
    btn.setAttribute('aria-disabled', disabled ? 'true' : 'false');
    try { btn.title = disabled ? 'Maximum of 3 sections reached' : 'Add section'; } catch(e){}
  }

  // Add button(s) inside section: append a new sub-line for the nearest section and focus it
  document.querySelectorAll('.cis-criteria .criteria-actions-row .criteria-add-btn').forEach(function(addBtn){
    addBtn.addEventListener('click', function(){
      const section = this.closest('.section') || document.querySelector('.cis-criteria .section');
      addSubLineToSection(section);
    });
  });

  // Remove button(s) inside section: remove the last non-empty sub-line (keep at least one blank line)
  document.querySelectorAll('.cis-criteria .criteria-actions-row .criteria-remove-btn').forEach(function(removeBtn){
    removeBtn.addEventListener('click', function(){
      const section = this.closest('.section') || document.querySelector('.cis-criteria .section');
      removeSubLineFromSection(section);
    });
  });

  // Side button: add a new section to the right
  const addSectionBtn = document.querySelector('.cis-criteria .criteria-add-section-btn');
  if (addSectionBtn) {
    addSectionBtn.addEventListener('click', function(){
      const container = document.getElementById('criteria-sections-container');
      if (!container) return;
      // enforce max of 3 sections
      if (container.querySelectorAll('.section').length >= 3) { return; }
      const index = container.querySelectorAll('.section').length + 1;
      const restored = popRemovedSection();
      const key = sanitizeSectionKey(restored && restored.key ? restored.key : '', index);
      const section = document.createElement('div');
      section.className = 'section';
      section.dataset.sectionKey = key;
      section.innerHTML = `
        <div class="section-head">
          <textarea rows="1" name="criteria_${key}_display" data-section="${key}" class="main-input cis-input autosize" placeholder="-"></textarea>
        </div>
        <div class="sub-list" aria-live="polite" data-init='[]'></div>
        <div class="criteria-actions-row">
          <button type="button" class="btn btn-sm criteria-remove-btn" title="Remove last sub-item" aria-label="Remove last sub-item"><i data-feather="minus"></i></button>
          <button type="button" class="btn btn-sm criteria-add-btn" title="Add sub-item" aria-label="Add sub-item"><i data-feather="plus"></i></button>
        </div>`;
      container.appendChild(section);
      // initialize autosize & one blank sub-line
      const subList = section.querySelector('.sub-list');
      const mainEl = section.querySelector('.main-input');
      if (restored && restored.heading && mainEl) {
        mainEl.value = restored.heading;
      }
      if (subList) {
        subList.innerHTML = '';
        const rows = Array.isArray(restored?.values) ? restored.values : (Array.isArray(restored?.value) ? restored.value : []);
        if (rows.length) {
          rows.forEach(function(row){
            subList.appendChild(createSubLine(row));
          });
        } else {
          subList.appendChild(createSubLine());
        }
        attachSubHandlers(subList, mainEl);
        subList.querySelectorAll('textarea.autosize').forEach(function(ta){
          try { ta.style.height = 'auto'; ta.style.height = (ta.scrollHeight || 0) + 'px'; } catch (e) { /* noop */ }
        });
        updateSectionRemoveState(section);
      }
      // autosize main textarea
      if (mainEl) {
        mainEl.addEventListener('input', function(){ fireCriteriaChanged(); });
        try { mainEl.style.height = 'auto'; mainEl.style.height = (mainEl.scrollHeight||0)+'px'; } catch(e){}
      }
      // bind internal add/remove buttons for this section only
      section.querySelectorAll('.criteria-actions-row .criteria-add-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
          const sec = this.closest('.section') || section;
          addSubLineToSection(sec);
        });
      });
      section.querySelectorAll('.criteria-actions-row .criteria-remove-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
          const sec = this.closest('.section') || section;
          removeSubLineFromSection(sec);
        });
      });
      // re-run feather icons if available
      if (window.feather && typeof window.feather.replace==='function') { try { window.feather.replace(); } catch(e){} }
      if (restored && restored.key) {
        removedState.rows[restored.key] = [];
        removedState.rows[key] = [];
      }
      fireCriteriaChanged();
      updateAddSectionState();
      recomputeAutosizeAll();
    });
  }

  // Side button: remove last section (keep at least one)
  const removeSectionBtn = document.querySelector('.cis-criteria .criteria-remove-section-btn');
  if (removeSectionBtn) {
    removeSectionBtn.addEventListener('click', function(){
      const container = document.getElementById('criteria-sections-container');
      if (!container) return;
      const sections = container.querySelectorAll('.section');
      if (sections.length <= 1) return; // keep at least one
      const last = sections[sections.length - 1];
      const stored = collectSectionData(last);
      if (stored) {
        stashRemovedSection(stored);
        removedState.rows[stored.key] = [];
      }
      last.remove();
      fireCriteriaChanged();
      updateAddSectionState();
      recomputeAutosizeAll();
    });
  }

  // initialize add-section button state
  updateAddSectionState();

});
</script>
