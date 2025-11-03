// Faculty Programs module - adapted from admin version
console.log("Faculty Programs module loading...");

// Add CSS for loading spinner animation
const spinnerCSS = `
  .spinner {
    animation: spin 1s linear infinite;
  }
  
  @keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }
  
  .programs-loading-row td {
    background-color: rgba(248, 249, 250, 0.8);
  }
`;

// Inject CSS if not already present
if (!document.querySelector('#programs-spinner-css')) {
  const style = document.createElement('style');
  style.id = 'programs-spinner-css';
  style.textContent = spinnerCSS;
  document.head.appendChild(style);
}

let programsTable;

function getCsrfToken() {
    return document.querySelector("meta[name=csrf-token]")?.getAttribute("content") || "";
}

async function parseJsonSafe(response) {
  try { return await response.json(); } catch { return null; }
}

function closeModal(modalId) {
  const el = document.getElementById(modalId);
  const m = window.bootstrap?.Modal?.getInstance(el);
  if (m) m.hide();
}

// Department filter function with AJAX
async function filterByDepartment(departmentId) {
  console.log('Faculty Programs - Filtering by department:', departmentId);
  
  const tableBody = document.querySelector('#svProgramsTable tbody');
  const departmentFilter = document.getElementById('departmentFilter');
  
  if (!tableBody) {
    console.error('Table body not found');
    return;
  }
  
  try {
    // Clear search input when filtering
    clearSearchOnFilter();
    
    // Show loading state
    showTableLoading();
    
    // Disable the filter dropdown during request
    if (departmentFilter) {
      departmentFilter.disabled = true;
    }
    
    // Make AJAX request to filter endpoint
    const response = await fetch(`/faculty/programs/filter?department=${encodeURIComponent(departmentId)}`, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken()
      }
    });
    
    const data = await parseJsonSafe(response);
    
    if (!data || !response.ok) {
      throw new Error(data?.message || `Server error: ${response.status}`);
    }
    
    if (data.success && data.html) {
      // Update table content
      tableBody.innerHTML = data.html;
      
      // Update URL without page reload
      const url = new URL(window.location);
      if (departmentId === 'all') {
        url.searchParams.delete('department');
      } else {
        url.searchParams.set('department', departmentId);
      }
      window.history.replaceState({}, '', url.toString());
      
      // Update programs config for other JavaScript functions
      window.programsConfig = window.programsConfig || {};
      window.programsConfig.departmentFilter = departmentId === 'all' ? null : departmentId;
      
      // Re-initialize Feather icons for new content
      if (typeof feather !== 'undefined') {
        feather.replace();
        setTimeout(() => feather.replace(), 10);
      }
      
      // Update table header if needed (show/hide department column)
      updateTableHeader(departmentId, data.department_filter);
      
      console.log(`Filter applied: ${data.count} programs found for department ${departmentId}`);
      
    } else {
      throw new Error('Invalid response format');
    }
    
  } catch (error) {
    console.error('Filter request failed:', error);
    
    // Show error message
    if (window.showAlertOverlay) {
      window.showAlertOverlay('error', `Failed to filter programs: ${error.message}`);
    } else {
      alert(`Failed to filter programs: ${error.message}`);
    }
    
    // Fallback to page reload on error
    const url = new URL(window.location);
    if (departmentId === 'all') {
      url.searchParams.delete('department');
    } else {
      url.searchParams.set('department', departmentId);
    }
    window.location.href = url.toString();
    
  } finally {
    // Re-enable the filter dropdown
    if (departmentFilter) {
      departmentFilter.disabled = false;
    }
    hideTableLoading();
  }
}

// Show loading state in table
function showTableLoading() {
  const tableBody = document.querySelector('#svProgramsTable tbody');
  if (!tableBody) return;
  
  const colspan = document.querySelectorAll('#svProgramsTable thead th').length;
  const loadingRow = `
    <tr class="programs-loading-row">
      <td colspan="${colspan}" class="text-center py-4">
        <div class="d-flex flex-column align-items-center">
          <i data-feather="loader" class="spinner mb-2" style="width: 32px; height: 32px;"></i>
          <p class="mb-0 text-muted">Loading programs...</p>
        </div>
      </td>
    </tr>
  `;
  
  tableBody.innerHTML = loadingRow;
  if (typeof feather !== 'undefined') {
    feather.replace();
  }
}

