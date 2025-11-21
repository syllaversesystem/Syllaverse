document.addEventListener('DOMContentLoaded', function() {
	const addColumnBtn = document.getElementById('add-week-column');
	const removeColumnBtn = document.getElementById('remove-week-column');

	// Function to sync week columns with TLA week numbers
	function syncWeekColumnsWithTLA() {
		const tlaRows = document.querySelectorAll('#tlaTable tbody tr:not(#tla-placeholder)');
		const weekTable = document.querySelector('.assessment-mapping table.week');
		
		if (!weekTable) return;
		
		// If no TLA rows, set to "No weeks" state
		if (tlaRows.length === 0) {
			const headerRow = weekTable.querySelector('tr:first-child');
			const allDataRows = weekTable.querySelectorAll('tr:not(:first-child)');
			const currentHeaders = Array.from(headerRow.querySelectorAll('th.week-number'));
			
			// Check if already in "No weeks" state
			const hasPlaceholder = currentHeaders.length === 1 && currentHeaders[0].textContent.trim() === 'No weeks';
			if (hasPlaceholder) return;
			
			// Remove all existing columns
			currentHeaders.forEach(th => th.remove());
			allDataRows.forEach(row => {
				const cells = row.querySelectorAll('td.week-mapping');
				cells.forEach(cell => cell.remove());
			});
			
			// Add "No weeks" placeholder
			const placeholderTh = document.createElement('th');
			placeholderTh.className = 'week-number';
			placeholderTh.style.cssText = 'border:none; border-bottom:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; color:#6c757d; font-weight:normal; text-align:center;';
			placeholderTh.textContent = 'No weeks';
			headerRow.appendChild(placeholderTh);
			
			// Add placeholder cells to all data rows
			allDataRows.forEach(function(row) {
				const placeholderTd = document.createElement('td');
				placeholderTd.className = 'week-mapping';
				placeholderTd.style.cssText = 'border:none; height:30px; padding:0.2rem 0.5rem; background-color:#fff; text-align:center;';
				row.appendChild(placeholderTd);
			});
			
			return;
		}

		// Collect all week numbers from TLA rows
		const weekNumbers = new Set();
		tlaRows.forEach(function(row) {
			const wksInput = row.querySelector('.tla-wks input');
			if (wksInput && wksInput.value.trim()) {
				// Parse week numbers (handle ranges like "1-3" or comma-separated like "1,2,3")
				const wksValue = wksInput.value.trim();
				
				// Handle ranges (e.g., "1-3")
				if (wksValue.includes('-')) {
					const parts = wksValue.split('-');
					const start = parseInt(parts[0]);
					const end = parseInt(parts[1]);
					if (!isNaN(start) && !isNaN(end)) {
						for (let i = start; i <= end; i++) {
							weekNumbers.add(i);
						}
					}
				}
				// Handle comma-separated (e.g., "1,2,3")
				else if (wksValue.includes(',')) {
					wksValue.split(',').forEach(function(num) {
						const parsed = parseInt(num.trim());
						if (!isNaN(parsed)) {
							weekNumbers.add(parsed);
						}
					});
				}
				// Handle single number
				else {
					const parsed = parseInt(wksValue);
					if (!isNaN(parsed)) {
						weekNumbers.add(parsed);
					}
				}
			}
		});

		// Sort week numbers
		const sortedWeeks = Array.from(weekNumbers).sort((a, b) => a - b);
		
		const headerRow = weekTable.querySelector('tr:first-child');
		const allDataRows = weekTable.querySelectorAll('tr:not(:first-child)');
		const currentHeaders = Array.from(headerRow.querySelectorAll('th.week-number'));
		
		// If no weeks, show "No weeks" placeholder
		if (sortedWeeks.length === 0) {
			// Check if placeholder already exists
			const hasPlaceholder = currentHeaders.length === 1 && currentHeaders[0].textContent.trim() === 'No weeks';
			if (hasPlaceholder) return;
			
			// Remove all existing columns
			currentHeaders.forEach(th => th.remove());
			allDataRows.forEach(row => {
				const cells = row.querySelectorAll('td.week-mapping');
				cells.forEach(cell => cell.remove());
			});
			
			// Add placeholder
			const placeholderTh = document.createElement('th');
			placeholderTh.className = 'week-number';
			placeholderTh.style.cssText = 'border:none; border-bottom:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; color:#6c757d; font-weight:normal; text-align:center;';
			placeholderTh.textContent = 'No weeks';
			headerRow.appendChild(placeholderTh);
			
			// Add placeholder cells to all data rows
			allDataRows.forEach(function(row) {
				const placeholderTd = document.createElement('td');
				placeholderTd.className = 'week-mapping';
				placeholderTd.style.cssText = 'border:none; height:30px; padding:0.2rem 0.5rem; background-color:#fff; text-align:center;';
				row.appendChild(placeholderTd);
			});
			
			return;
		}
		
		// Check if we have a placeholder
		const hasPlaceholder = currentHeaders.length === 1 && currentHeaders[0].textContent.trim() === 'No weeks';
		
		// Get current week numbers (excluding placeholder)
		const currentWeeks = hasPlaceholder ? [] : currentHeaders.map(th => parseInt(th.textContent.trim())).filter(n => !isNaN(n));
		
		// If current weeks match sorted weeks, no need to update
		if (JSON.stringify(currentWeeks) === JSON.stringify(sortedWeeks)) return;

		// Clear existing headers and cells
		if (hasPlaceholder) {
			currentHeaders[0].remove();
			allDataRows.forEach(row => {
				const cell = row.querySelector('td.week-mapping');
				if (cell) cell.remove();
			});
		} else {
			currentHeaders.forEach(th => th.remove());
			allDataRows.forEach(row => {
				const cells = row.querySelectorAll('td.week-mapping');
				cells.forEach(cell => cell.remove());
			});
		}

		// Add new week columns based on TLA weeks
		sortedWeeks.forEach(function(weekNum, index) {
			// Add header
			const newTh = document.createElement('th');
			newTh.className = 'week-number';
			const borderLeft = index > 0 ? 'border-left:1px solid #343a40;' : '';
			newTh.style.cssText = `border:none; border-bottom:1px solid #343a40; ${borderLeft} height:30px; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; color:#000; font-weight:bold; text-align:center;`;
			newTh.textContent = weekNum;
			headerRow.appendChild(newTh);

			// Add cells to all data rows
			allDataRows.forEach(function(row, rowIndex) {
				const newTd = document.createElement('td');
				newTd.className = 'week-mapping';
				const borderTop = rowIndex > 0 ? 'border-top:1px solid #343a40;' : '';
				newTd.style.cssText = `border:none; ${borderLeft} ${borderTop} height:30px; padding:0.2rem 0.5rem; background-color:#fff; cursor:pointer; text-align:center;`;
				row.appendChild(newTd);
				attachWeekCellClickHandler(newTd);
			});
		});
	}

	// Initial sync on load
	setTimeout(syncWeekColumnsWithTLA, 500);

	// Watch for changes in TLA table week inputs
	const tlaTable = document.querySelector('#tlaTable tbody');
	if (tlaTable) {
		// Use event delegation for input changes
		tlaTable.addEventListener('input', function(e) {
			if (e.target.matches('.tla-wks input')) {
				// Debounce the sync
				clearTimeout(window.tlaWeekSyncTimeout);
				window.tlaWeekSyncTimeout = setTimeout(syncWeekColumnsWithTLA, 300);
			}
		});

		// Watch for row additions/deletions
		const tlaObserver = new MutationObserver(function(mutations) {
			clearTimeout(window.tlaWeekSyncTimeout);
			window.tlaWeekSyncTimeout = setTimeout(syncWeekColumnsWithTLA, 300);
		});

		tlaObserver.observe(tlaTable, {
			childList: true
		});
	}

	if (addColumnBtn) {
		addColumnBtn.addEventListener('click', function() {
			// Target the week table specifically
			const weekTable = document.querySelector('.assessment-mapping table.week');
			if (!weekTable) return;

			const headerRow = weekTable.querySelector('tr:first-child');
			
			// Get current week headers
			const weekHeaders = Array.from(headerRow.querySelectorAll('th.week-number'));
			
			// Get all data rows
			const allDataRows = weekTable.querySelectorAll('tr:not(:first-child)');
			
			// Check if there's a placeholder "No weeks"
			const hasPlaceholder = weekHeaders.length === 1 && weekHeaders[0].textContent.trim() === 'No weeks';
			
			let newWeekNumber;
			if (hasPlaceholder) {
				// Remove placeholder header
				weekHeaders[0].remove();
				// Remove placeholder cells from all data rows
				allDataRows.forEach(function(row) {
					const placeholderCell = row.querySelector('td.week-mapping');
					if (placeholderCell) placeholderCell.remove();
				});
				newWeekNumber = 1;
			} else {
				// Get the next week number
				newWeekNumber = weekHeaders.length + 1;
			}

			// Add new week header (th)
			const newTh = document.createElement('th');
			newTh.className = 'week-number';
			// Add left border if this is not the first column
			const borderLeft = newWeekNumber > 1 ? 'border-left:1px solid #343a40;' : '';
			newTh.style.cssText = `border:none; border-bottom:1px solid #343a40; ${borderLeft} height:30px; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; color:#000; font-weight:bold; text-align:center;`;
			newTh.textContent = newWeekNumber;
			headerRow.appendChild(newTh);

			// Add new week mapping cell to all data rows
			allDataRows.forEach(function(row, rowIndex) {
				const newTd = document.createElement('td');
				newTd.className = 'week-mapping';
				// Check if it's the first row or not to determine border-top
				const borderTop = rowIndex > 0 ? 'border-top:1px solid #343a40;' : '';
				newTd.style.cssText = `border:none; ${borderLeft} ${borderTop} height:30px; padding:0.2rem 0.5rem; background-color:#fff; cursor:pointer; text-align:center;`;
				row.appendChild(newTd);
				attachWeekCellClickHandler(newTd);
			});
		});
	}

	if (removeColumnBtn) {
		removeColumnBtn.addEventListener('click', function() {
			// Target the week table specifically
			const weekTable = document.querySelector('.assessment-mapping table.week');
			if (!weekTable) return;

			const headerRow = weekTable.querySelector('tr:first-child');
			const dataRow = weekTable.querySelector('tr:last-child');
			
			const weekHeaders = Array.from(headerRow.querySelectorAll('th.week-number'));
			const allDataRows = weekTable.querySelectorAll('tr:not(:first-child)');

			// Remove last header first
			weekHeaders[weekHeaders.length - 1].remove();
			
			// Remove last cell from all data rows (including any 'x' marks)
			allDataRows.forEach(function(row) {
				const cells = row.querySelectorAll('td.week-mapping');
				if (cells.length > 0) {
					cells[cells.length - 1].remove();
				}
			});

			// If no columns left after removal, add placeholder
			if (weekHeaders.length === 1) {
				// Add placeholder header
				const placeholderTh = document.createElement('th');
				placeholderTh.className = 'week-number';
				placeholderTh.style.cssText = 'border:none; border-bottom:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; color:#6c757d; font-weight:normal; text-align:center;';
				placeholderTh.textContent = 'No weeks';
				headerRow.appendChild(placeholderTh);
				
				// Add empty placeholder cells to all data rows
				allDataRows.forEach(function(row, index) {
					const placeholderTd = document.createElement('td');
					placeholderTd.className = 'week-mapping';
					const borderTop = index > 0 ? 'border-top:1px solid #343a40;' : '';
					placeholderTd.style.cssText = `border:none; ${borderTop} height:30px; padding:0.2rem 0.5rem; background-color:#fff;`;
					placeholderTd.textContent = ''; // Empty, no marks
					row.appendChild(placeholderTd);
				});
			}
		});
	}

	// Week cell click handler - toggle 'x'
	function attachWeekCellClickHandler(cell) {
		cell.addEventListener('click', function() {
			// Check if this is a "No weeks" placeholder column
			const weekTable = this.closest('table.week');
			if (!weekTable) return;
			
			const headerRow = weekTable.querySelector('tr:first-child');
			const headers = Array.from(headerRow.querySelectorAll('th.week-number'));
			
			// If only one header and it says "No weeks", don't allow mapping
			if (headers.length === 1 && headers[0].textContent.trim() === 'No weeks') {
				return; // Do nothing, can't map on "No weeks"
			}
			
			// Normal toggle behavior
			if (this.textContent.trim() === 'x') {
				this.textContent = '';
				this.classList.remove('marked');
				this.style.color = '';
			} else {
				this.textContent = 'x';
				this.classList.add('marked');
				this.style.color = '#000';
			}
		});
	}

	// Attach handlers to existing week cells
	document.querySelectorAll('.assessment-mapping .week-mapping').forEach(function(cell) {
		attachWeekCellClickHandler(cell);
	});

	// Add Row Button
	const addRowBtn = document.getElementById('add-row');
	if (addRowBtn) {
		addRowBtn.addEventListener('click', function() {
			alert('Rows are automatically synced from Criteria for Assessment. Please add sub-rows there instead.');
			return;
			
			/* Original manual add logic disabled due to auto-sync
			const distributionTable = document.querySelector('.assessment-mapping table.distribution');
			const weekTable = document.querySelector('.assessment-mapping table.week');
			if (!distributionTable || !weekTable) return;

			// Add row to distribution table
			const distTr = document.createElement('tr');
			const distTd = document.createElement('td');
			distTd.className = 'task';
			distTd.style.cssText = 'border:none; border-top:1px solid #343a40; height:30px; padding:0; background-color:#fff;';
			
			const distInput = document.createElement('input');
			distInput.type = 'text';
			distInput.className = 'form-control form-control-sm distribution-input';
			distInput.placeholder = '-';
			distInput.style.cssText = 'width:100%; height:22px; border:none; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box;';
			
			distTd.appendChild(distInput);
			distTr.appendChild(distTd);
			distributionTable.appendChild(distTr);

			// Add row to week table (matching all week columns)
			const weekTr = document.createElement('tr');
			const weekHeaders = weekTable.querySelectorAll('tr:first-child th.week-number');
			
			weekHeaders.forEach(function(header, index) {
				const weekTd = document.createElement('td');
				weekTd.className = 'week-mapping';
				const borderLeft = index > 0 ? 'border-left:1px solid #343a40;' : '';
				weekTd.style.cssText = `border:none; border-top:1px solid #343a40; ${borderLeft} height:30px; padding:0.2rem 0.5rem; background-color:#fff; cursor:pointer; text-align:center;`;
				weekTr.appendChild(weekTd);
				attachWeekCellClickHandler(weekTd);
			});
			
			weekTable.appendChild(weekTr);
			*/
		});
	}

	// Remove Row Button
	const removeRowBtn = document.getElementById('remove-row');
	if (removeRowBtn) {
		removeRowBtn.addEventListener('click', function() {
			alert('Rows are automatically synced from Criteria for Assessment. Please remove sub-rows there instead.');
			return;
			
			/* Original manual remove logic disabled due to auto-sync
			const distributionTable = document.querySelector('.assessment-mapping table.distribution');
			const weekTable = document.querySelector('.assessment-mapping table.week');
			if (!distributionTable || !weekTable) return;

			const distRows = distributionTable.querySelectorAll('tr');
			const weekRows = weekTable.querySelectorAll('tr');

			// Keep at least 2 rows (1 header + 1 data)
			if (distRows.length <= 2 || weekRows.length <= 2) {
				alert('Cannot remove the last data row');
				return;
			}

			// Remove last row from both tables
			distRows[distRows.length - 1].remove();
			weekRows[weekRows.length - 1].remove();
			*/
		});
	}

	// Global save function that can be called from toolbar
	window.saveAssessmentMappings = function() {
		return new Promise((resolve, reject) => {
			const distributionTable = document.querySelector('.assessment-mapping table.distribution');
			const weekTable = document.querySelector('.assessment-mapping table.week');
			if (!distributionTable || !weekTable) {
				resolve(); // No tables, nothing to save
				return;
			}

			// Get syllabus ID from URL or data attribute
			const syllabusId = document.querySelector('[data-syllabus-id]')?.dataset.syllabusId;
			if (!syllabusId) {
				reject(new Error('Syllabus ID not found'));
				return;
			}

			// Collect data from tables
			const mappings = [];
			const distRows = distributionTable.querySelectorAll('tr:not(:first-child)');
			const weekRows = weekTable.querySelectorAll('tr:not(:first-child)');
			const weekHeaders = weekTable.querySelectorAll('tr:first-child th.week-number');

			// Build week numbers array (skip placeholder)
			const weekNumbers = [];
			weekHeaders.forEach(function(header) {
				const weekText = header.textContent.trim();
				if (weekText !== 'No weeks') {
					weekNumbers.push(parseInt(weekText));
				}
			});

			// Iterate through each row
			distRows.forEach(function(distRow, index) {
				const distInput = distRow.querySelector('input.distribution-input');
				const name = distInput ? distInput.value.trim() : '';

				// Get week marks from corresponding week row
				const weekRow = weekRows[index];
				const weekCells = weekRow ? weekRow.querySelectorAll('td.week-mapping') : [];
				
				const weekMarks = {};
				weekCells.forEach(function(cell, cellIndex) {
					if (cellIndex < weekNumbers.length) {
						const weekNum = weekNumbers[cellIndex];
						const marked = cell.textContent.trim() === 'x';
						weekMarks[weekNum] = marked ? 'x' : '';
					}
				});

				// Only add if there's a name or any week marks
				if (name || Object.keys(weekMarks).length > 0) {
					mappings.push({
						name: name || null,
						week_marks: weekMarks,
						position: index
					});
				}
			});

			// If no mappings collected, send empty array to delete all existing mappings
			// This allows saving when there are no fields (clears all data)

			// Send AJAX request (even if mappings is empty to delete all)
			fetch(`/faculty/syllabi/${syllabusId}/assessment-mappings`, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
				'Accept': 'application/json'
			},
			body: JSON.stringify({ mappings: mappings })
		})
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				resolve(data);
			} else {
				reject(new Error(data.message || 'Failed to save assessment mappings'));
			}
		})
		.catch(error => {
			console.error('Error saving assessment mappings:', error);
			reject(error);
		});
	});
};

