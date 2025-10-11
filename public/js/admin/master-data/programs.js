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

function hasNumberColumn() {
  const th0 = document.querySelector('#svProgramsTable thead th:first-child');
  if (!th0) return false;
  const t = (th0.textContent || '').trim().toLowerCase();
  return t === '#' || t.includes('no.') || t === '';
}

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

function showEmptyState() {
  let tbody = document.querySelector('#svProgramsTable tbody');
  if (!tbody) return;
  
  // Check if department column is visible by counting header columns
  const headerCols = document.querySelectorAll('#svProgramsTable thead th').length;
  const colspan = headerCols;
  
  tbody.innerHTML = `
    <tr class="sv-empty-row">
      <td colspan="${colspan}">
        <div class="sv-empty">
          <h6>No programs found</h6>
          <p>Click the <i data-feather="plus"></i> button to add one.</p>
        </div>
      </td>
    </tr>
  `;
  
  if (typeof feather !== 'undefined') {
    setTimeout(() => feather.replace(), 100);
  }
}

// Deleted program suggestions functionality
let suggestionTimeout = null;

async function searchDeletedPrograms(query) {
  if (query.length < 2) return [];
  
  try {
    const response = await fetch(`/admin/programs/search-deleted?q=${encodeURIComponent(query)}`, {
      headers: {
        'X-CSRF-TOKEN': getCsrfToken(),
        'Accept': 'application/json'
      }
    });
    
    if (response.ok) {
      return await response.json();
    }
  } catch (error) {
    console.error('Error searching deleted programs:', error);
  }
  
  return [];
}

function showSuggestions(inputElement, suggestions, suggestionsContainer) {
  if (suggestions.length === 0) {
    suggestionsContainer.style.display = 'none';
    return;
  }
  
  const html = suggestions.map(program => `
    <div class="suggestion-item" data-program='${JSON.stringify(program)}'>
      <div class="suggestion-main">
        ${program.name} (${program.code})
        <span class="suggestion-restore-badge">RESTORE</span>
      </div>
      <div class="suggestion-meta">${program.department_name}</div>
    </div>
  `).join('');
  
  suggestionsContainer.innerHTML = html;
  suggestionsContainer.style.display = 'block';
  
  // Add click handlers for suggestions
  suggestionsContainer.querySelectorAll('.suggestion-item').forEach(item => {
    item.addEventListener('click', function() {
      const program = JSON.parse(this.dataset.program);
      populateAddFormWithDeletedProgram(program);
      hideSuggestions();
    });
  });
}

function hideSuggestions() {
  document.getElementById('programNameSuggestions').style.display = 'none';
  document.getElementById('programCodeSuggestions').style.display = 'none';
}

function populateAddFormWithDeletedProgram(program) {
  // Populate form fields
  document.getElementById('programName').value = program.name;
  document.getElementById('programCode').value = program.code;
  document.getElementById('programDescription').value = program.description || '';
  
  // Set department if dropdown exists
  const deptField = document.getElementById('programDepartment');
  if (deptField) {
    deptField.value = program.department_id;
  }
  
  // Show confirmation
  if (window.showAlertOverlay) {
    window.showAlertOverlay('info', `Populated with deleted program "${program.name}". Creating this will restore the program.`);
  }
}

function setupSuggestionListeners() {
  const programName = document.getElementById('programName');
  const programCode = document.getElementById('programCode');
  const nameSuggestions = document.getElementById('programNameSuggestions');
  const codeSuggestions = document.getElementById('programCodeSuggestions');
  
  if (!programName || !programCode) return;
  
  // Program name input handler
  programName.addEventListener('input', function() {
    const query = this.value.trim();
    
    clearTimeout(suggestionTimeout);
    
    if (query.length < 2) {
      nameSuggestions.style.display = 'none';
      return;
    }
    
    suggestionTimeout = setTimeout(async () => {
      const suggestions = await searchDeletedPrograms(query);
      showSuggestions(programName, suggestions, nameSuggestions);
    }, 300);
  });
  
  // Program code input handler
  programCode.addEventListener('input', function() {
    const query = this.value.trim();
    
    clearTimeout(suggestionTimeout);
    
    if (query.length < 2) {
      codeSuggestions.style.display = 'none';
      return;
    }
    
    suggestionTimeout = setTimeout(async () => {
      const suggestions = await searchDeletedPrograms(query);
      showSuggestions(programCode, suggestions, codeSuggestions);
    }, 300);
  });
  
  // Hide suggestions when clicking outside
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.position-relative')) {
      hideSuggestions();
    }
  });
  
  // Hide suggestions when modal is closed
  document.getElementById('addProgramModal').addEventListener('hidden.bs.modal', function() {
    hideSuggestions();
  });
}

