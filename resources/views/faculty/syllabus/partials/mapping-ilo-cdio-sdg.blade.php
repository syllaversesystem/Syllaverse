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
            // spacer + ILO + CDIOs + SDGs (add back left spacer column)
                $totalCols = 2 + $cdioCount + $sdgCount;
            // use a fractional percent so columns fill the width exactly
            $colWidth = $totalCols > 0 ? number_format(100 / $totalCols, 4) : '100.0000';
        @endphp
        <colgroup>
            <col style="width:{{ $colWidth }}%"> {{-- left spacer --}}
            <col style="width:{{ $colWidth }}%"> {{-- ILO label --}}
            {{-- CDIO columns first --}}
            @for ($i = 0; $i < $cdioCount; $i++)
                <col style="width:{{ $colWidth }}%;">
            @endfor
            {{-- SDG columns next --}}
            @for ($i = 0; $i < $sdgCount; $i++)
                <col style="width:{{ $colWidth }}%;">
            @endfor
            {{-- no trailing spacer column --}}
        </colgroup>

        <tbody>
            <tr>
                <td rowspan="2" style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"></td>
                <td rowspan="2" style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
                    <div style="height:100%; display:flex; align-items:center; justify-content:center;">
                        <strong style="font-family:Georgia, serif; font-size:12px;">ILOs</strong>
                    </div>
                </td>
                {{-- Split header into two blocks: CDIO (left) and SDG (right) --}}
                <th colspan="{{ max(1, $cdioCount) }}" style="border:1px solid #343a40; padding:0.25rem 0.5rem; min-height:3.5rem; vertical-align:middle; font-weight:700; font-family:Georgia, serif; font-size:12px; text-align:center;">CDIO SKILLS</th>
                <th colspan="{{ max(1, $sdgCount) }}" style="border:1px solid #343a40; padding:0.25rem 0.5rem; min-height:3.5rem; vertical-align:middle; font-weight:700; font-family:Georgia, serif; font-size:12px; text-align:center;">SDG Skills</th>
            </tr>
            <tr>
                @php
                    $sdgLabels = $sdgsList->map(fn($s) => $s->code ?? '');
                    $cdioLabels = $cdiosList->map(fn($c) => $c->code ?? '');
                @endphp
                {{-- CDIO labels first (left block) — force numeric labels --}}
                @foreach ($cdioLabels as $idx => $label)
                    <td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><strong>{{ $idx + 1 }}</strong></td>
                @endforeach
                {{-- SDG labels next (right block) — force numeric labels --}}
                @foreach ($sdgLabels as $idx => $label)
                    <td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><strong>{{ $idx + 1 }}</strong></td>
                @endforeach
            </tr>

            <tr>
                <td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"></td>
                <td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
                    {{-- Visible badge-style ILO label (non-editable) + hidden input for form --}}
                    <span class="ilo-badge fw-semibold d-inline-block text-center" tabindex="0">{{ old('ilo_sdg_cdio_ilos_text', $syllabus?->ilo_sdg_cdio_ilos_text ?? '') }}</span>
                    <input type="hidden" name="ilo_sdg_cdio_ilos_text[]" form="syllabusForm" value="{{ old('ilo_sdg_cdio_ilos_text', $syllabus?->ilo_sdg_cdio_ilos_text ?? '') }}" data-original="{{ $syllabus?->ilo_sdg_cdio_ilos_text ?? '' }}" />
                </td>

                {{-- CDIO inputs first to match header placement --}}
                @for ($i = 1; $i <= $cdioCount; $i++)
                    @php $prop = 'ilo_sdg_cdio_cdio' . $i . '_text'; @endphp
                    <td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
                        <input id="ilo_sdg_cdio_cdio{{ $i }}_text" name="ilo_sdg_cdio_cdio{{ $i }}_text[]" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="-" value="{{ old($prop, $syllabus?->{$prop} ?? '') }}" data-original="{{ $syllabus?->{$prop} ?? '' }}" />
                    </td>
                @endfor

                {{-- SDG inputs next --}}
                @for ($i = 1; $i <= $sdgCount; $i++)
                    @php $prop = 'ilo_sdg_cdio_sdg' . $i . '_text'; @endphp
                    <td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
                        <input id="ilo_sdg_cdio_sdg{{ $i }}_text" name="ilo_sdg_cdio_sdg{{ $i }}_text[]" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="-" value="{{ old($prop, $syllabus?->{$prop} ?? '') }}" data-original="{{ $syllabus?->{$prop} ?? '' }}" />
                    </td>
                @endfor
            {{-- no trailing spacer cell --}}
            </tr>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const root = document.querySelector('.ilo-sdg-cdio-mapping');
    if (!root) return;

    // helper to find data rows (those containing the hidden ILO input)
    function dataRows() { return Array.from(root.querySelectorAll('tbody tr')).filter(r => r.querySelector('input[name="ilo_sdg_cdio_ilos_text[]"]')); }

    // keyboard add/remove (Ctrl/Cmd+Enter, Ctrl/Cmd+Backspace) reusing existing UX
    document.addEventListener('keydown', function(e){
        const active = document.activeElement; if (!active || !root.contains(active)) return;
        // add
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter'){
            e.preventDefault(); const rows = dataRows(); if (!rows.length) return; const template = rows[rows.length - 1]; const clone = template.cloneNode(true);
            clone.querySelectorAll('input').forEach(inp => { inp.value = ''; if (inp.hasAttribute('id')) inp.removeAttribute('id'); if (inp.hasAttribute('data-original')) inp.removeAttribute('data-original'); });
            clone.querySelectorAll('.ilo-badge').forEach(b => { b.textContent = ''; b.className = 'ilo-badge fw-semibold d-inline-block text-center'; });
            const tbody = root.querySelector('tbody'); tbody.appendChild(clone);
            const newRows = dataRows(); const idx = newRows.indexOf(clone); const badge = clone.querySelector('.ilo-badge'); const newLabel = 'ILO' + (idx + 1); if (badge) badge.textContent = newLabel; const hidden = clone.querySelector('input[type="hidden"][name="ilo_sdg_cdio_ilos_text[]"]'); if (hidden) hidden.value = newLabel; const firstVisible = clone.querySelector('.ilo-badge'); if (firstVisible) firstVisible.focus(); return;
        }
        // remove
        if ((e.ctrlKey || e.metaKey) && e.key === 'Backspace'){
            e.preventDefault(); const rows = dataRows(); if (!rows.length) return; const row = active.closest('tr'); if (!row) return; if (rows.length <= 1){ row.querySelectorAll('input').forEach(i=>i.value=''); return; } const idx = rows.indexOf(row); row.parentNode.removeChild(row); const prev = rows[Math.max(0, idx - 1)]; if (prev) { const fi = prev.querySelector('input'); if (fi) fi.focus(); } return;
        }
    });

    // Sync columns/rows with SDG and CDIO lists
    const sdgContainer = document.getElementById('syllabus-sdg-sortable');
    const cdioContainer = document.getElementById('syllabus-cdio-sortable');
    // debounce helper
    function debounce(fn, wait){ let t; return function(...args){ clearTimeout(t); t = setTimeout(()=> fn.apply(this, args), wait); }; }


    // Read CDIO or SDG labels from their respective sortable containers
    // Only count visible, real rows (exclude template rows and those hidden with d-none)
    function readLabels(container, prefix){
        if (!container) return [];
        const rows = Array.from(container.querySelectorAll('tr')).filter(r => {
            if (!r || r.id === 'sdg-template-row' || r.id === 'cdio-template-row') return false;
            if (r.classList && r.classList.contains('d-none')) return false;
            // prefer rows that contain a visible code/badge or an input for the label
            if (r.querySelector && (r.querySelector('.cdio-badge') || r.querySelector('.ilo-badge') || r.querySelector('input[name="sdgs[]"]') || r.querySelector('input[name="code[]"]'))) return true;
            return false;
        });
        // Force numeric labels (1-based index)
        return rows.map((r, idx) => String(idx + 1));
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
    function updateHeaderRow(cdioLabels, sdgLabels){ const tBody = root.querySelector('tbody'); const rows = Array.from(tBody.querySelectorAll('tr')); const headerRow = rows[1]; if (!headerRow) return; let html = ''; cdioLabels.forEach((l, i) => { const label = l || String(i+1); html += `<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><strong>${escapeHtml(label)}</strong></td>`; }); sdgLabels.forEach((l, i) => { const label = l || String(i+1); html += `<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><strong>${escapeHtml(label)}</strong></td>`; }); headerRow.innerHTML = html; }

    // Rebuild each data row with CDIO inputs first, then SDG inputs
    function updateDataRows(cdioLabels, sdgLabels){
        const tbody = root.querySelector('tbody'); if (!tbody) return;
        // existing data rows (those with the hidden ILO input)
        const existing = Array.from(tbody.querySelectorAll('tr')).filter(r => r.querySelector && r.querySelector('input[name="ilo_sdg_cdio_ilos_text[]"]'));
        existing.forEach((row) => {
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

            // CDIO inputs
            cdioLabels.forEach((_, i) => {
                const val = existingCdioInputs[i] ?? '';
                html += `<td style=\"border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;\"><input id=\"ilo_sdg_cdio_cdio${i+1}_text\" name=\"ilo_sdg_cdio_cdio${i+1}_text[]\" form=\"syllabusForm\" type=\"text\" class=\"cis-input text-center cis-field\" placeholder=\"-\" value=\"${escapeHtml(val)}\" /></td>`;
            });

            // SDG inputs
            sdgLabels.forEach((_, i) => {
                const val = existingSdgInputs[i] ?? '';
                html += `<td style=\"border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;\"><input id=\"ilo_sdg_cdio_sdg${i+1}_text\" name=\"ilo_sdg_cdio_sdg${i+1}_text[]\" form=\"syllabusForm\" type=\"text\" class=\"cis-input text-center cis-field\" placeholder=\"-\" value=\"${escapeHtml(val)}\" /></td>`;
            });

            // no trailing spacer
            row.innerHTML = html;
        });
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
                    newRow.querySelectorAll('input').forEach(inp => { inp.value = ''; if (inp.hasAttribute('id')) inp.removeAttribute('id'); if (inp.hasAttribute('data-original')) inp.removeAttribute('data-original'); });
                    newRow.querySelectorAll('.ilo-badge').forEach(b => { b.textContent = ''; b.className = 'ilo-badge fw-semibold d-inline-block text-center'; });
                } else {
                    newRow = document.createElement('tr');
                    newRow.innerHTML = '<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"></td><td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><span class="ilo-badge fw-semibold d-inline-block text-center" tabindex="0"></span><input type="hidden" name="ilo_sdg_cdio_ilos_text[]" form="syllabusForm" value="" /></td>';
                }
                tBody.appendChild(newRow);
                current++;
            }
        }

        // Refresh and sync labels
        const updatedAll = Array.from(tBody.querySelectorAll('tr'));
        dataRows = updatedAll.filter(r => r.querySelector('input[name="ilo_sdg_cdio_ilos_text[]"]'));
        dataRows.forEach((row, idx) => {
            const iloRow = iloRows[idx];
            let labelText = 'ILO' + (idx + 1);
            if (iloRow) {
                const badge = iloRow.querySelector('.ilo-badge');
                if (badge && badge.textContent) labelText = badge.textContent.trim();
            }
            const badgeEl = row.querySelector('.ilo-badge'); if (badgeEl) { badgeEl.textContent = labelText; badgeEl.className = 'ilo-badge fw-semibold d-inline-block text-center'; }
            const hidden = row.querySelector('input[type="hidden"][name="ilo_sdg_cdio_ilos_text[]"]'); if (hidden) hidden.value = labelText;
        });

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