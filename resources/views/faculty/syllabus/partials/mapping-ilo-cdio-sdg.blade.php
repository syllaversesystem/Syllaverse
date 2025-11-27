{{--
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/mapping-ilo-cdio-sdg.blade.php
* Purpose: ILO → CDIO & SDG mapping box (static partial - no dynamic JS)
* Notes: Renders CDIO and SDG columns from controller-provided `$cdios` and `$sdgs`.
-------------------------------------------------------------------------------
--}}

<style>
	.ilo-cdio-sdg-mapping .ilo-header-controls {
		position: absolute;
		top: 0;
		bottom: 0;
		left: 0;
		right: 0;
		display: flex;
		flex-direction: column;
		pointer-events: none;
		z-index: 10;
	}
	.ilo-cdio-sdg-mapping .ilo-remove-btn {
		padding: 4px 10px;
		line-height: 1;
		font-weight: 600;
		border: none !important;
		background: #fff !important;
		color: #212529;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		cursor: pointer;
		pointer-events: auto;
		height: auto;
	}
	.ilo-cdio-sdg-mapping .ilo-add-btn {
		padding: 4px 10px;
		line-height: 1;
		font-weight: 600;
		border: none !important;
		background: #fff !important;
		color: #212529;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		cursor: pointer;
		pointer-events: auto;
		margin-top: auto;
		height: auto;
	}
	.ilo-cdio-sdg-mapping .ilo-remove-btn:hover,
	.ilo-cdio-sdg-mapping .ilo-remove-btn:focus,
	.ilo-cdio-sdg-mapping .ilo-add-btn:hover,
	.ilo-cdio-sdg-mapping .ilo-add-btn:focus {
		background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46)) !important;
		backdrop-filter: blur(7px);
		-webkit-backdrop-filter: blur(7px);
		box-shadow: 0 4px 10px rgba(204, 55, 55, 0.12);
		color: #CB3737;
	}
	.ilo-cdio-sdg-mapping .ilo-remove-btn:hover i,
	.ilo-cdio-sdg-mapping .ilo-remove-btn:hover svg,
	.ilo-cdio-sdg-mapping .ilo-remove-btn:focus i,
	.ilo-cdio-sdg-mapping .ilo-remove-btn:focus svg,
	.ilo-cdio-sdg-mapping .ilo-add-btn:hover i,
	.ilo-cdio-sdg-mapping .ilo-add-btn:hover svg,
	.ilo-cdio-sdg-mapping .ilo-add-btn:focus i,
	.ilo-cdio-sdg-mapping .ilo-add-btn:focus svg {
		color: #CB3737;
	}
	.ilo-cdio-sdg-mapping .ilo-remove-btn:active,
	.ilo-cdio-sdg-mapping .ilo-add-btn:active {
		transform: scale(0.97);
		filter: brightness(0.98);
	}
	.ilo-cdio-sdg-mapping .ilo-remove-btn i,
	.ilo-cdio-sdg-mapping .ilo-remove-btn svg,
	.ilo-cdio-sdg-mapping .ilo-add-btn i,
	.ilo-cdio-sdg-mapping .ilo-add-btn svg {
		width: 14px;
		height: 14px;
		margin: 0;
	}
	.ilo-cdio-sdg-mapping .cdio-header-controls {
		position: absolute;
		top: 0;
		bottom: 0;
		left: 0;
		right: 0;
		display: flex;
		align-items: center;
		justify-content: flex-start;
		pointer-events: none;
		z-index: 10;
	}
	.ilo-cdio-sdg-mapping .sdg-header-controls {
		position: absolute;
		top: 0;
		bottom: 0;
		left: 0;
		right: 0;
		display: flex;
		align-items: center;
		justify-content: flex-end;
		pointer-events: none;
		z-index: 10;
	}
	.ilo-cdio-sdg-mapping .cdio-remove-btn,
	.ilo-cdio-sdg-mapping .cdio-add-btn,
	.ilo-cdio-sdg-mapping .sdg-remove-btn,
	.ilo-cdio-sdg-mapping .sdg-add-btn {
		padding: 4px 10px;
		line-height: 1;
		font-weight: 600;
		border: none !important;
		background: #fff !important;
		color: #212529;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		cursor: pointer;
		pointer-events: auto;
		height: auto;
	}
	.ilo-cdio-sdg-mapping .cdio-remove-btn i,
	.ilo-cdio-sdg-mapping .cdio-remove-btn svg,
	.ilo-cdio-sdg-mapping .cdio-add-btn i,
	.ilo-cdio-sdg-mapping .cdio-add-btn svg,
	.ilo-cdio-sdg-mapping .sdg-remove-btn i,
	.ilo-cdio-sdg-mapping .sdg-remove-btn svg,
	.ilo-cdio-sdg-mapping .sdg-add-btn i,
	.ilo-cdio-sdg-mapping .sdg-add-btn svg {
		width: 14px;
		height: 14px;
		margin: 0;
	}
	.ilo-cdio-sdg-mapping .cdio-remove-btn:hover,
	.ilo-cdio-sdg-mapping .cdio-remove-btn:focus,
	.ilo-cdio-sdg-mapping .cdio-add-btn:hover,
	.ilo-cdio-sdg-mapping .cdio-add-btn:focus,
	.ilo-cdio-sdg-mapping .sdg-remove-btn:hover,
	.ilo-cdio-sdg-mapping .sdg-remove-btn:focus,
	.ilo-cdio-sdg-mapping .sdg-add-btn:hover,
	.ilo-cdio-sdg-mapping .sdg-add-btn:focus {
		background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46)) !important;
		backdrop-filter: blur(7px);
		-webkit-backdrop-filter: blur(7px);
		box-shadow: 0 4px 10px rgba(204, 55, 55, 0.12);
		color: #CB3737;
	}
	.ilo-cdio-sdg-mapping .cdio-remove-btn:hover i,
	.ilo-cdio-sdg-mapping .cdio-remove-btn:hover svg,
	.ilo-cdio-sdg-mapping .cdio-remove-btn:focus i,
	.ilo-cdio-sdg-mapping .cdio-remove-btn:focus svg,
	.ilo-cdio-sdg-mapping .cdio-add-btn:hover i,
	.ilo-cdio-sdg-mapping .cdio-add-btn:hover svg,
	.ilo-cdio-sdg-mapping .cdio-add-btn:focus i,
	.ilo-cdio-sdg-mapping .cdio-add-btn:focus svg,
	.ilo-cdio-sdg-mapping .sdg-remove-btn:hover i,
	.ilo-cdio-sdg-mapping .sdg-remove-btn:hover svg,
	.ilo-cdio-sdg-mapping .sdg-remove-btn:focus i,
	.ilo-cdio-sdg-mapping .sdg-remove-btn:focus svg,
	.ilo-cdio-sdg-mapping .sdg-add-btn:hover i,
	.ilo-cdio-sdg-mapping .sdg-add-btn:hover svg,
	.ilo-cdio-sdg-mapping .sdg-add-btn:focus i,
	.ilo-cdio-sdg-mapping .sdg-add-btn:focus svg {
		color: #CB3737;
	}
	.ilo-cdio-sdg-mapping .cdio-remove-btn:active,
	.ilo-cdio-sdg-mapping .cdio-add-btn:active,
	.ilo-cdio-sdg-mapping .sdg-remove-btn:active,
	.ilo-cdio-sdg-mapping .sdg-add-btn:active {
		transform: scale(0.97);
		filter: brightness(0.98);
	}
	@media print {
		.ilo-cdio-sdg-mapping .ilo-remove-btn,
		.ilo-cdio-sdg-mapping .ilo-add-btn,
		.ilo-cdio-sdg-mapping .cdio-remove-btn,
		.ilo-cdio-sdg-mapping .cdio-add-btn,
		.ilo-cdio-sdg-mapping .sdg-remove-btn,
		.ilo-cdio-sdg-mapping .sdg-add-btn {
			display: none !important;
		}
	}
