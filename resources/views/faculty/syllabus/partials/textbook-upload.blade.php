{{-- 
------------------------------------------------
* File: resources/views/faculty/syllabus/partials/textbook-upload.blade.php
* Description: Styled like Mission and Vision section – for textbook file upload (Syllaverse)
------------------------------------------------ 
--}}

<table class="table table-bordered mb-4" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.4;">
  <colgroup>
    <col style="width: 15%;">
    <col style="width: 85%;">
  </colgroup>
  <tbody>
    <tr>
      <th class="align-top text-start">Textbook</th>
      <td>
        <input 
          type="file" 
          name="textbook_file" 
          class="form-control border-0 p-0 bg-transparent @error('textbook_file') is-invalid @enderror"
          style="font-size: 13px;"
          accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt" 
          required
        >
        @error('textbook_file')
          <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
        @enderror
        <small class="text-muted d-block mt-1">Accepted formats: PDF, Word, Excel, CSV, TXT. Max size: 5MB.</small>
      </td>
    </tr>
  </tbody>
</table>
