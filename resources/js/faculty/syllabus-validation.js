/**
 * ===========================================================================================
 * File: resources/js/faculty/syllabus-validation.js
 * Description: Syllabus validation and progress tracking system
 * ===========================================================================================
 */

(function() {
  // Required fields per partial - can be expanded later
  const REQUIRED_FIELDS = {
    course_info: {
      course_description: 'Course Rationale and Description',
      tla_strategies: 'Teaching, Learning, and Assessment Strategies',
      // Add more fields here as needed
    },
    criteria_assessment: {
      criteria_data: 'Criteria for Assessment',
      // Add more fields here as needed
    },
    ilo: {
      'ilos[]': 'Intended Learning Outcomes',
      // Add more fields here as needed
    },
    assessment_tasks: {
      assessment_tasks_data: 'Assessment Tasks Distribution',
      // Add more fields here as needed
    },
    iga: {
      'igas[]': 'Institutional Graduate Attributes',
      // Add more fields here as needed
    },
    so: {
      'sos[]': 'Student Outcomes',
      // Add more fields here as needed
    },
    cdio: {
      'cdios[]': 'CDIO Framework Skills',
      // Add more fields here as needed
    },
    sdg: {
      'sdgs[]': 'Sustainable Development Goals',
      // Add more fields here as needed
    },
    tla: {
      'tla[]': 'Teaching, Learning, and Assessment Activities',
      // Add more fields here as needed
    },
    assessment_mapping: {
      'assessment-mapping-data': 'Assessment Schedule Mapping',
      // Add more fields here as needed
    },
    ilo_so_cpa: {
      'ilo-so-cpa-data': 'ILO-SO-CPA Mapping',
      // Add more fields here as needed
    },
    ilo_iga: {
      'ilo-iga-data': 'ILO-IGA Mapping',
      // Add more fields here as needed
    },
    ilo_cdio_sdg: {
      'ilo-cdio-sdg-data': 'ILO-CDIO-SDG Mapping',
      // Add more fields here as needed
    },
  };

  // Track validation state
  const validationState = {
    isValid: false,
    completedFields: {},
    totalRequired: 0,
    completedRequired: 0,
  };

  /**
   * Calculate total required and completed fields
   */
  function calculateValidationStatus() {
    let total = 0;
    let completed = 0;

    Object.keys(REQUIRED_FIELDS).forEach((partial) => {
      const fields = REQUIRED_FIELDS[partial];
      Object.keys(fields).forEach((fieldName) => {
        total++;
        const isComplete = isFieldComplete(fieldName);
        validationState.completedFields[fieldName] = isComplete;
        if (isComplete) completed++;
      });
    });

    validationState.totalRequired = total;
    validationState.completedRequired = completed;
    validationState.isValid = total > 0 && completed === total;

    return {
      isValid: validationState.isValid,
      completed: completed,
      total: total,
      percentage: total > 0 ? Math.round((completed / total) * 100) : 0,
    };
  }

  /**
   * Check if a single field is complete (not empty)
   */
  function isFieldComplete(fieldName) {
    // Special handling for array fields (ILOs, IGAs, SOs, CDIOs, SDGs)
    if (fieldName === 'ilos[]' || fieldName === 'igas[]' || fieldName === 'sos[]' || fieldName === 'cdios[]' || fieldName === 'sdgs[]') {
      const elements = document.querySelectorAll(`[name="${fieldName}"]`);
      if (elements.length === 0) return false;
      // Check if at least one item has content
      return Array.from(elements).some(el => (el.value?.trim() || '').length > 0);
    }

    // Special handling for TLA array (check if any TLA row has topic or outcomes content)
    if (fieldName === 'tla[]') {
      const tlaRows = document.querySelectorAll('#tlaTable tbody tr:not(#tla-placeholder)');
      if (tlaRows.length === 0) return false;
      // Check if at least one TLA row has content in topic or outcomes
      return Array.from(tlaRows).some(row => {
        const topic = row.querySelector('[name*="[topic]"]')?.value?.trim() || '';
        const outcomes = row.querySelector('[name*="[outcomes]"]')?.value?.trim() || '';
        return topic.length > 0 || outcomes.length > 0;
      });
    }

    // Special handling for assessment mapping (check if any distribution task has content or any week has marks)
    if (fieldName === 'assessment-mapping-data') {
      const distTable = document.querySelector('.assessment-mapping table.distribution');
      const weekTable = document.querySelector('.assessment-mapping table.week');
      if (!distTable || !weekTable) return false;

      const distInputs = distTable.querySelectorAll('input.distribution-input');
      const weekRows = distTable.querySelectorAll('tr:not(:first-child)');

      // Check if any distribution has a task name
      const hasDistribution = Array.from(distInputs).some(input => 
        (input.value?.trim() || '').length > 0
      );

      // Check if any week cell has an 'x' mark
      const hasWeekMarks = Array.from(weekRows).some(row => {
        const cells = row.querySelectorAll('td.week-mapping');
        return Array.from(cells).some(cell => 
          (cell.textContent?.trim() || '') === 'x'
        );
      });

      return hasDistribution || hasWeekMarks;
    }

    // Special handling for ILO-SO-CPA mapping (check if table has any real data)
    if (fieldName === 'ilo-so-cpa-data') {
      const mapping = document.querySelector('.ilo-so-cpa-mapping');
      if (!mapping) return false;

      const mappingTable = mapping.querySelector('.mapping');
      if (!mappingTable) return false;

      const tbody = mappingTable.querySelector('tbody') || mappingTable;
      const dataRows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.querySelector('td'));

      if (dataRows.length === 0) return false;

      // Check if any row has meaningful content (not just "No ILO" placeholder)
      return dataRows.some(row => {
        const cells = Array.from(row.querySelectorAll('td'));
        if (cells.length === 0) return false;

        // Check first cell (ILO)
        const iloCell = cells[0];
        const iloInput = iloCell.querySelector('input');
        const iloText = iloInput ? iloInput.value?.trim() : iloCell.textContent?.trim();
        
        // Skip placeholder rows
        if (iloText === 'No ILO') return false;

        // Check if ILO has content
        if (iloText && iloText.length > 0) return true;

        // Check if any SO/CPA cell has content
        return cells.slice(1).some(cell => {
          const textarea = cell.querySelector('textarea');
          return textarea && (textarea.value?.trim() || '').length > 0;
        });
      });
    }

    // Special handling for ILO-IGA mapping (check if table has any real data)
    if (fieldName === 'ilo-iga-data') {
      const mapping = document.querySelector('.ilo-iga-mapping');
      if (!mapping) return false;

      const mappingTable = mapping.querySelector('.mapping');
      if (!mappingTable) return false;

      const tbody = mappingTable.querySelector('tbody') || mappingTable;
      const dataRows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.querySelector('td'));

      if (dataRows.length === 0) return false;

      // Check if any row has meaningful content (not just "No ILO" placeholder)
      return dataRows.some(row => {
        const cells = Array.from(row.querySelectorAll('td'));
        if (cells.length === 0) return false;

        // Check first cell (ILO)
        const iloCell = cells[0];
        const iloInput = iloCell.querySelector('input');
        const iloText = iloInput ? iloInput.value?.trim() : iloCell.textContent?.trim();
        
        // Skip placeholder rows
        if (iloText === 'No ILO') return false;

        // Check if ILO has content
        if (iloText && iloText.length > 0) return true;

        // Check if any IGA cell has content
        return cells.slice(1).some(cell => {
          const textarea = cell.querySelector('textarea');
          return textarea && (textarea.value?.trim() || '').length > 0;
        });
      });
    }

    // Special handling for ILO-CDIO-SDG mapping (check if table has any real data)
    if (fieldName === 'ilo-cdio-sdg-data') {
      const mapping = document.querySelector('.ilo-cdio-sdg-mapping');
      if (!mapping) return false;

      const mappingTable = mapping.querySelector('.mapping');
      if (!mappingTable) return false;

      const allRows = mappingTable.querySelectorAll('tr');
      if (allRows.length < 3) return false;

      // Skip header rows (0, 1, 2 is "No ILO" placeholder)
      const dataRows = Array.from(allRows).slice(3);

      if (dataRows.length === 0) return false;

      // Check if any row has meaningful content (not just "No ILO" placeholder)
      return dataRows.some(row => {
        const cells = Array.from(row.querySelectorAll('td'));
        if (cells.length === 0) return false;

        // Check first cell (ILO)
        const iloCell = cells[0];
        const iloInput = iloCell.querySelector('input');
        const iloText = iloInput ? iloInput.value?.trim() : iloCell.textContent?.trim();
        
        // Skip placeholder rows
        if (iloText === 'No ILO') return false;

        // Check if ILO has content
        if (iloText && iloText.length > 0) return true;

        // Check if any CDIO or SDG cell has content
        return cells.slice(1).some(cell => {
          const textarea = cell.querySelector('textarea');
          return textarea && (textarea.value?.trim() || '').length > 0;
        });
      });
    }

    const el = document.querySelector(`[name="${fieldName}"]`);
    if (!el) return false;

    let value = el.value?.trim() || '';
    
    // Special handling for criteria_data (JSON array)
    if (fieldName === 'criteria_data') {
      try {
        const parsed = JSON.parse(value);
        if (!Array.isArray(parsed) || parsed.length === 0) return false;
        // Check if any section has content
        return parsed.some(section => {
          const heading = (section.heading || '').trim();
          const hasValues = Array.isArray(section.value) && section.value.some(v => 
            (v.description || '').trim() !== '' || (v.percent || '').trim() !== ''
          );
          return heading !== '' || hasValues;
        });
      } catch (e) {
        return false;
      }
    }

    // Special handling for assessment_tasks_data (JSON object with sections)
    if (fieldName === 'assessment_tasks_data') {
      try {
        const parsed = JSON.parse(value);
        if (!parsed || !Array.isArray(parsed.sections) || parsed.sections.length === 0) return false;
        // Check if any section has sub rows with content
        return parsed.sections.some(section => {
          if (!Array.isArray(section.sub_rows) || section.sub_rows.length === 0) return false;
          return section.sub_rows.some(subRow => 
            (subRow.code || '').trim() !== '' || 
            (subRow.task || '').trim() !== '' ||
            (subRow.ird || '').trim() !== ''
          );
        });
      } catch (e) {
        return false;
      }
    }
    
    return value.length > 0;
  }

  /**
   * Update the progress bar UI
   */
  function updateProgressBar() {
    const status = calculateValidationStatus();
    const progressContainer = document.getElementById('syllabus-progress-container');
    const progressBar = document.getElementById('syllabus-progress-bar');
    const progressText = document.getElementById('syllabus-progress-text');

    if (progressContainer && progressBar && progressText) {
      progressBar.style.width = status.percentage + '%';
      progressText.textContent = `${status.percentage}% complete (${status.completed}/${status.total})`;

      // Add color based on progress
      if (status.percentage === 100) {
        progressBar.classList.remove('bg-warning', 'bg-danger');
        progressBar.classList.add('bg-success');
      } else if (status.percentage >= 50) {
        progressBar.classList.remove('bg-danger', 'bg-success');
        progressBar.classList.add('bg-warning');
      } else {
        progressBar.classList.remove('bg-warning', 'bg-success');
        progressBar.classList.add('bg-danger');
      }
    }

    updateSubmitButtonState(status.isValid);
  }

  // Expose updateProgressBar globally so other modules can trigger re-calculation
  window.updateProgressBar = updateProgressBar;

  /**
   * Update the submit button disabled state based on validation
   */
  function updateSubmitButtonState(isValid) {
    const submitBtn = document.getElementById('syllabusSubmitBtn');
    if (!submitBtn) return;

    if (isValid) {
      submitBtn.disabled = false;
      submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
      submitBtn.title = 'Submit For Review';
    } else {
      submitBtn.disabled = true;
      submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
      submitBtn.title = 'Complete all required fields before submitting';
    }
  }

  /**
   * Public API: Get current validation status
   */
  window.getSyllabusValidationStatus = function() {
    return calculateValidationStatus();
  };

  /**
   * Public API: Check if syllabus is valid for submission
   */
  window.isSyllabusValid = function() {
    return calculateValidationStatus().isValid;
  };

  /**
   * Initialize validation system
   */
  window.initSyllabusValidation = function() {
    console.log('Initializing syllabus validation...');

    // Initial update
    updateProgressBar();

    // Listen to all required field changes
    Object.keys(REQUIRED_FIELDS).forEach((partial) => {
      const fields = REQUIRED_FIELDS[partial];
      Object.keys(fields).forEach((fieldName) => {
        const el = document.querySelector(`[name="${fieldName}"]`);
        if (el) {
          ['input', 'change'].forEach((evt) => {
            el.addEventListener(evt, function() {
              // Debounce the update
              clearTimeout(el._validationTimeout);
              el._validationTimeout = setTimeout(() => {
                updateProgressBar();
              }, 300);
            });
          });
        }
      });
    });

    console.log('Validation system initialized. Required fields:', REQUIRED_FIELDS);
  };

  /**
   * Public API: Add new required field to validation
   */
  window.addRequiredField = function(partial, fieldName, fieldLabel) {
    if (!REQUIRED_FIELDS[partial]) {
      REQUIRED_FIELDS[partial] = {};
    }
    REQUIRED_FIELDS[partial][fieldName] = fieldLabel;

    // Re-initialize listener for this field
    const el = document.querySelector(`[name="${fieldName}"]`);
    if (el) {
      ['input', 'change'].forEach((evt) => {
        el.addEventListener(evt, function() {
          clearTimeout(el._validationTimeout);
          el._validationTimeout = setTimeout(() => {
            updateProgressBar();
          }, 300);
        });
      });
    }

    // Update progress
    updateProgressBar();
    console.log(`Added required field: ${fieldName} (${fieldLabel})`);
  };

  /**
   * Initialize on DOM ready
   */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.initSyllabusValidation);
  } else {
    window.initSyllabusValidation();
  }
})();
