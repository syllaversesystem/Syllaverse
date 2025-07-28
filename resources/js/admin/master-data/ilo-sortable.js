// -----------------------------------------------------------------------------
// File: resources/js/admin/master-data/ilo-sortable.js
// Description: Enables drag-and-drop sorting and saves ILO order via AJAX – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Initial creation – sortable list with Save Order button for ILOs.
// [2025-07-29] Enhanced: auto-update visible ILO codes on drag.
// -----------------------------------------------------------------------------

import Sortable from 'sortablejs';

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('ilo-sortable');
    const saveBtn = document.getElementById('save-ilo-order');

    if (!list || !saveBtn) return;

    // ✅ Function to update ILO codes live in the DOM
    function updateVisibleCodes() {
        const items = list.querySelectorAll('li[data-id]');
        items.forEach((el, index) => {
            const input = el.querySelector('input[name="code"]');
            if (input) {
                input.value = `ILO${index + 1}`;
            }
        });
    }

    // ✅ Make the list sortable
    Sortable.create(list, {
        animation: 150,
        ghostClass: 'bg-light',
        onEnd: updateVisibleCodes,
    });

    // ✅ Handle Save Order button click
    saveBtn.addEventListener('click', () => {
        const items = list.querySelectorAll('li[data-id]');
        const courseId = list.getAttribute('data-course-id');

        const orderedIds = Array.from(items).map((el) => el.getAttribute('data-id'));

        fetch(`/admin/master-data/reorder/ilo`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: orderedIds, course_id: courseId })
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to save ILO order.');
            return response.json();
        })
        .then(data => {
            alert(data.message || 'ILO order saved successfully!');
            location.reload();
        })
        .catch(error => {
            console.error('Reorder error:', error);
            alert('There was a problem saving the new order.');
        });
    });

    // ✅ Initial update of codes (in case DOM order is off)
    updateVisibleCodes();
});
