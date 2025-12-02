{{-- 
-------------------------------------------------------------------------------
* File: resources/views/components/alert-overlay.blade.php
* Description: Floating alert overlay (always-rendered container + flash-to-JS bridge) – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-08-06] Initial component creation – supports success, info, and error flash messages.
[2025-08-06] Modularized styles via alert-overlay.css; added feather icons and auto-timer.
[2025-08-11] Refactor – container always rendered; exposes flash via data-*; A11y aria-live; no inline JS.
[2025-08-11] Fix – removed Blade/PHP comment mixing to avoid syntax errors.
-------------------------------------------------------------------------------
--}}

@vite('resources/css/components/alert-overlay.css')
@vite('resources/js/components/alert-overlay.js')

@php
  // START: Flash/Errors Bridge
  $flashType = null;
  $flashMsg  = null;

  if (session('success')) {
    $flashType = 'success';
    $flashMsg  = (string) session('success');
  } elseif (session('info')) {
    $flashType = 'info';
    $flashMsg  = (string) session('info');
  } elseif ($errors->any()) {
    $flashType = 'error';
    $flashMsg  = (string) $errors->first();
  }

  // Map icon + loading bar color to existing CSS classes
  switch ($flashType) {
    case 'success':
      $iconName = 'check-circle';
      $barColor = 'green';
      break;
    case 'error':
      $iconName = 'x-circle';
      $barColor = 'red';
      break;
    case 'info':
    default:
      $iconName = 'info';
      $barColor = 'blue';
      break;
  }

  // Sanitize message for safe attribute usage (JS may read this)
  $flashMsgAttr = $flashMsg ? preg_replace('/\s+/u', ' ', $flashMsg) : null;
  // END: Flash/Errors Bridge
@endphp

<div
  id="svAlertOverlay"
  class="alert-overlay"
  aria-live="polite"
  aria-atomic="true"
  @if($flashType && $flashMsgAttr)
    data-flash-type="{{ $flashType }}"
    data-flash-message="{{ $flashMsgAttr }}"
  @endif
>
  @if($flashType && $flashMsg)
    <div class="alert alert-overlay-style alert-{{ $flashType }} d-flex align-items-center gap-2 show" role="alert">
      <i data-feather="{{ $iconName }}"></i>
      <div>{{ $flashMsg }}</div>
      <div class="loading-bar {{ $barColor }}"></div>
    </div>
  @endif
</div>
