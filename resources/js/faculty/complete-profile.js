// -------------------------------------------------------------------------------
// * File: resources/js/faculty/complete-profile.js
// * Description: Stepper + role-request UI logic for Faculty Complete Profile – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-10-24] Updated to include 2-step wizard with role selection functionality
// -------------------------------------------------------------------------------

/* Plain-English: bootstrap the page, wire the stepper, and manage role selection. */
document.addEventListener('DOMContentLoaded', () => {
  // Elements for the stepper
  const step1 = document.getElementById('svStep1');
  const step2 = document.getElementById('svStep2');
  const nextBtn = document.getElementById('svNextToStep2');
  const backBtn = document.getElementById('svBackToStep1');
  const stepBadge1 = document.getElementById('svStepBadge1');
  const stepBadge2 = document.getElementById('svStepBadge2');
  const progressBar = document.getElementById('svStepProgress');
  const step2Label = document.getElementById('svStep2Label');

  // Core inputs
  const name = document.getElementById('svName');
  const email = document.getElementById('svEmail');
  const designation = document.getElementById('svDesignation');
  const employeeCode = document.getElementById('svEmployeeCode');

  // Role-request controls
  // Support new Department Head checkbox id (request_dept_head) while remaining backward compatible with legacy request_dept_chair
  const cbDept = document.querySelector('#request_dept_head') || document.querySelector('#request_dept_chair');
  const cbVcaa = document.querySelector('#request_vcaa');
  const cbAssocVcaa = document.querySelector('#request_assoc_vcaa');
  const cbDean = document.querySelector('#request_dean');
  const cbAssocDean = document.querySelector('#request_assoc_dean');
  const cbFaculty = document.querySelector('#request_faculty');
  
  // Department selectors
  const selDept = document.querySelector('#svDepartmentId');
  const deptSelector = document.querySelector('#svDepartmentSelector');
  const selFacultyDept = document.querySelector('#svFacultyDepartmentId');
  const facultyDeptSelector = document.querySelector('#svFacultyDepartmentSelector');

  // If essential elements are missing, bail out gracefully.
  if (!step1 || !step2) return;

  // Determine if requests are locked (pending exists) by checking disabled state (set by Blade).
  const allCheckboxes = [cbDept, cbVcaa, cbAssocVcaa, cbDean, cbAssocDean, cbFaculty].filter(Boolean);
  const requestsLocked = allCheckboxes.length > 0 && allCheckboxes.every(cb => cb.disabled);

  // --- Initial UI state
  showStep(1);
  applyToggleState();

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
    });
  }

  if (backBtn) {
    backBtn.addEventListener('click', () => showStep(1));
  }

  // --- Role toggles + department showing/hiding
  const roleChangeHandler = () => {
    applyToggleState();
  };

  if (cbDept) cbDept.addEventListener('change', roleChangeHandler);
  if (cbVcaa) cbVcaa.addEventListener('change', roleChangeHandler);
  if (cbAssocVcaa) cbAssocVcaa.addEventListener('change', roleChangeHandler);
  if (cbDean) cbDean.addEventListener('change', roleChangeHandler);
  if (cbAssocDean) cbAssocDean.addEventListener('change', roleChangeHandler);
  if (cbFaculty) cbFaculty.addEventListener('change', roleChangeHandler);

  // Mutual exclusion: Dean vs Associate Dean
  if (cbDean && cbAssocDean) {
    cbDean.addEventListener('change', () => {
      if (cbDean.checked) {
        cbAssocDean.checked = false;
        cbAssocDean.disabled = true;
      } else {
        if (!requestsLocked) cbAssocDean.disabled = false;
      }
    });
    cbAssocDean.addEventListener('change', () => {
      if (cbAssocDean.checked) {
        cbDean.checked = false;
        cbDean.disabled = true;
      } else {
        if (!requestsLocked) cbDean.disabled = false;
      }
    });
  }

  // --- Final submit guard (polite checks; server still validates)
  const form = document.getElementById('svCompleteProfileForm');
  if (form) {
    form.addEventListener('submit', (e) => {
      // If requests are locked (pending), we let it submit HR fields only.
      if (requestsLocked) return;

      // Check if department-specific roles need department selection
      const wantsDept = cbDept && cbDept.checked; // Dept Head or legacy Dept Chair
      const wantsDean = cbDean && cbDean.checked;
      const wantsAssocDean = cbAssocDean && cbAssocDean.checked;
      const wantsFaculty = cbFaculty && cbFaculty.checked;

      // Check if any leadership role is selected
      const hasLeadershipRole = wantsDept || wantsDean || wantsAssocDean;

      // Require department for department-specific roles
      if (hasLeadershipRole && selDept && !selDept.value) {
        e.preventDefault();
        markInvalid(selDept, 'Please select a department for the selected leadership role.');
        showStep(2);
        return;
      }

      // Do NOT auto-include Faculty when leadership is selected

      // Require faculty department for faculty role (only when no leadership role is selected)
      if (wantsFaculty && !hasLeadershipRole && selFacultyDept && !selFacultyDept.value) {
        e.preventDefault();
        markInvalid(selFacultyDept, 'Please select your department for the Faculty role.');
        showStep(2);
        return;
      }
    });
  }

  // ===== Helpers =====

  /* This switches between Step 1 and Step 2 and animates the step badges and progress bar. */
  function showStep(n) {
    const goStep1 = n === 1;
    step1.hidden = !goStep1;
    step2.hidden = goStep1;

    // Badge visuals
    if (stepBadge1 && stepBadge2) {
      stepBadge1.classList.toggle('sv-step-active', goStep1);
      stepBadge1.classList.toggle('sv-step-disabled', !goStep1);
      stepBadge2.classList.toggle('sv-step-active', !goStep1);
      stepBadge2.classList.toggle('sv-step-disabled', goStep1);
    }

    // Progress bar animation: 50% on step 1, 100% on step 2
    if (progressBar) {
      progressBar.style.width = goStep1 ? '50%' : '100%';
    }

    // Step 2 label styling
    if (step2Label) {
      if (goStep1) {
        step2Label.classList.remove('fw-semibold', 'text-dark');
        step2Label.classList.add('text-muted');
      } else {
        step2Label.classList.remove('text-muted');
        step2Label.classList.add('fw-semibold', 'text-dark');
      }
    }

    // Focus first input of the current step (for accessibility)
    if (goStep1 && designation) designation.focus();
    if (!goStep1 && !requestsLocked) {
      if (cbDept) cbDept.focus();
    }
  }

  /* This shows/hides department selectors based on role selections and manages faculty role logic. */
  function applyToggleState() {
    const wantsDept = !!(cbDept && cbDept.checked); // Dept Head or legacy Dept Chair
    const wantsDean = !!(cbDean && cbDean.checked);
    const wantsAssocDean = !!(cbAssocDean && cbAssocDean.checked);
    const wantsFaculty = !!(cbFaculty && cbFaculty.checked);

    // Check if any department-specific leadership role is selected
    const needsDepartment = wantsDept || wantsDean || wantsAssocDean;
    const hasLeadershipRole = needsDepartment;

    // Show department selector when any department-specific role is selected
    if (deptSelector) {
      deptSelector.style.display = needsDepartment ? 'block' : 'none';
      if (!needsDepartment && selDept) {
        selDept.value = '';
        clearInvalid(selDept);
      }
    }

    // Faculty role logic: when any Department-Specific Leadership is selected, uncheck and restrict Faculty
    if (cbFaculty) {
      if (hasLeadershipRole) {
        cbFaculty.checked = false;
        cbFaculty.disabled = true;
        const facultyCard = cbFaculty.closest('.card');
        if (facultyCard) {
          facultyCard.style.opacity = '0.6';
          const helpText = facultyCard.querySelector('.text-muted.small');
          if (helpText) {
            helpText.textContent = 'Restricted: leadership requests exclude separate Faculty selection';
          }
        }
      } else {
        cbFaculty.disabled = requestsLocked; // Only disable if requests are locked
        const facultyCard = cbFaculty.closest('.card');
        if (facultyCard) {
          facultyCard.style.opacity = '1';
          const helpText = facultyCard.querySelector('.text-muted.small');
          if (helpText) {
            helpText.textContent = 'Regular faculty position for teaching and research';
          }
        }
      }
    }

    // Show faculty department selector only when faculty role is selected and no leadership role
    const showFacultyDept = !!wantsFaculty && !hasLeadershipRole;
    if (facultyDeptSelector) {
      facultyDeptSelector.style.display = showFacultyDept ? 'block' : 'none';
      if (!showFacultyDept && selFacultyDept) {
        selFacultyDept.value = '';
        clearInvalid(selFacultyDept);
      }
    }

    // No automatic syncing from leadership; faculty department is independent
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
});