<style>
	.ilo-so-cpa-mapping .ilo-header-controls {
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
	.ilo-so-cpa-mapping .ilo-remove-btn {
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
	.ilo-so-cpa-mapping .ilo-add-btn {
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
	.ilo-so-cpa-mapping .ilo-remove-btn:hover,
	.ilo-so-cpa-mapping .ilo-remove-btn:focus,
	.ilo-so-cpa-mapping .ilo-add-btn:hover,
	.ilo-so-cpa-mapping .ilo-add-btn:focus {
		background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46)) !important;
		backdrop-filter: blur(7px);
		-webkit-backdrop-filter: blur(7px);
		box-shadow: 0 4px 10px rgba(204, 55, 55, 0.12);
		color: #CB3737;
	}
	.ilo-so-cpa-mapping .ilo-remove-btn:hover i,
	.ilo-so-cpa-mapping .ilo-remove-btn:hover svg,
	.ilo-so-cpa-mapping .ilo-remove-btn:focus i,
	.ilo-so-cpa-mapping .ilo-remove-btn:focus svg,
	.ilo-so-cpa-mapping .ilo-add-btn:hover i,
	.ilo-so-cpa-mapping .ilo-add-btn:hover svg,
	.ilo-so-cpa-mapping .ilo-add-btn:focus i,
	.ilo-so-cpa-mapping .ilo-add-btn:focus svg {
		color: #CB3737;
	}
	.ilo-so-cpa-mapping .ilo-remove-btn:active,
	.ilo-so-cpa-mapping .ilo-add-btn:active {
		transform: scale(0.97);
		filter: brightness(0.98);
	}
	.ilo-so-cpa-mapping .ilo-remove-btn i,
	.ilo-so-cpa-mapping .ilo-remove-btn svg,
	.ilo-so-cpa-mapping .ilo-add-btn i,
	.ilo-so-cpa-mapping .ilo-add-btn svg {
		width: 14px;
		height: 14px;
		margin: 0;
	}
	.ilo-so-cpa-mapping .so-side-btn {
		padding: 0 8px;
		line-height: 1;
		font-weight: 600;
		border: none !important;
		background: #fff !important;
		color: #212529;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		cursor: pointer;
		align-self: stretch;
		height: auto;
	}
	.ilo-so-cpa-mapping .so-side-btn i,
	.ilo-so-cpa-mapping .so-side-btn svg {
		width: 14px;
		height: 14px;
	}
	.ilo-so-cpa-mapping .so-side-btn:hover,
	.ilo-so-cpa-mapping .so-side-btn:focus {
		background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46)) !important;
		backdrop-filter: blur(7px);
		-webkit-backdrop-filter: blur(7px);
		box-shadow: 0 4px 10px rgba(204, 55, 55, 0.12);
		color: #CB3737;
	}
	.ilo-so-cpa-mapping .so-side-btn:active {
		transform: scale(0.97);
		filter: brightness(0.98);
	}
	.ilo-so-cpa-mapping .so-side-btn:disabled {
		opacity: 0.5;
		cursor: not-allowed;
	}
	.ilo-so-cpa-mapping .so-board {
		display: flex;
		align-items: stretch;
		gap: 0;
	}
	.ilo-so-cpa-mapping .so-header-controls {
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
	.ilo-so-cpa-mapping .a-header-controls {
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
	.ilo-so-cpa-mapping .so-remove-btn,
	.ilo-so-cpa-mapping .so-add-btn {
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
	.ilo-so-cpa-mapping .so-remove-btn i,
	.ilo-so-cpa-mapping .so-remove-btn svg,
	.ilo-so-cpa-mapping .so-add-btn i,
	.ilo-so-cpa-mapping .so-add-btn svg {
		width: 14px;
		height: 14px;
		margin: 0;
	}
	.ilo-so-cpa-mapping .so-remove-btn:hover,
	.ilo-so-cpa-mapping .so-remove-btn:focus,
	.ilo-so-cpa-mapping .so-add-btn:hover,
	.ilo-so-cpa-mapping .so-add-btn:focus {
		background: linear-gradient(135deg, rgba(255, 240, 235, 0.88), rgba(255, 255, 255, 0.46)) !important;
		backdrop-filter: blur(7px);
		-webkit-backdrop-filter: blur(7px);
		box-shadow: 0 4px 10px rgba(204, 55, 55, 0.12);
		color: #CB3737;
	}
	.ilo-so-cpa-mapping .so-remove-btn:hover i,
	.ilo-so-cpa-mapping .so-remove-btn:hover svg,
	.ilo-so-cpa-mapping .so-remove-btn:focus i,
	.ilo-so-cpa-mapping .so-remove-btn:focus svg,
	.ilo-so-cpa-mapping .so-add-btn:hover i,
	.ilo-so-cpa-mapping .so-add-btn:hover svg,
	.ilo-so-cpa-mapping .so-add-btn:focus i,
	.ilo-so-cpa-mapping .so-add-btn:focus svg {
		color: #CB3737;
	}
	.ilo-so-cpa-mapping .so-remove-btn:active,
	.ilo-so-cpa-mapping .so-add-btn:active {
		transform: scale(0.97);
		filter: brightness(0.98);
	}
	@media print {
		.ilo-so-cpa-mapping .ilo-remove-btn,
		.ilo-so-cpa-mapping .ilo-add-btn,
		.ilo-so-cpa-mapping .so-side-btn,
		.ilo-so-cpa-mapping .so-remove-btn,
		.ilo-so-cpa-mapping .so-add-btn {
			display: none !important;
		}
	}
</style>

<div class="ilo-so-cpa-mapping mb-4" 
     data-so-columns="{{ json_encode($soColumns ?? []) }}"
     data-mappings="{{ json_encode($syllabus->iloSoCpa ?? []) }}">
	<table class="table table-bordered" style="width:100%; border:1px solid #343a40; border-collapse:collapse; overflow:visible;">
		<thead>
			<tr>
				<th class="partial-label" style="border:1px solid #343a40; width:50px; padding:0; color:#000; font-weight:700; text-align:center; vertical-align:middle; font-family:Georgia, serif !important; font-size:13px; height:auto; position:relative; overflow:hidden;">
					<div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%) rotate(-90deg); writing-mode:horizontal-tb; white-space:nowrap; max-width:500px; overflow:hidden; text-overflow:ellipsis;">ILO-SO and ILO-CPA<br>Mapping</div>
				</th>
				<th style="border:none; padding:0;">
					<table class="mapping" style="width:100%; border:none; border-collapse:collapse; table-layout:auto;">
						<colgroup>
							<col style="width:90px">
							<col style="width:80px">
							<col style="width:80px">
							<col style="width:80px">
							<col style="width:80px">
						</colgroup>
					<tr>
						<th rowspan="2" style="border:none; border-bottom:1px solid #343a40; border-right:1px solid #343a40; width:90px; height:30px; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center; vertical-align:middle; position:relative;">
							<div class="ilo-header-controls">
								<button type="button" class="btn btn-sm ilo-remove-btn" onclick="removeIloRow()" title="Remove ILO row" aria-label="Remove ILO row">
										<i data-feather="minus"></i>
									</button>
									<button type="button" class="btn btn-sm ilo-add-btn" onclick="addIloRow()" title="Add ILO row" aria-label="Add ILO row">
										<i data-feather="plus"></i>
									</button>
								</div>
								ILOs
							</th>
							<th colspan="4" style="border:none; border-bottom:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;">STUDENT OUTCOMES (SO): Mapping of Assessment Tasks (AT)</th>
						</tr>
						<tr>
							<th style="border:none; border-bottom:1px solid #343a40; border-right:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:400; font-style:italic; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#999; text-align:center; position:relative;">
								<div class="so-header-controls">
									<button type="button" class="btn btn-sm so-remove-btn" onclick="removeSoColumn()" title="Remove SO column" aria-label="Remove SO column">
										<i data-feather="minus"></i>
									</button>
								</div>
								No SO
							</th>
							<th style="border:none; border-bottom:1px solid #343a40; border-right:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center;">C</th>
							<th style="border:none; border-bottom:1px solid #343a40; border-right:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center;">P</th>
							<th style="border:none; border-bottom:1px solid #343a40; height:30px; padding:0.2rem 0.5rem; font-weight:700; font-family:Georgia, serif; font-size:13px; line-height:1.4; color:#111; text-align:center; position:relative;">
								<div class="a-header-controls">
									<button type="button" class="btn btn-sm so-add-btn" onclick="addSoColumn()" title="Add SO column" aria-label="Add SO column">
										<i data-feather="plus"></i>
									</button>
								</div>
								A
							</th>
						</tr>
						<tr>
							<td style="border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; font-family:Georgia, serif; font-size:13px; text-align:center; vertical-align:middle; color:#999; font-style:italic;">No ILO</td>
							<td style="border:none; border-top:1px solid #343a40; border-right:1px solid #343a40; padding:0.2rem 0.5rem; text-align:center; vertical-align:middle; background-color:#f8f9fa;">
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
</div>

<script>
	function registerValidationField() {
		if (typeof window.addRequiredField === 'function') {
			window.addRequiredField('ilo_so_cpa', 'ilo-so-cpa-data', 'ILO-SO-CPA Mapping');
			setupTableMutationObserver();
		} else {
			setTimeout(registerValidationField, 500);
		}
	}

	function setupTableMutationObserver() {
		const mapping = document.querySelector('.ilo-so-cpa-mapping');
		const mappingTable = mapping ? mapping.querySelector('.mapping') : null;
		
		if (!mappingTable) return;
		
		const observer = new MutationObserver(() => {
			if (typeof window.updateProgressBar === 'function') {
				window.updateProgressBar();
			}
		});
		
		observer.observe(mappingTable, { childList: true, subtree: true, characterData: true });
	}

	document.addEventListener('DOMContentLoaded', registerValidationField);
</script>

@push('scripts')
	@vite('resources/js/app.js')
@endpush
