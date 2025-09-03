// File: resources/js/faculty/syllabus-sdg.js
// Description: Handles AJAX-based SDG mapping (attach, update, remove) – Syllaverse

import './syllabus-sdg-sortable';

document.addEventListener('DOMContentLoaded', function () {
    const addForm = document.querySelector('#addSdgModal form');
    const modal = new bootstrap.Modal(document.getElementById('addSdgModal'));
    const tbody = document.querySelector('#syllabus-sdg-sortable');
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

                // Clone the SDG template row used by the sortable SDG table
                const newRow = template.cloneNode(true);
                newRow.id = '';
                newRow.classList.remove('d-none');

                // Set pivot + sdg ids so other scripts can target them
                newRow.setAttribute('data-id', data.pivot_id);
                newRow.setAttribute('data-sdg-id', data.sdg_id);

                // Fill textarea (description) and hidden code input if present
                const ta = newRow.querySelector('textarea[name="sdgs[]"]');
                if (ta) {
                    ta.value = data.description ?? '';
                    ta.setAttribute('data-original', data.description ?? '');
                }
                // Fill visible title and hidden title input if present
                const titleEl = newRow.querySelector('.sdg-title');
                if (titleEl) titleEl.textContent = data.title || '';
                const titleInput = newRow.querySelector('input[name="title[]"]');
                if (titleInput) titleInput.value = data.title || '';
                const codeInput = newRow.querySelector('input[name="code[]"]');
                // set a provisional code; sortable script will renumber on mutation
                const currentCount = Array.from(tbody.querySelectorAll('tr')).filter(r => r.querySelector('textarea[name="sdgs[]"]') || r.querySelector('.cdio-badge')).length;
                if (codeInput) codeInput.value = `SDG${currentCount + 1}`;
                const badge = newRow.querySelector('.cdio-badge'); if (badge) badge.textContent = `SDG${currentCount + 1}`;

                // Append and ensure autosize/renumber triggers
                tbody.appendChild(newRow);
                try { if (window.initAutosize) window.initAutosize(); } catch (e) {}
                try { if (window.updateVisibleCodes) window.updateVisibleCodes(); } catch (e) {}

                // Remove newly selected option from dropdown
                const select = document.querySelector('#sdg_id');
                const usedOption = select && select.querySelector(`option[value="${data.sdg_id}"]`);
                if (usedOption) usedOption.remove();

                // If the sortable script expects event bindings, it will handle persisted rows (inline save).

                // Small success toast
                showSdgToast('SDG added', `${data.title || 'SDG'} added to syllabus.`);

                // Dispatch a custom event so other modules can react
                try { document.dispatchEvent(new CustomEvent('sdg:attached', { detail: data })); } catch (e) {}
        })
        .catch(async (err) => {
            let message = 'Network error';
            try { message = await err.text(); } catch (e) {}
            showSdgToast('Error', message, true);
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

        // ✅ Delete SDG (AJAX)
        deleteForm?.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!confirm('Remove this SDG?')) return;

            // Prefer using syllabus + sdg id for the detach route; fallback to the form action
            const sdgId = row.getAttribute('data-sdg-id');
            const syllabusId = tbody && tbody.dataset ? tbody.dataset.syllabusId : null;
            const deleteUrl = (sdgId && syllabusId) ? `/faculty/syllabi/${syllabusId}/sdgs/${sdgId}` : deleteForm.action;

            fetch(deleteUrl, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(async (res) => {
                if (!res.ok) {
                    const txt = await res.text().catch(() => 'Delete failed');
                    throw new Error(txt);
                }
                return res.json().catch(() => ({}));
            })
            .then((data) => {
                // Remove the row from DOM
                row.remove();

                // Re-add the SDG option back to modal <select> if present
                const select = document.querySelector('#sdg_id');
                const sdgTitleEl = row.querySelector('input[name="title[]"]') || row.querySelector('.sdg-title') || row.querySelector('textarea[name="sdgs[]"]');
                const sdgTitle = sdgTitleEl ? (sdgTitleEl.value || sdgTitleEl.textContent || '') : '';

                if (select && sdgId && !select.querySelector(`option[value="${sdgId}"]`)) {
                    const option = document.createElement('option');
                    option.value = sdgId;
                    option.textContent = sdgTitle || `SDG ${sdgId}`;
                    select.appendChild(option);
                }

                showSdgToast('SDG removed', data.message || 'SDG removed from syllabus.');
                try { document.dispatchEvent(new CustomEvent('sdg:detached', { detail: { sdg_id: sdgId, pivot: row.getAttribute('data-id') } })); } catch (e) {}
            })
            .catch(async (err) => {
                const msg = err && err.message ? err.message : (await (err.text ? err.text() : Promise.resolve('Failed to remove'))).toString();
                showSdgToast('Error', msg, true);
            });
        });
    }
});

// Toast helper (lightweight, appended to body)
function showSdgToast(title, message, isError = false) {
    try {
        const id = `sdg-toast-${Date.now()}`;
        const toastHtml = `
            <div id="${id}" class="toast align-items-center text-bg-${isError ? 'danger' : 'success'} border-0 position-fixed" role="alert" aria-live="assertive" aria-atomic="true" style="top:1rem; right:1rem; z-index:11000;">
                <div class="d-flex">
                    <div class="toast-body"> <strong>${title}:</strong> ${message} </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        const container = document.createElement('div'); container.innerHTML = toastHtml; document.body.appendChild(container.firstElementChild);
        const toastEl = document.getElementById(id);
        const bsToast = new bootstrap.Toast(toastEl, { delay: 2500 });
        bsToast.show();
        setTimeout(() => { try { toastEl.remove(); } catch (e) {} }, 3500);
    } catch (e) { try { alert(title + ': ' + message); } catch (err) {} }
}