// Hide loading state
function hideTableLoading() {
  const loadingRow = document.querySelector('.programs-loading-row');
  if (loadingRow) {
    loadingRow.remove();
  }
}

// Update table header based on filter
function updateTableHeader(departmentId, departmentFilter) {
  const table = document.getElementById('svProgramsTable');
  if (!table) return;
  
  const thead = table.querySelector('thead');
  if (!thead) return;
  
  const showDepartmentColumn = window.programsConfig?.showDepartmentColumn && departmentId === 'all';
  
  // Rebuild header row
  const headerRow = thead.querySelector('tr');
  if (headerRow) {
    const departmentHeader = '<th><i data-feather="layers"></i> Department</th>';
    const baseHeaders = [
      '<th><i data-feather="type"></i> Program Name</th>',
      '<th><i data-feather="code"></i> Code</th>'
    ];
    const actionsHeader = '<th class="text-end"><i data-feather="more-vertical"></i></th>';
    
    let headers = [...baseHeaders];
    if (showDepartmentColumn) {
      headers.push(departmentHeader);
    }
    headers.push(actionsHeader);
    
    headerRow.innerHTML = headers.join('');
    
    // Re-initialize feather icons in header
    if (typeof feather !== 'undefined') {
      feather.replace();
    }
  }
}

// Make function globally available
window.filterByDepartment = filterByDepartment;

function showEmptyState() {
  const tbody = document.querySelector('#svProgramsTable tbody');
  if (!tbody) return;
  
  const emptyRow = `
    <tr class="programs-empty-row">
      <td colspan="100%" class="text-center text-muted py-4">
        <div class="d-flex flex-column align-items-center">
          <i data-feather="folder-x" class="mb-2" style="width: 48px; height: 48px;"></i>
          <p class="mb-2">No programs found.</p>
        </div>
      </td>
    </tr>
  `;
  
  tbody.innerHTML = emptyRow;
  if (window.feather) window.feather.replace();
}

function hasNumberColumn() {
  const th0 = document.querySelector('#svProgramsTable thead th:first-child');
  if (!th0) return false;
  const t = (th0.textContent || '').trim().toLowerCase();
  return t === '#' || t.includes('no.') || t === '';
}

function actionsHtml(program) {
  return `
    <button type="button" class="btn programs-action-btn edit-btn rounded-circle me-2 editProgramBtn"
            data-bs-toggle="modal" data-bs-target="#editProgramModal"
            data-id="${program.id}" data-name="${program.name || ''}"
            data-code="${program.code || ''}" data-description="${program.description || ''}"
            data-department-id="${program.department_id || ''}"
            title="Edit" aria-label="Edit">
      <i data-feather="edit"></i>
    </button>
    <button type="button" class="btn programs-action-btn delete-btn rounded-circle deleteProgramBtn"
            data-bs-toggle="modal" data-bs-target="#deleteProgramModal"
            data-id="${program.id}" data-name="${program.name || ''}"
            data-code="${program.code || ''}"
            title="Delete" aria-label="Delete">
      <i data-feather="trash"></i>
    </button>
  `;
}

function insertProgramRow(program) {
  const tbody = document.querySelector('#svProgramsTable tbody');
  if (!tbody) return;

    const emptyRow = tbody.querySelector('.programs-empty-row');
  if (emptyRow) emptyRow.remove();

  const numbered = hasNumberColumn();
  
  // Check if department filter is showing all departments AND user has permission to see department column
  const departmentFilter = document.getElementById('departmentFilter');
  const hasPermissionForDepartmentColumn = window.programsConfig?.showDepartmentColumn ?? true;
  const showDepartmentColumn = hasPermissionForDepartmentColumn && departmentFilter && departmentFilter.value === 'all';
  
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
  
  // Ensure Feather icons are properly initialized for the new row
  if (typeof feather !== 'undefined') {
    feather.replace();
    // Additional initialization with a slight delay to ensure DOM is ready
    setTimeout(() => {
      feather.replace();
    }, 10);
  }
}

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
  
  document.querySelector('#editProgramForm').setAttribute('action', `/faculty/programs/${btn.dataset.id}`);
  document.getElementById('programEditLabel').textContent = btn.dataset.name || 'Program';
});

