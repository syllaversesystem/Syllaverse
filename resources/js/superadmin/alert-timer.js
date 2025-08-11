// -----------------------------------------------------------------------------
// File: resources/js/components/alert-timer.js
// Description: Handles display + auto-dismiss of alert-overlay (flash + dynamic)
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-08-06] Initial creation – read from Blade data-* attributes, auto-hide.
// [2025-08-06] Added Feather icon rendering, smooth fade-out.
// [2025-08-11] Support for dynamic triggering via window.showAlertOverlay().
// -----------------------------------------------------------------------------

import feather from 'feather-icons';

/**
 * Show the alert overlay with given type and message.
 * @param {'success'|'info'|'error'} type
 * @param {string} message
 */
window.showAlertOverlay = function(type, message) {
  const container = document.getElementById('svAlertOverlay');
  if (!container) return;

  // Clear old content
  container.innerHTML = '';

  // Create new alert element
  const alertEl = document.createElement('div');
  alertEl.className = `alert alert-overlay-style alert-${type} d-flex align-items-center gap-2 show`;
  alertEl.setAttribute('role', 'alert');

  // Icon
  const icon = document.createElement('i');
  switch (type) {
    case 'success':
      icon.setAttribute('data-feather', 'check-circle');
      break;
    case 'error':
      icon.setAttribute('data-feather', 'x-circle');
      break;
    case 'info':
    default:
      icon.setAttribute('data-feather', 'info');
      break;
  }

  // Message
  const msgDiv = document.createElement('div');
  msgDiv.textContent = message;

  // Loading bar
  const bar = document.createElement('div');
  bar.className = 'loading-bar ' + (
    type === 'success' ? 'green' : type === 'error' ? 'red' : 'blue'
  );

  // Append children
  alertEl.appendChild(icon);
  alertEl.appendChild(msgDiv);
  alertEl.appendChild(bar);

  container.appendChild(alertEl);

  // Replace feather icons
  feather.replace();

  // Auto-hide after 1.5s (CSS animation length)
  setTimeout(() => {
    alertEl.classList.remove('show');
    alertEl.classList.add('fade');
    setTimeout(() => alertEl.remove(), 200); // wait for fade
  }, 1500);
};

document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('svAlertOverlay');
  if (!container) return;

  const flashType = container.dataset.flashType;
  const flashMessage = container.dataset.flashMessage;

  if (flashType && flashMessage) {
    window.showAlertOverlay(flashType, flashMessage);
  }
});
