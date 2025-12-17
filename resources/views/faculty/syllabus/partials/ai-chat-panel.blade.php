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
  <!-- Resize Handle (left edge) -->
  <div id="aiChatResizeHandle" class="ai-chat-resize-handle"></div>
  
  <!-- Panel Header -->
  <div class="ai-chat-header" id="aiChatDragHandle">
    <div class="ai-chat-title-wrap">
      <i class="bi bi-stars text-primary me-2"></i>
      <h3 id="aiChatTitle" class="ai-chat-title">AI Assistant</h3>
      <div class="ai-chat-drag-indicator">
        <i class="bi bi-arrows-move"></i>
      </div>
    </div>
    <button type="button" id="aiChatClose" class="ai-chat-close-btn" aria-label="Close AI Assistant">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>

  <!-- Messages Container -->
  <div id="aiChatMessages" class="ai-chat-messages"></div>

  <!-- Action Panel -->
  <div class="ai-chat-actions">
    <div class="ai-chat-section">
      <div class="ai-chat-section-title">Generate</div>
      <div class="ai-chat-section-body-wrap">
        <button type="button" class="ai-section-nav ai-section-nav-prev" aria-label="Scroll left">
          <i class="bi bi-chevron-left"></i>
        </button>
        <div class="ai-chat-section-body">
          <button type="button" class="ai-chip">Course Rationale and Description</button>
          <button type="button" class="ai-chip">Teaching, Learning, and Assessment Strategies</button>
          <button type="button" class="ai-chip">ILO</button>
          <button type="button" class="ai-chip">Teaching, Learning, and Assessment (TLA) Activities</button>
        </div>
        <button type="button" class="ai-section-nav ai-section-nav-next" aria-label="Scroll right">
          <i class="bi bi-chevron-right"></i>
        </button>
      </div>
    </div>
    <div class="ai-chat-section">
      <div class="ai-chat-section-title">Map</div>
      <div class="ai-chat-section-body-wrap">
        <button type="button" class="ai-section-nav ai-section-nav-prev" aria-label="Scroll left">
          <i class="bi bi-chevron-left"></i>
        </button>
        <div class="ai-chat-section-body">
          <button type="button" class="ai-chip">Assessment Schedule</button>
          <button type="button" class="ai-chip">ILO-SO-CPA</button>
          <button type="button" class="ai-chip">ILO-IGA</button>
          <button type="button" class="ai-chip">ILO-CDIO-SDG</button>
        </div>
        <button type="button" class="ai-section-nav ai-section-nav-next" aria-label="Scroll right">
          <i class="bi bi-chevron-right"></i>
        </button>
      </div>
    </div>
  </div>

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
  transition: none;
}

.ai-chat-panel.dragging {
  user-select: none;
}

/* Resize Handle */
.ai-chat-resize-handle {
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  cursor: ew-resize;
  background: transparent;
  transition: background 0.2s ease;
  z-index: 10;
}

.ai-chat-resize-handle:hover {
  background: #dc2626;
}

.ai-chat-resize-handle.resizing {
  background: #dc2626;
}

/* Drag Indicator */
.ai-chat-drag-indicator {
  display: none;
  margin-left: 0.5rem;
  color: #9ca3af;
  font-size: 0.9rem;
  opacity: 0;
  transition: opacity 0.2s ease;
  cursor: grab;
}

.ai-chat-header:hover .ai-chat-drag-indicator {
  opacity: 0.6;
}

.ai-chat-header.dragging .ai-chat-drag-indicator {
  opacity: 1;
  color: #dc2626;
  cursor: grabbing;
}

@media (min-width: 768px) {
  .ai-chat-drag-indicator {
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
}

/* Panel Header */
.ai-chat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
  user-select: none;
  transition: background 0.2s ease;
}

