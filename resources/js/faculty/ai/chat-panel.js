/**
 * File: resources/js/faculty/ai/chat-panel.js
 * Description: AI Chat Panel - modern slide-in interface with message handling
 */

(function() {
  'use strict';

  // State
  let isOpen = false;
  const conversationHistory = [];

  // DOM Elements
  let fab, panel, backdrop, closeBtn, messagesContainer, input, sendBtn;

  /**
   * Initialize the chat panel
   */
  function init() {
    // Get DOM references
    fab = document.getElementById('aiChatFab');
    panel = document.getElementById('aiChatPanel');
    backdrop = document.getElementById('aiChatBackdrop');
    closeBtn = document.getElementById('aiChatClose');
    messagesContainer = document.getElementById('aiChatMessages');
    input = document.getElementById('aiChatInput');
    sendBtn = document.getElementById('aiChatSend');

    if (!fab || !panel) {
      console.warn('[AI Chat] Required elements not found');
      return;
    }

    // Attach event listeners
    fab.addEventListener('click', openPanel);
    closeBtn?.addEventListener('click', closePanel);
    backdrop?.addEventListener('click', closePanel);
    sendBtn?.addEventListener('click', handleSend);
    
    input?.addEventListener('input', handleInputResize);
    input?.addEventListener('keydown', handleKeyDown);

    // Initial resize
    if (input) handleInputResize();

    console.log('[AI Chat] Initialized');
  }

  /**
   * Open the chat panel
   */
  function openPanel() {
    if (isOpen) return;
    
    isOpen = true;
    panel.classList.add('open');
    backdrop.classList.add('show');
    panel.setAttribute('aria-hidden', 'false');
    
    // Focus input
    setTimeout(() => input?.focus(), 300);
    
    // Scroll to bottom
    scrollToBottom();
  }

  /**
   * Close the chat panel
   */
  function closePanel() {
    if (!isOpen) return;
    
    isOpen = false;
    panel.classList.remove('open');
    backdrop.classList.remove('show');
    panel.setAttribute('aria-hidden', 'true');
  }

  /**
   * Handle keyboard shortcuts
   */
  function handleKeyDown(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      handleSend();
    } else if (e.key === 'Escape') {
      closePanel();
    }
  }

  /**
   * Auto-resize textarea as user types
   */
  function handleInputResize() {
    if (!input) return;
    
    input.style.height = 'auto';
    const newHeight = Math.min(input.scrollHeight, 120);
    input.style.height = newHeight + 'px';
  }

  /**
   * Handle send button click
   */
  async function handleSend() {
    if (!input || !messagesContainer) return;
    
    const message = input.value.trim();
    if (!message) return;

    // Add user message to UI
    appendMessage('user', message);
    
    // Clear input
    input.value = '';
    handleInputResize();

    // Disable send button during processing
    if (sendBtn) {
      sendBtn.disabled = true;
    }

    // Add loading indicator
    const loadingMsg = appendMessage('ai', 'Thinking...', true);

    try {
      // Send to backend
      const response = await sendToBackend(message);
      
      // Remove loading
      if (loadingMsg) loadingMsg.remove();
      
      // Add AI response
      appendMessage('ai', response);
      
    } catch (error) {
      console.error('[AI Chat] Error:', error);
      
      // Remove loading
      if (loadingMsg) loadingMsg.remove();
      
      // Show error message
      appendMessage('ai', 'Sorry, I encountered an error. Please try again.');
    } finally {
      // Re-enable send button
      if (sendBtn) {
        sendBtn.disabled = false;
      }
      
      // Refocus input
      input?.focus();
    }
  }

  /**
   * Append message to chat
   */
  function appendMessage(role, text, isLoading = false) {
    if (!messagesContainer) return null;

    const msgDiv = document.createElement('div');
    msgDiv.className = `ai-chat-msg ${role}${isLoading ? ' loading' : ''}`;
    
    const bubbleDiv = document.createElement('div');
    bubbleDiv.className = 'ai-chat-bubble';
    
    if (role === 'ai' && !isLoading) {
      // Format AI response (support basic markdown-like formatting)
      bubbleDiv.innerHTML = formatAIResponse(text);
    } else {
      bubbleDiv.textContent = text;
    }
    
    msgDiv.appendChild(bubbleDiv);
    messagesContainer.appendChild(msgDiv);
    
    // Save to history (skip loading messages)
    if (!isLoading) {
      conversationHistory.push({ role, text });
    }
    
    // Scroll to bottom
    scrollToBottom();
    
    return msgDiv;
  }

  /**
   * Format AI response with basic markdown support
   */
  function formatAIResponse(text) {
    let html = text;
    
    // Escape HTML
    html = html
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
    
    // Code blocks
    html = html.replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>');
    
    // Inline code
    html = html.replace(/`([^`]+)`/g, '<code>$1</code>');
    
    // Bold
    html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
    
    // Italic
    html = html.replace(/\*([^*]+)\*/g, '<em>$1</em>');
    
    // Line breaks to paragraphs
    html = html.split('\n\n').map(para => {
      if (para.trim()) {
        // Check if it's a list
        if (/^[-*]\s/.test(para)) {
          const items = para.split('\n').map(line => {
            return line.replace(/^[-*]\s/, '<li>') + '</li>';
          }).join('');
          return '<ul>' + items + '</ul>';
        }
        return '<p>' + para.replace(/\n/g, '<br>') + '</p>';
      }
      return '';
    }).join('');
    
    return html;
  }

  /**
   * Scroll messages container to bottom
   */
  function scrollToBottom() {
    if (!messagesContainer) return;
    
    requestAnimationFrame(() => {
      messagesContainer.scrollTop = messagesContainer.scrollHeight;
    });
  }

  /**
   * Send message to backend
   */
  async function sendToBackend(message) {
    const syllabusId = getSyllabusId();
    if (!syllabusId) {
      throw new Error('Syllabus ID not found');
    }

    // Collect context from the page
    const context = collectContext();
    
    // Prepare payload
    const formData = new FormData();
    formData.append('message', message);
    formData.append('context', context);
    formData.append('history', JSON.stringify(conversationHistory.slice(-10))); // Last 10 messages
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Send request
    const response = await fetch(`/faculty/syllabi/${syllabusId}/ai-chat`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken || '',
        'Accept': 'application/json',
      },
      body: formData
    });

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      throw new Error(errorData.message || `HTTP ${response.status}`);
    }

    const data = await response.json();
    
    if (!data.reply) {
      throw new Error('No reply from server');
    }

    return data.reply;
  }

  /**
   * Get syllabus ID from the page
   */
  function getSyllabusId() {
    const syllabusDoc = document.getElementById('syllabus-document');
    return syllabusDoc?.getAttribute('data-syllabus-id') || null;
  }

  /**
   * Collect context from the current syllabus page
   */
  function collectContext() {
    const context = {
      sections: []
    };

    // Get all partial sections
    const partials = document.querySelectorAll('.sv-partial');
    
    partials.forEach(partial => {
      const key = partial.getAttribute('data-partial-key');
      if (!key || key === 'status') return;
      
      // Get section text (limit length)
      let text = partial.textContent || '';
      text = text.replace(/\s+/g, ' ').trim();
      
      if (text.length > 800) {
        text = text.slice(0, 800) + '...';
      }
      
      if (text) {
        context.sections.push({
          key,
          text
        });
      }
    });

    // Get basic course info
    const courseTitle = document.querySelector('[name="course_title"]')?.value || '';
    const courseCode = document.querySelector('[name="course_code"]')?.value || '';
    
    if (courseTitle) context.courseTitle = courseTitle;
    if (courseCode) context.courseCode = courseCode;

    return JSON.stringify(context);
  }

  /**
   * Public API
   */
  window.AIChat = {
    open: openPanel,
    close: closePanel,
    send: (message) => {
      if (input) {
        input.value = message;
        handleSend();
      }
    }
  };

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
