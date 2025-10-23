/**
 * Shared Master Data CRUD Operations
 * Handles Add, Edit, Delete operations for SO, SDG, IGA, CDIO
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Shared Master Data CRUD module loaded successfully');
    
    // Initialize CSRF token for all AJAX requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        window.csrfToken = csrfToken.getAttribute('content');
    }

    // Add form submission handler
    const addForm = document.getElementById('addMasterDataForm');
    if (addForm) {
        addForm.addEventListener('submit', handleAddSubmission);
    }

    // Edit form submission handler
    const editForm = document.getElementById('editMasterDataForm');
    if (editForm) {
        editForm.addEventListener('submit', handleEditSubmission);
    }

    // Delete form submission handler
    const deleteForm = document.getElementById('deleteMasterDataForm');
    if (deleteForm) {
        deleteForm.addEventListener('submit', handleDeleteSubmission);
    }

    // Initialize Feather icons when modals are shown
    document.addEventListener('shown.bs.modal', function(e) {
        if (window.feather) {
            feather.replace();
        }
        console.log('Modal shown:', e.target.id);
    });

    // Clear errors when modal is hidden
    document.addEventListener('hidden.bs.modal', function(e) {
        const modal = e.target;
        const errorContainer = modal.querySelector('.alert-danger');
        if (errorContainer) {
            hideErrors(errorContainer);
        }
        console.log('Modal hidden:', e.target.id);
    });
});

/**
 * Handle Add Form Submission
 */
async function handleAddSubmission(event) {
    event.preventDefault();
    
    const form = event.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    const errorContainer = document.getElementById('addMasterDataErrors');
    
    // Show loading state
    submitBtn.innerHTML = '<i data-feather="loader" class="rotating"></i> Creating...';
    submitBtn.disabled = true;
    
    // Clear previous errors
    hideErrors(errorContainer);
    
    try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const result = await response.json();

        if (response.ok) {
            // Success - close modal and refresh page
            const modal = bootstrap.Modal.getInstance(document.getElementById('addMasterDataModal'));
            modal.hide();
            
            // Show success message and reload
            showSuccessMessage('Item created successfully!');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            // Handle validation errors
            if (result.errors) {
                showErrors(errorContainer, result.errors);
            } else {
                showErrors(errorContainer, { general: [result.message || 'An error occurred while creating the item.'] });
            }
        }
    } catch (error) {
        console.error('Add submission error:', error);
        showErrors(errorContainer, { general: ['Network error. Please try again.'] });
    } finally {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        if (window.feather) feather.replace();
    }
}

/**
 * Handle Edit Form Submission
 */
async function handleEditSubmission(event) {
    event.preventDefault();
    
    const form = event.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    const errorContainer = document.getElementById('editMasterDataErrors');
    
    // Show loading state
    submitBtn.innerHTML = '<i data-feather="loader" class="rotating"></i> Updating...';
    submitBtn.disabled = true;
    
    // Clear previous errors
    hideErrors(errorContainer);
    
    try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const result = await response.json();

        if (response.ok) {
            // Success - close modal and refresh page
            const modal = bootstrap.Modal.getInstance(document.getElementById('editMasterDataModal'));
            modal.hide();
            
            // Show success message and reload
            showSuccessMessage('Item updated successfully!');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            // Handle validation errors
            if (result.errors) {
                showErrors(errorContainer, result.errors);
            } else {
                showErrors(errorContainer, { general: [result.message || 'An error occurred while updating the item.'] });
            }
        }
    } catch (error) {
        console.error('Edit submission error:', error);
        showErrors(errorContainer, { general: ['Network error. Please try again.'] });
    } finally {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        if (window.feather) feather.replace();
    }
}

/**
 * Handle Delete Form Submission
 */
async function handleDeleteSubmission(event) {
    event.preventDefault();
    
    const form = event.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = '<i data-feather="loader" class="rotating"></i> Deleting...';
    submitBtn.disabled = true;
    
    try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const result = await response.json();

        if (response.ok) {
            // Success - close modal and refresh page
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteMasterDataModal'));
            modal.hide();
            
            // Show success message and reload
            showSuccessMessage('Item deleted successfully!');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            // Handle errors
            showErrorMessage(result.message || 'An error occurred while deleting the item.');
        }
    } catch (error) {
        console.error('Delete submission error:', error);
        showErrorMessage('Network error. Please try again.');
    } finally {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        if (window.feather) feather.replace();
    }
}

/**
 * Show validation errors in the form
 */
function showErrors(container, errors) {
    let errorHtml = '<strong>Please fix the following errors:</strong><ul class="mb-0 mt-2">';
    
    for (const [field, messages] of Object.entries(errors)) {
        messages.forEach(message => {
            errorHtml += `<li>${message}</li>`;
        });
    }
    
    errorHtml += '</ul>';
    
    container.innerHTML = errorHtml;
    container.classList.remove('d-none');
    container.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

/**
 * Hide error container
 */
function hideErrors(container) {
    container.classList.add('d-none');
    container.innerHTML = '';
}

/**
 * Show success message
 */
function showSuccessMessage(message) {
    // Create or update success notification
    let notification = document.getElementById('success-notification');
    if (!notification) {
        notification = document.createElement('div');
        notification.id = 'success-notification';
        notification.className = 'alert alert-success alert-dismissible fade show position-fixed';
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        document.body.appendChild(notification);
    }
    
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i data-feather="check-circle" class="me-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    if (window.feather) feather.replace();
    
    // Auto-hide after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

/**
 * Show error message
 */
function showErrorMessage(message) {
    // Create or update error notification
    let notification = document.getElementById('error-notification');
    if (!notification) {
        notification = document.createElement('div');
        notification.id = 'error-notification';
        notification.className = 'alert alert-danger alert-dismissible fade show position-fixed';
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        document.body.appendChild(notification);
    }
    
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i data-feather="alert-circle" class="me-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    if (window.feather) feather.replace();
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Add CSS for rotating loader
const style = document.createElement('style');
style.textContent = `
    .rotating {
        animation: rotate 1s linear infinite;
    }
    
    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    #success-notification,
    #error-notification {
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border: none;
    }
    
    #success-notification {
        background: linear-gradient(135deg, rgba(40, 167, 69, 0.1) 0%, rgba(34, 139, 34, 0.05) 100%);
        color: #155724;
        border-left: 4px solid #28a745;
    }
    
    #error-notification {
        background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(255, 0, 0, 0.05) 100%);
        color: #721c24;
        border-left: 4px solid #dc3545;
    }
`;
document.head.appendChild(style);