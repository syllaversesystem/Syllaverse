{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/sdg.blade.php
* Description: SDG Mapping (CIS-style layout, compact height and narrow actions) – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-07-29] Refactored layout to match CIS format.
[2025-07-29] Reduced textarea and button height; made action column smaller; icon-only compact buttons.
-------------------------------------------------------------------------------
--}}

<form id="sdgForm" action="{{ route('faculty.syllabi.sdgs.save', $default['id']) }}" method="POST">
  @csrf
  <table class="table table-bordered mb-4 cis-table sdg-module" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.4;">
    <style>
      /* Reuse SO/CDIO layout styles for visual parity */
      .so-left-title { font-weight: 700; padding: 0.5rem; font-family: Georgia, serif; vertical-align: top; box-sizing: border-box; line-height: 1.2; font-size: 0.97rem; }
      table.cis-table { table-layout: fixed; margin: 0; }
      #sdg-right-wrap { padding: 0; margin: 0; }
      #sdg-right-wrap > table { width: 100%; height: 100%; margin: 0; border-spacing: 0; border-collapse: collapse; }
      #sdg-right-wrap td, #sdg-right-wrap th { vertical-align: middle; padding: 0.45rem 0.5rem; }
      #sdg-right-wrap > table th, #sdg-right-wrap > table td { border: 1px solid #dee2e6; }
      #sdg-right-wrap > table thead th { border-top: 0; }
      #sdg-right-wrap > table th:first-child, #sdg-right-wrap > table td:first-child { border-left: 0; }
      #sdg-right-wrap > table th:last-child, #sdg-right-wrap > table td:last-child { border-right: 0; }
      #sdg-right-wrap > table tbody tr:last-child td { border-bottom: 0 !important; }
      .cdio-badge { display: inline-block; min-width: 48px; text-align: center; font-weight: 700; }
      .drag-handle { width: 28px; display: inline-flex; justify-content: center; }
      .cis-textarea { width: 100%; box-sizing: border-box; resize: none; padding: 0.25rem 0.4rem; border-radius: 4px; }
      textarea.autosize { min-height: 42px; overflow: hidden; }
      .btn-delete-cdio { margin-left: 0.25rem; }
      .cdio-code-box { display: inline-block; padding: 6px 8px; border: 1px solid #111; background:#fff; font-weight:700; }
      </style>

      <style>
  /* Clean professional Add button in table header */
        .sdg-add-btn { display: inline-flex; align-items: center; gap: .35rem; padding: .28rem .5rem; border-radius: 6px; }
        .sdg-add-btn i { width: 16px; height: 16px; }

  /* Outer SDG module border: top and right lines */
  /* Use Bootstrap's .table-bordered for the separation lines to match CDIO module */
  .sdg-module { border: none; }

        /* SDG bordered container for title + description */
  /* Remove surrounding box for the description, keep title underline */
  .sdg-box { border: none; padding: .25rem 0; background: transparent; }
  .sdg-box .sdg-title { margin-bottom: .15rem; }
  /* Strong title underline without an outer box */
  .sdg-box-strong { border: none; padding: .15rem 0; }
  .sdg-box-strong .sdg-title { padding-bottom: .15rem; border-bottom: 2px solid #111; display: block; margin-bottom: .4rem; }
  .sdg-cell { vertical-align: middle; }
  /* Make the SDG description textarea appear borderless inside the boxed container */
  /* Keep most SDG textareas visually inline (borderless) but allow the
    dedicated description input to show the yellow highlight when present. */
  .sdg-box .cis-textarea:not(.sdg-description-input) { border: none !important; box-shadow: none !important; background: transparent !important; padding: 0 !important; margin: 0 !important; }
  .sdg-box .cis-textarea:not(.sdg-description-input):focus { outline: none !important; box-shadow: none !important; border: none !important; background: transparent !important; }

  /* Let the global textarea.form-control:focus rule (CDIO) control the visible highlight
    so SDG textareas look exactly like CDIO's. Keep sdg-description-input class for JS hooks. */
  .sdg-description-input { /* intentionally blank: visual handled by global rules */ }

  /* Make the single-line SDG title input look and behave exactly like the
     SDG description textarea: same typography, spacing and focus highlight. */
  .sdg-title-input {
    display: block;
    width: 100%;
    box-sizing: border-box;
    font-family: Georgia, serif;
    font-size: 13px;
    line-height: 1.4;
    font-weight: 600;
    background: transparent !important;
    border: none !important;
    padding: 0 !important;
    margin: 0 0 0.15rem 0 !important;
  }
  .sdg-title-input:focus {
    background-color: #fffbe6 !important; /* same as textarea.form-control:focus */
    padding: 0.25rem !important;
    border-radius: 6px !important;
    border: 1px solid rgba(0,0,0,0.06) !important;
    box-shadow: 0 4px 14px rgba(16,185,129,0.06);
    outline: none !important;
  }
  /* Left SDG code cell (badge) styling - boxed container */
  .sdg-code-cell { padding: .35rem .5rem; border: 2px solid #111; border-right: 2px solid #111; border-radius: 6px; background: #fff; vertical-align: middle; }
  .sdg-code-cell .cdio-badge { font-family: Georgia, serif; font-weight:700; display:block; }
      </style>

    <colgroup>
      <col style="width:16%">
      <col style="width:84%">
    </colgroup>
    <tbody>
      <tr>
        <th class="align-top text-start cis-label so-left-title">Sustainable Development Goals (SDG)
          <div class="d-flex align-items-center gap-2">
            <span id="unsaved-sdgs" class="unsaved-pill d-none">Unsaved</span>
          </div>
        </th>
        <td id="sdg-right-wrap">
          <table class="table mb-0" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.4; border: none;">
            <colgroup>
              <col style="width: 10%">
              <col style="width: 90%">
            </colgroup>
          <!-- Inline fallback: lightweight delete handler (works without rebuilt assets) -->
          <script>
          document.addEventListener('DOMContentLoaded', function () {
            try {
              const list = document.getElementById('syllabus-sdg-sortable');
              if (!list) return;
              list.addEventListener('click', function (ev) {
                const btn = ev.target.closest && ev.target.closest('.btn-delete-cdio');
                if (!btn) return;
                const row = btn.closest('tr'); if (!row) return;
                // If row is new (no server id), just remove
                const entryId = row.getAttribute('data-id');
                const sdgId = row.getAttribute('data-sdg-id');
                const syllabusId = list.dataset ? list.dataset.syllabusId : null;
                if (!entryId || entryId.startsWith('new-')) { row.remove(); try { if (window.updateVisibleCodes) window.updateVisibleCodes(); } catch (e) {} return; }
                if (!confirm('Are you sure you want to delete this SDG?')) return;
                // build delete url (match controller routes)
                let deleteUrl = '';
                if (entryId && syllabusId) deleteUrl = '/faculty/syllabi/' + syllabusId + '/sdgs/entry/' + entryId;
                else if (sdgId && syllabusId) deleteUrl = '/faculty/syllabi/' + syllabusId + '/sdgs/' + sdgId;
                else deleteUrl = '/faculty/syllabi/sdgs/' + entryId;
                fetch(deleteUrl, { method: 'DELETE', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
                  .then(async (res) => {
                    if (!res.ok) throw new Error(await res.text().catch(() => 'Delete failed'));
                    return res.json().catch(() => ({}));
                  })
                  .then((data) => {
                    try {
                      if (window.handleSdgRowRemoval) {
                        window.handleSdgRowRemoval(row, sdgId, data.message || 'SDG removed');
                        return;
                      }
                    } catch (e) { console.error('handler call failed', e); }
                    // fallback behavior
                    try { row.remove(); } catch (e) { if (row.parentNode) row.parentNode.removeChild(row); }
                    try { if (window.updateVisibleCodes) window.updateVisibleCodes(); } catch (e) {}
                    try {
                      const list = document.getElementById('syllabus-sdg-sortable');
                      const syllabusId = list && list.dataset ? list.dataset.syllabusId : null;
                      if (syllabusId) {
                        const rows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector && (r.querySelector('textarea[name="sdgs[]"]') || r.querySelector('.cdio-badge')) && (!r.id || r.id !== 'sdg-template-row'));
                        const positions = rows.map((r, idx) => ({ id: r.getAttribute('data-id') || null, position: idx + 1 })).filter(p => p.id);
                        if (positions.length) fetch('/faculty/syllabi/' + syllabusId + '/sdgs/reorder', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }, body: JSON.stringify({ positions }) }).catch(()=>{});
                      }
                    } catch (e) {}
                    try {
                      const list = document.querySelector('.sdg-checkbox-list');
                      const titleEl = row.querySelector('input[name="title[]"]') || row.querySelector('.sdg-title') || row.querySelector('textarea[name="sdgs[]"]');
                      const title = titleEl ? (titleEl.value || titleEl.textContent || '') : null;
                      if (list && sdgId && !list.querySelector(`#sdg_check_${sdgId}`)) {
                        const wrapper = document.createElement('div'); wrapper.className = 'form-check mb-1';
                        const input = document.createElement('input'); input.className = 'form-check-input sdg-checkbox'; input.type = 'checkbox'; input.id = `sdg_check_${sdgId}`; input.value = sdgId;
                        const label = document.createElement('label'); label.className = 'form-check-label small'; label.htmlFor = input.id; label.textContent = title || ('SDG ' + sdgId);
                        wrapper.appendChild(input); wrapper.appendChild(label); list.appendChild(wrapper);
                      }
                    } catch (e) {}
                  })
                  .catch(err => { console.error(err); alert(err && err.message ? err.message : 'Failed to delete SDG.'); });
              });
            } catch (e) { console.error('SDG fallback handler init failed', e); }
          });
          </script>

              <tr class="table-light">
                  <th class="text-center cis-label">SDG</th>
                  <th class="text-start cis-label">
                    <div class="d-flex justify-content-between align-items-center">
                      <span>SDG Skills</span>
                      <div>
      <button type="button" class="btn btn-sm btn-outline-primary sdg-add-btn" data-bs-toggle="modal" data-bs-target="#addSdgModal" title="Add SDG from master data">
                          <i data-feather="plus"></i>

                      <script>
                      // Inline drag-and-drop fallback for SDG rows when SortableJS is not available
                      document.addEventListener('DOMContentLoaded', function () {
                        try {
                          if (window.Sortable) return; // let compiled bundle handle it if present
                          const list = document.getElementById('syllabus-sdg-sortable');
                          if (!list) return;

                          function makeDraggable(row) {
                            if (!row || row.id === 'sdg-template-row') return;
                            row.setAttribute('draggable', 'true');
                          }

                          // initialize existing rows
                          Array.from(list.querySelectorAll('tr')).forEach(makeDraggable);

                          // observe new rows
                          const mo = new MutationObserver((mutations) => {
                            for (const m of mutations) {
                              for (const n of m.addedNodes) { if (n && n.nodeType === 1 && n.tagName === 'TR') makeDraggable(n); }
                            }
                          });
                          try { mo.observe(list, { childList: true }); } catch (e) {}

                          let dragged = null;

                          function renumberLocal() {
                            try {
                              const rows = Array.from(list.children).filter(r => {
                                if (!r || r.nodeType !== Node.ELEMENT_NODE) return false;
                                if (r.id && r.id === 'sdg-template-row') return false;
                                if (r.classList && r.classList.contains('d-none')) return false;
                                return Boolean(r.querySelector && (r.querySelector('textarea[name="sdgs[]"]') || r.querySelector('.cdio-badge')));
                              });
                              rows.forEach((row, idx) => {
                                const code = `SDG${idx + 1}`;
                                const badge = row.querySelector('.cdio-badge'); if (badge) badge.textContent = code;
                                const codeInput = row.querySelector('input[name="code[]"]'); if (codeInput) codeInput.value = code;
                                const btn = row.querySelector('.btn-delete-cdio'); if (btn) btn.style.display = '';
                                try { row.setAttribute('data-code', code); } catch (e) {}
                              });
                            } catch (e) { console.error('renumberLocal failed', e); }
                          }

                          list.addEventListener('dragstart', function (e) {
                            const tr = e.target.closest && e.target.closest('tr');
                            if (!tr) return;
                            dragged = tr;
                            tr.classList.add('dragging');
                            try { e.dataTransfer.setData('text/plain', 'drag-sdg'); } catch (ex) {}
                            e.dataTransfer.effectAllowed = 'move';
                          });

                          list.addEventListener('dragover', function (e) {
                            if (!dragged) return; e.preventDefault();
                            const over = e.target.closest && e.target.closest('tr');
                            if (!over || over === dragged || over.id === 'sdg-template-row') return;
                            const rect = over.getBoundingClientRect();
                            const after = (e.clientY - rect.top) > (rect.height / 2);
                            const parent = over.parentNode;
                            if (after && over.nextSibling !== dragged) parent.insertBefore(dragged, over.nextSibling);
                            else if (!after && over !== dragged.nextSibling) parent.insertBefore(dragged, over);
                          });

                          list.addEventListener('drop', function (e) {
                            if (!dragged) return; e.preventDefault();
                            try { dragged.classList.remove('dragging'); } catch (e) {}
                            dragged = null;
                            // renumber and persist order
                            try {
                              if (window.updateVisibleCodes) window.updateVisibleCodes(); else renumberLocal();
                            } catch (e) {}
                            try { if (window.saveSdgOrder) window.saveSdgOrder(); else if (window.persistSdgOrder) window.persistSdgOrder(); else {
                              // inline persist fallback
                              const syllabusId = list.dataset ? list.dataset.syllabusId : null; if (syllabusId) {
                                const rows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector && (r.querySelector('textarea[name="sdgs[]"]') || r.querySelector('.cdio-badge')) && (!r.id || r.id !== 'sdg-template-row'));
                                const positions = rows.map((r, idx) => ({ id: r.getAttribute('data-id') || null, position: idx + 1 })).filter(p => p.id);
                                if (positions.length) fetch('/faculty/syllabi/' + syllabusId + '/sdgs/reorder', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }, body: JSON.stringify({ positions }) }).catch(()=>{});
                              }
                            } } catch (e) {}
                            // Mark module as unsaved and enable top Save button (fallback)
                            try { document.dispatchEvent(new CustomEvent('sdg:reordered', { detail: { source: 'inline-fallback' } })); } catch (e) {}
                          });

                          list.addEventListener('dragend', function () { if (dragged) { try { dragged.classList.remove('dragging'); } catch (e) {} dragged = null; } });
                        } catch (e) { console.error('SDG inline drag fallback failed', e); }
                      });
                      </script>

                      <script>
                      function fallbackDeleteSdg(btn) {
                        try {
                          const row = btn.closest('tr'); if (!row) return;
                          const list = document.getElementById('syllabus-sdg-sortable');
                          const entryId = row.getAttribute('data-id');
                          const sdgId = row.getAttribute('data-sdg-id');
                          const syllabusId = list && list.dataset ? list.dataset.syllabusId : null;
                          if (!entryId || entryId.startsWith('new-')) { row.remove(); try { if (window.updateVisibleCodes) window.updateVisibleCodes(); } catch (e) {} return; }
                          if (!confirm('Are you sure you want to delete this SDG?')) return;
                          let deleteUrl = '';
                          if (entryId && syllabusId) deleteUrl = '/faculty/syllabi/' + syllabusId + '/sdgs/entry/' + entryId;
                          else if (sdgId && syllabusId) deleteUrl = '/faculty/syllabi/' + syllabusId + '/sdgs/' + sdgId;
                          else deleteUrl = '/faculty/syllabi/sdgs/' + entryId;
                          fetch(deleteUrl, { method: 'DELETE', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
                            .then(async (res) => {
                              if (!res.ok) throw new Error(await res.text().catch(() => 'Delete failed'));
                              return res.json().catch(() => ({}));
                            })
                            .then((data) => {
                              try {
                                if (window.handleSdgRowRemoval) { window.handleSdgRowRemoval(row, sdgId, data.message || 'SDG removed'); return; }
                              } catch (e) { console.error('handler failed', e); }
                              try { row.remove(); } catch (e) { if (row.parentNode) row.parentNode.removeChild(row); }
                              try { if (window.updateVisibleCodes) window.updateVisibleCodes(); } catch (e) {}
                              try {
                                const select = document.querySelector('#sdg_id');
                                const titleEl = row.querySelector('input[name="title[]"]') || row.querySelector('.sdg-title') || row.querySelector('textarea[name="sdgs[]"]');
                                const title = titleEl ? (titleEl.value || titleEl.textContent || '') : null;
                                if (select && sdgId && !select.querySelector('option[value="' + sdgId + '"]')) {
                                  const opt = document.createElement('option'); opt.value = sdgId; opt.textContent = title || ('SDG ' + sdgId); select.appendChild(opt);
                                }
                              } catch (e) {}
                            })
                            .catch(err => { console.error(err); alert(err && err.message ? err.message : 'Failed to delete SDG.'); });
                        } catch (e) { console.error('fallbackDeleteSdg error', e); }
                      }
                      </script>

                      <script>
                      function fallbackMoveUp(btn) {
                        try {
                          const row = btn.closest('tr'); if (!row) return;
                          const prev = row.previousElementSibling; if (!prev || !prev.querySelector) return;
                          prev.parentNode.insertBefore(row, prev);
                          try { if (window.updateVisibleCodes) window.updateVisibleCodes(); } catch (e) {}
                          // persist order using inline fetch (works without compiled bundle)
                          try {
                            const list = document.getElementById('syllabus-sdg-sortable');
                            const syllabusId = list && list.dataset ? list.dataset.syllabusId : null;
                            if (syllabusId) {
                              const rows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector && (r.querySelector('textarea[name="sdgs[]"]') || r.querySelector('.cdio-badge')) && (!r.id || r.id !== 'sdg-template-row'));
                              const positions = rows.map((r, idx) => ({ id: r.getAttribute('data-id') || null, position: idx + 1 })).filter(p => p.id);
                              if (positions.length) fetch('/faculty/syllabi/' + syllabusId + '/sdgs/reorder', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }, body: JSON.stringify({ positions }) }).catch(()=>{});
                            }
                          } catch (e) {}
                        } catch (e) { console.error('fallbackMoveUp', e); }
                      }
                      function fallbackMoveDown(btn) {
                        try {
                          const row = btn.closest('tr'); if (!row) return;
                          const next = row.nextElementSibling; if (!next || !next.querySelector) return;
                          next.parentNode.insertBefore(next, row);
                          try { if (window.updateVisibleCodes) window.updateVisibleCodes(); } catch (e) {}
                          // persist order using inline fetch (works without compiled bundle)
                          try {
                            const list = document.getElementById('syllabus-sdg-sortable');
                            const syllabusId = list && list.dataset ? list.dataset.syllabusId : null;
                            if (syllabusId) {
                              const rows = Array.from(list.querySelectorAll('tr')).filter(r => r.querySelector && (r.querySelector('textarea[name="sdgs[]"]') || r.querySelector('.cdio-badge')) && (!r.id || r.id !== 'sdg-template-row'));
                              const positions = rows.map((r, idx) => ({ id: r.getAttribute('data-id') || null, position: idx + 1 })).filter(p => p.id);
                              if (positions.length) fetch('/faculty/syllabi/' + syllabusId + '/sdgs/reorder', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }, body: JSON.stringify({ positions }) }).catch(()=>{});
                            }
                          } catch (e) {}
                        } catch (e) { console.error('fallbackMoveDown', e); }
                      }
                      </script>
                          <span class="ms-1 d-none d-md-inline">Add SDG</span>
                        </button>
                      </div>
                    </div>
    </th>
                </tr>
            </thead>
            <tbody id="syllabus-sdg-sortable" data-syllabus-id="{{ $default['id'] }}">
              @php
                $sdgsCollection = $default['sdgs'] ?? collect();
                // Accept either a collection of Sdg models with pivot or SyllabusSdg entries
                $sdgsSorted = $sdgsCollection->sortBy(function($s){
                    if (isset($s->pivot)) return $s->pivot->position ?? 0;
                    return $s->sort_order ?? 0;
                })->values();
              @endphp
              @if($sdgsSorted->count())
                @foreach($sdgsSorted as $index => $sdg)
          @php
            $isPivot = isset($sdg->pivot);
            $seqCode = $isPivot ? ($sdg->pivot->code ?? ('SDG' . ($index + 1))) : ($sdg->code ?? ('SDG' . ($index + 1)));
            // rowId is the per-syllabus entry id (pivot id for pivoted relations, or SyllabusSdg id)
            $rowId = $isPivot ? $sdg->pivot->id : ($sdg->id ?? null);
            // master SDG id: for pivoted master sdg it's the master id; for per-syllabus entries try to resolve by title/code
            $sdgMasterId = null;
            if ($isPivot) {
              $sdgMasterId = $sdg->id;
            } else {
              // attempt to find matching master SDG by normalized title or code
              try {
                $needleTitle = mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) ($sdg->title ?? ''))));
                $needleCode = mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) ($sdg->code ?? ''))));
                $found = collect($sdgs ?? [])->first(function($m) use ($needleTitle, $needleCode) {
                  $t = mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) ($m->title ?? ''))));
                  $c = mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) ($m->code ?? ''))));
                  return ($needleTitle && $t === $needleTitle) || ($needleCode && $c === $needleCode);
                });
                if ($found) $sdgMasterId = $found->id;
              } catch (\Throwable $__e) {
                // ignore resolution errors
              }
            }
            @endphp
            <tr data-id="{{ $rowId }}" data-sdg-id="{{ $sdgMasterId }}">
                    <td class="text-center align-middle sdg-code-cell">
                      <div class="cdio-badge">{{ $seqCode }}</div>
                    </td>
                    <td class="sdg-cell">
                      <div class="d-flex align-items-center gap-2">
                        <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;"><i class="bi bi-grip-vertical"></i></span>
                        <div class="flex-grow-1 sdg-box sdg-box-strong">
                          @php $visibleTitle = $isPivot ? ($sdg->pivot->title ?? $sdg->title) : ($sdg->title ?? ''); @endphp
                          <input type="text" name="title[]" class="form-control form-control-sm sdg-title-input fw-semibold" value="{{ $visibleTitle }}" data-original="{{ $visibleTitle }}" style="background: transparent; border: none; padding: 0; margin-bottom: .15rem;" />
                          <textarea name="sdgs[]" class="form-control cis-textarea autosize flex-grow-1 sdg-description-input" data-original="{{ old("sdgs.$index", $isPivot ? $sdg->pivot->description : ($sdg->description ?? '')) }}">{{ old("sdgs.$index", $isPivot ? $sdg->pivot->description : ($sdg->description ?? '')) }}</textarea>
                        </div>
                        <input type="hidden" name="code[]" value="{{ $seqCode }}">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-cdio ms-2" title="Delete SDG" onclick="fallbackDeleteSdg(this)"><i class="bi bi-trash"></i></button>
                      </div>
                    </td>
                  </tr>
                @endforeach
              @endif

              {{-- Hidden template row for JS clone --}}
              <tr id="sdg-template-row" class="d-none">
                <td class="text-center align-middle sdg-code-cell"><div class="cdio-badge">SDG#</div></td>
                <td class="sdg-cell">
                  <div class="d-flex align-items-center gap-2">
                    <span class="drag-handle text-muted" title="Drag to reorder" style="cursor: grab;"><i class="bi bi-grip-vertical"></i></span>
                      <div class="flex-grow-1 sdg-box sdg-box-strong">
                      <input type="text" name="title[]" class="form-control form-control-sm sdg-title-input fw-semibold" value="" data-original="" style="background: transparent; border: none; padding: 0; margin-bottom: .15rem;" />
                      <textarea name="sdgs[]" class="form-control cis-textarea autosize" rows="1" required></textarea>
                    </div>
                    <input type="hidden" name="code[]" value="SDG#">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-cdio ms-2" title="Delete SDG" onclick="fallbackDeleteSdg(this)"><i class="bi bi-trash"></i></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>
    </tbody>
  </table>
