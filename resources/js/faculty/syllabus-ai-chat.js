// -----------------------------------------------------------------------------
// * File: resources/js/faculty/syllabus-ai-chat.js
// * Description: AI Chat (right toolbar) initialization and backend wiring.
// * Extracted from inline Blade script for maintainability.
// -----------------------------------------------------------------------------

(function(){
  // In-memory conversation buffer
  const _convo = [];
  function pushConvo(role, content){
    if (!role || typeof content !== 'string') return;
    const r = (role.toLowerCase() === 'you' || role.toLowerCase() === 'user') ? 'user' : 'assistant';
    _convo.push({ role: r, content: content });
  }
  function buildHistoryPayload(excludeLastUser){
    const MAX_MSGS = 10;
    const MAX_TOTAL = 8000;
    let msgs = _convo.slice();
    if (excludeLastUser && msgs.length){
      const last = msgs[msgs.length - 1];
      if (last.role === 'user') msgs = msgs.slice(0, msgs.length - 1);
    }
    // take last MAX_MSGS
    msgs = msgs.slice(Math.max(0, msgs.length - MAX_MSGS));
    let total = 0;
    const trimmed = [];
    for (const m of msgs){
      let c = m.content || '';
      if (!c) continue;
      if (total >= MAX_TOTAL) break;
      const room = MAX_TOTAL - total;
      if (c.length > room) c = c.slice(0, room);
      trimmed.push({ role: m.role, content: c });
      total += c.length;
    }
    return trimmed;
  }
  // Basic Markdown formatter (headings, lists, code blocks, bold/italic, tables) with HTML sanitization
  function formatAIResponse(raw){
    if (!raw) return '';
    let text = String(raw);
    // Escape HTML first
    text = text.replace(/[&<>]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[c]));
    // Code blocks ```lang\n...```
    text = text.replace(/```([a-zA-Z0-9_-]+)?\n([\s\S]*?)```/g, (m, lang, code) => {
      const safeCode = code.replace(/\n$/,'').replace(/&/g,'&amp;').replace(/</g,'&lt;');
      const cls = lang ? ` class="lang-${lang.toLowerCase()}"` : '';
      return `<pre><code${cls}>${safeCode}</code></pre>`;
    });
    // Inline code `code`
    text = text.replace(/`([^`]+)`/g, (m, code) => `<code>${code}</code>`);
    // Headings ###, ##, #
    text = text.replace(/^###\s*(.+)$/gm, '<h4>$1</h4>');
    text = text.replace(/^##\s*(.+)$/gm, '<h3>$1</h3>');
    text = text.replace(/^#\s*(.+)$/gm, '<h2>$1</h2>');
    // Bold **text** and italic *text*
    text = text.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
    text = text.replace(/\*(?!\*)([^*]+)\*/g, '<em>$1</em>');
    // Lists - item / * item
    function listify(src){
      return src.replace(/^(?:-|\*)\s.+(?:\n(?:-|\*)\s.+)*/gm, block => {
        const items = block.split(/\n/).map(l => l.replace(/^(?:-|\*)\s*/, '').trim());
        return '<ul>' + items.map(it => `<li>${it}</li>`).join('') + '</ul>';
      });
    }
    text = listify(text);
    // Numbered lists 1. item
    text = text.replace(/^(?:\d+)\.\s.+(?:\n(?:\d+)\.\s.+)*/gm, block => {
      const items = block.split(/\n/).map(l => l.replace(/^\d+\.\s*/, '').trim());
      return '<ol>' + items.map(it => `<li>${it}</li>`).join('') + '</ol>';
    });
    // Tables: detect blocks starting and ending with pipe rows + separator
    // Simple markdown table pattern: header row, separator row of --- and optional alignment colons, then data rows
    function convertTables(src){
      return src.replace(/((?:^\|.*\n)+)(?=^\S|$)/gm, block => {
        const lines = block.trim().split(/\n/).filter(l => /^\|/.test(l.trim()));
        if (lines.length < 2) return block; // need at least header + separator
        const header = lines[0];
        const separator = lines[1];
        if (!/\|\s*:?-{3,}:?\s*/.test(separator)) return block; // second line must be separator style
        const rows = lines.slice(2);
        function splitRow(r){
          return r.replace(/^\|/,'').replace(/\|$/,'').split(/\|/).map(c => c.trim());
        }
        const headers = splitRow(header);
        const body = rows.map(r => splitRow(r));
        // Build HTML table
        let html = '<div class="sv-ai-table-wrap"><table class="sv-ai-table"><thead><tr>' + headers.map(h => `<th>${h}</th>`).join('') + '</tr></thead>';        
        if (body.length){
          html += '<tbody>' + body.map(cells => '<tr>' + cells.map(c => `<td>${c}</td>`).join('') + '</tr>').join('') + '</tbody>';
        }
        html += '</table></div>';
        return html;
      });
    }
    text = convertTables(text);
    // Paragraphs: wrap remaining lines not already block-level
    const blockPattern = /<(h[2-5]|ul|ol|pre|code|blockquote|table|div class="sv-ai-table-wrap")/i;
    const lines = text.split(/\n+/).filter(l => l.trim() !== '');
    text = lines.map(l => blockPattern.test(l) || /^<li>/.test(l) ? l : `<p>${l}</p>`).join('');
    return text;
  }
  function getSyllabusId(){
    return document.getElementById('syllabus-document')?.getAttribute('data-syllabus-id') || null;
  }
  function appendMsg(role, text){
    const wrap = document.getElementById('svAiChatMessages');
    if (!wrap) return null;
    const row = document.createElement('div');
    const isUser = (role || '').toLowerCase() === 'you' || (role || '').toLowerCase() === 'user';
    row.className = 'sv-ai-msg ' + (isUser ? 'user' : 'ai');
    const bubble = document.createElement('div');
    bubble.className = 'bubble';
    if (!isUser) {
      try { bubble.innerHTML = formatAIResponse(text || ''); } catch(e){ bubble.textContent = text || ''; }
    } else {
      bubble.textContent = text || '';
    }
    row.appendChild(bubble);
    wrap.appendChild(row);
    wrap.scrollTop = wrap.scrollHeight;
    try { pushConvo(role, text || ''); } catch(e) {}
    return row;
  }
  function appendLoading(){
    const wrap = document.getElementById('svAiChatMessages');
    if (!wrap) return null;
    const row = document.createElement('div');
    row.className = 'sv-ai-msg ai loading';
    const bubble = document.createElement('div');
    bubble.className = 'bubble';
    bubble.textContent = 'Processing full syllabus…';
    row.appendChild(bubble);
    wrap.appendChild(row);
    wrap.scrollTop = wrap.scrollHeight;
    return row;
  }
  function buildSelectedSectionContent(){
    // Detect highlighted / outlined partial (review or selection logic may apply outline inline styles)
    const active = Array.from(document.querySelectorAll('.sv-partial')).find(el => {
      const outline = (el.style && el.style.outline) ? el.style.outline : '';
      return outline && outline.includes('2px');
    });
    if (!active) return null;
    const key = active.getAttribute('data-partial-key') || 'selected-section';
    const text = active.textContent?.trim() || '';
    if (!text) return null;
    return { key, text };
  }
  function collectContext(){
    // Gather key syllabus sections (limit length per section)
    const sections = [];
    const MAX_SECTION_CHARS = 900; // per section cap
    const partials = document.querySelectorAll('.sv-partial');
    partials.forEach(p => {
      const key = p.getAttribute('data-partial-key') || 'unknown';
      // Avoid giant status/meta sections
      if (key === 'status') return;
      let text = p.textContent || '';
      text = text.replace(/\s+/g,' ').trim();
      if (!text) return;
      if (text.length > MAX_SECTION_CHARS) text = text.slice(0, MAX_SECTION_CHARS) + ' …';
      sections.push(`# ${key}\n${text}`);
    });
    // Prioritize selected section by moving it to top if exists
    const selected = buildSelectedSectionContent();
    if (selected) {
      const idx = sections.findIndex(s => s.startsWith(`# ${selected.key}\n`));
      if (idx > 0) {
        const item = sections.splice(idx,1)[0];
        sections.unshift(item);
      }
    }
      // Do not inject TLA into lightweight context; rely on full snapshot/realtime
    let combined = sections.join("\n\n");
    const MAX_TOTAL = 6000;
    if (combined.length > MAX_TOTAL) combined = combined.slice(0, MAX_TOTAL) + "\n[Context truncated]";
    return combined;
  }
  // Full snapshot of syllabus: serialize each partial and its form field values
  function collectFullSnapshot(){
    const snapshotParts = [];
    const partials = Array.from(document.querySelectorAll('.sv-partial'));
    function serializePartial(p){
      const key = p.getAttribute('data-partial-key') || 'unknown';
      if (key === 'status') return null; // skip status meta
      if (key === 'tla') return null; // do not add TLA to full snapshot
      // Collect headings
      const headingTexts = Array.from(p.querySelectorAll('h1,h2,h3,h4,h5,h6,th'))
        .map(h => h.textContent.trim())
        .filter(Boolean);
      // Main text content (exclude <style>/<script> noise)
      let textBlock = '';
      try {
        const clone = p.cloneNode(true);
        clone.querySelectorAll('style,script,noscript').forEach(el => el.remove());
        textBlock = (clone.textContent || '').replace(/\s+/g,' ').trim();
      } catch(e) {
        textBlock = (p.textContent || '').replace(/\s+/g,' ').trim();
      }
      if (textBlock.length > 1400) textBlock = textBlock.slice(0,1400) + ' …';
      // Form fields with label resolution
      const fields = [];
      p.querySelectorAll('input, textarea, select').forEach(f => {
        const name = f.name || f.id || '';
        let val = '';
        if (f.tagName === 'SELECT') {
          val = f.multiple ? Array.from(f.selectedOptions).map(o=>o.value).join(', ') : f.value;
        } else if (f.type === 'checkbox') {
          val = f.checked ? 'checked' : 'unchecked';
        } else if (f.type === 'radio') {
          if (!f.checked) return; val = f.value;
        } else {
          val = f.value;
        }
        val = (val || '').toString().trim();
        if (val === '') return;
        let labelText = '';
        if (f.id) {
          const lbl = p.querySelector(`label[for="${CSS.escape(f.id)}"]`);
          if (lbl) labelText = lbl.textContent.trim();
        }
        if (!labelText) {
          const prevLabel = f.closest('div,td,th')?.querySelector('label');
          if (prevLabel) labelText = prevLabel.textContent.trim();
        }
        const fieldName = labelText || name || 'field';
        fields.push({ label: fieldName, value: val });
      });
      // Structured tagged block for AI parsing
      const lines = [];
      lines.push(`PARTIAL_BEGIN:${key}`);
      if (headingTexts.length) lines.push('HEADINGS:' + headingTexts.join(' | '));
      // Special handling: SO list (codes, titles, descriptions)
      try {
        const soTable = p.querySelector('#syllabus-so-sortable');
        if (soTable) {
          const rows = Array.from(soTable.querySelectorAll('tr'));
          const soRows = rows.filter(r => !r.id || r.id !== 'so-placeholder');
          if (soRows.length) {
            lines.push('SO_START');
            soRows.forEach((row, index) => {
              const code = row.querySelector('.so-badge')?.textContent?.trim() || row.querySelector('input[name="code[]"]')?.value?.trim() || `SO${index+1}`;
              const title = row.querySelector('textarea[name="so_titles[]"]')?.value?.trim() || '';
              const desc  = row.querySelector('textarea[name="sos[]"]')?.value?.trim() || '';
              const t = title.length > 160 ? (title.slice(0,160) + ' …') : title;
              const d = desc.length  > 320 ? (desc.slice(0,320)   + ' …') : desc;
              lines.push(`${code}: ${t} | ${d}`);
            });
            lines.push('SO_END');
          }
        }
      } catch(e) { /* ignore SO snapshot errors */ }
      // Special handling: IGA list (codes, titles, descriptions)
      try {
        const igaTable = p.querySelector('#syllabus-iga-sortable');
        if (igaTable) {
          const rows = Array.from(igaTable.querySelectorAll('tr.iga-row'));
          lines.push('IGA_START');
          rows.forEach((row, index) => {
            const code = row.querySelector('.iga-badge')?.textContent?.trim() || `IGA${index+1}`;
            const title = row.querySelector('textarea[name="iga_titles[]"]')?.value?.trim() || '';
            const desc  = row.querySelector('textarea[name="igas[]"]')?.value?.trim() || '';
            // clamp very long fields
            const t = title.length > 160 ? (title.slice(0,160) + ' …') : title;
            const d = desc.length  > 320 ? (desc.slice(0,320)   + ' …') : desc;
            lines.push(`${code}: ${t} | ${d}`);
          });
          lines.push('IGA_END');
        }
      } catch(e) { /* ignore IGA snapshot errors */ }
      // Special handling: CDIO list (codes, titles, descriptions)
      try {
        const cdioTable = p.querySelector('#syllabus-cdio-sortable');
        if (cdioTable) {
          const rows = Array.from(cdioTable.querySelectorAll('tr')).filter(r => !r.id || r.id !== 'cdio-placeholder');
          lines.push('CDIO_START');
          if (rows.length) {
            rows.forEach((row, index) => {
              const code = row.querySelector('.cdio-badge')?.textContent?.trim() || row.querySelector('input[name="code[]"]')?.value?.trim() || `CDIO${index+1}`;
              const title = row.querySelector('textarea[name="cdio_titles[]"]')?.value?.trim() || '';
              const desc  = row.querySelector('textarea[name="cdios[]"]')?.value?.trim() || '';
              const t = title.length > 160 ? (title.slice(0,160) + ' …') : title;
              const d = desc.length  > 320 ? (desc.slice(0,320)   + ' …') : desc;
              lines.push(`${code}: ${t} | ${d}`);
            });
          }
          lines.push('CDIO_END');
        }
      } catch(e) { /* ignore CDIO snapshot errors */ }
      // Special handling: SDG list (codes, titles, descriptions)
      try {
        const sdgTable = p.querySelector('#syllabus-sdg-sortable');
        if (sdgTable) {
          const rows = Array.from(sdgTable.querySelectorAll('tr')).filter(r => !r.id || r.id !== 'sdg-placeholder');
          lines.push('SDG_START');
          if (rows.length) {
            rows.forEach((row, index) => {
              const code = row.querySelector('.sdg-badge')?.textContent?.trim() || row.querySelector('input[name="code[]"]')?.value?.trim() || `SDG${index+1}`;
              const title = row.querySelector('textarea[name="sdg_titles[]"]')?.value?.trim() || '';
              const desc  = row.querySelector('textarea[name="sdgs[]"]')?.value?.trim() || '';
              const t = title.length > 160 ? (title.slice(0,160) + ' …') : title;
              const d = desc.length  > 320 ? (desc.slice(0,320)   + ' …') : desc;
              lines.push(`${code}: ${t} | ${d}`);
            });
          }
          lines.push('SDG_END');
        }
      } catch(e) { /* ignore SDG snapshot errors */ }
      // (Removed) Do not serialize TLA rows in full snapshot
      // Special handling: Course Policies (five areas)
      try {
        const isPolicies = (key === 'course-policies') || p.classList.contains('course-policies');
        if (isPolicies) {
          const sectionKeys = ['policy','exams','dishonesty','dropping','other'];
          const labels = {
            policy: 'Class policy',
            exams: 'Missed examinations',
            dishonesty: 'Academic dishonesty',
            dropping: 'Dropping',
            other: 'Other course policies and requirements'
          };
          const tas = Array.from(p.querySelectorAll('textarea[name="course_policies[]"]'));
          lines.push('POLICIES_START');
          for (let i = 0; i < sectionKeys.length; i++) {
            const keyName = sectionKeys[i];
            const ta = tas[i];
            let val = (ta && typeof ta.value === 'string') ? ta.value.trim() : '';
            if (val.length > 800) val = val.slice(0,800) + ' …';
            if (val === '') val = '[empty]';
            lines.push(`${labels[keyName]}: ${val}`);
          }
          lines.push('POLICIES_END');
        }
      } catch(e) { /* ignore policies snapshot errors */ }
      // Special handling: Assessment Mapping (distribution + week grid)
      try {
        const mappingWrap = p.querySelector('.assessment-mapping');
        if (mappingWrap) {
          const distRows = Array.from(mappingWrap.querySelectorAll('table.distribution tr:not(:first-child)'));
          const weekHeader = Array.from(mappingWrap.querySelectorAll('table.week tr:first-child th.week-number'))
            .map(th => th.textContent.trim())
            .filter(t => t && t.toLowerCase() !== 'no weeks')
            .map(t => parseInt(t, 10))
            .filter(n => !isNaN(n));
          const weekRows = Array.from(mappingWrap.querySelectorAll('table.week tr:not(:first-child) td.week-mapping'));
          lines.push('GRID_START');
          lines.push('WEEKS:' + (weekHeader.length ? weekHeader.join(',') : ''));
          // Each row: name | marks
          distRows.forEach((dr, idx) => {
            const name = dr.querySelector('input.distribution-input')?.value?.trim() || '';
            const cell = weekRows[idx];
            let marks = [];
            if (cell) {
              const spans = Array.from(cell.querySelectorAll('span'));
              marks = spans.map((s, sIdx) => (s.textContent.trim() === 'x') ? (weekHeader[sIdx] || '') : '')
                           .filter(v => v !== '');
            }
            lines.push(`ROW:${name} | ${marks.join(',')}`);
          });
          lines.push('GRID_END');
        }
      } catch(e){ /* ignore grid snapshot errors */ }
      if (fields.length) {
        lines.push('FIELDS_START');
        fields.forEach(f => lines.push(`${f.label} = ${f.value}`));
        lines.push('FIELDS_END');
      }
      if (textBlock) {
        lines.push('TEXT_START');
        lines.push(textBlock);
        lines.push('TEXT_END');
      }
      lines.push(`PARTIAL_END:${key}`);
      return lines.join('\n');
    }
    partials.forEach(p => { const part = serializePartial(p); if (part) snapshotParts.push(part); });
    // Ensure Course Policies appears early to avoid truncation effects
    try {
      const idx = snapshotParts.findIndex(s => s.indexOf('PARTIAL_BEGIN:course-policies') !== -1);
      if (idx > 0) {
        const pol = snapshotParts.splice(idx, 1)[0];
        snapshotParts.unshift(pol);
      }
    } catch(e) { /* ignore reorder errors */ }
    // Ensure TLA appears early as well so it's not trimmed
    try {
      const idxTla = snapshotParts.findIndex(s => s.indexOf('PARTIAL_BEGIN:tla') !== -1);
      // place TLA right after Course Policies if both exist, else at the top
      if (idxTla > 0) {
        const tla = snapshotParts.splice(idxTla, 1)[0];
        const polIdxNow = snapshotParts.findIndex(s => s.indexOf('PARTIAL_BEGIN:course-policies') !== -1);
        const insertPos = (polIdxNow === 0) ? 1 : 0;
        snapshotParts.splice(insertPos, 0, tla);
      }
    } catch(e) { /* ignore reorder errors */ }
    // Fallback: include SO block even if not wrapped in .sv-partial
    try {
      const hasSoBlock = snapshotParts.some(s => s.includes('PARTIAL_BEGIN:so'));
      const soTable = document.getElementById('syllabus-so-sortable');
      if (!hasSoBlock && soTable) {
        const rows = Array.from(soTable.querySelectorAll('tr')).filter(r => !r.id || r.id !== 'so-placeholder');
        if (rows.length) {
          const lines = [];
          lines.push('PARTIAL_BEGIN:so');
          lines.push('HEADINGS:Student Outcomes (SO) | SO | Student Outcomes (SO) Statements');
          lines.push('SO_START');
          rows.forEach((row, index) => {
            const code = row.querySelector('.so-badge')?.textContent?.trim() || row.querySelector('input[name="code[]"]')?.value?.trim() || `SO${index+1}`;
            const title = row.querySelector('textarea[name="so_titles[]"]')?.value?.trim() || '';
            const desc  = row.querySelector('textarea[name="sos[]"]')?.value?.trim() || '';
            const t = title.length > 160 ? (title.slice(0,160) + ' …') : title;
            const d = desc.length  > 320 ? (desc.slice(0,320)   + ' …') : desc;
            lines.push(`${code}: ${t} | ${d}`);
          });
          lines.push('SO_END');
          lines.push('PARTIAL_END:so');
          snapshotParts.push(lines.join('\n'));
        }
      }
    } catch(e) { /* ignore SO fallback errors */ }
    // Fallback: include IGA block even if not wrapped in .sv-partial
    try {
      const hasIgaBlock = snapshotParts.some(s => s.includes('PARTIAL_BEGIN:iga'));
      const igaTable = document.getElementById('syllabus-iga-sortable');
      if (!hasIgaBlock && igaTable) {
        const rows = Array.from(igaTable.querySelectorAll('tr.iga-row'));
        if (rows.length) {
          const lines = [];
          lines.push('PARTIAL_BEGIN:iga');
          lines.push('HEADINGS:Institutional Graduate Attributes (IGA)');
          lines.push('IGA_START');
          rows.forEach((row, index) => {
            const code = row.querySelector('.iga-badge')?.textContent?.trim() || `IGA${index+1}`;
            const title = row.querySelector('textarea[name="iga_titles[]"]')?.value?.trim() || '';
            const desc  = row.querySelector('textarea[name="igas[]"]')?.value?.trim() || '';
            const t = title.length > 160 ? (title.slice(0,160) + ' …') : title;
            const d = desc.length  > 320 ? (desc.slice(0,320)   + ' …') : desc;
            lines.push(`${code}: ${t} | ${d}`);
          });
          lines.push('IGA_END');
          lines.push('PARTIAL_END:iga');
          snapshotParts.push(lines.join('\n'));
        }
      }
    } catch(e) { /* ignore fallback errors */ }
    // Fallback: include CDIO block even if not wrapped in .sv-partial
    try {
      const hasCdioBlock = snapshotParts.some(s => s.includes('PARTIAL_BEGIN:cdio'));
      const cdioTable = document.getElementById('syllabus-cdio-sortable');
      if (!hasCdioBlock && cdioTable) {
        const rows = Array.from(cdioTable.querySelectorAll('tr')).filter(r => !r.id || r.id !== 'cdio-placeholder');
        const lines = [];
        lines.push('PARTIAL_BEGIN:cdio');
        lines.push('HEADINGS:CDIO Framework Skills (CDIO) | CDIO | CDIO Framework Skills Statements');
        lines.push('CDIO_START');
        if (rows.length) {
          rows.forEach((row, index) => {
            const code = row.querySelector('.cdio-badge')?.textContent?.trim() || row.querySelector('input[name="code[]"]')?.value?.trim() || `CDIO${index+1}`;
            const title = row.querySelector('textarea[name="cdio_titles[]"]')?.value?.trim() || '';
            const desc  = row.querySelector('textarea[name="cdios[]"]')?.value?.trim() || '';
            const t = title.length > 160 ? (title.slice(0,160) + ' …') : title;
            const d = desc.length  > 320 ? (desc.slice(0,320)   + ' …') : desc;
            lines.push(`${code}: ${t} | ${d}`);
          });
        }
        lines.push('CDIO_END');
        lines.push('PARTIAL_END:cdio');
        snapshotParts.push(lines.join('\n'));
      }
    } catch(e) { /* ignore CDIO fallback errors */ }
    // Fallback: include SDG block even if not wrapped in .sv-partial
    try {
      const hasSdgBlock = snapshotParts.some(s => s.includes('PARTIAL_BEGIN:sdg'));
      const sdgTable = document.getElementById('syllabus-sdg-sortable');
      if (!hasSdgBlock && sdgTable) {
        const rows = Array.from(sdgTable.querySelectorAll('tr')).filter(r => !r.id || r.id !== 'sdg-placeholder');
        const lines = [];
        lines.push('PARTIAL_BEGIN:sdg');
        lines.push('HEADINGS:Sustainable Development Goals (SDG) | SDG | Sustainable Development Goals (SDG) Statements');
        lines.push('SDG_START');
        if (rows.length) {
          rows.forEach((row, index) => {
            const code = row.querySelector('.sdg-badge')?.textContent?.trim() || row.querySelector('input[name="code[]"]')?.value?.trim() || `SDG${index+1}`;
            const title = row.querySelector('textarea[name="sdg_titles[]"]')?.value?.trim() || '';
            const desc  = row.querySelector('textarea[name="sdgs[]"]')?.value?.trim() || '';
            const t = title.length > 160 ? (title.slice(0,160) + ' …') : title;
            const d = desc.length  > 320 ? (desc.slice(0,320)   + ' …') : desc;
            lines.push(`${code}: ${t} | ${d}`);
          });
        }
        lines.push('SDG_END');
        lines.push('PARTIAL_END:sdg');
        snapshotParts.push(lines.join('\n'));
      }
    } catch(e) { /* ignore SDG fallback errors */ }
    // (Removed) Do not add TLA fallback block to full snapshot
    // Fallback: include Course Policies block even if not wrapped in .sv-partial
    try {
      const hasPolicies = snapshotParts.some(s => s.includes('PARTIAL_BEGIN:course-policies'));
      const policiesRoot = document.querySelector('table.course-policies') || document.querySelector('.course-policies');
      if (!hasPolicies && policiesRoot) {
        const lines = [];
        lines.push('PARTIAL_BEGIN:course-policies');
        lines.push('HEADINGS:Course Policies | Grading System | Class policy | Missed examinations | Academic dishonesty | Dropping | Other course policies and requirements');
        // Collect the five policy textareas in order
        const sectionKeys = ['policy','exams','dishonesty','dropping','other'];
        const labels = {
          policy: 'Class policy',
          exams: 'Missed examinations',
          dishonesty: 'Academic dishonesty',
          dropping: 'Dropping',
          other: 'Other course policies and requirements'
        };
        const textareas = Array.from(policiesRoot.querySelectorAll('textarea[name="course_policies[]"]'));
        if (textareas.length) {
          lines.push('FIELDS_START');
          for (let i = 0; i < sectionKeys.length; i++) {
            const key = sectionKeys[i];
            const ta = textareas[i];
            const val = (ta && typeof ta.value === 'string') ? ta.value.trim() : '';
            if (val !== '') {
              const v = val.length > 800 ? (val.slice(0,800) + ' …') : val;
              lines.push(`${labels[key]} = ${v}`);
            }
          }
          lines.push('FIELDS_END');
        }
        // Add a compact text block (grading table headings and any visible labels)
        try {
          const clone = policiesRoot.cloneNode(true);
          clone.querySelectorAll('style,script,noscript').forEach(el => el.remove());
          let text = (clone.textContent || '').replace(/\s+/g,' ').trim();
          if (text && text.length > 1200) text = text.slice(0,1200) + ' …';
          if (text) {
            lines.push('TEXT_START');
            lines.push(text);
            lines.push('TEXT_END');
          }
        } catch(e) { /* ignore text extraction errors */ }
        lines.push('PARTIAL_END:course-policies');
        // Prepend to prioritize inclusion before any truncation
        snapshotParts.unshift(lines.join('\n'));
      }
    } catch(e) { /* ignore Course Policies fallback errors */ }
    let full = snapshotParts.join('\n\n');
    const MAX_SNAPSHOT = 18000;
    if (full.length > MAX_SNAPSHOT) full = full.slice(0, MAX_SNAPSHOT) + '\n[Snapshot truncated]';
    try {
      const hasSO = full.includes('SO_START');
      const hasIGA = full.includes('IGA_START');
      const hasCDIO = full.includes('CDIO_START');
      const hasSDG = full.includes('SDG_START');
      const hasPOL = full.includes('PARTIAL_BEGIN:course-policies');
      const hasPOLBlock = full.includes('POLICIES_START');
      const hasTLA = full.includes('PARTIAL_BEGIN:tla');
      console.debug('[AIChat][snapshot]', { length: full.length, hasSO, hasIGA, hasCDIO, hasSDG, hasPOL, hasPOLBlock, hasTLA });
    } catch(e) {}
    return full;
  }
  function autosizeTextarea(el){
    if (!el) return;
    const maxPx = 160; // ~10rem max
    el.style.height = 'auto';
    const newH = Math.min(el.scrollHeight, maxPx);
    el.style.height = newH + 'px';
    el.style.overflowY = (el.scrollHeight > maxPx) ? 'auto' : 'hidden';
  }
  async function sendMessage(){
    const aiInput = document.getElementById('svAiChatInput');
    if (!aiInput) return;
    const val = (aiInput.value || '').trim();
    if (!val) return;
    // Ensure latest realtime context is captured before snapshot
    try { if (typeof window.rebuildTlaRealtimeContext === 'function') window.rebuildTlaRealtimeContext(); } catch(e) {}
    appendMsg('you', val);
    aiInput.value = '';
    autosizeTextarea(aiInput);
    const syllabusId = getSyllabusId();
    const endpoint = syllabusId ? `/faculty/syllabi/${syllabusId}/ai-chat` : null;
    if (!endpoint) { appendMsg('ai', 'AI service unavailable.'); return; }
    const loadingRow = appendLoading();
    const fd = new FormData(); fd.append('message', val);
    try {
      // Phase 1: send a minimal snapshot (mission_vision, course_info, ilo, so, iga, cdio, sdg)
      function collectPhase1Snapshot(){
        const full = collectFullSnapshot() || '';
        const real = (typeof window._svRealtimeContext === 'string') ? window._svRealtimeContext : '';
        const keys = ['mission_vision','course_info','tlas','ilo','so','iga','cdio','sdg'];
        const blocks = [];
        const found = [];
        keys.forEach(k => {
          const re = new RegExp(`PARTIAL_BEGIN:${k}[\\s\\S]*?PARTIAL_END:${k}`, 'm');
          let m = full.match(re);
          if (!m && real) { m = real.match(re); }
          if (m && m[0]) { blocks.push(m[0]); found.push(k); }
        });
        // Build a compact table overview for Phase 1
        function summarizeBlock(b){
          if (!b) return '-';
          // Prefer HEADINGS line then first TEXT or first ROW
          const mHead = b.match(/HEADINGS:(.+)/);
          const mText = b.match(/TEXT_START\n([\s\S]*?)\nTEXT_END/);
          const mRow = b.match(/^(ROW:.*)$/m);
          const head = mHead ? mHead[1].trim() : '';
          const txt = mText ? mText[1].trim().replace(/\s+/g,' ') : (mRow ? mRow[1] : '');
          const s = (head ? head + ' — ' : '') + txt;
          return s.length > 240 ? (s.slice(0,240) + ' …') : (s || '-');
        }
        const overviewRows = keys.map(k => {
          const re = new RegExp(`PARTIAL_BEGIN:${k}[\\s\\S]*?PARTIAL_END:${k}`, 'm');
          const blk = blocks.find(b => re.test(b));
          const present = blk ? 'Yes' : 'No';
          const summary = blk ? summarizeBlock(blk) : '-';
          return `| ${k} | ${present} | ${summary} |`;
        });
        const table = [
          '| Section | Present | Summary |',
          '|:--|:--:|:--|',
          ...overviewRows
        ].join('\n');
        let payload = table + (blocks.length ? ('\n\n' + blocks.join('\n\n')) : '');
        const MAX = 8000; // keep small for phase 1
        if (payload.length > MAX) payload = payload.slice(0, MAX) + '\n[Context trimmed]';
        try { console.debug('[AIChat][Phase1] Found keys:', found); } catch(e) {}
        return payload;
      }
      const phase1 = collectPhase1Snapshot();
      try {
        if (phase1) {
          const preview = phase1.slice(0, 1000);
          console.debug('[AIChat][Phase1] Sending essentials snapshot', { length: phase1.length, preview });
          // Log full payload as a separate entry for deep inspection
          console.debug('[AIChat][Phase1] Payload FULL:\n' + phase1);
        } else {
          console.debug('[AIChat][Phase1] No essentials snapshot found');
        }
      } catch(e) {}
      // Phase 2: Assessment (TLA) Activities snapshot from TLA structure
      function collectPhase2Snapshot(){
        const full = collectFullSnapshot() || '';
        const real = (typeof window._svRealtimeContext === 'string') ? window._svRealtimeContext : '';
        // Prefer realtime TLA block, fallback to full snapshot
        const reTla = /PARTIAL_BEGIN:tla[\s\S]*?PARTIAL_END:tla/;
        const mReal = real.match(reTla);
        const mFull = full.match(reTla);
        const tlaBlock = (mReal && mReal[0]) || (mFull && mFull[0]) || '';
        if (!tlaBlock) return '';
        // Parse lines to a compact table that mirrors the blade structure
        const lines = tlaBlock.split('\n');
        const headerTitle = lines.find(l => l.includes('Teaching, Learning, and Assessment')) || 'Teaching, Learning, and Assessment (TLA) Activities';
        const tblHead = 'Columns: Ch. | Topics / Reading List | Wks. | Topic Outcomes | ILO | SO | Delivery Method';
        const rowLines = lines.filter(l => /^ROW:\d+ \| /.test(l));
        const fieldsLines = lines.filter(l => /^FIELDS_ROW:\d+ \| /.test(l));
        // Build markdown table
        const tableHeader = ['| # | Ch. | Topics / Reading List | Wks. | Topic Outcomes | ILO | SO | Delivery |','|:--:|:--:|:--|:--:|:--|:--:|:--:|:--|'];
        const rowsMd = rowLines.map((rl, idx) => {
          // Example: ROW:1 | Ch:1 | Wks:1 | Topic:... | Outcomes:... | ILO:... | SO:... | Delivery:...
          const get = (label) => {
            const m = rl.match(new RegExp(label+':([^|]+)'));
            return m ? m[1].trim() : '-';
          };
          const ch = get('Ch');
          const wks = get('Wks');
          const topic = get('Topic');
          const outcomes = get('Outcomes');
          const ilo = get('ILO');
          const so = get('SO');
          const delivery = get('Delivery');
          return `| ${idx+1} | ${ch} | ${topic} | ${wks} | ${outcomes} | ${ilo} | ${so} | ${delivery} |`;
        });
        let overview = [
          `### ${headerTitle}`,
          tblHead,
          ...tableHeader,
          ...rowsMd
        ].join('\n');

        // Criteria for Assessment: attempt to extract and summarize sections with items and %
        const reCriteria = /PARTIAL_BEGIN:criteria_assessment[\s\S]*?PARTIAL_END:criteria_assessment/;
        const mCrit = full.match(reCriteria) || real.match(reCriteria);
        if (mCrit && mCrit[0]) {
          const critBlock = mCrit[0];
          // Try to find JSON payload lines or simple text with percentages
          // First, look for a JSON blob (criteria_data) if present
          let sections = [];
          try {
            const jsonMatch = critBlock.match(/criteria_data\s*=\s*(\[.*?\]|\{[\s\S]*?\})/);
            if (jsonMatch && jsonMatch[1]) {
              const parsed = JSON.parse(jsonMatch[1]);
              if (Array.isArray(parsed)) sections = parsed;
            }
          } catch(e) { /* ignore parse errors */ }
          // Fallback: parse lines with "<desc> <num>%"
          if (!sections.length) {
            const critLines = critBlock.split('\n');
            const items = critLines.map(l => {
              const m = l.match(/(.+?)\s+(\d+\s*%)/);
              if (m) return { heading: '', value: [{ description: m[1].trim(), percent: m[2].trim() }] };
              return null;
            }).filter(Boolean);
            if (items.length) sections = items;
          }
          const critHeader = ['| Section | Item | % |','|:--|:--|:--:|'];
          const critRows = [];
          sections.forEach(sec => {
            const heading = (sec.heading || sec.key || '').toString() || '-';
            const vals = Array.isArray(sec.value) ? sec.value : [];
            if (!vals.length) {
              critRows.push(`| ${heading} | - | - |`);
              return;
            }
            vals.forEach(v => {
              const desc = (v.description || v.label || '').toString() || '-';
              const pct = (v.percent || '').toString() || '-';
              critRows.push(`| ${heading} | ${desc} | ${pct} |`);
            });
          });
          overview += '\n\n' + ['### Criteria for Assessment', ...critHeader, ...critRows].join('\n');
          try {
            console.debug('[AIChat][Phase2][Criteria] Sections:', sections.length, 'Rows:', critRows.length);
            const critPreview = ['### Criteria for Assessment', ...critHeader, ...critRows].join('\n').slice(0, 800);
            console.debug('[AIChat][Phase2][Criteria] Preview:\n' + critPreview);
          } catch(e) {}
          // Append raw criteria block for fidelity later
          tlaBlock; // keep reference to satisfy linter, raw appended below
        } else {
          // Include an explicit empty criteria table so model knows it's absent
          const critHeader = ['| Section | Item | % |','|:--|:--|:--:|'];
          const critRows = ['| - | - | - |'];
          overview += '\n\n' + ['### Criteria for Assessment', ...critHeader, ...critRows, '', '[No criteria section found – include best‑practice defaults or ask for details]'].join('\n');
          try {
            console.debug('[AIChat][Phase2][Criteria] No criteria block found; sending placeholder table');
          } catch(e) {}
        }

        // Assessment Tasks Distribution (AT) summary — parse DOM table if present
        try {
          const atRoot = document.querySelector('.at-map-outer');
          if (atRoot) {
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

            overview += '\n\n' + md.join('\n');
            console.debug('[AIChat][Phase2][AT] Built AT summary with', rows.length, 'row(s), ILO cols:', iloCount);
          } else {
            overview += '\n\n### Assessment Tasks Distribution\nNote: AT table not present in the current view.';
            console.debug('[AIChat][Phase2][AT] Table not present; added placeholder section.');
          }
        } catch(err) {
          console.warn('[AIChat][Phase2][AT] Error building AT summary:', err);
        }

        // Course Policies compact summary — extract grading table and policy textareas
        try {
          const polRoot = document.querySelector('table.course-policies') || document.querySelector('.course-policies');
          if (polRoot) {
            const md = [];
            md.push('### Course Policies');
            // Grading System compact table: label | grade | range
            const inner = polRoot.querySelector('.cis-inner') || polRoot;
            const rows = inner ? Array.from(inner.querySelectorAll('tbody > tr')) : [];
            const gradeRows = rows.filter(r => r.children && r.children.length >= 3 && r.children[0].textContent.trim() && /\d/.test(r.children[1].textContent));
            if (gradeRows.length) {
              md.push('| Label | Grade | Range |');
              md.push('|:--|:--:|:--|');
              gradeRows.forEach(r => {
                const label = r.children[0].textContent.trim();
                const grade = r.children[1].textContent.trim();
                const range = r.children[2].textContent.trim();
                md.push(`| ${label} | ${grade} | ${range} |`);
              });
            }
            // Textareas for sections: order is Class policy, Missed examinations, Academic dishonesty, Dropping, Other
            const tas = Array.from(polRoot.querySelectorAll('textarea[name="course_policies[]"]'));
            if (tas.length) {
              const labels = ['Class policy','Missed examinations','Academic dishonesty','Dropping','Other course policies and requirements'];
              md.push('\n#### Policy Texts');
              md.push('| Section | Content |');
              md.push('|:--|:--|');
              tas.forEach((ta, i) => {
                let val = (ta.value || '').toString().trim();
                if (val.length > 600) val = val.slice(0,600) + ' …';
                const label = labels[i] || `Section ${i+1}`;
                md.push(`| ${label} | ${val || '-'} |`);
              });
            }
            overview += '\n\n' + md.join('\n');
            console.debug('[AIChat][Phase2][Policies] Added Course Policies summary', { gradeRows: gradeRows.length, sections: (polRoot.querySelectorAll('textarea[name="course_policies[]"]').length) });
          } else {
            overview += '\n\n### Course Policies\nNote: Course Policies section not present in the current view.';
            console.debug('[AIChat][Phase2][Policies] Not present; added placeholder.');
          }
        } catch(err) {
          console.warn('[AIChat][Phase2][Policies] Error building policies summary:', err);
        }
        // Attach raw block at the end for fidelity
        const critRaw = (mCrit && mCrit[0]) ? ("\n\n" + mCrit[0]) : '';
        let payload = overview + '\n\n' + tlaBlock + critRaw;
        const MAX = 12000; // allow more for phase 2
        if (payload.length > MAX) payload = payload.slice(0, MAX) + '\n[Context trimmed]';
        try {
          const preview = payload.slice(0, 1000);
          console.debug('[AIChat][Phase2] Sending TLA + Criteria snapshot', { rows: rowsMd.length, length: payload.length, preview });
          console.debug('[AIChat][Phase2] Payload FULL:\n' + payload);
        } catch(e) {}
        return payload;
      }
      const phase2 = collectPhase2Snapshot();
      // Attach both Phase 1 and Phase 2 if available, preserving order
      if (phase1) fd.append('context_phase1', phase1);
      if (phase2) fd.append('context_phase2', phase2);
      // Set current phase indicator (prefer 2 when present)
      if (phase2) fd.append('phase', '2'); else if (phase1) fd.append('phase', '1');
      const hist = buildHistoryPayload(true);
      if (hist && hist.length) fd.append('history', JSON.stringify(hist));
    } catch(e) { /* noop */ }
    fetch(endpoint, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '', 'Accept':'application/json' },
      body: fd
    }).then(async res => {
      let reply = 'Sorry, I could not generate a response.';
      try {
        const j = await res.json();
        if (res.ok && j && j.reply) reply = j.reply; else if (j && j.error) reply = 'AI Error: ' + j.error;
      } catch(e) { /* noop */ }
      if (loadingRow) loadingRow.remove();
      appendMsg('ai', reply);
    }).catch(() => { if (loadingRow) loadingRow.remove(); appendMsg('ai', 'Network error reaching AI service.'); });
  }
  function init(){
    const aiInput = document.getElementById('svAiChatInput');
    const aiSend = document.getElementById('svAiChatSend');
    if (aiSend) aiSend.addEventListener('click', sendMessage);
    if (aiInput) {
      aiInput.addEventListener('input', () => autosizeTextarea(aiInput));
      aiInput.addEventListener('keydown', (e) => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); } });
      autosizeTextarea(aiInput);
    }
    // Optional keyboard shortcut: Shift+T to open TLA context viewer
    document.addEventListener('keydown', (e) => {
      if (e.shiftKey && e.key.toLowerCase() === 't') {
        try { if (typeof window._aiChat?.openTlaContextViewer === 'function') window._aiChat.openTlaContextViewer(); } catch(err) {}
      }
    });
  }
  document.addEventListener('DOMContentLoaded', init);
  // expose for debugging
  try {
    function extractTlaBlocks(full){
      const blocks = [];
      if (!full) return blocks;
      const re = /PARTIAL_BEGIN:tla[\s\S]*?PARTIAL_END:tla/g;
      let m;
      while ((m = re.exec(full))){ blocks.push(m[0]); }
      return blocks;
    }
    function openTlaContextViewer(){
      const full = collectFullSnapshot();
      const real = (typeof window._svRealtimeContext === 'string') ? window._svRealtimeContext : '';
      const blocks = extractTlaBlocks(full);
      const mergedView = [
        '--- Full Snapshot (extracted TLA) ---',
        (blocks[0] || '[No TLA block found in full snapshot]'),
        '',
        '--- Realtime Context ---',
        (real || '[No realtime context]')
      ].join('\n');
      const overlay = document.createElement('div');
      overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:9999;display:flex;align-items:center;justify-content:center;';
      const modal = document.createElement('div');
      modal.style.cssText = 'width:80%;max-width:960px;max-height:80%;background:#fff;border-radius:8px;box-shadow:0 8px 32px rgba(0,0,0,0.25);display:flex;flex-direction:column;';
      const head = document.createElement('div');
      head.style.cssText = 'padding:12px 16px;border-bottom:1px solid #e5e5e5;display:flex;justify-content:space-between;align-items:center;font-weight:600;';
      head.textContent = 'TLA Context Viewer';
      const closeBtn = document.createElement('button');
      closeBtn.textContent = 'Close';
      closeBtn.style.cssText = 'margin-left:auto;padding:6px 10px;border:1px solid #ccc;background:#f7f7f7;border-radius:6px;cursor:pointer;';
      closeBtn.addEventListener('click', () => overlay.remove());
      head.appendChild(closeBtn);
      const body = document.createElement('div');
      body.style.cssText = 'padding:12px 16px;overflow:auto;';
      const pre = document.createElement('pre');
      pre.style.cssText = 'white-space:pre-wrap;word-wrap:break-word;font-size:12px;line-height:1.4;';
      pre.textContent = mergedView;
      body.appendChild(pre);
      modal.appendChild(head);
      modal.appendChild(body);
      overlay.appendChild(modal);
      document.body.appendChild(overlay);
    }
    window._aiChat = { sendMessage, appendMsg, openTlaContextViewer };
    window._aiChatSnapshot = () => collectFullSnapshot();
  } catch(e) {}
})();
