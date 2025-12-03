// AJAX helpers for SuperAdmin Manage Accounts modals

function serializeForm(form) {
	const formData = new FormData(form);
	return formData;
}

async function ajaxSubmit(form) {
	const action = form.getAttribute('action');
	const methodAttr = (form.getAttribute('method') || 'POST').toUpperCase();
	// Ensure role/department have sensible defaults for injected edit forms
	const collapse = form.closest('.sv-details');
	const roleSelect = form.querySelector('select[name="role"]');
	const deptSelect = form.querySelector('select[name="department_id"]');
	if (collapse && roleSelect && !roleSelect.value) {
		const currentRole = collapse.getAttribute('data-current-role');
		if (currentRole) {
			// Try selecting matching option; if not present, append fallback
			let matched = false;
			Array.from(roleSelect.options).forEach(opt => {
				if (opt.value === currentRole) { opt.selected = true; matched = true; }
			});
			if (!matched) {
				const opt = document.createElement('option');
				opt.value = currentRole;
				opt.textContent = currentRole;
				opt.selected = true;
				roleSelect.appendChild(opt);
			}
		}
		// As a final fallback, select the first non-empty option
		if (!roleSelect.value) {
			const nonEmpty = Array.from(roleSelect.options).find(o => o.value && o.value.trim() !== '');
			if (nonEmpty) nonEmpty.selected = true;
		}
	}
	if (collapse && deptSelect && !deptSelect.value) {
		const currentDeptId = collapse.getAttribute('data-current-dept-id');
		if (currentDeptId) {
			Array.from(deptSelect.options).forEach(opt => { opt.selected = (opt.value === currentDeptId); });
		}
		// Fallback: first non-empty dept option
		if (!deptSelect.value) {
			const nonEmptyDept = Array.from(deptSelect.options).find(o => o.value && o.value.trim() !== '');
			if (nonEmptyDept) nonEmptyDept.selected = true;
		}
	}

	const data = serializeForm(form);

	// Force-set critical fields into FormData to avoid missing keys
	if (roleSelect) {
		data.set('role', roleSelect.value || '');
	}
	if (deptSelect) {
		data.set('department_id', deptSelect.value || '');
	}

	// Client-side validation for required fields (since we prevent default submit)
	const requiredFields = Array.from(form.querySelectorAll('[required]'));
	for (const field of requiredFields) {
		const name = field.getAttribute('name') || 'field';
		const value = (field instanceof HTMLSelectElement) ? field.value : (field.value ?? '').trim();
		if (!value) {
			throw new Error(`${name.replace('_',' ')} is required.`);
		}
	}

	// Respect Laravel method spoofing if present
	const spoof = form.querySelector('input[name="_method"]');
	const method = spoof ? spoof.value.toUpperCase() : methodAttr;

	const headers = {
		'X-Requested-With': 'XMLHttpRequest'
	};

	// CSRF token support
	const token = document.querySelector('meta[name="csrf-token"]');
	if (token) headers['X-CSRF-TOKEN'] = token.getAttribute('content');

	const opts = {
		method: method,
		headers,
		body: data
	};

	// Debug: log outgoing payload entries
	try {
		console.debug('ManageAccounts AJAX:', { action, method, entries: Array.from(data.entries()) });
	} catch (_) {}

	// Normalize non-POST methods to POST with method override so Laravel reliably parses FormData
	if (method === 'DELETE') {
		opts.method = 'POST';
		data.append('_method', 'DELETE');
	} else if (method !== 'POST') {
		opts.method = 'POST';
		data.append('_method', method);
	}

	const res = await fetch(action, opts);
	const contentType = res.headers.get('content-type') || '';
	let payload;
	if (contentType.includes('application/json')) {
		payload = await res.json();
	} else {
		payload = await res.text();
	}

	if (!res.ok || (payload && payload.ok === false)) {
		let message = (payload && payload.message) ? payload.message : `Request failed (${res.status})`;
		// If Laravel validation errors present, surface first message
		if (payload && payload.errors) {
			const first = Object.values(payload.errors).flat()[0];
			if (first) message = first;
		}
		throw new Error(message);
	}
	return payload;
}

function dispatchAjaxSuccess(target, detail) {
	const evt = new CustomEvent('ajaxSuccess', { detail, bubbles: true, composed: true });
	target.dispatchEvent(evt);
}

function dispatchAjaxError(target, error) {
	const evt = new CustomEvent('ajaxError', { detail: { error }, bubbles: true, composed: true });
	target.dispatchEvent(evt);
}

// Alert Overlay helper
function showAlertOverlay(type, message) {
	const container = document.getElementById('svAlertOverlay');
	if (!container) return; // Graceful no-op if component not present
	// Clear previous inline alert if any
	const existing = container.querySelector('.alert');
	if (existing) existing.remove();
	// Map type to icon/color
	let icon = 'info';
	let bar = 'blue';
	if (type === 'success') { icon = 'check-circle'; bar = 'green'; }
	else if (type === 'error') { icon = 'x-circle'; bar = 'red'; }
	// Build alert element
	const el = document.createElement('div');
	el.className = `alert alert-overlay-style alert-${type} d-flex align-items-center gap-2 show`;
	el.setAttribute('role', 'alert');
	el.innerHTML = `
		<i data-feather="${icon}"></i>
		<div>${message}</div>
		<div class="loading-bar ${bar}"></div>
	`;
	container.appendChild(el);
	if (window.feather) window.feather.replace();

	// Prefer a precise hide based on CSS transition end of loading bar
	const barEl = el.querySelector('.loading-bar');
	let hideScheduled = false;
	const hideNow = () => {
		if (hideScheduled) return;
		hideScheduled = true;
		try {
			el.classList.remove('show');
			// If CSS fade-out exists, wait a short tick before remove
			setTimeout(() => { try { el.remove(); } catch(_) {} }, 150);
		} catch(_) {
			try { el.remove(); } catch(_) {}
		}
	};

	// If the loading bar animates, listen for transition end
	if (barEl) {
		barEl.addEventListener('transitionend', hideNow, { once: true });
		barEl.addEventListener('animationend', hideNow, { once: true });
	}
	// Fallback: hard timeout to avoid lingering card
	setTimeout(hideNow, 2500);
}

