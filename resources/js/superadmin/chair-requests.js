// -------------------------------------------------------------------------------
// * File: resources/js/superadmin/chair-requests.js
// * Description: Per-row Department → Program filtering for Superadmin Chair Requests panel
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-08] Initial creation – filters program options by chosen department in each pending-request row.
// -------------------------------------------------------------------------------

/* ░░░ START: Bootstrapping ░░░ */
document.addEventListener('DOMContentLoaded', () => {
  // Scope to the Chair Requests tab pane so we don't touch other tables.
  const pane = document.querySelector('#chair-requests');
  if (!pane) return;

  // Find all department selects in the pending table
  const deptSelects = pane.querySelectorAll('form select[name="department_id"]');
  deptSelects.forEach((deptSel) => {
    // Get the program select in the same row/form
    const row = deptSel.closest('tr') || deptSel.closest('form');
    if (!row) return;
    const progSel = row.querySelector('select[name="program_id"]');
    if (!progSel) return;

    // Initial filter on load
    filterProgramsByDepartment(deptSel, progSel);

    // Refilter when department changes
    deptSel.addEventListener('change', () => {
      filterProgramsByDepartment(deptSel, progSel);
    });
  });
});
/* ░░░ END: Bootstrapping ░░░ */


/* ░░░ START: Filtering Logic ░░░ */
/**
 * Keep the Program list relevant by showing only options that belong to the selected Department.
 * If the current program selection becomes invalid, it is cleared.
 */
function filterProgramsByDepartment(deptSelect, progSelect) {
  const deptId = (deptSelect.value || '').toString();

  // Collect options; treat the first option as placeholder
  const options = Array.from(progSelect.options);
  const placeholder = options.shift(); // keep always visible/enabled

  let selectedStillValid = false;

  options.forEach((opt) => {
    const belongsTo = (opt.getAttribute('data-dept') || '').toString();
    const matches = deptId && belongsTo === deptId;

    // Show only matching programs; hide + disable others to reduce mistakes
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

  // If nothing selected or selection invalid, clear to placeholder
  if (!deptId || !selectedStillValid) {
    progSelect.value = '';
  }

  // If no department chosen, keep only the placeholder enabled
  if (!deptId) {
    options.forEach((opt) => {
      opt.hidden = true;
      opt.disabled = true;
    });
  }
}
/* ░░░ END: Filtering Logic ░░░ */
