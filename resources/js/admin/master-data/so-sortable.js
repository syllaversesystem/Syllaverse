// -----------------------------------------------------------------------------
// File: resources/js/admin/master-data/so-sortable.js
// Description: Enables drag-and-drop sorting and saves SO order via AJAX – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Initial creation – sortable list with Save Order button for SOs.
// -----------------------------------------------------------------------------

import Sortable from 'sortablejs';

document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('so-sortable');
    const saveBtn = document.getElementById('save-so-order');

    if (!list || !saveBtn) return;

    // ✅ Function to update visible SO codes after sorting
    function updateVisibleCodes() {
        const items = list.querySelectorAll('li[data-id]');
        items.forEach((el, index) => {
            const input = el.querySelector('input[name="code"]');
            if (input) {
                input.value = `SO${index + 1}`;
            }
        });
    }

    // ✅ Make list sortable
    Sortable.create(list, {
        animation: 150,
        ghostClass: 'bg-light',
        onEnd: updateVisibleCodes,
    });

    // ✅ Save order handler
    saveBtn.addEventListener('click', () => {
        const items = list.querySelectorAll('li[data-id]');
        const orderedIds = Array.from(items).map((el) => el.getAttribute('data-id'));

        fetch(`/admin/master-data/reorder/so`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ orderedIds })
        })
        .then(res => {
            if (!res.ok) throw new Error('Failed to save SO order.');
            return res.json();
        })
        .then(data => {
            alert(data.message || 'SO order saved successfully!');
            location.reload();
        })
        .catch(err => {
            console.error('SO reorder error:', err);
            alert('There was a problem saving the SO order.');
        });
    });

    // ✅ Initial code sync
    updateVisibleCodes();
});
