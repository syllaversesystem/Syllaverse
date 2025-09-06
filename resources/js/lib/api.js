// Small API helper used across faculty SDG modules
export async function apiFetch(url, options = {}) {
  const headers = Object.assign({}, options.headers || {});
  const tokenEl = document.querySelector('meta[name="csrf-token"]');
  if (tokenEl && !headers['X-CSRF-TOKEN']) headers['X-CSRF-TOKEN'] = tokenEl.content;
  if (!headers['Accept']) headers['Accept'] = 'application/json';

  const init = {
    method: options.method || 'GET',
    credentials: 'same-origin',
    headers,
  };

  if (options.body !== undefined) {
    // If the body is FormData, do not set Content-Type (browser will set boundary)
    if (options.body instanceof FormData) {
      init.body = options.body;
    } else if (typeof options.body === 'object') {
      headers['Content-Type'] = 'application/json';
      init.body = JSON.stringify(options.body);
    } else {
      init.body = options.body;
    }
  }

  const res = await fetch(url, init);
  const text = await res.text().catch(() => '');
  const contentType = res.headers.get('content-type') || '';
  const isJson = contentType.indexOf('application/json') !== -1;

  if (!res.ok) {
    let payload = {};
    try { payload = isJson && text ? JSON.parse(text) : { message: text || res.statusText }; } catch (e) { payload = { message: text || res.statusText }; }
    const err = new Error(payload.error || payload.message || res.statusText);
    err.response = res;
    err.payload = payload;
    throw err;
  }

  if (isJson && text) return JSON.parse(text);
  return text;
}

export function showToast(title, message, isError = false) {
  try {
    const id = `sdg-toast-${Date.now()}`;
    const toastHtml = `
      <div id="${id}" class="toast align-items-center text-bg-${isError ? 'danger' : 'success'} border-0 position-fixed" role="alert" aria-live="assertive" aria-atomic="true" style="top:1rem; right:1rem; z-index:11000;">
        <div class="d-flex">
          <div class="toast-body"> <strong>${title}:</strong> ${message} </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    `;
    const container = document.createElement('div'); container.innerHTML = toastHtml; document.body.appendChild(container.firstElementChild);
    const toastEl = document.getElementById(id);
    if (window.bootstrap && bootstrap.Toast) {
      const bsToast = new bootstrap.Toast(toastEl, { delay: 2500 });
      bsToast.show();
      setTimeout(() => { try { toastEl.remove(); } catch (e) {} }, 3500);
    } else {
      setTimeout(() => { try { toastEl.remove(); } catch (e) {} }, 3500);
    }
  } catch (e) { try { alert(title + ': ' + message); } catch (_) {} }
}
