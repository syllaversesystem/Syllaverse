{{-- 
------------------------------------------------
* File: resources/views/faculty/syllabus/partials/sdg.blade.php
* Description: SDG Mapping (CIS-style) with AJAX updates – Syllaverse
------------------------------------------------ 
--}}

<table class="table table-bordered mb-4" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.4;">
  <colgroup>
    <col style="width: 15%;">
    <col style="width: 85%;">
  </colgroup>
  <thead class="table-light">
    <tr>
      <th colspan="2" class="bg-light">
        <div class="d-flex justify-content-between align-items-center">
          <span class="fw-bold">Sustainable Development Goals (SDG) Mapping</span>
          <button type="button" class="btn btn-sm btn-danger fw-semibold" data-bs-toggle="modal" data-bs-target="#addSdgModal">
            <i class="bi bi-plus-circle"></i> Add SDG
          </button>
        </div>
      </th>
    </tr>
  </thead>
  <tbody id="sdg-table-body">
    {{-- 🔁 Existing SDGs --}}
    @forelse ($default['sdgs'] as $sdg)
      <tr>
        <td class="align-top text-start">
          <form data-sdg-form="update" action="{{ route('faculty.syllabi.sdgs.update', [$default['id'], $sdg->pivot->id]) }}">
            @csrf
            <input type="text" name="title" value="{{ $sdg->pivot->title }}"
                   class="form-control border-0 p-0 bg-transparent fw-bold mb-1"
                   style="font-family: Georgia, serif; font-size: 13px;" required>
        </td>
        <td class="align-top">
          <div class="d-flex justify-content-between align-items-start gap-2">
            <textarea name="description"
                      class="form-control border-0 p-0 bg-transparent"
                      style="font-family: Georgia, serif; font-size: 13px;" rows="2"
                      required>{{ $sdg->pivot->description }}</textarea>
            <div class="d-flex flex-column gap-1 align-items-end">
              <button type="submit" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-save"></i> Save
              </button>
          </form>
          <form data-sdg-form="delete" action="{{ route('faculty.syllabi.sdgs.detach', [$default['id'], $sdg->id]) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">
              <i class="bi bi-x-circle"></i> Remove
            </button>
          </form>
            </div>
          </div>
        </td>
      </tr>
    @empty
      <tr><td colspan="2" class="text-muted fst-italic">No SDGs mapped yet.</td></tr>
    @endforelse

    {{-- 🆕 Hidden template row for JS clone --}}
    <tr id="sdg-template-row" class="d-none">
      <td class="align-top text-start">
        <form data-sdg-form="update">
          <input type="text" name="title"
                 class="form-control border-0 p-0 bg-transparent fw-bold mb-1"
                 style="font-family: Georgia, serif; font-size: 13px;" required>
      </td>
      <td class="align-top">
        <div class="d-flex justify-content-between align-items-start gap-2">
          <textarea name="description"
                    class="form-control border-0 p-0 bg-transparent"
                    style="font-family: Georgia, serif; font-size: 13px;" rows="2"
                    required></textarea>
          <div class="d-flex flex-column gap-1 align-items-end">
            <button type="submit" class="btn btn-sm btn-outline-primary">
              <i class="bi bi-save"></i> Save
            </button>
        </form>
        <form data-sdg-form="delete">
          <button type="submit" class="btn btn-sm btn-outline-danger">
            <i class="bi bi-x-circle"></i> Remove
          </button>
        </form>
          </div>
        </div>
      </td>
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
