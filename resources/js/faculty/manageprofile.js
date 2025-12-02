// Manage Profile JS: handles form save, role requests, and delete

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('manageProfileForm');
  const saveBtn = document.getElementById('mpSaveBtn');
  const deleteBtn = document.getElementById('mpDeleteBtn');
  const roleForm = document.getElementById('mpRoleRequestForm');
  const roleStatusList = document.getElementById('mpRoleStatusList');

  function getToken(){
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  if (form && saveBtn) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      saveBtn.disabled = true;
      try {
        const fd = new FormData(form);
        const res = await fetch('/faculty/manage-profile', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': getToken(), 'Accept':'application/json' },
          body: fd,
        });
        if (!res.ok) {
          let msg = 'Failed to update profile';
          try { const j = await res.json(); if (j.errors) msg = Array.isArray(Object.values(j.errors)[0]) ? Object.values(j.errors)[0][0] : Object.values(j.errors)[0]; } catch(e){}
          if (window.showAlertOverlay) window.showAlertOverlay('error', msg); else alert(msg);
        } else {
          if (window.showAlertOverlay) window.showAlertOverlay('success', 'Profile updated'); else alert('Profile updated');
        }
      } catch (err) {
        console.error(err);
        alert('Unexpected error');
      } finally {
        saveBtn.disabled = false;
      }
    });
  }

  if (roleForm) {
    // Toggle department selects enabled state based on checkbox
    const rid = ['dean','assoc_dean','dept_chair','faculty'];
    rid.forEach(r => {
      const cb = document.getElementById(`mpRole_${r}`);
      const sel = document.getElementById(`mpDept_${r}`);
      if (cb && sel) {
        const sync = () => {
          const isCurrent = cb.dataset && cb.dataset.current === '1';
          sel.disabled = !cb.checked && !isCurrent; // keep enabled if current even when unchecked
          if (!cb.checked) {
            // Keep current roles' departments rendered; do not clear when unchecked
            if (!isCurrent && r !== 'faculty') {
              // Reset department when unchecked for non-faculty roles
              sel.selectedIndex = 0;
              if (sel.options.length && sel.options[0].value !== '') {
                const emptyOpt = Array.from(sel.options).find(o => o.value === '');
                if (emptyOpt) sel.value = '';
              } else {
                sel.value = '';
              }
              sel.dispatchEvent(new Event('change'));
            }
          }
        };
        cb.addEventListener('change', sync);
        sync();
      }
    });

    // If any leadership role is checked, uncheck and restrict Faculty
    const leadership = ['dean','assoc_dean','dept_chair'];
    const facultyCb = document.getElementById('mpRole_faculty');
    const facultySel = document.getElementById('mpDept_faculty');
    function updateFacultyRestriction() {
      const anyLeaderChecked = leadership.some(r => {
        const c = document.getElementById(`mpRole_${r}`);
        return c && c.checked;
      });
      if (!facultyCb || !facultySel) return;
      if (anyLeaderChecked) {
        facultyCb.checked = false;
        facultyCb.disabled = true;
        // Keep faculty department rendered (do not clear), but disable while restricted
        facultySel.disabled = true;
        // Optional: title hint
        facultyCb.title = 'Faculty is auto-included with leadership requests';
      } else {
        facultyCb.disabled = false;
        // Re-sync select enablement with current checkbox state
        facultySel.disabled = !facultyCb.checked;
        facultyCb.title = '';
      }
    }
    leadership.forEach(r => {
      const cb = document.getElementById(`mpRole_${r}`);
      if (cb) cb.addEventListener('change', updateFacultyRestriction);
    });
    // Initialize on load
    updateFacultyRestriction();

    // Dean vs Associate Dean mutual exclusion (also honor pending requests)
    const deanCb = document.getElementById('mpRole_dean');
    const assocCb = document.getElementById('mpRole_assoc_dean');
    const deanSel = document.getElementById('mpDept_dean');
    const assocSel = document.getElementById('mpDept_assoc_dean');

    function resetSelect(sel) {
      if (!sel) return;
      sel.selectedIndex = 0;
      sel.value = '';
      sel.dispatchEvent(new Event('change'));
    }

    function updateDeanAssocRestriction() {
      const deanPending = (deanCb && deanCb.dataset && deanCb.dataset.pending === '1');
      const assocPending = (assocCb && assocCb.dataset && assocCb.dataset.pending === '1');

      if (deanCb && assocCb) {
        // Only enforce pending restrictions; do not auto-uncheck or mutually exclude based on checked state
        if (deanPending) {
          assocCb.disabled = true;
          if (assocSel) { assocSel.disabled = true; }
          assocCb.title = 'Associate Dean disabled due to pending Dean request';
        } else {
          assocCb.disabled = false;
          assocCb.title = '';
          if (assocSel) assocSel.disabled = !assocCb.checked;
        }

        if (assocPending) {
          deanCb.disabled = true;
          if (deanSel) { deanSel.disabled = true; }
          deanCb.title = 'Dean disabled due to pending Associate Dean request';
        } else {
          deanCb.disabled = false;
          deanCb.title = '';
          if (deanSel) deanSel.disabled = !deanCb.checked;
        }
      }
    }

    if (deanCb) deanCb.addEventListener('change', updateDeanAssocRestriction);
    if (assocCb) assocCb.addEventListener('change', updateDeanAssocRestriction);
    // Initialize on load
    updateDeanAssocRestriction();

    // Global single-select restriction across all roles
    const allRoles = ['dean','assoc_dean','dept_chair','faculty'];
    function updateGlobalRoleRestriction() {
      const checked = allRoles.filter(r => {
        const cb = document.getElementById(`mpRole_${r}`);
        return cb && cb.checked;
      });
      if (checked.length > 0) {
        const selected = checked[0];
        allRoles.forEach(r => {
          const cb = document.getElementById(`mpRole_${r}`);
          const sel = document.getElementById(`mpDept_${r}`);
          if (!cb || !sel) return;
          const isCurrent = cb.dataset && cb.dataset.current === '1';
          if (r === selected) {
            cb.disabled = false; // keep selected usable
            sel.disabled = false; // allow dept selection for selected
          } else {
            cb.checked = false;
            cb.disabled = true;
            sel.disabled = !isCurrent; // keep enabled for current role
            // Keep Faculty department rendered even when another role is selected
            if (!isCurrent && r !== 'faculty') {
              // Reset department for other non-selected roles
              sel.selectedIndex = 0; sel.value = ''; sel.dispatchEvent(new Event('change'));
            }
            cb.title = 'Disabled: another role is selected';
          }
        });
      } else {
        // No selection: re-enable all roles unless restricted by pending flags
        allRoles.forEach(r => {
          const cb = document.getElementById(`mpRole_${r}`);
          const sel = document.getElementById(`mpDept_${r}`);
          if (!cb || !sel) return;
          const pending = cb.dataset && cb.dataset.pending === '1';
          cb.disabled = !!pending; // keep disabled if pending
          if (pending) {
            cb.title = 'Disabled: pending request exists';
            sel.disabled = true; // ensure select disabled when pending
          } else {
            cb.title = '';
            sel.disabled = !cb.checked;
          }
        });
        // Also re-apply leadership/faculty & dean/assoc logic when none selected
        updateFacultyRestriction();
        updateDeanAssocRestriction();
      }
    }
    // Bind global handler to all role checkbox changes
    allRoles.forEach(r => {
      const cb = document.getElementById(`mpRole_${r}`);
      if (cb) cb.addEventListener('change', updateGlobalRoleRestriction);
    });
    // Initialize global restriction after initial syncs
    updateGlobalRoleRestriction();
    // Intercept submit to show confirmation modal
    roleForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const submitBtn = document.getElementById('mpSubmitRoleBtn');
      if (submitBtn && submitBtn.disabled) {
        if (window.showAlertOverlay) window.showAlertOverlay('error', 'You have a pending request. Please wait for a decision.');
        else alert('You have a pending request. Please wait for a decision.');
        return;
      }
      const checked = Array.from(document.querySelectorAll('input[name="roles[]"]:checked'));
      if (!checked.length) { if (window.showAlertOverlay) window.showAlertOverlay('error', 'Please select at least one role'); else alert('Please select at least one role'); return; }
      // Show confirmation modal styled like Add CDIO
      const modalEl = document.getElementById('mpConfirmModal');
      const confirmBtn = document.getElementById('mpConfirmSubmitBtn');
      const Modal = window.bootstrap?.Modal;
      if (modalEl && confirmBtn && Modal) {
        const modal = new Modal(modalEl, { backdrop: 'static' });
        // Customize message based on selection and current roles
        const deanCbEl = document.getElementById('mpRole_dean');
        const assocCbEl = document.getElementById('mpRole_assoc_dean');
        const deanCurrent = deanCbEl && deanCbEl.dataset && deanCbEl.dataset.current === '1';
        const assocCurrent = assocCbEl && assocCbEl.dataset && assocCbEl.dataset.current === '1';
        const selectedRole = checked[0]?.value || '';
        const modalBody = modalEl.querySelector('.modal-body');
        let note = 'Please confirm you want to submit the selected role request. You won’t be able to send another until this is approved or rejected.';
        if (selectedRole === 'assoc_dean' && deanCurrent) {
          note = 'Requesting Associate Dean will replace your current Dean role upon approval.';
        } else if (selectedRole === 'dean' && assocCurrent) {
          note = 'Requesting Dean will replace your current Associate Dean role upon approval.';
        } else if (selectedRole === 'faculty' && (deanCurrent || assocCurrent || (document.getElementById('mpRole_dept_chair')?.dataset?.current === '1'))) {
          note = 'Submitting a Faculty request while currently holding a leadership role (Dean/Associate Dean/Department Chair) may result in a downgrade of your appointment upon approval.';
        }
        if (modalBody) { modalBody.innerHTML = `<p class="mb-0 small text-muted">${note}</p>`; }
        const onConfirm = async () => {
          confirmBtn.disabled = true;
          if (submitBtn) submitBtn.disabled = true;
          try {
            const fd = new FormData();
            checked.forEach(cb => {
              const role = cb.value;
              fd.append('roles[]', role);
              const sel = document.getElementById(`mpDept_${role}`);
              if (sel && sel.value) fd.append(`department_id[${role}]`, sel.value);
            });
            const res = await fetch('/faculty/manage-profile/request-role', {
              method: 'POST',
              headers: { 'X-CSRF-TOKEN': getToken(), 'Accept':'application/json' },
              body: fd,
            });
            let data = {};
            try { data = await res.json(); } catch {}
            if (res.ok) {
              modal.hide();
              if (window.showAlertOverlay) window.showAlertOverlay('success', 'Role request submitted'); else alert('Role request submitted');
              setTimeout(() => { window.location.reload(); }, 350);
            } else {
              const msg = data?.message || 'Failed to submit role request';
              if (window.showAlertOverlay) window.showAlertOverlay('error', msg); else alert(msg);
            }
          } catch (err) {
            console.error(err);
            alert('Unexpected error');
          } finally {
            confirmBtn.disabled = false;
            if (submitBtn) submitBtn.disabled = false;
            confirmBtn.removeEventListener('click', onConfirm);
          }
        };
        // Ensure no stale listeners
        confirmBtn.addEventListener('click', onConfirm, { once: true });
        modal.show();
      } else {
        // Fallback to direct submit
        if (submitBtn) submitBtn.disabled = true;
        try {
          const fd = new FormData();
          checked.forEach(cb => {
            const role = cb.value;
            fd.append('roles[]', role);
            const sel = document.getElementById(`mpDept_${role}`);
            if (sel && sel.value) fd.append(`department_id[${role}]`, sel.value);
          });
          const res = await fetch('/faculty/manage-profile/request-role', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': getToken(), 'Accept':'application/json' },
            body: fd,
          });
          let data = {};
          try { data = await res.json(); } catch {}
          if (res.ok) {
            if (window.showAlertOverlay) window.showAlertOverlay('success', 'Role request submitted'); else alert('Role request submitted');
            setTimeout(() => { window.location.reload(); }, 350);
          } else {
            const msg = data?.message || 'Failed to submit role request';
            if (window.showAlertOverlay) window.showAlertOverlay('error', msg); else alert(msg);
          }
        } catch (err) {
          console.error(err);
          alert('Unexpected error');
        } finally {
          if (submitBtn) submitBtn.disabled = false;
        }
      }
    });
  }

  // Auto-disappear approved/rejected after 24h once seen
  (function handleRoleStatusVisibility(){
    if (!roleStatusList) return;
    const key = 'mp_role_seen_times';
    let seen = {};
    try { seen = JSON.parse(localStorage.getItem(key) || '{}'); } catch { seen = {}; }
    const now = Date.now();
    roleStatusList.querySelectorAll('.mp-role-status-item').forEach(item => {
      const id = item.dataset.id;
      const status = (item.dataset.status || '').toLowerCase();
      if (!id) return;
      if (status === 'approved' || status === 'rejected') {
        const firstSeen = seen[id] ? Number(seen[id]) : null;
        if (!firstSeen) {
          seen[id] = now;
        } else {
          const diffHrs = (now - firstSeen) / (1000 * 60 * 60);
          if (diffHrs >= 24) {
            item.remove();
          }
        }
      }
    });
    try { localStorage.setItem(key, JSON.stringify(seen)); } catch {}
  })();

  if (deleteBtn) {
    deleteBtn.addEventListener('click', async () => {
      const modalEl = document.getElementById('mpDeleteConfirmModal');
      const confirmBtn = document.getElementById('mpDeleteConfirmBtn');
      const Modal = window.bootstrap?.Modal;
      if (modalEl && confirmBtn && Modal) {
        const modal = new Modal(modalEl, { backdrop: 'static' });
        const onConfirm = async () => {
          confirmBtn.disabled = true;
          deleteBtn.disabled = true;
          try {
            const res = await fetch('/faculty/manage-profile', {
              method: 'DELETE',
              headers: { 'X-CSRF-TOKEN': getToken(), 'Accept':'application/json' },
            });
            if (res.ok) {
              modal.hide();
              if (window.showAlertOverlay) window.showAlertOverlay('success', 'Account deleted'); else alert('Account deleted');
              window.location.href = '/faculty/login';
            } else {
              if (window.showAlertOverlay) window.showAlertOverlay('error', 'Failed to delete account'); else alert('Failed to delete account');
            }
          } catch (err) {
            console.error(err);
            alert('Unexpected error');
          } finally {
            confirmBtn.disabled = false;
            deleteBtn.disabled = false;
            confirmBtn.removeEventListener('click', onConfirm);
          }
        };
        confirmBtn.addEventListener('click', onConfirm, { once: true });
        modal.show();
      } else {
        // Fallback to native confirm
        if (!confirm('This will delete your account permanently. Continue?')) return;
        deleteBtn.disabled = true;
        try {
          const res = await fetch('/faculty/manage-profile', {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': getToken(), 'Accept':'application/json' },
          });
          if (res.ok) {
            if (window.showAlertOverlay) window.showAlertOverlay('success', 'Account deleted'); else alert('Account deleted');
            window.location.href = '/faculty/login';
          } else {
            if (window.showAlertOverlay) window.showAlertOverlay('error', 'Failed to delete account'); else alert('Failed to delete account');
          }
        } catch (err) {
          console.error(err);
          alert('Unexpected error');
        } finally {
          deleteBtn.disabled = false;
        }
      }
    });
  }
});
