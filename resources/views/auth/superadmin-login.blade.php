{{-- 
------------------------------------------------------------------------------
* File: resources/views/auth/superadmin-login.blade.php
* Description: Final Super Admin Login – Syllaverse (Desktop-like Tablet Layout)
------------------------------------------------------------------------------
📜 Log:
[2025-07-28] Initial version – built fully responsive login view for super admin with .env-based credentials.
[2025-07-28] Extracted inline JS to `resources/js/superadmin-login.js` and loaded via Vite.
[2025-07-28] Removed Feather Icons and Bootstrap JS CDN – switched to Vite + NPM imports.
[2025-07-28] Moved CSS to `resources/css/superadmin/login.css` and updated Vite asset path.
------------------------------------------------------------------------------
--}}
<!DOCTYPE html>
<html lang="en">

<head>
  {{-- START: Meta and Head Resources --}}
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Super Admin Login • Syllaverse</title>
  <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png" />

  {{-- Bootstrap Fonts --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  {{-- END: Meta and Head Resources --}}

  {{-- START: Vite Assets --}}
  @vite(['resources/css/superadmin/login.css', 'resources/js/superadmin/superadmin-login.js'])
  {{-- END: Vite Assets --}}
</head>

<body>

  {{-- START: Login Card --}}
  <div class="login-card">
    <img src="{{ asset('images/syllaverse-logo.png') }}" alt="Syllaverse Logo" class="logo">
    <h4>Super Admin Login</h4>

    {{-- Session Error --}}
    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    {{-- START: Login Form --}}
    <form method="POST" action="{{ route('superadmin.login') }}">
      @csrf

      <div class="form-floating mb-3">
        <input type="text" id="username" name="username" class="form-control @error('username') is-invalid @enderror" placeholder="Username" required autofocus>
        <label for="username">Username</label>
        @error('username')
          <div class="invalid-feedback text-start">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-floating mb-3 password-wrapper">
        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Password" required>
        <label for="password">Password</label>
        <i data-feather="eye" class="toggle-password"></i>
        @error('password')
          <div class="invalid-feedback text-start">{{ $message }}</div>
        @enderror
      </div>

      <button type="submit" class="btn btn-brand" id="loginBtn">
        <span id="loginText">Login</span>
      </button>
    </form>
    {{-- END: Login Form --}}

    <footer>&copy; Batangas State University • 2025</footer>
  </div>
  {{-- END: Login Card --}}
</body>

</html>
