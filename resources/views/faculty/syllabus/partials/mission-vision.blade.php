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
  @vite(['resources/js/faculty/syllabus-mission-vision.js', 'resources/js/faculty/mission-vision-input-handler.js'])
  <script>
    (function(){
      function sanitize(val){
        if (val == null) return '-';
        const s = String(val).trim();
        return s.length ? s : '-';
      }
      function buildMvBlock(){
        const vision = document.getElementById('vision-text');
        const mission = document.getElementById('mission-text');
        const v = sanitize(vision?.value);
        const m = sanitize(mission?.value);
        const lines = [];
        lines.push('PARTIAL_BEGIN:mission_vision');
        lines.push('TITLE: Institutional Vision & Mission');
        lines.push('COLUMNS: Label | Text');
        lines.push(`ROW: Vision | ${v}`);
        lines.push(`ROW: Mission | ${m}`);
        lines.push('PARTIAL_END:mission_vision');
        return lines.join('\n');
      }
      function updateRealtime(){
        const mv = buildMvBlock();
        const existing = window._svRealtimeContext || '';
        const others = existing
          .split(/\n{2,}/)
          .filter(s => s && !/PARTIAL_BEGIN:mission_vision[\s\S]*PARTIAL_END:mission_vision/.test(s))
          .join('\n\n');
        const merged = others ? (others + '\n\n' + mv) : mv;
        window._svRealtimeContext = merged;
      }
      ['input','change'].forEach(evt => {
        document.addEventListener(evt, function(e){
          if (e && e.target && (e.target.id === 'vision-text' || e.target.id === 'mission-text')) {
            updateRealtime();
          }
        }, true);
      });
      document.addEventListener('DOMContentLoaded', updateRealtime);
      window.addEventListener('load', updateRealtime);
      // Initial run
      updateRealtime();
    })();
  </script>
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

  /* Mission/Vision partial drop zone highlighting */
  .sv-partial.mv-drop-active {
    border: 2px solid #dc2626 !important;
    background-color: #fef2f2 !important;
    box-shadow: 0 0 12px rgba(220, 38, 38, 0.2) !important;
    transition: all 0.15s ease;
  }
</style>
@endpush
@endonce
