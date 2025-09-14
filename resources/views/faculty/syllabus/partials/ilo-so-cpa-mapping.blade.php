
{{--
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/ilo-so-cpa-mapping.blade.php
* Purpose: Small 2-row × 1-column box to display ILO → SO → CPA mapping placeholder
-------------------------------------------------------------------------------
--}}

<div class="ilo-so-cpa-mapping mb-4">
	<table class="table table-bordered" style="width:100%; table-layout:fixed; border:1px solid #343a40; border-collapse:collapse; font-size:12px;">
		@php
			// $sos is passed from controller as $syllabus->sos
			$sosList = $sos ?? collect();
			// Use exact number of SOs defined in the syllabus (no hard-coded fallback)
			$soCount = $sosList->count();
			$totalCols = 2 + $soCount + 3; // spacer + ILO + SOs + CPA (C,P,A)
			$colWidth = $totalCols > 0 ? (int) floor(100 / $totalCols) : 100;
		@endphp
		<colgroup>
			<col style="width:{{ $colWidth }}%;"> {{-- spacer --}}
			<col style="width:{{ $colWidth }}%;"> {{-- ILO label --}}
			@for ($i = 0; $i < $soCount; $i++)
				<col style="width:{{ $colWidth }}%;">
			@endfor
			<col style="width:{{ $colWidth }}%;"> {{-- C --}}
			<col style="width:{{ $colWidth }}%;"> {{-- P --}}
			<col style="width:{{ $colWidth }}%;"> {{-- A --}}
		</colgroup>

		<tbody>
			<tr>
				<td rowspan="2" style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"></td>
				<td rowspan="2" style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
					<div style="height:100%; display:flex; align-items:center; justify-content:center;">
						<strong style="font-family:Georgia, serif; font-size:12px;">ILOs</strong>
					</div>
				</td>
				<th colspan="{{ max(1, $soCount) + 3 }}" style="border:1px solid #343a40; padding:0.25rem 0.5rem; min-height:3.5rem; vertical-align:middle; font-weight:700; font-family:Georgia, serif; font-size:12px; text-align:center;">STUDENT OUTCOMES (SO): Mapping of Assessment Tasks (AT)</th>
			</tr>
			<tr>
				@php
					// Render SO headers exactly from the syllabus list; if empty, no SO columns are shown
					$soLabels = $sosList->map(fn($s) => $s->code ?? '');
				@endphp
				@foreach ($soLabels as $label)
					<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><strong>{{ $label ?: 'SO' }}</strong></td>
				@endforeach
				<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><strong>C</strong></td>
				<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><strong>P</strong></td>
				<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><strong>A</strong></td>
			</tr>
			<tr>
				<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"></td>
				<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
					{{-- Visible badge-style label for ILO (non-editable) + hidden input so form submission remains unchanged --}}
					<span class="ilo-badge fw-semibold d-inline-block text-center" tabindex="0" style="min-width:48px;">{{ old('ilo_so_cpa_ilos_text', $syllabus?->ilo_so_cpa_ilos_text ?? '') }}</span>
					<input type="hidden" name="ilo_so_cpa_ilos_text[]" form="syllabusForm" value="{{ old('ilo_so_cpa_ilos_text', $syllabus?->ilo_so_cpa_ilos_text ?? '') }}" data-original="{{ $syllabus?->ilo_so_cpa_ilos_text ?? '' }}" />
				</td>
				@for ($i = 1; $i <= $soCount; $i++)
					@php $prop = 'ilo_so_cpa_so' . $i . '_text'; @endphp
					<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
						<input id="ilo_so_cpa_so{{ $i }}_text" name="ilo_so_cpa_so{{ $i }}_text[]" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="-" value="{{ old($prop, $syllabus?->{$prop} ?? '') }}" data-original="{{ $syllabus?->{$prop} ?? '' }}" />
					</td>
				@endfor
				<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
					<input id="ilo_so_cpa_c_text" name="ilo_so_cpa_c_text[]" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="-" value="{{ old('ilo_so_cpa_c_text', $syllabus?->ilo_so_cpa_c_text ?? '') }}" data-original="{{ $syllabus?->ilo_so_cpa_c_text ?? '' }}" />
				</td>
				<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
					<input id="ilo_so_cpa_p_text" name="ilo_so_cpa_p_text[]" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="-" value="{{ old('ilo_so_cpa_p_text', $syllabus?->ilo_so_cpa_p_text ?? '') }}" data-original="{{ $syllabus?->ilo_so_cpa_p_text ?? '' }}" />
				</td>
				<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
					<input id="ilo_so_cpa_a_text" name="ilo_so_cpa_a_text[]" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="-" value="{{ old('ilo_so_cpa_a_text', $syllabus?->ilo_so_cpa_a_text ?? '') }}" data-original="{{ $syllabus?->ilo_so_cpa_a_text ?? '' }}" />
				</td>
			</tr>
		</tbody>
	</table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
	const root = document.querySelector('.ilo-so-cpa-mapping');
	if (!root) return;

	// Helper: find data rows (rows that contain input elements)
	function dataRows() {
		return Array.from(root.querySelectorAll('tbody tr')).filter(r => r.querySelector('input'));
	}

	document.addEventListener('keydown', function(e){
		// Only act when focus is inside this partial
		const active = document.activeElement;
		if (!active || !root.contains(active)) return;

		// Add row: Ctrl/Cmd + Enter
				if ((e.ctrlKey || e.metaKey) && e.key === 'Enter'){
			e.preventDefault();
			const rows = dataRows();
			if (!rows.length) return;
			const template = rows[rows.length - 1];
			const clone = template.cloneNode(true);
			// Clear input values in the cloned row and strip id/data-original to avoid duplicates
					clone.querySelectorAll('input').forEach(function(inp){
						inp.value = '';
						if (inp.hasAttribute('id')) inp.removeAttribute('id');
						if (inp.hasAttribute('data-original')) inp.removeAttribute('data-original');
					});
					// Clear any visible ILO badge text in the clone so it's ready for new content
					clone.querySelectorAll('.ilo-badge').forEach(function(lbl){ lbl.textContent = ''; lbl.className = 'ilo-badge fw-semibold d-inline-block text-center'; });
					// insert after the last data row (append to tbody) to avoid placing above the title/header
					const tbody = root.querySelector('tbody');
					tbody.appendChild(clone);
					// Immediately number the new row so it doesn't appear blank (match ILO list format e.g. ILO1)
					const newRows = dataRows();
					const idx = newRows.indexOf(clone);
					const badge = clone.querySelector('.ilo-badge');
					const newLabel = 'ILO' + (idx + 1);
					if (badge) { badge.textContent = newLabel; }
					const hidden = clone.querySelector('input[type="hidden"][name="ilo_so_cpa_ilos_text[]"]');
					if (hidden) hidden.value = newLabel;
					// Focus the visible badge (so keyboard shortcuts still work) or fallback to hidden input
					const firstVisible = clone.querySelector('.ilo-badge');
					if (firstVisible) firstVisible.focus(); else { const first = clone.querySelector('input'); if (first) first.focus(); }
			return;
		}

		// Remove row: Ctrl/Cmd + Backspace
		if ((e.ctrlKey || e.metaKey) && e.key === 'Backspace'){
			e.preventDefault();
			const rows = dataRows();
			if (!rows.length) return;
			// Find the row containing the active element
			const row = active.closest('tr');
			if (!row) return;
			// If only one data row remains, clear its inputs instead of removing
			if (rows.length <= 1){
				row.querySelectorAll('input').forEach(function(inp){ inp.value = ''; });
				return;
			}
			// Remove the row and move focus to the previous data row's first input
			const idx = rows.indexOf(row);
			row.parentNode.removeChild(row);
			const prev = rows[Math.max(0, idx - 1)];
			if (prev) { const fi = prev.querySelector('input'); if (fi) fi.focus(); }
			return;
		}
	});
});
</script>