// Edit program submit handler with improved AJAX
document.addEventListener('submit', async function (e) {
  if (e.target?.id !== 'editProgramForm') return;
  e.preventDefault();
  const form = e.target;
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalBtnContent = submitBtn?.innerHTML;
  
  const formData = new FormData(form);
  formData.append('_method', 'PUT');
  
  try {
    // Show loading state
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i data-feather="loader" class="spinner"></i> Updating...';
      if (typeof feather !== 'undefined') {
        setTimeout(() => feather.replace(), 10);
      }
    }

    const response = await fetch(form.action, {
      method: 'POST',
      headers: { 
        'X-CSRF-TOKEN': getCsrfToken(),
        'Accept': 'application/json'
      },
      body: formData,
    });

    const data = await parseJsonSafe(response);
    if (!data) {
      throw new Error('Invalid server response');
    }

    if (response.status === 422 && data.errors) {
      // Handle validation errors
      let errorMessage = 'Validation failed:\n';
      for (const field in data.errors) {
        errorMessage += `• ${data.errors[field].join(', ')}\n`;
      }
      throw new Error(errorMessage);
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
          row.style.transition = 'opacity 0.3s ease-out';
          row.style.opacity = '0';
          
          setTimeout(() => {
            row.remove();
            
            // Check if table is now empty and show empty state
            const tbody = document.querySelector('#svProgramsTable tbody');
            const dataRows = tbody?.querySelectorAll('tr:not(.programs-empty-row)');
            if (dataRows.length === 0) {
              showEmptyState();
            }
          }, 300);
          
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
        
        // Add highlight animation for updated row
        row.style.backgroundColor = '#fff3cd';
        setTimeout(() => {
          row.style.transition = 'background-color 0.5s ease';
          row.style.backgroundColor = '';
        }, 100);
        
        // Re-initialize Feather icons after updating action buttons
        if (typeof feather !== 'undefined') {
          feather.replace();
          setTimeout(() => feather.replace(), 10);
        }
        
        // Update the row data attributes with new program data
        const editBtn = row.querySelector('.editProgramBtn');
        if (editBtn) {
          editBtn.setAttribute('data-name', data.program.name);
          editBtn.setAttribute('data-code', data.program.code);
          editBtn.setAttribute('data-description', data.program.description || '');
          editBtn.setAttribute('data-department-id', data.program.department_id);
        }
        
        closeModal('editProgramModal');
        if (window.showAlertOverlay) {
          window.showAlertOverlay('success', data.message || 'Program updated successfully!');
        }
      }
    } else {
      throw new Error(data.message || `Server error: ${response.status}`);
    }
  } catch (err) {
    console.error('Edit program failed:', err);
    
    // Show error message
    const errorMsg = err.message || 'Something went wrong while updating the program.';
    if (window.showAlertOverlay) {
      window.showAlertOverlay('error', errorMsg);
    }
  } finally {
    // Restore button state
    if (submitBtn && originalBtnContent) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalBtnContent;
      if (typeof feather !== 'undefined') {
        setTimeout(() => feather.replace(), 10);
      }
    }
  }
});

