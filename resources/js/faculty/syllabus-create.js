// -----------------------------------------------------------------------------
// * File: resources/js/faculty/syllabus-create.js
// * Description: Dedicated script for syllabus create modal functionality with two-phase flow
// -----------------------------------------------------------------------------

let currentPhase = 1;

/**
 * Initialize the create syllabus modal
 */
function initCreateSyllabusModal() {
  const modal = document.getElementById('selectSyllabusMetaModal');
  const form = modal?.querySelector('form');
  
  if (!modal || !form) return;

  // Phase elements
  const phase1 = document.getElementById('phase1');
  const phase2 = document.getElementById('phase2');
  const nextBtn = document.getElementById('nextPhaseBtn');
  const phase2Footer = document.getElementById('phase2Footer');
  const backBtn = document.getElementById('backPhaseBtn');
  const submitBtn = document.getElementById('createSyllabusBtn');
  const facultySelection = document.getElementById('facultySelection');
  const facultySearchInput = document.getElementById('facultySearchInput');
  const facultyCardsContainer = document.getElementById('facultyCardsContainer');

  // Faculty search functionality for cards
  if (facultySearchInput && facultyCardsContainer) {
    facultySearchInput.addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase().trim();
      const cards = facultyCardsContainer.querySelectorAll('.faculty-card');
      
      cards.forEach(card => {
        const name = card.getAttribute('data-name') || '';
        const code = card.getAttribute('data-code') || '';
        const matches = name.includes(searchTerm) || code.includes(searchTerm);
        
        card.style.display = matches ? '' : 'none';
      });
    });
  }

  // Recipient type radios
  const recipientRadios = document.querySelectorAll('input[name="recipient_type"]');

  // Show/hide faculty selection based on recipient type
  recipientRadios.forEach(radio => {
    radio.addEventListener('change', function() {
      if (this.value === 'shared' || this.value === 'others') {
        facultySelection.style.display = 'block';
      } else if (this.value === 'myself') {
        facultySelection.style.display = 'none';
        // Clear all checkbox selections when switching to "myself"
        const checkboxes = document.querySelectorAll('.faculty-checkbox');
        checkboxes.forEach(cb => cb.checked = false);
      }
    });
  });

  // Initialize visibility based on default selection
  const defaultChecked = document.querySelector('input[name="recipient_type"]:checked');
  if (defaultChecked && defaultChecked.value === 'myself') {
    facultySelection.style.display = 'none';
  }

  // Next button - go to phase 2
  nextBtn?.addEventListener('click', function(e) {
    console.log('Next button clicked');
    
    const recipientType = document.querySelector('input[name="recipient_type"]:checked')?.value;
    const selectedFaculty = document.querySelectorAll('.faculty-checkbox:checked');

    console.log('Recipient type:', recipientType);
    console.log('Selected faculty count:', selectedFaculty.length);
    console.log('Phase1 element:', phase1);
    console.log('Phase2 element:', phase2);

    // Validate phase 1
    if ((recipientType === 'shared' || recipientType === 'others') && selectedFaculty.length === 0) {
      console.log('Validation failed: No faculty selected');
      if (window.showAlertOverlay) {
        window.showAlertOverlay('error', 'Please select at least one faculty member');
      } else {
        alert('Please select at least one faculty member');
      }
      return;
    }

    console.log('Moving to phase 2');
    
    // Move to phase 2
    currentPhase = 2;
    if (phase1) phase1.style.display = 'none';
    if (phase2) phase2.style.display = 'block';
    if (nextBtn) nextBtn.classList.add('d-none');
    if (phase2Footer) {
      phase2Footer.classList.remove('d-none');
      phase2Footer.classList.add('d-flex');
    }

    // Update modal title
    const modalTitle = modal.querySelector('.modal-title');
    if (modalTitle) {
      const icon = modalTitle.querySelector('i');
      modalTitle.innerHTML = '';
      if (icon) modalTitle.appendChild(icon);
      modalTitle.append(' Syllabus Details');
    }
    
    console.log('Phase 2 display:', phase2?.style.display);
  });

  // Back button - go to phase 1
  backBtn?.addEventListener('click', function() {
    currentPhase = 1;
    phase1.style.display = 'block';
    phase2.style.display = 'none';
    if (nextBtn) nextBtn.classList.remove('d-none');
    if (phase2Footer) {
      phase2Footer.classList.remove('d-flex');
      phase2Footer.classList.add('d-none');
    }

    // Restore modal title
    const modalTitle = modal.querySelector('.modal-title');
    if (modalTitle) {
      const icon = modalTitle.querySelector('i');
      modalTitle.innerHTML = '';
      if (icon) modalTitle.appendChild(icon);
      modalTitle.append(' Create Syllabus');
    }
  });

  // Reset form when modal is closed
  modal.addEventListener('hidden.bs.modal', function() {
    form.reset();
    clearValidationErrors();
    
    // Reset to phase 1
    currentPhase = 1;
    phase1.style.display = 'block';
    phase2.style.display = 'none';
    if (nextBtn) nextBtn.classList.remove('d-none');
    if (phase2Footer) {
      phase2Footer.classList.remove('d-flex');
      phase2Footer.classList.add('d-none');
    }
    facultySelection.style.display = 'none';
    
    // Reset faculty search and selections
    if (facultySearchInput) {
      facultySearchInput.value = '';
      const cards = facultyCardsContainer?.querySelectorAll('.faculty-card');
      cards?.forEach(card => card.style.display = '');
    }
    // Clear all checkbox selections
    const checkboxes = document.querySelectorAll('.faculty-checkbox');
    checkboxes.forEach(cb => cb.checked = false);

    // Restore modal title
    const modalTitle = modal.querySelector('.modal-title');
    if (modalTitle) {
      const icon = modalTitle.querySelector('i');
      modalTitle.innerHTML = '';
      if (icon) modalTitle.appendChild(icon);
      modalTitle.append(' Create Syllabus');
    }
  });

  // Handle form submission
  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const originalBtnContent = submitBtn?.innerHTML;
    
    try {
      // Show loading state
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
      }

      // Clear previous errors
      clearValidationErrors();

      // Submit form data
      const formData = new FormData(form);
      const response = await fetch(form.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': getCsrfToken(),
          'Accept': 'application/json'
        },
        body: formData
      });

      const data = await parseJsonSafe(response);

      if (!data) {
        throw new Error('Invalid server response');
      }

      // Handle validation errors
      if (response.status === 422 && data.errors) {
        displayValidationErrors(data.errors);
        throw new Error('Validation failed');
      }

      if (!response.ok) {
        throw new Error(data.message || `Server error: ${response.status}`);
      }

      // Success - redirect to the new syllabus
      if (data.redirect || data.syllabusId) {
        const redirectUrl = data.redirect || `/faculty/syllabi/${data.syllabusId}`;
        window.location.href = redirectUrl;
      } else {
        // Fallback: reload the page
        window.location.reload();
      }

    } catch (err) {
      console.error('Create syllabus failed:', err);
      
      // Restore button state
      if (submitBtn && originalBtnContent) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnContent;
      }

      // Show error message (only if not validation error)
      if (err.message !== 'Validation failed') {
        const errorMsg = err.message || 'Something went wrong while creating the syllabus.';
        if (window.showAlertOverlay) {
          window.showAlertOverlay('error', errorMsg);
        } else {
          alert(errorMsg);
        }
      }
    }
  });
}

