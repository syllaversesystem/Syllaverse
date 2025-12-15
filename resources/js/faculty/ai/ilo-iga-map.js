(function(){
  try { console.log('[ILO–IGA] script loaded'); window._svIloIgaLoaded = true; } catch(e) {}
  let _inFlight = false;
  let _lastAiOutput = '';

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
      if (pctEl) pctEl.textContent = (((pct || 0) | 0)) + '%';
      if (val && msg) val.querySelector('span').textContent = msg;
    } catch(e) {}
  }
  function hideProgress(){ const wrap = document.getElementById('svAiMapProgressWrap'); if (wrap) wrap.style.display='none'; }

  // Build Input Snapshot: Course Rationale & Description + TLA strategies
  function collectIloIgaFactors(){
    try {
      const lines = [];
      // Course Rationale & Description (from course_description)
      try {
        const descEl = document.querySelector('[name="course_description"]');
        const text = descEl ? String(descEl.value || '').trim() : '';
        lines.push('PARTIAL_BEGIN:course_info');
        lines.push('TITLE: Course Rationale and Description');
        lines.push('FORMAT: TEXT');
        lines.push('TEXT:');
        lines.push(text);
        lines.push('PARTIAL_END:course_info');
      } catch(e) {}
      // ILOs (codes + descriptions) after Course Info
      try {
        const iloList = document.getElementById('syllabus-ilo-sortable');
        if (iloList) {
          const rows = Array.from(iloList.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="ilos[]"]') || r.querySelector('.ilo-badge'));
          if (rows.length) {
            lines.push('');
            lines.push('PARTIAL_BEGIN:ilos');
            lines.push('TITLE: Intended Learning Outcomes');
            lines.push('COLUMNS: Code | Description');
            rows.forEach((r, i) => {
              const codeEl = r.querySelector('.ilo-badge');
              const codeHidden = r.querySelector('input[name="code[]"]');
              const code = (codeEl && codeEl.textContent) ? codeEl.textContent.trim() : (codeHidden ? String(codeHidden.value || '').trim() : `ILO${i+1}`);
              const ta = r.querySelector('textarea[name="ilos[]"]');
              const desc = ta ? String(ta.value || '').trim() : '';
              lines.push(`ROW: ${code} | ${desc || '-'}`);
            });
            lines.push('PARTIAL_END:ilos');
          }
        }
      } catch(e) {}
      // IGAs (code + title + description) after ILOs
      try {
        const igaList = document.getElementById('syllabus-iga-sortable');
        if (igaList) {
          const rows = Array.from(igaList.querySelectorAll('tr.iga-row'));
          if (rows.length) {
            lines.push('');
            lines.push('PARTIAL_BEGIN:igas');
            lines.push('TITLE: Institutional Graduate Attributes');
            lines.push('COLUMNS: Code | Title | Description');
            rows.forEach((r, i) => {
              const codeHidden = r.querySelector('input[name="code[]"]');
              const badge = r.querySelector('.iga-badge');
              const code = (badge && badge.textContent ? badge.textContent.trim() : (codeHidden ? String(codeHidden.value || '').trim() : `IGA${i+1}`));
              const titleTa = r.querySelector('textarea[name="iga_titles[]"]');
              const descTa = r.querySelector('textarea[name="igas[]"]');
              const title = titleTa ? String(titleTa.value || '').trim() : '';
              const desc = descTa ? String(descTa.value || '').trim() : '';
              lines.push(`ROW: ${code} | ${title || '-'} | ${desc || '-'}`);
            });
            lines.push('PARTIAL_END:igas');
          }
        }
      } catch(e) {}
      // AMD (Assessment Tasks) minimal: Code | Task | ILO
      try {
        const amdInput = document.getElementById('assessment_tasks_data');
        const raw = amdInput ? String(amdInput.value || '').trim() : '';
        if (raw) {
          try {
            const parsed = JSON.parse(raw);
            const mini = buildAmdMiniTable(parsed);
            if (mini) lines.push(mini);
          } catch(e) { /* ignore parse errors */ }
        }
      } catch(e) {}
      return lines.join('\n');
    } catch(e){ return ''; }
  }

  // Build structured AMD table: header + rows per section (main + subs)
  function buildAmdStructuredTable(parsed){
    try {
      if (!parsed) return '';
      const sections = Array.isArray(parsed.sections) ? parsed.sections : [];
      if (!sections.length) return '';
      const out = [];
      out.push('');
      out.push('PARTIAL_BEGIN:assessment_tasks_structured');
      out.push('TITLE: Assessment Method and Distribution (Structured)');
      out.push('TABLE:');
      sections.forEach((section, idx) => {
        const secNum = (section.section_num ?? (idx+1));
        const header = '| Code | Task | I/R/D | % | ILO(1..N) | C | P | A |';
        if (idx > 0) out.push(`Section ${secNum}`);
        out.push(header);
        const main = section.main_row || {};
        const mainCode = (main.code ?? '').toString();
        const mainTask = (main.task ?? '').toString();
        const mainPct = (main.percent ?? '').toString();
        const mainIlo = (section.main_ilo_columns || []).map(v => (v ?? '').toString());
        out.push(`| ${mainCode} | ${mainTask} |  | ${mainPct} | [${mainIlo.join(', ')}] |  |  |  |`);
        const subs = Array.isArray(section.sub_rows) ? section.sub_rows : [];
        subs.forEach(sub => {
          const code = (sub.code ?? '').toString();
          const task = (sub.task ?? '').toString();
          const ird = (sub.ird ?? '').toString();
          const pct = (sub.percent ?? '').toString();
          const ilo = (sub.ilo_columns || []).map(v => (v ?? '').toString());
          const cpa = Array.isArray(sub.cpa_columns) ? sub.cpa_columns : [];
          const c = (cpa[0] ?? '').toString();
          const p = (cpa[1] ?? '').toString();
          const a = (cpa[2] ?? '').toString();
          out.push(`| ${code} | ${task} | ${ird} | ${pct} | [${ilo.join(', ')}] | ${c} | ${p} | ${a} |`);
        });
      });
      out.push('PARTIAL_END:assessment_tasks_structured');
      return out.join('\n');
    } catch(e){ return ''; }
  }

  // Build minimal AMD table: Code | Task | ILO
  function buildAmdMiniTable(parsed){
    try {
      if (!parsed) return '';
      const sections = Array.isArray(parsed.sections) ? parsed.sections : [];
      if (!sections.length) return '';
      function deriveIloCodes(arr){
        const a = Array.isArray(arr) ? arr : [];
        if (!a.length) return [];
        const tokens = [];
        let looksLikeCounts = false;
        // Heuristic: if length >= 3 or any entry is numeric >= 1 or '0'/'-' present, treat as counts per ILO column
        if (a.length >= 3) looksLikeCounts = true;
        for (const v of a) {
          const s = String(v ?? '').trim();
          const n = Number(s);
          if (!Number.isNaN(n)) { looksLikeCounts = true; break; }
          if (s === '-' || s === '') { looksLikeCounts = true; break; }
        }
        if (looksLikeCounts) {
          for (let i = 0; i < a.length; i++) {
            const v = a[i];
            const s = String(v ?? '').trim();
            const n = Number(s);
            if (!Number.isNaN(n) && n > 0) tokens.push('ILO' + (i + 1));
          }
          return tokens;
        }
        // Otherwise treat entries as ILO numbers/tokens
        for (const v of a) {
          const s = String(v ?? '').trim();
          if (!s) continue;
          if (/^ILO\s*\d+$/i.test(s)) { tokens.push(s.toUpperCase().replace(/\s+/g,'')); continue; }
          if (/^\d+$/.test(s)) { tokens.push('ILO' + s); continue; }
        }
        return tokens;
      }
      const out = [];
      out.push('');
      out.push('PARTIAL_BEGIN:assessment_tasks_min');
      out.push('TITLE: Assessment Tasks (Code, Task, ILO)');
      out.push('COLUMNS: Code | Task | ILO');
      sections.forEach((section) => {
        const main = section.main_row || {};
        const mainCode = (main.code ?? '').toString();
        const mainTask = (main.task ?? '').toString();
        const mainIlo = deriveIloCodes(section.main_ilo_columns || []);
        if (mainIlo.length) {
          out.push(`ROW: ${mainCode} | ${mainTask} | [${mainIlo.join(', ')}]`);
        }
        const subs = Array.isArray(section.sub_rows) ? section.sub_rows : [];
        subs.forEach(sub => {
          const code = (sub.code ?? '').toString();
          const task = (sub.task ?? '').toString();
          const ilo = deriveIloCodes(sub.ilo_columns || []);
          if (ilo.length) {
            out.push(`ROW: ${code} | ${task} | [${ilo.join(', ')}]`);
          }
        });
      });
      out.push('PARTIAL_END:assessment_tasks_min');
      return out.join('\n');
    } catch(e){ return ''; }
  }

  async function runIloIgaAiFlow(){
    if (_inFlight) return;
    _inFlight = true;
    try {
      setProgress('Preparing', 10, 'Preparing ILO–IGA snapshot…', 'state-running');
      const snapshot = collectIloIgaFactors();
      // Do not auto-open preview modal; run silently in background
      _lastAiOutput = _lastAiOutput || '';
      // Send to AI with a strict prompt to reply only "hotdog"
      setProgress('Sending', 40, 'Contacting AI…', 'state-running');
      const m = (location.pathname||'').match(/\/faculty\/syllabi\/(\d+)/);
      const syllabusId = m ? m[1] : null;
      if (!syllabusId) {
        setProgress('Error', 100, 'Cannot find syllabus ID in URL.', 'state-warn');
        _inFlight = false; return;
      }
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const fd = new FormData();
      // Mapping instructions: Task Code → ILO → IGA, with matrix output
      let userPrompt = [
        'We are mapping Assessment Task Codes to ILO–IGA alignment.',
        'Use only these snapshot blocks: PARTIAL_BEGIN:assessment_tasks_min (Code, Task, ILO), PARTIAL_BEGIN:ilos (Code | Description), PARTIAL_BEGIN:igas (Code | Title | Description).',
        'Rules:',
        '- If a task already lists ILO codes in the AMD block, use those ILOs.',
        '- If a task has no ILOs and Teaching, Learning, and Assessment Strategies (TLA) are provided, infer the best ILO(s) by reading the TLA strategies and matching to ILO descriptions.',
        '- If TLA strategies are missing or empty, do NOT infer ILOs for tasks that do not list any; leave such tasks unmapped (do not place them in any IGA column).',
        '- Uniqueness constraint: For each Task–ILO pair, assign at most one IGA column. Do NOT repeat the same task code across multiple IGA columns within the same ILO row. If multiple IGAs seem valid, select the single strongest match. The same task code may appear again on a different ILO row only if the task truly maps to that other ILO.',
        '- For each selected ILO, choose the most relevant IGA by aligning the ILO description to the IGA statements (1 per Task–ILO; use \'-\' if none).',
        'Output format (must strictly match):',
        'Title: ILO–IGA Mapping of Assessment Tasks (AT)',
        '| ILOs | IGA1 | IGA2 | ... |',
        '| :---: | :---: | :---: | ... |',
        '| ILO1 | codes | codes | ... |',
        '| ILO2 | codes | codes | ... |',
        'Where each cell contains comma-separated Assessment Task Codes (e.g., ME,FE) with no spaces. Do not duplicate a code within a cell. Use only IGA columns that exist in the snapshot; omit an IGA column entirely if no ILO row has any task code in that column. Include only ILO rows that have at least one task code placed in any IGA column (omit empty ILO rows). Use "-" only for cells within included rows/columns that happen to be empty. Do not invent tasks/ILOs/IGAs beyond the snapshot.',
        '',
        'Then, after the table, output a fenced JSON block (```json ... ```), with NO extra commentary, matching the app\'s save format (Faculty\\Syllabus\\IloIgaController::saveMapping):',
        '- "iga_labels": array of IGA column labels as strings, using exactly the IGA Codes from the IGAs snapshot (like "IGA1", "IGA2"), in the same left-to-right order as your table. Do not include placeholder columns.',
        '- "mappings": array of row objects, include only ILO rows that appear in your table (omit empty rows), preserving row order, with fields:',
        '  - "ilo_text": the ILO code string for that row (e.g., "ILO1", "ILO2"). Do not use the description here.',
        '  - "igas": an object whose keys are the same labels from "iga_labels"; each value is either a string of comma-separated task codes (no spaces, e.g., "ME,FE") for that cell, or an empty string if none for that column. Do not duplicate a task code across keys within the same row; each Task–ILO pair maps to at most one IGA key.',
        '  - "position": the zero-based row index for the ILO row.',
        'Example JSON shape (values illustrative only):',
        '```json',
        '{',
        '  "iga_labels": ["IGA1", "IGA2", "IGA3"],',
        '  "mappings": [',
        '    {',
        '      "ilo_text": "ILO1",',
        '      "igas": { "IGA1": "ME,FE", "IGA2": "", "IGA3": "LEC1" },',
        '      "position": 0',
        '    },',
        '    {',
        '      "ilo_text": "ILO2",',
        '      "igas": { "IGA1": "PRJ", "IGA2": "", "IGA3": "" },',
        '      "position": 1',
        '    }',
        '  ]',
        '}',
        '```'
      ].join(' ');
      // Include current TLA text inline so the model can read it when inferring ILOs
      try {
        const tlaEl = document.getElementById('tla_strategies') || document.querySelector('[name="tla_strategies"]');
        const tlaVal = tlaEl ? String(tlaEl.value || '').trim() : '';
        if (tlaVal) {
          userPrompt += ['','TLA_BEGIN', tlaVal, 'TLA_END'].join('\n');
        }
      } catch(e) {}
      fd.append('message', userPrompt);
      // Attach snapshot as context so backend includes it as system context
      if (snapshot) fd.append('context', snapshot);
      const res = await fetch(`/faculty/syllabi/${syllabusId}/ai-chat`, { method: 'POST', headers: token ? { 'X-CSRF-TOKEN': token } : {}, body: fd });
      setProgress('Waiting', 70, 'Awaiting response…', 'state-running');
      if (!res.ok) {
        setProgress('Error', 100, 'AI request failed.', 'state-warn');
        _inFlight = false; return;
      }
      const data = await res.json().catch(() => ({}));
      const msg = data?.message || data?.reply || data?.response || '';
      _lastAiOutput = msg || '';
      // Update modal output if present
      try {
        const pre = document.getElementById('svIloIgaOutputPre');
        if (pre) pre.textContent = _lastAiOutput || '';
      } catch(e){}
      // Attempt to auto-save JSON output (if present)
      try {
        if (_lastAiOutput) {
          const jsonBlock = extractJsonBlock(_lastAiOutput);
          if (jsonBlock) {
            const parsed = JSON.parse(jsonBlock);
            const normalized = normalizeIloIgaJson(parsed);
            if (normalized && Array.isArray(normalized.iga_labels) && Array.isArray(normalized.mappings)) {
              await postIloIgaMapping(syllabusId, normalized);
            }
          }
        }
      } catch(err) {
        try { console.warn('[ILO–IGA] Auto-save skipped:', err); } catch(_e){}
      }
      setProgress('Done', 100, 'AI response received.', 'state-ok');
    } catch(e){
      setProgress('Error', 100, 'Unexpected error while sending.', 'state-warn');
    } finally {
      _inFlight = false;
    }
  }

  // Inline overlay modal similar to Assessment Schedule preview
  let _overlayIga = null;
  function escapeHtml(s){ return String(s||'').replace(/[&<>]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[c])); }
  function openIloIgaModal(){
    try { if (_overlayIga) { _overlayIga.remove(); _overlayIga = null; } } catch(e){}
    _overlayIga = document.createElement('div');
    _overlayIga.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:9999;display:flex;align-items:center;justify-content:center;';
    const modal = document.createElement('div');
    modal.style.cssText = 'width:70%;max-width:800px;max-height:70%;background:#fff;border-radius:8px;box-shadow:0 8px 32px rgba(0,0,0,0.25);display:flex;flex-direction:column;';
    const head = document.createElement('div');
    head.style.cssText = 'padding:12px 16px;border-bottom:1px solid #e5e5e5;display:flex;gap:8px;align-items:center;font-weight:600;';
    head.textContent = 'AI Preview — ILO–IGA';
    const closeBtn = document.createElement('button');
    closeBtn.textContent = 'Close';
    closeBtn.style.cssText = 'margin-left:auto;padding:6px 10px;border:1px solid #ccc;background:#f7f7f7;border-radius:6px;cursor:pointer;';
    closeBtn.addEventListener('click', () => { try { _overlayIga.remove(); _overlayIga = null; } catch(e){} });
    head.appendChild(closeBtn);
    const body = document.createElement('div');
    body.style.cssText = 'padding:12px 16px;overflow:auto;';
    const inputBlock = document.createElement('div');
    inputBlock.style.cssText = 'margin-bottom:16px;padding:10px;border:1px solid #e6e9ed;border-radius:8px;background:#fcfcfc;';
    const inputTitle = document.createElement('div'); inputTitle.textContent = 'Input'; inputTitle.style.cssText = 'font-weight:600;margin-bottom:8px;color:#111827;';
    const inputContent = document.createElement('div'); inputContent.style.cssText = 'font-size:.9rem;line-height:1.45;color:#111827;';
    inputContent.innerHTML = '<pre style="white-space:pre-wrap;margin:0">'+escapeHtml(collectIloIgaFactors() || 'No input.')+'</pre>';
    inputBlock.appendChild(inputTitle); inputBlock.appendChild(inputContent);

    const outputBlock = document.createElement('div');
    outputBlock.style.cssText = 'margin-bottom:0;padding:10px;border:1px solid #e6e9ed;border-radius:8px;background:#fcfcfc;';
    const outputTitle = document.createElement('div'); outputTitle.textContent = 'AI Output'; outputTitle.style.cssText = 'font-weight:600;margin-bottom:8px;color:#111827;';
    const outputContent = document.createElement('div'); outputContent.style.cssText = 'font-size:.9rem;line-height:1.45;color:#111827;';
    outputContent.innerHTML = '<pre id="svIloIgaOutputPre" style="white-space:pre-wrap;margin:0">'+escapeHtml(_lastAiOutput || 'No AI output yet.')+'</pre>';
    outputBlock.appendChild(outputTitle); outputBlock.appendChild(outputContent);

    body.appendChild(inputBlock);
    body.appendChild(outputBlock);
    modal.appendChild(head);
    modal.appendChild(body);
    _overlayIga.appendChild(modal);
    document.body.appendChild(_overlayIga);
    const escHandler = (e) => { if (e.key === 'Escape') { try { _overlayIga.remove(); _overlayIga = null; } catch(err){} } };
    document.addEventListener('keydown', escHandler, { once: true });
    return true;
  }
  try { window.svOpenIloIgaModal = openIloIgaModal; } catch(e) {}

  // Extract fenced JSON (```json ... ```)
  function extractJsonBlock(text){
    try {
      if (!text) return '';
      const re = /```json\s*\n([\s\S]*?)\n```/i;
      const m = text.match(re);
      if (m && m[1]) return m[1].trim();
      // Fallback: try generic fence
      const re2 = /```\s*\n([\s\S]*?)\n```/i;
      const m2 = text.match(re2);
      if (m2 && m2[1]) return m2[1].trim();
      return '';
    } catch(e){ return ''; }
  }

  // Ensure per-row uniqueness and key alignment to iga_labels
  function normalizeIloIgaJson(obj){
    try {
      if (!obj || !Array.isArray(obj.iga_labels) || !Array.isArray(obj.mappings)) return null;
      const labels = obj.iga_labels.map(x => String(x ?? '')).filter(x => x !== 'No IGA');
      const rows = [];
      obj.mappings.forEach((row, idx) => {
        const iloText = String(row.ilo_text ?? '');
        const igasRaw = row.igas && typeof row.igas === 'object' ? row.igas : {};
        const seen = new Set();
        const igas = {};
        labels.forEach(lbl => {
          const raw = String(igasRaw[lbl] ?? '').trim();
          if (!raw) { igas[lbl] = ''; return; }
          const codes = raw.split(',').map(s => s.trim()).filter(Boolean);
          const kept = [];
          for (const code of codes) {
            const key = code.toUpperCase();
            if (seen.has(key)) continue; // enforce uniqueness within the row across columns
            seen.add(key);
            kept.push(code.replace(/\s+/g, ''));
          }
          igas[lbl] = kept.join(',');
        });
        rows.push({ ilo_text: iloText, igas, position: (typeof row.position === 'number' ? row.position : idx) });
      });
      return { iga_labels: labels, mappings: rows };
    } catch(e){ return null; }
  }

  async function postIloIgaMapping(syllabusId, payload){
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const body = JSON.stringify({ syllabus_id: syllabusId, iga_labels: payload.iga_labels, mappings: payload.mappings });
    const res = await fetch('/faculty/syllabus/save-ilo-iga-mapping', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', ...(token ? { 'X-CSRF-TOKEN': token } : {}) },
      body
    });
    if (!res.ok) {
      let msg = 'Failed to save ILO–IGA mapping';
      try { const j = await res.json(); if (j && j.message) msg = j.message; } catch(_){}
      throw new Error(msg);
    }
    try {
      const j = await res.json();
      if (!j.success) throw new Error(j.message || 'Save not successful');
      // Surface a lightweight notice in the modal output area
      const pre = document.getElementById('svIloIgaOutputPre');
      if (pre) pre.textContent = (pre.textContent || '') + '\n\n[Saved] ILO–IGA mapping stored successfully.';
      // Refresh visible partial: prefer direct refresh with known payload, fallback to AJAX
      try {
        if (typeof window.refreshIloIgaPartial === 'function') {
          window.refreshIloIgaPartial(payload.iga_labels || [], payload.mappings || []);
        } else if (typeof window.ajaxRefreshIloIgaPartial === 'function') {
          await window.ajaxRefreshIloIgaPartial();
        }
      } catch(_) {}
      return j;
    } catch(e){ throw e; }
  }

  // Global hotkey listener (early bind) – acts even if DOMContentLoaded hasn't fired yet
  (function attachGlobalHotkey(){
    function onHotkeyGlobal(e){
      const t = e.target;
      const tag = (t && t.tagName) ? t.tagName.toLowerCase() : '';
      const isEditable = (t && (t.isContentEditable || tag === 'input' || tag === 'textarea' || tag === 'select'));
      if (isEditable) return;
      const isShift3 = !!(e.shiftKey && (
        e.code === 'Digit3' || e.key === '3' || e.key === '#' || e.keyCode === 51
      ));
      if (!isShift3) return;
      e.preventDefault();
      openIloIgaModal();
    }
    try {
      document.addEventListener('keydown', onHotkeyGlobal, true);
      window.addEventListener('keydown', onHotkeyGlobal, true);
      document.addEventListener('keyup', onHotkeyGlobal, true);
      window.addEventListener('keyup', onHotkeyGlobal, true);
    } catch(err) {}
  })();

  function init(){
    document.addEventListener('DOMContentLoaded', function(){
      try {
        const btn = document.getElementById('svAiIloIgaBtn');
        if (btn && !btn.dataset.boundIloIga) {
          btn.dataset.boundIloIga = '1';
          btn.addEventListener('click', async function(){
            if (btn.disabled) return;
            btn.disabled = true;
            btn.setAttribute('aria-disabled', 'true');
            btn.classList.add('disabled');
            try { await runIloIgaAiFlow(); }
            finally {
              btn.disabled = false;
              btn.removeAttribute('aria-disabled');
              btn.classList.remove('disabled');
            }
          });
        }
        // Hotkey: Shift+3 opens ILO–IGA AI modal (robust detection)
        function onHotkey(e){
          // Ignore when typing in inputs/textareas/selects or contentEditable
          const t = e.target;
          const tag = (t && t.tagName) ? t.tagName.toLowerCase() : '';
          const isEditable = (t && (t.isContentEditable || tag === 'input' || tag === 'textarea' || tag === 'select'));
          if (isEditable) return;
          // Detect Shift+3 across layouts: code Digit3, key '3', '#' on some keyboards, keyCode 51
          const isShift3 = !!(e.shiftKey && (
            e.code === 'Digit3' || e.key === '3' || e.key === '#' || e.keyCode === 51
          ));
          if (!isShift3) return;
          e.preventDefault();
          openIloIgaModal();
        }
        // Listen on both keydown and keyup to avoid interception by other handlers
        document.addEventListener('keydown', onHotkey, true);
        document.addEventListener('keyup', onHotkey, true);
        // Also attach to window for broader coverage
        window.addEventListener('keydown', onHotkey, true);
        window.addEventListener('keyup', onHotkey, true);

        // Optional debug: set window._svDebugHotkey = true to log key events
        function debugLog(e){
          if (!window._svDebugHotkey) return;
          try {
            console.debug('[Shift+3 check]', { key:e.key, code:e.code, keyCode:e.keyCode, shift:e.shiftKey, target:e.target && e.target.tagName });
          } catch(err){}
        }
        document.addEventListener('keydown', debugLog, true);
        window.addEventListener('keydown', debugLog, true);

        // Fallback: Alt+Click on ILO–IGA button opens modal
        if (btn) {
          btn.addEventListener('click', function(e){
            if (e.altKey) {
              e.preventDefault();
              openIloIgaModal();
            }
          });
          // Double-click also opens modal for quick testing
          btn.addEventListener('dblclick', function(){ openIloIgaModal(); });
        }
      } catch(e) {}
    });
  }

  if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
})().catch?.(function(err){ try { console.error('[ILO–IGA] init error', err); } catch(e){} });
