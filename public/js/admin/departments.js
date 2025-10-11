// -----------------------------------------------------------------------------
// File: resources/js/admin/departments.js
// Description: Handles feather icons, modal data setup, and AJAX form submissions for Admin Departments page – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-01-04] Created admin version based on superadmin departments.js
// -----------------------------------------------------------------------------

// 🔄 Function to refresh the departments table via AJAX
function refreshDepartmentsTable() {
    console.log('Refreshing departments table...');
    
    fetch('/admin/departments/table-content')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const tableBody = document.getElementById('departmentsTableBody');
                if (tableBody) {
                    tableBody.innerHTML = data.html;
                    
                    // Re-initialize feather icons for the new content
                    if (typeof feather !== 'undefined') {
                        feather.replace();
                    }
                    
                    console.log('Departments table refreshed successfully');
                } else {
                    console.error('Table body element not found');
                }
            } else {
                console.error('Failed to refresh table:', data.message);
                if (typeof window.showAlertOverlay === 'function') {
                    window.showAlertOverlay('error', 'Failed to refresh table');
                }
            }
        })
        .catch(error => {
            console.error('Error refreshing table:', error);
            if (typeof window.showAlertOverlay === 'function') {
                window.showAlertOverlay('error', 'Error refreshing table');
            }
        });
}

