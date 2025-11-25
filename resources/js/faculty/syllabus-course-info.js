/**
 * ===========================================================================================
 * File: resources/js/faculty/syllabus-course-info.js
 * Description: Course Info save functionality
 * ===========================================================================================
 */

document.addEventListener('DOMContentLoaded', function () {
  const syllabusId = document.getElementById('syllabus-document')?.dataset.syllabusId;

  /**
   * ===========================================================================================
   * saveCourseInfo()
   * ===========================================================================================
   * Saves all course info fields to the backend
   */
  window.saveCourseInfo = async function(showAlert = false) {
    if (!syllabusId) {
      console.error('Syllabus ID not found');
      return;
    }

    const form = document.getElementById('syllabusForm');
    if (!form) {
      console.error('Syllabus form not found');
      return;
    }

    // Collect all course info fields matching database structure
    const fields = [
      'course_title', 'course_code', 'course_category', 'course_prerequisites',
      'semester', 'year_level', 'credit_hours_text', 'instructor_name', 'employee_code',
      'reference_cmo', 'instructor_designation', 'date_prepared', 'instructor_email',
      'revision_no', 'academic_year', 'revision_date', 'course_description',
      'tla_strategies', 'contact_hours', 'contact_hours_lec', 'contact_hours_lab'
    ];

    // Build payload object
    const payload = {};
    
    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Collect course info fields
    fields.forEach((name) => {
      const el = form.querySelector(`[name="${name}"]`);
      if (el) {
        payload[name] = el.value ?? '';
      }
    });

    // Append criteria module inputs if present
    try {
      const critL = document.getElementById('criteria_lecture_input');
      const critLab = document.getElementById('criteria_laboratory_input');
      const critData = document.getElementById('criteria_data_input');
      
      if (critL) payload.criteria_lecture = critL.value || '';
      if (critLab) payload.criteria_laboratory = critLab.value || '';
      if (critData) payload.criteria_data = critData.value || '[]';
    } catch (e) {
      console.warn('Failed to append criteria inputs', e);
    }

    try {
      const response = await fetch(`/faculty/syllabi/${syllabusId}/course-info`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'same-origin',
        body: JSON.stringify(payload)
      });

      if (!response.ok) {
        let bodyText = '';
        try {
          const ct = response.headers.get('content-type') || '';
          if (ct.includes('application/json')) {
            const j = await response.json();
            bodyText = JSON.stringify(j);
            // If Laravel validation errors present
            if (j.errors) {
              const firstKey = Object.keys(j.errors)[0];
              const firstMsg = Array.isArray(j.errors[firstKey]) ? j.errors[firstKey][0] : j.errors[firstKey];
              throw new Error('Validation failed: ' + firstMsg + ' (' + firstKey + ')');
            }
          } else {
            bodyText = await response.text();
          }
        } catch (parseErr) {
          console.error('Failed to parse error response', parseErr);
        }
        const msg = `Server returned ${response.status} ${response.statusText}. Response: ${bodyText}`;
        throw new Error(msg);
      }

      // Success: hide unsaved pills and reset originals
      fields.forEach((name) => {
        const badge = document.getElementById(`unsaved-${name}`);
        if (badge) {
          badge.classList.add('d-none');
        }
        // Handle instructor group badge
        if (name.startsWith('instructor')) {
          const group = document.getElementById('unsaved-instructor_name');
          if (group) {
            group.classList.add('d-none');
          }
        }
        const el = form.querySelector(`[name="${name}"]`);
        if (el) {
          el.dataset.original = el.value ?? '';
          el.classList.remove('sv-new-highlight');
        }
      });

      // Update unsaved count if function exists
      if (typeof window.updateUnsavedCount === 'function') {
        window.updateUnsavedCount();
      }

      if (showAlert && window.showAlertOverlay) {
        window.showAlertOverlay('success', 'Course Info saved successfully');
      }

      console.log('Course Info saved successfully');

    } catch (error) {
      console.error('Error saving course info:', error);
      if (showAlert && window.showAlertOverlay) {
        window.showAlertOverlay('error', 'Error saving course info: ' + error.message);
      }
    }
  };
});
