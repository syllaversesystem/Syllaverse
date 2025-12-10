// File: resources/js/faculty/syllabus-textbook.js
// Description: Upload, delete, and rebuild textbook rows consistently (Syllaverse)

document.addEventListener('DOMContentLoaded', () => {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
  const mainInput = document.getElementById('textbook_main_files');
  const otherInput = document.getElementById('textbook_other_files');
  const referenceInput = document.getElementById('textbook_reference_files');
  const base = window.syllabusBasePath || '/faculty/syllabi';

  if (!csrfToken || typeof syllabusId === 'undefined') return;

  // 🔁 Re-fetch section and rebuild rows
  function refreshSection(type) {
    const inputId = type === 'main' ? 'textbook_main_files' : (type === 'other' ? 'textbook_other_files' : 'textbook_reference_files');
    const label = type === 'main' ? 'Textbook' : (type === 'other' ? 'Other Books and Articles' : 'References');

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
      if (ext === 'pdf') return 'bi-filetype-pdf text-danger';
      if (ext === 'doc' || ext === 'docx') return 'bi-file-earmark-word text-primary';
      return 'bi-file-earmark text-secondary';
    };

    files.forEach((file, index) => {
      const newRow = document.createElement('tr');
      newRow.className = 'textbook-file-row';
      newRow.setAttribute('data-id', file.id);
      newRow.setAttribute('data-type', type);
      const isRef = !!file.is_reference || !file.url;
      const icon = isRef ? 'bi-journal-text text-secondary' : iconFor(file.name);
      const nameCellHtml = isRef
        ? `<span class="textbook-ref-name" title="${(file.name || '').replace(/"/g, '&quot;')}">${file.name}</span>`
        : `<a href="${file.url}" target="_blank" class="textbook-file-link" title="${(file.name || '').replace(/"/g, '&quot;')}">${file.name}</a>`;
      newRow.innerHTML = `
        <td class="text-center align-middle">${index + 1}</td>
        <td class="align-middle">
          <i class="bi ${icon} textbook-file-icon"></i>
          ${nameCellHtml}
        </td>
        <td class="text-end align-middle">
          <button type="button" class="btn btn-outline-secondary btn-sm textbook-edit-btn edit-textbook-btn me-1" title="Rename">
            <i class="bi bi-pencil"></i>
          </button>
          <button type="button" class="btn btn-outline-danger btn-sm textbook-delete-btn delete-textbook-btn" title="Delete">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      `;
      lastRow.insertAdjacentElement('afterend', newRow);
      lastRow = newRow;
    });

    // 🔁 Update rowspan on section label
    const labelCell = inputRow.querySelector('th');
    if (labelCell) {
      labelCell.setAttribute('rowspan', files.length + 1); // Upload row + file rows
    }
  }

  // 📤 Upload files
  function uploadFiles(input, type) {
    const files = Array.from(input.files);
    if (!files.length) return;

    const progressWrap = document.getElementById(type === 'main' ? 'textbook_main_progress' : 'textbook_other_progress');
    const progressBar = progressWrap ? progressWrap.querySelector('.progress-bar') : null;
    const progressLabel = document.getElementById(type === 'main' ? 'textbook_main_progress_label' : 'textbook_other_progress_label');
    const showProgress = (pct, text) => {
      if (!progressWrap || !progressBar) return;
      progressWrap.style.display = '';
      const v = Math.max(0, Math.min(100, Math.round(pct || 0)));
      progressBar.style.width = v + '%';
      progressBar.setAttribute('aria-valuenow', String(v));
      if (progressLabel && text) progressLabel.textContent = text;
    };

    const formData = new FormData();
    files.forEach(file => formData.append('textbook_files[]', file));
    formData.append('type', type);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', `${base}/${syllabusId}/textbook`, true);
    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

    xhr.upload.onprogress = (e) => {
      if (e.lengthComputable) {
        const pct = (e.loaded / e.total) * 100;
        showProgress(pct, `Uploading… ${Math.round(pct)}%`);
      } else {
        showProgress(50, 'Uploading…');
      }
    };

    xhr.onreadystatechange = () => {
      if (xhr.readyState === 4) {
        try {
          const result = JSON.parse(xhr.responseText || '{}');
          if (xhr.status >= 200 && xhr.status < 300 && result.success) {
            showProgress(100, 'Processing…');
            refreshSection(type);
          } else {
            console.error('Upload failed:', result.message || xhr.statusText);
          }
        } catch (err) {
          console.error('Upload failed:', err);
        } finally {
          input.value = '';
          setTimeout(() => { if (progressWrap) progressWrap.style.display = 'none'; }, 600);
        }
      }
    };

    xhr.send(formData);
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
      const link = nameCell.querySelector('a.textbook-file-link');
      const span = nameCell.querySelector('span.textbook-ref-name');
      const currentFull = (link?.textContent || link?.getAttribute('title') || span?.textContent || span?.getAttribute('title') || '').trim();
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
  if (referenceInput) referenceInput.addEventListener('change', () => guardAndUpload(referenceInput, 'reference'));

  // 🧭 Actions: Upload or Add reference (no Bootstrap dependency)
  document.addEventListener('click', (e) => {
    const uploadLink = e.target.closest('.textbook-action-upload');
    if (uploadLink) {
      e.preventDefault();
      const targetId = uploadLink.getAttribute('data-target');
      const input = document.getElementById(targetId);
      if (input) input.click();
      return;
    }

    const refLink = e.target.closest('.textbook-action-reference');
    if (refLink) {
      e.preventDefault();
      const type = refLink.getAttribute('data-type') || 'main';
      const modal = document.getElementById('addReferenceModal');
      const textarea = document.getElementById('addReferenceText');
      const typeInput = document.getElementById('addReferenceType');

      if (modal && textarea && typeInput && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        typeInput.value = type;
        textarea.value = '';
        const m = bootstrap.Modal.getOrCreateInstance(modal);
        m.show();
        setTimeout(() => textarea.focus(), 250);
      } else {
        // Fallback prompt if Bootstrap or modal isn't available
        const text = window.prompt('Add reference (e.g., citation text):');
        if (!text) return;
        fetch(`${base}/${syllabusId}/textbook`, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
          body: JSON.stringify({ type, reference: text })
        })
          .then(res => res.json())
          .then(result => { if (result.success) refreshSection(type); })
          .catch(err => console.error('Add reference failed:', err));
      }
      return;
    }
  });

  // Confirm add reference from modal
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('#confirmAddReference');
    if (!btn) return;
    const textarea = document.getElementById('addReferenceText');
    const typeInput = document.getElementById('addReferenceType');
    const modal = document.getElementById('addReferenceModal');
    const type = (typeInput?.value || 'main');
    const text = (textarea?.value || '').trim();
    if (!text) { alert('Please enter a reference.'); return; }
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving…';
    fetch(`${base}/${syllabusId}/textbook`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
      body: JSON.stringify({ type, reference: text })
    })
      .then(async (res) => {
        let payload = {};
        try { payload = await res.json(); } catch (_) {}
        if (!res.ok || payload.success === false) {
          const msg = payload?.message || 'Failed to save reference.';
          if (window.showAlertOverlay) window.showAlertOverlay('danger', msg);
          throw new Error(msg);
        }
        return payload;
      })
      .then(() => {
        try { const m = bootstrap.Modal.getInstance(modal); if (m) m.hide(); } catch(_){}
        refreshSection(type);
        if (window.showAlertOverlay) window.showAlertOverlay('success', 'Reference added');
      })
      .catch(err => {
        console.error('Add reference failed:', err);
        alert(err.message || 'Failed to add reference.');
      })
      .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
      });
  });

  // Allow Enter key to submit reference when textarea focused
  document.getElementById('addReferenceText')?.addEventListener('keydown', (ev) => {
    if (ev.key === 'Enter' && (ev.ctrlKey || ev.metaKey)) {
      const btn = document.getElementById('confirmAddReference');
      if (btn) btn.click();
    }
  });
});








