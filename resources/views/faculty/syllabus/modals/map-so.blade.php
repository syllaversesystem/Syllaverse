{{--
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/modals/map-so.blade.php
* Description: Modal for mapping SOs to a specific TLA row – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-07-29] Initial creation – lists SOs as checkboxes with proper IDs and values.
-------------------------------------------------------------------------------
--}}

<div class="modal fade" id="mapSoModal" tabindex="-1" aria-labelledby="mapSoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="mapSoModalLabel">Map Student Outcomes (SOs)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="mapSoTlaId">

        @if ($sos->count())
          @foreach ($sos as $so)
            <div class="form-check">
              <input class="form-check-input so-checkbox" type="checkbox" value="{{ $so->id }}" id="so_{{ $so->id }}">
              <label class="form-check-label" for="so_{{ $so->id }}">
                {{ $so->code }} – {{ $so->description }}
              </label>
            </div>
          @endforeach
        @else
          <p class="text-muted">No SOs available for this syllabus.</p>
        @endif
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveMappedSo">Save Mapping</button>
      </div>
    </div>
  </div>
</div>
