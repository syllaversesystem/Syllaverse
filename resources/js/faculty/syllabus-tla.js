// -----------------------------------------------------------------------------
// File: resources/js/faculty/syllabus-tla.js
// Description: Handles add, edit, and delete (with DB sync) of TLA rows – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-28] Added delete-to-DB functionality via DELETE /tla/{id} with AJAX.
// -----------------------------------------------------------------------------

document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('#syllabusForm');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
  const tlaTable = document.querySelector('#tlaTable');
  const tlaBody = tlaTable?.querySelector('tbody');
  const addRowBtn = document.getElementById('add-tla-row');

  if (!form || !csrfToken || !tlaBody || !addRowBtn) return;

  // ➕ Add row + insert into DB immediately
  async function addTlaRow() {
    const syllabusId = form.action.split('/').pop();

    try {
      const res = await fetch(`/faculty/syllabi/${syllabusId}/tla/append`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
        },
      });

      const result = await res.json();

      if (result.success) {
        const firstRow = tlaBody.querySelector('tr');
        if (!firstRow) return;

        const newRow = firstRow.cloneNode(true);
        newRow.querySelectorAll('input').forEach(input => input.value = '');

        // Clear any hidden TLA ID for new rows
        const idInput = newRow.querySelector('.tla-id-field');
        if (idInput) idInput.value = '';
        const deleteBtn = newRow.querySelector('.remove-tla-row');
        if (deleteBtn) deleteBtn.setAttribute('data-id', '');

        tlaBody.appendChild(newRow);
        updateTlaIndices();

        newRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
        newRow.classList.add('tla-new-row');
        setTimeout(() => newRow.classList.remove('tla-new-row'), 1000);
      } else {
        alert('❌ Failed to add TLA row.');
      }
    } catch (err) {
      console.error('TLA row add error:', err);
      alert('❌ Error adding TLA row to database.');
    }
  }

  // 💾 Save all TLA rows
  async function saveTlaRows(event) {
    event.preventDefault();

    const syllabusId = form.action.split('/').pop();
    const rows = Array.from(tlaBody.querySelectorAll('tr'));

    const tlaData = rows.map((row) => ({
      ch: row.querySelector('[name*="[ch]"]')?.value ?? '',
      topic: row.querySelector('[name*="[topic]"]')?.value ?? '',
      wks: row.querySelector('[name*="[wks]"]')?.value ?? '',
      outcomes: row.querySelector('[name*="[outcomes]"]')?.value ?? '',
      ilo: row.querySelector('[name*="[ilo]"]')?.value ?? '',
      so: row.querySelector('[name*="[so]"]')?.value ?? '',
      delivery: row.querySelector('[name*="[delivery]"]')?.value ?? '',
    }));

    try {
      const res = await fetch(`/faculty/syllabi/${syllabusId}/tla`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
        },
        body: JSON.stringify({ tla: tlaData }),
      });

      const result = await res.json();

      if (result.success) {
        alert('✅ TLA rows saved successfully.');
      } else {
        alert('❌ Failed to save TLA rows.');
      }
    } catch (err) {
      console.error('TLA save error:', err);
      alert('❌ An error occurred while saving TLA rows.');
    }
  }

  // 🔁 Renumber input names
  function updateTlaIndices() {
    const rows = tlaBody.querySelectorAll('tr');
    rows.forEach((row, index) => {
      row.querySelectorAll('input').forEach(input => {
        const name = input.getAttribute('name');
        if (name) {
          input.setAttribute('name', name.replace(/tla\[\d+\]/, `tla[${index}]`));
        }
      });

      const deleteBtn = row.querySelector('.remove-tla-row');
      if (deleteBtn) {
        deleteBtn.style.display = index === 0 ? 'none' : 'inline-block';
      }
    });
  }

  // 🗑️ Handle delete button click (frontend + DB)
  tlaBody.addEventListener('click', async function (e) {
    const deleteBtn = e.target.closest('.remove-tla-row');
    if (!deleteBtn) return;

    const row = deleteBtn.closest('tr');
    const index = [...tlaBody.children].indexOf(row);
    if (index === 0) return; // Never delete the first row

    const tlaId = deleteBtn.dataset.id;

    if (!tlaId) {
      // No DB record yet — just remove it
      row.remove();
      updateTlaIndices();
      return;
    }

    // Confirm delete
    if (!confirm('Are you sure you want to delete this row?')) return;

    try {
      const res = await fetch(`/faculty/syllabi/tla/${tlaId}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
        },
      });

      const result = await res.json();

      if (result.success) {
        row.remove();
        updateTlaIndices();
        alert('🗑️ TLA row deleted.');
      } else {
        alert('❌ Could not delete TLA row.');
      }
    } catch (err) {
      console.error('TLA delete error:', err);
      alert('❌ Error deleting TLA row.');
    }
  });

  addRowBtn.addEventListener('click', addTlaRow);
  form.addEventListener('submit', saveTlaRows);

  updateTlaIndices();
});
