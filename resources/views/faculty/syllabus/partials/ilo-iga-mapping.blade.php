{{--
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/ilo-iga-mapping.blade.php
* Purpose: Small 2-row × 1-column box to display ILO → IGA mapping placeholder
* Notes: Copied structure and keyboard UI from ilo-so-cpa-mapping to keep behavior
* consistent (Ctrl/Cmd+Enter to add row, Ctrl/Cmd+Backspace to remove row).
-------------------------------------------------------------------------------
--}}

<div class="ilo-iga-mapping mb-4">
    <table class="table table-bordered" style="width:100%; table-layout:fixed; border:1px solid #343a40; border-collapse:collapse; font-size:12px;">
        @php
            $igasList = $igas ?? collect();
            $igaCount = $igasList->count();
            $totalCols = 2 + $igaCount; // spacer + ILO + IGAs
            $colWidth = $totalCols > 0 ? (int) floor(100 / $totalCols) : 100;
            $igaLabels = $igasList->map(fn($g) => $g->code ?? '');
        @endphp

        <colgroup>
            <col style="width:{{ $colWidth }}%;"> {{-- spacer --}}
            <col style="width:{{ $colWidth }}%;"> {{-- ILO label --}}
            @for ($i = 0; $i < $igaCount; $i++)
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
                <th colspan="{{ max(1, $igaCount) }}" style="border:1px solid #343a40; padding:0.25rem 0.5rem; min-height:3.5rem; vertical-align:middle; font-weight:700; font-family:Georgia, serif; font-size:12px; text-align:center;">INSTITUTIONAL GRADUATE ATTRIBUTES (IGA): Mapping of Assessment Tasks (AT)</th>
            </tr>
            <tr>
                @foreach ($igaLabels as $label)
                    <td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><strong>{{ $label ?: 'IGA' }}</strong></td>
                @endforeach
            </tr>
            <tr>
                <td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"></td>
                <td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
                    {{-- Visible label for ILO (non-editable) + hidden input for form submission --}}
                    <span class="ilo-label d-block text-center" tabindex="0">{{ old('ilo_iga_ilos_text', $syllabus?->ilo_iga_ilos_text ?? '') }}</span>
                    <input type="hidden" name="ilo_iga_ilos_text[]" form="syllabusForm" value="{{ old('ilo_iga_ilos_text', $syllabus?->ilo_iga_ilos_text ?? '') }}" data-original="{{ $syllabus?->ilo_iga_ilos_text ?? '' }}" />
                </td>
                @for ($i = 1; $i <= $igaCount; $i++)
                    @php $prop = 'ilo_iga_iga' . $i . '_text'; @endphp
                    <td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">
                        <input id="ilo_iga_iga{{ $i }}_text" name="ilo_iga_iga{{ $i }}_text[]" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="-" value="{{ old($prop, $syllabus?->{$prop} ?? '') }}" data-original="{{ $syllabus?->{$prop} ?? '' }}" />
                    </td>
                @endfor
            </tr>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const root = document.querySelector('.ilo-iga-mapping');
    if (!root) return;

    // Helper: find data rows (rows that contain input elements)
    function dataRows() {
        return Array.from(root.querySelectorAll('tbody tr')).filter(r => r.querySelector('input'));
    }

    document.addEventListener('keydown', function(e){
        // Only act when focus is inside this partial
        const active = document.activeElement;
        if (!active || !root.contains(active)) return;

        // Add row: Ctrl/Cmd + Enter
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter'){
            e.preventDefault();
            const rows = dataRows();
            if (!rows.length) return;
            const template = rows[rows.length - 1];
            const clone = template.cloneNode(true);
            // Clear input values in the cloned row and strip id/data-original to avoid duplicates
            clone.querySelectorAll('input').forEach(function(inp){
                inp.value = '';
                if (inp.hasAttribute('id')) inp.removeAttribute('id');
                if (inp.hasAttribute('data-original')) inp.removeAttribute('data-original');
            });
            // Clear visible ILO label text in the clone
            clone.querySelectorAll('.ilo-label').forEach(function(lbl){ lbl.textContent = ''; });
            // Append to tbody so new rows appear after existing data rows and below headers
            const tbody = root.querySelector('tbody');
            tbody.appendChild(clone);
            // Focus the visible label in the new row (keeps keyboard UX)
            const first = clone.querySelector('.ilo-label'); if (first) first.focus();
            return;
        }

        // Remove row: Ctrl/Cmd + Backspace
        if ((e.ctrlKey || e.metaKey) && e.key === 'Backspace'){
            e.preventDefault();
            const rows = dataRows();
            if (!rows.length) return;
            // Find the row containing the active element
            const row = active.closest('tr');
            if (!row) return;
            // If only one data row remains, clear its inputs instead of removing
            if (rows.length <= 1){
                row.querySelectorAll('input').forEach(function(inp){ inp.value = ''; });
                return;
            }
            // Remove the row and move focus to the previous data row's first input
            const idx = rows.indexOf(row);
            row.parentNode.removeChild(row);
            const prev = rows[Math.max(0, idx - 1)];
            if (prev) { const fi = prev.querySelector('input'); if (fi) fi.focus(); }
            return;
        }
    });
});
</script>

