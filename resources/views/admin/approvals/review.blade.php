@extends('layouts.admin')

@section('title', 'Review Request • Admin • Syllaverse')
@section('page-title', 'Review Request')

@section('content')
<div class="request-review">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Review Request</h5>
                    <p class="text-muted">Request review functionality is under development.</p>
                    <a href="{{ route('admin.approvals.index') }}" class="btn btn-secondary">Back to Approvals</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection