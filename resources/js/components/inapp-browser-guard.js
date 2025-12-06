// Detect common in-app browsers that Google disallows for OAuth and show guidance.
(function(){
  const ua = navigator.userAgent || navigator.vendor || window.opera || '';
  const isFacebookInApp = /FBAN|FBAV|FB_IAB|FBAN\//i.test(ua) || /FB\w+Browser/i.test(ua);
  const isInstagramInApp = /Instagram/i.test(ua);
  const isTwitterInApp = /Twitter/i.test(ua);
  const isTikTokInApp = /TikTok/i.test(ua);
  const isLineInApp = /Line\//i.test(ua);
  const isMessengerInApp = /Messenger/i.test(ua) || /FBMD/i.test(ua);
  const isInApp = isFacebookInApp || isInstagramInApp || isTwitterInApp || isTikTokInApp || isLineInApp || isMessengerInApp;

  function showOverlay(){
    if (document.getElementById('inapp-guard-overlay')) return;
    const overlay = document.createElement('div');
    overlay.id = 'inapp-guard-overlay';
    overlay.className = 'inapp-guard-overlay';
    overlay.innerHTML = `
      <div class="inapp-guard-card" role="dialog" aria-live="assertive">
        <div class="inapp-guard-icon">🔒</div>
        <h2>Open in your browser</h2>
        <p>Google Sign-In requires a secure browser. This app's built-in viewer is not supported.</p>
        <div class="inapp-guard-actions">
          <button id="inapp-guard-open" type="button" class="btn-primary">Open in Browser</button>
          <button id="inapp-guard-dismiss" type="button" class="btn-secondary">Dismiss</button>
        </div>
      </div>`;
    document.body.appendChild(overlay);
    document.getElementById('inapp-guard-open')?.addEventListener('click', () => {
      try {
        const url = window.location.href;
        // Attempt to trigger external browser intent where supported
        window.location.href = url.replace(/^https?:\/\//, 'intent://');
        // Fallback: prompt copy/open
        setTimeout(() => { window.open(url, '_blank'); }, 300);
      } catch {
        window.open(window.location.href, '_blank');
      }
    });
    document.getElementById('inapp-guard-dismiss')?.addEventListener('click', () => {
      overlay.remove();
    });
  }

  if (isInApp) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', showOverlay);
    } else {
      showOverlay();
    }
  }
})();
