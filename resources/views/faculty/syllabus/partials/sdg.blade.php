{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/sdg.blade.php
* Description: Sustainable Development Goals (SDG) — CDIO-style unified layout
-------------------------------------------------------------------------------
--}}

@php $rp = $routePrefix ?? 'faculty.syllabi'; @endphp
<form id="sdgForm" method="POST" action="{{ route($rp . '.sdgs.save', $default['id']) }}">
	@csrf

	@php
		$sdgsCollection = $default['sdgs'] ?? collect();
		// Accept either a collection of Sdg models with pivot or SyllabusSdg entries
		$sdgsSorted = $sdgsCollection->sortBy(function($s){
			if (isset($s->pivot)) return $s->pivot->position ?? 0;
			return $s->sort_order ?? 0;
		})->values();
	@endphp

	<style>
		.sdg-left-title { font-weight: 700; padding: 0.75rem; font-family: Georgia, serif; vertical-align: top; box-sizing: border-box; line-height: 1.2; }
		table.cis-table { table-layout: fixed; }
		table.cis-table th.cis-label { white-space: normal; overflow-wrap: break-word; word-break: break-word; }
		table.cis-table td, table.cis-table th { overflow: hidden; }
		/* Right inner wrap like CDIO */
		#sdg-right-wrap { padding: 0; margin: 0; border-top: 0 !important; border-bottom: 0 !important; }
		#sdg-right-wrap > table { width: 100%; height: 100%; margin: 0; border-spacing: 0; border-collapse: collapse; }
		#sdg-right-wrap td, #sdg-right-wrap th { vertical-align: middle; padding: 0.5rem 0.5rem; }
		/* Header labels in Times New Roman 10pt, black */
		#sdg-right-wrap > table thead th.cis-label { color:#000 !important; font-family:'Times New Roman', Times, serif !important; font-size:10pt !important; }
		#sdg-right-wrap > table thead th.cis-label:first-child { white-space: nowrap; width:1%; }
		/* Inner grid borders — solid black; hide outer edges */
		#sdg-right-wrap > table th, #sdg-right-wrap > table td { border: 1px solid #000; }
		#sdg-right-wrap > table thead th { border-top: 0; border-bottom: 1px solid #000 !important; }
		#sdg-right-wrap > table th:first-child, #sdg-right-wrap > table td:first-child { padding: 6.4px !important; border-left: 0; }
		#sdg-right-wrap > table th:last-child, #sdg-right-wrap > table td:last-child { border-right: 0; }
		#sdg-right-wrap > table tbody tr:last-child td { border-bottom: 0 !important; }
		/* Badges, handle, textareas */
		.cdio-badge { display: inline-block; min-width: 0; width: auto; padding: 0; text-align: center; color:#000; white-space: normal; line-height: 1.1; font-weight: 700; }
		.drag-handle { width: 28px; display: inline-flex; justify-content: center; align-self: center; cursor: grab; }
		.cis-textarea { width: 100%; box-sizing: border-box; resize: none; }
		#sdg-right-wrap textarea.cis-textarea.autosize { overflow: hidden; }
		table.cis-table th.cis-label, table.cis-table th { vertical-align: top; }

		/* Icon-only header buttons styled like CDIO */
		.sdg-header-actions .btn {
			position: relative; padding: 0 !important;
			width: 2.2rem; height: 2.2rem; min-width: 2.2rem; min-height: 2.2rem;
			border-radius: 50% !important;
			display: inline-flex; align-items: center; justify-content: center;
			background: var(--sv-card-bg, #fff);
			border: none; box-shadow: none; color: #000;
			transition: all 0.2s ease-in-out; line-height: 0;
		}
		.sdg-header-actions .btn .bi { font-size: 1rem; width: 1rem; height: 1rem; line-height: 1; color: var(--sv-text, #000); }
		.sdg-header-actions .btn svg { width: 1.05rem; height: 1.05rem; display: block; margin: 0; vertical-align: middle; stroke: currentColor; }
		.sdg-header-actions .btn:hover, .sdg-header-actions .btn:focus { background: linear-gradient(135deg, rgba(255,240,235,.88), rgba(255,255,255,.46)); backdrop-filter: blur(7px); -webkit-backdrop-filter: blur(7px); box-shadow: 0 4px 10px rgba(204,55,55,.12); color: #CB3737; }
		.sdg-header-actions .btn:hover .bi, .sdg-header-actions .btn:focus .bi { color: #CB3737; }
		.sdg-header-actions .btn:active { transform: scale(.97); filter: brightness(.98); }
	</style>

	<table class="table table-bordered mb-4 cis-table">
		<colgroup>
			<col style="width:16%">
			<col style="width:84%">
		</colgroup>
		<tbody>
			<tr>
				<th class="align-top text-start cis-label sdg-left-title">Sustainable Development Goals (SDG)
					<span id="unsaved-sdgs" class="unsaved-pill d-none">Unsaved</span>
				</th>
				<td id="sdg-right-wrap">
					<table class="table mb-0" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.4; border: none; table-layout: fixed;">
						<colgroup>
							<col style="width:70px">
							<col style="width:auto">
						</colgroup>
						<thead>
							<tr class="table-light">
								<th class="text-center cis-label">SDG</th>
								<th class="text-center cis-label">
									<div class="d-flex justify-content-between align-items-start gap-2">
										<span class="flex-grow-1 text-center">SDG Skills Statements</span>
										<span class="sdg-header-actions d-inline-flex gap-1" style="white-space:nowrap;">
											<button type="button" class="btn btn-sm" id="sdg-add-header" title="Add" aria-label="Add" data-bs-toggle="modal" data-bs-target="#addSdgModal" style="background:transparent;">
												<i data-feather="plus"></i>
												<span class="visually-hidden">Add</span>
											</button>
										</span>
									</div>
								</th>
							</tr>
						</thead>
						<tbody id="syllabus-sdg-sortable" data-syllabus-id="{{ $default['id'] }}">
							@if($sdgsSorted->count())
								@foreach($sdgsSorted as $index => $sdg)
									@php
										$isPivot = isset($sdg->pivot);
										$seqCode = $isPivot ? ($sdg->pivot->code ?? ('SDG' . ($index + 1))) : ($sdg->code ?? ('SDG' . ($index + 1)));
										$rowId = $isPivot ? $sdg->pivot->id : ($sdg->id ?? null);
										$visibleTitle = $isPivot ? ($sdg->pivot->title ?? $sdg->title) : ($sdg->title ?? '');
										$visibleDesc = $isPivot ? ($sdg->pivot->description ?? '') : ($sdg->description ?? '');
									@endphp
									<tr data-id="{{ $rowId }}">
										<td class="text-center align-middle">
											<div class="cdio-badge">{{ $seqCode }}</div>
										</td>
										<td>
											<div class="d-flex align-items-center gap-2">
												<span class="drag-handle text-muted" title="Drag to reorder"><i class="bi bi-grip-vertical"></i></span>
												<div class="flex-grow-1 w-100">
													<input type="text" name="title[]" class="form-control form-control-sm" value="{{ $visibleTitle }}" data-original="{{ $visibleTitle }}" placeholder="-" style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;font-weight:700;background:transparent;border:none;" />
													<textarea name="sdgs[]" class="cis-textarea cis-field autosize" data-original="{{ old("sdgs.$index", $visibleDesc) }}" placeholder="Description" rows="1" style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;" required>{{ old("sdgs.$index", $visibleDesc) }}</textarea>
												</div>
												<input type="hidden" name="code[]" value="{{ $seqCode }}">
												<button type="button" class="btn btn-sm btn-outline-danger btn-delete-cdio ms-2" title="Delete SDG"><i class="bi bi-trash"></i></button>
											</div>
										</td>
									</tr>
								@endforeach
							@else
								<tr>
									<td class="text-center align-middle"><div class="cdio-badge">SDG1</div></td>
									<td>
										<div class="d-flex align-items-center gap-2">
											<span class="drag-handle text-muted" title="Drag to reorder"><i class="bi bi-grip-vertical"></i></span>
											<div class="flex-grow-1 w-100">
												<input type="text" name="title[]" class="form-control form-control-sm" placeholder="-" value="" style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;font-weight:700;background:transparent;border:none;" />
												<textarea name="sdgs[]" class="cis-textarea cis-field autosize" placeholder="Description" rows="1" style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;" required></textarea>
											</div>
											<input type="hidden" name="code[]" value="SDG1">
											<button type="button" class="btn btn-sm btn-outline-danger btn-delete-cdio ms-2" title="Delete SDG"><i class="bi bi-trash"></i></button>
										</div>
									</td>
								</tr>
							@endif
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>
</form>

{{-- Add SDG Modal: select from master SDGs via checkboxes --}}
<div class="modal fade sv-faculty-dept-modal" id="addSdgModal" tabindex="-1" aria-labelledby="addSdgModalLabel" aria-hidden="true" data-bs-backdrop="static">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<form method="POST" action="{{ route($rp . '.sdgs.attach', $default['id']) }}" class="modal-content">
			@csrf
			<style>
				/* Brand-aligned UI, mirroring Faculty Departments add modal */
				#addSdgModal {
					--sv-bg:   #FAFAFA;
					--sv-bdr:  #E3E3E3;
					--sv-acct: #EE6F57;
					--sv-danger:#CB3737;
				}
				#addSdgModal .modal-header { padding:.85rem 1rem; border-bottom:1px solid var(--sv-bdr); background:#fff; }
				#addSdgModal .modal-title { font-weight:600; font-size:1rem; display:inline-flex; align-items:center; gap:.5rem; }
				#addSdgModal .modal-title i, #addSdgModal .modal-title svg { width:1.05rem; height:1.05rem; stroke: var(--sv-text-muted, #777); }
				#addSdgModal .modal-content { border-radius:16px; border:1px solid var(--sv-bdr); background:#fff; box-shadow:0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06); overflow:hidden; }
				#addSdgModal .form-control, #addSdgModal .form-select { border-radius:12px; border:1px solid var(--sv-bdr); background:#fff; }
				#addSdgModal .form-control:focus, #addSdgModal .form-select:focus { border-color: var(--sv-acct); box-shadow:0 0 0 3px rgba(238,111,87,.16); outline:none; }
				#addSdgModal .btn-danger{ background:#fff; border:none; color:#000; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
				#addSdgModal .btn-danger:hover, #addSdgModal .btn-danger:focus { background:linear-gradient(135deg, rgba(255,240,235,.88), rgba(255,255,255,.46)); backdrop-filter:blur(7px); -webkit-backdrop-filter:blur(7px); box-shadow:0 4px 10px rgba(204,55,55,.12); color:#CB3737; }
				#addSdgModal .btn-light{ background:#fff; border:none; color:#6c757d; transition:all .2s ease; display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; }
				#addSdgModal .btn-light:hover, #addSdgModal .btn-light:focus { background:linear-gradient(135deg, rgba(220,220,220,.88), rgba(240,240,240,.46)); box-shadow:0 4px 10px rgba(108,117,125,.12); color:#495057; }
				#addSdgModal .sdg-checkbox-list { max-height:320px; overflow:auto; border:1px solid var(--sv-bdr); padding:0.5rem; border-radius:6px; }
				#addSdgModal .form-label.small { color:#6c757d; }
			</style>
			<div class="modal-header">
				<h5 class="modal-title" id="addSdgModalLabel"><i data-feather="plus-circle"></i><span>Add Sustainable Development Goal(s)</span></h5>
			</div>
			<div class="modal-body">
				<label class="form-label small fw-medium text-muted">Select SDGs to attach</label>
				<div class="sdg-checkbox-list">
						@php
							// Determine currently attached master SDG ids if possible
							$attachedMasterIds = collect($sdgsSorted ?? [])->map(function($s){
								if (isset($s->pivot) && isset($s->id)) return $s->id; // master model with pivot
								return $s->sdg_id ?? null; // per-syllabus entry
							})->filter()->unique()->values();
						@endphp
						@foreach (($sdgs ?? []) as $sdg)
							<div class="form-check mb-1">
								<input name="sdg_ids[]" class="form-check-input sdg-checkbox" type="checkbox" value="{{ $sdg->id }}" id="sdg_check_{{ $sdg->id }}" @if($attachedMasterIds->contains($sdg->id)) checked @endif>
								<label class="form-check-label small" for="sdg_check_{{ $sdg->id }}">{{ $sdg->title }}</label>
							</div>
						@endforeach
					</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light" data-bs-dismiss="modal"><i data-feather="x"></i> Cancel</button>
				<button type="submit" class="btn btn-danger"><i data-feather="plus"></i> Add</button>
			</div>
		</form>
	</div>
</div>

<script>
// Handle modal form submit: attach newly checked and detach unchecked items
document.addEventListener('DOMContentLoaded', function () {
	try {
		const modalForm = document.querySelector('#addSdgModal form');
		if (!modalForm) return;
		modalForm.addEventListener('submit', async function (ev) {
			ev.preventDefault();
			const tbody = document.getElementById('syllabus-sdg-sortable');
			if (!tbody) return;
			const syllabusId = tbody.dataset ? tbody.dataset.syllabusId : null;
			// Compute sets
			const checked = Array.from(modalForm.querySelectorAll('input[name="sdg_ids[]"]:checked')).map(i => String(i.value));
			const attached = Array.from(tbody.querySelectorAll('tr[data-sdg-id]')).map(r => String(r.getAttribute('data-sdg-id'))).filter(Boolean);
			const toAttach = checked.filter(id => !attached.includes(id));
			const toDetach = attached.filter(id => !checked.includes(id));

			// Attach new
			if (toAttach.length) {
				const res = await fetch(modalForm.action, {
					method: 'POST', credentials: 'same-origin',
					headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
					body: JSON.stringify({ sdg_ids: toAttach })
				});
				if (!res.ok) throw new Error(await res.text().catch(() => res.statusText));
				const payload = await res.json().catch(() => ({}));
				const created = Array.isArray(payload.created) ? payload.created : (payload.pivot_id ? [payload] : []);
				for (const item of created) {
					const row = document.createElement('tr');
					row.setAttribute('data-id', item.pivot_id || ('new-' + Date.now()));
					if (item.sdg_id) row.setAttribute('data-sdg-id', item.sdg_id);
					row.innerHTML = `
						<td class="text-center align-middle"><div class="cdio-badge"></div></td>
						<td>
							<div class="d-flex align-items-center gap-2">
								<span class="drag-handle text-muted" title="Drag to reorder"><i class="bi bi-grip-vertical"></i></span>
								<div class="flex-grow-1 w-100">
									<input type="text" name="title[]" class="form-control form-control-sm" value="${(item.title||'').replace(/"/g,'&quot;')}" data-original="${(item.title||'').replace(/"/g,'&quot;')}" placeholder="-" style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;font-weight:700;background:transparent;border:none;" />
									<textarea name="sdgs[]" class="cis-textarea cis-field autosize" data-original="${(item.description||'').replace(/"/g,'&quot;')}" placeholder="Description" rows="1" style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;">${item.description||''}</textarea>
								</div>
								<input type="hidden" name="code[]" value="">
								<button type="button" class="btn btn-sm btn-outline-danger btn-delete-cdio ms-2" title="Delete SDG"><i class="bi bi-trash"></i></button>
							</div>
						</td>`;
					tbody.appendChild(row);
				}
			}

			// Detach removed
			for (const sid of toDetach) {
				if (!syllabusId) continue;
				const res2 = await fetch(`/faculty/syllabi/${syllabusId}/sdgs/${sid}`, { method: 'DELETE', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } });
				if (res2.ok) {
					const r = document.querySelector(`#syllabus-sdg-sortable tr[data-sdg-id="${sid}"]`);
					if (r) { try { r.remove(); } catch (e) { r.parentNode && r.parentNode.removeChild(r); } }
				}
			}

			try { if (window.updateVisibleCodes) window.updateVisibleCodes(); } catch (e) {}
			try { if (window.initAutosize) window.initAutosize(); else { document.querySelectorAll('textarea.autosize').forEach(t => { t.style.height = 'auto'; t.style.height = (t.scrollHeight||24) + 'px'; }); } } catch (e) {}
			try { const modalEl = document.getElementById('addSdgModal'); if (window.bootstrap && modalEl) { (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).hide(); } } catch (e) {}
		});
	} catch (e) { console.error('Add SDG modal wiring failed', e); }
});
</script>

@push('scripts')
	@vite('resources/js/faculty/syllabus-sdg-sortable.js')
@endpush
