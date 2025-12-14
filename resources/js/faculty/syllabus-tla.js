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
  const syllabusId = (function(){
    const form = document.querySelector('#syllabusForm');
    try { return form?.action?.split('/')?.pop() || null; } catch(e){ return null; }
  })();

  // require at least the form and the TLA table body; csrfToken and add button are optional
  if (!form || !tlaBody) return;

  // ➕ Add row (persist to DB via append; fallback to frontend only)
  async function addTlaRow() {
    // Remove placeholder if it exists
    const placeholder = tlaBody.querySelector('#tla-placeholder');
    if (placeholder) {
      placeholder.remove();
    }

    let firstRow = tlaBody.querySelector('tr:not(#tla-placeholder)');
    
    // If no rows exist, create a template row (and persist via append if possible)
    if (!firstRow) {
      const templateRow = document.createElement('tr');
      templateRow.className = 'text-center align-middle';
      templateRow.innerHTML = `
        <td class="tla-ch"><input name="tla[][ch]" form="syllabusForm" class="form-control cis-input text-center" value="" placeholder="-"></td>
        <td class="tla-topic text-start"><textarea name="tla[][topic]" form="syllabusForm" class="form-control cis-textarea autosize cis-field" rows="2" placeholder="-"></textarea></td>
        <td class="tla-wks"><input name="tla[][wks]" form="syllabusForm" class="form-control cis-input text-center" value="" placeholder="-"></td>
        <td class="tla-outcomes text-start"><textarea name="tla[][outcomes]" form="syllabusForm" class="form-control cis-textarea autosize cis-field" rows="2" placeholder="-"></textarea></td>
        <td class="tla-ilo"><input name="tla[][ilo]" form="syllabusForm" class="form-control cis-input text-center" value="" placeholder="-"></td>
        <td class="tla-so"><input name="tla[][so]" form="syllabusForm" class="form-control cis-input text-center" value="" placeholder="-"></td>
        <td class="tla-delivery"><textarea name="tla[][delivery]" form="syllabusForm" class="form-control cis-textarea autosize cis-field" rows="1" placeholder="-"></textarea></td>
        <td class="tla-actions text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-tla-row" data-id="" title="Delete Row"><i class="bi bi-trash"></i></button></td>
        <input type="hidden" class="tla-id-field" name="tla[][id]" value="">
        <input type="hidden" class="tla-position-field" name="tla[][position]" value="0">
      `;
      tlaBody.appendChild(templateRow);
      
      // Re-render feather icons for the new row
      if (window.feather) window.feather.replace();
      
      updateTlaIndices();
      
      // Removed backend auto-append: keep row creation strictly frontend

      // Focus first input
      const firstInput = templateRow.querySelector('input, textarea');
      if (firstInput) setTimeout(() => firstInput.focus(), 100);

      try { rebuildTlaRealtimeContext(); } catch(e) {}
      
      // Trigger unsaved changes counter
      try {
        window.updateUnsavedCount && window.updateUnsavedCount();
      } catch (e) {}
      
      return;
    }

    const newRow = firstRow.cloneNode(true);
    
    // Clear all inputs and textareas
    newRow.querySelectorAll('input, textarea').forEach(el => {
      if (el.type === 'hidden') {
        el.value = '';
      } else if (el.tagName === 'TEXTAREA') {
        el.value = '';
      } else {
        el.value = '';
      }
    });

    // Clear any hidden TLA ID for new rows
    const idInput = newRow.querySelector('.tla-id-field');
    if (idInput) idInput.value = '';

    // Clear ILO/SO visual displays if they exist
    const iloDisplay = newRow.querySelector('.ilo-mapped-codes');
    if (iloDisplay) iloDisplay.textContent = '';

    const soDisplay = newRow.querySelector('.so-mapped-codes');
    if (soDisplay) soDisplay.textContent = '';

    // Clear modal button data-tlaid if they exist
    newRow.querySelectorAll('.map-ilo-btn, .map-so-btn').forEach(btn => {
      btn.dataset.tlaid = '';
    });

    // Clear delete button data-id
    const deleteBtn = newRow.querySelector('.remove-tla-row');
    if (deleteBtn) {
      deleteBtn.setAttribute('data-id', '');
    }

    tlaBody.appendChild(newRow);
    updateTlaIndices();

    try { rebuildTlaRealtimeContext(); } catch(e) {}
    // Removed backend auto-append: keep row creation strictly frontend

    newRow.classList.add('tla-new-row');
    setTimeout(() => newRow.classList.remove('tla-new-row'), 1000);

    // Focus first input in new row
    const firstInput = newRow.querySelector('input, textarea');
    if (firstInput) setTimeout(() => firstInput.focus(), 100);

    // Trigger unsaved changes counter if available
    try {
      window.updateUnsavedCount && window.updateUnsavedCount();
    } catch (e) {
      // Silently fail
    }
  }
  // Removed auto-append on load: do not create rows via backend automatically

  // === Realtime TLA snapshot builder (updates window._svRealtimeContext) ===
  function rebuildTlaRealtimeContext() {
    try {
      const prev = typeof window._svRealtimeContext === 'string' ? window._svRealtimeContext : '';
      // Remove any existing TLA snapshot block
      const cleaned = prev ? prev.replace(/PARTIAL_BEGIN:tla[\s\S]*?PARTIAL_END:tla/g, '').trim() : '';

      // Build deterministic snapshot from current table inputs
      const rows = Array.from(tlaBody.querySelectorAll('tr:not(#tla-placeholder)'));
      const headerTitle = 'Teaching, Learning, and Assessment (TLA) Activities';
      const columns = 'Columns: Ch. | Topics / Reading List | Wks. | Topic Outcomes | ILO | SO | Delivery Method';

      function valOrDash(el) {
        if (!el) return '-';
        const v = (el.value ?? '').toString().trim();
        return v ? v : '-';
      }

      const lines = [];
      lines.push('PARTIAL_BEGIN:tla');
      lines.push(`<!-- TLA_ROWS:${rows.length} -->`);
      lines.push(headerTitle);
      lines.push(columns);
      lines.push('');
      lines.push('| Ch. | Topics / Reading List | Wks. | Topic Outcomes | ILO | SO | Delivery Method |');
      lines.push('|:---:|:----------------------|:----:|:---------------|:---:|:--:|:-----------------|');

      rows.forEach((row) => {
        const ch = valOrDash(row.querySelector('[name*="[ch]"]'));
        const topic = valOrDash(row.querySelector('[name*="[topic]"]'));
        const wks = valOrDash(row.querySelector('[name*="[wks]"]'));
        const outcomes = valOrDash(row.querySelector('[name*="[outcomes]"]'));
        const ilo = valOrDash(row.querySelector('[name*="[ilo]"]'));
        const so = valOrDash(row.querySelector('[name*="[so]"]'));
        const delivery = valOrDash(row.querySelector('[name*="[delivery]"]'));
        lines.push(`| ${ch} | ${topic} | ${wks} | ${outcomes} | ${ilo} | ${so} | ${delivery} |`);
      });

      lines.push('PARTIAL_END:tla');

      const snapshot = lines.join('\n');
      // Append snapshot separated by two newlines if existing context
      window._svRealtimeContext = cleaned ? `${cleaned}\n\n${snapshot}` : snapshot;
    } catch (e) { /* noop */ }
  }

  // Rebuild on initial load
  document.addEventListener('DOMContentLoaded', () => {
    // Initial snapshot build
    try { rebuildTlaRealtimeContext(); } catch(e) {}
  });

  // Hook input/textarea changes within the TLA table to rebuild snapshot
  document.addEventListener('input', (e) => {
    const el = e.target;
    if (el && table.contains(el) && (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.tagName === 'SELECT')) {
      try { rebuildTlaRealtimeContext(); } catch(e) {}
    }
  });

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
  const res = await fetch(`${base}/${syllabusId}/tla`, {
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

  // 🗑️ Handle delete button click - show modal confirmation
  let rowToDelete = null;
  let deleteColIndex = -1;
  
  tlaBody.addEventListener('click', function (e) {
    const deleteBtn = e.target.closest('.remove-tla-row');
    if (!deleteBtn) return;

    const row = deleteBtn.closest('tr');
    const tlaId = deleteBtn.dataset.id;

    // Store row reference for deletion after confirmation
    rowToDelete = row;

    // Determine focused column index if any (to preserve focus after removal)
    deleteColIndex = -1;
    const active = document.activeElement;
    if (active && row.contains(active) && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA')) {
      const els = Array.from(row.querySelectorAll('input,textarea'));
      deleteColIndex = Math.max(0, els.indexOf(active));
    }

    // If no ID, it's a new unsaved row - just remove from frontend without modal
    if (!tlaId) {
      removeTlaRowClient(row, deleteColIndex);
      return;
    }

    // Show confirmation modal for saved rows
    const deleteModal = document.getElementById('deleteTlaModal');
    if (deleteModal) {
      const modal = new bootstrap.Modal(deleteModal);
      modal.show();
    }
  });

  // Handle confirm delete button in modal
  const confirmDeleteBtn = document.getElementById('confirmDeleteTla');
  if (confirmDeleteBtn) {
    confirmDeleteBtn.addEventListener('click', async function() {
      if (!rowToDelete) return;

      const tlaId = rowToDelete.querySelector('.remove-tla-row')?.dataset.id;
      if (!tlaId) return;

      const deleteModalElement = document.getElementById('deleteTlaModal');
      const deleteModal = deleteModalElement ? bootstrap.Modal.getInstance(deleteModalElement) : null;
      
      try {
        confirmDeleteBtn.disabled = true;
        confirmDeleteBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Deleting...';
        
        const res = await fetch(`/faculty/syllabi/tla/${tlaId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
          },
        });

        const result = await res.json();

        if (result.success) {
          // Close modal first
          if (deleteModal) {
            deleteModal.hide();
          }
          
          // Small delay to ensure modal closes before manipulating DOM
          setTimeout(() => {
            // Remove from frontend after successful DB deletion
            removeTlaRowClient(rowToDelete, deleteColIndex);
            
            if (window.showAlertOverlay) {
              window.showAlertOverlay('success', 'TLA row deleted successfully');
            } else {
              alert('TLA row deleted successfully');
            }
            
            // Trigger unsaved changes counter
            try {
              window.updateUnsavedCount && window.updateUnsavedCount();
            } catch (e) {}
            
            // Reset state
            rowToDelete = null;
            deleteColIndex = -1;
          }, 150);
        } else {
          throw new Error(result.message || 'Could not delete TLA row');
        }
      } catch (err) {
        console.error('TLA delete error:', err);
        
        if (window.showAlertOverlay) {
          window.showAlertOverlay('error', 'Error deleting TLA row: ' + err.message);
        } else {
          alert('Error deleting TLA row: ' + err.message);
        }
        
        // Reset state on error
        rowToDelete = null;
        deleteColIndex = -1;
      } finally {
        confirmDeleteBtn.disabled = false;
        confirmDeleteBtn.innerHTML = '<i class="bi bi-trash"></i> Delete';
      }
    });
  }

  if (addRowBtn) addRowBtn.addEventListener('click', addTlaRow);
  form.addEventListener('submit', saveTlaRows);


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
    const rowsBefore = Array.from(tlaBody.querySelectorAll('tr:not(#tla-placeholder)'));

    // Remove row from DOM
    row.remove();
    updateTlaIndices();

    // If no rows left, show placeholder
    const remainingRows = Array.from(tlaBody.querySelectorAll('tr:not(#tla-placeholder)'));
    if (remainingRows.length === 0) {
      const placeholder = document.createElement('tr');
      placeholder.id = 'tla-placeholder';
      placeholder.innerHTML = `
        <td colspan="8" class="text-center text-muted py-4">
          <p class="mb-2">No TLA activities added yet.</p>
          <p class="mb-0"><small>Click the <strong>+</strong> button above to add a TLA row.</small></p>
        </td>
      `;
      tlaBody.appendChild(placeholder);
      return;
    }

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


  updateTlaIndices();
});
