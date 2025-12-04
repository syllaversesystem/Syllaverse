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
  const DEFAULT_HIDE_MS = 3000;

  function clearExisting(){
    try { container.querySelectorAll('.alert').forEach(el => el.remove()); } catch {}
  }

  function render(type, message, timeoutMs){
    clearExisting();
    const alert = document.createElement('div');
    alert.className = `alert alert-overlay-style alert-${type} d-flex align-items-center gap-2 show`;
    alert.setAttribute('role','alert');
    const icon = ICONS[type] || ICONS.info;
    const bar  = BARS[type]  || BARS.info;
    const ms   = Number.isFinite(timeoutMs) && timeoutMs > 0 ? timeoutMs : DEFAULT_HIDE_MS;
    alert.innerHTML = `
      <i data-feather="${icon}"></i>
      <div>${message || ''}</div>
      <div class="loading-bar ${bar}" style="animation-duration: ${ms}ms;"></div>
    `;
    container.appendChild(alert);
    try { window.feather?.replace?.(); } catch {}
    setTimeout(() => { try { alert.classList.remove('show'); alert.remove(); } catch {} }, ms);
  }

  // Initial flash from server-rendered data attributes
  const initialType = (container.getAttribute('data-flash-type') || '').trim();
  const initialMsg  = (container.getAttribute('data-flash-message') || '').trim();
  if (initialType && initialMsg) {
    render(initialType, initialMsg, DEFAULT_HIDE_MS);
  }

  // Listen for app-wide alert events
  window.addEventListener('sv:alert', (ev) => {
    const detail = ev?.detail || {};
    const type = String(detail.type || 'success').toLowerCase();
    const msg  = String(detail.message || '').trim();
    const ms   = Number.parseInt(detail.timeout, 10);
    if (!msg) return;
    render(type, msg, Number.isFinite(ms) && ms > 0 ? ms : DEFAULT_HIDE_MS);
  });
})();
