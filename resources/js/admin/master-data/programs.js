// -------------------------------// Build a row's actions cell
function actionsHtml(p) {
  return `
    <button class="btn action-btn rounded-circle edit me-2 editProgramBtn"
            data-id="${p.id}"
            data-name="${p.name}"
            data-code="${p.code}"
            data-description="${p.description ?? ''}"
            data-department-id="${p.department_id ?? ''}"
            data-bs-toggle="modal"
            data-bs-target="#editProgramModal">
      <i data-feather="edit"></i>
    </button>------------------------------------
// File: resources/js/admin/programs.js
// Description: Handles AJAX add, edit, and delete for Programs (Admin - Syllaverse)
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-17] Initial version – AJAX submit for add/edit/delete; updates table rows dynamically.
// [2025-08-18] FIX: Added Accept: application/json header + safe JSON parsing to prevent crashes.
// [2025-08-18] UPDATED: Removed inline alerts → global overlay (<x-alert-overlay />).
// [2025-08-18] FIX: Preserve first column on edit; no auto-content in first column on add.
// -------------------------------------------------------------------------------

import feather from 'feather-icons';

function getCsrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}
async function parseJsonSafe(response) {
  try { return await response.json(); } catch { return null; }
}
function closeModal(modalId) {
  const el = document.getElementById(modalId);
  const m = window.bootstrap?.Modal?.getInstance(el);
  if (m) m.hide();
}

// Detect if the table uses a leading number (“#”) column
function hasNumberColumn() {
  const th0 = document.querySelector('#svProgramsTable thead th:first-child');
  if (!th0) return false;
  const t = (th0.textContent || '').trim().toLowerCase();
  // Common patterns for a number/index column
  return t === '#' || t.includes('no.') || t === '';
}

// Build a row’s actions cell
function actionsHtml(p) {
  return `
    <button class="btn action-btn rounded-circle edit me-2 editProgramBtn"
            data-id="${p.id}"
            data-name="${p.name}"
            data-code="${p.code}"
            data-description="${p.description ?? ''}"
            data-bs-toggle="modal"
            data-bs-target="#editProgramModal">
      <i data-feather="edit"></i>
    </button>
    <button class="btn action-btn rounded-circle delete deleteProgramBtn"
            data-id="${p.id}"
            data-name="${p.name}"
            data-bs-toggle="modal"
            data-bs-target="#deleteProgramModal">
      <i data-feather="trash"></i>
    </button>
  `;
}

// Insert a new row without touching the first column (if present)
function insertProgramRow(program) {
  const tbody = document.querySelector('#svProgramsTable tbody');
  if (!tbody) return;

  const numbered = hasNumberColumn();

  // If you want the new row at top, use 'afterbegin'; at bottom, use 'beforeend'
  const html = numbered
    ? `
      <tr id="program-${program.id}">
        <td></td> <!-- intentionally left blank; no auto-numbering -->
        <td>${program.name}</td>
        <td>${program.code}</td>
        <td class="text-end">
          ${actionsHtml(program)}
        </td>
      </tr>
    `
    : `
      <tr id="program-${program.id}">
        <td>${program.name}</td>
        <td>${program.code}</td>
        <td class="text-end">
          ${actionsHtml(program)}
        </td>
      </tr>
    `;

  tbody.insertAdjacentHTML('afterbegin', html);
  feather.replace();
}

// -------------------------------------------------------------------------------
// ADD PROGRAM
// -------------------------------------------------------------------------------
document.addEventListener('submit', async function (e) {
  if (e.target?.id !== 'addProgramForm') return;
  e.preventDefault();
  const form = e.target;

  try {
    const response = await fetch(form.action, {
      method: 'POST',
      headers: { 
        'X-CSRF-TOKEN': getCsrfToken(),
        'Accept': 'application/json'
      },
      body: new FormData(form),
    });

    const data = await parseJsonSafe(response);
    if (!data) {
      window.showAlertOverlay?.('error', 'Unexpected server response. Please refresh.');
      return;
    }

    if (response.ok) {
      // Remove any empty placeholder rows
      document.querySelector('#svProgramsTable tbody .sv-empty-row')?.remove();

      insertProgramRow(data.program);

      form.reset();
      closeModal('addProgramModal');
      window.showAlertOverlay?.('success', data.message);
    } else {
      if (response.status === 422 && data.errors) {
        const messages = Object.values(data.errors).flat().join('<br>');
        window.showAlertOverlay?.('error', messages);
      } else {
        window.showAlertOverlay?.('error', data.error || data.message || 'Failed to add program.');
      }
    }
  } catch (err) {
    console.error(err);
    window.showAlertOverlay?.('error', 'Something went wrong while adding program.');
  }
});

// -------------------------------------------------------------------------------
// EDIT PROGRAM (preserve first column content exactly as-is)
// -------------------------------------------------------------------------------
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.editProgramBtn');
  if (!btn) return;

  console.log('Edit button clicked:', btn);
  console.log('Button datasets:', btn.dataset);
  console.log('Name:', btn.dataset.name);
  console.log('Code:', btn.dataset.code); 
  console.log('Description:', btn.dataset.description);
  console.log('Dept ID:', btn.dataset.departmentId);

  const nameField = document.getElementById('editProgramName');
  const codeField = document.getElementById('editProgramCode');
  const descField = document.getElementById('editProgramDescription');

  console.log('Name field:', nameField);
  console.log('Code field:', codeField);
  console.log('Desc field:', descField);

  if (nameField) nameField.value = btn.dataset.name || '';
  if (codeField) codeField.value = btn.dataset.code || '';
  if (descField) descField.value = btn.dataset.description || '';
  
  // Set department dropdown if it exists
  const deptField = document.getElementById('editProgramDepartment');
  if (deptField) {
    deptField.value = btn.dataset.departmentId || '';
  }
  
  document.querySelector('#editProgramForm').setAttribute('action', `/admin/programs/${btn.dataset.id}`);
  document.getElementById('programEditLabel').textContent = btn.dataset.name || 'Program';
});

document.addEventListener('submit', async function (e) {
  if (e.target?.id !== 'editProgramForm') return;
  e.preventDefault();
  const form = e.target;

  try {
    const response = await fetch(form.action, {
      method: 'POST', // PUT override handled by Laravel
      headers: { 
        'X-CSRF-TOKEN': getCsrfToken(),
        'Accept': 'application/json'
      },
      body: new FormData(form),
    });

    const data = await parseJsonSafe(response);
    if (!data) {
      window.showAlertOverlay?.('error', 'Unexpected server response. Please refresh.');
      return;
    }

    if (response.ok) {
      const row = document.querySelector(`#program-${data.program.id}`);
      if (row) {
        const cells = row.querySelectorAll('td');
        const numbered = hasNumberColumn();

        // ✅ Preserve the first column as-is
        // Update only the remaining columns based on structure (removed created column)
        if (numbered) {
          // 0 = number (preserve), 1=name, 2=code, 3=actions
          if (cells[1]) cells[1].textContent = data.program.name;
          if (cells[2]) cells[2].textContent = data.program.code;
          if (cells[3]) cells[3].innerHTML = actionsHtml(data.program);
        } else {
          // 0=name, 1=code, 2=actions
          if (cells[0]) cells[0].textContent = data.program.name;
          if (cells[1]) cells[1].textContent = data.program.code;
          if (cells[2]) cells[2].innerHTML = actionsHtml(data.program);
        }
        feather.replace();
      }
      closeModal('editProgramModal');
      window.showAlertOverlay?.('success', data.message);
    } else {
      if (response.status === 422 && data.errors) {
        const messages = Object.values(data.errors).flat().join('<br>');
        window.showAlertOverlay?.('error', messages);
      } else {
        window.showAlertOverlay?.('error', data.error || data.message || 'Failed to update program.');
      }
    }
  } catch (err) {
    console.error(err);
    window.showAlertOverlay?.('error', 'Something went wrong while updating program.');
  }
});

