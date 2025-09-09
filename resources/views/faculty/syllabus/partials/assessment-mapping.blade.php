
{{--
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/assessment-mapping.blade.php
* Purpose: Minimal 2×2 assessment mapping box with dark borders to match other modules
-------------------------------------------------------------------------------
--}}


<div class="assessment-mapping">
<table class="table table-bordered mb-4" style="width:100%; table-layout:fixed; border:1px solid #343a40; border-collapse:collapse;">
	<colgroup>
		<col style="width:10%;">
		<col style="width:14%;">
		@for ($j = 0; $j < 16; $j++)
			<col style="width:4.75%;">
		@endfor
	</colgroup>
	<tbody>
		<thead>
			<tr>
				<th colspan="2" style="border:1px solid #343a40; height:30px; width:24%; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center;" class="text-center">Assessment Schedule</th>
				<th colspan="16" style="border:1px solid #343a40; height:30px; width:65%; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center;" class="text-center">Week No.</th>
			</tr>
		</thead>
			<tr>
				<td class="merge-cell" rowspan="1" style="border:1px solid #343a40; height:30px; width:10%;"></td>
				<td style="border:1px solid #343a40; height:30px; width:14%; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center;">Distribution</td>
				@for ($i = 1; $i <= 16; $i++)
					<td style="border:1px solid #343a40; height:30px; width:auto; padding:0.15rem 0.25rem; text-align:center; font-family:Georgia, serif; font-size:12px;">@if ($i == 1) 1-2 @else {{ $i + 1 }} @endif</td>
				@endfor
			</tr>
			<tr>
				<td style="border:1px solid #343a40; height:30px; width:14%; padding:0.12rem 0.18rem; text-align:center;">
					<input type="text" name="assessment_distribution" form="syllabusForm" value="{{ old('assessment_distribution', '') }}" class="form-control cis-input text-center cis-field" placeholder="LE" />
				</td>
				@for ($i = 1; $i <= 16; $i++)
					<td class="week-cell" data-week="{{ $i == 1 ? '1-2' : $i + 1 }}" style="border:1px solid #343a40; height:30px; width:auto; padding:0;"></td>
				@endfor
			</tr>
	</tbody>
</table>

<style>
/* Revert vertical alignment inside this partial back to top */
.assessment-mapping td { vertical-align: top; }
</style>

