document.addEventListener('DOMContentLoaded', function() {
	const addColumnBtn = document.getElementById('addWeekColumn');
	const removeColumnBtn = document.getElementById('removeWeekColumn');

	if (addColumnBtn) {
		addColumnBtn.addEventListener('click', function() {
			const innerWeekTable = document.querySelector('.inner-week-table');
			if (!innerWeekTable) return;

			const headerRow = innerWeekTable.querySelector('tr:first-child');
			const dataRow = innerWeekTable.querySelector('tr:last-child');
			
			// Get current week count
			const weekHeaders = headerRow.querySelectorAll('th');
			const newWeekNumber = weekHeaders.length + 1;

			// Add header cell
			const newTh = document.createElement('th');
			newTh.className = 'week-no-header';
			newTh.style.cssText = 'border-top:none; border-left:1px solid #343a40; border-right:none; border-bottom:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center;';
			newTh.textContent = newWeekNumber;
			headerRow.appendChild(newTh);

			// Add data cell
			const newTd = document.createElement('td');
			newTd.className = 'week-mappings';
			newTd.style.cssText = 'border-top:none; border-left:1px solid #343a40; border-right:none; border-bottom:none; padding:0.12rem 0.18rem; text-align:center; cursor:pointer; user-select:none;';
			newTd.setAttribute('data-week', newWeekNumber);
			dataRow.appendChild(newTd);

			// Attach click handler
			attachWeekCellClickHandler(newTd);
		});
	}

	if (removeColumnBtn) {
		removeColumnBtn.addEventListener('click', function() {
			const innerWeekTable = document.querySelector('.inner-week-table');
			if (!innerWeekTable) return;

			const headerRow = innerWeekTable.querySelector('tr:first-child');
			const dataRow = innerWeekTable.querySelector('tr:last-child');
			
			const weekHeaders = headerRow.querySelectorAll('th');
			const weekCells = dataRow.querySelectorAll('td');

			// Keep at least one week column
			if (weekHeaders.length <= 1) {
				alert('Cannot remove the last week column');
				return;
			}

			// Remove last header and cell
			weekHeaders[weekHeaders.length - 1].remove();
			weekCells[weekCells.length - 1].remove();
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
				this.style.color = '#111';
			}
		});
	}

	// Attach handlers to existing week cells
	document.querySelectorAll('.week-mappings').forEach(function(cell) {
		attachWeekCellClickHandler(cell);
	});
});
