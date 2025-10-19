// -------------------------------------------------------------------------------
// * File: resources/js/faculty/complete-profile.js
// * Description: Stepper + chair-role UI logic for Faculty Complete Profile (professional, non-modal) – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-10-18] Copied from admin complete profile and adapted for faculty users
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
  const name = document.getElementById('name');
  const email = document.getElementById('email');
  const designation = document.getElementById('designation');
  const employeeCode = document.getElementById('employee_code');

  // Role-request controls
  const cbDept = document.querySelector('#request_dept_chair');
  const cbProg = null; // Program Chair removed
  const cbVcaa = document.querySelector('#request_vcaa');
  const cbAssocVcaa = document.querySelector('#request_assoc_vcaa');
  const cbDean = document.querySelector('#request_dean');
  const cbAssocDean = document.querySelector('#request_assoc_dean');
  const cbFaculty = document.querySelector('#request_faculty');
  const selDept = document.querySelector('#department_id');
  const selFacultyDept = document.querySelector('#faculty_department_id');
  const selProg = null; // Program select removed

  // If essential elements are missing, bail out gracefully.
  if (!step1 || !step2) return;

  // Determine if requests are locked (pending exists) by checking disabled state (set by Blade).
  const allCheckboxes = [cbDept, cbVcaa, cbAssocVcaa, cbDean, cbAssocDean, cbFaculty].filter(Boolean);
  const requestsLocked = allCheckboxes.length > 0 && allCheckboxes.every(cb => cb.disabled);

  // --- Initial UI state
  showStep(1);
  if (selDept || selFacultyDept) {
    applyToggleState(cbDept, cbProg, selDept, selProg, cbVcaa, cbAssocVcaa, cbDean, cbAssocDean, cbFaculty, selFacultyDept);
  }

  // --- Stepper wiring
  if (nextBtn) {
    nextBtn.addEventListener('click', () => {
      // Gentle client-side validation for Step 1
      let ok = true;
      ok &= validateRequired(name);
      ok &= validateRequired(email);
      ok &= validateEmail(email);
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
  if (selDept || selFacultyDept) {
    // Wire change handlers for role checkboxes so department enabling is consistent
    const roleChangeHandler = () => {
      applyToggleState(cbDept, cbProg, selDept, selProg, cbVcaa, cbAssocVcaa, cbDean, cbAssocDean, cbFaculty, selFacultyDept);
    };

    if (cbDept) cbDept.addEventListener('change', roleChangeHandler);
    if (cbVcaa) cbVcaa.addEventListener('change', roleChangeHandler);
    if (cbAssocVcaa) cbAssocVcaa.addEventListener('change', roleChangeHandler);
    if (cbDean) cbDean.addEventListener('change', roleChangeHandler);
    if (cbAssocDean) cbAssocDean.addEventListener('change', roleChangeHandler);
    if (cbFaculty) cbFaculty.addEventListener('change', roleChangeHandler);

    if (selDept) {
      selDept.addEventListener('change', () => {
        // Keep department enabled state consistent
        applyToggleState(cbDept, cbProg, selDept, selProg, cbVcaa, cbAssocVcaa, cbDean, cbAssocDean, cbFaculty, selFacultyDept);
      });
    }

    if (selFacultyDept) {
      selFacultyDept.addEventListener('change', () => {
        // Keep faculty department enabled state consistent
        applyToggleState(cbDept, cbProg, selDept, selProg, cbVcaa, cbAssocVcaa, cbDean, cbAssocDean, cbFaculty, selFacultyDept);
      });
    }
  }

  // --- Final submit guard (polite checks; server still validates)
  const form = document.getElementById('svCompleteProfileForm');
  if (form) {
    form.addEventListener('submit', (e) => {
      // If requests are locked (pending), we let it submit HR fields only (button is disabled in Blade anyway).
      if (requestsLocked) return;

      // If no role requested, allow submit (profile-only).
      const wantsDept = cbDept && cbDept.checked;
      const wantsDeanOnly = cbDean && cbDean.checked;
      const wantsAssocDeanOnly = cbAssocDean && cbAssocDean.checked;
      const wantsFaculty = cbFaculty && cbFaculty.checked;

      // Require department only when requesting Department Chair, Dean, or Associate Dean.
      if ((wantsDept || wantsDeanOnly || wantsAssocDeanOnly) && selDept && !selDept.value) {
        e.preventDefault();
        markInvalid(selDept, 'Please select a department for the selected chair role.');
        showStep(2);
        return;
      }

      // Require faculty department when requesting Faculty Member role.
      if (wantsFaculty && selFacultyDept && !selFacultyDept.value) {
        e.preventDefault();
        markInvalid(selFacultyDept, 'Please select your department for the Faculty Member role.');
        showStep(2);
        return;
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
  function applyToggleState(cbDept, cbProg, selDept, selProg, cbVcaa, cbAssocVcaa, cbDean, cbAssocDean, cbFaculty, selFacultyDept) {
    const wantsDept = !!(cbDept && cbDept.checked);
    const wantsProg = !!(cbProg && cbProg.checked);
    const wantsDean = !!(cbDean && cbDean.checked);
    const wantsAssocDean = !!(cbAssocDean && cbAssocDean.checked);
    const wantsFaculty = !!(cbFaculty && cbFaculty.checked);
    const wantsInstitution = wantsDean || wantsAssocDean; // Dean or Associate Dean counts for department enabling

    const anyChair = wantsDept || wantsProg || wantsInstitution;

    // Department select is enabled when any chair role (including institution-level) is requested
    if (selDept) {
      selDept.disabled = !anyChair || requestsLocked;
      if (!anyChair) {
        clearSelect(selDept);
      }
    }

    // Faculty department select is enabled when Faculty Member role is requested
    if (selFacultyDept) {
      selFacultyDept.disabled = !wantsFaculty || requestsLocked;
      if (!wantsFaculty) {
        clearSelect(selFacultyDept);
      }
    }

    // Program select is only enabled for Program Chair (selProg may be null)
    if (selProg) {
      selProg.disabled = !wantsProg || requestsLocked;
      if (!wantsProg) clearSelect(selProg);
    }

    // Clear previous invalid state when toggling
    if (selDept) clearInvalid(selDept);
    if (selFacultyDept) clearInvalid(selFacultyDept);
    if (selProg) clearInvalid(selProg);
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

  /* Email format validation. */
  function validateEmail(el) {
    if (!el) return true;
    const email = String(el.value || '').trim();
    if (!email) return true; // Let validateRequired handle empty emails
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const ok = emailRegex.test(email);
    if (!ok) markInvalid(el, 'Please enter a valid email address.');
    else clearInvalid(el);
    return ok;
  }

  /* Resets a <select> to its placeholder option. */
  function clearSelect(selectEl) {
    if (!selectEl) return;
    selectEl.value = '';
  }
});