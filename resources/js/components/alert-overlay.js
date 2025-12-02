/*
-------------------------------------------------------------------------------
* File: resources/js/components/alert-overlay.js
* Description: Client-side controller for floating alert overlay
-------------------------------------------------------------------------------
*/

(function(){
  const container = document.getElementById('svAlertOverlay');
  if (!container) return;

  const ICONS = { success: 'check-circle', error: 'x-circle', info: 'info' };
  const BARS  = { success: 'green', error: 'red', info: 'blue' };
  const AUTO_HIDE_MS = 4000;

  function clearExisting(){
    try { container.querySelectorAll('.alert').forEach(el => el.remove()); } catch {}
  }

  function render(type, message){
    clearExisting();
    const alert = document.createElement('div');
    alert.className = `alert alert-overlay-style alert-${type} d-flex align-items-center gap-2 show`;
    alert.setAttribute('role','alert');
    const icon = ICONS[type] || ICONS.info;
    const bar  = BARS[type]  || BARS.info;
    alert.innerHTML = `
      <i data-feather="${icon}"></i>
      <div>${message || ''}</div>
      <div class="loading-bar ${bar}"></div>
    `;
    container.appendChild(alert);
    try { window.feather?.replace?.(); } catch {}
    setTimeout(() => { try { alert.classList.remove('show'); alert.remove(); } catch {} }, AUTO_HIDE_MS);
  }

  // Initial flash from server-rendered data attributes
  const initialType = (container.getAttribute('data-flash-type') || '').trim();
  const initialMsg  = (container.getAttribute('data-flash-message') || '').trim();
  if (initialType && initialMsg) {
    render(initialType, initialMsg);
  }

  // Listen for app-wide alert events
  window.addEventListener('sv:alert', (ev) => {
    const detail = ev?.detail || {};
    const type = String(detail.type || 'success').toLowerCase();
    const msg  = String(detail.message || '').trim();
    if (!msg) return;
    render(type, msg);
  });
})();
