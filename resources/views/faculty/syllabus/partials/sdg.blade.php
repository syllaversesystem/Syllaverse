{{-- 
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/partials/sdg.blade.php
* Description: SDG Mapping (CIS-style layout, compact height and narrow actions) – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-07-29] Refactored layout to match CIS format.
[2025-07-29] Reduced textarea and button height; made action column smaller; icon-only compact buttons.
-------------------------------------------------------------------------------
--}}

<table class="table table-bordered mb-4" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.2;">
  <colgroup>
    <col style="width: 25%;">
    <col style="width: 65%;">
    <col style="width: 10%;">
  </colgroup>
  <thead class="table-light">
    <tr>
      <th colspan="3" class="bg-light">
        <div class="d-flex justify-content-between align-items-center">
          <span class="fw-bold">Sustainable Development Goals (SDG) Mapping</span>
          <button type="button" class="btn btn-sm btn-danger fw-semibold" data-bs-toggle="modal" data-bs-target="#addSdgModal">
            <i class="bi bi-plus-circle"></i>
          </button>
        </div>
      </th>
    </tr>
    <tr class="text-center fw-semibold">
      <th>SDG Title</th>
      <th>Description</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody id="sdg-table-body">
    @forelse ($default['sdgs'] as $sdg)
      <tr>
        <form data-sdg-form="update" action="{{ route('faculty.syllabi.sdgs.update', [$default['id'], $sdg->pivot->id]) }}">
          @csrf
          <td class="align-top py-1">
            <input type="text" name="title" value="{{ $sdg->pivot->title }}"
                   class="form-control border-0 p-1 bg-transparent fw-bold"
                   style="font-family: Georgia, serif; font-size: 13px;" required>
          </td>
          <td class="align-top py-1">
            <textarea name="description"
                      class="form-control border-0 p-1 bg-transparent"
                      style="font-family: Georgia, serif; font-size: 13px; resize: none;" rows="1"
                      required>{{ $sdg->pivot->description }}</textarea>
          </td>
          <td class="align-top text-end py-1">
            <div class="d-flex justify-content-end align-items-center gap-1">
              <button type="submit" class="btn btn-sm btn-outline-primary px-2" title="Save">
                <i class="bi bi-save"></i>
              </button>
        </form>
        <form data-sdg-form="delete" action="{{ route('faculty.syllabi.sdgs.detach', [$default['id'], $sdg->id]) }}" method="POST">
          @csrf
          @method('DELETE')
              <button type="submit" class="btn btn-sm btn-outline-danger px-2" title="Remove">
                <i class="bi bi-x-circle"></i>
              </button>
            </div>
          </td>
        </form>
      </tr>
    @empty
      <tr><td colspan="3" class="text-muted fst-italic text-center">No SDGs mapped yet.</td></tr>
    @endforelse

    {{-- 🆕 Hidden template row for JS clone --}}
    <tr id="sdg-template-row" class="d-none">
      <form data-sdg-form="update">
        <td class="align-top py-1">
          <input type="text" name="title"
                 class="form-control border-0 p-1 bg-transparent fw-bold"
                 style="font-family: Georgia, serif; font-size: 13px;" required>
        </td>
        <td class="align-top py-1">
          <textarea name="description"
                    class="form-control border-0 p-1 bg-transparent"
                    style="font-family: Georgia, serif; font-size: 13px; resize: none;" rows="1"
                    required></textarea>
        </td>
        <td class="align-top text-end py-1">
          <div class="d-flex justify-content-end align-items-center gap-1">
            <button type="submit" class="btn btn-sm btn-outline-primary px-2" title="Save">
              <i class="bi bi-save"></i>
            </button>
      </form>
      <form data-sdg-form="delete">
            <button type="submit" class="btn btn-sm btn-outline-danger px-2" title="Remove">
              <i class="bi bi-x-circle"></i>
            </button>
          </div>
        </td>
      </form>
    </tr>
  </tbody>
</table>

{{-- ➕ Add SDG Modal --}}
<div class="modal fade" id="addSdgModal" tabindex="-1" aria-labelledby="addSdgModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('faculty.syllabi.sdgs.attach', $default['id']) }}">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addSdgModalLabel">Add Sustainable Development Goal</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <label for="sdg_id" class="form-label">Select SDG</label>
          <select name="sdg_id" id="sdg_id" class="form-select" required>
            <option value="">-- Choose SDG --</option>
            @foreach ($sdgs as $sdg)
              @if (!$default['sdgs']->contains($sdg))
                <option value="{{ $sdg->id }}">{{ $sdg->title }}</option>
              @endif
            @endforeach
          </select>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger">Add</button>
        </div>
      </div>
    </form>
  </div>
</div>
