<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reset • Admin Login • Syllaverse</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="p-4">
  <div class="container" style="max-width:720px">
    <h2 class="mb-3">Enter New Password</h2>
    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif
    <form method="POST" action="{{ route('superadmin.reset.update') }}" class="mt-3">
      @csrf
      <div class="row g-3">
        <div class="col-12">
          <label for="new_password" class="form-label">New Password</label>
          <input type="password" id="new_password" name="new_password" class="form-control" required minlength="8" placeholder="Enter new password">
        </div>
        <div class="col-12">
          <label for="confirm_password" class="form-label">Re-enter New Password</label>
          <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="8" placeholder="Re-enter new password">
        </div>
      </div>
      <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-danger">Update Password</button>
        <a class="btn btn-outline-secondary" href="{{ route('superadmin.login.form') }}">Back to Login</a>
      </div>
    </form>
  </div>
</body>
</html>
