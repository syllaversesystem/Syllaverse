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
			// Replace placeholder with ILO1
			firstCell.textContent = 'ILO1';
			firstCell.style.cssText = 'border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; font-family:Georgia, serif; font-size:13px; color:#000; text-align:center; vertical-align:middle;';
			
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
			// Right border already handled above
			
			// Check if SO placeholder exists, if so disable CPA inputs only
			const headerRow2 = mappingTable.querySelectorAll('tr')[1];
			const allHeaders = Array.from(headerRow2.querySelectorAll('th'));
			const soPlaceholderExists = allHeaders.some(th => th.textContent.trim() === 'No SO');
			
			if (soPlaceholderExists) {
				// Disable only CPA inputs (last 3 cells)
				for (let i = cells.length - 3; i < cells.length; i++) {
					const textarea = cells[i].querySelector('textarea');
					if (textarea) {
						textarea.disabled = true;
						textarea.style.backgroundColor = '#f8f9fa';
						textarea.style.cursor = 'not-allowed';
					}
					if (i === cells.length - 1) {
						cells[i].style.cssText = 'border:none; border-top:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle; background-color:#f8f9fa;';
					} else {
						cells[i].style.cssText = 'border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle; background-color:#f8f9fa;';
					}
				}
			}
			return;
		}
		
		const newRow = lastRow.cloneNode(true);
		const iloCell = newRow.querySelector('td:first-child');
		const iloNumber = dataRows.length + 1;
		iloCell.textContent = 'ILO' + iloNumber;
		iloCell.style.cssText = 'border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; font-family:Georgia, serif; font-size:13px; color:#000; text-align:center; vertical-align:middle;';
		
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
			alert('No ILO rows to remove');
			return;
		}
		
		// If last real row: convert to placeholder
		if (dataRows.length <= 1) {
			const lastRow = dataRows[0];
			const cells = lastRow.querySelectorAll('td');
			
			// Change first cell to "No ILO" placeholder
			cells[0].textContent = 'No ILO';
			cells[0].style.cssText = 'border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; font-family:Georgia, serif; font-size:13px; text-align:center; vertical-align:middle; color:#999; font-style:italic;';
			
			// Disable all textareas (SO and CPA) when no ILO exists
			for (let i = 1; i < cells.length; i++) {
				const textarea = cells[i].querySelector('textarea');
				if (textarea) {
					textarea.disabled = true;
					textarea.value = '';
					textarea.style.backgroundColor = '#f8f9fa';
					textarea.style.cursor = 'not-allowed';
				}
				if (i === cells.length - 1) {
					cells[i].style.cssText = 'border:none; border-top:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle; background-color:#f8f9fa;';
				} else {
					cells[i].style.cssText = 'border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle; background-color:#f8f9fa;';
				}
			}
			return;
		}
		
		const lastRow = dataRows[dataRows.length - 1];
		lastRow.remove();
		checkPartialLabelOverflow();
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
			const text = th.textContent.trim();
			return (text.startsWith('SO') || text === 'No SO') && text !== 'STUDENT OUTCOMES (SO): Mapping of Assessment Tasks (AT)';
		});
		
		// Check if placeholder exists
		const placeholderHeader = soHeaders.find(th => th.textContent.trim() === 'No SO');
		
		if (placeholderHeader) {
			// Replace placeholder with SO1
			placeholderHeader.textContent = 'SO1';
			placeholderHeader.style.cssText = 'border:none; border-bottom:1px solid #343a40; border-right:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center;';
			
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
				
				// Re-enable CPA inputs (last 3 cells)
				for (let i = cells.length - 3; i < cells.length; i++) {
					const textarea = cells[i].querySelector('textarea');
					if (textarea) {
						textarea.disabled = false;
						textarea.style.backgroundColor = '';
						textarea.style.cursor = '';
					}
					// Remove right border from last cell
					if (i === cells.length - 1) {
						cells[i].style.cssText = 'border:none; border-top:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;';
					} else {
						cells[i].style.cssText = 'border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;';
					}
				}
			});
			return;
		}
		
		// Find the highest SO number
		let maxSoNum = 0;
		soHeaders.forEach(header => {
			const match = header.textContent.match(/SO(\d+)/);
			if (match) {
				const num = parseInt(match[1]);
				if (num > maxSoNum) maxSoNum = num;
			}
		});
		const newSoNumber = maxSoNum + 1;
		
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
		newSoHeader.textContent = 'SO' + newSoNumber;
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
	};
	
	// Remove SO column function
	window.removeSoColumn = function() {
		const mappingTable = mapping.querySelector('.mapping');
		const colgroup = mappingTable.querySelector('colgroup');
		const headerRow1 = mappingTable.querySelectorAll('tr')[0];
		const headerRow2 = mappingTable.querySelectorAll('tr')[1];
		const tbody = mappingTable.querySelector('tbody') || mappingTable;
		const dataRows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.querySelector('td'));
		
		// Count current SO columns (filter properly like in addSoColumn)
		const allHeaders = Array.from(headerRow2.querySelectorAll('th'));
		const soHeaders = allHeaders.filter(th => {
			const text = th.textContent.trim();
			return text.startsWith('SO') && text !== 'STUDENT OUTCOMES (SO): Mapping of Assessment Tasks (AT)';
		});
		
		// Check if already showing placeholder
		const placeholderExists = soHeaders.some(th => th.textContent.trim() === 'No SO');
		
		if (placeholderExists) {
			// Already at placeholder, can't remove further
			alert('No SO columns to remove');
			return;
		}
		
		if (soHeaders.length <= 1) {
			// If removing the last SO column, add a placeholder column
			// Don't remove from colgroup - keep the column structure
			
			// Don't update header colspan - keep the same span
			
			// Replace last SO header with placeholder
			const lastSoHeader = soHeaders[soHeaders.length - 1];
			lastSoHeader.textContent = 'No SO';
			lastSoHeader.style.cssText = 'border:none; border-bottom:1px solid #343a40; border-right:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:400; font-style:italic; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#999; text-align:center;';
			
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
				
				// Disable CPA inputs (last 3 cells)
				for (let i = cells.length - 3; i < cells.length; i++) {
					const textarea = cells[i].querySelector('textarea');
					if (textarea) {
						textarea.disabled = true;
						textarea.value = '';
						textarea.style.backgroundColor = '#f8f9fa';
						textarea.style.cursor = 'not-allowed';
					}
					if (i === cells.length - 1) {
						cells[i].style.cssText = 'border:none; border-top:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle; background-color:#f8f9fa;';
					} else {
						cells[i].style.cssText = 'border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle; background-color:#f8f9fa;';
					}
				}
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
	window.saveIloSoCpaMapping = function() {
		const mappingTable = mapping.querySelector('.mapping');
		const headerRow2 = mappingTable.querySelectorAll('tr')[1];
		const tbody = mappingTable.querySelector('tbody') || mappingTable;
		const dataRows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.querySelector('td'));
		
		// Get all SO headers (excluding ILOs and C/P/A)
		const allHeaders = Array.from(headerRow2.querySelectorAll('th'));
		const soHeaders = allHeaders.filter(th => {
			const text = th.textContent.trim();
			return (text.startsWith('SO') || text === 'No SO') && text !== 'STUDENT OUTCOMES (SO): Mapping of Assessment Tasks (AT)';
		});
		
		// Build data array - always send data (even if empty) to allow deletion
		const mappingData = [];
		
		dataRows.forEach((row, index) => {
			const cells = Array.from(row.querySelectorAll('td'));
			const iloText = cells[0].textContent.trim();
			
			// Skip placeholder rows - this will result in empty array if only placeholder exists
			if (iloText === 'No ILO') return;
			
			// Collect SO values as simple array
			const sos = [];
			soHeaders.forEach((header, soIndex) => {
				const soCode = header.textContent.trim();
				// Skip "No SO" placeholder
				if (soCode === 'No SO') return;
				
				const soCell = cells[soIndex + 1]; // +1 because first cell is ILO
				if (soCell) {
					const textarea = soCell.querySelector('textarea');
					if (textarea) {
						sos.push(textarea.value.trim());
					}
				}
			});
			
			// Get C, P, A values (last 3 cells)
			const cCell = cells[cells.length - 3];
			const pCell = cells[cells.length - 2];
			const aCell = cells[cells.length - 1];
			
			const cValue = cCell ? (cCell.querySelector('textarea')?.value.trim() || '') : '';
			const pValue = pCell ? (pCell.querySelector('textarea')?.value.trim() || '') : '';
			const aValue = aCell ? (aCell.querySelector('textarea')?.value.trim() || '') : '';
			
			mappingData.push({
				ilo_text: iloText,
				sos: sos,
				c: cValue,
				p: pValue,
				a: aValue,
				position: index
			});
		});
		
		// If no real rows exist (only placeholders or empty), send empty array to delete all
		// The controller will handle deleting all records when mappings array is empty
		
		// Get syllabus_id from the page context
		const syllabusDoc = document.getElementById('syllabus-document');
		const syllabusId = syllabusDoc ? syllabusDoc.getAttribute('data-syllabus-id') : null;
		
		if (!syllabusId) {
			alert('Syllabus ID not found. Please save the syllabus first.');
			return;
		}
		
		// Send data to server
		fetch('/faculty/syllabus/save-ilo-so-cpa-mapping', {
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
				alert('ILO-SO-CPA mapping saved successfully!');
			} else {
				alert('Error saving mapping: ' + (data.message || 'Unknown error'));
			}
		})
		.catch(error => {
			console.error('Error:', error);
			alert('Error saving mapping: ' + error.message);
		});
	};
});
