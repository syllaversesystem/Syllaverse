{{--
* File: resources/views/faculty/syllabus/partials/ai-chat-panel.blade.php
* Description: AI Chat panel with FAB trigger - modern slide-in interface
--}}

<!-- AI Chat FAB (Floating Action Button) -->
<button type="button" id="aiChatFab" class="ai-chat-fab" aria-label="Open AI Assistant" title="AI Assistant">
  <i class="bi bi-stars"></i>
</button>

<!-- AI Chat Panel (slide-in from right, full height) -->
<div id="aiChatPanel" class="ai-chat-panel" role="dialog" aria-labelledby="aiChatTitle" aria-hidden="true">
  <!-- Panel Header -->
  <div class="ai-chat-header">
    <div class="ai-chat-title-wrap">
      <i class="bi bi-stars text-primary me-2"></i>
      <h3 id="aiChatTitle" class="ai-chat-title">AI Assistant</h3>
    </div>
    <button type="button" id="aiChatClose" class="ai-chat-close-btn" aria-label="Close AI Assistant">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>

  <!-- Messages Container -->
  <div id="aiChatMessages" class="ai-chat-messages"></div>

  <!-- Input Area -->
  <div class="ai-chat-input-wrap">
    <div class="ai-chat-input-container">
      <textarea 
        id="aiChatInput" 
        class="ai-chat-input" 
        placeholder="Ask me anything about your syllabus..."
        rows="1"
        aria-label="Message input"></textarea>
      <button type="button" id="aiChatSend" class="ai-chat-send-btn" aria-label="Send message">
        <i class="bi bi-send-fill"></i>
      </button>
    </div>
    <div class="ai-chat-hint">
      <i class="bi bi-info-circle me-1"></i>
      <span>Press Enter to send, Shift+Enter for new line</span>
    </div>
  </div>
</div>

<!-- Backdrop -->
<div id="aiChatBackdrop" class="ai-chat-backdrop"></div>

<style>
/* ======================================
   AI Chat FAB (Floating Action Button)
   ====================================== */
.ai-chat-fab {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
  border: none;
  color: #fff;
  font-size: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4), 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 1040;
  outline: none;
}

.ai-chat-fab:hover {
  transform: translateY(-2px) scale(1.05);
  box-shadow: 0 6px 16px rgba(220, 38, 38, 0.5), 0 3px 6px rgba(0, 0, 0, 0.15);
}

.ai-chat-fab:active {
  transform: translateY(0) scale(0.98);
}

.ai-chat-fab i {
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

/* Hide FAB when panel is open */
.ai-chat-panel.open ~ .ai-chat-fab {
  opacity: 0;
  pointer-events: none;
  transform: scale(0.8);
}

/* ======================================
   AI Chat Panel (Slide-in)
   ====================================== */
.ai-chat-panel {
  position: fixed;
  top: 0;
  right: 0;
  width: 420px;
  max-width: 90vw;
  height: 100vh;
  background: #ffffff;
  box-shadow: -4px 0 24px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  transform: translateX(100%);
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 1050;
}

.ai-chat-panel.open {
  transform: translateX(0);
}

/* Panel Header */
.ai-chat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
}

.ai-chat-title-wrap {
  display: flex;
  align-items: center;
}

.ai-chat-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #111827;
}

.ai-chat-close-btn {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: none;
  background: transparent;
  color: #6b7280;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 1.1rem;
}

.ai-chat-close-btn:hover {
  background: #f3f4f6;
  color: #111827;
}

/* Messages Container */
.ai-chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  background: #f9fafb;
}