</form>

{{-- ➕ Add SDG Modal --}}
<div class="modal fade" id="addSdgModal" tabindex="-1" aria-labelledby="addSdgModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('faculty.syllabi.sdgs.attach', $default['id']) }}">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addSdgModalLabel">Add Sustainable Development Goal</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <label class="form-label">Select SDGs to add</label>
          <div class="sdg-checkbox-list" style="max-height:320px; overflow:auto; border:1px solid #e9ecef; padding:0.5rem; border-radius:6px;">
      @php
        // compute attached information for this syllabus
        // $default['sdgs'] may contain Sdg models (with pivot), arrays, or SyllabusSdg entries
        $attached = collect($default['sdgs'] ?? [])->map(function($s){
          // array shape
          if (is_array($s)) {
            $id = $s['sdg_id'] ?? ($s['id'] ?? null);
            $title = $s['title'] ?? ($s['pivot']['title'] ?? null) ?? null;
            $code = $s['code'] ?? null;
            return ['id' => $id, 'title' => $title, 'code' => $code];
          }
          // object with pivot (master Sdg model)
          if (isset($s->pivot)) {
            return ['id' => $s->id ?? null, 'title' => ($s->pivot->title ?? $s->title) ?? null, 'code' => $s->code ?? null];
          }
          // object that looks like per-syllabus SyllabusSdg entry
          if (isset($s->title) && !isset($s->id)) {
            return ['id' => $s->sdg_id ?? null, 'title' => $s->title ?? null, 'code' => $s->code ?? null];
          }
          // fallback: try to pick sensible fields
          $id = $s->sdg_id ?? $s->id ?? null;
          $title = $s->title ?? null;
          $code = $s->code ?? null;
          return ['id' => $id, 'title' => $title, 'code' => $code];
        })->filter(function($it){ return ($it['id'] ?? $it['title']) != null; });

    // master ids (numeric) — note: attached could be per-syllabus rows (their 'id' is not master id)
    $attachedMasterIds = $attached->pluck('id')->filter()->unique()->values();

    // normalized titles map for case-insensitive matching
    $attachedTitles = $attached->pluck('title')->filter()->map(function($t){ return mb_strtolower(trim(preg_replace('/\s+/', ' ', (string)$t))); })->unique()->values();
    $attachedCodes = $attached->pluck('code')->filter()->map(function($c){ return mb_strtolower(trim(preg_replace('/\s+/', ' ', (string)$c))); })->unique()->values();

    // Try to resolve per-syllabus entries back to master SDG ids using the master $sdgs collection
    try {
      $masterByTitle = collect($sdgs ?? [])->mapWithKeys(function($m){ $k = mb_strtolower(trim(preg_replace('/\s+/', ' ', (string)($m->title ?? '')))); return [$k => $m->id ?? null]; });
      $masterByCode = collect($sdgs ?? [])->mapWithKeys(function($m){ $k = mb_strtolower(trim(preg_replace('/\s+/', ' ', (string)($m->code ?? '')))); return [$k => $m->id ?? null]; });

      $resolvedIds = collect();
      foreach ($attachedTitles as $t) {
        if ($masterByTitle->has($t) && $masterByTitle->get($t)) $resolvedIds->push($masterByTitle->get($t));
      }
      foreach ($attachedCodes as $c) {
        if ($masterByCode->has($c) && $masterByCode->get($c)) $resolvedIds->push($masterByCode->get($c));
      }
      $resolvedIds = $resolvedIds->filter()->unique()->values();
      // merge resolved master ids into attachedMasterIds so checkbox id checks succeed
      $attachedMasterIds = $attachedMasterIds->merge($resolvedIds)->unique()->values();
  } catch (\Throwable $e) {
      // best effort — don't break rendering on unexpected shapes
    }
      @endphp
            @foreach ($sdgs as $sdg)
              <div class="form-check mb-1">
                @php
                  $normTitle = mb_strtolower(trim(preg_replace('/\s+/', ' ', (string)($sdg->title ?? ''))));
                  $normCode = mb_strtolower(trim(preg_replace('/\s+/', ' ', (string)($sdg->code ?? ''))));
                @endphp
                <input name="sdg_ids[]" class="form-check-input sdg-checkbox" type="checkbox" value="{{ $sdg->id }}" id="sdg_check_{{ $sdg->id }}" @if($attachedMasterIds->contains($sdg->id) || $attachedTitles->contains($normTitle) || $attachedCodes->contains($normCode)) checked @endif>
                <label class="form-check-label small" for="sdg_check_{{ $sdg->id }}">{{ $sdg->title }}</label>
              </div>
            @endforeach
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
// Minimal AJAX fallback: prevents navigation to raw JSON and updates DOM when compiled bundle isn't loaded.
document.addEventListener('DOMContentLoaded', function () {
  try {
    const modalForm = document.querySelector('#addSdgModal form');
    if (!modalForm) return;
    modalForm.addEventListener('submit', async function (ev) {
      if (ev.defaultPrevented) return; // compiled handler already took care of it
      ev.preventDefault();
      const action = modalForm.action;
      const tbody = document.getElementById('syllabus-sdg-sortable');
      const syllabusId = tbody && tbody.dataset ? tbody.dataset.syllabusId : null;

      const checked = Array.from(modalForm.querySelectorAll('input[name="sdg_ids[]"]:checked')).map(i => String(i.value));
      const attached = Array.from(tbody.querySelectorAll('tr[data-sdg-id]')).map(r => String(r.getAttribute('data-sdg-id')));
      const toAttach = checked.filter(id => !attached.includes(id));
      const toDetach = attached.filter(id => !checked.includes(id));

      try {
        // Attach
        if (toAttach.length) {
          const res = await fetch(action, {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ sdg_ids: toAttach })
          });
          if (!res.ok) throw new Error(await res.text().catch(() => res.statusText));
          const payload = await res.json().catch(() => ({}));
          const created = Array.isArray(payload.created) ? payload.created : (payload.pivot_id ? [payload] : []);
          const template = document.getElementById('sdg-template-row');
          created.forEach(item => {
            const row = template.cloneNode(true);
            row.id = '';
            row.classList.remove('d-none');
            row.setAttribute('data-id', item.pivot_id || ('new-' + Date.now()));
            if (item.sdg_id) row.setAttribute('data-sdg-id', item.sdg_id);
            const ta = row.querySelector('textarea[name="sdgs[]"]'); if (ta) { ta.value = item.description ?? ''; ta.setAttribute('data-original', ta.value); }
            const titleEl = row.querySelector('.sdg-title'); if (titleEl) titleEl.textContent = item.title || '';
            const titleInput = row.querySelector('input[name="title[]"]'); if (titleInput) { titleInput.value = item.title || ''; titleInput.setAttribute('data-original', titleInput.value || ''); }
            const codeInput = row.querySelector('input[name="code[]"]'); const badge = row.querySelector('.cdio-badge');
            if (badge) {
              if (item.code) { badge.textContent = item.code; if (codeInput) codeInput.value = item.code; }
              else { const existing = Array.from(tbody.children).filter(r => r.id !== 'sdg-template-row' && !r.classList.contains('d-none')).length; const provisional = 'SDG' + (existing + 1); badge.textContent = provisional; if (codeInput) codeInput.value = provisional; }
            }
            tbody.appendChild(row);
            try { if (typeof bindSdgForms === 'function') bindSdgForms(row); } catch (e) {}
            // keep the checkbox in the modal but mark it as checked so user can uncheck to detach later
            try {
              const cb = document.querySelector(`#sdg_check_${item.sdg_id}`);
              if (cb) cb.checked = true;
            } catch (e) {}
            // ensure textarea is visible (autosize)
            try {
              const ta = row.querySelector('textarea.autosize, textarea');
              if (ta) { ta.style.height = 'auto'; ta.style.height = (ta.scrollHeight || 24) + 'px'; }
            } catch (e) {}
          });
        }

        // Detach
        for (const sid of toDetach) {
          try {
            if (!syllabusId) continue;
            const deleteUrl = `/faculty/syllabi/${syllabusId}/sdgs/${sid}`;
            const res2 = await fetch(deleteUrl, { method: 'DELETE', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } });
            if (!res2.ok) continue;
            const json = await res2.json().catch(() => ({}));
            const row = document.querySelector(`#syllabus-sdg-sortable tr[data-sdg-id="${sid}"]`);
            if (row) { try { row.remove(); } catch (e) { row.parentNode && row.parentNode.removeChild(row); } }
            const list = document.querySelector('.sdg-checkbox-list');
            if (list && !list.querySelector(`#sdg_check_${sid}`)) {
              const wrapper = document.createElement('div'); wrapper.className = 'form-check mb-1';
              const input = document.createElement('input'); input.name = 'sdg_ids[]'; input.className = 'form-check-input sdg-checkbox'; input.type = 'checkbox'; input.id = `sdg_check_${sid}`; input.value = sid;
              const label = document.createElement('label'); label.className = 'form-check-label small'; label.htmlFor = input.id; label.textContent = json.title || (`SDG ${sid}`);
              wrapper.appendChild(input); wrapper.appendChild(label); list.appendChild(wrapper);
            } else if (list) {
              const cb = list.querySelector(`#sdg_check_${sid}`); if (cb) cb.checked = false;
            }
          } catch (e) { console.error('detach failed for', sid, e); }
        }

        try { if (window.updateVisibleCodes) window.updateVisibleCodes(); else updateVisibleCodes(); } catch (e) {}
        try { if (window.saveSdgOrder) window.saveSdgOrder(); else if (window.persistSdgOrder) window.persistSdgOrder(); } catch (e) {}
        try { const modalEl = document.getElementById('addSdgModal'); if (window.bootstrap && modalEl) { const inst = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl); inst.hide(); } } catch (e) {}
      } catch (err) {
        console.error('Save SDGs failed', err);
        alert('Failed to save SDG changes');
      }
    });
  } catch (e) { console.error('Add SDG fallback init failed', e); }
});
</script>
