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
// Hydrate assessment mappings from server data
document.addEventListener('DOMContentLoaded', function() {
	const existingMappings = @json(isset($syllabus) ? ($syllabus->assessmentMappings->map(function($m){ return ['name' => $m->name, 'week_marks' => $m->week_marks, 'position' => $m->position]; })->toArray()) : []);
	
	if (window.hydrateAssessmentMappings) {
		window.hydrateAssessmentMappings(existingMappings);
	}
});
</script>

</div>

