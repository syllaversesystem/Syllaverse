// File: resources/js/faculty/syllabus-ilo-so-cpa.js
// Expose window.saveIloSoCpa() so the top-level Save flow can persist mapping rows

document.addEventListener('DOMContentLoaded', () => {
  window.saveIloSoCpa = async function() {
    const form = document.getElementById('syllabusForm');
    if (!form) return { message: 'No syllabus form present' };
    // Collect mapping table rows
    const rows = [];
    const mappingRoot = document.querySelector('.ilo-so-cpa-mapping');
    if (!mappingRoot) return { message: 'No mapping partial present' };

    const dataRows = Array.from(mappingRoot.querySelectorAll('tbody tr')).filter(r => r.querySelector('input[name="ilo_so_cpa_ilos_text[]"]'));
    dataRows.forEach((row) => {
      const ilo = row.querySelector('input[name="ilo_so_cpa_ilos_text[]"]')?.value ?? '';
      const c = row.querySelector('input[name="ilo_so_cpa_c_text[]"]')?.value ?? '';
      const p = row.querySelector('input[name="ilo_so_cpa_p_text[]"]')?.value ?? '';
      const a = row.querySelector('input[name="ilo_so_cpa_a_text[]"]')?.value ?? '';
  const sos = [];
  // gather SO inputs by name pattern (some rows are rendered without id attributes)
  const soInputs = Array.from(row.querySelectorAll('input[name^="ilo_so_cpa_so"]'));
  soInputs.forEach(si => sos.push(si.value ?? ''));
      rows.push({ ilo: ilo, sos: sos, c: c, p: p, a: a });
    });

    // build FormData
    const fd = new FormData();
    fd.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
    fd.append('rows', JSON.stringify(rows));

    // derive syllabus id from form action
    let action = form.action;
    try {
      const resp = await fetch(action.replace(/\/faculty\/syllabi\/(\d+)(.*)/, '/faculty/syllabi/$1/ilo-so-cpa'), {
        method: 'POST',
        credentials: 'same-origin',
        body: fd,
      });
      if (!resp.ok) {
        let data = null;
        try { data = await resp.json(); } catch (e) { /* noop */ }
        throw new Error((data && data.message) ? data.message : ('Server returned ' + resp.status));
      }
      return await resp.json().catch(() => ({ message: 'IloSoCpa saved' }));
    } catch (err) {
      console.error('saveIloSoCpa failed', err);
      throw err;
    }
  };
});
