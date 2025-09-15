// Expose window.saveIloCdioSdg() so the top-level Save flow can persist ILO→CDIO→SDG mapping rows

document.addEventListener('DOMContentLoaded', function(){
  window.saveIloCdioSdg = async function(){
    var i;
    const form = document.getElementById('syllabusForm');
    if (!form) return { message: 'No syllabus form present' };
    const rows = [];
    const root = document.querySelector('.ilo-sdg-cdio-mapping');
    if (!root) return { message: 'No ILO→CDIO→SDG mapping partial present' };

    // collect each data row (those with the hidden ILO input)
    Array.from(root.querySelectorAll('tbody tr')).filter(r => r.querySelector('input[name="ilo_sdg_cdio_ilos_text[]"]')).forEach((r) => {
      const iloInput = r.querySelector('input[name="ilo_sdg_cdio_ilos_text[]"]');
      const iloText = (iloInput ? (iloInput.value || '') : '');
      const cdios = [];
      const sdgs = [];

      // cdio inputs (ids like ilo_sdg_cdio_cdio1_text)
      Array.from(r.querySelectorAll('input[id^="ilo_sdg_cdio_cdio"][type="text"]')).forEach((inp) => {
        cdios.push(inp.value || '');
      });
      // sdg inputs (ids like ilo_sdg_cdio_sdg1_text)
      Array.from(r.querySelectorAll('input[id^="ilo_sdg_cdio_sdg"][type="text"]')).forEach((inp) => {
        sdgs.push(inp.value || '');
      });

      rows.push({ ilo: iloText, cdios: cdios, sdgs: sdgs });
    });

    try {
      const fd = new FormData();
      fd.append('_token', (document.querySelector('meta[name="csrf-token"]') || { getAttribute: () => '' }).getAttribute('content') || '');
      fd.append('rows', JSON.stringify(rows));
      let action = form.action;
      const resp = await fetch(action.replace(/\/faculty\/syllabi\/(\d+)(.*)/, '/faculty/syllabi/$1/ilo-cdio-sdg'), { method: 'POST', credentials: 'same-origin', body: fd });
      if (!resp.ok) {
        let json = null;
        try { json = await resp.json(); } catch (e) {}
        throw new Error(json && json.message ? json.message : ('Server returned ' + resp.status));
      }
      return await resp.json().catch(() => ({ message: 'IloCdioSdg saved' }));
    } catch (err) {
      console.error('saveIloCdioSdg failed', err);
      throw err;
    }
  };
});
