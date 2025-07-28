{{-- 
-------------------------------------------------------------------------------
* File: resources/views/admin/master-data/tabs/so.blade.php
* Description: SO Tab Content (Admin Master Data) – sortable with live code update
-------------------------------------------------------------------------------
📜 Log:
[2025-07-29] Added drag-and-drop reordering and Save Order button for SOs.
-------------------------------------------------------------------------------
--}}

<h5>Student Outcomes (SO)</h5>

{{-- Add SO Form --}}
<form method="POST" action="{{ route('admin.master-data.store', 'so') }}" class="d-flex justify-content-between align-items-center mb-2">
    @csrf
    <div class="flex-grow-1 me-3">
        <textarea name="description" class="form-control" placeholder="SO Description" required>{{ old('description') }}</textarea>
    </div>
    <button type="submit" class="btn btn-danger">Add SO</button>
    <button type="button" class="btn btn-outline-primary ms-2" id="save-so-order">Save Order</button>
</form>

<hr>

{{-- SO List --}}
<ul class="list-group mt-3" id="so-sortable">
    @forelse ($studentOutcomes->sortBy('position') as $so)
        <li class="list-group-item d-flex align-items-center justify-content-between" data-id="{{ $so->id }}">
            <div class="d-flex align-items-center w-100">
                {{-- Drag Handle --}}
                <span class="me-3 cursor-move text-muted" title="Drag to reorder">
                    <i class="bi bi-grip-vertical fs-5"></i>
                </span>

                {{-- SO Update Form --}}
                <form method="POST" action="{{ route('admin.master-data.update', ['type' => 'so', 'id' => $so->id]) }}" class="row g-2 align-items-center flex-grow-1">
                    @csrf @method('PUT')

                    {{-- Code (readonly) --}}
                    <div class="col-md-3">
                        <input type="text" name="code" class="form-control form-control-sm" value="{{ $so->code }}" readonly>
                    </div>

                    {{-- Description --}}
                    <div class="col-md-6">
                        <textarea name="description" class="form-control form-control-sm" rows="1" required>{{ $so->description }}</textarea>
                    </div>

                    {{-- Save Button --}}
                    <div class="col-md-3 d-flex gap-1 justify-content-end">
                        <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                    </div>
                </form>
            </div>

            {{-- Delete Form --}}
            <form method="POST" action="{{ route('admin.master-data.destroy', ['type' => 'so', 'id' => $so->id]) }}" onsubmit="return confirm('Are you sure you want to delete this SO?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete SO">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </li>
    @empty
        <li class="list-group-item text-muted">No Student Outcomes defined yet.</li>
    @endforelse
</ul>

@push('scripts')
    @vite('resources/js/admin/master-data/so-sortable.js')
@endpush
