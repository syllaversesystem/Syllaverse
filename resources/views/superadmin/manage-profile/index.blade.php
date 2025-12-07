{{-- Superadmin Manage Profile --}}
@extends('layouts.superadmin')
@section('title', 'Manage Profile')
@section('content')
@push('styles')
<style>
  /* Button style localized to this page */
  .btn-navlike {
    background-color: #ffffff;
    color: #333333;
    border: none;
    transition: all 0.2s ease;
  }
  .btn-navlike:hover {
    background: linear-gradient(135deg, rgba(255, 240, 235, 0.8), rgba(255, 255, 255, 0.4));
    -webkit-backdrop-filter: blur(6px);
    backdrop-filter: blur(6px);
    color: #CB3737 !important;
    box-shadow: 0 4px 8px rgba(204, 55, 55, 0.1);
    border-color: transparent;
  }
  /* Ensure button text (and nested spans) also turn red */
  .btn-navlike:hover *,
  .btn-navlike:hover .label,
  .btn-navlike:hover .btn-text {
    color: #CB3737 !important;
  }
  /* Ensure feather icons also turn red on hover */
  .btn-navlike:hover svg {
    stroke: #CB3737 !important;
  }
</style>
@endpush
<div class="container py-3">
  <div class="row justify-content-center">
    <div class="col-xl-6 col-lg-7 col-md-8">
      <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
          <h6 class="fw-bold mb-3">Profile Information</h6>
          <form method="POST" action="{{ route('superadmin.manage-profile.update') }}">
            @csrf
            <div class="mb-3">
              <label class="form-label small fw-medium text-muted">Username</label>
              <input type="text" class="form-control form-control-sm" name="username" value="{{ $user->username ?? '' }}" required>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-medium text-muted">New Password</label>
              <input type="password" class="form-control form-control-sm" name="password" placeholder="Leave blank to keep current">
            </div>
            <div class="d-flex justify-content-center">
              <button type="submit" class="btn btn-light text-secondary btn-navlike">
                <i data-feather="check-circle"></i> Save Changes
              </button>
            </div>
          </form>
        </div>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <h6 class="fw-bold mb-1">Google Account</h6>
              <div class="text-muted small mt-2"><strong>{{ $user->email ?? 'none' }}</strong></div>
            </div>
            <div>
              <a href="{{ route('superadmin.google.link') }}" class="btn btn-light btn-sm btn-navlike text-secondary" id="btn-link-email">
                <i data-feather="link"></i> Link New Email
              </a>
            </div>
          </div>
        </div>
      </div>

      @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
      @endif
      @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
      @endif
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script>
  (function(){
    const form = document.querySelector('form[action="{{ route('superadmin.manage-profile.update') }}"]');
    if (!form) return;
    const endpoint = form.getAttribute('action');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(form);
      // Build payload
      const payload = {
        username: fd.get('username') || '',
        password: fd.get('password') || ''
      };
      try {
        const res = await fetch(endpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf || ''
          },
          body: JSON.stringify(payload)
        });
        const isJson = (res.headers.get('content-type') || '').includes('application/json');
        const data = isJson ? await res.json() : { ok: res.ok };
        if (res.ok) {
          window.dispatchEvent(new CustomEvent('sv:alert', {
            detail: { type: 'success', message: (data.message || 'Profile updated successfully'), timeout: 3000 }
          }));
        } else {
          const msg = (data.message || 'Failed to update profile');
          window.dispatchEvent(new CustomEvent('sv:alert', {
            detail: { type: 'error', message: msg, timeout: 4000 }
          }));
        }
      } catch (err) {
        window.dispatchEvent(new CustomEvent('sv:alert', {
          detail: { type: 'error', message: 'Network error. Please try again.', timeout: 4000 }
        }));
      }
    });
  })();
</script>
@endpush
