// File: resources/js/faculty/syllabus-ilo-cdio-sdg.js
// ILO-CDIO-SDG mapping - Functions for add/remove ILO rows and CDIO/SDG columns

document.addEventListener('DOMContentLoaded', function() {
    const mapping = document.querySelector('.ilo-cdio-sdg-mapping');
    if (!mapping) return;

    // Hide partial label text if it overflows
    function checkPartialLabelOverflow() {
        const partialLabel = mapping.querySelector('.partial-label');
        const labelText = partialLabel ? partialLabel.querySelector('div') : null;
        if (!partialLabel || !labelText) return;
        
        // Temporarily show to measure
        labelText.style.display = 'block';
        
        const containerHeight = partialLabel.offsetHeight;
        const textWidth = labelText.scrollWidth; // Width when rotated becomes height
        
        // Hide if overflowing
        if (textWidth > containerHeight) {
            labelText.style.display = 'none';
        }
    }
    
    // Check on load and when rows are added/removed
    checkPartialLabelOverflow();
    window.addEventListener('resize', checkPartialLabelOverflow);
    
    // Store function for use by add/remove row functions
    window.checkCdioSdgLabelOverflow = checkPartialLabelOverflow;
    
    // Auto-format ILO inputs - attach listener to existing inputs
    function attachIloAutoFormat() {
        const innerTable = mapping.querySelector('table.mapping');
        if (!innerTable) return;
        
        const allRows = innerTable.querySelectorAll('tr');
        const dataRows = Array.from(allRows).slice(3); // Skip header rows and "No ILO" row
        
        dataRows.forEach(row => {
            const iloCell = row.querySelector('td:first-child');
            if (iloCell) {
                const iloInput = iloCell.querySelector('input');
                if (iloInput && !iloInput.dataset.iloFormatAttached) {
                    iloInput.addEventListener('input', function(e) {
                        const value = e.target.value;
                        if (/^\d+$/.test(value)) {
                            e.target.value = 'ILO' + value;
                        }
                    });
                    iloInput.dataset.iloFormatAttached = 'true';
                }
            }
        });
    }
    
    // Attach on load
    attachIloAutoFormat();
    
    // Store function for use by dynamically created rows
    window.attachIloAutoFormat = attachIloAutoFormat;
    
    // Load saved data on page load
    function loadSavedData() {
        const mappingsData = mapping.getAttribute('data-mappings');
        
        if (!mappingsData) return;
        
        try {
            const mappings = JSON.parse(mappingsData);
            
            if (!mappings || mappings.length === 0) return;
            
            // Collect all unique CDIO and SDG labels from the data
            const cdioLabelsSet = new Set();
            const sdgLabelsSet = new Set();
            
            mappings.forEach(row => {
                if (row.cdios && typeof row.cdios === 'object') {
                    Object.keys(row.cdios).forEach(label => cdioLabelsSet.add(label));
                }
                if (row.sdgs && typeof row.sdgs === 'object') {
                    Object.keys(row.sdgs).forEach(label => sdgLabelsSet.add(label));
                }
            });
            
            const cdioLabels = Array.from(cdioLabelsSet);
            const sdgLabels = Array.from(sdgLabelsSet);
            
            // Add CDIO columns
            if (cdioLabels.length > 0) {
                cdioLabels.forEach((cdioLabel, index) => {
                    if (index === 0) {
                        // First CDIO - convert placeholder
                        addCdioColumn();
                        // Update the label
                        const innerTable = mapping.querySelector('table.mapping');
                        const allRows = innerTable.querySelectorAll('tr');
                        const headerRow2 = allRows[1];
                        const cdioHeaders = Array.from(headerRow2.querySelectorAll('th.cdio-label-cell'));
                        const firstCdioHeader = cdioHeaders[0];
                        if (firstCdioHeader) {
                            const input = firstCdioHeader.querySelector('input');
                            if (input) input.value = cdioLabel;
                        }
                    } else {
                        // Additional CDIO columns
                        addCdioColumn();
                        // Update the label
                        const innerTable = mapping.querySelector('table.mapping');
                        const allRows = innerTable.querySelectorAll('tr');
                        const headerRow2 = allRows[1];
                        const cdioHeaders = Array.from(headerRow2.querySelectorAll('th.cdio-label-cell'));
                        const currentCdioHeader = cdioHeaders[index];
                        if (currentCdioHeader) {
                            const input = currentCdioHeader.querySelector('input');
                            if (input) input.value = cdioLabel;
                        }
                    }
                });
            }
            
            // Refresh function: rebuild partial from provided mappings (after AI insert / manual save)
            window.refreshIloCdioSdgPartial = function(mappings){
                try {
                    const innerTable = mapping.querySelector('table.mapping');
                    if (!innerTable) return false;

                    // Persist new state on the root for resilience
                    mapping.setAttribute('data-mappings', JSON.stringify(mappings || []));

                    const allRows = innerTable.querySelectorAll('tr');
                    const headerRow1 = allRows[0];
                    const headerRow2 = allRows[1];

                    // Helpers to count real columns (exclude placeholders)
                    const countCdioCols = () => Array.from(headerRow2.querySelectorAll('th.cdio-label-cell')).filter(th => !th.textContent.includes('No CDIO')).length;
                    const countSdgCols = () => Array.from(headerRow2.querySelectorAll('th.sdg-label-cell')).filter(th => !th.textContent.includes('No SDG')).length;

                    const desiredMappings = Array.isArray(mappings) ? mappings : [];
                    const desiredCdioLabels = (() => {
                        const set = new Set();
                        desiredMappings.forEach(m => { if (m && m.cdios && typeof m.cdios === 'object') Object.keys(m.cdios).forEach(k => set.add(k)); });
                        return Array.from(set);
                    })();
                    const desiredSdgLabels = (() => {
                        const set = new Set();
                        desiredMappings.forEach(m => { if (m && m.sdgs && typeof m.sdgs === 'object') Object.keys(m.sdgs).forEach(k => set.add(k)); });
                        return Array.from(set);
                    })();

                    // Ensure CDIO columns count
                    let currentCdio = countCdioCols();
                    while (currentCdio < desiredCdioLabels.length) { addCdioColumn(); currentCdio = countCdioCols(); }
                    while (currentCdio > desiredCdioLabels.length) { removeCdioColumn(); currentCdio = countCdioCols(); }
                    // Set CDIO labels
                    const cdioHeaders = Array.from(headerRow2.querySelectorAll('th.cdio-label-cell')).filter(th => !th.textContent.includes('No CDIO'));
                    cdioHeaders.forEach((th, idx) => { const input = th.querySelector('input'); if (input) input.value = desiredCdioLabels[idx] || ''; });

                    // Ensure SDG columns count
                    let currentSdg = countSdgCols();
                    while (currentSdg < desiredSdgLabels.length) { addSdgColumn(); currentSdg = countSdgCols(); }
                    while (currentSdg > desiredSdgLabels.length) { removeSdgColumn(); currentSdg = countSdgCols(); }
                    // Set SDG labels
                    const sdgHeaders = Array.from(headerRow2.querySelectorAll('th.sdg-label-cell')).filter(th => !th.textContent.includes('No SDG'));
                    sdgHeaders.forEach((th, idx) => { const input = th.querySelector('input'); if (input) input.value = desiredSdgLabels[idx] || ''; });

                    // Ensure ILO rows count
                    const dataRowsAll = Array.from(innerTable.querySelectorAll('tr')).slice(3); // rows after headers
                    let dataRows = dataRowsAll.filter(row => row.querySelector('td') && row.style.display !== 'none');
                    while (dataRows.length < desiredMappings.length) { addIloRowCdioSdg(); dataRows = Array.from(innerTable.querySelectorAll('tr')).slice(3).filter(r => r.querySelector('td') && r.style.display !== 'none'); }
                    while (dataRows.length > desiredMappings.length) { removeIloRowCdioSdg(); dataRows = Array.from(innerTable.querySelectorAll('tr')).slice(3).filter(r => r.querySelector('td') && r.style.display !== 'none'); }

                    // If no rows desired, show placeholder and exit
                    if (desiredMappings.length === 0) {
                        const noIloRow = allRows[2];
                        if (noIloRow) noIloRow.style.display = '';
                        return true;
                    }

                    // Populate rows
                    const cdioKeys = cdioHeaders.map(th => th.querySelector('input')?.value.trim() || th.textContent.trim()).filter(Boolean);
                    const sdgKeys = sdgHeaders.map(th => th.querySelector('input')?.value.trim() || th.textContent.trim()).filter(Boolean);
                    desiredMappings.forEach((m, idx) => {
                        const row = dataRows[idx];
                        if (!row) return;
                        const cells = Array.from(row.querySelectorAll('td'));
                        const iloInput = cells[0]?.querySelector('input');
                        if (iloInput) iloInput.value = m?.ilo_text || '';
                        cdioKeys.forEach((key, cIdx) => {
                            const cell = cells[1 + cIdx];
                            const ta = cell?.querySelector('textarea');
                            if (ta) ta.value = (m?.cdios && typeof m.cdios === 'object' && m.cdios[key] !== undefined && m.cdios[key] !== null) ? m.cdios[key] : '';
                        });
                        sdgKeys.forEach((key, sIdx) => {
                            const cell = cells[1 + cdioKeys.length + sIdx];
                            const ta = cell?.querySelector('textarea');
                            if (ta) ta.value = (m?.sdgs && typeof m.sdgs === 'object' && m.sdgs[key] !== undefined && m.sdgs[key] !== null) ? m.sdgs[key] : '';
                        });
                    });

                    // Re-attach helpers
                    attachIloAutoFormat();
                    if (typeof window.checkCdioSdgLabelOverflow === 'function') window.checkCdioSdgLabelOverflow();

                    return true;
                } catch (e) {
                    console.error('refreshIloCdioSdgPartial failed', e);
                    return false;
                }
            };

            // AJAX refresh helper: fetch syllabus page and update this partial
            window.ajaxRefreshIloCdioSdgPartial = async function(){
                let syllabusId = null;
                const syllabusDoc = document.getElementById('syllabus-document');
                if (syllabusDoc) syllabusId = syllabusDoc.getAttribute('data-syllabus-id');
                if (!syllabusId) {
                    const m = (location.pathname||'').match(/\/faculty\/syllabi\/(\d+)/);
                    syllabusId = m ? m[1] : null;
                }
                if (!syllabusId) throw new Error('Syllabus ID not found for refresh');
                const res = await fetch(`/faculty/syllabi/${syllabusId}`, { headers: { 'Accept': 'text/html' } });
                if (!res.ok) throw new Error('Failed to fetch syllabus page');
                const html = await res.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const partial = doc.querySelector('.ilo-cdio-sdg-mapping');
                if (!partial) throw new Error('ILO-CDIO-SDG partial not found in response');
                const mappingsData = partial.getAttribute('data-mappings') || '[]';
                let mappings = [];
                try { mappings = JSON.parse(mappingsData); } catch(_) { mappings = []; }
                if (typeof window.refreshIloCdioSdgPartial === 'function') window.refreshIloCdioSdgPartial(mappings);
                return { mappings };
            };

            // Add SDG columns
            if (sdgLabels.length > 0) {
                sdgLabels.forEach((sdgLabel, index) => {
                    if (index === 0) {
                        // First SDG - convert placeholder
                        addSdgColumn();
                        // Update the label
                        const innerTable = mapping.querySelector('table.mapping');
                        const allRows = innerTable.querySelectorAll('tr');
                        const headerRow2 = allRows[1];
                        const sdgHeaders = Array.from(headerRow2.querySelectorAll('th.sdg-label-cell'));
                        const firstSdgHeader = sdgHeaders[0];
                        if (firstSdgHeader) {
                            const input = firstSdgHeader.querySelector('input');
                            if (input) input.value = sdgLabel;
                        }
                    } else {
                        // Additional SDG columns
                        addSdgColumn();
                        // Update the label
                        const innerTable = mapping.querySelector('table.mapping');
                        const allRows = innerTable.querySelectorAll('tr');
                        const headerRow2 = allRows[1];
                        const sdgHeaders = Array.from(headerRow2.querySelectorAll('th.sdg-label-cell'));
                        const currentSdgHeader = sdgHeaders[index];
                        if (currentSdgHeader) {
                            const input = currentSdgHeader.querySelector('input');
                            if (input) input.value = sdgLabel;
                        }
                    }
                });
            }
            
            // Populate ILO rows
            mappings.forEach((mappingRow, rowIndex) => {
                if (rowIndex === 0) {
                    // First row - convert "No ILO" placeholder
                    addIloRowCdioSdg();
                    const innerTable = mapping.querySelector('table.mapping');
                    const allRows = innerTable.querySelectorAll('tr');
                    const dataRows = Array.from(allRows).slice(3); // Skip header rows and "No ILO" row
                    const firstRow = dataRows[0];
                    if (firstRow) {
                        const cells = Array.from(firstRow.querySelectorAll('td'));
                        // Set ILO label
                        const iloInput = cells[0].querySelector('input');
                        if (iloInput) iloInput.value = mappingRow.ilo_text;
                        
                        // Set CDIO values
                        if (mappingRow.cdios && typeof mappingRow.cdios === 'object') {
                            Object.entries(mappingRow.cdios).forEach(([cdioLabel, cdioValue], cdioIndex) => {
                                const cdioCell = cells[1 + cdioIndex]; // +1 because first cell is ILO
                                if (cdioCell) {
                                    const textarea = cdioCell.querySelector('textarea');
                                    if (textarea) {
                                        textarea.value = cdioValue;
                                    }
                                }
                            });
                        }
                        
                        // Set SDG values (after CDIO cells)
                        if (mappingRow.sdgs && typeof mappingRow.sdgs === 'object') {
                            Object.entries(mappingRow.sdgs).forEach(([sdgLabel, sdgValue], sdgIndex) => {
                                const sdgCell = cells[1 + cdioLabels.length + sdgIndex]; // +1 for ILO, +cdioLabels.length for CDIO columns
                                if (sdgCell) {
                                    const textarea = sdgCell.querySelector('textarea');
                                    if (textarea) {
                                        textarea.value = sdgValue;
                                    }
                                }
                            });
                        }
                    }
                } else {
                    // Additional rows
                    addIloRowCdioSdg();
                    const innerTable = mapping.querySelector('table.mapping');
                    const allRows = innerTable.querySelectorAll('tr');
                    const dataRows = Array.from(allRows).slice(3); // Skip header rows and "No ILO" row
                    const currentRow = dataRows[rowIndex];
                    if (currentRow) {
                        const cells = Array.from(currentRow.querySelectorAll('td'));
                        // Set ILO label
                        const iloInput = cells[0].querySelector('input');
                        if (iloInput) iloInput.value = mappingRow.ilo_text;
                        
                        // Set CDIO values
                        if (mappingRow.cdios && typeof mappingRow.cdios === 'object') {
                            Object.entries(mappingRow.cdios).forEach(([cdioLabel, cdioValue], cdioIndex) => {
                                const cdioCell = cells[1 + cdioIndex]; // +1 because first cell is ILO
                                if (cdioCell) {
                                    const textarea = cdioCell.querySelector('textarea');
                                    if (textarea) {
                                        textarea.value = cdioValue;
                                    }
                                }
                            });
                        }
                        
                        // Set SDG values (after CDIO cells)
                        if (mappingRow.sdgs && typeof mappingRow.sdgs === 'object') {
                            Object.entries(mappingRow.sdgs).forEach(([sdgLabel, sdgValue], sdgIndex) => {
                                const sdgCell = cells[1 + cdioLabels.length + sdgIndex]; // +1 for ILO, +cdioLabels.length for CDIO columns
                                if (sdgCell) {
                                    const textarea = sdgCell.querySelector('textarea');
                                    if (textarea) {
                                        textarea.value = sdgValue;
                                    }
                                }
                            });
                        }
                    }
                }
            });
        } catch (e) {
            console.error('Error loading saved ILO-CDIO-SDG data:', e);
        }
    }
    
    // Load data after a short delay to ensure DOM is fully ready
    setTimeout(() => {
        loadSavedData();
    }, 100);
});