// Save Assessment Mappings Button (uses the global function)
const saveBtn = document.getElementById('save-assessment-mappings');
if (saveBtn) {
	saveBtn.addEventListener('click', function() {
		const btn = this;
		const originalText = btn.textContent;
		btn.disabled = true;
		btn.textContent = 'Saving...';

		window.saveAssessmentMappings()
			.then(data => {
				alert('Assessment mappings saved successfully!');
				btn.disabled = false;
				btn.textContent = originalText;
			})
			.catch(error => {
				alert('Failed to save: ' + error.message);
				btn.disabled = false;
				btn.textContent = originalText;
			});
	});
}	// Function to load existing assessment mappings
	function loadAssessmentMappings() {
		const syllabusId = document.querySelector('[data-syllabus-id]')?.dataset.syllabusId;
		if (!syllabusId) return;

		fetch(`/faculty/syllabi/${syllabusId}/assessment-mappings`, {
			method: 'GET',
			headers: {
				'Accept': 'application/json',
				'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
			}
		})
		.then(response => response.json())
		.then(data => {
			if (data.success && data.mappings && data.mappings.length > 0) {
				renderMappings(data.mappings);
			}
		})
		.catch(error => {
			console.error('Error loading assessment mappings:', error);
		});
	}

	// Function to render loaded mappings
	function renderMappings(mappings) {
		const distributionTable = document.querySelector('.assessment-mapping table.distribution');
		const weekTable = document.querySelector('.assessment-mapping table.week');
		if (!distributionTable || !weekTable) return;

		// Collect all unique week numbers from mappings
		const allWeeks = new Set();
		mappings.forEach(mapping => {
			if (mapping.week_marks) {
				const weekMarks = typeof mapping.week_marks === 'string' 
					? JSON.parse(mapping.week_marks) 
					: mapping.week_marks;
				Object.keys(weekMarks).forEach(week => allWeeks.add(parseInt(week)));
			}
		});

		// Sort weeks
		const sortedWeeks = Array.from(allWeeks).sort((a, b) => a - b);

		// Clear existing rows (keep header)
		const distRows = distributionTable.querySelectorAll('tr:not(:first-child)');
		const weekRows = weekTable.querySelectorAll('tr:not(:first-child)');
		distRows.forEach(row => row.remove());
		weekRows.forEach(row => row.remove());

		// Clear existing week headers (keep placeholder if exists)
		const headerRow = weekTable.querySelector('tr:first-child');
		const headers = headerRow.querySelectorAll('th.week-number');
		headers.forEach(th => th.remove());

		// Add week columns
		if (sortedWeeks.length > 0) {
			sortedWeeks.forEach((weekNum, index) => {
				const newTh = document.createElement('th');
				newTh.className = 'week-number';
				const borderLeft = index > 0 ? 'border-left:1px solid #343a40;' : '';
				newTh.style.cssText = `border:none; border-bottom:1px solid #343a40; ${borderLeft} height:30px; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; color:#000; font-weight:bold; text-align:center;`;
				newTh.textContent = weekNum;
				headerRow.appendChild(newTh);
			});
		} else {
			// Add "No weeks" placeholder
			const placeholderTh = document.createElement('th');
			placeholderTh.className = 'week-number';
			placeholderTh.style.cssText = 'border:none; border-bottom:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; color:#6c757d; font-weight:normal; text-align:center;';
			placeholderTh.textContent = 'No weeks';
			headerRow.appendChild(placeholderTh);
		}

		// Add rows for each mapping
		mappings.forEach((mapping, rowIndex) => {
			// Add distribution row
			const distTr = document.createElement('tr');
			const distTd = document.createElement('td');
			distTd.className = 'task';
			const borderTop = rowIndex > 0 ? 'border-top:1px solid #343a40;' : '';
			distTd.style.cssText = `border:none; ${borderTop} height:30px; padding:0; background-color:#fff;`;
			
			const distInput = document.createElement('input');
			distInput.type = 'text';
			distInput.className = 'form-control form-control-sm distribution-input';
			distInput.placeholder = '-';
			distInput.value = mapping.name || '';
			distInput.style.cssText = 'width:100%; height:22px; border:none; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box;';
			
			distTd.appendChild(distInput);
			distTr.appendChild(distTd);
			distributionTable.appendChild(distTr);

			// Add week row
			const weekTr = document.createElement('tr');
			const weekMarks = typeof mapping.week_marks === 'string' 
				? JSON.parse(mapping.week_marks) 
				: (mapping.week_marks || {});

			if (sortedWeeks.length > 0) {
				sortedWeeks.forEach((weekNum, index) => {
					const weekTd = document.createElement('td');
					weekTd.className = 'week-mapping';
					const borderLeft = index > 0 ? 'border-left:1px solid #343a40;' : '';
					weekTd.style.cssText = `border:none; ${borderTop} ${borderLeft} height:30px; padding:0.2rem 0.5rem; background-color:#fff; cursor:pointer; text-align:center;`;
					
					// Check if this week is marked
					if (weekMarks[weekNum] === 'x') {
						weekTd.textContent = 'x';
						weekTd.style.color = '#000';
						weekTd.classList.add('marked');
					}
					
					weekTr.appendChild(weekTd);
					attachWeekCellClickHandler(weekTd);
				});
			} else {
				// Add empty cell for placeholder
				const weekTd = document.createElement('td');
				weekTd.className = 'week-mapping';
				weekTd.style.cssText = `border:none; ${borderTop} height:30px; padding:0.2rem 0.5rem; background-color:#fff; text-align:center;`;
				weekTr.appendChild(weekTd);
			}

			weekTable.appendChild(weekTr);
		});
	}

	// Sync distribution names from Assessment Tasks and adjust row count
	function syncDistributionFromAT() {
		const atTable = document.querySelector('.at-map-outer .cis-table tbody');
		if (!atTable) {
			console.log('AT table not found');
			return;
		}
		
		const distributionTable = document.querySelector('.assessment-mapping table.distribution');
		const weekTable = document.querySelector('.assessment-mapping table.week');
		if (!distributionTable || !weekTable) {
			console.log('Distribution or week table not found');
			return;
		}
		
		// Get all sub-input textareas from Assessment Tasks (readonly, populated from Criteria)
		const atSubInputs = Array.from(atTable.querySelectorAll('.at-sub-row td:nth-child(2) textarea.sub-input'));
		
		// Get current distribution and week rows
		const distRows = Array.from(distributionTable.querySelectorAll('tr:not(:first-child)'));
		const weekRows = Array.from(weekTable.querySelectorAll('tr:not(:first-child)'));
		
		const targetRowCount = atSubInputs.length;
		const currentRowCount = distRows.length;
		
		console.log('Syncing distribution from AT:', targetRowCount, 'AT rows vs', currentRowCount, 'current rows');
		
		// Add rows if needed
		if (targetRowCount > currentRowCount) {
			const toAdd = targetRowCount - currentRowCount;
			console.log(`Adding ${toAdd} rows`);
			
			for (let i = 0; i < toAdd; i++) {
				// Get current week headers to match column count
				const weekHeaders = Array.from(weekTable.querySelectorAll('tr:first-child th.week-number'));
				const weekNumbers = [];
				weekHeaders.forEach(function(header) {
					const weekText = header.textContent.trim();
					if (weekText !== 'No weeks') {
						weekNumbers.push(parseInt(weekText));
					}
				});
				
				// Add distribution row
				const newDistRow = document.createElement('tr');
				newDistRow.innerHTML = `
					<td class="task" style="border:none; border-top:1px solid #343a40; height:30px; padding:0; background-color:#fff;">
						<input type="text" class="form-control form-control-sm distribution-input" placeholder="-" style="width:100%; height:22px; border:none; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box;">
					</td>
				`;
				distributionTable.appendChild(newDistRow);
				
				// Add week row with cells matching current week columns
				const newWeekRow = document.createElement('tr');
				if (weekNumbers.length > 0) {
					weekNumbers.forEach(function(weekNum, index) {
						const newTd = document.createElement('td');
						newTd.className = 'week-mapping';
						const borderLeft = index > 0 ? 'border-left:1px solid #343a40;' : '';
						newTd.style.cssText = `border:none; border-top:1px solid #343a40; ${borderLeft} height:30px; padding:0.2rem 0.5rem; background-color:#fff; cursor:pointer; text-align:center;`;
						newWeekRow.appendChild(newTd);
						attachWeekCellClickHandler(newTd);
					});
				} else {
					// Add placeholder cell
					const newTd = document.createElement('td');
					newTd.className = 'week-mapping';
					newTd.style.cssText = 'border:none; border-top:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; background-color:#fff; text-align:center;';
					newWeekRow.appendChild(newTd);
				}
				weekTable.appendChild(newWeekRow);
			}
		}
		// Remove rows if needed
		else if (targetRowCount < currentRowCount) {
			const toRemove = currentRowCount - targetRowCount;
			console.log(`Removing ${toRemove} rows`);
			
			for (let i = 0; i < toRemove; i++) {
				// Only remove data rows, not header rows
				const allDistRows = Array.from(distributionTable.querySelectorAll('tr'));
				const allWeekRows = Array.from(weekTable.querySelectorAll('tr'));
				
				// Keep at least header row (first row)
				if (allDistRows.length > 1 && allWeekRows.length > 1) {
					const lastDistRow = allDistRows[allDistRows.length - 1];
					const lastWeekRow = allWeekRows[allWeekRows.length - 1];
					
					// Make sure we're not removing header rows (check if they have .distribution-header or .week-number class)
					const isDistHeader = lastDistRow.querySelector('.distribution-header');
					const isWeekHeader = lastWeekRow.querySelector('.week-number');
					
					if (!isDistHeader && lastDistRow) lastDistRow.remove();
					if (!isWeekHeader && lastWeekRow) lastWeekRow.remove();
				}
			}
		}
		
		// Now sync values
		const distInputs = Array.from(distributionTable.querySelectorAll('input.distribution-input'));
		atSubInputs.forEach((atInput, index) => {
			if (index < distInputs.length) {
				const atValue = atInput.value.trim();
				distInputs[index].value = atValue;
				console.log(`Synced row ${index}: "${atValue}"`);
			}
		});
	}
	
	// Watch for changes in Assessment Tasks table (which gets updated from Criteria)
	const atTableContainer = document.querySelector('.at-map-outer');
	if (atTableContainer) {
		// Initial sync
		setTimeout(syncDistributionFromAT, 1200);
		
		// Watch for any changes in the entire AT container
		const atObserver = new MutationObserver(function(mutations) {
			clearTimeout(window.atDistSyncTimeout);
			window.atDistSyncTimeout = setTimeout(syncDistributionFromAT, 200);
		});
		
		atObserver.observe(atTableContainer, {
			childList: true,
			subtree: true,
			characterData: true,
			attributes: false
		});
	}
	
	// Also watch Criteria container directly for faster response
	const criteriaContainer = document.getElementById('criteria-sections-container');
	if (criteriaContainer) {
		criteriaContainer.addEventListener('input', function(e) {
			if (e.target && e.target.classList.contains('sub-input')) {
				clearTimeout(window.criteriaToDistSyncTimeout);
				window.criteriaToDistSyncTimeout = setTimeout(syncDistributionFromAT, 400);
			}
		});
	}

	// Load mappings on page load (after TLA sync delay)
	setTimeout(loadAssessmentMappings, 1000);

	// Function to check if Assessment Method text overflows and hide if needed
	function checkAssessmentMethodOverflow() {
		const header = document.querySelector('.assessment-method-header');
		const text = document.querySelector('.assessment-method-text');
		
		if (!header || !text) return;
		
		const headerHeight = header.offsetHeight;
		const textWidth = text.scrollWidth; // Text width becomes height when rotated
		
		// If text is longer than available height, hide it completely
		if (textWidth > headerHeight) {
			text.style.visibility = 'hidden';
		} else {
			text.style.visibility = 'visible';
		}
	}

	// Check overflow initially and whenever rows change
	setTimeout(checkAssessmentMethodOverflow, 1500);
	
	// Re-check when distribution rows are added/removed
	const originalSyncDistribution = syncDistributionFromAT;
	syncDistributionFromAT = function() {
		originalSyncDistribution();
		setTimeout(checkAssessmentMethodOverflow, 300);
	};
});
