document.addEventListener('DOMContentLoaded', function() {
	const addColumnBtn = document.getElementById('add-week-column');
	const removeColumnBtn = document.getElementById('remove-week-column');

	// Function to sync week columns with TLA week numbers
	function syncWeekColumnsWithTLA() {
		const tlaRows = document.querySelectorAll('#tlaTable tbody tr:not(#tla-placeholder)');
		const weekTable = document.querySelector('.assessment-mapping table.week');
		
		if (!weekTable || tlaRows.length === 0) return;

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
		
		if (sortedWeeks.length === 0) return;

		const headerRow = weekTable.querySelector('tr:first-child');
		const allDataRows = weekTable.querySelectorAll('tr:not(:first-child)');
		const currentHeaders = Array.from(headerRow.querySelectorAll('th.week-number'));
		
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

			// If only one column left, add placeholder and remove it
			if (weekHeaders.length === 1) {
				// Add placeholder column before removing
				const placeholderTh = document.createElement('th');
				placeholderTh.className = 'week-number';
				placeholderTh.style.cssText = 'border:none; border-bottom:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; color:#6c757d; font-weight:normal; text-align:center;';
				placeholderTh.textContent = 'No weeks';
				headerRow.appendChild(placeholderTh);
				
				// Add placeholder to all data rows
				allDataRows.forEach(function(row, index) {
					const placeholderTd = document.createElement('td');
					placeholderTd.className = 'week-mapping';
					const borderTop = index > 0 ? 'border-top:1px solid #343a40;' : '';
					placeholderTd.style.cssText = `border:none; ${borderTop} height:30px; padding:0.2rem 0.5rem; background-color:#fff;`;
					placeholderTd.textContent = '';
					row.appendChild(placeholderTd);
				});
			}

			// Remove last header
			weekHeaders[weekHeaders.length - 1].remove();
			
			// Remove last cell from all data rows
			allDataRows.forEach(function(row) {
				const cells = row.querySelectorAll('td.week-mapping');
				if (cells.length > 0) {
					cells[cells.length - 1].remove();
				}
			});
		});
	}

	// Week cell click handler - toggle 'x'
	function attachWeekCellClickHandler(cell) {
		cell.addEventListener('click', function() {
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
		});
	}

	// Remove Row Button
	const removeRowBtn = document.getElementById('remove-row');
	if (removeRowBtn) {
		removeRowBtn.addEventListener('click', function() {
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
		});
	}

	// Save Assessment Mappings Button
	const saveBtn = document.getElementById('save-assessment-mappings');
	if (saveBtn) {
		saveBtn.addEventListener('click', function() {
			const distributionTable = document.querySelector('.assessment-mapping table.distribution');
			const weekTable = document.querySelector('.assessment-mapping table.week');
			if (!distributionTable || !weekTable) return;

			// Get syllabus ID from URL or data attribute
			const syllabusId = document.querySelector('[data-syllabus-id]')?.dataset.syllabusId;
			if (!syllabusId) {
				alert('Syllabus ID not found');
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

			// Disable button and show loading state
			saveBtn.disabled = true;
			const originalText = saveBtn.textContent;
			saveBtn.textContent = 'Saving...';

			// Send AJAX request
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
					alert('Assessment mappings saved successfully!');
				} else {
					alert('Error: ' + (data.message || 'Failed to save'));
				}
			})
			.catch(error => {
				console.error('Error:', error);
				alert('Failed to save assessment mappings. Please try again.');
			})
			.finally(() => {
				// Re-enable button
				saveBtn.disabled = false;
				saveBtn.textContent = originalText;
			});
		});
	}
});
