{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/cdio.blade.php
* Description: CDIO Mapping — placeholder CIS-style table (Blade only)
-------------------------------------------------------------------------------
--}}

<table class="table table-bordered mb-4 cis-table">
  <thead class="table-light">
    <tr>
      <th colspan="2" class="text-start fw-bold">CDIO Framework Skills (CDIO)</th>
    </tr>
    <tr class="text-center">
      <th style="width: 25%;">CDIO</th>
      <th style="width: 75%;">CDIO Skills</th>
    </tr>
  </thead>
  <tbody>
    @for ($i = 1; $i <= 3; $i++)
      <tr>
        <td>
          <input type="text" class="cis-input" placeholder="e.g., 2.1, 2.2">
        </td>
        <td>
          <textarea class="cis-field" rows="2" placeholder="Describe how the course maps to the CDIO standard..."></textarea>
        </td>
      </tr>
    @endfor
  </tbody>
</table>
