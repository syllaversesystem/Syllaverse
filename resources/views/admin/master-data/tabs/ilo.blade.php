{{-- 
------------------------------------------------
* File: resources/views/admin/master-data/tabs/ilo.blade.php
* Description: ILO Tab Content (Admin Master Data) – auto-code & auto-position (no manual inputs)
------------------------------------------------ 
--}}

<h5>Intended Learning Outcomes (ILO)</h5>

{{-- Course Filter --}}
<form method="GET" action="{{ route('admin.master-data.index') }}" class="mb-4">
    <input type="hidden" name="tab" value="soilo">
    <input type="hidden" name="subtab" value="ilo">

    <div class="row g-2 align-items-center">
        <div class="col-md-6">
            <select name="course_id" class="form-select" onchange="this.form.submit()">
                <option value="">Select a Course</option>
                @foreach ($courses as $course)
                    <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                        {{ $course->code }} - {{ $course->title }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</form>

@if (request('course_id'))
    {{-- Add New ILO Form --}}
    <form method="POST" action="{{ route('admin.master-data.store', ['type' => 'ilo']) }}" class="d-flex justify-content-between align-items-center mb-2" id="addIloModal">
        @csrf
        <input type="hidden" name="course_id" value="{{ request('course_id') }}">

        <div class="flex-grow-1 me-3">
            <textarea name="description" class="form-control" placeholder="ILO Description" required>{{ old('description') }}</textarea>
        </div>

        <button type="submit" class="btn btn-danger">Add ILO</button>
        <button type="button" class="btn btn-outline-primary ms-2" id="save-ilo-order">Save Order</button>
    </form>

    <hr>

    {{-- ILO List --}}
    <ul class="list-group mt-3" id="ilo-sortable" data-course-id="{{ request('course_id') }}">
        @forelse ($intendedLearningOutcomes->sortBy('position') as $ilo)
            <li class="list-group-item d-flex align-items-center justify-content-between" data-id="{{ $ilo->id }}">
                <div class="d-flex align-items-center w-100">
                    {{-- Drag Handle --}}
                    <span class="me-3 cursor-move text-muted" title="Drag to reorder">
                        <i class="bi bi-grip-vertical fs-5"></i>
                    </span>

                    {{-- ILO Update Form --}}
                    <form method="POST" action="{{ route('admin.master-data.update', ['type' => 'ilo', 'id' => $ilo->id]) }}" class="row g-2 align-items-center flex-grow-1">
                        @csrf @method('PUT')
                        <input type="hidden" name="course_id" value="{{ request('course_id') }}">

                        {{-- Code (readonly) --}}
                        <div class="col-md-3">
                            <input type="text" name="code" class="form-control form-control-sm" value="{{ $ilo->code }}" readonly>
                        </div>

                        {{-- Description --}}
                        <div class="col-md-6">
                            <textarea name="description" class="form-control form-control-sm" rows="1" required>{{ $ilo->description }}</textarea>
                        </div>

                        {{-- Save Button --}}
                        <div class="col-md-3 d-flex gap-1 justify-content-end">
                            <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                        </div>
                    </form>
                </div>

                {{-- Separate Delete Form --}}
                <form method="POST" action="{{ route('admin.master-data.destroy', ['type' => 'ilo', 'id' => $ilo->id]) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete ILO">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </li>
        @empty
            <li class="list-group-item text-muted">No ILOs found for this course.</li>
        @endforelse
    </ul>
@else
    <p class="text-muted">Please select a course to manage its ILOs.</p>
@endif

@if (session('open_modal') === 'add-ilo')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('addIloModal')?.scrollIntoView({ behavior: 'smooth' });
        });
    </script>
@endif

@push('scripts')
    @vite('resources/js/admin/master-data/ilo-sortable.js')
@endpush
