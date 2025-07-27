{{-- 
------------------------------------------------
* File: resources/views/faculty/syllabus/partials/ilo.blade.php
* Description: ILO section mimicking printed CIS format, aligned with mission/vision layout – Syllaverse
------------------------------------------------ 
--}}

<div class="mb-4">
  <form id="iloForm" method="POST" action="{{ route('faculty.syllabi.ilos.update', $default['id']) }}">
    @csrf
    @method('PUT')

    <div class="d-flex justify-content-between align-items-center mb-2">
      <h6 class="fw-bold mb-0">Intended Learning Outcomes</h6>
      <button type="submit" class="btn btn-outline-danger btn-sm">
        <i class="bi bi-save"></i> Save ILOs
      </button>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered" style="font-family: Georgia, serif; font-size: 13px; line-height: 1.4;">
        <colgroup>
          <col style="width: 15%;">
          <col style="width: 10%;">
          <col style="width: 75%;">
        </colgroup>
        <tbody>
          @forelse ($ilos as $index => $ilo)
            <tr>
              {{-- Merge first column only once for ILO label --}}
              @if ($index === 0)
                <td class="text-center align-middle" rowspan="{{ count($ilos) }}">
                  Intended Learning <br> Outcomes (ILO)
                </td>
              @endif
              <td class="fw-bold text-center align-middle">ILO{{ $index + 1 }}</td>
              <td>
                <textarea 
                  name="ilos[]" 
                  class="form-control border-0 p-0 bg-transparent"
                  style="min-height: 60px;" 
                  required>{{ old("ilos.$index", $ilo->description) }}</textarea>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="text-center text-muted">No ILOs found for this syllabus.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </form>
</div>