</style>

@php
	// Prepare data for JavaScript load function
	$iloCdioSdgMappings = $syllabus->iloCdioSdg ?? collect();
@endphp

<div class="ilo-cdio-sdg-mapping mb-4" 
	data-mappings="{{ $iloCdioSdgMappings->isNotEmpty() ? json_encode($iloCdioSdgMappings->map(function($m) {
		return [
			'ilo_text' => $m->ilo_text ?? '',
			'cdios' => $m->cdios ?? [],
			'sdgs' => $m->sdgs ?? [],
			'position' => $m->position ?? 0
		];
	})->values()) : '[]' }}">
	<table class="table table-bordered" style="width:100%; border:1px solid #343a40; border-collapse:collapse; overflow:visible;">
		<thead>
			<tr>
				<th class="partial-label" style="border:1px solid #343a40; width:50px; padding:0; color:#000; font-weight:700; text-align:center; vertical-align:middle; font-family:Georgia, serif !important; font-size:13px; height:auto; position:relative; overflow:hidden;">
					<div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%) rotate(-90deg); writing-mode:horizontal-tb; white-space:nowrap; max-width:500px; overflow:hidden; text-overflow:ellipsis;">ILO-CDIO and ILO-SDG<br>Mapping</div>
				</th>
				<th style="border:none; padding:0;">
					<table class="mapping" style="width:100%; border:none; border-collapse:collapse; table-layout:fixed;">
						<colgroup>
							<col style="width:60px">
							<col style="width:50%">
							<col style="width:50%">
						</colgroup>
						<tr>
							<th rowspan="2" style="border:none; border-bottom:1px solid #343a40; border-right:1px solid #343a40; width:90px; height:30px; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center; vertical-align:middle; position:relative;">
								<div class="ilo-header-controls">
									<button type="button" class="btn btn-sm ilo-remove-btn" onclick="removeIloRowCdioSdg()" title="Remove ILO row" aria-label="Remove ILO row">
										<i data-feather="minus"></i>
									</button>
									<button type="button" class="btn btn-sm ilo-add-btn" onclick="addIloRowCdioSdg()" title="Add ILO row" aria-label="Add ILO row">
										<i data-feather="plus"></i>
									</button>
								</div>
								ILOs
							</th>
							<th colspan="1" style="border:none; border-bottom:1px solid #343a40; border-right:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;">CDIO SKILLS</th>
							<th colspan="1" style="border:none; border-bottom:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;">SDG SKILLS</th>
						</tr>
						<tr>
							<th class="cdio-label-cell" style="border:none; border-bottom:1px solid #343a40; border-right:1px solid #343a40; min-height:30px; height:30px; padding:0.2rem 0.5rem; font-weight:400; font-style:italic; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#999; text-align:center; position:relative;">
								<div class="cdio-header-controls">
									<button type="button" class="btn btn-sm cdio-remove-btn" onclick="removeCdioColumn()" title="Remove CDIO column" aria-label="Remove CDIO column">
										<i data-feather="minus"></i>
									</button>
								</div>
								<div class="sdg-header-controls">
									<button type="button" class="btn btn-sm cdio-add-btn" onclick="addCdioColumn()" title="Add CDIO column" aria-label="Add CDIO column">
										<i data-feather="plus"></i>
									</button>
								</div>
							No CDIO
						</th>
						<th class="sdg-label-cell" style="border:none; border-bottom:1px solid #343a40; border-left:1px solid #343a40; min-height:30px; height:30px; padding:0.2rem 0.5rem; font-weight:400; font-style:italic; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#999; text-align:center; position:relative;">
							<div class="cdio-header-controls">
									<button type="button" class="btn btn-sm sdg-remove-btn" onclick="removeSdgColumn()" title="Remove SDG column" aria-label="Remove SDG column">
										<i data-feather="minus"></i>
									</button>
								</div>
								<div class="sdg-header-controls">
									<button type="button" class="btn btn-sm sdg-add-btn" onclick="addSdgColumn()" title="Add SDG column" aria-label="Add SDG column">
										<i data-feather="plus"></i>
									</button>
								</div>
								No SDG
							</th>
						</tr>
						<tr>
							<td style="border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.1rem 0.25rem; font-family:Georgia, serif; font-size:13px; color:#999; text-align:center; vertical-align:middle; font-style:italic;">No ILO</td>
						<td style="border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle; background-color:#f8f9fa;">
							<textarea class="form-control form-control-sm" placeholder="-" rows="1" style="width:100%; min-height:22px; border:none; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; resize:none; overflow:hidden; background-color:#f8f9fa; cursor:not-allowed;" disabled></textarea>
						</td>
						<td style="border:none; border-top:1px solid #343a40; border-left:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle; background-color:#f8f9fa;">
								<textarea class="form-control form-control-sm" placeholder="-" rows="1" style="width:100%; min-height:22px; border:none; padding:0.2rem 0.5rem; font-family:Georgia,serif; font-size:13px; text-align:center; box-sizing:border-box; resize:none; overflow:hidden; background-color:#f8f9fa; cursor:not-allowed;" disabled></textarea>
							</td>
						</tr>
					</table>
				</th>
			</tr>
		</thead>
	</table>
</div>

@push('scripts')
	@vite('resources/js/app.js')
@endpush
