<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Monitoring | Login</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- Custom Styles -->
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            margin: 80px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .login-header {
            text-align: center;
            color: #c30f08;
            font-weight: 600;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #c30f08;
            border: none;
        }
        .btn-primary:hover {
            background-color: #a50d07;
        }
        .form-control:focus {
            border-color: #c30f08;
            box-shadow: 0 0 5px rgba(195, 15, 8, 0.5);
        }
        .forgot-password {
            text-align: right;
            font-size: 14px;
        }
        .forgot-password a {
            color: #c30f08;
            text-decoration: none;
        }
        .forgot-password a:hover {
            text-decoration: underline;
        }
        .btn-primary:disabled {
            background-color: #c30f08 !important;
            border-color: #c30f08 !important;
            opacity: 0.8;
            cursor: not-allowed;
        }

    </style>
</head>
<body>

<div class="login-container">
    <div class="login-header">
        Project Monitoring System
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if (session('fail'))
        <div class="alert alert-danger">{{ session('fail') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" onsubmit="handleLoginSubmit(event)">
        @csrf

        <!-- Email -->
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" placeholder="Enter your email" required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror"
                   placeholder="Enter your password" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
            <label class="form-check-label" for="remember">Remember Me</label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary w-100" id="loginButton">
            <span id="btnText">Login</span>
            <span id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
        </button>

        <!-- Forgot Password -->
        <div class="forgot-password mt-3">
            <a href="{{ route('password.request') }}">Forgot your password?</a>
        </div>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Optional JS to disable button and show spinner -->
<script>
    function handleLoginSubmit(event) {
        const loginButton = document.getElementById('loginButton');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');

        loginButton.disabled = true;
        btnText.textContent = 'Logging in...';
        btnSpinner.classList.remove('d-none');
    }
</script>

</body>
</html>