// -------------------------------------------------------------------------------
// DELETE PROGRAM
// -------------------------------------------------------------------------------
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.deleteProgramBtn');
  if (!btn) return;

  document.querySelector('#deleteProgramForm').setAttribute('action', `/admin/programs/${btn.dataset.id}`);
  document.getElementById('programDeleteLabel').textContent = btn.dataset.name;
  document.getElementById('programDeleteWhat').textContent = btn.dataset.name;
});

document.addEventListener('submit', async function (e) {
  if (e.target?.id !== 'deleteProgramForm') return;
  e.preventDefault();
  const form = e.target;

  try {
    const response = await fetch(form.action, {
      method: 'POST', // DELETE override handled by Laravel
      headers: { 
        'X-CSRF-TOKEN': getCsrfToken(),
        'Accept': 'application/json'
      },
      body: new FormData(form),
    });

    const data = await parseJsonSafe(response);
    if (!data) {
      window.showAlertOverlay?.('error', 'Unexpected server response. Please refresh.');
      return;
    }

    if (response.ok) {
      const rowId = data.id ?? form.action.split('/').pop();
      document.querySelector(`#program-${rowId}`)?.remove();
      closeModal('deleteProgramModal');
      window.showAlertOverlay?.('success', data.message);
    } else {
      window.showAlertOverlay?.('error', data.error || data.message || 'Failed to delete program.');
    }
  } catch (err) {
    console.error(err);
    window.showAlertOverlay?.('error', 'Something went wrong while deleting program.');
  }
});