/**
 * Add a new ILO row to the CDIO-SDG mapping table
 */
window.addIloRowCdioSdg = function() {
    const innerTable = document.querySelector('.ilo-cdio-sdg-mapping table.mapping');
    if (!innerTable) return;

    const allRows = innerTable.querySelectorAll('tr');
    const rows = Array.from(allRows).slice(3); // Skip header rows and "No ILO" row (rows 0, 1, 2)
    const noIloRow = allRows[2]; // "No ILO" row
    
    // Hide "No ILO" row when adding first real row
    if (rows.length === 0) {
        noIloRow.style.display = 'none';
    }
    
    // Calculate new ILO number (existing data rows + 1)
    const newIloNum = rows.length + 1;

    // Create new row
    const newRow = document.createElement('tr');
    
    // Get the number of columns (ILO + CDIO columns + SDG columns)
    const headerRow1 = allRows[0];
    const cdioColspan = parseInt(headerRow1.children[1].getAttribute('colspan')) || 1;
    const sdgColspan = parseInt(headerRow1.children[2].getAttribute('colspan')) || 1;
    const totalCols = 1 + cdioColspan + sdgColspan;
    
    // Create ILO label cell with input
    const iloCell = document.createElement('td');
    iloCell.style.cssText = 'border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.1rem 0.25rem; font-family:Georgia, serif; font-size:13px; color:#000; text-align:center; vertical-align:middle;';
    
    const iloInput = document.createElement('input');
    iloInput.type = 'text';
    iloInput.className = 'form-control form-control-sm';
    iloInput.placeholder = '-';
    iloInput.value = '';
    iloInput.setAttribute('name', 'ilo_sdg_cdio_ilos_text[]');
    iloInput.style.cssText = 'width:100%; border:none; padding:0.1rem 0.25rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; background:transparent;';
    // Auto-format ILO number
    iloInput.addEventListener('input', function(e) {
        const value = e.target.value;
        if (/^\d+$/.test(value)) {
            e.target.value = 'ILO' + value;
        }
    });
    iloCell.appendChild(iloInput);
    newRow.appendChild(iloCell);
    
    // Create CDIO and SDG cells with enabled textareas
    for (let i = 1; i < totalCols; i++) {
        const cell = document.createElement('td');
        const isCdioCol = (i <= cdioColspan);
        const isFirstSdgCol = (i === cdioColspan + 1);
        
        // CDIO columns have border-right, first SDG column has border-left (inner separator)
        if (isCdioCol) {
            cell.style.cssText = 'border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;';
        } else if (isFirstSdgCol) {
            cell.style.cssText = 'border:none; border-top:1px solid #343a40; border-left:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;';
        } else {
            cell.style.cssText = 'border:none; border-top:1px solid #343a40; border-left:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;';
        }
        
        const textarea = document.createElement('textarea');
        textarea.className = 'form-control form-control-sm';
        textarea.placeholder = '-';
        textarea.rows = 1;
        textarea.style.cssText = 'width:100%; min-height:22px; border:none; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; resize:none; overflow:hidden;';
        
        // Set appropriate name based on column type
        if (i <= cdioColspan) {
            // CDIO column
            textarea.setAttribute('name', `ilo_sdg_cdio_cdio${i}_text[]`);
        } else {
            // SDG column
            const sdgIndex = i - cdioColspan;
            textarea.setAttribute('name', `ilo_sdg_cdio_sdg${sdgIndex}_text[]`);
        }
        
        cell.appendChild(textarea);
        newRow.appendChild(cell);
    }
    
    innerTable.appendChild(newRow);
    
    // Check label overflow after adding row
    if (typeof window.checkCdioSdgLabelOverflow === 'function') {
        window.checkCdioSdgLabelOverflow();
    }
};/**
 * Remove the last ILO row from the CDIO-SDG mapping table
 */
