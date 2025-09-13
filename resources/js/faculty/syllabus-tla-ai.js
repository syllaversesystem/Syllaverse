// -----------------------------------------------------------------------------
// File: resources/js/faculty/syllabus-tla-ai.js
// Description: Handles "Generate TLA Plan (AI)" button logic via Gemini integration – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-30] Initial creation – triggers Gemini-based TLA generation and reloads table.
// [2025-07-30] Improved: handles HTML fallback, safe JSON parse, loading feedback.
// [2025-07-30] Added: shows prompt and raw Gemini response in debug window.
// -----------------------------------------------------------------------------

document.addEventListener('DOMContentLoaded', () => {
  const generateBtn = document.getElementById('generate-tla-ai');
  const syllabusId = document.querySelector('meta[name="csrf-token"]')?.content
    ? document.querySelector('#syllabusForm')?.action.split('/').pop()
    : null;

  if (!generateBtn || !syllabusId) return;

  generateBtn.addEventListener('click', async () => {
    if (!confirm('This will replace your current TLA content. Continue?')) return;

    generateBtn.disabled = true;
    generateBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Generating...';

    try {
  const base = window.syllabusBasePath || '/faculty/syllabi';
  const res = await fetch(`${base}/${syllabusId}/generate-tla`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({}),
      });

      const raw = await res.text();
      let data = {};

      try {
        data = JSON.parse(raw);
      } catch (parseErr) {
        console.error('⚠️ Gemini returned non-JSON (likely error page):', raw);
        alert('The AI server returned an unexpected response. Check Laravel logs.');
        return;
      }

      // ✅ Debug window: Show prompt + raw Gemini response
      if (data.prompt || data.ai_raw_output) {
        const debugWindow = window.open('', '_blank');
        debugWindow.document.write(`
          <pre style="white-space:pre-wrap;font-size:13px">
<strong>📥 PROMPT SENT TO AI</strong>\n\n${data.prompt || 'N/A'}

<strong>📤 RAW AI RESPONSE</strong>\n\n${data.ai_raw_output || 'N/A'}
          </pre>
        `);
        debugWindow.document.title = 'AI Debug – Prompt & Output';
      }

      if (data.success) {
        alert(data.message);
        window.location.reload();
      } else {
        alert(data.message || 'AI did not return a valid TLA plan.');
      }
    } catch (error) {
      console.error('❌ TLA AI generation error:', error);
      alert('Error occurred while generating TLA. See console for details.');
    } finally {
      generateBtn.disabled = false;
      generateBtn.innerHTML = '<i class="bi bi-stars"></i> Generate TLA Plan (AI)';
    }
  });
});
