
{{--
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/assessment-mapping.blade.php
* Purpose: Minimal 2×2 assessment mapping box with dark borders to match other modules
-------------------------------------------------------------------------------
--}}

<table class="table table-bordered mb-4" style="width:100%; border:1px solid #343a40; border-collapse:collapse;">
	<tbody>
		<thead>
			<tr>
				<th colspan="2" style="border:1px solid #343a40; height:30px; width:24%; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center;" class="text-center">Assessment Schedule</th>
				<th colspan="16" style="border:1px solid #343a40; height:30px; width:65%; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center;" class="text-center">Week No.</th>
			</tr>
		</thead>
			<tr>
				<td style="border:1px solid #343a40; height:30px; width:10%;"></td>
				<td style="border:1px solid #343a40; height:30px; width:14%; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center;">Distribution</td>
				@for ($i = 1; $i <= 16; $i++)
					<td style="border:1px solid #343a40; height:30px; width:auto; padding:0.15rem 0.25rem; text-align:center; font-family:Georgia, serif; font-size:12px;">@if ($i == 1) 1-2 @else {{ $i + 1 }} @endif</td>
				@endfor
			</tr>
			<tr>
				<td style="border:1px solid #343a40; height:30px; width:10%;"></td>
				<td style="border:1px solid #343a40; height:30px; width:14%; padding:0.12rem 0.18rem; text-align:center;">
					<input type="text" name="assessment_distribution" form="syllabusForm" value="{{ old('assessment_distribution', '') }}" class="form-control cis-input text-center cis-field" placeholder="" />
				</td>
				@for ($i = 1; $i <= 16; $i++)
					<td style="border:1px solid #343a40; height:30px; width:auto; padding:0;"></td>
				@endfor
			</tr>
	</tbody>
</table>

