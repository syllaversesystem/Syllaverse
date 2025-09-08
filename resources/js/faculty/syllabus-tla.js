// -----------------------------------------------------------------------------
// File: resources/js/faculty/syllabus-tla.js
// Description: Handles add, edit, and delete (with DB sync) of TLA rows – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-28] Added delete-to-DB functionality via DELETE /tla/{id} with AJAX.
// [2025-07-29] Fixed addTlaRow to clear mapped ILO/SO displays and assign correct tla_id.
// -----------------------------------------------------------------------------

document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('#syllabusForm');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
  const tlaTable = document.querySelector('#tlaTable');
  const tlaBody = tlaTable?.querySelector('tbody');
  const addRowBtn = document.getElementById('add-tla-row');

  // require at least the form and the TLA table body; csrfToken and add button are optional
  if (!form || !tlaBody) return;

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

        // Clear ILO/SO visual displays
        const iloDisplay = newRow.querySelector('.ilo-mapped-codes');
        if (iloDisplay) iloDisplay.textContent = '';

        const soDisplay = newRow.querySelector('.so-mapped-codes');
        if (soDisplay) soDisplay.textContent = '';

        // Clear modal button data-tlaid
        newRow.querySelectorAll('.map-ilo-btn, .map-so-btn').forEach(btn => {
          btn.dataset.tlaid = '';
        });

        // 🔗 Assign actual new TLA ID from backend so it can be deleted later
        if (result.row?.id) {
          if (idInput) idInput.setAttribute('value', result.row.id);
          const deleteBtn = newRow.querySelector('.remove-tla-row');
          if (deleteBtn) deleteBtn.setAttribute('data-id', result.row.id);

          newRow.querySelectorAll('.map-ilo-btn, .map-so-btn').forEach(btn => {
            btn.dataset.tlaid = result.row.id;
          });
        }

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
    // If this submit was already triggered programmatically after TLA save, allow normal submit
    if (window._syllabusSubmitting) return;

    // Prevent the default full-form submit so we can save TLA first via AJAX
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
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
        },
        body: JSON.stringify({ tla: tlaData }),
      });

      if (!res.ok) {
        const text = await res.text().catch(() => '');
        throw new Error('Server returned ' + res.status + ' ' + text);
      }

      const result = await res.json().catch(() => ({}));

      if (result.success) {
        // Mark that we're about to submit the full form so this handler won't intercept again
        try { window._syllabusSubmitting = true; } catch (e) { /* noop */ }
        // Continue with the original form submission to persist non-TLA fields
        form.submit();
      } else {
        console.error('TLA save response:', result);
        alert('❌ Failed to save TLA rows. Fix errors and try again.');
      }
    } catch (err) {
      console.error('TLA save error:', err);
      alert('❌ An error occurred while saving TLA rows. See console for details.');
    }
  }

  // 🔁 Renumber input names
  function updateTlaIndices() {
    const rows = tlaBody.querySelectorAll('tr');
    rows.forEach((row, index) => {
      row.querySelectorAll('input, textarea').forEach(input => {
        const name = input.getAttribute('name');
        if (name) {
          // Replace either tla[] or tla[123] with tla[index]
          // Normalize template names (tla[]...) or existing indexed names
          const newName = name.replace(/tla\[\d*\]|tla\[\]/, `tla[${index}]`);
          input.setAttribute('name', newName);
          // If this is a position hidden field, update its value to the index
          if (input.classList.contains('tla-position-field')) {
            try { input.value = index; } catch (e) {}
          }
          // If this is an id hidden field and has no value, ensure it's empty string
          if (input.classList.contains('tla-id-field') && !input.value) {
            input.value = '';
          }
        }
      });

  const deleteBtn = row.querySelector('.remove-tla-row');
      if (deleteBtn) {
        deleteBtn.style.display = index === 0 ? 'none' : 'inline-block';
      }
    });
  }

  // ➕ Client-side fast add (no server) — clones first row, clears values, updates indices
  function addTlaRowClient() {
    const firstRow = tlaBody.querySelector('tr');
    if (!firstRow) return;

    const newRow = firstRow.cloneNode(true);

    // Clear inputs and textareas
    newRow.querySelectorAll('input, textarea').forEach(el => {
      if (el.type === 'hidden') el.value = '';
      else el.value = '';
    });

    // Clear any mapped displays
    const iloDisplay = newRow.querySelector('.ilo-mapped-codes');
    if (iloDisplay) iloDisplay.textContent = '';
    const soDisplay = newRow.querySelector('.so-mapped-codes');
    if (soDisplay) soDisplay.textContent = '';

    // Clear data attributes on mapping buttons if present
    newRow.querySelectorAll('.map-ilo-btn, .map-so-btn').forEach(btn => {
      if (btn.dataset) btn.dataset.tlaid = '';
    });

    tlaBody.appendChild(newRow);
    updateTlaIndices();
    newRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
    newRow.classList.add('tla-new-row');
    setTimeout(() => newRow.classList.remove('tla-new-row'), 900);

    // Focus the new row's sensible editing field (Topics textarea if present)
    setTimeout(() => {
      focusRowDefaultField(newRow);
    }, 50);
  }

  // 🗑️ Handle delete button click (frontend + DB)
  tlaBody.addEventListener('click', async function (e) {
    const deleteBtn = e.target.closest('.remove-tla-row');
    if (!deleteBtn) return;

    const row = deleteBtn.closest('tr');
    const index = [...tlaBody.children].indexOf(row);
    if (index === 0) return; // Never delete the first row

    const tlaId = deleteBtn.dataset.id;

    // Determine focused column index if any (to preserve focus after removal)
    let colIndex = -1;
    const active = document.activeElement;
    if (active && row.contains(active) && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA')) {
      const els = Array.from(row.querySelectorAll('input,textarea'));
      colIndex = Math.max(0, els.indexOf(active));
    }

    if (!tlaId) {
      // No DB record yet — just remove it (use helper to restore focus)
      removeTlaRowClient(row, colIndex);
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
        // Use remove helper to restore focus appropriately
        removeTlaRowClient(row, colIndex);
        alert('🗑️ TLA row deleted.');
      } else {
        alert('❌ Could not delete TLA row.');
      }
    } catch (err) {
      console.error('TLA delete error:', err);
      alert('❌ Error deleting TLA row.');
    }
  });

  if (addRowBtn) addRowBtn.addEventListener('click', addTlaRow);
  form.addEventListener('submit', saveTlaRows);

  // ➕ Ctrl/Cmd + Enter inside any TLA input/textarea => add new TLA row (client-side)
  tlaBody.addEventListener('keydown', function (e) {
    const target = e.target;
    if (!target) return;
    const isInput = target.tagName === 'TEXTAREA' || target.tagName === 'INPUT';
    if (!isInput) return;

    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
      e.preventDefault();
      addTlaRowClient();
    }
  });

  // Fallback: Listen on document in case delegated listener couldn't catch events
  document.addEventListener('keydown', function (e) {
  if (!((e.ctrlKey || e.metaKey) && e.key === 'Enter')) return;
  // if a delegated handler already handled this event, don't run again
  if (e.defaultPrevented) return;
    const active = document.activeElement;
    if (!active) return;
    // Only trigger when focus is inside the TLA table
    if (tlaTable && tlaTable.contains(active)) {
      e.preventDefault();
      addTlaRowClient();
    }
  });

  // Focus helpers
  function focusElementAtEnd(el) {
    if (!el) return;
    try {
      el.focus();
      if (typeof el.selectionStart === 'number') {
        const len = (el.value || '').length;
        el.setSelectionRange(len, len);
      }
    } catch (err) {
      // ignore
    }
  }

  function focusRowDefaultField(row) {
    if (!row) return;
    // Prefer the topic textarea
    const topic = row.querySelector('textarea[name*="[topic]"]');
    if (topic) {
      focusElementAtEnd(topic);
      return;
    }
    const first = row.querySelector('input,textarea');
    if (first) focusElementAtEnd(first);
  }

  // Remove a TLA row client-side and restore focus to a logical cell
  function removeTlaRowClient(row, colIndex) {
    if (!row) return;
    // Never remove the first row
    const rowsBefore = Array.from(tlaBody.querySelectorAll('tr'));
    const index = rowsBefore.indexOf(row);
    if (index <= 0) return;

    // Remove row from DOM
    row.remove();
    updateTlaIndices();

    // Determine target row to focus: try the row that now occupies the same index
    const rows = Array.from(tlaBody.querySelectorAll('tr'));
    let targetRow = rows[index]; // next row that shifted into this position
    if (!targetRow) targetRow = rows[index - 1]; // fallback to previous
    if (!targetRow) return;

    // If caller provided a column index, try to focus the same column's input/textarea
    if (typeof colIndex === 'number' && colIndex >= 0) {
      const els = Array.from(targetRow.querySelectorAll('input,textarea'));
      if (els[colIndex]) {
        focusElementAtEnd(els[colIndex]);
        return;
      }
    }

    // Otherwise focus the default field for the row
    focusRowDefaultField(targetRow);
  }

  // Expose a helper so the top Save flow can persist TLA rows before the main form
  // Usage: await window.postTlaRows(syllabusId)
  window.postTlaRows = async function(syllabusId) {
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

    const res = await fetch(`/faculty/syllabi/${syllabusId}/tla`, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
      },
      body: JSON.stringify({ tla: tlaData }),
    });

    if (!res.ok) {
      const text = await res.text().catch(() => '');
      const err = new Error('Server returned ' + res.status + ' ' + text);
      err.status = res.status;
      throw err;
    }

    const result = await res.json().catch(() => ({}));
    if (!result.success) {
      const err = new Error('Failed to save TLA rows');
      err.response = result;
      throw err;
    }
    return result;
  };

  // Ctrl/Cmd + Backspace: remove the focused TLA row (client-side)
  document.addEventListener('keydown', function (e) {
  if (!((e.ctrlKey || e.metaKey) && e.key === 'Backspace')) return;
    const active = document.activeElement;
    if (!active) return;
    if (tlaTable && tlaTable.contains(active)) {
      e.preventDefault();
      const row = active.closest('tr');
      // determine column index in the row
      let colIndex = -1;
      if (row) {
        const els = Array.from(row.querySelectorAll('input,textarea'));
        colIndex = Math.max(0, els.indexOf(active));
      }
      removeTlaRowClient(row, colIndex);
    }
  });

  // Backspace on empty input/textarea at caret start -> remove the row
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Backspace') return;
    // don't handle when Ctrl/Cmd is pressed (handled by Ctrl/Cmd+Backspace)
    if (e.ctrlKey || e.metaKey) return;
    const active = document.activeElement;
    if (!active) return;
    if (!(active.tagName === 'INPUT' || active.tagName === 'TEXTAREA')) return;
    if (!tlaTable || !tlaTable.contains(active)) return;

    try {
      const val = active.value || '';
      const start = typeof active.selectionStart === 'number' ? active.selectionStart : 0;
      const end = typeof active.selectionEnd === 'number' ? active.selectionEnd : 0;

      // If field is empty OR caret is at start with no selection, remove the row
      if (val.trim() === '' || (start === 0 && end === 0)) {
        const row = active.closest('tr');
        // Don't remove the first (header/template) row
        const rows = Array.from(tlaBody.querySelectorAll('tr'));
        const index = rows.indexOf(row);
        if (index <= 0) return; // keep first row
        e.preventDefault();
        // determine column index to preserve focus
        const els = Array.from(row.querySelectorAll('input,textarea'));
        const colIndex = Math.max(0, els.indexOf(active));
        removeTlaRowClient(row, colIndex);
      }
    } catch (err) {
      // ignore selection access errors
    }
  });

  updateTlaIndices();
});
