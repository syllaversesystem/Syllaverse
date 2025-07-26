// File: resources/js/faculty/syllabus-textbook.js
// Description: Upload, delete, and rebuild textbook rows consistently (Syllaverse)

document.addEventListener('DOMContentLoaded', () => {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
  const mainInput = document.getElementById('textbook_main_files');
  const otherInput = document.getElementById('textbook_other_files');

  if (!csrfToken || typeof syllabusId === 'undefined') return;

  // 🔁 Re-fetch section and rebuild rows
  function refreshSection(type) {
    const inputId = type === 'main' ? 'textbook_main_files' : 'textbook_other_files';
    const label = type === 'main' ? 'Textbook' : 'Other Books and Articles';

    fetch(`/faculty/syllabi/${syllabusId}/textbook/list?type=${type}`)
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          rebuildSection(type, data.files, inputId, label);
        }
      });
  }

  // ♻️ Fully rebuild section layout after upload/delete
  function rebuildSection(type, files, inputId, label) {
    const inputRow = document.getElementById(inputId)?.closest('tr');
    const tbody = inputRow?.closest('tbody');
    if (!inputRow || !tbody) return;

    // 🧹 Remove all file rows for this type
    const sectionRows = Array.from(tbody.querySelectorAll(`tr[data-type="${type}"]`));
    sectionRows.forEach(row => row.remove());

    // 🧱 Append rows after upload input row
    let lastRow = inputRow;
    files.forEach((file, index) => {
      const newRow = document.createElement('tr');
      newRow.setAttribute('data-id', file.id);
      newRow.setAttribute('data-type', type);
      newRow.innerHTML = `
        <td class="text-center">${index + 1}</td>
        <td>
          <a href="${file.url}" target="_blank">${file.name}</a>
        </td>
        <td>
          <button type="button" class="btn btn-sm btn-outline-danger float-end delete-textbook-btn">🗑️</button>
        </td>
      `;
      lastRow.insertAdjacentElement('afterend', newRow);
      lastRow = newRow;
    });

    // 🔁 Update rowspan on section label
    const labelCell = inputRow.querySelector('td');
    if (labelCell) {
      labelCell.setAttribute('rowspan', files.length + 1); // Upload row + file rows
    }
  }

  // 📤 Upload files
  function uploadFiles(input, type) {
    const files = Array.from(input.files);
    if (!files.length) return;

    const formData = new FormData();
    files.forEach(file => formData.append('textbook_files[]', file));
    formData.append('type', type);

    fetch(`/faculty/syllabi/${syllabusId}/textbook`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrfToken },
      body: formData,
    })
      .then(res => res.json())
      .then(result => {
        if (result.success) {
          refreshSection(type);
        }
        input.value = '';
      })
      .catch(err => console.error('Upload failed:', err));
  }

  // 🗑️ Delete file
  document.addEventListener('click', function (e) {
    if (!e.target.classList.contains('delete-textbook-btn')) return;

    const row = e.target.closest('tr');
    const id = row.dataset.id;
    const type = row.dataset.type;

    fetch(`/faculty/syllabi/textbook/${id}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': csrfToken },
    })
      .then(res => res.json())
      .then(result => {
        if (result.success) {
          refreshSection(type);
        }
      })
      .catch(err => console.error('Delete failed:', err));
  });

  // 📎 Bind input events
  if (mainInput) mainInput.addEventListener('change', () => uploadFiles(mainInput, 'main'));
  if (otherInput) otherInput.addEventListener('change', () => uploadFiles(otherInput, 'other'));
});
