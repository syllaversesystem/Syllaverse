// -----------------------------------------------------------------------------
// File: resources/js/faculty/syllabus-ilo-sortable.js
// Description: Enables drag-reorder, auto-code update, and inline add/delete for CIS-style ILO layout – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Adapted to match CIS-style two-column layout with <th> and textarea in <td>.
// -----------------------------------------------------------------------------

import Sortable from 'sortablejs';

document.addEventListener('DOMContentLoaded', () => {
  const list = document.getElementById('syllabus-ilo-sortable');
  const saveBtn = document.getElementById('save-syllabus-ilo-order');
  const addBtn = document.getElementById('add-ilo-row');

  if (!list || !saveBtn || !addBtn) return;

  function updateVisibleCodes() {
    const rows = list.querySelectorAll('tr[data-id]');
    rows.forEach((row, index) => {
      const th = row.querySelector('th');
      const hiddenCode = row.querySelector('input[name="code[]"]');
      if (th && hiddenCode) {
        const newCode = `ILO${index + 1}`;
        th.textContent = newCode;
        hiddenCode.value = newCode;
      }
    });
  }

  Sortable.create(list, {
    handle: '.drag-handle',
    animation: 150,
    fallbackOnBody: true,
    draggable: 'tr',
    swapThreshold: 0.65,
    onEnd: updateVisibleCodes
  });

  saveBtn.addEventListener('click', () => {
    const syllabusId = list.getAttribute('data-syllabus-id');
    const rows = list.querySelectorAll('tr[data-id]');
    const orderedIds = Array.from(rows).map(row => row.getAttribute('data-id'));

    fetch('/faculty/syllabi/reorder/ilo', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ ids: orderedIds, syllabus_id: syllabusId })
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

  updateVisibleCodes();
});
