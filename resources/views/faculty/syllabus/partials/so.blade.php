{{-- 
------------------------------------------------
* File: resources/views/faculty/syllabus/partials/so.blade.php
* Description: Editable SO section with AJAX submission – Syllaverse
------------------------------------------------ 
--}}

<div class="mb-4">
  <form id="soForm" action="{{ route('faculty.syllabi.sos.update', $default['id']) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="d-flex justify-content-between align-items-center mb-2">
      <h6 class="fw-bold mb-0">Student Outcomes</h6>
      <button type="submit" class="btn btn-outline-danger btn-sm">
        <i class="bi bi-save"></i> Save SOs
      </button>
    </div>

    <table class="table table-bordered" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.4;">
      <colgroup>
        <col style="width: 15%;">
        <col style="width: 10%;">
        <col style="width: 75%;">
      </colgroup>
      <tbody>
        @forelse ($sos as $index => $so)
          <tr>
            @if ($index === 0)
              <td class="text-center align-middle" rowspan="{{ count($sos) }}">
                Student <br> Outcomes (SO)
              </td>
            @endif
            <td class="fw-bold text-center align-middle">SO{{ $index + 1 }}</td>
            <td>
              <textarea name="sos[]" class="form-control border-0 p-0 bg-transparent" style="min-height: 60px;" required>{{ old("sos.$index", $so->description) }}</textarea>
            </td>
          </tr>
        @empty
          <tr><td colspan="3" class="text-center text-muted">No SOs found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </form>
</div>
