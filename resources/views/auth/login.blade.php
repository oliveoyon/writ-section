<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RTFTS | Login</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        @font-face {
            font-family: 'SolaimanLipi';
            src: url('/assets/font/SolaimanLipi.ttf') format('truetype');
        }

        :root {
            --navy: #00284d;
            --navy-soft: #073b70;
            --gold: #d4a017;
            --green: #178766;
            --ink: #132238;
            --muted: #607086;
            --line: #d9e2ec;
            --page: #eef3f8;
            --panel: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: 'SolaimanLipi', "Segoe UI", sans-serif;
            color: var(--ink);
            background: linear-gradient(180deg, #f7f9fc 0%, var(--page) 100%);
        }

        .site-topbar {
            border-bottom: 1px solid rgba(255, 255, 255, .14);
            color: #fff;
            background: var(--navy);
            box-shadow: 0 6px 20px rgba(0, 40, 77, .16);
        }

        .site-topbar .container {
            min-height: 68px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: .8rem;
            min-width: 0;
        }

        .brand-mark {
            width: 44px;
            height: 44px;
            border-radius: 6px;
            display: grid;
            place-items: center;
            background: var(--gold);
            color: #fff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .18);
            flex: 0 0 auto;
        }

        .brand-title {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 900;
            letter-spacing: 0;
            line-height: 1.15;
        }

        .brand-subtitle {
            margin: .12rem 0 0;
            color: rgba(255, 255, 255, .78);
            font-size: .84rem;
            font-weight: 600;
        }

        .topbar-link {
            display: inline-flex;
            align-items: center;
            gap: .42rem;
            border: 1px solid rgba(255, 255, 255, .35);
            border-radius: 4px;
            color: #fff;
            text-decoration: none;
            padding: .5rem .75rem;
            font-size: .88rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .topbar-link:hover {
            border-color: var(--gold);
            color: #fff;
            background: rgba(255, 255, 255, .08);
        }

        .login-page {
            min-height: calc(100vh - 69px);
            display: grid;
            align-items: center;
            padding: 2rem 0;
        }

        .login-page > .container {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            padding: clamp(1.1rem, 3vw, 2.1rem);
            background:
                linear-gradient(135deg, rgba(0, 40, 77, .98) 0%, rgba(5, 60, 104, .98) 62%, rgba(12, 83, 123, .96) 100%);
            box-shadow: 0 22px 60px rgba(16, 32, 51, .18);
        }

        .login-page > .container::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(90deg, rgba(255, 255, 255, .06) 1px, transparent 1px),
                linear-gradient(180deg, rgba(255, 255, 255, .05) 1px, transparent 1px);
            background-size: 34px 34px;
            opacity: .36;
            pointer-events: none;
        }

        .login-page > .container::after {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            width: 38%;
            height: 100%;
            background: rgba(255, 255, 255, .08);
            transform: skewX(-13deg) translateX(34%);
            transform-origin: top right;
            pointer-events: none;
        }

        .login-page > .container > .row {
            position: relative;
            z-index: 1;
        }

        .intro-panel {
            color: #fff;
            padding-right: 1.5rem;
            padding-left: .4rem;
        }

        .system-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            border: 1px solid rgba(212, 160, 23, .55);
            border-radius: 999px;
            color: #ffe5a0;
            background: rgba(212, 160, 23, .13);
            padding: .4rem .72rem;
            font-size: .82rem;
            font-weight: 800;
        }

        .intro-panel h1 {
            margin: 1rem 0 .65rem;
            max-width: 560px;
            font-size: clamp(2rem, 4vw, 3.25rem);
            line-height: 1.04;
            font-weight: 900;
            letter-spacing: 0;
            text-shadow: 0 2px 10px rgba(0, 0, 0, .14);
        }

        .intro-panel p {
            max-width: 520px;
            margin: 0;
            color: rgba(255, 255, 255, .78);
            font-size: 1rem;
            line-height: 1.65;
            font-weight: 600;
        }

        .mini-status {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .65rem;
            max-width: 560px;
            margin-top: 1.35rem;
        }

        .mini-status div {
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: 5px;
            background: rgba(255, 255, 255, .08);
            padding: .75rem;
            border-left: 3px solid rgba(212, 160, 23, .85);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .07);
        }

        .mini-status strong {
            display: block;
            color: #fff;
            font-size: .92rem;
            font-weight: 900;
        }

        .mini-status span {
            color: rgba(255, 255, 255, .68);
            font-size: .78rem;
            font-weight: 700;
        }

        .login-card {
            width: min(440px, 100%);
            margin-left: auto;
            background: var(--panel);
            border: 1px solid rgba(0, 40, 77, .12);
            border-radius: 8px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, .22);
            overflow: hidden;
        }

        .login-card-header {
            padding: 1.1rem 1.15rem;
            border-bottom: 1px solid var(--line);
            background: linear-gradient(180deg, #ffffff 0%, #f7fafc 100%);
            border-top: 4px solid var(--gold);
        }

        .login-card-header h2 {
            margin: 0;
            color: var(--navy);
            font-size: 1.22rem;
            font-weight: 900;
            letter-spacing: 0;
        }

        .login-card-header p {
            margin: .2rem 0 0;
            color: var(--muted);
            font-size: .86rem;
            font-weight: 600;
        }

        .login-card-body {
            padding: 1rem 1.15rem 1.15rem;
        }

        .scan-target {
            display: flex;
            align-items: center;
            gap: .85rem;
            width: 100%;
            border: 2px solid rgba(23, 135, 102, .34);
            border-radius: 7px;
            padding: .9rem;
            background: #f2fbf8;
            cursor: pointer;
            transition: border-color .16s ease, box-shadow .16s ease;
        }

        .scan-target:hover,
        .scan-target:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 .2rem rgba(23, 135, 102, .12);
            outline: 0;
        }

        .scan-icon {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            border-radius: 6px;
            background: var(--green);
            color: #fff;
            flex: 0 0 auto;
        }

        .scan-target strong {
            display: block;
            color: var(--navy);
            font-size: 1.08rem;
            font-weight: 900;
            line-height: 1.2;
        }

        .scan-target span {
            display: block;
            color: var(--muted);
            font-size: .86rem;
            font-weight: 700;
        }

        .scan-status {
            display: inline-flex;
            align-items: center;
            gap: .34rem;
            margin-top: .55rem;
            color: var(--green);
            font-size: .82rem;
            font-weight: 800;
        }

        .form-label {
            margin-top: .85rem;
            margin-bottom: .36rem;
            color: var(--navy);
            font-size: .88rem;
            font-weight: 800;
        }

        .form-control {
            min-height: 44px;
            border-radius: 5px;
            border-color: #cfd9e4;
            color: var(--ink);
            font-size: .98rem;
            font-weight: 600;
        }

        .form-control:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 .2rem rgba(212, 160, 23, .14);
        }

        #card_login_mask {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
            pointer-events: none;
        }

        .section-divider {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin: 1rem 0 .25rem;
            color: var(--muted);
            font-size: .8rem;
            font-weight: 900;
            text-transform: uppercase;
        }

        .section-divider::before,
        .section-divider::after {
            content: "";
            height: 1px;
            flex: 1;
            background: var(--line);
        }

        .btn-main {
            min-height: 44px;
            border-radius: 5px;
            border: 0;
            background: var(--navy);
            color: #fff;
            font-weight: 900;
        }

        .btn-main:hover,
        .btn-main:focus {
            background: var(--navy-soft);
            color: #fff;
        }

        .form-check-label {
            color: var(--muted);
            font-size: .86rem;
            font-weight: 700;
        }

        .alert {
            border-radius: 5px;
            padding: .65rem .75rem;
            font-size: .9rem;
        }

        .login-footer {
            padding: .75rem 1.15rem;
            border-top: 1px solid var(--line);
            background: #f8fafc;
            color: var(--muted);
            font-size: .76rem;
            font-weight: 700;
            text-align: center;
        }

        @media (max-width: 991.98px) {
            body {
                background: var(--page);
            }

            .login-page {
                min-height: auto;
                padding: 1.25rem 0;
            }

            .login-page > .container {
                border-radius: 10px;
            }

            .intro-panel {
                padding: 0;
                margin-bottom: 1rem;
            }

            .intro-panel p {
                color: rgba(255, 255, 255, .78);
            }

            .system-badge {
                color: #ffe5a0;
                background: rgba(212, 160, 23, .13);
            }

            .intro-panel h1 {
                color: #fff;
                font-size: 1.9rem;
            }

            .mini-status {
                display: none;
            }

            .login-card {
                margin: 0;
                width: 100%;
            }
        }

        @media (max-width: 575.98px) {
            .site-topbar .container {
                align-items: flex-start;
                flex-direction: column;
                padding-top: .85rem;
                padding-bottom: .85rem;
            }

            .topbar-link {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header class="site-topbar">
        <div class="container">
            <div class="brand">
                <div class="brand-mark">
                    <i class="bi bi-folder-check fs-4"></i>
                </div>
                <div>
                    <h1 class="brand-title">RTFTS</h1>
                    <p class="brand-subtitle">Real Time File Tracking System</p>
                </div>
            </div>

            <a class="topbar-link" href="{{ route('web.home') }}">
                <i class="bi bi-house-door"></i> Home
            </a>
        </div>
    </header>

    <main class="login-page">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <section class="intro-panel">
                        <span class="system-badge">
                            <i class="bi bi-shield-check"></i> Writ Section MIS
                        </span>
                        <h1>File movement tracking for the writ section</h1>
                        <p>
                            Scan, receive, send and trace writ files with department-wise accountability.
                        </p>

                        <div class="mini-status">
                            <div>
                                <strong>Scan</strong>
                                <span>Card access</span>
                            </div>
                            <div>
                                <strong>Track</strong>
                                <span>File journey</span>
                            </div>
                            <div>
                                <strong>Report</strong>
                                <span>Register output</span>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="col-lg-5">
                    <section class="login-card" id="passwordLogin">
                        <div class="login-card-header">
                            <h2>System Login</h2>
                            <p>Use card tap or user ID and password.</p>
                        </div>

                        <div class="login-card-body">
                            @if (session('status'))
                                <div class="alert alert-success">{{ session('status') }}</div>
                            @endif

                            @if (session('fail'))
                                <div class="alert alert-danger">{{ session('fail') }}</div>
                            @endif

                            <div class="scan-target" id="scanTarget" role="button" tabindex="0">
                                <div class="scan-icon">
                                    <i class="bi bi-upc-scan fs-4"></i>
                                </div>
                                <div>
                                    <strong>Tap Card</strong>
                                    <span>Hold card near the reader</span>
                                </div>
                            </div>
                            <div class="scan-status" id="scanStatus">
                                <i class="bi bi-bullseye"></i> Ready to scan
                            </div>

                            <form method="POST" action="{{ route('proximity.login') }}" id="cardForm">
                                @csrf
                                <input type="hidden" name="login_id" id="card_login_id" value="{{ old('login_id') }}">
                                <input
                                    type="password"
                                    id="card_login_mask"
                                    class="@error('login_id') is-invalid @enderror"
                                    value="{{ old('login_id') }}"
                                    autocomplete="off"
                                    autofocus
                                >
                                @error('login_id')
                                    <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                                @enderror

                                <button type="submit" class="visually-hidden" id="cardLoginButton" tabindex="-1" aria-hidden="true">
                                    <span id="cardLoginText">Login</span>
                                    <span id="cardLoginSpinner" class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span>
                                </button>
                            </form>

                            <div class="section-divider">User Login</div>

                            <form method="POST" action="{{ route('login') }}" id="passwordForm">
                                @csrf

                                <label class="form-label" for="expert_login_id">User ID</label>
                                <input
                                    type="text"
                                    id="expert_login_id"
                                    name="login_id"
                                    class="form-control @error('login_id') is-invalid @enderror"
                                    value="{{ old('login_id') }}"
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
                                    autocomplete="current-password"
                                    required
                                >
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror

                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                                    <label class="form-check-label" for="remember_me">Remember this login</label>
                                </div>

                                <button type="submit" class="btn btn-main w-100 mt-3">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Login
                                </button>
                            </form>
                        </div>

                        <div class="login-footer">
                            Technical Assistance by Access to Justice For Women, GIZ Bangladesh
                        </div>
                    </section>
                </div>
            </div>
        </div>
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
                return element && passwordPanel && passwordPanel.contains(element) && element !== target && !target?.contains(element);
            };

            const setStatus = (ready) => {
                if (!status) {
                    return;
                }

                status.innerHTML = ready
                    ? '<i class="bi bi-bullseye"></i> Ready to scan'
                    : '<i class="bi bi-cursor-text"></i> Tap scan area';
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
