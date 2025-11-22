<div class="ilo-so-cpa-mapping mb-4">
	<table class="table table-bordered" style="width:100%; border:1px solid #343a40; border-collapse:collapse;">
		<thead>
			<tr>
				<th class="partial-label" style="border:1px solid #343a40; width:34.050px; padding:0; color:#000; font-weight:700; text-align:center; vertical-align:middle; font-family:Georgia, serif !important; font-size:13px; height:auto; position:relative; overflow:hidden;">
					<div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%) rotate(-90deg); writing-mode:horizontal-tb; white-space:nowrap;">ILO-SO and ILO-CPA Mapping</div>
				</th>
				<th style="border:1px solid #343a40; padding:0;">
					<table class="mapping" style="width:100%; border:none; border-collapse:collapse; table-layout:fixed;">
						<colgroup>
							<col style="width:60px">
							<col>
							<col>
							<col>
							<col>
						</colgroup>
						<tr>
							<th rowspan="2" style="border:none; border-bottom:1px solid #343a40; border-right:1px solid #343a40; width:60px; height:30px; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center; vertical-align:middle;">ILOs</th>
							<th colspan="4" style="border:none; border-bottom:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center;">STUDENT OUTCOMES (SO): Mapping of Assessment Tasks (AT)</th>
						</tr>
						<tr>
							<th style="border:none; border-bottom:1px solid #343a40; border-right:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center;">SO1</th>
							<th style="border:none; border-bottom:1px solid #343a40; border-right:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center;">C</th>
							<th style="border:none; border-bottom:1px solid #343a40; border-right:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center;">P</th>
							<th style="border:none; border-bottom:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center;">A</th>
						</tr>
						<tr>
							<td style="border:none; border-right:1px solid #343a40; padding:0.2rem 0.5rem; font-family:Georgia, serif; font-size:13px; color:#111; text-align:center; vertical-align:middle;">ILO1</td>
							<td style="border:none; border-right:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;">
								<textarea class="form-control form-control-sm" placeholder="-" rows="1" style="width:100%; min-height:22px; border:none; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; resize:none; overflow:hidden;"></textarea>
							</td>
							<td style="border:none; border-right:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;">
								<textarea class="form-control form-control-sm" placeholder="-" rows="1" style="width:100%; min-height:22px; border:none; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; resize:none; overflow:hidden;"></textarea>
							</td>
							<td style="border:none; border-right:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;">
								<textarea class="form-control form-control-sm" placeholder="-" rows="1" style="width:100%; min-height:22px; border:none; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; resize:none; overflow:hidden;"></textarea>
							</td>
							<td style="border:none; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;">
								<textarea class="form-control form-control-sm" placeholder="-" rows="1" style="width:100%; min-height:22px; border:none; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; resize:none; overflow:hidden;"></textarea>
							</td>
						</tr>
					</table>
				</th>
			</tr>
		</thead>
	</table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const mapping = document.querySelector('.ilo-so-cpa-mapping');
	if (!mapping) return;

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
});
</script>