window.removeIloRowCdioSdg = function() {
    const innerTable = document.querySelector('.ilo-cdio-sdg-mapping table.mapping');
    if (!innerTable) return;

    const allRows = innerTable.querySelectorAll('tr');
    const rows = Array.from(allRows).slice(3); // Skip header rows and "No ILO" row
    const noIloRow = allRows[2]; // "No ILO" row
    
    if (rows.length > 0) {
        rows[rows.length - 1].remove();
        
        // Show "No ILO" row again if no data rows remain
        if (rows.length === 1) {
            noIloRow.style.display = '';
        }
    }
    
    // Check label overflow after removing row
    if (typeof window.checkCdioSdgLabelOverflow === 'function') {
        window.checkCdioSdgLabelOverflow();
    }
};

/**
 * Add a new CDIO column to the mapping table
 * Structure: ILO column (60px) | CDIO columns (50%) | SDG columns (50%)
 */
window.addCdioColumn = function() {
    const innerTable = document.querySelector('.ilo-cdio-sdg-mapping table.mapping');
    if (!innerTable) {
        console.error('Inner table not found');
        return;
    }

    const colgroup = innerTable.querySelector('colgroup');
    const allRows = innerTable.querySelectorAll('tr');
    const headerRow1 = allRows[0]; // ILOs | CDIO SKILLS | SDG
    const headerRow2 = allRows[1]; // No CDIO | No SDG

    const cdioColspan = parseInt(headerRow1.children[1].getAttribute('colspan')) || 1;
    const sdgColspan = parseInt(headerRow1.children[2].getAttribute('colspan')) || 1;

    // Get the first CDIO header cell from row 2
    const firstCdioHeader = headerRow2.children[0];
    
    // Check if we're converting from placeholder (text node "No CDIO")
    const isPlaceholder = firstCdioHeader.textContent.includes('No CDIO');
    
    // If this is the first additional column (converting from placeholder), convert text to input
    if (isPlaceholder) {
        // Don't add a new col to colgroup - just update width of existing one
        colgroup.children[1].style.width = '50%';
        
        // Keep colspan at 1 in row 1
        // Update the original header cell to have an input field
        firstCdioHeader.innerHTML = '';
        
        // Add both buttons - it's the only column so it's both first and last
        const removeControlsDiv = document.createElement('div');
        removeControlsDiv.className = 'cdio-header-controls';
        removeControlsDiv.innerHTML = `
            <button type="button" class="btn btn-sm cdio-remove-btn" onclick="removeCdioColumn()" title="Remove CDIO column" aria-label="Remove CDIO column">
                <i data-feather="minus"></i>
            </button>
        `;
        firstCdioHeader.prepend(removeControlsDiv);
        
        const addControlsDiv = document.createElement('div');
        addControlsDiv.className = 'sdg-header-controls';
        addControlsDiv.innerHTML = `
            <button type="button" class="btn btn-sm cdio-add-btn" onclick="addCdioColumn()" title="Add CDIO column" aria-label="Add CDIO column">
                <i data-feather="plus"></i>
            </button>
        `;
        firstCdioHeader.appendChild(addControlsDiv);
        
        // Add input field
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control form-control-sm';
        input.placeholder = '-';
        input.style.cssText = 'width:100%; border:none; padding:0.1rem 0.25rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; background:transparent; font-weight:700;';
        firstCdioHeader.appendChild(input);
        
        // Update styling to match input state
        firstCdioHeader.style.fontWeight = '700';
        firstCdioHeader.style.fontStyle = 'normal';
        firstCdioHeader.style.color = '#111';
        
        // Update data row cells to convert placeholder to textarea
        for (let i = 2; i < allRows.length; i++) {
            const row = allRows[i];
            const firstCdioCell = row.children[1]; // First CDIO cell (after ILO cell)
            firstCdioCell.innerHTML = '';
            firstCdioCell.style.cssText = 'border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;';
            
            const textarea = document.createElement('textarea');
            textarea.className = 'form-control form-control-sm';
            textarea.placeholder = '-';
            textarea.rows = 1;
            textarea.style.cssText = 'width:100%; min-height:22px; border:none; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; resize:none; overflow:hidden;';
            textarea.setAttribute('name', 'ilo_sdg_cdio_cdio1_text[]');
            firstCdioCell.appendChild(textarea);
        }
        
        // Re-initialize feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
        
        // Don't add another column - just converted placeholder
        return;
    }
    
    // For additional columns beyond the first:
    // Calculate new column widths
    const newCdioColspan = cdioColspan + 1;
    const cdioWidth = (50 / newCdioColspan).toFixed(4);

    // Update existing CDIO column widths in colgroup
    for (let i = 1; i <= cdioColspan; i++) {
        colgroup.children[i].style.width = `${cdioWidth}%`;
    }

    // Add new CDIO col to colgroup at position 1 + cdioColspan (before SDG columns)
    const newCol = document.createElement('col');
    newCol.style.width = `${cdioWidth}%`;
    if (colgroup.children[1 + cdioColspan]) {
        colgroup.insertBefore(newCol, colgroup.children[1 + cdioColspan]);
    } else {
        colgroup.appendChild(newCol);
    }

    // Update CDIO header colspan in row 1
    headerRow1.children[1].setAttribute('colspan', newCdioColspan);
    
    // Clone the first CDIO header as template for new column
    const newCdioHeader = firstCdioHeader.cloneNode(true);
    
    // Remove ALL controls from cloned header
    const controls = newCdioHeader.querySelectorAll('.cdio-header-controls, .sdg-header-controls');
    controls.forEach(ctrl => ctrl.remove());
    
    // Update input placeholder
    const input = newCdioHeader.querySelector('input');
    if (input) {
        input.placeholder = '-';
        input.value = '';
    }
    
    // Remove ALL controls from all existing CDIO columns
    for (let i = 0; i < cdioColspan; i++) {
        const header = headerRow2.children[i];
        const existingControls = header.querySelectorAll('.cdio-header-controls, .sdg-header-controls');
        existingControls.forEach(ctrl => ctrl.remove());
    }
    
    // Add remove button to FIRST CDIO column (leftmost)
    const firstRemoveControlsDiv = document.createElement('div');
    firstRemoveControlsDiv.className = 'cdio-header-controls';
    firstRemoveControlsDiv.innerHTML = `
        <button type="button" class="btn btn-sm cdio-remove-btn" onclick="removeCdioColumn()" title="Remove CDIO column" aria-label="Remove CDIO column">
            <i data-feather="minus"></i>
        </button>
    `;
    firstCdioHeader.prepend(firstRemoveControlsDiv);
    
    // Add add button to the NEW (last) CDIO column
    const addControlsDiv = document.createElement('div');
    addControlsDiv.className = 'sdg-header-controls';
    addControlsDiv.innerHTML = `
        <button type="button" class="btn btn-sm cdio-add-btn" onclick="addCdioColumn()" title="Add CDIO column" aria-label="Add CDIO column">
            <i data-feather="plus"></i>
        </button>
    `;
    newCdioHeader.appendChild(addControlsDiv);
    
    // Insert before the SDG header at position cdioColspan
    headerRow2.insertBefore(newCdioHeader, headerRow2.children[cdioColspan]);
    
    // Re-initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Add cells to all data rows (row 2 onwards - "No ILO" and actual data rows)
    for (let i = 2; i < allRows.length; i++) {
        const row = allRows[i];
        const cells = row.children;
        const newCell = document.createElement('td');
        
        // Clone the first CDIO cell as template
        const firstCdioCell = cells[1];
        newCell.innerHTML = firstCdioCell.innerHTML;
        newCell.style.cssText = firstCdioCell.style.cssText;
        
        // Update textarea name for non-disabled cells
        const textarea = newCell.querySelector('textarea');
        if (textarea && !textarea.disabled) {
            textarea.setAttribute('name', `ilo_sdg_cdio_cdio${newCdioColspan}_text[]`);
            textarea.value = '';
        }
        
        // Insert before the SDG cell at position 1 + cdioColspan
        row.insertBefore(newCell, cells[1 + cdioColspan]);
    }
};

