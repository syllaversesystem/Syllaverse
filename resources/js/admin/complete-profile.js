// -------------------------------------------------------------------------------
// * File: resources/js/admin/complete-profile.js
// * Description: Stepper + chair-role UI logic for Admin Complete Profile (professional, non-modal) – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-08] Initial creation – checkbox-driven enable/disable and department→program filtering.
// [2025-08-08] Upgrade – two-step wizard (Next/Back), progress bar, gentle client-side validation.
// -------------------------------------------------------------------------------

/* Plain-English: bootstrap the page, wire the stepper, and keep program options filtered by department. */
document.addEventListener('DOMContentLoaded', () => {
  // Elements for the stepper
  const step1 = document.getElementById('svStep1');
  const step2 = document.getElementById('svStep2');
  const nextBtn = document.getElementById('svNextToStep2');
  const backBtn = document.getElementById('svBackToStep1');
  const progressBar = document.getElementById('svStepProgress');
  const stepBadges = document.querySelectorAll('.sv-step'); // [0]=Step1, [1]=Step2

  // Core inputs
  const designation = document.getElementById('designation');
  const employeeCode = document.getElementById('employee_code');

  // Role-request controls
  const cbDept = document.querySelector('#request_dept_chair');
  const cbProg = document.querySelector('#request_prog_chair');
  const selDept = document.querySelector('#department_id');
  const selProg = document.querySelector('#program_id');

  // If essential elements are missing, bail out gracefully.
  if (!step1 || !step2) return;

  // Determine if requests are locked (pending exists) by checking disabled state (set by Blade).
  const requestsLocked = !!(cbDept && cbProg && cbDept.disabled && cbProg.disabled);

  // --- Initial UI state
  showStep(1);
  if (cbDept && cbProg && selDept && selProg) {
    applyToggleState(cbDept, cbProg, selDept, selProg);
    filterProgramsByDepartment(selDept, selProg);
  }

  // --- Stepper wiring
  if (nextBtn) {
    nextBtn.addEventListener('click', () => {
      // Gentle client-side validation for Step 1
      let ok = true;
      ok &= validateRequired(designation);
      ok &= validateRequired(employeeCode);

      if (!ok) {
        // Scroll first invalid into view
        const firstInvalid = document.querySelector('.is-invalid');
        if (firstInvalid) firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
      }

      showStep(2);
      // When entering Step 2, keep options filtered.
      if (selDept && selProg) filterProgramsByDepartment(selDept, selProg);
    });
  }

  if (backBtn) {
    backBtn.addEventListener('click', () => showStep(1));
  }

  // --- Role toggles + filtering
  if (cbDept && cbProg && selDept && selProg) {
    cbDept.addEventListener('change', () => {
      applyToggleState(cbDept, cbProg, selDept, selProg);
    });

    cbProg.addEventListener('change', () => {
      applyToggleState(cbDept, cbProg, selDept, selProg);
      filterProgramsByDepartment(selDept, selProg);
    });

    selDept.addEventListener('change', () => {
      filterProgramsByDepartment(selDept, selProg);
    });
  }

  // --- Final submit guard (polite checks; server still validates)
  const form = document.getElementById('svCompleteProfileForm');
  if (form) {
    form.addEventListener('submit', (e) => {
      // If requests are locked (pending), we let it submit HR fields only (button is disabled in Blade anyway).
      if (requestsLocked) return;

      // If no role requested, allow submit (profile-only).
      const wantsDept = cbDept && cbDept.checked;
      const wantsProg = cbProg && cbProg.checked;

      // If Dept Chair requested → require department.
      if (wantsDept && selDept && !selDept.value) {
        e.preventDefault();
        markInvalid(selDept, 'Please select a department for the Department Chair request.');
        showStep(2);
        return;
      }

      // If Program Chair requested → require department AND program.
      if (wantsProg) {
        if (selDept && !selDept.value) {
          e.preventDefault();
          markInvalid(selDept, 'Please select a department to filter programs.');
          showStep(2);
          return;
        }
        if (selProg && !selProg.value) {
          e.preventDefault();
          markInvalid(selProg, 'Please select a program for the Program Chair request.');
          showStep(2);
          return;
        }
      }
    });
  }

  // ===== Helpers =====

  /* This switches between Step 1 and Step 2 and animates the progress bar / step badges. */
  function showStep(n) {
    const goStep1 = n === 1;
    step1.hidden = !goStep1;
    step2.hidden = goStep1;

    // Progress: 50% on step 1, 100% on step 2 (subtle, not literal)
    if (progressBar) {
      progressBar.style.width = goStep1 ? '50%' : '100%';
    }

    // Badge visuals
    if (stepBadges && stepBadges.length >= 2) {
      stepBadges.forEach(b => b.classList.remove('sv-step-active'));
      stepBadges[goStep1 ? 0 : 1].classList.add('sv-step-active');
    }

    // Focus first input of the current step (for keyboard/accessibility)
    if (goStep1 && designation) designation.focus();
    if (!goStep1 && !requestsLocked) {
      if (cbDept) cbDept.focus();
    }
  }

  /* This turns Department/Program selects on/off depending on chosen chair roles. */
  function applyToggleState(cbDept, cbProg, selDept, selProg) {
    const wantsDept = !!cbDept.checked;
    const wantsProg = !!cbProg.checked;
    const anyChair = wantsDept || wantsProg;

    // Department select is enabled when any chair role is requested
    selDept.disabled = !anyChair || requestsLocked;
    if (!anyChair) {
      clearSelect(selDept);
    }

    // Program select is only enabled for Program Chair
    selProg.disabled = !wantsProg || requestsLocked;
    if (!wantsProg) {
      clearSelect(selProg);
    }

    // Clear previous invalid state when toggling
    clearInvalid(selDept);
    clearInvalid(selProg);
  }

  /* This keeps the Program list relevant by showing only options under the selected Department. */
  function filterProgramsByDepartment(selDept, selProg) {
    const deptId = selDept.value || '';
    const options = Array.from(selProg.options);
    const placeholder = options.shift(); // keep always visible

    let selectedStillValid = false;

    options.forEach(opt => {
      const belongsTo = opt.getAttribute('data-dept') || '';
      const matches = deptId && belongsTo === deptId;

      if (matches) {
        opt.hidden = false;
        opt.disabled = false;
        if (opt.selected) selectedStillValid = true;
      } else {
        opt.hidden = true;
        opt.disabled = true;
        if (opt.selected) selectedStillValid = false;
      }
    });

    if (!deptId || !selectedStillValid) {
      selProg.value = '';
    }
  }

  /* Marks a field invalid with a custom message (Bootstrap-friendly). */
  function markInvalid(el, message) {
    if (!el) return;
    el.classList.add('is-invalid');
    // Try to find / create an adjacent invalid-feedback container
    let fb = el.parentElement?.querySelector('.invalid-feedback');
    if (!fb) {
      fb = document.createElement('div');
      fb.className = 'invalid-feedback';
      el.parentElement?.appendChild(fb);
    }
    fb.textContent = message || 'This field is required.';
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    el.focus();
  }

  /* Clears invalid state from a field. */
  function clearInvalid(el) {
    if (!el) return;
    el.classList.remove('is-invalid');
    const fb = el.parentElement?.querySelector('.invalid-feedback');
    if (fb) fb.textContent = '';
  }

  /* Quick required-field check that also shows invalid UI. */
  function validateRequired(el) {
    if (!el) return true;
    const ok = !!String(el.value || '').trim();
    if (!ok) markInvalid(el, 'This field is required.');
    else clearInvalid(el);
    return ok;
    }

  /* Resets a <select> to its placeholder option. */
  function clearSelect(selectEl) {
    if (!selectEl) return;
    selectEl.value = '';
  }
});
