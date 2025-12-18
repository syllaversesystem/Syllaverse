(function(){
  'use strict';

  let _chatPanel = null;
  let _inFlight = false;

  /**
   * Initialize chat panel reference
   */
  function initChatPanel() {
    if (!_chatPanel) {
      _chatPanel = document.getElementById('aiChatMessages');
    }
    return _chatPanel;
  }

  /**
   * Append message to chat
   */
  function appendChatMessage(role, text, isLoading = false) {
    const chatPanel = initChatPanel();
    if (!chatPanel) {
      console.warn('[Assessment Schedule] Chat panel not available');
      return null;
    }

    const msgDiv = document.createElement('div');
    msgDiv.className = `ai-chat-msg ${role}${isLoading ? ' loading' : ''}`;

    const bubbleDiv = document.createElement('div');
    bubbleDiv.className = 'ai-chat-bubble';
    bubbleDiv.textContent = text;

    msgDiv.appendChild(bubbleDiv);
    chatPanel.appendChild(msgDiv);

    // Scroll to bottom
    chatPanel.scrollTop = chatPanel.scrollHeight;

    return msgDiv;
  }

  /**
   * Reload the assessment mapping table via AJAX
   */
  function reloadAssessmentMappingTable() {
    try {
      // Use the existing AJAX reload function from syllabus-assessment-mapping.js
      if (window.reloadAssessmentMappings && typeof window.reloadAssessmentMappings === 'function') {
        console.log('[Assessment Schedule] Reloading assessment mappings via AJAX');
        window.reloadAssessmentMappings();
      } else {
        console.warn('[Assessment Schedule] Global reloadAssessmentMappings function not available');
      }
    } catch (error) {
      console.error('[Assessment Schedule] Error reloading table:', error);
    }
  }

  /**
   * Get syllabus ID
   */
  function getSyllabusId() {
    const el = document.getElementById('syllabus-document');
    return el ? el.getAttribute('data-syllabus-id') : null;
  }

  /**
   * Collect context from syllabus
   */
  function collectContext() {
    const ctx = { sections: [] };
    const partials = document.querySelectorAll('.sv-partial');
    partials.forEach(p => {
      const key = p.getAttribute('data-partial-key');
      if (!key || key === 'status') return;
      let text = (p.textContent || '').replace(/\s+/g, ' ').trim();
      if (text.length > 800) text = text.slice(0, 800) + '...';
      if (text) ctx.sections.push({ key, text });
    });
    const courseTitle = document.querySelector('[name="course_title"]')?.value || '';
    const courseCode = document.querySelector('[name="course_code"]')?.value || '';
    if (courseTitle) ctx.courseTitle = courseTitle;
    if (courseCode) ctx.courseCode = courseCode;
    return JSON.stringify(ctx);
  }

  /**
   * Collect all snapshots
   */
  function collectAllSnapshots() {
    if (!window.SVSnapshot || typeof window.SVSnapshot.collectAllSnapshots !== 'function') {
      return '{}';
    }
    try {
      const snapshots = window.SVSnapshot.collectAllSnapshots();
      return JSON.stringify(snapshots);
    } catch (e) {
      console.warn('[Assessment Schedule] Failed to collect snapshots', e);
      return '{}';
    }
  }

  /**
   * Collect all prompts
   */
  function collectAllPrompts() {
    if (!window.SVPrompts || typeof window.SVPrompts.getAll !== 'function') {
      return '{}';
    }
    try {
      const allPrompts = window.SVPrompts.getAll();
      return JSON.stringify(allPrompts);
    } catch (e) {
      console.warn('[Assessment Schedule] Failed to collect all prompts', e);
      return '{}';
    }
  }

  /**
   * Parse JSON output from AI response
   * Expected format: { weeks: [...], mappings: [...] }
   */
  function parseAssessmentScheduleJson(responseText) {
    try {
      if (!responseText || typeof responseText !== 'string') {
        throw new Error(`Invalid input: expected string, got ${typeof responseText}`);
      }

      console.log('[Assessment Schedule] Attempting to parse response:', responseText.substring(0, 200));

      // Extract JSON from markdown code block if present
      let jsonText = responseText;
      
      // Try to find ```json...``` or ```...``` blocks
      const jsonBlockMatch = responseText.match(/```(?:json)?\s*([\s\S]*?)```/);
      if (jsonBlockMatch) {
        jsonText = jsonBlockMatch[1].trim();
        console.log('[Assessment Schedule] Extracted from code block');
      } else {
        // If no code block, try to find raw JSON starting with { or [
        const rawJsonMatch = responseText.match(/(\{[\s\S]*\})/);
        if (rawJsonMatch) {
          jsonText = rawJsonMatch[1].trim();
          console.log('[Assessment Schedule] Extracted raw JSON');
        }
      }

      if (!jsonText || jsonText.length === 0) {
        throw new Error('No JSON content found in response');
      }

      console.log('[Assessment Schedule] JSON text to parse:', jsonText.substring(0, 200));

      const parsed = JSON.parse(jsonText);

      // Validate structure
      if (!Array.isArray(parsed.weeks) || !Array.isArray(parsed.mappings)) {
        throw new Error('Invalid JSON structure: missing weeks or mappings array');
      }

      // Validate weeks array - can have fewer entries if ranges are preserved (e.g., "2-3", "12-14")
      // But should represent coverage of all 18 weeks via ranges or individual weeks
      if (parsed.weeks.length === 0) {
        throw new Error('Weeks array is empty');
      }

      console.log(`[Assessment Schedule] Weeks array has ${parsed.weeks.length} entries: ${JSON.stringify(parsed.weeks)}`);

      // Validate mappings structure
      parsed.mappings.forEach((mapping, idx) => {
        if (!mapping.name || typeof mapping.name !== 'string') {
          throw new Error(`Mapping ${idx}: missing or invalid name`);
        }
        if (!mapping.week_marks || typeof mapping.week_marks !== 'object') {
          throw new Error(`Mapping ${idx}: missing or invalid week_marks object`);
        }
        // Verify all weeks are present in week_marks
        for (const week of parsed.weeks) {
          if (!(week in mapping.week_marks)) {
            throw new Error(`Mapping ${idx}: missing week_marks entry for week "${week}"`);
          }
        }
      });

      console.log('[Assessment Schedule] Parsed JSON successfully', parsed);
      return parsed;

    } catch (error) {
      console.error('[Assessment Schedule] JSON parsing error:', error);
      console.error('[Assessment Schedule] Response text:', responseText);
      throw new Error(`Failed to parse assessment schedule JSON: ${error.message}`);
    }
  }

  /**
   * Save assessment schedule mappings to database
   */
  async function saveAssessmentScheduleMappings(mappings) {
    try {
      const syllabusId = getSyllabusId();
      if (!syllabusId) {
        throw new Error('Syllabus ID not found');
      }

      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

      const response = await fetch(`/faculty/syllabi/${syllabusId}/assessment-mappings`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ mappings })
      });

      if (!response.ok) {
        const error = await response.json().catch(() => ({}));
        throw new Error(error.message || `HTTP ${response.status}`);
      }

      const data = await response.json();

      if (!data.success) {
        throw new Error(data.message || 'Failed to save assessment mappings');
      }

      console.log('[Assessment Schedule] Saved mappings to database', data);
      return data;

    } catch (error) {
      console.error('[Assessment Schedule] Save error:', error);
      throw error;
    }
  }

  /**
   * Generate assessment schedule mapping via AI
   */
  async function generateAssessmentScheduleMapping() {
    try {
      const syllabusId = getSyllabusId();
      if (!syllabusId) {
        throw new Error('Syllabus ID not found');
      }

      // Check if SVAI module is loaded
      if (!window.SVAI || typeof window.SVAI.send !== 'function') {
        throw new Error('AI module not loaded');
      }

      // Show loading state
      const loadingMsg = appendChatMessage('ai', 'Processing assessment schedule...', true);

      // Build the message for AI
      const message = 'Generate an assessment schedule mapping. Output ONLY valid JSON in a ```json``` code block with the following structure: { "weeks": ["1","2",...,"18"], "mappings": [{ "name": "task name", "week_marks": { "1": "", "2": "x", ... }, "position": 0 }, ...] }. Mark weeks with "x" where the task appears and "" for empty weeks.';

      // Send via SVAI module (uses AI controller)
      let response;
      try {
        response = await window.SVAI.send(message, []);
      } catch (aiError) {
        throw new Error(`AI request failed: ${aiError.message}`);
      }

      if (!response) {
        throw new Error('No response from AI');
      }

      // Parse the AI response to extract JSON
      let parsedData;
      try {
        console.log('[Assessment Schedule] Response from AI:', response.substring(0, 500));
        parsedData = parseAssessmentScheduleJson(response);
      } catch (parseError) {
        throw new Error(`Failed to parse AI response: ${parseError.message}`);
      }

      // Save to database
      try {
        await saveAssessmentScheduleMappings(parsedData.mappings);
      } catch (saveError) {
        throw new Error(`Failed to save mappings: ${saveError.message}`);
      }

      // Remove loading message
      if (loadingMsg) {
        loadingMsg.remove();
      }

      // Show completion message in chat
      appendChatMessage('ai', 'Mapping Assessment Schedule Done');

      console.log('[Assessment Schedule] Mapping generated and saved successfully');

      // Reload the assessment mapping table via AJAX to show updated data immediately
      reloadAssessmentMappingTable();

      return true;

    } catch (error) {
      console.error('[Assessment Schedule] Error:', error);

      // Show error message in chat
      appendChatMessage('ai', `Error: ${error.message || 'Failed to generate assessment schedule mapping'}`);

      return false;
    }
  }

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

  function escapeHtml(s){ return String(s||'').replace(/[&<>]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[c])); }

  function openModal(inputText, replyText){
    _overlay = document.createElement('div');
    _overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:9999;display:flex;align-items:center;justify-content:center;';
    const modal = document.createElement('div');
    modal.style.cssText = 'width:70%;max-width:800px;max-height:70%;background:#fff;border-radius:8px;box-shadow:0 8px 32px rgba(0,0,0,0.25);display:flex;flex-direction:column;';
    const head = document.createElement('div');
    head.style.cssText = 'padding:12px 16px;border-bottom:1px solid #e5e5e5;display:flex;gap:8px;align-items:center;font-weight:600;';
    head.textContent = 'AI Preview — Assessment Schedule';
    const closeBtn = document.createElement('button');
    closeBtn.textContent = 'Close';
    closeBtn.style.cssText = 'margin-left:auto;padding:6px 10px;border:1px solid #ccc;background:#f7f7f7;border-radius:6px;cursor:pointer;';
    closeBtn.addEventListener('click', () => closeModal());
    head.appendChild(closeBtn);
    const body = document.createElement('div');
    body.style.cssText = 'padding:12px 16px;overflow:auto;';
    const inputBlock = document.createElement('div');
    inputBlock.style.cssText = 'margin-bottom:16px;padding:10px;border:1px solid #e6e9ed;border-radius:8px;background:#fcfcfc;';
    const inputTitle = document.createElement('div'); inputTitle.textContent = 'Input'; inputTitle.style.cssText = 'font-weight:600;margin-bottom:8px;color:#111827;';
    const inputContent = document.createElement('div'); inputContent.style.cssText = 'font-size:.9rem;line-height:1.45;color:#111827;';
    inputContent.innerHTML = '<pre style="white-space:pre-wrap;margin:0">'+escapeHtml(inputText || 'No input.')+'</pre>';
    inputBlock.appendChild(inputTitle); inputBlock.appendChild(inputContent);

    const outputBlock = document.createElement('div');
    outputBlock.style.cssText = 'margin-bottom:0;padding:10px;border:1px solid #e6e9ed;border-radius:8px;background:#fcfcfc;';
    const outputTitle = document.createElement('div'); outputTitle.textContent = 'AI Output'; outputTitle.style.cssText = 'font-weight:600;margin-bottom:8px;color:#111827;';
    const outputContent = document.createElement('div'); outputContent.style.cssText = 'font-size:.9rem;line-height:1.45;color:#111827;';
    outputContent.innerHTML = '<pre style="white-space:pre-wrap;margin:0">'+escapeHtml(replyText || 'No reply.')+'</pre>';
    outputBlock.appendChild(outputTitle); outputBlock.appendChild(outputContent);

    body.appendChild(inputBlock);
    body.appendChild(outputBlock);
    modal.appendChild(head);
    modal.appendChild(body);
    _overlay.appendChild(modal);
    document.body.appendChild(_overlay);
    const escHandler = (e) => { if (e.key === 'Escape') closeModal(); };
    document.addEventListener('keydown', escHandler, { once: true });
  }
  function closeModal(){ if (_overlay) { _overlay.remove(); _overlay = null; } }

  // Build TLA snapshot from current table inputs
  function collectTlaSnapshot(){
    try {
      const table = document.getElementById('tlaTable');
      if (!table) return 'No TLA table found.';
      const tbody = table.querySelector('tbody');
      if (!tbody) return 'No TLA body found.';
      const rows = Array.from(tbody.querySelectorAll('tr:not(#tla-placeholder)'));
      const lines = [];
      lines.push('Partial: Teaching, Learning, and Assessment (TLA) Activities');
      lines.push('| Ch. | Topics / Reading List | Wks. | Topic Outcomes | ILO | SO | Delivery Method |');
      lines.push('|:---:|:----------------------|:----:|:---------------|:---:|:--:|:-----------------|');
      const dash = (v) => { const s = String(v||'').trim(); return s ? s : '-'; };
      rows.forEach(row => {
        const ch = dash(row.querySelector('[name*="[ch]"]')?.value);
        const topic = dash(row.querySelector('[name*="[topic]"]')?.value);
        const wks = dash(row.querySelector('[name*="[wks]"]')?.value);
        const outcomes = dash(row.querySelector('[name*="[outcomes]"]')?.value);
        const ilo = dash(row.querySelector('[name*="[ilo]"]')?.value);
        const so = dash(row.querySelector('[name*="[so]"]')?.value);
        const delivery = dash(row.querySelector('[name*="[delivery]"]')?.value);
        lines.push(`| ${ch} | ${topic.replace(/\r?\n/g,' \\n ')} | ${wks} | ${outcomes.replace(/\r?\n/g,' \\n ')} | ${ilo} | ${so} | ${delivery.replace(/\r?\n/g,' \\n ')} |`);
      });
      return lines.join('\n');
    } catch(e) { return 'TLA snapshot unavailable.'; }
  }

  // Build Assessment Mapping snapshot (Distribution + Week marks)
  function collectAssessmentMappingSnapshot(){
    try {
      const root = document.querySelector('.assessment-mapping');
      if (!root) return 'No Assessment Mapping found.';
      const distTable = root.querySelector('table.distribution');
      const weekTable = root.querySelector('table.week');
      if (!distTable || !weekTable) return 'Assessment Mapping tables missing.';
      const distRows = Array.from(distTable.querySelectorAll('tr:not(:first-child)'));
      const weekRows = Array.from(weekTable.querySelectorAll('tr:not(:first-child)'));
      const weekHeaders = Array.from(weekTable.querySelectorAll('tr:first-child th.week-number')).map(th => (th.textContent||'').trim());
      // Build header line
      const lines = [];
      lines.push('Partial: Assessment Schedule');
      const header = ['Distribution'].concat(weekHeaders.length ? weekHeaders : ['No weeks']);
      lines.push('| ' + header.join(' | ') + ' |');
      lines.push('| ' + header.map(()=>':---:').join(' | ') + ' |');
      // Rows
      distRows.forEach((dr, idx) => {
        const name = (dr.querySelector('input.distribution-input')?.value || '').trim();
        const wr = weekRows[idx];
        const cells = wr ? Array.from(wr.querySelectorAll('td.week-mapping')) : [];
        const marks = cells.map(c => {
          const txt = (c.textContent||'').trim();
          const marked = txt.toLowerCase() === 'x' || c.classList.contains('marked') || c.getAttribute('data-mark') === 'x' || /x/i.test(c.innerHTML);
          return marked ? 'x' : '';
        });
        const rowCells = [name || '-'].concat(marks.length ? marks : ['' ]);
        lines.push('| ' + rowCells.join(' | ') + ' |');
      });
      // If no data rows, emit a placeholder row
      if (distRows.length === 0) {
        const placeholder = ['-'].concat(weekHeaders.length ? weekHeaders.map(()=> '') : ['']);
        lines.push('| ' + placeholder.join(' | ') + ' |');
      }
      return lines.join('\n');
    } catch(e) { return 'Assessment Mapping snapshot unavailable.'; }
  }

  async function sendSmallPrompt(){
    if (_inFlight) return;
    _inFlight = true;
    setProgress('Preparing', 5, 'Preparing prompt…', 'state-running');
    try {
      const tlaSnapshot = collectTlaSnapshot();
      const amSnapshot = collectAssessmentMappingSnapshot();
      // Build concise instructions for mapping Assessment Schedule with 'x'
      const instructions = [
        'We are mapping the Assessment Schedule using x marks.',
        'Step 1: List ALL existing tasks in the Distribution column and output them as a simple list (one per line). Include every current task shown in the Distribution rows (do not omit any).',
        'If no tasks are present, output: "no distribution tasks".',
        'Step 2: Read the TLA table. Find tasks in the "Topics / Reading List" column and read their aligned weeks from the "Wks." column. If a single TLA row contains multiple tasks, separate them and list each task individually. Output each task with its week number(s) aligned to it as lines: "<task> — <weeks>". If the week value is a range (e.g., 1-2), do NOT separate or expand it; keep the range text as-is. If multiple weeks are comma-separated, keep them comma-separated without expanding ranges.',
        'Output format requirements (fixed structure):',
        'Step 1 Output:\n| Task |\n|:-----|\n| <task 1> |\n| <task 2> |\n... (each Distribution task on its own row). If none: no distribution tasks',
        'Step 2 Output:\n| Task | Weeks |\n|:-----|:------|\n| <task A> | <weeks> |\n| <task B> | <weeks> |\n... (each parsed task from TLA on its own row; preserve ranges and commas exactly)',
        'Step 3: Produce the updated Assessment Schedule snapshot by applying the new mapping from Step 2 onto the Assessment Schedule table. For each Distribution task, mark x under the week columns whose labels match the task\'s weeks from Step 2. Do not separate or expand ranged week labels (e.g., 1-2 stays 1-2). Keep comma-separated week lists intact. If a task\'s week does not exist among the Assessment Schedule headers, skip that week.',
        'Step 3 Output (fixed structure: full snapshot table):\n| Distribution | <Week headers from Assessment Schedule> |\n|:------------:|:--------------------------------------:|\n| <task 1> | x | ... |\n| <task 2> |   | ... |\n... (exactly mirror the Assessment Schedule snapshot from input: first column is Distribution task names, subsequent columns are the original week headers in the same order, with cells marked x only where aligned; preserve week header text exactly, including ranges)',
        
        'Step 4: Return ONLY this JSON (in ```json``` fenced block), no commentary: { "weeks": ["<Week1>", "<Week2>", ...], "tasks": [{ "name": "<Distribution task>", "marks": { "<Week1>": "x" | "", "<Week2>": "x" | "" } }] }. Requirements: preserve week labels exactly (incl. ranges like "1-2" and commas), include only headers’ weeks, marks must be "x" or "" only, no extra fields.'
      ].join(' ');
      // Determine syllabus ID from URL like /faculty/syllabi/{id}
      const m = (location.pathname||'').match(/\/faculty\/syllabi\/(\d+)/);
      const syllabusId = m ? m[1] : null;
      if (!syllabusId) { setProgress('Error', 100, 'Syllabus ID not found.', 'state-warn'); _inFlight = false; return; }
      const fd = new FormData();
      fd.append('message', instructions + '\n\n' + tlaSnapshot + '\n\n' + amSnapshot);
      fd.append('context_phase3', '1');
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      setProgress('Sending', 35, 'Contacting AI…', 'state-running');
      const res = await fetch(`/faculty/syllabi/${syllabusId}/ai-chat`, { method: 'POST', headers: token ? { 'X-CSRF-TOKEN': token } : {}, body: fd });
      setProgress('Waiting', 65, 'Awaiting response…', 'state-running');
      if (!res.ok) { setProgress('Error', 100, 'AI request failed.', 'state-warn'); _inFlight = false; return; }
      const data = await res.json().catch(() => ({}));
      let msg = data?.message || data?.reply || data?.response || 'No output.';
      // Try auto-parsing a JSON object from the AI output and persist
      try {
        setProgress('Parsing', 80, 'Parsing JSON for save…', 'state-running');
        // Extract only JSON fenced block to avoid mixing with markdown text
        let jsonText = '';
        if (typeof msg === 'string') {
          const codeBlockMatch = msg.match(/```json\s*([\s\S]*?)```/i) || msg.match(/```\s*([\s\S]*?)```/i);
          if (codeBlockMatch) {
            jsonText = codeBlockMatch[1].trim();
          }
        }
        if (jsonText) {
          // Sanitize common issues: smart quotes, trailing commas
          const sanitized = jsonText
            .replace(/[“”]/g, '"')
            .replace(/[‘’]/g, "'")
            .replace(/,\s*([}\]])/g, '$1');
          // Prefer strict JSON; if it fails, surface the error
          await window.parseAssessmentJsonAndSave(sanitized);
          const saveRes = await window.parseAssessmentJsonAndSave(jsonText);
          setProgress('Saved', 100, `Saved ${saveRes?.mappings?.length || 0} mappings.`, 'state-ok');
          try { if (typeof window.reloadAssessmentMappings === 'function') window.reloadAssessmentMappings(); } catch(e) {}
          // Optional: log success for debugging without showing modal
          try { console.log('[AssessmentSchedule] Saved mappings:', saveRes?.mappings?.length || 0); } catch(e) {}
        } else {
          setProgress('Done', 100, 'AI response received (no JSON found).', 'state-ok');
        }
      } catch (saveErr) {
        setProgress('Error', 100, 'Failed to save JSON output.', 'state-warn');
        try { console.warn('[AssessmentSchedule] Save failed:', saveErr?.message || saveErr); } catch(e) {}
      }
      // Do not show modal in AI flow; rely on progress bar + table reload
    } catch(e){
      setProgress('Error', 100, 'Unexpected error.', 'state-warn');
    } finally {
      _inFlight = false;
    }
  }

  function init(){
    document.addEventListener('DOMContentLoaded', function(){
      try {
        // Export the main function to window for access by chat panel
        window.SVAssessmentSchedule = {
          generate: generateAssessmentScheduleMapping
        };
        
        console.log('[Assessment Schedule] Module initialized with new chat-based approach');
      } catch(e) {
        console.error('[Assessment Schedule] Initialization error:', e);
      }
    });
  }

  if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }

  // Parse AI Step 4 JSON and persist to backend
  // Schema: { weeks: ["W1", "W2", ...], tasks: [{ name: "Quiz", marks: { "W1": "x"|"", ... } }, ...] }
  // Saves via: POST /faculty/syllabi/{id}/assessment-mappings
  window.parseAssessmentJsonAndSave = async function(jsonText){
    if (!jsonText || typeof jsonText !== 'string') throw new Error('jsonText must be a string');
    let payload;
    try { payload = JSON.parse(jsonText); } catch(e){ throw new Error('Invalid JSON: ' + e.message); }
    const weeks = Array.isArray(payload?.weeks) ? payload.weeks : null;
    const tasks = Array.isArray(payload?.tasks) ? payload.tasks : null;
    if (!weeks || !weeks.length) throw new Error('JSON missing weeks array');
    if (!tasks || !tasks.length) throw new Error('JSON missing tasks array');

    const mappings = tasks.map((t, idx) => {
      const name = (t?.name ?? '').toString();
      const marksObj = t?.marks && typeof t.marks === 'object' ? t.marks : {};
      // Preserve week labels exactly; build an object keyed by labels
      const week_marks = {};
      weeks.forEach(w => {
        const raw = marksObj[w];
        week_marks[w] = raw && String(raw).trim().toLowerCase() === 'x' ? 'x' : '';
      });
      return { name, week_marks, position: idx };
    });

    // Prefer DOM data attribute if available, fallback to URL
    const domSyllabusId = document.querySelector('[data-syllabus-id]')?.dataset?.syllabusId || null;
    const m = (location.pathname||'').match(/\/faculty\/syllabi\/(\d+)/);
    const syllabusId = domSyllabusId || (m ? m[1] : null);
    if (!syllabusId) throw new Error('Syllabus ID not found (data-syllabus-id or URL)');

    const url = `/faculty/syllabi/${encodeURIComponent(syllabusId)}/assessment-mappings`;
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(token ? { 'X-CSRF-TOKEN': token } : {}),
      },
      body: JSON.stringify({ mappings }),
      credentials: 'same-origin',
    });
    let data;
    try { data = await res.json(); }
    catch(e) {
      const text = await res.text().catch(() => '');
      throw new Error(`Save failed (non-JSON response). HTTP ${res.status}. ${text}`);
    }
    if (!res.ok || !data?.success) {
      const msg = data?.message || `HTTP ${res.status}`;
      // Surface validation errors if present
      if (data?.errors) {
        const details = Object.entries(data.errors).map(([k,v]) => `${k}: ${Array.isArray(v)?v.join('; '):v}`).join(' | ');
        throw new Error('Save assessment mappings failed: ' + msg + ' — ' + details);
      }
      throw new Error('Save assessment mappings failed: ' + msg);
    }
    return data;
  };
})();
