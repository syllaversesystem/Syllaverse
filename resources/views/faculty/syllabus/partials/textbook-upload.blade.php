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
        <button type="button" class="btn btn-outline-secondary btn-sm me-1 textbook-action-upload" data-target="textbook_main_files">
          <i class="bi bi-cloud-upload"></i> Upload file
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm textbook-action-reference" data-type="main">
          <i class="bi bi-journal-plus"></i> Add reference
        </button>
        <small class="text-muted ms-2" style="font-size: 0.75rem;">PDF/Word • 300MB max</small>
        <div class="mt-2" id="textbook_main_progress" style="display:none;">
          <div class="progress" style="height: 6px;">
            <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
          </div>
          <small class="text-muted d-block mt-1" id="textbook_main_progress_label">Uploading…</small>
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
        <button type="button" class="btn btn-outline-secondary btn-sm me-1 textbook-action-upload" data-target="textbook_other_files">
          <i class="bi bi-cloud-upload"></i> Upload file
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm textbook-action-reference" data-type="other">
          <i class="bi bi-journal-plus"></i> Add reference
        </button>
        <small class="text-muted ms-2" style="font-size: 0.75rem;">PDF/Word • 300MB max</small>
        <div class="mt-2" id="textbook_other_progress" style="display:none;">
          <div class="progress" style="height: 6px;">
            <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
          </div>
          <small class="text-muted d-block mt-1" id="textbook_other_progress_label">Uploading…</small>
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

{{-- ░░░ START: Add Reference Modal ░░░ --}}
<div class="modal fade sv-ref-modal" id="addReferenceModal" tabindex="-1" aria-labelledby="addReferenceModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      @csrf

      <style>
        /* Scoped styles for Add Reference modal (inspired by ILO modal) */
        #addReferenceModal { z-index: 10010 !important; }
        #addReferenceModal .modal-dialog, #addReferenceModal .modal-content { position: relative; z-index: 10011; }
        #addReferenceModal .modal-header { padding: .85rem 1rem; border-bottom: 1px solid #E3E3E3; background: #fff; }
        #addReferenceModal .modal-title { font-weight: 600; font-size: 1rem; display: inline-flex; align-items: center; gap: .5rem; }
        #addReferenceModal .modal-content { border-radius: 16px; border: 1px solid #E3E3E3; background: #fff; box-shadow: 0 10px 30px rgba(0,0,0,.08), 0 2px 12px rgba(0,0,0,.06); overflow: hidden; }
        #addReferenceModal .form-label { font-weight: 600; }
        #addReferenceModal textarea { min-height: 110px; resize: vertical; }
        #addReferenceModal .btn-light, #addReferenceModal .btn-primary { border: none; box-shadow: none; }
        #addReferenceModal .btn-light { background: #fff; color: #000; }
        #addReferenceModal .btn-light:hover{ background: linear-gradient(135deg, rgba(220,220,220,.88), rgba(240,240,240,.46)); }
        #addReferenceModal .btn-primary { background: #CB3737; }
        #addReferenceModal .btn-primary:hover{ filter: brightness(.95); }
        .modal-backdrop { z-index: 10008 !important; }
      </style>

      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2" id="addReferenceModalLabel">
          <i data-feather="book"></i>
          <span>Add Reference</span>
        </h5>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label for="addReferenceText" class="form-label">Reference (citation text)</label>
          <textarea id="addReferenceText" class="form-control" placeholder="e.g., Author, Title, Publisher, Year, DOI/URL..."></textarea>
          <input type="hidden" id="addReferenceType" value="main">
        </div>
        <div class="text-muted" style="font-size: .85rem;">Tip: Paste a full citation or brief reference text. You can rename it later.</div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
          <i data-feather="x"></i> Cancel
        </button>
        <button type="button" class="btn btn-primary" id="confirmAddReference">
          <i data-feather="plus"></i> Save Reference
        </button>
      </div>
    </div>
  </div>
</div>
{{-- ░░░ END: Add Reference Modal ░░░ --}}

<script>
  // Relocate Add Reference modal under <body> to avoid stacking context issues
  document.addEventListener('DOMContentLoaded', function(){
    try {
      const modal = document.getElementById('addReferenceModal');
      if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
        modal.style.zIndex = '10012';
        const dlg = modal.querySelector('.modal-dialog');
        if (dlg) dlg.style.zIndex = '10013';
      }
    } catch (e) { console.error('Reference modal relocation failed', e); }
  });
</script>


