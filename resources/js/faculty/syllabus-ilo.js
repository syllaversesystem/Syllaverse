// -----------------------------------------------------------------------------
// File: resources/js/faculty/syllabus-ilo.js
// Description: Handles AJAX save for Intended Learning Outcomes (ILO) including sort-order – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Fixed to support saving ILO sort order via form submission.
// [2025-07-29] Synced selectors to match ilo.blade.php (code[] and ilos[]).
// -----------------------------------------------------------------------------

document.addEventListener('DOMContentLoaded', () => {
  const iloForm = document.querySelector('#iloForm');
  if (!iloForm) return;

  iloForm.addEventListener('submit', async function (e) {
    e.preventDefault();

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const url = iloForm.getAttribute('action');

    const iloRows = Array.from(iloForm.querySelectorAll('tr[data-id]'));

    const payload = iloRows.map((row, index) => {
      return {
        id: row.getAttribute('data-id'),
        code: row.querySelector('input[name="code[]"]')?.value,
        description: row.querySelector('textarea[name="ilos[]"]')?.value,
        position: index + 1
      };
    });

    try {
      const response = await fetch(url, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ ilos: payload })
      });

      const result = await response.json();

      if (response.ok && result.success) {
        alert('✅ ILOs saved and reordered successfully.');
      } else {
        alert('❌ Failed to save ILOs: ' + (result.message || 'Unknown error'));
      }
    } catch (error) {
      console.error('ILO Save Error:', error);
      alert('❌ Error while saving ILOs.');
    }
  });
});
