@extends('layouts.faculty')
@section('page-title','Syllabi')
@section('content')
  @push('styles')
    @vite('resources/css/faculty/syllabus-index.css')
  @endpush

  @includeIf('faculty.syllabus.modals.create')
  @includeIf('faculty.syllabus.modals.submit')

  <div class="svx-fullbleed">
    <div class="container-fluid px-3 py-3">
      <!-- Toolbar card: search + add button (parity with other modules) -->
      <div class="svx-card mb-3">
        <div class="svx-card-body">
          <div class="programs-toolbar mb-0" id="syllabiToolbar">
            <div class="input-group">
              <span class="input-group-text" id="syllabiSearchIcon"><i data-feather="search"></i></span>
              <input type="search" class="form-control" id="syllabiSearch" placeholder="Search syllabi..." aria-label="Search syllabi" />
            </div>
            <span class="flex-spacer"></span>
            <button type="button"
                    class="btn programs-add-btn d-none d-md-inline-flex"
                    data-bs-toggle="modal"
                    data-bs-target="#selectSyllabusMetaModal"
                    title="Create Syllabus"
                    aria-label="Create Syllabus">
              <i data-feather="plus"></i>
            </button>
          </div>
        </div>
      </div>

      @if($syllabi->isEmpty())
        <div class="svx-empty text-center py-5">
          <div class="mb-3"><i class="bi bi-journal-text" style="font-size:2.5rem; opacity:.45;"></i></div>
          <h6 class="fw-bold mb-1">No syllabi yet</h6>
          <p class="text-muted mb-3">Click the <i data-feather="plus" style="width: 1rem; height: 1rem; vertical-align: -0.125em;"></i> button to create your first syllabus.</p>
        </div>
      @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3" id="syllabiGrid">
          @foreach($syllabi as $syllabus)
            <div class="col">
              <article class="svx-card shadow-sm h-100 d-flex flex-column syllabus-card" tabindex="0" role="button"
                       data-syllabus-id="{{ $syllabus->id }}"
                       data-open-url="{{ route('faculty.syllabi.show',$syllabus->id) }}"
                       aria-label="Open syllabus {{ $syllabus->title }}">
                <div class="svx-card-body flex-grow-1">
                  <div class="d-flex align-items-center justify-content-between mb-1 small text-muted">
                    <span class="svx-course-pill"><i class="bi bi-book"></i> {{ $syllabus->course->code ?? '-' }}</span>
                    @php
                      $submissionStatus = $syllabus->submission_status ?? 'draft';
                      $statusConfig = [
                        'draft' => ['label' => 'Draft', 'icon' => 'bi-pencil', 'class' => 'bg-secondary'],
                        'pending_review' => ['label' => 'Pending Review', 'icon' => 'bi-clock-history', 'class' => 'bg-warning text-dark'],
                        // Show "Returned" with yellow background and black font
                        'revision' => ['label' => 'Returned', 'icon' => 'bi-arrow-clockwise', 'class' => 'bg-warning text-dark'],
                        'approved' => ['label' => 'Reviewed', 'icon' => 'bi-check-circle', 'class' => 'bg-success'],
                        'final_approval' => ['label' => 'Final Approval', 'icon' => 'bi-award', 'class' => 'bg-primary'],
                      ];
                      $status = $statusConfig[$submissionStatus] ?? $statusConfig['draft'];
                    @endphp
                    <span class="badge {{ $status['class'] }} submission-status-badge-small">
                      <i class="bi {{ $status['icon'] }}"></i>
                      {{ $status['label'] }}
                    </span>
                  </div>
                  <h6 class="fw-semibold mb-0 syllabus-title">{{ $syllabus->title }}</h6>
                  @if(!empty($syllabus->course?->title))
                    <div class="text-muted small syllabus-course-title">{{ $syllabus->course->title }}</div>
                  @endif
                  <div class="svx-meta mt-2 small">
                    <span class="chip"><i class="bi bi-calendar3"></i> AY {{ $syllabus->academic_year }}</span>
                    <span class="chip"><i class="bi bi-collection"></i> {{ $syllabus->semester }}</span>
                    <span class="chip"><i class="bi bi-people"></i> {{ $syllabus->year_level ?? '-' }}</span>
                  </div>
                  
                </div>
                <div class="svx-card-footer d-flex justify-content-between align-items-center gap-2">
                  @php
                    // Determine button text and behavior based on status
                    $canSubmit = in_array($submissionStatus, ['draft', 'revision']);
                    $canSubmitApproval = $submissionStatus === 'approved';
                    $isPending = $submissionStatus === 'pending_review';
                    $isFinalApproval = $submissionStatus === 'final_approval';

                    // Default to "Submit" icon/text
                    $buttonText = 'Submit';
                    $buttonIcon = 'bi-send';
                    $buttonDisabled = false;

                    if ($canSubmitApproval) {
                      // After review approved, allow final approval submission
                      $buttonText = 'Submit for Approval';
                      $buttonIcon = 'bi-check-circle';
                    } elseif ($isPending) {
                      // Keep text as "Submit" but disable interaction while pending review
                      $buttonDisabled = true;
                    } elseif ($isFinalApproval) {
                      // Final approval state: keep text as "Submit" but disabled
                      $buttonDisabled = true;
                    }
                  @endphp
                  
                  <button type="button" 
                          class="btn btn-sm btn-outline-primary submission-action-btn"
                          data-bs-toggle="modal" 
                          data-bs-target="#submitSyllabusModal"
                          data-syllabus-id="{{ $syllabus->id }}"
                          data-status="{{ $submissionStatus }}"
                          data-department-id="{{ $syllabus->program->department_id ?? '' }}"
                          data-program-id="{{ $syllabus->program->id ?? '' }}"
                          {{ $buttonDisabled ? 'disabled' : '' }}>
                    <i class="bi {{ $buttonIcon }}"></i> {{ $buttonText }}
                  </button>
                  
                  @if($submissionStatus === 'pending_review')
                    <button type="button" class="btn btn-outline-danger btn-sm" disabled title="Cannot delete while pending review" aria-label="Delete disabled (pending review)">
                      <i class="bi bi-trash"></i>
                    </button>
                  @else
                  <form action="{{ route('faculty.syllabi.destroy',$syllabus->id) }}" method="POST" onsubmit="return confirm('Delete this syllabus? This action cannot be undone.');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm" aria-label="Delete syllabus"><i class="bi bi-trash"></i></button>
                  </form>
                  @endif
                </div>
              </article>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>
