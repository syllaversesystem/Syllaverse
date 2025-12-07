{{--
  File cleared: resources/views/faculty/syllabus/partials/tla.blade.php
  Reason: content removed per request; file retained as an explicit placeholder so templates
  including this partial won't error. Restore original UI here when needed.
--}}

<!-- Toolbar with Generate/Distribute actions removed per request -->

<div class="tla-partial sv-partial" data-partial-key="tla">
  <style>
    /* Harmonize TLA table typography with other CIS partials */
    .tla-partial #tlaTable,
    .tla-partial #tlaTable th,
    .tla-partial #tlaTable td,
    .tla-partial #tlaTable textarea,
    .tla-partial #tlaTable input {
      font-family: Georgia, serif !important;
      font-size: 13px !important;
      line-height: 1.4 !important;
      color: #111 !important;
    }
    .tla-partial #tlaTable th,
    .tla-partial #tlaTable td { padding: 0.25rem 0.5rem !important; vertical-align: middle; }
    .tla-partial #tlaTable textarea.cis-textarea { border:0 !important; background:transparent !important; resize:vertical; }
    /* Remove previous yellow focus effect; keep neutral focus */
    .tla-partial #tlaTable textarea.cis-textarea.cis-field:focus { background:transparent !important; outline:none !important; box-shadow:none !important; }
    .tla-partial #tlaTable input.cis-input { height:28px; }
    /* Remove padding from specific column inputs for compact layout */
    .tla-partial #tlaTable td.tla-ch input.form-control,
    .tla-partial #tlaTable td.tla-wks input.form-control,
    .tla-partial #tlaTable td.tla-ilo input.form-control,
    .tla-partial #tlaTable td.tla-so input.form-control,
    .tla-partial #tlaTable td.tla-delivery input.form-control,
    .tla-partial #tlaTable td.tla-delivery textarea.form-control {
      padding: 0 !important;
    }
    /* Icon-only header buttons styled like ILO/SDG circular actions */
    .tla-header-actions .btn {
      position: relative; padding: 0 !important;
      width: 2.2rem; height: 2.2rem; min-width: 2.2rem; min-height: 2.2rem;
      border-radius: 50% !important;
      display: inline-flex; align-items: center; justify-content: center;
      background: var(--sv-card-bg, #fff);
      border: none; box-shadow: none; color: #000;
      transition: all 0.2s ease-in-out; line-height: 0;
    }
    .tla-header-actions .btn svg { width: 1.05rem; height: 1.05rem; display:block; margin:0; vertical-align:middle; stroke: currentColor; }
    .tla-header-actions .btn:hover, .tla-header-actions .btn:focus {
      background: linear-gradient(135deg, rgba(255,240,235,.88), rgba(255,255,255,.46));
      backdrop-filter: blur(7px); -webkit-backdrop-filter: blur(7px);
      box-shadow: 0 4px 10px rgba(204,55,55,.12);
      color: #CB3737;
    }
    .tla-header-actions .btn:active { transform: scale(.97); filter: brightness(.98); }
    /* Make Actions column fit its button (minimal width) */
    .tla-partial #tlaTable td.tla-actions,
    .tla-partial #tlaTable th.tla-actions {
      width: 1%;
      white-space: nowrap;
    }
    .tla-partial #tlaTable td.tla-actions { padding-left: .25rem !important; padding-right: .25rem !important; }
  </style>
  <table class="table table-bordered mb-4 cis-table" id="tlaTable" style="width: 100%; margin: 0;">
    <colgroup>
      <col style="width:4%">
      <col style="width:32%">
      <col style="width:6%">
      <col style="width:26%">
      <col style="width:6%">
      <col style="width:6%">
      <col style="width:12%">
      <col>
    </colgroup>
    <thead>
      <tr>
        <th colspan="8" class="text-center">
          <div class="d-flex justify-content-between align-items-center">
            <span></span>
            <span>Teaching, Learning, and Assessment (TLA) Activities</span>
            <span class="tla-header-actions d-inline-flex gap-1" style="white-space:nowrap;">
              <button type="button" class="btn btn-sm" id="add-tla-row" title="Add TLA Row" aria-label="Add TLA Row" style="background:transparent;">
                <i data-feather="plus"></i>
                <span class="visually-hidden">Add TLA Row</span>
              </button>
            </span>
          </div>
        </th>
      </tr>
      <tr class="table-light align-middle">
        <th class="text-center" style="width: 4%;">Ch.</th>
        <th class="text-start" style="width: 32%;">Topics / Reading List</th>
        <th class="text-center" style="width: 6%;">Wks.</th>
        <th class="text-center" style="width: 26%;">Topic Outcomes</th>
        <th class="text-center" style="width: 6%;">ILO</th>
        <th class="text-center" style="width: 6%;">SO</th>
        <th class="text-center" style="width: 12%;">Delivery Method</th>
        <th class="text-center tla-actions"></th>
      </tr>
    </thead>
    <tbody>
      @php
        // Ensure $syllabus is available; fallback to old input or an empty collection
        $tlaRows = isset($syllabus) ? $syllabus->tla : collect([]);
        if (old('tla')) {
          $tlaRows = collect(old('tla'));
        }
      @endphp

      @if($tlaRows->count() === 0)
        <tr id="tla-placeholder">
          <td colspan="8" class="text-center text-muted py-4">
            <p class="mb-2">No TLA activities added yet.</p>
            <p class="mb-0"><small>Click the <strong>+</strong> button above to add a TLA row.</small></p>
          </td>
        </tr>
      @else
        @foreach($tlaRows as $i => $r)
          @php
            // Normalize model vs array
            $ch = is_object($r) ? $r->ch : ($r['ch'] ?? '');
            $topic = is_object($r) ? $r->topic : ($r['topic'] ?? '');
            $wks = is_object($r) ? $r->wks : ($r['wks'] ?? '');
            $outcomes = is_object($r) ? $r->outcomes : ($r['outcomes'] ?? '');
            $ilo = is_object($r) ? $r->ilo : ($r['ilo'] ?? '');
            $so = is_object($r) ? $r->so : ($r['so'] ?? '');
            $delivery = is_object($r) ? $r->delivery : ($r['delivery'] ?? '');
            $id = is_object($r) ? ($r->id ?? '') : ($r['id'] ?? '');
          @endphp
          <tr class="text-center align-middle" data-tla-id="{{ $id }}">
            <td class="tla-ch">
              <input name="tla[{{ $i }}][ch]" form="syllabusForm" class="form-control cis-input text-center" value="{{ $ch }}" placeholder="-">
            </td>
            <td class="tla-topic text-start">
              <textarea name="tla[{{ $i }}][topic]" form="syllabusForm" class="form-control cis-textarea autosize cis-field" rows="2" placeholder="-">{{ $topic }}</textarea>
            </td>
            <td class="tla-wks">
              <input name="tla[{{ $i }}][wks]" form="syllabusForm" class="form-control cis-input text-center" value="{{ $wks }}" placeholder="-">
            </td>
            <td class="tla-outcomes text-start">
              <textarea name="tla[{{ $i }}][outcomes]" form="syllabusForm" class="form-control cis-textarea autosize cis-field" rows="2" placeholder="-">{{ $outcomes }}</textarea>
            </td>
            <td class="tla-ilo">
              <input name="tla[{{ $i }}][ilo]" form="syllabusForm" class="form-control cis-input text-center" value="{{ $ilo }}" placeholder="-">
            </td>
            <td class="tla-so">
              <input name="tla[{{ $i }}][so]" form="syllabusForm" class="form-control cis-input text-center" value="{{ $so }}" placeholder="-">
            </td>
            <td class="tla-delivery">
              <textarea name="tla[{{ $i }}][delivery]" form="syllabusForm" class="form-control cis-textarea autosize cis-field" rows="1" placeholder="-">{{ $delivery }}</textarea>
            </td>
            <td class="tla-actions text-center">
              <button type="button" class="btn btn-sm btn-outline-danger remove-tla-row" data-id="{{ $id }}" title="Delete Row">
                <i class="bi bi-trash"></i>
              </button>
            </td>
            {{-- Hidden id & position fields so JS can detect existing DB row IDs when using server-backed add/delete --}}
            <input type="hidden" class="tla-id-field" name="tla[{{ $i }}][id]" value="{{ $id }}">
            <input type="hidden" class="tla-position-field" name="tla[{{ $i }}][position]" value="{{ $position ?? $i }}">
          </tr>
        @endforeach
      @endif
    </tbody>
  </table>
