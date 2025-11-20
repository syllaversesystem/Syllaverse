// -----------------------------------------------------------------------------
// * File: resources/js/faculty/syllabus.js
// * Description: Modular script for syllabus editing – dirty state, exit guard,
//                autosize, field-level "Unsaved" pills, and CIS Contact Hours logic
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-08-31] Initial + Option B (bind unsaved, autosize, credit-hours recompute).
// [2025-08-31] Contact Hours UI – toggle dash when lec/lab are both zero; keep
//              credit-hours text synced; ensure unsaved state reflects edits.
// -----------------------------------------------------------------------------

let isDirty = false;
// short-lived lock to prevent spurious dirty-marking during or immediately after save
window._syllabusSaveLock = false;
// current UI state for save button: 'idle'|'dirty'|'saving'|'saved'
let _sv_state = 'idle';

function setSyllabusSaveState(state, originalHtml = null) {
  try {
    const saveBtn = document.getElementById('syllabusSaveBtn');
    const unsavedCountBadge = document.getElementById('unsaved-count-badge');
    if (!saveBtn) return;
    _sv_state = state;
    switch(state) {
      case 'saving':
        saveBtn.classList.remove('btn-danger'); saveBtn.classList.add('btn-warning');
        saveBtn.innerHTML = '<i class="bi bi-arrow-repeat" style="animation: spin 1s linear infinite;"></i>';
        break;
      case 'saved':
        saveBtn.classList.remove('btn-warning'); saveBtn.classList.add('btn-success');
        saveBtn.innerHTML = '<i class="bi bi-check-lg"></i>';
        // short visual then revert to idle
        setTimeout(() => { setSyllabusSaveState('idle', originalHtml); }, 900);
        break;
      case 'dirty':
        saveBtn.classList.remove('btn-danger'); saveBtn.classList.add('btn-warning');
        if (unsavedCountBadge) unsavedCountBadge.style.display = '';
        break;
      case 'idle':
      default:
        saveBtn.classList.remove('btn-success','btn-warning'); saveBtn.classList.add('btn-danger');
        if (originalHtml) saveBtn.innerHTML = originalHtml;
        if (unsavedCountBadge) unsavedCountBadge.style.display = 'none';
        break;
    }
  } catch (e) { /* noop */ }
}
// expose helper globally
try { window.setSyllabusSaveState = setSyllabusSaveState; } catch (e) { /* noop */ }

function trackFormChanges(formSelector = '#syllabusForm') {
  const form = document.querySelector(formSelector);
  if (!form) return;
  const fields = form.querySelectorAll('textarea, input, select');
  fields.forEach(field => {
    const handler = (e) => {
      // ignore programmatic events
      if (e && e.isTrusted === false) return;
      isDirty = true;
    };
    field.addEventListener('input', handler);
    field.addEventListener('change', handler);
  });
  form.addEventListener('submit', () => { isDirty = false; });
}

function setupExitConfirmation(exitUrlVariable = 'syllabusExitUrl') {
  const targetUrl = window[exitUrlVariable];
  window.handleExit = function (url) {
    // Determine destination URL: explicit URL passed, then globally provided syllabusExitUrl, then computed targetUrl
    let dest = url || window.syllabusExitUrl || targetUrl;

    // If we're on an admin path but the destination points to faculty index, rewrite to admin index.
    try {
      const currentPath = window.location.pathname || '';
      if (currentPath.startsWith('/admin') && typeof dest === 'string' && dest.includes('/faculty/syllabi')) {
        dest = dest.replace('/faculty/syllabi', '/admin/syllabi');
      }
    } catch (e) { /* noop */ }

    // If dest is missing or contains the string 'undefined', fall back to a safer exit URL
    try {
      if (!dest || (typeof dest === 'string' && dest.indexOf('undefined') !== -1)) {
        // Prefer explicit global syllabusExitUrl, otherwise build from syllabusBasePath
        if (window.syllabusExitUrl) {
          dest = window.syllabusExitUrl;
        } else if (window.syllabusBasePath) {
          const base = (typeof window.syllabusBasePath === 'string' && window.syllabusBasePath.startsWith('/')) ? window.syllabusBasePath : ('/' + (window.syllabusBasePath || ''));
          dest = window.location.origin + base;
        } else {
          dest = '/admin/syllabi';
        }
      }
    } catch (e) { /* noop */ }

    if (typeof isDirty !== 'undefined' && isDirty) {
      if (confirm("You have unsaved changes. Do you want to leave without saving?")) {
        window.location.href = dest;
      }
    } else {
      window.location.href = dest;
    }
  };
  window.addEventListener('beforeunload', function (e) {
    if (typeof isDirty !== 'undefined' && isDirty) {
      e.preventDefault();
      e.returnValue = '';
    }
  });
}