function attachAjaxHandlers() {
	// Delegate for any form marked data-ajax="true"
	document.addEventListener('submit', async (e) => {
		const form = e.target;
		if (!(form instanceof HTMLFormElement)) return;
		if (form.getAttribute('data-ajax') !== 'true') return;

		e.preventDefault();

		// Optional: small loading state
		const submitBtn = form.querySelector('[type="submit"]');
		const origText = submitBtn ? submitBtn.innerHTML : null;
		if (submitBtn) {
			submitBtn.disabled = true;
			submitBtn.classList.add('is-loading');
		}

		try {
			const result = await ajaxSubmit(form);
			dispatchAjaxSuccess(form, { result });

			// Bubble success to containers (e.g., appointments list)
			const parent = form.closest('[data-sv-scope]') || form.closest('.sv-request-list') || document.body;
			dispatchAjaxSuccess(parent, { result });

						// Do not refresh page; rely on ajaxSuccess listeners to update UI.
						// Do not close Manage Faculty/Admin appointment modals on add/update/delete.
						const modalEl = form.closest('.modal');
						const isApptModal = modalEl && modalEl.classList.contains('sv-appt-modal');
						if (modalEl && !isApptModal) {
				try {
					if (window.bootstrap && window.bootstrap.Modal) {
						// Avoid aria-hidden focus warning by blurring the active element before hide
						if (document.activeElement && typeof document.activeElement.blur === 'function') {
							document.activeElement.blur();
						}
						const modal = window.bootstrap.Modal.getInstance(modalEl) || new window.bootstrap.Modal(modalEl);
						modal.hide();
					} else {
						// Fallback: click any dismiss button or force-hide
						const dismissBtn = modalEl.querySelector('[data-bs-dismiss="modal"]');
						if (dismissBtn) {
							try { dismissBtn.click(); } catch(_) {}
						}
						// Force hide as last resort
						modalEl.classList.remove('show');
						modalEl.setAttribute('aria-hidden', 'true');
						modalEl.style.display = 'none';
						// Remove any lingering backdrops and normalize body
						document.querySelectorAll('.modal-backdrop').forEach(b => { try { b.remove(); } catch(_) {} });
						document.body.classList.remove('modal-open');
						document.body.style.removeProperty('padding-right');
					}
				} catch (_) {}
			}
		} catch (err) {
			console.error('AJAX form error:', err);
			dispatchAjaxError(form, err);
			// Optional: show toast/alert
			const msg = (err && err.message) ? err.message : 'Request failed.';
			showAlertOverlay('error', msg);
		} finally {
			if (submitBtn) {
				submitBtn.disabled = false;
				submitBtn.classList.remove('is-loading');
				if (origText !== null) submitBtn.innerHTML = origText;
			}
		}
	});

	// Support buttons/links with data-ajax="true" that are not forms
	// Also catch elements with data-sv-revoke / data-sv-reapprove even if data-ajax is missing
	document.addEventListener('click', async (e) => {
		let el = e.target.closest('[data-ajax="true"][data-href]');
		// Fallback: allow elements marked with action attributes (can be a FORM)
		if (!el) {
			const cand = e.target.closest('[data-sv-revoke="true"],[data-sv-reapprove="true"]');
			if (!cand) return;
			el = cand;
		}
		e.preventDefault();
		// Determine request source
		const isForm = el instanceof HTMLFormElement;
		const url = isForm ? el.getAttribute('action') : (el.getAttribute('data-href') || el.getAttribute('href'));
		const methodAttr = isForm ? (el.getAttribute('method') || 'POST') : (el.getAttribute('data-method') || 'POST');
		const method = methodAttr.toUpperCase();

		const headers = { 'X-Requested-With': 'XMLHttpRequest' };
		const token = document.querySelector('meta[name="csrf-token"]');
		if (token) headers['X-CSRF-TOKEN'] = token.getAttribute('content');

		try {
			if (!url) throw new Error('Missing action URL for AJAX click.');
			let res;
			if (isForm) {
				// Submit the form via AJAX with FormData (includes @csrf token input)
				const data = new FormData(el);
				// Respect method spoofing
				const spoof = el.querySelector('input[name="_method"]');
				const submitMethod = spoof ? spoof.value.toUpperCase() : method;
				let fetchMethod = submitMethod;
				if (submitMethod !== 'POST') {
					fetchMethod = 'POST';
					data.append('_method', submitMethod);
				}
				res = await fetch(url, { method: fetchMethod, headers, body: data });
			} else {
				res = await fetch(url, { method, headers });
			}
			const ct = res.headers.get('content-type') || '';
			let payload;
			if (ct.includes('application/json')) {
				payload = await res.json();
			} else {
				// Gracefully handle non-JSON responses from server
				const text = await res.text();
				payload = { ok: res.ok, message: text };
			}
			if (!res.ok || payload.ok === false) throw new Error(payload.message || `Request failed (${res.status})`);

			// Tag action type for downstream listeners
			const detail = { result: payload };
			if (el.getAttribute('data-sv-revoke') === 'true') detail.action = 'revoke';
			if (el.getAttribute('data-sv-reapprove') === 'true') detail.action = 'reapprove';
			dispatchAjaxSuccess(el, detail);
			const modalEl = el.closest('.modal');
			if (modalEl && window.bootstrap) {
				try {
					// Avoid aria-hidden focus warning by blurring the active element before hide
					if (document.activeElement && typeof document.activeElement.blur === 'function') {
						document.activeElement.blur();
					}
					const modal = window.bootstrap.Modal.getInstance(modalEl) || new window.bootstrap.Modal(modalEl);
					modal.hide();
				} catch (_) {}
			}
		} catch (err) {
			console.error('AJAX click error:', err);
			dispatchAjaxError(el, err);
			showAlertOverlay('error', err.message || 'Request failed.');
		}
	});
}

// Init on DOM ready
document.addEventListener('DOMContentLoaded', attachAjaxHandlers);

// Mitigate aria-hidden focus warning when hiding Bootstrap modals
document.addEventListener('hide.bs.modal', function (e) {
	const modalEl = e.target;
	if (!(modalEl instanceof Element)) return;
	// If focus is inside the modal, blur before Bootstrap toggles aria-hidden
	const active = document.activeElement;
	if (active && modalEl.contains(active) && typeof active.blur === 'function') {
		try { active.blur(); } catch (_) {}
	}
});

