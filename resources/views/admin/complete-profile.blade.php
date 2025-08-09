{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/complete-profile.blade.php
* Description: Standalone professional Complete Profile (no dashboard layout) – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-08-08] Rewrote as a full page (no @extends). Keeps two-step wizard + chair request UI.
-------------------------------------------------------------------------------
--}}

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Complete Profile • Syllaverse</title>
  <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png" />

  {{-- Bootstrap & Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />

  {{-- ░░░ START: Minimal page styles (palette-aware) ░░░ --}}
  <style>
    body { background:#FAFAFA; }
    .sv-step { width:32px;height:32px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;background:#E3E3E3;color:#333;font-weight:600; }
    .sv-step-active { background:#CB3737;color:#fff; }
    .sv-step-disabled { background:#E3E3E3;color:#aaa; }
    .sv-step-pane[hidden]{ display:none!important; }
  </style>
  {{-- ░░░ END: Minimal page styles ░░░ --}}
</head>
<body>

  {{-- ░░░ START: Header (no sidebar) ░░░ --}}
<header class="bg-white border-bottom">
  <div class="container d-flex align-items-center justify-content-between py-3">
    <div class="d-flex align-items-center">
      <img src="{{ asset('images/syllaverse-text-logo.png') }}" alt="Syllaverse" height="28">
    </div>
    <div class="text-muted small">Admin Profile Setup</div>
  </div>
</header>

  {{-- ░░░ END: Header ░░░ --}}

  <main class="container py-4 py-md-5">

    {{-- ░░░ START: Page Title + Alerts ░░░ --}}
    <div class="mb-3">
{{-- Page header text (replace the two lines) --}}
<h1 class="h4 mb-1">Complete Your Profile</h1>
<p class="text-muted mb-0">Tell us who you are and request your chair role (required). Your designation and employee code will appear on generated syllabi/exports.</p>

    </div>

    @if (session('info'))
      <div class="alert alert-warning">{{ session('info') }}</div>
    @endif
    @if ($errors->any())
      <div class="alert alert-danger">
        <strong>We found some issues:</strong>
        <ul class="mb-0">
          @foreach ($errors->all() as $err)
            <li>{{ $err }}</li>
          @endforeach
        </ul>
      </div>
    @endif
    {{-- ░░░ END: Page Title + Alerts ░░░ --}}

    @php
      $user->loadMissing(['chairRequests.program', 'chairRequests.department']);
      $pendingRequests = $user->chairRequests->where('status', 'pending');
      $hasPendingRequests = $pendingRequests->isNotEmpty();
    @endphp

    {{-- ░░░ START: Your Requests Panel (if any) ░░░ --}}
    @if ($user->chairRequests->count() > 0)
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-light d-flex justify-content-between">
        <strong>Your Chair Requests</strong>
        <span class="badge {{ $hasPendingRequests ? 'bg-primary' : 'bg-secondary' }}">
          {{ $user->chairRequests->count() }} total
        </span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>#</th><th>Role</th><th>Department</th><th>Program</th><th>Status</th><th>Submitted</th><th>Decision</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($user->chairRequests->sortByDesc('created_at') as $r)
                <tr>
                  <td>{{ $r->id }}</td>
                  <td>{{ $r->requested_role === \App\Models\ChairRequest::ROLE_DEPT ? 'Department Chair' : 'Program Chair' }}</td>
                  <td>{{ $r->department->name ?? '—' }}</td>
                  <td>{{ $r->program->name ?? '—' }}</td>
                  <td>
                    @switch($r->status)
                      @case('pending')  <span class="badge bg-primary">Pending</span> @break
                      @case('approved') <span class="badge bg-success">Approved</span> @break
                      @case('rejected') <span class="badge bg-danger">Rejected</span> @break
                      @default <span class="badge bg-secondary">{{ ucfirst($r->status) }}</span>
                    @endswitch
                  </td>
                  <td>{{ optional($r->created_at)->format('Y-m-d H:i') ?? '—' }}</td>
                  <td>{{ optional($r->decided_at)->format('Y-m-d H:i') ?? '—' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    @endif
    {{-- ░░░ END: Your Requests Panel ░░░ --}}

    {{-- ░░░ START: Wizard Card ░░░ --}}
    <div class="card shadow-sm">
      <div class="card-header bg-white">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="d-flex align-items-center gap-3">
            <div class="sv-step sv-step-active" data-step-label="1">1</div>
            <div class="small fw-semibold">Profile &amp; Employment</div>
          </div>
          <div class="flex-grow-1 mx-3 d-none d-md-block">
            <div class="progress" style="height:6px;"><div class="progress-bar bg-danger" id="svStepProgress" style="width:50%;"></div></div>
          </div>
          <div class="d-flex align-items-center gap-3">
            <div class="sv-step {{ $hasPendingRequests ? 'sv-step-disabled' : '' }}" data-step-label="2">2</div>
            <div class="small fw-semibold">Chair Role Request</div>
          </div>
        </div>
      </div>

      <div class="card-body">
        <form action="{{ route('admin.submit-profile') }}" method="POST" id="svCompleteProfileForm" novalidate>
          @csrf

          {{-- ░░░ START: Step 1 – Profile & Employment ░░░ --}}
          <section id="svStep1" class="sv-step-pane">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" value="{{ $user->name }}" disabled>
              </div>
              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" value="{{ $user->email }}" disabled>
              </div>

              <div class="col-md-6">
                <label for="designation" class="form-label">Designation <span class="text-danger">*</span></label>
                
                <input type="text" name="designation" id="designation"
                       class="form-control @error('designation') is-invalid @enderror"
                       value="{{ old('designation', $user->designation) }}"
                       placeholder="e.g., Professor IV" required>
                @error('designation') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="col-md-6">
                <label for="employee_code" class="form-label">Employee Code <span class="text-danger">*</span></label>
                <input type="text" name="employee_code" id="employee_code"
                       class="form-control @error('employee_code') is-invalid @enderror"
                       value="{{ old('employee_code', $user->employee_code) }}"
                       placeholder="e.g., 2025-XXXX" required>
                @error('employee_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
              <button type="button" class="btn btn-danger" id="svNextToStep2" {{ $hasPendingRequests ? 'disabled' : '' }}>Next</button>
            </div>
          </section>
          {{-- ░░░ END: Step 1 – Profile & Employment ░░░ --}}

          {{-- ░░░ START: Step 2 – Chair Role Request ░░░ --}}
<section id="svStep2" class="sv-step-pane" hidden>
  @if ($hasPendingRequests)
    <div class="alert alert-info">
      You currently have a <strong>pending chair request</strong>. You can update your profile fields above,
      but you’ll need to wait for Superadmin to decide before submitting another request.
    </div>
  @endif

  <div class="row g-3">
    {{-- Department Chair --}}
    <div class="col-md-6">
      <label for="department_id" class="form-label d-flex align-items-center gap-2">
        <input class="form-check-input m-0"
               type="checkbox"
               id="request_dept_chair"
               name="request_dept_chair"
               value="1"
               {{ old('request_dept_chair') ? 'checked' : '' }}
               {{ $hasPendingRequests ? 'disabled' : '' }}>
        <span class="fw-semibold">Department Chair</span>
      </label>

      <select id="department_id"
              name="department_id"
              class="form-select @error('department_id') is-invalid @enderror"
              {{ $hasPendingRequests ? 'disabled' : '' }}>
        <option value="">— Select Department —</option>
        @foreach (($departments ?? []) as $dept)
          <option value="{{ $dept->id }}"
            {{ (string) old('department_id') === (string) $dept->id ? 'selected' : '' }}>
            {{ $dept->name }}
          </option>
        @endforeach
      </select>
      @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Program Chair --}}
    <div class="col-md-6">
      <label for="program_id" class="form-label d-flex align-items-center gap-2">
        <input class="form-check-input m-0"
               type="checkbox"
               id="request_prog_chair"
               name="request_prog_chair"
               value="1"
               {{ old('request_prog_chair') ? 'checked' : '' }}
               {{ $hasPendingRequests ? 'disabled' : '' }}>
        <span class="fw-semibold">Program Chair</span>
      </label>

      <select id="program_id"
              name="program_id"
              class="form-select @error('program_id') is-invalid @enderror"
              {{ old('request_prog_chair') && !$hasPendingRequests ? '' : 'disabled' }}>
        <option value="">— Select Program —</option>
        @foreach (($programs ?? []) as $prog)
          <option value="{{ $prog->id }}"
                  data-dept="{{ $prog->department_id }}"
                  {{ (string) old('program_id') === (string) $prog->id ? 'selected' : '' }}>
            {{ $prog->name }}
          </option>
        @endforeach
      </select>
      @error('program_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
  </div>

  <div class="d-flex justify-content-between mt-4">
    <button type="button" class="btn btn-outline-secondary" id="svBackToStep1">Back</button>
    <button type="submit" class="btn btn-danger" {{ $hasPendingRequests ? 'disabled' : '' }}>Submit</button>
  </div>
</section>
{{-- ░░░ END: Step 2 – Chair Role Request ░░░ --}}

        </form>
      </div>
    </div>
    {{-- ░░░ END: Wizard Card ░░░ --}}
  </main>

  {{-- Page JS (stepper + filtering) --}}
  @vite('resources/js/admin/complete-profile.js')
</body>
</html>
