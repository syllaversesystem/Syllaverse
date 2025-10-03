{{-- 
-------------------------------------------------------------------------------
* File: resources/views/superadmin/master-data/tabs/information.blade.php
* Description: Information tab (content-only) with general academic information fields – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-08-12] Fix – removed nested .tab-pane wrapper; this partial is now content-only for #information.
[2025-08-12] UI  – labels use compact, semibold typography to match Skills & Outcomes.
[2025-08-12] Change – removed "General Academic Information" heading for a cleaner look.
[2025-08-12] Change – Save buttons are now icon-only circular (btn-brand-sm) like Departments' Add button.
-------------------------------------------------------------------------------
--}}

{{-- ░░░ START: General Academic Information (content-only) ░░░ --}}

{{-- ░░░ START: Information Tab Styles ░░░ --}}
<style>
  /* Smaller font size for information form controls */
  #information .form-control {
    font-size: 0.8rem;
    line-height: 1.3;
    min-height: auto;
    resize: none;
    overflow: hidden;
    padding: 0.35rem 0.75rem;
    border-radius: 0.375rem;
  }
  
  /* Auto-resize textarea to content */
  #information .form-control.autosize {
    min-height: 2.5rem;
    max-height: none;
    transition: none; /* Remove transition for immediate sizing */
    box-sizing: border-box;
    overflow-y: hidden;
    word-wrap: break-word;
    white-space: pre-wrap;
  }
  
  /* Compact label styling */
  #information .form-label.small {
    font-size: 0.75rem;
    margin-bottom: 0.25rem;
  }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Auto-resize function specifically for information tab
  function autoResizeTextarea(textarea) {
    if (!textarea) return;
    
    // Store original height
    const originalHeight = textarea.style.height;
    
    // Reset height to get accurate scrollHeight
    textarea.style.height = 'auto';
    textarea.style.height = '1px'; // Force to minimum
    
    // Get the actual content height
    const scrollHeight = textarea.scrollHeight;
    
    // Set the height to fit content
    textarea.style.height = scrollHeight + 'px';
  }
  
  // Initialize all textareas in information tab
  const infoTextareas = document.querySelectorAll('#information textarea.autosize');
  infoTextareas.forEach(textarea => {
    // Initial resize
    autoResizeTextarea(textarea);
    
    // Add event listeners
    textarea.addEventListener('input', () => autoResizeTextarea(textarea));
    textarea.addEventListener('paste', () => {
      setTimeout(() => autoResizeTextarea(textarea), 10);
    });
    textarea.addEventListener('keyup', () => autoResizeTextarea(textarea));
    textarea.addEventListener('change', () => autoResizeTextarea(textarea));
    
    // Force resize after a delay to handle initial content
    setTimeout(() => autoResizeTextarea(textarea), 200);
  });
  
  // Also trigger on tab switch to information tab
  const infoTab = document.querySelector('#information-tab');
  if (infoTab) {
    infoTab.addEventListener('shown.bs.tab', function() {
      setTimeout(() => {
        infoTextareas.forEach(textarea => autoResizeTextarea(textarea));
      }, 100);
    });
  }
});
</script>
{{-- ░░░ END: Information Tab Styles ░░░ --}}

  @php
    $fields = [
      'mission'     => ['label' => 'Mission',                          'ph' => 'State the department’s mission in 2–3 sentences.'],
      'vision'      => ['label' => 'Vision',                           'ph' => 'Describe the long-term vision and aspirations.'],
      'policy'      => ['label' => 'Class Policy',                     'ph' => 'Summarize attendance, participation, and grading rules.'],
      'exams'       => ['label' => 'Missed Examinations',              'ph' => 'Explain procedures for make-up exams or special cases.'],
      'dishonesty'  => ['label' => 'Academic Dishonesty',              'ph' => 'Define violations and consequences (e.g., plagiarism).'],
      'dropping'    => ['label' => 'Dropping Students',                'ph' => 'Outline the drop process and deadlines.'],
  'other' => ['label' => 'Other', 'ph' => 'Consultation, academic advising, and support for students with disabilities.'],
    ];
  @endphp

  @foreach ($fields as $field => $meta)
    <form
      action="{{ route('superadmin.general-info.update', ['section' => $field]) }}"
      method="POST"
      class="mb-4 general-info-form"
    >
      @csrf
      @method('PUT')

      <div class="mb-2">
        {{-- compact, semibold label to mirror Skills & Outcomes tone --}}
        <label for="{{ $field }}" class="form-label small fw-semibold mb-1">
          {{ $meta['label'] }}
        </label>
        <textarea
          class="form-control autosize"
          name="{{ $field }}"
          id="{{ $field }}"
          placeholder="{{ $meta['ph'] }}"
          required
        >{{ $info[$field]->content ?? '' }}</textarea>
      </div>

      <div class="text-end">
        {{-- icon-only, circular save button (same look as Departments’ Add button) --}}
        <button type="submit"
                class="btn-brand-sm"
                title="Save {{ $meta['label'] }}"
                aria-label="Save {{ $meta['label'] }}">
          <i data-feather="save"></i>
        </button>
      </div>

      <hr class="mt-3 mb-0" style="opacity:.1;">
    </form>
  @endforeach

{{-- ░░░ END: General Academic Information ░░░ --}}
