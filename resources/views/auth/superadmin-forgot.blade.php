<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Forgot • Admin Login • Syllaverse</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="p-4">
  <div class="container" style="max-width:720px">
    <h2 class="mb-3">Reset Super Admin Password</h2>
    <p class="text-muted">We’ll send a reset request to <strong>syllaverse.system@gmail.com</strong> (we can change this later).</p>

    @if(session('status'))
      <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('superadmin.forgot.request') }}" class="mt-3">
      @csrf
      <p class="text-muted">Click the button below and we will email a secure link to enter a new password.</p>
      <div class="d-flex gap-2 mt-2">
        <button type="submit" class="btn btn-danger">Send Reset Request</button>
        <a class="btn btn-outline-secondary" href="{{ route('superadmin.login.form') }}">Back to Login</a>
      </div>
    </form>
  </div>
</body>
</html>
