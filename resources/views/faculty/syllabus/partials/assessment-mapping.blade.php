<div class="assessment-mapping" data-syllabus-id="{{ $syllabus->id ?? '' }}">
<table class="table table-bordered mb-4" style="width:100%; table-layout:fixed; border:1px solid #343a40; border-collapse:collapse;">
	<thead>
		<tr>
			<th colspan="2" style="border:1px solid #343a40; height:30px; width:30%; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center;">Assessment Schedule</th>
			<th style="border:1px solid #343a40; height:30px; width:70%; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center;">Week No.</th>
		</tr>
		<tr>
			<th class="align-top text-start cis-label" style="border:1px solid #343a40; height:30px; width:15%; padding:0.2rem 0.5rem; color:#000; font-weight:700;">Assessment Method</th>
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
