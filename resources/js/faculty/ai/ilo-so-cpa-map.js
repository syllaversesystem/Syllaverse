// ILO–SO & ILO–CPA Mapping: initializer and scaffolding
// Wires the syllabus page button, preview modal, and scaffolds functions
// for snapshot, AI call, parse, and apply (to be implemented next).

let _lastInput = null;   // snapshot text (to be populated next step)
let _lastReply = null;   // AI reply (markdown/table or structured text)
let _lastParsed = null;  // parsed mapping rows (structure TBD)
let _lastJson = null;    // parsed JSON payload for deterministic apply
let _autoApply = true;   // default: apply automatically when valid

// Progress UI (reuses shared IDs if present)
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

function showInfoOverlay(title, message) {
  let overlay = document.getElementById('svIloSoCpaInfo');
  if (!overlay) {
    overlay = document.createElement('div');
    overlay.id = 'svIloSoCpaInfo';
    overlay.style.position = 'fixed';
    overlay.style.top = '0';
    overlay.style.left = '0';
    overlay.style.right = '0';
    overlay.style.bottom = '0';
    overlay.style.background = 'rgba(0,0,0,0.25)';
    overlay.style.zIndex = '20060';
    overlay.style.display = 'none';
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-modal', 'true');

    const panel = document.createElement('div');
    panel.style.position = 'absolute';
    panel.style.top = '50%';
    panel.style.left = '50%';
    panel.style.transform = 'translate(-50%, -50%)';
    panel.style.background = '#fff';
    panel.style.border = '1px solid #e2e5e9';
    panel.style.borderRadius = '12px';
    panel.style.boxShadow = '0 16px 40px rgba(0,0,0,0.12)';
    panel.style.width = 'min(680px, calc(100vw - 40px))';
    panel.style.maxHeight = '70vh';
    panel.style.overflow = 'auto';

    panel.innerHTML = `
      <div style="padding:14px 16px; border-bottom:1px solid #f1f3f5; display:flex; align-items:center; justify-content:space-between;">
        <div style="display:flex; align-items:center; gap:.5rem;">
          <i class="bi bi-diagram-3" aria-hidden="true" style="color:#CB3737;"></i>
          <strong>${title}</strong>
        </div>
        <button type="button" id="svIloSoCpaInfoClose" class="btn btn-sm btn-light" aria-label="Close">
          Close
        </button>
      </div>
      <div style="padding:16px;">
        <div class="text-muted" style="font-size:.95rem;">${message}</div>
        <div class="mt-3">
          <ul style="margin:0; padding-left:1rem; font-size:.93rem;">
            <li>Maps ILOs to SO and CPA with strict alignment.</li>
            <li>Produces deterministic JSON for apply, plus a readable preview.</li>
            <li>Runs on demand and keeps your current selections intact.</li>
          </ul>
        </div>
      </div>
    `;
    overlay.appendChild(panel);
    document.body.appendChild(overlay);

    const closeBtn = panel.querySelector('#svIloSoCpaInfoClose');
    if (closeBtn) closeBtn.addEventListener('click', function(){ overlay.style.display = 'none'; });
    overlay.addEventListener('click', function(ev){ if (ev.target === overlay) overlay.style.display = 'none'; });
    document.addEventListener('keydown', function(ev){ if (ev.key === 'Escape' && overlay.style.display !== 'none') overlay.style.display = 'none'; });
  }
  overlay.style.display = '';
}

