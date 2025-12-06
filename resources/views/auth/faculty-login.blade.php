{{-- 
------------------------------------------------
* File: resources/views/auth/faculty-login.blade.php
* Description: Faculty Login Page (Syllaverse) – Updated UI aligned with Admin login (centered card, triangle background, Poppins font)
------------------------------------------------ 
--}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Faculty Login • Syllaverse</title>
  <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png" />
  <link rel="canonical" href="{{ url('/faculty/login') }}" />
  <meta name="description" content="Faculty Login to Syllaverse — Sign in with your BSU GSuite account to manage syllabi and coursework." />
  <meta name="robots" content="index,follow" />

  <!-- Open Graph -->
  <meta property="og:type" content="website" />
  <meta property="og:title" content="Faculty Login • Syllaverse" />
  <meta property="og:description" content="Sign in with your BSU GSuite account to access the faculty dashboard." />
  <meta property="og:url" content="{{ url('/faculty/login') }}" />
  <meta property="og:image" content="{{ asset('images/syllaverse-logo.png') }}" />

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="Faculty Login • Syllaverse" />
  <meta name="twitter:description" content="Sign in with your BSU GSuite account to access the faculty dashboard." />
  <meta name="twitter:image" content="{{ asset('images/syllaverse-logo.png') }}" />

  <!-- JSON-LD Structured Data for WebPage -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "WebPage",
    "name": "Faculty Login • Syllaverse",
    "url": "{{ url('/faculty/login') }}",
    "isPartOf": {
      "@type": "WebSite",
      "name": "Syllaverse",
      "url": "{{ url('/') }}"
    },
    "description": "Faculty Login to Syllaverse — Sign in with your BSU GSuite account to manage syllabi and coursework."
  }
  </script>

  {{-- Bootstrap & Fonts --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  {{-- In-app browser guard CSS via Vite --}}
  @vite('resources/css/components/inapp-browser-guard.css')

  {{-- Feather Icons --}}
  <script src="https://unpkg.com/feather-icons"></script>

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #FAFAFA;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      margin: 0;
      overflow: hidden;
    }

    .accent-bg {
      position: absolute;
      bottom: 0;
      right: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, #EE6F57, #CB3737);
      clip-path: polygon(100% 100%, 100% 0%, 0% 100%);
      z-index: -1;
    }

    .login-card {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
      max-width: 420px;
      width: 90%;
      padding: 2.5rem;
      text-align: center;
      animation: fadeInUp 0.8s ease both;
      z-index: 2;
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .login-card img.logo {
      max-width: 120px;
      margin-bottom: 0.5rem;
    }

    .login-card h4 {
      color: #CB3737;
      margin-bottom: 1.5rem;
      font-weight: 600;
    }

    .btn-google {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      border: 1px solid #E3E3E3;
      border-radius: 10px;
      padding: 10px;
      width: 100%;
      transition: 0.3s;
      font-weight: 500;
      background: #fff;
      text-decoration: none;
      color: #333;
    }

    .btn-google:hover {
      border-color: #CB3737;
      background-color: #fef3f2;
      color: #CB3737;
    }

    footer {
      font-size: 0.85rem;
      color: #777;
      margin-top: 1.5rem;
    }

    input:focus,
    button:focus,
    a:focus,
    select:focus,
    textarea:focus {
      outline: none !important;
      box-shadow: none !important;
      border-color: #CB3737 !important;
    }

    ::selection {
      background: #FFE5E0;
      color: #CB3737;
    }

    @media (max-width: 576px) {
      .accent-bg {
        display: none;
      }
      .login-card {
        padding: 2rem 1.5rem;
      }
      .login-card h4 {
        font-size: 1.2rem;
      }
    }
  </style>
</head>
<body>
  {{-- Triangle Background --}}
  <div class="accent-bg"></div>

  {{-- Faculty Login Card --}}
  <div class="login-card">
    <img src="{{ asset('images/syllaverse-logo.png') }}" alt="Syllaverse Logo" class="logo">
    <h4>Faculty Login</h4>

    {{-- Error Alerts (mirror Admin login) --}}
    @if ($errors->has('email'))
      <div class="alert alert-danger">{{ $errors->first('email') }}</div>
    @endif
    @if ($errors->has('rejected'))
      <div class="alert alert-danger">{{ $errors->first('rejected') }}</div>
    @endif
    @if ($errors->has('role'))
      <div class="alert alert-danger">{{ $errors->first('role') }}</div>
    @endif
    @if ($errors->has('login'))
      <div class="alert alert-danger">{{ $errors->first('login') }}</div>
    @endif

    {{-- Pending Approval / Profile Submission Message (robust fallback) --}}
    @php(
      $pendingMsg = trim($errors->first('pending') ?? '')
    )
    @php(
      $approvalMsg = trim($errors->first('approval') ?? '')
    )
    @php(
      $sessionPending = trim(session('pending') ?? '')
    )
    @php(
      $sessionApproval = trim(session('approval') ?? '')
    )
    @php(
      $finalApprovalMsg = $pendingMsg !== ''
        ? $pendingMsg
        : ($approvalMsg !== ''
            ? $approvalMsg
            : ($sessionPending !== ''
                ? $sessionPending
                : ($sessionApproval !== ''
                    ? $sessionApproval
                    : 'Your account is pending approval. You will be notified once approved.')))
    )
    @if($pendingMsg !== '' || $approvalMsg !== '' || $sessionPending !== '' || $sessionApproval !== '')
      <div class="alert alert-warning" style="background-color: #fef8e7; border-color: #f4e5a9; color: #6b5d00; border-radius: 10px; padding: 1rem; margin-bottom: 1.5rem; font-size: 14px;">
        {{ $finalApprovalMsg }}
      </div>
    @endif

    {{-- Google Sign In --}}
    <a href="{{ route('faculty.google.login') }}" class="btn-google" aria-label="Login with Google (Faculty)">
      <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google Logo" width="20" height="20">
      Sign in with Google
    </a>

    <p class="text-muted mt-3 mb-0" style="font-size: 13px;">
      Use your BSU GSuite account to access the faculty dashboard.
    </p>

    <footer>&copy; Batangas State University • 2025</footer>
  </div>

  <script>
    feather.replace();
  </script>
  {{-- In-app browser guard JS via Vite --}}
  @vite('resources/js/components/inapp-browser-guard.js')
</body>
</html>
