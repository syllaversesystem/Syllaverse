// File: resources/js/faculty/syllabus-so.js
// Description: Handles AJAX save for Student Outcomes (SO) – Syllaverse

document.addEventListener('DOMContentLoaded', () => {
  const soForm = document.querySelector('#soForm');
  if (!soForm) return;

  soForm.addEventListener('submit', async function (e) {
    e.preventDefault();
    try {
      await window.saveSo();
      alert('✅ SOs updated successfully.');
    } catch (error) {
      console.error('SO Save Error:', error);
      alert('❌ Failed to update SOs:\n' + (error.message || 'Unknown error'));
    }
  });
  
  // Expose an async save function so top-level syllabus Save can await SO persistence
  window.saveSo = async function() {
    const form = document.querySelector('#soForm');
    if (!form) return { message: 'No SO form present' };
    const formData = new FormData(form);
    const url = form.getAttribute('action');
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    const headers = { 'Accept': 'application/json' };
    if (tokenMeta) headers['X-CSRF-TOKEN'] = tokenMeta.getAttribute('content');
    try {
      const resp = await fetch(url, { method: 'POST', headers, body: formData, credentials: 'same-origin' });
      if (!resp.ok) {
        let data = null;
        try { data = await resp.json(); } catch (e) { /* noop */ }
        throw new Error((data && data.message) ? data.message : ('Server returned ' + resp.status));
      }
      return await resp.json().catch(() => ({ message: 'SOs saved' }));
    } catch (err) {
      throw err;
    }
  };
});
