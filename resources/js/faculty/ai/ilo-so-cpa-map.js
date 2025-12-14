/*
	Faculty AI: ILO–SO–CPA Mapping Modal (Assessment-style UI)
	- Opens on Shift+2 (or '@')
	- Displays Input Snapshot and AI Output in stacked blocks
	- Header with title + Close button; Esc to close
*/

(function(){
	let _lastInput = null;
	let _lastOutput = null;
	let _overlay = null;
	let _inFlight = false;
	let _parsedMapping = null; // holds JSON from Step 4 transformed for saving

	// Progress bar controls (matching Assessment Mapping IDs)
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

	function escapeHtml(s){
		return String(s||'').replace(/[&<>]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[c]));
	}
	function safeStringify(obj){
		try { return typeof obj === 'string' ? obj : JSON.stringify(obj, null, 2); }
		catch(e){ return String(obj); }
	}
/* Snapshot and payload logic removed per request */

	// Build Input Snapshot including Assessment Tasks Distribution (ADM)
	function collectInputSnapshot(){
		const atPayload = collectAssessmentTasksPayload();
		const tlaPayload = collectTlaPayload();
		const iloSoCpaPayload = collectIloSoCpaPayload();
		const parts = [];
		if (atPayload) {
			parts.push('Partial: Assessment Tasks Distribution');
			// Human-readable summary for AI friendliness
			parts.push(formatAssessmentTasksForAI(atPayload));
		}
		if (tlaPayload) {
			parts.push('\n--------------------------------------------------------------------------');
			parts.push('Partial: Teaching, Learning, and Assessment (TLA) Activities');
			parts.push(formatTlaForAI(tlaPayload));
		}
		if (iloSoCpaPayload) {
			parts.push('\n--------------------------------------------------------------------------');
			parts.push('Partial: ILO–SO and ILO–CPA Mapping');
			parts.push(formatIloSoCpaForAI(iloSoCpaPayload));
		}
		return parts.length ? parts.join('\n') : 'No snapshot available yet.';
	}

	// Render ADM payload into concise lines the AI can easily recite
	function formatAssessmentTasksForAI(payload){
		try {
			const lines = [];
			const sections = Array.isArray(payload?.sections) ? payload.sections : [];
			const markNewlines = (s) => String(s || '').replace(/\r?\n/g, ' \\n ').trim();
			const makeRow = (cells) => `| ${cells.map(c => markNewlines(c ?? '')).join(' | ')} |`;
			const sep = (colCount) => `+${Array(colCount).fill('-').map(()=>'-'.repeat(3)).join('+')}+`;
			sections.forEach((sec, idx) => {
				const sNum = sec.section_num ?? (idx+1);
				const main = sec.main_row || {};
				const mainIlo = Array.isArray(sec.main_ilo_columns) ? sec.main_ilo_columns : [];
				lines.push(`Section ${sNum}`);
				// Header row (text-based table)
				const header = ['Code','Task','I/R/D','%','ILO(1..N)','C','P','A'];
				lines.push(makeRow(header));
				// Main row (no I/R/D or CPA values on main)
				lines.push(makeRow([
					main.code||'',
					main.task||'',
					'',
					main.percent ?? '',
					`[${mainIlo.map(v=>markNewlines(v||'')).join(', ')}]`,
					'', '', ''
				]));
				// Sub rows
				const subs = Array.isArray(sec.sub_rows) ? sec.sub_rows : [];
				subs.forEach((sr) => {
					const cpa = Array.isArray(sr.cpa_columns) ? sr.cpa_columns : [null,null,null];
					const iloCols = Array.isArray(sr.ilo_columns) ? sr.ilo_columns : [];
					lines.push(makeRow([
						sr.code||'',
						sr.task||'',
						sr.ird||'',
						sr.percent ?? '',
						`[${iloCols.map(v=>markNewlines(v||'')).join(', ')}]`,
						cpa[0] ?? '',
						cpa[1] ?? '',
						cpa[2] ?? ''
					]));
				});
			});
			return lines.join('\n');
		} catch(e){
			return 'ADM summary unavailable.';
		}
	}

	// Serialize TLA table from DOM into a compact payload
	function collectTlaPayload(){
		try {
			const table = document.getElementById('tlaTable');
			if (!table) return null;
			const tbody = table.querySelector('tbody');
			if (!tbody) return null;
			const rows = Array.from(tbody.querySelectorAll('tr:not(#tla-placeholder)'));
			const items = rows.map((row, index) => {
				const get = (sel) => {
					const el = row.querySelector(sel);
					if (!el) return '';
					const v = (el.value ?? '').toString().trim();
					return v || '';
				};
				return {
					ch: get('[name*="[ch]"]'),
					topic: get('[name*="[topic]"]'),
					wks: get('[name*="[wks]"]'),
					outcomes: get('[name*="[outcomes]"]'),
					ilo: get('[name*="[ilo]"]'),
					so: get('[name*="[so]"]'),
					delivery: get('[name*="[delivery]"]'),
					position: index
				};
			}).filter(item => Object.values(item).some(v => String(v).trim() !== ''));
			return { items };
		} catch(e){ return null; }
	}

	// Format TLA payload as a text-based table, mirroring UI columns
	function formatTlaForAI(payload){
		try {
			const items = Array.isArray(payload?.items) ? payload.items : [];
			const rows = [];
			rows.push('| Ch. | Topics / Reading List | Wks. | Topic Outcomes | ILO | SO | Delivery Method |');
			rows.push('|:---:|:----------------------|:----:|:---------------|:---:|:--:|:-----------------|');
			const markNewlines = (s) => {
				const t = String(s || '').replace(/\r?\n/g, ' \\n ');
				return t.trim();
			};
			items.forEach(it => {
				const dash = (v) => { const s = markNewlines(v); return s ? s : '-'; };
				rows.push(`| ${dash(it.ch)} | ${dash(it.topic)} | ${dash(it.wks)} | ${dash(it.outcomes)} | ${dash(it.ilo)} | ${dash(it.so)} | ${dash(it.delivery)} |`);
			});
			return rows.join('\n');
		} catch(e){
			return 'TLA summary unavailable.';
		}
	}
	function collectOutputSnapshot(){ return 'Output snapshot disabled.'; }

	// Serialize Assessment Tasks Distribution (ADM) from the syllabus table
	function collectAssessmentTasksPayload(){
		const table = document.querySelector('.at-map-outer .cis-table');
		if (!table) return null;
		const tbody = table.querySelector('#at-tbody');
		if (!tbody) return null;

		const cleanText = (value) => (value || '').toString().trim();
		const numericOrNull = (value) => {
			const num = parseFloat((value || '').toString().replace(/[^0-9.\-]/g, ''));
			return Number.isFinite(num) ? num : null;
		};

		const sections = [];
		const allMainRows = tbody.querySelectorAll('.at-main-row');

		allMainRows.forEach((mainRow) => {
			const sectionNum = mainRow.dataset.section;
			const mainCells = Array.from(mainRow.children);

			const mainRowData = {
				code: cleanText(mainCells[0]?.querySelector('textarea')?.value),
				task: cleanText(mainCells[1]?.querySelector('textarea')?.value),
				percent: numericOrNull(mainCells[3]?.querySelector('textarea.percent-input')?.value),
			};

			const mainIloColumns = [];
			const totalCols = mainCells.length;
			const iloStartIdx = 4;
			const iloEndIdx = totalCols - 3;
			for (let i = iloStartIdx; i < iloEndIdx; i++) {
				const val = mainCells[i]?.querySelector('textarea')?.value || '';
				mainIloColumns.push(val);
			}

			const subRows = [];
			const allSubRows = tbody.querySelectorAll(`.at-sub-row[data-section="${sectionNum}"]`);
			allSubRows.forEach((subRow) => {
				const subCells = Array.from(subRow.children);

				const iloColumns = [];
				for (let i = iloStartIdx; i < iloEndIdx; i++) {
					const val = subCells[i]?.querySelector('textarea')?.value || '';
					iloColumns.push(val);
				}

				const cTextarea = subCells[totalCols - 3]?.querySelector('textarea');
				const pTextarea = subCells[totalCols - 2]?.querySelector('textarea');
				const aTextarea = subCells[totalCols - 1]?.querySelector('textarea');
				const cValue = cTextarea ? (cTextarea.value || '').trim() : '';
				const pValue = pTextarea ? (pTextarea.value || '').trim() : '';
				const aValue = aTextarea ? (aTextarea.value || '').trim() : '';
				const parseIntSafe = (val) => { if (!val) return null; const parsed = parseInt(val, 10); return isNaN(parsed) ? null : parsed; };
				const cpaColumns = [ parseIntSafe(cValue), parseIntSafe(pValue), parseIntSafe(aValue) ];

				subRows.push({
					code: cleanText(subCells[0]?.querySelector('textarea')?.value),
					task: cleanText(subCells[1]?.querySelector('textarea')?.value),
					ird: cleanText(subCells[2]?.querySelector('textarea')?.value),
					percent: numericOrNull(subCells[3]?.querySelector('textarea.percent-input')?.value),
					ilo_columns: iloColumns,
					cpa_columns: cpaColumns,
				});
			});

			sections.push({
				section_num: sectionNum ? parseInt(sectionNum, 10) : null,
				section_label: mainRowData.task || null,
				main_row: mainRowData,
				main_ilo_columns: mainIloColumns,
				sub_rows: subRows,
			});
		});

		return { sections };
	}

	/* ILO payload removed */

	/* SO payload removed */

	// Serialize ILO–SO–CPA mapping table from DOM
	function collectIloSoCpaPayload(){
		try {
			const root = document.querySelector('.ilo-so-cpa-mapping');
			if (!root) return null;
			const table = root.querySelector('.mapping');
			if (!table) return null;
			const headerRows = table.querySelectorAll('tr');
			if (headerRows.length < 2) return null;
			const headerRow2 = headerRows[1];
			const ths = Array.from(headerRow2.querySelectorAll('th'));
			if (ths.length === 0) return null;
			// Identify indices: first cell after ILOs until before C are SOs
			const iloIdx = ths.findIndex(th => (th.textContent||'').includes('ILOs'));
			const cIdx = ths.findIndex(th => (th.textContent||'').trim() === 'C');
			const pIdx = ths.findIndex(th => (th.textContent||'').trim() === 'P');
			const aIdx = ths.findIndex(th => (th.textContent||'').trim() === 'A');
			// SO headers lie between iloIdx+1 and cIdx-1
			const soHeaders = ths.slice((iloIdx>=0?iloIdx:0)+1, cIdx>=0?cIdx:ths.length);
			const soLabels = soHeaders.map(th => {
				const inp = th.querySelector('input');
				const raw = inp ? inp.value.trim() : (th.textContent||'').trim();
				return raw || '-';
			});
			// Body rows (may be in TBODY or direct)
			const tbody = table.querySelector('tbody') || table;
			const dataRows = Array.from(tbody.querySelectorAll('tr')).filter(r => r.querySelector('td'));
			const rows = [];
			dataRows.forEach(r => {
				const tds = Array.from(r.querySelectorAll('td'));
				if (tds.length === 0) return;
				// ILO label (input or text)
				const iloInput = tds[0].querySelector('input');
				const iloText = iloInput ? iloInput.value.trim() : (tds[0].textContent||'').trim();
				if (!iloText) return; // skip empty ILO rows unless placeholder
				const isPlaceholder = iloText === 'No ILO';
				// SO cell values correspond to next N cells before C,P,A
				const cCell = tds[tds.length - 3];
				const pCell = tds[tds.length - 2];
				const aCell = tds[tds.length - 1];
				const readCell = (cell) => {
					if (!cell) return '';
					const ta = cell.querySelector('textarea');
					return (ta ? ta.value : cell.textContent || '').toString().trim();
				};
				const soValues = [];
				for (let i = 1; i < tds.length - 3; i++) {
					soValues.push(readCell(tds[i]));
				}
				rows.push({
					ilo: iloText,
					so: soValues,
					c: readCell(cCell),
					p: readCell(pCell),
					a: readCell(aCell),
					placeholder: isPlaceholder
				});
			});
			if (!rows.length && !soLabels.length) return null;
			return { soLabels, rows };
		} catch(e){ return null; }
	}

	// Format ILO–SO–CPA mapping as a text-based table
	function formatIloSoCpaForAI(payload){
		try {
			const markNewlines = (s) => String(s || '').replace(/\r?\n/g, ' \\n ').trim();
			const soLabels = Array.isArray(payload?.soLabels) ? payload.soLabels.map(markNewlines) : [];
			const rows = Array.isArray(payload?.rows) ? payload.rows : [];
			const header = ['ILO'].concat(soLabels.length ? soLabels : ['SO']).concat(['C','P','A']);
			const out = [];
			const mk = (cells) => `| ${cells.map(v => { const t = markNewlines(v ?? ''); return (t || '-'); }).join(' | ')} |`;
			out.push(mk(header));
			out.push(mk(header.map(()=>':---:')));
			rows.forEach(r => {
				const soVals = (Array.isArray(r.so) ? r.so : []).map(v => (markNewlines(v||'') || '-'));
				// pad/truncate to header SO count
				const targetCount = header.length - 4; // excluding ILO and C/P/A
				const normSo = soVals.slice(0, targetCount);
				while (normSo.length < targetCount) normSo.push('-');
				out.push(mk([markNewlines(r.ilo) || '-', ...normSo, markNewlines(r.c) || '-', markNewlines(r.p) || '-', markNewlines(r.a) || '-']));
			});
			return out.join('\n');
		} catch(e){ return 'ILO–SO–CPA summary unavailable.'; }
	}

	/* TLA payload removed */

	function shouldIgnoreTarget(t){
		if (!t) return false;
		const tag = (t.tagName||'').toLowerCase();
		return tag === 'input' || tag === 'textarea' || !!t.isContentEditable;
	}

	function addBlock(container, title, html){
		const wrap = document.createElement('div');
		wrap.style.cssText = 'margin-bottom:16px;padding:10px;border:1px solid #e6e9ed;border-radius:8px;background:#fcfcfc;';
		const t = document.createElement('div'); t.textContent = title; t.style.cssText = 'font-weight:600;margin-bottom:8px;color:#111827;';
		const content = document.createElement('div'); content.style.cssText = 'font-size:.9rem;line-height:1.45;color:#111827;';
		content.innerHTML = html;
		wrap.appendChild(t); wrap.appendChild(content); container.appendChild(wrap);
	}

	// If modal is open, update the AI Output block in place
	function updateModalOutput(){
		if (!_overlay) return;
		try {
			const blocks = _overlay.querySelectorAll('div');
			// Find the AI Output block by its title text
			let contentDiv = null;
			blocks.forEach(div => {
				if (!contentDiv && div.textContent === 'AI Output') {
					// The next sibling contains the content wrapper inside addBlock
					const parent = div.parentElement;
					contentDiv = parent ? parent.querySelector('div:nth-child(2)') : null;
				}
			});
			if (contentDiv) {
				const out = _lastOutput || '';
				let formattedOut = '';
				if (!out) {
					formattedOut = '<div class="text-muted">No AI output yet.</div>';
				} else {
					try {
						if (typeof window.formatAIResponse === 'function') {
							formattedOut = window.formatAIResponse(out);
						} else {
							formattedOut = '<pre style="white-space:pre-wrap;margin:0">'+escapeHtml(out)+'</pre>';
						}
					} catch(e) {
						formattedOut = '<pre style="white-space:pre-wrap;margin:0">'+escapeHtml(out)+'</pre>';
					}
				}
				contentDiv.innerHTML = formattedOut;
			}
		} catch(e) {}
	}

	function openModal(){
		// Build overlay + modal using assessment-map style
		_overlay = document.createElement('div');
		_overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:9999;display:flex;align-items:center;justify-content:center;';
		const modal = document.createElement('div');
		modal.style.cssText = 'width:80%;max-width:960px;max-height:80%;background:#fff;border-radius:8px;box-shadow:0 8px 32px rgba(0,0,0,0.25);display:flex;flex-direction:column;';
		const head = document.createElement('div');
		head.style.cssText = 'padding:12px 16px;border-bottom:1px solid #e5e5e5;display:flex;gap:8px;align-items:center;font-weight:600;';
		head.textContent = 'AI Preview — ILO–SO–CPA Mapping';
		const closeBtn = document.createElement('button');
		closeBtn.textContent = 'Close';
		closeBtn.style.cssText = 'margin-left:auto;padding:6px 10px;border:1px solid #ccc;background:#f7f7f7;border-radius:6px;cursor:pointer;';
		closeBtn.addEventListener('click', () => closeModal());
		head.appendChild(closeBtn);
		const body = document.createElement('div');
		body.style.cssText = 'padding:12px 16px;overflow:auto;';

		// Input Snapshot block
		const inputText = safeStringify(_lastInput || '');
		const safeInput = escapeHtml(inputText);
		addBlock(body, 'Input Snapshot', safeInput ? '<pre style="white-space:pre-wrap;word-wrap:break-word;margin:0">'+safeInput+'</pre>' : '<div class="text-muted">No snapshot captured yet.</div>');

		// AI Output block (support optional formatter)
		const out = _lastOutput || '';
		let formattedOut = '';
		if (!out) {
			formattedOut = '<div class="text-muted">No AI output yet.</div>';
		} else {
			try {
				if (typeof window.formatAIResponse === 'function') {
					formattedOut = window.formatAIResponse(out);
				} else {
					formattedOut = '<pre style="white-space:pre-wrap;margin:0">'+escapeHtml(out)+'</pre>';
				}
			} catch(e) {
				formattedOut = '<pre style="white-space:pre-wrap;margin:0">'+escapeHtml(out)+'</pre>';
			}
		}
		addBlock(body, 'AI Output', formattedOut);

		const footer = document.createElement('div');
		footer.style.cssText = 'padding:10px 16px;border-top:1px solid #e5e5e5;display:flex;gap:8px;justify-content:flex-end;';

		// Insert Mapping button
		const insertBtn = document.createElement('button');
		insertBtn.textContent = 'Insert Mapping';
		insertBtn.style.cssText = 'padding:6px 12px;border:1px solid #1d4ed8;background:#2563eb;color:#fff;border-radius:6px;cursor:pointer;';
		insertBtn.addEventListener('click', async () => { await insertAiMapping(); });
		footer.appendChild(insertBtn);
		const closeBtn2 = document.createElement('button');
		closeBtn2.textContent = 'Close';
		closeBtn2.style.cssText = 'padding:6px 12px;border:1px solid #ccc;background:#f7f7f7;border-radius:6px;cursor:pointer;';
		closeBtn2.addEventListener('click', () => closeModal());
		footer.appendChild(closeBtn2);

		modal.appendChild(head);
		modal.appendChild(body);
		modal.appendChild(footer);
		_overlay.appendChild(modal);
		document.body.appendChild(_overlay);

		// Esc key to close
		const escHandler = (e) => { if (e.key === 'Escape') closeModal(); };
		document.addEventListener('keydown', escHandler, { once: true });
	}

	function closeModal(){ if (_overlay) { _overlay.remove(); _overlay = null; } }

	function onHotkey(e){
		const isCombo = (e.shiftKey && e.key === '2') || e.key === '@';
		if (!isCombo) return;
		if (shouldIgnoreTarget(e.target)) return;
		e.preventDefault();
		// Toggle behavior: if open, close; otherwise populate and open
		if (_overlay) { 
			closeModal();
			return;
		}
		// show progress briefly to match UX
		setProgress('Preparing', 5, 'Building snapshot…', 'state-running');
		try { _lastInput = collectInputSnapshot(); } catch(e) { _lastInput = 'No snapshot available yet.'; }
		// Initialize output placeholder; actual AI response will replace it
		try { _lastOutput = ''; } catch(e) { _lastOutput = ''; }
		setProgress('Preview', 35, 'Opening modal…', 'state-running');
		openModal();
		// Immediately send snapshots to AI so the modal shows live output
		setProgress('Sending', 40, 'Contacting AI…', 'state-running');
		try { sendAllSnapshotsToAI(); } catch(e) { setProgress('Idle', 100, 'Ready.', 'state-warn'); }
		setProgress('Idle', 100, 'Ready.', 'state-ok');
	}

	function exposeAPI(){
		window.FacultyAIMapModal = {
			open: () => openModal(),
			close: () => closeModal(),
			setInputSnapshot: (data) => { _lastInput = data; },
			setOutputSnapshot: (data) => { _lastOutput = data; },
			callAi: async () => { await callAiForIloSoCpa(); },
			sendAll: async () => { await sendAllSnapshotsToAI(); },
			parseAndInsert: async () => { await insertAiMapping(); }
		};
	}

	// Unified insert function: parse AI JSON, save via AJAX, refresh partial
	async function insertAiMapping(){
		setProgress('Parsing', 60, 'Parsing AI JSON…', 'state-running');
		let parsed = null;
		try { parsed = parseAiJsonToMappings(_lastOutput); } catch(e) { /* validation removed: skip on parse error */ }
		if (!parsed) { setProgress('Idle', 100, 'No AI JSON to insert.', 'state-warn'); return; }
		_parsedMapping = parsed;
		setProgress('Saving', 75, 'Saving mapping via AJAX…', 'state-running');
		try { await saveIloSoCpaAjax(parsed); } catch(e) { /* validation removed: skip failure */ setProgress('Idle', 100, 'Save skipped.', 'state-warn'); return; }
		setProgress('Saved', 90, 'Mapping saved. Refreshing…', 'state-ok');
		// Ajax refresh: fetch latest syllabus page and rebuild partial from its data attributes
		try {
			await ajaxRefreshIloSoCpaPartial();
			setProgress('Done', 100, 'Partial refreshed.', 'state-ok');
		} catch(e){ setProgress('Idle', 100, 'Partial refresh skipped.', 'state-warn'); }
	}

	// Fetch the syllabus page and update the partial via its data attributes
	async function ajaxRefreshIloSoCpaPartial(){
		const m = (location.pathname||'').match(/\/faculty\/syllabi\/(\d+)/);
		const syllabusId = m ? m[1] : null;
		if (!syllabusId) throw new Error('Syllabus ID not found for refresh');
		const res = await fetch(`/faculty/syllabi/${syllabusId}`, { headers: { 'Accept': 'text/html' } });
		if (!res.ok) throw new Error('Failed to fetch syllabus page');
		const html = await res.text();
		const parser = new DOMParser();
		const doc = parser.parseFromString(html, 'text/html');
		const partial = doc.querySelector('.ilo-so-cpa-mapping');
		if (!partial) throw new Error('Partial not found in response');
		const soColumnsData = partial.getAttribute('data-so-columns') || '[]';
		const mappingsData = partial.getAttribute('data-mappings') || '[]';
		let soColumns = []; let mappings = [];
		try { soColumns = JSON.parse(soColumnsData); } catch(e) { soColumns = []; }
		try { mappings = JSON.parse(mappingsData); } catch(e) { mappings = []; }
		if (window.refreshIloSoCpaPartial) {
			window.refreshIloSoCpaPartial(soColumns, mappings);
		}
		return { soColumns, mappings };
	}

	// Parse Step 4 JSON from AI output and convert to backend payload
	function parseAiJsonToMappings(aiText){
		if (!aiText || typeof aiText !== 'string') throw new Error('No AI output to parse.');
		let jsonText = aiText.trim();
		// Attempt to extract JSON block if wrapped in code fences or preceded by text
		const codeFenceMatch = jsonText.match(/```json\s*([\s\S]*?)```/i) || jsonText.match(/```\s*([\s\S]*?)```/);
		if (codeFenceMatch) jsonText = codeFenceMatch[1].trim();
		// Find first curly brace block
		const firstBrace = jsonText.indexOf('{');
		const lastBrace = jsonText.lastIndexOf('}');
		if (firstBrace >= 0 && lastBrace > firstBrace) {
			jsonText = jsonText.slice(firstBrace, lastBrace + 1);
		}
		let obj;
		try { obj = JSON.parse(jsonText); } catch(e){ throw new Error('Invalid JSON from AI.'); }
		const mapping = obj?.mapping || {};
		const cVal = obj?.C ?? '-';
		const pVal = obj?.P ?? '-';
		const aVal = obj?.A ?? '-';
		// Build so_columns from keys within ILO entries (union of SO keys)
		const soSet = new Set();
		Object.values(mapping).forEach(iloObj => {
			if (iloObj && typeof iloObj === 'object') {
				Object.keys(iloObj).forEach(soKey => soSet.add(soKey));
			}
		});
		const soColumns = Array.from(soSet);
		// Build mappings array ordered by appearance
		const mappings = [];
		let pos = 0;
		Object.keys(mapping).forEach(iloKey => {
			const iloObj = mapping[iloKey] || {};
			const sos = {};
			soColumns.forEach(soKey => {
				const codes = Array.isArray(iloObj[soKey]) ? [...new Set(iloObj[soKey].map(String))] : [];
				sos[soKey] = codes;
			});
			mappings.push({
				ilo_text: iloKey,
				sos,
				c: cVal === '-' ? null : String(cVal),
				p: pVal === '-' ? null : String(pVal),
				a: aVal === '-' ? null : String(aVal),
				position: pos++,
			});
		});
		return { so_columns: soColumns, mappings };
	}

	// AJAX save to backend route using payload from parser
	async function saveIloSoCpaAjax(parsed){
		if (!parsed) throw new Error('No parsed mapping to save.');
		// Get syllabus id from URL
		const m = (location.pathname||'').match(/\/faculty\/syllabi\/(\d+)/);
		const syllabusId = m ? m[1] : null;
		if (!syllabusId) throw new Error('Syllabus ID not found.');
		const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
		// no-op body placeholder removed
		// Assemble request body according to controller expectations
		const reqBody = {
			syllabus_id: syllabusId,
			so_columns: parsed.so_columns,
			mappings: parsed.mappings
		};
		const res = await fetch('/faculty/syllabus/save-ilo-so-cpa-mapping', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				...(token ? { 'X-CSRF-TOKEN': token } : {})
			},
			body: JSON.stringify(reqBody)
		});
		if (!res.ok) {
			const txt = await res.text().catch(()=> '');
			throw new Error('Save failed: ' + txt);
		}
		const data = await res.json().catch(()=>({ success: true }));
		if (!data?.success) throw new Error(data?.message || 'Save failed.');
		return data;
	}

	// Build and send the full snapshot to the AI with a hardcoded prompt
	async function sendAllSnapshotsToAI(){
		if (_inFlight) return;
		try {
			_inFlight = true;
			setProgress('Collecting', 10, 'Gathering snapshot…', 'state-running');
			const snapshot = collectInputSnapshot();
			_lastInput = snapshot;
			if (!_overlay) { _lastOutput = ''; }
			// Build TLA-only message section (recite only the TLA partial)
			let tlaOnly = '';
			try {
				const tlaPayload = collectTlaPayload();
				if (tlaPayload) {
					const tlaText = formatTlaForAI(tlaPayload);
					// Mirror modal section title for clarity
					tlaOnly = 'Partial: Teaching, Learning, and Assessment (TLA) Activities\n' + tlaText;
				}
			} catch(e) {}
			// Build ADM-only message section (code + task overview)
			let admOnly = '';
			try {
				const admPayload = collectAssessmentTasksPayload();
				if (admPayload) {
					const admText = formatAssessmentTasksForAI(admPayload);
					admOnly = 'Partial: Assessment Tasks Distribution\n' + admText;
				}
			} catch(e) {}
			_lastInput = snapshot;
			// Determine syllabus ID from URL like /faculty/syllabi/{id}/...
			const m = (location.pathname||'').match(/\/faculty\/syllabi\/(\d+)/);
			const syllabusId = m ? m[1] : null;
			if (!syllabusId) {
				setProgress('Error', 100, 'Cannot find syllabus ID in URL.', 'state-warn');
				_inFlight = false;
				return;
			}
			// Prompt: Step procedure for ILO–SO and ILO–CPA Mapping
			// Step 1: Read all tasks in ADM and their codes, then output them with CPA checks.
			//         Use this header: | ADM Code | Task | C | P | A |
			//         For C/P/A columns: if the task's CPA cell contains a number, mark '✓'; otherwise leave blank.
			// Step 2: Read TLA and list tasks from the Topics/Reading List column
			//         with their aligned ILO and SO.
			//         Include only rows where Task is present (non-empty) AND BOTH ILO and SO are present, each as a numeric code or comma-separated set of numeric codes (e.g., 1,2,3).
			//         Output Step 2 as a table similar to Step 1 using this header: | # | Code | Task | ILO | SO |
			//         The Code column is the ADM Code of the task (match the task text against ADM tasks/subtasks to retrieve its code).
			//         The # column is the TLA row number where the task appears (use the task's row index as shown in the TLA table, starting from 1).
			//         If a single TLA row contains multiple tasks, list each task separately as its own row. If the same task appears more than once (in the same row or different rows), include all occurrences (do not deduplicate).
			//         If no tasks exist in TLA, output: "no task in TLA".
			// Step 3: Create the ILO–SO–CPA mapping as a matrix based on Step 2.
			//         Output using this format:
			//         Partial: ILO–SO and ILO–CPA Mapping
			//         | ILO | SO1 | SO2 | ... | C | P | A |
			//         | :---: | :---: | :---: | :---: | :---: | :---: |
			//         | ILO1 | codes | codes | ... | - | - | - |
			//         | ILO2 | codes | codes | ... | - | - | - |
			//         The SO numbers are the columns (SO1, SO2, etc.), the ILO numbers are the rows (ILO1, ILO2, etc.). Only include ILO rows and SO columns that have at least one alignment from Step 2 (do not generate empty rows/columns). Each populated cell contains the comma-separated ADM task codes aligned to that (ILO, SO) strictly based on Step 2 (do not infer new alignments). If multiple tasks share the same alignment across different TLA rows, include the task code only once in that cell (no duplicates within a cell). Leave C, P, and A as '-' unless otherwise specified.
			const instructions = [
				'We are working on Partial: ILO–SO and ILO–CPA Mapping.',
				'Rules: Ensure output across all steps is consistent and smart. Follow exact pipe-table headers and formats; use numeric ILO/SO codes; comma-separate multiples without spaces; use "-" for empty cells; do not invent data; rely only on ADM/TLA content and Step 2 alignments; deduplicate task codes per (ILO, SO) cell; and keep row/column ordering stable.',
				'Step 1: Read all tasks in the ADM and their codes.',
				'Output as a table with this header and rows:',
				'| ADM Code | Task | C | P | A |',
				"For C/P/A: mark '✓' if a number exists in the CPA cell; otherwise leave blank.",
				'Step 2: From TLA, output a table with this header: | # | Code | Task | ILO | SO |. The # column is the task\'s TLA row number (starting at 1). Use the ADM section to find and include the ADM Code for each listed task by matching the task text (main or sub tasks). If a single TLA row has multiple tasks, list them separately. If a task appears multiple times (same row or across rows), include all occurrences (no deduplication). Only include rows where the Task is present (non-empty) AND both ILO and SO are numeric codes or comma-separated numeric sets (e.g., 1,2,3). If none, output: no task in TLA.',
				'Step 3: Create the ILO–SO–CPA mapping as a matrix using Step 2. Output a table with ILO codes as rows and SO codes as columns, plus trailing C, P, A columns: header like | ILO | SO1 | SO2 | ... | C | P | A | with rows like | ILO1 | taskcode[,taskcode] | taskcode[,taskcode] | ... | codes-in-C | codes-in-P | codes-in-A |. Include only ILO rows and SO columns that have at least one alignment in Step 2 (no empty rows/columns). Use only the task, ILO, and SO alignments from Step 2; do not invent new ones. For each (ILO, SO) cell, list comma-separated ADM task codes aligned to that pair, deduplicated within the cell. For the trailing C/P/A columns: check Step 1 CPA marks for each ADM task code that appears anywhere in the row (for that ILO). If a task has a check in C, include its ADM code under the C column for that ILO row (comma-separated, deduplicated). Do the same for P and A. If none, use "-".',
				'Step 4: Output a JSON object representing the Step 3 matrix. Use this shape and only include ILO/SO pairs that exist in Step 3 (no empty entries): { "mapping": { "ILO<code>": { "SO<code>": ["<ADM code>", "<ADM code>"] } }, "C": "-", "P": "-", "A": "-" }. Ensure ADM codes in each list are deduplicated, codes are strings, and ILO/SO keys use their numeric codes (e.g., ILO1 → "ILO1", SO2 → "SO2").'
			].join(' ');
			const fd = new FormData();
			const admSection = admOnly || '';
			const tlaSection = tlaOnly || '';
			fd.append('message', instructions + '\n\n' + admSection + (tlaSection ? ('\n\n' + tlaSection) : ''));
			fd.append('context_phase3', '1');
			// CSRF token from meta
			const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
			setProgress('Sending', 40, 'Contacting AI…', 'state-running');
			const res = await fetch(`/faculty/syllabi/${syllabusId}/ai-chat`, {
				method: 'POST',
				headers: token ? { 'X-CSRF-TOKEN': token } : {},
				body: fd
			});
			setProgress('Waiting', 65, 'Awaiting response…', 'state-running');
			if (!res.ok) {
				setProgress('Error', 100, 'AI request failed.', 'state-warn');
				_inFlight = false;
				return;
			}
			const data = await res.json().catch(() => ({}));
			const msg = data?.message || data?.reply || data?.response || '';
			_lastOutput = msg || 'No output.';
			// If modal is open, refresh output; else keep progress only
			if (_overlay) {
				updateModalOutput();
			}
			setProgress('Done', 100, 'AI response received.', 'state-ok');
		} catch (e) {
			setProgress('Error', 100, 'Unexpected error while sending.', 'state-warn');
		} finally {
			_inFlight = false;
		}
	}


	// Legacy entry point kept for API compatibility
	async function callAiForIloSoCpa(){
		await sendAllSnapshotsToAI();
	}
	

	function init(){
		exposeAPI();
		document.addEventListener('keydown', onHotkey);
		// Wire toolbar button to show progress bar and run AI (no modal)
		document.addEventListener('DOMContentLoaded', function(){
			try {
				const btn = document.getElementById('svAiIloSoCpaBtn');
				if (btn && !btn.dataset.boundAiIloSoCpa) {
					btn.dataset.boundAiIloSoCpa = '1';
					btn.addEventListener('click', async function(){
						setProgress('Preparing', 5, 'Building snapshot…', 'state-running');
						await sendAllSnapshotsToAI();
						// After AI responds, auto-insert mapping to DB and refresh partial
						await insertAiMapping();
					});
				}
			} catch(e) {}
		});
	}

	if (document.readyState === 'loading'){
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();