<style>
/* Click-to-toggle mark styling (scoped) */
.assessment-mapping .week-cell{ cursor: pointer; user-select: none; text-align: center; line-height: 30px; font-family: Georgia, serif; font-size: 13px; }
.assessment-mapping .week-cell.marked{ font-weight: 700; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function(){
	const root = document.querySelector('.assessment-mapping');
	if (!root) return;

	// Delegated click handler for week cells (handles dynamically added rows)
	root.addEventListener('click', function(e){
		const cell = e.target.closest('.week-cell');
		if (!cell || !root.contains(cell)) return;
		if (e.target.tagName.toLowerCase() === 'input' || e.target.closest('input')) return;
		if (cell.classList.contains('marked')){
			cell.classList.remove('marked');
			cell.textContent = '';
		} else {
			cell.classList.add('marked');
			cell.textContent = 'x';
		}
	});

	// Ctrl+Enter or Cmd+Enter to add a new data row when focus is inside the mapping
	document.addEventListener('keydown', function(e){
		// Add row: Ctrl/Cmd + Enter
		if ((e.ctrlKey || e.metaKey) && e.key === 'Enter'){
			const active = document.activeElement;
			if (!root.contains(active)) return; // only when focus is inside the mapping
			e.preventDefault();

			// Find the last row that contains week cells to use as a template
			const rows = Array.from(root.querySelectorAll('tbody tr'));
			let templateRow = null;
			for (let i = rows.length - 1; i >= 0; i--) {
				if (rows[i].querySelector('.week-cell')) { templateRow = rows[i]; break; }
			}
			if (!templateRow) return;

		// Clone and prepare the new row. Remove any merge-cell in the clone so there's only one merge cell.
	const newRow = templateRow.cloneNode(true);
	const cloneMerge = newRow.querySelector('.merge-cell');
	if (cloneMerge) cloneMerge.parentNode.removeChild(cloneMerge);

	// Replace the cloned Distribution input cell with a per-row assessment name input
	// (so each added row has its own editable field). Keep styling consistent with other inputs.
	const firstCell = newRow.querySelector('td');
	if (firstCell){
		// clear any existing content in that cell
		firstCell.innerHTML = '';
		const rowInput = document.createElement('input');
		rowInput.type = 'text';
		rowInput.name = 'assessment_name[]';
		rowInput.className = 'form-control cis-input text-center cis-field';
		rowInput.placeholder = 'Assessment';
		rowInput.value = '';
		rowInput.setAttribute('form', 'syllabusForm');
		firstCell.appendChild(rowInput);
	}

	// Clear any week-cell marks in the new row
	newRow.querySelectorAll('.week-cell').forEach(function(c){ c.classList.remove('marked'); c.textContent = ''; });

		// Insert after the template row
		templateRow.parentNode.insertBefore(newRow, templateRow.nextSibling);

		// Update merge-cell rowspan
		updateMergeRowspan();

		// Focus the new row's input if present
		const newInput = newRow.querySelector('input');
		if (newInput) newInput.focus();
		return;
		}

		// Remove row: Ctrl/Cmd + Backspace
		if ((e.ctrlKey || e.metaKey) && e.key === 'Backspace'){
			const active = document.activeElement;
			if (!root.contains(active)) return; // only when focus is inside the mapping
			e.preventDefault();

			// Find the nearest row that contains week cells starting from active element
			const row = active.closest('tr');
			if (!row) return;
			const tbody = row.parentNode;
			// Ensure the row is a data row with week cells
			if (!row.querySelector('.week-cell')) return;

			const dataRows = Array.from(tbody.querySelectorAll('tr')).filter(r => r.querySelector('.week-cell'));
			if (dataRows.length <= 1) {
				// If only one data row remains, clear its values instead of removing
				row.querySelectorAll('.week-cell').forEach(function(c){ c.classList.remove('marked'); c.textContent = ''; });
				const input = row.querySelector('input'); if (input) input.value = '';
				return;
			}

			// If the row contains the merge cell, move it to the next data row before removing
			const mergeCell = row.querySelector('.merge-cell');
			if (mergeCell) {
				// find next data row
				let next = row.nextElementSibling;
				while (next && !next.querySelector('.week-cell')) next = next.nextElementSibling;
				if (next) {
					// move the merge cell into the next data row as first child
					next.insertBefore(mergeCell, next.firstChild);
				}
			}

			// Remove the row
			const prev = row.previousElementSibling;
			row.parentNode.removeChild(row);

			// Update merge-cell rowspan
			updateMergeRowspan();

			// Move focus to previous row's input if available
			let focusRow = prev;
			while (focusRow && !focusRow.querySelector('.week-cell')) focusRow = focusRow.previousElementSibling;
			if (focusRow){
				const focusInput = focusRow.querySelector('input');
				if (focusInput) focusInput.focus();
			}
		}

		// Remove row by pressing Backspace inside an empty input (no modifier)
		if (!e.ctrlKey && !e.metaKey && e.key === 'Backspace'){
			const active = document.activeElement;
			if (!root.contains(active)) return; // only when focus is inside the mapping
			// Only trigger when focus is in an <input> inside a data row
			if (!active || active.tagName.toLowerCase() !== 'input') return;
			const row = active.closest('tr');
			if (!row || !row.querySelector('.week-cell')) return;
			// If input has selection or caret not at start, do not intercept
			try {
				if (typeof active.selectionStart === 'number'){
					if (active.selectionStart !== 0 || active.selectionEnd !== 0) return;
				}
			} catch (err) {
				// ignore and proceed only when input appears empty
			}
			if (active.value && active.value.length > 0) return; // not empty
            // At this point, input is empty and caret at start -> remove/clear row
			e.preventDefault();
			const tbody = row.parentNode;
			const dataRows = Array.from(tbody.querySelectorAll('tr')).filter(r => r.querySelector('.week-cell'));
			if (dataRows.length <= 1) {
				row.querySelectorAll('.week-cell').forEach(function(c){ c.classList.remove('marked'); c.textContent = ''; });
				active.value = '';
				return;
			}
            // If the row contains the merge cell, move it to the next data row before removing
            const mergeCell2 = row.querySelector('.merge-cell');
            if (mergeCell2) {
                let next = row.nextElementSibling;
                while (next && !next.querySelector('.week-cell')) next = next.nextElementSibling;
                if (next) next.insertBefore(mergeCell2, next.firstChild);
            }

            // Remove the row and move focus to previous data row input if any
            const prev = row.previousElementSibling;
            row.parentNode.removeChild(row);

            // Update merge-cell rowspan
            updateMergeRowspan();

            let focusRow = prev;
            while (focusRow && !focusRow.querySelector('.week-cell')) focusRow = focusRow.previousElementSibling;
            if (focusRow){
                const focusInput = focusRow.querySelector('input');
                if (focusInput) focusInput.focus();
            }
		}
	});

    		// Utility: update the merge-cell's rowspan to match the number of data rows + the Distribution row above
    		function updateMergeRowspan(){
    			const root = document.querySelector('.assessment-mapping');
    			if (!root) return;
    			const dataRows = Array.from(root.querySelectorAll('tbody tr')).filter(r => r.querySelector('.week-cell'));
    			const merge = root.querySelector('.merge-cell');
    			if (!merge){
    				// create merge cell and insert into the row above the first data row (Distribution row) when possible
    				if (dataRows.length === 0) return;
    				const firstData = dataRows[0];
    				const insertRow = firstData.previousElementSibling || firstData;
    				const td = document.createElement('td');
    				td.className = 'merge-cell';
    				td.rowSpan = dataRows.length + 1;
    				td.setAttribute('style', 'border:1px solid #343a40; height:30px; width:10%;');
    				insertRow.insertBefore(td, insertRow.firstChild);
    				return;
    			}
    			// span the distribution row + all data rows
    			merge.rowSpan = dataRows.length + 1;
    		}

	// run once on load to set correct rowspan
	updateMergeRowspan();
});
</script>

</div>