// Defensive UX helpers: set a short save lock when user clicks any button inside the syllabus document
// This helps avoid beforeunload prompts when module-level saves trigger background navigation or network activity.
try {
  document.addEventListener('click', function(ev) {
    try {
      const btn = ev.target.closest && ev.target.closest('button, a');
      if (!btn) return;
      // Only apply locking within the syllabus document area to avoid global side-effects
      const inside = btn.closest && btn.closest('.syllabus-doc');
      if (!inside) return;
      // set temporary lock for a short window
      try { window._syllabusSaveLock = true; } catch (e) {}
      setTimeout(() => { try { window._syllabusSaveLock = false; } catch (e) {} }, 1500);
    } catch (e) { /* noop */ }
  }, true);
} catch (e) { /* noop */ }

// Soften blocking alerts related to saving/network issues by replacing alert for specific messages
try {
  (function(){
    const origAlert = window.alert.bind(window);
    window.alert = function(msg) {
      try {
        const text = String(msg || '').toLowerCase();
        if (text.includes('failed to save') || text.includes('failed to fetch') || text.includes('failed to save assessment tasks')) {
          // log and don't block
          console.warn('Suppressed blocking alert:', msg);
          return;
        }
      } catch (e) { /* noop */ }
      // default behaviour for other alerts
      try { origAlert(msg); } catch (e) { console.log('Alert:', msg); }
    };
  })();
} catch (e) { /* noop */ }

/** Bind a field to its Unsaved pill (#unsaved-{name or badgeId}). */
function bindUnsavedIndicator(fieldName, badgeId = null) {
  const el = document.querySelector(`[name="${fieldName}"]`);
  const badge = document.getElementById(`unsaved-${badgeId ?? fieldName}`);
  if (!el || !badge) return;

  const original = el.dataset.original ?? '';
  const toggle = () => {
    const changed = (el.value ?? '') !== original;
    badge.classList.toggle('d-none', !changed);
    if (changed) isDirty = true;
    // highlight newly added/edited fields
    el.classList.toggle('sv-new-highlight', changed && (el.value ?? '') !== '');
    updateUnsavedCount();
  };
  toggle();
  const handler = (e) => {
    // ignore synthetic/programmatic events
    if (e && e.isTrusted === false) {
      try { console.debug('bindUnsavedIndicator: ignored synthetic event for', fieldName); } catch (err) { /* noop */ }
      return;
    }
    try { console.debug('bindUnsavedIndicator: event for', fieldName, 'trusted=', e ? !!e.isTrusted : 'no-event'); } catch (err) { /* noop */ }
    toggle();
  };
  el.addEventListener('input', handler);
  el.addEventListener('change', handler);

  const form = document.getElementById('syllabusForm');
  if (form) form.addEventListener('submit', () => badge.classList.add('d-none'));
}

/** Mark a specific unsaved pill by id and toggle dirty state */
function markDirty(badgeId) {
  // If a save lock is active, ignore external requests to mark dirty to avoid races
  if (window._syllabusSaveLock) {
    try { console.debug('markDirty ignored due to _syllabusSaveLock', badgeId); } catch (e) { /* noop */ }
    return;
  }
  const badge = document.getElementById(badgeId);
  if (!badge) {
    try { console.debug('markDirty: badge not found', badgeId); console.trace(); } catch (e) { /* noop */ }
    return;
  }
  badge.classList.remove('d-none');
  isDirty = true;
  updateUnsavedCount();
  // Debug: log a lightweight stack trace so we can identify who invoked markDirty
  try {
    console.debug('markDirty called for', badgeId, 'at', new Date());
    console.trace();
  } catch (e) { /* noop */ }
}

/** Update the unsaved-count badge in the toolbar based on visible .unsaved-pill elements */
function updateUnsavedCount() {
  const badge = document.getElementById('unsaved-count-badge');
  if (!badge) return;
  const visible = Array.from(document.querySelectorAll('.unsaved-pill')).filter(b => !b.classList.contains('d-none'));
  const count = visible.length;
  if (count > 0) {
    badge.textContent = count;
    badge.style.display = '';
  badge.setAttribute('aria-label', `${count} unsaved changes`);
  // small pulse animation
  badge.animate([{ transform: 'scale(1)' }, { transform: 'scale(1.06)' }, { transform: 'scale(1)' }], { duration: 280 });
  } else {
    badge.style.display = 'none';
  }
}

/** Auto-size textareas that have .autosize */
function autosize(el) { el.style.height = 'auto'; el.style.height = (el.scrollHeight || 0) + 'px'; }
function initAutosize() {
  const areas = document.querySelectorAll('textarea.autosize');
  areas.forEach((ta) => {
    // Avoid stacking multiple listeners if initAutosize runs more than once
    if (!ta.__autosizeBound) {
      ta.__autosizeBound = true;
      const resize = () => autosize(ta);
      ta.addEventListener('input', resize);
      ta.addEventListener('change', resize);
    }
    // Run at call time to sync height with current content
    autosize(ta);
  });
}

