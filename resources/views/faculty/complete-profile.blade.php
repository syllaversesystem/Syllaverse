{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/complete-profile.blade.php
* Description: Standalone professional Complete Profile (no dashboard layout) – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-10-18] Copied from admin complete profile and adapted for faculty users
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
    <div class="text-muted small">Faculty Profile Setup</div>
  </div>
</header>

  {{-- ░░░ END: Header ░░░ --}}

  <main class="container py-4 py-md-5">

    {{-- ░░░ START: Page Title + Alerts ░░░ --}}
    <div class="mb-3">
{{-- Page header text (replace the two lines) --}}
<h1 class="h4 mb-1">Complete Your Profile</h1>

    </div>


    {{-- ░░░ END: Page Title + Alerts ░░░ --}}

    @php
      $user->loadMissing(['chairRequests.department']);
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
                <th>#</th><th>Role</th><th>Department</th><th>Status</th><th>Submitted</th><th>Decision</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($user->chairRequests->sortByDesc('created_at') as $r)
                <tr>
                  <td>{{ $r->id }}</td>
                      <td>
                        @php
                          $roleLabel = match ($r->requested_role) {
                            \App\Models\ChairRequest::ROLE_DEPT => 'Department Chair',
                            \App\Models\ChairRequest::ROLE_PROG => 'Program Chair',
                            \App\Models\ChairRequest::ROLE_VCAA => 'Vice Chancellor for Academic Affairs (VCAA)',
                            \App\Models\ChairRequest::ROLE_ASSOC_VCAA => 'Associate VCAA',
                            \App\Models\ChairRequest::ROLE_DEAN => 'Dean',
                            \App\Models\ChairRequest::ROLE_ASSOC_DEAN => 'Associate Dean',
                            \App\Models\ChairRequest::ROLE_FACULTY => 'Faculty Member',
                            default => $r->requested_role,
                          };
                        @endphp
                        {{ $roleLabel }}
                      </td>
                  <td>
                    @if($r->department)
                      {{ $r->department->name }}
                    @else
                      <span class="text-muted">Institution-wide</span>
                    @endif
                  </td>
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
        <form action="{{ route('faculty.submit-profile') }}" method="POST" id="svCompleteProfileForm" novalidate>
          @csrf

          {{-- ░░░ START: Step 1 – Profile & Employment ░░░ --}}
          <section id="svStep1" class="sv-step-pane">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}">
              </div>
              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}">
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
          {{-- ░░░ START: Step 2 – Chair Role Request ░░░ --}}
