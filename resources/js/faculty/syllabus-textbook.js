// File: resources/js/faculty/syllabus-textbook.js
// Description: Handles AJAX-based upload of textbook file for Faculty syllabi – Syllaverse

document.addEventListener('DOMContentLoaded', () => {
  const uploadInput = document.querySelector('input[name="textbook_file"]');
  const form = document.querySelector('#syllabusForm');
  const syllabusId = form?.action?.split('/').pop(); // Assumes form URL ends in ID
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

  if (!uploadInput || !syllabusId || !csrfToken) return;

  uploadInput.addEventListener('change', async function () {
    const file = this.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('textbook_file', file);

    try {
      const response = await fetch(`/faculty/syllabi/${syllabusId}/textbook`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
        },
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        alert('✅ Textbook uploaded successfully.');
        // Optionally update UI with result.file_url
      } else {
        alert('❌ Upload failed.');
      }
    } catch (error) {
      console.error('Textbook upload error:', error);
      alert('❌ An error occurred while uploading the file.');
    }
  });
});
