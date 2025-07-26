{{-- 
------------------------------------------------
* File: resources/views/faculty/syllabus/partials/tla.blade.php
* Description: Editable table for TLA (Topics & Delivery) with add/remove row support – Syllaverse
------------------------------------------------ 
--}}

<table id="tlaTable" class="table table-bordered mb-4" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.4;">
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
      $tlaRows = old('tla') ?? ($syllabus->tla->toArray() ?? []);
      if (empty($tlaRows)) {
        $tlaRows[] = ['ch' => '', 'topic' => '', 'wks' => '', 'outcomes' => '', 'ilo' => '', 'so' => '', 'delivery' => ''];
      }
    @endphp

    @foreach ($tlaRows as $index => $row)
    <tr>
      <td><input type="text" name="tla[{{ $index }}][ch]" class="form-control border-0 bg-transparent text-center" value="{{ $row['ch'] ?? '' }}" required></td>
      <td><input type="text" name="tla[{{ $index }}][topic]" class="form-control border-0 bg-transparent" value="{{ $row['topic'] ?? '' }}" required></td>
      <td><input type="text" name="tla[{{ $index }}][wks]" class="form-control border-0 bg-transparent text-center" value="{{ $row['wks'] ?? '' }}" required></td>
      <td><input type="text" name="tla[{{ $index }}][outcomes]" class="form-control border-0 bg-transparent" value="{{ $row['outcomes'] ?? '' }}" required></td>
      <td><input type="text" name="tla[{{ $index }}][ilo]" class="form-control border-0 bg-transparent text-center" value="{{ $row['ilo'] ?? '' }}" required></td>
      <td><input type="text" name="tla[{{ $index }}][so]" class="form-control border-0 bg-transparent text-center" value="{{ $row['so'] ?? '' }}" required></td>
      <td><input type="text" name="tla[{{ $index }}][delivery]" class="form-control border-0 bg-transparent" value="{{ $row['delivery'] ?? '' }}" required></td>
      <td class="text-center align-middle">
        @if ($index > 0)
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTlaRow(this)">
          <i class="bi bi-trash"></i>
        </button>
        @endif
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

<div class="mb-4">
  <button type="button" class="btn btn-sm btn-outline-primary" onclick="addTlaRow()">
    <i class="bi bi-plus-circle"></i> Add Row
  </button>
</div>

@push('scripts')
<script>
  let tlaIndex = {{ count($tlaRows) }};

  function addTlaRow() {
    const tbody = document.querySelector('#tlaTable tbody');
    const row = document.createElement('tr');
    row.innerHTML = `
      <td><input type="text" name="tla[${tlaIndex}][ch]" class="form-control border-0 bg-transparent text-center" required></td>
      <td><input type="text" name="tla[${tlaIndex}][topic]" class="form-control border-0 bg-transparent" required></td>
      <td><input type="text" name="tla[${tlaIndex}][wks]" class="form-control border-0 bg-transparent text-center" required></td>
      <td><input type="text" name="tla[${tlaIndex}][outcomes]" class="form-control border-0 bg-transparent" required></td>
      <td><input type="text" name="tla[${tlaIndex}][ilo]" class="form-control border-0 bg-transparent text-center" required></td>
      <td><input type="text" name="tla[${tlaIndex}][so]" class="form-control border-0 bg-transparent text-center" required></td>
      <td><input type="text" name="tla[${tlaIndex}][delivery]" class="form-control border-0 bg-transparent" required></td>
      <td class="text-center align-middle">
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTlaRow(this)">
          <i class="bi bi-trash"></i>
        </button>
      </td>
    `;
    tbody.appendChild(row);
    tlaIndex++;
  }

  function removeTlaRow(button) {
    const row = button.closest('tr');
    const tbody = document.querySelector('#tlaTable tbody');
    if (tbody && tbody.children.length > 1 && row) {
      row.remove();
    }
  }
</script>
@endpush
