@php(
  $inFinalApprovalMinimal = (!empty($fromApprovals) && !empty($submissionStatus) && $submissionStatus === 'final_approval')
)
<section class="syllabus-right-toolbar sv-review-panel" aria-label="Syllabus Review Panel">
  <div class="right-resize-handle" aria-hidden="true"></div>

  <!-- Header removed per request: selected partial title and color tag no longer shown -->

  @if(!$inFinalApprovalMinimal)
    <!-- Comment Section (default) -->
    <div class="sv-toolbar-comment-section">
      <div class="sv-review-comments" id="svReviewComments" aria-live="polite"></div>
    </div>
  @endif

  {{-- AI Chat Section removed --}}

  {{-- Mapping tools moved to main content above Assessment Mapping --}}

  @if(!empty($reviewMode))
  <!-- Button Section: Return / Approve (hidden when not in review mode) -->
  <div class="sv-toolbar-button-section sv-review-actions d-flex gap-2">
    @if(!$inFinalApprovalMinimal)
      <button type="button" class="btn review-revise-btn" id="svReturnBtn" aria-label="Return">
        <i class="bi bi-arrow-clockwise"></i> Return
      </button>
    @endif
    <button type="button" class="btn review-approve-btn" id="svApproveBtn" aria-label="Approve">
      <i class="bi bi-check-circle"></i> Approve
    </button>
  </div>
  @endif
</section>
<!-- end toolbar -->