<section id="svStep2" class="sv-step-pane" hidden>
  @if ($hasPendingRequests)
    <div class="alert alert-info">
      You currently have a <strong>pending chair request</strong>. You can update your profile fields above,
      but you'll need to wait for Admin to decide before submitting another request.
    </div>
  @endif

  {{-- Leadership Role Requests --}}
  <div class="mb-4">
    <h6 class="text-muted mb-3">Request Leadership Roles</h6>
    
    {{-- Department-Specific Leadership --}}
    <div class="card border-0 bg-light mb-4">
      <div class="card-body p-3">
        <h6 class="card-title text-dark mb-2">
          <i class="bi bi-building me-2"></i>Department-Specific Leadership
        </h6>
        <p class="text-muted small mb-3">These roles manage and oversee a specific department</p>
        
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <div class="form-check">
              <input class="form-check-input" 
                     type="checkbox" 
                     id="request_dept_chair" 
                     name="request_dept_chair" 
                     value="1" 
                     {{ old('request_dept_chair') ? 'checked' : '' }}
                     {{ $hasPendingRequests ? 'disabled' : '' }}>
              <label class="form-check-label fw-semibold" for="request_dept_chair">
                Department Chair
              </label>
              <div class="form-text small text-muted">Manages department operations and faculty</div>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="form-check">
              <input class="form-check-input" 
                     type="checkbox" 
                     id="request_dean" 
                     name="request_dean" 
                     value="1" 
                     {{ old('request_dean') ? 'checked' : '' }}
                     {{ $hasPendingRequests ? 'disabled' : '' }}>
              <label class="form-check-label fw-semibold" for="request_dean">
                Dean
              </label>
              <div class="form-text small text-muted">Leads department administration and strategy</div>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="form-check">
              <input class="form-check-input" 
                     type="checkbox" 
                     id="request_assoc_dean" 
                     name="request_assoc_dean" 
                     value="1" 
                     {{ old('request_assoc_dean') ? 'checked' : '' }}
                     {{ $hasPendingRequests ? 'disabled' : '' }}>
              <label class="form-check-label fw-semibold" for="request_assoc_dean">
                Associate Dean
              </label>
              <div class="form-text small text-muted">Assists Dean with department leadership</div>
            </div>
          </div>
        </div>

        <div class="mb-2">
          <label for="department_id" class="form-label small text-muted">Select Department</label>
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
          <div class="form-text small text-muted">Required when requesting Department Chair, Dean, or Associate Dean roles.</div>
        </div>
      </div>
    </div>

    {{-- Institution-Wide Leadership --}}
    <div class="card border-0 bg-light mb-4">
      <div class="card-body p-3">
        <h6 class="card-title text-dark mb-2">
          <i class="bi bi-mortarboard me-2"></i>Institution-Wide Leadership
        </h6>
        <p class="text-muted small mb-3">These roles oversee all departments across the entire institution</p>
        
        <div class="row g-3">
          <div class="col-md-6">
            <div class="form-check">
              <input class="form-check-input" 
                     type="checkbox" 
                     id="request_vcaa" 
                     name="request_vcaa" 
                     value="1" 
                     {{ old('request_vcaa') ? 'checked' : '' }}
                     {{ $hasPendingRequests ? 'disabled' : '' }}>
              <label class="form-check-label fw-semibold" for="request_vcaa">
                Vice Chancellor for Academic Affairs (VCAA)
              </label>
              <div class="form-text small text-muted">Oversees all academic programs and departments</div>
            </div>
          </div>
          
          <div class="col-md-6">
            <div class="form-check">
              <input class="form-check-input" 
                     type="checkbox" 
                     id="request_assoc_vcaa" 
                     name="request_assoc_vcaa" 
                     value="1" 
                     {{ old('request_assoc_vcaa') ? 'checked' : '' }}
                     {{ $hasPendingRequests ? 'disabled' : '' }}>
              <label class="form-check-label fw-semibold" for="request_assoc_vcaa">
                Associate VCAA
              </label>
              <div class="form-text small text-muted">Assists VCAA with institution-wide academic oversight</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Faculty Position --}}
    <div class="card border-0 bg-light">
      <div class="card-body p-3">
        <h6 class="card-title text-dark mb-2">
          <i class="bi bi-person-workspace me-2"></i>Faculty Position
        </h6>
        <p class="text-muted small mb-3">Standard faculty member without administrative responsibilities</p>
        
        <div class="row g-3 mb-3">
          <div class="col-md-12">
            <div class="form-check">
              <input class="form-check-input" 
                     type="checkbox" 
                     id="request_faculty" 
                     name="request_faculty" 
                     value="1" 
                     {{ old('request_faculty') ? 'checked' : '' }}
                     {{ $hasPendingRequests ? 'disabled' : '' }}>
              <label class="form-check-label fw-semibold" for="request_faculty">
                Faculty Member
              </label>
              <div class="form-text small text-muted">Teaching and research responsibilities within a specific department</div>
            </div>
          </div>
        </div>

        <div class="mb-2">
          <label for="faculty_department_id" class="form-label small text-muted">Select Your Department</label>
          <select id="faculty_department_id"
                  name="faculty_department_id"
                  class="form-select @error('faculty_department_id') is-invalid @enderror"
                  {{ $hasPendingRequests ? 'disabled' : '' }}>
            <option value="">— Select Department —</option>
            @foreach (($departments ?? []) as $dept)
              <option value="{{ $dept->id }}"
                {{ (string) old('faculty_department_id') === (string) $dept->id ? 'selected' : '' }}>
                {{ $dept->name }}
              </option>
            @endforeach
          </select>
          @error('faculty_department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
          <div class="form-text small text-muted">Required when requesting Faculty Member position.</div>
        </div>
      </div>
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
  @vite('resources/js/faculty/complete-profile.js')
</body>
</html>