<script>
// Keep mapping columns in sync with SO partial (#syllabus-so-sortable)
document.addEventListener('DOMContentLoaded', function(){
	const soContainer = document.getElementById('syllabus-so-sortable');
	const mappingRoot = document.querySelector('.ilo-so-cpa-mapping');
	if (!soContainer || !mappingRoot) return;

	// small debounce helper
	function debounce(fn, wait) {
		let t;
		return function(...args){ clearTimeout(t); t = setTimeout(()=> fn.apply(this, args), wait); };
	}

	function readSoLabels() {
		const rows = Array.from(soContainer.querySelectorAll('tr'));
		return rows.map((r, idx) => {
			const codeInput = r.querySelector('input[name="code[]"]');
			if (codeInput) return (codeInput.value || ('SO' + (idx + 1))).trim();
			// fallback to badge content
			const badge = r.querySelector('.so-badge');
			return badge ? (badge.textContent || ('SO' + (idx + 1))).trim() : ('SO' + (idx + 1));
		});
	}

	function updateColgroup(soCount) {
		const colgroup = mappingRoot.querySelector('colgroup');
		if (!colgroup) return;
		const totalCols = 2 + soCount + 3; // spacer + ILO + SOs + CPA
		const colWidth = totalCols > 0 ? Math.floor(100 / totalCols) : 100;
		let html = '';
		html += `<col style="width:${colWidth}%">`;
		html += `<col style="width:${colWidth}%">`;
		for (let i=0;i<soCount;i++) html += `<col style="width:${colWidth}%">`;
		html += `<col style="width:${colWidth}%">`;
		html += `<col style="width:${colWidth}%">`;
		html += `<col style="width:${colWidth}%">`;
		colgroup.innerHTML = html;
	}

	function updateHeaderRow(labels) {
		const tBody = mappingRoot.querySelector('tbody');
		const rows = Array.from(tBody.querySelectorAll('tr'));
		// header row with SO labels is the second row (index 1)
		const headerRow = rows[1];
		if (!headerRow) return;
		let html = '';
		labels.forEach(label => {
			html += `<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><strong>${label || 'SO'}</strong></td>`;
		});
		// CPA headers
		html += `<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><strong>C</strong></td>`;
		html += `<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><strong>P</strong></td>`;
		html += `<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><strong>A</strong></td>`;
		headerRow.innerHTML = html;
	}

	function updateDataRows(labels) {
		const tBody = mappingRoot.querySelector('tbody');
		const allRows = Array.from(tBody.querySelectorAll('tr'));
		// data rows are those that contain input elements (we keep header rows intact)
		const dataRows = allRows.filter(r => r.querySelector('input'));
			dataRows.forEach((row, idx) => {
				// capture existing values (from hidden input)
				const iloInput = row.querySelector('input[name="ilo_so_cpa_ilos_text[]"]');
				const iloVal = iloInput ? iloInput.value : '';
			const existingSoInputs = Array.from(row.querySelectorAll('input[id^="ilo_so_cpa_so"]')).map(i => i.value);
			const cInput = row.querySelector('input[name="ilo_so_cpa_c_text[]"]');
			const pInput = row.querySelector('input[name="ilo_so_cpa_p_text[]"]');
			const aInput = row.querySelector('input[name="ilo_so_cpa_a_text[]"]');
			const cVal = cInput ? cInput.value : '';
			const pVal = pInput ? pInput.value : '';
			const aVal = aInput ? aInput.value : '';

			// rebuild row: first spacer cell + ILO cell
			let html = '';
			html += '<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"></td>';
			// Visible badge-style label + hidden input to preserve form data. If there's no ILO text, show a numbered fallback (ILO1)
			const visibleIlo = iloVal && String(iloVal).trim() ? iloVal : ('ILO' + (idx + 1));
			html += `<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">`;
			html += `<span class="ilo-badge fw-semibold d-inline-block text-center" tabindex="0" style="min-width:48px;">${escapeHtml(visibleIlo)}</span>`;
			// submit original value if present, otherwise submit the fallback label so server still gets something meaningful
			html += `<input type="hidden" name="ilo_so_cpa_ilos_text[]" form="syllabusForm" value="${escapeHtml(iloVal || visibleIlo)}" />`;
			html += '</td>';

			// SO input columns
			for (let i = 0; i < labels.length; i++) {
				const propIndex = i + 1;
				const propName = `ilo_so_cpa_so${propIndex}_text[]`;
				const val = existingSoInputs[i] ?? '';
				html += `<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><input name="${propName}" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="-" value="${escapeHtml(val)}" /></td>`;
			}

			// CPA inputs
			html += `<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><input name="ilo_so_cpa_c_text[]" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="-" value="${escapeHtml(cVal)}" /></td>`;
			html += `<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><input name="ilo_so_cpa_p_text[]" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="-" value="${escapeHtml(pVal)}" /></td>`;
			html += `<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><input name="ilo_so_cpa_a_text[]" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="-" value="${escapeHtml(aVal)}" /></td>`;

			row.innerHTML = html;
		});
	}

	// simple HTML escaper for attribute/value injection
	function escapeHtml(str) {
		if (str === null || str === undefined) return '';
		return String(str).replace(/[&<>"']/g, function(m){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[m]; });
	}

	function syncNow() {
		const labels = readSoLabels();
		updateColgroup(labels.length);
		updateHeaderRow(labels);
		updateDataRows(labels);
	}

	const debouncedSync = debounce(syncNow, 120);

	// initial sync
	syncNow();

	// Observe childList changes (add/remove rows) and attribute changes inside SO container
	const mo = new MutationObserver(debouncedSync);
	mo.observe(soContainer, { childList: true, subtree: true, attributes: true, attributeFilter: ['value'] });

	// Listen to input events (e.g. code[] hidden inputs edited) to update header labels live
	soContainer.addEventListener('input', debouncedSync);

		// Also sync mapping rows with ILO list changes (#syllabus-ilo-sortable)
		const iloContainer = document.getElementById('syllabus-ilo-sortable');
		if (iloContainer) {
			function syncRowsToIlo() {
				const iloRows = Array.from(iloContainer.querySelectorAll('tr'));
				const tBody = mappingRoot.querySelector('tbody');
				const allRows = Array.from(tBody.querySelectorAll('tr'));
				let dataRows = allRows.filter(r => r.querySelector('input[name="ilo_so_cpa_ilos_text[]"]'));

				const desired = Math.max(1, iloRows.length);
				let current = dataRows.length;

				// Add rows if needed: clone last data row if available, otherwise build a new one
				if (current < desired) {
					const template = dataRows.length ? dataRows[dataRows.length - 1] : null;
					for (let i = current; i < desired; i++) {
						let newRow;
						if (template) {
							newRow = template.cloneNode(true);
							// clear inputs and identifiers
							newRow.querySelectorAll('input').forEach(inp => { inp.value = ''; if (inp.hasAttribute('id')) inp.removeAttribute('id'); if (inp.hasAttribute('data-original')) inp.removeAttribute('data-original'); });
							// reset visible badge(s)
							newRow.querySelectorAll('.ilo-badge').forEach(b => { b.textContent = ''; b.className = 'ilo-badge fw-semibold d-inline-block text-center'; });
						} else {
							// Build a minimal row matching the structure: spacer + ilo badge + so inputs + c/p/a
							newRow = document.createElement('tr');
							let inner = '';
							inner += '<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"></td>';
							inner += '<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">';
							inner += '<span class="ilo-badge fw-semibold d-inline-block text-center" tabindex="0" style="min-width:48px;"></span>';
							inner += '<input type="hidden" name="ilo_so_cpa_ilos_text[]" form="syllabusForm" value="" />';
							inner += '</td>';
							// SO inputs: create as many as current header labels
							const soCount = mappingRoot.querySelectorAll('tbody tr')[1] ? mappingRoot.querySelectorAll('tbody tr')[1].querySelectorAll('td').length - 3 : 0;
							for (let j = 0; j < soCount; j++) inner += '<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><input name="ilo_so_cpa_so' + (j+1) + '_text[]" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="-" value="" /></td>';
							inner += '<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><input name="ilo_so_cpa_c_text[]" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="-" value="" /></td>';
							inner += '<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><input name="ilo_so_cpa_p_text[]" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="-" value="" /></td>';
							inner += '<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><input name="ilo_so_cpa_a_text[]" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="-" value="" /></td>';
							newRow.innerHTML = inner;
						}
						// Always append new data rows to the end of the tbody so they appear after existing data rows
						tBody.appendChild(newRow);
						current++;
					}
				}

				// Refresh dataRows reference and then sync labels/hidden inputs to ILO badges
				const updatedAll = Array.from(tBody.querySelectorAll('tr'));
				dataRows = updatedAll.filter(r => r.querySelector('input[name="ilo_so_cpa_ilos_text[]"]'));
				dataRows.forEach((row, idx) => {
					const iloRow = iloRows[idx];
					let labelText = 'ILO' + (idx + 1); // no space to match existing format
					if (iloRow) {
						const badge = iloRow.querySelector('.ilo-badge');
						if (badge && badge.textContent) labelText = badge.textContent.trim();
					}
					const badgeEl = row.querySelector('.ilo-badge');
					if (badgeEl) { badgeEl.textContent = labelText; badgeEl.className = 'ilo-badge fw-semibold d-inline-block text-center'; }
					const hidden = row.querySelector('input[type="hidden"][name="ilo_so_cpa_ilos_text[]"]');
					if (hidden) hidden.value = labelText;
				});

				// If we have too many rows, remove extras from the end but keep at least one
				if (dataRows.length > desired) {
					for (let i = dataRows.length - 1; i >= desired; i--) {
						const toRemove = dataRows[i];
						if (!toRemove) continue;
						// If removing would leave zero, instead clear fields
						if (dataRows.length <= 1) {
							toRemove.querySelectorAll('input').forEach(inp => inp.value = '');
						} else {
							toRemove.parentNode.removeChild(toRemove);
						}
					}
				}
			}

			const iloMo = new MutationObserver(debounce(syncRowsToIlo, 100));
			iloMo.observe(iloContainer, { childList: true, subtree: true, attributes: true, attributeFilter: ['value'] });
			iloContainer.addEventListener('input', debounce(syncRowsToIlo, 100));
			// initial sync
			syncRowsToIlo();
		}
});
</script>
