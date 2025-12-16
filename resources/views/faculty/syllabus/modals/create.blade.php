{{-- 
------------------------------------------------
* File: resources/views/faculty/syllabus/modals/create.blade.php
* Description: Modal for creating a syllabus with metadata fields only – Syllaverse
------------------------------------------------ 
--}}

<div class="modal fade sv-faculty-syllabus-modal" id="selectSyllabusMetaModal" tabindex="-1" aria-labelledby="selectSyllabusMetaModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    @php $rp = $routePrefix ?? 'faculty.syllabi'; @endphp
    <form action="{{ route($rp . '.store') }}" method="POST" class="modal-content syllabus-form" style="border-radius: 16px;">
      @csrf

      {{-- Local styles (scoped to this modal) --}}
      <style>
        /* Brand tokens */
        #selectSyllabusMetaModal {
          --sv-bg:   #FAFAFA;
          --sv-bdr:  #E3E3E3;
          --sv-acct: #EE6F57;
          --sv-danger:#CB3737;
          /* Ensure modal stacks above backdrop and any page overlays */
          z-index: 2000 !important;
        }
        /* Ensure Bootstrap backdrop sits below our modal */
        .modal-backdrop.show { z-index: 1990 !important; }
        /* Extra safety: keep dialog above backdrop and surrounding UI */
        #selectSyllabusMetaModal .modal-dialog { z-index: 2001; }
        #selectSyllabusMetaModal .modal-header {
          border-bottom: 1px solid var(--sv-bdr);
          background: var(--sv-bg);
          padding: 0.85rem 1rem;
          position: sticky; /* keep icon + title always visible */
          top: 0;
          z-index: 20;
        }
        /* Shadow appears once body scrolls to differentiate header */
        #selectSyllabusMetaModal .modal-header.sv-stuck {
          box-shadow: 0 2px 6px rgba(0,0,0,.08);
        }
        #selectSyllabusMetaModal .modal-title {
          font-weight: 600;
          font-size: 1rem;
          display: inline-flex;
          align-items: center;
          gap: .5rem;
        }
        #selectSyllabusMetaModal .modal-title i,
        #selectSyllabusMetaModal .modal-title svg {
          width: 1.05rem;
          height: 1.05rem;
          stroke: var(--sv-text-muted, #777777);
        }
        #selectSyllabusMetaModal .sv-card {
          border: 1px solid var(--sv-bdr);
          background: #fff;
          border-radius: .75rem;
        }
        #selectSyllabusMetaModal .sv-section-title {
          font-size: .8rem;
          letter-spacing: .02em;
          color: #6c757d;
        }
        #selectSyllabusMetaModal .form-control,
        #selectSyllabusMetaModal .form-select {
          border-color: var(--sv-bdr);
          border-radius: 12px;
        }
        #selectSyllabusMetaModal .form-control:focus,
        #selectSyllabusMetaModal .form-select:focus {
          border-color: var(--sv-acct);
          box-shadow: 0 0 0 .2rem rgb(238 111 87 / 15%);
          outline: none;
        }
        #selectSyllabusMetaModal .form-label {
          font-weight: 500;
          color: var(--sv-text-muted, #777);
          margin-bottom: 0.5rem;
          font-size: 0.875rem;
        }
        /* Unified neutral action button (Next / Create / Update style) */
        #selectSyllabusMetaModal .btn-danger {
          background:#fff;
          border:none;
          color:#000;
          transition:all .2s ease-in-out;
          box-shadow:none;
          display:inline-flex;
          align-items:center;
          justify-content:center;
          gap:.5rem;
          padding:.65rem 1rem;
          border-radius:.375rem;
          font-weight:500;
        }
        #selectSyllabusMetaModal .btn-danger i,
        #selectSyllabusMetaModal .btn-danger svg { stroke:#000; }
        #selectSyllabusMetaModal .btn-danger:hover,
        #selectSyllabusMetaModal .btn-danger:focus {
          background:linear-gradient(135deg, rgba(235,235,235,.88), rgba(250,250,250,.46));
          box-shadow:0 4px 10px rgba(0,0,0,.10);
          color:#000;
        }
        #selectSyllabusMetaModal .btn-danger:hover i,
        #selectSyllabusMetaModal .btn-danger:hover svg,
        #selectSyllabusMetaModal .btn-danger:focus i,
        #selectSyllabusMetaModal .btn-danger:focus svg { stroke:#000; }
        /* Neutral utility buttons (Cancel / Back) */
        #selectSyllabusMetaModal .btn-light {
          background:#fff;
          border:none;
          color:#000;
          transition:all .2s ease-in-out;
          display:inline-flex;
          align-items:center;
          justify-content:center;
          gap:.5rem;
          padding:.65rem 1rem;
          border-radius:.375rem;
          font-weight:500;
        }
        #selectSyllabusMetaModal .btn-light i,
        #selectSyllabusMetaModal .btn-light svg { stroke:#000; }
        #selectSyllabusMetaModal .btn-light:hover,
        #selectSyllabusMetaModal .btn-light:focus {
          background:linear-gradient(135deg, rgba(225,225,225,.88), rgba(240,240,240,.46));
          box-shadow:0 4px 10px rgba(0,0,0,.08);
          color:#000;
        }
        #selectSyllabusMetaModal .btn-light:hover i,
        #selectSyllabusMetaModal .btn-light:hover svg,
        #selectSyllabusMetaModal .btn-light:focus i,
        #selectSyllabusMetaModal .btn-light:focus svg { stroke:#000; }
        /* Feedback */
        #selectSyllabusMetaModal .invalid-feedback {
          display: block;
          font-size: 0.875rem;
          color: #dc3545;
          margin-top: 0.25rem;
        }
        #selectSyllabusMetaModal .is-invalid {
          border-color: #dc3545;
        }
        #selectSyllabusMetaModal .recipient-options .form-check {
          padding: 1rem;
          border: 1px solid var(--sv-bdr);
          border-radius: 8px;
          transition: all 0.2s ease;
        }
        #selectSyllabusMetaModal .recipient-options .form-check:hover {
          background: #f8f9fa;
          border-color: var(--sv-acct);
        }
        #selectSyllabusMetaModal .recipient-options .form-check-input:checked + .form-check-label {
          color: var(--sv-danger);
        }
        #selectSyllabusMetaModal .recipient-options .form-check-input:checked ~ * {
          border-color: var(--sv-danger);
        }
        /* Remove old secondary styling – using .btn-light instead */

        /* Faculty Cards */
        .faculty-cards-container {
          display: grid;
          grid-template-columns: repeat(2, 1fr); /* each card 50% width */
          gap: 0.75rem;
          max-height: 400px;
          overflow-y: auto;
          padding: 0.5rem;
          border: 1px solid var(--sv-bdr);
          border-radius: 8px;
          background: #fafafa;
        }
        @media (max-width: 640px) {
          .faculty-cards-container { grid-template-columns: 1fr; }
        }
        .faculty-card {
          position: relative;
        }
        .faculty-checkbox {
          position: absolute;
          opacity: 0;
          pointer-events: none;
        }
        .faculty-card-label {
          display: flex;
          align-items: center;
          gap: 0.75rem;
          padding: 0.875rem;
          border: 2px solid var(--sv-bdr);
          border-radius: 8px;
          background: white;
          cursor: pointer;
          transition: all 0.2s ease;
          margin: 0;
        }
        .faculty-card-label:hover {
          border-color: var(--sv-acct);
          background: #fff5f5;
          transform: translateY(-2px);
          box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .faculty-checkbox:checked + .faculty-card-label {
          border-color: var(--sv-danger);
          background: linear-gradient(135deg, rgba(255, 235, 235, 0.5), rgba(255, 245, 245, 0.3));
        }
        .faculty-avatar {
          flex-shrink: 0;
          width: 40px;
          height: 40px;
          border-radius: 50%;
          background: linear-gradient(135deg, #f0f0f0, #e0e0e0);
          display: flex;
          align-items: center;
          justify-content: center;
          color: #666;
        }
        .faculty-checkbox:checked + .faculty-card-label .faculty-avatar {
          background: linear-gradient(135deg, var(--sv-danger), var(--sv-acct));
          color: white;
        }
        .faculty-info {
          flex: 1;
          min-width: 0;
        }
        .faculty-name {
          font-weight: 600;
          font-size: 0.9rem;
          color: #333;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
        }
        .faculty-code {
          font-size: 0.75rem;
          color: #666;
          margin-top: 0.125rem;
        }
        .faculty-designation {
          font-size: 0.7rem;
          color: #888;
          font-style: italic;
          margin-top: 0.125rem;
        }
        .faculty-check {
          flex-shrink: 0;
          width: 24px;
          height: 24px;
          border-radius: 50%;
          border: 2px solid var(--sv-bdr);
          display: flex;
          align-items: center;
          justify-content: center;
          color: transparent;
          transition: all 0.2s ease;
        }
        .faculty-checkbox:checked + .faculty-card-label .faculty-check {
          background: var(--sv-danger);
          border-color: var(--sv-danger);
          color: white;
        }
        .faculty-check svg {
          width: 14px;
          height: 14px;
        }
      </style>

      <div class="modal-header">
        <h5 class="modal-title" id="selectSyllabusMetaModalLabel">
          <i data-feather="file-plus"></i>
          Create Syllabus
        </h5>
        {{-- Removed top-right close button per design unification request --}}
      </div>

      <div class="modal-body">
        {{-- Phase 1: Select Recipients --}}
        <div id="phase1" class="sv-card p-3">
          <h6 class="sv-section-title mb-3">Who is this syllabus for?</h6>
          <div class="recipient-options">
            <div class="form-check mb-3">
              <input class="form-check-input" type="radio" name="recipient_type" id="recipient_myself" value="myself" checked>
              <label class="form-check-label" for="recipient_myself">
                <strong>For Myself</strong>
                <div class="text-muted small">I am the sole owner of this syllabus</div>
              </label>
            </div>
            <div class="form-check mb-3">
              <input class="form-check-input" type="radio" name="recipient_type" id="recipient_shared" value="shared">
              <label class="form-check-label" for="recipient_shared">
                <strong>For Myself and Others</strong>
                <div class="text-muted small">I will collaborate with other faculty members</div>
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="recipient_type" id="recipient_others" value="others">
              <label class="form-check-label" for="recipient_others">
                <strong>For Another Faculty</strong>
                <div class="text-muted small">Creating on behalf of someone else</div>
              </label>
            </div>
          </div>

          {{-- Faculty Selection (shown when shared or others is selected) --}}
          <div id="facultySelection" class="mt-3" style="display: none;">
            <label class="form-label">Select Faculty Members <span class="text-danger">*</span></label>
            <input type="text" id="facultySearchInput" class="form-control mb-3" placeholder="Search faculty by name or employee code...">
            
            <div id="facultyCardsContainer" class="faculty-cards-container">
              @php
                $currentUser = auth()->user();
                $currentDeptId = optional($currentUser?->appointments()
                  ->where('status','active')
                  ->first())->scope_id;

                $faculties = \App\Models\User::where('id', '!=', auth()->id())
                  ->whereHas('appointments', function($q) use ($currentDeptId){
                    $q->where('status','active');
                    if ($currentDeptId) { $q->where('scope_id', $currentDeptId); }
                  })
                  ->with(['appointments' => function($q) use ($currentDeptId){
                    $q->where('status','active');
                    if ($currentDeptId) { $q->where('scope_id', $currentDeptId); }
                  }])
                  ->orderBy('name')
                  ->get();
              @endphp
              @foreach($faculties as $faculty)
                <div class="faculty-card" 
                     data-faculty-id="{{ $faculty->id }}" 
                     data-name="{{ strtolower($faculty->name) }}" 
                     data-code="{{ strtolower($faculty->employee_code ?? '') }}">
                  <input type="checkbox" name="faculty_members[]" value="{{ $faculty->id }}" id="faculty_{{ $faculty->id }}" class="faculty-checkbox">
                  <label for="faculty_{{ $faculty->id }}" class="faculty-card-label">
                    <div class="faculty-avatar">
                      <i data-feather="user"></i>
                    </div>
                    <div class="faculty-info">
                      <div class="faculty-name">{{ $faculty->name }}</div>
                      <div class="faculty-code">{{ $faculty->employee_code ?? 'N/A' }}</div>
                      @if($faculty->designation)
                        <div class="faculty-designation">{{ $faculty->designation }}</div>
                      @endif
                    </div>
                    <div class="faculty-check">
                      <i data-feather="check"></i>
                    </div>
                  </label>
                </div>
              @endforeach
            </div>
            
            <small class="text-muted d-block mt-2">Click to select multiple faculty members</small>
          </div>
        </div>

        {{-- Phase 2: Syllabus Details --}}
        <div id="phase2" class="sv-card p-3" style="display: none;">
          <div class="row g-3">
            {{-- Syllabus Title removed as per new requirements --}}
            <div class="col-md-6">
              <label class="form-label">Program</label>
              <select name="program_id" class="form-select">
                <option value="">-- Select Program --</option>
                @foreach($programs as $program)
                  <option value="{{ $program->id }}">{{ $program->code }} - {{ $program->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Course <span class="text-danger">*</span></label>
              <select name="course_id" class="form-select" required>
                <option value="">-- Select Course --</option>
                @foreach($courses as $course)
                  <option value="{{ $course->id }}">{{ $course->code }} - {{ $course->title }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Academic Year <span class="text-danger">*</span></label>
              <select name="academic_year" class="form-select" required>
                <option value="">-- Select Academic Year --</option>
                @php
                  $currentYear = date('Y');
                  $startYear = $currentYear - 2;
                  $endYear = $currentYear + 3;
                  for ($year = $endYear; $year >= $startYear; $year--) {
                    $nextYear = $year + 1;
                    $ayLabel = "{$year}-{$nextYear}";
                    $selected = ($ayLabel === "{$currentYear}-" . ($currentYear + 1)) ? 'selected' : '';
                    echo "<option value=\"{$ayLabel}\" {$selected}>{$ayLabel}</option>";
                  }
                @endphp
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Semester <span class="text-danger">*</span></label>
              <select name="semester" class="form-select" required>
                <option value="">-- Select Semester --</option>
                <option value="1st Semester">1st Semester</option>
                <option value="2nd Semester">2nd Semester</option>
                <option value="Summer">Summer</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Year Level <span class="text-danger">*</span></label>
              <select name="year_level" class="form-select" required>
                <option value="">-- Select Year Level --</option>
                <option value="1st Year">1st Year</option>
                <option value="2nd Year">2nd Year</option>
                <option value="3rd Year">3rd Year</option>
                <option value="4th Year">4th Year</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <!-- Phase 1 Footer (dynamic) -->
        <div id="phase1Footer" class="d-flex gap-2 w-100">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="width:110px;">
            <i data-feather="x"></i>
            Cancel
          </button>
          <button type="button" id="nextPhaseBtn" class="btn btn-danger flex-grow-1">
            <i data-feather="arrow-right"></i>
            Next
          </button>
        </div>
        <!-- Phase 2 Footer (shown after Next) -->
        <div id="phase2Footer" class="d-none gap-2 w-100">
          <button type="button" id="backPhaseBtn" class="btn btn-light" style="width:110px;">
            <i data-feather="arrow-left"></i>
            Back
          </button>
          <button type="submit" id="createSyllabusBtn" class="btn btn-danger flex-grow-1">
            <i data-feather="check"></i>
            Create Syllabus
          </button>
        </div>
      </div>
      <script>
        // Phase toggle logic: ensure Cancel (phase1Footer) hidden during phase 2
        document.addEventListener('DOMContentLoaded', function() {
          // Ensure the modal lives under <body> to avoid stacking-context issues
          const createModalRoot = document.getElementById('selectSyllabusMetaModal');
          if (createModalRoot && createModalRoot.parentElement !== document.body) {
            document.body.appendChild(createModalRoot);
          }

          const nextBtn = document.getElementById('nextPhaseBtn');
          const backBtn = document.getElementById('backPhaseBtn');
          const phase1 = document.getElementById('phase1');
          const phase2 = document.getElementById('phase2');
          const phase1Footer = document.getElementById('phase1Footer');
          const phase2Footer = document.getElementById('phase2Footer');
          const modalBody = document.querySelector('#selectSyllabusMetaModal .modal-body');
          const modalHeader = document.querySelector('#selectSyllabusMetaModal .modal-header');
          if (nextBtn) {
            nextBtn.addEventListener('click', () => {
              if (phase1) phase1.style.display = 'none';
              if (phase2) phase2.style.display = 'block';
              if (phase1Footer) phase1Footer.classList.add('d-none');
              if (phase2Footer) phase2Footer.classList.remove('d-none');
            });
          }
          if (backBtn) {
            backBtn.addEventListener('click', () => {
              if (phase1) phase1.style.display = 'block';
              if (phase2) phase2.style.display = 'none';
              if (phase1Footer) phase1Footer.classList.remove('d-none');
              if (phase2Footer) phase2Footer.classList.add('d-none');
            });
          }
          // Add scroll listener to toggle header shadow for visual persistence
          if (modalBody && modalHeader) {
            modalBody.addEventListener('scroll', () => {
              if (modalBody.scrollTop > 4) {
                modalHeader.classList.add('sv-stuck');
              } else {
                modalHeader.classList.remove('sv-stuck');
              }
            });
          }
        });
      </script>
    </form>
  </div>
</div>
