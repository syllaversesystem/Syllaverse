<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reset • Admin Login • Syllaverse</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    .toggle-eye {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #6c757d;
      display: none;
    }
    .has-value .toggle-eye { display: inline; }
    .position-relative input.form-control { padding-right: 40px; }
  </style>
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
          <div class="position-relative" id="np-wrap">
            <input type="password" id="new_password" name="new_password" class="form-control" required minlength="8" placeholder="Enter new password">
            <span class="toggle-eye" id="np-eye" title="Show/Hide">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            </span>
          </div>
        </div>
        <div class="col-12">
          <label for="confirm_password" class="form-label">Re-enter New Password</label>
          <div class="position-relative" id="cp-wrap">
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="8" placeholder="Re-enter new password">
            <span class="toggle-eye" id="cp-eye" title="Show/Hide">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            </span>
          </div>
        </div>
      </div>
      <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-danger">Update Password</button>
        <a class="btn btn-outline-secondary" href="{{ route('superadmin.login.form') }}">Back to Login</a>
      </div>
    </form>
  </div>
<script>
  (function(){
    function initToggle(inputId, eyeId, wrapId){
      const input = document.getElementById(inputId);
      const eye = document.getElementById(eyeId);
      const wrap = document.getElementById(wrapId);
      if (!input || !eye || !wrap) return;
      function update(){ wrap.classList.toggle('has-value', !!input.value); }
      update();
      input.addEventListener('input', update);
      eye.addEventListener('click', function(){
        const isPw = input.type === 'password';
        input.type = isPw ? 'text' : 'password';
        eye.innerHTML = isPw
          ? '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye-off"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20C5 20 1 12 1 12a21.86 21.86 0 0 1 5.06-6.88"></path><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"></path><path d="M1 1l22 22"></path></svg>'
          : '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
        input.focus();
      });
    }
    initToggle('new_password','np-eye','np-wrap');
    initToggle('confirm_password','cp-eye','cp-wrap');
  })();
</script>
</body>
</html>