/**
 * Generic dynamic input lists
 * - Container: element with class `dynamic-list`
 * - Item wrapper: element with class `dynamic-item`
 * - Input: single-line input with class `dynamic-input`
 * Behavior:
 *  - Enter inside a `.dynamic-input` appends a new `.dynamic-item` after current and focuses it
 *  - Backspace on an empty `.dynamic-input` when caret at 0 removes the item and focuses previous input or a heading
 */
function initDynamicInputLists() {
  const lists = document.querySelectorAll('.dynamic-list');
  if (!lists.length) return;

  lists.forEach((list) => {
    // ensure at least one item exists
    function createItem(value = '') {
      const wrapper = document.createElement('div');
      wrapper.className = 'dynamic-item';
      const input = document.createElement('input');
      input.type = 'text';
      input.className = 'dynamic-input form-control';
      input.value = value;
      wrapper.appendChild(input);
      return wrapper;
    }

    if (!list.querySelector('.dynamic-item')) {
      list.appendChild(createItem());
    }

    // delegated keydown handler
    list.addEventListener('keydown', (e) => {
      const el = e.target;
      if (!el || !el.classList) return;
      if (!el.classList.contains('dynamic-input')) return;

      // ENTER: append new item after current
      if (e.key === 'Enter') {
        e.preventDefault();
        const currentWrapper = el.closest('.dynamic-item');
        const newItem = createItem();
        if (currentWrapper && currentWrapper.parentElement) {
          currentWrapper.parentElement.insertBefore(newItem, currentWrapper.nextSibling);
        } else {
          list.appendChild(newItem);
        }
        // focus the new input
        const ni = newItem.querySelector('.dynamic-input');
        if (ni) ni.focus();
        // notify input listeners
        list.dispatchEvent(new Event('input', { bubbles: true }));
        return;
      }

      // BACKSPACE: if input empty and caret at 0, remove the item and focus previous
      if (e.key === 'Backspace') {
        const val = el.value ?? '';
        const selStart = (typeof el.selectionStart === 'number') ? el.selectionStart : 0;
        if (val === '' && selStart === 0) {
          const wrapper = el.closest('.dynamic-item');
          if (wrapper && wrapper.classList.contains('dynamic-item')) {
            e.preventDefault();
            const prev = wrapper.previousElementSibling;
            wrapper.remove();
            if (prev && prev.querySelector) {
              const prevInput = prev.querySelector('.dynamic-input');
              if (prevInput) prevInput.focus();
            } else {
              // fallback: try focusing an associated heading input in parent column
              const col = list.closest('.col-6');
              const heading = col ? col.querySelector('input[type="text"]') : null;
              if (heading) heading.focus();
            }
            list.dispatchEvent(new Event('input', { bubbles: true }));
          }
        }
      }
    });
  });
}

/** Initialize legacy criteria lists (keeps existing Blade partial markup working)
 * - List container: `.criteria-list`
 * - Item wrapper: `.criteria-item`
 * - Desc input: `.criteria-desc-input`
 * - Percent input: `.criteria-percent-input`
 * Behavior: Enter adds a new `.criteria-item`; Backspace on empty desc removes item
 */
function initCriteriaLists() {
  const lists = document.querySelectorAll('.criteria-list');
  if (!lists.length) return;

  lists.forEach((list) => {
    // ensure at least one item exists
    function createItem(desc = '', percent = '') {
      const wrapper = document.createElement('div');
      wrapper.className = 'criteria-item sub-item';
      wrapper.setAttribute('role', 'listitem');

      const descInput = document.createElement('input');
      descInput.type = 'text';
      descInput.className = 'criteria-desc-input';
      descInput.placeholder = 'e.g., Midterm Exam';
      descInput.value = desc;

      const percentInput = document.createElement('input');
      percentInput.type = 'text';
      percentInput.className = 'criteria-percent-input';
      percentInput.placeholder = '20%';
      percentInput.value = percent;

      wrapper.appendChild(descInput);
      wrapper.appendChild(percentInput);
      return wrapper;
    }

    if (!list.querySelector('.criteria-item')) list.appendChild(createItem());

    list.addEventListener('keydown', (e) => {
      const el = e.target;
      if (!el || !el.classList) return;
      // ENTER: when in a desc or percent input, insert a new item after current
      if (e.key === 'Enter' && (el.classList.contains('criteria-desc-input') || el.classList.contains('criteria-percent-input'))) {
        e.preventDefault();
        const current = el.closest('.criteria-item');
        const newItem = createItem();
        if (current && current.parentElement) current.parentElement.insertBefore(newItem, current.nextSibling);
        else list.appendChild(newItem);
        const ni = newItem.querySelector('.criteria-desc-input');
        if (ni) ni.focus();
        list.dispatchEvent(new Event('input', { bubbles: true }));
        return;
      }

      // BACKSPACE: if desc input is empty and caret at 0, remove current item
      if (e.key === 'Backspace' && el.classList.contains('criteria-desc-input')) {
        const val = el.value ?? '';
        const selStart = (typeof el.selectionStart === 'number') ? el.selectionStart : 0;
        if (val === '' && selStart === 0) {
          const wrapper = el.closest('.criteria-item');
          if (wrapper && wrapper.classList.contains('criteria-item')) {
            e.preventDefault();
            const prev = wrapper.previousElementSibling;
            wrapper.remove();
            if (prev && prev.querySelector) {
              const prevInput = prev.querySelector('.criteria-desc-input');
              if (prevInput) prevInput.focus();
            }
            list.dispatchEvent(new Event('input', { bubbles: true }));
          }
        }
      }
    });
  });
}

