{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/index.blade.php
* Description: Faculty • Syllabi – professional full-bleed board with refined cards
-------------------------------------------------------------------------------
📜 Log:
[2025-08-18] Pro redesign – tighter full-bleed layout; new “pro” card style (clear hierarchy, brand stripe, meta chips, refined actions).
-------------------------------------------------------------------------------
--}}

@extends('layouts.faculty')

@section('content')

  {{-- ░░░ START: Inline Styles (scoped to this page) ░░░ --}}
  <style>
    :root{
      --sv-bg: #FAFAFA;
      --sv-border: #E3E3E3;
      --sv-brand: #EE6F57;
      --sv-brand-dark:#CB3737;
      --sv-ink:#1f2937;
      --sv-muted:#6b7280;
    }

    /* Full-bleed wrapper (flush to edges & top) */
    .sv-fullbleed{margin:-1rem -1rem 0}

    /* Board header */
    .sv-board{
      background:#fff;border-left:0;border-right:0;border-top:0;border-bottom:1px solid var(--sv-border);
      padding:.85rem 1rem
    }
    .sv-title{margin:0;font-weight:800;font-size:1.15rem;letter-spacing:.2px;color:var(--sv-ink)}
    .sv-chip{display:inline-flex;align-items:center;gap:.4rem;padding:.28rem .55rem;border:1px solid var(--sv-border);
      border-radius:999px;font-size:.76rem;font-weight:600;background:#fff;color:#374151}
    .sv-chip .dot{width:8px;height:8px;border-radius:50%;background:var(--sv-brand)}
    .sv-head-actions .btn{border-radius:12px;font-weight:700;padding:.45rem .85rem}

    /* Pro Card */
    .sv-card{
      position:relative;display:flex;flex-direction:column;height:100%;
      background:#fff;border:1px solid var(--sv-border);border-radius:16px;overflow:hidden;
      transition:box-shadow .18s ease,transform .18s ease,border-color .18s ease
    }
    .sv-card:hover{transform:translateY(-2px);box-shadow:0 14px 28px rgba(0,0,0,.06);border-color:#d9d9d9}

    /* Brand edge */
    .sv-card::before{
      content:"";position:absolute;inset:0 auto 0 0;width:6px;
      background:linear-gradient(180deg,var(--sv-brand),var(--sv-brand-dark))
    }

    /* Card body layout */
    .sv-card-body{padding:.9rem .95rem .75rem .95rem}
    .sv-course-pill{
      display:inline-flex;align-items:center;gap:.4rem;padding:.22rem .55rem;
      border:1px solid var(--sv-border);border-radius:999px;background:#fff;
      font-size:.74rem;font-weight:800;text-transform:uppercase;color:#111827
    }
    .sv-title-lg{margin:.5rem 0 .25rem;font-size:1.06rem;line-height:1.25;font-weight:800;color:#111827}
    .sv-subtle{font-size:.86rem;color:var(--sv-muted)}

    /* Meta chips row */
    .sv-meta{display:flex;flex-wrap:wrap;gap:.35rem .5rem;margin-top:.35rem}
    .sv-meta .chip{
      display:inline-flex;align-items:center;gap:.35rem;padding:.22rem .5rem;border-radius:10px;
      border:1px solid var(--sv-border);background:#fff;font-size:.75rem;font-weight:600;color:#444
    }

    .sv-stamp{margin-top:.4rem;font-size:.75rem;color:#9aa0a6}

    /* Footer */
    .sv-card-footer{
      margin-top:auto;display:flex;justify-content:flex-end;gap:.5rem;
      padding:.7rem .95rem .9rem;border-top:1px dashed var(--sv-border);background:#fff
    }
    .sv-card-footer .btn{border-radius:10px;padding:.4rem .7rem;font-weight:700}

    /* Grid spacing */
    .sv-grid{row-gap:1rem}

    /* Empty */
    .sv-empty{
      background:var(--sv-bg);border-top:1px dashed var(--sv-border);border-bottom:1px dashed var(--sv-border);
      text-align:center;padding:1.4rem 1rem
    }
    .sv-empty .ico{width:52px;height:52px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;
      border:1px solid var(--sv-border);background:#fff;color:var(--sv-brand-dark);font-size:1.2rem;margin-bottom:.6rem}
  </style>
  {{-- ░░░ END: Inline Styles ░░░ --}}

  {{-- Create Syllabus Modal --}}
  @include('faculty.syllabus.modals.create')

  <div class="sv-fullbleed">

    {{-- ░░░ Header / Board ░░░ --}}
    <div class="sv-board">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex flex-column gap-1">
          <h2 class="sv-title">Syllabi</h2>
          <div class="d-flex flex-wrap gap-2">
            <span class="sv-chip"><span class="dot"></span> {{ $syllabi->count() }} total</span>
            <span class="sv-chip"><i class="bi bi-mortarboard"></i> {{ $syllabi->pluck('course_id')->filter()->unique()->count() }} courses</span>
            <span class="sv-chip">
              <i class="bi bi-clock-history"></i>
              Updated {{ optional($syllabi->max('updated_at')) ? \Carbon\Carbon::parse($syllabi->max('updated_at'))->diffForHumans() : '—' }}
            </span>
          </div>
        </div>
        <div class="sv-head-actions">
          <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#selectSyllabusMetaModal" aria-label="Create syllabus">
            <i class="bi bi-plus-lg me-1"></i> Create Syllabus
          </button>
        </div>
      </div>
    </div>

    {{-- ░░░ Alerts (full-bleed) ░░░ --}}
    @if (session('success'))
      <div class="alert alert-success alert-dismissible rounded-0 mb-0 fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif
    @if ($errors->any())
      <div class="alert alert-danger alert-dismissible rounded-0 mb-0 fade show" role="alert">
        {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    {{-- ░░░ Content ░░░ --}}
    <div class="container-fluid px-3 py-3">

      @if ($syllabi->isEmpty())
        <div class="sv-empty">
          <div class="ico"><i class="bi bi-journal-text"></i></div>
          <h5 class="fw-bold mb-1">No syllabi yet</h5>
          <p class="text-muted mb-3">Generate your first syllabus by selecting the course and term details.</p>
          <button type="button" class="btn btn-danger fw-semibold" data-bs-toggle="modal" data-bs-target="#selectSyllabusMetaModal">
            <i class="bi bi-plus-lg me-1"></i> Create Syllabus
          </button>
        </div>
      @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 sv-grid">
          @foreach ($syllabi as $syllabus)
            <div class="col">
              <article class="sv-card shadow-sm">

                <div class="sv-card-body">
                  {{-- Course badge + optional course title line --}}
                  <div class="d-flex align-items-center justify-content-between">
                    <span class="sv-course-pill">
                      <i class="bi bi-book"></i>{{ $syllabus->course->code ?? '—' }}
                    </span>
                  </div>

                  {{-- Title --}}
                  <h3 class="sv-title-lg">{{ $syllabus->title }}</h3>

                  {{-- Optional course title under the badge for context --}}
                  @if(!empty($syllabus->course?->title))
                    <div class="sv-subtle mb-1">{{ $syllabus->course->title }}</div>
                  @endif

                  {{-- Meta chips --}}
                  <div class="sv-meta">
                    <span class="chip"><i class="bi bi-calendar3"></i> AY {{ $syllabus->academic_year }}</span>
                    <span class="chip"><i class="bi bi-collection"></i> {{ $syllabus->semester }}</span>
                    <span class="chip"><i class="bi bi-people"></i> {{ $syllabus->year_level ?? '—' }}</span>
                  </div>

                  {{-- Stamp --}}
                  <div class="sv-stamp">Updated {{ optional($syllabus->updated_at)->diffForHumans() ?? '—' }}</div>
                </div>

                <div class="sv-card-footer">
                  <a href="{{ route('faculty.syllabi.show', $syllabus->id) }}"
                     class="btn btn-outline-primary btn-sm"
                     title="Open syllabus" aria-label="Open syllabus">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Open
                  </a>

                  <form action="{{ route('faculty.syllabi.destroy', $syllabus->id) }}"
                        method="POST"
                        onsubmit="return confirm('Delete this syllabus? This action cannot be undone.');">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="btn btn-outline-danger btn-sm"
                            title="Delete syllabus" aria-label="Delete syllabus">
                      <i class="bi bi-trash me-1"></i> Delete
                    </button>
                  </form>
                </div>
              </article>
            </div>
          @endforeach
        </div>
      @endif

    </div>
  </div>
@endsection
