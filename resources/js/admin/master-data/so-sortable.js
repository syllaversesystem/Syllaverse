// -----------------------------------------------------------------------------
// File: resources/js/admin/master-data/so-sortable.js
// Description: Enables drag-and-drop reordering of SOs with save via AJAX – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Initial creation – drag + save order support for Student Outcomes
// -----------------------------------------------------------------------------

import Sortable from 'sortablejs';

document.addEventListener('DOMContentLoaded', () => {
    const soTableBody = document.querySelector('#so-table-body');
    const saveButton = document.getElementById('save-so-order');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    if (!soTableBody || !saveButton || !csrfToken) return;

    // ✅ Enable drag-and-drop
    Sortable.create(soTableBody, {
        animation: 150,
        handle: '.drag-handle',
        ghostClass: 'bg-light',
    });

    // ✅ Save reordered SOs
    saveButton.addEventListener('click', async () => {
        const orderedIds = Array.from(soTableBody.querySelectorAll('tr')).map(row => row.dataset.id);

        try {
            const res = await fetch('/admin/master-data/reorder/so', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ orderedIds })
            });

            if (!res.ok) throw new Error('Failed to save SO order.');

            const data = await res.json();
            alert(data.message || 'SO order saved successfully.');
            location.reload();
        } catch (err) {
            console.error('SO reorder error:', err);
            alert('There was a problem saving the SO order.');
        }
    });
});