/* Message Bubble */
.ai-chat-msg {
  display: flex;
  flex-direction: column;
  animation: slideIn 0.3s ease;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.ai-chat-msg.user {
  align-items: flex-end;
}

.ai-chat-msg.ai {
  align-items: flex-start;
}

.ai-chat-bubble {
  max-width: 85%;
  padding: 0.875rem 1.125rem;
  border-radius: 16px;
  font-size: 0.9375rem;
  line-height: 1.5;
  word-wrap: break-word;
  position: relative;
}

.ai-chat-msg.user .ai-chat-bubble {
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
  color: #ffffff;
  border-bottom-right-radius: 4px;
}

.ai-chat-msg.ai .ai-chat-bubble {
  background: #ffffff;
  color: #111827;
  border: 1px solid #e5e7eb;
  border-bottom-left-radius: 4px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

/* AI Message Formatting */
.ai-chat-msg.ai .ai-chat-bubble p {
  margin: 0 0 0.75rem 0;
}

.ai-chat-msg.ai .ai-chat-bubble p:last-child {
  margin-bottom: 0;
}

.ai-chat-msg.ai .ai-chat-bubble ul,
.ai-chat-msg.ai .ai-chat-bubble ol {
  margin: 0.5rem 0;
  padding-left: 1.5rem;
}

.ai-chat-msg.ai .ai-chat-bubble li {
  margin: 0.25rem 0;
}

.ai-chat-msg.ai .ai-chat-bubble code {
  background: #f3f4f6;
  padding: 0.125rem 0.375rem;
  border-radius: 4px;
  font-size: 0.875em;
  font-family: 'Courier New', monospace;
}

.ai-chat-msg.ai .ai-chat-bubble pre {
  background: #1f2937;
  color: #f9fafb;
  padding: 0.75rem;
  border-radius: 8px;
  overflow-x: auto;
  margin: 0.75rem 0;
}

.ai-chat-msg.ai .ai-chat-bubble pre code {
  background: transparent;
  color: inherit;
  padding: 0;
}

/* Loading indicator */
.ai-chat-msg.loading .ai-chat-bubble {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
}

.ai-chat-msg.loading .ai-chat-bubble::after {
  content: '';
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #9ca3af;
  animation: loadingDot 1.4s infinite ease-in-out both;
}

.ai-chat-msg.loading .ai-chat-bubble::before {
  content: '';
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #9ca3af;
  animation: loadingDot 1.4s infinite ease-in-out both;
  animation-delay: -0.32s;
  margin-left: 4px;
}

@keyframes loadingDot {
  0%, 80%, 100% { 
    opacity: 0.3;
    transform: scale(0.8);
  }
  40% { 
    opacity: 1;
    transform: scale(1);
  }
}

/* Input Area */
.ai-chat-input-wrap {
  border-top: 1px solid #e5e7eb;
  background: #ffffff;
  padding: 1rem 1.5rem;
}

.ai-chat-input-container {
  display: flex;
  align-items: flex-end;
  gap: 0.75rem;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 0.625rem 0.75rem;
  transition: all 0.2s ease;
}

.ai-chat-input-container:focus-within {
  border-color: #dc2626;
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
  background: #ffffff;
}

.ai-chat-input {
  flex: 1;
  border: none;
  outline: none;
  background: transparent;
  resize: none;
  font-size: 0.9375rem;
  line-height: 1.5;
  max-height: 120px;
  padding: 0.25rem 0;
  color: #111827;
  font-family: inherit;
}

.ai-chat-input::placeholder {
  color: #9ca3af;
}

.ai-chat-send-btn {
  width: 36px;
  height: 36px;
  min-width: 36px;
  border-radius: 8px;
  border: none;
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 1rem;
}

.ai-chat-send-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(220, 38, 38, 0.3);
}

.ai-chat-send-btn:active {
  transform: translateY(0);
}

.ai-chat-send-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.ai-chat-hint {
  margin-top: 0.5rem;
  font-size: 0.8125rem;
  color: #6b7280;
  display: flex;
  align-items: center;
}

/* Backdrop */
.ai-chat-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.3s ease;
  z-index: 1045;
}

.ai-chat-backdrop.show {
  opacity: 1;
  pointer-events: auto;
}

/* Scrollbar Styling */
.ai-chat-messages::-webkit-scrollbar {
  width: 6px;
}

.ai-chat-messages::-webkit-scrollbar-track {
  background: transparent;
}

.ai-chat-messages::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 3px;
}

.ai-chat-messages::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}

/* Responsive */
@media (max-width: 768px) {
  .ai-chat-panel {
    width: 100%;
    max-width: 100%;
  }

  .ai-chat-fab {
    bottom: 1.5rem;
    right: 1.5rem;
    width: 56px;
    height: 56px;
    font-size: 1.375rem;
  }

  .ai-chat-messages {
    padding: 1rem;
  }

  .ai-chat-bubble {
    max-width: 90%;
  }
}
</style>