</div>

<!-- Section divider moved to assessment-mapping partial -->

{{-- ░░░ START: Delete TLA Row Modal ░░░ --}}
<div class="modal fade sv-tla-modal" id="deleteTlaModal" tabindex="-1" aria-labelledby="deleteTlaModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content">
      {{-- ░░░ START: Local styles (scoped to this modal) ░░░ --}}
      <style>
        /* Style synced with ILO load modal (structure & sizing) while retaining red delete button theme */
        #deleteTlaModal {
          --sv-bg:   #FAFAFA;
          --sv-bdr:  #E3E3E3;
          --sv-danger:#CB3737;
          z-index: 10010 !important;
        }
        #deleteTlaModal .modal-dialog { max-width: 520px; }
        #deleteTlaModal .modal-dialog, #deleteTlaModal .modal-content { position: relative; z-index: 10011; }
        .modal-backdrop.show { z-index: 10009 !important; }
        #deleteTlaModal .modal-content {
          border-radius: 16px;
          border: 1px solid var(--sv-bdr);
          background: #fff;
          box-shadow: 0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06);
          overflow: hidden;
          display: flex; flex-direction: column; max-height: 85vh;
        }
        #deleteTlaModal .modal-header { padding: .85rem 1rem; border-bottom: 1px solid var(--sv-bdr); background:#fff; }
        #deleteTlaModal .modal-title { font-weight:600; font-size:1rem; display:inline-flex; align-items:center; gap:.5rem; color: var(--sv-danger); }
        #deleteTlaModal .modal-title i, #deleteTlaModal .modal-title svg { width:1.05rem; height:1.05rem; }
        #deleteTlaModal .modal-body { flex:1 1 auto; padding:1rem; overflow-y:auto; }
        #deleteTlaModal .alert-danger { border-radius:12px; padding:.75rem 1rem; font-size:.875rem; background:#FFE5E5; border:1px solid rgba(203,55,55,.35); color:#7f1d1d; }
        #deleteTlaModal .alert-danger i, #deleteTlaModal .alert-danger svg { width:1.05rem; height:1.05rem; }
        /* Delete button retains original red theme & hover effects */
        #deleteTlaModal .btn-danger {
          background:#fff; border:none; color: var(--sv-danger);
          transition: all .2s ease-in-out; box-shadow:none; display:inline-flex; align-items:center; gap:.5rem;
          padding:.5rem 1rem; border-radius:.375rem; font-size:.95rem;
        }
        #deleteTlaModal .btn-danger i, #deleteTlaModal .btn-danger svg { width:1.05rem; height:1.05rem; color: var(--sv-danger); stroke: var(--sv-danger); }
        #deleteTlaModal .btn-danger:hover, #deleteTlaModal .btn-danger:focus {
          background: linear-gradient(135deg, rgba(255,235,235,.88), rgba(255,245,245,.46));
          backdrop-filter: blur(7px); -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(203,55,55,.15);
          color: var(--sv-danger);
        }
        #deleteTlaModal .btn-danger:active { transform: scale(.97); filter: brightness(.98); }
        /* Cancel button neutral like ILO modal */
        #deleteTlaModal .btn-light {
          background:#fff; border:none; color:#000; transition: all .2s ease-in-out; box-shadow:none;
          display:inline-flex; align-items:center; gap:.5rem; padding:.5rem 1rem; border-radius:.375rem; font-size:.95rem;
        }
        #deleteTlaModal .btn-light i, #deleteTlaModal .btn-light svg { width:1.05rem; height:1.05rem; stroke:#000; }
        #deleteTlaModal .btn-light:hover, #deleteTlaModal .btn-light:focus {
          background: linear-gradient(135deg, rgba(220,220,220,.88), rgba(240,240,240,.46));
          backdrop-filter: blur(7px); -webkit-backdrop-filter: blur(7px);
          box-shadow: 0 4px 10px rgba(108,117,125,.12); color:#495057;
        }
        #deleteTlaModal .btn-light:active { background: linear-gradient(135deg, rgba(240,242,245,.98), rgba(255,255,255,.62)); box-shadow:0 1px 8px rgba(108,117,125,.16); }
      </style>
      {{-- ░░░ END: Local styles ░░░ --}}

      {{-- ░░░ START: Header ░░░ --}}
      <div class="modal-header">
        <h5 class="modal-title fw-semibold d-flex align-items-center gap-2" id="deleteTlaModalLabel">
          <i data-feather="trash-2"></i> Confirm Delete
        </h5>
      </div>
      {{-- ░░░ END: Header ░░░ --}}

      {{-- ░░░ START: Body ░░░ --}}
      <div class="modal-body">
        <div class="alert alert-danger d-flex align-items-start gap-3 mb-0" role="alert">
          <i data-feather="alert-triangle" class="flex-shrink-0 mt-1" style="width: 1.2rem; height: 1.2rem;"></i>
          <div>
            <div class="fw-semibold mb-1">Permanent Deletion Warning</div>
            <p class="mb-0 small">This action will permanently delete this TLA row and cannot be undone.</p>
          </div>
        </div>
      </div>
      {{-- ░░░ END: Body ░░░ --}}

      {{-- ░░░ START: Footer ░░░ --}}
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
          <i data-feather="x"></i> Cancel
        </button>
        <button type="button" class="btn btn-danger" id="confirmDeleteTla">
          <i data-feather="trash-2"></i> Delete
        </button>
      </div>
      {{-- ░░░ END: Footer ░░░ --}}
    </div>
  </div>
