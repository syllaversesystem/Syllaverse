{{--
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/ilo-iga-mapping.blade.php
* Purpose: ILO → IGA mapping table with add/remove controls matching ILO-SO-CPA style
* Notes: Matches structure and behavior of ilo-so-cpa-mapping partial
-------------------------------------------------------------------------------
--}}

<style>
	.ilo-iga-mapping .ilo-header-controls {
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
	.ilo-iga-mapping .ilo-remove-btn {
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
	.ilo-iga-mapping .ilo-add-btn {
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
	.ilo-iga-mapping .ilo-remove-btn:hover,
	.ilo-iga-mapping .ilo-remove-btn:focus,
	.ilo-iga-mapping .ilo-add-btn:hover,
	.ilo-iga-mapping .ilo-add-btn:focus {
		background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46)) !important;
		backdrop-filter: blur(7px);
		-webkit-backdrop-filter: blur(7px);
		box-shadow: 0 4px 10px rgba(204, 55, 55, 0.12);
		color: #CB3737;
	}
	.ilo-iga-mapping .ilo-remove-btn:hover i,
	.ilo-iga-mapping .ilo-remove-btn:hover svg,
	.ilo-iga-mapping .ilo-remove-btn:focus i,
	.ilo-iga-mapping .ilo-remove-btn:focus svg,
	.ilo-iga-mapping .ilo-add-btn:hover i,
	.ilo-iga-mapping .ilo-add-btn:hover svg,
	.ilo-iga-mapping .ilo-add-btn:focus i,
	.ilo-iga-mapping .ilo-add-btn:focus svg {
		color: #CB3737;
	}
	.ilo-iga-mapping .ilo-remove-btn:active,
	.ilo-iga-mapping .ilo-add-btn:active {
		transform: scale(0.97);
		filter: brightness(0.98);
	}
	.ilo-iga-mapping .ilo-remove-btn i,
	.ilo-iga-mapping .ilo-remove-btn svg,
	.ilo-iga-mapping .ilo-add-btn i,
	.ilo-iga-mapping .ilo-add-btn svg {
		width: 14px;
		height: 14px;
		margin: 0;
	}
	.ilo-iga-mapping .iga-remove-controls {
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
	.ilo-iga-mapping .iga-add-controls {
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
	.ilo-iga-mapping .iga-remove-btn,
	.ilo-iga-mapping .iga-add-btn {
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
	.ilo-iga-mapping .iga-remove-btn i,
	.ilo-iga-mapping .iga-remove-btn svg,
	.ilo-iga-mapping .iga-add-btn i,
	.ilo-iga-mapping .iga-add-btn svg {
		width: 14px;
		height: 14px;
		margin: 0;
	}
	.ilo-iga-mapping .iga-remove-btn:hover,
	.ilo-iga-mapping .iga-remove-btn:focus,
	.ilo-iga-mapping .iga-add-btn:hover,
	.ilo-iga-mapping .iga-add-btn:focus {
		background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46)) !important;
		backdrop-filter: blur(7px);
		-webkit-backdrop-filter: blur(7px);
		box-shadow: 0 4px 10px rgba(204, 55, 55, 0.12);
		color: #CB3737;
	}
	.ilo-iga-mapping .iga-remove-btn:hover i,
	.ilo-iga-mapping .iga-remove-btn:hover svg,
	.ilo-iga-mapping .iga-remove-btn:focus i,
	.ilo-iga-mapping .iga-remove-btn:focus svg,
	.ilo-iga-mapping .iga-add-btn:hover i,
	.ilo-iga-mapping .iga-add-btn:hover svg,
	.ilo-iga-mapping .iga-add-btn:focus i,
	.ilo-iga-mapping .iga-add-btn:focus svg {
		color: #CB3737;
	}
	.ilo-iga-mapping .iga-remove-btn:active,
	.ilo-iga-mapping .iga-add-btn:active {
		transform: scale(0.97);
		filter: brightness(0.98);
	}
	@media print {
		.ilo-iga-mapping .ilo-remove-btn,
		.ilo-iga-mapping .ilo-add-btn,
		.ilo-iga-mapping .iga-remove-btn,
		.ilo-iga-mapping .iga-add-btn {
			display: none !important;
		}
	}
</style>

<div class="ilo-iga-mapping mb-4"
	data-iga-headers="{{ json_encode([]) }}"
	data-mappings="{{ json_encode($iloIgaMappings ?? []) }}">
	<table class="table table-bordered" style="width:100%; border:1px solid #343a40; border-collapse:collapse; overflow:visible;">
		<thead>
			<tr>
				<th class="partial-label" style="border:1px solid #343a40; width:50px; padding:0; color:#000; font-weight:700; text-align:center; vertical-align:middle; font-family:Georgia, serif !important; font-size:13px; height:auto; position:relative; overflow:hidden;">
					<div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%) rotate(-90deg); writing-mode:horizontal-tb; white-space:nowrap; max-width:500px; overflow:hidden; text-overflow:ellipsis;">ILO-IGA<br>Mapping</div>
				</th>
				<th style="border:none; padding:0;">
					<table class="mapping" style="width:100%; border:none; border-collapse:collapse; table-layout:fixed;">
						<colgroup>
							<col style="width:60px">
							<col>
						</colgroup>
						<tr>
							<th rowspan="2" style="border:none; border-bottom:1px solid #343a40; border-right:1px solid #343a40; width:90px; height:30px; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center; vertical-align:middle; position:relative;">
								<div class="ilo-header-controls">
									<button type="button" class="btn btn-sm ilo-remove-btn" onclick="removeIloRowIga()" title="Remove ILO row" aria-label="Remove ILO row">
										<i data-feather="minus"></i>
									</button>
									<button type="button" class="btn btn-sm ilo-add-btn" onclick="addIloRowIga()" title="Add ILO row" aria-label="Add ILO row">
										<i data-feather="plus"></i>
									</button>
								</div>
								ILOs
							</th>
							<th colspan="1" style="border:none; border-bottom:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;">INSTITUTIONAL GRADUATE ATTRIBUTES (IGA): Mapping of Assessment Tasks (AT)</th>
						</tr>
						<tr>
						<th id="iga-placeholder-header" style="border:none; border-bottom:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:400; font-style:italic; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#999; text-align:center; position:relative;">
							<div class="iga-remove-controls">
								<button type="button" class="btn btn-sm iga-remove-btn" onclick="removeIgaColumn()" title="Remove IGA column" aria-label="Remove IGA column">
									<i data-feather="minus"></i>
								</button>
							</div>
							<div class="iga-add-controls">
								<button type="button" class="btn btn-sm iga-add-btn" onclick="addIgaColumn()" title="Add IGA column" aria-label="Add IGA column">
									<i data-feather="plus"></i>
								</button>
							</div>
							No IGA
						</th>
						</tr>
						<tr>
							<td style="border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; font-family:Georgia, serif; font-size:13px; text-align:center; vertical-align:middle; color:#999; font-style:italic;">No ILO</td>
							<td style="border:none; border-top:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle; background-color:#f8f9fa;">
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
