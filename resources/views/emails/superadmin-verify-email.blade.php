<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ $appName }} • Verify Super Admin Email</title>
  <style>
    body { font-family: -apple-system, Segoe UI, Roboto, Arial, sans-serif; background:#f6f7fb; margin:0; padding:24px; }
    .card { max-width:600px; margin:0 auto; background:#ffffff; border-radius:12px; padding:24px; box-shadow:0 2px 10px rgba(0,0,0,0.06); }
    h1 { font-size:18px; margin:0 0 12px; color:#111827; }
    p { font-size:14px; color:#374151; line-height:1.6; }
    .btn { display:inline-block; background:#dc2626; color:#fff !important; text-decoration:none; font-weight:600; padding:12px 18px; border-radius:8px; margin:12px 0; }
    .small { font-size:12px; color:#6b7280; }
    .link { color:#dc2626; word-break:break-all; }
  </style>
  </head>
<body>
  <div class="card">
    <h1>Verify Your Super Admin Email</h1>
    <p>We received a request to change the Super Admin email to <strong>{{ $newEmail }}</strong>.</p>
    <p>Click the button below to verify and finalize this change. This link expires in 60 minutes.</p>
    <p>
      <a class="btn" href="{{ $verifyUrl }}" target="_blank" rel="noopener noreferrer">Verify Email</a>
    </p>
    <p class="small">If the button doesn't work, copy and paste this link into your browser:</p>
    <p class="small link">{{ $verifyUrl }}</p>
    <p class="small">— {{ $appName }}</p>
  </div>
</body>
</html>