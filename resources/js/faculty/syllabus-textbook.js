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

  const base = window.syllabusBasePath || '/faculty/syllabi';
  fetch(`${base}/${syllabusId}/textbook/list?type=${type}`)
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
    const iconFor = (name) => {
      const m = (name || '').split('.');
      const ext = (m.length ? m[m.length - 1] : '').toLowerCase();
      if (ext === 'pdf') return 'bi-filetype-pdf';
      if (ext === 'doc' || ext === 'docx') return 'bi-file-earmark-word';
      if (ext === 'xls' || ext === 'xlsx' || ext === 'csv') return 'bi-file-earmark-excel';
      if (ext === 'txt') return 'bi-file-earmark-text';
      return 'bi-file-earmark';
    };

    files.forEach((file, index) => {
      const newRow = document.createElement('tr');
      newRow.setAttribute('data-id', file.id);
      newRow.setAttribute('data-type', type);
      newRow.innerHTML = `
        <td class="text-center">${index + 1}</td>
        <td>
          <div class="file-name-wrap">
            <i class="bi ${iconFor(file.name)} file-icon"></i>
            <a href="${file.url}" target="_blank" class="textbook-name file-name" title="${file.name}">${file.name}</a>
            <button type="button" class="btn btn-link btn-sm p-0 ms-1 edit-textbook-btn edit-inline-btn" title="Rename">
              <i class="bi bi-pencil"></i>
            </button>
          </div>
        </td>
        <td class="text-end cis-actions align-middle">
          <button type="button" class="btn btn-sm btn-outline-danger delete-textbook-btn" title="Delete"><i class="bi bi-trash"></i></button>
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

  fetch(`${base}/${syllabusId}/textbook`, {
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
    const btn = e.target.closest('button');
    if (!btn) return;
    const row = btn.closest('tr');
    if (!row) return;
    const id = row.dataset.id;
    const type = row.dataset.type;

    // Delete
    if (btn.classList.contains('delete-textbook-btn')) {
  fetch((window.syllabusBasePath || '/faculty/syllabi') + `/textbook/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken },
      })
        .then(res => res.json())
        .then(result => { if (result.success) refreshSection(type); })
        .catch(err => console.error('Delete failed:', err));
      return;
    }

    // Inline rename
    if (btn.classList.includes && btn.classList.includes('edit-textbook-btn') || btn.classList.contains('edit-textbook-btn')) {
      const nameCell = row.querySelector('td:nth-child(2)') || row.children[1];
      const link = nameCell.querySelector('a.textbook-name');
      const currentFull = (link?.textContent || link?.getAttribute('title') || '').trim();
      const lastDot = currentFull.lastIndexOf('.');
      const currentExt = lastDot > 0 ? currentFull.slice(lastDot + 1) : '';
      const currentBase = lastDot > 0 ? currentFull.slice(0, lastDot) : currentFull;

      const editor = document.createElement('div');
      editor.className = 'd-flex align-items-center gap-2';
      editor.innerHTML = `
        <input type="text" class="form-control form-control-sm" value="${currentBase.replace(/"/g, '&quot;')}">
        <div class="btn-group btn-group-sm">
          <button type="button" class="btn btn-primary save-textbook-name">Save</button>
          <button type="button" class="btn btn-outline-secondary cancel-textbook-name">Cancel</button>
        </div>
        ${currentExt ? `<small class="text-muted ms-2">.${currentExt}</small>` : ''}
      `;

      nameCell.dataset.originalHtml = nameCell.innerHTML;
      nameCell.innerHTML = '';
      nameCell.appendChild(editor);

      const input = editor.querySelector('input');
      input.focus();

      editor.querySelector('.save-textbook-name').addEventListener('click', () => {
        const newBase = input.value.trim();
        if (!newBase) return;
  fetch((window.syllabusBasePath || '/faculty/syllabi') + `/textbook/${id}`, {
          method: 'PUT',
          headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
          // Send base name only; server preserves extension
          body: JSON.stringify({ name: newBase }),
        })
          .then(res => res.json())
          .then(result => {
            if (result.success) {
              refreshSection(type);
            }
          })
          .catch(err => console.error('Update failed:', err));
      });

      editor.querySelector('.cancel-textbook-name').addEventListener('click', () => {
        nameCell.innerHTML = nameCell.dataset.originalHtml || '';
      });
    }
  });

  // 📎 Bind input events
  const maxBytes = 300 * 1024 * 1024; // 300 MB client-side hint only
  const guardAndUpload = (input, type) => {
    const tooBig = Array.from(input.files).find(f => f.size > maxBytes);
    if (tooBig) {
      alert(`File exceeds 300MB: ${tooBig.name}`);
      input.value = '';
      return;
    }
    uploadFiles(input, type);
  };
  if (mainInput) mainInput.addEventListener('change', () => guardAndUpload(mainInput, 'main'));
  if (otherInput) otherInput.addEventListener('change', () => guardAndUpload(otherInput, 'other'));
});








