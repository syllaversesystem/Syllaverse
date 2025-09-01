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

function trackFormChanges(formSelector = '#syllabusForm') {
  const form = document.querySelector(formSelector);
  if (!form) return;
  const fields = form.querySelectorAll('textarea, input, select');
  fields.forEach(field => {
    field.addEventListener('input', () => (isDirty = true));
    field.addEventListener('change', () => (isDirty = true));
  });
  form.addEventListener('submit', () => { isDirty = false; });
}

function setupExitConfirmation(exitUrlVariable = 'syllabusExitUrl') {
  const targetUrl = window[exitUrlVariable];
  window.handleExit = function () {
    if (typeof isDirty !== 'undefined' && isDirty) {
      if (confirm("You have unsaved changes. Do you want to leave without saving?")) {
        window.location.href = targetUrl;
      }
    } else {
      window.location.href = targetUrl;
    }
  };
  window.addEventListener('beforeunload', function (e) {
    if (typeof isDirty !== 'undefined' && isDirty) {
      e.preventDefault();
      e.returnValue = '';
    }
  });
}

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
  el.addEventListener('input', toggle);
  el.addEventListener('change', toggle);

  const form = document.getElementById('syllabusForm');
  if (form) form.addEventListener('submit', () => badge.classList.add('d-none'));
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
  // enable Save if disabled
  const saveBtn = document.getElementById('syllabusSaveBtn');
  if (saveBtn) saveBtn.disabled = false;
  } else {
    badge.style.display = 'none';
  const saveBtn = document.getElementById('syllabusSaveBtn');
  if (saveBtn) saveBtn.disabled = true;
  }
}

/** Auto-size textareas that have .autosize */
function autosize(el) { el.style.height = 'auto'; el.style.height = (el.scrollHeight || 0) + 'px'; }
function initAutosize() {
  const areas = document.querySelectorAll('textarea.autosize');
  areas.forEach((ta) => {
    autosize(ta);
    ta.addEventListener('input', () => autosize(ta));
  });
}

