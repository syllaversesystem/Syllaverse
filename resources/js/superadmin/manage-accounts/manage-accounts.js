/* 
-------------------------------------------------------------------------------
* File: resources/js/superadmin/manage-accounts/manage-accounts.js
* Description: Manage Accounts – tabs/icons, AJAX forms, popup-only validation,
*              appointments DOM updates, and revoke/re-approve flows with table refresh
-------------------------------------------------------------------------------
📜 Log:
[2025-08-11] Initial creation — tabs/icons/tooltips + AJAX + tab persistence.
[2025-08-11] Hardening — credentials, redirect/HTML detection, 401/403/419/422 handling.
[2025-08-11] DOM — render modal appointment list & badges from JSON (no Blade fragments), keep modal open.
[2025-08-11] Validation UX — popup-only 422 for .sv-appt-form when data-inline-errors="false".
[2025-08-11] Revoke UX v4 — force-close modal once, then refresh BOTH tables once; notify exactly once.
[2025-08-11] Table Sync — after appointment create/update/end/destroy, refresh Approved table (row-first).
[2025-08-11] Re-approve Sync — after re-approve, refresh Approved + Rejected tables (stay on tab).
-------------------------------------------------------------------------------
*/

(() => {
  const LS_KEYS = { top: 'sv.accountTabs.active', sub: 'sv.adminsSubTabs.active' };

  // ── Utilities ──────────────────────────────────────────────────────────────
  const refreshIcons = () => { try { window.feather?.replace?.(); } catch {} };

  // Only dispatch — singleton listener lives in alert-timer.js
  const notify = (message, type = 'success') => {
    try { window.dispatchEvent(new CustomEvent('sv:alert', { detail: { type, message } })); }
    catch { console.log((type || 'info').toUpperCase() + ': ' + (message || '')); }
  };

  const clearErrors = (form) => {
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback[data-sv]').forEach(el => el.remove());
  };

  const INLINE_HIGHLIGHTS_DEFAULT = true;
  const shouldInline = (form) => {
    const raw = (form.getAttribute('data-inline-errors') || '').trim().toLowerCase();
    if (['false','0','no','off'].includes(raw)) return false;
    return INLINE_HIGHLIGHTS_DEFAULT;
  };

  const markErrors = (form, errors) => {
    clearErrors(form);
    if (!shouldInline(form)) return; // popup-only path
    Object.entries(errors || {}).forEach(([raw, msgs]) => {
      const msg = Array.isArray(msgs) ? msgs[0] : String(msgs || 'Invalid.');
      const base = raw.replace(/\.\d+$/, '');
      const field =
        form.querySelector(`[name="${base}"]`) ||
        form.querySelector(`[name="${base}[]"]`) ||
        form.querySelector(`[name="data[${base}]"]`) ||
        form.querySelector(`[name^="${base}"]`);
      if (!field) return;
      field.classList.add('is-invalid');
      const fb = document.createElement('div');
      fb.className = 'invalid-feedback d-block';
      fb.setAttribute('data-sv', '1');
      fb.textContent = msg;
      field.insertAdjacentElement('afterend', fb);
    });
    const first = form.querySelector('.is-invalid');
    if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
  };

  const formatErrorsForPopup = (errors) => {
    const lines = [];
    Object.values(errors || {}).forEach(arr => {
      const first = Array.isArray(arr) ? arr[0] : String(arr || '');
      if (first) lines.push(`• ${first}`);
    });
    return lines.length ? `Please fix the following:\n${lines.join('\n')}` : 'Please fix the highlighted fields.';
  };

  const getCsrf = (root) =>
    root?.querySelector?.('input[name="_token"]')?.value ||
    document.querySelector('meta[name="csrf-token"]')?.content || '';

  // ── Tab persistence ────────────────────────────────────────────────────────
  function initTabHandlers() {
    const save = (btn) => {
      const target = btn.getAttribute('data-bs-target') || btn.getAttribute('href');
      if (!target) return;
      if (btn.closest('#accountTabs')) localStorage.setItem(LS_KEYS.top, target);
      if (btn.closest('#adminsSubTabs')) localStorage.setItem(LS_KEYS.sub, target);
      setTimeout(refreshIcons, 0);
    };
    document.querySelectorAll('#accountTabs [data-bs-toggle="tab"], #accountTabs [data-bs-toggle="pill"]').forEach(el => {
      el.addEventListener('shown.bs.tab', () => save(el));
    });
    document.querySelectorAll('#adminsSubTabs [data-bs-toggle="tab"], #adminsSubTabs [data-bs-toggle="pill"]').forEach(el => {
      el.addEventListener('shown.bs.tab', () => save(el));
    });
  }
  const showTab = (selector) => {
    if (!selector) return;
    const btn = document.querySelector(`[data-bs-target="${selector}"]`);
    const Tab = window.bootstrap?.Tab;
    if (btn && Tab) new Tab(btn).show();
  };
  function restoreActiveTabs(){
    const hash = location.hash;
    const topSaved = localStorage.getItem(LS_KEYS.top);
    const subSaved = localStorage.getItem(LS_KEYS.sub);
    if ((hash && hash.startsWith('#admins-')) || (subSaved && subSaved.startsWith('#admins-'))) {
      showTab('#admins');
    }
    setTimeout(() => {
      showTab(topSaved || '#admins');
      showTab(hash || subSaved || '#admins-approvals');
      setTimeout(refreshIcons, 0);
    }, 30);
  }

  // ── Chevron + tooltips ─────────────────────────────────────────────────────
  function setChevronFor(id, open){
    const btn = document.querySelector(`.sv-row-toggle[data-bs-target="#${id}"]`);
    const icon = btn?.querySelector('i[data-feather]');
    if (icon){ icon.setAttribute('data-feather', open ? 'chevron-up' : 'chevron-down'); refreshIcons(); }
  }
  function initCollapseChevronHandlers(){
    document.querySelectorAll('.tab-content .collapse[id^="sv-"]').forEach(coll => {
      coll.addEventListener('shown.bs.collapse', () => setChevronFor(coll.id, true));
      coll.addEventListener('hidden.bs.collapse', () => setChevronFor(coll.id, false));
    });
  }
  function initTooltips(){
    const Tooltip = window.bootstrap?.Tooltip;
    if (!Tooltip) return;
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new Tooltip(el));
  }

  // ── Appointments render helpers (no Blade fragments) ───────────────────────
  const roleLabel = (appt) => appt && appt.is_prog ? 'Program Chair' : (appt && appt.is_dept ? 'Dept Chair' : (appt && appt.role ? appt.role : 'Appointment'));

  const getAddForm = (modal) => modal?.querySelector('form[data-sv-scope^="add-"]');
  const getDeptOptionsHTML = (modal, selectedId) => {
    const src = getAddForm(modal)?.querySelector('.sv-dept');
    const opts = src ? Array.from(src.options) : [];
    return opts.map(o => {
      const sel = String(o.value) === String(selectedId || '') ? ' selected' : '';
      return `<option value="${o.value}"${sel}>${o.text}</option>`;
    }).join('');
  };
  // Program select removed — Program Chair no longer supported

  const renderApptItemHTML = (modal, adminId, appt) => {
    const csrf = getCsrf(modal);
    const base = (getAddForm(modal)?.action || '/superadmin/appointments').replace(/\/+$/, '');
    const collapseId = `sv-appt-edit-${appt.id}`;
    const progDisabledAttr = appt.is_dept ? ' disabled' : '';
    return `
      <div class="sv-request-item">
        <div class="sv-request-meta">
          <span class="sv-pill is-accent sv-pill--sm">${roleLabel(appt)}</span>
          ${appt.scope_label ? `<span class="sv-pill is-muted sv-pill--sm">${appt.scope_label}</span>` : ''}
        </div>
        <div class="sv-request-actions">
          <button class="action-btn edit" type="button"
                  data-bs-toggle="collapse" data-bs-target="#${collapseId}"
                  aria-expanded="false" aria-controls="${collapseId}"
                  title="Edit appointment" aria-label="Edit appointment">
            <i data-feather="edit-3"></i>
          </button>
          <form method="POST" action="${base}/${appt.id}/end" class="d-inline" data-ajax="true" data-inline-errors="false">
            <input type="hidden" name="_token" value="${csrf}">
            <button class="action-btn reject" type="submit" title="Delete appointment" aria-label="Delete appointment">
              <i data-feather="x"></i>
            </button>
          </form>
        </div>
      </div>

      <div id="${collapseId}" class="collapse sv-details">
        <form method="POST" action="${base}/${appt.id}" class="row g-2 align-items-end sv-appt-form" data-sv-scope="edit-${appt.id}" data-ajax="true" data-inline-errors="false">
          <input type="hidden" name="_token" value="${csrf}">
          <input type="hidden" name="_method" value="PUT">

          <div class="col-md-4">
            <label class="form-label small">Role</label>
            <select name="role" class="form-select form-select-sm sv-role">
              <option value="DEPT_CHAIR"${(appt && (appt.is_dept || appt.is_prog)) ? ' selected' : ''}>Program/Department Chair</option>
              <option value="DEAN"${appt && appt.role === 'DEAN' ? ' selected' : ''}>Dean</option>
              <option value="VCAA"${appt && appt.role === 'VCAA' ? ' selected' : ''}>VCAA</option>
              <option value="ASSOC_VCAA"${appt && appt.role === 'ASSOC_VCAA' ? ' selected' : ''}>Associate VCAA</option>
            </select>
          </div>

          <div class="col-md-7">
            <label class="form-label small">Department</label>
            <select name="department_id" class="form-select form-select-sm sv-dept"${(appt && (appt.role === 'VCAA' || appt.role === 'ASSOC_VCAA')) ? ' disabled' : ''}>
              ${(appt && (appt.role === 'VCAA' || appt.role === 'ASSOC_VCAA')) ? '<option value="">— Not Required —</option>' : ''}
              ${getDeptOptionsHTML(modal, appt.dept_id)}
            </select>
          </div>

          <div class="col-md-1 d-flex">
            <button class="action-btn approve ms-auto" type="submit" title="Save changes" aria-label="Save changes">
              <i data-feather="check"></i>
            </button>
          </div>
        </form>
      </div>
    `;
  };

  const renderApptList = (modal, adminId, appointments) => {
    const list = modal?.querySelector(`#sv-appt-list-${adminId}`);
    if (!list) return;
    if (!appointments || !appointments.length) {
      list.innerHTML = `<div class="text-muted">No active appointments for this admin.</div>`;
      refreshIcons();
      return;
    }
    list.innerHTML = appointments.map(a => renderApptItemHTML(modal, adminId, a)).join('');
    modal.dispatchEvent(new CustomEvent('shown.bs.modal', { bubbles: true }));
    refreshIcons();
    
    // Re-initialize appointment forms for the new edit forms
    if (window.initAllForms && typeof window.initAllForms === 'function') {
      // Use setTimeout to ensure DOM is fully updated before initializing forms
      setTimeout(() => {
        window.initAllForms();
      }, 10);
    }
  };

  const renderBadgesCell = (adminId, appointments) => {
    const cell = document.getElementById(`sv-active-badges-${adminId}`);
    if (!cell) return;
    if (!appointments || !appointments.length) {
      cell.innerHTML = `<span class="text-muted">—</span>`;
      return;
    }
    const html = [
      `<div class="d-flex flex-wrap gap-2">`,
      ...appointments.map(a => `<span class="badge bg-secondary">${roleLabel(a)} — ${a.scope_label}</span>`),
      `</div>`
    ].join('');
    cell.innerHTML = html;
  };

  // ── Table refresh helpers ──────────────────────────────────────────────────
  async function fetchDocHTML() {
    const res  = await fetch(window.location.href, { headers: { 'Accept': 'text/html' } });
    const html = await res.text();
    return new DOMParser().parseFromString(html, 'text/html');
  }

  async function refreshApprovedTableBodyFromDoc(doc) {
    const table = document.querySelector('#svApprovedAdminsTable');
    if (!table) return false;
    const newTbody = doc.querySelector('#svApprovedAdminsTable tbody');
    const oldTbody = table.querySelector('tbody');
    if (newTbody && oldTbody) {
      oldTbody.replaceWith(newTbody);
      refreshIcons();
      return true;
    }
    return false;
  }

  /** Try to replace only the affected row; fallback to whole tbody. */
  async function refreshApprovedRow(adminId) {
    const table = document.querySelector('#svApprovedAdminsTable');
    if (!table) return;
    try {
      const doc = await fetchDocHTML();
      const selector = `#svApprovedAdminsTable #sv-approved-row-${adminId}`;
      const newRow = doc.querySelector(selector);
      const curRow = document.querySelector(selector);
      if (newRow && curRow) {
        curRow.replaceWith(newRow);
        refreshIcons();
        return;
      }
      await refreshApprovedTableBodyFromDoc(doc);
    } catch (e) {
      console.warn('Approved row refresh failed, falling back:', e);
      try { const doc = await fetchDocHTML(); await refreshApprovedTableBodyFromDoc(doc); } catch {}
    }
  }

  async function refreshApprovedTable() {
    try {
      const doc = await fetchDocHTML();
      await refreshApprovedTableBodyFromDoc(doc);
    } catch (e) {
      console.warn('Approved table refresh failed:', e);
    }
  }

  async function refreshRejectedTable() {
    const table = document.querySelector('#svRejectedAdminsTable');
    if (!table) return;
    try {
      const res  = await fetch(window.location.href, { headers: { 'Accept': 'text/html' } });
      const html = await res.text();
      const doc  = new DOMParser().parseFromString(html, 'text/html');
      const newTbody = doc.querySelector('#svRejectedAdminsTable tbody');
      const oldTbody = table.querySelector('tbody');
      if (newTbody && oldTbody) {
        oldTbody.replaceWith(newTbody);
        refreshIcons();
      }
    } catch (e) {
      console.warn('Rejected table refresh failed:', e);
    }
  }

  // ── Revoke / Re-approve helpers ────────────────────────────────────────────
  function isRevokeRequest(form) {
    if (!form) return false;
    if (form.hasAttribute('data-sv-revoke')) return true; // explicit flag (recommended)
    const url = form.action || '';
    return /\/reject(\/|$)/i.test(url) || /\/revoke(\/|$)/i.test(url);
  }

  function isReapproveRequest(form) {
    if (!form) return false;
    if (form.hasAttribute('data-sv-reapprove')) return true; // from Blade we added
    const url = form.action || '';
    return /\/approve(\/|$)/i.test(url);
  }

  // Try: dismiss button → Bootstrap API → forced DOM hide
  function closeParentModal(el) {
    const modal = el?.closest?.('.modal') || null;
    if (!modal) return { closed: false, modal: null };

    const dismissBtn = modal.querySelector('[data-bs-dismiss="modal"]');
    if (dismissBtn) { dismissBtn.click(); return { closed: true, modal, via: 'button' }; }

    try {
      const Modal = window.bootstrap?.Modal;
      if (Modal) {
        const instance = Modal.getInstance(modal) || new Modal(modal);
        instance.hide();
        return { closed: true, modal, via: 'api' };
      }
    } catch {}

    modal.classList.remove('show');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
    return { closed: true, modal, via: 'forced' };
  }

  // Close modal NOW; refresh both tables once (guarded) and notify once.
  function closeModalThenRefresh(form, message) {
    const modal = form.closest('.modal');
    let done = false;
    const once = async () => {
      if (done) return; done = true;
      await Promise.all([refreshApprovedTable(), refreshRejectedTable()]);
      notify(message || 'Admin access revoked.');
    };

    const result = closeParentModal(form);
    if (!modal) { once(); return; }

    if (result.via === 'forced') { setTimeout(once, 60); return; }

    const onHidden = () => { modal.removeEventListener('hidden.bs.modal', onHidden); once(); };
    modal.addEventListener('hidden.bs.modal', onHidden, { once: true });

    // Safety: if hidden never fires, still run once.
    setTimeout(once, 700);
  }

  // ── AJAX interceptor ───────────────────────────────────────────────────────
  function initAjaxForms(){
    document.addEventListener('submit', async (ev) => {
      const form = ev.target;
      if (!(form instanceof HTMLFormElement)) return;
      if (!form.matches('form[data-ajax="true"]')) return;

      ev.preventDefault();

      const btn = form.querySelector('button[type="submit"], .action-btn.approve, .action-btn.reject');
      btn?.setAttribute('disabled', 'disabled');

      const url = form.action;
      const fd  = new FormData(form);
      let method = (form.getAttribute('method') || 'POST').toUpperCase();
      
      // Always use POST for Laravel method spoofing to work properly
      if (fd.has('_method')) { 
        method = 'POST'; // Keep as POST and let Laravel handle method spoofing
      }
      const token = getCsrf(form);

      try {
        const res = await fetch(url, {
          method,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            ...(token ? { 'X-CSRF-TOKEN': token } : {}),
          },
          body: fd,
          credentials: 'same-origin',
        });

        const ct = res.headers.get('content-type') || '';
        const isJSON = ct.includes('application/json');

        if (res.redirected || ct.includes('text/html')) {
          notify('Your session may have expired. Reloading…', 'error');
          window.location.reload();
          return;
        }

        const payload = isJSON ? await res.json() : null;

        if ([401,403,419].includes(res.status)) {
          notify('Session/auth issue. Please refresh and try again.', 'error');
          return;
        }

        if (res.status === 422 && payload && payload.errors) {
          notify(formatErrorsForPopup(payload.errors), 'error'); // popup-only
          clearErrors(form);
          // markErrors(form, payload.errors); // keep disabled if you prefer popup-only
          return;
        }

        if (!res.ok || (payload && payload.ok === false)) {
          throw new Error((payload && (payload.message || payload.error)) || `Request failed (${res.status})`);
        }

        // ✅ Revoke: close modal immediately, then refresh tables and notify once
        if (isRevokeRequest(form)) {
          closeModalThenRefresh(form, payload?.message || 'Admin access revoked.');
          return;
        }

        // ✅ Re-approve: refresh Approved + Rejected tables (stay on current tab)
        if (isReapproveRequest(form)) {
          await Promise.all([refreshApprovedTable(), refreshRejectedTable()]);
          notify(payload?.message || 'Admin re-approved.');
          return;
        }

        // ✅ Appointments: keep modal open; update inline list + badges, then refresh Approved row
        const modal = form.closest('.modal');
        if (payload && payload.admin_id && Array.isArray(payload.appointments)) {
          renderApptList(modal, payload.admin_id, payload.appointments);
          renderBadgesCell(payload.admin_id, payload.appointments);
          await refreshApprovedRow(payload.admin_id); // row-first (falls back to tbody)
        }

        // If this was the Add form, reset and keep Program disabled
        if (form.matches('[data-sv-scope^="add-"]')) {
          form.reset();
          const prog = form.querySelector('.sv-prog');
          if (prog) { prog.disabled = true; prog.setAttribute('disabled','disabled'); }
          clearErrors(form);
        }

        notify((payload && payload.message) || 'Saved.');

      } catch (e) {
        notify(e.message || 'Something went wrong.', 'error');
      } finally {
        btn?.removeAttribute('disabled');
      }
    });
  }

  // ── Init ───────────────────────────────────────────────────────────────────
  function init(){
    refreshIcons();
    initTabHandlers();
    initCollapseChevronHandlers();
    initTooltips();
    restoreActiveTabs();
    initAjaxForms();
  }

  document.readyState === 'loading'
    ? document.addEventListener('DOMContentLoaded', init)
    : init();
})();
