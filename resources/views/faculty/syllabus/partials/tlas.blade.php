{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/tlas.blade.php
* Description: Teaching, Learning, and Assessment Strategies (summary textarea)
* Rationale: Separate high-level strategies section placed directly below Course Info.
-------------------------------------------------------------------------------
--}}

@php
  // Access per-syllabus course info record for stored strategies (if any)
  $courseInfo = $syllabus->courseInfo ?? null;
  $tlaStrategiesValue = old('tla_strategies', $courseInfo?->tla_strategies ?? '');
@endphp

<table class="table table-bordered mb-4 cis-table sv-tlas-table" aria-labelledby="tlas-label">
  <colgroup>
    <col style="width:16%">
    <col style="width:84%">
  </colgroup>
  <tbody>
    <tr>
      <th id="tlas-label" class="align-top text-start cis-label">Teaching, Learning, and Assessment Strategies
        <span id="unsaved-tla_strategies" class="unsaved-pill d-none" aria-live="polite">Unsaved</span>
      </th>
      <td style="padding:0.35rem 0.5rem;">
        <textarea
          name="tla_strategies"
          id="tla_strategies"
          class="cis-textarea cis-field autosize"
          data-original="{{ $courseInfo?->tla_strategies ?? '' }}"
          placeholder="-"
          rows="1"
          style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;"
        >{{ $tlaStrategiesValue }}</textarea>
      </td>
    </tr>
  </tbody>
</table>


@push('scripts')
<script>
  // Bind unsaved indicator for TLAS strategies if global helper exists
  document.addEventListener('DOMContentLoaded', function(){
    if (typeof bindUnsavedIndicator === 'function') {
      try { bindUnsavedIndicator('tla_strategies'); } catch(e) {}
    }
  });
</script>
@endpush
