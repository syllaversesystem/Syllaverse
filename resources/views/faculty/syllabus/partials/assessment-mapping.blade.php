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
				<th class="merge-cell" rowspan="1" style="border:1px solid #343a40; height:30px; width:10%;"></th>
				<th style="border:1px solid #343a40; height:30px; width:14%; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center;">Distribution</th>
				@for ($i = 1; $i <= 16; $i++)
					<th style="border:1px solid #343a40; height:30px; width:auto; padding:0.15rem 0.25rem; text-align:center; font-family:Georgia, serif; font-size:12px;">@if ($i == 1) 1-2 @else {{ $i + 1 }} @endif</th>
				@endfor
			</tr>
			<tr>
				<td style="border:1px solid #343a40; height:30px; width:14%; padding:0.12rem 0.18rem; text-align:center;">
					<input type="text" name="mapping_name[]" form="syllabusForm" value="" class="form-control cis-input text-center cis-field" placeholder="-" />
				</td>
				@for ($i = 1; $i <= 16; $i++)
					<td class="week-cell" data-week="{{ $i == 1 ? '1-2' : $i + 1 }}" style="border:1px solid #343a40; height:30px; width:auto; padding:0;"></td>
				@endfor
			</tr>
	</tbody>
</table>

<!-- Hidden textarea to hold serialized assessment mappings (Assessment Tasks distribution removed) -->
<textarea id="assessment_mappings_data" name="assessment_mappings_data" form="syllabusForm" class="d-none" aria-hidden="true"></textarea>

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

	// Keyboard shortcuts (Ctrl+Enter / Ctrl+Backspace / Backspace empty-row removal) removed per request.

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

	// --- Hydration: render existing mappings from server into the table ---
	// Inject server-provided mappings (assumes $syllabus is available in the view)
	const __existingAssessmentMappings = @json(isset($syllabus) ? ($syllabus->assessmentMappings->map(function($m){ return ['name' => $m->name, 'week_marks' => $m->week_marks, 'position' => $m->position]; })->toArray()) : []);

	(function hydrateMappings(){
		if (!Array.isArray(__existingAssessmentMappings) || __existingAssessmentMappings.length === 0) return;
		// Ensure we have enough data rows to show all mappings
		function dataRows(){ return Array.from(root.querySelectorAll('tbody tr')).filter(r => r.querySelector('.week-cell')); }
		function ensureRows(n){
			while (dataRows().length < n) {
				const rows = dataRows();
				const templateRow = rows.length ? rows[rows.length - 1] : root.querySelector('tbody tr');
				if (!templateRow) break;
				const newRow = templateRow.cloneNode(true);
				const cloneMerge = newRow.querySelector('.merge-cell');
				if (cloneMerge) cloneMerge.parentNode.removeChild(cloneMerge);
				// clear inputs and marks
				const nameInput = newRow.querySelector('input[name="mapping_name[]"]');
				const distInput = newRow.querySelector('input[name="mapping_distribution[]"]');
				// copy placeholders from template to cloned inputs so UX is consistent
				try {
					const tplName = templateRow ? templateRow.querySelector('input[name="mapping_name[]"]') : null;
					const tplDist = templateRow ? templateRow.querySelector('input[name="mapping_distribution[]"]') : null;
					if (nameInput) { nameInput.value = ''; if (tplName && tplName.placeholder) nameInput.placeholder = tplName.placeholder; }
					if (distInput) { distInput.value = ''; if (tplDist && tplDist.placeholder) distInput.placeholder = tplDist.placeholder; }
				} catch (e) { if (nameInput) nameInput.value = ''; if (distInput) distInput.value = ''; }
				newRow.querySelectorAll('.week-cell').forEach(function(c){ c.classList.remove('marked'); c.textContent = ''; });
				templateRow.parentNode.insertBefore(newRow, templateRow.nextSibling);
				updateMergeRowspan();
			}
		}

		ensureRows(__existingAssessmentMappings.length);

		// Apply each mapping into its corresponding row
		const rows = dataRows();
		__existingAssessmentMappings.forEach(function(mapping, idx){
			const tr = rows[idx];
			if (!tr) return;
			// set mapping name input if present
			const nameInput = tr.querySelector('input[name="mapping_name[]"]');
			if (nameInput) nameInput.value = mapping.name || '';
			// Determine per-row distribution (support both legacy array-of-strings and new object shape)
			let distVal = '';
			if (Array.isArray(mapping.week_marks) && mapping.week_marks.length){
				const first = mapping.week_marks[0];
				if (first && typeof first === 'object' && 'distribution' in first) distVal = first.distribution || '';
				else distVal = '';
			}
			if (!distVal && mapping.distribution) distVal = mapping.distribution;
			const distInput = tr.querySelector('input[name="mapping_distribution[]"]');
			if (distInput) distInput.value = distVal || '';

			// mark week-cells
			if (Array.isArray(mapping.week_marks)){
				mapping.week_marks.forEach(function(w){
					let week = w;
					if (w && typeof w === 'object' && 'week' in w) week = w.week;
					if (week === null || typeof week === 'undefined') return;
					week = String(week);
					tr.querySelectorAll('.week-cell').forEach(function(c){
						const cw = c.getAttribute('data-week') || (c.textContent || '').trim();
						if (String(cw) === week){ c.classList.add('marked'); c.textContent = 'x'; }
					});
				});
			}
		});
	})();


	// Serializer: build a rows array representing each data row: { section, code, task, ird, percent, iloFlags, c, p, a }
	function serializeRows(){
		const rows = [];
		const root = document.querySelector('.assessment-mapping');
		if (!root) return rows;
		// Data rows are tbody tr that contain .week-cell
		const dataRows = Array.from(root.querySelectorAll('tbody tr')).filter(r => r.querySelector('.week-cell'));
		dataRows.forEach(function(tr){
			// Static name now comes from span.mapping-name-static if present
			const staticNameEl = tr.querySelector('.mapping-name-static');
			const section = staticNameEl ? (staticNameEl.getAttribute('data-mapping-name') || staticNameEl.textContent || '').trim() : '';

			const weeks = [];
			tr.querySelectorAll('.week-cell').forEach(function(c){
				if (c.classList.contains('marked')) {
					const w = c.getAttribute('data-week') || c.textContent.trim();
					weeks.push(w);
				}
			});

			rows.push({
				name: section,
				code: '', task: '', ird: '', percent: '', iloFlags: [], c: '', p: '', a: '', weeks: weeks,
				distribution: (function(){
					const globalEl = document.querySelector('select[name="assessment_distribution"], input[name="assessment_distribution"]');
					if (!globalEl) return '';
					const tag = (globalEl.tagName || '').toLowerCase();
					if (tag === 'select'){
						const opt = globalEl.options[globalEl.selectedIndex];
						return opt ? ((opt.text || opt.textContent || opt.value)+'').trim() : '';
					}
					return (globalEl.value || '').trim();
				})(),
			});
		});
		return rows;
	}

	// On syllabus form submit, serialize only assessment mappings (Assessment Tasks integration removed)
	const mainForm = document.getElementById('syllabusForm');
	if (mainForm) {
		mainForm.addEventListener('submit', function(){
			try {
				const rows = serializeRows();
				const ta = document.getElementById('assessment_mappings_data');
				if (ta) ta.value = JSON.stringify(rows);
			} catch (err) { console.warn('Failed to serialize assessment mappings', err); }
		}, {capture: true});
	}

	// Expose a lightweight save function so the top Save can call it before performing the main form save.
	// This function ensures the serialized JSON is written into the hidden textarea for mappings.
	try {
		window.saveAssessmentMappings = async function() {
			try {
				const rows = serializeRows() || [];
				const ta = document.getElementById('assessment_mappings_data');
				if (ta) ta.value = JSON.stringify(rows);
				// small pause to allow any pending input handlers to settle
				await new Promise(r => setTimeout(r, 10));
				return { success: true };
			} catch (e) {
				console.warn('saveAssessmentMappings failed', e);
				throw e;
			}
		};
	} catch (e) { /* noop */ }

	// Expose a page-level POST helper so the top-level Save flow can persist mapping rows.

	// New: helper to post assessment mappings (name + week_marks)
	if (typeof window.postAssessmentMappings !== 'function') {
		window.postAssessmentMappings = async function(syllabusId){
			try {
				const rows = serializeRows() || [];
					// Build mappings array with name + week_marks, preferring human-readable distribution names
					function displayFrom(el){
						if (!el) return '';
						const tag = (el.tagName || '').toLowerCase();
						if (tag === 'select'){
							const opt = el.options[el.selectedIndex];
							return opt ? ((opt.text || opt.textContent || opt.value) + '').trim() : '';
						}
						if (el.dataset && el.dataset.name) return (el.dataset.name + '').trim();
						return (el.value || '').trim();
					}
					const mappings = rows.map(function(r, idx){
						const globalEl = document.querySelector('select[name="assessment_distribution"], input[name="assessment_distribution"]');
						const globalVal = displayFrom(globalEl);
						const rowEl = null; // we already have r.distribution captured; but prefer display from the DOM when possible
						const weekMarks = (r.weeks || []).map(function(w){
							// try to find the corresponding row DOM to extract display name if present
							let distVal = '';
							try {
								const tr = document.querySelectorAll('tbody tr').item(idx);
								const el = tr ? tr.querySelector('select[name="mapping_distribution[]"], input[name="mapping_distribution[]"]') : null;
								distVal = displayFrom(el) || (r.distribution || '').trim() || globalVal;
							} catch (e) {
								distVal = (r.distribution || '').trim() || globalVal;
							}
							return { week: w, distribution: distVal };
						});
						return {
							name: r.name || r.section || '',
							week_marks: weekMarks,
							position: idx
						};
					});

				const body = JSON.stringify({ mappings: mappings });
				const csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || document.querySelector('input[name="_token"]')?.value || '';
				const url = (window.syllabusBasePath || '/faculty/syllabi') + '/' + encodeURIComponent(syllabusId) + '/assessment-mappings';

				try {
					const resp = await fetch(url, {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json',
							'X-CSRF-TOKEN': csrf
						},
						body: body,
						keepalive: true,
						credentials: 'same-origin'
					});
					return resp;
				} catch (fetchErr) {
					// Beacon fallback
					try { if (navigator && typeof navigator.sendBeacon === 'function') navigator.sendBeacon(url, body); } catch (beaconErr) { console.warn('postAssessmentMappings: beacon fallback failed', beaconErr); }
					throw fetchErr;
				}
			} catch (err) {
				console.warn('postAssessmentMappings failed', err);
				throw err;
			}
		};
	}
});
</script>

</div>

