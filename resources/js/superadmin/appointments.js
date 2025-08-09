// -------------------------------------------------------------------------------
// * File: resources/js/superadmin/appointments.js
// * Description: Per-form role toggle + Department→Program filtering for Approved Admins tab – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-08] Initial creation – supports Add and Edit forms using .sv-appt-form with sv-role/sv-dept/sv-prog.
// -------------------------------------------------------------------------------

document.addEventListener('DOMContentLoaded', () => {
  const pane = document.querySelector('#admins-approved');
  if (!pane) return;

  // For each inline appointment form (add or edit)
  pane.querySelectorAll('.sv-appt-form').forEach((form) => {
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
      // If switching away from Program Chair, clear program
      if (roleSel.value !== roleProgValue()) {
        progSel.value = '';
      }
    });

    deptSel.addEventListener('change', () => {
      filterProgramsByDepartment(deptSel, progSel);
    });
  });
});

function applyRoleToggle(roleSelect, progSelect) {
  const isProgramChair = roleSelect.value === roleProgValue();
  progSelect.disabled = !isProgramChair;
}

function roleProgValue() {
  // Keep in sync with PHP constant \App\Models\Appointment::ROLE_PROG
  return 'PROG_CHAIR';
}

function filterProgramsByDepartment(deptSelect, progSelect) {
  const deptId = (deptSelect.value || '').toString();
  const options = Array.from(progSelect.options);
  const placeholder = options.shift(); // first is placeholder

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
