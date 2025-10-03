// -------------------------------------------------------------------------------
// * File: resources/js/superadmin/appointments.js
// * Description: Drives role → department → program linkage in Manage Admin modal
//                and bridges AJAX notifications to the floating alert overlay.
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-11] Initial creation – enable/disable Program by role, filter by department,
//              init on DOM ready and on modal show, supports Add + inline Edit forms.
// [2025-08-11] Alert bridge – listens to window "sv:alert" from AJAX layer and shows the
//              popup overlay (uses SV.alert → showAlertOverlay → inline fallback).
// -------------------------------------------------------------------------------

(function () {
  // ░░░ START: Alert Bridge Utilities ░░░

  /**
   * This tries SV.alert first (if your global helper exists), then falls back to
   * showAlertOverlay(), and finally to a tiny inline renderer so you always see a popup.
   * Use types: 'success' | 'info' | 'error'.
   */
  function showAlertBridge(message, type) {
    // Preferred: global helper (if you added it elsewhere)
    if (window.SV && typeof window.SV.alert === 'function') {
      window.SV.alert(message, type);
      return;
    }

    // Secondary: our earlier helper from alert-timer.js
    if (typeof window.showAlertOverlay === 'function') {
      window.showAlertOverlay(type || 'info', message || '');
      return;
    }

    // Last resort: inline minimal overlay (keeps UX working even if other files aren’t loaded)
    // NOTE: This uses your existing CSS classes from alert-overlay.css.
    try {
      document.querySelectorAll('.alert-overlay').forEach(el => el.remove());

      const overlay = document.createElement('div');
      overlay.className = 'alert-overlay';

      const alertEl = document.createElement('div');
      const t = type || 'info';
      alertEl.className = `alert alert-overlay-style alert-${t} d-flex align-items-center gap-2 show`;
      alertEl.setAttribute('role', 'alert');

      const icon = document.createElement('i');
      icon.setAttribute('data-feather', t === 'success' ? 'check-circle' : (t === 'error' ? 'x-circle' : 'info'));

      const msgDiv = document.createElement('div');
      msgDiv.textContent = message || '';

      const bar = document.createElement('div');
      bar.className = 'loading-bar ' + (t === 'success' ? 'green' : (t === 'error' ? 'red' : 'blue'));

      alertEl.appendChild(icon);
      alertEl.appendChild(msgDiv);
      alertEl.appendChild(bar);
      overlay.appendChild(alertEl);
      document.body.appendChild(overlay);

      // Feather icons (safe-guarded)
      try { window.feather?.replace?.(); } catch {}

      // Auto-dismiss to mirror your timer (1.5s + small fade)
      setTimeout(() => {
        alertEl.classList.remove('show');
        alertEl.classList.add('fade');
        setTimeout(() => overlay.remove(), 200);
      }, 1500);
    } catch (e) {
      // As a last fallback, log to console so something is visible during dev
      console[(type === 'error' ? 'error' : 'log')]((type || 'info').toUpperCase() + ': ' + (message || ''));
    }
  }

  /**
   * This listens for the global "sv:alert" event that your AJAX layer dispatches.
   * manage-accounts.js already does `window.dispatchEvent(new CustomEvent('sv:alert', { detail: { type, message } }))`
   * so we just render it here.
   */
  function bindGlobalAlertListener() {
    window.addEventListener('sv:alert', (ev) => {
      const detail = ev?.detail || {};
      const type = detail.type || 'info';
      const message = String(detail.message || '').trim();
      if (message) showAlertBridge(message, type);
    });
  }

  // ░░░ END: Alert Bridge Utilities ░░░


  // ░░░ START: Form-State Utilities (role → dept → program) ░░░

  /**
   * This returns the key controls within a given appointment form.
   * It makes selectors resilient and avoids null errors.
   */
  function getFormEls(form) {
    return {
      role: form.querySelector('.sv-role'),
      dept: form.querySelector('.sv-dept'),
      prog: form.querySelector('.sv-prog'),
    };
  }

  /**
   * This detects if the selected role is "Program Chair" using the option's label,
   * so it works whether your backend uses strings or ints for values.
   */
  function isProgramRole(roleSelect) {
    if (!roleSelect) return false;
    const opt = roleSelect.options[roleSelect.selectedIndex];
    const label = (opt && (opt.text || opt.label) || '').toLowerCase();
    return label.includes('program'); // robust against numeric values
  }

  /**
   * This filters Program options by department.
   * It keeps the placeholder (empty value) always visible.
   */
  function filterProgramsByDept(progSelect, deptId) {
    if (!progSelect) return;

    const want = String(deptId || '');

    Array.from(progSelect.options).forEach((opt) => {
      const isPlaceholder = opt.value === '';
      if (isPlaceholder) {
        opt.hidden = false;
        opt.disabled = false;
        return;
      }
      const optDept = String(opt.getAttribute('data-dept') || '');
      const show = want !== '' && optDept === want;
      opt.hidden = !show;
      opt.disabled = !show;
    });

    // If current selection is now hidden, clear it
    const selectedOpt = progSelect.options[progSelect.selectedIndex];
    if (selectedOpt && (selectedOpt.hidden || selectedOpt.disabled)) {
      progSelect.value = '';
    }
  }

  /**
   * Helper function to update department dropdown placeholder text
   */
  function updateDepartmentPlaceholder(deptSelect, text, removeForRequired = false) {
    if (!deptSelect) return;
    
    // Find existing placeholder option
    let placeholder = deptSelect.querySelector('option[value=""]');
    
    // If we want to remove the placeholder for required fields, remove it
    if (removeForRequired && placeholder) {
      placeholder.remove();
      return;
    }
    
    // If no placeholder option exists, create one (only for non-required cases)
    if (!placeholder && !removeForRequired) {
      placeholder = document.createElement('option');
      placeholder.value = '';
      deptSelect.insertBefore(placeholder, deptSelect.firstChild);
    }
    
    // Update the text if placeholder exists
    if (placeholder) {
      placeholder.textContent = text;
    }
  }

  /**
   * This enables/disables the Department and Program selects based on role,
   * and applies the filtering.
   */
  function updateFormState(form) {
    const { role, dept, prog } = getFormEls(form);
    if (!role || !dept) return;

    const selectedRole = role.value;
    const requiresDept = ['DEPT_CHAIR', 'DEAN'].includes(selectedRole);
    const isInstitutionWide = ['VCAA', 'ASSOC_VCAA'].includes(selectedRole);
    const programMode = isProgramRole(role);
    const deptId = dept.value;

    // Ensure role dropdown is never disabled
    role.disabled = false;
    role.removeAttribute('disabled');

    // Enable/disable department dropdown based on role
    if (requiresDept) {
      dept.disabled = false;
      dept.removeAttribute('disabled');
      // Remove placeholder option for department-required roles (Dean, Chair)
      updateDepartmentPlaceholder(dept, '', true);
      
      // If this was previously disabled and had a placeholder, clear the value
      if (dept.value === '') {
        dept.value = '';
      }
    } else {
      // Institution-wide roles (VCAA, Associate VCAA) don't need department
      dept.value = '';
      dept.disabled = true;
      dept.setAttribute('disabled', 'disabled');
      // Add placeholder for institution-wide roles
      updateDepartmentPlaceholder(dept, '— Not Required —', false);
    }

    // Handle program dropdown (only if prog exists - not in edit forms)
    if (prog) {
      if (programMode && deptId) {
        prog.disabled = false;
        prog.removeAttribute('disabled');
        filterProgramsByDept(prog, deptId);
      } else {
        // Not in Program mode, or no department yet — keep Program off
        prog.value = '';
        prog.disabled = true;
        prog.setAttribute('disabled', 'disabled');
        filterProgramsByDept(prog, ''); // unhide placeholder, hide others
      }
    }
  }

  /**
   * This wires listeners for a single form (Add or Edit) and applies the initial state.
   */
  function bindForm(form) {
    const { role, dept } = getFormEls(form);
    if (role) role.addEventListener('change', () => updateFormState(form));
    if (dept) dept.addEventListener('change', () => updateFormState(form));
    
    // Initial state
    updateFormState(form);
  }

  /**
   * This initializes all appointment forms currently in the DOM.
   */
  function initAllForms() {
    document.querySelectorAll('.sv-appt-form').forEach(bindForm);
  }

  // Make initAllForms available globally for re-initialization after AJAX updates
  window.initAllForms = initAllForms;

  // ░░░ END: Form-State Utilities ░░░


  // ░░░ START: Bootstrap Lifecycle Hooks ░░░

  /**
   * This initializes forms on page load and whenever a Manage Admin modal opens.
   */
  function bindLifecycle() {
    // On DOM ready
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initAllForms, { once: true });
    } else {
      initAllForms();
    }

    // On modal shown (re-bind inside that modal)
    document.addEventListener('shown.bs.modal', (ev) => {
      const modal = ev.target;
      if (!modal || !modal.classList.contains('sv-appt-modal')) return;
      modal.querySelectorAll('.sv-appt-form').forEach(bindForm);
    });
  }

  // ░░░ END: Bootstrap Lifecycle Hooks ░░░


  // ░░░ START: Init ░░░

  /**
   * This kicks off both the alert bridge and the form-state logic.
   */
  function init() {
    bindGlobalAlertListener(); // listen for AJAX-driven alerts (sv:alert)
    bindLifecycle();           // keep the role→dept→program UX working
  }

  // Fire immediately or on DOMContentLoaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }

  // ░░░ END: Init ░░░
})();
