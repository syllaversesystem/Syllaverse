
{{--
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/ilo-so-cpa-mapping.blade.php
* Purpose: Small 2-row × 1-column box to display ILO → SO → CPA mapping placeholder
-------------------------------------------------------------------------------
--}}

<div class="ilo-so-cpa-mapping mb-4">
	<table class="table table-bordered" style="width:100%; table-layout:fixed; border:1px solid #343a40; border-collapse:collapse; font-size:12px;">
		<colgroup>
			<col style="width:9.09%;">
			<col style="width:9.09%;">
			<col style="width:9.09%;">
			<col style="width:9.09%;">
			<col style="width:9.09%;">
			<col style="width:9.09%;">
			<col style="width:9.09%;">
			<col style="width:9.09%;">
			<col style="width:9.09%;">
			<col style="width:9.09%;">
			<col style="width:9.09%;">
		</colgroup>

		<tbody>
			<tr>
				<td rowspan="2" style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"></td>
				<td rowspan="2" style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
					<div style="height:100%; display:flex; align-items:center; justify-content:center;">
						<strong style="font-family:Georgia, serif; font-size:12px;">ILOs</strong>
					</div>
				</td>
				<th colspan="9" style="border:1px solid #343a40; padding:0.25rem 0.5rem; min-height:3.5rem; vertical-align:middle; font-weight:700; font-family:Georgia, serif; font-size:12px; text-align:center;">STUDENT OUTCOMES (SO): Mapping of Assessment Tasks (AT)</th>
			</tr>
			<tr>
				<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><strong>SO 1</strong></td>
				<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><strong>SO 2</strong></td>
				<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><strong>SO 3</strong></td>
				<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><strong>SO 4</strong></td>
				<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><strong>SO 5</strong></td>
				<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><strong>SO 6</strong></td>
				<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><strong>C</strong></td>
				<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><strong>P</strong></td>
				<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><strong>A</strong></td>
			</tr>
			<tr>
				<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"></td>
				<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
					<input id="ilo_so_cpa_ilos_text" name="ilo_so_cpa_ilos_text[]" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="Enter ILOs" value="{{ old('ilo_so_cpa_ilos_text', $syllabus?->ilo_so_cpa_ilos_text ?? '') }}" data-original="{{ $syllabus?->ilo_so_cpa_ilos_text ?? '' }}" />
				</td>
				<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
					<input id="ilo_so_cpa_so1_text" name="ilo_so_cpa_so1_text[]" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="-" value="{{ old('ilo_so_cpa_so1_text', $syllabus?->ilo_so_cpa_so1_text ?? '') }}" data-original="{{ $syllabus?->ilo_so_cpa_so1_text ?? '' }}" />
				</td>
				<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
					<input id="ilo_so_cpa_so2_text" name="ilo_so_cpa_so2_text[]" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="-" value="{{ old('ilo_so_cpa_so2_text', $syllabus?->ilo_so_cpa_so2_text ?? '') }}" data-original="{{ $syllabus?->ilo_so_cpa_so2_text ?? '' }}" />
				</td>
				<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
					<input id="ilo_so_cpa_so3_text" name="ilo_so_cpa_so3_text[]" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="-" value="{{ old('ilo_so_cpa_so3_text', $syllabus?->ilo_so_cpa_so3_text ?? '') }}" data-original="{{ $syllabus?->ilo_so_cpa_so3_text ?? '' }}" />
				</td>
				<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
					<input id="ilo_so_cpa_so4_text" name="ilo_so_cpa_so4_text[]" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="-" value="{{ old('ilo_so_cpa_so4_text', $syllabus?->ilo_so_cpa_so4_text ?? '') }}" data-original="{{ $syllabus?->ilo_so_cpa_so4_text ?? '' }}" />
				</td>
				<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
					<input id="ilo_so_cpa_so5_text" name="ilo_so_cpa_so5_text[]" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="-" value="{{ old('ilo_so_cpa_so5_text', $syllabus?->ilo_so_cpa_so5_text ?? '') }}" data-original="{{ $syllabus?->ilo_so_cpa_so5_text ?? '' }}" />
				</td>
				<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
					<input id="ilo_so_cpa_so6_text" name="ilo_so_cpa_so6_text[]" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="-" value="{{ old('ilo_so_cpa_so6_text', $syllabus?->ilo_so_cpa_so6_text ?? '') }}" data-original="{{ $syllabus?->ilo_so_cpa_so6_text ?? '' }}" />
				</td>
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
			template.parentNode.insertBefore(clone, template.nextSibling);
			// Focus the first input in the new row
			const first = clone.querySelector('input'); if (first) first.focus();
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
