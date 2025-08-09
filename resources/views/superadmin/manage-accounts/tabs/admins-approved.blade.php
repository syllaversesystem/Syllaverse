{{-- 
-------------------------------------------------------------------------------
* File: resources/views/superadmin/manage-accounts/tabs/admins-approved.blade.php
* Description: Approved Admins tab with per-admin “Manage” modal (modular, stacked outside table) – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-08-08] Reworked UI: list active appointments; allow Add/Edit/End (inline logic).
[2025-08-09] Modularized: moved modal into manage-accounts/modals/manage-admin.blade.php and included per row.
[2025-08-09] Fix: push modals to a Blade stack so they render outside <table>; add z-index overrides for proper layering.
-------------------------------------------------------------------------------
--}}

<div class="tab-pane fade" id="admins-approved" role="tabpanel">
  {{-- ░░░ START: Header & Search ░░░ --}}
  <div class="card border-0 shadow-sm p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
      <div>
        <h5 class="mb-0">Approved Admins</h5>
        <div class="text-muted small">
          Manage <strong>chair appointments</strong> per admin (Department/Program). One active chair per scope.
        </div>
      </div>
      <div class="input-group" style="max-width: 320px;">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="search" class="form-control" placeholder="Search admins..." aria-label="Search admins"
               oninput="svFilterApprovedAdmins(this.value)">
      </div>
    </div>
  {{-- ░░░ END: Header & Search ░░░ --}}

    @php
      // Avoid N+1 when showing active appointments
      if (method_exists($approvedAdmins, 'load')) {
        $approvedAdmins->loadMissing('appointments');
      }
      // Quick maps for nicer labels in badge lists
      $deptById = collect($departments ?? [])->keyBy('id');
      $progById = collect($programs ?? [])->keyBy('id');
    @endphp

    {{-- ░░░ START: Table ░░░ --}}
    <div class="table-responsive">
      <table class="table table-hover align-middle" id="svApprovedAdminsTable">
        <thead class="table-light">
          <tr>
            <th style="width: 26%;">Name</th>
            <th style="width: 28%;">Email</th>
            <th>Active Appointments</th>
            <th style="width: 120px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($approvedAdmins as $admin)
            @php
              $activeAppointments = $admin->appointments ? $admin->appointments->where('status','active') : collect();
            @endphp

            <tr>
              <td class="fw-semibold">{{ $admin->name }}</td>
              <td class="text-muted">{{ $admin->email }}</td>

              <td>
                @if ($activeAppointments->count())
                  <div class="d-flex flex-wrap gap-2">
                    @foreach ($activeAppointments as $appt)
                      @php
                        $isDept     = $appt->role === \App\Models\Appointment::ROLE_DEPT;
                        $roleLabel  = $isDept ? 'Dept Chair' : 'Program Chair';
                        $scopeLabel = $isDept
                          ? ($deptById[$appt->scope_id]->name ?? ('Dept #'.$appt->scope_id))
                          : ($progById[$appt->scope_id]->name ?? ('Prog #'.$appt->scope_id));
                      @endphp
                      <span class="badge bg-secondary">{{ $roleLabel }} — {{ $scopeLabel }}</span>
                    @endforeach
                  </div>
                @else
                  <span class="text-muted">No active appointment.</span>
                @endif
              </td>

              <td>
                <button class="btn btn-sm btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#manageAdmin-{{ $admin->id }}">
                  Manage
                </button>
              </td>
            </tr>

            {{-- ░░░ START: Push modal to stack (renders outside the table) ░░░ --}}
            @push('modals')
              @include('superadmin.manage-accounts.modals.manage-admin', [
                'admin' => $admin,
                'departments' => $departments,
                'programs' => $programs
              ])
            @endpush
            {{-- ░░░ END: Push modal to stack ░░░ --}}

          @empty
            <tr>
              <td colspan="4" class="text-center text-muted py-4">No approved admins found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    {{-- ░░░ END: Table ░░░ --}}
  </div>
</div>

{{-- ░░░ START: Render modals outside the table/card ░░░ --}}
@stack('modals')
{{-- ░░░ END: Render modals ░░░ --}}

{{-- ░░░ START: Styles & Scripts ░░░ --}}
@push('styles')
<style>
  /* Keep modal above any custom overlays/backdrops */
  .modal { z-index: 1060; }
  .modal-backdrop { z-index: 1055; }
  /* Ensure your sidebar backdrop stays below modals (ID from your layout) */
  #sidebar-backdrop { z-index: 1040; }
</style>
@endpush

@push('scripts')
  {{-- If Bootstrap JS bundle is already loaded globally, remove the next line. --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  {{-- Modal-scoped role/program filtering --}}
  @vite('resources/js/superadmin/appointments-modal.js')

  <script>
    // Simple client-side table filter
    function svFilterApprovedAdmins(q) {
      const query = (q || '').toLowerCase().trim();
      document.querySelectorAll('#svApprovedAdminsTable tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
      });
    }
  </script>
@endpush
{{-- ░░░ END: Styles & Scripts ░░░ --}}
