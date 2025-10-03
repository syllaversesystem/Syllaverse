// -------------------------------------------------------------------------------
// * File: resources/js/superadmin/master-data/sortable.js
// * Description: Drag-to-reorder helpers for SDG/IGA/CDIO tables; clean init/destroy,
// *              renumber visible codes, and emit dirty-change events – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-12] Initial creation – whole-row drag (SortableJS), live code renumber,
//              per-tab Save, robust CSRF/headers, and safe filters for controls.
// [2025-08-12] Refactor – Converted to exportable helpers used by index.js.
//              Removed DOMContentLoaded auto-init and local Save button handlers.
//              Added `sv:sortable:dirtychange` event + `setDirty`/`isDirty` helpers.
// -------------------------------------------------------------------------------

import Sortable from 'sortablejs';

// ░░░ START: Auto-size functionality ░░░
/** Auto-size textareas that have .autosize */
function autosize(el) { 
  // Reset height to recalculate
  el.style.height = 'auto';
  
  // Get the scroll height (full content height)
  const scrollHeight = el.scrollHeight;
  
  // Get minimum height from CSS
  const computed = window.getComputedStyle(el);
  const minHeight = parseFloat(computed.minHeight) || 0;
  
  // Set height to either scroll height or minimum height, whichever is larger
  // This ensures all text is visible without scrolling
  const newHeight = Math.max(scrollHeight, minHeight);
  el.style.height = newHeight + 'px';
  
  // Ensure no scrollbar appears
  el.style.overflowY = 'hidden';
}

function initAutosize() {
  const areas = document.querySelectorAll('textarea.autosize');
  areas.forEach((ta) => {
    // Force initial calculation
    ta.style.height = 'auto';
    
    // Initial sizing for existing content
    autosize(ta);
    
    // Add event listeners for dynamic resizing
    ta.addEventListener('input', () => {
      autosize(ta);
    });
    
    ta.addEventListener('paste', () => {
      // Delay to allow paste content to be processed
      setTimeout(() => autosize(ta), 10);
    });
    
    ta.addEventListener('keydown', (e) => {
      // Handle Enter key immediately
      if (e.key === 'Enter') {
        setTimeout(() => autosize(ta), 5);
      }
    });
    
    // Handle content changes from external sources (like form resets)
    ta.addEventListener('change', () => autosize(ta));
    
    // Final adjustment after DOM is fully rendered
    setTimeout(() => autosize(ta), 100);
  });
}
// ░░░ END: Auto-size functionality ░░░

// ░░░ START: Internal state ░░░
/** Track Sortable instances per table so we can safely destroy/re-init. */
const _instances = new WeakMap();
// ░░░ END: Internal state ░░░

// ░░░ START: Utilities ░░░
/** Get the <tbody> of a table element. */
function _tbody(table) {
  return table?.tBodies?.[0] || table?.querySelector?.('tbody') || null;
}

/** Read the configured prefix (e.g., SDG/IGA/CDIO) from data-sv-prefix. */
function _prefix(table) {
  return table?.dataset?.svPrefix || '';
}

/** Recompute visible code cells to reflect current DOM order (1-based). */
export function recalcCodes(tableOrTbody) {
  const tb = tableOrTbody?.tagName === 'TBODY' ? tableOrTbody : _tbody(tableOrTbody);
  if (!tb) return;

  const table = tb.closest('table');
  const prefix = _prefix(table);
  Array.from(tb.querySelectorAll('tr[data-id]')).forEach((tr, i) => {
    const cell = tr.querySelector('.sv-code');
    if (cell) cell.textContent = `${prefix}${i + 1}`;
  });
}

/** Collect row IDs in their current DOM order for a given table. */
export function getOrder(table) {
  const tb = _tbody(table);
  if (!tb) return [];
  return Array.from(tb.querySelectorAll('tr[data-id]')).map(tr => Number(tr.getAttribute('data-id')));
}

/** Mark a table dirty/clean and emit a change event so UI can toggle Save buttons. */
export function setDirty(table, dirty = true) {
  const was = table.dataset.svDirty === '1';
  table.dataset.svDirty = dirty ? '1' : '0';
  if (was !== dirty) {
    table.dispatchEvent(new CustomEvent('sv:sortable:dirtychange', { detail: { dirty } }));
  }
}

/** Read the current dirty state for a table. */
export function isDirty(table) {
  return table?.dataset?.svDirty === '1';
}
// ░░░ END: Utilities ░░░

// ░░░ START: Initialization ░░░
/**
 * Initialize Sortable on a single table (expects attributes:
 *  - id="svTable-<type>"
 *  - data-sv-type="<type>"
 *  - data-sv-prefix="SDG|IGA|CDIO"
 */
export function initSortable(table) {
  if (!table || _instances.has(table)) return;
  const tb = _tbody(table);
  if (!tb) return;

  const sortable = new Sortable(tb, {
    draggable        : 'tr',
    animation        : 150,
    ghostClass       : 'sv-row-ghost',
    chosenClass      : 'sv-row-chosen',
    dragClass        : 'sv-row-dragging',
    filter           : '.action-btn, button, a, input, textarea, select, [data-bs-toggle]',
    preventOnFilter  : false,
    onEnd: () => {
      // After a drag finishes, renumber visible codes and mark this table dirty.
      recalcCodes(tb);
      setDirty(table, true);
    },
  });

  _instances.set(table, sortable);
  // Ensure codes are consistent on first init
  recalcCodes(tb);
  setDirty(table, false);
}

/** Initialize Sortable on all master-data tables present in the DOM. */
export function initAllSortable() {
  document.querySelectorAll('table[id^="svTable-"][data-sv-type]').forEach(initSortable);
}

/** Destroy Sortable instance on a table (safe no-op if not initialized). */
export function destroySortable(table) {
  const inst = _instances.get(table);
  if (inst) {
    try { inst.destroy(); } catch {}
    _instances.delete(table);
  }
  // Do not mutate dirty state here; caller decides (often after replacing <tbody>)
}
// ░░░ END: Initialization ░░░

// ░░░ START: DOM Ready initialization ░░░
document.addEventListener('DOMContentLoaded', () => {
  // Initialize autosize for all textareas with .autosize class
  initAutosize();
});
// ░░░ END: DOM Ready initialization ░░░
