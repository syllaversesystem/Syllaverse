{{-- 
File: resources/views/faculty/syllabus/partials/mission-vision.blade.php  
Description: Mission and Vision with smaller text, table format (CIS-style – Syllaverse) 
--}}

<table class="table table-bordered mb-4 cis-table">
  <colgroup>
    <col style="width: 16%">
    <col style="width: 84%">
  </colgroup>
  <tbody>
    <tr>
      <th class="align-top text-start cis-label">Vision
        <span id="unsaved-vision" class="unsaved-pill d-none" aria-live="polite">Unsaved</span>
      </th>
      <td>
        <textarea
          name="vision"
          class="form-control cis-textarea autosize"
          data-original="{{ old('vision', $default['vision'] ?? $syllabus->missionVision?->vision ?? '') }}"
          placeholder="Enter the official university vision"
          required>{{ old('vision', $default['vision'] ?? $syllabus->missionVision?->vision ?? '') }}</textarea>
      </td>
    </tr>
    <tr>
      <th class="align-top text-start cis-label">Mission
        <span id="unsaved-mission" class="unsaved-pill d-none" aria-live="polite">Unsaved</span>
      </th>
      <td>
        <textarea
          name="mission"
          class="form-control cis-textarea autosize"
          data-original="{{ old('mission', $default['mission'] ?? $syllabus->missionVision?->mission ?? '') }}"
          placeholder="Enter the official university mission"
          required>{{ old('mission', $default['mission'] ?? $syllabus->missionVision?->mission ?? '') }}</textarea>
      </td>
    </tr>
  </tbody>
  {{-- The look aims to match CIS: serif font, compact leading, crisp borders. --}}
</table>
