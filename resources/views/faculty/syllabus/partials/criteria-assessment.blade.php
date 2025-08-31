{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/criteria-assessment.blade.php
* Description: Criteria for Assessment (percentages) — CIS-style table (Blade only)
-------------------------------------------------------------------------------
--}}

<table class="table table-bordered mb-4 cis-table">
  <thead class="table-light">
    <tr>
      <th colspan="2" class="text-start fw-bold">Criteria for Assessment</th>
    </tr>
    <tr class="text-center">
      <th style="width: 50%;">Lecture</th>
      <th style="width: 50%;">Laboratory</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>
        <table class="cis-subtable">
          <tbody>
            <tr>
              <td>Midterm Exam</td>
              <td class="text-end"><input type="text" class="cis-input text-end" placeholder="0"></td>
            </tr>
            <tr>
              <td>Final Exam</td>
              <td class="text-end"><input type="text" class="cis-input text-end" placeholder="0"></td>
            </tr>
            <tr>
              <td>Quizzes / Chapter Tests</td>
              <td class="text-end"><input type="text" class="cis-input text-end" placeholder="0"></td>
            </tr>
            <tr>
              <td>Assignment / Research Review</td>
              <td class="text-end"><input type="text" class="cis-input text-end" placeholder="0"></td>
            </tr>
            <tr>
              <td>Projects</td>
              <td class="text-end"><input type="text" class="cis-input text-end" placeholder="0"></td>
            </tr>
            <tr>
              <th class="text-end">Total</th>
              <th>100%</th>
            </tr>
          </tbody>
        </table>
      </td>
      <td>
        <table class="cis-subtable">
          <tbody>
            <tr>
              <td>Laboratory Exercises</td>
              <td class="text-end"><input type="text" class="cis-input text-end" placeholder="0"></td>
            </tr>
            <tr>
              <td>Laboratory Exams</td>
              <td class="text-end"><input type="text" class="cis-input text-end" placeholder="0"></td>
            </tr>
            <tr>
              <th class="text-end">Total</th>
              <th>100%</th>
            </tr>
          </tbody>
        </table>
      </td>
    </tr>
  </tbody>
  {{-- Layout-only per CIS style; percentages are simple inputs. --}}
</table>
