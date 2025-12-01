<section class="syllabus-right-toolbar sv-review-panel" aria-label="Syllabus Review Panel">
  <div class="right-resize-handle" aria-hidden="true"></div>

  <!-- Header removed per request: selected partial title and color tag no longer shown -->

  <!-- Comment Section (default) -->
  <div class="sv-toolbar-comment-section">
    <div class="sv-review-comments" id="svReviewComments" aria-live="polite"></div>
  </div>

  <!-- AI Chat Section (hidden by default; toggled by AI button) -->
  <div class="sv-toolbar-ai-section" id="svAiChatSection" style="display:none;">
    <div class="sv-ai-header d-flex align-items-center justify-content-between mb-1">
      <div class="sv-ai-title"><i class="bi bi-stars me-1"></i> AI Assist</div>
    </div>
    <div class="sv-ai-chat" id="svAiChatMessages" aria-live="polite"></div>
    <div class="sv-ai-input">
      <div class="input-group input-group-sm sv-ai-input-group">
        <span class="input-group-text" aria-hidden="true"><i class="bi bi-stars"></i></span>
        <textarea id="svAiChatInput" class="form-control sv-ai-textarea" rows="1" placeholder="Ask AI about this syllabus..." aria-label="AI chat input"></textarea>
        <button class="btn btn-danger" type="button" id="svAiChatSend" aria-label="Send AI message"><i class="bi bi-send-fill"></i></button>
      </div>
    </div>
  </div>

  @if(!empty($reviewMode))
  <!-- Button Section: Return / Approve (hidden when not in review mode) -->
  <div class="sv-toolbar-button-section sv-review-actions d-flex gap-2">
    <button type="button" class="btn review-revise-btn" id="svReturnBtn" aria-label="Return">
      <i class="bi bi-arrow-clockwise"></i> Return
    </button>
    <button type="button" class="btn review-approve-btn" id="svApproveBtn" aria-label="Approve">
      <i class="bi bi-check-circle"></i> Approve
    </button>
  </div>
  @endif
</section>
<!-- end toolbar -->