/** Criteria contenteditable helpers: keep hidden textarea in sync and set unsaved pill */
function bindCriteriaEditable() {
  const lists = document.querySelectorAll('.criteria-list');

  // small debounce helper to avoid excessive writes while typing
  function debounce(fn, wait = 120) {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), wait); };
  }

  lists.forEach((list) => {
    const targetName = list.dataset.target;
    const ta = document.querySelector(`textarea[name="${targetName}"]`);
    if (!ta) return;

    // Update the criteria unsaved pill only when serialized data or the column heading differs from original
    const updateCriteriaUnsaved = () => {
      const badge = document.getElementById('unsaved-criteria');
      if (!badge) return;
      const original = (ta.dataset.original ?? '').toString();
      const current = (ta.value ?? '').toString();
      let changed = current !== original;
      // also consider heading change for this column
      const col = list.closest('.col-6');
      const heading = col ? col.querySelector('.criteria-heading-input') : null;
      if (heading) {
        const hOrig = (heading.dataset.original ?? '').toString();
        const hCur = (heading.value ?? '').toString();
        if (hCur !== hOrig) changed = true;
      }
      badge.classList.toggle('d-none', !changed);
      if (changed) isDirty = true;
      updateUnsavedCount();
    };

    const serialize = () => {
      const lines = Array.from(list.querySelectorAll('.criteria-item')).map((item) => {
        const desc = (item.querySelector('.criteria-desc-input')?.value ?? '').trim();
        let pct = (item.querySelector('.criteria-percent-input')?.value ?? '').trim();
        if (pct !== '' && !pct.endsWith('%')) pct = pct + '%';
        if (desc === '' && pct === '%') return null; // ignore empty rows
        if (desc === '' && pct === '') return null;
        return (desc + (pct ? ' (' + pct + ')' : '')).trim();
      }).filter(Boolean);
  ta.value = lines.join('\n');
  updateCriteriaUnsaved();
    };

  const debouncedSerialize = debounce(serialize, 100);
  // expose synchronous serializer for external callers (e.g., save handler)
  try { list._serialize = serialize; } catch (err) {}

    // Try to find and initialize the column heading input so we can track its original value
    const col = list.closest('.col-6');
    const headingInput = col ? col.querySelector('.criteria-heading-input') : null;
    if (headingInput && typeof headingInput.dataset.original === 'undefined') {
      // store current as original so comparisons work
      headingInput.dataset.original = headingInput.value ?? '';
    }
    if (headingInput) headingInput.addEventListener('input', updateCriteriaUnsaved);

    // helpers to create/insert/remove rows
    const createRow = (desc = '', pct = '') => {
      const div = document.createElement('div');
      div.className = 'criteria-item sub-item';
      div.setAttribute('role', 'listitem');
      const d = document.createElement('input');
      d.type = 'text'; d.className = 'criteria-desc-input'; d.placeholder = 'e.g., Midterm Exam'; d.value = desc;
      const p = document.createElement('input');
      p.type = 'text'; p.className = 'criteria-percent-input'; p.placeholder = '20%'; p.value = pct;
      div.appendChild(d); div.appendChild(p);
  // announce to screen readers when a row is created (aria-live)
  const live = document.getElementById('criteria-aria-live');
  if (live) live.textContent = 'Criterion added';
      return div;
    };

    const focusDesc = (item) => {
      const el = item?.querySelector('.criteria-desc-input');
      if (el) el.focus();
    };

    const focusPreviousOrHeading = (item) => {
      const prev = item?.previousElementSibling;
      if (prev && prev.classList && prev.classList.contains('criteria-item')) {
        // prefer focusing desc of previous sub-item
        const d = prev.querySelector('.criteria-desc-input');
        if (d) return d.focus();
      }
      // otherwise focus the column heading
      const col = list.closest('.col-6');
      const heading = col ? col.querySelector('.criteria-heading-input') : null;
      if (heading) heading.focus();
    };

    // Delegate input events for serialization
    list.addEventListener('input', debouncedSerialize);

    // Single delegated keydown handler for keyboard UX
    list.addEventListener('keydown', (e) => {
      const el = e.target;
      if (!el || !el.classList) return;

      // ENTER: append a new row after current item and focus its description input
      if (e.key === 'Enter') {
        if (el.classList.contains('criteria-desc-input') || el.classList.contains('criteria-percent-input')) {
          e.preventDefault();
          const currentItem = el.closest('.criteria-item');
          const newRow = createRow();
          if (currentItem && currentItem.parentElement) currentItem.parentElement.insertBefore(newRow, currentItem.nextSibling);
          else list.appendChild(newRow);
          // focus the new row's description for fast entry
          focusDesc(newRow);
          const live = document.getElementById('criteria-aria-live'); if (live) live.textContent = 'Criterion added';
          debouncedSerialize();
        }
      }

      // BACKSPACE: remove empty sub-item when caret at start and focus previous or heading
      if (e.key === 'Backspace') {
        if (el.classList.contains('criteria-desc-input') || el.classList.contains('criteria-percent-input')) {
          const val = el.value ?? '';
          const selStart = (typeof el.selectionStart === 'number') ? el.selectionStart : 0;
          if (val.trim() === '' && selStart === 0) {
            const item = el.closest('.criteria-item');
            if (item && item.classList.contains('sub-item')) {
              e.preventDefault();
              const prevSibling = item.previousElementSibling;
              item.remove();
                const live = document.getElementById('criteria-aria-live'); if (live) live.textContent = 'Criterion removed';
              debouncedSerialize();
              if (prevSibling && prevSibling.classList.contains('criteria-item')) {
                focusDesc(prevSibling);
              } else {
                // fallback to column heading
                const col = list.closest('.col-6');
                const heading = col ? col.querySelector('.criteria-heading-input') : null;
                if (heading) heading.focus();
              }
            }
          }
        }
      }
    });

    // on init, ensure there's at least one empty sub-item to start with for convenience
    const existingSub = list.querySelector('.criteria-item.sub-item');
    if (!existingSub) {
      const starter = createRow();
      list.appendChild(starter);
    }

    // Run an initial serialize to sync the hidden textarea and set unsaved state correctly
    serialize();
  });
}

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

  // Ensure criteria editable lists are bound (serialize into hidden textareas)
  bindCriteriaEditable();

  // When Enter is pressed on the heading input (Lecture/Laboratory), append a new sub-item
  const headingInputs = document.querySelectorAll('.criteria-heading-input');
  headingInputs.forEach((hi) => {
    hi.addEventListener('keydown', (e) => {
      if (e.key !== 'Enter') return;
      e.preventDefault();
      // find the nearest criteria-list in the same column
      let list = hi.nextElementSibling;
      if (!list || !list.classList || !list.classList.contains('criteria-list')) {
        const col = hi.closest('.col-6');
        list = col ? col.querySelector('.criteria-list') : null;
      }
      if (!list) return;

      const newItem = document.createElement('div');
      newItem.className = 'criteria-item sub-item';
      newItem.innerHTML = '<input type="text" class="criteria-desc-input" placeholder="e.g., Midterm Exam">' +
                          '<input type="text" class="criteria-percent-input" placeholder="20%">';
      // append and focus
      list.appendChild(newItem);
      const d = newItem.querySelector('.criteria-desc-input');
      if (d) d.focus();
      list.dispatchEvent(new Event('input', { bubbles: true }));
    });
    try { hi.dataset.enterBound = '1'; } catch (err) {}
  });

  

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

      const missionEl = document.querySelector('[name="mission"]');
      const visionEl = document.querySelector('[name="vision"]');
      if (!missionEl || !visionEl) return;

      const originalHtml = saveBtn.innerHTML;
      saveBtn.disabled = true;
      saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';

      try {
        const action = form.action;
        const tokenEl = form.querySelector('input[name="_token"]');
        const token = tokenEl ? tokenEl.value : '';

          const fd = new FormData();
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
            'contact_hours_lec','contact_hours_lab'
          ];
          extraFields.forEach((name) => {
            const el = form.querySelector(`[name="${name}"]`);
            if (!el) return;
            fd.append(name, el.value ?? '');
          });

          // Ensure all criteria lists serialize synchronously before saving
          document.querySelectorAll('.criteria-list').forEach((l) => {
            try {
              if (typeof l._serialize === 'function') l._serialize();
            } catch (err) {
              console.warn('criteria list serialize failed for', l, err);
            }
          });
          // Include any form fields whose name starts with "criteria_" (dynamic columns)
          const criteriaEls = form.querySelectorAll('[name^="criteria_"]');
          criteriaEls.forEach((el) => {
            if (!el.name) return;
            fd.append(el.name, el.value ?? '');
          });

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

        // Also mark criteria module fields as saved: update their data-original and hide the criteria badge
        try {
          const critEls = form.querySelectorAll('[name^="criteria_"]');
          critEls.forEach((el) => {
            el.dataset.original = el.value ?? '';
            el.classList.remove('sv-new-highlight');
          });
          const criteriaBadge = document.getElementById('unsaved-criteria');
          if (criteriaBadge) criteriaBadge.classList.add('d-none');
          updateUnsavedCount();
        } catch (err) {
          console.warn('Failed to sync criteria fields after save', err);
        }

  isDirty = false;

        // show lightweight toast
        const toast = document.getElementById('svToast');
        if (toast) {
          toast.textContent = 'Saved';
          toast.classList.add('show');
          setTimeout(() => toast.classList.remove('show'), 1600);
        }

        // brief success feedback on button
        saveBtn.classList.add('btn-success');
        saveBtn.innerHTML = '<i class="bi bi-check-lg"></i> Saved';
        setTimeout(() => {
          saveBtn.classList.remove('btn-success');
          saveBtn.innerHTML = originalHtml;
        }, 900);

      } catch (err) {
  console.error('Syllabus save failed:', err);
  // show a clearer alert including error message to help debugging
  alert('Failed to save. ' + (err && err.message ? err.message : 'See console for details.'));
  // restore button text to original so user can retry
  try { saveBtn.innerHTML = originalHtml; } catch (e) { console.warn(e); }
      } finally {
  // Always ensure the button is re-enabled so user can retry
  try { saveBtn.disabled = false; } catch (e) { console.warn('Could not re-enable save button', e); }
      }
    });
  }

  // Partial-save buttons removed; top Save button is the single source of truth for saving
});

// Optional exports
export { trackFormChanges, setupExitConfirmation, bindUnsavedIndicator, recalcCreditHours, initAutosize };