// Removed: no delete confirmation modal in Rejected module; deletes submit directly via AJAX forms

document.addEventListener('hidden.bs.modal', function (e) {
	const modalEl = e.target;
	if (!(modalEl instanceof Element)) return;
	// Ensure nothing inside the now-hidden modal retains focus
	const active = document.activeElement;
	if (active && modalEl.contains(active) && typeof active.blur === 'function') {
		try { active.blur(); } catch (_) {}
	}
});

// Fallback: force-close any visible modals and remove backdrops
function forceCloseAllModals() {
	try {
		// Blur focus
		if (document.activeElement && typeof document.activeElement.blur === 'function') {
			document.activeElement.blur();
		}
		// Hide any visible modals
		document.querySelectorAll('.modal.show').forEach(m => {
			m.classList.remove('show');
			m.setAttribute('aria-hidden', 'true');
			m.style.display = 'none';
		});
		// Remove backdrops
		document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
		// Restore body classes
		document.body.classList.remove('modal-open');
		document.body.style.removeProperty('padding-right');
	} catch (_) {}
}

// Public utility: clear any Bootstrap modal/backdrop artifacts
function clearModalAndBackdrop() {
	try {
		forceCloseAllModals();
		const sb = document.getElementById('sidebar-backdrop');
		if (sb && !sb.classList.contains('d-none')) sb.classList.add('d-none');
		document.body.style.overflow = '';
		document.documentElement.style.overflow = '';
		// Brief guard style
		const guardId = 'sv-backdrop-guard-style-now';
		let guard = document.getElementById(guardId);
		if (!guard) {
			guard = document.createElement('style');
			guard.id = guardId;
			guard.textContent = '.modal-backdrop{opacity:0!important;display:none!important;visibility:hidden!important;}';
			document.head.appendChild(guard);
			setTimeout(() => { try { guard.remove(); } catch(_){} }, 1500);
		}
		// Kill backdrops reinserted briefly
		const obs = new MutationObserver((mut) => {
			mut.forEach(m => Array.from(m.addedNodes).forEach(n => {
				if (n.nodeType === 1 && n.classList && n.classList.contains('modal-backdrop')) {
					try { n.remove(); } catch(_){}
				}
			}));
		});
		obs.observe(document.body, { childList: true });
		setTimeout(() => { try { obs.disconnect(); } catch(_){} }, 1500);
	} catch(_) {}
}

// expose for debugging if needed
window.svClearBackdrops = clearModalAndBackdrop;

// Hide edit buttons on frontend (without changing backend routes)
document.addEventListener('DOMContentLoaded', function () {
	const style = document.createElement('style');
	style.textContent = `
		.sv-appt-modal .action-btn.edit { display: none !important; }
		.sv-appt-modal .action-btn.reject[disabled],
		.sv-appt-modal .action-btn.reject[aria-disabled="true"] {
			pointer-events: none;
			opacity: 0.6;
			cursor: not-allowed;
		}
	`;
	document.head.appendChild(style);
});

// Ensure Add New Appointment role dropdown includes any currently active role
document.addEventListener('DOMContentLoaded', function () {
	const modalEl = document.querySelector('.sv-appt-modal');
	if (!modalEl) return;
	const addForm = modalEl.querySelector('form[data-sv-scope^="add-"]');
	const addRoleSelect = addForm ? addForm.querySelector('select[name="role"]') : null;
	if (!addRoleSelect) return;

	// Always reset to a full, unfiltered role list for Add Appointment
	addRoleSelect.innerHTML = `
		<option value="">— Select Role —</option>
		<option value="DEPT_HEAD">Department Head (Dean/Head/Principal)</option>
		<option value="ASSOC_DEAN">Associate Dean</option>
		<option value="CHAIR">Chairperson</option>
		<option value="FACULTY">Faculty</option>
	`;

	// Collect active roles from existing appointment collapses
	const collapses = modalEl.querySelectorAll('.sv-details[data-current-role]');
	const existingRoles = new Set();
	collapses.forEach(c => {
		const r = c.getAttribute('data-current-role');
		if (r) existingRoles.add(r);
	});

	// Helper: map role value to readable label
	const roleLabel = (r) => {
		switch (r) {
			case 'DEPT_HEAD': return 'Department Head (Dean/Head/Principal)';
			case 'ASSOC_DEAN': return 'Associate Dean';
			case 'CHAIR':
			case 'DEPT_CHAIR': return 'Chairperson';
			case 'PROG_CHAIR': return 'Program Chair';
			case 'FACULTY': return 'Faculty';
			default: return r;
		}
	};

	// Append any missing role options so user can still see/select them
	existingRoles.forEach(r => {
		const has = Array.from(addRoleSelect.options).some(o => o.value === r);
		if (!has) {
			const opt = document.createElement('option');
			opt.value = r;
			opt.textContent = roleLabel(r);
			addRoleSelect.appendChild(opt);
		}
	});

	// Preselect an active role if present (choose the first detected)
	const firstActive = existingRoles.values().next().value;
	if (firstActive) {
		Array.from(addRoleSelect.options).forEach(opt => { opt.selected = (opt.value === firstActive); });
	}
});