function openPreviewModal(){
  // Refresh snapshot before opening preview
  try {
    if (typeof window.updateIloSoCpaRealtimeSnapshot === 'function') window.updateIloSoCpaRealtimeSnapshot();
    _lastInput = buildIloSoCpaSnapshot();
  } catch(e) {}
  // Build lightweight preview modal similar to Assessment Map
  const overlay = document.createElement('div');
  overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:20070;display:flex;align-items:center;justify-content:center;';
  const modal = document.createElement('div');
  modal.style.cssText = 'width:80%;max-width:900px;max-height:80%;background:#fff;border-radius:8px;box-shadow:0 8px 32px rgba(0,0,0,0.25);display:flex;flex-direction:column;';
  const head = document.createElement('div');
  head.style.cssText = 'padding:12px 16px;border-bottom:1px solid #e5e5e5;display:flex;gap:8px;align-items:center;font-weight:600;';
  head.textContent = 'AI Preview — ILO–SO & ILO–CPA Mapping';
  const closeBtn = document.createElement('button');
  closeBtn.textContent = 'Close';
  closeBtn.style.cssText = 'margin-left:auto;padding:6px 10px;border:1px solid #ccc;background:#f7f7f7;border-radius:6px;cursor:pointer;';
  closeBtn.addEventListener('click', () => overlay.remove());
  head.appendChild(closeBtn);
  const body = document.createElement('div');
  body.style.cssText = 'padding:12px 16px;overflow:auto;';

  function addBlock(title, html){
    const wrap = document.createElement('div');
    wrap.style.cssText = 'margin-bottom:16px;padding:10px;border:1px solid #e6e9ed;border-radius:8px;background:#fcfcfc;';
    const t = document.createElement('div'); t.textContent = title; t.style.cssText = 'font-weight:600;margin-bottom:8px;color:#111827;';
    const content = document.createElement('div'); content.style.cssText = 'font-size:.9rem;line-height:1.45;color:#111827;';
    content.innerHTML = html;
    wrap.appendChild(t); wrap.appendChild(content); body.appendChild(wrap);
  }

  // Input snapshot (placeholder until snapshot logic is added)
  const safeInput = (_lastInput || '').replace(/[&<>]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[c]));
  addBlock('Input Snapshot', safeInput ? '<pre style="white-space:pre-wrap;word-wrap:break-word;margin:0">'+safeInput+'</pre>' : '<div class="text-muted">No snapshot captured yet. Use the button to build one.</div>');
  // AI output placeholder / formatted
  const formatted = (function(){
    const reply = _lastReply || '';
    if (!reply) return '<div class="text-muted">No AI output yet. This preview will display the proposed mappings and the JSON payload once available.</div>';
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

  const footer = document.createElement('div');
  footer.style.cssText = 'padding:10px 16px;border-top:1px solid #e5e5e5;display:flex;gap:8px;justify-content:flex-end;';
  const applyBtn = document.createElement('button');
  applyBtn.textContent = 'Apply Mappings';
  applyBtn.style.cssText = 'padding:6px 12px;border:1px solid #ccc;background:#f7f7f7;border-radius:6px;cursor:pointer;';
  applyBtn.addEventListener('click', function(){
    try {
      applyParsedMappings();
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

// Scaffolding: snapshot builder (to be implemented next step)
function buildIloSoCpaSnapshot(){
  try {
    const root = document.querySelector('.ilo-so-cpa-mapping');
    if (!root) return '';
    const mappingTable = root.querySelector('.mapping');
    if (!mappingTable) return '';
    const headerRows = mappingTable.querySelectorAll('tr');
    if (headerRows.length < 2) return '';
    const headerRow2 = headerRows[1];
    const tbody = mappingTable.querySelector('tbody') || mappingTable;
    const dataRows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.querySelector('td'));

    // Collect SO columns (inputs-only; include empty labels as '')
    const allHeaders = Array.from(headerRow2.querySelectorAll('th'));
    const iloHeaderIndex = allHeaders.findIndex(th => (th.textContent || '').includes('ILOs'));
    const cHeaderIndex = allHeaders.findIndex(th => (th.textContent || '').trim() === 'C');
    const soHeaders = allHeaders.slice(iloHeaderIndex + 1, cHeaderIndex);
    const soLabels = soHeaders.map(th => {
      const input = th.querySelector('input');
      const v = input ? (input.value || '').trim() : (th.textContent || '').trim();
      return v === 'No SO' ? '' : v;
    });

    const md = [];
    // Prepend Assessment Method and Distribution Map snapshot if present
    try {
      const atTable = document.querySelector('.at-map-outer .cis-table');
      const atTbody = atTable?.querySelector('#at-tbody') || atTable?.querySelector('tbody');
      const atThead = atTable?.querySelector('thead');
      if (atTable && atTbody && atThead) {
        const hr2 = atThead.querySelector('tr:nth-child(2)');
        const headers = [];
        if (hr2) {
          headers.push('Code','Task','I/R/D','%');
          const ths = Array.from(hr2.children);
          const last3Start = ths.length - 3;
          for (let i=4; i<last3Start; i++){ headers.push(ths[i].textContent.trim() || String(i-3)); }
          headers.push('C','P','A');
        } else {
          headers.push('Code','Task','I/R/D','%','ILO','C','P','A');
        }
        md.push('');
        md.push('PARTIAL_BEGIN:assessment_method_distribution');
        md.push('Assessment Method and Distribution Map');
        md.push('Columns: ' + headers.join(' | '));
        md.push('');
        md.push('| ' + headers.join(' | ') + ' |');
        md.push('|' + headers.map((h,i)=> (i===1?':--':':--:')).join('|') + '|');
        let dataRowCount = 0;
        const getTxt = (cell) => {
          if (!cell) return '-';
          const t = cell.querySelector('textarea');
          if (t) { const v = (t.value||'').trim(); return v || '-'; }
          const v = (cell.textContent||'').trim(); return v || '-';
        };
        Array.from(atTbody.querySelectorAll('tr')).forEach(row => {
          if (!(row.classList.contains('at-main-row') || row.classList.contains('at-sub-row'))) return;
          const vals = Array.from(row.children).map(c => getTxt(c).replace(/\r?\n/g,' ').trim());
          md.push('| ' + vals.join(' | ') + ' |');
          dataRowCount++;
        });
        if (!dataRowCount) md.push('| - | - | - | - | - | - | - | - |');
        md.push('');
        md.push('<!-- AMD_ROWS:'+dataRowCount+' -->');
        md.push('PARTIAL_END:assessment_method_distribution');
      }
    } catch(e) { /* ignore AMD snapshot errors */ }
    // Append ILO list snapshot block just below AMD (inputs-only)
    try {
      const iloList = document.getElementById('syllabus-ilo-sortable');
      const iloRows = Array.from(iloList?.querySelectorAll('tr') || []).filter(r => r.querySelector('textarea[name="ilos[]"]') || r.querySelector('.ilo-badge'));
      md.push('');
      md.push('PARTIAL_BEGIN:ilo_list');
      md.push('Intended Learning Outcomes (ILO) — Inputs Only');
      md.push('Columns: ILO | Description');
      md.push('');
      md.push('| ILO | Description |');
      md.push('|:---:|:-----------|');
      if (!iloRows.length) {
        md.push('| - | - |');
      } else {
        iloRows.forEach((row, i) => {
          const codeEl = row.querySelector('.ilo-badge');
          const code = (codeEl?.textContent || `ILO${i+1}`).trim();
          const ta = row.querySelector('textarea[name="ilos[]"]');
          const desc = (ta?.value || '').trim() || '-';
          const oneLine = desc.replace(/\r?\n/g, ' ').trim();
          md.push(`| ${code} | ${oneLine} |`);
        });
      }
      md.push('');
      md.push(`<!-- ILO_LIST_ROWS:${iloRows.length} -->`);
      md.push('PARTIAL_END:ilo_list');
    } catch(e) { /* ignore ILO list snapshot errors */ }
    // Append SO list snapshot block below ILO list (inputs-only)
    try {
      const soList = document.getElementById('syllabus-so-sortable');
      const soRows = Array.from(soList?.querySelectorAll('tr') || []).filter(r => r.querySelector('textarea[name=\"sos[]\"]') || r.querySelector('.so-badge'));
      md.push('');
      md.push('PARTIAL_BEGIN:so_list');
      md.push('Student Outcomes (SO) — Inputs Only');
      md.push('Columns: SO | Title | Description');
      md.push('');
      md.push('| SO | Title | Description |');
      md.push('|:--:|:-----|:-----------|');
      if (!soRows.length) {
        md.push('| - | - | - |');
      } else {
        soRows.forEach((row, i) => {
          const codeEl = row.querySelector('.so-badge');
          const code = (codeEl?.textContent || `SO${i+1}`).trim();
          const titleTa = row.querySelector('textarea[name=\"so_titles[]\"]');
          const descTa = row.querySelector('textarea[name=\"sos[]\"]');
          const title = (titleTa?.value || '').trim() || '-';
          const desc = (descTa?.value || '').trim() || '-';
          const oneLineTitle = title.replace(/\r?\n/g, ' ').trim();
          const oneLineDesc = desc.replace(/\r?\n/g, ' ').trim();
          md.push(`| ${code} | ${oneLineTitle} | ${oneLineDesc} |`);
        });
      }
      md.push('');
      md.push(`<!-- SO_LIST_ROWS:${soRows.length} -->`);
      md.push('PARTIAL_END:so_list');
    } catch(e) { /* ignore SO list snapshot errors */ }
    // Prepend TLA snapshot block (inputs-only)
    try {
      const tlaTable = document.getElementById('tlaTable');
      const tbody = tlaTable?.querySelector('tbody');
      const rows = Array.from(tbody?.querySelectorAll('tr:not(#tla-placeholder)') || []);
      md.push('');
      md.push('PARTIAL_BEGIN:tla');
      md.push('<!-- TLA_ROWS:'+rows.length+' -->');
      md.push('Teaching, Learning, and Assessment (TLA) Activities');
      md.push('Columns: Ch. | Topics / Reading List | Wks. | Topic Outcomes | ILO | SO | Delivery Method');
      md.push('');
      md.push('| Ch. | Topics / Reading List | Wks. | Topic Outcomes | ILO | SO | Delivery Method |');
      md.push('|:---:|:----------------------|:----:|:---------------|:---:|:--:|:-----------------|');
      const valOrDash = (el) => { if (!el) return '-'; const v = (el.value ?? '').toString().trim(); return v ? v : '-'; };
      if (rows.length === 0) {
        md.push('| - | - | - | - | - | - | - |');
      } else {
        rows.forEach((row) => {
          const ch = valOrDash(row.querySelector('[name*="[ch]"]'));
          const topic = valOrDash(row.querySelector('[name*="[topic]"]'));
          const wks = valOrDash(row.querySelector('[name*="[wks]"]'));
          const outcomes = valOrDash(row.querySelector('[name*="[outcomes]"]'));
          const ilo = valOrDash(row.querySelector('[name*="[ilo]"]'));
          const so = valOrDash(row.querySelector('[name*="[so]"]'));
          const delivery = valOrDash(row.querySelector('[name*="[delivery]"]'));
          md.push(`| ${ch} | ${topic} | ${wks} | ${outcomes} | ${ilo} | ${so} | ${delivery} |`);
        });
      }
      md.push('PARTIAL_END:tla');
    } catch(e) { /* ignore TLA snapshot errors */ }

    md.push('');
    md.push('PARTIAL_BEGIN:ilo_so_cpa');
    // Header row
    const headerCells = ['ILO'].concat(soLabels.length ? soLabels : ['SO']).concat(['C','P','A']);
    md.push('| ' + headerCells.join(' | ') + ' |');
    md.push('|:--|' + (soLabels.length ? soLabels.map(()=>':--').join('|') : ':--') + '|:--:|:--:|:--:|');

    // Rows
    dataRows.forEach(row => {
      const cells = Array.from(row.querySelectorAll('td'));
      const iloCell = cells[0];
      // ILO value: prefer input value; fallback to text; convert placeholder to '-'
      let iloVal = (iloCell?.querySelector('input')?.value || '').trim();
      if (!iloVal) { iloVal = (iloCell?.textContent || '').trim(); }
      if (iloVal === 'No ILO') iloVal = '-';
      // SO values: aligned to headers; inputs-only; placeholder '-' if missing/disabled
      const soVals = [];
      soHeaders.forEach((_, idx) => {
        const soCell = cells[idx + 1];
        const ta = soCell ? soCell.querySelector('textarea') : null;
        let val = ta ? (ta.value || '').trim() : '';
        if (!val) val = '-';
        soVals.push(val.replace(/\r?\n/g,' ').trim());
      });
      // C, P, A: last three cells; inputs-only; '-' if empty
      const cCell = cells[cells.length - 3];
      const pCell = cells[cells.length - 2];
      const aCell = cells[cells.length - 1];
      const cVal = (cCell?.querySelector('textarea')?.value || '').trim() || '-';
      const pVal = (pCell?.querySelector('textarea')?.value || '').trim() || '-';
      const aVal = (aCell?.querySelector('textarea')?.value || '').trim() || '-';
      const rowVals = [iloVal.replace(/\r?\n/g,' ').trim()].concat(soVals).concat([cVal,pVal,aVal].map(v=>v.replace(/\r?\n/g,' ').trim()));
      md.push('| ' + rowVals.join(' | ') + ' |');
    });

    if (!dataRows.length) md.push('| - | - | - | - | - |');
    // Attach marker lines for counts
    md.push('');
    md.push(`<!-- ILO_ROWS:${dataRows.length} -->`);
    md.push(`<!-- SO_COLS:${soLabels.length} -->`);
    md.push('PARTIAL_END:ilo_so_cpa');
    return md.join('\n');
  } catch(e){ return ''; }
}

// Scaffolding: parse reply sections (markdown and JSON)
function parseIloSoCpaMarkdown(md){
  try {
    // TODO: Parse mapping table into a structured array
    return [];
  } catch(e){ return []; }
}
function parseIloSoCpaJson(text){
  try {
    const m = text.match(/\[\s*\{[\s\S]*?\}\s*\]/);
    if (!m) return null;
    const arr = JSON.parse(m[0]);
    return Array.isArray(arr) ? arr : null;
  } catch(e){ return null; }
}

// Scaffolding: apply parsed mappings back to the UI (strict mode planned)
function applyParsedMappings(){
  const root = document.getElementById('iloSoCpaMapping') || document;
  const sourceRows = (_lastJson && Array.isArray(_lastJson)) ? _lastJson : _lastParsed;
  if (!sourceRows || !sourceRows.length) throw new Error('No parsed AI data to apply');
  // TODO: Implement deterministic apply to ILO–SO and ILO–CPA tables once structure is defined
}

// Scaffolding: AI call (will use snapshot in the next step)
async function callAiForIloSoCpa(){
  try {
    setProgress('Preparing', 5, 'Getting things ready…', 'state-running');
    _lastInput = buildIloSoCpaSnapshot();
    if (!_lastInput) {
      setProgress('Complete', 100, 'Add ILOs and current mappings to continue.', 'state-warn');
      // Professional message; no modal auto-open here
      if (window.showAlertOverlay) window.showAlertOverlay('info', 'Add ILOs and current mappings to continue.');
      return;
    }
    setProgress('Calling AI', 35, 'Asking AI to map ILO to SO & CPA…', 'state-running');
    const syllabusId = document.getElementById('syllabus-document')?.getAttribute('data-syllabus-id') || null;
    const endpoint = syllabusId ? `/faculty/syllabi/${syllabusId}/ai-chat` : null;
    if (!endpoint) { setProgress('Complete', 100, 'AI service is not available right now.', 'state-warn'); return; }
    const fd = new FormData();
    const instruction = [
      'Map ILOs to Student Outcomes (SO) and Course Performance Assessment (CPA) deterministically.',
      'STRICT mode: Use only provided ILO text and current SO/CPA taxonomies present in the snapshot. Do not invent items.',
      'Output two parts in order:',
      '1) A single Markdown table with clear ILO → SO and ILO → CPA relations.',
      '2) A JSON array `mapping`: items with `{ ilo:"…", so:[…], cpa:[…] }` using identifiers from the snapshot.',
      'No prose; keep names as provided; ensure deterministic alignment.'
    ].join('\n');
    fd.append('message', instruction);
    fd.append('context_phase_ilo_so_cpa', _lastInput);
    fd.append('phase', 'ilo_so_cpa');
    const res = await fetch(endpoint, { method:'POST', headers:{ 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 'Accept':'application/json' }, body: fd });
    const j = await res.json().catch(()=>({}));
    if (res.ok && j && j.reply){
      setProgress('Processing', 65, 'Reviewing AI suggestion…', 'state-running');
      _lastReply = j.reply;
      _lastParsed = parseIloSoCpaMarkdown(_lastReply);
      _lastJson = parseIloSoCpaJson(_lastReply);
      if (_autoApply) {
        try {
          setProgress('Applying', 90, 'Applying mappings to your tables…', 'state-running');
          applyParsedMappings();
          setProgress('Applied', 95, 'Your mappings have been updated.', 'state-ok');
        } catch(e){ setProgress('Complete', 100, 'We couldn’t update the mappings. Please try again.', 'state-warn'); }
      }
      // Keep preview on-demand via Shift+2 or button
      setProgress('Done', 100, 'All set.', 'state-ok');
    } else {
      setProgress('Complete', 100, 'We didn’t get a valid AI response. Please try again.', 'state-warn');
    }
  } catch(err){ setProgress('Complete', 100, 'There was a problem reaching AI. Please try again.', 'state-warn'); }
}

function initIloSoCpaBtn(){
  const btn = document.getElementById('svAiIloSoCpaBtn');
  if (!btn || btn.dataset.bound) return;
  btn.dataset.bound = '1';
  btn.addEventListener('click', function(){
    // Trigger AI mapping first; preview is available on demand
    try { callAiForIloSoCpa(); } catch(e){}
  });
  // Shortcut Shift+2 ("@") to open preview modal
  document.addEventListener('keydown', function(e){
    if (e.shiftKey && e.key === '@') {
      e.preventDefault();
      // Open preview on demand
      openPreviewModal();
    }
  });

  // Build and merge snapshot into realtime context
  function updateRealtimeSnapshot(){
    try {
      const snap = buildIloSoCpaSnapshot();
      const existing = window._svRealtimeContext || '';
      const cleaned = (existing || '')
        .replace(/PARTIAL_BEGIN:tla[\s\S]*?PARTIAL_END:tla/g, '')
        .replace(/PARTIAL_BEGIN:assessment_method_distribution[\s\S]*?PARTIAL_END:assessment_method_distribution/g, '')
        .replace(/PARTIAL_BEGIN:ilo_list[\s\S]*?PARTIAL_END:ilo_list/g, '')
        .replace(/PARTIAL_BEGIN:so_list[\s\S]*?PARTIAL_END:so_list/g, '')
        .replace(/PARTIAL_BEGIN:ilo_so_cpa[\s\S]*?PARTIAL_END:ilo_so_cpa/g, '')
        .trim();
      const merged = [cleaned, snap].filter(Boolean).map(s=>s.trim()).join('\n\n').trim();
      window._svRealtimeContext = merged;
    } catch(e) { /* ignore */ }
  }
  try { window.updateIloSoCpaRealtimeSnapshot = updateRealtimeSnapshot; } catch(e) {}

  // Snapshot on actions: inputs/changes and DOM mutations in ILO–SO/CPA area
  const mappingRoot = document.querySelector('.ilo-so-cpa-mapping');
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
  bindListeners(mappingRoot);
  // Initial snapshot build so context exists
  setTimeout(updateRealtimeSnapshot, 50);
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', initIloSoCpaBtn);

// Expose minimal API for future wiring
try { window._iloSoCpaMap = { init: initIloSoCpaBtn, buildSnapshot: buildIloSoCpaSnapshot, callAi: callAiForIloSoCpa, apply: applyParsedMappings, setAutoApply: (v)=>{ _autoApply = !!v; } }; } catch(e) {}
