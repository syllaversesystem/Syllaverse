/* 
-------------------------------------------------------------------------------
* File: resources/js/superadmin/master-data.js
* Description: Master Data (SDG/IGA/CDIO) – drag locally (renumber codes + enable “Save order”),
*              persist on Save via POST /superadmin/master-data/{type}/reorder.
-------------------------------------------------------------------------------
📜 Log:
[2025-08-12] Initial Vite version with drag-to-reorder + auto-save.
[2025-08-12] Update – decouple save: drag only updates DOM; “Save order” persists to DB.
[2025-08-12] Fix – robust DnD: bind on <tbody>, allow handle or row as origin, setData for Firefox.
-------------------------------------------------------------------------------
*/

(() => {
  // ── Utils ─────────────────────────────────────────────────────────────────
  const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
  const notify = (message, type = 'success') => {
    try { window.dispatchEvent(new CustomEvent('sv:alert', { detail: { type, message } })); }
    catch { alert(message); }
  };
  const refreshIcons = () => { try { window.feather?.replace?.(); } catch {} };

  async function fetchDocHTML() {
    const res  = await fetch(window.location.href, { headers: { 'Accept': 'text/html' } });
    const html = await res.text();
    return new DOMParser().parseFromString(html, 'text/html');
  }
  async function replaceTbodyFor(type, doc) {
    const tableId = `#svTable-${type}`;
    const table   = document.querySelector(tableId);
    if (!table) return false;
    const newTbody = doc.querySelector(`${tableId} tbody`);
    const oldTbody = table.querySelector('tbody');
    if (newTbody && oldTbody) { oldTbody.replaceWith(newTbody); refreshIcons(); return true; }
    return false;
  }
  async function refreshTypeTable(type) {
    try { const doc = await fetchDocHTML(); await replaceTbodyFor(type, doc); }
    catch (e) { console.warn('Refresh table failed:', type, e); }
  }

  // ── “Dirty” state helpers ────────────────────────────────────────────────
  function setDirty(table, isDirty) {
    table.dataset.svDirty = isDirty ? '1' : '';
    const type = table.getAttribute('data-sv-type');
    const btn  = document.querySelector(`.sv-save-order-btn[data-sv-type="${type}"]`);
    if (btn) btn.disabled = !isDirty;
  }
  function isDirty(table) {
    return table.dataset.svDirty === '1';
  }

  // ── Local code recalculation ─────────────────────────────────────────────
  function recalcCodes(table) {
    const prefix = table.getAttribute('data-sv-prefix') || '';
    Array.from(table.tBodies[0].rows).forEach((tr, idx) => {
      const cell = tr.querySelector('.sv-code');
      if (cell) cell.textContent = `${prefix}${idx + 1}`;
    });
  }

  // ── Drag & Drop (local only) ─────────────────────────────────────────────
  function clearDropIndicators(tbody) {
    tbody.querySelectorAll('.sv-drop-before, .sv-drop-after').forEach(tr => {
      tr.classList.remove('sv-drop-before', 'sv-drop-after');
    });
  }

  function initDnDForTable(table) {
    const type  = table.getAttribute('data-sv-type');
    const tbody = table.tBodies && table.tBodies[0];
    if (!type || !tbody) return;

    // 🛠 FIX: make the handle itself draggable so Safari/Firefox always trigger dragstart
    tbody.querySelectorAll('.sv-drag-handle').forEach(h => h.setAttribute('draggable', 'true'));

    let dragRow = null;

    const getRow = (el) => el?.closest?.('tr');
    const isRow  = (el) => el && el.tagName === 'TR';

    // 🛠 FIX: bind on TBODY (more reliable than TABLE for row operations)
    tbody.addEventListener('dragstart', (ev) => {
      const row = getRow(ev.target);
      if (!isRow(row)) return ev.preventDefault();

      dragRow = row;
      row.classList.add('sv-dragging');

      // 🛠 FIX: Firefox requires some data to be set to initiate DnD
      try {
        ev.dataTransfer.effectAllowed = 'move';
        ev.dataTransfer.setData('text/plain', row.id || 'row');
      } catch {}

      // Minimal ghost (text-only)
      const crt = document.createElement('div');
      crt.style.position = 'absolute';
      crt.style.top = '-9999px';
      crt.textContent = row.querySelector('.sv-code')?.textContent || 'Moving…';
      document.body.appendChild(crt);
      try { ev.dataTransfer.setDragImage(crt, 0, 0); } catch {}
      setTimeout(() => crt.remove(), 0);
    });

    tbody.addEventListener('dragover', (ev) => {
      ev.preventDefault(); // allow drop
      try { ev.dataTransfer.dropEffect = 'move'; } catch {}

      const overRow = getRow(ev.target);
      if (!isRow(overRow) || overRow === dragRow) return;

      const rect = overRow.getBoundingClientRect();
      const before = (ev.clientY - rect.top) < rect.height / 2;

      clearDropIndicators(tbody);
      overRow.classList.add(before ? 'sv-drop-before' : 'sv-drop-after');

      // Move the row in DOM
      tbody.insertBefore(dragRow, before ? overRow : overRow.nextSibling);
    });

    tbody.addEventListener('drop', (ev) => {
      ev.preventDefault();
      clearDropIndicators(tbody);
      dragRow?.classList.remove('sv-dragging');
      dragRow = null;

      // Local effects only: renumber codes + mark dirty
      recalcCodes(table);
      setDirty(table, true);
    });

    tbody.addEventListener('dragend', () => {
      dragRow?.classList.remove('sv-dragging');
      clearDropIndicators(tbody);
      dragRow = null;
    });
  }

  // ── Save order (persist) ─────────────────────────────────────────────────
  async function saveOrderForType(type) {
    const table = document.querySelector(`#svTable-${type}`);
    if (!table) return;

    const ids = Array.from(table.tBodies[0].rows)
      .map(tr => parseInt(tr.getAttribute('data-id'), 10))
      .filter(Boolean);

    if (!ids.length) return;

    const btn = document.querySelector(`.sv-save-order-btn[data-sv-type="${type}"]`);
    btn?.setAttribute('disabled', 'disabled');
    btn?.classList.add('disabled');

    try {
      const res = await fetch(`/superadmin/master-data/${type}/reorder`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf(),
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ ids }),
        credentials: 'same-origin',
      });

      const payload = await res.json();

      if (!res.ok || payload?.ok === false) {
        throw new Error(payload?.message || `Reorder failed (${res.status})`);
      }

      // Server truth: update codes just in case
      const map = new Map(payload.items.map(x => [String(x.id), x.code]));
      table.querySelectorAll('tbody tr').forEach(tr => {
        const id = tr.getAttribute('data-id');
        const codeCell = tr.querySelector('.sv-code');
        if (id && codeCell && map.has(id)) codeCell.textContent = map.get(id);
      });

      setDirty(table, false);
      notify(payload?.message || `${type.toUpperCase()} order saved.`);
      refreshIcons();

    } catch (e) {
      notify(e.message || 'Could not save order.', 'error');
      await refreshTypeTable(type); // revert to server order
      const tableAfter = document.querySelector(`#svTable-${type}`);
      if (tableAfter) { setDirty(tableAfter, false); refreshIcons(); }
    } finally {
      btn?.classList.remove('disabled');
      btn?.removeAttribute('disabled');
      const stillDirty = isDirty(document.querySelector(`#svTable-${type}`));
      if (!stillDirty) btn?.setAttribute('disabled', 'disabled');
    }
  }

  function initSaveButtons() {
    document.querySelectorAll('.sv-save-order-btn[data-sv-type]').forEach(btn => {
      btn.addEventListener('click', async () => {
        const type = btn.getAttribute('data-sv-type');
        await saveOrderForType(type);
      });
    });
  }

  // ── Add/Edit AJAX (unchanged) ────────────────────────────────────────────
  function initAjaxForms() {
    document.addEventListener('submit', async (ev) => {
      const form = ev.target;
      if (!(form instanceof HTMLFormElement)) return;
      if (!form.matches('.add-master-data-form, .edit-master-data-form')) return;

      ev.preventDefault();

      const modalEl = form.closest('.modal');
      const type = resolveTypeFromAction(form.action);
      const btn = form.querySelector('button[type="submit"]');
      btn?.setAttribute('disabled','disabled');

      try {
        const fd = new FormData(form);
        let method = (form.getAttribute('method') || 'POST').toUpperCase();
        if (fd.has('_method')) { method = String(fd.get('_method')).toUpperCase(); fd.delete('_method'); }

        const res = await fetch(form.action, {
          method,
          headers: {
            'X-CSRF-TOKEN': csrf(),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
          body: fd,
          credentials: 'same-origin',
        });

        const ct = res.headers.get('content-type') || '';
        const isJSON = ct.includes('application/json');
        const payload = isJSON ? await res.json() : null;

        if (res.status === 422 && payload?.errors) {
          const first = Object.values(payload.errors)[0];
          notify(Array.isArray(first) ? first[0] : 'Please fix the errors.', 'error');
          return;
        }

        if (!res.ok || payload?.ok === false) {
          throw new Error(payload?.message || `Request failed (${res.status})`);
        }

        if (modalEl) {
          try {
            const Modal = window.bootstrap?.Modal;
            (Modal.getInstance(modalEl) || new Modal(modalEl)).hide();
          } catch {}
        }

        if (type) {
          await refreshTypeTable(type);
          const table = document.querySelector(`#svTable-${type}`);
          if (table) setDirty(table, false);
        } else {
          window.location.reload();
          return;
        }

        notify(payload?.message || 'Saved.');
        if (form.classList.contains('add-master-data-form')) form.reset();

      } catch (e) {
        notify(e.message || 'Something went wrong.', 'error');
      } finally {
        btn?.removeAttribute('disabled');
      }
    });
  }

  function resolveTypeFromAction(action) {
    const m = action.match(/\/master-data\/(sdg|iga|cdio)\b/i);
    return m ? m[1].toLowerCase() : null;
  }

  // ── Init ─────────────────────────────────────────────────────────────────
  function init() {
    document.querySelectorAll('table[id^="svTable-"][data-sv-type]').forEach(initDnDForTable);
    initSaveButtons();
    initAjaxForms();
    refreshIcons();
  }

  document.readyState === 'loading'
    ? document.addEventListener('DOMContentLoaded', init)
    : init();
})();