// Live DOM updates for appointments in Manage Faculty modal
document.addEventListener('ajaxSuccess', function(e) {
	const detail = e.detail || {};
	const result = detail.result || {};
	const appt = result.appointment;
	if (!appt) return;

	// Find the containing modal and list
	const targetEl = e.target instanceof Element ? e.target : document.body;
	const modalEl = targetEl.closest('.sv-appt-modal') || document.querySelector('.sv-appt-modal');
	if (!modalEl) return;
	const list = modalEl.querySelector('.sv-request-list');
	if (!list) return;

	// Helper to build label from role; server already renders in Blade, but for live updates we approximate
	function roleLabelFrom(appt, result) {
		const deptNameLower = (result.department_name || '').toLowerCase();
		const programCount = typeof result.program_count === 'number' ? result.program_count : null;
		if (appt.role === 'DEPT_HEAD') {
			if (deptNameLower.includes('laboratory school') || deptNameLower.includes('lab school') || deptNameLower.includes('labschool')) {
				return 'Principal';
			}
			if (deptNameLower.includes('general education')) return 'Head';
			return 'Dean';
		}
		if (appt.role === 'ASSOC_DEAN') return 'Associate Dean';
		if (appt.role === 'CHAIR' || appt.role === 'DEPT_CHAIR') {
			if (programCount !== null) return programCount >= 2 ? 'Department Chairperson' : 'Program Chair';
			return 'Department Chairperson';
		}
		if (appt.role === 'PROG_CHAIR') return 'Program Chair';
		if (appt.role === 'FACULTY') return 'Faculty';
		return appt.role || 'Appointment';
	}

	// Find existing item by collapse id or data-id
	const item = list.querySelector(`#sv-fac-appt-edit-${appt.id}`)?.previousElementSibling;

	// Build minimal item HTML if new
	if (!item) {
		const wrapper = document.createElement('div');
		wrapper.className = 'sv-request-item';
		const deleteDisabled = appt.role === 'FACULTY' ? 'disabled aria-disabled="true"' : '';
		wrapper.innerHTML = `
			<div class="sv-request-meta">
				<span class=\"sv-pill is-accent sv-pill--sm\">${roleLabelFrom(appt, result)}</span>
				<span class=\"sv-pill is-muted sv-pill--sm\">${result.department_name || 'Institution-wide'}</span>
			</div>
			<div class="sv-request-actions">
				<form method="POST" action="/superadmin/appointments/${appt.id}" class="d-inline" data-ajax="true">
					<input type="hidden" name="_method" value="DELETE" />
					<button class="action-btn reject" type="submit" title="Delete appointment" aria-label="Delete appointment" ${deleteDisabled}>
						<i data-feather="trash-2"></i>
					</button>
				</form>
			</div>
		`;

		// Replace existing roles: remove other active items in UI and call DELETE on the server
		const existingItems = Array.from(list.querySelectorAll('.sv-request-item'));
		existingItems.forEach(existing => {
			// Prefer extracting id from the delete form action URL
			let oldId = null;
			const delForm = existing.querySelector('form[action^="/superadmin/appointments/"]');
			if (delForm) {
				const actionUrl = delForm.getAttribute('action') || '';
				const m = actionUrl.match(/\/superadmin\/appointments\/(\d+)/);
				if (m) oldId = m[1];
			}
			// Fallback: infer from associated collapse id if present
			let collapseEl = null;
			if (!oldId) {
				const tgt = existing.querySelector('[data-bs-target]');
				const targetId = tgt ? tgt.getAttribute('data-bs-target') : null;
				collapseEl = targetId ? list.querySelector(targetId) : null;
				const idMatch = targetId ? targetId.match(/sv-fac-appt-edit-(\d+)/) : null;
				oldId = idMatch ? idMatch[1] : null;
			}
			if (oldId && String(oldId) !== String(appt.id)) {
				// Fire DELETE request to server to replace existing roles
				const fd = new FormData();
				fd.append('_method', 'DELETE');
				const headers = { 'X-Requested-With': 'XMLHttpRequest' };
				const token = document.querySelector('meta[name="csrf-token"]');
				if (token) headers['X-CSRF-TOKEN'] = token.getAttribute('content');
				fetch(`/superadmin/appointments/${oldId}`, { method: 'POST', headers, body: fd })
					.then(() => {})
					.catch(() => {});

				// Remove UI elements for the old appointment
				existing.remove();
				if (collapseEl) collapseEl.remove();
			}
		});

		list.appendChild(wrapper);

		const collapse = document.createElement('div');
		collapse.id = `sv-fac-appt-edit-${appt.id}`;
		collapse.className = 'collapse sv-details';
		collapse.setAttribute('data-bs-parent', `#${list.id}`);

		// Build an editable form with a full, unfiltered role list (avoid cloning filtered Add options)
		const deptSelect = modalEl.querySelector('form[data-sv-scope^="add-"] select[name="department_id"]');
		const deptOptionsHTML = deptSelect ? deptSelect.innerHTML : '<option value="">— Select Department —</option>';
		const roleOptionsHTML = `
			<option value="">— Select Role —</option>
			<option value="DEPT_HEAD">Department Head (Dean/Head/Principal)</option>
			<option value="ASSOC_DEAN">Associate Dean</option>
			<option value="CHAIR">Chairperson</option>
			<option value="FACULTY">Faculty</option>
		`;

		collapse.innerHTML = `
			<form method="POST"
					action="/superadmin/appointments/${appt.id}"
					class="row g-2 align-items-end sv-appt-form"
					data-sv-scope="edit-${appt.id}"
					data-ajax="true">
				<input type="hidden" name="_method" value="PUT" />
				<div class="col-md-4">
					<label class="form-label small">Role</label>
					<select name="role" class="form-select form-select-sm" required>
						${roleOptionsHTML}
					</select>
				</div>
				<div class="col-md-7">
					<label class="form-label small">Department</label>
					<select name="department_id" class="form-select form-select-sm sv-dept" required>
						${deptOptionsHTML}
					</select>
				</div>
				<div class="col-md-1 d-flex">
					<button class="action-btn approve ms-auto" type="submit" title="Save changes" aria-label="Save changes">
						<i data-feather="check"></i>
					</button>
				</div>
			</form>
		`;

		list.appendChild(collapse);

		// Preselect current role and department
		const collapseRole = collapse.querySelector('select[name="role"]');
		const collapseDept = collapse.querySelector('select[name="department_id"]');
		if (collapseRole) {
			Array.from(collapseRole.options).forEach(opt => { opt.selected = (opt.value === appt.role); });
			// Ensure a value is selected; if not found (legacy), default to current string value
			const hasSelection = Array.from(collapseRole.options).some(opt => opt.selected);
			if (!hasSelection) {
				const custom = document.createElement('option');
				custom.value = appt.role;
				custom.textContent = appt.role;
				custom.selected = true;
				collapseRole.appendChild(custom);
			}
		}
		if (collapseDept && result.department_name) {
			// Try to match by text
			Array.from(collapseDept.options).forEach(opt => {
				opt.selected = (opt.textContent.trim() === result.department_name);
			});
		}
		if (window.feather) window.feather.replace();
		// Remove placeholder if present
		const placeholder = list.querySelector('.sv-appt-placeholder');
		if (placeholder) placeholder.remove();
		// Success notice for create
		showAlertOverlay('success', 'Appointment set successfully.');
		return;
	}

	// Update existing item's role pill
	const rolePill = item.querySelector('.sv-pill.is-accent');
	if (rolePill) rolePill.textContent = roleLabelFrom(appt, result);
	const deptPill = item.querySelector('.sv-pill.is-muted');
	if (deptPill && result.department_name) deptPill.textContent = result.department_name;

	// Close the edit collapse section after successful update
	const collapseEl = list.querySelector(`#sv-fac-appt-edit-${appt.id}`);
	if (collapseEl) {
		try {
			if (window.bootstrap && window.bootstrap.Collapse) {
				const inst = window.bootstrap.Collapse.getInstance(collapseEl) || new window.bootstrap.Collapse(collapseEl, { toggle: false });
				inst.hide();
			} else {
				collapseEl.classList.remove('show');
			}
		} catch (_) {}
	}
	// Success notice for update
	showAlertOverlay('success', 'Appointment updated.');

	// Also refresh the Approved table to reflect role pills and department
	try {
		refreshApprovedTablePartial();
	} catch(_) {}
});

