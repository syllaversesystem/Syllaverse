// -----------------------------------------------------------------------------
// * File: resources/js/faculty/syllabus-status.js
// * Description: Save functionality for syllabus status (Prepared/Reviewed/Approved)
// -----------------------------------------------------------------------------

/**
 * Save syllabus status fields to database
 * @param {boolean} showAlert - Whether to show success/error alerts
 * @returns {Promise<void>}
 */
async function saveSyllabusStatus(showAlert = true) {
  try {
    const syllabusDoc = document.getElementById('syllabus-document');
    if (!syllabusDoc) {
      throw new Error('Syllabus document container not found');
    }

    const syllabusId = syllabusDoc.getAttribute('data-syllabus-id');
    if (!syllabusId) {
      throw new Error('Syllabus ID not found');
    }

    // Collect status field values
    const data = {
      syllabus_id: syllabusId,
      prepared_by_name: document.querySelector('[name="prepared_by_name"]')?.value || null,
      prepared_by_title: document.querySelector('[name="prepared_by_title"]')?.value || null,
      prepared_by_date: document.querySelector('[name="prepared_by_date"]')?.value || null,
      reviewed_by_name: document.querySelector('[name="reviewed_by_name"]')?.value || null,
      reviewed_by_title: document.querySelector('[name="reviewed_by_title"]')?.value || null,
      reviewed_by_date: document.querySelector('[name="reviewed_by_date"]')?.value || null,
      approved_by_name: document.querySelector('[name="approved_by_name"]')?.value || null,
      approved_by_title: document.querySelector('[name="approved_by_title"]')?.value || null,
      approved_by_date: document.querySelector('[name="approved_by_date"]')?.value || null,
      status_remarks: document.querySelector('[name="status_remarks"]')?.value || null,
    };

    console.log('Saving syllabus status:', data);

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
      throw new Error('CSRF token not found');
    }

    const response = await fetch('/faculty/syllabus/save-status', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
      },
      body: JSON.stringify(data),
    });

    const result = await response.json();

    if (!response.ok) {
      throw new Error(result.message || 'Failed to save syllabus status');
    }

    console.log('Syllabus status saved successfully:', result);

    // Update data-original attributes to reflect saved values
    const statusFields = [
      'prepared_by_name', 'prepared_by_title', 'prepared_by_date',
      'reviewed_by_name', 'reviewed_by_title', 'reviewed_by_date',
      'approved_by_name', 'approved_by_title', 'approved_by_date',
      'status_remarks'
    ];

    statusFields.forEach(fieldName => {
      const field = document.querySelector(`[name="${fieldName}"]`);
      if (field) {
        field.dataset.original = field.value || '';
        // Hide unsaved pill
        const badge = document.getElementById(`unsaved-${fieldName}`);
        if (badge) {
          badge.classList.add('d-none');
        }
      }
    });

    // Update unsaved count
    if (typeof window.updateUnsavedCount === 'function') {
      window.updateUnsavedCount();
    }

    if (showAlert) {
      alert('Syllabus status saved successfully!');
    }

    return result;

  } catch (error) {
    console.error('Failed to save syllabus status:', error);
    if (showAlert) {
      alert('Failed to save syllabus status: ' + error.message);
    }
    throw error;
  }
}

// Expose globally for toolbar integration
window.saveSyllabusStatus = saveSyllabusStatus;

export { saveSyllabusStatus };
