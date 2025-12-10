<!-- Section divider above Assessment Mapping: centered AI mapping button (no functionality yet) -->
<div class="sv-partial-sep" role="separator" aria-label="Section divider">
	<style>
		.sv-partial-sep { display:flex; align-items:center; gap:10px; margin: 12px 0 16px; }
		.sv-partial-sep .sep-line { flex:1 1 auto; height:1px; background:#e2e5e9; }
		.sv-partial-sep .sv-ai-map-btn { background:#fff; border:1px solid #e2e5e9; border-radius:999px; padding:.3rem .7rem; font-size:.85rem; color:#CB3737; display:inline-flex; align-items:center; gap:.4rem; }
		.sv-partial-sep .sv-ai-map-btn i { font-size:1rem; }
		.sv-partial-sep .sv-ai-map-btn:hover { background: linear-gradient(135deg, rgba(255,240,235,.88), rgba(255,255,255,.46)); box-shadow: 0 4px 10px rgba(203,55,55,.12); }
		.sv-partial-sep .sv-ai-map-btn:active { transform: translateY(0); filter: brightness(.98); }
	</style>
	<div class="sep-line" aria-hidden="true"></div>
	<button type="button" class="btn btn-sm sv-ai-map-btn" id="svAiAutoMapBtn" title="AI Map" aria-label="AI Map">
		<i class="bi bi-stars" aria-hidden="true"></i>
		AI Map
	</button>
	<div class="sep-line" aria-hidden="true"></div>
</div>

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
<script src="{{ Vite::asset('resources/js/faculty/ai-map.js') }}" type="module"></script>
<script>
	// Ensure ai-map initializes after DOM
	document.addEventListener('DOMContentLoaded', function(){
		try { if (window._aiMap && typeof window._aiMap.collectPhasePayloads === 'function') window._aiMap.collectPhasePayloads(); } catch(e) {}
	});
	// Shortcut note: Cmd+Shift+R (mac) / Ctrl+Shift+R (win) opens AI Map Input Viewer
</script>
@endpush
