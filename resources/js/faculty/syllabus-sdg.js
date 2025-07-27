// File: resources/js/faculty/syllabus-sdg.js
// Description: Handles AJAX-based SDG mapping (attach, update, remove) – Syllaverse

document.addEventListener('DOMContentLoaded', function () {
    const addForm = document.querySelector('#addSdgModal form');
    const modal = new bootstrap.Modal(document.getElementById('addSdgModal'));
    const tableBody = document.querySelector('#sdg-table-body');
    const template = document.querySelector('#sdg-template-row');

    // ✅ Attach SDG via modal (live row add)
    addForm?.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(addForm);

        fetch(addForm.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => {
            if (!res.ok) throw res;
            return res.json();
        })
        .then(data => {
            modal.hide();

            const newRow = template.cloneNode(true);
            newRow.id = '';
            newRow.classList.remove('d-none');

            // Fill in title + description
            newRow.querySelector('input[name="title"]').value = data.title ?? '';
            newRow.querySelector('textarea[name="description"]').value = data.description ?? '';

            // Set form actions
            newRow.querySelector('[data-sdg-form="update"]').action = `/faculty/syllabi/${syllabusId}/sdgs/update/${data.pivot_id}`;
            newRow.querySelector('[data-sdg-form="delete"]').action = `/faculty/syllabi/${syllabusId}/sdgs/${data.sdg_id}`;

            tableBody.appendChild(newRow);

            // Remove newly selected option from dropdown
            const select = document.querySelector('#sdg_id');
            const usedOption = select.querySelector(`option[value="${data.sdg_id}"]`);
            if (usedOption) usedOption.remove();

            // Bind events on new row
            bindSdgForms(newRow);
        })
        .catch(async (err) => {
            const message = await err.text();
            alert('Error: ' + message);
        });
    });

    // ✅ Bind update & delete on load
    document.querySelectorAll('[data-sdg-form="update"]').forEach(form => {
        const row = form.closest('tr');
        bindSdgForms(row);
    });

    // ✅ Event binding utility
    function bindSdgForms(row) {
        const updateForm = row.querySelector('[data-sdg-form="update"]');
        const deleteForm = row.querySelector('[data-sdg-form="delete"]');

        // ✅ Update SDG
        updateForm?.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(updateForm);

            console.log('[DEBUG] Submitting SDG update to:', updateForm.action);
            console.log('[DEBUG] Payload:', Object.fromEntries(formData.entries()));

            fetch(updateForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-HTTP-Method-Override': 'PUT',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => {
                if (!res.ok) throw res;
                return res.json().catch(() => res.text());
            })
            .then(() => {
                const btn = updateForm.querySelector('button[type="submit"]');
                btn.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> Saved!';
                setTimeout(() => {
                    btn.innerHTML = '<i class="bi bi-save"></i> Save';
                }, 1500);
            })
            .catch(async (err) => {
                const message = await err.text();
                alert('Update failed: ' + message);
            });
        });

        // ✅ Delete SDG
        deleteForm?.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!confirm('Remove this SDG?')) return;

            const formData = new FormData(deleteForm);

            fetch(deleteForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-HTTP-Method-Override': 'DELETE',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => {
                if (!res.ok) throw res;
                return res.json().catch(() => res.text());
            })
            .then(() => {
                // ✅ Remove the row
                row.remove();

                // ✅ Re-add the SDG option back to modal <select>
                const select = document.querySelector('#sdg_id');
                const sdgId = deleteForm.action.split('/').pop();
                const sdgTitle = row.querySelector('input[name="title"]').value;

                if (!select.querySelector(`option[value="${sdgId}"]`)) {
                    const option = document.createElement('option');
                    option.value = sdgId;
                    option.textContent = sdgTitle;
                    select.appendChild(option);
                }
            })
            .catch(async (err) => {
                const message = await err.text();
                alert('Failed to remove: ' + message);
            });
        });
    }
});
