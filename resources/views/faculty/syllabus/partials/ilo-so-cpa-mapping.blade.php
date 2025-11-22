<div class="ilo-so-cpa-mapping mb-4">
	<table class="table table-bordered" style="width:100%; border:1px solid #343a40; border-collapse:collapse; overflow:visible;">
		<thead>
			<tr>
				<th class="partial-label" style="border:1px solid #343a40; width:50px; padding:0; color:#000; font-weight:700; text-align:center; vertical-align:middle; font-family:Georgia, serif !important; font-size:13px; height:auto; position:relative; overflow:hidden;">
					<div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%) rotate(-90deg); writing-mode:horizontal-tb; white-space:nowrap; max-width:500px; overflow:hidden; text-overflow:ellipsis;">ILO-SO and ILO-CPA<br>Mapping</div>
				</th>
				<th style="border:none; padding:0;">
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
							<th colspan="4" style="border:none; border-bottom:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;">STUDENT OUTCOMES (SO): Mapping of Assessment Tasks (AT)</th>
						</tr>
						<tr>
							<th style="border:none; border-bottom:1px solid #343a40; border-right:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:400; font-style:italic; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#999; text-align:center;">No SO</th>
							<th style="border:none; border-bottom:1px solid #343a40; border-right:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center;">C</th>
							<th style="border:none; border-bottom:1px solid #343a40; border-right:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center;">P</th>
							<th style="border:none; border-bottom:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center;">A</th>
						</tr>
						<tr>
							<td style="border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; font-family:Georgia, serif; font-size:13px; text-align:center; vertical-align:middle; color:#999; font-style:italic;">No ILO</td>
							<td style="border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle;">
								<textarea class="form-control form-control-sm" placeholder="-" rows="1" style="width:100%; min-height:22px; border:none; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; resize:none; overflow:hidden; background-color:#f8f9fa; cursor:not-allowed;" disabled></textarea>
							</td>
							<td style="border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle; background-color:#f8f9fa;">
								<textarea class="form-control form-control-sm" placeholder="-" rows="1" style="width:100%; min-height:22px; border:none; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; resize:none; overflow:hidden; background-color:#f8f9fa; cursor:not-allowed;" disabled></textarea>
							</td>
							<td style="border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle; background-color:#f8f9fa;">
								<textarea class="form-control form-control-sm" placeholder="-" rows="1" style="width:100%; min-height:22px; border:none; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; resize:none; overflow:hidden; background-color:#f8f9fa; cursor:not-allowed;" disabled></textarea>
							</td>
							<td style="border:none; border-top:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle; background-color:#f8f9fa;">
								<textarea class="form-control form-control-sm" placeholder="-" rows="1" style="width:100%; min-height:22px; border:none; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; resize:none; overflow:hidden; background-color:#f8f9fa; cursor:not-allowed;" disabled></textarea>
							</td>
						</tr>
					</table>
				</th>
			</tr>
		</thead>
	</table>
	
	<div class="mt-2">
		<button type="button" class="btn btn-sm btn-primary" onclick="addIloRow()">
			<i class="bi bi-plus-circle"></i> Add Row
		</button>
		<button type="button" class="btn btn-sm btn-danger" onclick="removeIloRow()">
			<i class="bi bi-dash-circle"></i> Remove Row
		</button>
		<button type="button" class="btn btn-sm btn-success" onclick="addSoColumn()">
			<i class="bi bi-plus-circle"></i> Add SO Column
		</button>
		<button type="button" class="btn btn-sm btn-warning" onclick="removeSoColumn()">
			<i class="bi bi-dash-circle"></i> Remove SO Column
		</button>
		<button type="button" class="btn btn-sm btn-info" onclick="saveIloSoCpaMapping()">
			<i class="bi bi-save"></i> Save Mapping
		</button>
	</div>
</div>

{{-- JavaScript moved to resources/js/faculty/syllabus-ilo-so-cpa-mapping.js --}}
