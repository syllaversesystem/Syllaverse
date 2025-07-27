// File: resources/js/faculty/syllabus-so.js
// Description: Handles AJAX save for Student Outcomes (SO) – Syllaverse

document.addEventListener('DOMContentLoaded', () => {
  const soForm = document.querySelector('#soForm');
  if (!soForm) return;

  soForm.addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = new FormData(soForm);
    const url = soForm.getAttribute('action');
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json'
        },
        body: formData
      });

      if (response.ok) {
        alert('✅ SOs updated successfully.');
      } else {
        const data = await response.json();
        alert('❌ Failed to update SOs:\n' + (data.message || 'Unknown error'));
      }
    } catch (error) {
      console.error('SO Save Error:', error);
      alert('❌ Error while saving SOs.');
    }
  });
});