// Handle deletions by removing the item on success when server doesn't return appointment
document.addEventListener('ajaxSuccess', function(e) {
	const targetEl = e.target instanceof Element ? e.target : null;
	if (!targetEl) return;
	// Handle revoke access submissions
	const isRevoke = targetEl.matches('form[data-ajax="true"][data-sv-revoke="true"]') || (e.detail && e.detail.action === 'revoke');
	if (isRevoke) {
		// Identify confirmation modal and main manage modal
		let modalEl = targetEl.closest('.modal');
		let userId = null;
		if (modalEl) {
			// Try parse user id from confirmation modal id e.g. revokeFacultyModal-123
			const idAttr = modalEl.id || '';
			const m = idAttr.match(/(revokeFacultyModal|revokeAdminModal)-(\d+)/);
			if (m) userId = m[2];
			// If not found, try nearest manage modal ancestor
			if (!userId) {
				const manageAncestor = document.getElementById('manageFaculty-' + (modalEl.getAttribute('data-user-id') || ''))
					|| document.getElementById('manageAdmin-' + (modalEl.getAttribute('data-user-id') || ''))
					|| modalEl.closest('.sv-appt-modal');
				userId = manageAncestor ? manageAncestor.getAttribute('data-user-id') : null;
			}
		}
		// Locate the main manage modal for UI updates
		const manageModal = userId
			? (document.getElementById('manageFaculty-' + userId) || document.getElementById('manageAdmin-' + userId))
			: (document.querySelector('.sv-appt-modal'));
		if (manageModal) {
			const list = manageModal.querySelector('.sv-request-list');
			if (list) {
				list.innerHTML = `
					<div class="sv-appt-placeholder rounded border border-2 border-dashed p-3 text-center text-muted">
						<div class="mb-2"><i data-feather="briefcase"></i></div>
						<div class="fw-semibold">No active appointments</div>
						<div class="small">Access revoked for this faculty.</div>
					</div>
				`;
				if (window.feather) window.feather.replace();
			}
			// Optionally disable the Add form to prevent new appointments until status restored
			const addForm = manageModal.querySelector('form[data-sv-scope^="add-"]');
			if (addForm) {
				const submitBtn = addForm.querySelector('[type="submit"]');
				if (submitBtn) {
					submitBtn.disabled = true;
					submitBtn.setAttribute('aria-disabled', 'true');
				}
				addForm.querySelectorAll('select, input').forEach(el => el.disabled = true);
			}
			// Also update Approved tab: remove this user's row if present
			if (userId) {
				const approvedBody = document.querySelector('#svApprovedAdminsTable tbody');
				if (approvedBody) {
					const manageBtn = approvedBody.querySelector(`button[data-bs-target="#manageAdmin-${userId}"], button[data-bs-target="#manageFaculty-${userId}"]`);
					if (manageBtn) {
						const row = manageBtn.closest('tr');
						if (row) row.remove();
						// If table empty, show empty state
						if (!approvedBody.querySelector('tr')) {
							const emptyRow = document.createElement('tr');
							emptyRow.className = 'superadmin-manage-account-empty-row';
							emptyRow.innerHTML = `
								<td colspan="4">
									<div class="sv-empty">
										<h6>No approved accounts</h6>
										<p>Approved admins and faculty will appear here once accounts are verified.</p>
									</div>
								</td>
							`;
							approvedBody.appendChild(emptyRow);
						}
					}
				}
				// Update Rejected tab: add a row for this user (match UI)
				const rejectedBody = document.querySelector('#svRejectedAdminsTable tbody');
				if (rejectedBody) {
					// Remove empty-state first if present
					const existingEmpty = rejectedBody.querySelector('.superadmin-manage-account-empty-row');
					if (existingEmpty) existingEmpty.remove();
					// Derive user info from server response or modal context
					let userName = '—';
					let userEmail = '—';
					let userRole = 'faculty';
					if (typeof result === 'object') {
						const u = result.user || result;
						if (u) {
							userName = u.name || userName;
							userEmail = u.email || userEmail;
							userRole = u.role || userRole;
						}
					}
					try {
						// Prefer header name/email in manage modal
						const title = manageModal ? manageModal.querySelector('.modal-title span') : null;
						userName = title ? (title.textContent.replace('Manage Faculty —', '').trim()) : userName;
						const emailEl = manageModal ? manageModal.querySelector('[data-sv-user-email]') : null;
						userEmail = emailEl ? (emailEl.textContent.trim() || emailEl.value || userEmail) : userEmail;
						const roleEl = manageModal ? manageModal.querySelector('[data-sv-user-role]') : null;
						userRole = roleEl ? (roleEl.textContent.trim() || roleEl.value || userRole) : userRole;
					} catch(_) {}
					// Build actions: reapprove and delete (icon-only)
					const tr = document.createElement('tr');
					tr.id = `sv-rejected-row-${userId}`;
					tr.innerHTML = `
						<td>${userName}</td>
						<td class="text-muted">${userEmail}</td>
						<td class="text-end">
							<div class="d-flex justify-content-end align-items-center gap-2">
								<form method="POST" action="${userRole === 'admin' ? '/superadmin/approve/admin/' + userId : '/superadmin/approve/faculty/' + userId}" class="d-inline" data-ajax="true" data-sv-reapprove="true" aria-label="Re-approve ${userName}">
									<button class="action-btn approve" type="submit" title="Re-approve ${userName}" data-bs-toggle="tooltip" data-bs-placement="top">
										<i data-feather="check-circle"></i>
									</button>
								</form>
								<form method="POST" action="${userRole === 'admin' ? '/superadmin/delete/admin/' + userId : '/superadmin/delete/faculty/' + userId}" class="d-inline" data-ajax="true" data-sv-delete="true" aria-label="Delete ${userName}">
									<button class="action-btn reject" type="submit" title="Delete ${userName}" data-bs-toggle="tooltip" data-bs-placement="top">
										<i data-feather="trash-2"></i>
									</button>
								</form>
							</div>
						</td>
					`;
					// Ensure tooltips work for dynamically inserted buttons
					if (window.bootstrap) {
						Array.from(tr.querySelectorAll('[data-bs-toggle="tooltip"]')).forEach(el => {
							try { new window.bootstrap.Tooltip(el); } catch (_) {}
						});
					}
					rejectedBody.prepend(tr);
					if (window.feather) window.feather.replace();
				}
			}
			// Close revoke confirmation modal (if open) and parent manage modal
				// Immediate DOM hide for the current modal hosting the form
				const currentModal = targetEl.closest('.modal');
				if (currentModal) {
					try {
						if (document.activeElement && typeof document.activeElement.blur === 'function') document.activeElement.blur();
						currentModal.classList.remove('show');
						currentModal.setAttribute('aria-hidden', 'true');
						currentModal.style.display = 'none';
					} catch (_) {}
				}
				if (window.bootstrap) {
				try {
					// Determine confirmation modal by id pattern
					const confirmModal = userId ? (document.getElementById(`revokeFacultyModal-${userId}`) || document.getElementById(`revokeAdminModal-${userId}`)) : null;
					if (confirmModal) {
						if (document.activeElement && typeof document.activeElement.blur === 'function') document.activeElement.blur();
						const cmInst = window.bootstrap.Modal.getInstance(confirmModal) || new window.bootstrap.Modal(confirmModal);
						cmInst.hide();
						// Dispose instance to avoid stale state
						if (cmInst.dispose) {
							try { cmInst.dispose(); } catch (_) {}
						}
							// Ultimate fallback: remove confirmation modal element from DOM
							setTimeout(() => {
								try {
									confirmModal.remove();
								} catch (_) {}
							}, 50);
					}
					if (manageModal) {
						const inst = window.bootstrap.Modal.getInstance(manageModal) || new window.bootstrap.Modal(manageModal);
						inst.hide();
						if (inst.dispose) {
							try { inst.dispose(); } catch (_) {}
						}
							// Ultimate fallback: remove manage modal element from DOM
							setTimeout(() => {
								try {
									manageModal.remove();
								} catch (_) {}
							}, 50);
					}
					// Safety: force-close any remaining visible modals
					forceCloseAllModals();
					// Extra safety: run a second cleanup tick to remove any stray backdrops
					setTimeout(() => {
						try {
							forceCloseAllModals();
							// Clean custom sidebar backdrop if visible
							const sb = document.getElementById('sidebar-backdrop');
							if (sb && !sb.classList.contains('d-none')) sb.classList.add('d-none');
							// Remove any lingering Bootstrap backdrop nodes
							document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
							// Ensure body state is normal
							document.body.classList.remove('modal-open');
							document.body.style.removeProperty('padding-right');
							// Ensure scroll/overflow re-enabled in tough cases
							document.body.style.overflow = '';
							document.documentElement.style.overflow = '';
							// Temporary CSS guard to suppress any backdrop flashes
							const guardId = 'sv-backdrop-guard-style';
							let guard = document.getElementById(guardId);
							if (!guard) {
								guard = document.createElement('style');
								guard.id = guardId;
								guard.textContent = '.modal-backdrop{opacity:0!important;display:none!important;visibility:hidden!important;}';
								document.head.appendChild(guard);
								setTimeout(() => { try { guard.remove(); } catch(_){} }, 2000);
							}
							// Short-lived mutation observer to kill any reinserted backdrops
							const killBackdrop = (nodes) => {
								Array.from(nodes || []).forEach(n => {
									try {
										if (n.nodeType === 1 && n.classList && n.classList.contains('modal-backdrop')) n.remove();
									} catch(_){}
								});
							};
							try {
								const obs = new MutationObserver((mut) => {
									mut.forEach(m => killBackdrop(m.addedNodes));
								});
								obs.observe(document.body, { childList: true });
								setTimeout(() => { try { obs.disconnect(); } catch(_){} }, 2000);
							} catch(_){}
						} catch (_) {}
					}, 100);
				} catch (_) {}
			}
		}
		showAlertOverlay('success', 'Faculty access revoked.');
		// Final cleanup call to ensure backdrop is cleared
		clearModalAndBackdrop();
		return;
	}

	// Handle reapprove action submissions
	const isReapprove = targetEl.matches('[data-ajax="true"][data-sv-reapprove="true"]') || (e.detail && e.detail.action === 'reapprove');
	if (isReapprove) {
		const modalEl = targetEl.closest('.modal');
		if (modalEl) {
			// Re-enable Add form inputs and button
			const addForm = modalEl.querySelector('form[data-sv-scope^="add-"]');
			if (addForm) {
				addForm.querySelectorAll('select, input').forEach(el => el.disabled = false);
				const submitBtn = addForm.querySelector('[type="submit"]');
				if (submitBtn) {
					submitBtn.disabled = false;
					submitBtn.removeAttribute('aria-disabled');
				}
			}
			// If the list has a placeholder, ensure it's present until user adds
			const list = modalEl.querySelector('.sv-request-list');
			if (list && !list.querySelector('.sv-request-item') && !list.querySelector('.sv-appt-placeholder')) {
				const placeholder = document.createElement('div');
				placeholder.className = 'sv-appt-placeholder rounded border border-2 border-dashed p-3 text-center text-muted';
				placeholder.innerHTML = `
					<div class="mb-2"><i data-feather="briefcase"></i></div>
					<div class="fw-semibold">No active appointments</div>
					<div class="small">Use the form above to set a new appointment.</div>
				`;
				list.appendChild(placeholder);
				if (window.feather) window.feather.replace();
			}
			// Close modal to indicate completion
			if (window.bootstrap) {
				try {
					const inst = window.bootstrap.Modal.getInstance(modalEl) || new window.bootstrap.Modal(modalEl);
					inst.hide();
				} catch (_) {}
			}
		}
		// If triggered from Rejected table, remove the row and update empty-state
		const rejectedRow = targetEl.closest('tr[id^="sv-rejected-row-"]');
		const tableBody = targetEl.closest('tbody');
		if (rejectedRow && tableBody) {
			rejectedRow.remove();
			// Show empty state if no rows remain
			if (!tableBody.querySelector('tr[id^="sv-rejected-row-"]')) {
				const emptyRow = document.createElement('tr');
				emptyRow.className = 'superadmin-manage-account-empty-row';
				emptyRow.innerHTML = `
					<td colspan="4">
						<div class="sv-empty">
							<h6>No rejected accounts</h6>
							<p>When accounts are rejected, they will appear here. You can re-approve them anytime.</p>
						</div>
					</td>
				`;
				tableBody.appendChild(emptyRow);
			}
			// Add a basic row to Approved tab
			const approvedBody = document.querySelector('#svApprovedAdminsTable tbody');
			let user = (e.detail && e.detail.result && (e.detail.result.user || e.detail.result.approved_user)) || null;
			// Fallback: derive from Rejected row DOM if payload lacks user
			if (!user && rejectedRow) {
				try {
					const idMatch = rejectedRow.id.match(/sv-rejected-row-(\d+)/);
					const derivedId = idMatch ? idMatch[1] : null;
					const tds = rejectedRow.querySelectorAll('td');
					const derivedName = tds[0] ? (tds[0].textContent || '').trim() : '—';
					// Determine role based on which form exists in the actions cell
					const actionsCell = tds[2] || rejectedRow.querySelector('td.text-end');
					let derivedRole = 'user';
					if (actionsCell) {
						if (actionsCell.querySelector('form[action*="approve.admin"]')) derivedRole = 'admin';
						else if (actionsCell.querySelector('form[action*="approve.faculty"]')) derivedRole = 'faculty';
					}
					user = { id: derivedId, name: derivedName, role: derivedRole };
				} catch (_) {}
			}
			if (approvedBody && user && user.id) {
				// Remove empty-state row if present
				const existingEmpty = approvedBody.querySelector('.superadmin-manage-account-empty-row');
				if (existingEmpty) existingEmpty.remove();
				const tr = document.createElement('tr');
				const roleLabel = (function(role){
					if (!role) return 'User';
					switch (role) {
						case 'admin': return 'Admin';
						case 'faculty': return 'Faculty';
						case 'student': return 'Student';
						case 'superadmin': return 'Superadmin';
						default: return role.charAt(0).toUpperCase() + role.slice(1).toLowerCase();
					}
				})(user.role);
				const manageBtn = user.role === 'admin'
					? `<button class="action-btn edit" type="button" data-bs-toggle="modal" data-bs-target="#manageAdmin-${user.id}" title="Manage appointments for ${user.name}" aria-label="Manage appointments for ${user.name}"><i data-feather="settings"></i></button>`
					: `<button class="action-btn edit" type="button" data-bs-toggle="modal" data-bs-target="#manageFaculty-${user.id}" title="Manage faculty for ${user.name}" aria-label="Manage faculty for ${user.name}"><i data-feather="settings"></i></button>`;
				tr.innerHTML = `
					<td>${user.name || '—'}</td>
					<td>
						<div class="d-flex align-items-center gap-2">
							<span class="sv-pill is-primary sv-pill--sm">${roleLabel}</span>
						</div>
					</td>
					<td><span class="text-muted">—</span></td>
					<td class="text-end"><div class="d-flex justify-content-end align-items-center gap-2">${manageBtn}</div></td>
				`;
				approvedBody.prepend(tr);
				if (window.feather) window.feather.replace();
			}
		}
		showAlertOverlay('success', 'Faculty reapproved. You can set appointments again.');
		return;
	}

	// Handle delete user from Rejected tab
	const isDeleteUser = targetEl.matches('[data-ajax="true"][data-sv-delete="true"]');
	if (isDeleteUser) {
		// If delete initiated from shared modal, remove specified row
		const formEl = targetEl.closest('form');
		if (formEl && formEl.dataset && formEl.dataset.svRow) {
			try {
				const rowSel = formEl.dataset.svRow;
				const rowEl = document.querySelector(rowSel);
				if (rowEl) {
					rowEl.parentElement && rowEl.parentElement.removeChild(rowEl);
				}
			} catch(_) {}
		}
		// Close the confirmation modal
		const confirmModal = targetEl.closest('.modal');
		if (confirmModal) {
			try {
				if (window.bootstrap && window.bootstrap.Modal) {
					if (document.activeElement && typeof document.activeElement.blur === 'function') {
						document.activeElement.blur();
					}
					const cmInst = window.bootstrap.Modal.getInstance(confirmModal) || new window.bootstrap.Modal(confirmModal);
					cmInst.hide();
					setTimeout(() => {
						try { if (cmInst.dispose) cmInst.dispose(); } catch(_) {}
						document.querySelectorAll('.modal-backdrop').forEach(b => { try { b.remove(); } catch(_) {} });
						document.body.classList.remove('modal-open');
						document.body.style.removeProperty('padding-right');
					}, 100);
				} else {
					// Fallback: click dismiss or force-hide
					const dismissBtn = confirmModal.querySelector('[data-bs-dismiss="modal"]');
					if (dismissBtn) {
						try { dismissBtn.click(); } catch(_) {}
					}
					confirmModal.classList.remove('show');
					confirmModal.setAttribute('aria-hidden', 'true');
					confirmModal.style.display = 'none';
					document.querySelectorAll('.modal-backdrop').forEach(b => { try { b.remove(); } catch(_) {} });
					document.body.classList.remove('modal-open');
					document.body.style.removeProperty('padding-right');
				}
			} catch(_) {}
		}

		const rejectedRow = targetEl.closest('tr[id^="sv-rejected-row-"]');
		const tableBody = targetEl.closest('tbody');
		if (rejectedRow && tableBody) {
			rejectedRow.remove();
			// Show empty state if no rows remain
			if (!tableBody.querySelector('tr[id^="sv-rejected-row-"]')) {
				const emptyRow = document.createElement('tr');
				emptyRow.className = 'superadmin-manage-account-empty-row';
				emptyRow.innerHTML = `
					<td colspan="4">
						<div class="sv-empty">
							<h6>No rejected accounts</h6>
							<p>When accounts are rejected, they will appear here. You can re-approve them anytime.</p>
						</div>
					</td>
				`;
				tableBody.appendChild(emptyRow);
			}
		}

		// Refresh the Rejected table from server to keep in sync
		try { refreshRejectedTablePartial(); } catch(_) {}

		showAlertOverlay('success', 'Account deleted.');
		return;
	}
	// Only act for delete forms
	const isDelete = targetEl.matches('form[data-ajax="true"]') && targetEl.querySelector('input[name="_method"][value="DELETE"]');
	if (!isDelete) return;
	const modalEl = targetEl.closest('.sv-appt-modal');
	const list = modalEl ? modalEl.querySelector('.sv-request-list') : null;
	if (!list) return;
	// Find the request item containing this form and remove it and its collapse
	const item = targetEl.closest('.sv-request-item');
	const collapseId = item ? item.querySelector('[data-bs-target]')?.getAttribute('data-bs-target') : null;
	// Read department id from collapse before removal for fallback Faculty creation
	let deletedDeptId = null;
	if (collapseId) {
		const beforeCollapseEl = list.querySelector(collapseId);
		deletedDeptId = beforeCollapseEl ? beforeCollapseEl.getAttribute('data-current-dept-id') : null;
	}
	if (item) item.remove();
	if (collapseId) {
		const collapseEl = list.querySelector(collapseId);
		if (collapseEl) collapseEl.remove();
	}
	// Success notice for delete
	showAlertOverlay('success', 'Appointment deleted.');

	// Refresh Approved table after delete
	try { refreshApprovedTablePartial(); } catch(_) {}
	// If list empty, show placeholder
	if (!list.querySelector('.sv-request-item')) {
		// Auto-create a fallback Faculty role using the deleted department (or leadership dept)
		const userId = modalEl ? modalEl.getAttribute('data-user-id') : null;
		let deptId = deletedDeptId;
		if (!deptId || deptId === '0') {
			const leadDept = modalEl ? modalEl.getAttribute('data-leadership-dept-id') : null;
			deptId = leadDept && leadDept !== '0' ? leadDept : null;
		}
		if (userId && deptId) {
			const formData = new FormData();
			formData.append('user_id', userId);
			formData.append('role', 'FACULTY');
			formData.append('department_id', deptId);
			const headers = { 'X-Requested-With': 'XMLHttpRequest' };
			const token = document.querySelector('meta[name="csrf-token"]');
			if (token) headers['X-CSRF-TOKEN'] = token.getAttribute('content');
			fetch('/superadmin/appointments', { method: 'POST', headers, body: formData })
				.then(async (res) => {
					const payload = await res.json().catch(() => ({}));
					if (!res.ok || payload.ok === false) throw new Error(payload.message || `Request failed (${res.status})`);
					// Dispatch success so existing handlers update UI
					dispatchAjaxSuccess(list, { result: payload });
					// Info notice for auto-created Faculty
					showAlertOverlay('info', 'No active appointments left. A Faculty appointment was created.');
				})
				.catch(() => {
					// If creation fails, fall back to showing placeholder
					const placeholder = document.createElement('div');
					placeholder.className = 'sv-appt-placeholder rounded border border-2 border-dashed p-3 text-center text-muted';
					placeholder.innerHTML = `
						<div class=\"mb-2\"><i data-feather=\"briefcase\"></i></div>
						<div class=\"fw-semibold\">No active appointments</div>
						<div class=\"small\">Use the form above to add Department Head, Associate Dean, or Chairperson.</div>
					`;
					list.appendChild(placeholder);
					if (window.feather) window.feather.replace();
					showAlertOverlay('error', 'Failed to auto-create Faculty appointment.');
				});
		} else {
			// No dept/user context; just show placeholder
			const placeholder = document.createElement('div');
			placeholder.className = 'sv-appt-placeholder rounded border border-2 border-dashed p-3 text-center text-muted';
			placeholder.innerHTML = `
				<div class=\"mb-2\"><i data-feather=\"briefcase\"></i></div>
				<div class=\"fw-semibold\">No active appointments</div>
				<div class=\"small\">Use the form above to add Department Head, Associate Dean, or Chairperson.</div>
			`;
			list.appendChild(placeholder);
			if (window.feather) window.feather.replace();
			showAlertOverlay('info', 'No active appointments.');
		}
	}
});

