// -----------------------------------------------------------------------------
// * File: resources/js/faculty/ai-map.js
// * Description: AI Map helpers for Assessment Mapping partial.
// * Reuses snapshot builders from syllabus-ai-chat.js and provides viewers.
// -----------------------------------------------------------------------------

(function(){
  // Last request snapshot for AI Map
  let _lastMapInput = null; // { phase, context_phase1, context_phase2, context_phase3, context_all }
  let _lastMapOutput = null; // string (AI reply)
  let _lastParsedMap = null; // array of rows { name, week_marks, position }
  let _lastAmOutput = null; // string (AI reply for Assessment Mapping)
  let _lastIloSoCpaOutput = null; // string (AI reply for ILO–SO–CPA)

  // Build Phase 1–3 payloads by reusing chat snapshot utilities when available
  function collectPhasePayloads(){
    // Prefer using the same functions from syllabus-ai-chat.js
    const full = (typeof window._aiChatSnapshot === 'function') ? window._aiChatSnapshot() : '';
    const real = (typeof window._svRealtimeContext === 'string') ? window._svRealtimeContext : '';

    function extractBlock(src, key){
      if (!src) return '';
      const re = new RegExp(`PARTIAL_BEGIN:${key}[\\s\\S]*?PARTIAL_END:${key}`, 'm');
      const m = src.match(re);
      return (m && m[0]) ? m[0] : '';
    }

    // Phase 1 essentials
    const keys1 = ['mission_vision','course_info','tlas','ilo','so','iga','cdio','sdg'];
    const p1Blocks = [];
    keys1.forEach(k => {
      let blk = extractBlock(full, k);
      if (!blk && real) blk = extractBlock(real, k);
      if (blk) p1Blocks.push(blk);
    });
    const phase1 = p1Blocks.length ? p1Blocks.join('\n\n') : '';

    // Phase 2: Include TLA Activities from frontend snapshot (no backend changes)
    // Use realtime-only TLA block (DOM snapshot); do not fallback to full
    // Build TLA snapshot directly from DOM (no realtime context dependency)
    function buildTlaMdFromDom(){
      try {
        const table = document.getElementById('tlaTable');
        if (!table) return '';
        const tbody = table.querySelector('tbody') || table;
        const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => r.id !== 'tla-placeholder');
        const md = [];
        md.push('### Teaching, Learning, and Assessment (TLA) Activities');
        md.push('Columns: Ch. | Topics / Reading List | Wks. | Topic Outcomes | ILO | SO | Delivery Method');
        md.push('| # | Ch. | Topics / Reading List | Wks. | Topic Outcomes | ILO | SO | Delivery |');
        md.push('|:--:|:--:|:--|:--:|:--|:--:|:--:|:--|');
        let count = 0;
        const read = (row, selector) => {
          const el = row.querySelector(selector);
          if (!el) return '-';
          // If element is a form field, use .value and do not fall back to textContent
          if (typeof el.value === 'string') {
            return (el.value || '').toString().trim();
          }
          // Otherwise, check for nested field
          const inner = el.querySelector && el.querySelector('input,textarea,select');
          if (inner && typeof inner.value === 'string') {
            return (inner.value || '').toString().trim();
          }
          // Finally, non-form visible text
          const txt = (el.textContent || '').trim();
          return txt || '-';
        };
        rows.forEach((row) => {
          const ch = read(row, '[name*="[ch]"]');
          const topic = read(row, '[name*="[topic]"]');
          const wks = read(row, '[name*="[wks]"]');
          const outcomes = read(row, '[name*="[outcomes]"]');
          const ilo = read(row, '[name*="[ilo]"]');
          const so = read(row, '[name*="[so]"]');
          const delivery = read(row, '[name*="[delivery]"]');
          const any = [ch,topic,wks,outcomes,ilo,so,delivery].some(v => (v && v !== '-' && v.trim() !== ''));
          if (any) {
            count++;
            md.push(`| ${count} | ${ch} | ${topic} | ${wks} | ${outcomes} | ${ilo} | ${so} | ${delivery} |`);
          }
        });
        if (count === 0) md.push('| - | - | - | - | - | - | - | - |');
        return md.join('\n');
      } catch(e){ return ''; }
    }
    const tla = buildTlaMdFromDom();

    // Build Assessment Tasks Distribution snapshot (DOM-based)
    function buildAssessmentTasksDistributionMd(){
      try {
        const atRoot = document.querySelector('.at-map-outer');
        if (!atRoot) return '';
        const headerRow = atRoot.querySelector('thead tr:nth-child(2)');
        let iloCount = 0;
        if (headerRow) {
          const totalCols = headerRow.children.length; // Code, Task, I/R/D, %, ILO*, C, P, A
          iloCount = Math.max(1, totalCols - 7);
        }
        const tbody = atRoot.querySelector('#at-tbody');
        const rows = tbody ? Array.from(tbody.querySelectorAll('tr')) : [];
        const md = [];
        md.push('### Assessment Tasks Distribution');
        const hdr = ['Code','Task','I/R/D','%'];
        for (let i = 1; i <= iloCount; i++) hdr.push(`ILO ${i}`);
        hdr.push('C','P','A');
        md.push('| ' + hdr.join(' | ') + ' |');
        md.push('| ' + hdr.map(()=> '---').join(' | ') + ' |');

        rows.forEach(tr => {
          const tds = Array.from(tr.children);
          const readCell = (idx, numeric=false) => {
            const cell = tds[idx];
            if (!cell) return '';
            const ta = cell.querySelector('textarea');
            let v = ta ? (ta.value || '') : (cell.textContent || '');
            v = v.toString().trim();
            if (numeric) {
              const n = parseFloat(v.replace(/[^0-9.\-]/g,''));
              return Number.isFinite(n) ? String(Math.round(n)) : '';
            }
            return v;
          };
          const code = readCell(0);
          const task = readCell(1);
          const ird = readCell(2);
          const pct = readCell(3, true);
          const iloVals = [];
          for (let i = 4; i < tds.length - 3; i++) {
            const ta = tds[i].querySelector('textarea');
            iloVals.push((ta ? ta.value : '' ).toString().trim());
          }
          const c = readCell(tds.length - 3);
          const p = readCell(tds.length - 2);
          const a = readCell(tds.length - 1);
          const rowVals = [code || '-', task || '-', ird || '-', pct ? pct + '%' : '-']
            .concat(iloVals.length ? iloVals.map(v => v || '-') : Array.from({length: iloCount}, () => '-'))
            .concat([c || '-', p || '-', a || '-']);
          md.push('| ' + rowVals.join(' | ') + ' |');
        });

        if (!rows.length) {
          let placeholder = ['-','-','-','-'];
          for (let i = 0; i < iloCount; i++) placeholder.push('-');
          placeholder.push('-','-','-');
          md.push('Note: No assessment tasks defined; using placeholder.');
          md.push('| ' + placeholder.join(' | ') + ' |');
        }
        return md.join('\n');
      } catch(e){ return ''; }
    }

    const atDistribution = buildAssessmentTasksDistributionMd();
    // Re-include Criteria-for-Assessment from realtime context if present
    const reCriteria = /PARTIAL_BEGIN:criteria_assessment[\s\S]*?PARTIAL_END:criteria_assessment/;
    const crit = (typeof real === 'string' && real) ? (real.match(reCriteria)?.[0] || '') : (full.match(reCriteria)?.[0] || '');
    let phase2 = [tla, atDistribution, crit].filter(Boolean).join('\n\n');

    // Phase 3: mappings (Assessment Mapping grid, ILO–SO–CPA, ILO–IGA, ILO–CDIO–SDG)
    // These are visual DOM tables; build compact markdown from the page like chat did.
    function buildAssessmentMappingMd(){
      const amRoot = document.querySelector('.assessment-mapping');
      if (!amRoot) return '';
      const distRows = Array.from(amRoot.querySelectorAll('table.distribution tr')).slice(1);
      const weekHeader = Array.from(amRoot.querySelectorAll('table.week tr:first-child th.week-number'))
        .map(th => th.textContent.trim())
        .filter(t => t && t.toLowerCase() !== 'no weeks');
      const weekRows = Array.from(amRoot.querySelectorAll('table.week tr')).slice(1);
      const md = [];
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
      return md.join('\n');
    }

    // ILO–SO–CPA mapping (smart snapshot: ILO number, SO number columns, and C/P/A)
    function buildIloSoCpaMd(){
      const soRoot = document.querySelector('.ilo-so-cpa-mapping');
      if (!soRoot) return '';
      const mapTable = soRoot.querySelector('.mapping');
      const headerRow2 = mapTable?.querySelectorAll('tr')[1];
      const soHeaders = [];
      if (headerRow2) {
        Array.from(headerRow2.querySelectorAll('th')).forEach(th => {
          const inp = th.querySelector('input');
          const raw = (inp ? inp.value : th.textContent || '').toString().trim();
          const num = (raw.match(/\d+/) || [null])[0];
          if (num) soHeaders.push(num);
        });
      }
      const rows = Array.from((mapTable?.querySelector('tbody') || mapTable).querySelectorAll('tr')).filter(r => r.querySelector('td'));
      const md = [];
      md.push('### ILO–SO–CPA Mapping');
      md.push('| ILO | ' + (soHeaders.length ? soHeaders.map(n=>`SO ${n}`).join(' | ') : 'SO') + ' | C | P | A |');
      md.push('|:--|'+ (soHeaders.length ? soHeaders.map(()=>':--:').join('|') : ':--:') +'|:--:|:--:|:--:|');
      rows.forEach(r => {
        const tds = Array.from(r.querySelectorAll('td'));
        const rawIlo = (tds[0]?.querySelector('input')?.value || tds[0]?.textContent || '').toString().trim();
        const iloNum = (rawIlo.match(/\d+/) || [null])[0];
        const ilo = iloNum ? `ILO ${iloNum}` : (rawIlo || '-');
        const soCount = Math.max(1, soHeaders.length);
        const soVals = [];
        for (let i = 1; i <= soCount; i++) {
          const cell = tds[i];
          const ta = cell?.querySelector('textarea');
          const v = (ta ? ta.value : cell?.textContent || '').toString().trim();
          soVals.push(v || '-');
        }
        const c = (tds[tds.length-3]?.querySelector('textarea')?.value || tds[tds.length-3]?.textContent || '').toString().trim() || '-';
        const p = (tds[tds.length-2]?.querySelector('textarea')?.value || tds[tds.length-2]?.textContent || '').toString().trim() || '-';
        const a = (tds[tds.length-1]?.querySelector('textarea')?.value || tds[tds.length-1]?.textContent || '').toString().trim() || '-';
        md.push('| ' + [ilo].concat(soVals).concat([c,p,a]).join(' | ') + ' |');
      });
      if (!rows.length) md.push('| - | - | - | - | - |');
      return md.join('\n');
    }

    function buildIloIgaMd(){
      const igaRoot = document.querySelector('.ilo-iga-mapping');
      if (!igaRoot) return '';
      const mapTable = igaRoot.querySelector('.mapping');
      const headerRow2 = mapTable?.querySelectorAll('tr')[1];
      const ths = headerRow2 ? Array.from(headerRow2.querySelectorAll('th')) : [];
      const isPlaceholderOnly = ths.length === 1 && !ths[0]?.querySelector('input') && (ths[0]?.textContent || '').toString().trim().toLowerCase() === 'no iga';
      const igaHeaders = [];
      if (!isPlaceholderOnly) {
        ths.forEach((th, i) => {
          const inp = th.querySelector('input');
          const raw = (inp?.value || th.textContent || '').toString().trim();
          igaHeaders.push(raw || `IGA ${i+1}`);
        });
      }
      const rows = Array.from((mapTable?.querySelector('tbody') || mapTable).querySelectorAll('tr')).filter(r => r.querySelector('td'));
      const md = [];
      md.push('### ILO–IGA Mapping');
      if (isPlaceholderOnly) {
        md.push('| ILO | IGA |');
        md.push('|:--|:--:|');
      } else {
        md.push('| ILO | ' + (igaHeaders.length ? igaHeaders.join(' | ') : 'IGA') + ' |');
        md.push('|:--|' + (igaHeaders.length ? igaHeaders.map(()=>':--:').join('|') : ':--:') + '|');
      }
      rows.forEach(r => {
        const tds = Array.from(r.querySelectorAll('td'));
        const ilo = (tds[0]?.querySelector('input')?.value || tds[0]?.textContent || '').toString().trim() || '-';
        const vals = [];
        for (let i = 1; i < tds.length; i++) {
          const cell = tds[i];
          const ta = cell?.querySelector('textarea');
          const v = (ta ? ta.value : cell?.textContent || '').toString().trim();
          vals.push(v || '-');
        }
        const outVals = isPlaceholderOnly ? [vals[0] || '-'] : vals;
        md.push('| ' + [ilo].concat(outVals).join(' | ') + ' |');
      });
      if (!rows.length) md.push(isPlaceholderOnly ? '| - | - |' : '| - | - |');
      return md.join('\n');
    }

    function buildIloCdioSdgMd(){
      const csRoot = document.querySelector('.ilo-cdio-sdg-mapping');
      if (!csRoot) return '';
      const mapTable = csRoot.querySelector('.mapping');
      const headerRow1 = mapTable?.querySelectorAll('tr')[0];
      const headerRow2 = mapTable?.querySelectorAll('tr')[1];
      const cdioSpan = headerRow1 ? parseInt(headerRow1.children[1]?.getAttribute('colspan') || '1', 10) : 1;
      const sdgSpan  = headerRow1 ? parseInt(headerRow1.children[2]?.getAttribute('colspan') || '1', 10) : 1;
      const row2Cells = headerRow2 ? Array.from(headerRow2.children) : [];
      let cdioHeaderCells = headerRow2 ? Array.from(headerRow2.querySelectorAll('th.cdio-label-cell')) : [];
      let sdgHeaderCells  = headerRow2 ? Array.from(headerRow2.querySelectorAll('th.sdg-label-cell'))  : [];
      if (!cdioHeaderCells.length && cdioSpan > 0) cdioHeaderCells = row2Cells.slice(0, cdioSpan);
      if (!sdgHeaderCells.length && sdgSpan  > 0) sdgHeaderCells  = row2Cells.slice(cdioSpan, cdioSpan + sdgSpan);
      const cdioPlaceholderOnly = (cdioHeaderCells.length === 1) && !cdioHeaderCells[0]?.querySelector('input') && ((cdioHeaderCells[0]?.textContent || '').toString().trim().toLowerCase() === 'no cdio');
      const sdgPlaceholderOnly  = (sdgHeaderCells.length === 1)  && !sdgHeaderCells[0]?.querySelector('input')  && ((sdgHeaderCells[0]?.textContent  || '').toString().trim().toLowerCase()  === 'no sdg');
      const cdioLabels = [];
      const sdgLabels = [];
      if (!cdioPlaceholderOnly) {
        cdioHeaderCells.forEach((cell, i) => {
          const inp = cell.querySelector('input');
          let txt = (inp?.value || cell.textContent || '').toString().trim();
          txt = txt.replace(/Remove CDIO column|Add CDIO column/gi, '').trim();
          cdioLabels.push(txt || `CDIO ${i+1}`);
        });
      }
      if (!sdgPlaceholderOnly) {
        sdgHeaderCells.forEach((cell, j) => {
          const inp = cell.querySelector('input');
          let txt = (inp?.value || cell.textContent || '').toString().trim();
          txt = txt.replace(/Remove SDG column|Add SDG column/gi, '').trim();
          sdgLabels.push(txt || `SDG ${j+1}`);
        });
      }
      const allTr = Array.from(mapTable.querySelectorAll('tr'));
      const rows = allTr.slice(3).filter(r => r.querySelector('td'));
      const realCdioCols = cdioPlaceholderOnly ? 1 : cdioHeaderCells.length;
      const realSdgCols  = sdgPlaceholderOnly  ? 1 : sdgHeaderCells.length;
      const cdioHeadersForTable = (cdioPlaceholderOnly || !cdioLabels.length) ? ['CDIO'] : cdioLabels;
      const sdgHeadersForTable  = (sdgPlaceholderOnly  || !sdgLabels.length)  ? ['SDG']  : sdgLabels;
      const md = [];
      md.push('### ILO–CDIO–SDG Mapping');
      const headerLine = '| ILO | ' + cdioHeadersForTable.join(' | ') + ' |  | ' + sdgHeadersForTable.join(' | ') + ' |';
      const alignLine  = '|:--|' + cdioHeadersForTable.map(()=>':--:').join('|') + '|---|' + sdgHeadersForTable.map(()=>':--:').join('|') + '|';
      md.push(headerLine);
      md.push(alignLine);
      rows.forEach(r => {
        const tds = Array.from(r.querySelectorAll('td'));
        const ilo = (tds[0]?.querySelector('input')?.value || tds[0]?.textContent || '').toString().trim() || '-';
        const cdioVals = [];
        for (let i = 0; i < realCdioCols; i++) {
          const cell = tds[1 + i];
          const ta = cell?.querySelector('textarea');
          const v = (ta ? ta.value : cell?.textContent || '').toString().trim();
          cdioVals.push(v || '-');
        }
        const sdgVals = [];
        const offset = 1 + realCdioCols + 1;
        for (let j = 0; j < realSdgCols; j++) {
          const cell = tds[offset + j];
          const ta = cell?.querySelector('textarea');
          const v = (ta ? ta.value : cell?.textContent || '').toString().trim();
          sdgVals.push(v || '-');
        }
        md.push('| ' + [ilo].concat(cdioVals).concat(['']).concat(sdgVals).join(' | ') + ' |');
      });
      if (!rows.length) md.push('| - | - | - |');
      return md.join('\n');
    }

    const phase3Parts = [
      buildAssessmentMappingMd(),
      buildIloSoCpaMd(),
      buildIloIgaMd(),
      buildIloCdioSdgMd(),
    ].filter(Boolean);
    const phase3 = phase3Parts.length ? phase3Parts.join('\n\n') : '';

    const combined = [
      phase1 ? '--- Phase 1 ---\n' + phase1 : '',
      phase2 ? '--- Phase 2 ---\n' + phase2 : '',
      phase3 ? '--- Phase 3 ---\n' + phase3 : ''
    ].filter(Boolean).join('\n\n');

    const phaseInd = phase3 ? '3' : (phase2 ? '2' : (phase1 ? '1' : ''));
    _lastMapInput = {
      phase: phaseInd,
      context_phase1: phase1,
      context_phase2: phase2,
      context_phase3: phase3,
      context_all: combined
    };
    return _lastMapInput;
  }

  function openInputViewer(){
    if (!_lastMapInput) collectPhasePayloads();
    const overlay = document.createElement('div');
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:9999;display:flex;align-items:center;justify-content:center;';
    const modal = document.createElement('div');
    modal.style.cssText = 'width:80%;max-width:960px;max-height:80%;background:#fff;border-radius:8px;box-shadow:0 8px 32px rgba(0,0,0,0.25);display:flex;flex-direction:column;';
    const head = document.createElement('div');
    head.style.cssText = 'padding:12px 16px;border-bottom:1px solid #e5e5e5;display:flex;gap:8px;align-items:center;font-weight:600;';
    head.textContent = 'AI Map Input Viewer';
    const closeBtn = document.createElement('button');
    closeBtn.textContent = 'Close';
    closeBtn.style.cssText = 'margin-left:auto;padding:6px 10px;border:1px solid #ccc;background:#f7f7f7;border-radius:6px;cursor:pointer;';
    closeBtn.addEventListener('click', () => overlay.remove());
    head.appendChild(closeBtn);
    const body = document.createElement('div');
    body.style.cssText = 'padding:12px 16px;overflow:auto;';

    const addBlock = (title, text) => {
      const wrap = document.createElement('div');
      wrap.style.cssText = 'margin-bottom:16px;padding:10px;border:1px solid #e6e9ed;border-radius:8px;background:#fcfcfc;';
      const t = document.createElement('div');
      t.textContent = title;
      t.style.cssText = 'font-weight:600;margin-bottom:8px;color:#111827;';
      const pre = document.createElement('pre');
      pre.style.cssText = 'white-space:pre-wrap;word-wrap:break-word;font-size:12px;line-height:1.4;';
      pre.textContent = text || '-';
      wrap.appendChild(t);
      wrap.appendChild(pre);
      body.appendChild(wrap);
    };

    addBlock('Phase', _lastMapInput?.phase || '-');
    addBlock('Phase 1', _lastMapInput?.context_phase1 || '');
    addBlock('Phase 2', _lastMapInput?.context_phase2 || '');
    addBlock('Phase 3', _lastMapInput?.context_phase3 || '');
    addBlock('Combined', _lastMapInput?.context_all || '');

    // Output block (if any)
    const outWrap = document.createElement('div');
    outWrap.style.cssText = 'margin-bottom:16px;padding:10px;border:1px solid #e6e9ed;border-radius:8px;background:#fcfcfc;';
    // Two outputs: Assessment Mapping and ILO–SO–CPA
    const makeOut = (title, text) => {
      const t = document.createElement('div');
      t.textContent = title;
      t.style.cssText = 'font-weight:600;margin:10px 0 8px;color:#111827;';
      outWrap.appendChild(t);
      const body = document.createElement('div');
      body.style.cssText = 'font-size:.9rem;line-height:1.45;color:#111827;';
      try {
        if (typeof window.formatAIResponse === 'function') body.innerHTML = window.formatAIResponse(text || '');
        else {
          const safe = (text || '').replace(/[&<>]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[c]));
          body.innerHTML = '<pre style="margin:0;white-space:pre-wrap;word-wrap:break-word">' + safe + '</pre>';
        }
      } catch(e){ body.textContent = text || ''; }
      outWrap.appendChild(body);
    };
    makeOut('AI Output — Assessment Mapping', _lastAmOutput || '');
    makeOut('AI Output — ILO–SO–CPA', _lastIloSoCpaOutput || '');
    body.appendChild(outWrap);

    // Parsed JSON payload (Assessment Mapping) if available
    const jsonWrap = document.createElement('div');
    jsonWrap.style.cssText = 'margin-bottom:16px;padding:10px;border:1px solid #e6e9ed;border-radius:8px;background:#fcfcfc;';
    const jsonTitle = document.createElement('div');
    jsonTitle.textContent = 'Parsed Assessment Mapping (JSON)';
    jsonTitle.style.cssText = 'font-weight:600;margin-bottom:8px;color:#111827;';
    jsonWrap.appendChild(jsonTitle);
    const jsonPre = document.createElement('pre');
    jsonPre.style.cssText = 'white-space:pre-wrap;word-wrap:break-word;font-size:12px;line-height:1.4;';
    jsonPre.textContent = JSON.stringify(_lastParsedMap || [], null, 2);
    jsonWrap.appendChild(jsonPre);
    // Apply button
    const applyBtn = document.createElement('button');
    applyBtn.textContent = 'Apply AI Map to Database';
    applyBtn.style.cssText = 'margin-top:8px;padding:6px 10px;border:1px solid #ccc;background:#f7f7f7;border-radius:6px;cursor:pointer;';
    applyBtn.addEventListener('click', async () => {
      try {
        const syllabusId = document.getElementById('syllabus-document')?.getAttribute('data-syllabus-id') || null;
        if (!syllabusId) { alert('No syllabus ID found.'); return; }
        const endpoint = `/faculty/syllabi/${syllabusId}/assessment-mapping/ai-apply`;
        const res = await fetch(endpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ rows: _lastParsedMap || [] })
        });
        const j = await res.json().catch(()=>({}));
        if (res.ok) {
          alert('Assessment mapping applied successfully.');
        } else {
          alert('Apply failed: ' + (j?.error || res.status));
        }
      } catch(err){ alert('Network error: ' + (err?.message || String(err))); }
    });
    jsonWrap.appendChild(applyBtn);
    body.appendChild(jsonWrap);

    // Parsed ILO–SO–CPA JSON if available
    const iloWrap = document.createElement('div');
    iloWrap.style.cssText = 'margin-bottom:16px;padding:10px;border:1px solid #e6e9ed;border-radius:8px;background:#fcfcfc;';
    const iloTitle = document.createElement('div');
    iloTitle.textContent = 'Parsed ILO–SO–CPA (JSON)';
    iloTitle.style.cssText = 'font-weight:600;margin-bottom:8px;color:#111827;';
    iloWrap.appendChild(iloTitle);
    const iloPre = document.createElement('pre');
    iloPre.style.cssText = 'white-space:pre-wrap;word-wrap:break-word;font-size:12px;line-height:1.4;';
    try {
      const parsed = window._lastParsedIloSoCpa || null;
      iloPre.textContent = JSON.stringify(parsed || { so_columns: [], mappings: [] }, null, 2);
    } catch(e){ iloPre.textContent = '{"so_columns":[],"mappings":[]}'; }
    iloWrap.appendChild(iloPre);
    body.appendChild(iloWrap);

    modal.appendChild(head);
    modal.appendChild(body);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
  }

  function init(){
    // Wire the AI Map button (if present) to compute payloads and show viewer
    const btn = document.getElementById('svAiAutoMapBtn');
    if (btn) {
      btn.addEventListener('click', async () => {
        // Run Assessment Mapping flow first
        await triggerAutoMap();
        // Then run ILO–SO–CPA auto-map
        await runIloSoCpaAutoMap();
        // Finally, open viewer to show both outputs
        openInputViewer();
      });
    }
    // Shortcut: Shift+R to open input viewer
    document.addEventListener('keydown', (e) => {
      const isShiftR = e.shiftKey && e.key.toLowerCase() === 'r';
      if (isShiftR) {
        e.preventDefault();
        collectPhasePayloads();
        // Shortcut will only open the viewer; mapping runs on button click
        openInputViewer();
      }
    });
  }

  document.addEventListener('DOMContentLoaded', init);
  // expose
  // Parser: convert Markdown table to array of { name, week_marks, position }
  function parseMarkdownToJson(md){
    try {
      if (!md || typeof md !== 'string') return [];
      // Find the first markdown table with header containing week columns
      const lines = md.split(/\r?\n/);
      // Detect header and separator lines
      let start = -1;
      for (let i = 0; i < lines.length - 1; i++){
        const l = lines[i].trim();
        const sep = lines[i+1].trim();
        if (/^\|\s*Task\s*\|/i.test(l) && /^\|.*-+.*\|$/i.test(sep)) { start = i; break; }
      }
      if (start === -1) return [];
      // Header
      const headerCells = lines[start].replace(/^\|/,'').replace(/\|$/,'').split('|').map(c => c.trim());
      const weekLabels = headerCells.slice(1); // everything after Task
      // Consume rows until blank or non-table
      const rows = [];
      for (let i = start + 2; i < lines.length; i++){
        const ln = lines[i];
        if (!/^\|/.test(ln.trim())) break;
        const cells = ln.replace(/^\|/,'').replace(/\|$/,'').split('|').map(c => c.trim());
        if (cells.length < 1) continue;
        const name = cells[0] || '-';
        const marks = {};
        for (let w = 0; w < weekLabels.length; w++){
          const raw = (cells[1 + w] || '').trim();
          const isX = /(^x$|^X$)/.test(raw) || /x/i.test(raw);
          marks[weekLabels[w]] = isX ? 'x' : null;
        }
        rows.push({ name, week_marks: marks });
      }
      // Assign positions based on order
      return rows.map((r, idx) => ({ ...r, position: idx }));
    } catch(e){ return []; }
  }

  async function autoApplyParsedMap(rows){
    try {
      const syllabusId = document.getElementById('syllabus-document')?.getAttribute('data-syllabus-id') || null;
      if (!syllabusId) { showToast('No syllabus ID found.'); return; }
      const endpoint = `/faculty/syllabi/${syllabusId}/assessment-mapping/ai-apply`;
      const res = await fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ rows: rows || [] })
      });
      const j = await res.json().catch(()=>({}));
      if (!res.ok) {
        showToast('Auto-apply failed: ' + (j?.error || res.status));
      }
    } catch(err){ showToast('Network error: ' + (err?.message || String(err))); }
  }

  function refreshAssessmentMappingGrid(rows){
    try {
      if (!rows || !rows.length) return;
      const amRoot = document.querySelector('.assessment-mapping');
      if (!amRoot) return;
      const weekHeader = Array.from(amRoot.querySelectorAll('table.week tr:first-child th.week-number'))
        .map(th => th.textContent.trim())
        .filter(t => t && t.toLowerCase() !== 'no weeks');
      const weekRows = Array.from(amRoot.querySelectorAll('table.week tr')).slice(1);
      const distRows = Array.from(amRoot.querySelectorAll('table.distribution tr')).slice(1);
      rows.forEach((r) => {
        // Find matching task row by name; fallback to position index
        let rowIdx = -1;
        for (let i = 0; i < distRows.length; i++){
          const name = distRows[i].querySelector('input.distribution-input')?.value?.trim() || '-';
          if (name === r.name) { rowIdx = i; break; }
        }
        if (rowIdx === -1 && typeof r.position === 'number') {
          rowIdx = r.position;
        }
        const cells = Array.from(weekRows[rowIdx]?.querySelectorAll('td.week-mapping') || []);
        weekHeader.forEach((wLabel, wIdx) => {
          const mark = r.week_marks?.[wLabel] ? 'x' : '';
          const cell = cells[wIdx];
          if (!cell) return;
          cell.textContent = mark ? 'x' : '';
          if (mark) { cell.classList.add('marked'); cell.setAttribute('data-mark','x'); }
          else { cell.classList.remove('marked'); cell.removeAttribute('data-mark'); }
        });
      });
    } catch(e){ /* noop */ }
  }

  function showToast(msg){
    try {
      const toast = document.createElement('div');
      toast.textContent = msg || '';
      toast.style.cssText = 'position:fixed;bottom:20px;right:20px;background:#111827;color:#fff;padding:10px 12px;border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,0.25);z-index:99999;font-size:.9rem;';
      document.body.appendChild(toast);
      setTimeout(() => { toast.remove(); }, 3000);
    } catch(e){ /* ignore */ }
  }

  async function triggerAutoMap(){
    // Run Assessment Mapping flow directly (do not re-click the button)
    try { window._svAiMapProgress?.set('Preparing', 5, 'Checking Assessment Mapping details…', 'state-running'); } catch(e) {}
    const payloads = collectPhasePayloads();
    // Check sufficiency
    const suff = assessDataSufficiency();
    if (!suff.ok) {
      try {
        const f=document.getElementById('svAiMapProgressFill');
        if(f){ f.classList.remove('state-running','state-ok'); f.classList.add('state-warn'); }
        window._svAiMapProgress?.set('Complete', 100, suff.message || 'Not enough details to place marks yet.', 'state-warn');
      } catch(e) {}
      _lastMapOutput = 'Data not sufficient to map X marks. ' + (suff.explain || 'Please complete TLA activities and week schedule.');
      _lastParsedMap = [];
      return;
    }
    try { window._svAiMapProgress?.set('Calling AI', 20, 'Generating Assessment Mapping (Task Calendar)…', 'state-running'); } catch(e) {}
    try {
      const syllabusId = document.getElementById('syllabus-document')?.getAttribute('data-syllabus-id') || null;
      const endpoint = syllabusId ? `/faculty/syllabi/${syllabusId}/ai-chat` : null;
      if (!endpoint) { _lastMapOutput = 'AI service unavailable.'; return; }
      const fd = new FormData();
      const instruction = [
        'Analyze and generate ONLY the X marks for the Assessment Mapping (Task Calendar).',
        '- Do NOT change task names or week labels; use EXACTLY the tasks and week headers visible on the page.',
        '- Only map a task if that task appears in the Teaching, Learning, and Assessment (TLA) Activities. If a task is not present in TLA Activities, do not place any X for it.',
        '- Map weeks based on the weeks indicated in the TLA Activities for the corresponding task. If a task has weeks in TLA, place "x" in those same weeks; otherwise leave blank or "-".',
        '- Output strictly a Markdown table with the same columns as the UI: | Task | <week headers…> |.',
        '- If there are existing X marks, CORRECT them when they do not align with weekly topics/TLA chronology (assessments must occur after topics are introduced).',
        '- If TLA Activities or week data is missing for a task, explicitly leave it unmapped and do not infer new weeks.'
      ].join(' \n');
      fd.append('message', instruction);
      if (payloads?.context_phase1) fd.append('context_phase1', payloads.context_phase1);
      if (payloads?.context_phase2) fd.append('context_phase2', payloads.context_phase2);
      if (payloads?.context_phase3) fd.append('context_phase3', payloads.context_phase3);
      if (payloads?.context_all) fd.append('context_all', payloads.context_all);
      fd.append('phase', payloads?.phase || '3');
      const res = await fetch(endpoint, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 'Accept':'application/json' },
        body: fd
      });
      const j = await res.json().catch(()=>({}));
      if (res.ok && j && j.reply) {
        _lastAmOutput = j.reply;
        try { window._svAiMapProgress?.set('Parsing', 45, 'Reading suggested Assessment Mapping…', 'state-running'); } catch(e) {}
        _lastParsedMap = parseMarkdownToJson(_lastAmOutput);
        try { window._svAiMapProgress?.set('Applying', 65, 'Applying Assessment Mapping to grid…', 'state-running'); } catch(e) {}
        await autoApplyParsedMap(_lastParsedMap);
        try { window._svAiMapProgress?.set('Refreshing', 80, 'Updating Assessment Schedule UI…', 'state-running'); } catch(e) {}
        refreshAssessmentMappingGrid(_lastParsedMap);
        showToast('Assessment mapping auto-applied.');
                // Leave headroom for next flow (ILO–SO–CPA) to continue progress
                try { window._svAiMapProgress?.set('Done', 90, 'Assessment Mapping applied.', 'state-ok'); } catch(e) {}
      }
    } catch(err){ _lastMapOutput = 'Network/AI error: ' + (err?.message || String(err)); }
  }

  // Parse ILO–SO–CPA markdown to JSON payload for saving
  function parseIloSoCpaMarkdown(md){
    try {
      if (!md || typeof md !== 'string') return { so_columns: [], mappings: [] };
      const lines = md.split(/\r?\n/);
      // Find header for ILO–SO–CPA table
      let start = -1;
      for (let i = 0; i < lines.length - 1; i++){
        const l = lines[i].trim();
        const sep = lines[i+1].trim();
        if (/^\|\s*ILO\s*\|/i.test(l) && /^\|.*-+.*\|$/i.test(sep)) { start = i; break; }
      }
      if (start === -1) return { so_columns: [], mappings: [] };
      const headerCells = lines[start].replace(/^\|/,'').replace(/\|$/,'').split('|').map(c => c.trim());
      // Header format: ILO | SO n ... | C | P | A
      const soCols = [];
      for (let i = 1; i < headerCells.length - 3; i++){
        const raw = headerCells[i] || '';
        const num = (raw.match(/\d+/) || [null])[0];
        soCols.push(num ? `SO ${num}` : (raw || `SO ${i}`));
      }
      const rows = [];
      for (let i = start + 2; i < lines.length; i++){
        const ln = lines[i];
        if (!/^\|/.test(ln.trim())) break;
        const cells = ln.replace(/^\|/,'').replace(/\|$/,'').split('|').map(c => c.trim());
        if (cells.length < 2) continue;
        const iloText = cells[0] || '-';
        const sos = {};
        for (let j = 0; j < soCols.length; j++){
          const v = (cells[1 + j] || '').trim();
          sos[soCols[j]] = v || null;
        }
        const c = (cells[1 + soCols.length] || '').trim();
        const p = (cells[2 + soCols.length] || '').trim();
        const a = (cells[3 + soCols.length] || '').trim();
        rows.push({ ilo_text: iloText, sos, c, p, a, position: rows.length });
      }
      return { so_columns: soCols, mappings: rows };
    } catch(e){ return { so_columns: [], mappings: [] }; }
  }

  // Auto-map ILO–SO–CPA using AI similar to Assessment Mapping
  async function runIloSoCpaAutoMap(){
    // Continue a single progress flow: advance from current percentage without resetting
    const currentPctText = document.getElementById('svAiMapPct')?.textContent || '0%';
    const currentPct = Math.max(parseInt(String(currentPctText).replace(/[^0-9]/g,''), 10) || 0, 90);
    try { window._svAiMapProgress?.set('Preparing', Math.min(currentPct + 2, 92), 'Checking ILO–SO–CPA details…', 'state-running'); } catch(e) {}
    const payloads = collectPhasePayloads();
    // Sufficiency: need some ILO rows and SO columns present in DOM
    const soRoot = document.querySelector('.ilo-so-cpa-mapping');
    const mapTable = soRoot?.querySelector('.mapping');
    const headerRow2 = mapTable?.querySelectorAll('tr')[1];
    const soHeaders = headerRow2 ? Array.from(headerRow2.querySelectorAll('th')) : [];
    const bodyRows = Array.from((mapTable?.querySelector('tbody') || mapTable)?.querySelectorAll('tr') || []).filter(r => r.querySelector('td'));
    const ok = soHeaders.length > 0 && bodyRows.length > 0;
    if (!ok){
      try { window._svAiMapProgress?.set('Complete', 100, 'Add SO columns and ILO rows first.', 'state-warn'); } catch(e) {}
      _lastMapOutput = 'Insufficient ILO–SO–CPA data. Please add SO columns and at least one ILO row.';
      _lastParsedMap = [];
      openInputViewer();
      return;
    }
    try { window._svAiMapProgress?.set('Calling AI', Math.min(currentPct + 4, 94), 'Generating ILO–SO–CPA mapping…', 'state-running'); } catch(e) {}
    try {
      const syllabusId = document.getElementById('syllabus-document')?.getAttribute('data-syllabus-id') || null;
      const endpoint = syllabusId ? `/faculty/syllabi/${syllabusId}/ai-chat` : null;
      if (!endpoint) { _lastMapOutput = 'AI service unavailable.'; openInputViewer(); return; }
      const fd = new FormData();
      const instruction = [
        'Generate ONLY the ILO–SO–CPA mapping table based on Phase 3 snapshots.',
        '- Base linkages on the Teaching, Learning, and Assessment (TLA) Activities and the Assessment Method & Distribution Map. Use task codes such as FE/ME only when they appear in these sections.',
        '- Map ALL possible alignments: for each ILO, consider every SO that appears alongside that ILO in the TLA Activities, and include any assessment task codes (FE, ME, QCT, etc.) that occur in the Distribution Map after the related topics/weeks. Do not omit valid alignments visible in TLA.',
        '- Keep existing ILO row labels and SO columns as shown. Use numeric ILO and SO labels when available (e.g., ILO 1, SO 2).',
        '- Fill each SO cell with the appropriate linkage mark or short note if present; leave blank if not linked.',
        '- Include C, P, A values per row using assessment task CODES (e.g., FE, ME, QCT, PR, LE, LEX), not numbers. Derive these codes from the Assessment Method & Distribution Map and the TLA alignment (task occurs after the related topics and ILO/SO). Do not infer missing values; leave blank if unknown.',
        '- Do NOT include SO columns that have no linkages for any ILO (drop empty SO columns).',
        '- Output strictly one Markdown table with columns: | ILO | <SO columns…> | C | P | A |',
        '',
        'After the table, also output a JSON object with keys `so_columns` and `mappings` (array of rows with `ilo_text`, `sos` object keyed by SO column (e.g., "SO1": "FE,ME"), `c`, `p`, `a`, `position`). Use the exact SO column order from the table and DO NOT include any empty SO columns.'
      ].join(' \\n');
      fd.append('message', instruction);
      if (payloads?.context_phase1) fd.append('context_phase1', payloads.context_phase1);
      if (payloads?.context_phase2) fd.append('context_phase2', payloads.context_phase2);
      // Build candidate ILO–SO pairs from TLA and append to Phase 3 context
      function buildTlaIloSoPairsMd(){
        try {
          const table = document.getElementById('tlaTable');
          if (!table) return '';
          const tbody = table.querySelector('tbody') || table;
          const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => r.id !== 'tla-placeholder');
          const md = [];
          md.push('### Candidate ILO–SO Pairs (derived from TLA)');
          md.push('| ILO | SO |');
          md.push('|:--:|:--:|');
          function parseNums(val){
            return (String(val || '').match(/\d+/g) || []).map(s => s.trim()).filter(Boolean);
          }
          rows.forEach(row => {
            const iloVal = (row.querySelector('[name*="[ilo]"]')?.value || '').toString().trim();
            const soVal  = (row.querySelector('[name*="[so]"]')?.value  || '').toString().trim();
            const ilos = parseNums(iloVal);
            const sos  = parseNums(soVal);
            ilos.forEach(i => { sos.forEach(s => { md.push(`| ILO ${i} | SO ${s} |`); }); });
          });
          return md.length > 3 ? md.join('\n') : '';
        } catch(e){ return ''; }
      }
      const pairsMd = buildTlaIloSoPairsMd();
      const phase3WithPairs = (payloads?.context_phase3 || '') + (pairsMd ? ('\n\n' + pairsMd) : '');
      if (phase3WithPairs) fd.append('context_phase3', phase3WithPairs);
      if (payloads?.context_all) fd.append('context_all', payloads.context_all);
      fd.append('phase', payloads?.phase || '3');
      const res = await fetch(endpoint, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 'Accept':'application/json' },
        body: fd
      });
      const j = await res.json().catch(()=>({}));
      if (res.ok && j && j.reply){
        _lastIloSoCpaOutput = j.reply;
        try { window._svAiMapProgress?.set('Parsing', Math.min(currentPct + 6, 96), 'Reading suggested ILO–SO–CPA mapping…', 'state-running'); } catch(e) {}
        let parsed = parseIloSoCpaMarkdown(_lastIloSoCpaOutput);
        // Validate CPA values: enforce assessment task codes from Distribution Map
        try {
          const atRoot = document.querySelector('.at-map-outer');
          // Collect valid codes from Distribution Map (left Code column)
          const codes = new Set();
          if (atRoot) {
            const tbody = atRoot.querySelector('#at-tbody');
            const rows = tbody ? Array.from(tbody.querySelectorAll('tr')) : [];
            rows.forEach(tr => {
              const tdCode = tr.children?.[0];
              const v = (tdCode?.querySelector('textarea')?.value || tdCode?.textContent || '').toString().trim();
              if (v) codes.add(v);
            });
          }
          const normalize = (val) => {
            const raw = (val || '').toString().trim();
            if (!raw) return '';
            // split by comma and filter to known codes
            const parts = raw.split(/\s*,\s*/).filter(Boolean);
            const kept = parts.filter(p => codes.has(p));
            return kept.join(', ');
          };
          parsed.mappings = (parsed.mappings || []).map(r => ({
            ...r,
            c: normalize(r.c),
            p: normalize(r.p),
            a: normalize(r.a)
          }));
        } catch(e) { /* ignore CPA validation errors */ }
        // Prune SO columns with no mappings across all rows
        try {
          const cols = parsed?.so_columns || [];
          const rows = parsed?.mappings || [];
          const keep = cols.filter(col => rows.some(r => {
            const v = (r?.sos || {})[col];
            return v && String(v).trim() !== '' && String(v).toLowerCase() !== 'null';
          }));
          if (keep.length !== cols.length) {
            parsed.so_columns = keep;
            parsed.mappings = rows.map(r => {
              const sosObj = r.sos || {};
              const pruned = {};
              keep.forEach(k => { pruned[k] = sosObj[k] || null; });
              return { ...r, sos: pruned };
            });
          }
        } catch(e) { /* ignore prune errors */ }
        window._lastParsedIloSoCpa = parsed; // store globally for viewer
        // Save via backend endpoint
        try { window._svAiMapProgress?.set('Applying', Math.min(currentPct + 8, 98), 'Saving ILO–SO–CPA to database…', 'state-running'); } catch(e) {}
        await saveIloSoCpa(parsed);
        // Refresh UI grid to reflect applied mapping
        try { window._svAiMapProgress?.set('Refreshing', Math.min(currentPct + 9, 99), 'Updating ILO–SO–CPA table…', 'state-running'); } catch(e) {}
        refreshIloSoCpaGridFromParsed(parsed);
        try { window._svAiMapProgress?.set('Done', 100, 'ILO–SO–CPA mapping applied.', 'state-ok'); } catch(e) {}
      } else {
        _lastMapOutput = j?.error ? ('AI Error: ' + j.error) : 'No reply received.';
        try { window._svAiMapProgress?.set('Complete', 100, 'AI did not return mapping.', 'state-warn'); } catch(e) {}
      }
    } catch(err){ _lastMapOutput = 'Network/AI error: ' + (err?.message || String(err)); try { window._svAiMapProgress?.set('Complete', 100, 'Network/AI error.', 'state-warn'); } catch(e) {} }
    openInputViewer();
  }

  function refreshIloSoCpaGridFromParsed(parsed){
    try {
      if (!parsed || !Array.isArray(parsed.mappings)) return;
      const soRoot = document.querySelector('.ilo-so-cpa-mapping');
      if (!soRoot) return;
      const mapTable = soRoot.querySelector('.mapping');
      const headerRow2 = mapTable?.querySelectorAll('tr')[1];
      const ths = headerRow2 ? Array.from(headerRow2.querySelectorAll('th')) : [];
      const soColIndex = {}; // label -> column index in row tds (starting at 1 after ILO)
      ths.forEach((th, i) => {
        const raw = (th.querySelector('input')?.value || th.textContent || '').toString().trim();
        const num = (raw.match(/\d+/) || [null])[0];
        const key = num ? `SO ${num}` : (raw || `SO ${i+1}`);
        soColIndex[key] = 1 + i; // first col is ILO
      });
      const bodyRows = Array.from((mapTable?.querySelector('tbody') || mapTable).querySelectorAll('tr')).filter(r => r.querySelector('td'));
      parsed.mappings.forEach((r, pos) => {
        // find row by ILO text; fallback to position
        let rowEl = null;
        for (let i = 0; i < bodyRows.length; i++){
          const tds = Array.from(bodyRows[i].querySelectorAll('td'));
          const rawIlo = (tds[0]?.querySelector('input')?.value || tds[0]?.textContent || '').toString().trim();
          if (rawIlo === r.ilo_text) { rowEl = bodyRows[i]; break; }
        }
        if (!rowEl && typeof r.position === 'number') rowEl = bodyRows[r.position] || null;
        if (!rowEl) return;
        const tds = Array.from(rowEl.querySelectorAll('td'));
        // fill SO cells
        (parsed.so_columns || []).forEach(col => {
          const idx = soColIndex[col];
          if (!idx || !tds[idx]) return;
          const val = (r.sos || {})[col] || '';
          const ta = tds[idx].querySelector('textarea');
          if (ta) ta.value = val; else tds[idx].textContent = val;
        });
        // fill C, P, A (last 3)
        const cIdx = tds.length - 3;
        const pIdx = tds.length - 2;
            try { window._svAiMapProgress?.set('Done', 100, 'ILO–SO–CPA mapping applied.', 'state-ok'); } catch(e) {}
        const setCell = (cell, val) => { const ta = cell?.querySelector('textarea'); if (ta) ta.value = val || ''; else if (cell) cell.textContent = val || ''; };
        setCell(tds[cIdx], r.c || '');
        setCell(tds[pIdx], r.p || '');
        setCell(tds[aIdx], r.a || '');
      });
    } catch(e){ /* ignore refresh errors */ }
  }

  async function saveIloSoCpa(parsed){
    try {
      const syllabusId = document.getElementById('syllabus-document')?.getAttribute('data-syllabus-id') || null;
      if (!syllabusId) { showToast('No syllabus ID found.'); return; }
      const endpoint = '/faculty/syllabus/save-ilo-so-cpa-mapping';
      const payload = { syllabus_id: syllabusId, so_columns: parsed?.so_columns || [], mappings: parsed?.mappings || [] };
      const res = await fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
      });
      const j = await res.json().catch(()=>({}));
      if (!res.ok){ showToast('Save failed: ' + (j?.error || res.status)); }
    } catch(err){ showToast('Network error: ' + (err?.message || String(err))); }
  }

  // Assess sufficiency of data based on TLA activities presence and week headers
  function assessDataSufficiency(){
    try {
      const amRoot = document.querySelector('.assessment-mapping');
      const weekHeader = Array.from(amRoot?.querySelectorAll('table.week tr:first-child th.week-number')||[])
        .map(th => th.textContent.trim()).filter(t => t && t.toLowerCase() !== 'no weeks');
      const distRows = Array.from(amRoot?.querySelectorAll('table.distribution tr')||[]).slice(1);
      const hasTasks = distRows.some(dr => (dr.querySelector('input.distribution-input')?.value||'').trim());
      // TLA activities block presence: look for TLA textarea or context snapshot
      const tlaBlock = (typeof window._svRealtimeContext === 'string') ? /PARTIAL_BEGIN:tla[\s\S]*PARTIAL_END:tla/m.test(window._svRealtimeContext) : false;
      const tlaDom = document.querySelector('.tla-activities, .tla-section, .tla, .tla-partial, #tlaTable');
      const hasTla = tlaBlock || !!tlaDom;
      const messages = [];
      if (!hasTla && !weekHeader.length && !hasTasks) {
        return { ok: false, message: 'We need TLA activities, tasks, and weeks before we can map.', explain: 'No TLA details, tasks, or week schedule found.' };
      }
      if (!hasTla) messages.push('Add some Teaching, Learning, and Assessment activities.');
      if (!hasTasks) messages.push('Add at least one task in Distribution.');
      if (!weekHeader.length) messages.push('Add week headers to the schedule.');
      const ok = hasTla && hasTasks && weekHeader.length > 0;
      return { ok, message: ok ? '' : ('Looks like we need more info: ' + messages.join(' ')), explain: messages.join(' ') };
    } catch(e){ return { ok: false, message: 'We need a bit more info to map correctly.', explain: '' }; }
  }

  window._aiMap = { collectPhasePayloads, openInputViewer, parseMarkdownToJson, triggerAutoMap, assessDataSufficiency, runIloSoCpaAutoMap, parseIloSoCpaMarkdown, refreshIloSoCpaGridFromParsed };
})();
