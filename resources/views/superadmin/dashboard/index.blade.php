{{-- 
-------------------------------------------------------------------------------
* File: resources/views/superadmin/dashboard/index.blade.php
* Description: Super Admin Dashboard (blank scaffold)
-------------------------------------------------------------------------------
--}}

@extends('layouts.superadmin')

@section('title', 'Dashboard • Super Admin • Syllaverse')
@section('page-title', 'Dashboard')

@section('content')
  <div>
    @push('styles')
      @vite('resources/css/superadmin/dashboard.css')
    @endpush

    {{-- Stats Grid: 5 cards on large screens (CSS Grid for spanning) --}}
    <div class="sv-stats-grid">
      {{-- Entities --}}
      <div class="sv-grid-item">
        <div class="stat-card stat-danger sv-h-100 sv-p-3">
          <div class="sv-flex sv-ai-center sv-gap-2">
            <div class="stat-icon"><i class="bi bi-building"></i></div>
            <div>
              <div class="stat-label">Departments</div>
              <div class="stat-value">{{ number_format($stats['departments'] ?? 0) }}</div>
            </div>
          </div>
          <div class="stat-footer">
            <span class="sv-text-muted sv-small">Institutional units</span>
            @if (Route::has('superadmin.departments.index'))
              <a class="stat-link" href="{{ route('superadmin.departments.index') }}">Manage <i class="bi bi-arrow-right"></i></a>
            @else
              <span class="stat-link">Manage</span>
            @endif
          </div>
        </div>
      </div>

      <div class="sv-grid-item">
        <div class="stat-card stat-danger sv-h-100 sv-p-3">
          <div class="sv-flex sv-ai-center sv-gap-2">
            <div class="stat-icon"><i class="bi bi-diagram-3"></i></div>
            <div>
              <div class="stat-label">Programs</div>
              <div class="stat-value">{{ number_format($stats['programs'] ?? 0) }}</div>
            </div>
          </div>
          <div class="stat-footer">
            <span class="sv-text-muted sv-small">Academic offerings</span>
          </div>
        </div>
      </div>

      <div class="sv-grid-item">
        <div class="stat-card stat-danger sv-h-100 sv-p-3">
          <div class="sv-flex sv-ai-center sv-gap-2">
            <div class="stat-icon"><i class="bi bi-journal-bookmark"></i></div>
            <div>
              <div class="stat-label">Courses</div>
              <div class="stat-value">{{ number_format($stats['courses'] ?? 0) }}</div>
            </div>
          </div>
          <div class="stat-footer">
            <span class="sv-text-muted sv-small">Catalog entries</span>
          </div>
        </div>
      </div>

      <div class="sv-grid-item">
        <div class="stat-card stat-danger sv-h-100 sv-p-3">
          <div class="sv-flex sv-ai-center sv-gap-2">
            <div class="stat-icon"><i class="bi bi-person-badge"></i></div>
            <div>
              <div class="stat-label">Faculty</div>
              <div class="stat-value">{{ number_format($stats['faculty'] ?? 0) }}</div>
            </div>
          </div>
          <div class="stat-footer">
            <span class="sv-text-muted sv-small">Active members</span>
            @if (Route::has('superadmin.approved-accounts'))
              <a class="stat-link" href="{{ route('superadmin.approved-accounts') }}">Manage <i class="bi bi-arrow-right"></i></a>
            @elseif (Route::has('superadmin.manage-accounts'))
              <a class="stat-link" href="{{ route('superadmin.manage-accounts') }}">Manage <i class="bi bi-arrow-right"></i></a>
            @elseif (Route::has('superadmin.pending-accounts'))
              <a class="stat-link" href="{{ route('superadmin.pending-accounts') }}">Manage <i class="bi bi-arrow-right"></i></a>
            @else
              <span class="stat-link">Manage</span>
            @endif
          </div>
        </div>
      </div>

      {{-- Pending Accounts (rightmost on first row) --}}
      <div class="sv-grid-item">
        <div class="stat-card stat-warning sv-h-100 sv-p-3">
          <div class="sv-flex sv-ai-center sv-gap-2">
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
            <div>
              <div class="stat-label">Pending Accounts</div>
              <div class="stat-value">{{ number_format($stats['pending_accounts'] ?? 0) }}</div>
            </div>
          </div>
          <div class="stat-footer">
            <span class="sv-text-muted sv-small">Need approval</span>
            @if (Route::has('superadmin.pending-accounts'))
              <a class="stat-link" href="{{ route('superadmin.pending-accounts') }}">Manage <i class="bi bi-arrow-right"></i></a>
            @else
              <span class="stat-link">Manage</span>
            @endif
          </div>
        </div>
      </div>

      {{-- Spanning white card (2 cols wide, 2 rows tall starting row 2 on lg) --}}
      <div class="sv-grid-item sv-span-2x2">
        <div class="stat-card stat-plain sv-h-100 sv-p-3">
          <div class="sv-flex sv-ai-center sv-justify-between sv-mb-2">
            <div class="sv-flex sv-ai-center sv-gap-2">
              <div class="stat-icon" style="background:rgba(220,53,69,.10);color:#dc3545"><i class="bi bi-people"></i></div>
              <div>
                <div class="stat-label">Leadership Directory</div>
                <div class="sv-text-muted sv-small">Dept Heads & Chairs</div>
              </div>
            </div>
          </div>

            <div class="sv-flex sv-column" style="height:100%">
            <div class="sv-flex-grow sv-overflow-auto sv-table-container">
              <table class="sv-table sv-table--minimal sv-table--rows-tight sv-table--elevated sv-table--no-wrap sv-mb-0">
                <thead>
                  <tr>
                    <th class="sv-text-muted sv-small sv-col-name">Name</th>
                    <th class="sv-text-muted sv-small sv-col-role">Role</th>
                    <th class="sv-text-muted sv-small sv-col-dept">Department (Code)</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($leadership ?? [] as $row)
                    <tr>
                      <td class="sv-fw-semibold sv-col-name">{{ $row['name'] ?? '—' }}</td>
                      <td class="sv-text-muted sv-col-role">{{ $row['role'] ?? '—' }}</td>
                      <td class="sv-text-muted sv-col-dept">{{ $row['department'] ?? '—' }}</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="3" class="sv-text-center sv-text-muted sv-small">No leadership data</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      {{-- Accounts by Department graph (right-side card) --}}
      <div class="sv-grid-item sv-span-graph">
        <div class="stat-card stat-plain sv-h-100 sv-p-3">
          <div class="sv-flex sv-ai-center sv-justify-between sv-mb-2">
            <div class="sv-flex sv-ai-center sv-gap-2">
              <div class="stat-icon" style="background:rgba(220,53,69,.10);color:#dc3545"><i class="bi bi-bar-chart"></i></div>
              <div>
                <div class="stat-label">Accounts by Department</div>
                <div class="sv-text-muted sv-small">Distribution of users</div>
              </div>
            </div>
          </div>
          <div class="sv-flex-grow" style="height:100%">
            <canvas id="accountsByDeptChart"></canvas>
          </div>
        </div>
      </div>

      @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
          (function(){
            const el = document.getElementById('accountsByDeptChart');
            if(!el || !window.Chart) return;
            const data = @json($accountsByDept ?? []);
            const labels = data.map(d => d.department);
            const values = data.map(d => d.total);
            const ctx = el.getContext('2d');
            new Chart(ctx, {
              type: 'bar',
              data: {
                labels,
                datasets: [{
                  label: 'Accounts',
                  data: values,
                  backgroundColor: 'rgba(220,53,69,0.2)',
                  borderColor: '#dc3545',
                  borderWidth: 1,
                }]
              },
              options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                  x: { grid: { display: false } },
                  y: { grid: { color: 'rgba(0,0,0,0.05)' }, beginAtZero: true, ticks: { precision:0 } }
                }
              }
            });
          })();
        </script>
      @endpush

      {{-- Activity Overview: Syllabus Status by Department (full-width rows 5→6) --}}
      <div class="sv-grid-item sv-span-full-row-5 sv-row-4-tight">
        <div class="stat-card stat-plain sv-h-100 sv-p-3">
          <div class="sv-flex sv-ai-center sv-justify-between sv-mb-2">
            <div class="sv-flex sv-ai-center sv-gap-2">
              <div class="stat-icon" style="background:rgba(220,53,69,.10);color:#dc3545"><i class="bi bi-table"></i></div>
              <div>
                <div class="stat-label">Syllabus Status by Department</div>
                <div class="sv-text-muted sv-small">Draft • Pending • Reviewed • Status</div>
              </div>
            </div>
          </div>
          <div class="sv-flex sv-column" style="height:100%">
            <div class="sv-flex-grow sv-overflow-auto sv-table-container">
              <table class="sv-table sv-table--pro sv-table--rows-tight sv-table--no-wrap sv-mb-0">
                <thead>
                  <tr>
                    <th class="sv-text-muted sv-small">Department</th>
                    <th class="sv-text-muted sv-small">Draft</th>
                    <th class="sv-text-muted sv-small">Pending</th>
                    <th class="sv-text-muted sv-small">Reviewed</th>
                    <th class="sv-text-muted sv-small">Status</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse(($syllabusStatusByDept ?? []) as $row)
                    <tr>
                      <td class="sv-fw-semibold">{{ $row['department'] ?? '—' }}</td>
                      <td class="sv-text-muted">{{ number_format($row['draft'] ?? 0) }}</td>
                      <td class="sv-text-muted">{{ number_format($row['pending'] ?? 0) }}</td>
                      <td class="sv-text-muted">{{ number_format($row['reviewed'] ?? 0) }}</td>
                      @php
                        $d = (int)($row['draft'] ?? 0);
                        $p = (int)($row['pending'] ?? 0);
                        $r = (int)($row['reviewed'] ?? 0);
                        $fa = (int)($row['final_approved'] ?? 0);
                        $total = max($d + $p + $r + $fa, 1);
                        $pct = (int) floor(($fa / $total) * 100);
                        $cls = $pct >= 75 ? 'sv-progress--ok' : ($pct >= 40 ? 'sv-progress--warn' : '');
                      @endphp
                      <td>
                        <div class="sv-progress {{ $cls }}" title="{{ $pct }}% final approved">
                          <div class="sv-progress__bar" style="width: {{ $pct }}%"></div>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="5" class="sv-text-center sv-text-muted sv-small">No syllabus status data</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      {{-- Governance section removed (Chair Requests) --}}

      {{-- Syllabi Workflow removed --}}

      {{-- Academic Outcomes section removed as requested --}}

      {{-- Today’s Activity section removed --}}
    </div>
  </div>
@endsection
