// Small helper to size and position the ILO left title panel so it visually
// spans the entire ILO rows area. Called on load, resize, and after DOM changes.

export function initIloLeftPanel() {
  const container = document.querySelector('.ilo-container');
  if (!container) return;
  const tbody = container.querySelector('tbody#syllabus-ilo-sortable');
  const panel = container.querySelector('.ilo-left-panel');
  if (!tbody || !panel) return;

  function positionPanel() {
  // position the panel to cover the entire table (header + body)
  const table = container.querySelector('.ilo-table');
  if (!table) return;
  const tableRect = table.getBoundingClientRect();
  const containerRect = container.getBoundingClientRect();
  const top = tableRect.top - containerRect.top;
  const height = tableRect.height;
  panel.style.top = `${top}px`;
  panel.style.height = `${height}px`;
  }

  // initial
  positionPanel();
  // reposition on window resize
  window.addEventListener('resize', positionPanel);

  // expose for manual calls
  return { positionPanel };
}
