// -----------------------------------------------------------------------------
// File: resources/js/superadmin/alert-timer.js
// Description: Auto-dismiss and animate top-center alert overlays – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-08-06] Initial creation – alert auto-close with timer progress bar.
// [2025-08-06] Updated auto-dismiss timer to 3 seconds.
// -----------------------------------------------------------------------------

document.addEventListener('DOMContentLoaded', () => {
  const alertOverlay = document.querySelector('.alert-overlay');
  const alertBox = alertOverlay?.querySelector('.alert');

  if (alertBox) {
    // 3-second dismiss timer
setTimeout(() => {
  alertBox.classList.remove('show');
  alertBox.classList.add('hide');
  setTimeout(() => alertOverlay.remove(), 300);
}, 1500); // ⏱ 1.5 seconds

  }
});
