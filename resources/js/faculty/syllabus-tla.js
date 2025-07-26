// File: resources/js/faculty/syllabus-tla.js
// Description: Handles dynamic add/remove of TLA rows and AJAX submission – Syllaverse

document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('#syllabusForm');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
  const tlaTable = document.querySelector('#tlaTable');
  const tlaBody = tlaTable?.querySelector('tbody');
  const addRowBtn = document.getElementById('add-tla-row');

  if (!form || !csrfToken || !tlaBody) return;

  // --- Helper: Update Input Names by Index ---
  function updateRowIndices() {
    const rows = tlaBody.querySelectorAll('tr');
    rows.forEach((row, index) => {
      row.querySelectorAll('input').forEach(input => {
        const name = input.getAttribute('name');
        const updatedName = name.replace(/tla\[\d+\]/, `tla[${index}]`);
        input.setAttribute('name', updatedName);
      });

      const deleteBtn = row.querySelector('.remove-tla-row');
      if (deleteBtn) {
        deleteBtn.style.display = index === 0 ? 'none' : 'inline-block';
      }
    });
  }

  // --- Add Row ---
  if (addRowBtn) {
    addRowBtn.addEventListener('click', () => {
      const firstRow = tlaBody.querySelector('tr');
      if (!firstRow) return;

      const newRow = firstRow.cloneNode(true);

      newRow.querySelectorAll('input').forEach(input => {
        input.value = '';
      });

      tlaBody.appendChild(newRow);
      updateRowIndices();
    });
  }

  // --- Remove Row ---
  tlaBody.addEventListener('click', (e) => {
    if (e.target.closest('.remove-tla-row')) {
      const row = e.target.closest('tr');
      const index = [...tlaBody.children].indexOf(row);

      if (index > 0) {
        row.remove();
        updateRowIndices();
      }
    }
  });

  updateRowIndices(); // Initial load

  // --- AJAX Submit ---
  form.addEventListener('submit', async function (e) {
    e.preventDefault(); // Prevent full form submission first

    const syllabusId = form.action.split('/').pop();
    const tlaRows = Array.from(tlaBody.querySelectorAll('tr')).map((row) => {
      return {
        ch: row.querySelector('[name*="[ch]"]')?.value ?? '',
        topic: row.querySelector('[name*="[topic]"]')?.value ?? '',
        wks: row.querySelector('[name*="[wks]"]')?.value ?? '',
        outcomes: row.querySelector('[name*="[outcomes]"]')?.value ?? '',
        ilo: row.querySelector('[name*="[ilo]"]')?.value ?? '',
        so: row.querySelector('[name*="[so]"]')?.value ?? '',
        delivery: row.querySelector('[name*="[delivery]"]')?.value ?? '',
      };
    });

    try {
      const response = await fetch(`/faculty/syllabi/${syllabusId}/tla`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ tla: tlaRows }),
      });

      const result = await response.json();

      if (result.success) {
        form.submit(); // Continue full form submit after TLA AJAX
      } else {
        alert('❌ Failed to update TLA rows.');
      }
    } catch (error) {
      console.error('TLA Update Error:', error);
      alert('❌ An error occurred while saving TLA data.');
    }
  });
});
