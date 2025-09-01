{{--
	Foundation partial: Criteria for Assessment
	- Table skeleton and non-editable rendering of existing criteria values (lecture / laboratory)
	- No input fields or contenteditable elements included here (reserved for later)
--}}

<table class="table table-bordered mb-4 cis-table">
	<colgroup>
		<col style="width:16%">
		<col style="width:84%">
	</colgroup>
	<tbody>
		<!-- criteria styles moved to resources/css/faculty/syllabus.css -->

		<tr>
			<th class="align-top text-start cis-label">Criteria for Assessment
				<span id="unsaved-criteria" class="unsaved-pill d-none">Unsaved</span>
			</th>
			<td>
				<div class="criteria-box">
					{{-- criteria content --}}
					<div class="row">
						<div class="col-6">
								<input type="text" name="criteria_lecture_title" class="criteria-heading-input mb-2" placeholder="Lecture (40%)">
								<div class="criteria-list" role="list" aria-label="Lecture criteria" data-target="criteria_lecture">
								@php
									$lectureLines = preg_split('/\r?\n/', trim((string) ($local?->criteria_lecture ?? '')));
								@endphp
								@if(!empty($lectureLines) && count(array_filter($lectureLines)) > 0)
									@foreach($lectureLines as $line)
										@php
											$line = trim($line);
											if ($line === '') continue;
											$percent = '';
											if (preg_match('/\((\s*\d+%\s*)\)$/', $line, $m)) {
												$percent = trim($m[1]);
												$desc = trim(preg_replace('/\(\s*\d+%\s*\)$/', '', $line));
											} elseif (preg_match('/\b(\d+%)$/', $line, $m2)) {
												$percent = $m2[1];
												$desc = trim(preg_replace('/\b\d+%$/', '', $line));
											} else {
												$desc = $line;
											}
										@endphp
										<div class="criteria-item" role="listitem">
											<div class="criteria-desc">{{ $desc }}</div>
											<div class="criteria-percent">{{ $percent }}</div>
										</div>
									@endforeach
								@endif
							</div>
						</div>

						<div class="col-6">
							<input type="text" name="criteria_laboratory_title" class="criteria-heading-input mb-2" placeholder="Laboratory (60%)">
								<div class="criteria-list" role="list" aria-label="Laboratory criteria" data-target="criteria_laboratory">
								@php
									$labLines = preg_split('/\r?\n/', trim((string) ($local?->criteria_laboratory ?? '')));
								@endphp
								@if(!empty($labLines) && count(array_filter($labLines)) > 0)
									@foreach($labLines as $line)
										@php
											$line = trim($line);
											if ($line === '') continue;
											$percent = '';
											if (preg_match('/\((\s*\d+%\s*)\)$/', $line, $m)) {
												$percent = trim($m[1]);
												$desc = trim(preg_replace('/\(\s*\d+%\s*\)$/', '', $line));
											} elseif (preg_match('/\b(\d+%)$/', $line, $m2)) {
												$percent = $m2[1];
												$desc = trim(preg_replace('/\b\d+%$/', '', $line));
											} else {
												$desc = $line;
											}
										@endphp
										<div class="criteria-item" role="listitem">
											<div class="criteria-desc">{{ $desc }}</div>
											<div class="criteria-percent">{{ $percent }}</div>
										</div>
									@endforeach
								@endif
							</div>
						</div>
					</div>
				</div>
					<div class="form-text text-muted small mt-2" aria-hidden="false">
						<i class="bi bi-info-circle-fill text-muted me-1" aria-hidden="true"></i>
						<strong>Note:</strong> Enter = add · Backspace = remove · Tab = next
					</div>
					<!-- Live region for screen reader announcements about add/remove -->
					<div id="criteria-aria-live" class="sr-only" aria-live="polite" aria-atomic="true"></div>
			</td>
		</tr>
		<tr class="d-none">
			<td colspan="2">
				{{-- Hidden canonical textareas used by the client-side serializer. JS will write
				     the visible criteria rows into these fields so they are submitted with the form. --}}
				<textarea name="criteria_lecture" class="d-none" data-original="{{ old('criteria_lecture', $local?->criteria_lecture ?? '') }}">{{ old('criteria_lecture', $local?->criteria_lecture ?? '') }}</textarea>
				<textarea name="criteria_laboratory" class="d-none" data-original="{{ old('criteria_laboratory', $local?->criteria_laboratory ?? '') }}">{{ old('criteria_laboratory', $local?->criteria_laboratory ?? '') }}</textarea>
			</td>
		</tr>
	</tbody>
</table>
 