</div>
{{-- ░░░ END: Delete TLA Row Modal ░░░ --}}

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Relocate delete modal under body to avoid parent stacking contexts clipping it
  try {
    const delModal = document.getElementById('deleteTlaModal');
    if (delModal && delModal.parentElement !== document.body) {
      document.body.appendChild(delModal);
    }
  } catch(e) { console.warn('TLA delete modal relocation failed', e); }
  // Expose TLA save function globally for toolbar save button
  window.saveTla = async function() {
    const tlaBody = document.querySelector('#tlaTable tbody');
    const form = document.querySelector('#syllabusForm');
    if (!tlaBody || !form) return;

    const rows = Array.from(tlaBody.querySelectorAll('tr:not(#tla-placeholder)'));
    
    if (rows.length === 0) {
      console.log('No TLA rows to save');
      return;
    }

    const tlaData = rows.map((row, index) => ({
      ch: row.querySelector('[name*="[ch]"]')?.value ?? '',
      topic: row.querySelector('[name*="[topic]"]')?.value ?? '',
      wks: row.querySelector('[name*="[wks]"]')?.value ?? '',
      outcomes: row.querySelector('[name*="[outcomes]"]')?.value ?? '',
      ilo: row.querySelector('[name*="[ilo]"]')?.value ?? '',
      so: row.querySelector('[name*="[so]"]')?.value ?? '',
      delivery: row.querySelector('[name*="[delivery]"]')?.value ?? '',
      position: index
    }));

    // Get syllabus ID from form action URL
    const syllabusId = form.action.split('/').pop();
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    const response = await fetch(`/faculty/syllabi/${syllabusId}/tla`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ tla: tlaData })
    });

    const result = await response.json();

    if (result.success && result.rows) {
      console.log('TLA Data saved:', tlaData);
      
      // Update the data-id attributes on delete buttons and hidden ID fields
      rows.forEach((row, index) => {
        if (result.rows[index]) {
          const deleteBtn = row.querySelector('.remove-tla-row');
          const idField = row.querySelector('.tla-id-field');
          
          if (deleteBtn) {
            deleteBtn.setAttribute('data-id', result.rows[index].id);
          }
          if (idField) {
            idField.value = result.rows[index].id;
          }
          
          // Also update row data-tla-id if it exists
          row.setAttribute('data-tla-id', result.rows[index].id);
        }
      });
    } else {
      throw new Error(result.message || 'Failed to save TLA rows');
    }
  };
});
</script>
<script>
// Inject realtime TLA context for AI chat without requiring a rebuild
document.addEventListener('DOMContentLoaded', function() {
  try {
    const table = document.getElementById('tlaTable');
    if (!table) return;
    const headerTitle = 'Teaching, Learning, and Assessment (TLA) Activities';
    const columns = 'Columns: Ch. | Topics / Reading List | Wks. | Topic Outcomes | ILO | SO | Delivery Method';
    const rows = Array.from(table.querySelectorAll('tbody tr')).filter(r => r.id !== 'tla-placeholder');
    const lines = [];
    lines.push('PARTIAL_BEGIN:tla');
    lines.push(headerTitle);
    lines.push(columns);
    if (rows.length) {
      rows.forEach((row, index) => {
        const getVal = sel => {
          const el = row.querySelector(sel);
          if (!el) return '-';
          const v = (el.value ?? '').toString().trim();
          if (v) return v;
          const inner = el.querySelector && el.querySelector('input,textarea');
          if (inner && inner.value) return inner.value.toString().trim() || '-';
          const txt = (el.textContent || '').trim();
          return txt || '-';
        };
        const ch = getVal('[name*="[ch]"]');
        const topic = getVal('[name*="[topic]"]');
        const wks = getVal('[name*="[wks]"]');
        const outcomes = getVal('[name*="[outcomes]"]');
        const ilo = getVal('[name*="[ilo]"]');
        const so = getVal('[name*="[so]"]');
        const delivery = getVal('[name*="[delivery]"]');
        lines.push(`ROW:${index+1} | Ch:${ch} | Wks:${wks} | Topic:${topic} | Outcomes:${outcomes} | ILO:${ilo} | SO:${so} | Delivery:${delivery}`);
        lines.push(`FIELDS_ROW:${index+1} | ch=${ch} | wks=${wks} | topic=${topic} | outcomes=${outcomes} | ilo=${ilo} | so=${so} | delivery=${delivery}`);
      });
    } else {
      lines.push('[No TLA rows entered yet – AI may suggest a weekly plan]');
      lines.push('FIELDS_ROW:0 | ch=- | wks=- | topic=- | outcomes=- | ilo=- | so=- | delivery=-');
    }
    lines.push('PARTIAL_END:tla');
    window._svRealtimeContext = lines.join('\n');
  } catch(e) { /* ignore */ }
});
</script>
