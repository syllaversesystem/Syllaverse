// -------------------------------------------------------------------------------
// * File: resources/js/superadmin/appointments-modal.js
// * Description: Modal-scoped role toggle + Department→Program filtering for Approved Admins – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-09] Initial creation – binds on modal show; supports Add and Edit forms in each modal.
// -------------------------------------------------------------------------------

document.addEventListener('DOMContentLoaded', () => {
  // Bind when each Manage modal is shown so DOM is ready
  document.querySelectorAll('.sv-appt-modal').forEach((modal) => {
    modal.addEventListener('shown.bs.modal', () => initModal(modal));
  });
});

function initModal(modalEl) {
  // Wire every appointment form inside this modal
  modalEl.querySelectorAll('.sv-appt-form').forEach((form) => {
    const roleSel = form.querySelector('.sv-role');
    const deptSel = form.querySelector('.sv-dept');
    const progSel = form.querySelector('.sv-prog');

    if (!roleSel || !deptSel || !progSel) return;

    // Initial state
    applyRoleToggle(roleSel, progSel);
    filterProgramsByDepartment(deptSel, progSel);

    // Events
    roleSel.addEventListener('change', () => {
      applyRoleToggle(roleSel, progSel);
      if (roleSel.value !== roleProgValue()) {
        progSel.value = '';
      }
    });

    deptSel.addEventListener('change', () => {
      filterProgramsByDepartment(deptSel, progSel);
    });
  });
}

function applyRoleToggle(roleSelect, progSelect) {
  const isProgramChair = roleSelect.value === roleProgValue();
  progSelect.disabled = !isProgramChair;
}

function roleProgValue() {
  // Keep this string equal to \App\Models\Appointment::ROLE_PROG
  return 'PROG_CHAIR';
}

function filterProgramsByDepartment(deptSelect, progSelect) {
  const deptId = (deptSelect.value || '').toString();
  const options = Array.from(progSelect.options);
  const placeholder = options.shift(); // assume first option is placeholder

  let validSelected = false;

  options.forEach((opt) => {
    const belongsTo = (opt.getAttribute('data-dept') || '').toString();
    const show = deptId && belongsTo === deptId;

    opt.hidden = !show;
    opt.disabled = !show;

    if (opt.selected && show) validSelected = true;
  });

  if (!validSelected) progSelect.value = '';
}
