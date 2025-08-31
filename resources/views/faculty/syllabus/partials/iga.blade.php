{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/iga.blade.php
* Description: Institutional Graduate Attributes (IGA) — placeholder CIS-style table
-------------------------------------------------------------------------------
--}}

<table class="table table-bordered mb-4 cis-table">
  <thead class="table-light">
    <tr>
      <th colspan="2" class="text-start fw-bold">Institutional Graduate Attributes (IGA)</th>
    </tr>
    <tr class="text-center">
      <th style="width: 20%;">Attribute</th>
      <th style="width: 80%;">Description / Notes</th>
    </tr>
  </thead>
  <tbody>
    @for ($i = 1; $i <= 8; $i++)
      <tr>
        <td class="align-middle fw-semibold">IGA {{ $i }}</td>
        <td>
          <textarea class="cis-field" rows="2" placeholder="Describe IGA {{ $i }}..."></textarea>
        </td>
      </tr>
    @endfor
  </tbody>
</table>