// Delete button handler (vanilla JavaScript)
document.addEventListener('click', function(e) {
    if (!e.target.closest('.deleteProgramBtn')) return;
    
    const button = e.target.closest('.deleteProgramBtn');
    console.log('🔥 DELETE BUTTON HANDLER TRIGGERED! 🔥');
    console.log('This element:', button);
    
    // Get program ID with multiple fallback methods
    let programId = button.dataset.id;
    if (!programId || programId === '0') {
        programId = button.getAttribute('data-id');
    }
    
    const programName = button.dataset.name || button.getAttribute('data-name');
    const programCode = button.dataset.code || button.getAttribute('data-code');
    
    console.log('=== DELETE BUTTON CLICKED ===');
    console.log('Raw program ID from dataset.id:', button.dataset.id);
    console.log('Raw program ID from getAttribute:', button.getAttribute('data-id'));
    console.log('Final program ID used:', programId);
    console.log('Program name:', programName);
    console.log('Program code:', programCode);
    console.log('Button element HTML:', button.outerHTML);
    console.log('Button dataset:', button.dataset);
    console.log('All data attributes:', Object.keys(button.dataset).map(key => `${key}: ${button.dataset[key]}`));
    
    // Validate program ID
    if (!programId || programId === '0' || isNaN(programId)) {
        console.error('Invalid program ID detected:', programId);
        alert('Error: Unable to get program ID. Please refresh the page and try again.');
        return;
    }
    
    const modal = document.getElementById('deleteProgramModal');
    console.log('Modal found:', modal !== null);
    console.log('Modal HTML (first 300 chars):', modal?.outerHTML?.substring(0, 300));
    
    if (!modal) {
        console.error('Delete modal not found!');
        return;
    }
    
    // Handle both master-data modal and standalone programs modal
    const programNameElement = modal.querySelector('#deleteProgramName');
    const programWhatElement = modal.querySelector('#programDeleteWhat');
    const programCodeElement = modal.querySelector('#deleteProgramCode');
    
    console.log('Program name element (#deleteProgramName):', programNameElement !== null);
    console.log('Program what element (#programDeleteWhat):', programWhatElement !== null);
    console.log('Program code element (#deleteProgramCode):', programCodeElement !== null);
    
    if (programNameElement) {
        // Standalone programs module modal
        console.log('Using standalone programs modal structure');
        programNameElement.textContent = programName || 'this program';
        
        // Also update program code if available
        if (programCodeElement && programCode) {
            programCodeElement.textContent = programCode;
        }
    } else if (programWhatElement) {
        // Master-data modal  
        console.log('Using master-data modal structure');
        programWhatElement.textContent = programName || 'this program';
    } else {
        console.error('No suitable program name element found in modal!');
    }
    
    // Update existing hidden input
    let hiddenInput = modal.querySelector('input[name="id"]');
    if (!hiddenInput) {
        hiddenInput = modal.querySelector('input[name="program_id"]');
    }
    
    if (hiddenInput) {
        hiddenInput.value = programId;
        console.log('Updated existing hidden input:', hiddenInput.name, '=', programId);
    } else {
        console.error('No suitable hidden input found in modal!');
    }
    
    // Update the form action with enhanced URL construction
    const form = modal.querySelector('#deleteProgramForm');
    if (!form) {
        console.error('Form not found!');
        return;
    }
    
    const currentAction = form.getAttribute('action');
    console.log('Form element found:', form !== null);
    console.log('Current form action before update:', currentAction);
    console.log('Current action ends with /0?', currentAction?.endsWith('/0'));
    console.log('Current action has numeric ending?', /\/\d+$/.test(currentAction || ''));
    
    let newAction;
    if (currentAction && currentAction.endsWith('/0')) {
        // Replace the trailing /0 with the program ID
        newAction = currentAction.slice(0, -1) + programId;
        console.log('Using slice method: removing /0 and adding', programId);
    } else if (currentAction && /\/\d+$/.test(currentAction)) {
        // Replace existing numeric ID at the end
        newAction = currentAction.replace(/\/\d+$/, '/' + programId);
        console.log('Using regex replace method for numeric ending');
    } else {
        // Append the program ID
        newAction = (currentAction || '') + '/' + programId;
        console.log('Using append method');
    }
    
    console.log('Updated form action:', newAction);
    form.setAttribute('action', newAction);
    console.log('Form action set successfully');
    console.log('Form after action update:', form.outerHTML.substring(0, 200) + '...');
    console.log('=== END DELETE BUTTON PROCESSING ===');
});

