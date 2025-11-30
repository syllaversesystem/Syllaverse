{{-- 
File: resources/views/faculty/syllabus/partials/mission-vision.blade.php  
Description: Institutional Vision & Mission section (refactored for semantic, accessible layout) 
--}}

<table class="table mb-4 cis-table sv-mv-table">
  <colgroup>
    <col style="width: 16%">
    <col style="width: 84%">
  </colgroup>
  <tbody>
    <tr>
      <th class="text-start cis-label sv-mv-label">Vision</th>
      <td>
        <textarea
          id="vision-text"
          name="vision"
          class="cis-textarea cis-field autosize"
          placeholder="-"
          rows="1"
          style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;"
          required>{{ old('vision', $default['vision'] ?? $syllabus->missionVision?->vision ?? '') }}</textarea>
      </td>
    </tr>
    <tr>
      <th class="text-start cis-label sv-mv-label">Mission</th>
      <td>
        <textarea
          id="mission-text"
          name="mission"
          class="cis-textarea cis-field autosize"
          placeholder="-"
          rows="1"
          style="display:block;width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;"
          required>{{ old('mission', $default['mission'] ?? $syllabus->missionVision?->mission ?? '') }}</textarea>
      </td>
    </tr>
  </tbody>
</table>

<!-- Local save button removed; toolbar Save handles mission & vision -->

@push('scripts')
  @vite(['resources/js/faculty/syllabus-mission-vision.js'])
@endpush

@once
@push('styles')
<style>
  /* Mission & Vision section refined layout */
  .sv-mv-table {
    margin-bottom: 0; /* let the table height fit without extra spacing */
    border: none;              /* remove outer container border */
  }
  .sv-mv-label {
    font-weight:600;
    font-size:10pt;
    font-family:'Times New Roman', Times, serif;
    letter-spacing:.4px;
    color:#555;
    margin:0;            /* remove margins */
    /* Keep TH as a proper table-cell; avoid flex to preserve table flow */
    /* Let the cell itself control the height; center content vertically */
    padding:0.2rem 0.4rem;   /* tighter padding so row height fits content */
  }
  /* Remove default TH padding/margins and make cell content fill height */
  .sv-mv-table th.cis-label.sv-mv-label {
    padding: 0.2rem 0.4rem !important;
    margin: 0 !important;
  }
  .sv-mv-table th.cis-label {
    padding: 0 !important;
    vertical-align: middle; /* ensure label fills the cell height visually */
  }
  .sv-mv-table td { padding: 0.2rem 0.4rem; }
  .sv-mv-table tr { height: auto; }
  .sv-mv-textarea { min-height: unset; height:auto; font-size:10pt; font-family:'Times New Roman', Times, serif; line-height:1.35; resize:vertical; }
  .sv-mv-textarea:focus { box-shadow:none; border-color:#666; }
  /* table layout is responsive by default */
</style>
@endpush
@endonce
