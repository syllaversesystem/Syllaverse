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

{{-- ░░░ START: General Academic Information Card (content-only) ░░░ --}}
<div class="card border-0 shadow-sm p-4">

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
</div>
{{-- ░░░ END: General Academic Information Card ░░░ --}}
