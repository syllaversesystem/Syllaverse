{{--
  File cleared: resources/views/faculty/syllabus/partials/tla.blade.php
  Reason: content removed per request; file retained as an explicit placeholder so templates
  including this partial won't error. Restore original UI here when needed.
--}}

<!-- Toolbar with Generate/Distribute actions removed per request -->

<div class="tla-partial">
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
  </style>
  <table class="table table-bordered mb-4 cis-table" id="tlaTable" style="width: 100%; margin: 0;">
    <colgroup>
      <col style="width:4%">
      <col style="width:38%">
      <col style="width:6%">
      <col style="width:32%">
      <col style="width:6%">
      <col style="width:6%">
      <col style="width:8%">
    </colgroup>
    <thead>
      <tr>
        <th colspan="7" class="text-start">
          <div class="d-flex justify-content-between align-items-center">
            <span>Teaching, Learning, and Assessment (TLA) Activities</span>
            <span class="tla-header-actions d-inline-flex gap-1" style="white-space:nowrap;">
              <button type="button" class="btn btn-sm" id="tla-add-row" title="Add TLA Row" aria-label="Add TLA Row" style="background:transparent;">
                <i data-feather="plus"></i>
                <span class="visually-hidden">Add TLA Row</span>
              </button>
              <button type="button" class="btn btn-sm" id="tla-remove-row" title="Remove Last TLA Row" aria-label="Remove Last TLA Row" style="background:transparent;">
                <i data-feather="minus"></i>
                <span class="visually-hidden">Remove Last TLA Row</span>
              </button>
            </span>
          </div>
        </th>
      </tr>
      <tr class="table-light align-middle">
        <th class="text-center" style="width: 4%;">Ch.</th>
        <th class="text-start" style="width: 38%;">Topics / Reading List</th>
        <th class="text-center" style="width: 6%;">Wks.</th>
        <th class="text-center" style="width: 32%;">Topic Outcomes</th>
        <th class="text-center" style="width: 6%;">ILO</th>
        <th class="text-center" style="width: 6%;">SO</th>
        <th class="text-center" style="width: 8%;">Delivery Method</th>
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
        {{-- Render one empty template row when no saved rows exist --}}
  <tr class="text-center align-middle">
          <td>
            <input name="tla[][ch]" form="syllabusForm" class="form-control cis-input text-center" value="" placeholder="-">
          </td>
          <td class="text-start">
            <textarea name="tla[][topic]" form="syllabusForm" class="form-control cis-textarea autosize cis-field" rows="2" placeholder="-"></textarea>
          </td>
          <td>
            <input name="tla[][wks]" form="syllabusForm" class="form-control cis-input text-center" value="" placeholder="-">
          </td>
          <td class="text-start">
            <textarea name="tla[][outcomes]" form="syllabusForm" class="form-control cis-textarea autosize cis-field" rows="2" placeholder="-"></textarea>
          </td>
          <td>
            <input name="tla[][ilo]" form="syllabusForm" class="form-control cis-input text-center" value="" placeholder="-">
          </td>
          <td>
            <input name="tla[][so]" form="syllabusForm" class="form-control cis-input text-center" value="" placeholder="-">
          </td>
          <td>
            <input name="tla[][delivery]" form="syllabusForm" class="form-control cis-input" value="" placeholder="-">
          </td>
          {{-- hidden id & position for template row so client-side clones include them --}}
          <input type="hidden" class="tla-id-field" name="tla[][id]" value="">
          <input type="hidden" class="tla-position-field" name="tla[][position]" value="0">
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
            <td>
              <input name="tla[{{ $i }}][ch]" form="syllabusForm" class="form-control cis-input text-center" value="{{ $ch }}" placeholder="-">
            </td>
            <td class="text-start">
              <textarea name="tla[{{ $i }}][topic]" form="syllabusForm" class="form-control cis-textarea autosize cis-field" rows="2" placeholder="-">{{ $topic }}</textarea>
            </td>
            <td>
              <input name="tla[{{ $i }}][wks]" form="syllabusForm" class="form-control cis-input text-center" value="{{ $wks }}" placeholder="-">
            </td>
            <td class="text-start">
              <textarea name="tla[{{ $i }}][outcomes]" form="syllabusForm" class="form-control cis-textarea autosize cis-field" rows="2" placeholder="-">{{ $outcomes }}</textarea>
            </td>
            <td>
              <input name="tla[{{ $i }}][ilo]" form="syllabusForm" class="form-control cis-input text-center" value="{{ $ilo }}" placeholder="-">
            </td>
            <td>
              <input name="tla[{{ $i }}][so]" form="syllabusForm" class="form-control cis-input text-center" value="{{ $so }}" placeholder="-">
            </td>
            <td>
              <input name="tla[{{ $i }}][delivery]" form="syllabusForm" class="form-control cis-input" value="{{ $delivery }}" placeholder="-">
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