// Delete program submit handler with improved AJAX
document.addEventListener('submit', async function (e) {
  if (e.target?.id !== 'deleteProgramForm') return;
  
  e.preventDefault();
  const form = e.target;
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalBtnContent = submitBtn?.innerHTML;

  try {
    // Show loading state
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i data-feather="loader" class="spinner"></i> Processing...';
      if (typeof feather !== 'undefined') {
        setTimeout(() => feather.replace(), 10);
      }
    }

    // Get the program ID from form data
    const formData = new FormData(form);
    const programId = formData.get('id') || form.getAttribute('action').split('/').pop();
    const actionType = formData.get('action_type') || 'remove';
    
    // Validate program ID
    if (!programId || programId === '0') {
      throw new Error('Invalid program ID');
    }

    const response = await fetch(form.action, {
      method: 'POST',
      headers: { 
        'X-CSRF-TOKEN': getCsrfToken(),
        'Accept': 'application/json'
      },
      body: formData,
    });

    const data = await parseJsonSafe(response);
    
    if (!data) {
      throw new Error('Invalid server response');
    }

    if (response.status === 422 && data.errors) {
      // Handle validation errors
      let errorMessage = 'Validation failed:\n';
      for (const field in data.errors) {
        errorMessage += `• ${data.errors[field].join(', ')}\n`;
      }
      throw new Error(errorMessage);
    }

    if (response.ok) {
      // Remove the program row with smooth animation
      const programRow = document.querySelector(`#program-${programId}`);
      if (programRow) {
        // Add fade out animation
        programRow.style.transition = 'opacity 0.3s ease-out';
        programRow.style.opacity = '0';
        
        setTimeout(() => {
          programRow.remove();
          
          // Check if table is empty and show empty state
          const tbody = document.querySelector('#svProgramsTable tbody');
          const remainingRows = tbody?.querySelectorAll('tr:not(.programs-empty-row)');
          if (remainingRows && remainingRows.length === 0) {
            showEmptyState();
          }
        }, 300);
      }
      
      // Close modal and show success message
      closeModal('deleteProgramModal');
      
      const successMessage = data.message || (actionType === 'delete' 
        ? 'Program permanently deleted!' 
        : 'Program removed successfully!');
        
      if (window.showAlertOverlay) {
        window.showAlertOverlay('success', successMessage);
      }
    } else {
      throw new Error(data.message || `Server error: ${response.status}`);
    }
  } catch (err) {
    console.error('Delete operation failed:', err);
    
    // Show error message
    const errorMsg = err.message || 'Something went wrong while processing your request.';
    if (window.showAlertOverlay) {
      window.showAlertOverlay('error', errorMsg);
    }
  } finally {
    // Restore button state
    if (submitBtn && originalBtnContent) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalBtnContent;
      if (typeof feather !== 'undefined') {
        setTimeout(() => feather.replace(), 10);
      }
    }
  }
});

// Add program submit handler with improved AJAX
document.addEventListener('submit', async function (e) {
  if (e.target?.id !== 'addProgramForm') return;
  e.preventDefault();
  const form = e.target;
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalBtnContent = submitBtn?.innerHTML;

  try {
    // Show loading state
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i data-feather="loader" class="spinner"></i> Creating...';
      if (typeof feather !== 'undefined') {
        setTimeout(() => feather.replace(), 10);
      }
    }

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
      throw new Error('Invalid server response');
    }

    if (response.status === 422 && data.errors) {
      // Handle validation errors
      let errorMessage = 'Validation failed:\n';
      for (const field in data.errors) {
        errorMessage += `• ${data.errors[field].join(', ')}\n`;
      }
      throw new Error(errorMessage);
    }

    if (response.ok) {
      // Remove empty state if present
      const emptyRow = document.querySelector('#svProgramsTable tbody .programs-empty-row');
      if (emptyRow) emptyRow.remove();
      
      // Add new program row with animation
      insertProgramRow(data.program);
      const newRow = document.querySelector(`#program-${data.program.id}`);
      if (newRow) {
        newRow.style.backgroundColor = '#d4edda';
        setTimeout(() => {
          newRow.style.transition = 'background-color 0.5s ease';
          newRow.style.backgroundColor = '';
        }, 100);
      }
      
      // Reset form and close modal
      form.reset();
      closeModal('addProgramModal');
      
      if (window.showAlertOverlay) {
        window.showAlertOverlay('success', data.message || 'Program created successfully!');
      }
    } else {
      throw new Error(data.message || `Server error: ${response.status}`);
    }
  } catch (err) {
    console.error('Add program failed:', err);
    
    // Show error message
    const errorMsg = err.message || 'Something went wrong while creating the program.';
    if (window.showAlertOverlay) {
      window.showAlertOverlay('error', errorMsg);
    }
  } finally {
    // Restore button state
    if (submitBtn && originalBtnContent) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalBtnContent;
      if (typeof feather !== 'undefined') {
        setTimeout(() => feather.replace(), 10);
      }
    }
  }
});