/**
 * Remove the last CDIO column from the mapping table
 */
window.removeCdioColumn = function() {
    // Prevent multiple rapid calls
    if (window.removingCdioColumn) {
        console.log('Remove CDIO already in progress, ignoring');
        return;
    }
    window.removingCdioColumn = true;
    
    const innerTable = document.querySelector('.ilo-cdio-sdg-mapping table.mapping');
    if (!innerTable) {
        window.removingCdioColumn = false;
        return;
    }

    const colgroup = innerTable.querySelector('colgroup');
    const allRows = innerTable.querySelectorAll('tr');
    const headerRow1 = allRows[0];
    const headerRow2 = allRows[1];

    const cdioColspan = parseInt(headerRow1.children[1].getAttribute('colspan')) || 1;
    
    // If already at 1 column, convert to placeholder state
    if (cdioColspan <= 1) {
        const firstCdioHeader = headerRow2.children[0];
        
        // Check if it's already a placeholder
        const isPlaceholder = firstCdioHeader.textContent.includes('No CDIO');
        if (isPlaceholder) {
            window.removingCdioColumn = false;
            return; // Already at placeholder, can't remove further
        }
        
        // Convert to placeholder
        firstCdioHeader.innerHTML = '';
        
        // Add buttons
        const removeControlsDiv = document.createElement('div');
        removeControlsDiv.className = 'cdio-header-controls';
        removeControlsDiv.innerHTML = `
            <button type="button" class="btn btn-sm cdio-remove-btn" onclick="removeCdioColumn()" title="Remove CDIO column" aria-label="Remove CDIO column">
                <i data-feather="minus"></i>
            </button>
        `;
        firstCdioHeader.appendChild(removeControlsDiv);
        
        const addControlsDiv = document.createElement('div');
        addControlsDiv.className = 'sdg-header-controls';
        addControlsDiv.innerHTML = `
            <button type="button" class="btn btn-sm cdio-add-btn" onclick="addCdioColumn()" title="Add CDIO column" aria-label="Add CDIO column">
                <i data-feather="plus"></i>
            </button>
        `;
        firstCdioHeader.appendChild(addControlsDiv);
        
        // Add placeholder text
        firstCdioHeader.appendChild(document.createTextNode('No CDIO'));
        
        // Update styling to placeholder state
        firstCdioHeader.style.cssText = 'border:none; border-bottom:1px solid #343a40; border-right:1px solid #343a40; min-height:30px; height:30px; padding:0.2rem 0.5rem; font-weight:400; font-style:italic; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#999; text-align:center; position:relative;';
        
        // Update data row cells to placeholder with disabled textarea
        for (let i = 2; i < allRows.length; i++) {
            const row = allRows[i];
            const firstCdioCell = row.children[1];
            if (firstCdioCell) {
                firstCdioCell.innerHTML = '<textarea class="form-control form-control-sm" placeholder="-" rows="1" style="width:100%; min-height:22px; border:none; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; resize:none; overflow:hidden; background-color:#f8f9fa; cursor:not-allowed;" disabled></textarea>';
                firstCdioCell.style.cssText = 'border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle; background-color:#f8f9fa;';
            }
        }
        
        // Re-initialize feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
        
        window.removingCdioColumn = false;
        return;
    }

    const newCdioColspan = cdioColspan - 1;
    
    // Remove last col from colgroup first (always remove when going from 2+ to 1+)
    if (colgroup.children[cdioColspan]) {
        colgroup.children[cdioColspan].remove();
    }
    
    // If going back to 1 column (placeholder state)
    if (newCdioColspan === 1) {
        // Update the remaining CDIO column width to 50%
        colgroup.children[1].style.width = '50%';
        
        // Keep colspan at 1 in row 1
        headerRow1.children[1].setAttribute('colspan', 1);
        
        // Remove the last CDIO header from row 2 (the one we want to delete)
        if (headerRow2.children[cdioColspan - 1]) {
            headerRow2.children[cdioColspan - 1].remove();
        }
    } else {
        // Recalculate CDIO column widths for remaining columns
        const cdioWidth = (50 / newCdioColspan).toFixed(4);
        for (let i = 1; i <= newCdioColspan; i++) {
            colgroup.children[i].style.width = `${cdioWidth}%`;
        }

        // Update CDIO header colspan in row 1
        headerRow1.children[1].setAttribute('colspan', newCdioColspan);

        // Remove last CDIO header from row 2
        if (headerRow2.children[cdioColspan - 1]) {
            headerRow2.children[cdioColspan - 1].remove();
        }
    }

    // Add buttons based on remaining columns
    if (newCdioColspan === 1) {
        // If 1 column remains, it's both first and last - add both buttons
        const singleHeader = headerRow2.children[0];
        
        // Remove any existing controls first
        const existingControls = singleHeader.querySelectorAll('.cdio-header-controls, .sdg-header-controls');
        existingControls.forEach(ctrl => ctrl.remove());
        
        const removeControlsDiv = document.createElement('div');
        removeControlsDiv.className = 'cdio-header-controls';
        removeControlsDiv.innerHTML = `
            <button type="button" class="btn btn-sm cdio-remove-btn" onclick="removeCdioColumn()" title="Remove CDIO column" aria-label="Remove CDIO column">
                <i data-feather="minus"></i>
            </button>
        `;
        singleHeader.prepend(removeControlsDiv);
        
        const addControlsDiv = document.createElement('div');
        addControlsDiv.className = 'sdg-header-controls';
        addControlsDiv.innerHTML = `
            <button type="button" class="btn btn-sm cdio-add-btn" onclick="addCdioColumn()" title="Add CDIO column" aria-label="Add CDIO column">
                <i data-feather="plus"></i>
            </button>
        `;
        singleHeader.appendChild(addControlsDiv);
    } else if (newCdioColspan > 1) {
        // If multiple columns remain, remove on first and add on last
        const firstCdioHeader = headerRow2.children[0];
        const lastCdioHeader = headerRow2.children[newCdioColspan - 1];
        
        // Remove all existing controls first
        for (let i = 0; i < newCdioColspan; i++) {
            const header = headerRow2.children[i];
            const existingControls = header.querySelectorAll('.cdio-header-controls, .sdg-header-controls');
            existingControls.forEach(ctrl => ctrl.remove());
        }
        
        // Add remove button to first column
        const removeControlsDiv = document.createElement('div');
        removeControlsDiv.className = 'cdio-header-controls';
        removeControlsDiv.innerHTML = `
            <button type="button" class="btn btn-sm cdio-remove-btn" onclick="removeCdioColumn()" title="Remove CDIO column" aria-label="Remove CDIO column">
                <i data-feather="minus"></i>
            </button>
        `;
        firstCdioHeader.prepend(removeControlsDiv);
        
        // Add add button to last column
        const addControlsDiv = document.createElement('div');
        addControlsDiv.className = 'sdg-header-controls';
        addControlsDiv.innerHTML = `
            <button type="button" class="btn btn-sm cdio-add-btn" onclick="addCdioColumn()" title="Add CDIO column" aria-label="Add CDIO column">
                <i data-feather="plus"></i>
            </button>
        `;
        lastCdioHeader.appendChild(addControlsDiv);
    }
    
    // Re-initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Remove corresponding cells from all data rows (row 2 onwards)
    for (let i = 2; i < allRows.length; i++) {
        const row = allRows[i];
        if (row.children[cdioColspan]) {
            row.children[cdioColspan].remove();
        }
    }
    
    // Release the lock
    window.removingCdioColumn = false;
};

