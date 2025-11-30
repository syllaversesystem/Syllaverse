/**
 * File: resources/js/faculty/syllabus-mission-vision.js
 * Description: Local save handler for Mission & Vision section
 */

(function(){
  function getForm() {
    return document.getElementById('syllabusForm')
        || document.querySelector('form#syllabusForm')
        || document.querySelector('form[name="syllabusForm"]')
        || document.querySelector('form[data-role="syllabus-form"]')
        || (document.querySelector('.syllabus-doc') ? document.querySelector('.syllabus-doc form') : null);
  }
  function getCsrfToken(form) {
    const tokenEl = form ? form.querySelector('input[name="_token"]') : null;
    if (tokenEl) return tokenEl.value || '';
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? (meta.getAttribute('content') || '') : '';
  }
  function getAction(form) {
    let action = form && form.action ? form.action : '';
    if (action && action.trim() !== '') return action;
    const docEl = document.getElementById('syllabus-document');
    const syllabusId = docEl && docEl.dataset ? docEl.dataset.syllabusId : null;
    if (syllabusId) action = `/faculty/syllabi/${syllabusId}`;
    return action;
  }

  // Expose a global helper the toolbar can call
  window.saveMissionVision = async function(showAlert = false) {
    const missionEl = document.getElementById('mission-text') || document.querySelector('[name="mission"]');
    const visionEl = document.getElementById('vision-text') || document.querySelector('[name="vision"]');
    if (!missionEl || !visionEl) {
      const msg = 'Mission/Vision fields not found';
      if (showAlert) try { alert(msg); } catch (e) {}
      throw new Error(msg);
    }
    const form = getForm();
    const action = getAction(form);
    const token = getCsrfToken(form);
    if (!action || action.trim() === '') {
      const msg = 'Save endpoint not found';
      if (showAlert) try { alert(msg); } catch (e) {}
      throw new Error(msg);
    }
    if (!token) {
      const msg = 'CSRF token missing';
      if (showAlert) try { alert(msg); } catch (e) {}
      throw new Error(msg);
    }

    const fd = new FormData();
    fd.append('_token', token);
    fd.append('_method', 'PUT');
    fd.append('mission', missionEl.value || '');
    fd.append('vision', visionEl.value || '');

    const res = await fetch(action, { method: 'POST', credentials: 'same-origin', body: fd });
    if (!res.ok) {
      let msg = res.status + ' ' + res.statusText;
      try {
        const ct = res.headers.get('content-type') || '';
        if (ct.includes('application/json')) {
          const j = await res.json();
          if (j.errors) {
            const k = Object.keys(j.errors)[0];
            const m = Array.isArray(j.errors[k]) ? j.errors[k][0] : j.errors[k];
            msg = 'Validation failed: ' + m;
          } else if (j.message) { msg = j.message; }
        } else {
          msg = await res.text();
        }
      } catch (e) {}
      if (showAlert) try { alert('Failed to save Mission/Vision: ' + msg); } catch (e) {}
      throw new Error(msg);
    }

    // Success: update unsaved indicators and originals
    try {
      const mvBadges = ['unsaved-mission','unsaved-vision'];
      mvBadges.forEach(id => { const b = document.getElementById(id); if (b) b.classList.add('d-none'); });
      missionEl.dataset.original = missionEl.value || '';
      visionEl.dataset.original = visionEl.value || '';
      missionEl.classList.remove('sv-new-highlight');
      visionEl.classList.remove('sv-new-highlight');
      if (window.updateUnsavedCount) window.updateUnsavedCount();
    } catch (e) {}
  };

  // Optional: hook up local button if present (non-blocking)
  document.addEventListener('DOMContentLoaded', function(){
    try {
      const btn = document.getElementById('saveMissionVisionBtn');
      const statusEl = document.getElementById('saveMissionVisionStatus');
      if (!btn) return;
      btn.addEventListener('click', async function(){
        const prev = btn.innerHTML;
        try {
          btn.disabled = true;
          btn.innerHTML = '<i class="bi bi-arrow-repeat" style="animation: spin 1s linear infinite;"></i>';
          if (statusEl) statusEl.textContent = 'Saving…';
          await window.saveMissionVision(true);
          if (statusEl) statusEl.textContent = 'Saved';
          btn.innerHTML = '<i class="bi bi-check-lg"></i>';
        } catch (err) {
          console.error('Mission/Vision save failed:', err);
          if (statusEl) statusEl.textContent = 'Failed: ' + (err && err.message ? err.message : 'See console');
        } finally {
          setTimeout(() => { btn.innerHTML = prev; btn.disabled = false; if (statusEl) statusEl.textContent = ''; }, 900);
        }
      });
    } catch (e) {}
  });
})();
