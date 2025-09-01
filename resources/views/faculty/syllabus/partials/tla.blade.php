{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/tla.blade.php
* Description: Editable table for TLA (Topics & Delivery) with DB-aware delete and modal-based ILO/SO mapping – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-07-28] Added hidden TLA ID field and data-id on delete button for DB deletion support.
[2025-07-29] Integrated modal-based many-to-many ILO and SO mapping.
[2025-07-29] Added display spans for mapped ILO/SO codes under each button.
[2025-07-29] Show mapped ILO/SO codes in table on initial Blade render.
[2025-07-30] Added "Generate TLA Plan (AI)" button to trigger Gemini-based generation.
-------------------------------------------------------------------------------
--}}

{{-- ░░░ START: TLA Table Section ░░░ --}}
<table id="tlaTable" class="table table-bordered mb-4" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.4;">
  <style>
    .cis-input { font-weight: 400; font-size: 0.93rem; line-height: 1.15; font-family: inherit; }
  </style>
  <thead class="table-light text-center align-middle">
    <tr>
      <th style="width: 5%;">Ch.</th>
      <th style="width: 30%;">Topics / Reading List</th>
      <th style="width: 7%;">Wks</th>
      <th style="width: 25%;">Topic Outcomes</th>
      <th style="width: 8%;">ILO</th>
      <th style="width: 8%;">SO</th>
      <th style="width: 12%;">Delivery Method</th>
      <th style="width: 5%;">Action</th>
    </tr>
  </thead>
  <tbody>
    @php
      $tlaRows = old('tla') ?? ($syllabus->tla ?? []);
      if ($tlaRows instanceof \Illuminate\Support\Collection) {
        $tlaRows = $tlaRows->toArray();
      }
      if (empty($tlaRows)) {
        $tlaRows[] = ['id' => null, 'ch' => '', 'topic' => '', 'wks' => '', 'outcomes' => '', 'ilo' => '', 'so' => '', 'delivery' => '', 'ilos' => [], 'sos' => []];
      }
    @endphp

    @foreach ($tlaRows as $index => $row)
    @php
      $mapped_ilo_codes = isset($row['ilos']) && is_iterable($row['ilos']) ? collect($row['ilos'])->pluck('code')->toArray() : [];
      $mapped_so_codes = isset($row['sos']) && is_iterable($row['sos']) ? collect($row['sos'])->pluck('code')->toArray() : [];
    @endphp
    <tr>
      {{-- Hidden TLA ID field --}}
      <input type="hidden" name="tla[{{ $index }}][id]" value="{{ $row['id'] ?? '' }}" class="tla-id-field">

      <td><input type="text" name="tla[{{ $index }}][ch]" class="form-control border-0 bg-transparent text-center" value="{{ $row['ch'] ?? '' }}" required></td>
      <td><input type="text" name="tla[{{ $index }}][topic]" class="form-control border-0 bg-transparent" value="{{ $row['topic'] ?? '' }}" required></td>
      <td><input type="text" name="tla[{{ $index }}][wks]" class="form-control border-0 bg-transparent text-center" value="{{ $row['wks'] ?? '' }}" required></td>
      <td><input type="text" name="tla[{{ $index }}][outcomes]" class="form-control border-0 bg-transparent" value="{{ $row['outcomes'] ?? '' }}" required></td>
      <td class="text-center align-middle">
        <button type="button" class="btn btn-sm btn-outline-secondary map-ilo-btn" data-index="{{ $index }}" data-tlaid="{{ $row['id'] ?? '' }}">
          Map ILO
        </button>
        <br>
        <small class="ilo-mapped-codes text-muted">{{ implode(', ', $mapped_ilo_codes) }}</small>
      </td>
      <td class="text-center align-middle">
        <button type="button" class="btn btn-sm btn-outline-secondary map-so-btn" data-index="{{ $index }}" data-tlaid="{{ $row['id'] ?? '' }}">
          Map SO
        </button>
        <br>
        <small class="so-mapped-codes text-muted">{{ implode(', ', $mapped_so_codes) }}</small>
      </td>
      <td><input type="text" name="tla[{{ $index }}][delivery]" class="form-control border-0 bg-transparent" value="{{ $row['delivery'] ?? '' }}" required></td>
      <td class="text-center align-middle">
        <button type="button" class="btn btn-sm btn-outline-danger remove-tla-row" data-id="{{ $row['id'] ?? '' }}">
          <i class="bi bi-trash"></i>
        </button>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

<div class="mb-4 d-flex gap-2">
  <button type="button" class="btn btn-sm btn-outline-primary" id="add-tla-row">
    <i class="bi bi-plus-circle"></i> Add Row
  </button>

  <button type="button" class="btn btn-sm btn-outline-danger ms-auto" id="generate-tla-ai">
    <i class="bi bi-stars"></i> Generate TLA Plan (AI)
  </button>
</div>

{{-- ░░░ END: TLA Table Section ░░░ --}}

{{-- ░░░ START: ILO/SO Mapping Modals ░░░ --}}
@include('faculty.syllabus.modals.map-ilo')
@include('faculty.syllabus.modals.map-so')
@vite([
  'resources/js/faculty/syllabus-tla-mapping.js',
  'resources/js/faculty/syllabus-tla-ai.js',
])
{{-- ░░░ END: ILO/SO Mapping Modals ░░░ --}}
