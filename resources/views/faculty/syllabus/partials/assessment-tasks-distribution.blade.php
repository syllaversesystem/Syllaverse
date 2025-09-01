{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/assessment-tasks-distribution.blade.php
* Description: Assessment Tasks Distribution — placeholder table (Blade only)
-------------------------------------------------------------------------------
--}}

@php $iloCols = range(1,8); @endphp
<table class="table table-bordered mb-4 cis-table" style="table-layout: fixed;">
  <thead class="table-light">
    <tr>
      <th class="text-start fw-bold" colspan="{{ 6 + count($iloCols) + 4 }}">Assessment Tasks (AT) Distribution</th>
    </tr>
    <tr class="text-center align-middle">
      <th style="width:5%;">Code</th>
      <th style="width:20%;">Assessment Tasks</th>
      <th style="width:5%;">U/R</th>
      <th style="width:5%;">I/R</th>
      <th style="width:5%;">%</th>
      <th style="width:10%;">Week</th>
      @foreach ($iloCols as $c)
        <th style="width:3%;">{{ $c }}</th>
      @endforeach
      <th style="width:3%;">T</th>
      <th style="width:3%;">C</th>
      <th style="width:3%;">P</th>
      <th style="width:3%;">A</th>
    </tr>
  </thead>
    <style>
      .cis-input { font-weight: 400; font-size: 0.93rem; line-height: 1.15; font-family: inherit; }
    </style>
    <tbody>
    <tr class="table-light"><th colspan="{{ 6 + count($iloCols) + 4 }}" class="text-start">LEC — LECTURE</th></tr>
    @for ($i = 1; $i <= 3; $i++)
      <tr>
        <td><input type="text" class="cis-input text-center" placeholder="ME"></td>
        <td><input type="text" class="cis-input" placeholder="Midterm Exam / Final Exam / Quizzes..."></td>
        <td><input type="text" class="cis-input text-center" placeholder="U"></td>
        <td><input type="text" class="cis-input text-center" placeholder="I"></td>
        <td><input type="text" class="cis-input text-center" placeholder="0"></td>
        <td><input type="text" class="cis-input text-center" placeholder="1-2"></td>
        @foreach ($iloCols as $c)
          <td class="text-center"><input type="text" class="cis-input text-center" placeholder=""></td>
        @endforeach
        <td class="text-center"><input type="text" class="cis-input text-center" placeholder=""></td>
        <td class="text-center"><input type="text" class="cis-input text-center" placeholder=""></td>
        <td class="text-center"><input type="text" class="cis-input text-center" placeholder=""></td>
        <td class="text-center"><input type="text" class="cis-input text-center" placeholder=""></td>
      </tr>
    @endfor

    <tr class="table-light"><th colspan="{{ 6 + count($iloCols) + 4 }}" class="text-start">LAB — LABORATORY</th></tr>
    @for ($i = 1; $i <= 2; $i++)
      <tr>
        <td><input type="text" class="cis-input text-center" placeholder="LE"></td>
        <td><input type="text" class="cis-input" placeholder="Laboratory Exercises / Exams..."></td>
        <td><input type="text" class="cis-input text-center" placeholder="R"></td>
        <td><input type="text" class="cis-input text-center" placeholder="D"></td>
        <td><input type="text" class="cis-input text-center" placeholder="0"></td>
        <td><input type="text" class="cis-input text-center" placeholder="1-2"></td>
        @foreach ($iloCols as $c)
          <td class="text-center"><input type="text" class="cis-input text-center" placeholder=""></td>
        @endforeach
        <td class="text-center"><input type="text" class="cis-input text-center" placeholder=""></td>
        <td class="text-center"><input type="text" class="cis-input text-center" placeholder=""></td>
        <td class="text-center"><input type="text" class="cis-input text-center" placeholder=""></td>
        <td class="text-center"><input type="text" class="cis-input text-center" placeholder=""></td>
      </tr>
    @endfor

    <tr class="table-light">
      <th colspan="{{ 5 }}" class="text-end">Total</th>
      <th>100%</th>
      <th colspan="{{ count($iloCols) + 4 }}"></th>
    </tr>
  </tbody>
</table>