<script>
// Keep mapping columns in sync with IGA partial (#syllabus-iga-sortable)
document.addEventListener('DOMContentLoaded', function(){
    const igaContainer = document.getElementById('syllabus-iga-sortable');
    const mappingRoot = document.querySelector('.ilo-iga-mapping');
    if (!igaContainer || !mappingRoot) return;

    function debounce(fn, wait) {
        let t;
        return function(...args){ clearTimeout(t); t = setTimeout(()=> fn.apply(this, args), wait); };
    }

    function readIgaLabels() {
        const rows = Array.from(igaContainer.querySelectorAll('tr'));
        return rows.map((r, idx) => {
            const codeInput = r.querySelector('input[name="code[]"]');
            if (codeInput) return (codeInput.value || ('IGA' + (idx + 1))).trim();
            const badge = r.querySelector('.iga-badge');
            return badge ? (badge.textContent || ('IGA' + (idx + 1))).trim() : ('IGA' + (idx + 1));
        });
    }

    function updateColgroup(igaCount) {
        const colgroup = mappingRoot.querySelector('colgroup');
        if (!colgroup) return;
        const totalCols = 2 + igaCount; // spacer + ILO + IGAs
        const colWidth = totalCols > 0 ? Math.floor(100 / totalCols) : 100;
        let html = '';
        html += `<col style="width:${colWidth}%">`;
        html += `<col style="width:${colWidth}%">`;
        for (let i=0;i<igaCount;i++) html += `<col style="width:${colWidth}%">`;
        colgroup.innerHTML = html;
    }

    function updateHeaderRow(labels) {
        const tBody = mappingRoot.querySelector('tbody');
        const rows = Array.from(tBody.querySelectorAll('tr'));
        const headerRow = rows[1];
        if (!headerRow) return;
        let html = '';
        labels.forEach(label => {
            html += `<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><strong>${label || 'IGA'}</strong></td>`;
        });
        headerRow.innerHTML = html;
    }

    function updateDataRows(labels) {
        const tBody = mappingRoot.querySelector('tbody');
        const allRows = Array.from(tBody.querySelectorAll('tr'));
        const dataRows = allRows.filter(r => r.querySelector('input'));
        dataRows.forEach((row, idx) => {
            // read ILO value from hidden input so the visible label is non-editable
            const iloInput = row.querySelector('input[name="ilo_iga_ilos_text[]"]');
            const iloVal = iloInput ? iloInput.value : '';
            const existingIgaInputs = Array.from(row.querySelectorAll('input[id^="ilo_iga_iga"]')).map(i => i.value);

            // rebuild row: spacer + ILO cell
            let html = '';
            html += '<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"></td>';
            const visibleIlo = iloVal && String(iloVal).trim() ? iloVal : ('ILO ' + (idx + 1));
            // visible non-editable label + hidden input; submit fallback when original is empty
            html += `<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;">`;
            html += `<span class="ilo-label d-block text-center" tabindex="0">${escapeHtml(visibleIlo)}</span>`;
            html += `<input type="hidden" name="ilo_iga_ilos_text[]" form="syllabusForm" value="${escapeHtml(iloVal || visibleIlo)}" />`;
            html += '</td>';

            for (let i = 0; i < labels.length; i++) {
                const propIndex = i + 1;
                const propName = `ilo_iga_iga${propIndex}_text[]`;
                const val = existingIgaInputs[i] ?? '';
                html += `<td style="border:1px solid #343a40; padding:0.5rem; min-height:3.5rem; vertical-align:middle; text-align:center;"><input name="${propName}" form="syllabusForm" type="text" class="cis-input text-center cis-field" placeholder="-" value="${escapeHtml(val)}" /></td>`;
            }

            row.innerHTML = html;
        });
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/[&<>"']/g, function(m){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[m]; });
    }

    function syncNow() {
        const labels = readIgaLabels();
        updateColgroup(labels.length);
        updateHeaderRow(labels);
        updateDataRows(labels);
    }

    const debouncedSync = debounce(syncNow, 120);
    syncNow();

    const mo = new MutationObserver(debouncedSync);
    mo.observe(igaContainer, { childList: true, subtree: true, attributes: true, attributeFilter: ['value'] });
    igaContainer.addEventListener('input', debouncedSync);

    // Keep mapping data rows in sync with ILO list (#syllabus-ilo-sortable)
    const iloContainer = document.getElementById('syllabus-ilo-sortable');
    if (iloContainer) {
        function syncRowsToIlo() {
            const iloRows = Array.from(iloContainer.querySelectorAll('tr'));
            const tBody = mappingRoot.querySelector('tbody');
            const allRows = Array.from(tBody.querySelectorAll('tr'));
            const dataRows = allRows.filter(r => r.querySelector('input'));
            const desired = Math.max(1, iloRows.length);
            const current = dataRows.length;

            if (current < desired) {
                const template = dataRows.length ? dataRows[dataRows.length - 1] : null;
                for (let i = current; i < desired; i++) {
                    let newRow;
                    if (template) {
                        newRow = template.cloneNode(true);
                        newRow.querySelectorAll('input').forEach(inp => { inp.value = ''; if (inp.hasAttribute('id')) inp.removeAttribute('id'); if (inp.hasAttribute('data-original')) inp.removeAttribute('data-original'); });
                    } else {
                        newRow = document.createElement('tr');
                        newRow.innerHTML = tBody.querySelector('tr[data-template]') ? tBody.querySelector('tr[data-template]').innerHTML : '';
                    }
                    // Always append new data rows to the end of tbody so they appear after existing data rows
                    tBody.appendChild(newRow);
                }
            }

            if (current > desired) {
                for (let i = 0; i < (current - desired); i++) {
                    const toRemove = dataRows[dataRows.length - 1 - i];
                    if (!toRemove) break;
                    if (dataRows.length - i <= 1) {
                        toRemove.querySelectorAll('input').forEach(inp => inp.value = '');
                    } else {
                        toRemove.parentNode.removeChild(toRemove);
                    }
                }
            }

                // After adjusting rows, sync visible ILO labels to match ILO list badges
                const updatedAll = Array.from(tBody.querySelectorAll('tr'));
                const updatedDataRows = updatedAll.filter(r => r.querySelector('input'));
                updatedDataRows.forEach((row, idx) => {
                    const iloRow = iloRows[idx];
                    let labelText = 'ILO ' + (idx + 1);
                    if (iloRow) {
                        const badge = iloRow.querySelector('.ilo-badge');
                        if (badge && badge.textContent) labelText = badge.textContent.trim();
                    }
                    const lbl = row.querySelector('.ilo-label');
                    if (lbl) { lbl.textContent = labelText; lbl.className = 'ilo-label d-block text-center fw-semibold'; }
                    const hidden = row.querySelector('input[type="hidden"][name="ilo_iga_ilos_text[]"]');
                    if (hidden) hidden.value = labelText;
                });
        }

        const iloMo = new MutationObserver(debounce(syncRowsToIlo, 100));
        iloMo.observe(iloContainer, { childList: true, subtree: true, attributes: true, attributeFilter: ['value'] });
        iloContainer.addEventListener('input', debounce(syncRowsToIlo, 100));
        syncRowsToIlo();
    }
});
</script>
