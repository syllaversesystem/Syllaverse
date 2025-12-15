(function(){
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

  function collectIloList(){
    const out = [];
    try {
      const iloList = document.getElementById('syllabus-ilo-sortable');
      if (!iloList) return out;
      const rows = Array.from(iloList.querySelectorAll('tr'));
      rows.forEach((r, i) => {
        const codeEl = r.querySelector('.ilo-badge');
        const codeHidden = r.querySelector('input[name="code[]"]');
        const code = (codeEl && codeEl.textContent) ? codeEl.textContent.trim() : (codeHidden ? String(codeHidden.value || '').trim() : `ILO${i+1}`);
        const ta = r.querySelector('textarea[name="ilos[]"]');
        const desc = ta ? String(ta.value || '').trim() : '';
        if (code || desc) out.push({ code, desc });
      });
    } catch(e) {}
    return out;
  }

  function extractJsonBlock(text){
    try {
      if (!text) return null;
      const codeFence = /```[a-zA-Z0-9]*\n([\s\S]*?)```/;
      const m = text.match(codeFence);
      const raw = m ? m[1] : text;
      // Try direct parse first
      try { return JSON.parse(raw); } catch(_){}
      // Else try to find first JSON-ish object/array
      const firstBrace = raw.indexOf('{');
      const firstBracket = raw.indexOf('[');
      let start = -1;
      if (firstBrace !== -1 && firstBracket !== -1) start = Math.min(firstBrace, firstBracket);
      else start = (firstBrace !== -1 ? firstBrace : firstBracket);
      if (start >= 0) {
        const candidate = raw.slice(start);
        // heuristic: trim trailing non-json
        const trimmed = candidate.replace(/\s*[\]\}]\s*[\s\S]*$/, function(m0){
          // keep the last ] or }
          return m0.match(/[\]\}]/) ? m0.match(/[\]\}]/)[0] : '';
        });
        try { return JSON.parse(trimmed); } catch(_){}
      }
    } catch(e) {}
    return null;
  }

  function normalizeIloCdioSdgJson(obj){
    if (!obj) return null;
    const arr = Array.isArray(obj.mappings) ? obj.mappings : (Array.isArray(obj) ? obj : []);
    const mappings = [];
    let pos = 0;
    arr.forEach(entry => {
      if (!entry) return;
      const ilo_text = (entry.ilo_text || entry.ilo || entry.ilo_code || '').toString();
      const cdiosRaw = entry.cdios || entry.cdio || entry.cdio_codes || [];
      const sdgsRaw = entry.sdgs || entry.sdg || entry.sdg_codes || [];
      const cdios = Array.isArray(cdiosRaw) ? cdiosRaw.map(x => String(x||'').trim()).filter(Boolean) : [];
      const sdgs = Array.isArray(sdgsRaw) ? sdgsRaw.map(x => String(x||'').trim()).filter(Boolean) : [];
      if (ilo_text || cdios.length || sdgs.length) {
        mappings.push({ ilo_text, cdios, sdgs, position: (typeof entry.position === 'number') ? entry.position : pos++ });
      }
    });
    return { mappings };
  }

  async function postIloCdioSdgMapping(syllabusId, normalized){
    if (!normalized || !Array.isArray(normalized.mappings)) throw new Error('Invalid mapping payload');
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const body = { syllabus_id: syllabusId, mappings: normalized.mappings };
    const res = await fetch('/faculty/syllabus/save-ilo-cdio-sdg-mapping', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', ...(token ? { 'X-CSRF-TOKEN': token } : {}) },
      body: JSON.stringify(body),
      credentials: 'same-origin',
    });
    if (!res.ok) {
      const t = await res.text().catch(()=> '');
      throw new Error('Save failed: ' + t);
    }
    const data = await res.json().catch(()=>({ success:true }));
    if (!data?.success) throw new Error(data?.message || 'Save failed');
    return data;
  }

  async function sendIloCdioSdgAi(){
    if (_inFlight) return;
    try {
      _inFlight = true;
      setProgress('Collecting', 10, 'Gathering ILOs…', 'state-running');
      const ilos = collectIloList();
      if (!ilos.length) {
        setProgress('Error', 100, 'No ILOs found. Add ILOs first.', 'state-warn');
        return;
      }
      const domSyllabusId = document.querySelector('[data-syllabus-id]')?.dataset?.syllabusId || null;
      const m = (location.pathname||'').match(/\/faculty\/syllabi\/(\d+)/);
      const syllabusId = domSyllabusId || (m ? m[1] : null);
      if (!syllabusId) { setProgress('Error', 100, 'Cannot find syllabus ID.', 'state-warn'); return; }
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

      // Build context as text
      const lines = [];
      lines.push('PARTIAL_BEGIN:ilos');
      lines.push('TITLE: Intended Learning Outcomes');
      lines.push('COLUMNS: Code | Description');
      ilos.forEach(i => { lines.push(`ROW: ${i.code} | ${i.desc || '-'}`); });
      lines.push('PARTIAL_END:ilos');
      const context = lines.join('\n');

      // Build user message prompt
      let userPrompt = [
        'You are assisting in mapping course ILOs to CDIO and SDG frameworks. ',
        'Read the provided ILO list and infer suitable CDIO skills and SDG goals per ILO. ',
        'Output ONLY a JSON object with this structure:',
        '```json',
        '{',
        '  "mappings": [',
        '    {',
        '      "ilo_text": "<ILO code or text>",',
        '      "cdios": ["C1.1", "C2.3"],',
        '      "sdgs": ["SDG3", "SDG4"],',
        '      "position": 0',
        '    }',
        '  ]',
        '}',
        '```',
        'Rules:',
        '- Use known CDIO codes when possible; otherwise, leave the array empty.',
        '- Use SDG codes like "SDG1".."SDG17"; otherwise, leave empty.',
        '- If there are no TLA tasks aligned to an ILO, you may leave arrays empty.',
      ].join(' ');

      const fd = new FormData();
      fd.append('message', userPrompt);
      fd.append('context', context);
      setProgress('Sending', 45, 'Requesting AI mapping…', 'state-running');
      const res = await fetch(`/faculty/syllabi/${encodeURIComponent(syllabusId)}/ai-chat`, { method:'POST', headers: token ? { 'X-CSRF-TOKEN': token } : {}, body: fd, credentials:'same-origin' });
      setProgress('Waiting', 70, 'Awaiting response…', 'state-running');
      if (!res.ok) { setProgress('Error', 100, 'AI request failed.', 'state-warn'); return; }
      const data = await res.json().catch(()=>({}));
      const msg = data?.message || data?.reply || data?.response || '';
      _lastAiOutput = msg || '';

      // Parse JSON and save
      try {
        const parsed = extractJsonBlock(_lastAiOutput);
        const normalized = normalizeIloCdioSdgJson(parsed);
        if (normalized && Array.isArray(normalized.mappings)) {
          await postIloCdioSdgMapping(syllabusId, normalized);
          setProgress('Saved', 100, 'Mappings saved successfully.', 'state-ok');
        } else {
          setProgress('Done', 100, 'No JSON found; nothing saved.', 'state-warn');
        }
      } catch(err) {
        setProgress('Error', 100, 'Failed to save JSON output.', 'state-warn');
        try { console.warn('[ILO-CDIO-SDG] Save failed:', err?.message || err); } catch(e){}
      }
    } catch(e){
      setProgress('Error', 100, 'Unexpected error.', 'state-warn');
    } finally {
      _inFlight = false;
    }
  }

  function init(){
    document.addEventListener('DOMContentLoaded', function(){
      try {
        const btn = document.getElementById('svAiIloCdioSdgBtn');
        if (btn && !btn.dataset.boundIloCdioSdg) {
          btn.dataset.boundIloCdioSdg = '1';
          btn.addEventListener('click', async function(){
            if (btn.disabled) return;
            btn.disabled = true;
            btn.setAttribute('aria-disabled', 'true');
            btn.classList.add('disabled');
            try { await sendIloCdioSdgAi(); }
            finally {
              btn.disabled = false;
              btn.removeAttribute('aria-disabled');
              btn.classList.remove('disabled');
            }
          });
        }
      } catch(e) {}
    });
  }

  if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
})();