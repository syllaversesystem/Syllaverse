// -----------------------------------------------------------------------------
// File: resources/js/faculty/syllabus-so-sortable.js
// Description: Enables drag-reorder, auto-code update, and inline add/delete for CIS-style SO layout – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Adapted from ILO sortable – added auto-code, add/delete, and save order.
// -----------------------------------------------------------------------------

import Sortable from 'sortablejs';

document.addEventListener('DOMContentLoaded', () => {
  const list = document.getElementById('syllabus-so-sortable');
  const saveBtn = document.getElementById('save-syllabus-so-order');
  const addBtn = document.getElementById('add-so-row');

  if (!list || !saveBtn || !addBtn) return;

  function updateVisibleCodes() {
    const rows = list.querySelectorAll('tr[data-id]');
    rows.forEach((row, index) => {
      const th = row.querySelector('td:first-child');
      const hiddenCode = row.querySelector('input[name="code[]"]');
      if (th && hiddenCode) {
        const newCode = `SO${index + 1}`;
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
    const orderedIds = Array.from(rows)
      .map((row, i) => ({
        id: row.getAttribute('data-id'),
        position: i + 1
      }))
      .filter(item => !item.id.startsWith('new-'));

    fetch(`/faculty/syllabi/${syllabusId}/sos/reorder`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ positions: orderedIds })
    })
      .then(res => res.json())
      .then(data => {
        alert(data.message || 'SO order saved.');
        location.reload();
      })
      .catch(err => {
        console.error(err);
        alert('Failed to save SO order.');
      });
  });

  addBtn.addEventListener('click', () => {
    const timestamp = Date.now();
    const newRow = document.createElement('tr');
    newRow.setAttribute('data-id', `new-${timestamp}`);

    newRow.innerHTML = `
      <td class="text-center align-middle fw-bold"></td>
      <td class="text-center align-middle">
        <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;">
          <i class="bi bi-grip-vertical"></i>
        </span>
      </td>
      <td>
        <div class="d-flex align-items-start gap-2">
          <textarea name="sos[]" class="form-control border-0 p-0 bg-transparent" style="min-height: 60px; flex: 1;"></textarea>
          <input type="hidden" name="code[]" value="">
          <button type="button" class="btn btn-sm btn-outline-danger btn-delete-so mt-1" title="Delete SO">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </td>
    `;

    list.appendChild(newRow);
    updateVisibleCodes();
  });

  list.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-delete-so');
    if (!btn) return;

    const row = btn.closest('tr');
    const id = row.getAttribute('data-id');

    if (!id || id.startsWith('new-')) {
      row.remove();
      updateVisibleCodes();
      return;
    }

    if (!confirm('Are you sure you want to delete this SO?')) return;

    fetch(`/faculty/syllabi/sos/${id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
      }
    })
      .then(res => res.json())
      .then(data => {
        alert(data.message || 'SO deleted.');
        location.reload();
      })
      .catch(err => {
        console.error(err);
        alert('Failed to delete SO.');
      });
  });

  updateVisibleCodes();
});
