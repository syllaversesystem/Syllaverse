// -----------------------------------------------------------------------------
// File: resources/js/faculty/syllabus-tla-mapping.js
// Description: Handles modal-based many-to-many mapping of TLA rows to ILOs and SOs – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Initial creation – modal open, checkbox sync, and AJAX save for ILO and SO.
// [2025-07-29] Injected mapped ILO and SO codes into table after save.
// [2025-07-29] Deletion of unchecked ILO/SO mappings upon modal save.
// [2025-07-29] Fix for checkbox sync using proper input[value=id] targeting.
// -----------------------------------------------------------------------------

document.addEventListener('DOMContentLoaded', () => {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

  // 🔗 Map ILO Button
  document.querySelectorAll('.map-ilo-btn').forEach(button => {
    button.addEventListener('click', async () => {
      const tlaId = button.dataset.tlaid;
      document.getElementById('mapIloTlaId').value = tlaId;

      document.querySelectorAll('.ilo-checkbox').forEach(cb => cb.checked = false);

      if (tlaId) {
  const base = window.syllabusBasePath || '/faculty/syllabi';
  const res = await fetch(`${base}/tla/${tlaId}/sync-ilo`, {
          method: 'GET',
          headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();

        if (Array.isArray(data.ilos)) {
          data.ilos.forEach(id => {
            const checkbox = document.querySelector(`.ilo-checkbox[value="${id}"]`);
            if (checkbox) checkbox.checked = true;
          });
        }
      }

      new bootstrap.Modal(document.getElementById('mapIloModal')).show();
    });
  });

  // 🔗 Save Mapped ILOs
  document.getElementById('saveMappedIlo')?.addEventListener('click', async () => {
    const tlaId = document.getElementById('mapIloTlaId').value;
    const selected = [...document.querySelectorAll('.ilo-checkbox:checked')].map(cb => cb.value);

    if (!confirm('Do you want to update the ILO mappings? Unchecked items will be removed.')) return;

  await fetch((window.syllabusBasePath || '/faculty/syllabi') + `/tla/${tlaId}/sync-ilo`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ ilo_ids: selected })
    });

    const res = await fetch(`/faculty/syllabi/tla/${tlaId}/sync-ilo`, {
      method: 'GET',
      headers: { 'Accept': 'application/json' }
    });
    const data = await res.json();

    document.querySelectorAll(`.map-ilo-btn[data-tlaid="${tlaId}"]`).forEach(btn => {
      const container = btn.closest('td');
      const display = container.querySelector('.ilo-mapped-codes');
      if (display && data.ilo_codes) display.textContent = data.ilo_codes.join(', ');
    });

    bootstrap.Modal.getInstance(document.getElementById('mapIloModal')).hide();
    alert('✅ ILOs mapped successfully.');
  });

  // 🔗 Map SO Button
  document.querySelectorAll('.map-so-btn').forEach(button => {
    button.addEventListener('click', async () => {
      const tlaId = button.dataset.tlaid;
      document.getElementById('mapSoTlaId').value = tlaId;

      document.querySelectorAll('.so-checkbox').forEach(cb => cb.checked = false);

      if (tlaId) {
        const res = await fetch(`/faculty/syllabi/tla/${tlaId}/sync-so`, {
          method: 'GET',
          headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();

        if (Array.isArray(data.sos)) {
          data.sos.forEach(id => {
            const checkbox = document.querySelector(`.so-checkbox[value="${id}"]`);
            if (checkbox) checkbox.checked = true;
          });
        }
      }

      new bootstrap.Modal(document.getElementById('mapSoModal')).show();
    });
  });

  // 🔗 Save Mapped SOs
  document.getElementById('saveMappedSo')?.addEventListener('click', async () => {
    const tlaId = document.getElementById('mapSoTlaId').value;
    const selected = [...document.querySelectorAll('.so-checkbox:checked')].map(cb => cb.value);

    if (!confirm('Do you want to update the SO mappings? Unchecked items will be removed.')) return;

    await fetch(`/faculty/syllabi/tla/${tlaId}/sync-so`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ so_ids: selected })
    });

  const res = await fetch((window.syllabusBasePath || '/faculty/syllabi') + `/tla/${tlaId}/sync-so`, {
      method: 'GET',
      headers: { 'Accept': 'application/json' }
    });
    const data = await res.json();

    document.querySelectorAll(`.map-so-btn[data-tlaid="${tlaId}"]`).forEach(btn => {
      const container = btn.closest('td');
      const display = container.querySelector('.so-mapped-codes');
      if (display && data.so_codes) display.textContent = data.so_codes.join(', ');
    });

    bootstrap.Modal.getInstance(document.getElementById('mapSoModal')).hide();
    alert('✅ SOs mapped successfully.');
  });
});