.ai-chat-header.dragging {
  background: linear-gradient(135deg, #fef2f2 0%, #fce7e7 100%);
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

/* AI Tables */
.ai-chat-table-wrap {
  width: 100%;
  overflow-x: auto;
  margin: 0.75rem 0;
}

.ai-chat-table {
  border-collapse: collapse;
  min-width: 420px;
  width: 100%;
  font-size: 0.9rem;
}

.ai-chat-table th,
.ai-chat-table td {
  border: 1px solid #e5e7eb;
  padding: 0.5rem 0.75rem;
  text-align: left;
  background: #ffffff;
}

.ai-chat-table th {
  background: #f3f4f6;
  font-weight: 600;
}

/* Action Panel */
.ai-chat-actions {
  padding: 0.5rem 1rem 0.35rem 1rem;
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(4px);
  border-top: 1px solid #e5e7eb;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  font-size: 0.72rem;
}

.ai-chat-section {
  padding: 0.35rem 0;
}

.ai-chat-section + .ai-chat-section {
  border-top: 1px solid #e5e7eb;
  padding-top: 0.45rem;
}

.ai-chat-section-title {
  font-size: 0.66rem;
  font-weight: 600;
  color: #6b7280;
  margin-bottom: 0.25rem;
}

.ai-chat-section-body-wrap {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.ai-chat-section-body {
  display: flex;
  flex-wrap: nowrap;
  gap: 0.32rem;
  overflow-x: auto;
  scroll-behavior: smooth;
  flex: 1;
  min-width: 0;
  padding-right: 0.25rem;
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* IE and Edge */
}

.ai-chat-section-body::-webkit-scrollbar {
  display: none; /* Chrome, Safari, Opera */
}

.ai-section-nav {
  background: #f3f4f6;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #6b7280;
  font-size: 0.65rem;
  flex-shrink: 0;
  transition: all 0.2s ease;
}

.ai-section-nav:hover {
  background: #e5e7eb;
  color: #111827;
}

.ai-section-nav:active {
  transform: scale(0.95);
}

.ai-chip {
  border: 1px solid #e5e7eb;
  background: #ffffff;
  color: #111827;
  border-radius: 999px;
  padding: 0.22rem 0.5rem;
  font-size: 0.7rem;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
  flex-shrink: 0;
}

.ai-chip:hover {
  border-color: #dc2626;
  color: #b91c1c;
  box-shadow: 0 2px 6px rgba(220, 38, 38, 0.15);
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

/* Course Rationale Card */
.ai-course-rationale-container {
  margin-top: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.ai-course-rationale-card {
  background: #fef2f2;
  border: 2px solid #dc2626;
  border-radius: 12px;
  overflow: hidden;
}

/* TLAS Card */
.ai-tlas-container {
  margin-top: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.ai-tlas-card {
  background: #fef2f2;
  border: 2px solid #dc2626;
  border-radius: 12px;
  overflow: hidden;
}

/* ILO Card */
.ai-ilo-container {
  margin-top: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.ai-ilo-card {
  background: #fef2f2;
  border: 2px solid #dc2626;
  border-radius: 12px;
  overflow: hidden;
}

.ai-ilo-card .ai-card-body {
  max-height: 400px;
  overflow-y: auto;
}

/* TLA Activities Card */
.ai-tla-activities-container {
  margin-top: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.ai-tla-activities-card {
  background: #fef2f2;
  border: 2px solid #dc2626;
  border-radius: 12px;
  overflow: hidden;
}

.ai-tla-activities-card .ai-card-body {
  max-height: 500px;
  overflow-y: auto;
}

.ai-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1rem;
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
  color: #ffffff;
}

.ai-card-title {
  font-weight: 600;
  font-size: 0.95rem;
}

.ai-card-button-wrapper {
  display: flex;
  justify-content: center;
}

.ai-card-insert-btn {
  padding: 0.5rem 1rem;
  border-radius: 8px;
  border: none;
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 0.9rem;
  font-weight: 500;
}

.ai-card-insert-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.ai-card-insert-btn:active {
  transform: translateY(0);
}

.ai-card-body {
  padding: 1rem;
}

.ai-card-body p {
  margin: 0;
  font-size: 0.95rem;
  line-height: 1.6;
  color: #1e293b;
}

/* Tables inside cards */
.ai-card-body .ai-chat-table-wrap {
  margin: 0;
}

.ai-card-body .ai-chat-table {
  font-size: 0.875rem;
  width: 100%;
}

.ai-card-body .ai-chat-table th {
  background: #fee2e2;
  color: #991b1b;
  font-weight: 600;
  text-align: center;
  white-space: nowrap;
}

.ai-card-body .ai-chat-table td {
  background: #ffffff;
  color: #374151;
  vertical-align: top;
}

.ai-card-body .ai-chat-table th,
.ai-card-body .ai-chat-table td {
  border: 1px solid #fecaca;
  padding: 0.5rem;
}

/* Insert Feedback Animation */
@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateX(-50%) translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
  }
}

@keyframes slideDown {
  from {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
  }
  to {
    opacity: 0;
    transform: translateX(-50%) translateY(20px);
  }
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

<script>
// Handle scroll navigation for action panel buttons
document.addEventListener('DOMContentLoaded', function() {
  const navButtons = document.querySelectorAll('.ai-section-nav');
  
  navButtons.forEach(btn => {
    btn.addEventListener('click', function() {
      const bodyWrap = this.closest('.ai-chat-section-body-wrap');
      const scrollContainer = bodyWrap.querySelector('.ai-chat-section-body');
      const scrollAmount = 120;
      
      if (this.classList.contains('ai-section-nav-prev')) {
        scrollContainer.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
      } else if (this.classList.contains('ai-section-nav-next')) {
        scrollContainer.scrollBy({ left: scrollAmount, behavior: 'smooth' });
      }
    });
  });
});
</script>

@push('scripts')
  @vite([
    'resources/js/faculty/ai/prompts.js',
    'resources/js/faculty/ai/ai.js',
    'resources/js/faculty/ai/chat-panel.js',
  ])
@endpush
