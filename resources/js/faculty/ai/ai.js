/*
 * File: resources/js/faculty/ai/ai.js
 * Description: Core AI utilities for sending/receiving messages.
 * Responsibilities:
 *  - Build payloads with syllabus context and recent history
 *  - Handle CSRF and network errors
 *  - Provide a simple promise-based API for chat UIs
 */
(function(){
  'use strict';

  function getSyllabusId(){
    const el = document.getElementById('syllabus-document');
    return el ? el.getAttribute('data-syllabus-id') : null;
  }

  function collectContext(){
    const ctx = { sections: [] };
    const partials = document.querySelectorAll('.sv-partial');
    partials.forEach(p => {
      const key = p.getAttribute('data-partial-key');
      if (!key || key === 'status') return;
      let text = (p.textContent || '').replace(/\s+/g,' ').trim();
      if (text.length > 800) text = text.slice(0,800) + '...';
      if (text) ctx.sections.push({ key, text });
    });
    const courseTitle = document.querySelector('[name="course_title"]')?.value || '';
    const courseCode = document.querySelector('[name="course_code"]')?.value || '';
    if (courseTitle) ctx.courseTitle = courseTitle;
    if (courseCode) ctx.courseCode = courseCode;
    return JSON.stringify(ctx);
  }

  async function send(message, history){
    const syllabusId = getSyllabusId();
    if (!syllabusId) throw new Error('Syllabus ID not found');

    const formData = new FormData();
    formData.append('message', message);
    formData.append('context', collectContext());
    formData.append('history', JSON.stringify(Array.isArray(history) ? history.slice(-10) : []));

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const res = await fetch(`/faculty/syllabi/${syllabusId}/ai-chat`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
      body: formData,
    });

    if (!res.ok) {
      let errMsg = `HTTP ${res.status}`;
      try { const err = await res.json(); errMsg = err.message || errMsg; } catch(_){}
      throw new Error(errMsg);
    }

    const data = await res.json();
    if (!data.reply) throw new Error('No reply from server');
    return data.reply;
  }

  // Event-based wrapper (optional): dispatches browser events for listeners
  async function sendAndDispatch(message, history){
    try {
      const reply = await send(message, history);
      window.dispatchEvent(new CustomEvent('ai:reply', { detail: { reply, message } }));
      return reply;
    } catch (error) {
      window.dispatchEvent(new CustomEvent('ai:error', { detail: { error: String(error?.message || error) } }));
      throw error;
    }
  }

  // Public API
  window.SVAI = { send, sendAndDispatch };
})();
