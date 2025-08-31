{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/course-policies.blade.php
* Description: Course Policies — Grading, Class Policy, Academic Honesty, Dropping, Missed Exams (Blade only)
-------------------------------------------------------------------------------
--}}

<table class="table table-bordered mb-4 cis-table">
  <thead class="table-light">
    <tr>
      <th colspan="2" class="text-start fw-bold">Course Policies</th>
    </tr>
  </thead>
  <tbody>
    {{-- A. Grading System --}}
    <tr>
      <th style="width: 20%;" class="align-top text-start">A. Grading System</th>
      <td style="width: 80%;">
        <table class="cis-subtable">
          <thead>
            <tr>
              <th>Rating</th>
              <th class="text-end">Scale</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>Excellent</td><td class="text-end"><input type="text" class="cis-input text-end" placeholder="1.00 / 98-100"></td></tr>
            <tr><td>Superior</td><td class="text-end"><input type="text" class="cis-input text-end" placeholder="1.25 / 94-97"></td></tr>
            <tr><td>Very Good</td><td class="text-end"><input type="text" class="cis-input text-end" placeholder="1.50 / 90-93"></td></tr>
            <tr><td>Good</td><td class="text-end"><input type="text" class="cis-input text-end" placeholder="1.75 / 88-89"></td></tr>
            <tr><td>Meritorious</td><td class="text-end"><input type="text" class="cis-input text-end" placeholder="2.00 / 85-87"></td></tr>
            <tr><td>Very Satisfactory</td><td class="text-end"><input type="text" class="cis-input text-end" placeholder="2.25 / 83-84"></td></tr>
            <tr><td>Satisfactory</td><td class="text-end"><input type="text" class="cis-input text-end" placeholder="2.50 / 80-82"></td></tr>
            <tr><td>Fairly Satisfactory</td><td class="text-end"><input type="text" class="cis-input text-end" placeholder="2.75 / 78-79"></td></tr>
            <tr><td>Passing</td><td class="text-end"><input type="text" class="cis-input text-end" placeholder="3.00 / 75-77"></td></tr>
            <tr><td>Failure</td><td class="text-end"><input type="text" class="cis-input text-end" placeholder="5.00 / Below 70"></td></tr>
            <tr><td>Incomplete</td><td class="text-end"><input type="text" class="cis-input text-end" placeholder="INC"></td></tr>
          </tbody>
        </table>
      </td>
    </tr>

    {{-- B. Class Policy --}}
    <tr>
      <th class="align-top text-start">B. Class Policy</th>
      <td>
        <textarea class="cis-field" rows="3" placeholder="Describe attendance, conduct, and general class policies..."></textarea>
      </td>
    </tr>

    {{-- Missed Examinations --}}
    <tr>
      <th class="align-top text-start">Missed Examinations</th>
      <td>
        <textarea class="cis-field" rows="3" placeholder="Guidelines for special exams and valid reasons..."></textarea>
      </td>
    </tr>

    {{-- Academic Dishonesty --}}
    <tr>
      <th class="align-top text-start">Academic Dishonesty</th>
      <td>
        <textarea class="cis-field" rows="3" placeholder="Cheating and plagiarism policies; penalties and references to institutional code..."></textarea>
      </td>
    </tr>

    {{-- Dropping --}}
    <tr>
      <th class="align-top text-start">Dropping</th>
      <td>
        <textarea class="cis-field" rows="3" placeholder="Official/Unofficial dropping procedures and consequences..."></textarea>
      </td>
    </tr>

    {{-- Other Course Policies and Requirements --}}
    <tr>
      <th class="align-top text-start">Other Course Policies and Requirements</th>
      <td>
        <textarea class="cis-field" rows="4" placeholder="Students with Disabilities/Special Needs (PWD), environment of respect, etc..."></textarea>
      </td>
    </tr>

    {{-- Consultation and Academic Advising --}}
    <tr>
      <th class="align-top text-start">Consultation and Academic Advising</th>
      <td>
        <textarea class="cis-field" rows="3" placeholder="Consultation hours and advising guidelines..."></textarea>
      </td>
    </tr>
  </tbody>
</table>