/**
 * Display validation errors inline
 */
function displayValidationErrors(errors) {
  for (const field in errors) {
    const input = document.querySelector(`[name="${field}"]`);
    if (!input) continue;

    // Add error class to input
    input.classList.add('is-invalid');

    // Create or update error message
    let errorDiv = input.parentElement.querySelector('.invalid-feedback');
    if (!errorDiv) {
      errorDiv = document.createElement('div');
      errorDiv.className = 'invalid-feedback';
      input.parentElement.appendChild(errorDiv);
    }
    errorDiv.textContent = errors[field][0];
  }
}

/**
 * Clear all validation errors
 */
function clearValidationErrors() {
  const modal = document.getElementById('selectSyllabusMetaModal');
  if (!modal) return;

  // Remove error classes from inputs
  modal.querySelectorAll('.is-invalid').forEach(el => {
    el.classList.remove('is-invalid');
  });

  // Remove error messages
  modal.querySelectorAll('.invalid-feedback').forEach(el => {
    el.remove();
  });
}

/**
 * Get CSRF token from meta tag
 */
function getCsrfToken() {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  if (!token) {
    console.error('CSRF token not found');
  }
  return token || '';
}

/**
 * Safely parse JSON response
 */
async function parseJsonSafe(response) {
  try {
    return await response.json();
  } catch (e) {
    console.error('Failed to parse JSON response:', e);
    return null;
  }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
  initCreateSyllabusModal();
});

// Export for use in other modules
export { initCreateSyllabusModal, displayValidationErrors, clearValidationErrors };
