/*
 * File: resources/js/faculty/ai/snapshot.js
 * Description: Snapshot extraction for syllabus partials + modal viewer.
 * This module will grow partial-by-partial. Starts with Mission & Vision.
 */
(function(){
  'use strict';

  // ---------- Utilities ----------
  function textTrim(val){ return String(val == null ? '' : val).replace(/\s+/g,' ').trim(); }
  function escHtml(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function getVal(name){
    const el = document.querySelector(`[name="${name}"]`);
    if (!el) return '';
    return textTrim(el.value ?? el.textContent ?? '');
  }
  // Format value to show newlines as " \n " (with spaces)
  function formatWithNewlines(val) {
    if (!val) return '';
    return String(val).replace(/\n/g, ' \\n ').trim();
  }

  // ---------- Course Policies Snapshot ----------
  function snapshotCoursePolicies(){
    const root = document.querySelector('.sv-partial[data-partial-key="course-policies"]')
      || document.querySelector('table.course-policies')?.closest('.sv-partial')
      || document.querySelector('table.course-policies');

    const raw = { grading: { description: '', note: '', rows: [] }, policies: [] };
    const md = [];

    if (!root) {
      return { key: 'course_policies', markdown: '## Course Policies\n\n_No course policies section found._', raw };
    }

    const inner = root.querySelector('table.cis-inner');
    if (inner) {
      const descEl = inner.querySelector('.grade-desc-left');
      const noteEl = inner.querySelector('.note-left');
      raw.grading.description = textTrim(descEl?.textContent || '');
      raw.grading.note = textTrim(noteEl?.textContent || '');

      const gradeRows = Array.from(inner.querySelectorAll('tr'));
      gradeRows.forEach(tr => {
        const firstCell = tr.querySelector('td.grade-label-noleft');
        if (!firstCell) return;
        const tds = tr.querySelectorAll('td');
        const label = textTrim(tds[0]?.textContent || '');
        const grade = textTrim(tds[1]?.textContent || '');
        const range = textTrim(tds[2]?.textContent || '');
        if (label || grade || range) {
          raw.grading.rows.push({ label, grade, range });
        }
      });
    }

    const sectionNames = [
      'Class policy',
      'Missed examinations',
      'Academic dishonesty',
      'Dropping',
      'Other course policies and requirements'
    ];
    const textareas = Array.from(root.querySelectorAll('textarea[name="course_policies[]"]'));
    textareas.forEach((ta, idx) => {
      const content = textTrim(ta?.value ?? ta?.textContent ?? '');
      if (content) {
        raw.policies.push({ section: sectionNames[idx] || `Section ${idx+1}`, content });
      }
    });

    md.push('## Course Policies');
    if (raw.grading.description || raw.grading.rows.length) {
      md.push('');
      md.push('### Grading System');
      if (raw.grading.description) md.push(raw.grading.description);
      if (raw.grading.rows.length) {
        md.push('');
        md.push('| Category | Grade | Range |');
        md.push('|---|---|---|');
        raw.grading.rows.forEach(r => {
          md.push(`| ${r.label || ''} | ${r.grade || ''} | ${r.range || ''} |`);
        });
      }
      if (raw.grading.note) {
        md.push('');
        md.push(`Note: ${raw.grading.note}`);
      }
    }

    if (raw.policies.length) {
      md.push('');
      md.push('### Policy Sections');
      md.push('');
      md.push('| Section | Content |');
      md.push('|---|---|');
      raw.policies.forEach(p => {
        md.push(`| ${p.section} | ${p.content.replaceAll('|','\\|')} |`);
      });
    }

    return {
      key: 'course_policies',
      markdown: md.join('\n'),
      raw,
    };
  }

  // ---------- Mission & Vision Snapshot ----------
  function snapshotMissionVision(){
    // Prefer inputs with known names/ids, fallback to content under mission/vision partials
    const vEl = document.getElementById('vision-text') || document.querySelector('[name="vision"]');
    const mEl = document.getElementById('mission-text') || document.querySelector('[name="mission"]');
    const vision = textTrim(vEl ? (vEl.value ?? vEl.textContent) : '');
    const mission = textTrim(mEl ? (mEl.value ?? mEl.textContent) : '');

    // Build concise markdown table for consistency
    const md = [];
    md.push('### Mission & Vision');
    md.push('| Label | Text |');
    md.push('|:--|:--|');
    md.push(`| Vision | ${vision || '-'} |`);
    md.push(`| Mission | ${mission || '-'} |`);

    return {
      key: 'mission_vision',
      markdown: md.join('\n'),
      raw: { vision, mission }
    };
  }

  // ---------- Course Information Snapshot ----------
  function snapshotCourseInfo(){
    const fields = [
      ['Course Title','course_title'],
      ['Course Code','course_code'],
      ['Course Category','course_category'],
      ['Pre-requisite(s)','course_prerequisites'],
      ['Semester','semester'],
      ['Year Level','year_level'],
      ['Credit Hours','credit_hours_text'],
      ['Instructor Name','instructor_name'],
      ['Employee No.','employee_code'],
      ['Reference CMO','reference_cmo'],
      ['Instructor Designation','instructor_designation'],
      ['Date Prepared','date_prepared'],
      ['Instructor Email','instructor_email'],
      ['Revision No.','revision_no'],
      ['Period of Study','academic_year'],
      ['Revision Date','revision_date'],
      ['Course Rationale and Description','course_description'],
      ['Contact Hours','contact_hours'],
    ];

    const md = [];
    md.push('### Course Information');
    md.push('| Field | Value |');
    md.push('|:--|:--|');
    const raw = {};
    fields.forEach(([label, name]) => {
      const val = getVal(name) || '-';
      raw[name] = val;
      md.push(`| ${label} | ${val} |`);
    });

    return {
      key: 'course_info',
      markdown: md.join('\n'),
      raw,
    };
  }

  // ---------- Criteria for Assessment Snapshot ----------
  function snapshotCriteriaAssessment(){
    const criteriaDataEl = document.getElementById('criteria_data_input');
    let sections = [];
    
    if (criteriaDataEl && criteriaDataEl.value) {
      try {
        const parsed = JSON.parse(criteriaDataEl.value);
        sections = Array.isArray(parsed) ? parsed : [];
      } catch(e) {
        console.warn('[snapshot] Failed to parse criteria data', e);
      }
    }

    const md = [];
    md.push('### Criteria for Assessment');
    md.push('| Category | Assessment | Percent |');
    md.push('|:--|:--|:--|');
    
    const raw = { sections: [] };
    
    if (sections.length === 0) {
      md.push('| - | No criteria defined | - |');
    } else {
      sections.forEach(section => {
        const key = section.key || '';
        const heading = section.heading || '-';
        const values = Array.isArray(section.value) ? section.value : [];
        
        raw.sections.push({
          key,
          heading,
          values
        });
        
        if (values.length === 0) {
          md.push(`| ${heading} | - | - |`);
        } else {
          values.forEach((item) => {
            const desc = item.description || '-';
            const pct = item.percent || '-';
            md.push(`| ${heading} | ${desc} | ${pct} |`);
          });
        }
      });
    }

    return {
      key: 'criteria_assessment',
      markdown: md.join('\n'),
      raw,
    };
  }

  // ---------- Teaching, Learning, and Assessment Strategies Snapshot ----------
  function snapshotTLAS(){
    const tlasEl = document.getElementById('tla_strategies') || document.querySelector('[name="tla_strategies"]');
    const tlas = tlasEl ? textTrim(tlasEl.value ?? tlasEl.textContent ?? '') : '';

    const md = [];
    md.push('### Teaching, Learning, and Assessment Strategies');
    md.push('');
    md.push(tlas || '-');

    return {
      key: 'tlas',
      markdown: md.join('\n'),
      raw: { tlas }
    };
  }

  // ---------- Teaching, Learning, and Assessment (TLA) Activities Snapshot ----------
  function snapshotTLA(){
    const tlaTable = document.getElementById('tlaTable');
    const raw = { rows: [] };
    const md = [];

    if (!tlaTable) {
      return { key: 'tla_activities', markdown: '### Teaching, Learning, and Assessment (TLA) Activities\n\n_No TLA activities defined._', raw };
    }

    const tbody = tlaTable.querySelector('tbody');
    if (!tbody) {
      return { key: 'tla_activities', markdown: '### Teaching, Learning, and Assessment (TLA) Activities\n\n_No TLA activities defined._', raw };
    }

    // Helper to preserve newlines as \n literals in values with spaces around them
    function preserveNewlines(val) {
      const str = String(val == null ? '' : val).trim();
      if (!str) return '';
      // Replace actual newlines with literal \n string with spaces before and after, collapse other whitespace
      return str.replace(/\n/g, ' \\n ').replace(/\s+/g, ' ').trim();
    }

    const rows = Array.from(tbody.querySelectorAll('tr:not(#tla-placeholder)'));

    rows.forEach(row => {
      const chInput = row.querySelector('[name*="[ch]"]');
      const topicInput = row.querySelector('[name*="[topic]"]');
      const wksInput = row.querySelector('[name*="[wks]"]');
      const outcomesInput = row.querySelector('[name*="[outcomes]"]');
      const iloInput = row.querySelector('[name*="[ilo]"]');
      const soInput = row.querySelector('[name*="[so]"]');
      const deliveryInput = row.querySelector('[name*="[delivery]"]');

      const ch = preserveNewlines(chInput?.value ?? '');
      const topic = preserveNewlines(topicInput?.value ?? '');
      const wks = preserveNewlines(wksInput?.value ?? '');
      const outcomes = preserveNewlines(outcomesInput?.value ?? '');
      const ilo = preserveNewlines(iloInput?.value ?? '');
      const so = preserveNewlines(soInput?.value ?? '');
      const delivery = preserveNewlines(deliveryInput?.value ?? '');

      if (ch || topic || wks || outcomes || ilo || so || delivery) {
        raw.rows.push({ ch, topic, wks, outcomes, ilo, so, delivery });
      }
    });

    md.push('### Teaching, Learning, and Assessment (TLA) Activities');
    md.push('| Ch. | Topics / Reading List | Wks. | Topic Outcomes | ILO | SO | Delivery Method |');
    md.push('|:---:|:---|:---:|:---|:---:|:---:|:---|');

    if (raw.rows.length === 0) {
      md.push('| - | No TLA activities defined | - | - | - | - | - |');
    } else {
      raw.rows.forEach(row => {
        md.push(`| ${row.ch || '-'} | ${row.topic || '-'} | ${row.wks || '-'} | ${row.outcomes || '-'} | ${row.ilo || '-'} | ${row.so || '-'} | ${row.delivery || '-'} |`);
      });
    }

    return {
      key: 'tla_activities',
      markdown: md.join('\n'),
      raw,
    };
  }

  // ---------- Intended Learning Outcomes (ILO) Snapshot ----------
  function snapshotILO(){
    const listEl = document.getElementById('syllabus-ilo-sortable');
    let ilos = [];
    
    if (listEl) {
      const rows = Array.from(listEl.querySelectorAll('tr'));
      ilos = rows
        .map((row, idx) => {
          const badge = row.querySelector('.ilo-badge');
          const ta = row.querySelector('textarea[name="ilos[]"]');
          const code = badge ? textTrim(badge.textContent ?? '') : `ILO${idx + 1}`;
          const description = ta ? textTrim(ta.value ?? '') : '';
          return { code, description };
        })
        .filter(ilo => ilo.description !== ''); // exclude empty ILOs
    }

    const md = [];
    md.push('### Intended Learning Outcomes (ILO)');
    md.push('| Code | Description |');
    md.push('|:--|:--|');
    
    const raw = { ilos: [] };
    
    if (ilos.length === 0) {
      md.push('| - | No ILOs defined | - |');
    } else {
      ilos.forEach(ilo => {
        md.push(`| ${ilo.code} | ${ilo.description} |`);
        raw.ilos.push(ilo);
      });
    }

    return {
      key: 'ilo',
      markdown: md.join('\n'),
      raw,
    };
  }

  // ---------- Student Outcomes (SO) Snapshot ----------
  function snapshotSO(){
    const listEl = document.getElementById('syllabus-so-sortable');
    let sos = [];
    
    if (listEl) {
      const rows = Array.from(listEl.querySelectorAll('tr'));
      sos = rows
        .map((row, idx) => {
          const badge = row.querySelector('.so-badge');
          const titleTa = row.querySelector('textarea[name="so_titles[]"]');
          const descTa = row.querySelector('textarea[name="sos[]"]');
          const code = badge ? textTrim(badge.textContent ?? '') : `SO${idx + 1}`;
          const title = titleTa ? textTrim(titleTa.value ?? '') : '';
          const description = descTa ? textTrim(descTa.value ?? '') : '';
          return { code, title, description };
        })
        .filter(so => so.title !== '' || so.description !== '');
    }

    const md = [];
    md.push('### Student Outcomes (SO)');
    md.push('| Code | Title | Description |');
    md.push('|:--|:--|:--|');
    
    const raw = { sos: [] };
    
    if (sos.length === 0) {
      md.push('| - | No SOs defined | - |');
    } else {
      sos.forEach(so => {
        md.push(`| ${so.code} | ${so.title || '-'} | ${so.description || '-'} |`);
        raw.sos.push(so);
      });
    }

    return {
      key: 'so',
      markdown: md.join('\n'),
      raw,
    };
  }

  // ---------- CDIO Framework Skills Snapshot ----------
  function snapshotCDIO(){
    const listEl = document.getElementById('syllabus-cdio-sortable');
    let cdios = [];
    
    if (listEl) {
      const rows = Array.from(listEl.querySelectorAll('tr'));
      cdios = rows
        .map((row, idx) => {
          const badge = row.querySelector('.cdio-badge');
          const titleTa = row.querySelector('textarea[name="cdio_titles[]"]');
          const descTa = row.querySelector('textarea[name="cdios[]"]');
          const code = badge ? textTrim(badge.textContent ?? '') : `CDIO${idx + 1}`;
          const title = titleTa ? textTrim(titleTa.value ?? '') : '';
          const description = descTa ? textTrim(descTa.value ?? '') : '';
          return { code, title, description };
        })
        .filter(cdio => cdio.title !== '' || cdio.description !== '');
    }

    const md = [];
    md.push('### CDIO Framework Skills (CDIO)');
    md.push('| Code | Title | Description |');
    md.push('|:--|:--|:--|');
    
    const raw = { cdios: [] };
    
    if (cdios.length === 0) {
      md.push('| - | No CDIOs defined | - |');
    } else {
      cdios.forEach(cdio => {
        md.push(`| ${cdio.code} | ${cdio.title || '-'} | ${cdio.description || '-'} |`);
        raw.cdios.push(cdio);
      });
    }

    return {
      key: 'cdio',
      markdown: md.join('\n'),
      raw,
    };
  }

  // ---------- Sustainable Development Goals (SDG) Snapshot ----------
  function snapshotSDG(){
    const listEl = document.getElementById('syllabus-sdg-sortable');
    let sdgs = [];
    
    if (listEl) {
      const rows = Array.from(listEl.querySelectorAll('tr'));
      sdgs = rows
        .map((row, idx) => {
          const badge = row.querySelector('.sdg-badge');
          const titleTa = row.querySelector('textarea[name="sdg_titles[]"]');
          const descTa = row.querySelector('textarea[name="sdgs[]"]');
          const code = badge ? textTrim(badge.textContent ?? '') : `SDG${idx + 1}`;
          const title = titleTa ? textTrim(titleTa.value ?? '') : '';
          const description = descTa ? textTrim(descTa.value ?? '') : '';
          return { code, title, description };
        })
        .filter(sdg => sdg.title !== '' || sdg.description !== '');
    }

    const md = [];
    md.push('### Sustainable Development Goals (SDG)');
    md.push('| Code | Title | Description |');
    md.push('|:--|:--|:--|');
    
    const raw = { sdgs: [] };
    
    if (sdgs.length === 0) {
      md.push('| - | No SDGs defined | - |');
    } else {
      sdgs.forEach(sdg => {
        md.push(`| ${sdg.code} | ${sdg.title || '-'} | ${sdg.description || '-'} |`);
        raw.sdgs.push(sdg);
      });
    }

    return {
      key: 'sdg',
      markdown: md.join('\n'),
      raw,
    };
  }

  // ---------- Institutional Graduate Attributes (IGA) Snapshot ----------
  function snapshotIGA(){
    const listEl = document.getElementById('syllabus-iga-sortable');
    let igas = [];
    
    if (listEl) {
      const rows = Array.from(listEl.querySelectorAll('tr.iga-row'));
      igas = rows
        .map((row, idx) => {
          const badge = row.querySelector('.iga-badge');
          const titleTa = row.querySelector('textarea[name="iga_titles[]"]');
          const descTa = row.querySelector('textarea[name="igas[]"]');
          const code = badge ? textTrim(badge.textContent ?? '') : `IGA${idx + 1}`;
          const title = titleTa ? textTrim(titleTa.value ?? '') : '';
          const description = descTa ? textTrim(descTa.value ?? '') : '';
          return { code, title, description };
        })
        .filter(iga => iga.title !== '' || iga.description !== ''); // exclude empty IGAs
    }

    const md = [];
    md.push('### Institutional Graduate Attributes (IGA)');
    md.push('| Code | Title | Description |');
    md.push('|:--|:--|:--|');
    
    const raw = { igas: [] };
    
    if (igas.length === 0) {
      md.push('| - | No IGAs defined | - |');
    } else {
      igas.forEach(iga => {
        md.push(`| ${iga.code} | ${iga.title || '-'} | ${iga.description || '-'} |`);
        raw.igas.push(iga);
      });
    }

    return {
      key: 'iga',
      markdown: md.join('\n'),
      raw,
    };
  }

  // ---------- Assessment Schedule Snapshot ----------
  function snapshotAssessmentSchedule(){
    const mapping = document.querySelector('.assessment-mapping');
    const raw = { assessments: [] };
    const md = [];

    if (!mapping) {
      return { key: 'assessment_schedule', markdown: '### Assessment Schedule\n\n_No assessment schedule defined._', raw };
    }

    const distTable = mapping.querySelector('table.distribution');
    const weekTable = mapping.querySelector('table.week');

    if (!distTable || !weekTable) {
      return { key: 'assessment_schedule', markdown: '### Assessment Schedule\n\n_No assessment schedule defined._', raw };
    }

    // Get week headers (skip placeholder "No weeks")
    const weekHeaders = Array.from(weekTable.querySelectorAll('tr:first-child th.week-number'));
    const weekLabels = weekHeaders
      .map(th => textTrim(th.textContent || ''))
      .filter(label => label && label !== 'No weeks');

    // Get distribution rows
    const distRows = Array.from(distTable.querySelectorAll('tr:not(:first-child)'));
    const weekRows = Array.from(weekTable.querySelectorAll('tr:not(:first-child)'));

    distRows.forEach((distRow, idx) => {
      const input = distRow.querySelector('input.distribution-input');
      const name = textTrim(input?.value ?? '');

      // Get week marks from corresponding week row
      const weekRow = weekRows[idx];
      const weekCells = weekRow ? Array.from(weekRow.querySelectorAll('td.week-mapping')) : [];
      const weekMarks = {};

      weekCells.forEach((cell, cellIdx) => {
        if (cellIdx < weekLabels.length) {
          const mark = textTrim(cell.textContent ?? '');
          weekMarks[weekLabels[cellIdx]] = mark;
        }
      });

      if (name || Object.keys(weekMarks).length > 0) {
        raw.assessments.push({ name: name || '-', marks: weekMarks });
      }
    });

    md.push('### Assessment Schedule');
    
    if (raw.assessments.length === 0) {
      md.push('| Assessment Method | (No weeks scheduled) |');
      md.push('|---|---|');
    } else if (weekLabels.length === 0) {
      md.push('| Assessment Method |');
      md.push('|---|');
      raw.assessments.forEach(a => {
        md.push(`| ${a.name} |`);
      });
    } else {
      // Build header with week labels (add "Week" prefix)
      const headerCols = ['Assessment Method', ...weekLabels.map(wk => `Week ${wk}`)];
      md.push('| ' + headerCols.join(' | ') + ' |');
      md.push('|' + Array(headerCols.length).fill(':--').join('|') + '|');

      raw.assessments.forEach(a => {
        const row = [a.name];
        weekLabels.forEach(wk => {
          row.push(a.marks[wk] || '-');
        });
        md.push('| ' + row.join(' | ') + ' |');
      });
    }

    return {
      key: 'assessment_schedule',
      markdown: md.join('\n'),
      raw,
    };
  }

  // ---------- ILO-SO-CPA Mapping Snapshot ----------
  function snapshotIloSoCpa(){
    const root = document.querySelector('.ilo-so-cpa-mapping');
    const raw = { so_columns: [], mappings: [] };
    const md = [];

    if (!root) {
      return { key: 'ilo_so_cpa_mapping', markdown: '### ILO-SO-CPA Mapping\n\n_No ILO-SO-CPA mapping defined._', raw };
    }

    // Get SO columns from header row
    const mappingTable = root.querySelector('.mapping');
    if (!mappingTable) {
      return { key: 'ilo_so_cpa_mapping', markdown: '### ILO-SO-CPA Mapping\n\n_No ILO-SO-CPA mapping defined._', raw };
    }

    const headerRow2 = mappingTable.querySelectorAll('tr')[1];
    const allHeaders = Array.from(headerRow2.querySelectorAll('th'));
    const iloHeaderIndex = allHeaders.findIndex(th => th.textContent.includes('ILOs'));
    const cHeaderIndex = allHeaders.findIndex(th => th.textContent.trim() === 'C');

    // SO columns are between ILOs and C
    const soHeaders = allHeaders.slice(iloHeaderIndex + 1, cHeaderIndex);
    soHeaders.forEach(th => {
      const input = th.querySelector('input');
      const label = input ? textTrim(input.value) : textTrim(th.textContent);
      if (label && label !== 'No SO') {
        raw.so_columns.push(label);
      }
    });

    // Get ILO rows
    const tbody = mappingTable.querySelector('tbody') || mappingTable;
    const dataRows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.querySelector('td'));

    dataRows.forEach((row, idx) => {
      const cells = Array.from(row.querySelectorAll('td'));
      const iloCell = cells[0];
      const iloInput = iloCell.querySelector('input');
      const iloText = iloInput ? textTrim(iloInput.value) : textTrim(iloCell.textContent);

      // Skip placeholder rows
      if (iloText === 'No ILO') return;

      // Collect SO values
      const sos = {};
      soHeaders.forEach((header, soIdx) => {
        const input = header.querySelector('input');
        const soLabel = input ? textTrim(input.value) : textTrim(header.textContent);

        // Skip "No SO" placeholder but collect actual SO values
        if (soLabel === 'No SO') return;

        const soCell = cells[soIdx + 1];
        const soValue = soCell ? formatWithNewlines(soCell.querySelector('textarea')?.value ?? soCell.textContent ?? '') : '';
        sos[soLabel] = soValue;
      });

      // Get C, P, A values (last 3 cells)
      const cCell = cells[cells.length - 3];
      const pCell = cells[cells.length - 2];
      const aCell = cells[cells.length - 1];

      const cValue = formatWithNewlines(cCell?.querySelector('textarea')?.value ?? cCell?.textContent ?? '');
      const pValue = formatWithNewlines(pCell?.querySelector('textarea')?.value ?? pCell?.textContent ?? '');
      const aValue = formatWithNewlines(aCell?.querySelector('textarea')?.value ?? aCell?.textContent ?? '');

      if (iloText) {
        raw.mappings.push({
          ilo: iloText,
          sos,
          c: cValue,
          p: pValue,
          a: aValue
        });
      }
    });

    // Build markdown table
    md.push('### ILO-SO-CPA Mapping');

    if (raw.mappings.length === 0) {
      md.push('| ILO | (No data) |');
      md.push('|---|---|');
    } else {
      // Build header: ILO | SO1 | SO2 | ... | C | P | A
      const headerCols = ['ILO', ...raw.so_columns, 'C', 'P', 'A'];
      md.push('| ' + headerCols.join(' | ') + ' |');
      md.push('|' + Array(headerCols.length).fill(':--').join('|') + '|');

      // Build rows
      raw.mappings.forEach(mapping => {
        const row = [mapping.ilo];

        // Add SO values in order of columns
        raw.so_columns.forEach(soCol => {
          row.push(mapping.sos[soCol] || '-');
        });

        // Add C, P, A
        row.push(mapping.c || '-');
        row.push(mapping.p || '-');
        row.push(mapping.a || '-');

        md.push('| ' + row.join(' | ') + ' |');
      });
    }

    return {
      key: 'ilo_so_cpa_mapping',
      markdown: md.join('\n'),
      raw,
    };
  }

  // ---------- ILO-IGA Mapping Snapshot ----------
  function snapshotIloIga(){
    const root = document.querySelector('.ilo-iga-mapping');
    const raw = { iga_columns: [], mappings: [] };
    const md = [];

    if (!root) {
      return { key: 'ilo_iga_mapping', markdown: '### ILO-IGA Mapping\n\n_No ILO-IGA mapping defined._', raw };
    }

    // Get IGA columns from header row
    const mappingTable = root.querySelector('.mapping');
    if (!mappingTable) {
      return { key: 'ilo_iga_mapping', markdown: '### ILO-IGA Mapping\n\n_No ILO-IGA mapping defined._', raw };
    }

    const headerRow2 = mappingTable.querySelectorAll('tr')[1];
    const allHeaders = Array.from(headerRow2.querySelectorAll('th'));
    const iloHeaderIndex = allHeaders.findIndex(th => th.textContent.includes('ILOs'));

    // IGA columns are after ILOs (all remaining headers)
    const igaHeaders = allHeaders.slice(iloHeaderIndex + 1);
    igaHeaders.forEach(th => {
      const input = th.querySelector('input');
      const label = input ? textTrim(input.value) : textTrim(th.textContent);
      if (label && label !== 'No IGA') {
        raw.iga_columns.push(label);
      }
    });

    // Get ILO rows
    const tbody = mappingTable.querySelector('tbody') || mappingTable;
    const dataRows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.querySelector('td'));

    dataRows.forEach((row, idx) => {
      const cells = Array.from(row.querySelectorAll('td'));
      const iloCell = cells[0];
      const iloInput = iloCell.querySelector('input');
      const iloText = iloInput ? textTrim(iloInput.value) : textTrim(iloCell.textContent);

      // Skip placeholder rows
      if (iloText === 'No ILO') return;

      // Collect IGA values
      const igas = {};
      igaHeaders.forEach((header, igaIdx) => {
        const input = header.querySelector('input');
        const igaLabel = input ? textTrim(input.value) : textTrim(header.textContent);

        // Skip "No IGA" placeholder but collect actual IGA values
        if (igaLabel === 'No IGA') return;

        const igaCell = cells[igaIdx + 1];
        const igaValue = igaCell ? formatWithNewlines(igaCell.querySelector('textarea')?.value ?? igaCell.textContent ?? '') : '';
        igas[igaLabel] = igaValue;
      });

      if (iloText) {
        raw.mappings.push({
          ilo: iloText,
          igas
        });
      }
    });

    // Build markdown table
    md.push('### ILO-IGA Mapping');

    if (raw.mappings.length === 0) {
      md.push('| ILO | (No data) |');
      md.push('|---|---|');
    } else {
      // Build header: ILO | IGA1 | IGA2 | ...
      const headerCols = ['ILO', ...raw.iga_columns];
      md.push('| ' + headerCols.join(' | ') + ' |');
      md.push('|' + Array(headerCols.length).fill(':--').join('|') + '|');

      // Build rows
      raw.mappings.forEach(mapping => {
        const row = [mapping.ilo];

        // Add IGA values in order of columns
        raw.iga_columns.forEach(igaCol => {
          row.push(mapping.igas[igaCol] || '-');
        });

        md.push('| ' + row.join(' | ') + ' |');
      });
    }

    return {
      key: 'ilo_iga_mapping',
      markdown: md.join('\n'),
      raw,
    };
  }

  // ---------- ILO-CDIO-SDG Mapping Snapshot ----------
  function snapshotIloCdioSdg(){
    const root = document.querySelector('.ilo-cdio-sdg-mapping');
    const raw = { cdio_columns: [], sdg_columns: [], mappings: [] };
    const md = [];

    if (!root) {
      return { key: 'ilo_cdio_sdg_mapping', markdown: '### ILO-CDIO-SDG Mapping\n\n_No ILO-CDIO-SDG mapping defined._', raw };
    }

    // Get mapping table and header rows
    const mappingTable = root.querySelector('.mapping');
    if (!mappingTable) {
      return { key: 'ilo_cdio_sdg_mapping', markdown: '### ILO-CDIO-SDG Mapping\n\n_No ILO-CDIO-SDG mapping defined._', raw };
    }

    const headerRow1 = mappingTable.querySelectorAll('tr')[0];
    const headerRow2 = mappingTable.querySelectorAll('tr')[1];
    
    if (!headerRow1 || !headerRow2) {
      return { key: 'ilo_cdio_sdg_mapping', markdown: '### ILO-CDIO-SDG Mapping\n\n_No ILO-CDIO-SDG mapping defined._', raw };
    }

    // Get CDIO and SDG column headers from row 2
    const allHeaders = Array.from(headerRow2.querySelectorAll('th'));
    const cdioHeaders = allHeaders.filter(th => th.classList.contains('cdio-label-cell'));
    const sdgHeaders = allHeaders.filter(th => th.classList.contains('sdg-label-cell'));

    // Extract CDIO column labels
    cdioHeaders.forEach(th => {
      const input = th.querySelector('input');
      const label = input ? textTrim(input.value) : textTrim(th.textContent);
      if (label && label !== 'No CDIO') {
        raw.cdio_columns.push(label);
      }
    });

    // Extract SDG column labels
    sdgHeaders.forEach(th => {
      const input = th.querySelector('input');
      const label = input ? textTrim(input.value) : textTrim(th.textContent);
      if (label && label !== 'No SDG') {
        raw.sdg_columns.push(label);
      }
    });

    // Get ILO rows (skip header rows 0, 1 and "No ILO" row at index 2)
    const allRows = mappingTable.querySelectorAll('tr');
    const dataRows = Array.from(allRows).slice(3); // Skip header rows and "No ILO" placeholder

    dataRows.forEach((row, idx) => {
      const cells = Array.from(row.querySelectorAll('td'));
      if (cells.length === 0) return;

      const iloCell = cells[0];
      const iloInput = iloCell.querySelector('input');
      const iloText = iloInput ? textTrim(iloInput.value) : textTrim(iloCell.textContent);

      // Skip placeholder or empty ILO
      if (!iloText || iloText === 'No ILO') return;

      // Collect CDIO values (cells after ILO, count matches cdioHeaders)
      const cdios = {};
      cdioHeaders.forEach((header, cdioIdx) => {
        const input = header.querySelector('input');
        const cdioLabel = input ? textTrim(input.value) : textTrim(header.textContent);

        // Skip "No CDIO" placeholder but collect actual CDIO values
        if (cdioLabel === 'No CDIO') return;

        const cdioCell = cells[1 + cdioIdx];
        const cdioValue = cdioCell ? formatWithNewlines(cdioCell.querySelector('textarea')?.value ?? cdioCell.textContent ?? '') : '';
        cdios[cdioLabel] = cdioValue;
      });

      // Collect SDG values (cells after CDIO columns)
      const sdgs = {};
      sdgHeaders.forEach((header, sdgIdx) => {
        const input = header.querySelector('input');
        const sdgLabel = input ? textTrim(input.value) : textTrim(header.textContent);

        // Skip "No SDG" placeholder but collect actual SDG values
        if (sdgLabel === 'No SDG') return;

        const sdgCell = cells[1 + cdioHeaders.length + sdgIdx];
        const sdgValue = sdgCell ? formatWithNewlines(sdgCell.querySelector('textarea')?.value ?? sdgCell.textContent ?? '') : '';
        sdgs[sdgLabel] = sdgValue;
      });

      if (iloText) {
        raw.mappings.push({
          ilo: iloText,
          cdios,
          sdgs
        });
      }
    });

    // Build markdown table
    md.push('### ILO-CDIO-SDG Mapping');

    if (raw.mappings.length === 0) {
      md.push('| ILO | (No data) |');
      md.push('|---|---|');
    } else {
      // Build header with CDIO and SDG prefixes: ILO | CDIO: CDIO1 | CDIO: CDIO2 | ... | SDG: SDG1 | SDG: SDG2 | ...
      const headerCols = [
        'ILO',
        ...raw.cdio_columns.map(col => `CDIO: ${col}`),
        ...raw.sdg_columns.map(col => `SDG: ${col}`)
      ];
      md.push('| ' + headerCols.join(' | ') + ' |');
      md.push('|' + Array(headerCols.length).fill(':--').join('|') + '|');

      // Build rows
      raw.mappings.forEach(mapping => {
        const row = [mapping.ilo];

        // Add CDIO values in order of columns
        raw.cdio_columns.forEach(cdioCol => {
          row.push(mapping.cdios[cdioCol] || '-');
        });

        // Add SDG values in order of columns
        raw.sdg_columns.forEach(sdgCol => {
          row.push(mapping.sdgs[sdgCol] || '-');
        });

        md.push('| ' + row.join(' | ') + ' |');
      });
    }

    return {
      key: 'ilo_cdio_sdg_mapping',
      markdown: md.join('\n'),
      raw,
    };
  }

  // ---------- Textbook Upload Snapshot ----------
  function snapshotTextbook(){
    const rows = Array.from(document.querySelectorAll('tr.textbook-file-row'));
    let textbooksByType = {
      main: [],
      other: [],
      reference: []
    };
    
    rows.forEach(row => {
      const type = row.dataset.type || 'main';
      const nameCell = row.querySelector('td:nth-child(2)') || row.children[1];
      let name = '';
      
      const link = nameCell?.querySelector('a.textbook-file-link');
      const span = nameCell?.querySelector('span.textbook-ref-name');
      
      if (link) {
        name = textTrim(link.textContent ?? link.getAttribute('title') ?? '');
      } else if (span) {
        name = textTrim(span.textContent ?? span.getAttribute('title') ?? '');
      }
      
      if (name) {
        if (!textbooksByType[type]) textbooksByType[type] = [];
        textbooksByType[type].push({ name });
      }
    });

    const md = [];
    md.push('### Textbook and References');
    
    const typeLabels = {
      main: 'Textbook',
      other: 'Other Books and Articles',
      reference: 'References'
    };
    
    const raw = { textbooks: [] };
    let hasContent = false;
    
    Object.entries(textbooksByType).forEach(([type, items]) => {
      if (items.length > 0) {
        hasContent = true;
        md.push(`**${typeLabels[type]}:**`);
        items.forEach((item, idx) => {
          md.push(`${idx + 1}. ${item.name || '-'}`);
          raw.textbooks.push({ type, name: item.name });
        });
        md.push('');
      }
    });
    
    if (!hasContent) {
      md.push('No textbooks or references added.');
    }

    return {
      key: 'textbook',
      markdown: md.join('\n'),
      raw,
    };
  }

  // ---------- Assessment Method and Distribution Map Snapshot ----------
  function snapshotAssessmentTasks(){
    const tbody = document.getElementById('at-tbody');
    let sections = [];
    let iloCount = 0;
    
    if (tbody) {
      // Get ILO column count from table header
      const headerRow = document.querySelector('.at-map-outer .cis-table thead tr:nth-child(2)');
      if (headerRow) {
        // Total columns - (Code, Task, I/R/D, %, C, P, A) = ILO count
        iloCount = Math.max(0, headerRow.children.length - 7);
      }
      
      const mainRows = Array.from(tbody.querySelectorAll('.at-main-row'));
      mainRows.forEach((mainRow, sectionIdx) => {
        const sectionNum = mainRow.dataset.section || (sectionIdx + 1);
        const cells = Array.from(mainRow.children);
        const totalCols = cells.length;
        const iloStartIdx = 4;
        const iloEndIdx = totalCols - 3;
        
        // Extract ILO columns from main row
        const mainIloColumns = [];
        for (let i = iloStartIdx; i < iloEndIdx; i++) {
          const val = textTrim(cells[i]?.querySelector('textarea')?.value ?? '');
          mainIloColumns.push(val);
        }
        
        const section = {
          code: textTrim(cells[0]?.querySelector('textarea')?.value ?? ''),
          task: textTrim(cells[1]?.querySelector('textarea')?.value ?? ''),
          percent: textTrim(cells[3]?.querySelector('textarea')?.value ?? ''),
          iloColumns: mainIloColumns,
          subRows: []
        };
        
        // Get all sub rows for this section
        const subRows = tbody.querySelectorAll(`.at-sub-row[data-section="${sectionNum}"]`);
        subRows.forEach(subRow => {
          const subCells = Array.from(subRow.children);
          
          // Extract ILO columns from sub row
          const iloColumns = [];
          for (let i = iloStartIdx; i < iloEndIdx; i++) {
            const val = textTrim(subCells[i]?.querySelector('textarea')?.value ?? '');
            iloColumns.push(val);
          }
          
          // Extract C, P, A columns (last 3)
          const c = textTrim(subCells[totalCols - 3]?.querySelector('textarea')?.value ?? '');
          const p = textTrim(subCells[totalCols - 2]?.querySelector('textarea')?.value ?? '');
          const a = textTrim(subCells[totalCols - 1]?.querySelector('textarea')?.value ?? '');
          
          section.subRows.push({
            code: textTrim(subCells[0]?.querySelector('textarea')?.value ?? ''),
            task: textTrim(subCells[1]?.querySelector('textarea')?.value ?? ''),
            ird: textTrim(subCells[2]?.querySelector('textarea')?.value ?? ''),
            percent: textTrim(subCells[3]?.querySelector('textarea')?.value ?? ''),
            iloColumns: iloColumns,
            cpa: { c, p, a }
          });
        });
        
        sections.push(section);
      });
    }

    const md = [];
    md.push('### Assessment Method and Distribution Map');
    
    // Build header with ILO numbers and CPA
    let headerCols = ['Code', 'Task', 'I/R/D', '%'];
    for (let i = 1; i <= iloCount; i++) {
      headerCols.push(`ILO${i}`);
    }
    headerCols.push('C', 'P', 'A');
    
    md.push('| ' + headerCols.join(' | ') + ' |');
    md.push('|' + Array(headerCols.length).fill(':--').join('|') + '|');
    
    const raw = { sections: [], iloCount };
    
    if (sections.length === 0) {
      md.push('| - | No assessment tasks defined | - | - |' + '| - |'.repeat(iloCount + 3));
    } else {
      sections.forEach(section => {
        raw.sections.push(section);
        
        if (section.subRows.length === 0) {
          const row = [section.code || '-', section.task || '-', '-', section.percent || ''];
          row.push(...section.iloColumns.map(v => v || '-'));
          row.push('-', '-', '-');
          md.push('| ' + row.join(' | ') + ' |');
        } else {
          // First, show main category row
          const mainRow = [section.code || '-', section.task || '-', '-', section.percent || ''];
          mainRow.push(...section.iloColumns.map(v => v || '-'));
          mainRow.push('-', '-', '-');
          md.push('| ' + mainRow.join(' | ') + ' |');
          
          // Then show each sub-row with subtask in same Task column
          section.subRows.forEach((subRow) => {
            const subRowArray = [];
            // Sub-task code in Code column
            subRowArray.push(subRow.code || '-');
            // Sub-task name in Task column (indented with dash for clarity)
            subRowArray.push(`- ${subRow.task || '-'}`);
            // Sub-task I/R/D type
            subRowArray.push(subRow.ird || '-');
            // Empty % column
            subRowArray.push('');
            // Sub-row ILO distribution
            subRowArray.push(...subRow.iloColumns.map(v => v || '-'));
            // CPA values
            subRowArray.push(subRow.cpa.c || '-', subRow.cpa.p || '-', subRow.cpa.a || '-');
            md.push('| ' + subRowArray.join(' | ') + ' |');
          });
        }
      });
    }

    return {
      key: 'assessment_tasks',
      markdown: md.join('\n'),
      raw,
    };
  }

  // ---------- Aggregation (will expand as we add more partials) ----------
  function collectAllSnapshots(){
    const out = [];
    // Mission & Vision
    try { out.push(snapshotMissionVision()); } catch(e){ console.warn('[snapshot] mission_vision failed', e); }
    // Course Information
    try { out.push(snapshotCourseInfo()); } catch(e){ console.warn('[snapshot] course_info failed', e); }
    // Criteria for Assessment
    try { out.push(snapshotCriteriaAssessment()); } catch(e){ console.warn('[snapshot] criteria_assessment failed', e); }
    // Teaching, Learning, and Assessment Strategies
    try { out.push(snapshotTLAS()); } catch(e){ console.warn('[snapshot] tlas failed', e); }
    // Intended Learning Outcomes
    try { out.push(snapshotILO()); } catch(e){ console.warn('[snapshot] ilo failed', e); }
    // Assessment Method and Distribution Map
    try { out.push(snapshotAssessmentTasks()); } catch(e){ console.warn('[snapshot] assessment_tasks failed', e); }
    // Textbook and References
    try { out.push(snapshotTextbook()); } catch(e){ console.warn('[snapshot] textbook failed', e); }
    // Institutional Graduate Attributes (moved above SO)
    try { out.push(snapshotIGA()); } catch(e){ console.warn('[snapshot] iga failed', e); }
    // Student Outcomes
    try { out.push(snapshotSO()); } catch(e){ console.warn('[snapshot] so failed', e); }
    // CDIO Framework Skills
    try { out.push(snapshotCDIO()); } catch(e){ console.warn('[snapshot] cdio failed', e); }
    // Sustainable Development Goals
    try { out.push(snapshotSDG()); } catch(e){ console.warn('[snapshot] sdg failed', e); }
    // Course Policies (moved below SDG)
    try { out.push(snapshotCoursePolicies()); } catch(e){ console.warn('[snapshot] course_policies failed', e); }
    // Teaching, Learning, and Assessment (TLA) Activities
    try { out.push(snapshotTLA()); } catch(e){ console.warn('[snapshot] tla_activities failed', e); }
    // Assessment Schedule
    try { out.push(snapshotAssessmentSchedule()); } catch(e){ console.warn('[snapshot] assessment_schedule failed', e); }
    // ILO-SO-CPA Mapping
    try { out.push(snapshotIloSoCpa()); } catch(e){ console.warn('[snapshot] ilo_so_cpa_mapping failed', e); }
    // ILO-IGA Mapping
    try { out.push(snapshotIloIga()); } catch(e){ console.warn('[snapshot] ilo_iga_mapping failed', e); }
    // ILO-CDIO-SDG Mapping
    try { out.push(snapshotIloCdioSdg()); } catch(e){ console.warn('[snapshot] ilo_cdio_sdg_mapping failed', e); }
    return out;
  }

  // ---------- Modal Viewer for Snapshots ----------
  function openSnapshotModal(){
    // If already open, do nothing (use toggle to close)
    if (document.getElementById('svSnapshotOverlay')) return;

    // Gather snapshots first
    const shots = collectAllSnapshots();

    // Create overlay/modal
    const overlay = document.createElement('div');
    overlay.id = 'svSnapshotOverlay';
    overlay.style.cssText = 'position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.45);display:flex;align-items:center;justify-content:center;';
    const modal = document.createElement('div');
    modal.style.cssText = 'width:90%;max-width:1000px;max-height:85%;background:#fff;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.25);display:flex;flex-direction:column;';

    const header = document.createElement('div');
    header.style.cssText = 'padding:14px 18px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;gap:10px;';
    const title = document.createElement('div');
    title.textContent = 'Syllabus Snapshots';
    title.style.cssText = 'font-weight:700;color:#111827;';
    const closeBtn = document.createElement('button');
    closeBtn.textContent = 'Close';
    closeBtn.style.cssText = 'margin-left:auto;padding:6px 10px;border:1px solid #ccc;background:#f7f7f7;border-radius:8px;cursor:pointer;';
    closeBtn.addEventListener('click', closeSnapshotModal);
    header.appendChild(title); header.appendChild(closeBtn);

    const body = document.createElement('div');
    body.style.cssText = 'padding:12px 16px;overflow:auto;';

    // Build content per snapshot
    shots.forEach(s => {
      const card = document.createElement('div');
      card.style.cssText = 'border:1px solid #e6e9ed;border-radius:10px;margin-bottom:12px;background:#fff;';
      const head = document.createElement('div');
      head.style.cssText = 'padding:10px 12px;border-bottom:1px solid #e6e9ed;display:flex;align-items:center;gap:8px;';
      const h = document.createElement('div');
      h.textContent = s.key;
      h.style.cssText = 'font-weight:600;color:#111827;';
      head.appendChild(h);
      const content = document.createElement('div');
      content.style.cssText = 'padding:12px;';
      const pre = document.createElement('pre');
      pre.style.cssText = 'white-space:pre-wrap;word-wrap:break-word;font-size:12px;line-height:1.45;margin:0;';
      pre.textContent = s.markdown || '';
      content.appendChild(pre);
      card.appendChild(head);
      card.appendChild(content);
      body.appendChild(card);
    });

    modal.appendChild(header);
    modal.appendChild(body);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
  }

  function closeSnapshotModal(){
    const overlay = document.getElementById('svSnapshotOverlay');
    if (overlay) overlay.remove();
  }

  // ---------- Expose API ----------
  window.SVSnapshot = {
    snapshotMissionVision,
    snapshotCourseInfo,
    snapshotCriteriaAssessment,
    snapshotTLAS,
    snapshotTLA,
    snapshotILO,
    snapshotAssessmentTasks,
    snapshotAssessmentSchedule,
    snapshotIloSoCpa,
    snapshotIloIga,
    snapshotIloCdioSdg,
    snapshotCoursePolicies,
    snapshotSO,
    snapshotCDIO,
    snapshotSDG,
    snapshotIGA,
    snapshotTextbook,
    collectAllSnapshots,
    openSnapshotModal,
    closeSnapshotModal,
  };

  // Optional: keyboard shortcut Shift+S to open modal
  document.addEventListener('keydown', (e) => {
    if (!e.shiftKey) return;
    const key = e.key ? e.key.toLowerCase() : '';
    if (key !== 's') return;
    // Toggle: if open, close; else open
    const existing = document.getElementById('svSnapshotOverlay');
    if (existing) { closeSnapshotModal(); } else { openSnapshotModal(); }
  });
})();
