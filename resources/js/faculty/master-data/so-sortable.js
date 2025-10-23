// -------------------------------------------------------------------------------
// * File: resources/js/faculty/master-data/so-sortable.js
// * Description: Drag-and-drop reorder for SO table; auto-renumber codes; Save Order via AJAX (Faculty Version)
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-01-20] Copied from admin version with faculty route updates
// -------------------------------------------------------------------------------

import Sortable from 'sortablejs';

const $  = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

/** Get CSRF token for AJAX calls. */
function csrf() {
  return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

/** Update the visible SO codes (SO1, SO2, …) after a drag or any reorder. */
function updateVisibleCodes(tbody) {
  const rows = $$('tr[data-id]', tbody);
  rows.forEach((tr, idx) => {
    // column 1 is the code cell per table structure
    const codeCell = tr.querySelector('.sv-code');
    const newCode = `SO${idx + 1}`;
    if (codeCell) codeCell.textContent = newCode;

    // keep Edit button data fresh so the modal shows the current code
    const editBtn = tr.querySelector('button.edit');
    if (editBtn) editBtn.setAttribute('data-sv-code', newCode);
  });
}

/** Read ordered row IDs from the tbody into an array. */
function collectOrderedIds(tbody) {
  return $$('tr[data-id]', tbody).map((tr) => parseInt(tr.getAttribute('data-id'), 10));
}

/** Enable or disable the Save Order button, with accessibility attributes. */
function setSaveEnabled(saveBtn, enabled) {
  if (!saveBtn) return;
  if (enabled) {
    saveBtn.removeAttribute('disabled');
    saveBtn.setAttribute('aria-disabled', 'false');
  } else {
    saveBtn.setAttribute('disabled', 'disabled');
    saveBtn.setAttribute('aria-disabled', 'true');
  }
}

/** Initialize SortableJS on the SO table. */
function bootSortableSO() {
  const tbody   = $('#svTable-so tbody');
  const saveBtn = $('.sv-save-order-btn[data-sv-type="so"]');
  if (!tbody || !saveBtn) return;

  let dirty = false; // whether order changed since last save

  // Make rows draggable by the grip icon only
  Sortable.create(tbody, {
    animation: 150,
    handle: '.sv-row-grip',
    ghostClass: 'bg-light',
    onEnd: () => {
      // After a drop, renumber visible codes and allow saving.
      updateVisibleCodes(tbody);
      dirty = true;
      setSaveEnabled(saveBtn, true);
    },
  });

  // Save Order handler: POST orderedIds to the server
  saveBtn.addEventListener('click', async () => {
    if (!dirty) return;

    const orderedIds = collectOrderedIds(tbody);
    setSaveEnabled(saveBtn, false);
    const originalHtml = saveBtn.innerHTML;
    saveBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving…`;

    try {
      const res = await fetch('/faculty/master-data/reorder/so', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf(),
          'Accept': 'application/json',
        },
        body: JSON.stringify({ orderedIds }),
      });

      if (!res.ok) {
        // Try to parse an error message if available
        const data = await res.json().catch(() => ({}));
        const msg = data?.message || 'Failed to save SO order.';
        window.showAlertOverlay?.('error', msg) || alert(msg);
        // Re-enable to allow retry
        setSaveEnabled(saveBtn, true);
        saveBtn.innerHTML = originalHtml;
        return;
      }

      // Success — server has persisted new positions and codes
      dirty = false;
      setSaveEnabled(saveBtn, false);
      saveBtn.innerHTML = originalHtml;

      // (Optional) Ensure visible codes are in sync (already done on drop)
      updateVisibleCodes(tbody);

      window.showAlertOverlay?.('success', 'Student Outcomes reordered successfully!');
    } catch (err) {
      console.error('SO reorder error:', err);
      window.showAlertOverlay?.('error', 'Network error while saving order.') || alert('Network error while saving order.');
      // Allow retry
      setSaveEnabled(saveBtn, true);
      saveBtn.innerHTML = originalHtml;
    }
  });

  // Initial state: compute codes from current order (defensive) and keep Save disabled
  updateVisibleCodes(tbody);
  setSaveEnabled(saveBtn, false);
}

document.addEventListener('DOMContentLoaded', bootSortableSO);