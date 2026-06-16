<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real Time File Tracking System | Login</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        @font-face {
            font-family: 'SolaimanLipi';
            src: url('/assets/font/SolaimanLipi.ttf') format('truetype');
        }

        :root {
            --navy: #062a4f;
            --blue: #0d63d6;
            --ink: #102033;
            --muted: #52667c;
            --line: rgba(6, 42, 79, .14);
            --soft: #f3f7ff;
            --ring: 0 0 0 .22rem rgba(13, 99, 214, .2);
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: 'SolaimanLipi', "Segoe UI", sans-serif;
            color: var(--ink);
            background: linear-gradient(135deg, #f5f8ff 0%, #ffffff 54%, #eef5ff 100%);
        }

        .topbar {
            min-height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px clamp(16px, 4vw, 42px);
            border-bottom: 1px solid var(--line);
            background: rgba(255, 255, 255, .88);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--navy);
        }

        .brand-icon {
            width: 46px;
            height: 46px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            color: #fff;
            background: var(--navy);
        }

        .brand h1 {
            margin: 0;
            font-size: 1.08rem;
            font-weight: 800;
        }

        .brand p {
            margin: 2px 0 0;
            color: var(--muted);
            font-size: .92rem;
            font-weight: 600;
        }

        .page {
            min-height: calc(100vh - 70px);
            display: grid;
            place-items: center;
            padding: 28px 16px;
        }

        .login-shell {
            width: min(1060px, 100%);
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            border: 1px solid var(--line);
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 18px 50px rgba(6, 42, 79, .12);
        }

        .scan-panel,
        .password-panel {
            padding: clamp(22px, 4vw, 38px);
        }

        .scan-panel {
            background: var(--soft);
            border-right: 1px solid var(--line);
        }

        .panel-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }

        h2 {
            margin: 0;
            color: var(--navy);
            font-size: clamp(1.35rem, 2.4vw, 1.85rem);
            font-weight: 850;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            color: var(--navy);
            background: #fff;
            border: 1px solid rgba(13, 99, 214, .24);
            font-size: .9rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .scan-target {
            border: 2px dashed rgba(13, 99, 214, .42);
            border-radius: 8px;
            padding: 22px;
            background: #fff;
            cursor: pointer;
        }

        .scan-target strong {
            display: block;
            color: var(--navy);
            font-size: 1.22rem;
            margin-bottom: 6px;
        }

        .scan-target span,
        .muted {
            color: var(--muted);
            font-weight: 600;
        }

        .form-label {
            margin-top: 16px;
            margin-bottom: 7px;
            color: var(--navy);
            font-weight: 800;
        }

        .form-control {
            min-height: 54px;
            border-radius: 8px;
            border-color: rgba(6, 42, 79, .2);
            font-size: 1.06rem;
        }

        .form-control:focus {
            border-color: rgba(13, 99, 214, .58);
            box-shadow: var(--ring);
        }

        .btn-main {
            min-height: 54px;
            border-radius: 8px;
            border: 0;
            background: var(--navy);
            color: #fff;
            font-weight: 850;
        }

        .btn-main:hover,
        .btn-main:focus {
            background: #0a3765;
            color: #fff;
        }

        .btn-outline-main {
            min-height: 44px;
            border-radius: 8px;
            color: var(--navy);
            border-color: rgba(6, 42, 79, .26);
            font-weight: 800;
        }

        .divider {
            height: 1px;
            background: var(--line);
            margin: 20px 0;
        }

        @media (max-width: 860px) {
            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .login-shell {
                grid-template-columns: 1fr;
            }

            .scan-panel {
                border-right: 0;
                border-bottom: 1px solid var(--line);
            }

            .panel-title {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="brand">
            <div class="brand-icon">
                <i class="bi bi-folder2-open fs-4"></i>
            </div>
            <div>
                <h1>Real Time File Tracking System</h1>
                <p>File Movement Tracking</p>
            </div>
        </div>

        <a class="btn btn-outline-main" href="#passwordLogin">
            <i class="bi bi-person-lock me-1"></i> Login
        </a>
    </header>

    <main class="page">
        <section class="login-shell">
            <div class="scan-panel">
                <div class="panel-title">
                    <h2>Card Login</h2>
                    <span class="status-pill" id="scanStatus">
                        <i class="bi bi-bullseye"></i> Ready to Scan
                    </span>
                </div>

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                @if (session('fail'))
                    <div class="alert alert-danger">{{ session('fail') }}</div>
                @endif

                <div class="scan-target" id="scanTarget" role="button" tabindex="0">
                    <strong><i class="bi bi-upc-scan me-1"></i> Tap Card</strong>
                    <span>Place the card on the reader.</span>
                </div>

                <form method="POST" action="{{ route('proximity.login') }}" id="cardForm" class="mt-3">
                    @csrf
                    <input type="hidden" name="login_id" id="card_login_id" value="{{ old('login_id') }}">

                    <label class="form-label" for="card_login_mask">Card ID</label>
                    <input
                        type="password"
                        id="card_login_mask"
                        class="form-control @error('login_id') is-invalid @enderror"
                        value="{{ old('login_id') }}"
                        placeholder="Tap card"
                        autocomplete="off"
                        autofocus
                    >
                    @error('login_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    <button type="submit" class="visually-hidden" id="cardLoginButton" tabindex="-1" aria-hidden="true">
                        <span id="cardLoginText"><i class="bi bi-box-arrow-in-right me-1"></i> Login</span>
                        <span id="cardLoginSpinner" class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span>
                    </button>
                </form>

            </div>

            <div class="password-panel" id="passwordLogin">
                <h2>User Login</h2>

                <div class="divider"></div>

                <form method="POST" action="{{ route('login') }}" id="passwordForm">
                    @csrf

                    <label class="form-label" for="expert_login_id">User ID</label>
                    <input
                        type="text"
                        id="expert_login_id"
                        name="login_id"
                        class="form-control @error('login_id') is-invalid @enderror"
                        value="{{ old('login_id') }}"
                        placeholder="Enter user ID"
                        autocomplete="username"
                        required
                    >
                    @error('login_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    <label class="form-label" for="expert_password">Password</label>
                    <input
                        type="password"
                        id="expert_password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Password"
                        autocomplete="current-password"
                        required
                    >
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                        <label class="form-check-label muted" for="remember_me">Remember this login</label>
                    </div>

                    <button type="submit" class="btn btn-main w-100 mt-3">
                        <i class="bi bi-person-check me-1"></i> Login
                    </button>
                </form>
            </div>
        </section>
    </main>

    <script>
        (function () {
            const hidden = document.getElementById('card_login_id');
            const masked = document.getElementById('card_login_mask');
            const form = document.getElementById('cardForm');
            const target = document.getElementById('scanTarget');
            const status = document.getElementById('scanStatus');
            const button = document.getElementById('cardLoginButton');
            const buttonText = document.getElementById('cardLoginText');
            const spinner = document.getElementById('cardLoginSpinner');
            const passwordPanel = document.getElementById('passwordLogin');

            if (!hidden || !masked || !form) {
                return;
            }

            const sync = () => {
                hidden.value = masked.value;
            };

            const isPasswordArea = (element) => {
                return element && passwordPanel && passwordPanel.contains(element);
            };

            const setStatus = (ready) => {
                if (!status) {
                    return;
                }

                status.innerHTML = ready
                    ? '<i class="bi bi-bullseye"></i> Ready to Scan'
                    : '<i class="bi bi-cursor-text"></i> Click Scan Box';
            };

            const focusScanner = () => {
                if (isPasswordArea(document.activeElement)) {
                    return;
                }

                masked.focus({ preventScroll: true });
                const value = masked.value || '';
                try {
                    masked.setSelectionRange(value.length, value.length);
                } catch (error) {
                    // Some scanner/browser combinations do not support selection on password fields.
                }
            };

            masked.addEventListener('input', sync);
            masked.addEventListener('focus', () => setStatus(true));
            masked.addEventListener('blur', () => setStatus(false));

            form.addEventListener('submit', () => {
                sync();
                if (button && buttonText && spinner) {
                    button.disabled = true;
                    buttonText.textContent = 'Logging in...';
                    spinner.classList.remove('d-none');
                }
            });

            if (target) {
                target.addEventListener('click', focusScanner);
                target.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        focusScanner();
                    }
                });
            }

            document.addEventListener('pointerdown', (event) => {
                if (!isPasswordArea(event.target)) {
                    setTimeout(focusScanner, 180);
                }
            });

            document.addEventListener('keydown', () => {
                if (!isPasswordArea(document.activeElement)) {
                    focusScanner();
                }
            });

            window.addEventListener('load', () => {
                sync();
                focusScanner();
            });
        })();
    </script>
</body>
</html>
