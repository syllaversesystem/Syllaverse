{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/complete-profile.blade.php
* Description: Standalone professional Complete Profile with role requests (no dashboard layout) – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-10-24] Updated to include role selection functionality like admin complete profile
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
        <strong>Your Role Requests</strong>
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
                            \App\Models\ChairRequest::ROLE_DEPT => 'Department Head',
                            \App\Models\ChairRequest::ROLE_DEPT_HEAD => 'Department Head',
                            \App\Models\ChairRequest::ROLE_PROG => 'Program Chair',
                            \App\Models\ChairRequest::ROLE_VCAA => 'Vice Chancellor for Academic Affairs (VCAA)',
                            \App\Models\ChairRequest::ROLE_ASSOC_VCAA => 'Associate VCAA',
                            \App\Models\ChairRequest::ROLE_DEAN => 'Dean',
                            \App\Models\ChairRequest::ROLE_ASSOC_DEAN => 'Associate Dean',
                            \App\Models\ChairRequest::ROLE_FACULTY => 'Faculty',
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
                    @if ($r->status === 'pending')
                      <span class="badge bg-warning text-dark">Pending</span>
                    @elseif ($r->status === 'approved')
                      <span class="badge bg-success">Approved</span>
                    @else
                      <span class="badge bg-danger">{{ ucfirst($r->status) }}</span>
                    @endif
                  </td>
                  <td class="text-muted">{{ $r->created_at->format('M j, Y') }}</td>
                  <td class="text-muted">
                    {{ $r->decided_at ? $r->decided_at->format('M j, Y') : '—' }}
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    @endif
    {{-- ░░░ END: Your Requests Panel ░░░ --}}



    {{-- ░░░ START: Main Form (2-Step Wizard) ░░░ --}}
    <form method="POST" action="{{ route('faculty.submit-profile') }}" id="svCompleteProfileForm" class="needs-validation" novalidate>
        @csrf

        <div class="card shadow-sm">
          <div class="card-body">
            {{-- ░░░ START: Progress Steps ░░░ --}}
            <div class="d-flex align-items-center mb-4">
              <div class="d-flex align-items-center">
                <span class="sv-step sv-step-active" id="svStepBadge1">1</span>
                <span class="ms-2 fw-semibold text-dark">Profile & Employment</span>
              </div>
              
              {{-- Progress Line --}}
              <div class="flex-grow-1 mx-3">
                <div class="progress" style="height: 2px; background-color: #E3E3E3;">
                  <div class="progress-bar" role="progressbar" style="width: 50%; background-color: #CB3737;" id="svStepProgress"></div>
                </div>
              </div>
              
              <div class="d-flex align-items-center">
                <span class="sv-step sv-step-disabled" id="svStepBadge2">2</span>
                <span class="ms-2 text-muted" id="svStep2Label">Role Requests</span>
              </div>
            </div>
            {{-- ░░░ END: Progress Steps ░░░ --}}

          {{-- ░░░ START: Step 1 – Profile & Employment ░░░ --}}
          <section id="svStep1" class="sv-step-pane">
            <h6 class="text-muted mb-3">Basic Information</h6>
                <div class="row g-3">
                    {{-- Name --}}
                    <div class="col-md-6">
                        <label for="svName" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="svName" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="col-md-6">
                        <label for="svEmail" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="svEmail" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Designation --}}
                    <div class="col-md-6">
                        <label for="svDesignation" class="form-label">Designation <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('designation') is-invalid @enderror" id="svDesignation" name="designation" value="{{ old('designation', $user->designation) }}" required>
                        @error('designation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Employee Code --}}
                    <div class="col-md-6">
                        <label for="svEmployeeCode" class="form-label">Employee Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('employee_code') is-invalid @enderror" id="svEmployeeCode" name="employee_code" value="{{ old('employee_code', $user->employee_code) }}" required>
                        @error('employee_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

            <div class="d-flex justify-content-end mt-4">
              <button type="button" class="btn btn-danger" id="svNextToStep2" {{ $hasPendingRequests ? 'disabled' : '' }}>Next</button>
            </div>
          </section>
          {{-- ░░░ END: Step 1 – Profile & Employment ░░░ --}}

          {{-- ░░░ START: Step 2 – Role Requests ░░░ --}}
<section id="svStep2" class="sv-step-pane" hidden>
  @if ($hasPendingRequests)
    <div class="alert alert-info">
      You currently have a <strong>pending role request</strong>. You can update your profile fields above,
      but you'll need to wait for Superadmin to decide before submitting another request.
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
          <div class="col-md-6">
            <div class="form-check">
              <input class="form-check-input" 
                     type="checkbox" 
                     id="request_dept_head" 
                     name="request_dept_head" 
                     value="1" 
                     {{ old('request_dept_head') ? 'checked' : '' }}
                     {{ $hasPendingRequests ? 'disabled' : '' }}>
              <label class="form-check-label fw-semibold" for="request_dept_head">
                Department Chairperson
              </label>
              <div class="form-text small text-muted">Leads department operations and faculty</div>
            </div>
          </div>
          
          <div class="col-md-6">
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
              <div class="form-text small text-muted">Leads and oversees the department</div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
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
            <div class="form-text small text-muted">Assists the Dean in departmental operations</div>
          </div>
        </div>

        {{-- Department Selection (conditional) --}}
        <div class="mt-3" id="svDepartmentSelector" style="display: none;">
          <label for="svDepartmentId" class="form-label">Select Department <span class="text-danger">*</span></label>
          <select class="form-select @error('department_id') is-invalid @enderror" 
                  id="svDepartmentId" 
                  name="department_id"
                  {{ $hasPendingRequests ? 'disabled' : '' }}>
            <option value="">Choose a department...</option>
            @foreach ($departments as $dept)
              <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                {{ $dept->name }}
              </option>
            @endforeach
          </select>
          @error('department_id')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>

    {{-- Institution-Wide Leadership (hidden as per requirement) --}}
    {{--
    <div class="card border-0 bg-light mb-4" aria-hidden="true">
      <div class="card-body p-3">
        <h6 class="card-title text-dark mb-2">
          <i class="bi bi-globe me-2"></i>Institution-Wide Leadership
        </h6>
        <p class="text-muted small mb-3">These roles have authority across the entire institution</p>
        <div class="alert alert-secondary mb-0 py-2 px-3 small">This section has been disabled.</div>
      </div>
    </div>
    --}}

    {{-- Faculty Role --}}
    <div class="card border-0 bg-light mb-4">
      <div class="card-body p-3">
        <h6 class="card-title text-dark mb-2">
          <i class="bi bi-person-badge me-2"></i>Faculty Position
        </h6>
        <p class="text-muted small mb-3">Standard teaching and research position</p>
        
        <div class="alert alert-info py-2 px-3 mb-3" style="font-size: 0.875rem;">
          <i class="bi bi-info-circle me-1"></i>
          <strong>Note:</strong> If you select any leadership role above, faculty privileges are automatically included - no need to select this separately.
        </div>
        
        <div class="form-check mb-3">
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
          <div class="form-text small text-muted">Regular faculty position for teaching and research</div>
        </div>

        {{-- Faculty Department Selection (conditional) --}}
        <div class="mt-3" id="svFacultyDepartmentSelector" style="display: none;">
          <label for="svFacultyDepartmentId" class="form-label">Select Your Department <span class="text-danger">*</span></label>
          <select class="form-select @error('faculty_department_id') is-invalid @enderror" 
                  id="svFacultyDepartmentId" 
                  name="faculty_department_id"
                  {{ $hasPendingRequests ? 'disabled' : '' }}>
            <option value="">Choose your department...</option>
            @foreach ($departments as $dept)
              <option value="{{ $dept->id }}" {{ old('faculty_department_id') == $dept->id ? 'selected' : '' }}>
                {{ $dept->name }}
              </option>
            @endforeach
          </select>
          @error('faculty_department_id')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-between mt-4">
    <button type="button" class="btn btn-secondary" id="svBackToStep1">Back</button>
    <button type="submit" class="btn btn-danger" {{ $hasPendingRequests ? 'disabled' : '' }}>Complete Profile</button>
  </div>
</section>
{{-- ░░░ END: Step 2 – Role Requests ░░░ --}}

          </div>
        </div>
    </form>
    {{-- ░░░ END: Main Form ░░░ --}}
  </main>

  {{-- Page JS (stepper + filtering) --}}
  @vite('resources/js/faculty/complete-profile.js')
</body>
</html>
