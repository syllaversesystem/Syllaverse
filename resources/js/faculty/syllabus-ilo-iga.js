// File: resources/js/faculty/syllabus-ilo-iga.js
// Consolidated ILO-IGA mapping functionality

document.addEventListener('DOMContentLoaded', function() {
	const mapping = document.querySelector('.ilo-iga-mapping');
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
	
	// Add IGA column function
	window.addIgaColumn = function() {
		const mappingTable = mapping.querySelector('.mapping');
		const colgroup = mappingTable.querySelector('colgroup');
		const headerRow1 = mappingTable.querySelectorAll('tr')[0];
		const headerRow2 = mappingTable.querySelectorAll('tr')[1];
		const tbody = mappingTable.querySelector('tbody') || mappingTable;
		const dataRows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.querySelector('td'));
		
		// Get all IGA headers (all headers after ILOs)
		const allHeaders = Array.from(headerRow2.querySelectorAll('th'));
		const iloHeaderIndex = allHeaders.findIndex(th => th.textContent.includes('ILOs'));
		const igaHeaders = allHeaders.slice(iloHeaderIndex + 1);
		
		// Check if placeholder exists
		const placeholderHeader = igaHeaders.find(th => {
			const input = th.querySelector('input');
			const text = input ? input.value.trim() : th.textContent.trim();
			return text === 'No IGA';
		});
		
		if (placeholderHeader) {
			// Replace placeholder with blank IGA input and keep buttons
			const existingRemoveControls = placeholderHeader.querySelector('.iga-remove-controls');
			const existingAddControls = placeholderHeader.querySelector('.iga-add-controls');
			placeholderHeader.textContent = ''; // Clear all content
			
			// Re-add controls if they exist
			if (existingRemoveControls) {
				placeholderHeader.appendChild(existingRemoveControls);
			}
			if (existingAddControls) {
				placeholderHeader.appendChild(existingAddControls);
			}
			
			// Add blank input for IGA
			const igaInput = document.createElement('input');
			igaInput.type = 'text';
			igaInput.value = '';
			igaInput.placeholder = '-';
			igaInput.className = 'form-control form-control-sm';
			igaInput.style.cssText = 'width:100%; border:none; padding:0.1rem 0.25rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; background:transparent; font-weight:700;';
			// Auto-format IGA number
			igaInput.addEventListener('input', function(e) {
				const value = e.target.value;
				if (/^\d+$/.test(value)) {
					e.target.value = 'IGA' + value;
				}
			});
			placeholderHeader.appendChild(igaInput);
			
			placeholderHeader.style.cssText = 'border:none; border-bottom:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center; position:relative;';
			
			// Replace placeholder cells with textarea in each data row
			dataRows.forEach(row => {
				const cells = Array.from(row.querySelectorAll('td'));
				// Last IGA cell is at index length - 1 (last cell)
				const lastIgaIndex = cells.length - 1;
				const igaCell = cells[lastIgaIndex];
				if (igaCell && (igaCell.textContent.trim() === '-' || igaCell.querySelector('span'))) {
					igaCell.style.cssText = 'border:none; border-top:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;';
					const newTextarea = document.createElement('textarea');
					newTextarea.className = 'form-control form-control-sm';
					newTextarea.placeholder = '-';
					newTextarea.rows = 1;
					newTextarea.style.cssText = 'width:100%; min-height:22px; border:none; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; resize:none; overflow:hidden;';
					newTextarea.addEventListener('input', function() {
						autoResize(this);
					});
					igaCell.innerHTML = '';
					igaCell.appendChild(newTextarea);
				}
			});
			return;
		}
		
		// Remove add button from current last IGA header
		const currentLastIgaHeader = igaHeaders[igaHeaders.length - 1];
		if (currentLastIgaHeader) {
			const existingAddControls = currentLastIgaHeader.querySelector('.iga-add-controls');
			if (existingAddControls) {
				existingAddControls.remove();
			}
		}
		
		// Add column to colgroup
		const newCol = document.createElement('col');
		colgroup.appendChild(newCol);
		
		// Update header colspan
		const igaHeaderSpan = headerRow1.querySelectorAll('th')[1];
		igaHeaderSpan.setAttribute('colspan', parseInt(igaHeaderSpan.getAttribute('colspan')) + 1);
		
		// Add new IGA header in row 2 (at the end)
		const newIgaHeader = document.createElement('th');
		newIgaHeader.style.cssText = 'border:none; border-bottom:1px solid #343a40; border-left:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center; position:relative;';
		
		// Add controls to new header
		const addControls = document.createElement('div');
		addControls.className = 'iga-add-controls';
		const addBtn = document.createElement('button');
		addBtn.type = 'button';
		addBtn.className = 'btn btn-sm iga-add-btn';
		addBtn.onclick = addIgaColumn;
		addBtn.title = 'Add IGA column';
		addBtn.setAttribute('aria-label', 'Add IGA column');
		addBtn.innerHTML = '<i data-feather="plus"></i>';
		addControls.appendChild(addBtn);
		newIgaHeader.appendChild(addControls);
		
		// Create blank input for IGA header
		const igaInput = document.createElement('input');
		igaInput.type = 'text';
		igaInput.value = '';
		igaInput.placeholder = '-';
		igaInput.className = 'form-control form-control-sm';
		igaInput.style.cssText = 'width:100%; border:none; padding:0.1rem 0.25rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; background:transparent; font-weight:700;';
		// Auto-format IGA number
		igaInput.addEventListener('input', function(e) {
			const value = e.target.value;
			if (/^\d+$/.test(value)) {
				e.target.value = 'IGA' + value;
			}
		});
		newIgaHeader.appendChild(igaInput);
		
		headerRow2.appendChild(newIgaHeader);
		
		// Add new IGA cell to each data row (at the end)
		dataRows.forEach(row => {
			const newCell = document.createElement('td');
			newCell.style.cssText = 'border:none; border-top:1px solid #343a40; border-left:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;';
			const newTextarea = document.createElement('textarea');
			newTextarea.className = 'form-control form-control-sm';
			newTextarea.placeholder = '-';
			newTextarea.rows = 1;
			newTextarea.style.cssText = 'width:100%; min-height:22px; border:none; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; resize:none; overflow:hidden;';
			newTextarea.addEventListener('input', function() {
				autoResize(this);
			});
			newCell.appendChild(newTextarea);
			row.appendChild(newCell);
		});
		
		// Re-initialize feather icons for the new button
		if (typeof feather !== 'undefined') {
			feather.replace();
		}
	};
	
	// Remove IGA column function
	window.removeIgaColumn = function() {
		const mappingTable = mapping.querySelector('.mapping');
		const colgroup = mappingTable.querySelector('colgroup');
		const headerRow1 = mappingTable.querySelectorAll('tr')[0];
		const headerRow2 = mappingTable.querySelectorAll('tr')[1];
		const tbody = mappingTable.querySelector('tbody') || mappingTable;
		const dataRows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.querySelector('td'));
		
		// Get all IGA headers (all headers after ILOs)
		const allHeaders = Array.from(headerRow2.querySelectorAll('th'));
		const iloHeaderIndex = allHeaders.findIndex(th => th.textContent.includes('ILOs'));
		const igaHeaders = allHeaders.slice(iloHeaderIndex + 1);
		
		// Check if already showing placeholder
		const placeholderExists = igaHeaders.some(th => {
			const input = th.querySelector('input');
			const text = input ? input.value.trim() : th.textContent.trim();
			return text === 'No IGA';
		});
		
		if (placeholderExists) {
			// Already at placeholder, can't remove further
			return;
		}
		
		if (igaHeaders.length <= 1) {
			// If removing the last IGA column, replace it with placeholder
			// Don't remove from colgroup - keep the column structure
			
			// Don't update header colspan - keep the same span
			
			// Replace last IGA header with placeholder but keep buttons
			const lastIgaHeader = igaHeaders[igaHeaders.length - 1];
			const existingRemoveControls = lastIgaHeader.querySelector('.iga-remove-controls');
			const existingAddControls = lastIgaHeader.querySelector('.iga-add-controls');
			
			lastIgaHeader.innerHTML = ''; // Clear all content
			
			// Re-add controls if they exist
			if (existingRemoveControls) {
				lastIgaHeader.appendChild(existingRemoveControls);
			}
			if (existingAddControls) {
				lastIgaHeader.appendChild(existingAddControls);
			}
			
			// Add text node for placeholder
			const textNode = document.createTextNode('No IGA');
			lastIgaHeader.appendChild(textNode);
			
			lastIgaHeader.style.cssText = 'border:none; border-bottom:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:400; font-style:italic; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#999; text-align:center; position:relative;';
			
			// Replace IGA cells with placeholder in each data row
			dataRows.forEach(row => {
				const cells = Array.from(row.querySelectorAll('td'));
				// Last IGA cell is at index length - 1 (last cell)
				const lastIgaIndex = cells.length - 1;
				const igaCell = cells[lastIgaIndex];
				if (igaCell) {
					igaCell.innerHTML = '<span style="color:#999; font-style:italic;">-</span>';
					igaCell.style.cssText = 'border:none; border-top:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle; background:#f9f9f9;';
				}
			});
			return;
		}
		
		// Remove last IGA column from colgroup
		colgroup.removeChild(colgroup.children[colgroup.children.length - 1]);
		
		// Update header colspan
		const igaHeaderSpan = headerRow1.querySelectorAll('th')[1];
		igaHeaderSpan.setAttribute('colspan', parseInt(igaHeaderSpan.getAttribute('colspan')) - 1);
		
		// Remove last IGA header
		const lastIgaHeader = igaHeaders[igaHeaders.length - 1];
		lastIgaHeader.remove();
		
		// Remove last IGA cell from each data row
		dataRows.forEach(row => {
			const cells = Array.from(row.querySelectorAll('td'));
			// Last cell is the IGA cell
			const lastIgaIndex = cells.length - 1;
			if (lastIgaIndex > 0 && cells[lastIgaIndex]) {
				cells[lastIgaIndex].remove();
			}
		});
		
		// After removing, add the plus button to the new last IGA header
		const updatedAllHeaders = Array.from(headerRow2.querySelectorAll('th'));
		const updatedIloHeaderIndex = updatedAllHeaders.findIndex(th => th.textContent.includes('ILOs'));
		const updatedIgaHeaders = updatedAllHeaders.slice(updatedIloHeaderIndex + 1);
		
		if (updatedIgaHeaders.length > 0) {
			const newLastIgaHeader = updatedIgaHeaders[updatedIgaHeaders.length - 1];
			// Check if it doesn't already have add controls
			if (!newLastIgaHeader.querySelector('.iga-add-controls')) {
				const addControls = document.createElement('div');
				addControls.className = 'iga-add-controls';
				const addBtn = document.createElement('button');
				addBtn.type = 'button';
				addBtn.className = 'btn btn-sm iga-add-btn';
				addBtn.onclick = addIgaColumn;
				addBtn.title = 'Add IGA column';
				addBtn.setAttribute('aria-label', 'Add IGA column');
				addBtn.innerHTML = '<i data-feather="plus"></i>';
				addControls.appendChild(addBtn);
				newLastIgaHeader.insertBefore(addControls, newLastIgaHeader.firstChild);
				
				// Re-initialize feather icons for the new button
				if (typeof feather !== 'undefined') {
					feather.replace();
				}
			}
		}
	};
	
	// Add ILO row function
	window.addIloRowIga = function() {
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
			
			// Re-enable all textareas in IGA cells
			const cells = lastRow.querySelectorAll('td');
			for (let i = 1; i < cells.length; i++) {
				const textarea = cells[i].querySelector('textarea');
				if (textarea) {
					textarea.disabled = false;
					textarea.style.backgroundColor = '';
					textarea.style.cursor = '';
				}
				// Check if this cell should have a left border (not first IGA column)
				if (i === 1) {
					cells[i].style.cssText = 'border:none; border-top:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;';
				} else {
					cells[i].style.cssText = 'border:none; border-top:1px solid #343a40; border-left:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;';
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
		
		// Update all IGA cell borders in new row
		const newCells = newRow.querySelectorAll('td');
		for (let i = 1; i < newCells.length; i++) {
			// First IGA column has no left border, others have left border
			if (i === 1) {
				newCells[i].style.cssText = 'border:none; border-top:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;';
			} else {
				newCells[i].style.cssText = 'border:none; border-top:1px solid #343a40; border-left:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;';
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
	
	// Remove ILO row function
	window.removeIloRowIga = function() {
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
			
			// Keep all textareas enabled
			for (let i = 1; i < cells.length; i++) {
				const textarea = cells[i].querySelector('textarea');
				if (textarea) {
					textarea.disabled = false;
					textarea.value = '';
					textarea.style.backgroundColor = '';
					textarea.style.cursor = '';
				}
				// First IGA column has no left border, others have left border
				if (i === 1) {
					cells[i].style.cssText = 'border:none; border-top:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;';
				} else {
					cells[i].style.cssText = 'border:none; border-top:1px solid #343a40; border-left:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;';
				}
			}
			return;
		}
		
		const lastRow = dataRows[dataRows.length - 1];
		lastRow.remove();
		checkPartialLabelOverflow();
	};
	
	// Save ILO-IGA mapping function
	window.saveIloIga = function(showAlert = true) {
		const mappingTable = mapping.querySelector('.mapping');
		const headerRow2 = mappingTable.querySelectorAll('tr')[1];
		const tbody = mappingTable.querySelector('tbody') || mappingTable;
		const dataRows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.querySelector('td'));
		
		// Get all IGA headers (all headers after ILOs)
		const allHeaders = Array.from(headerRow2.querySelectorAll('th'));
		const iloHeaderIndex = allHeaders.findIndex(th => th.textContent.includes('ILOs'));
		const igaHeaders = allHeaders.slice(iloHeaderIndex + 1);
		
		// Collect IGA column labels - always send even if empty to allow deletion
		const igaLabels = [];
		igaHeaders.forEach(th => {
			const input = th.querySelector('input');
			const label = input ? input.value.trim() : th.textContent.trim();
			// Include empty labels, skip only "No IGA" placeholder
			if (label !== 'No IGA') {
				igaLabels.push(label || '');
			}
		});
		
		// Build data array - always send data (even if empty) to allow deletion
		const mappingData = [];
		
		dataRows.forEach((row, index) => {
			const cells = Array.from(row.querySelectorAll('td'));
			const iloInput = cells[0].querySelector('input');
			const iloText = iloInput ? iloInput.value.trim() : cells[0].textContent.trim();
			
			// Include all rows except placeholder to allow deletion
			if (iloText === 'No ILO') return;
			
			// Collect IGA values as object with column labels as keys
			const igas = {};
			igaHeaders.forEach((header, igaIndex) => {
				const input = header.querySelector('input');
				const igaLabel = input ? input.value.trim() : header.textContent.trim();
				// Skip only "No IGA" placeholder, allow empty labels
				if (igaLabel === 'No IGA') return;
				
				const igaCell = cells[igaIndex + 1]; // +1 because first cell is ILO
				if (igaCell) {
					const textarea = igaCell.querySelector('textarea');
					if (textarea) {
						// Save the value even if empty
						igas[igaLabel || ''] = textarea.value.trim();
					}
				}
			});
			
			mappingData.push({
				ilo_text: iloText || '', // Allow empty ILO text
				igas: igas,
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
		return fetch('/faculty/syllabus/save-ilo-iga-mapping', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
			},
			body: JSON.stringify({
				syllabus_id: syllabusId,
				iga_labels: igaLabels,
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
				if (showAlert) alert('ILO-IGA mapping saved successfully!');
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
		const igaHeadersData = mapping.getAttribute('data-iga-headers');
		const mappingsData = mapping.getAttribute('data-mappings');
		
		if (!igaHeadersData || !mappingsData) return;
		
		try {
			const igaHeaders = JSON.parse(igaHeadersData);
			const mappings = JSON.parse(mappingsData);
			
			if (!mappings || mappings.length === 0) return;
			
			// First, add IGA columns
			if (igaHeaders && igaHeaders.length > 0) {
				// Remove placeholder and add columns
				igaHeaders.forEach((igaLabel, index) => {
					if (index === 0) {
						// First IGA - convert placeholder
						addIgaColumn();
						// Update the label
						const mappingTable = mapping.querySelector('.mapping');
						const headerRow2 = mappingTable.querySelectorAll('tr')[1];
						const allHeaders = Array.from(headerRow2.querySelectorAll('th'));
						const iloHeaderIndex = allHeaders.findIndex(th => th.textContent.includes('ILOs'));
						const firstIgaHeader = allHeaders.slice(iloHeaderIndex + 1)[0];
						if (firstIgaHeader) {
							const input = firstIgaHeader.querySelector('input');
							if (input) input.value = igaLabel;
						}
					} else {
						// Additional IGA columns
						addIgaColumn();
						// Update the label
						const mappingTable = mapping.querySelector('.mapping');
						const headerRow2 = mappingTable.querySelectorAll('tr')[1];
						const allHeaders = Array.from(headerRow2.querySelectorAll('th'));
						const iloHeaderIndex = allHeaders.findIndex(th => th.textContent.includes('ILOs'));
						const currentIgaHeaders = allHeaders.slice(iloHeaderIndex + 1);
						const currentIgaHeader = currentIgaHeaders[index];
						if (currentIgaHeader) {
							const input = currentIgaHeader.querySelector('input');
							if (input) input.value = igaLabel;
						}
					}
				});
			}
			
			// Then, populate ILO rows
			mappings.forEach((mappingRow, rowIndex) => {
				if (rowIndex === 0) {
					// First row - convert placeholder
					addIloRowIga();
					const mappingTable = mapping.querySelector('.mapping');
					const tbody = mappingTable.querySelector('tbody') || mappingTable;
					const dataRows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.querySelector('td'));
					const firstRow = dataRows[0];
					if (firstRow) {
						const cells = Array.from(firstRow.querySelectorAll('td'));
						// Set ILO label
						const iloInput = cells[0].querySelector('input');
						if (iloInput) iloInput.value = mappingRow.ilo_text;
						
						// Set IGA values
						if (mappingRow.igas && typeof mappingRow.igas === 'object') {
							Object.entries(mappingRow.igas).forEach(([igaLabel, igaValue], igaIndex) => {
								const igaCell = cells[igaIndex + 1];
								if (igaCell) {
									const textarea = igaCell.querySelector('textarea');
									if (textarea) {
										textarea.value = igaValue;
										autoResize(textarea);
									}
								}
							});
						}
					}
				} else {
					// Additional rows
					addIloRowIga();
					const mappingTable = mapping.querySelector('.mapping');
					const tbody = mappingTable.querySelector('tbody') || mappingTable;
					const dataRows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.querySelector('td'));
					const currentRow = dataRows[rowIndex];
					if (currentRow) {
						const cells = Array.from(currentRow.querySelectorAll('td'));
						// Set ILO label
						const iloInput = cells[0].querySelector('input');
						if (iloInput) iloInput.value = mappingRow.ilo_text;
						
						// Set IGA values
						if (mappingRow.igas && typeof mappingRow.igas === 'object') {
							Object.entries(mappingRow.igas).forEach(([igaLabel, igaValue], igaIndex) => {
								const igaCell = cells[igaIndex + 1];
								if (igaCell) {
									const textarea = igaCell.querySelector('textarea');
									if (textarea) {
										textarea.value = igaValue;
										autoResize(textarea);
									}
								}
							});
						}
					}
				}
			});
		} catch (e) {
			console.error('Error loading saved ILO-IGA data:', e);
		}
	}
	
	// Load data after a short delay to ensure DOM is fully ready
	setTimeout(() => {
		loadSavedData();
	}, 100);
});