/** Serialize visible .criteria-item rows into the hidden textarea fields
 * Format: "Description (20%)" or "Description" if no percent
 */
function serializeCriteriaLists() {
  const lists = document.querySelectorAll('.criteria-list[data-target]');
  if (!lists.length) return;

  lists.forEach((list) => {
    const target = list.getAttribute('data-target');
    if (!target) return;
    const lines = [];
    const items = list.querySelectorAll('.criteria-item');
    items.forEach((it) => {
      const descEl = it.querySelector('.criteria-desc-input');
      const pctEl = it.querySelector('.criteria-percent-input');
      const desc = descEl ? (descEl.value || '').trim() : '';
      const pct = pctEl ? (pctEl.value || '').trim() : '';
      if (!desc) return; // skip empty rows
      if (pct) {
        // normalize percent to have trailing % if user omitted it
        const normalized = pct.endsWith('%') ? pct : (pct + '%');
        lines.push(`${desc} (${normalized})`);
      } else {
        lines.push(desc);
      }
    });

    const textarea = document.querySelector(`#${target}`) || document.querySelector(`textarea[name="${target}"]`);
    if (textarea) {
      textarea.value = lines.join('\n');
      textarea.dispatchEvent(new Event('input', { bubbles: true }));
    }
  });
}

// Criteria module removed: no-op placeholder kept for compatibility
function bindCriteriaEditable() { /* removed: criteria module disabled */ }

/**
 * Recalculate Credit Hours text from lec/lab and toggle CIS dash when both zero.
 * Also marks credit_hours_text as unsaved via its pill if changed from original.
 */
function recalcCreditHours() {
  const lecEl = document.querySelector('[name="contact_hours_lec"]');
  const labEl = document.querySelector('[name="contact_hours_lab"]');
  const creditEl = document.querySelector('[name="credit_hours_text"]');
  const creditBadge = document.getElementById('unsaved-credit_hours_text');
  const dash = document.getElementById('contact-hours-dash');

  if (!lecEl || !labEl) return;

  const lec = parseInt(lecEl.value || '0', 10) || 0;
  const lab = parseInt(labEl.value || '0', 10) || 0;
  const total = lec + lab;

  // Toggle dash visibility (dash shows only when both are 0)
  if (dash) dash.classList.toggle('d-none', !(lec === 0 && lab === 0));

  // Update the Credit Hours text to match CIS format
  if (creditEl) {
    const next = total ? `${total} (${lec} hrs lec; ${lab} hrs lab)` : '';
    const prev = creditEl.value;
    creditEl.value = next;
    creditEl.dispatchEvent(new Event('input', { bubbles: true })); // drive generic listeners

    if (creditBadge) {
      const original = creditEl.dataset.original ?? '';
      const changed = (next ?? '') !== original;
      creditBadge.classList.toggle('d-none', !changed);
      if (changed) isDirty = true;
    }
  }
}

