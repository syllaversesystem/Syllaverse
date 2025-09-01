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
@endphp

<style>
  /* keep typography and spacing consistent with course-info and mission-vision */
  .cis-criteria { font-size: 13px; }
  .cis-criteria .section { padding: 6px 8px; border:0; border-radius:6px; background:#fff }
  /* use the same textarea look as mission-vision: form-control + cis-textarea */
  .cis-criteria .main-input { width:100%; border:none; background:transparent; padding:0; font-weight:400; font-family: inherit; font-size: inherit; line-height:1.15; color:#000; }
  /* sub items: indented and matching textarea style */
  .cis-criteria .sub-list { margin-top:6px; }
  .cis-criteria .sub-line { margin-left:18px; display:flex; gap:8px; align-items:center; }
  /* make sub input match Course Title (`.cis-input`) appearance */
  .cis-criteria .sub-input { width:100%; border:none; background:transparent; padding:0; }
  /* compact percent field, vertically centered with the sub title */
  .cis-criteria .sub-percent { flex:0 0 64px; width:64px; font-size:13px; padding:0 6px; margin-bottom:4px; height:28px; align-self:center; border:none; background:transparent; text-align:right; }
  /* yellow focus for main and sub fields (match .cis-input focus) */
  .cis-criteria .main-input:focus, .cis-criteria .sub-input:focus, .cis-criteria .sub-percent:focus { outline: none; box-shadow: none; background-color: #fffbe6; }
  .cis-criteria .section-head { display:flex; justify-content:flex-start; align-items:flex-start; gap:8px; }
  .cis-criteria .placeholder-muted { color:#6c757d; }
</style>

<div class="mt-3 cis-criteria">
  <div class="mb-1">
    <p class="mb-0 text-muted small">Keyboard shortcuts: press <strong>ENTER</strong> in a sub-item (or in the main heading) to add a new sub-item; press <strong>BACKSPACE</strong> on an empty sub-item to remove it.</p>
  </div>
  <table class="table table-bordered mb-4 cis-table">
    <colgroup>
      <col style="width: 16%">
      <col style="width: 84%">
    </colgroup>
    <tbody>
      <tr>
        <th class="align-top text-start cis-label">Criteria for Assessment
          <span id="unsaved-criteria" class="unsaved-pill d-none">Unsaved</span>
        </th>
        <td>
          <div class="d-flex gap-2">
            @foreach($sections as $idx => $sec)
              <div class="flex-fill">
                <div class="section" id="criteria-{{ $sec['key'] ?? ('section_' . ($idx+1)) }}">
                  <div class="section-head">
                    <input type="text" name="criteria_{{ $sec['key'] ?? ($idx+1) }}_display" data-section="{{ $sec['key'] ?? ('section_' . ($idx+1)) }}" class="main-input cis-input" placeholder="{{ $sec['heading'] ?: ucfirst($sec['key'] ?? 'Section') }}" value="{{ old('criteria_section_heading.' . $idx, $sec['heading'] ?? '') }}">
                  </div>
                  <div class="sub-list" aria-live="polite" data-init='{{ json_encode($sec['value'] ?? []) }}'></div>
                </div>
              </div>
            @endforeach
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
  // helper to create a sub input line
  function createSubLine(text) {
    const el = document.createElement('div');
    el.className = 'sub-line';
  const ta = document.createElement('input');
  ta.type = 'text';
  ta.className = 'sub-input cis-input';
  ta.placeholder = 'e.g., Midterm Exam';
    // if incoming text contains a trailing percentage like '... 20%' or '(20%)', extract it into percent field
    let pct = '';
    if (text) {
      const m = text.match(/^(.*?)\s*(?:\(?([0-9]{1,3}%?)\)?)?\s*$/);
      if (m) { ta.value = (m[1] || '').trim(); pct = (m[2] || '').trim(); }
      else { ta.value = text; }
    } else { ta.value = ''; }
    el.appendChild(ta);
    // percent input to the right
  const p = document.createElement('input');
  p.type = 'text';
  p.className = 'sub-percent cis-number';
  p.placeholder = '20%';
    p.value = pct || '';
    el.appendChild(p);
    return el;
  }

  // initialize each section: parse main textarea into first line and remaining sublines
  document.querySelectorAll('.cis-criteria .section').forEach(function(section){
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
      // ensure two default sublines if none exist
      if (subList.children.length === 0) {
        subList.appendChild(createSubLine());
        subList.appendChild(createSubLine());
      }
      attachSubHandlers(subList, main);
      fireCriteriaChanged();
    }

    // main: Enter should create a new sub line (do not allow newline in main)
    main.addEventListener('keydown', function(e){
      if (e.key === 'Enter') {
        e.preventDefault();
        const subListEl = section.querySelector('.sub-list');
        // do not add if last sub is empty
        const last = subListEl.lastElementChild;
        if (last) {
          const lastInp = last.querySelector('.sub-input');
          if (lastInp && lastInp.value.trim() === '') { lastInp.focus(); return; }
        }
        const newLine = createSubLine();
        subListEl.appendChild(newLine);
        attachSubHandlers(subListEl, main);
        const ta = newLine.querySelector('.sub-input');
        if (ta) ta.focus();
        fireCriteriaChanged();
      }
    });
    main.addEventListener('input', function(){ fireCriteriaChanged(); });

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
      } else if ((main.value || '').indexOf('\n') !== -1) {
        syncFromMain();
      } else {
        // default: two blank sub inputs so user fills them; placeholders show examples
        subList.innerHTML = '';
        subList.appendChild(createSubLine());
        subList.appendChild(createSubLine());
        attachSubHandlers(subList, main);
      }
    } catch (e) {
      // if JSON parsing fails, fallback to previous behavior
      if ((main.value || '').indexOf('\n') !== -1) { syncFromMain(); }
      else { subList.innerHTML = ''; subList.appendChild(createSubLine()); subList.appendChild(createSubLine()); attachSubHandlers(subList, main); }
    }
  });

  // attach handlers to sub textareas: Enter creates a new sibling sub, Backspace on empty removes it
  function attachSubHandlers(listEl, mainEl) {
    Array.from(listEl.querySelectorAll('.sub-line .sub-input')).forEach(function(inp){
      inp.addEventListener('keydown', function(e){
        if (e.key === 'Backspace' && inp.value === '') {
          // remove this line if more than one; focus moves sensibly
          const wrapper = inp.parentElement;
          const list = wrapper.parentElement;
          if (list.children.length > 1) {
            const prev = wrapper.previousElementSibling;
            wrapper.remove();
            if (prev) {
              const prevTa = prev.querySelector('.sub-input');
              if (prevTa) prevTa.focus();
            } else {
              // focus main header if no previous
              const main = list.parentElement.querySelector('.main-input');
              if (main) main.focus();
            }
          } else {
            // keep one empty field and focus it
            inp.value = '';
            inp.focus();
          }
          fireCriteriaChanged();
          e.preventDefault();
          return;
        }
        if (e.key === 'Enter') {
          // create new sub after this one, but avoid duplicates
          e.preventDefault();
          const wrapper = inp.parentElement;
          const next = wrapper.nextElementSibling;
          if (next) {
            const nextInp = next.querySelector('.sub-input');
            if (nextInp && nextInp.value.trim() === '') { nextInp.focus(); return; }
          }
          const newLine = createSubLine();
          wrapper.parentElement.insertBefore(newLine, wrapper.nextSibling);
          attachSubHandlers(listEl, mainEl);
          const ta = newLine.querySelector('.sub-input');
          if (ta) { ta.focus(); ta.setSelectionRange(0,0); }
          fireCriteriaChanged();
        }
      });
      inp.addEventListener('input', function(){ fireCriteriaChanged(); });
    });
    // percent inputs: Enter creates a new sub line and focuses the new sub title input
    Array.from(listEl.querySelectorAll('.sub-percent')).forEach(function(pin){
      pin.addEventListener('keydown', function(e){
        if (e.key === 'Enter') {
          e.preventDefault();
          const wrapper = pin.parentElement;
          const next = wrapper.nextElementSibling;
          if (next) {
            const nextTa = next.querySelector('.sub-input');
            if (nextTa && nextTa.value.trim() === '') { nextTa.focus(); return; }
          }
          const newLine = createSubLine();
          wrapper.parentElement.insertBefore(newLine, wrapper.nextSibling);
          attachSubHandlers(listEl, mainEl);
          const ta = newLine.querySelector('.sub-input');
          if (ta) { ta.focus(); ta.setSelectionRange(0,0); }
          fireCriteriaChanged();
        }
      });
      // when the user finishes editing the percent, normalize simple numeric input to include a percent sign
      pin.addEventListener('blur', function(){
        let v = (pin.value || '').toString().trim();
        if (v === '') return;
        // remove accidental multiple percent signs and whitespace
        v = v.replace(/%+/g, '%').replace(/\s+/g, '');
        // If it's a plain number like '20' or '20.5', append '%'
        if (/^\d+(?:\.\d+)?$/.test(v)) {
          pin.value = v + '%';
        } else if (/^\d+(?:\.\d+)?%$/.test(v)) {
          pin.value = v; // already ok
        } else {
          // leave custom values untouched
          pin.value = v;
        }
        fireCriteriaChanged();
      });
      pin.addEventListener('input', function(){ fireCriteriaChanged(); });
    });
  }

  function serializeSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (!section) return '';
  const main = section.querySelector('.main-input');
  const subLines = section.querySelectorAll('.sub-list .sub-line');
  let lines = [];
  // main title only (no main percent)
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
    const lecture = serializeSection('criteria-lecture');
    const laboratory = serializeSection('criteria-laboratory');
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
    const sections = document.querySelectorAll('.cis-criteria .section');
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

});
</script>
