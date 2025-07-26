// File: resources/js/faculty/syllabus-textbook.js
// Description: Handles AJAX-based upload and deletion of textbook files for Syllaverse

document.addEventListener('DOMContentLoaded', () => {
  const input = document.querySelector('#textbook_files');
  const list = document.querySelector('#uploadedTextbookList');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

  // ✅ Ensure global syllabusId is defined in Blade
  if (!input || !list || !csrfToken || typeof syllabusId === 'undefined') return;

  // 📤 Upload Handler
  input.addEventListener('change', async function () {
    const files = Array.from(this.files);
    if (files.length === 0) return;

    const formData = new FormData();
    files.forEach(file => formData.append('textbook_files[]', file));

    try {
      const response = await fetch(`/faculty/syllabi/${syllabusId}/textbook`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        body: formData,
      });

      const result = await response.json();

      if (result.success && Array.isArray(result.files)) {
        result.files.forEach(file => {
          const li = document.createElement('li');
          li.className = 'mb-2 d-flex align-items-center justify-content-between';
          li.setAttribute('data-id', file.id);
          li.innerHTML = `
            <a href="${file.url}" target="_blank">${file.name}</a>
            <button type="button" class="btn btn-sm btn-outline-danger ms-2 delete-textbook-btn">🗑️</button>
          `;
          list.appendChild(li);
        });

        showToast('✅ Files uploaded successfully.');
        input.value = ''; // ✅ Fix: allow re-uploading the same file after deletion
      } else {
        showToast('❌ Upload failed.', true);
      }
    } catch (error) {
      console.error('Upload error:', error);
      showToast('❌ Error during upload.', true);
    }
  });

  // 🗑️ Delete Handler
  list.addEventListener('click', async function (e) {
    const deleteBtn = e.target.closest('.delete-textbook-btn');
    if (!deleteBtn) return;

    const li = deleteBtn.closest('li');
    const textbookId = li?.getAttribute('data-id');

    if (!textbookId || !confirm('Are you sure you want to delete this file?')) return;

    try {
      const response = await fetch(`/faculty/syllabi/textbook/${textbookId}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
        },
      });

      const result = await response.json();

      if (result.success) {
        li.style.transition = 'opacity 0.3s ease';
        li.style.opacity = '0';
        setTimeout(() => li.remove(), 300);
        showToast('🗑️ File deleted successfully.');
      } else {
        showToast('❌ Failed to delete file.', true);
      }
    } catch (error) {
      console.error('Delete error:', error);
      showToast('❌ Error during deletion.', true);
    }
  });

  // ✅ Toast helper
  function showToast(message, isError = false) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${isError ? 'danger' : 'success'} alert-dismissible fade show position-fixed top-0 end-0 m-4`;
    toast.role = 'alert';
    toast.style.zIndex = 1055;
    toast.innerHTML = `
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
  }
});