document.addEventListener('DOMContentLoaded', function () {
    console.log('Admin departments.js loaded');
    
    // 🪶 Replace all feather icons safely
    if (typeof feather !== 'undefined') {
        feather.replace();
    } else {
        console.warn("⚠️ Feather icons not loaded: skipping feather.replace()");
    }

    // 🔁 Refresh feather icons when dropdowns open (for action menus)
    document.querySelectorAll('.dropdown').forEach(dropdown => {
        dropdown.addEventListener('shown.bs.dropdown', function () {
            if (typeof feather !== 'undefined') feather.replace();
        });
    });

    // 🆕 Clear add modal when it opens
    const addModal = document.getElementById('addDepartmentModal');
    if (addModal) {
        addModal.addEventListener('show.bs.modal', function () {
            const form = document.getElementById('addDepartmentForm');
            const errorDiv = document.getElementById('addDepartmentErrors');
            
            if (form) {
                form.reset();
            }
            
            if (errorDiv) {
                errorDiv.classList.add('d-none');
                errorDiv.innerHTML = '';
            }
            
            console.log('Add department modal opened - form cleared');
        });
    }

    // 📝 Setup Edit Department modal
    window.setEditDepartment = function (button) {
        console.log('setEditDepartment called', button.dataset);
        const id = button.dataset.id;
        const name = button.dataset.name;
        const code = button.dataset.code;
        const form = document.getElementById('editDepartmentForm');

        if (!form) {
            console.error('Edit form not found');
            return;
        }

        form.action = `/admin/departments/${id}`;
        
        const idInput = document.getElementById('editDepartmentId');
        const nameInput = form.querySelector('#editDepartmentName');
        const codeInput = form.querySelector('#editDepartmentCode');
        
        if (idInput) idInput.value = id;
        if (nameInput) nameInput.value = name;
        if (codeInput) codeInput.value = code;
        
        // Clear any previous errors
        const errorDiv = document.getElementById('editDepartmentErrors');
        if (errorDiv) {
            errorDiv.classList.add('d-none');
            errorDiv.innerHTML = '';
        }
    };

    // 🗑️ Setup Delete Department modal
    window.setDeleteDepartment = function (button) {
        console.log('setDeleteDepartment called', button.dataset);
        const id = button.dataset.id;
        const name = button.dataset.name;
        const code = button.dataset.code;
        
        // Set form action
        const deleteForm = document.getElementById('deleteDepartmentForm');
        const idInput = document.getElementById('deleteDepartmentId');
        
        if (deleteForm) {
            deleteForm.action = `/admin/departments/${id}`;
        } else {
            console.error('Delete form not found');
        }
        
        if (idInput) idInput.value = id;
        
        // Populate department details in modal
        const nameElement = document.getElementById('deleteDepartmentName');
        const codeElement = document.getElementById('deleteDepartmentCode');
        
        if (nameElement) nameElement.textContent = name || 'Unknown';
        if (codeElement) codeElement.textContent = code || 'Unknown';
    };

    // 📨 Handle Add Department form submission
    const addForm = document.getElementById('addDepartmentForm');
    if (addForm) {
        // Remove any existing action attribute to prevent form navigation
        addForm.removeAttribute('action');
        
        addForm.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            console.log('Add department form submitted');
            
            const formData = new FormData(this);
            const submitBtn = document.getElementById('addDepartmentSubmit');
            const errorDiv = document.getElementById('addDepartmentErrors');
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i data-feather="loader" class="spinner"></i> Creating...';
            if (typeof feather !== 'undefined') feather.replace();
            
            // Hide previous errors
            errorDiv.classList.add('d-none');
            errorDiv.innerHTML = '';
            
            fetch('/admin/departments', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers.get('content-type'));
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    console.warn('Server returned non-JSON response');
                    throw new Error('Server returned non-JSON response');
                }
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Add department response:', data);
                
                // Reset form immediately before closing modal
                addForm.reset();
                
                // Clear any error messages
                if (errorDiv) {
                    errorDiv.classList.add('d-none');
                    errorDiv.innerHTML = '';
                }
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addDepartmentModal'));
                if (modal) modal.hide();
                
                if (data && data.success) {
                    // Show success overlay toast
                    if (typeof window.showAlertOverlay === 'function') {
                        window.showAlertOverlay('success', data.message || 'Department created successfully!');
                    } else {
                        console.log('showAlertOverlay not available');
                        alert('Department created successfully!');
                    }
                    
                    // Refresh the table instead of reloading the page
                    setTimeout(() => refreshDepartmentsTable(), 300);
                } else {
                    console.log('Add department failed:', data);
                    // Show errors in toast only (skip modal to prevent pretty print)
                    if (data && data.errors) {
                        let errorMessages = [];
                        Object.values(data.errors).forEach(errors => {
                            errors.forEach(error => {
                                errorMessages.push(error);
                            });
                        });
                        
                        // Show toast for validation errors
                        if (typeof window.showAlertOverlay === 'function') {
                            window.showAlertOverlay('error', errorMessages.join(', '));
                        } else {
                            alert('Error: ' + errorMessages.join(', '));
                        }
                    } else {
                        const errorMessage = (data && data.message) || 'An error occurred';
                        // Show toast for general errors
                        if (typeof window.showAlertOverlay === 'function') {
                            window.showAlertOverlay('error', errorMessage);
                        } else {
                            alert('Error: ' + errorMessage);
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Add department error:', error);
                if (typeof window.showAlertOverlay === 'function') {
                    window.showAlertOverlay('error', 'An unexpected error occurred: ' + error.message);
                } else {
                    alert('An unexpected error occurred: ' + error.message);
                    errorDiv.innerHTML = 'An unexpected error occurred: ' + error.message;
                    errorDiv.classList.remove('d-none');
                }
            })
            .finally(() => {
                // Reset button
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i data-feather="plus"></i> Create';
                if (typeof feather !== 'undefined') feather.replace();
            });
        });
    }

    // 📨 Handle Edit Department form submission
    const editForm = document.getElementById('editDepartmentForm');
    if (editForm) {
        // Remove any existing action attribute to prevent form navigation
        editForm.removeAttribute('action');
        
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            const formData = new FormData(this);
            const submitBtn = document.getElementById('editDepartmentSubmit');
            const errorDiv = document.getElementById('editDepartmentErrors');
            const departmentId = document.getElementById('editDepartmentId').value;
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i data-feather="loader" class="spinner"></i> Updating...';
            if (typeof feather !== 'undefined') feather.replace();
            
            // Hide previous errors
            errorDiv.classList.add('d-none');
            errorDiv.innerHTML = '';
            
            fetch(`/admin/departments/${departmentId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal and show success toast
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editDepartmentModal'));
                    modal.hide();
                    
                    // Show success overlay toast
                    if (typeof window.showAlertOverlay === 'function') {
                        window.showAlertOverlay('success', data.message || 'Department updated successfully!');
                    }
                    
                    // Refresh the table instead of reloading the page
                    setTimeout(() => refreshDepartmentsTable(), 300);
                } else {
                    // Show errors in modal and toast
                    if (data.errors) {
                        let errorHtml = '<ul class="mb-0">';
                        let errorMessages = [];
                        Object.values(data.errors).forEach(errors => {
                            errors.forEach(error => {
                                errorHtml += `<li>${error}</li>`;
                                errorMessages.push(error);
                            });
                        });
                        errorHtml += '</ul>';
                        errorDiv.innerHTML = errorHtml;
                        
                        // Also show toast for validation errors
                        if (typeof window.showAlertOverlay === 'function') {
                            window.showAlertOverlay('error', errorMessages.join(', '));
                        }
                    } else {
                        errorDiv.innerHTML = data.message || 'An error occurred';
                        
                        // Show toast for general errors
                        if (typeof window.showAlertOverlay === 'function') {
                            window.showAlertOverlay('error', data.message || 'An error occurred');
                        }
                    }
                    errorDiv.classList.remove('d-none');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof window.showAlertOverlay === 'function') {
                    window.showAlertOverlay('error', 'An unexpected error occurred');
                } else {
                    errorDiv.innerHTML = 'An unexpected error occurred';
                    errorDiv.classList.remove('d-none');
                }
            })
            .finally(() => {
                // Reset button
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i data-feather="save"></i> Update';
                if (typeof feather !== 'undefined') feather.replace();
            });
        });
    }

    // 📨 Handle Delete Department form submission
    const deleteForm = document.getElementById('deleteDepartmentForm');
    if (deleteForm) {
        // Remove any existing action attribute to prevent form navigation
        deleteForm.removeAttribute('action');
        
        deleteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            const formData = new FormData(this);
            const submitBtn = document.getElementById('deleteDepartmentSubmit');
            const departmentId = document.getElementById('deleteDepartmentId').value;
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i data-feather="loader" class="spinner"></i> Deleting...';
            if (typeof feather !== 'undefined') feather.replace();
            
            fetch(`/admin/departments/${departmentId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Close modal regardless of success/error
                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteDepartmentModal'));
                modal.hide();
                
                if (data.success) {
                    // Show success overlay toast
                    if (typeof window.showAlertOverlay === 'function') {
                        window.showAlertOverlay('success', data.message || 'Department deleted successfully!');
                    }
                    
                    // Refresh the table instead of reloading the page
                    setTimeout(() => refreshDepartmentsTable(), 300);
                } else {
                    // Show error overlay toast
                    if (typeof window.showAlertOverlay === 'function') {
                        window.showAlertOverlay('error', data.message || 'An error occurred while deleting the department');
                    } else {
                        alert(data.message || 'An error occurred while deleting the department');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteDepartmentModal'));
                modal.hide();
                
                // Show error overlay toast
                if (typeof window.showAlertOverlay === 'function') {
                    window.showAlertOverlay('error', 'An unexpected error occurred');
                } else {
                    alert('An unexpected error occurred');
                }
            })
            .finally(() => {
                // Reset button
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i data-feather="trash-2"></i> Delete';
                if (typeof feather !== 'undefined') feather.replace();
            });
        });
    }

    // 🎨 Add spinner animation styles
    const style = document.createElement('style');
    style.textContent = `
        .spinner {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);

    // 📝 AJAX Helper Functions
    // Note: addDepartmentToTable removed to prevent pretty print issues
    // Using page reload instead for cleaner experience

    // Note: AJAX table manipulation functions removed to prevent pretty print issues
    // Using page reload instead for cleaner, more reliable experience

    // 🔍 Search functionality
    const searchInput = document.querySelector('input[type="search"]');
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterDepartments(this.value.toLowerCase());
            }, 300); // Debounce search
        });
    }

    function filterDepartments(searchTerm) {
        const tableBody = document.getElementById('departmentsTableBody');
        if (!tableBody) return;

        const rows = tableBody.querySelectorAll('tr');
        let visibleCount = 0;

        rows.forEach(row => {
            const codeCell = row.querySelector('td:first-child');
            const nameCell = row.querySelector('td:nth-child(2)');
            
            if (codeCell && nameCell) {
                const code = codeCell.textContent.toLowerCase();
                const name = nameCell.textContent.toLowerCase();
                
                const matches = code.includes(searchTerm) || name.includes(searchTerm);
                row.style.display = matches ? '' : 'none';
                
                if (matches) visibleCount++;
            }
        });

        // Show/hide empty state if needed
        showEmptyState(visibleCount === 0 && searchTerm !== '');
    }

    function showEmptyState(show) {
        const tableBody = document.getElementById('departmentsTableBody');
        if (!tableBody) return;

        let emptyRow = tableBody.querySelector('.sv-empty-row');
        
        if (show && !emptyRow) {
            emptyRow = document.createElement('tr');
            emptyRow.className = 'sv-empty-row';
            emptyRow.innerHTML = `
                <td colspan="4">
                    <div class="sv-empty">
                        <h6><i data-feather="search"></i> No departments found</h6>
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