{{--
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/modals/map-ilo.blade.php
* Description: Modal for mapping ILOs to a specific TLA row – Syllaverse
-------------------------------------------------------------------------------
📜 Log:
[2025-07-29] Initial creation – lists ILOs as checkboxes with proper IDs and values.
[2025-07-29] Ensured each checkbox value matches ILO ID for JS pre-check logic.
-------------------------------------------------------------------------------
--}}

<div class="modal fade" id="mapIloModal" tabindex="-1" aria-labelledby="mapIloModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="mapIloModalLabel">Map Intended Learning Outcomes (ILOs)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="mapIloTlaId">

        @if ($ilos->count())
          @foreach ($ilos as $ilo)
            <div class="form-check">
              <input class="form-check-input ilo-checkbox" type="checkbox" value="{{ $ilo->id }}" id="ilo_{{ $ilo->id }}">
              <label class="form-check-label" for="ilo_{{ $ilo->id }}">
                {{ $ilo->code }} – {{ $ilo->description }}
              </label>
            </div>
          @endforeach
        @else
          <p class="text-muted">No ILOs available for this syllabus.</p>
        @endif
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveMappedIlo">Save Mapping</button>
      </div>
    </div>
  </div>
</div>
