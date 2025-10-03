{{-- 
-------------------------------------------------------------------------------
* File: resources/views/superadmin/master-data/tabs/skills-outcomes.blade.php
* Description: SDG / IGA / CDIO subtabs with drag-to-reorder – aligned to Manage Accounts tab UI
-------------------------------------------------------------------------------
📜 Log:
[2025-08-12] Realign – subtabs now use Manage Accounts pattern (.sv-subtabs) with underline handled in tabs.css.
[2025-08-12] Polish – icon-only circular actions (Add/Edit/Delete), Bootstrap-Icons grip, clean section titles.
[2025-08-12] Update – Description shows single-line ellipsis with tooltip; kept responsive width caps.
[2025-08-12] Fix – per-table data-reorder-url and stable IDs (svTable-*).
-------------------------------------------------------------------------------
--}}

{{-- ░░░ START: Scoped helpers (only what isn’t in global CSS) ░░░ --}}
<style>
  /* Professional single-line description with ellipsis + responsive max width */
  #skills-outcomes .sv-desc{
    display:inline-block; max-width:520px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; line-height:1.4;
  }
  @media (max-width: 992px){ #skills-outcomes .sv-desc{ max-width:360px; } }
  @media (max-width: 768px){ #skills-outcomes .sv-desc{ max-width:240px; } }
</style>
{{-- ░░░ END: Scoped helpers ░░░ --}}

{{-- ░░░ START: Subtabs (match Manage Accounts Admins structure) ░░░ --}}
<ul class="nav mb-4" id="masterDataSubTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link master-data-subtab active"
            id="sdg-tab"
            data-bs-toggle="pill"
            data-bs-target="#sdg"
            type="button"
            role="tab">
      SDG
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link master-data-subtab"
            id="iga-tab"
            data-bs-toggle="pill"
            data-bs-target="#iga"
            type="button"
            role="tab">
      IGA
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link master-data-subtab"
            id="cdio-tab"
            data-bs-toggle="pill"
            data-bs-target="#cdio"
            type="button"
            role="tab">
      CDIO
    </button>
  </li>
</ul>
{{-- ░░░ END: Subtabs ░░░ --}}



{{-- ░░░ START: Subtab Content ░░░ --}}
<div class="tab-content" id="skills-outcomes">

  @php
    $buckets = [
      'sdg'  => ['label' => 'Sustainable Development Goals',     'items' => $sdgs  ?? collect(), 'prefix' => 'SDG'],
      'iga'  => ['label' => 'Institutional Graduate Attributes', 'items' => $igas  ?? collect(), 'prefix' => 'IGA'],
      'cdio' => ['label' => 'Conceive–Design–Implement–Operate', 'items' => $cdios ?? collect(), 'prefix' => 'CDIO'],
    ];
  @endphp

  @foreach ($buckets as $id => $data)
    <div class="tab-pane fade @if ($loop->first) show active @endif" id="{{ $id }}" role="tabpanel" aria-labelledby="{{ $id }}-tab">

      {{-- ░░░ START: Toolbar Section ░░░ --}}
      <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
        <h6 class="mb-0 fw-semibold" style="font-size:.95rem;">{{ $data['label'] }}</h6>

        <div class="d-flex align-items-center gap-2">
          {{-- Save Order (clean pill; enabled only when dirty) --}}
          <button type="button"
                  class="btn btn-light btn-sm border rounded-pill sv-save-order-btn"
                  data-sv-type="{{ $id }}"
                  disabled
                  title="Save current order">
            <i data-feather="save"></i><span class="d-none d-md-inline ms-1">Save Order</span>
          </button>

          {{-- Add (icon-only circular, like Departments) --}}
          <button type="button"
                  class="btn-brand-sm"
                  data-bs-toggle="modal"
                  data-bs-target="#add{{ ucfirst($id) }}Modal"
                  aria-label="Add {{ strtoupper($id) }}"
                  title="Add {{ strtoupper($id) }}">
            <i data-feather="plus"></i>
          </button>
        </div>
      </div>
      {{-- ░░░ END: Toolbar Section ░░░ --}}

      {{-- ░░░ START: Table Wrapper ░░░ --}}
      <div class="table-wrapper position-relative">
        <div class="table-responsive">
          <table class="table mb-0"
                 id="svTable-{{ $id }}"
                 data-sv-type="{{ $id }}"
                 data-sv-prefix="{{ $data['prefix'] }}"
                 data-reorder-url="{{ route('superadmin.master-data.reorder', ['type' => $id]) }}">
            <colgroup>
              <col style="width:40px;">   {{-- grip --}}
              <col style="width:110px;">  {{-- code --}}
              <col style="width:24%;">    {{-- title --}}
              <col>                        {{-- description --}}
              <col style="width:140px;">  {{-- actions --}}
            </colgroup>

            <thead>
              <tr>
                <th><span class="visually-hidden">Reorder</span></th>
                <th><i data-feather="code"></i> Code</th>
                <th><i data-feather="type"></i> Title</th>
                <th><i data-feather="align-left"></i> Description</th>
                <th class="text-end"><i data-feather="more-vertical"></i></th>
              </tr>
            </thead>

            <tbody>
              @forelse ($data['items'] as $index => $item)
                @php $code = $item->code ?? ($data['prefix'] . ($index + 1)); @endphp
                <tr data-id="{{ $item->id }}">
                  {{-- Grip (Bootstrap Icons) --}}
                  <td class="text-muted">
                    <i class="sv-row-grip bi bi-grip-vertical fs-5" title="Drag to reorder" aria-hidden="true" style="cursor: move;"></i>
                  </td>

                  {{-- Code --}}
                  <td class="sv-code fw-semibold">{{ $code }}</td>

                  {{-- Title --}}
                  <td class="fw-medium">{{ $item->title ?? '—' }}</td>

                  {{-- Description (ellipsis + full on hover) --}}
                  <td class="text-muted">
                    <span class="sv-desc" title="{{ $item->description }}">{{ $item->description }}</span>
                  </td>

                  {{-- Actions (icon-only circles) --}}
                  <td class="text-end">
                    <button type="button"
                            class="btn action-btn rounded-circle edit me-2"
                            data-bs-toggle="modal"
                            data-bs-target="#editMasterDataModal"
                            data-sv-type="{{ $id }}"
                            data-sv-id="{{ $item->id }}"
                            data-sv-title="{{ $item->title }}"
                            data-sv-description="{{ $item->description }}"
                            data-sv-label="{{ $code }}"
                            title="Edit"
                            aria-label="Edit">
                      <i data-feather="edit"></i>
                    </button>

                    <button type="button"
                            class="btn action-btn rounded-circle delete"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteMasterDataModal"
                            data-sv-type="{{ $id }}"
                            data-sv-id="{{ $item->id }}"
                            data-sv-label="{{ $code }}"
                            title="Delete"
                            aria-label="Delete">
                      <i data-feather="trash"></i>
                    </button>
                  </td>
                </tr>
              @empty
                <tr class="sv-empty-row">
                  <td colspan="5">
                    <div class="sv-empty">
                      <h6>No {{ strtoupper($id) }} entries</h6>
                      <p>Click the <i data-feather="plus"></i> button to add one.</p>
                    </div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      {{-- ░░░ END: Table Wrapper ░░░ --}}

    </div>
  @endforeach
</div>
{{-- ░░░ END: Subtab Content ░░░ --}}
