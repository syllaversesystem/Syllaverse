@php
  $local = $syllabus->courseInfo ?? null;
  $lectureText = old('criteria_lecture', $local?->criteria_lecture ?? "");
  $labText     = old('criteria_laboratory', $local?->criteria_laboratory ?? "");
@endphp

<style>
  /* keep typography and spacing consistent with course-info and mission-vision */
  .cis-criteria { font-size: 13px; }
  .cis-criteria .section { padding: 6px 8px; border:0; border-radius:6px; background:#fff }
  /* use the same textarea look as mission-vision: form-control + cis-textarea */
  .cis-criteria .main-input { width:100%; border:none; background:transparent; padding:0; font-weight:600; font-family: inherit; font-size: inherit; line-height:1.15; color:#000; }
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
            <div class="flex-fill">
              <div class="section" id="criteria-lecture">
                <div class="section-head">
                  <input type="text" name="criteria_lecture_display" data-section="lecture" class="main-input cis-input" placeholder="Lecture (40%)" value="{{ preg_replace('/\s*\(?\d+%?\)?$/','', explode('\n', trim($lectureText))[0] ?? '') }}">
                </div>
                <div class="sub-list" aria-live="polite"></div>
              </div>
            </div>

            <div class="flex-fill">
              <div class="section" id="criteria-laboratory">
                <div class="section-head">
                  <input type="text" name="criteria_laboratory_display" data-section="laboratory" class="main-input cis-input" placeholder="Laboratory (60%)" value="{{ preg_replace('/\s*\(?\d+%?\)?$/','', explode('\n', trim($labText))[0] ?? '') }}">
                </div>
                <div class="sub-list" aria-live="polite"></div>
              </div>
            </div>
          </div>

          {{-- Hidden inputs to submit serialized criteria lines (one per section) --}}
          <input type="hidden" name="criteria_lecture" id="criteria_lecture_input">
          <input type="hidden" name="criteria_laboratory" id="criteria_laboratory_input">
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

    // initial sync: if the textarea contains multiple lines, parse it; otherwise leave main blank and create defaults
    if ((main.value || '').indexOf('\n') !== -1) { syncFromMain(); }
    else {
      // default: two blank sub inputs so user fills them; placeholders show examples
      subList.innerHTML = '';
      subList.appendChild(createSubLine());
      subList.appendChild(createSubLine());
      attachSubHandlers(subList, main);
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
    document.getElementById('criteria_lecture_input').value = serializeSection('criteria-lecture');
    document.getElementById('criteria_laboratory_input').value = serializeSection('criteria-laboratory');
  };

  function fireCriteriaChanged(){
    document.dispatchEvent(new Event('criteriaChanged'));
  }

});
</script>
