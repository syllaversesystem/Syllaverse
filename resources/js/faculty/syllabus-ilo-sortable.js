// -----------------------------------------------------------------------------
// File: resources/js/faculty/syllabus-ilo-sortable.js
// Description: Enables drag-reorder, auto-code update, and inline add/delete for CIS-style ILO layout – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Adapted to match CIS-style two-column layout with <th> and textarea in <td>.
// [2025-07-29] Synced sortable logic with dynamic field naming and hidden ILO ID tracking.
// [2025-07-29] Referenced SO sortable logic for consistency and added structured payload.
// [2025-07-29] FIXED: ILO codes now always update correctly after drag/add/delete.
// -----------------------------------------------------------------------------

import Sortable from 'sortablejs';

document.addEventListener('DOMContentLoaded', () => {
  const list = document.getElementById('syllabus-ilo-sortable');
  const saveBtn = document.getElementById('save-syllabus-ilo-order');
  const addBtn = document.getElementById('add-ilo-row');

  if (!list || !saveBtn || !addBtn) return;

  // 🔁 Re-number all ILO codes visibly and in hidden inputs
  function updateVisibleCodes() {
    const rows = list.querySelectorAll('tr[data-id]');
    rows.forEach((row, index) => {
      const newCode = `ILO${index + 1}`;

      // Update visible display (in <th> or first <td>)
      let codeCell = row.querySelector('th');
      if (!codeCell) {
        const tds = row.querySelectorAll('td');
        if (tds.length > 0) codeCell = tds[0];
      }
      if (codeCell) codeCell.textContent = newCode;

      // Update hidden <input name="code[]">
      const codeInput = row.querySelector('input[name="code[]"]');
      if (codeInput) codeInput.value = newCode;
    });
  }

  // 🧲 Enable sortable functionality
  Sortable.create(list, {
    handle: '.drag-handle',
    animation: 150,
    fallbackOnBody: true,
    draggable: 'tr',
    swapThreshold: 0.65,
    onEnd: updateVisibleCodes
  });

  // 💾 Save button logic (AJAX reorder)
  saveBtn.addEventListener('click', () => {
    const syllabusId = list.getAttribute('data-syllabus-id');
    const rows = list.querySelectorAll('tr[data-id]');

    const ordered = Array.from(rows).map((row, index) => {
      return {
        id: row.getAttribute('data-id'),
        code: row.querySelector('input[name="code[]"]')?.value,
        description: row.querySelector('textarea[name="ilos[]"]')?.value,
        position: index + 1
      };
    }).filter(item => item.id && !item.id.startsWith('new-'));

    fetch(`/faculty/syllabi/reorder/ilo`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ syllabus_id: syllabusId, positions: ordered })
    })
    .then(res => res.json())
    .then(data => {
      alert(data.message || 'ILO order saved.');
      location.reload();
    })
    .catch(err => {
      console.error(err);
      alert('Failed to save ILO order.');
    });
  });

  // ➕ Add a new ILO row
  addBtn.addEventListener('click', () => {
    const timestamp = Date.now();
    const newRow = document.createElement('tr');
    newRow.setAttribute('data-id', `new-${timestamp}`);

    newRow.innerHTML = `
      <th class="align-top text-start"></th>
      <td>
        <div class="d-flex gap-2 align-items-start">
          <span class="drag-handle text-muted pt-1">
            <i class="bi bi-grip-vertical"></i>
          </span>
          <textarea name="ilos[]" class="form-control border-0 p-0 bg-transparent" style="min-height: 60px; flex: 1;"></textarea>
          <input type="hidden" name="code[]" value="">
          <button type="button" class="btn btn-sm btn-outline-danger btn-delete-ilo" title="Delete ILO">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </td>
    `;

    list.appendChild(newRow);
    updateVisibleCodes();
  });

  // 🗑️ Delete ILO row (temporary or from DB)
  list.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-delete-ilo');
    if (!btn) return;

    const row = btn.closest('tr');
    const id = row.getAttribute('data-id');

    if (!id || id.startsWith('new-')) {
      row.remove();
      updateVisibleCodes();
      return;
    }

    if (!confirm('Are you sure you want to delete this ILO?')) return;

    fetch(`/faculty/syllabi/ilos/${id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
      }
    })
    .then(res => res.json())
    .then(data => {
      alert(data.message || 'ILO deleted.');
      location.reload();
    })
    .catch(err => {
      console.error(err);
      alert('Failed to delete ILO.');
    });
  });

  // ✅ Initialize code labels on load
  updateVisibleCodes();
});