function insertProgramRow(program) {
  const tbody = document.querySelector('#svProgramsTable tbody');
  if (!tbody) return;

  const emptyRow = tbody.querySelector('.sv-empty-row');
  if (emptyRow) emptyRow.remove();

  const numbered = hasNumberColumn();
  
  // Check if department filter is showing all departments
  const departmentFilter = document.getElementById('departmentFilter');
  const showDepartmentColumn = departmentFilter && departmentFilter.value === 'all';
  
  const departmentCell = showDepartmentColumn ? `<td>${program.department?.code || 'N/A'}</td>` : '';
  
  const html = numbered
    ? `<tr id="program-${program.id}">
        <td></td>
        <td>${program.name}</td>
        <td>${program.code}</td>
        ${departmentCell}
        <td class="text-end">${actionsHtml(program)}</td>
      </tr>`
    : `<tr id="program-${program.id}">
        <td>${program.name}</td>
        <td>${program.code}</td>
        ${departmentCell}
        <td class="text-end">${actionsHtml(program)}</td>
      </tr>`;

  tbody.insertAdjacentHTML('afterbegin', html);
  if (typeof feather !== 'undefined') feather.replace();
}

// Add program handler
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
      if (window.showAlertOverlay) window.showAlertOverlay('error', 'Unexpected server response.');
      return;
    }

    if (response.ok) {
      const emptyRow = document.querySelector('#svProgramsTable tbody .sv-empty-row');
      if (emptyRow) emptyRow.remove();
      
      insertProgramRow(data.program);
      form.reset();
      closeModal('addProgramModal');
      if (window.showAlertOverlay) window.showAlertOverlay('success', data.message);
    }
  } catch (err) {
    console.error(err);
    if (window.showAlertOverlay) window.showAlertOverlay('error', 'Something went wrong.');
  }
});

// Edit program click handler
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.editProgramBtn');
  if (!btn) return;

  console.log('Edit button clicked:', btn);
  console.log('Button datasets:', btn.dataset);

  const nameField = document.getElementById('editProgramName');
  const codeField = document.getElementById('editProgramCode');
  const descField = document.getElementById('editProgramDescription');

  console.log('Fields found:', nameField, codeField, descField);

  if (nameField) nameField.value = btn.dataset.name || '';
  if (codeField) codeField.value = btn.dataset.code || '';
  if (descField) descField.value = btn.dataset.description || '';
  
  const deptField = document.getElementById('editProgramDepartment');
  if (deptField) {
    deptField.value = btn.dataset.departmentId || '';
  }
  
  document.querySelector('#editProgramForm').setAttribute('action', `/admin/programs/${btn.dataset.id}`);
  document.getElementById('programEditLabel').textContent = btn.dataset.name || 'Program';
});

// Edit program submit handler
document.addEventListener('submit', async function (e) {
  if (e.target?.id !== 'editProgramForm') return;
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
      if (window.showAlertOverlay) window.showAlertOverlay('error', 'Unexpected server response.');
      return;
    }

    if (response.ok) {
      // Check if table is filtered by a specific department
      const departmentFilter = document.getElementById('departmentFilter');
      const currentFilter = departmentFilter ? departmentFilter.value : 'all';
      
      const row = document.querySelector(`#program-${data.program.id}`);
      
      // If table is filtered by a specific department and program was moved to a different department
      if (currentFilter !== 'all' && currentFilter != data.program.department_id) {
        // Remove the row from the filtered view since it no longer belongs to the filtered department
        if (row) {
          row.remove();
          
          // Check if table is now empty and show empty state
          const tbody = document.querySelector('#svProgramsTable tbody');
          const dataRows = tbody.querySelectorAll('tr:not(.sv-empty-row)');
          if (dataRows.length === 0) {
            showEmptyState();
          }
          
          // Show informative message about the department change
          const departmentName = data.program.department?.code || 'another department';
          if (window.showAlertOverlay) {
            window.showAlertOverlay('success', `${data.message} Program moved to ${departmentName} and removed from current filtered view.`);
          }
        }
        closeModal('editProgramModal');
      } else if (row) {
        // Update the row if it should remain in the current view
        const cells = row.querySelectorAll('td');
        const numbered = hasNumberColumn();
        
        // Check if department column is visible by counting header columns
        const headerCols = document.querySelectorAll('#svProgramsTable thead th').length;
        const hasDepartmentColumn = headerCols > 3; // More than 3 columns means department column is visible

        if (numbered) {
          if (cells[1]) cells[1].textContent = data.program.name;
          if (cells[2]) cells[2].textContent = data.program.code;
          if (hasDepartmentColumn && cells[3]) cells[3].textContent = data.program.department?.code || 'N/A';
          const actionsIndex = hasDepartmentColumn ? 4 : 3;
          if (cells[actionsIndex]) cells[actionsIndex].innerHTML = actionsHtml(data.program);
        } else {
          if (cells[0]) cells[0].textContent = data.program.name;
          if (cells[1]) cells[1].textContent = data.program.code;
          if (hasDepartmentColumn && cells[2]) cells[2].textContent = data.program.department?.code || 'N/A';
          const actionsIndex = hasDepartmentColumn ? 3 : 2;
          if (cells[actionsIndex]) cells[actionsIndex].innerHTML = actionsHtml(data.program);
        }
        
        // Update the row data attributes with new program data
        row.querySelector('.editProgramBtn')?.setAttribute('data-name', data.program.name);
        row.querySelector('.editProgramBtn')?.setAttribute('data-code', data.program.code);
        row.querySelector('.editProgramBtn')?.setAttribute('data-description', data.program.description || '');
        row.querySelector('.editProgramBtn')?.setAttribute('data-department-id', data.program.department_id);
        if (typeof feather !== 'undefined') feather.replace();
        
        closeModal('editProgramModal');
        if (window.showAlertOverlay) window.showAlertOverlay('success', data.message);
      }
    }
  } catch (err) {
    console.error(err);
    if (window.showAlertOverlay) window.showAlertOverlay('error', 'Something went wrong.');
  }
});