// Enhanced search functionality that works with AJAX filtering
document.addEventListener('input', function(e) {
  if (e.target.id !== 'programsSearch') return;
  
  const searchTerm = e.target.value.toLowerCase().trim();
  const rows = document.querySelectorAll('#svProgramsTable tbody tr:not(.programs-empty-row):not(.programs-loading-row)');
  let visibleCount = 0;
  
  rows.forEach(row => {
    const cells = row.querySelectorAll('td');
    const programName = cells[0]?.textContent?.toLowerCase() || '';
    const programCode = cells[1]?.textContent?.toLowerCase() || '';
    
    // Check if department column exists by counting header columns
    const headerCols = document.querySelectorAll('#svProgramsTable thead th').length;
    const hasDepartmentColumn = headerCols > 3;
    const department = hasDepartmentColumn ? (cells[2]?.textContent?.toLowerCase() || '') : '';
    
    const matches = programName.includes(searchTerm) || 
                   programCode.includes(searchTerm) || 
                   (hasDepartmentColumn && department.includes(searchTerm));
    
    if (matches) {
      row.style.display = '';
      visibleCount++;
    } else {
      row.style.display = 'none';
    }
  });
  
  // Show empty state if no matches
  const tbody = document.querySelector('#svProgramsTable tbody');
  if (visibleCount === 0 && searchTerm !== '') {
    const existingEmptyRow = tbody.querySelector('.programs-empty-row');
    if (!existingEmptyRow) {
      const colspan = document.querySelectorAll('#svProgramsTable thead th').length;
      const emptyRow = `
        <tr class="programs-empty-row">
          <td colspan="${colspan}" class="text-center text-muted py-4">
            <div class="d-flex flex-column align-items-center">
              <i data-feather="search" class="mb-2" style="width: 48px; height: 48px;"></i>
              <p class="mb-2">No results found for "${searchTerm}".</p>
            </div>
          </td>
        </tr>
      `;
      tbody.insertAdjacentHTML('beforeend', emptyRow);
      if (window.feather) window.feather.replace();
    }
  } else {
    // Remove empty state if there are matches or search is cleared
    const existingEmptyRow = tbody.querySelector('.programs-empty-row');
    if (existingEmptyRow && (visibleCount > 0 || searchTerm === '')) {
      existingEmptyRow.remove();
    }
  }
});

// Clear search when department filter changes
function clearSearchOnFilter() {
  const searchInput = document.getElementById('programsSearch');
  if (searchInput && searchInput.value.trim()) {
    searchInput.value = '';
    console.log('Search cleared due to department filter change');
  }
}

