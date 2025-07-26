{{-- 
------------------------------------------------
* File: resources/views/faculty/syllabus/partials/textbook-upload.blade.php
* Description: CIS-style layout with upload above, numbered rows below, and file titles visible – Syllaverse
------------------------------------------------ 
--}}

<table class="table table-bordered mb-4" style="font-family: Georgia, serif; font-size: 13px;">
  <colgroup>
    <col style="width: 15%;">
    <col style="width: 5%;">
    <col style="width: 55%;">
    <col style="width: 25%;">
  </colgroup>
  <tbody>
    {{-- 📚 TEXTBOOK SECTION --}}
    @php
      $textbooksMain = $syllabus->textbooks->where('type', 'main')->values();
      $mainCount = 1;
    @endphp
    <tr>
      <td class="align-middle fw-bold" rowspan="{{ $textbooksMain->count() + 1 }}">Textbook</td>
      <td colspan="3">
        <input 
          type="file" 
          id="textbook_main_files"
          multiple
          class="form-control form-control-sm"
          accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt"
        >
        <small class="text-muted d-block mt-1">
          Accepted formats: PDF, Word, Excel, CSV, TXT. Max 5MB per file.
        </small>
      </td>
    </tr>
    @foreach ($textbooksMain as $textbook)
      <tr data-id="{{ $textbook->id }}" data-type="main">
        <td class="text-center">{{ $mainCount++ }}</td>
        <td>
          <a href="{{ Storage::url($textbook->file_path) }}" target="_blank">
            {{ $textbook->original_name }}
          </a>
        </td>
        <td>
          <button type="button" class="btn btn-sm btn-outline-danger float-end delete-textbook-btn">🗑️</button>
        </td>
      </tr>
    @endforeach

    {{-- 📘 OTHER BOOKS SECTION --}}
    @php
      $textbooksOther = $syllabus->textbooks->where('type', 'other')->values();
      $otherCount = 1;
    @endphp
    <tr>
      <td class="align-middle fw-bold" rowspan="{{ $textbooksOther->count() + 1 }}">Other Books and Articles</td>
      <td colspan="3">
        <input 
          type="file" 
          id="textbook_other_files"
          multiple
          class="form-control form-control-sm"
          accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt"
        >
        <small class="text-muted d-block mt-1">
          Accepted formats: PDF, Word, Excel, CSV, TXT. Max 5MB per file.
        </small>
      </td>
    </tr>
    @foreach ($textbooksOther as $textbook)
      <tr data-id="{{ $textbook->id }}" data-type="other">
        <td class="text-center">{{ $otherCount++ }}</td>
        <td>
          <a href="{{ Storage::url($textbook->file_path) }}" target="_blank">
            {{ $textbook->original_name }}
          </a>
        </td>
        <td>
          <button type="button" class="btn btn-sm btn-outline-danger float-end delete-textbook-btn">🗑️</button>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>

<script>
  const syllabusId = @json($syllabus->id);
</script>
