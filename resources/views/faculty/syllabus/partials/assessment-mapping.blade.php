<div class="assessment-mapping" data-syllabus-id="{{ $syllabus->id ?? '' }}">
<table class="table table-bordered mb-4" style="width:100%; border:1px solid #343a40; border-collapse:collapse;">
	<thead>
		<tr>
			<th colspan="2" style="border:1px solid #343a40; height:30px; width:20%; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center;">Assessment Schedule</th>
			<th style="border:1px solid #343a40; height:30px; width:80%; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center;">Week No.</th>
		</tr>
		<tr>
			<th class="cis-label assessment-method-header" style="border:1px solid #343a40; width:30px; padding:0; color:#000; font-weight:700; text-align:center; vertical-align:middle; font-family:Georgia, serif !important; font-size:13px; height:auto; position:relative; overflow:hidden;">
				<div class="assessment-method-text" style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%) rotate(-90deg); writing-mode:horizontal-tb; white-space:nowrap;">Assessment Method</div>
			</th>
			<td class="distribution-table" style="border:1px solid #343a40; padding:0;">
				<table class="distribution" style="width:100%; border:none; border-collapse:collapse;">
					<tr>
						<th class="distribution-header" style="border:none; border-bottom:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; color:#000; font-weight:bold; text-align:center;">Distribution</th>
					</tr>
					<tr>
						<td class="task" style="border:none; height:30px; padding:0; background-color:#fff;">
							<input type="text" class="form-control form-control-sm distribution-input" placeholder="-" style="width:100%; height:22px; border:none; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box;">
						</td>
					</tr>
				</table>
			</td>
			<td class="week-table" style="border:1px solid #343a40; padding:0;">
				<table class="week" style="width:100%; border:none; border-collapse:collapse; table-layout:fixed;">
					<tr>
						<th class="week-number" style="border:none; border-bottom:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; color:#6c757d; font-weight:normal; text-align:center;">No weeks</th>
					</tr>
					<tr>
						<td class="week-mapping" style="border:none; height:30px; padding:0.2rem 0.5rem; background-color:#fff; height:30px;"></td>
					</tr>
				</table>
			</td>
		</tr>
	</thead>
</table>
</div>


@push('scripts')
<script>
	// Ensure ai-map initializes after DOM
	document.addEventListener('DOMContentLoaded', function(){
		try { if (window._aiMap && typeof window._aiMap.collectPhasePayloads === 'function') window._aiMap.collectPhasePayloads(); } catch(e) {}
		// Hook progress UI helpers if provided
		try {
			window._svAiMapProgress = {
				set: function(stage, pct, validationText, state){
					const wrap = document.getElementById('svAiMapProgressWrap');
					const fill = document.getElementById('svAiMapProgressFill');
					const label = document.getElementById('svAiMapStage');
					const pctEl = document.getElementById('svAiMapPct');
					const val = document.getElementById('svAiMapValidation');
					if (wrap) wrap.style.display = 'block';
					if (fill) fill.style.width = (pct || 0) + '%';
					if (fill) { fill.classList.remove('state-ok','state-warn','state-running'); if (state) fill.classList.add(state); }
					if (label) label.textContent = stage || 'Processing';
					if (pctEl) pctEl.textContent = ((pct || 0)|0) + '%';
					if (val && validationText) val.querySelector('span').textContent = validationText;
				},
				hide: function(){ const wrap = document.getElementById('svAiMapProgressWrap'); if (wrap) wrap.style.display='none'; }
			};
		} catch(e) {}
	});
	// Shortcut note: Cmd+Shift+R (mac) / Ctrl+Shift+R (win) opens AI Map Input Viewer
</script>
@endpush
