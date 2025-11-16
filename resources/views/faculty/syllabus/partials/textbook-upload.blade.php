{{-- 
------------------------------------------------
* File: resources/views/faculty/syllabus/partials/textbook-upload.blade.php
* Description: Modern textbook upload interface with drag-and-drop UI – Syllaverse
------------------------------------------------ 
--}}

<style>
  .textbook-upload-area {
    cursor: pointer;
    border: 1px dashed #ced4da;
    border-radius: 4px;
    padding: 0.5rem 0.75rem;
    text-align: center;
    background: #f8f9fa;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
  }
  
  .textbook-upload-area:hover {
    background: #e9ecef;
    border-color: #adb5bd;
  }
  
  .textbook-upload-area:active {
    transform: scale(0.98);
  }
  
  .textbook-upload-icon {
    font-size: 1.25rem;
    color: #6c757d;
  }
  
  .textbook-file-row {
    transition: background-color 0.15s ease;
  }
  
  .textbook-file-row:hover {
    background-color: #f8f9fa;
  }
  
  .textbook-file-icon {
    font-size: 1.25rem;
    margin-right: 0.5rem;
    vertical-align: middle;
  }
  
  .textbook-file-link {
    color: #0066cc;
    text-decoration: none;
    vertical-align: middle;
    word-break: break-word;
  }
  
  .textbook-file-link:hover {
    text-decoration: underline;
  }
  
  .textbook-edit-btn,
  .textbook-delete-btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
  }
</style>

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
      <th class="align-top text-start cis-label" rowspan="{{ $textbooksMain->count() + 1 }}">Textbook</th>
      <td colspan="3" class="p-2 text-start">
        <div class="textbook-upload-area" onclick="document.getElementById('textbook_main_files').click()">
          <i class="bi bi-cloud-upload textbook-upload-icon"></i>
          <span class="text-dark" style="font-size: 0.875rem;">Click to upload</span>
          <small class="text-muted" style="font-size: 0.75rem;">PDF/Word • 300MB max</small>
        </div>
        <input 
          type="file" 
          id="textbook_main_files"
          multiple
          class="d-none"
          accept=".pdf,.doc,.docx"
        >
      </td>
    </tr>
    @foreach ($textbooksMain as $textbook)
      @php
        $ext = strtolower(pathinfo($textbook->original_name, PATHINFO_EXTENSION));
        $icon = match($ext) {
          'pdf' => 'bi-filetype-pdf text-danger',
          'doc', 'docx' => 'bi-file-earmark-word text-primary',
          default => 'bi-file-earmark text-secondary'
        };
      @endphp
      <tr class="textbook-file-row" data-id="{{ $textbook->id }}" data-type="main">
        <td class="text-center align-middle">{{ $mainCount++ }}</td>
        <td class="align-middle">
          <i class="bi {{ $icon }} textbook-file-icon"></i>
          <a href="{{ Storage::url($textbook->file_path) }}" target="_blank" class="textbook-file-link" title="{{ $textbook->original_name }}">
            {{ $textbook->original_name }}
          </a>
        </td>
        <td class="text-end align-middle">
          <button type="button" class="btn btn-outline-secondary btn-sm textbook-edit-btn edit-textbook-btn me-1" title="Rename">
            <i class="bi bi-pencil"></i>
          </button>
          <button type="button" class="btn btn-outline-danger btn-sm textbook-delete-btn delete-textbook-btn" title="Delete">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>
    @endforeach

    {{-- 📘 OTHER BOOKS SECTION --}}
    @php
      $textbooksOther = $syllabus->textbooks->where('type', 'other')->values();
      $otherCount = 1;
    @endphp
    <tr>
      <th class="align-top text-start cis-label" rowspan="{{ $textbooksOther->count() + 1 }}">Other Books and Articles</th>
      <td colspan="3" class="p-2 text-start">
        <div class="textbook-upload-area" onclick="document.getElementById('textbook_other_files').click()">
          <i class="bi bi-cloud-upload textbook-upload-icon"></i>
          <span class="text-dark" style="font-size: 0.875rem;">Click to upload</span>
          <small class="text-muted" style="font-size: 0.75rem;">PDF/Word • 300MB max</small>
        </div>
        <input 
          type="file" 
          id="textbook_other_files"
          multiple
          class="d-none"
          accept=".pdf,.doc,.docx"
        >
      </td>
    </tr>
    @foreach ($textbooksOther as $textbook)
      @php
        $ext = strtolower(pathinfo($textbook->original_name, PATHINFO_EXTENSION));
        $icon = match($ext) {
          'pdf' => 'bi-filetype-pdf text-danger',
          'doc', 'docx' => 'bi-file-earmark-word text-primary',
          default => 'bi-file-earmark text-secondary'
        };
      @endphp
      <tr class="textbook-file-row" data-id="{{ $textbook->id }}" data-type="other">
        <td class="text-center align-middle">{{ $otherCount++ }}</td>
        <td class="align-middle">
          <i class="bi {{ $icon }} textbook-file-icon"></i>
          <a href="{{ Storage::url($textbook->file_path) }}" target="_blank" class="textbook-file-link" title="{{ $textbook->original_name }}">
            {{ $textbook->original_name }}
          </a>
        </td>
        <td class="text-end align-middle">
          <button type="button" class="btn btn-outline-secondary btn-sm textbook-edit-btn edit-textbook-btn me-1" title="Rename">
            <i class="bi bi-pencil"></i>
          </button>
          <button type="button" class="btn btn-outline-danger btn-sm textbook-delete-btn delete-textbook-btn" title="Delete">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>

<script>
  window.syllabusId = window.syllabusId ?? @json($syllabus->id);
</script>


