// Criteria for Assessment module extracted from Blade partial
// Handles section/sub-line editing, serialization, add/remove actions, and local save button

function initCriteriaModule() {
  function setupCriteriaDom(){
    const removedState = window.__criteriaRemovedState = window.__criteriaRemovedState || { sections: [], rows: {} };

    function getSectionKey(sectionEl) {
      if (!sectionEl) return '';
      return sectionEl.dataset.sectionKey
        || (sectionEl.querySelector('.main-input')?.dataset.section ?? '')
        || '';
    }

    function stashRemovedRow(sectionKey, rowData) {
      if (!sectionKey || !rowData) return;
      if (!removedState.rows[sectionKey]) removedState.rows[sectionKey] = [];
      removedState.rows[sectionKey].push({
        description: (rowData.description ?? '').trim(),
        percent: (rowData.percent ?? '').trim(),
      });
    }

    function popRemovedRow(sectionKey) {
      if (!sectionKey) return null;
      const stack = removedState.rows[sectionKey];
      if (!stack || !stack.length) return null;
      return stack.pop();
    }

    function stashRemovedSection(sectionData) { if (sectionData) removedState.sections.push(sectionData); }
    function popRemovedSection() { return removedState.sections.length ? removedState.sections.pop() : null; }

    function sanitizeSectionKey(rawKey, fallbackIndex) {
      let key = (rawKey || '').toString().trim().toLowerCase();
      key = key.replace(/[^a-z0-9\s-_]/g, '').replace(/[\s-_]+/g, '_').replace(/^_|_$/g, '');
      if (!key) key = 'section_' + (Number.isFinite(fallbackIndex) ? fallbackIndex : Date.now());
      return key;
    }

    function collectSectionData(sectionEl) {
      if (!sectionEl) return null;
      const key = getSectionKey(sectionEl);
      const heading = (sectionEl.querySelector('.main-input')?.value || '').trim();
      const values = [];
      sectionEl.querySelectorAll('.sub-list .sub-line').forEach(function(line){
        const desc = (line.querySelector('.sub-input')?.value || '').trim();
        const percent = (line.querySelector('.sub-percent')?.value || '').trim();
        if (desc === '' && percent === '') return;
        values.push({ description: desc, percent: percent });
      });
      if (!heading && values.length === 0) return null;
      return { key, heading, values };
    }

    function updateSectionRemoveState(sectionEl) {
      if (!sectionEl) return;
      const removeBtn = sectionEl.querySelector('.criteria-actions-row .criteria-remove-btn');
      if (!removeBtn) return;
      const subList = sectionEl.querySelector('.sub-list');
      const count = subList ? subList.querySelectorAll('.sub-line').length : 0;
      const disable = count <= 1;
      removeBtn.disabled = disable;
      removeBtn.setAttribute('aria-disabled', disable ? 'true' : 'false');
    }

    let __critChangedTimer = null;
    function fireCriteriaChangedDebounced(delay){
      try {
        const d = Number.isFinite(delay) ? delay : 60;
        if (__critChangedTimer) clearTimeout(__critChangedTimer);
        __critChangedTimer = setTimeout(() => { try { fireCriteriaChanged(); } catch (e) { /* noop */ } }, d);
      } catch (e) { /* noop */ }
    }

    function createSubLine(initial) {
      const el = document.createElement('div');
      el.className = 'sub-line';
      const ta = document.createElement('textarea');
      ta.rows = 1;
      ta.className = 'sub-input cis-input autosize';
      ta.placeholder = '-';
      let descValue = '';
      let pct = '';
      if (initial && typeof initial === 'object') {
        descValue = (initial.description ?? '').toString();
        pct = (initial.percent ?? '').toString();
      } else if (typeof initial === 'string' && initial !== '') {
        const m = initial.match(/^(.*?)\s*(?:\(?([0-9]{1,3}%?)\)?)\s*$/);
        if (m) { descValue = (m[1] || '').trim(); pct = (m[2] || '').trim(); }
        else { descValue = initial; }
      }
      ta.value = descValue; el.appendChild(ta);
      const p = document.createElement('textarea');
      p.rows = 1; p.className = 'sub-percent cis-number autosize';
      p.placeholder = '%'; p.value = pct || '';
      el.appendChild(p);
      return el;
    }

    document.querySelectorAll('.cis-criteria .sections-container .section').forEach(function(section){
      const main = section.querySelector('.main-input');
      const subList = section.querySelector('.sub-list');
      function syncFromMain() {
        const raw = (main.value || '').split(/\r?\n/).map(s => s.trim());
        main.value = raw[0] || '';
        subList.innerHTML = '';
        for (let i=1;i<raw.length;i++) { if (raw[i]) subList.appendChild(createSubLine(raw[i])); }
        if (subList.children.length === 0) subList.appendChild(createSubLine());
        attachSubHandlers(subList, main); updateSectionRemoveState(section); fireCriteriaChanged();
      }
      main.addEventListener('input', function(){
        try {
          const container = document.getElementById('criteria-sections-container');
          const sections = Array.from(container ? container.querySelectorAll('.section') : []);
          const sectionEl = main.closest('.section');
          const sectionIndex = Math.max(1, sections.indexOf(sectionEl) + 1);
          document.dispatchEvent(new CustomEvent('criteria:sectionMainChanged', { detail: { section: sectionIndex, value: main.value || '' } }));
        } catch (e) { /* noop */ }
        fireCriteriaChangedDebounced(60);
      });
      try {
        const initData = subList.dataset.init ? JSON.parse(subList.dataset.init) : null;
        if (Array.isArray(initData) && initData.length > 0) {
          subList.innerHTML = '';
          initData.forEach(function(item){
            const line = createSubLine((item.description || '') + (item.percent ? (' ' + item.percent) : ''));
            subList.appendChild(line);
          });
          attachSubHandlers(subList, main); updateSectionRemoveState(section);
        } else if ((main.value || '').indexOf('\n') !== -1) {
          syncFromMain();
        } else {
          subList.innerHTML = ''; subList.appendChild(createSubLine()); attachSubHandlers(subList, main); updateSectionRemoveState(section);
        }
      } catch (e) {
        if ((main.value || '').indexOf('\n') !== -1) { syncFromMain(); }
        else { subList.innerHTML = ''; subList.appendChild(createSubLine()); attachSubHandlers(subList, main); updateSectionRemoveState(section); }
      }
    });

    function attachSubHandlers(listEl, mainEl) {
      Array.from(listEl.querySelectorAll('.sub-line .sub-input')).forEach(function(inp){
        inp.addEventListener('input', function(){
          try {
            const sectionEl = inp.closest('.section');
            const container = document.getElementById('criteria-sections-container');
            const sections = Array.from(container ? container.querySelectorAll('.section') : []);
            const sectionIndex = Math.max(1, sections.indexOf(sectionEl) + 1);
            const subLines = Array.from(sectionEl.querySelectorAll('.sub-list .sub-line'));
            const thisLine = inp.closest('.sub-line');
            const subIndex = Math.max(1, subLines.indexOf(thisLine) + 1);
            document.dispatchEvent(new CustomEvent('criteria:subChanged', { detail: { section: sectionIndex, subIndex: subIndex, value: inp.value || '' } }));
          } catch (e) { /* noop */ }
          fireCriteriaChangedDebounced(60);
        });
      });
      Array.from(listEl.querySelectorAll('.sub-percent')).forEach(function(pin){
        pin.addEventListener('blur', function(){
          let v = (pin.value || '').toString().trim();
          if (v === '') return;
          v = v.replace(/%+/g, '%').replace(/\s+/g, '');
          if (/^\d+(?:\.\d+)?$/.test(v)) pin.value = v + '%';
          else if (/^\d+(?:\.\d+)?%$/.test(v)) pin.value = v;
          else pin.value = v;
          fireCriteriaChanged();
        });
        pin.addEventListener('input', function(){ fireCriteriaChangedDebounced(80); });
      });
    }

    function addSubLineToSection(sectionEl) {
      if (!sectionEl) return;
      const subList = sectionEl.querySelector('.sub-list'); if (!subList) return;
      const sectionKey = getSectionKey(sectionEl);
      const restoredRow = popRemovedRow(sectionKey);
      const newLine = createSubLine(restoredRow || undefined);
      subList.appendChild(newLine);
      attachSubHandlers(subList, sectionEl.querySelector('.main-input'));
      newLine.querySelectorAll('textarea.autosize').forEach(function(ta){ try { ta.style.height = 'auto'; ta.style.height = (ta.scrollHeight || 0) + 'px'; } catch (e) { /* noop */ } });
      updateSectionRemoveState(sectionEl);
      if (restoredRow) fireCriteriaChanged();
      const ta = newLine.querySelector('.sub-input'); if (ta) ta.focus();
      fireCriteriaChanged();
      try { recomputeAutosizeAll(); } catch (e) { /* noop */ }
    }

    function removeSubLineFromSection(sectionEl) {
      if (!sectionEl) return;
      const subList = sectionEl.querySelector('.sub-list'); if (!subList) return;
      const lines = Array.from(subList.querySelectorAll('.sub-line')); if (!lines.length) return;
      const sectionKey = getSectionKey(sectionEl);
      if (lines.length === 1) {
        const onlyLine = lines[0];
        if (onlyLine) {
          const subInput = onlyLine.querySelector('.sub-input');
          const pctInput = onlyLine.querySelector('.sub-percent');
          if (subInput && pctInput && (subInput.value || pctInput.value)) { try { subInput.focus(); } catch (e) { /* noop */ } }
        }
        updateSectionRemoveState(sectionEl);
        return;
      }
      let target = null;
      for (let i = lines.length - 1; i >= 0; i--) {
        const desc = (lines[i].querySelector('.sub-input')?.value || '').trim();
        const pct  = (lines[i].querySelector('.sub-percent')?.value || '').trim();
        if (desc !== '' || pct !== '') { target = lines[i]; break; }
        if (desc === '' && pct === '' && lines.length > 1) { target = lines[i]; break; }
      }
      if (!target) {
        if (lines.length > 1) { lines[lines.length - 1].remove(); fireCriteriaChanged(); }
        try { recomputeAutosizeAll(); } catch (e) { /* noop */ }
        updateSectionRemoveState(sectionEl);
        return;
      }
      const descVal = (target.querySelector('.sub-input')?.value || '').trim();
      const pctVal = (target.querySelector('.sub-percent')?.value || '').trim();
      if (descVal !== '' || pctVal !== '') stashRemovedRow(sectionKey, { description: descVal, percent: pctVal });
      const prev = target.previousElementSibling; target.remove(); if (prev) { const prevDesc = prev.querySelector('.sub-input'); if (prevDesc) prevDesc.focus(); }
      fireCriteriaChanged();
      try { recomputeAutosizeAll(); } catch (e) { /* noop */ }
      updateSectionRemoveState(sectionEl);
    }

    window.addCriteriaSubLine = addSubLineToSection;
    window.removeCriteriaSubLine = removeSubLineFromSection;

    function serializeSectionEl(section) {
      if (!section) return '';
      const main = section.querySelector('.main-input');
      const subLines = section.querySelectorAll('.sub-list .sub-line');
      let lines = [];
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
      const sectionEls = document.querySelectorAll('.cis-criteria .sections-container .section');
      const lecture = sectionEls[0] ? serializeSectionEl(sectionEls[0]) : '';
      const laboratory = sectionEls[1] ? serializeSectionEl(sectionEls[1]) : '';
      const critL = document.getElementById('criteria_lecture_input');
      const critLab = document.getElementById('criteria_laboratory_input');
      if (critL) critL.value = lecture;
      if (critLab) critLab.value = laboratory;
      const payload = [];
      function slugify(s) {
        if (!s) return '';
        return s.toString().toLowerCase().trim()
          .replace(/[^a-z0-9\s-_]/g, '')
          .replace(/[\s-_]+/g, '_')
          .replace(/^_|_$/g, '');
      }
      const sections = document.querySelectorAll('.cis-criteria .sections-container .section');
      sections.forEach(function(sectionEl, idx){
        const main = sectionEl.querySelector('.main-input');
        const heading = main ? (main.value || '').trim() : '';
        const subLines = sectionEl.querySelectorAll('.sub-list .sub-line');
        const values = [];
        subLines.forEach(function(line){
          const ta = line.querySelector('.sub-input');
          const p = line.querySelector('.sub-percent');
          const tval = ta ? (ta.value || '').trim() : '';
          const pval = p ? (p.value || '').trim() : '';
          if (tval !== '' || pval !== '') values.push({ description: tval, percent: pval });
        });
        let key = slugify(heading);
        if (!key && main && main.dataset && main.dataset.section) key = main.dataset.section;
        if (!key) key = 'section_' + (idx + 1);
        payload.push({ key: key, heading: heading, value: values });
      });
      const critData = document.getElementById('criteria_data_input');
      if (critData) {
        critData.value = JSON.stringify(payload);
        try { critData.dispatchEvent(new Event('input', { bubbles: true })); } catch (e) { /* noop */ }
      }
    };

    function fireCriteriaChanged(){ document.dispatchEvent(new Event('criteriaChanged')); }

    // Realtime snapshot: build and merge Criteria block into global context
    function sanitize(val){
      if (val == null) return '-';
      const s = String(val).trim();
      return s.length ? s : '-';
    }
    function buildCriteriaBlock(){
      const lines = [];
      lines.push('PARTIAL_BEGIN:criteria_assessment');
      lines.push('TITLE: Criteria for Assessment');
      lines.push('COLUMNS: Section | Description | Percent');
      const sections = document.querySelectorAll('.cis-criteria .sections-container .section');
      sections.forEach(function(sectionEl, idx){
        const main = sectionEl.querySelector('.main-input');
        const heading = sanitize(main ? main.value : '');
        const subLines = sectionEl.querySelectorAll('.sub-list .sub-line');
        if (subLines.length === 0) {
          lines.push(`ROW: ${heading} | - | -`);
        } else {
          subLines.forEach(function(line){
            const desc = sanitize(line.querySelector('.sub-input')?.value || '');
            let pct = (line.querySelector('.sub-percent')?.value || '').toString().trim();
            if (pct) {
              pct = pct.endsWith('%') ? pct : (/^\d+(?:\.\d+)?$/.test(pct) ? pct + '%' : pct);
            }
            const pctSan = sanitize(pct);
            lines.push(`ROW: ${heading} | ${desc} | ${pctSan}`);
          });
        }
      });
      if (!sections.length) {
        lines.push('NOTE: No criteria sections defined yet.');
      }
      lines.push('PARTIAL_END:criteria_assessment');
      return lines.join('\n');
    }
    function mergeRealtimeCriteria(){
      const block = buildCriteriaBlock();
      const existing = window._svRealtimeContext || '';
      const others = existing
        .split(/\n{2,}/)
        .filter(s => s && !/PARTIAL_BEGIN:criteria_assessment[\s\S]*PARTIAL_END:criteria_assessment/.test(s))
        .join('\n\n');
      const merged = others ? (others + '\n\n' + block) : block;
      window._svRealtimeContext = merged;
    }

    try { const __critInit = document.getElementById('criteria_data_input'); if (__critInit) __critInit.dataset.original = __critInit.value || '[]'; } catch (e) { /* noop */ }

    function recomputeAutosizeAll(){
      try { document.querySelectorAll('.cis-criteria textarea.autosize').forEach(function(ta){ ta.style.height = 'auto'; ta.style.height = (ta.scrollHeight || 0) + 'px'; }); } catch (e) { /* noop */ }
    }
    recomputeAutosizeAll();

    let __critSerializeTimer = null;
    document.addEventListener('criteriaChanged', function(){
      try {
        if (__critSerializeTimer) clearTimeout(__critSerializeTimer);
        __critSerializeTimer = setTimeout(function(){
          try { window.serializeCriteriaData(); } catch (e) { /* noop */ }
          try { mergeRealtimeCriteria(); } catch (e) { /* noop */ }
        }, 80);
      } catch (e) { /* noop */ }
    });

    // Also update realtime context on explicit events from typing
    document.addEventListener('criteria:sectionMainChanged', function(){
      try { mergeRealtimeCriteria(); } catch (e) { /* noop */ }
    });
    document.addEventListener('criteria:subChanged', function(){
      try { mergeRealtimeCriteria(); } catch (e) { /* noop */ }
    });

    function updateAddSectionState(){
      const container = document.getElementById('criteria-sections-container');
      const btn = document.querySelector('.cis-criteria .criteria-add-section-btn');
      if (!container || !btn) return;
      const count = container.querySelectorAll('.section').length;
      const disabled = count >= 3;
      btn.disabled = disabled;
      btn.setAttribute('aria-disabled', disabled ? 'true' : 'false');
      try { btn.title = disabled ? 'Maximum of 3 sections reached' : 'Add section'; } catch(e){}
    }

    document.querySelectorAll('.cis-criteria .criteria-actions-row .criteria-add-btn').forEach(function(addBtn){
      addBtn.addEventListener('click', function(){ const section = this.closest('.section') || document.querySelector('.cis-criteria .section'); addSubLineToSection(section); });
    });
    document.querySelectorAll('.cis-criteria .criteria-actions-row .criteria-remove-btn').forEach(function(removeBtn){
      removeBtn.addEventListener('click', function(){ const section = this.closest('.section') || document.querySelector('.cis-criteria .section'); removeSubLineFromSection(section); });
    });

    const addSectionBtn = document.querySelector('.cis-criteria .criteria-add-section-btn');
    if (addSectionBtn) {
      addSectionBtn.addEventListener('click', function(){
        const container = document.getElementById('criteria-sections-container'); if (!container) return;
        if (container.querySelectorAll('.section').length >= 3) { return; }
        const index = container.querySelectorAll('.section').length + 1;
        const restored = popRemovedSection();
        const key = sanitizeSectionKey(restored && restored.key ? restored.key : '', index);
        const section = document.createElement('div');
        section.className = 'section'; section.dataset.sectionKey = key;
        section.innerHTML = `
          <div class="section-head">
            <textarea rows="1" name="criteria_${key}_display" data-section="${key}" class="main-input cis-input autosize" placeholder="-"></textarea>
          </div>
          <div class="sub-list" aria-live="polite" data-init='[]'></div>
          <div class="criteria-actions-row">
            <button type="button" class="btn btn-sm criteria-remove-btn" title="Remove last sub-item" aria-label="Remove last sub-item"><i data-feather="minus"></i></button>
            <button type="button" class="btn btn-sm criteria-add-btn" title="Add sub-item" aria-label="Add sub-item"><i data-feather="plus"></i></button>
          </div>`;
        container.appendChild(section);
        const subList = section.querySelector('.sub-list');
        const mainEl = section.querySelector('.main-input');
        if (restored && restored.heading && mainEl) mainEl.value = restored.heading;
        if (subList) {
          subList.innerHTML = '';
          const rows = Array.isArray(restored?.values) ? restored.values : (Array.isArray(restored?.value) ? restored.value : []);
          if (rows.length) rows.forEach(function(row){ subList.appendChild(createSubLine(row)); });
          else subList.appendChild(createSubLine());
          attachSubHandlers(subList, mainEl);
          subList.querySelectorAll('textarea.autosize').forEach(function(ta){ try { ta.style.height = 'auto'; ta.style.height = (ta.scrollHeight || 0) + 'px'; } catch (e) {} });
          updateSectionRemoveState(section);
        }
        if (mainEl) {
          mainEl.addEventListener('input', function(){ fireCriteriaChanged(); });
          try { mainEl.style.height = 'auto'; mainEl.style.height = (mainEl.scrollHeight||0)+'px'; } catch(e){}
        }
        section.querySelectorAll('.criteria-actions-row .criteria-add-btn').forEach(function(btn){ btn.addEventListener('click', function(){ const sec = this.closest('.section') || section; addSubLineToSection(sec); }); });
        section.querySelectorAll('.criteria-actions-row .criteria-remove-btn').forEach(function(btn){ btn.addEventListener('click', function(){ const sec = this.closest('.section') || section; removeSubLineFromSection(sec); }); });
        if (window.feather && typeof window.feather.replace==='function') { try { window.feather.replace(); } catch(e){} }
        if (restored && restored.key) { removedState.rows[restored.key] = []; removedState.rows[key] = []; }
        fireCriteriaChanged(); updateAddSectionState(); recomputeAutosizeAll();
      });
    }

    const removeSectionBtn = document.querySelector('.cis-criteria .criteria-remove-section-btn');
    if (removeSectionBtn) {
      removeSectionBtn.addEventListener('click', function(){
        const container = document.getElementById('criteria-sections-container'); if (!container) return;
        const sections = container.querySelectorAll('.section'); if (sections.length <= 1) return;
        const last = sections[sections.length - 1]; const stored = collectSectionData(last);
        if (stored) { stashRemovedSection(stored); removedState.rows[stored.key] = []; }
        last.remove(); fireCriteriaChanged(); updateAddSectionState(); recomputeAutosizeAll();
      });
    }

    updateAddSectionState();
  }

  // Run now if DOM already parsed; else wait for DOMContentLoaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupCriteriaDom, { once: true });
  } else {
    try { setupCriteriaDom(); } catch (e) { /* noop */ }
  }

  // Local Save Criteria button wiring
  // Saving functionality removed per request; editing & serialization retained.
  (function(){
    try {
      const btn = document.getElementById('saveCriteriaBtn');
      const statusEl = document.getElementById('saveCriteriaStatus');
      if (!btn) return;

      function resolveForm() {
        return document.getElementById('syllabusForm')
          || document.querySelector('form#syllabusForm')
          || document.querySelector('form[name="syllabusForm"]')
          || document.querySelector('form[data-role="syllabus-form"]')
          || (document.querySelector('.syllabus-doc') ? document.querySelector('.syllabus-doc form') : null);
      }

      function resolveAction(form) {
        if (form && form.action) return form.action;
        const docEl = document.getElementById('syllabus-document');
        const sid = docEl && docEl.dataset ? docEl.dataset.syllabusId : null;
        return sid ? ('/faculty/syllabi/' + sid) : '';
      }

      function resolveCsrf(form) {
        const tokenEl = form ? form.querySelector('input[name="_token"]') : null;
        if (tokenEl) return tokenEl.value || '';
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? (meta.getAttribute('content') || '') : '';
      }

      async function doSave() {
        try { if (window.serializeCriteriaData) window.serializeCriteriaData(); } catch (e) { /* noop */ }
        const form = resolveForm();
        if (!form) throw new Error('Syllabus form not found');
        const action = resolveAction(form);
        if (!action) throw new Error('Save endpoint missing');
        const token = resolveCsrf(form);
        if (!token) throw new Error('CSRF token missing');
        const critL = document.getElementById('criteria_lecture_input');
        const critLab = document.getElementById('criteria_laboratory_input');
        const critData = document.getElementById('criteria_data_input');
        const fd = new FormData();
        fd.append('_token', token);
        fd.append('_method', 'PUT');
        fd.append('criteria_lecture', critL ? (critL.value || '') : '');
        fd.append('criteria_laboratory', critLab ? (critLab.value || '') : '');
        fd.append('criteria_data', critData ? (critData.value || '[]') : '[]');
        let res;
        try {
          res = await fetch(action, { method: 'POST', credentials: 'same-origin', body: fd });
        } catch (netErr) {
          throw new Error('Network error');
        }
        if (!res.ok) {
          let msg = res.status + ' ' + res.statusText;
          try {
            const ct = res.headers.get('content-type') || '';
            if (ct.includes('application/json')) {
              const j = await res.json();
              if (j.errors) {
                const firstKey = Object.keys(j.errors)[0];
                const firstMsg = Array.isArray(j.errors[firstKey]) ? j.errors[firstKey][0] : j.errors[firstKey];
                msg = 'Validation failed: ' + firstMsg + ' (' + firstKey + ')';
              } else if (j.message) { msg = j.message; }
            } else {
              msg = (await res.text()).slice(0, 300);
            }
          } catch (e) { /* noop */ }
          throw new Error(msg);
        }
        // success
        try {
          if (critData) critData.dataset.original = critData.value || '[]';
          document.dispatchEvent(new CustomEvent('criteria:saved'));
        } catch (e) { /* noop */ }
      }

      // Expose global toolbar-compatible function (showAlert optional)
      try {
        window.saveCriteria = async function(showAlert = false) {
          await doSave();
          // Hide unsaved pill if present and recompute badge counts
          try {
            const critData = document.getElementById('criteria_data_input');
            if (critData) critData.dataset.original = critData.value || '[]';
            const pill = document.getElementById('unsaved-criteria');
            if (pill) pill.classList.add('d-none');
            if (typeof window.updateUnsavedCount === 'function') window.updateUnsavedCount();
            // trigger re-evaluation for bindUnsavedIndicator
            if (critData) critData.dispatchEvent(new Event('input', { bubbles: true }));
          } catch (e) { /* noop */ }
          if (showAlert) { try { alert('Criteria saved'); } catch (e) { /* noop */ } }
        };
      } catch (e) { /* noop */ }

      btn.addEventListener('click', async function(){
        const prevHtml = btn.innerHTML;
        try {
          btn.disabled = true;
          btn.innerHTML = '<i class="bi bi-arrow-repeat" style="animation: spin 1s linear infinite;"></i>';
          if (statusEl) statusEl.textContent = 'Saving…';
          await doSave();
          btn.innerHTML = '<i class="bi bi-check-lg"></i>';
          if (statusEl) statusEl.textContent = 'Saved';
        } catch (err) {
          console.error('Criteria save error:', err);
          if (statusEl) statusEl.textContent = 'Failed: ' + (err && err.message ? err.message : 'Error');
        } finally {
          setTimeout(() => {
            btn.innerHTML = prevHtml;
            btn.disabled = false;
            if (statusEl) statusEl.textContent = '';
          }, 900);
        }
      });
    } catch (e) { /* noop */ }
  })();
}

// Initialize immediately when imported
try { initCriteriaModule(); } catch (e) { /* noop */ }

// Initial realtime merge when module initializes
try { document.addEventListener('DOMContentLoaded', function(){ try { const evt = new Event('criteriaChanged'); document.dispatchEvent(evt); } catch (e) { /* noop */ } }); } catch (e) { /* noop */ }

export { initCriteriaModule };