// Delete program click handler
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.deleteProgramBtn');
  if (!btn) return;

  // Populate the new delete modal fields
  document.getElementById('deleteProgramName').textContent = btn.dataset.name;
  document.getElementById('deleteProgramCode').textContent = btn.dataset.code;
  document.getElementById('deleteProgramId').value = btn.dataset.id;
  document.querySelector('#deleteProgramForm').setAttribute('action', `/admin/programs/${btn.dataset.id}`);
});

// Delete program submit handler
document.addEventListener('submit', async function (e) {
  if (e.target?.id !== 'deleteProgramForm') return;
  e.preventDefault();
  const form = e.target;

  try {
    const formAction = form.getAttribute('action');
    const rowId = formAction.split('/').pop();

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
      if (window.showAlertOverlay) window.showAlertOverlay('error', 'Unexpected server response.');
      return;
    }

    if (response.ok) {
      document.querySelector(`#program-${rowId}`)?.remove();
      
      const tbody = document.querySelector('#svProgramsTable tbody');
      const dataRows = tbody?.querySelectorAll('tr:not(.sv-empty-row)');
      if (tbody && dataRows && dataRows.length === 0) {
        showEmptyState();
      }
      
      closeModal('deleteProgramModal');
      if (window.showAlertOverlay) window.showAlertOverlay('success', data.message);
    }
  } catch (err) {
    console.error(err);
    if (window.showAlertOverlay) window.showAlertOverlay('error', 'Something went wrong.');
  }
});

// Initialize suggestion functionality when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  setupSuggestionListeners();
});

// Department filtering function
function filterByDepartment(departmentId) {
  const currentUrl = new URL(window.location.href);
  
  if (departmentId === 'all') {
    currentUrl.searchParams.delete('department_filter');
  } else {
    currentUrl.searchParams.set('department_filter', departmentId);
  }
  
  // Reload the page with the new filter
  window.location.href = currentUrl.toString();
}

// 🔍 Search functionality
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('programsSearch');
  if (searchInput) {
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        filterPrograms(this.value.toLowerCase());
      }, 300); // Debounce search
    });
  }

  function filterPrograms(searchTerm) {
    const tableBody = document.getElementById('svProgramsTable')?.querySelector('tbody');
    if (!tableBody) return;

    const rows = tableBody.querySelectorAll('tr:not(.sv-empty-row)');
    let visibleCount = 0;

    rows.forEach(row => {
      const codeCell = row.querySelector('td:first-child');
      const nameCell = row.querySelector('td:nth-child(2)');
      const departmentCell = row.querySelector('td:nth-child(3)');
      
      if (codeCell && nameCell && departmentCell) {
        const code = codeCell.textContent.toLowerCase();
        const name = nameCell.textContent.toLowerCase();
        const department = departmentCell.textContent.toLowerCase();
        
        const matches = code.includes(searchTerm) || 
                       name.includes(searchTerm) || 
                       department.includes(searchTerm);
        
        row.style.display = matches ? '' : 'none';
        
        if (matches) visibleCount++;
      }
    });

    // Show/hide empty state if needed
    showProgramsEmptyState(visibleCount === 0 && searchTerm !== '');
  }

  function showProgramsEmptyState(show) {
    const tableBody = document.getElementById('svProgramsTable')?.querySelector('tbody');
    if (!tableBody) return;

    let emptyRow = tableBody.querySelector('.sv-empty-row');
    
    if (show && !emptyRow) {
      emptyRow = document.createElement('tr');
      emptyRow.className = 'sv-empty-row';
      emptyRow.innerHTML = `
        <td colspan="4">
          <div class="sv-empty">
            <h6><i data-feather="search"></i> No programs found</h6>
            <p>Try adjusting your search terms or check your spelling.</p>
          </div>
        </td>
      `;
      tableBody.appendChild(emptyRow);
      
      // Re-initialize feather icons
      if (typeof feather !== 'undefined') {
        feather.replace();
      }
    } else if (!show && emptyRow) {
      emptyRow.remove();
    }
  }
});
