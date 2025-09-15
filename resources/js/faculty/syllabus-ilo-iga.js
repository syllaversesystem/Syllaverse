// File: resources/js/faculty/syllabus-ilo-iga.js
// Expose window.saveIloIga() so the top-level Save flow can persist ILO→IGA mapping rows

document.addEventListener('DOMContentLoaded', () => {
  window.saveIloIga = async function() {
    const form = document.getElementById('syllabusForm');
    if (!form) return { message: 'No syllabus form present' };
    // Collect mapping table rows
    const rows = [];
    const mappingRoot = document.querySelector('.ilo-iga-mapping');
    if (!mappingRoot) return { message: 'No IGA mapping partial present' };

    const dataRows = Array.from(mappingRoot.querySelectorAll('tbody tr')).filter(r => r.querySelector('input[name="ilo_iga_ilos_text[]"]'));
    dataRows.forEach((row) => {
      const ilo = row.querySelector('input[name="ilo_iga_ilos_text[]"]')?.value ?? '';
      const igas = [];
      // gather IGA inputs by name pattern
      const igaInputs = Array.from(row.querySelectorAll('input[name^="ilo_iga_iga"]'));
      igaInputs.forEach(ii => igas.push(ii.value ?? ''));
      rows.push({ ilo: ilo, igas: igas });
    });

    // build FormData
    const fd = new FormData();
    fd.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
    fd.append('rows', JSON.stringify(rows));

    // derive syllabus id from form action
    let action = form.action;
    try {
      const resp = await fetch(action.replace(/\/faculty\/syllabi\/(\d+)(.*)/, '/faculty/syllabi/$1/ilo-iga'), {
        method: 'POST',
        credentials: 'same-origin',
        body: fd,
      });
      if (!resp.ok) {
        let data = null;
        try { data = await resp.json(); } catch (e) { /* noop */ }
        throw new Error((data && data.message) ? data.message : ('Server returned ' + resp.status));
      }
      return await resp.json().catch(() => ({ message: 'IloIga saved' }));
    } catch (err) {
      console.error('saveIloIga failed', err);
      throw err;
    }
  };
});
