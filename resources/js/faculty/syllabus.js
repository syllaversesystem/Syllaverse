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
            // Textareas may contain line breaks; ensure value is sent
            fd.append(name, el.value ?? '');
          });

        const res = await fetch(action, {
          method: 'POST',
          credentials: 'same-origin',
          body: fd,
        });

  if (!res.ok) throw new Error('Server error: ' + res.status);

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
        console.error(err);
        alert('Failed to save mission & vision. See console for details.');
        saveBtn.innerHTML = originalHtml;
      } finally {
        saveBtn.disabled = false;
      }
    });
  }
});

// Optional exports
export { trackFormChanges, setupExitConfirmation, bindUnsavedIndicator, recalcCreditHours, initAutosize };
