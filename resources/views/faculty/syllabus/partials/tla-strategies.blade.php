{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/tla-strategies.blade.php
* Description: Teaching, Learning, and Assessment Strategies — CIS-style narrative block
-------------------------------------------------------------------------------
--}}

<table class="table table-bordered mb-4 cis-table">
  <colgroup>
    <col style="width:16%">
    <col style="width:84%">
  </colgroup>
  <tbody>
    <tr>
      <th class="align-top text-start cis-label">Teaching, Learning, and<br>Assessment Strategies
        <span id="unsaved-tla_strategies" class="unsaved-pill d-none">Unsaved</span>
      </th>
      <td>
  <textarea name="tla_strategies" class="cis-textarea cis-field autosize" data-original="{{ old('tla_strategies', $local?->tla_strategies ?? '') }}" placeholder="Enter teaching, learning, and assessment strategies">{{ old('tla_strategies', $local?->tla_strategies ?? '') }}</textarea>
      </td>
    </tr>
  </tbody>
</table>

