// -----------------------------------------------------------------------------
// File: resources/js/faculty/assessment-map.js
// Description: Maps Assessment Schedule via AI; wires button, progress bar, and preview modal.
// -----------------------------------------------------------------------------

(function(){
  let _lastInput = null; // snapshot text
  let _lastReply = null; // AI reply (markdown/table)
  let _lastParsed = null; // parsed rows [{ name, week_marks, position }]
  let _lastJson = null;   // parsed JSON schedule [{ name, weeks: [bool], position }]
  let _autoApply = true; // when true, apply to calendar immediately after parsing

  function setProgress(stage, pct, msg, state){
    try {
      const wrap = document.getElementById('svAiMapProgressWrap');
      const fill = document.getElementById('svAiMapProgressFill');
      const label = document.getElementById('svAiMapStage');
      const pctEl = document.getElementById('svAiMapPct');
      const val = document.getElementById('svAiMapValidation');
      if (wrap) wrap.style.display = 'block';
      if (fill) fill.style.width = String(pct || 0) + '%';
      if (fill) { fill.classList.remove('state-ok','state-warn','state-running'); if (state) fill.classList.add(state); }
      if (label) label.textContent = stage || 'Processing';
      if (pctEl) pctEl.textContent = ((pct || 0)|0) + '%';
      if (val && msg) val.querySelector('span').textContent = msg;
    } catch(e) {}
  }
  function hideProgress(){ const wrap = document.getElementById('svAiMapProgressWrap'); if (wrap) wrap.style.display='none'; }

  function buildAssessmentMappingSnapshot(){
    const amRoot = document.querySelector('.assessment-mapping');
    if (!amRoot) return '';
    const distRows = Array.from(amRoot.querySelectorAll('table.distribution tr')).slice(1);
    const weekHeader = Array.from(amRoot.querySelectorAll('table.week tr:first-child th.week-number'))
      .map(th => (th.textContent || '').trim())
      .filter(t => t && t.toLowerCase() !== 'no weeks');
    const weekRows = Array.from(amRoot.querySelectorAll('table.week tr')).slice(1);
    const md = [];

    // First: Append TLA partial snapshot (frontend DOM)
    let tlaCount = 0;
    try {
      const tlaTable = document.getElementById('tlaTable');
      const tbody = tlaTable?.querySelector('tbody');
      const rows = Array.from(tbody?.querySelectorAll('tr:not(#tla-placeholder)') || []);
      md.push('');
      md.push('PARTIAL_BEGIN:tla');
      md.push('| Ch. | Topics / Reading List | Wks. | Topic Outcomes | ILO | SO | Delivery Method |');
      md.push('|:--:|:--|:--:|:--|:--:|:--:|:--|');
      if (rows.length === 0) {
        md.push('| - | - | - | - | - | - | - |');
      } else {
        tlaCount = rows.length;
        rows.forEach((row) => {
          const getVal = (sel) => {
            const el = row.querySelector(sel);
            if (!el) return '-';
            const tag = (el.tagName || '').toLowerCase();
            if (tag === 'input' || tag === 'textarea' || tag === 'select') {
              const v = (el.value ?? '').toString().trim();
              return v || '-';
            }
            const inner = el.querySelector && el.querySelector('input,textarea,select');
            if (inner) {
              const v = (inner.value ?? '').toString().trim();
              return v || '-';
            }
            const txt = (el.textContent || '').trim();
            return txt || '-';
          };
          const ch = getVal('[name*="[ch]"]');
          const topic = getVal('[name*="[topic]"]');
          const wks = getVal('[name*="[wks]"]');
          const outcomes = getVal('[name*="[outcomes]"]');
          const ilo = getVal('[name*="[ilo]"]');
          const so = getVal('[name*="[so]"]');
          const delivery = getVal('[name*="[delivery]"]');
          md.push('| ' + [ch, topic, wks, outcomes, ilo, so, delivery].map(s => (s||'-').replace(/\r?\n/g,' ').trim()).join(' | ') + ' |');
        });
      }
      md.push('PARTIAL_END:tla');
    } catch(e) { /* ignore TLA snapshot errors */ }

    // Next: Assessment Mapping (Task Calendar)
    md.push('');
    md.push('### Assessment Mapping (Task Calendar)');
    md.push('| Task | ' + (weekHeader.length ? weekHeader.join(' | ') : 'Week') + ' |');
    md.push('|:--|' + (weekHeader.length ? weekHeader.map(()=>':--:').join('|') : ':--:') + '|');
    distRows.forEach((dr, idx) => {
      const name = dr.querySelector('input.distribution-input')?.value?.trim() || '-';
      const cells = Array.from(weekRows[idx]?.querySelectorAll('td.week-mapping') || []);
      const vals = [];
      for (let cIdx = 0; cIdx < (weekHeader.length || cells.length); cIdx++) {
        const cell = cells[cIdx];
        if (!cell) { vals.push('-'); continue; }
        const txt = (cell.textContent || '').trim();
        const marked = txt.toLowerCase() === 'x' || cell.classList.contains('marked') || cell.getAttribute('data-mark') === 'x' || /x/i.test(cell.innerHTML);
        vals.push(marked ? 'x' : '-');
      }
      const rowVals = (weekHeader.length ? vals.slice(0, weekHeader.length) : (vals.length ? vals : ['-']));
      md.push('| ' + [name].concat(rowVals).join(' | ') + ' |');
    });
    if (!distRows.length) md.push('| - | - |');

    // Attach a marker line to indicate TLA row count for upstream validation
    md.push('');
    md.push(`<!-- TLA_ROWS:${tlaCount} -->`);
    return md.join('\n');
  }

  function openPreviewModal(){
    // Refresh input snapshot just before opening
    try {
      if (typeof window.updateAssessmentRealtimeSnapshot === 'function') window.updateAssessmentRealtimeSnapshot();
    } catch(e) {}
    try { _lastInput = buildAssessmentMappingSnapshot(); } catch(e) {}
    const overlay = document.createElement('div');
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:9999;display:flex;align-items:center;justify-content:center;';
    const modal = document.createElement('div');
    modal.style.cssText = 'width:80%;max-width:960px;max-height:80%;background:#fff;border-radius:8px;box-shadow:0 8px 32px rgba(0,0,0,0.25);display:flex;flex-direction:column;';
    const head = document.createElement('div');
    head.style.cssText = 'padding:12px 16px;border-bottom:1px solid #e5e5e5;display:flex;gap:8px;align-items:center;font-weight:600;';
    head.textContent = 'AI Preview — Assessment Schedule';
    const closeBtn = document.createElement('button');
    closeBtn.textContent = 'Close';
    closeBtn.style.cssText = 'margin-left:auto;padding:6px 10px;border:1px solid #ccc;background:#f7f7f7;border-radius:6px;cursor:pointer;';
    closeBtn.addEventListener('click', () => overlay.remove());
    head.appendChild(closeBtn);
    const body = document.createElement('div');
    body.style.cssText = 'padding:12px 16px;overflow:auto;';
    const footer = document.createElement('div');
    footer.style.cssText = 'padding:10px 16px;border-top:1px solid #e5e5e5;display:flex;gap:8px;justify-content:flex-end;';
    function addBlock(title, html){
      const wrap = document.createElement('div');
      wrap.style.cssText = 'margin-bottom:16px;padding:10px;border:1px solid #e6e9ed;border-radius:8px;background:#fcfcfc;';
      const t = document.createElement('div'); t.textContent = title; t.style.cssText = 'font-weight:600;margin-bottom:8px;color:#111827;';
      const content = document.createElement('div'); content.style.cssText = 'font-size:.9rem;line-height:1.45;color:#111827;';
      content.innerHTML = html;
      wrap.appendChild(t); wrap.appendChild(content); body.appendChild(wrap);
    }
    // Input snapshot (or placeholder)
    const safeInput = (_lastInput || '').replace(/[&<>]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[c]));
    addBlock('Input Snapshot', safeInput ? '<pre style="white-space:pre-wrap;word-wrap:break-word;margin:0">'+safeInput+'</pre>' : '<div class="text-muted">No snapshot captured yet. Use the button to build one.</div>');
    // AI output
    const formatted = (function(){
      const reply = _lastReply || '';
      if (!reply) return '<div class="text-muted">No AI output yet. Click “mapp assessment schedule” to generate.</div>';
      try { return (typeof window.formatAIResponse === 'function') ? window.formatAIResponse(reply) : '<pre style="white-space:pre-wrap;margin:0">'+(reply.replace(/[&<>]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[c])))+'</pre>'; }
      catch(e){ return '<pre style="white-space:pre-wrap;margin:0">'+reply+'</pre>'; }
    })();
    addBlock('AI Output', formatted);

    // JSON output preview if available
    if (_lastJson) {
      try {
        const pretty = JSON.stringify(_lastJson, null, 2).replace(/[&<>]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[c]));
        addBlock('AI JSON', '<pre style="white-space:pre-wrap;margin:0">'+pretty+'</pre>');
      } catch(e) {
        addBlock('AI JSON', '<div class="text-muted">Invalid JSON payload.</div>');
      }
    }

    // Footer actions
    const applyBtn = document.createElement('button');
    applyBtn.textContent = 'Apply to Calendar';
    applyBtn.style.cssText = 'padding:6px 12px;border:1px solid #ccc;background:#f7f7f7;border-radius:6px;cursor:pointer;';
    applyBtn.addEventListener('click', function(){
      try {
        applyParsedToCalendar();
        closeBtn.click();
      } catch(e){ alert('Failed to apply: '+(e?.message||e)); }
    });
    footer.appendChild(applyBtn);
    modal.appendChild(head);
    modal.appendChild(body);
    modal.appendChild(footer);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
  }

  function parseAssessmentMarkdown(md){
    try {
      const lines = (md||'').split(/\r?\n/);
      // Expect a table starting with | Task |
      let start = -1;
      for (let i=0;i<lines.length;i++){
        if (/^\|\s*Task\s*\|/i.test(lines[i])) { start = i; break; }
      }
      if (start === -1) return [];
      const header = lines[start].replace(/^\|/,'').replace(/\|$/,'').split('|').map(s=>s.trim());
      const weekCols = header.slice(1); // remaining columns are weeks
      const rows = [];
      for (let i=start+2;i<lines.length;i++){
        const ln = lines[i].trim();
        if (!ln.startsWith('|')) break;
        const cells = ln.replace(/^\|/,'').replace(/\|$/,'').split('|').map(s=>s.trim());
        const name = cells[0] || '-';
        const marks = cells.slice(1).map(v => (v && /x/i.test(v)) ? 'x' : '-');
        rows.push({ name, week_marks: marks, position: rows.length });
      }
      return rows;
    } catch(e){ return []; }
  }

  async function callAiForAssessment(){
    // Validate sufficiency
    setProgress('Preparing', 5, 'Checking schedule and tasks…', 'state-running');
    const snap = buildAssessmentMappingSnapshot();
    _lastInput = snap;
    if (!snap){ setProgress('Complete', 100, 'Add weeks and tasks to map.', 'state-warn'); return; }
    // Require TLA context presence
    const hasTla = /PARTIAL_BEGIN:tla[\s\S]*?PARTIAL_END:tla/.test(snap);
    const tlaRowsMatch = snap.match(/<!--\s*TLA_ROWS:(\d+)\s*-->/);
    const tlaRows = tlaRowsMatch ? parseInt(tlaRowsMatch[1], 10) : 0;
    if (!hasTla || tlaRows <= 0){
      setProgress('Complete', 100, 'TLA activities missing. Add TLA rows (with Wks.) first.', 'state-warn');
      openPreviewModal();
      return;
    }
    try {
      setProgress('Calling AI', 35, 'Generating suggested schedule…', 'state-running');
      const syllabusId = document.getElementById('syllabus-document')?.getAttribute('data-syllabus-id') || null;
      const endpoint = syllabusId ? `/faculty/syllabi/${syllabusId}/ai-chat` : null;
      if (!endpoint) { setProgress('Complete', 100, 'AI service unavailable.', 'state-warn'); return; }
      const fd = new FormData();
      const instruction = [
        'STRICT mode: Derive schedule ONLY from TLA activities and their `Wks.` values.',
        'Task eligibility: Map a calendar task ONLY if it explicitly matches a TLA-referenced activity by name/type (e.g., Quizzes/Chapter Tests ↔ Quiz, Laboratory Exercises ↔ Lab Exercises, Laboratory Exams ↔ Lab Exam). Do NOT invent tasks.',
        'Week eligibility: Mark x ONLY on week columns that are explicitly present in TLA `Wks.` ranges for the corresponding activity. Do NOT infer additional weeks.',
        'Completeness rule: For any eligible task, mark ALL weeks that are present in TLA `Wks.` (i.e., fill every allowed week) unless the snapshot clearly indicates the task should be limited to a subset. Do not leave eligible weeks empty.',
        'Correction rule: The snapshot is CURRENT. Scan TLA + Assessment Mapping. If an existing x in the calendar does NOT align to that task’s TLA week(s), REMOVE it (leave empty).',
        'Output two parts in order:',
        '1) A single Markdown table: | Task | <Week columns…> | with x marks, from TLA weeks only (mark all eligible weeks).',
        '2) A JSON array `schedule`: [{"name":"<task>","weeks":[true|false,...]}] strictly derived from TLA weeks for that task, marking all eligible weeks.',
        'Keep existing task names and week columns. No prose. Place JSON after the table.',
        'Treat all provided context blocks as the latest updates; never rely on older versions or inferred fields.'
      ].join('\n');
      fd.append('message', instruction);
      fd.append('context_phase3', snap);
      fd.append('phase', '3');
      const res = await fetch(endpoint, { method:'POST', headers:{ 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 'Accept':'application/json' }, body: fd });
      const j = await res.json().catch(()=>({}));
      if (res.ok && j && j.reply){
        _lastReply = j.reply;
        setProgress('Parsing', 65, 'Reading table…', 'state-running');
        _lastParsed = parseAssessmentMarkdown(_lastReply);
        // Try parse JSON part
        _lastJson = parseAssessmentJson(_lastReply);
        // Auto-apply to calendar if enabled
        if (_autoApply) {
          try {
            setProgress('Applying', 90, 'Auto-applying to calendar…', 'state-running');
            applyParsedToCalendar();
            setProgress('Applied', 95, 'Applied to calendar.', 'state-ok');
          } catch(e) {
            setProgress('Complete', 100, 'Auto-apply failed: '+(e?.message||e), 'state-warn');
          }
        }
        // Do not open preview automatically in flow; keep modal accessible via shortcut/button
        setProgress('Done', 100, 'Applied. Preview available on demand.', 'state-ok');
      } else {
        setProgress('Complete', 100, j?.error ? ('AI Error: '+j.error) : 'No reply received.', 'state-warn');
      }
    } catch(err){ setProgress('Complete', 100, 'Network/AI error: '+(err?.message||String(err)), 'state-warn'); }
  }

  function parseAssessmentJson(text){
    try {
      // Extract the first JSON array/object from the reply
      const m = text.match(/\[\s*\{[\s\S]*?\}\s*\]/);
      if (!m) return null;
      const arr = JSON.parse(m[0]);
      if (!Array.isArray(arr)) return null;
      // Normalize to { name, week_marks, position }
      const rows = arr.map((r, idx) => ({
        name: (r.name || '').toString(),
        week_marks: Array.isArray(r.weeks) ? r.weeks.map(b => (b ? 'x' : '-')) : [],
        position: idx
      }));
      return rows;
    } catch(e){ return null; }
  }

  function applyParsedToCalendar(){
    const amRoot = document.querySelector('.assessment-mapping');
    const sourceRows = (_lastJson && _lastJson.length) ? _lastJson : _lastParsed;
    if (!amRoot || !sourceRows || !sourceRows.length) throw new Error('No parsed AI data to apply');
    const distRows = Array.from(amRoot.querySelectorAll('table.distribution tr')).slice(1);
    const weekRows = Array.from(amRoot.querySelectorAll('table.week tr')).slice(1);
    const normalize = (s) => (s||'').toString().trim().replace(/\s+/g,' ').toLowerCase();
    const canonicalTaskKey = (name) => {
      const n = normalize(name);
      if (/^finals?$/.test(n) || /final exam/.test(n)) return 'final exam';
      if (/^midterms?$/.test(n) || n === 'midterm' || /midterm exam/.test(n)) return 'midterm exam';
      if (n.includes('quiz') || n.includes('chapter test')) return 'quizzes/ chapter tests';
      if (n.includes('assignment') || n.includes('research review')) return 'assignments/ research review';
      if (n.includes('laboratory exam') || n.includes('lab exam')) return 'laboratory exams';
      if (n.includes('laboratory exercise') || n.includes('lab exercise')) return 'laboratory exercises';
      if (n.includes('project')) return 'projects';
      return n;
    };
    // Build allowed weeks per task type from TLA DOM
    const allowedWeeks = (function(){
      const map = {
        'midterm exam': new Set(),
        'final exam': new Set(),
        'quizzes/ chapter tests': new Set(),
        'assignments/ research review': new Set(),
        'projects': new Set(),
        'laboratory exercises': new Set(),
        'laboratory exams': new Set(),
      };
      const tlaTable = document.getElementById('tlaTable');
      const tbody = tlaTable?.querySelector('tbody');
      const rows = Array.from(tbody?.querySelectorAll('tr:not(#tla-placeholder)') || []);
      const addRange = (set, wkStr) => {
        const s = (wkStr||'').toString().trim();
        if (!s) return;
        const parts = s.split(/\s*,\s*/);
        parts.forEach(p => {
          const m = p.match(/^(\d+)(?:\s*-\s*(\d+))?$/);
          if (!m) return;
          const start = parseInt(m[1],10), end = m[2] ? parseInt(m[2],10) : start;
          for (let w=start; w<=end; w++) set.add(String(w));
        });
      };
      rows.forEach(r => {
        const topic = (r.querySelector('[name*="[topic]"]')?.value || '').toLowerCase();
        const wks = (r.querySelector('[name*="[wks]"]')?.value || '').toLowerCase();
        if (/midterm/.test(topic)) addRange(map['midterm exam'], wks);
        if (/final/.test(topic)) addRange(map['final exam'], wks);
        if (/quiz|chapter test/.test(topic)) addRange(map['quizzes/ chapter tests'], wks);
        if (/assignments?|research review/.test(topic)) addRange(map['assignments/ research review'], wks);
        if (/project/.test(topic)) addRange(map['projects'], wks);
        if (/laboratory exercises?|lab exercises?/.test(topic)) addRange(map['laboratory exercises'], wks);
        if (/laboratory exams?|lab exams?/.test(topic)) addRange(map['laboratory exams'], wks);
      });
      return map;
    })();
    // Build map of task name -> row index in calendar
    const taskIndex = new Map();
    distRows.forEach((dr, idx) => {
      const name = dr.querySelector('input.distribution-input')?.value || '';
      taskIndex.set(normalize(name), idx);
    });
    // Build set of task names present in sourceRows
    const sourceNames = new Set(sourceRows.map(r => normalize(r.name)));
    // First, clear any calendar marks for tasks not present in source (TLA alignment)
    distRows.forEach((dr, idx) => {
      const name = normalize(dr.querySelector('input.distribution-input')?.value || '');
      if (!sourceNames.has(name)) {
        const cells = Array.from(weekRows[idx]?.querySelectorAll('td.week-mapping') || []);
        cells.forEach(cell => { cell.classList.remove('marked'); cell.removeAttribute('data-mark'); cell.textContent = ''; });
      }
    });
    // Apply marks for matching tasks
    sourceRows.forEach(row => {
      const idx = taskIndex.get(normalize(row.name));
      if (typeof idx !== 'number') return; // task not found
      const cells = Array.from(weekRows[idx]?.querySelectorAll('td.week-mapping') || []);
      // Clear row first to remove any marks not present in source
      cells.forEach(cell => { cell.classList.remove('marked'); cell.removeAttribute('data-mark'); cell.textContent = ''; });
      const taskKey = canonicalTaskKey(row.name);
      const allowed = allowedWeeks[taskKey];
      if (!allowed || !allowed.size) {
        // Strict mode: if task has no allowed weeks derived from TLA, do not apply any marks
        return;
      }
      for (let c=0;c<cells.length;c++){
        const mark = row.week_marks[c];
        const cell = cells[c];
        if (!cell) continue;
        // Determine the week label for this column from header row
        const headerThs = amRoot.querySelectorAll('table.week tr:first-child th.week-number');
        const wkLabel = (headerThs[c]?.textContent || '').trim();
        const wkAllowed = allowed.has(String(wkLabel)) || // single week
                          // support ranges like 1-2 in header by expanding
                          (() => { const m = wkLabel.match(/^(\d+)-(\d+)$/); if (m){ const s=parseInt(m[1],10), e=parseInt(m[2],10); for (let w=s; w<=e; w++){ if (allowed.has(String(w))) return true; } } return false; })();
        if (mark === 'x' && wkAllowed) {
          cell.classList.add('marked');
          cell.setAttribute('data-mark','x');
          cell.textContent = 'x';
        }
      }
    });
    // Update realtime snapshot after applying
    try { if (typeof window.updateAssessmentRealtimeSnapshot === 'function') window.updateAssessmentRealtimeSnapshot(); } catch(e){}
  }

  // Auto-apply controls
  function setAutoApply(enabled){ _autoApply = !!enabled; }
  function applyLatestToCalendar(){ applyParsedToCalendar(); }

  function init(){
    const btn = document.getElementById('svAiAutoMapBtn');
    if (btn) btn.addEventListener('click', callAiForAssessment);
    // Build and merge snapshot into realtime context
    function updateRealtimeSnapshot(){
      try {
        const snap = buildAssessmentMappingSnapshot();
        const existing = window._svRealtimeContext || '';
        const cleaned = (existing || '')
          .replace(/PARTIAL_BEGIN:tla[\s\S]*?PARTIAL_END:tla/g, '')
          .replace(/###\s*Assessment Mapping \(Task Calendar\)[\s\S]*$/m, '')
          .trim();
        const merged = [cleaned, snap].filter(Boolean).map(s=>s.trim()).join('\n\n').trim();
        window._svRealtimeContext = merged;
      } catch(e) { /* ignore */ }
    }
    try { window.updateAssessmentRealtimeSnapshot = updateRealtimeSnapshot; } catch(e) {}

    // Snapshot on actions: inputs/changes and DOM mutations in both areas
    const amRoot = document.querySelector('.assessment-mapping');
    const tlaTable = document.getElementById('tlaTable');
    const bindListeners = (root) => {
      if (!root) return;
      root.addEventListener('input', () => setTimeout(updateRealtimeSnapshot, 30));
      root.addEventListener('change', () => setTimeout(updateRealtimeSnapshot, 30));
      const mo = new MutationObserver((muts) => {
        for (const m of muts) {
          if (m.type === 'childList' && (m.addedNodes.length || m.removedNodes.length)) { updateRealtimeSnapshot(); return; }
          if (m.type === 'attributes') { updateRealtimeSnapshot(); return; }
        }
      });
      mo.observe(root, { childList: true, subtree: true, attributes: true, attributeFilter: ['value','class','data-mark'] });
    };
    bindListeners(amRoot);
    bindListeners(tlaTable);

    // Initial snapshot build so context exists
    setTimeout(updateRealtimeSnapshot, 50);
    // Shortcut Shift+1 to open preview if available
    document.addEventListener('keydown', function(e){
      if (e.shiftKey && e.key === '!') { // Shift+1 yields '!'
        e.preventDefault();
        openPreviewModal();
      }
    });
  }

  document.addEventListener('DOMContentLoaded', init);

  // Expose minimal debug
  try {
    window._assessmentMap = {
      buildAssessmentMappingSnapshot,
      callAiForAssessment,
      applyParsedToCalendar,
      applyLatestToCalendar,
      setAutoApply,
      enableAutoApply: () => setAutoApply(true),
      disableAutoApply: () => setAutoApply(false)
    };
  } catch(e){}
})();
