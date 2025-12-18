/**
 * File: resources/js/faculty/ai/assessment-schedule-mapping.js
 * Description: Assessment Schedule Mapping module - handles generating assessment
 * schedule mappings using AI with prompts and snapshots without showing chat output.
 */

(function() {
  'use strict';

  // Reference to chat panel for messaging
  let chatPanel = null;

  /**
   * Initialize the assessment schedule mapping module
   */
  function init() {
    // Get chat panel reference
    chatPanel = document.getElementById('aiChatMessages');
    console.log('[Assessment Schedule] Module initialized');
  }

  /**
   * Append a message to the chat without exposing implementation details
   */
  function appendChatMessage(role, text, isLoading = false) {
    if (!chatPanel) {
      console.warn('[Assessment Schedule] Chat panel not available');
      return null;
    }

    const msgDiv = document.createElement('div');
    msgDiv.className = `ai-chat-msg ${role}${isLoading ? ' loading' : ''}`;

    const bubbleDiv = document.createElement('div');
    bubbleDiv.className = 'ai-chat-bubble';
    bubbleDiv.textContent = text;

    msgDiv.appendChild(bubbleDiv);
    chatPanel.appendChild(msgDiv);

    // Scroll to bottom
    chatPanel.scrollTop = chatPanel.scrollHeight;

    return msgDiv;
  }

  /**
   * Generate Assessment Schedule Mapping
   * Sends all prompts and snapshots to AI for processing
   * Shows only "Done" message in chat, no detailed output
   */
  async function generateAssessmentScheduleMapping() {
    try {
      // Get syllabus ID
      const syllabusId = getSyllabusId();
      if (!syllabusId) {
        throw new Error('Syllabus ID not found');
      }

      // Collect all necessary data
      const allPrompts = collectAllPrompts();
      const snapshots = collectAllSnapshots();
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

      // Build form data with all prompts and snapshots
      const formData = new FormData();
      formData.append('message', 'Generate assessment schedule mapping');
      formData.append('context', collectContext());
      formData.append('snapshots', snapshots);
      formData.append('prompts', allPrompts);
      formData.append('partial', 'assessment_schedule');
      formData.append('history', JSON.stringify([]));

      // Show loading state
      const loadingMsg = appendChatMessage('ai', 'Processing assessment schedule...', true);

      // Send request to AI backend
      const response = await fetch(`/faculty/syllabi/${syllabusId}/ai-chat`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: formData
      });

      if (!response.ok) {
        throw new Error(`HTTP Error: ${response.status}`);
      }

      const data = await response.json();

      if (!data.success) {
        throw new Error(data.message || 'Failed to generate assessment schedule mapping');
      }

      // Remove loading message
      if (loadingMsg) {
        loadingMsg.remove();
      }

      // Show only "Done" in chat
      appendChatMessage('ai', 'Done');

      console.log('[Assessment Schedule] Mapping generated successfully', data);

      // Optionally handle background processing or data storage
      if (data.mapping) {
        console.log('[Assessment Schedule] Mapping data:', data.mapping);
      }

      return true;

    } catch (error) {
      console.error('[Assessment Schedule] Error:', error);

      // Show error message in chat
      appendChatMessage('ai', `Error: ${error.message || 'Failed to generate assessment schedule mapping'}`);

      return false;
    }
  }

  /**
   * Get syllabus ID from DOM
   */
  function getSyllabusId() {
    const el = document.getElementById('syllabus-document');
    return el ? el.getAttribute('data-syllabus-id') : null;
  }

  /**
   * Collect context from syllabus
   */
  function collectContext() {
    const ctx = { sections: [] };
    const partials = document.querySelectorAll('.sv-partial');
    partials.forEach(p => {
      const key = p.getAttribute('data-partial-key');
      if (!key || key === 'status') return;
      let text = (p.textContent || '').replace(/\s+/g, ' ').trim();
      if (text.length > 800) text = text.slice(0, 800) + '...';
      if (text) ctx.sections.push({ key, text });
    });
    const courseTitle = document.querySelector('[name="course_title"]')?.value || '';
    const courseCode = document.querySelector('[name="course_code"]')?.value || '';
    if (courseTitle) ctx.courseTitle = courseTitle;
    if (courseCode) ctx.courseCode = courseCode;
    return JSON.stringify(ctx);
  }

  /**
   * Collect all snapshots
   */
  function collectAllSnapshots() {
    if (!window.SVSnapshot || typeof window.SVSnapshot.collectAllSnapshots !== 'function') {
      return '{}';
    }
    try {
      const snapshots = window.SVSnapshot.collectAllSnapshots();
      return JSON.stringify(snapshots);
    } catch (e) {
      console.warn('[Assessment Schedule] Failed to collect snapshots', e);
      return '{}';
    }
  }

  /**
   * Collect all prompts
   */
  function collectAllPrompts() {
    if (!window.SVPrompts || typeof window.SVPrompts.getAll !== 'function') {
      return '{}';
    }
    try {
      const allPrompts = window.SVPrompts.getAll();
      return JSON.stringify(allPrompts);
    } catch (e) {
      console.warn('[Assessment Schedule] Failed to collect all prompts', e);
      return '{}';
    }
  }

  // Export API
  window.SVAssessmentSchedule = {
    generate: generateAssessmentScheduleMapping
  };

  console.log('[Assessment Schedule] Module loaded');

})();
