// File: resources/js/faculty/syllabus.js
// Description: Modular script to track unsaved changes and confirm navigation for syllabus editing – Syllaverse

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

function setupExitConfirmation(exitUrlVariable = 'syllabusExitUrl') {
  const targetUrl = window[exitUrlVariable];

  window.handleExit = function () {
    if (isDirty) {
      if (confirm("You have unsaved changes. Do you want to leave without saving?")) {
        window.location.href = targetUrl;
      }
    } else {
      window.location.href = targetUrl;
    }
  };

  window.addEventListener('beforeunload', function (e) {
    if (isDirty) {
      e.preventDefault();
      e.returnValue = '';
    }
  });
}

// 🟢 Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
  trackFormChanges();
  setupExitConfirmation(); // uses global syllabusExitUrl
});

// Optionally export for testing or re-initialization
export { trackFormChanges, setupExitConfirmation };
