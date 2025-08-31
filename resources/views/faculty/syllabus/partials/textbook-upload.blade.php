{{-- 
------------------------------------------------
* File: resources/views/faculty/syllabus/partials/textbook-upload.blade.php
* Description: CIS-style layout with upload above, numbered rows below, and file titles visible – Syllaverse
------------------------------------------------ 
--}}

<table class="table table-bordered mb-4 cis-table">
  <colgroup>
    <col style="width: 16%">
    <col style="width: 6%">
    <col style="width: 68%">
    <col style="width: 10%">
  </colgroup>
  <tbody>
    {{-- 📚 TEXTBOOK SECTION --}}
    @php
      $textbooksMain = $syllabus->textbooks->where('type', 'main')->values();
      $mainCount = 1;
    @endphp
    <tr>
      <td class="align-middle fw-bold cis-label" rowspan="{{ $textbooksMain->count() + 1 }}">Textbook</td>
      <td colspan="3">
        <input 
          type="file" 
          id="textbook_main_files"
          multiple
          class="form-control form-control-sm"
          accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt"
        >
        <small class="text-muted d-block mt-1">Accepted: PDF, Word, Excel, CSV, TXT. Max 300MB/file.</small>
      </td>
    </tr>
    @foreach ($textbooksMain as $textbook)
      @php
        $ext = strtolower(pathinfo($textbook->original_name, PATHINFO_EXTENSION));
        $icon = match($ext) {
          'pdf' => 'bi-filetype-pdf',
          'doc', 'docx' => 'bi-file-earmark-word',
          'xls', 'xlsx', 'csv' => 'bi-file-earmark-excel',
          'txt' => 'bi-file-earmark-text',
          default => 'bi-file-earmark'
        };
      @endphp
      <tr data-id="{{ $textbook->id }}" data-type="main">
        <td class="text-center">{{ $mainCount++ }}</td>
        <td>
          <div class="file-name-wrap">
            <i class="bi {{ $icon }} file-icon"></i>
            <a href="{{ Storage::url($textbook->file_path) }}" target="_blank" class="textbook-name file-name" title="{{ $textbook->original_name }}">
              {{ $textbook->original_name }}
            </a>
            <button type="button" class="btn btn-link btn-sm p-0 ms-1 edit-textbook-btn edit-inline-btn" title="Rename">
              <i class="bi bi-pencil"></i>
            </button>
          </div>
        </td>
        <td class="text-end cis-actions align-middle">
          <button type="button" class="btn btn-sm btn-outline-danger delete-textbook-btn" title="Delete"><i class="bi bi-trash"></i></button>
        </td>
      </tr>
    @endforeach

    {{-- 📘 OTHER BOOKS SECTION --}}
    @php
      $textbooksOther = $syllabus->textbooks->where('type', 'other')->values();
      $otherCount = 1;
    @endphp
    <tr>
      <td class="align-middle fw-bold cis-label" rowspan="{{ $textbooksOther->count() + 1 }}">Other Books and Articles</td>
      <td colspan="3">
        <input 
          type="file" 
          id="textbook_other_files"
          multiple
          class="form-control form-control-sm"
          accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt"
        >
        <small class="text-muted d-block mt-1">Accepted: PDF, Word, Excel, CSV, TXT. Max 300MB/file.</small>
      </td>
    </tr>
    @foreach ($textbooksOther as $textbook)
      @php
        $ext = strtolower(pathinfo($textbook->original_name, PATHINFO_EXTENSION));
        $icon = match($ext) {
          'pdf' => 'bi-filetype-pdf',
          'doc', 'docx' => 'bi-file-earmark-word',
          'xls', 'xlsx', 'csv' => 'bi-file-earmark-excel',
          'txt' => 'bi-file-earmark-text',
          default => 'bi-file-earmark'
        };
      @endphp
      <tr data-id="{{ $textbook->id }}" data-type="other">
        <td class="text-center">{{ $otherCount++ }}</td>
        <td>
          <div class="file-name-wrap">
            <i class="bi {{ $icon }} file-icon"></i>
            <a href="{{ Storage::url($textbook->file_path) }}" target="_blank" class="textbook-name file-name" title="{{ $textbook->original_name }}">
              {{ $textbook->original_name }}
            </a>
            <button type="button" class="btn btn-link btn-sm p-0 ms-1 edit-textbook-btn edit-inline-btn" title="Rename">
              <i class="bi bi-pencil"></i>
            </button>
          </div>
        </td>
        <td class="text-end cis-actions align-middle">
          <button type="button" class="btn btn-sm btn-outline-danger delete-textbook-btn" title="Delete"><i class="bi bi-trash"></i></button>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>

<script>
  // Avoid redeclaring if already provided by the parent view
  window.syllabusId = window.syllabusId ?? @json($syllabus->id);
  // Optional: expose for modules expecting global
  const syllabusId = window.syllabusId;
  window.syllabusId = syllabusId;
</script>


