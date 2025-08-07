<!-- {{-- 
-------------------------------------------------------------------------------
* File: resources/views/components/alert-overlay.blade.php
* Description: Floating alert overlay component with icons, color coding, and timer – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-08-06] Initial component creation – supports success, info, and error flash messages.
[2025-08-06] Modularized styles via alert-overlay.css; added feather icons and auto-timer.
-------------------------------------------------------------------------------
--}} -->

@vite('resources/css/components/alert-overlay.css')

@if(session('success'))
  <div class="alert-overlay">
    <div class="alert alert-overlay-style alert-success d-flex align-items-center gap-2" role="alert">
      <i data-feather="check-circle"></i>
      <div>{{ session('success') }}</div>
      <div class="loading-bar green"></div>
    </div>
  </div>
@elseif(session('info'))
  <div class="alert-overlay">
    <div class="alert alert-overlay-style alert-info d-flex align-items-center gap-2" role="alert">
      <i data-feather="info"></i>
      <div>{{ session('info') }}</div>
      <div class="loading-bar blue"></div>
    </div>
  </div>
@elseif($errors->any())
  <div class="alert-overlay">
    <div class="alert alert-overlay-style alert-error d-flex align-items-center gap-2" role="alert">
      <i data-feather="x-circle"></i>
      <div>{{ $errors->first() }}</div>
      <div class="loading-bar red"></div>
    </div>
  </div>
@endif
