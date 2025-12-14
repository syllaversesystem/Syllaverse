// File: resources/js/faculty/syllabus-ilo-so-cpa.js
// Consolidated ILO-SO-CPA mapping functionality

document.addEventListener('DOMContentLoaded', function() {
	const mapping = document.querySelector('.ilo-so-cpa-mapping');
	if (!mapping) return;

	// Hide partial label text if it overflows
	function checkPartialLabelOverflow() {
		const partialLabel = mapping.querySelector('.partial-label');
		const labelText = partialLabel.querySelector('div');
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

	// Function to check and disable inputs when only placeholders exist
	function updateInputStates() {
		const mappingTable = mapping.querySelector('.mapping');
		const headerRow2 = mappingTable.querySelectorAll('tr')[1];
		const tbody = mappingTable.querySelector('tbody') || mappingTable;
		const dataRows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.querySelector('td'));
		
		// Check if ILO placeholder exists
		const iloPlaceholderExists = dataRows.some(row => {
			const firstCell = row.querySelector('td');
			return firstCell && firstCell.textContent.trim() === 'No ILO';
		});
		
		// Check if SO placeholder exists
		const allHeaders = Array.from(headerRow2.querySelectorAll('th'));
		const soPlaceholderExists = allHeaders.some(th => {
			const input = th.querySelector('input');
			const text = input ? input.value.trim() : th.textContent.trim();
			return text === 'No SO';
		});
		
		// Update all rows
		dataRows.forEach(row => {
			const cells = Array.from(row.querySelectorAll('td'));
			const firstCell = cells[0];
			const isPlaceholderRow = firstCell && firstCell.textContent.trim() === 'No ILO';
			
			// Disable all inputs if ILO placeholder exists
			for (let i = 1; i < cells.length; i++) {
				const textarea = cells[i].querySelector('textarea');
				if (textarea) {
					if (isPlaceholderRow) {
						// Disable all inputs in placeholder row
						textarea.disabled = true;
						textarea.style.backgroundColor = '#f8f9fa';
						textarea.style.cursor = 'not-allowed';
					} else if (soPlaceholderExists && i >= cells.length - 3) {
						// Disable only CPA inputs (last 3 cells) if SO placeholder exists
						textarea.disabled = true;
						textarea.style.backgroundColor = '#f8f9fa';
						textarea.style.cursor = 'not-allowed';
					} else {
						// Enable input
						textarea.disabled = false;
						textarea.style.backgroundColor = '';
						textarea.style.cursor = '';
					}
				}
				
				// Update cell styling
				if (textarea && textarea.disabled) {
					if (i === cells.length - 1) {
						cells[i].style.cssText = 'border:none; border-top:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle; background-color:#f8f9fa;';
					} else {
						cells[i].style.cssText = 'border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle; background-color:#f8f9fa;';
					}
				} else {
					if (i === cells.length - 1) {
						cells[i].style.cssText = 'border:none; border-top:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;';
					} else {
						cells[i].style.cssText = 'border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;';
					}
				}
			}
		});
	}

	// Auto-resize textareas
	function autoResize(textarea) {
		textarea.style.height = 'auto';
		textarea.style.height = textarea.scrollHeight + 'px';
	}

	// Apply to all textareas in the mapping table
	const textareas = mapping.querySelectorAll('textarea');
	textareas.forEach(textarea => {
		// Initial resize
		autoResize(textarea);
		
		// Resize on input
		textarea.addEventListener('input', function() {
			autoResize(this);
		});
	});
	
	// Add row function
	window.addIloRow = function() {
		const mappingTable = mapping.querySelector('.mapping');
		const tbody = mappingTable.querySelector('tbody') || mappingTable;
		const rows = tbody.querySelectorAll('tr');
		const dataRows = Array.from(rows).filter(row => row.querySelector('td'));
		const lastRow = dataRows[dataRows.length - 1];
		
		if (!lastRow) return;
		
		// Check if placeholder exists
		const firstCell = lastRow.querySelector('td');
		const isPlaceholder = firstCell && firstCell.textContent.trim() === 'No ILO';
		
		if (isPlaceholder) {
			// Replace placeholder with blank input
			firstCell.innerHTML = '';
			const iloInput = document.createElement('input');
			iloInput.type = 'text';
			iloInput.value = '';
			iloInput.placeholder = '-';
			iloInput.className = 'form-control form-control-sm';
			iloInput.style.cssText = 'width:100%; border:none; padding:0.1rem 0.25rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; background:transparent;';
			// Auto-format ILO number
			iloInput.addEventListener('input', function(e) {
				const value = e.target.value;
				if (/^\d+$/.test(value)) {
					e.target.value = 'ILO' + value;
				}
			});
			firstCell.appendChild(iloInput);
			firstCell.style.cssText = 'border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.1rem 0.25rem; font-family:Georgia, serif; font-size:13px; color:#000; text-align:center; vertical-align:middle;';
			
			// Re-enable all textareas (SO and CPA)
			const cells = lastRow.querySelectorAll('td');
			for (let i = 1; i < cells.length; i++) {
				const textarea = cells[i].querySelector('textarea');
				if (textarea) {
					textarea.disabled = false;
					textarea.style.backgroundColor = '';
					textarea.style.cursor = '';
				}
				if (i === cells.length - 1) {
					cells[i].style.cssText = 'border:none; border-top:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;';
				} else {
					cells[i].style.cssText = 'border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;';
				}
			}
			return;
		}
		
		const newRow = lastRow.cloneNode(true);
		const iloCell = newRow.querySelector('td:first-child');
		
		// Create or update input for ILO
		const existingInput = iloCell.querySelector('input');
		if (existingInput) {
			existingInput.value = '';
			// Re-attach auto-format listener
			const newInput = existingInput.cloneNode(true);
			newInput.addEventListener('input', function(e) {
				const value = e.target.value;
				if (/^\d+$/.test(value)) {
					e.target.value = 'ILO' + value;
				}
			});
			iloCell.replaceChild(newInput, existingInput);
		} else {
			iloCell.innerHTML = '';
			const iloInput = document.createElement('input');
			iloInput.type = 'text';
			iloInput.value = '';
			iloInput.placeholder = '-';
			iloInput.className = 'form-control form-control-sm';
			iloInput.style.cssText = 'width:100%; border:none; padding:0.1rem 0.25rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; background:transparent;';
			// Auto-format ILO number
			iloInput.addEventListener('input', function(e) {
				const value = e.target.value;
				if (/^\d+$/.test(value)) {
					e.target.value = 'ILO' + value;
				}
			});
			iloCell.appendChild(iloInput);
		}
		iloCell.style.cssText = 'border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.1rem 0.25rem; font-family:Georgia, serif; font-size:13px; color:#000; text-align:center; vertical-align:middle;';
		
		// Update all cell borders in new row
		const newCells = newRow.querySelectorAll('td');
		for (let i = 1; i < newCells.length; i++) {
			if (i === newCells.length - 1) {
				newCells[i].style.cssText = 'border:none; border-top:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;';
			} else {
				newCells[i].style.cssText = 'border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;';
			}
		}
		
		// Clear all textareas
		const newTextareas = newRow.querySelectorAll('textarea');
		newTextareas.forEach(textarea => {
			textarea.value = '';
			textarea.addEventListener('input', function() {
				autoResize(this);
			});
			autoResize(textarea);
		});
		
		tbody.appendChild(newRow);
		checkPartialLabelOverflow();
		updateInputStates();
	};
	
	// Remove row function
	window.removeIloRow = function() {
		const mappingTable = mapping.querySelector('.mapping');
		const tbody = mappingTable.querySelector('tbody') || mappingTable;
		const rows = tbody.querySelectorAll('tr');
		const dataRows = Array.from(rows).filter(row => row.querySelector('td'));
		
		// Check if placeholder already exists
		const placeholderExists = dataRows.some(row => {
			const firstCell = row.querySelector('td');
			return firstCell && firstCell.textContent.trim() === 'No ILO';
		});
		
		if (placeholderExists) {
			return;
		}
		
		// If last real row: convert to placeholder
		if (dataRows.length <= 1) {
			const lastRow = dataRows[0];
			const cells = lastRow.querySelectorAll('td');
			
			// Change first cell to "No ILO" placeholder
			cells[0].textContent = 'No ILO';
			cells[0].style.cssText = 'border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; font-family:Georgia, serif; font-size:13px; text-align:center; vertical-align:middle; color:#999; font-style:italic;';
			
			// Keep all textareas enabled even when ILO is placeholder
			for (let i = 1; i < cells.length; i++) {
				const textarea = cells[i].querySelector('textarea');
				if (textarea) {
					textarea.disabled = false;
					textarea.value = '';
					textarea.style.backgroundColor = '';
					textarea.style.cursor = '';
				}
				if (i === cells.length - 1) {
					cells[i].style.cssText = 'border:none; border-top:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;';
				} else {
					cells[i].style.cssText = 'border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;';
				}
			}
			return;
		}
		
		const lastRow = dataRows[dataRows.length - 1];
		lastRow.remove();
		checkPartialLabelOverflow();
		updateInputStates();
	};
	
	// Add SO column function
	window.addSoColumn = function() {
		const mappingTable = mapping.querySelector('.mapping');
		const colgroup = mappingTable.querySelector('colgroup');
		const headerRow1 = mappingTable.querySelectorAll('tr')[0];
		const headerRow2 = mappingTable.querySelectorAll('tr')[1];
		const tbody = mappingTable.querySelector('tbody') || mappingTable;
		const dataRows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.querySelector('td'));
		
		// Count current SO columns (all headers between ILOs and C/P/A)
		const allHeaders = Array.from(headerRow2.querySelectorAll('th'));
		const soHeaders = allHeaders.filter(th => {
			const input = th.querySelector('input');
			const text = input ? input.value.trim() : th.textContent.trim();
			return (text.startsWith('SO') || text === 'No SO') && text !== 'STUDENT OUTCOMES (SO): Mapping of Assessment Tasks (AT)';
		});
		
		// Check if placeholder exists
		const placeholderHeader = soHeaders.find(th => {
			const input = th.querySelector('input');
			const text = input ? input.value.trim() : th.textContent.trim();
			return text === 'No SO';
		});
		
		if (placeholderHeader) {
			// Replace placeholder with blank SO input and keep buttons
			const existingControls = placeholderHeader.querySelector('.so-header-controls');
			placeholderHeader.textContent = ''; // Clear all content
			
			// Re-add controls if they exist
			if (existingControls) {
				placeholderHeader.appendChild(existingControls);
			}
			
			// Add blank input for SO
			const soInput = document.createElement('input');
			soInput.type = 'text';
			soInput.value = '';
			soInput.placeholder = '-';
			soInput.className = 'form-control form-control-sm';
			soInput.style.cssText = 'width:100%; border:none; padding:0.1rem 0.25rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; background:transparent; font-weight:700;';
			// Auto-format SO number
			soInput.addEventListener('input', function(e) {
				const value = e.target.value;
				if (/^\d+$/.test(value)) {
					e.target.value = 'SO' + value;
				}
			});
			placeholderHeader.appendChild(soInput);
			
			placeholderHeader.style.cssText = 'border:none; border-bottom:1px solid #343a40; border-right:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center; position:relative;';
			
			// Replace placeholder cells with textarea and re-enable CPA inputs in each data row
			dataRows.forEach(row => {
				const cells = Array.from(row.querySelectorAll('td'));
				// Last SO cell is at index length - 4 (before C, P, A)
				const lastSoIndex = cells.length - 4;
				const soCell = cells[lastSoIndex];
				if (soCell && (soCell.textContent.trim() === '-' || soCell.querySelector('span'))) {
					soCell.style.cssText = 'border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;';
					const newTextarea = document.createElement('textarea');
					newTextarea.className = 'form-control form-control-sm';
					newTextarea.placeholder = '-';
					newTextarea.rows = 1;
					newTextarea.style.cssText = 'width:100%; min-height:22px; border:none; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; resize:none; overflow:hidden;';
					newTextarea.addEventListener('input', function() {
						autoResize(this);
					});
					soCell.innerHTML = '';
					soCell.appendChild(newTextarea);
				}
				
				// CPA inputs remain enabled (no need to change their state)
			});
			return;
		}
		
		// Add column to colgroup (before C, P, A columns)
		const newCol = document.createElement('col');
		colgroup.insertBefore(newCol, colgroup.children[colgroup.children.length - 3]);
		
		// Update header colspan
		const soHeaderSpan = headerRow1.querySelectorAll('th')[1];
		soHeaderSpan.setAttribute('colspan', parseInt(soHeaderSpan.getAttribute('colspan')) + 1);
		
		// Add new SO header in row 2 (before C header)
		const cHeader = allHeaders.find(th => th.textContent.trim() === 'C');
		const newSoHeader = document.createElement('th');
		newSoHeader.style.cssText = 'border:none; border-bottom:1px solid #343a40; border-right:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center;';
		
		// Create blank input for SO header
		const soInput = document.createElement('input');
		soInput.type = 'text';
		soInput.value = '';
		soInput.placeholder = '-';
		soInput.className = 'form-control form-control-sm';
		soInput.style.cssText = 'width:100%; border:none; padding:0.1rem 0.25rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; background:transparent; font-weight:700;';
		// Auto-format SO number
		soInput.addEventListener('input', function(e) {
			const value = e.target.value;
			if (/^\d+$/.test(value)) {
				e.target.value = 'SO' + value;
			}
		});
		newSoHeader.appendChild(soInput);
		
		headerRow2.insertBefore(newSoHeader, cHeader);
		
		// Add new SO cell to each data row (before C cell)
		dataRows.forEach(row => {
			const cells = row.querySelectorAll('td');
			const cCell = Array.from(cells).find(cell => {
				const textarea = cell.querySelector('textarea');
				return textarea && cells[cells.length - 3] === cell;
			}) || cells[cells.length - 3];
			
			const newCell = document.createElement('td');
			newCell.style.cssText = 'border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;';
			const newTextarea = document.createElement('textarea');
			newTextarea.className = 'form-control form-control-sm';
			newTextarea.placeholder = '-';
			newTextarea.rows = 1;
			newTextarea.style.cssText = 'width:100%; min-height:22px; border:none; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; resize:none; overflow:hidden;';
			newTextarea.addEventListener('input', function() {
				autoResize(this);
			});
			newCell.appendChild(newTextarea);
			row.insertBefore(newCell, cCell);
		});
		updateInputStates();
	};
	
	// Remove SO column function
	window.removeSoColumn = function() {
		const mappingTable = mapping.querySelector('.mapping');
		const colgroup = mappingTable.querySelector('colgroup');
		const headerRow1 = mappingTable.querySelectorAll('tr')[0];
		const headerRow2 = mappingTable.querySelectorAll('tr')[1];
		const tbody = mappingTable.querySelector('tbody') || mappingTable;
		const dataRows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.querySelector('td'));
		
		// Count current SO columns - identify by position (between ILOs and C/P/A headers)
		const allHeaders = Array.from(headerRow2.querySelectorAll('th'));
		// Find ILOs header and C header to identify SO columns in between
		const iloHeaderIndex = allHeaders.findIndex(th => th.textContent.includes('ILOs'));
		const cHeaderIndex = allHeaders.findIndex(th => th.textContent.trim() === 'C');
		
		// SO headers are between ILOs and C
		const soHeaders = allHeaders.slice(iloHeaderIndex + 1, cHeaderIndex);
		
		// Check if already showing placeholder
		const placeholderExists = soHeaders.some(th => {
			const input = th.querySelector('input');
			const text = input ? input.value.trim() : th.textContent.trim();
			return text === 'No SO';
		});
		
		if (placeholderExists) {
			// Already at placeholder, can't remove further
			return;
		}
		
		if (soHeaders.length <= 1) {
			// If removing the last SO column, add a placeholder column
			// Don't remove from colgroup - keep the column structure
			
			// Don't update header colspan - keep the same span
			
			// Replace last SO header with placeholder but keep buttons
			const lastSoHeader = soHeaders[soHeaders.length - 1];
			const existingControls = lastSoHeader.querySelector('.so-header-controls');
			
			lastSoHeader.innerHTML = ''; // Clear all content
			
			// Re-add controls if they exist
			if (existingControls) {
				lastSoHeader.appendChild(existingControls);
			}
			
			// Add text node for placeholder
			const textNode = document.createTextNode('No SO');
			lastSoHeader.appendChild(textNode);
			
			lastSoHeader.style.cssText = 'border:none; border-bottom:1px solid #343a40; border-right:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:400; font-style:italic; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#999; text-align:center; position:relative;';
			
			// Replace SO cells with placeholder and disable CPA inputs in each data row
			dataRows.forEach(row => {
				const cells = Array.from(row.querySelectorAll('td'));
				// Last SO cell is at index length - 4 (before C, P, A)
				const lastSoIndex = cells.length - 4;
				const soCell = cells[lastSoIndex];
				if (soCell) {
					soCell.innerHTML = '<span style="color:#999; font-style:italic;">-</span>';
					soCell.style.cssText = 'border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle; background:#f9f9f9;';
				}
				
				// CPA inputs remain enabled (no need to disable)
			});
			return;
		}
		
		// Remove last SO column from colgroup
		colgroup.removeChild(colgroup.children[colgroup.children.length - 4]);
		
		// Update header colspan
		const soHeaderSpan = headerRow1.querySelectorAll('th')[1];
		soHeaderSpan.setAttribute('colspan', parseInt(soHeaderSpan.getAttribute('colspan')) - 1);
		
		// Remove last SO header
		const lastSoHeader = soHeaders[soHeaders.length - 1];
		lastSoHeader.remove();
		
		// Remove last SO cell from each data row
		dataRows.forEach(row => {
			const cells = Array.from(row.querySelectorAll('td'));
			// ILO cell is first (index 0)
			// SO cells are from index 1 to length - 4 (C, P, A are last 3)
			// So last SO cell is at index length - 4
			const lastSoIndex = cells.length - 4;
			if (lastSoIndex > 0 && cells[lastSoIndex]) {
				cells[lastSoIndex].remove();
			}
		});
	};
	
	// Save ILO-SO-CPA mapping function
	window.saveIloSoCpaMapping = function(showAlert = true) {
		const mappingTable = mapping.querySelector('.mapping');
		const headerRow2 = mappingTable.querySelectorAll('tr')[1];
		const tbody = mappingTable.querySelector('tbody') || mappingTable;
		const dataRows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.querySelector('td'));
		
		// Get all SO headers (excluding ILOs and C/P/A)
		const allHeaders = Array.from(headerRow2.querySelectorAll('th'));
		const iloHeaderIndex = allHeaders.findIndex(th => th.textContent.includes('ILOs'));
		const cHeaderIndex = allHeaders.findIndex(th => th.textContent.trim() === 'C');
		const soHeaders = allHeaders.slice(iloHeaderIndex + 1, cHeaderIndex);
		
		// Collect SO column labels
		const soColumns = [];
		soHeaders.forEach(th => {
			const input = th.querySelector('input');
			const label = input ? input.value.trim() : th.textContent.trim();
			if (label && label !== 'No SO') {
				soColumns.push(label);
			}
		});
		
		// Build data array - always send data (even if empty) to allow deletion
		const mappingData = [];
		
		dataRows.forEach((row, index) => {
			const cells = Array.from(row.querySelectorAll('td'));
			const iloInput = cells[0].querySelector('input');
			const iloText = iloInput ? iloInput.value.trim() : cells[0].textContent.trim();
			
			// Skip only placeholder rows - allow empty rows to be saved
			if (iloText === 'No ILO') return;
			
			// Collect SO values as object with column labels as keys
			const sos = {};
			soHeaders.forEach((header, soIndex) => {
				const input = header.querySelector('input');
				const soLabel = input ? input.value.trim() : header.textContent.trim();
				// Skip "No SO" placeholder but allow empty SO labels to be saved
				if (soLabel === 'No SO') return;
				
				const soCell = cells[soIndex + 1]; // +1 because first cell is ILO
				if (soCell) {
					const textarea = soCell.querySelector('textarea');
					if (textarea) {
						// Save the value even if empty
						sos[soLabel || ''] = textarea.value.trim();
					}
				}
			});
			
			// Get C, P, A values (last 3 cells) - save even if empty
			const cCell = cells[cells.length - 3];
			const pCell = cells[cells.length - 2];
			const aCell = cells[cells.length - 1];
			
			const cValue = cCell ? (cCell.querySelector('textarea')?.value.trim() || '') : '';
			const pValue = pCell ? (pCell.querySelector('textarea')?.value.trim() || '') : '';
			const aValue = aCell ? (aCell.querySelector('textarea')?.value.trim() || '') : '';
			
			mappingData.push({
				ilo_text: iloText || '', // Allow empty ILO text
				sos: sos,
				c: cValue,
				p: pValue,
				a: aValue,
				position: index
			});
		});
		
		// Get syllabus_id from the page context
		const syllabusDoc = document.getElementById('syllabus-document');
		const syllabusId = syllabusDoc ? syllabusDoc.getAttribute('data-syllabus-id') : null;
		
		if (!syllabusId) {
			const error = new Error('Syllabus ID not found. Please save the syllabus first.');
			if (showAlert) alert(error.message);
			return Promise.reject(error);
		}
		
		// Send data to server and return Promise
		return fetch('/faculty/syllabus/save-ilo-so-cpa-mapping', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
			},
			body: JSON.stringify({
				syllabus_id: syllabusId,
				so_columns: soColumns,
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
				if (showAlert) alert('ILO-SO-CPA mapping saved successfully!');
				return data;
			} else {
				throw new Error(data.message || 'Unknown error');
			}
		})
		.catch(error => {
			console.error('Error:', error);
			if (showAlert) alert('Error saving mapping: ' + error.message);
			throw error;
		});
	};

	// Load saved data on page load
	function loadSavedData() {
		const soColumnsData = mapping.getAttribute('data-so-columns');
		const mappingsData = mapping.getAttribute('data-mappings');
		
		if (!soColumnsData || !mappingsData) return;
		
		try {
			const soColumns = JSON.parse(soColumnsData);
			const mappings = JSON.parse(mappingsData);
			
			if (!mappings || mappings.length === 0) return;
			
			// First, add SO columns
			if (soColumns && soColumns.length > 0) {
				// Remove placeholder and add columns
				soColumns.forEach((soLabel, index) => {
					if (index === 0) {
						// First SO - convert placeholder
						addSoColumn();
						// Update the label
						const mappingTable = mapping.querySelector('.mapping');
						const headerRow2 = mappingTable.querySelectorAll('tr')[1];
						const allHeaders = Array.from(headerRow2.querySelectorAll('th'));
						const iloHeaderIndex = allHeaders.findIndex(th => th.textContent.includes('ILOs'));
						const cHeaderIndex = allHeaders.findIndex(th => th.textContent.trim() === 'C');
						const firstSoHeader = allHeaders.slice(iloHeaderIndex + 1, cHeaderIndex)[0];
						if (firstSoHeader) {
							const input = firstSoHeader.querySelector('input');
							if (input) input.value = soLabel;
						}
					} else {
						// Additional SO columns
						addSoColumn();
						// Update the label
						const mappingTable = mapping.querySelector('.mapping');
						const headerRow2 = mappingTable.querySelectorAll('tr')[1];
						const allHeaders = Array.from(headerRow2.querySelectorAll('th'));
						const iloHeaderIndex = allHeaders.findIndex(th => th.textContent.includes('ILOs'));
						const cHeaderIndex = allHeaders.findIndex(th => th.textContent.trim() === 'C');
						const soHeaders = allHeaders.slice(iloHeaderIndex + 1, cHeaderIndex);
						const currentSoHeader = soHeaders[index];
						if (currentSoHeader) {
							const input = currentSoHeader.querySelector('input');
							if (input) input.value = soLabel;
						}
					}
				});
			}
			
			// Then, populate ILO rows
			mappings.forEach((mappingRow, rowIndex) => {
				if (rowIndex === 0) {
					// First row - convert placeholder
					addIloRow();
					const mappingTable = mapping.querySelector('.mapping');
					const tbody = mappingTable.querySelector('tbody') || mappingTable;
					const dataRows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.querySelector('td'));
					const firstRow = dataRows[0];
					if (firstRow) {
						const cells = Array.from(firstRow.querySelectorAll('td'));
						// Set ILO label
						const iloInput = cells[0].querySelector('input');
						if (iloInput) iloInput.value = mappingRow.ilo_text;
						
						// Set SO values
						if (mappingRow.sos && typeof mappingRow.sos === 'object') {
							Object.entries(mappingRow.sos).forEach(([soLabel, soValue], soIndex) => {
								const soCell = cells[soIndex + 1];
								if (soCell) {
									const textarea = soCell.querySelector('textarea');
									if (textarea) {
										textarea.value = soValue;
										autoResize(textarea);
									}
								}
							});
						}
						
						// Set C, P, A values
						const cCell = cells[cells.length - 3];
						const pCell = cells[cells.length - 2];
						const aCell = cells[cells.length - 1];
						if (cCell && mappingRow.c) {
							const textarea = cCell.querySelector('textarea');
							if (textarea) {
								textarea.value = mappingRow.c;
								autoResize(textarea);
							}
						}
						if (pCell && mappingRow.p) {
							const textarea = pCell.querySelector('textarea');
							if (textarea) {
								textarea.value = mappingRow.p;
								autoResize(textarea);
							}
						}
						if (aCell && mappingRow.a) {
							const textarea = aCell.querySelector('textarea');
							if (textarea) {
								textarea.value = mappingRow.a;
								autoResize(textarea);
							}
						}
					}
				} else {
					// Additional rows
					addIloRow();
					const mappingTable = mapping.querySelector('.mapping');
					const tbody = mappingTable.querySelector('tbody') || mappingTable;
					const dataRows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.querySelector('td'));
					const currentRow = dataRows[rowIndex];
					if (currentRow) {
						const cells = Array.from(currentRow.querySelectorAll('td'));
						// Set ILO label
						const iloInput = cells[0].querySelector('input');
						if (iloInput) iloInput.value = mappingRow.ilo_text;
						
						// Set SO values
						if (mappingRow.sos && typeof mappingRow.sos === 'object') {
							Object.entries(mappingRow.sos).forEach(([soLabel, soValue], soIndex) => {
								const soCell = cells[soIndex + 1];
								if (soCell) {
									const textarea = soCell.querySelector('textarea');
									if (textarea) {
										textarea.value = soValue;
										autoResize(textarea);
									}
								}
							});
						}
						
						// Set C, P, A values
						const cCell = cells[cells.length - 3];
						const pCell = cells[cells.length - 2];
						const aCell = cells[cells.length - 1];
						if (cCell && mappingRow.c) {
							const textarea = cCell.querySelector('textarea');
							if (textarea) {
								textarea.value = mappingRow.c;
								autoResize(textarea);
							}
						}
						if (pCell && mappingRow.p) {
							const textarea = pCell.querySelector('textarea');
							if (textarea) {
								textarea.value = mappingRow.p;
								autoResize(textarea);
							}
						}
						if (aCell && mappingRow.a) {
							const textarea = aCell.querySelector('textarea');
							if (textarea) {
								textarea.value = mappingRow.a;
								autoResize(textarea);
							}
						}
					}
				}
			});
		} catch (e) {
			console.error('Error loading saved ILO-SO-CPA data:', e);
		}
	}
	
	// Load data after a short delay to ensure DOM is fully ready
	setTimeout(() => {
		loadSavedData();
		updateInputStates();
	}, 100);
	
	// Legacy function for compatibility with existing save flow
	window.saveIloSoCpa = async function() {
		const form = document.getElementById('syllabusForm');
		if (!form) return { message: 'No syllabus form present' };
		const mappingRoot = document.querySelector('.ilo-so-cpa-mapping');
		if (!mappingRoot) return { message: 'No mapping partial present' };
		
		// Just call the main save function and return a success message
		try {
			saveIloSoCpaMapping();
			return { message: 'IloSoCpa save initiated' };
		} catch (err) {
			console.error('saveIloSoCpa failed', err);
			throw err;
		}
	};

	// Refresh function: rebuild partial from provided data (called after AI insert)
	window.refreshIloSoCpaPartial = function(soColumns, mappings){
		try {
			mapping.setAttribute('data-so-columns', JSON.stringify(soColumns || []));
			mapping.setAttribute('data-mappings', JSON.stringify(mappings || []));
			// Reset table to initial state by reloading from attributes
			// Remove existing dynamic rows beyond first, convert first to placeholder
			const mappingTable = mapping.querySelector('.mapping');
			const tbody = mappingTable.querySelector('tbody') || mappingTable;
			const dataRows = Array.from(tbody.querySelectorAll('tr')).filter(r => r.querySelector('td'));
			if (dataRows.length) {
				const firstRow = dataRows[0];
				const cells = Array.from(firstRow.querySelectorAll('td'));
				cells[0].textContent = 'No ILO';
				for (let i = 1; i < cells.length; i++) {
					const ta = cells[i].querySelector('textarea');
					if (ta) { ta.disabled = true; ta.value = ''; ta.style.backgroundColor = '#f8f9fa'; ta.style.cursor = 'not-allowed'; }
				}
				for (let i = 1; i < dataRows.length; i++) { dataRows[i].remove(); }
			}
			// Reset SO headers to single placeholder by removing extra SO headers
			const headerRow2 = mappingTable.querySelectorAll('tr')[1];
			const allHeaders = Array.from(headerRow2.querySelectorAll('th'));
			const iloHeaderIndex = allHeaders.findIndex(th => th.textContent.includes('ILOs'));
			const cHeaderIndex = allHeaders.findIndex(th => th.textContent.trim() === 'C');
			const soHeaders = allHeaders.slice(iloHeaderIndex + 1, cHeaderIndex);
			for (let i = 1; i < soHeaders.length; i++) soHeaders[i].remove();
			const firstSoHeader = soHeaders[0];
			if (firstSoHeader) {
				firstSoHeader.innerHTML = '<div class="so-header-controls"><button type="button" class="btn btn-sm so-remove-btn" onclick="removeSoColumn()" title="Remove SO column" aria-label="Remove SO column"><i data-feather="minus"></i></button></div>No SO';
			}
			// Now load from updated attributes
			loadSavedData();
			updateInputStates();
			return true;
		} catch(e){ console.error('refreshIloSoCpaPartial failed', e); return false; }
	};
});