// Helper: refresh Approved table tbody from server
async function refreshApprovedTablePartial() {
	const table = document.querySelector('#svApprovedAdminsTable');
	if (!table) return;
	const res = await fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
	const html = await res.text();
	const parser = new DOMParser();
	const doc = parser.parseFromString(html, 'text/html');
	const newTbody = doc.querySelector('#svApprovedAdminsTable tbody');
	if (!newTbody) return;
	const currentTbody = table.querySelector('tbody');
	if (currentTbody) currentTbody.replaceWith(newTbody); else table.appendChild(newTbody);
	if (window.feather && typeof window.feather.replace === 'function') {
		window.feather.replace();
	}
}

// Helper: refresh Rejected table tbody from server
async function refreshRejectedTablePartial() {
	const table = document.querySelector('#svRejectedAdminsTable');
	if (!table) return;
	const res = await fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
	const html = await res.text();
	const parser = new DOMParser();
	const doc = parser.parseFromString(html, 'text/html');
	const newTbody = doc.querySelector('#svRejectedAdminsTable tbody');
	if (!newTbody) return;
	const currentTbody = table.querySelector('tbody');
	if (currentTbody) currentTbody.replaceWith(newTbody); else table.appendChild(newTbody);
	if (window.feather && typeof window.feather.replace === 'function') {
		window.feather.replace();
	}
}