/**
 * Add a new SDG column to the mapping table
 * SDG columns are always at the end
 */
window.addSdgColumn = function() {
    const innerTable = document.querySelector('.ilo-cdio-sdg-mapping table.mapping');
    if (!innerTable) return;

    const colgroup = innerTable.querySelector('colgroup');
    const allRows = innerTable.querySelectorAll('tr');
    const headerRow1 = allRows[0];
    const headerRow2 = allRows[1];

    const cdioColspan = parseInt(headerRow1.children[1].getAttribute('colspan')) || 1;
    const sdgColspan = parseInt(headerRow1.children[2].getAttribute('colspan')) || 1;

    // Get the last SDG header cell from row 2
    const lastSdgHeader = headerRow2.lastElementChild;
    
    // Ensure it has the class for identification
    if (!lastSdgHeader.classList.contains('sdg-label-cell')) {
        lastSdgHeader.classList.add('sdg-label-cell');
    }
    
    // Check if we're converting from placeholder (text node "No SDG")
    const isPlaceholder = lastSdgHeader.textContent.includes('No SDG');
    
    // If this is the first additional column (converting from placeholder), convert text to input
    if (isPlaceholder) {
        // Don't add a new col to colgroup - just update width of existing one
        const firstSdgColIndex = 1 + cdioColspan;
        colgroup.children[firstSdgColIndex].style.width = '50%';
        
        // Update SDG header colspan stays at 1 (no change)
        // Update the original header cell to have an input field
        lastSdgHeader.innerHTML = '';
        
        // Add both buttons - it's the only column so it's both first and last
        const removeControlsDiv = document.createElement('div');
        removeControlsDiv.className = 'cdio-header-controls';
        removeControlsDiv.innerHTML = `
            <button type="button" class="btn btn-sm sdg-remove-btn" onclick="removeSdgColumn()" title="Remove SDG column" aria-label="Remove SDG column">
                <i data-feather="minus"></i>
            </button>
        `;
        lastSdgHeader.prepend(removeControlsDiv);
        
        const addControlsDiv = document.createElement('div');
        addControlsDiv.className = 'sdg-header-controls';
        addControlsDiv.innerHTML = `
            <button type="button" class="btn btn-sm sdg-add-btn" onclick="addSdgColumn()" title="Add SDG column" aria-label="Add SDG column">
                <i data-feather="plus"></i>
            </button>
        `;
        lastSdgHeader.appendChild(addControlsDiv);
        
        // Add input field
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control form-control-sm';
        input.placeholder = '-';
        input.style.cssText = 'width:100%; border:none; padding:0.1rem 0.25rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; background:transparent; font-weight:700;';
        lastSdgHeader.appendChild(input);
        
        // Update styling to match input state
        lastSdgHeader.style.fontWeight = '700';
        lastSdgHeader.style.fontStyle = 'normal';
        lastSdgHeader.style.color = '#111';
        
        // Update data row cells to convert placeholder to textarea
        for (let i = 2; i < allRows.length; i++) {
            const row = allRows[i];
            const lastCell = row.lastElementChild;
            lastCell.innerHTML = '';
            lastCell.style.cssText = 'border:none; border-top:1px solid #343a40; border-left:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;';
            
            const textarea = document.createElement('textarea');
            textarea.className = 'form-control form-control-sm';
            textarea.placeholder = '-';
            textarea.rows = 1;
            textarea.style.cssText = 'width:100%; min-height:22px; border:none; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; resize:none; overflow:hidden;';
            textarea.setAttribute('name', 'ilo_sdg_cdio_sdg1_text[]');
            lastCell.appendChild(textarea);
        }
        
        // Re-initialize feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
        
        // Don't add another column - just converted placeholder
        return;
    }
    
    // For additional columns beyond the first:
    // Calculate new column widths
    const newSdgColspan = sdgColspan + 1;
    const sdgWidth = (50 / newSdgColspan).toFixed(4);

    // Update existing SDG column widths
    const firstSdgColIndex = 1 + cdioColspan;
    for (let i = 0; i < sdgColspan; i++) {
        colgroup.children[firstSdgColIndex + i].style.width = `${sdgWidth}%`;
    }

    // Add new col to colgroup (at the end)
    const newCol = document.createElement('col');
    newCol.style.width = `${sdgWidth}%`;
    colgroup.appendChild(newCol);

    // Update SDG header colspan in row 1
    headerRow1.children[2].setAttribute('colspan', newSdgColspan);
    
    // Clone the now-updated last SDG header as template for new column
    const newSdgHeader = lastSdgHeader.cloneNode(true);
    
    // Ensure it has the class for identification
    if (!newSdgHeader.classList.contains('sdg-label-cell')) {
        newSdgHeader.classList.add('sdg-label-cell');
    }
    
    // Remove ALL controls from cloned header
    const controls = newSdgHeader.querySelectorAll('.cdio-header-controls, .sdg-header-controls');
    controls.forEach(ctrl => ctrl.remove());
    
    // Update input placeholder
    const input = newSdgHeader.querySelector('input');
    if (input) {
        input.placeholder = '-';
        input.value = '';
    }
    
    // Get all SDG headers before adding new one
    const allSdgHeaders = Array.from(headerRow2.querySelectorAll('th.sdg-label-cell'));
    
    // Remove ALL controls from all existing SDG columns
    allSdgHeaders.forEach(header => {
        const existingControls = header.querySelectorAll('.cdio-header-controls, .sdg-header-controls');
        existingControls.forEach(ctrl => ctrl.remove());
    });
    
    // Add remove button to the FIRST SDG column (leftmost)
    const firstSdgHeader = allSdgHeaders[0];
    const firstRemoveControlsDiv = document.createElement('div');
    firstRemoveControlsDiv.className = 'cdio-header-controls';
    firstRemoveControlsDiv.innerHTML = `
        <button type="button" class="btn btn-sm sdg-remove-btn" onclick="removeSdgColumn()" title="Remove SDG column" aria-label="Remove SDG column">
            <i data-feather="minus"></i>
        </button>
    `;
    firstSdgHeader.prepend(firstRemoveControlsDiv);
    
    // Add add button to the NEW (last) SDG column
    const addControlsDiv = document.createElement('div');
    addControlsDiv.className = 'sdg-header-controls';
    addControlsDiv.innerHTML = `
        <button type="button" class="btn btn-sm sdg-add-btn" onclick="addSdgColumn()" title="Add SDG column" aria-label="Add SDG column">
            <i data-feather="plus"></i>
        </button>
    `;
    newSdgHeader.appendChild(addControlsDiv);
    
    // Append to row 2
    headerRow2.appendChild(newSdgHeader);
    
    // Re-initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Add cells to all data rows (row 2 onwards)
    for (let i = 2; i < allRows.length; i++) {
        const row = allRows[i];
        const newCell = document.createElement('td');
        
        // Clone the last SDG cell as template
        const lastSdgCell = row.lastElementChild;
        newCell.innerHTML = lastSdgCell.innerHTML;
        
        // Set styling - all SDG columns have border-left for inner separator
        newCell.style.cssText = 'border:none; border-top:1px solid #343a40; border-left:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;';
        
        // Update textarea name for non-disabled cells
        const textarea = newCell.querySelector('textarea');
        if (textarea && !textarea.disabled) {
            textarea.setAttribute('name', `ilo_sdg_cdio_sdg${newSdgColspan}_text[]`);
            textarea.value = '';
        }
        
        row.appendChild(newCell);
    }
};

