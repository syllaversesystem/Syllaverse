// File: resources/js/faculty/syllabus-sdg.js
// Description: Handles AJAX-based SDG mapping (attach, update, remove) – Syllaverse

// Initialize after DOM ready
import { apiFetch, showToast } from '../lib/api';

document.addEventListener('DOMContentLoaded', function () {
    const addForm = document.querySelector('#addSdgModal form');
    let modal = { hide: () => {} };
    try {
        const modalEl = document.getElementById('addSdgModal');
        if (window.bootstrap && modalEl) modal = new bootstrap.Modal(modalEl);
    } catch (e) { console.warn('Bootstrap modal not available, using fallback', e); }
    const tbody = document.querySelector('#syllabus-sdg-sortable');
    const template = document.querySelector('#sdg-template-row');

    // Persist current ordering of persisted entries (rows with numeric data-id) to server
    async function persistOrder() {
        try {
            const syllabusId = tbody && tbody.dataset ? tbody.dataset.syllabusId : null;
            if (!syllabusId) return;
            // collect numeric persisted ids in current DOM order
            const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => {
                const id = r.getAttribute && r.getAttribute('data-id');
                return id && !id.startsWith('new-');
            });
            const ids = rows.map(r => r.getAttribute('data-id'));
            if (!ids.length) return;
            const base = window.syllabusBasePath || '/faculty/syllabi';
            await fetch(`${base}/${syllabusId}/sdgs/reorder`, {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                body: JSON.stringify({ ids })
            });
        } catch (e) { console.error('persistOrder failed', e); }
    }
    // Expose for other modules/fallbacks to call

    // Update visible SDG codes (badges and hidden inputs) to match current DOM order
    function updateVisibleCodes() {
        try {
            const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => {
                if (!r || r.nodeType !== Node.ELEMENT_NODE) return false;
                if (r.id && r.id === 'sdg-template-row') return false;
                if (r.classList && r.classList.contains('d-none')) return false;
                return Boolean(r.querySelector && (r.querySelector('textarea[name="sdgs[]"]') || r.querySelector('.cdio-badge')));
            });
            rows.forEach((r, idx) => {
                const ordinal = idx + 1;
                const badge = r.querySelector('.cdio-badge');
                if (badge) badge.textContent = `SDG${ordinal}`;
                const codeInput = r.querySelector('input[name="code[]"]');
                if (codeInput) codeInput.value = `SDG${ordinal}`;
                // Also keep data-code attribute in sync if present
                if (r.setAttribute) r.setAttribute('data-code', `SDG${ordinal}`);
            });
        } catch (e) { console.error('updateVisibleCodes failed', e); }
    }

    try { window.updateVisibleCodes = updateVisibleCodes; } catch (e) {}

    // Helper to append a SDG row into the tbody given server data
    function appendSdgRow(data) {
        try {
            const newRow = template.cloneNode(true);
            newRow.id = '';
            newRow.classList.remove('d-none');
            newRow.setAttribute('data-id', data.pivot_id || data.id || '');
            newRow.setAttribute('data-sdg-id', data.sdg_id || data.sdgId || data.sdg_id);

            const ta = newRow.querySelector('textarea[name="sdgs[]"]');
            if (ta) {
                const desc = data.description ?? '';
                ta.value = desc;
                try { ta.textContent = desc; } catch (e) {}
                ta.setAttribute('data-original', desc);
            }
            const titleInput = newRow.querySelector('input[name="title[]"]') || newRow.querySelector('.sdg-title-input');
            if (titleInput) {
                const tval = data.title || data.sdg_title || '';
                titleInput.value = tval;
                try { titleInput.setAttribute('data-original', tval); } catch (e) {}
            }
            const codeInput = newRow.querySelector('input[name="code[]"]');
            const badge = newRow.querySelector('.cdio-badge');
            try {
                if (badge) {
                    if (data.code) {
                        badge.textContent = data.code;
                        if (codeInput) codeInput.value = data.code;
                    } else {
                        const existing = Array.from(tbody.children).filter(r => {
                            if (!r || r.nodeType !== Node.ELEMENT_NODE) return false;
                            if (r.id && r.id === 'sdg-template-row') return false;
                            if (r.classList && r.classList.contains('d-none')) return false;
                            return Boolean(r.querySelector && (r.querySelector('textarea[name="sdgs[]"]') || r.querySelector('.cdio-badge')));
                        }).length;
                        const provisional = `SDG${existing + 1}`;
                        badge.textContent = provisional;
                        if (codeInput) codeInput.value = provisional;
                    }
                }
            } catch (e) { try { if (badge) badge.textContent = 'SDG#'; } catch (err) {} }

            tbody.appendChild(newRow);
            // ensure event bindings for update/delete exist on the new row
            try { bindSdgForms(newRow); } catch (e) {}
            try {
                if (window.initAutosize) window.initAutosize();
                else {
                    const tas = newRow.querySelectorAll('textarea.autosize, textarea');
                    tas.forEach((t) => { try { t.style.height = 'auto'; t.style.height = (t.scrollHeight || 24) + 'px'; } catch (e) {} });
                }
            } catch (e) {}

            try { if (window.updateVisibleCodes) window.updateVisibleCodes(); else updateVisibleCodes(); } catch (e) {}

            try { document.dispatchEvent(new CustomEvent('sdg:attached', { detail: data })); } catch (e) {}
            showToast('SDG added', `${data.title || 'SDG'} added to syllabus.`);
        } catch (err) { console.error('appendSdgRow failed', err); }
    }

    // ✅ Attach/detach SDGs via modal Save: compute diffs and perform AJAX
    document.addEventListener('submit', async function (e) {
        const form = e.target;
        if (!form || !form.matches || !form.matches('#addSdgModal form')) return;
        e.preventDefault();

        const action = form.action;
        const syllabusId = tbody && tbody.dataset ? tbody.dataset.syllabusId : null;

        // collect checked sdg ids from modal
        const checked = Array.from(form.querySelectorAll('input[name="sdg_ids[]"]:checked')).map(i => String(i.value));
        // collect currently attached sdg ids from table
        const attached = Array.from(tbody.querySelectorAll('tr[data-sdg-id]')).map(r => String(r.getAttribute('data-sdg-id')));

        // compute diffs
        const toAttach = checked.filter(id => !attached.includes(id));
        const toDetach = attached.filter(id => !checked.includes(id));

        // nothing to do
        if (!toAttach.length && !toDetach.length) {
            try { modal.hide(); } catch (e) {}
            return;
        }

        // perform attach (bulk) and detach (parallel deletes)
        const promises = [];

        if (toAttach.length) {
            // prefer JSON bulk attach
            promises.push(apiFetch(action, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ sdg_ids: toAttach }) })
                .then((res) => {
                    // response may include created array or single object
                    if (Array.isArray(res.created)) {
                        res.created.forEach(c => appendSdgRow(c));
                    } else if (res.pivot_id || res.sdg_id) {
                        appendSdgRow(res);
                    }
                })
            );
        }

        toDetach.forEach(sdgId => {
            // find row to get entry id
            const row = tbody.querySelector(`tr[data-sdg-id="${sdgId}"]`);
            let deleteUrl = form.action; // fallback
            if (row) {
                const entryId = row.getAttribute('data-id');
                const base = window.syllabusBasePath || '/faculty/syllabi';
                if (entryId) deleteUrl = `${base}/${syllabusId}/sdgs/entry/${entryId}`;
                else deleteUrl = `${base}/${syllabusId}/sdgs/${sdgId}`;
            } else {
                deleteUrl = `/faculty/syllabi/${syllabusId}/sdgs/${sdgId}`;
            }

            const p = fetch(deleteUrl, {
                method: 'DELETE', credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
            })
            .then(async res => {
                if (!res.ok) {
                    const txt = await res.text().catch(() => 'Delete failed');
                    throw new Error(txt);
                }
                return res.json().catch(() => ({}));
            })
            .then((data) => {
                // remove row and re-add modal checkbox
                try {
                    if (row && row.parentNode) row.parentNode.removeChild(row);
                    try { if (window.updateVisibleCodes) window.updateVisibleCodes(); else updateVisibleCodes(); } catch (e) {}
                    try { persistOrder(); } catch (e) { try { if (window.saveSdgOrder) window.saveSdgOrder(); } catch (er) {} }

                    // ensure the checkbox exists in modal and is unchecked
                    const list = document.querySelector('.sdg-checkbox-list');
                    if (list && !list.querySelector(`#sdg_check_${sdgId}`)) {
                        const wrapper = document.createElement('div'); wrapper.className = 'form-check mb-1';
                        const input = document.createElement('input'); input.name = 'sdg_ids[]'; input.className = 'form-check-input sdg-checkbox'; input.type = 'checkbox'; input.id = `sdg_check_${sdgId}`; input.value = sdgId;
                        const label = document.createElement('label'); label.className = 'form-check-label small'; label.htmlFor = input.id; label.textContent = data.title || `SDG ${sdgId}`;
                        wrapper.appendChild(input); wrapper.appendChild(label); list.appendChild(wrapper);
                    } else if (list) {
                        const cb = list.querySelector(`#sdg_check_${sdgId}`);
                        if (cb) cb.checked = false;
                    }
                    try { document.dispatchEvent(new CustomEvent('sdg:detached', { detail: { sdg_id: sdgId, title: data.title || null } })); } catch (e) {}
                } catch (err) { console.error('detach handling failed', err); }
            });

            promises.push(p);
        });

        Promise.all(promises)
        .then(() => {
            try { modal.hide(); } catch (e) {}
        })
        .catch((err) => {
            console.error('Modal save failed', err);
            const msg = err && err.message ? err.message : (err && err.payload && err.payload.message ? err.payload.message : 'Failed to update SDGs');
            showToast('Error', msg, true);
        });
    });

    // When the modal opens, mark checkboxes for SDGs that are already attached
    try {
        const modalEl = document.getElementById('addSdgModal');
        function syncModalCheckboxes() {
            try {
                // Build a normalized view of persisted rows (title, code, data-sdg-id)
                const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => r && r.id !== 'sdg-template-row');
                const rowData = rows.map(r => ({
                    sdgId: String(r.getAttribute('data-sdg-id') || ''),
                    title: (r.querySelector('input[name="title[]"]')?.value || r.querySelector('.sdg-title')?.textContent || '').trim(),
                    code: (r.querySelector('input[name="code[]"]')?.value || r.getAttribute('data-code') || '').trim(),
                }));
                const norm = (s) => (String(s || '').replace(/\s+/g, ' ').trim().toLowerCase());

                const checkboxes = Array.from(document.querySelectorAll('input[name="sdg_ids[]"]'));
                checkboxes.forEach(cb => {
                    try {
                        const mid = String(cb.value);
                        const label = document.querySelector(`label[for="sdg_check_${mid}"]`);
                        const masterTitle = label ? label.textContent.trim() : '';
                        const nTitle = norm(masterTitle);
                        const should = rowData.some(rd => {
                            if (rd.sdgId && rd.sdgId === mid) return true; // direct master id match
                            if (nTitle && rd.title && norm(rd.title) === nTitle) return true; // title match
                            if (rd.code && masterTitle && norm(rd.code) === norm(masterTitle)) return true; // code vs title (best-effort)
                            return false;
                        });
                        cb.checked = !!should;
                    } catch (e) { /* noop per checkbox */ }
                });
            } catch (e) { /* noop */ }
        }
        if (modalEl) {
            // bootstrap event
            modalEl.addEventListener('show.bs.modal', syncModalCheckboxes);
            // fallback: also sync when modal is triggered by click on any element that targets it
            document.addEventListener('click', function (ev) {
                const target = ev.target.closest && ev.target.closest('[data-bs-target]');
                if (target && target.getAttribute('data-bs-target') === '#addSdgModal') {
                    setTimeout(syncModalCheckboxes, 10);
                }
            });
        }
    } catch (e) {}

    // ✅ Bind update & delete on load
    // Ensure we attach per-row handlers for all server-rendered rows (not just forms)
    try {
        if (tbody) {
            Array.from(tbody.querySelectorAll('tr')).forEach(row => {
                if (!row || row.id === 'sdg-template-row') return;
                try { bindSdgForms(row); } catch (e) {}
            });
        } else {
            document.querySelectorAll('[data-sdg-form="update"]').forEach(form => {
                const row = form.closest('tr');
                bindSdgForms(row);
            });
        }
    } catch (e) { /* noop */ }

    // --- CDIO-like helpers: mark unsaved and provide a saveSdg() to persist all SDGs ---
    // Helper: return current order snapshot as array of data-id (persisted ids prefer, else data-sdg-id or generated)
    function getCurrentOrder() {
        try {
            const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => r && r.id !== 'sdg-template-row');
            return rows.map(r => r.getAttribute('data-id') || r.getAttribute('data-sdg-id') || '').filter(x => x != null);
        } catch (e) { return []; }
    }

    // initialize order snapshot so reorders can be detected
    try { tbody.dataset.orderSnapshot = JSON.stringify(getCurrentOrder()); } catch (e) {}
    function bindGlobalUnsaved() {
        try {
            const pill = document.getElementById('unsaved-sdgs');
            const checkAnyChanged = () => {
                const descChanged = Array.from(tbody.querySelectorAll('textarea.autosize')).some(t => (t.value || '') !== (t.getAttribute('data-original') || ''));
                const titleChanged = Array.from(tbody.querySelectorAll('input.sdg-title-input')).some(i => (i.value || '') !== (i.getAttribute('data-original') || ''));
                // detect reorder: compare current order to saved snapshot
                let orderChanged = false;
                try {
                    const saved = JSON.parse(tbody.dataset.orderSnapshot || '[]');
                    const now = getCurrentOrder();
                    // simple array compare
                    if (saved.length !== now.length) orderChanged = true;
                    else {
                        for (let k = 0; k < now.length; k++) { if (String(saved[k]) !== String(now[k])) { orderChanged = true; break; } }
                    }
                } catch (e) { orderChanged = false; }

                const anyChanged = descChanged || titleChanged || orderChanged;
                if (pill) pill.classList.toggle('d-none', !anyChanged);
                try { if (window.markDirty && anyChanged) window.markDirty('unsaved-sdgs'); } catch (e) {}
                try { if (window.updateUnsavedCount) window.updateUnsavedCount(); } catch (e) {}
            };
            // attach listeners to existing areas (descriptions and titles)
            Array.from(tbody.querySelectorAll('textarea.autosize')).forEach((ta) => { ta.addEventListener('input', checkAnyChanged); ta.addEventListener('change', checkAnyChanged); });
            Array.from(tbody.querySelectorAll('input.sdg-title-input')).forEach((ti) => { ti.addEventListener('input', checkAnyChanged); ti.addEventListener('change', checkAnyChanged); });
            // observe new nodes to wire events
            if (window.MutationObserver) {
                const mo = new MutationObserver((mutations) => {
                    let added = false;
                    for (const m of mutations) { if (m.addedNodes && m.addedNodes.length) { added = true; break; } }
                    if (added) Promise.resolve().then(() => { Array.from(tbody.querySelectorAll('textarea.autosize')).forEach((ta) => { if (!ta.__sdgBound) { ta.addEventListener('input', checkAnyChanged); ta.addEventListener('change', checkAnyChanged); ta.__sdgBound = true; } }); Array.from(tbody.querySelectorAll('input.sdg-title-input')).forEach((ti) => { if (!ti.__sdgBound) { ti.addEventListener('input', checkAnyChanged); ti.addEventListener('change', checkAnyChanged); ti.__sdgBound = true; } }); checkAnyChanged(); });
                });
                mo.observe(tbody, { childList: true, subtree: true });
            }
            // initial
            checkAnyChanged();
        } catch (e) { /* noop */ }
    }

    // Save all SDG rows (keeps parity with saveCdio)
    window.saveSdg = async function() {
        try {
            const form = document.getElementById('sdgForm');
            if (!form) return { ok: true };
            const items = [];
            Array.from(tbody.querySelectorAll('tr')).forEach((tr, idx) => {
                if (!tr || tr.id === 'sdg-template-row') return;
                const desc = tr.querySelector('textarea[name="sdgs[]"]')?.value || '';
                const code = tr.querySelector('input[name="code[]"]')?.value || (`SDG${idx+1}`);
                const title = tr.querySelector('input[name="title[]"]')?.value || (tr.querySelector('.sdg-title')?.textContent || '');
                // coerce pivot id: '' -> null, numeric string -> int
                const rawId = tr.getAttribute('data-id');
                const id = (rawId && rawId !== '' && !rawId.startsWith('new-')) ? (Number.isNaN(Number(rawId)) ? null : parseInt(rawId, 10)) : null;
                const sdgIdRaw = tr.getAttribute('data-sdg-id');
                const sdg_id = (sdgIdRaw && sdgIdRaw !== '') ? (Number.isNaN(Number(sdgIdRaw)) ? sdgIdRaw : parseInt(sdgIdRaw, 10)) : null;
                items.push({ id: id, sdg_id: sdg_id, code, title, description: desc, position: idx + 1 });
            });
            // Debug: log payload and endpoint so we can confirm network behavior
            try { console.debug && console.debug('saveSdg ->', { url: form.action, items }); } catch (e) {}

            let data = {};
            try {
                data = await apiFetch(form.action, { method: 'PUT', body: { sdgs: items } });
            } catch (err) {
                console.warn('saveSdg apiFetch error', err);
                // try to show helpful server message
                const msg = (err && err.payload && err.payload.message) ? err.payload.message : (err && err.message) ? err.message : 'Failed to save SDGs';
                try { showToast('Error saving SDGs', msg, true); } catch (e) {}
                return { ok: false, message: msg };
            }

            if (data && data.ok) {
                // mark inputs as saved
                Array.from(tbody.querySelectorAll('textarea[name="sdgs[]"]')).forEach((ta) => { ta.setAttribute('data-original', ta.value || ''); });
                Array.from(tbody.querySelectorAll('input.sdg-title-input')).forEach((ti) => { ti.setAttribute('data-original', ti.value || ''); });
                const pill = document.getElementById('unsaved-sdgs'); if (pill) pill.classList.add('d-none');
                // refresh saved order snapshot so reorder is no longer considered unsaved
                try { if (tbody) tbody.dataset.orderSnapshot = JSON.stringify(getCurrentOrder()); } catch (e) {}
                try { if (window.updateUnsavedCount) window.updateUnsavedCount(); } catch (e) {}
            }
            return data;
        } catch (err) { console.error('saveSdg error', err); return { ok: false, message: err && err.message ? err.message : 'Network error' }; }
    };

    // Initialize global unsaved wiring
    try { bindGlobalUnsaved(); } catch (e) {}

    // ✅ Event binding utility
    function bindSdgForms(row) {
        const updateForm = row.querySelector('[data-sdg-form="update"]');
        const deleteForm = row.querySelector('[data-sdg-form="delete"]');

    // ✅ Update SDG (manual save only — remove autosave-on-blur)
        const titleInput = row.querySelector('.sdg-title-input');
        const descInput = row.querySelector('.sdg-description-input');
        const pivotId = row.getAttribute('data-id');
        const syllabusId = tbody && tbody.dataset ? tbody.dataset.syllabusId : null;

        async function doUpdate(payload) {
            try {
                if (!syllabusId || !pivotId) return;
                const url = `${(window.syllabusBasePath || '/faculty/syllabi')}/${syllabusId}/sdgs/update/${pivotId}`;
                const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
                const token = document.querySelector('meta[name="csrf-token"]');
                if (token) headers['X-CSRF-TOKEN'] = token.content;
                const res = await fetch(url, {
                    method: 'PUT', credentials: 'same-origin', headers, body: JSON.stringify(payload)
                });
                if (!res.ok) {
                    const txt = await res.text().catch(() => 'Save failed');
                    throw new Error(txt || res.statusText);
                }
                const json = await res.json().catch(() => ({}));
                // mark field(s) as saved locally and update UI
                try {
                    if (payload.description && descInput) {
                        descInput.setAttribute('data-original', descInput.value || '');
                    }
                    if (payload.title && titleInput) {
                        titleInput.setAttribute('data-original', titleInput.value || '');
                    }
                    // re-evaluate global changed state and hide pill if nothing changed
                    try {
                        const pill = document.getElementById('unsaved-sdgs');
                        if (pill) {
                            const anyChanged = Array.from(document.querySelectorAll('#syllabus-sdg-sortable textarea.autosize')).some(t => (t.value || '') !== (t.getAttribute('data-original') || '')) || Array.from(document.querySelectorAll('#syllabus-sdg-sortable input.sdg-title-input')).some(i => (i.value || '') !== (i.getAttribute('data-original') || ''));
                            pill.classList.toggle('d-none', !anyChanged);
                        }
                    } catch (e) {}
                    try { if (window.updateUnsavedCount) window.updateUnsavedCount(); } catch (e) {}
                } catch (e) {}
                try { document.dispatchEvent(new CustomEvent('sdg:updated', { detail: { pivot: pivotId, payload, response: json } })); } catch (e) {}
                try { showToast('Saved', 'SDG updated'); } catch (e) {}
            } catch (err) {
                console.error('SDG update failed', err);
                try { showToast('Error', err.message || 'Failed to save', true); } catch (e) {}
            }
        }

        // Do not auto-save on blur. User must explicitly save via the Save button.
        // Keep marking as unsaved while editing so UI indicates pending changes.
        if (descInput) {
            descInput.addEventListener('input', function () { try { if (window.markDirty) window.markDirty('unsaved-sdgs'); } catch (e) {} try { if (window.updateUnsavedCount) window.updateUnsavedCount(); } catch (e) {} });
        }

        if (titleInput) {
            // mark unsaved on input
            titleInput.addEventListener('input', function () { try { if (window.markDirty) window.markDirty('unsaved-sdgs'); } catch (e) {} try { if (window.updateUnsavedCount) window.updateUnsavedCount(); } catch (e) {} });
        }

        // ✅ Delete SDG (AJAX)
    deleteForm?.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!confirm('Remove this SDG?')) return;

            // Prefer deleting by per-syllabus entry id (row data-id). Fallback to syllabus+sdg route.
            const entryId = row.getAttribute('data-id');
            const sdgId = row.getAttribute('data-sdg-id');
            const syllabusId = tbody && tbody.dataset ? tbody.dataset.syllabusId : null;
            let deleteUrl = deleteForm.action;
            if (entryId && syllabusId) {
                deleteUrl = `/faculty/syllabi/${syllabusId}/sdgs/entry/${entryId}`;
            } else if (sdgId && syllabusId) {
                deleteUrl = `/faculty/syllabi/${syllabusId}/sdgs/${sdgId}`;
            }

            apiFetch(deleteUrl, { method: 'DELETE' })
            .then((data) => {
                    // Centralized removal handler when available
                    try {
                        if (window.handleSdgRowRemoval) {
                            window.handleSdgRowRemoval(row, sdgId, data.message || 'SDG removed from syllabus.');
                        } else {
                            // fallback: remove row, renumber visible badges, and persist order
                            row.remove();
                            try { if (window.updateVisibleCodes) window.updateVisibleCodes(); else updateVisibleCodes(); } catch (e) {}
                            try { persistOrder(); } catch (e) { try { if (window.saveSdgOrder) window.saveSdgOrder(); } catch (er) {} }
                            // re-add checkbox into modal list
                            try {
                                const list = document.querySelector('.sdg-checkbox-list');
                                const sdgTitleEl = row.querySelector('input[name="title[]"]') || row.querySelector('.sdg-title') || row.querySelector('textarea[name="sdgs[]"]');
                                const sdgTitle = sdgTitleEl ? (sdgTitleEl.value || sdgTitleEl.textContent || '') : '';
                                if (list && sdgId && !list.querySelector(`#sdg_check_${sdgId}`)) {
                                    const wrapper = document.createElement('div'); wrapper.className = 'form-check mb-1';
                                    const input = document.createElement('input'); input.name = 'sdg_ids[]'; input.className = 'form-check-input sdg-checkbox'; input.type = 'checkbox'; input.id = `sdg_check_${sdgId}`; input.value = sdgId;
                                    const label = document.createElement('label'); label.className = 'form-check-label small'; label.htmlFor = input.id; label.textContent = sdgTitle || `SDG ${sdgId}`;
                                    wrapper.appendChild(input); wrapper.appendChild(label); list.appendChild(wrapper);
                                }
                            } catch (e) {}
                            showToast('SDG removed', data.message || 'SDG removed from syllabus.');
                            try { document.dispatchEvent(new CustomEvent('sdg:detached', { detail: { sdg_id: sdgId, title: sdgTitle, pivot: row.getAttribute('data-id') } })); } catch (e) {}
                        }
                    } catch (err) { console.error('handleSdgRowRemoval failed', err); }
                })
            .catch((err) => {
                const msg = err && err.payload && err.payload.message ? err.payload.message : (err.message || 'Failed to remove');
                showToast('Error', msg, true);
            });
        });
    }

    // Sync modal select when SDGs are detached/attached via any flow (keyboard, sortable, ajax forms)
    document.addEventListener('sdg:detached', function (ev) {
        try {
            const sdgId = ev?.detail?.sdg_id;
            const title = ev?.detail?.title || null;
            const list = document.querySelector('.sdg-checkbox-list');
            if (!list || !sdgId) return;
            if (!list.querySelector(`#sdg_check_${sdgId}`)) {
                const wrapper = document.createElement('div'); wrapper.className = 'form-check mb-1';
                const input = document.createElement('input'); input.name = 'sdg_ids[]'; input.className = 'form-check-input sdg-checkbox'; input.type = 'checkbox'; input.id = `sdg_check_${sdgId}`; input.value = sdgId;
                const label = document.createElement('label'); label.className = 'form-check-label small'; label.htmlFor = input.id; label.textContent = title || `SDG ${sdgId}`;
                wrapper.appendChild(input); wrapper.appendChild(label); list.appendChild(wrapper);
            }
        } catch (e) { /* noop */ }
    });

    document.addEventListener('sdg:attached', function (ev) {
        try {
            const sdgId = ev?.detail?.sdg_id;
            const list = document.querySelector('.sdg-checkbox-list');
            if (!list || !sdgId) return;
            const cb = list.querySelector(`#sdg_check_${sdgId}`);
            if (cb) cb.checked = true;
        } catch (e) { /* noop */ }
    });


    // Ensure top Save button becomes active when SDGs are reordered by any module
    document.addEventListener('sdg:reordered', function (ev) {
        try {
            // Prefer the consolidated helper if available
            if (window.markAsUnsaved) window.markAsUnsaved('sdgs');
            if (window.setSyllabusSaveState) window.setSyllabusSaveState('dirty');
            try { const pill = document.getElementById('unsaved-sdgs'); if (pill) pill.classList.remove('d-none'); } catch (e) {}
            const saveBtn = document.getElementById('syllabusSaveBtn');
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.classList.add('btn-warning');
                saveBtn.classList.remove('btn-danger');
                saveBtn.style.pointerEvents = 'auto';
            }
            try { if (window.updateUnsavedCount) window.updateUnsavedCount(); } catch (e) {}
        } catch (e) { /* noop */ }
    });


    // Toast helper (lightweight, appended to body)
    // Expose api helpers globally for inline fallbacks
    try { window.apiFetch = apiFetch; window.showSdgToast = showToast; } catch (e) {}

});
