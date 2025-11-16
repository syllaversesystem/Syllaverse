{{--
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/mapping-ilo-cdio-sdg.blade.php
* Purpose: ILO → SDG & CDIO mapping box (mirrors structure of ILO→SO→CPA mapping)
* Notes: Renders SDG and CDIO columns from controller-provided `$sdgs` and `$cdios`.
-------------------------------------------------------------------------------
--}}

<div class="ilo-sdg-cdio-mapping mb-4">
    <table class="table table-bordered" style="width:100%; table-layout:fixed; border:1px solid #343a40; border-collapse:collapse; font-size:12px;">
        @php
            $sdgsList = $sdgs ?? collect();
            $cdiosList = $cdios ?? collect();
            $sdgCount = $sdgsList->count();
            $cdioCount = $cdiosList->count();
            // Ensure we render at least one column for CDIO and SDG even when none are defined
            $effectiveCdio = max(1, $cdioCount);
            $effectiveSdg = max(1, $sdgCount);
            // spacer + ILO + CDIOs + SDGs (add back left spacer column)
                $totalCols = 2 + $effectiveCdio + $effectiveSdg;
            // use a fractional percent so columns fill the width exactly
            $colWidth = $totalCols > 0 ? number_format(100 / $totalCols, 4) : '100.0000';
        @endphp
        <colgroup>
            <col style="width:{{ $colWidth }}%"> {{-- left spacer --}}
            <col style="width:{{ $colWidth }}%"> {{-- ILO label --}}
            {{-- CDIO columns first --}}
            @for ($i = 0; $i < $effectiveCdio; $i++)
                <col style="width:{{ $colWidth }}%;">
            @endfor
            {{-- SDG columns next --}}
            @for ($i = 0; $i < $effectiveSdg; $i++)
                <col style="width:{{ $colWidth }}%;">
            @endfor
        </colgroup>
        <tbody>
                <tr>
                    <td rowspan="2" style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"></td>
                    <td rowspan="2" style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
                        <div style="height:100%; display:flex; align-items:center; justify-content:center;">
                            <strong style="font-family:Georgia, serif; font-size:12px;">ILOs</strong>
                        </div>
                    </td>
                    <th colspan="{{ max(1, $cdioCount) }}" style="border:1px solid #343a40; padding:0.25rem 0.5rem; min-height:3.5rem; vertical-align:middle; font-weight:700; font-family:Georgia, serif; font-size:12px; text-align:center;">CDIO SKILLS</th>
                    <th colspan="{{ max(1, $sdgCount) }}" style="border:1px solid #343a40; padding:0.25rem 0.5rem; min-height:3.5rem; vertical-align:middle; font-weight:700; font-family:Georgia, serif; font-size:12px; text-align:center;">SDG Skills</th>
                </tr>
                <tr>
                    @php
                        $cdioLabels = $cdiosList->map(fn($c) => $c->code ?? '');
                        $sdgLabels = $sdgsList->map(fn($s) => $s->code ?? '');
                    @endphp
                    @foreach ($cdioLabels as $idx => $label)
                        {{-- show the CDIO code (fallback to numeric index if label missing) --}}
                        <td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><strong>{{ e($label ?: ($idx + 1)) }}</strong></td>
                    @endforeach
                    @foreach ($sdgLabels as $idx => $label)
                        {{-- show the SDG syllabus code exactly as provided by the controller (fallback to SDG{index}) --}}
                        @php
                            $raw = trim((string)($label ?? ''));
                            if ($raw === '') {
                                $display = 'SDG' . ($idx + 1);
                            } elseif (preg_match('/^\d+$/', $raw)) {
                                $display = 'SDG' . $raw;
                            } else {
                                $display = $raw;
                            }
                        @endphp
                        <td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><strong>{{ e($display) }}</strong></td>
                    @endforeach
                </tr>

                @php $rows = $syllabus?->iloCdioSdg ?? null; @endphp
                @if ($rows && $rows->count())
                    {{-- Render normalized rows: one row per ILO with CDIO and SDG columns --}}
                    @foreach ($rows as $r)
                        @php
                            $cdioVals = is_array($r->cdios) ? $r->cdios : (json_decode((string)$r->cdios, true) ?? []);
                            $sdgVals = is_array($r->sdgs) ? $r->sdgs : (json_decode((string)$r->sdgs, true) ?? []);
                            $effectiveCdio = max(1, $cdioCount);
                            $effectiveSdg = max(1, $sdgCount);
                            $rowIndex = $loop->index;
                        @endphp
                        <tr data-row-index="{{ $rowIndex }}" data-cdios='@json($cdioVals)' data-sdgs='@json($sdgVals)'>
                            <td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"></td>
                            <td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
                                <span class="ilo-badge fw-semibold d-inline-block text-center" tabindex="0">{{ e($r->ilo_text ?? ('ILO' . ($r->position + 1))) }}</span>
                                <input type="hidden" name="ilo_sdg_cdio_ilos_text[]" form="syllabusForm" value="{{ e($r->ilo_text ?? ('ILO' . ($r->position + 1))) }}" />
                            </td>
                            @for ($i = 0; $i < $effectiveCdio; $i++)
                                @php $val = $cdioVals[$i] ?? ''; @endphp
                                <td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
                                    <input id="ilo_sdg_cdio_cdio{{ $i+1 }}_text" name="ilo_sdg_cdio_cdio{{ $i+1 }}_text[]" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="-" value="{{ e($val) }}" />
                                </td>
                            @endfor
                            @for ($i = 0; $i < $effectiveSdg; $i++)
                                @php $val = $sdgVals[$i] ?? ''; @endphp
                                <td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
                                    <input id="ilo_sdg_cdio_sdg{{ $i+1 }}_text" name="ilo_sdg_cdio_sdg{{ $i+1 }}_text[]" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="-" value="{{ e($val) }}" />
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                @else
                    {{-- Legacy single-row fallback (preserve previous UI) --}}
                    <tr>
                        <td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"></td>
                        <td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
                            <span class="ilo-badge fw-semibold d-inline-block text-center" tabindex="0">{{ old('ilo_sdg_cdio_ilos_text', $syllabus?->ilo_sdg_cdio_ilos_text ?? '') }}</span>
                            <input type="hidden" name="ilo_sdg_cdio_ilos_text[]" form="syllabusForm" value="{{ old('ilo_sdg_cdio_ilos_text', $syllabus?->ilo_sdg_cdio_ilos_text ?? '') }}" />
                        </td>
                        @for ($i = 1; $i <= $cdioCount; $i++)
                            @php $prop = 'ilo_sdg_cdio_cdio' . $i . '_text'; @endphp
                            <td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
                                <input id="ilo_sdg_cdio_cdio{{ $i }}_text" name="ilo_sdg_cdio_cdio{{ $i }}_text[]" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="-" value="{{ old($prop, $syllabus?->{$prop} ?? '') }}" />
                            </td>
                        @endfor
                        @for ($i = 1; $i <= $sdgCount; $i++)
                            @php $prop = 'ilo_sdg_cdio_sdg' . $i . '_text'; @endphp
                            <td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
                                <input id="ilo_sdg_cdio_sdg{{ $i }}_text" name="ilo_sdg_cdio_sdg{{ $i }}_text[]" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="-" value="{{ old($prop, $syllabus?->{$prop} ?? '') }}" />
                            </td>
                        @endfor
                    </tr>
                @endif
        </tbody>
    </table>
    <script>document.addEventListener('DOMContentLoaded', function(){
        // root and container references used by the sync logic
        const root = document.querySelector('.ilo-sdg-cdio-mapping');
        const cdioContainer = document.getElementById('syllabus-cdio-sortable');
        const sdgContainer = document.getElementById('syllabus-sdg-sortable');
        if (!root) return; // nothing to do when mapping not present on the page

        // small debounce helper (used to avoid thrashing on DOM mutations/inputs)
        function debounce(fn, wait) {
            let t;
            return function(...args) { clearTimeout(t); t = setTimeout(() => fn.apply(this, args), wait); };
        }

    // Build colgroup where CDIO columns come first then SDG columns
    function updateColgroup(cdioCount, sdgCount){
        const colgroup = root.querySelector('colgroup'); if (!colgroup) return;
    const total = 2 + cdioCount + sdgCount; // include left spacer
        const colWidth = total > 0 ? (100 / total) : 100;
        const pct = (Math.round(colWidth * 100) / 100).toFixed(2);
        let html = '';
    html += `<col style="width:${pct}%">`; // left spacer
    html += `<col style="width:${pct}%">`; // ILO
        for (let i=0;i<cdioCount;i++) html += `<col style="width:${pct}%">`;
        for (let i=0;i<sdgCount;i++) html += `<col style="width:${pct}%">`;
        colgroup.innerHTML = html;
    }

    // Top-level sync that reads both CDIO and SDG labels and updates the table
    // Read labels from a container (tries code[] inputs, then badge text, else falls back to prefix+index)
    function readLabels(container, prefix){
        if (!container) return [];
        // Only consider visible, real rows (ignore templates and d-none rows)
        const rows = Array.from(container.querySelectorAll('tr')).filter(r => {
            if (!r || r.nodeType !== Node.ELEMENT_NODE) return false;
            if (r.id === 'sdg-template-row' || r.id === 'cdio-template-row') return false;
            if (r.classList && r.classList.contains('d-none')) return false;
            // must have either a textarea or a badge to be considered a real entry
            if (r.querySelector && (r.querySelector('textarea[name="sdgs[]"]') || r.querySelector('.cdio-badge') || r.querySelector('input[name="code[]"]'))) return true;
            return false;
        });
        const labels = rows.map((r, idx) => {
            const codeInput = r.querySelector('input[name="code[]"]');
            if (codeInput) return (codeInput.value || (prefix + (idx + 1))).trim();
            const badge = r.querySelector('.' + prefix.toLowerCase() + '-badge');
            if (badge && badge.textContent) return badge.textContent.trim();
            return prefix + (idx + 1);
        });
        // Ensure at least one column exists: if prefix contains 'SDG' return 'SDG1', otherwise numeric '1'
        if (labels.length === 0) return [(String(prefix).toUpperCase().includes('SDG') ? 'SDG1' : String(1))];
        return labels;
    }

    function syncNow(){
        const cdioLabels = readLabels(cdioContainer, 'CDIO');
        const sdgLabels = readLabels(sdgContainer, 'SDG');
        updateColgroup(cdioLabels.length, sdgLabels.length);
        updateHeaderRow(cdioLabels, sdgLabels);
        updateDataRows(cdioLabels, sdgLabels);
    }

    const debouncedSync = debounce(syncNow, 120);
    // initial sync
    syncNow();

    if (cdioContainer) {
        const mo = new MutationObserver(debouncedSync);
        mo.observe(cdioContainer, { childList:true, subtree:true, attributes:true, attributeFilter:['value'] });
        cdioContainer.addEventListener('input', debouncedSync);
    }

    if (sdgContainer) {
        const mo2 = new MutationObserver(debouncedSync);
        mo2.observe(sdgContainer, { childList:true, subtree:true, attributes:true, attributeFilter:['value'] });
        sdgContainer.addEventListener('input', debouncedSync);
    }

    // Update the second header row: CDIO labels first then SDG labels
    // cdioLabels and sdgLabels contain the textual codes (e.g., 'C1', 'SDG3') when available
    function updateHeaderRow(cdioLabels, sdgLabels){
        const tBody = root.querySelector('tbody');
        const rows = Array.from(tBody.querySelectorAll('tr'));
        const headerRow = rows[1]; if (!headerRow) return;
        let html = '';
        cdioLabels.forEach((l, i) => {
            const label = (l && String(l).trim()) ? String(l).trim() : String(i+1);
            html += `<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><strong>${escapeHtml(label)}</strong></td>`;
        });
        sdgLabels.forEach((l, i) => {
            let raw = (l && String(l).trim()) ? String(l).trim() : '';
            let label;
            if (!raw) {
                label = 'SDG' + String(i+1);
            } else if (/^\d+$/.test(raw)) {
                label = 'SDG' + raw;
            } else {
                label = raw;
            }
            html += `<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;\"><strong>${escapeHtml(label)}</strong></td>`;
        });
        headerRow.innerHTML = html;
    }

    // Rebuild each data row with CDIO inputs first, then SDG inputs
    function updateDataRows(cdioLabels, sdgLabels){
        const tbody = root.querySelector('tbody'); if (!tbody) return;
        // existing data rows (those with the hidden ILO input)
        const existing = Array.from(tbody.querySelectorAll('tr')).filter(r => r.querySelector && r.querySelector('input[name="ilo_sdg_cdio_ilos_text[]"]'));
        existing.forEach((row, idx) => {
            // determine a stable row index (prefer dataset.rowIndex if available)
            const rowIndex = (row.dataset && typeof row.dataset.rowIndex !== 'undefined') ? row.dataset.rowIndex : idx;
            // capture existing values to preserve where possible
            const existingCdioInputs = Array.from(row.querySelectorAll('input[id^="ilo_sdg_cdio_cdio"][type="text"]')).map(i => i.value || '');
            const existingSdgInputs = Array.from(row.querySelectorAll('input[id^="ilo_sdg_cdio_sdg"][type="text"]')).map(i => i.value || '');
            let html = '';
            // left spacer cell
            html += `<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"></td>`;
            // ILO badge + hidden input (preserve existing hidden input value)
            const hidden = row.querySelector('input[type="hidden"][name="ilo_sdg_cdio_ilos_text[]"]');
            const hiddenVal = hidden ? hidden.value : '';
            html += `<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><span class=\"ilo-badge fw-semibold d-inline-block text-center\" tabindex=\"0\">${escapeHtml(hiddenVal)}</span><input type=\"hidden\" name=\"ilo_sdg_cdio_ilos_text[]\" form=\"syllabusForm\" value=\"${escapeHtml(hiddenVal)}\" /></td>`;

            // CDIO inputs (include stable row index in ids)
            cdioLabels.forEach((_, i) => {
                const val = existingCdioInputs[i] ?? '';
                const inputId = `ilo_sdg_cdio_cdio${rowIndex}_${i+1}_text`;
                html += `<td style=\"border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;\"><input id=\"${inputId}\" name=\"ilo_sdg_cdio_cdio${i+1}_text[]\" form=\"syllabusForm\" type=\"text\" class=\"cis-input text-center cis-field\" placeholder=\"-\" value=\"${escapeHtml(val)}\" /></td>`;
            });

            // SDG inputs (include stable row index in ids)
            sdgLabels.forEach((_, i) => {
                const val = existingSdgInputs[i] ?? '';
                const inputId = `ilo_sdg_cdio_sdg${rowIndex}_${i+1}_text`;
                html += `<td style=\"border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;\"><input id=\"${inputId}\" name=\"ilo_sdg_cdio_sdg${i+1}_text[]\" form=\"syllabusForm\" type=\"text\" class=\"cis-input text-center cis-field\" placeholder=\"-\" value=\"${escapeHtml(val)}\" /></td>`;
            });

            // no trailing spacer
            // ensure the data-row-index attribute is present
            row.dataset.rowIndex = rowIndex;
            row.innerHTML = html;
        });
        // After rebuilding, ensure each data row has a consistent rowIndex according to current ordering
        const updated = Array.from(tbody.querySelectorAll('tr')).filter(r => r.querySelector && r.querySelector('input[name="ilo_sdg_cdio_ilos_text[]"]'));
        updated.forEach((r, i) => r.dataset.rowIndex = i);
    }

    function escapeHtml(str){ if (str === null || str === undefined) return ''; return String(str).replace(/[&<>"']/g, function(m){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[m]; }); }

    // Sync mapping rows with ILO list (ensure rows count and ILO badges align)
    function syncRowsToIlo(){
        const iloContainer = document.getElementById('syllabus-ilo-sortable');
        if (!iloContainer) return;
        const iloRows = Array.from(iloContainer.querySelectorAll('tr'));
        const tBody = root.querySelector('tbody');
        const allRows = Array.from(tBody.querySelectorAll('tr'));
        let dataRows = allRows.filter(r => r.querySelector('input[name="ilo_sdg_cdio_ilos_text[]"]'));
        const desired = Math.max(1, iloRows.length);
        let current = dataRows.length;

        // Add rows if needed
        if (current < desired) {
            const template = dataRows.length ? dataRows[dataRows.length - 1] : null;
            for (let i = current; i < desired; i++) {
                let newRow;
                if (template) {
                    newRow = template.cloneNode(true);
                    newRow.querySelectorAll('input').forEach(inp => { inp.value = ''; });
                    newRow.querySelectorAll('[id]').forEach(el => el.removeAttribute('id'));
                    newRow.querySelectorAll('.ilo-badge').forEach(b => { b.textContent = ''; b.className = 'ilo-badge fw-semibold d-inline-block text-center'; });
                } else {
                    newRow = document.createElement('tr');
                    newRow.innerHTML = '<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"></td><td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><span class="ilo-badge fw-semibold d-inline-block text-center" tabindex="0"></span><input type="hidden" name="ilo_sdg_cdio_ilos_text[]" form="syllabusForm" value="" /></td>';
                }
                // Assign a data-row-index for stable id generation and then append
                const existing = Array.from(tBody.querySelectorAll('tr')).filter(r => r.querySelector('input[name="ilo_sdg_cdio_ilos_text[]"]'));
                const nextIndex = existing.length; // zero-based
                newRow.dataset.rowIndex = nextIndex;
                // If template-derived, assign ids to inputs inside newRow using the rowIndex
                newRow.querySelectorAll('input[name^="ilo_sdg_cdio_cdio"]').forEach((inp, k) => { inp.id = `ilo_sdg_cdio_cdio${nextIndex}_${k+1}_text`; });
                newRow.querySelectorAll('input[name^="ilo_sdg_cdio_sdg"]').forEach((inp, k) => { inp.id = `ilo_sdg_cdio_sdg${nextIndex}_${k+1}_text`; });
                tBody.appendChild(newRow);
                current++;
            }
        }

        // Refresh and sync labels
        const updatedAll = Array.from(tBody.querySelectorAll('tr'));
        dataRows = updatedAll.filter(r => r.querySelector('input[name="ilo_sdg_cdio_ilos_text[]"]'));
        dataRows.forEach((row, idx) => {
            // ensure row has a stable data-row-index according to its position
            row.dataset.rowIndex = idx;
            const iloRow = iloRows[idx];
            // Force ILO labels to display as 'ILO {n}' (1-based) to match other mapping modules
            let labelText = 'ILO ' + String(idx + 1);
            if (iloRow) {
                const badge = iloRow.querySelector('.ilo-badge');
                if (badge && badge.textContent) {
                    const t = badge.textContent.trim();
                    const num = (t.match(/\d+/) ? t.match(/\d+/)[0] : String(idx + 1));
                    labelText = 'ILO ' + num;
                }
            }
            const badgeEl = row.querySelector('.ilo-badge');
            if (badgeEl) { badgeEl.textContent = labelText; badgeEl.className = 'ilo-badge fw-semibold d-inline-block text-center'; }
            // some partials use an '.ilo-label' element for visible labels; update that too
            const labelEl = row.querySelector('.ilo-label');
            if (labelEl) { labelEl.textContent = labelText; labelEl.className = 'ilo-label d-block text-center fw-semibold'; }
            const hidden = row.querySelector('input[type="hidden"][name="ilo_sdg_cdio_ilos_text[]"]');
            if (hidden) hidden.value = labelText;

            // Also update ids of inputs in the row to include the new rowIndex for stability
            row.querySelectorAll('input[id]').forEach(inp => {
                // remap cdio/sgd ids to include rowIndex where appropriate
                if (inp.name && inp.name.startsWith('ilo_sdg_cdio_cdio')) {
                    const parts = inp.name.match(/ilo_sdg_cdio_cdio(\d+)_text/);
                    if (parts) inp.id = `ilo_sdg_cdio_cdio${idx}_${parts[1]}_text`;
                }
                if (inp.name && inp.name.startsWith('ilo_sdg_cdio_sdg')) {
                    const parts = inp.name.match(/ilo_sdg_cdio_sdg(\d+)_text/);
                    if (parts) inp.id = `ilo_sdg_cdio_sdg${idx}_${parts[1]}_text`;
                }
            });
        });

    // Defensive rehydrate: if client sync logic cleared CDIO/SDG inputs, restore from server-rendered data-attributes
    // Only writes values when inputs are empty to avoid overwriting user edits.
    try {
        setTimeout(() => {
            const dataRows = Array.from(root.querySelectorAll('tbody tr[data-cdios][data-sdgs]'));
            dataRows.forEach((row) => {
                try {
                    const rawC = row.getAttribute('data-cdios');
                    const rawS = row.getAttribute('data-sdgs');
                    const cdioVals = rawC ? JSON.parse(rawC) : [];
                    const sdgVals = rawS ? JSON.parse(rawS) : [];
                    // find inputs by name patterns
                    const cdioInputs = Array.from(row.querySelectorAll('input[name^="ilo_sdg_cdio_cdio"]'));
                    const sdgInputs = Array.from(row.querySelectorAll('input[name^="ilo_sdg_cdio_sdg"]'));
                    for (let i = 0; i < Math.max(cdioInputs.length, cdioVals.length); i++) {
                        const inp = cdioInputs[i];
                        const v = cdioVals[i] ?? '';
                        if (inp && (inp.value === '' || inp.value === null)) inp.value = v ?? '';
                    }
                    for (let i = 0; i < Math.max(sdgInputs.length, sdgVals.length); i++) {
                        const inp = sdgInputs[i];
                        const v = sdgVals[i] ?? '';
                        if (inp && (inp.value === '' || inp.value === null)) inp.value = v ?? '';
                    }
                } catch (e) { /* noop - defensive */ }
            });
        }, 40);
    } catch (e) { /* noop */ }

        // Remove extras
        if (dataRows.length > desired) {
            for (let i = dataRows.length - 1; i >= desired; i--) {
                const toRemove = dataRows[i]; if (!toRemove) continue; if (dataRows.length <= 1) { toRemove.querySelectorAll('input').forEach(inp => inp.value = ''); } else { toRemove.parentNode.removeChild(toRemove); }
            }
        }
    }

    // Observe ILO list changes
    const iloList = document.getElementById('syllabus-ilo-sortable');
    if (iloList) {
        const iloObserver = new MutationObserver(debounce(syncRowsToIlo, 100));
        iloObserver.observe(iloList, { childList: true, subtree: true, attributes: true, attributeFilter: ['value'] });
        iloList.addEventListener('input', debounce(syncRowsToIlo, 100));
        // ensure initial alignment
        syncRowsToIlo();
    }

});
</script>