document.addEventListener('DOMContentLoaded', () => {
  trackFormChanges();
  setupExitConfirmation();
  initAutosize();
  // Re-run after layout/fonts/images settle to ensure correct scrollHeight
  setTimeout(() => { try { initAutosize(); } catch (e) {} }, 120);
  try { window.addEventListener('load', () => { try { initAutosize(); } catch (e) {} }); } catch (e) { /* noop */ }
  initDynamicInputLists();
  initCriteriaLists();

  // Existing bindings (mission/vision + CIS fields)
  bindUnsavedIndicator('vision');
  bindUnsavedIndicator('mission');
  [
    'course_title','course_code','course_category','course_prerequisites',
    'semester','year_level','credit_hours_text','reference_cmo','date_prepared',
    'academic_year','revision_no','revision_date'
  ].forEach((f) => bindUnsavedIndicator(f));
  bindUnsavedIndicator('instructor_name','instructor_name');
  bindUnsavedIndicator('instructor_designation','instructor_name');
  bindUnsavedIndicator('instructor_email','instructor_name');
  bindUnsavedIndicator('employee_code','instructor_name');

  // New: rationale + contact hours
  bindUnsavedIndicator('course_description');
  bindUnsavedIndicator('contact_hours_lec');
  bindUnsavedIndicator('contact_hours_lab');

  // TLA strategies auto-saved via dedicated controller (no unsaved indicator)

  // Criteria module disabled

  // criteria heading inputs removed (module disabled)

  

  // Bind new single-line inputs below lists: Enter to append to list
  // heading inputs used instead of single-line add inputs
  // Header editable placeholders and serialization
  // headers removed: no-op for criteria headers

  const lecEl = document.querySelector('[name="contact_hours_lec"]');
  const labEl = document.querySelector('[name="contact_hours_lab"]');
  if (lecEl) lecEl.addEventListener('input', recalcCreditHours);
  if (labEl) labEl.addEventListener('input', recalcCreditHours);

  // Toggle the Contact Hours header unsaved pill when either lec or lab inputs change
  function updateContactHoursUnsaved() {
    const badge = document.getElementById('unsaved-contact_hours');
    if (!badge) return;
    const lecOriginal = lecEl?.dataset.original ?? '';
    const labOriginal = labEl?.dataset.original ?? '';
    const lecVal = lecEl?.value ?? '';
    const labVal = labEl?.value ?? '';
    const changed = (lecVal !== lecOriginal) || (labVal !== labOriginal);
    badge.classList.toggle('d-none', !changed);
    if (changed) isDirty = true;
    updateUnsavedCount();
  }

  if (lecEl) lecEl.addEventListener('input', updateContactHoursUnsaved);
  if (labEl) labEl.addEventListener('input', updateContactHoursUnsaved);

  // Initial sync (ensures dash & credit-hours text correct on load)
  recalcCreditHours();
  // initial unsaved count
  updateUnsavedCount();

  // Minimal Save: submit only mission & vision via Fetch when the top Save button is clicked
  const saveBtn = document.getElementById('syllabusSaveBtn');
  const form = document.getElementById('syllabusForm');
  if (saveBtn && form) {
    saveBtn.addEventListener('click', async (ev) => {
      ev.preventDefault();
      ev.stopPropagation();
      // set a save lock immediately to avoid beforeunload prompts while the save begins
      try { window._syllabusSaveLock = true; } catch (e) { /* noop */ }

  // Capture form action early so other pre-save steps can derive syllabus id from it
  const action = form.action;

      const missionEl = document.querySelector('[name="mission"]');
      const visionEl = document.querySelector('[name="vision"]');
      if (!missionEl || !visionEl) return;

      // --- Save ILOs before the main form submission ---
      try {
        if (window.saveIlo && typeof window.saveIlo === 'function') {
          await window.saveIlo();
        }
      } catch (iloErr) {
        console.error('Failed to save ILO data before syllabus save:', iloErr);
        alert('Failed to save ILOs: ' + (iloErr && iloErr.message ? iloErr.message : 'See console for details.'));
        try { saveBtn.disabled = false; saveBtn.innerHTML = originalHtml; } catch (e) { /* noop */ }
        return;
      }

      // --- Save SOs before the main form submission ---
      try {
        if (window.saveSo && typeof window.saveSo === 'function') {
          await window.saveSo();
        }
      } catch (soErr) {
        console.error('Failed to save SO data before syllabus save:', soErr);
        alert('Failed to save SOs: ' + (soErr && soErr.message ? soErr.message : 'See console for details.'));
        try { saveBtn.disabled = false; saveBtn.innerHTML = originalHtml; } catch (e) { /* noop */ }
        return;
      }

      // --- Save TLA rows before the main form submission ---
      try {
        if (window.saveTla && typeof window.saveTla === 'function') {
          await window.saveTla();
        }
      } catch (tlaErr) {
        console.error('Failed to save TLA data before syllabus save:', tlaErr);
        alert('Failed to save TLA rows: ' + (tlaErr && tlaErr.message ? tlaErr.message : 'See console for details.'));
        try { saveBtn.disabled = false; saveBtn.innerHTML = originalHtml; } catch (e) { /* noop */ }
        return;
      }

      // --- Save CDIOs before the main form submission ---
      try {
        if (window.saveCdio && typeof window.saveCdio === 'function') {
          await window.saveCdio();
        }
      } catch (cdioErr) {
        console.error('Failed to save CDIO data before syllabus save:', cdioErr);
        alert('Failed to save CDIOs: ' + (cdioErr && cdioErr.message ? cdioErr.message : 'See console for details.'));
        try { saveBtn.disabled = false; saveBtn.innerHTML = originalHtml; } catch (e) { /* noop */ }
        return;
      }

      // --- Save Assessment Tasks data before main form submission ---
      try {
        if (window.saveAssessmentTasks && typeof window.saveAssessmentTasks === 'function') {
          await window.saveAssessmentTasks();
          console.log('Assessment Tasks saved to database');
        } else if (window.saveATData && typeof window.saveATData === 'function') {
          window.saveATData();
          console.log('Assessment Tasks data serialized for form submission');
        }
      } catch (atErr) {
        console.error('Failed to save Assessment Tasks:', atErr);
        alert('Failed to save Assessment Tasks: ' + (atErr && atErr.message ? atErr.message : 'See console for details.'));
        try { saveBtn.disabled = false; saveBtn.innerHTML = originalHtml; window._syllabusSaveLock = false; } catch (e) { /* noop */ }
        return;
      }

      // Persist assessment mappings
      try {
        if (window.saveAssessmentMappingsForToolbar && typeof window.saveAssessmentMappingsForToolbar === 'function') {
          try { window._syllabusSaveLock = true; } catch (e) { /* noop */ }
          try {
            await window.saveAssessmentMappingsForToolbar();
          } catch (err) {
            console.warn('saveAssessmentMappingsForToolbar failed (ignored) during save flow', err);
          } finally {
            setTimeout(() => { try { window._syllabusSaveLock = false; } catch (e) { /* noop */ } }, 400);
          }
        }
      } catch (e) {
        console.warn('Unexpected error while attempting to save assessment mappings (ignored)', e);
      }

  const originalHtml = saveBtn.innerHTML;
  console.debug('save click handler: entering, will set Saving... UI', new Date());
  setSyllabusSaveState('saving');

      try {
        const action = form.action;
        // Ensure visible criteria inputs are serialized into their hidden canonical textareas
        try { serializeCriteriaLists(); } catch (e) { console.warn('Failed to serialize criteria lists before save', e); }
        // Also call the new criteria partial serializer if present so its hidden inputs are populated
        try { if (window.serializeCriteriaData && typeof window.serializeCriteriaData === 'function') window.serializeCriteriaData(); } catch (e) { console.warn('Failed to run serializeCriteriaData before save', e); }
        const tokenEl = form.querySelector('input[name="_token"]');
        const token = tokenEl ? tokenEl.value : '';

          const fd = new FormData();
          // Ensure elements that live outside the <form> but use form="syllabusForm"
          // (for example course_policies[] textareas) are included in the POST payload.
          try {
            const externals = document.querySelectorAll('[form="syllabusForm"]');
            externals.forEach((el) => {
              try {
                if (!el.name) return;
                const tag = el.tagName.toUpperCase();
                const type = (el.type || '').toLowerCase();
                if ((type === 'checkbox' || type === 'radio')) {
                  if (el.checked) fd.append(el.name, el.value);
                  return;
                }
                if (tag === 'SELECT' && el.multiple) {
                  Array.from(el.options).forEach(opt => { if (opt.selected) fd.append(el.name, opt.value); });
                  return;
                }
                // For textareas with repeated names (e.g. course_policies[]), append each value
                fd.append(el.name, el.value ?? '');
              } catch (inner) { /* noop */ }
            });
          } catch (e) { /* noop */ }
          fd.append('_token', token);
          fd.append('_method', 'PUT');
          // Minimal save now also persists the course-info partial fields
          fd.append('mission', missionEl.value || '');
          fd.append('vision', visionEl.value || '');

          const extraFields = [
            'course_title','course_code','course_category','course_prerequisites',
            'semester','year_level','credit_hours_text','instructor_name','employee_code',
            'reference_cmo','instructor_designation','date_prepared','instructor_email',
            'revision_no','academic_year','revision_date','course_description',
            'contact_hours','tla_strategies'
          ];
          extraFields.forEach((name) => {
            const el = form.querySelector(`[name="${name}"]`);
            if (!el) return;
            fd.append(name, el.value ?? '');
          });

          // Append criteria module hidden inputs (if the partial is present)
          try {
            const critL = document.getElementById('criteria_lecture_input');
            const critLab = document.getElementById('criteria_laboratory_input');
            if (critL) fd.append('criteria_lecture', critL.value || '');
            if (critLab) fd.append('criteria_laboratory', critLab.value || '');
            // Append structured JSON payload for normalized storage if present
            const critData = document.getElementById('criteria_data_input');
            if (critData) fd.append('criteria_data', critData.value || '[]');
          } catch (e) { console.warn('Failed to append criteria inputs to FormData', e); }
          // criteria module disabled; any existing criteria_* fields (legacy) will be included by the extraFields loop above if present

          // Debug: list all FormData entries so we can inspect what's being sent
          try {
            console.group('Syllabus Save - FormData');
            for (const pair of fd.entries()) {
              console.log(pair[0] + ':', pair[1]);
            }
            console.groupEnd();
          } catch (dbgErr) {
            console.warn('Failed to enumerate FormData for debug', dbgErr);
          }

          // criteria header inputs removed; not appending header titles

        const res = await fetch(action, {
          method: 'POST',
          credentials: 'same-origin',
          body: fd,
        });

        // Provide richer handling for non-OK responses to help debugging
        if (!res.ok) {
          let bodyText = '';
          try {
            const ct = res.headers.get('content-type') || '';
            if (ct.includes('application/json')) {
              const j = await res.json();
              bodyText = JSON.stringify(j);
              // If Laravel validation errors shape present, surface the first message
              if (j.errors) {
                const firstKey = Object.keys(j.errors)[0];
                const firstMsg = Array.isArray(j.errors[firstKey]) ? j.errors[firstKey][0] : j.errors[firstKey];
                throw new Error('Validation failed: ' + firstMsg + ' (' + firstKey + ')');
              }
            } else {
              bodyText = await res.text();
            }
          } catch (parseErr) {
            console.error('Failed to parse error response', parseErr);
          }
          const msg = `Server returned ${res.status} ${res.statusText}. Response: ${bodyText}`;
          throw new Error(msg);
        }

        // success: hide unsaved pills for mission/vision + course-info fields and reset originals
        const savedFields = ['mission','vision', ...extraFields];
        const hiddenBadges = new Set();
        savedFields.forEach((name) => {
          const badge = document.getElementById(`unsaved-${name}`);
          if (badge) { badge.classList.add('d-none'); hiddenBadges.add(`unsaved-${name}`); }
          // some fields share the same badge id (e.g., instructor group). Also try instructor_name badge id.
          if (name.startsWith('instructor')) {
            const group = document.getElementById('unsaved-instructor_name');
            if (group) { group.classList.add('d-none'); hiddenBadges.add('unsaved-instructor_name'); }
          }
          const el = form.querySelector(`[name="${name}"]`);
          if (el) {
            el.dataset.original = el.value ?? '';
            el.classList.remove('sv-new-highlight');
          }
        });
        updateUnsavedCount();

  // criteria module disabled: no post-save criteria handling

  isDirty = false;
  // Set a short lock so other handlers don't immediately re-mark the form dirty
  try { window._syllabusSaveLock = true; } catch (e) { /* noop */ }
  // Notify other scripts that a save completed so they can clear their UI state
  try { window.dispatchEvent(new CustomEvent('syllabusSaved')); } catch (e) { /* noop */ }
  // Clear the lock shortly after so normal editing resumes
  setTimeout(() => { try { window._syllabusSaveLock = false; } catch (e) { /* noop */ } }, 600);
        // Hide all unsaved indicators when form is submitted successfully
        document.querySelectorAll('.unsaved-pill').forEach(pill => {
          pill.classList.add('d-none');
        });
  // reset criteria original snapshot so its unsaved badge won't reappear
  try { if (window._resetCriteriaOriginal) window._resetCriteriaOriginal(); } catch (e) { /* noop */ }

  console.debug('save click handler: save succeeded, restoring button UI', new Date());
  // brief success feedback on button
  setSyllabusSaveState('saved', originalHtml);
        // Defensive watchdog: if some other handler accidentally sets the button to 'Saving...' after
        // we've restored it, force it back to the original state after a short delay.
        setTimeout(() => {
          try {
            const txt = (saveBtn && saveBtn.innerText) ? saveBtn.innerText.toLowerCase() : '';
            if (txt.includes('saving')) {
              saveBtn.innerHTML = originalHtml;
              saveBtn.disabled = false;
            }
          } catch (e) { /* noop */ }
        }, 2500);

      } catch (err) {
  console.error('Syllabus save failed:', err);
  // If this is a network/fetch error or aborted by navigation, don't block the user with an alert.
  const msg = (err && err.message) ? err.message : 'See console for details.';
  if (String(msg).toLowerCase().includes('failed to fetch') || err.name === 'AbortError') {
    // Log network errors silently
    console.warn('Network error during save:', msg);
  } else {
    // show an alert for non-network errors that likely need user action
    try { alert('Failed to save. ' + msg); } catch (e) { console.warn('Could not show alert', e); }
  }

  // ------------------------------
  // Minimal ILO save (no external module)
  // ------------------------------
  window.saveIlo = async function() {
    const list = document.getElementById('syllabus-ilo-sortable');
    if (!list) return { message: 'No ILO list present' };

    function getSyllabusId() {
      try { const id = list.getAttribute('data-syllabus-id'); if (id) return id; } catch (e) {}
      try { const act = (form && form.action) ? form.action : ''; const m = act.match(/\/faculty\/syllabi\/([^\/?#]+)/); if (m) return decodeURIComponent(m[1]); } catch (e) {}
      try {
        const idInput = document.querySelector('[name="id"], input[name="syllabus_id"], input[name="syllabus"]');
        if (idInput && idInput.value) return idInput.value;
      } catch (e) {}
      return '';
    }

    const syllabusId = getSyllabusId();
    if (!syllabusId) throw new Error('Cannot determine syllabus id for ILO save');

    // Build payload from visible rows
    const rows = Array.from(list.querySelectorAll('tr'))
      .filter(r => r.querySelector('textarea[name="ilos[]"]') || r.querySelector('.ilo-badge'));

    // Ensure codes are sequential before reading hidden inputs
    rows.forEach((row, i) => {
      const code = `ILO${i + 1}`;
      const badge = row.querySelector('.ilo-badge'); if (badge) badge.textContent = code;
      const codeInput = row.querySelector('input[name="code[]"]'); if (codeInput) codeInput.value = code;
    });

    const descriptors = rows.map((row, index) => {
      const rawId = row.getAttribute('data-id') || '';
      const id = (/^\d+$/.test(rawId)) ? Number(rawId) : null;
      const code = row.querySelector('input[name="code[]"]')?.value || `ILO${index + 1}`;
      const ta = row.querySelector('textarea[name="ilos[]"]');
      const description = ta ? (ta.value || '') : '';
      const hasContent = (description.trim().length > 0);
      return { row, entry: { id, code, description, position: index + 1 }, hasContent };
    });

    const payloadIlos = descriptors
      .filter(d => d.entry.id || d.hasContent)
      .map(d => d.entry);

    // CSRF headers
    const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
    try {
      const token = document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('#iloForm input[name="_token"], #syllabusForm input[name="_token"]')?.value
        || '';
      if (token) headers['X-CSRF-TOKEN'] = token;
    } catch (e) { /* noop */ }

    const url = (window.syllabusBasePath || '/faculty/syllabi') + `/${encodeURIComponent(syllabusId)}/ilos`;

    const pendingNew = descriptors.filter(d => !d.entry.id && d.hasContent).map(d => d.row);

    const res = await fetch(url, {
      method: 'PUT',
      headers,
      credentials: 'same-origin',
      body: JSON.stringify({ ilos: payloadIlos })
    });
    if (!res.ok) {
      let body = null; try { body = await res.json(); } catch (e) {}
      const msg = (body && (body.message || (body.errors && JSON.stringify(body.errors)))) || 'Failed to save ILOs';
      throw new Error(msg);
    }
    const data = await res.json();

    // Assign server IDs back to newly-created rows in DOM
    if (Array.isArray(data.created_ids) && data.created_ids.length) {
      const apply = pendingNew.slice(0, data.created_ids.length);
      apply.forEach((row, i) => {
        const nid = data.created_ids[i];
        if (row && nid) row.setAttribute('data-id', String(nid));
      });
    }

    // Update originals and hide unsaved pill
    try { document.getElementById('unsaved-ilos')?.classList.add('d-none'); } catch (e) {}
    try { list.querySelectorAll('textarea[name="ilos[]"]').forEach(ta => ta.setAttribute('data-original', ta.value || '')); } catch (e) {}
    try { updateUnsavedCount(); } catch (e) {}
    return data;
  };
  // restore button text to original so user can retry
  try { saveBtn.innerHTML = originalHtml; } catch (e) { console.warn(e); }
      } finally {
  // Always ensure the button is re-enabled so user can retry
  try { saveBtn.disabled = false; } catch (e) { console.warn('Could not re-enable save button', e); }
      }
    });
  }

  // Partial-save buttons removed; top Save button is the single source of truth for saving
  
  // Criteria unsaved detection: normalize JSON snapshot and reuse bindUnsavedIndicator for consistent UX
  (function() {
    const criteriaDataInput = document.getElementById('criteria_data_input');
    if (!criteriaDataInput) return;

    function normalizePayload(raw) {
      try {
        const arr = (typeof raw === 'string') ? JSON.parse(raw || '[]') : (raw || []);
        if (!Array.isArray(arr)) return JSON.stringify([]);
        const norm = arr.map(s => ({
          key: (s.key || '').toString(),
          heading: (s.heading || '').toString(),
          value: Array.isArray(s.value) ? s.value.map(v => ({ description: (v.description || '').toString(), percent: (v.percent || '').toString() })) : []
        }));
        return JSON.stringify(norm);
      } catch (e) { return String(raw || '[]'); }
    }

    // set a normalized original snapshot so simple string comparison works like course-info fields
    try {
      criteriaDataInput.dataset.original = normalizePayload(criteriaDataInput.value || criteriaDataInput.dataset.original || '[]');
    } catch (e) { /* noop */ }

    // reuse the existing bindUnsavedIndicator so the badge and unsaved count behave consistently
    try { bindUnsavedIndicator('criteria_data', 'criteria'); } catch (e) { /* noop */ }

    // when the partial fires a criteriaChanged event, ensure serialization runs and the input event is dispatched
    document.addEventListener('criteriaChanged', function(){
      try { if (window.serializeCriteriaData) window.serializeCriteriaData(); } catch (e) { /* noop */ }
    });

    // helper to update the stored original after a successful save
    window._resetCriteriaOriginal = function() {
      try { criteriaDataInput.dataset.original = normalizePayload(criteriaDataInput.value || '[]'); } catch (e) { /* noop */ }
      // trigger a change so bindUnsavedIndicator re-evaluates
      try { criteriaDataInput.dispatchEvent(new Event('input', { bubbles: true })); } catch (e) { /* noop */ }
    };
  })();
});

// Optional exports
export { trackFormChanges, setupExitConfirmation, bindUnsavedIndicator, recalcCreditHours, initAutosize, markDirty, updateUnsavedCount };
