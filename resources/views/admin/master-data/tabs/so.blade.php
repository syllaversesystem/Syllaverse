{{-- 
------------------------------------------------
* File: resources/views/admin/master-data/tabs/so.blade.php
* Description: SO Tab Content (Admin Master Data) – auto-code generation, no manual code input (Syllaverse)
------------------------------------------------ 
--}}

<h5>Student Outcomes (SO)</h5>

{{-- Add SO Form --}}
<form method="POST" action="{{ route('admin.master-data.store', 'so') }}">
    @csrf
    <input type="hidden" name="tab" value="soilo">
    <input type="hidden" name="subtab" value="so">

    <div class="mb-3">
        <textarea name="description" class="form-control" placeholder="SO Description" required>{{ old('description') }}</textarea>
    </div>
    <button type="submit" class="btn btn-danger">Add SO</button>
</form>

<hr>

{{-- SO List --}}
<ul class="list-group mt-3">
    @foreach ($studentOutcomes as $so)
        <li class="list-group-item">
            <form method="POST" action="{{ route('admin.master-data.update', ['type' => 'so', 'id' => $so->id]) }}" class="row g-2 align-items-center">
                @csrf @method('PUT')
                <input type="hidden" name="tab" value="soilo">
                <input type="hidden" name="subtab" value="so">

                {{-- Code (readonly) --}}
                <div class="col-md-2">
                    <input type="text" name="code" class="form-control form-control-sm" value="{{ $so->code }}" readonly>
                </div>

                {{-- Description --}}
                <div class="col-md-7">
                    <textarea name="description" class="form-control form-control-sm" rows="1" required>{{ $so->description }}</textarea>
                </div>

                {{-- Buttons --}}
                <div class="col-md-3 d-flex gap-1 justify-content-end">
                    <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                    <form method="POST" action="{{ route('admin.master-data.destroy', ['type' => 'so', 'id' => $so->id]) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </form>
        </li>
    @endforeach
</ul>


