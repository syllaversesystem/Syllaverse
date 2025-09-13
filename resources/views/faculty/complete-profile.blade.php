{{-- 
------------------------------------------------
* File: resources/views/faculty/complete-profile.blade.php
* Description: Faculty Profile Completion Page (Syllaverse)
------------------------------------------------ 
--}}
@extends('layouts.faculty')

@section('title', 'Complete Profile • Faculty • Syllaverse')
@section('page-title', 'Complete Your Profile')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if (session('success'))
                <div class="alert alert-success d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('faculty.complete-profile.submit') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="designation" class="form-label">Designation</label>
                            <input type="text" name="designation" id="designation" class="form-control" value="{{ old('designation') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="employee_code" class="form-label">Employee Code</label>
                            <input type="text" name="employee_code" id="employee_code" class="form-control" value="{{ old('employee_code') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="department_id" class="form-label">Department</label>
                            <select name="department_id" id="department_id" class="form-select" required>
                                <option value="" disabled selected>Select your department</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }} ({{ $dept->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                                                <div class="mb-3">
                                                    <label class="form-label d-flex align-items-center gap-2">
                                                        <input class="form-check-input m-0" type="checkbox" id="request_dept_chair" name="request_dept_chair" value="1" {{ old('request_dept_chair') ? 'checked' : '' }}>
                                                        <span class="fw-semibold">Department Chair / Program Chair</span>
                                                    </label>
                                                    <div class="form-text small text-muted">If your department has only one program, requesting this will submit a Program Chair request instead.</div>
                                                </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-save"></i> Save Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
