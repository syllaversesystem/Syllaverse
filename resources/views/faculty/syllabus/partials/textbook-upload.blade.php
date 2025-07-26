{{-- 
------------------------------------------------
* File: resources/views/faculty/syllabus/partials/textbook-upload.blade.php
* Description: Textbook upload section with multi-file support and individual delete – Syllaverse
------------------------------------------------ 
--}}

<table class="table table-bordered mb-4" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.4;">
  <colgroup>
    <col style="width: 15%;">
    <col style="width: 85%;">
  </colgroup>
  <tbody>
    <tr>
      <th class="align-top text-start">Textbooks</th>
      <td>
        {{-- Upload Input --}}
        <input 
          type="file" 
          name="textbook_files[]" 
          id="textbook_files"
          multiple
          class="form-control border-0 p-0 bg-transparent @error('textbook_files') is-invalid @enderror"
          accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt"
        >
        @error('textbook_files')
          <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
        @enderror

        <small class="text-muted d-block mt-1">
          Accepted formats: PDF, Word, Excel, CSV, TXT. Max size per file: 5MB.
        </small>

        {{-- Uploaded File List --}}
        <ul id="uploadedTextbookList" class="mt-3 list-unstyled">
          @foreach ($syllabus->textbooks as $textbook)
            <li class="mb-2 d-flex align-items-center justify-content-between" data-id="{{ $textbook->id }}">
              <a href="{{ Storage::url($textbook->file_path) }}" target="_blank">
                {{ $textbook->original_name }}
              </a>
              <button type="button" class="btn btn-sm btn-outline-danger ms-2 delete-textbook-btn">
                🗑️
              </button>
            </li>
          @endforeach
        </ul>
      </td>
    </tr>
  </tbody>
</table>
