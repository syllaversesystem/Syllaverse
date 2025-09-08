{{--
  File cleared: resources/views/faculty/syllabus/partials/tla.blade.php
  Reason: content removed per request; file retained as an explicit placeholder so templates
  including this partial won't error. Restore original UI here when needed.
--}}

<div class="tla-partial">
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
        <th colspan="7" class="text-center">Teaching, Learning, and Assessment (TLA) Activities</th>
      </tr>
      <tr class="table-light text-center align-middle">
  <th style="width: 4%;">Ch.</th>
  <th style="width: 38%;">Topics / Reading List</th>
  <th style="width: 6%;">Wks.</th>
  <th style="width: 32%;">Topic Outcomes</th>
  <th style="width: 6%;">ILO</th>
  <th style="width: 6%;">SO</th>
  <th style="width: 8%;">Delivery Method</th>
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
            <input name="tla[][ch]" form="syllabusForm" class="form-control cis-input text-center" value="" placeholder="Ch.">
          </td>
          <td class="text-start">
            <textarea name="tla[][topic]" form="syllabusForm" class="form-control cis-textarea autosize cis-field" rows="2" placeholder="Enter topics / reading list"></textarea>
          </td>
          <td>
            <input name="tla[][wks]" form="syllabusForm" class="form-control cis-input text-center" value="" placeholder="Wks">
          </td>
          <td class="text-start">
            <textarea name="tla[][outcomes]" form="syllabusForm" class="form-control cis-textarea autosize cis-field" rows="2" placeholder="Enter topic outcomes"></textarea>
          </td>
          <td>
            <input name="tla[][ilo]" form="syllabusForm" class="form-control cis-input text-center" value="" placeholder="ILO">
          </td>
          <td>
            <input name="tla[][so]" form="syllabusForm" class="form-control cis-input text-center" value="" placeholder="SO">
          </td>
          <td>
            <input name="tla[][delivery]" form="syllabusForm" class="form-control cis-input" value="" placeholder="Delivery Method">
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
              <input name="tla[{{ $i }}][ch]" form="syllabusForm" class="form-control cis-input text-center" value="{{ $ch }}" placeholder="Ch.">
            </td>
            <td class="text-start">
              <textarea name="tla[{{ $i }}][topic]" form="syllabusForm" class="form-control cis-textarea autosize cis-field" rows="2" placeholder="Enter topics / reading list">{{ $topic }}</textarea>
            </td>
            <td>
              <input name="tla[{{ $i }}][wks]" form="syllabusForm" class="form-control cis-input text-center" value="{{ $wks }}" placeholder="Wks">
            </td>
            <td class="text-start">
              <textarea name="tla[{{ $i }}][outcomes]" form="syllabusForm" class="form-control cis-textarea autosize cis-field" rows="2" placeholder="Enter topic outcomes">{{ $outcomes }}</textarea>
            </td>
            <td>
              <input name="tla[{{ $i }}][ilo]" form="syllabusForm" class="form-control cis-input text-center" value="{{ $ilo }}" placeholder="ILO">
            </td>
            <td>
              <input name="tla[{{ $i }}][so]" form="syllabusForm" class="form-control cis-input text-center" value="{{ $so }}" placeholder="SO">
            </td>
            <td>
              <input name="tla[{{ $i }}][delivery]" form="syllabusForm" class="form-control cis-input" value="{{ $delivery }}" placeholder="Delivery Method">
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