// Delete modal radio button handlers
function setupDeleteModalHandlers() {
  const removeRadio = document.getElementById('removeProgram');
  const deleteRadio = document.getElementById('deleteProgram');
  const confirmBtn = document.getElementById('confirmActionBtn');
  const deleteWarning = document.getElementById('deleteWarning');

  function updateButtonAndWarning() {
    if (deleteRadio && deleteRadio.checked) {
      if (confirmBtn) {
        confirmBtn.innerHTML = '<i data-feather="trash"></i> Delete Permanently';
        confirmBtn.className = 'btn btn-danger';
      }
      if (deleteWarning) deleteWarning.style.display = 'block';
    } else {
      if (confirmBtn) {
        confirmBtn.innerHTML = '<i data-feather="minus-circle"></i> Remove';
        confirmBtn.className = 'btn btn-warning';
      }
      if (deleteWarning) deleteWarning.style.display = 'none';
    }
    // Re-initialize feather icons
    if (typeof feather !== 'undefined') {
      setTimeout(() => feather.replace(), 10);
    }
  }

  if (removeRadio) removeRadio.addEventListener('change', updateButtonAndWarning);
  if (deleteRadio) deleteRadio.addEventListener('change', updateButtonAndWarning);
  
  // Initialize on modal show
  const deleteModal = document.getElementById('deleteProgramModal');
  if (deleteModal) {
    deleteModal.addEventListener('shown.bs.modal', function() {
      updateButtonAndWarning();
    });
  }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log("Faculty Programs module ready");
    
    // Initialize Feather icons with multiple attempts to ensure they load
    function initializeFeatherIcons() {
        if (typeof feather !== 'undefined' && feather.replace) {
            feather.replace();
            console.log('Feather icons initialized for faculty programs');
        } else {
            console.log('Feather icons not available, retrying...');
            setTimeout(initializeFeatherIcons, 100);
        }
    }
    
    // Call immediately and also with delays
    initializeFeatherIcons();
    setTimeout(initializeFeatherIcons, 100);
    setTimeout(initializeFeatherIcons, 500);
    
    // Make it globally available for debugging
    window.refreshFeatherIcons = initializeFeatherIcons;
    
    // Add keyboard shortcut for department filter (Alt + D)
    document.addEventListener('keydown', function(e) {
        if (e.altKey && e.key.toLowerCase() === 'd') {
            e.preventDefault();
            const departmentFilter = document.getElementById('departmentFilter');
            if (departmentFilter && !departmentFilter.disabled) {
                departmentFilter.focus();
            }
        }
    });
    
    // Add visual feedback for department filter changes
    const departmentFilter = document.getElementById('departmentFilter');
    if (departmentFilter) {
        departmentFilter.addEventListener('change', function(e) {
            // Add a subtle animation to indicate the filter is being applied
            e.target.style.transform = 'scale(0.98)';
            setTimeout(() => {
                e.target.style.transform = '';
            }, 150);
        });
    }
    
    const programsTableElement = document.getElementById("programsTable");
    if (programsTableElement) {
        // Note: DataTables requires jQuery, but this table might not be using DataTables in the standalone programs module
        console.log("Programs table element found, but DataTables functionality may need to be implemented differently");
        // TODO: Implement DataTables with vanilla JavaScript or add jQuery if needed
        /* 
        programsTable = $("#programsTable").DataTable({
            processing: true,
            pageLength: 25
        });
        */
    }
    
    // Setup delete modal handlers
    setupDeleteModalHandlers();
    
    // Setup deleted program search functionality
    setupDeletedProgramSearch();
});

