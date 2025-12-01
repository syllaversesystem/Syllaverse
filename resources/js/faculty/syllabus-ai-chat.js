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
      // Collect headings
      const headingTexts = Array.from(p.querySelectorAll('h1,h2,h3,h4,h5,h6,th'))
        .map(h => h.textContent.trim())
        .filter(Boolean);
      // Main text content (excluding excessive whitespace)
      let textBlock = (p.textContent || '').replace(/\s+/g,' ').trim();
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
    let full = snapshotParts.join('\n\n');
    const MAX_SNAPSHOT = 12000;
    if (full.length > MAX_SNAPSHOT) full = full.slice(0, MAX_SNAPSHOT) + '\n[Snapshot truncated]';
    try { console.debug('[AIChat][snapshot]', { length: full.length }); } catch(e) {}
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
    appendMsg('you', val);
    aiInput.value = '';
    autosizeTextarea(aiInput);
    const syllabusId = getSyllabusId();
    const endpoint = syllabusId ? `/faculty/syllabi/${syllabusId}/ai-chat` : null;
    if (!endpoint) { appendMsg('ai', 'AI service unavailable.'); return; }
    const loadingRow = appendLoading();
    const fd = new FormData(); fd.append('message', val);
    try {
      const snap = collectFullSnapshot();
      if (snap) fd.append('context', snap);
      // include prior conversation history (excluding this freshly added user message)
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
  // Predefined prompt: Assessment Method & Distribution Map
  function sendAssessmentMapPrompt(){
    const aiInput = document.getElementById('svAiChatInput');
    const preset = [
      'Validate the Assessment Method & Distribution Map.',
      'Output a compact Markdown table with columns: Code | Assessment Task | I/R/D | (%) | ILO1 | ILO2 | ILO3 | ILO4 | C | P | A.',
      'For each task, verify that ILO item totals equal C+P+A, and that all (%) weights sum to 100% overall (and reconcile lecture/lab subtotals if present).',
      'If any mismatch or gap exists, provide a Corrected Map table with balanced ILO coverage and appropriate domain totals.',
      'Add one-line rationales per task and finish with a Proposed Edits table referencing Code and exact column names changed.'
    ].join(' ');
    if (aiInput) {
      aiInput.value = preset;
      // trigger send
      sendMessage();
    }
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
  }
  document.addEventListener('DOMContentLoaded', init);
  // expose for debugging
  try { window._aiChat = { sendMessage, appendMsg, sendAssessmentMapPrompt }; } catch(e) {}
})();