/**
 * Remove the last SDG column from the mapping table
 */
window.removeSdgColumn = function() {
    // Prevent multiple rapid calls
    if (window.removingSdgColumn) {
        console.log('Remove SDG already in progress, ignoring');
        return;
    }
    window.removingSdgColumn = true;
    
    const innerTable = document.querySelector('.ilo-cdio-sdg-mapping table.mapping');
    if (!innerTable) {
        window.removingSdgColumn = false;
        return;
    }

    const colgroup = innerTable.querySelector('colgroup');
    const allRows = innerTable.querySelectorAll('tr');
    const headerRow1 = allRows[0];
    const headerRow2 = allRows[1];

    const cdioColspan = parseInt(headerRow1.children[1].getAttribute('colspan')) || 1;
    const sdgColspan = parseInt(headerRow1.children[2].getAttribute('colspan')) || 1;
    
    console.log('Remove SDG - Current colspan:', sdgColspan);
    
    // If already at 1 column, convert to placeholder state
    if (sdgColspan <= 1) {
        const sdgHeaders = Array.from(headerRow2.querySelectorAll('th.sdg-label-cell'));
        if (sdgHeaders.length > 0) {
            const singleHeader = sdgHeaders[0];
            
            // Check if it's already a placeholder
            const isPlaceholder = singleHeader.textContent.includes('No SDG');
            if (isPlaceholder) {
                window.removingSdgColumn = false;
                return; // Already at placeholder, can't remove further
            }
            
            // Convert to placeholder
            singleHeader.innerHTML = '';
            
            // Add buttons
            const removeControlsDiv = document.createElement('div');
            removeControlsDiv.className = 'cdio-header-controls';
            removeControlsDiv.innerHTML = `
                <button type="button" class="btn btn-sm sdg-remove-btn" onclick="removeSdgColumn()" title="Remove SDG column" aria-label="Remove SDG column">
                    <i data-feather="minus"></i>
                </button>
            `;
            singleHeader.appendChild(removeControlsDiv);
            
            const addControlsDiv = document.createElement('div');
            addControlsDiv.className = 'sdg-header-controls';
            addControlsDiv.innerHTML = `
                <button type="button" class="btn btn-sm sdg-add-btn" onclick="addSdgColumn()" title="Add SDG column" aria-label="Add SDG column">
                    <i data-feather="plus"></i>
                </button>
            `;
            singleHeader.appendChild(addControlsDiv);
            
            // Add placeholder text
            singleHeader.appendChild(document.createTextNode('No SDG'));
            
            // Update styling to placeholder state
            singleHeader.style.cssText = 'border:none; border-bottom:1px solid #343a40; border-left:1px solid #343a40; min-height:30px; height:30px; padding:0.2rem 0.5rem; font-weight:400; font-style:italic; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#999; text-align:center; position:relative;';
            
            // Update data row cells to placeholder
            for (let i = 2; i < allRows.length; i++) {
                const row = allRows[i];
                const lastCell = row.lastElementChild;
                if (lastCell) {
                    lastCell.innerHTML = '<textarea class="form-control form-control-sm" placeholder="-" rows="1" style="width:100%; min-height:22px; border:none; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; resize:none; overflow:hidden; background-color:#f8f9fa; cursor:not-allowed;" disabled></textarea>';
                    lastCell.style.cssText = 'border:none; border-top:1px solid #343a40; border-left:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle; background-color:#f8f9fa;';
                }
            }
            
            // Re-initialize feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        }
        window.removingSdgColumn = false;
        return;
    }
    
    const newSdgColspan = sdgColspan - 1;
    
    // Find all SDG header cells (they have class 'sdg-label-cell')
    const sdgHeaders = Array.from(headerRow2.querySelectorAll('th.sdg-label-cell'));
    console.log('SDG headers found:', sdgHeaders.length);
    console.log('Will remove last one, new count will be:', newSdgColspan);
    
    // Remove last col from colgroup
    if (colgroup.lastElementChild) {
        colgroup.lastElementChild.remove();
    }
    
    // Update column widths and colspan
    if (newSdgColspan === 1) {
        // Update the remaining SDG column width to 50%
        const firstSdgColIndex = 1 + cdioColspan;
        colgroup.children[firstSdgColIndex].style.width = '50%';
        
        // Keep colspan at 1 in row 1
        headerRow1.children[2].setAttribute('colspan', 1);
        
        // Remove the last SDG header from row 2
        if (sdgHeaders.length > 0) {
            sdgHeaders[sdgHeaders.length - 1].remove();
        }
    } else if (newSdgColspan > 1) {
        // Recalculate SDG column widths for remaining columns
        const sdgWidth = (50 / newSdgColspan).toFixed(4);
        const firstSdgColIndex = 1 + cdioColspan;
        for (let i = 0; i < newSdgColspan; i++) {
            colgroup.children[firstSdgColIndex + i].style.width = `${sdgWidth}%`;
        }

        // Update SDG header colspan in row 1
        headerRow1.children[2].setAttribute('colspan', newSdgColspan);

        // Remove last SDG header from row 2
        if (sdgHeaders.length > 0) {
            sdgHeaders[sdgHeaders.length - 1].remove();
        }
    }

    // Add buttons based on remaining columns
    const remainingSdgHeaders = Array.from(headerRow2.querySelectorAll('th.sdg-label-cell'));
    
    // Remove any existing controls from all headers first
    remainingSdgHeaders.forEach(header => {
        const existingControls = header.querySelectorAll('.cdio-header-controls, .sdg-header-controls');
        existingControls.forEach(ctrl => ctrl.remove());
    });
    
    if (newSdgColspan === 0) {
        // Convert back to "No SDG" placeholder
        const lastSdgHeader = remainingSdgHeaders[0];
        lastSdgHeader.innerHTML = '';
        
        // Add the controls
        const removeControlsDiv = document.createElement('div');
        removeControlsDiv.className = 'cdio-header-controls';
        removeControlsDiv.innerHTML = `
            <button type="button" class="btn btn-sm sdg-remove-btn" onclick="removeSdgColumn()" title="Remove SDG column" aria-label="Remove SDG column">
                <i data-feather="minus"></i>
            </button>
        `;
        lastSdgHeader.appendChild(removeControlsDiv);
        
        const addControlsDiv = document.createElement('div');
        addControlsDiv.className = 'sdg-header-controls';
        addControlsDiv.innerHTML = `
            <button type="button" class="btn btn-sm sdg-add-btn" onclick="addSdgColumn()" title="Add SDG column" aria-label="Add SDG column">
                <i data-feather="plus"></i>
            </button>
        `;
        lastSdgHeader.appendChild(addControlsDiv);
        
        // Add placeholder text
        lastSdgHeader.appendChild(document.createTextNode('No SDG'));
        
        // Update styling to match placeholder state
        lastSdgHeader.style.fontWeight = '400';
        lastSdgHeader.style.fontStyle = 'italic';
        lastSdgHeader.style.color = '#999';
        
        // Update the remaining data row cells to convert back to placeholder
        for (let i = 2; i < allRows.length; i++) {
            const row = allRows[i];
            const lastCell = row.lastElementChild;
            if (lastCell) {
                lastCell.innerHTML = '<span style="color:#999; font-style:italic;">-</span>';
                lastCell.style.cssText = 'border:none; border-top:1px solid #343a40; border-left:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle; background-color:#f8f9fa;';
            }
        }
    } else if (newSdgColspan === 1) {
        // If 1 column remains, it's both first and last - add both buttons
        const singleHeader = remainingSdgHeaders[0];
        
        const removeControlsDiv = document.createElement('div');
        removeControlsDiv.className = 'cdio-header-controls';
        removeControlsDiv.innerHTML = `
            <button type="button" class="btn btn-sm sdg-remove-btn" onclick="removeSdgColumn()" title="Remove SDG column" aria-label="Remove SDG column">
                <i data-feather="minus"></i>
            </button>
        `;
        singleHeader.prepend(removeControlsDiv);
        
        const addControlsDiv = document.createElement('div');
        addControlsDiv.className = 'sdg-header-controls';
        addControlsDiv.innerHTML = `
            <button type="button" class="btn btn-sm sdg-add-btn" onclick="addSdgColumn()" title="Add SDG column" aria-label="Add SDG column">
                <i data-feather="plus"></i>
            </button>
        `;
        singleHeader.appendChild(addControlsDiv);
    } else if (newSdgColspan > 1) {
        // If multiple columns remain, remove on first and add on last
        const firstSdgHeader = remainingSdgHeaders[0];
        const removeControlsDiv = document.createElement('div');
        removeControlsDiv.className = 'cdio-header-controls';
        removeControlsDiv.innerHTML = `
            <button type="button" class="btn btn-sm sdg-remove-btn" onclick="removeSdgColumn()" title="Remove SDG column" aria-label="Remove SDG column">
                <i data-feather="minus"></i>
            </button>
        `;
        firstSdgHeader.prepend(removeControlsDiv);
        
        const lastSdgHeader = remainingSdgHeaders[remainingSdgHeaders.length - 1];
        const addControlsDiv = document.createElement('div');
        addControlsDiv.className = 'sdg-header-controls';
        addControlsDiv.innerHTML = `
            <button type="button" class="btn btn-sm sdg-add-btn" onclick="addSdgColumn()" title="Add SDG column" aria-label="Add SDG column">
                <i data-feather="plus"></i>
            </button>
        `;
        lastSdgHeader.appendChild(addControlsDiv);
    }
    
    // Re-initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Remove last cell from all data rows (row 2 onwards)
    for (let i = 2; i < allRows.length; i++) {
        const row = allRows[i];
        if (row.lastElementChild) {
            row.lastElementChild.remove();
        }
    }
    
    // Release the lock
    window.removingSdgColumn = false;
};

/**
 * Save ILO-CDIO-SDG mapping to the database
 */
window.saveIloCdioSdgMapping = function(showAlert = true) {
    const container = document.querySelector('.ilo-cdio-sdg-mapping');
    if (!container) {
        const error = new Error('ILO-CDIO-SDG mapping container not found.');
        if (showAlert) alert(error.message);
        return Promise.reject(error);
    }

    const mappingTable = container.querySelector('.mapping');
    if (!mappingTable) {
        const error = new Error('Mapping table not found.');
        if (showAlert) alert(error.message);
        return Promise.reject(error);
    }

    const allRows = mappingTable.querySelectorAll('tr');
    console.log('All rows count:', allRows.length);
    
    const headerRow1 = allRows[0]; // Row with CDIO SKILLS | SDG SKILLS
    const headerRow2 = allRows[1]; // Row with column headers
    const noIloRow = allRows[2]; // "No ILO" placeholder row
    
    // Get actual data rows (skip header rows and "No ILO" placeholder)
    const dataRows = Array.from(allRows).slice(2).filter(row => {
        const firstCell = row.querySelector('td');
        if (!firstCell) return false;
        const text = firstCell.textContent.trim();
        console.log('Row text:', text, 'Include:', text !== 'No ILO');
        return text !== 'No ILO';
    });

    console.log('Data rows found:', dataRows.length);

    // Get individual column headers from row 2
    const allColumnHeaders = Array.from(headerRow2.querySelectorAll('th'));
    console.log('Header cells:', allColumnHeaders.length);
    
    // Separate CDIO and SDG headers based on class names
    const cdioHeaderCells = allColumnHeaders.filter(th => th.classList.contains('cdio-label-cell'));
    const sdgHeaderCells = allColumnHeaders.filter(th => th.classList.contains('sdg-label-cell'));
    
    console.log('CDIO headers:', cdioHeaderCells.length);
    console.log('SDG headers:', sdgHeaderCells.length);

    // Build mapping data
    const mappingData = [];

    dataRows.forEach((row, index) => {
        const cells = Array.from(row.querySelectorAll('td'));
        console.log('Processing row', index, 'cells:', cells.length);
        
        // cells[0] = ILO label (has input field), cells[1] = CDIO value, cells[2] = SDG value
        const iloCell = cells[0];
        let iloText = '';
        
        // Try to get from input first
        const iloInput = iloCell?.querySelector('input[name="ilo_sdg_cdio_ilos_text[]"]');
        if (iloInput) {
            iloText = iloInput.value.trim();
        } else {
            // Fallback to text content
            iloText = iloCell?.textContent.trim() || '';
        }
        
        console.log('ILO text:', iloText);

        // Skip placeholder ILO rows
        if (!iloText || iloText === '-' || iloText === 'No ILO') {
            console.log('Skipping empty/placeholder row');
            return;
        }

        // Collect CDIO values as sequential numeric keys (DB expects "1", "2", ...)
        const cdios = {};
        const cdioStartIndex = 1; // first CDIO cell is after ILO cell
        cdioHeaderCells.forEach((headerCell, idx) => {
            const key = String(idx + 1);
            const cdioCell = cells[cdioStartIndex + idx];
            if (!cdioCell) return;
            const textarea = cdioCell.querySelector('textarea');
            if (textarea && !textarea.disabled) {
                const value = textarea.value.trim();
                cdios[key] = value ? value : null; // use null for empty cells
            }
        });

        // Collect SDG values as sequential numeric keys (DB expects "1", "2", ...)
        const sdgs = {};
        const sdgStartIndex = 1 + cdioHeaderCells.length; // SDG cells come after all CDIO cells
        sdgHeaderCells.forEach((headerCell, idx) => {
            const key = String(idx + 1);
            const sdgCell = cells[sdgStartIndex + idx];
            if (!sdgCell) return;
            const textarea = sdgCell.querySelector('textarea');
            if (textarea && !textarea.disabled) {
                const value = textarea.value.trim();
                sdgs[key] = value ? value : null; // use null for empty cells
            }
        });

        mappingData.push({
            ilo_text: iloText,
            cdios: cdios,
            sdgs: sdgs,
            position: index
        });
    });

    console.log('ILO-CDIO-SDG Mapping Data:', {
        dataRowsCount: dataRows.length,
        mappingDataCount: mappingData.length,
        mappingData: mappingData
    });

    // Get syllabus ID from the page context
    const syllabusDoc = document.getElementById('syllabus-document');
    const syllabusId = syllabusDoc ? syllabusDoc.getAttribute('data-syllabus-id') : null;

    if (!syllabusId) {
        const error = new Error('Syllabus ID not found. Please save the syllabus first.');
        if (showAlert) alert(error.message);
        return Promise.reject(error);
    }

    console.log('Sending to server:', {
        syllabus_id: syllabusId,
        mappings: mappingData
    });

    // Send data to server
    return fetch('/faculty/syllabus/save-ilo-cdio-sdg-mapping', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            syllabus_id: syllabusId,
            mappings: mappingData
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || 'Server error');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            if (showAlert) alert('ILO-CDIO-SDG mapping saved successfully!');
            // After successful save, refresh the partial via AJAX to rehydrate state
            try { if (typeof window.ajaxRefreshIloCdioSdgPartial === 'function') window.ajaxRefreshIloCdioSdgPartial(); } catch(_){ }
            return data;
        } else {
            throw new Error(data.message || 'Unknown error');
        }
    })
    .catch(error => {
        console.error('Error saving ILO-CDIO-SDG mapping:', error);
        if (showAlert) alert('Error saving mapping: ' + error.message);
        throw error;
    });
};
