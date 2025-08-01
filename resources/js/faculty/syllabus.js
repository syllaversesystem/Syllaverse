// File: resources/js/faculty/syllabus.js
// Description: Modular script to track unsaved changes and confirm navigation for syllabus editing – Syllaverse

// START: Unsaved Changes Tracker
/**
 * Tracks form changes to mark the page as dirty.
 * Prevents accidental loss of data via navigation or refresh.
 *
 * @param {string} formSelector - CSS selector for the target form (default: #syllabusForm)
 */
let isDirty = false;

function trackFormChanges(formSelector = '#syllabusForm') {
  const form = document.querySelector(formSelector);
  if (!form) return;

  const fields = form.querySelectorAll('textarea, input, select');

  fields.forEach(field => {
    field.addEventListener('input', () => (isDirty = true));
    field.addEventListener('change', () => (isDirty = true));
  });

  form.addEventListener('submit', () => {
    isDirty = false;
  });
}
// END: Unsaved Changes Tracker

// START: Exit Confirmation Setup
/**
 * Sets up navigation and tab-exit warnings if unsaved changes are present.
 *
 * @param {string} exitUrlVariable - Name of the global JS variable holding redirect URL
 */
function setupExitConfirmation(exitUrlVariable = 'syllabusExitUrl') {
  const targetUrl = window[exitUrlVariable];

  window.handleExit = function () {
    if (typeof isDirty !== 'undefined' && isDirty) {
      if (confirm("You have unsaved changes. Do you want to leave without saving?")) {
        window.location.href = targetUrl;
      }
    } else {
      window.location.href = targetUrl;
    }
  };

  window.addEventListener('beforeunload', function (e) {
    if (typeof isDirty !== 'undefined' && isDirty) {
      e.preventDefault();
      e.returnValue = '';
    }
  });
}
// END: Exit Confirmation Setup

// START: Init on DOM Ready
document.addEventListener('DOMContentLoaded', () => {
  trackFormChanges();
  setupExitConfirmation(); // uses window.syllabusExitUrl
});
// END: Init on DOM Ready

// Optional exports for testability
export { trackFormChanges, setupExitConfirmation };