// Deleted Program Search and Restore Functionality
function setupDeletedProgramSearch() {
    const programNameInput = document.getElementById('programName');
    const suggestionsContainer = document.getElementById('programNameSuggestions');
    
    if (!programNameInput || !suggestionsContainer) {
        console.log('Program name input or suggestions container not found - skipping deleted program search setup');
        return;
    }
    
    let searchTimeout;
    let currentSuggestions = [];
    
    console.log('Setting up deleted program search functionality');
    
    // Listen for input in program name field
    programNameInput.addEventListener('input', function(e) {
        const query = e.target.value.trim();
        
        // Clear previous timeout
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        
        // Hide suggestions if query is too short
        if (query.length < 2) {
            hideSuggestions();
            return;
        }
        
        // Debounce search requests
        searchTimeout = setTimeout(() => {
            searchDeletedPrograms(query);
        }, 300);
    });
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!programNameInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            hideSuggestions();
        }
    });
    
    // Handle suggestion clicks
    suggestionsContainer.addEventListener('click', function(e) {
        const suggestionItem = e.target.closest('.suggestion-item');
        if (!suggestionItem) return;
        
        const programData = JSON.parse(suggestionItem.dataset.programData);
        restoreDeletedProgram(programData);
    });
    
    function searchDeletedPrograms(query) {
        console.log('Searching for deleted programs:', query);
        
        // Make request to search deleted programs
        fetch(`/faculty/programs/search-deleted?q=${encodeURIComponent(query)}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(programs => {
            console.log('Found deleted programs:', programs);
            displaySuggestions(programs);
        })
        .catch(error => {
            console.error('Error searching deleted programs:', error);
            hideSuggestions();
        });
    }
    
    function displaySuggestions(programs) {
        currentSuggestions = programs;
        
        if (programs.length === 0) {
            hideSuggestions();
            return;
        }
        
        const suggestionHtml = programs.map(program => `
            <div class="suggestion-item" data-program-data='${JSON.stringify(program)}'>
                <div class="suggestion-main">
                    ${program.name} (${program.code})
                    <span class="suggestion-restore-badge">Restore</span>
                </div>
                <div class="suggestion-meta">
                    Department: ${program.department_name}
                </div>
            </div>
        `).join('');
        
        suggestionsContainer.innerHTML = suggestionHtml;
        suggestionsContainer.style.display = 'block';
    }
    
    function hideSuggestions() {
        suggestionsContainer.style.display = 'none';
        suggestionsContainer.innerHTML = '';
        currentSuggestions = [];
    }
    
    function restoreDeletedProgram(programData) {
        console.log('Restoring deleted program:', programData);
        
        // Fill the form fields with the deleted program data
        const form = document.getElementById('addProgramForm');
        if (!form) return;
        
        // Populate form fields
        document.getElementById('programName').value = programData.name;
        document.getElementById('programCode').value = programData.code;
        
        const descriptionField = document.getElementById('programDescription');
        if (descriptionField && programData.description) {
            descriptionField.value = programData.description;
        }
        
        const departmentField = document.getElementById('programDepartment') || document.querySelector('input[name="department_id"]');
        if (departmentField && programData.department_id) {
            departmentField.value = programData.department_id;
        }
        
        // Hide suggestions
        hideSuggestions();
        
        // Show confirmation message
        if (window.showAlertOverlay) {
            window.showAlertOverlay('info', `Program "${programData.name}" data loaded. Click "Create" to restore it.`);
        } else {
            console.log(`Program "${programData.name}" data loaded. Click "Create" to restore it.`);
        }
        
        // Change submit button text to indicate restoration
        const submitButton = document.getElementById('addProgramSubmit');
        if (submitButton) {
            submitButton.innerHTML = '<i data-feather="refresh-cw"></i> Restore Program';
            // Re-initialize feather icons if available
            if (typeof feather !== 'undefined') {
                setTimeout(() => feather.replace(), 10);
            }
        }
    }
    
    // Reset form and button when modal is shown
    const addModal = document.getElementById('addProgramModal');
    if (addModal) {
        addModal.addEventListener('shown.bs.modal', function() {
            // Reset button text
            const submitButton = document.getElementById('addProgramSubmit');
            if (submitButton) {
                submitButton.innerHTML = '<i data-feather="plus"></i> Create';
                // Re-initialize feather icons if available
                if (typeof feather !== 'undefined') {
                    setTimeout(() => feather.replace(), 10);
                }
            }
            
            // Clear form
            const form = document.getElementById('addProgramForm');
            if (form) {
                form.reset();
            }
            
            // Auto-select department if there's a department filter active
            console.log('Programs config:', window.programsConfig);
            if (window.programsConfig?.departmentFilter) {
                const departmentSelect = document.getElementById('addProgramDepartment');
                console.log('Department filter found:', window.programsConfig.departmentFilter);
                console.log('Department select element:', departmentSelect);
                if (departmentSelect) {
                    console.log('Setting department value to:', window.programsConfig.departmentFilter);
                    departmentSelect.value = window.programsConfig.departmentFilter;
                    console.log('Department select value after setting:', departmentSelect.value);
                }
            }
            
            // Hide any open suggestions
            hideSuggestions();
        });
    }
}