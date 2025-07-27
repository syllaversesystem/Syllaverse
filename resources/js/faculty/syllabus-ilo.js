// File: resources/js/faculty/syllabus-ilo.js
// Description: Handles AJAX save for Intended Learning Outcomes (ILO) – Syllaverse

document.addEventListener('DOMContentLoaded', () => {
  const iloForm = document.querySelector('#iloForm');
  if (!iloForm) return;

  iloForm.addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = new FormData(iloForm);
    const url = iloForm.getAttribute('action');
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
        alert('✅ ILOs updated successfully.');
      } else {
        const data = await response.json();
        alert('❌ Failed to update ILOs:\n' + (data.message || 'Unknown error'));
      }
    } catch (error) {
      console.error('ILO Save Error:', error);
      alert('❌ Error while saving ILOs.');
    }
  });
});