@endsection

@push('styles')
<style>
  .programs-toolbar { display:flex; align-items:center; flex-wrap:wrap; gap:.25rem; margin-bottom:1.0rem; }
  .programs-toolbar .input-group {
    flex:1; max-width: 420px;
    background: var(--sv-bg, #FAFAFA);
    border: 1px solid var(--sv-border, #E3E3E3);
    border-radius: 6px; overflow: hidden;
    box-shadow: 0 1px 2px rgba(0,0,0,0.02);
  }
  .programs-toolbar .input-group .form-control { padding:.4rem .75rem; font-size:.88rem; border:none; background:transparent; height:2.2rem; }
  .programs-toolbar .input-group .form-control::placeholder { color: var(--sv-text-muted, #666); }
  .programs-toolbar .input-group .form-control:focus { outline:none; box-shadow:none; background:transparent; }
  .programs-toolbar .input-group .input-group-text { background:transparent; border:none; padding-left:.7rem; padding-right:.4rem; display:flex; align-items:center; }
  .programs-toolbar .input-group-text i, .programs-toolbar .input-group-text svg { width:.95rem !important; height:.95rem !important; }

  .programs-add-btn { padding:0; width:2.75rem; height:2.75rem; min-width:2.75rem; min-height:2.75rem; border-radius:50%; display:inline-flex; justify-content:center; align-items:center; background: var(--sv-card-bg,#f8f9fa); border:none; transition:all .2s ease-in-out; box-shadow:none; color:#000; }
  .programs-add-btn i, .programs-add-btn svg { width:1.25rem; height:1.25rem; }
  .programs-add-btn:hover, .programs-add-btn:focus { background:linear-gradient(135deg, rgba(255,240,235,.88), rgba(255,255,255,.46)); backdrop-filter:blur(7px); -webkit-backdrop-filter:blur(7px); box-shadow:0 4px 10px rgba(204,55,55,.12); color:#CB3737; }

  @media (max-width: 768px) {
    .programs-toolbar { gap:.5rem; }
    .programs-toolbar .input-group { max-width:100%; }
  }

  /* Make syllabus cards feel clickable */
  .syllabus-card { 
    cursor: pointer;
    transition: all 0.3s ease;
    border: 1px solid #e0e0e0;
  }
  .syllabus-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15) !important;
    border-color: #CB3737;
  }
  .syllabus-card:hover .syllabus-title {
    color: #CB3737;
  }
  .syllabus-card:focus { 
    outline: 2px solid rgba(203,55,55,.45); 
    outline-offset: 2px; 
  }
  .syllabus-card:active {
    transform: translateY(-2px);
  }

  /* Submission status badge (small version in card header) */
  .submission-status-badge-small {
    font-size: 0.65rem;
    padding: 0.25rem 0.5rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    border-radius: 4px;
  }
  .submission-status-badge-small i {
    font-size: 0.7rem;
  }

  /* Submission action button matching create modal style */
  .submission-action-btn {
    background: var(--sv-card-bg, #fff);
    border: 1px solid #E3E3E3;
    color: #CB3737;
    transition: all 0.2s ease-in-out;
    box-shadow: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 0.875rem;
    border-radius: 0.375rem;
    font-weight: 500;
    font-size: 0.875rem;
  }
  .submission-action-btn:hover,
  .submission-action-btn:focus {
    background: linear-gradient(135deg, rgba(255, 235, 235, 0.88), rgba(255, 245, 245, 0.46));
    backdrop-filter: blur(7px);
    -webkit-backdrop-filter: blur(7px);
    box-shadow: 0 4px 10px rgba(203, 55, 55, 0.15);
    color: #CB3737;
    border-color: #CB3737;
    transform: translateY(-1px);
  }
  .submission-action-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    background: #f8f9fa;
    color: #6c757d;
    border-color: #E3E3E3;
    pointer-events: none; /* ensure not clickable when disabled */
  }
  .submission-action-btn:disabled:hover {
    transform: none;
    box-shadow: none;
    background: #f8f9fa;
  }
</style>
@endpush

@push('scripts')
@vite('resources/js/faculty/syllabus-create.js')
<script>
  document.addEventListener('DOMContentLoaded', function(){
    const search = document.getElementById('syllabiSearch');
    const grid = document.getElementById('syllabiGrid');
    if (!search || !grid) return;
    const cards = () => Array.from(grid.querySelectorAll('.col'));
    function matches(txt, q){
      return txt.toLowerCase().indexOf(q) !== -1;
    }
    search.addEventListener('input', function(){
      const q = (search.value || '').trim().toLowerCase();
      cards().forEach(col => {
        const card = col.querySelector('.svx-card');
        const title = col.querySelector('.syllabus-title')?.textContent || '';
        const courseTitle = col.querySelector('.syllabus-course-title')?.textContent || '';
        const courseCode = col.querySelector('.svx-course-pill')?.textContent || '';
        const text = [title, courseTitle, courseCode].join(' \u2022 ');
        col.style.display = q ? (matches(text, q) ? '' : 'none') : '';
      });
    });

    // Make entire card clickable to open syllabus; respect clicks on buttons/links/forms
    const gridEl = document.getElementById('syllabiGrid');
    if (gridEl) {
      gridEl.addEventListener('click', function(ev){
        const target = ev.target;
        if (!target) return;
        // Ignore clicks on interactive controls within the card footer
        if (target.closest('a, button, form')) return;
        const card = target.closest('.syllabus-card');
        if (!card) return;
        const url = card.getAttribute('data-open-url');
        if (url) { window.location.href = url; }
      });
      // Keyboard support (Enter/Space)
      gridEl.addEventListener('keydown', function(ev){
        const card = ev.target.closest && ev.target.closest('.syllabus-card');
        if (!card) return;
        if (ev.key === 'Enter' || ev.key === ' ') {
          ev.preventDefault();
          const url = card.getAttribute('data-open-url');
          if (url) { window.location.href = url; }
        }
      });
    }
  });
</script>
@endpush





