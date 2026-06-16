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
            --blue: #0a4ea3;
            --blue-2: #0d63d6;
            --ice: #f3f7ff;
            --ink: #0b1b2c;
            --muted: #4a6178;
            --border: rgba(6, 42, 79, 0.12);
            --shadow: 0 18px 60px rgba(6, 42, 79, 0.16);
            --ring: 0 0 0 .22rem rgba(13, 99, 214, .22);
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: 'SolaimanLipi', "Segoe UI", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at 15% 20%, rgba(13, 99, 214, 0.18) 0%, transparent 35%),
                radial-gradient(circle at 90% 70%, rgba(10, 78, 163, 0.14) 0%, transparent 40%),
                linear-gradient(135deg, #eff5ff 0%, #f8fbff 45%, #ffffff 100%);
        }

        .topbar {
            position: fixed;
            inset: 0 0 auto 0;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 18px;
            z-index: 10;
            background: rgba(255,255,255,0.72);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--navy);
        }

        .brand .logo {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, rgba(13,99,214,.18), rgba(6,42,79,.10));
            border: 1px solid rgba(13,99,214,.20);
            box-shadow: 0 10px 24px rgba(6,42,79,.10);
            flex: 0 0 auto;
        }

        .brand .title {
            line-height: 1.1;
        }

        .brand .title h1 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 900;
            letter-spacing: .2px;
        }

        .brand .title p {
            margin: 0;
            font-size: .92rem;
            color: var(--muted);
            font-weight: 700;
        }

        .btn-admin {
            border: 1px solid rgba(13, 99, 214, 0.28);
            background: rgba(13, 99, 214, 0.08);
            color: var(--navy);
            font-weight: 900;
            border-radius: 14px;
            padding: .6rem .9rem;
            display: inline-flex;
            align-items: center;
            gap: .55rem;
        }
        .btn-admin:hover {
            background: rgba(13, 99, 214, 0.12);
            border-color: rgba(13, 99, 214, 0.40);
            color: var(--navy);
        }

        .screen {
            min-height: 100vh;
            padding-top: 92px;
            display: grid;
            place-items: center;
            padding-left: 14px;
            padding-right: 14px;
        }

        .panel {
            width: min(980px, 100%);
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.82);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .panel-inner {
            display: grid;
            grid-template-columns: 1.2fr .8fr;
        }

        @media (max-width: 992px) {
            .panel-inner { grid-template-columns: 1fr; }
        }

        .left {
            padding: 26px;
        }

        .right {
            padding: 26px;
            border-left: 1px solid var(--border);
            background: linear-gradient(180deg, rgba(13,99,214,.06), rgba(255,255,255,0));
        }
        @media (max-width: 992px) {
            .right { border-left: 0; border-top: 1px solid var(--border); }
        }

        .hero {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }

        .hero h2 {
            margin: 0;
            font-weight: 950;
            color: var(--navy);
            font-size: clamp(1.35rem, 2.2vw, 1.75rem);
        }

        .hero p {
            margin: .35rem 0 0 0;
            color: var(--muted);
            font-weight: 700;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .38rem .7rem;
            border-radius: 999px;
            font-weight: 900;
            font-size: .95rem;
            background: rgba(13,99,214,.10);
            border: 1px solid rgba(13,99,214,.18);
            color: var(--navy);
            white-space: nowrap;
        }

        .status-live {
            box-shadow: 0 0 0 .22rem rgba(13, 99, 214, 0.18);
        }

        .tap-zone {
            border-radius: 22px;
            border: 1.5px dashed rgba(13, 99, 214, 0.45);
            background: linear-gradient(180deg, rgba(13,99,214,.10), rgba(255,255,255,0));
            padding: 18px 18px;
            cursor: pointer;
            user-select: none;
        }

        .tap-zone .row1 {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 6px;
        }

        .tap-zone .icon {
            width: 46px;
            height: 46px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: rgba(13,99,214,.12);
            border: 1px solid rgba(13,99,214,.18);
            color: var(--navy);
            flex: 0 0 auto;
        }

        .tap-zone .big {
            font-size: 1.2rem;
            font-weight: 950;
            margin: 0;
            color: var(--navy);
        }

        .tap-zone .sub {
            margin: 0;
            color: var(--muted);
            font-weight: 700;
            font-size: 1.02rem;
        }

        .form-label {
            font-weight: 900;
            color: var(--navy);
            margin-top: 14px;
            margin-bottom: 6px;
        }

        .form-control {
            min-height: 58px;
            border-radius: 16px;
            font-size: 1.15rem;
            border-color: rgba(6, 42, 79, 0.18);
        }

        .form-control:focus {
            border-color: rgba(13, 99, 214, 0.55);
            box-shadow: var(--ring);
        }

        .btn-primaryish {
            min-height: 58px;
            border-radius: 16px;
            font-weight: 950;
            font-size: 1.12rem;
            border: 0;
            background: linear-gradient(135deg, var(--blue) 0%, var(--blue-2) 100%);
            box-shadow: 0 14px 34px rgba(13,99,214,.18);
        }
        .btn-primaryish:hover { filter: brightness(.98); }

        .micro {
            margin-top: 10px;
            color: var(--muted);
            font-weight: 700;
            font-size: .95rem;
        }

        /* Offcanvas styling */
        .offcanvas-header {
            border-bottom: 1px solid var(--border);
        }
        .offcanvas-title {
            font-weight: 950;
            color: var(--navy);
        }

        .btn-secondaryish {
            min-height: 56px;
            border-radius: 16px;
            font-weight: 950;
            border: 1px solid rgba(6, 42, 79, 0.18);
            background: rgba(6,42,79,.04);
            color: var(--navy);
        }
        .btn-secondaryish:hover {
            background: rgba(6,42,79,.07);
            border-color: rgba(6,42,79,.28);
        }

        .sub-link {
            color: var(--blue-2);
            text-decoration: none;
            font-weight: 900;
        }
        .sub-link:hover { text-decoration: underline; }

        .pin-wrap[hidden] { display: none !important; }
        .btn-linkish {
            background: transparent;
            border: 0;
            padding: 0;
            color: var(--blue-2);
            font-weight: 900;
            text-decoration: underline;
        }
        .btn-linkish:hover { filter: brightness(.95); }

        @media (prefers-reduced-motion: reduce) {
            * { scroll-behavior: auto !important; transition: none !important; }
        }
    </style>
</head>

<body>
<!-- Top Bar -->
<div class="topbar">
    <div class="brand">
        <div class="logo">
            <i class="bi bi-folder2-open fs-4"></i>
        </div>
        <div class="title">
            <h1>Real Time File Tracking System</h1>
            <p>লগইন পোর্টাল | Login Portal</p>
        </div>
    </div>

    <!-- Admin button opens offcanvas -->
    <button class="btn btn-admin" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminCanvas" aria-controls="adminCanvas">
        <i class="bi bi-person-lock"></i> Officer/Admin Login
    </button>
</div>

<div class="screen">
    <div class="panel">
        <div class="panel-inner">
            <!-- LEFT: Proximity Login -->
            <div class="left">
                <div class="hero">
                    <div>
                        <h2><i class="bi bi-upc-scan me-1"></i> কার্ড লগইন | Card Login</h2>
                        <p>কার্ড পাঞ্চ করুন এবং লগইন করুন — Card number will be hidden</p>
                    </div>
                    <span class="status-pill status-live" id="focusPill">
                        <i class="bi bi-bullseye"></i> Ready to Scan
                    </span>
                </div>

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                @if (session('fail'))
                    <div class="alert alert-danger">{{ session('fail') }}</div>
                @endif

                <div class="tap-zone" id="tapZone" role="button" tabindex="0" aria-label="Tap here then scan your card">
                    <div class="row1">
                        <div class="icon">
                            <i class="bi bi-credit-card-2-front fs-4"></i>
                        </div>
                        <div>
                            <p class="big mb-0">কার্ড পাঞ্চ করুন / Tap Your Card</p>
                            <p class="sub mb-0">শুধু আপনার কার্ড স্ক্যান করুন - স্বয়ংক্রিয়ভাবে লগইন হবে।</p>
                            <p class="sub mb-0">Just scan your card to login. No typing needed.</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('proximity.login') }}" id="cardForm" class="mt-3" onsubmit="handleCardSubmit(event)">
                    @csrf

                    <!-- Backend expects login_id; keep it unchanged -->
                    <input type="hidden" id="login_id" name="login_id" value="{{ old('login_id') }}">

                    <label for="login_id_mask" class="form-label">Card ID / কার্ড নম্বর (Hidden)</label>
                    <input
                        type="password"
                        id="login_id_mask"
                        class="form-control @error('login_id') is-invalid @enderror"
                        placeholder="Scan card here / এখানে কার্ড স্ক্যান করুন"
                        autocomplete="off"
                        autofocus
                    >
                    @error('login_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    <div class="micro">
                        Tip: টিপস: কার্সর না দেখালে যেকোনো জায়গায় ক্লিক করুন এবং কার্ড পাঞ্চ করুন।<br>Click anywhere if cursor is not blinking - focus returns automatically.
                    </div>

                    {{-- <div class="mt-3">
                        <button type="button" class="btn-linkish" id="togglePinBtn" aria-controls="pinWrap" aria-expanded="false">
                            Need PIN? (ঐচ্ছিক পিন)
                        </button>
                    </div>

                    <div class="mt-2 pin-wrap" id="pinWrap" hidden>
                        <label for="pin" class="form-label">PIN (Optional) / পিন (ঐচ্ছিক)</label>
                        <input type="password" id="pin" name="pin" class="form-control" placeholder="Optional PIN (if needed)">
                    </div> --}}

                    <button type="submit" class="btn btn-primaryish w-100 mt-3" id="cardLoginButton">
                        <span id="cardBtnText" style="color:#ffffff"><i class="bi bi-box-arrow-in-right me-1"></i> কার্ড দিয়ে লগইন করুন - Login by Card</span>
                        <span id="cardBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </form>
</div>

            <!-- RIGHT: Minimal help / guidance -->
            <div class="right">
                <h5 class="fw-black" style="font-weight: 950; color: var(--navy);">
                    How to Login (সহজ নিয়ম)
                </h5>
                <div class="mt-3">
                    <div class="d-flex gap-3 mb-3">
                        <div class="icon" style="width:44px;height:44px;border-radius:16px;">
                            <i class="bi bi-1-circle fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-weight: 950; color: var(--navy);">Tap your card</div>
                            <div class="text-muted" style="color: var(--muted) !important; font-weight: 700;">
                                কার্ড রিডারে কার্ড পাঞ্চ করুন
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-3 mb-3">
                        <div class="icon" style="width:44px;height:44px;border-radius:16px;">
                            <i class="bi bi-2-circle fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-weight: 950; color: var(--navy);">System will login</div>
                            <div class="text-muted" style="color: var(--muted) !important; font-weight: 700;">
                                সিস্টেম নিজে থেকেই লগইন করবে
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-3 mb-3">
                        <div class="icon" style="width:44px;height:44px;border-radius:16px;">
                            <i class="bi bi-3-circle fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-weight: 950; color: var(--navy);">If needed, Contact Assistant Registrar Office</div>
                            <div class="text-muted" style="color: var(--muted) !important; font-weight: 700;">
                                প্রয়োজন হলে এসিস্ট্যান্ট রেজিস্ট্রার অফিসে যোগাযোগ করুন
                            </div>
                        </div>
                    </div>

                    <hr style="border-color: var(--border);" />

                    <div class="text-muted" style="color: var(--muted) !important; font-weight: 700;">
                        Officer/Admin users can login from the top-right button.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- OFFCANVAS: Officer/Admin Login -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="adminCanvas" aria-labelledby="adminCanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="adminCanvasLabel"><i class="bi bi-person-lock me-1"></i> Officer/Admin Login</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <p class="text-muted" style="color: var(--muted) !important; font-weight: 700;">
            For admin/staff users: login with Login ID + password, or Login ID + face.
        </p>
        @if(session('admin_login_notice'))
            <div class="alert alert-info py-2">{{ session('admin_login_notice') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="passwordForm" onsubmit="handlePasswordSubmit(event)">
            @csrf

            <div class="mb-3">
                <label for="admin_login_id" class="form-label">Login ID / Card Number</label>
                <input type="text" id="admin_login_id" name="login_id"
                       class="form-control @error('login_id') is-invalid @enderror"
                       value="{{ old('login_id') }}"
                       placeholder="Enter your login ID"
                       required>
                @error('login_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password / পাসওয়ার্ড</label>
                <input type="password" id="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="Enter your password"
                       required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label" for="remember" style="font-weight: 800; color: var(--navy);">
                    Remember Me / মনে রাখুন
                </label>
            </div>

            <button type="submit" class="btn btn-primaryish w-100" id="passwordLoginButton">
                <span id="passwordBtnText"><i class="bi bi-box-arrow-in-right me-1"></i> Login</span>
                <span id="passwordBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            </button>

            <div class="mt-3 text-end">
                <a class="sub-link" href="{{ route('password.request') }}">Forgot your password?</a>
            </div>
        </form>
        <hr>

        <form method="POST" action="{{ route('login.face') }}" id="faceForm" onsubmit="handleFaceSubmit(event)">
            @csrf
            <div class="mb-3">
                <label for="face_login_id" class="form-label">Login ID / Card Number</label>
                <input type="text" id="face_login_id" name="login_id" class="form-control" value="{{ old('login_id') }}" placeholder="Enter login ID for face login" required>
            </div>

            @error('face_login')
                <div class="alert alert-danger py-2">{{ $message }}</div>
            @enderror

            <div class="mb-3">
                <video id="faceLoginVideo" class="w-100 rounded border bg-dark" autoplay muted playsinline style="min-height:220px; object-fit:cover;"></video>
            </div>

            <div id="faceLoginStatus" class="small mb-3 text-muted">Load models, start camera, then login with face.</div>

            <div class="d-grid gap-2">
                <button type="button" class="btn btn-secondaryish" id="startFaceLoginCameraBtn">
                    <i class="bi bi-camera-video me-1"></i> Start Camera
                </button>
                <button type="submit" class="btn btn-primaryish" id="faceLoginButton">
                    <span id="faceBtnText"><i class="bi bi-person-bounding-box me-1"></i> Login with Face</span>
                    <span id="faceBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </form>
    </div>
</div>

@if(session('show_admin_login'))
<div class="modal fade" id="quickLoginModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:20px; border:1px solid var(--border);">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-shield-lock me-1"></i> Quick Admin/Staff Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 mb-3">{{ session('admin_login_notice') }}</div>

                <div class="d-flex gap-2 mb-3">
                    <button type="button" id="quickUseFaceBtn" class="btn btn-primaryish flex-fill">Use Face</button>
                    <button type="button" id="quickUsePasswordBtn" class="btn btn-secondaryish flex-fill">Use Password</button>
                </div>

                <div id="quickFacePane">
                    <form method="POST" action="{{ route('login.face') }}" id="inlineFaceForm" onsubmit="handleInlineFaceSubmit(event)">
                        @csrf
                        <input type="hidden" name="login_id" value="{{ session('admin_login_id_prefill') }}">
                        <div class="mb-2">
                            <label class="form-label">Login ID</label>
                            <input type="text" class="form-control" value="{{ session('admin_login_id_prefill') }}" readonly>
                        </div>
                        <div class="mb-2">
                            <video id="inlineFaceLoginVideo" class="w-100 rounded border bg-dark" autoplay muted playsinline style="min-height:220px; object-fit:cover;"></video>
                        </div>
                        <div id="inlineFaceLoginStatus" class="small mb-2 text-muted">Camera will auto-start. Keep your face centered.</div>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-secondaryish" id="startInlineFaceCameraBtn">
                                <i class="bi bi-camera-video me-1"></i> Retry Camera
                            </button>
                            <button type="submit" class="btn btn-primaryish" id="inlineFaceLoginButton">
                                <span id="inlineFaceBtnText"><i class="bi bi-person-bounding-box me-1"></i> Login with Face</span>
                                <span id="inlineFaceBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                    </form>
                </div>

                <div id="quickPasswordPane" class="d-none">
                    <form method="POST" action="{{ route('login') }}" id="inlinePasswordForm" onsubmit="handleInlinePasswordSubmit(event)">
                        @csrf
                        <input type="hidden" name="login_id" value="{{ session('admin_login_id_prefill') }}">
                        <div class="mb-2">
                            <label class="form-label">Login ID</label>
                            <input type="text" class="form-control" value="{{ session('admin_login_id_prefill') }}" readonly>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" id="quickPasswordInput" class="form-control" required>
                        </div>
                        <div class="mb-2 form-check">
                            <input type="checkbox" class="form-check-input" id="inlineRemember" name="remember">
                            <label class="form-check-label" for="inlineRemember">Remember Me</label>
                        </div>
                        <button type="submit" class="btn btn-primaryish w-100" id="inlinePasswordLoginButton">
                            <span id="inlinePasswordBtnText"><i class="bi bi-box-arrow-in-right me-1"></i> Login with Password</span>
                            <span id="inlinePasswordBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

<script>
    // ===== Button loading state (no backend changes) =====
    function handlePasswordSubmit() {
        const button = document.getElementById('passwordLoginButton');
        document.getElementById('passwordBtnText').textContent = 'Logging in...';
        document.getElementById('passwordBtnSpinner').classList.remove('d-none');
        button.disabled = true;
    }

    function handleInlinePasswordSubmit() {
        const button = document.getElementById('inlinePasswordLoginButton');
        if (!button) return;
        document.getElementById('inlinePasswordBtnText').textContent = 'Logging in...';
        document.getElementById('inlinePasswordBtnSpinner').classList.remove('d-none');
        button.disabled = true;
    }

    const faceLogin = {
        video: null,
        statusEl: null,
        modelsLoaded: false,
        stream: null
    };

    function setFaceLoginStatus(message, type = 'muted') {
        if (!faceLogin.statusEl) return;
        faceLogin.statusEl.textContent = message;
        faceLogin.statusEl.className = 'small mb-3';
        if (type === 'success') {
            faceLogin.statusEl.classList.add('text-success');
            return;
        }
        if (type === 'error') {
            faceLogin.statusEl.classList.add('text-danger');
            return;
        }
        faceLogin.statusEl.classList.add('text-muted');
    }

    async function ensureFaceModelsLoaded() {
        if (faceLogin.modelsLoaded) return;
        if (!window.faceapi) {
            throw new Error('face-api.js failed to load.');
        }

        setFaceLoginStatus('Loading face models...');
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri('/models'),
            faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
            faceapi.nets.faceRecognitionNet.loadFromUri('/models'),
        ]);
        faceLogin.modelsLoaded = true;
        setFaceLoginStatus('Models loaded. Start camera.', 'success');
    }

    async function startFaceLoginCamera() {
        try {
            await ensureFaceModelsLoaded();

            if (faceLogin.stream) {
                faceLogin.video.srcObject = faceLogin.stream;
                await faceLogin.video.play();
                return true;
            }

            faceLogin.stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user' },
                audio: false
            });
            faceLogin.video.srcObject = faceLogin.stream;
            await faceLogin.video.play();
            setFaceLoginStatus('Camera started. Keep one face in frame.', 'success');
            return true;
        } catch (error) {
            setFaceLoginStatus(error.message || 'Could not start camera.', 'error');
            return false;
        }
    }

    async function captureFaceDescriptorForLogin() {
        if (!faceLogin.video) {
            throw new Error('Face login video is not available.');
        }

        if (!faceLogin.video.srcObject) {
            const started = await startFaceLoginCamera();
            if (!started || !faceLogin.video.srcObject) {
                throw new Error('Camera permission is required for face login.');
            }
        }

        setFaceLoginStatus('Detecting face...');
        const tinyOptions = new faceapi.TinyFaceDetectorOptions();
        const allFaces = await faceapi.detectAllFaces(faceLogin.video, tinyOptions);

        if (allFaces.length === 0) {
            throw new Error('No face detected.');
        }

        if (allFaces.length > 1) {
            throw new Error('Multiple faces detected.');
        }

        const detection = await faceapi
            .detectSingleFace(faceLogin.video, tinyOptions)
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!detection || !detection.descriptor) {
            throw new Error('Could not capture face descriptor.');
        }

        setFaceLoginStatus('Face captured. Logging in...', 'success');
        return Array.from(detection.descriptor);
    }

    async function handleFaceSubmit(event) {
        event.preventDefault();

        const button = document.getElementById('faceLoginButton');
        const text = document.getElementById('faceBtnText');
        const spinner = document.getElementById('faceBtnSpinner');
        const loginIdInput = document.getElementById('face_login_id');
        const form = event.target;

        if (!loginIdInput.value.trim()) {
            setFaceLoginStatus('Login ID is required for face login.', 'error');
            return;
        }

        form.querySelectorAll('input[data-face-descriptor=\"1\"]').forEach((node) => node.remove());

        button.disabled = true;
        text.textContent = 'Matching face...';
        spinner.classList.remove('d-none');

        try {
            const descriptor = await captureFaceDescriptorForLogin();

            descriptor.forEach((value, index) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `descriptor[${index}]`;
                input.value = String(value);
                input.setAttribute('data-face-descriptor', '1');
                form.appendChild(input);
            });

            form.submit();
        } catch (error) {
            setFaceLoginStatus(error.message || 'Face login failed.', 'error');
            button.disabled = false;
            text.innerHTML = '<i class=\"bi bi-person-bounding-box me-1\"></i> Login with Face';
            spinner.classList.add('d-none');
        }
    }

    const inlineFaceLogin = {
        video: null,
        statusEl: null,
        modelsLoaded: false,
        stream: null,
        autoDetectTimer: null,
        submitting: false
    };

    function setInlineFaceStatus(message, type = 'muted') {
        if (!inlineFaceLogin.statusEl) return;
        inlineFaceLogin.statusEl.textContent = message;
        inlineFaceLogin.statusEl.className = 'small mb-2';
        if (type === 'success') {
            inlineFaceLogin.statusEl.classList.add('text-success');
            return;
        }
        if (type === 'error') {
            inlineFaceLogin.statusEl.classList.add('text-danger');
            return;
        }
        inlineFaceLogin.statusEl.classList.add('text-muted');
    }

    async function ensureInlineFaceModelsLoaded() {
        if (inlineFaceLogin.modelsLoaded) return;
        if (!window.faceapi) {
            throw new Error('face-api.js failed to load.');
        }

        setInlineFaceStatus('Loading face models...');
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri('/models'),
            faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
            faceapi.nets.faceRecognitionNet.loadFromUri('/models'),
        ]);
        inlineFaceLogin.modelsLoaded = true;
        setInlineFaceStatus('Models loaded. Start camera.', 'success');
    }

    async function startInlineFaceCamera() {
        try {
            await ensureInlineFaceModelsLoaded();

            if (inlineFaceLogin.stream) {
                inlineFaceLogin.video.srcObject = inlineFaceLogin.stream;
                await inlineFaceLogin.video.play();
                return true;
            }

            inlineFaceLogin.stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user' },
                audio: false
            });
            inlineFaceLogin.video.srcObject = inlineFaceLogin.stream;
            await inlineFaceLogin.video.play();
            setInlineFaceStatus('Camera started. Keep one face in frame.', 'success');
            return true;
        } catch (error) {
            setInlineFaceStatus(error.message || 'Could not start camera.', 'error');
            return false;
        }
    }

    function stopInlineAutoDetection() {
        if (!inlineFaceLogin.autoDetectTimer) {
            return;
        }

        clearInterval(inlineFaceLogin.autoDetectTimer);
        inlineFaceLogin.autoDetectTimer = null;
    }

    async function captureInlineFaceDescriptor() {
        if (!inlineFaceLogin.video) {
            throw new Error('Face login video is not available.');
        }

        if (!inlineFaceLogin.video.srcObject) {
            const started = await startInlineFaceCamera();
            if (!started || !inlineFaceLogin.video.srcObject) {
                throw new Error('Camera permission is required for face login.');
            }
        }

        setInlineFaceStatus('Detecting face...');
        const tinyOptions = new faceapi.TinyFaceDetectorOptions();
        const allFaces = await faceapi.detectAllFaces(inlineFaceLogin.video, tinyOptions);

        if (allFaces.length === 0) {
            throw new Error('No face detected.');
        }

        if (allFaces.length > 1) {
            throw new Error('Multiple faces detected.');
        }

        const detection = await faceapi
            .detectSingleFace(inlineFaceLogin.video, tinyOptions)
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!detection || !detection.descriptor) {
            throw new Error('Could not capture face descriptor.');
        }

        setInlineFaceStatus('Face captured. Logging in...', 'success');
        return Array.from(detection.descriptor);
    }

    async function submitInlineFaceWithDescriptor(form, descriptor) {
        const button = document.getElementById('inlineFaceLoginButton');
        const text = document.getElementById('inlineFaceBtnText');
        const spinner = document.getElementById('inlineFaceBtnSpinner');

        if (!button || !text || !spinner) {
            return;
        }

        if (inlineFaceLogin.submitting) {
            return;
        }

        inlineFaceLogin.submitting = true;
        form.querySelectorAll('input[data-inline-face-descriptor=\"1\"]').forEach((node) => node.remove());

        button.disabled = true;
        text.textContent = 'Matching face...';
        spinner.classList.remove('d-none');

        try {
            descriptor.forEach((value, index) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `descriptor[${index}]`;
                input.value = String(value);
                input.setAttribute('data-inline-face-descriptor', '1');
                form.appendChild(input);
            });

            form.submit();
        } catch (error) {
            setInlineFaceStatus(error.message || 'Face login failed.', 'error');
            button.disabled = false;
            text.innerHTML = '<i class=\"bi bi-person-bounding-box me-1\"></i> Login with Face';
            spinner.classList.add('d-none');
            inlineFaceLogin.submitting = false;
        }
    }

    async function handleInlineFaceSubmit(event) {
        event.preventDefault();
        const form = event.target;

        try {
            const descriptor = await captureInlineFaceDescriptor();
            await submitInlineFaceWithDescriptor(form, descriptor);
        } catch (error) {
            setInlineFaceStatus(error.message || 'Face login failed.', 'error');
        }
    }

    async function autoDetectAndSubmitInlineFace() {
        if (inlineFaceLogin.submitting) {
            return;
        }

        const form = document.getElementById('inlineFaceForm');
        const facePane = document.getElementById('quickFacePane');
        if (!form || !facePane || facePane.classList.contains('d-none')) {
            return;
        }

        try {
            if (!inlineFaceLogin.video || !inlineFaceLogin.video.srcObject) {
                return;
            }

            const tinyOptions = new faceapi.TinyFaceDetectorOptions();
            const allFaces = await faceapi.detectAllFaces(inlineFaceLogin.video, tinyOptions);

            if (allFaces.length === 0) {
                setInlineFaceStatus('Looking for face...');
                return;
            }

            if (allFaces.length > 1) {
                setInlineFaceStatus('Multiple faces detected. Keep only one face in frame.', 'error');
                return;
            }

            const detection = await faceapi
                .detectSingleFace(inlineFaceLogin.video, tinyOptions)
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (!detection || !detection.descriptor) {
                setInlineFaceStatus('Face found but descriptor failed. Adjust light/angle.', 'error');
                return;
            }

            setInlineFaceStatus('Face detected. Logging in...', 'success');
            await submitInlineFaceWithDescriptor(form, Array.from(detection.descriptor));
        } catch (error) {
            setInlineFaceStatus(error.message || 'Auto face detection failed.', 'error');
        }
    }

    function handleCardSubmit() {
        const button = document.getElementById('cardLoginButton');
        document.getElementById('cardBtnText').textContent = 'Logging in...';
        document.getElementById('cardBtnSpinner').classList.remove('d-none');
        button.disabled = true;
    }

    // ===== Proximity UX: Masked input + always refocus to scan box =====
    (function () {
        const hidden = document.getElementById('login_id');          // backend field
        const masked = document.getElementById('login_id_mask');     // user-visible masked
        const focusPill = document.getElementById('focusPill');
        const tapZone = document.getElementById('tapZone');

        const pinWrap = document.getElementById('pinWrap');
        const togglePinBtn = document.getElementById('togglePinBtn');

        if (!hidden || !masked) return;

        // If validation failed and old('login_id') exists, reflect it in masked input (still hidden)
        if (hidden.value && !masked.value) masked.value = hidden.value;

        // Sync masked -> hidden
        const sync = () => { hidden.value = masked.value; };

        const IDLE_MS = 6000;         // autofocus after idle time
        const CLICK_REFOCUS_MS = 350; // after click anywhere
        let idleTimer = null;

        const adminCanvas = document.getElementById('adminCanvas');
        const isAdminCanvasOpen = () => adminCanvas && adminCanvas.classList.contains('show');

        const isPasswordArea = (el) => {
            if (!el) return false;
            const id = el.id || '';
            return ['admin_login_id', 'face_login_id', 'password', 'remember'].includes(id)
                || el.closest('#passwordForm')
                || el.closest('#faceForm')
                || el.closest('#inlinePasswordForm')
                || el.closest('#inlineFaceForm');
        };

        const setPill = (focused) => {
            if (!focusPill) return;
            focusPill.classList.toggle('status-live', focused);
            focusPill.innerHTML = focused
                ? '<i class="bi bi-bullseye"></i> Ready to Scan'
                : '<i class="bi bi-hourglass-split"></i> Click to Focus';
        };

        const focusCard = () => {
            // If admin offcanvas is open, do not steal focus
            if (isAdminCanvasOpen()) return;

            // Don't steal focus when user is typing in password area
            const active = document.activeElement;
            if (isPasswordArea(active)) return;

            masked.focus({ preventScroll: true });
            const v = masked.value || '';
            try { masked.setSelectionRange(v.length, v.length); } catch (e) {}
        };

        const resetIdle = () => {
            if (idleTimer) clearTimeout(idleTimer);
            idleTimer = setTimeout(() => focusCard(), IDLE_MS);
        };

        masked.addEventListener('focus', () => setPill(true));
        masked.addEventListener('blur', () => setPill(false));

        // RFID scanner types here
        masked.addEventListener('input', () => {
            sync();
            resetIdle();
        });

        window.addEventListener('load', () => {
            sync();
            focusCard();
            resetIdle();
        });

        // Click anywhere -> refocus after a short delay (unless admin canvas open)
        document.addEventListener('pointerdown', (e) => {
            if (isAdminCanvasOpen()) return;
            setTimeout(() => focusCard(), CLICK_REFOCUS_MS);
            resetIdle();
        });

        // Any key press outside password area -> keep scan focus ready
        document.addEventListener('keydown', (e) => {
            if (isAdminCanvasOpen()) return;
            if (isPasswordArea(document.activeElement)) { resetIdle(); return; }
            if (e.key === 'Tab') { resetIdle(); return; }
            resetIdle();
            focusCard();
        });

        // Tap zone focuses scan box
        if (tapZone) {
            tapZone.addEventListener('click', focusCard);
            tapZone.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    focusCard();
                }
            });
        }

        // Toggle PIN
        if (togglePinBtn && pinWrap) {
            togglePinBtn.addEventListener('click', () => {
                const isHidden = pinWrap.hasAttribute('hidden');
                if (isHidden) {
                    pinWrap.removeAttribute('hidden');
                    togglePinBtn.setAttribute('aria-expanded', 'true');
                    togglePinBtn.textContent = 'Hide PIN (পিন লুকান)';
                    focusCard();
                } else {
                    pinWrap.setAttribute('hidden', '');
                    togglePinBtn.setAttribute('aria-expanded', 'false');
                    togglePinBtn.textContent = 'Need PIN? (ঐচ্ছিক পিন)';
                    focusCard();
                }
            });
        }

        // When offcanvas closes, restore focus to scan
        if (adminCanvas) {
            adminCanvas.addEventListener('hidden.bs.offcanvas', () => {
                setTimeout(() => focusCard(), 250);
            });
        }
    })();

    (function () {
        faceLogin.video = document.getElementById('faceLoginVideo');
        faceLogin.statusEl = document.getElementById('faceLoginStatus');
        const startBtn = document.getElementById('startFaceLoginCameraBtn');

        if (!faceLogin.video || !faceLogin.statusEl || !startBtn) {
            return;
        }

        startBtn.addEventListener('click', startFaceLoginCamera);
        ensureFaceModelsLoaded().catch((error) => {
            setFaceLoginStatus(error.message || 'Failed to load face models.', 'error');
        });
    })();

    (function () {
        const prefillLoginId = @json((string) session('admin_login_id_prefill', ''));
        const adminLoginIdInput = document.getElementById('admin_login_id');
        const faceLoginIdInput = document.getElementById('face_login_id');

        if (prefillLoginId) {
            if (adminLoginIdInput) adminLoginIdInput.value = prefillLoginId;
            if (faceLoginIdInput) faceLoginIdInput.value = prefillLoginId;
        }
    })();

    (function () {
        inlineFaceLogin.video = document.getElementById('inlineFaceLoginVideo');
        inlineFaceLogin.statusEl = document.getElementById('inlineFaceLoginStatus');
        const inlineStartBtn = document.getElementById('startInlineFaceCameraBtn');

        if (!inlineFaceLogin.video || !inlineFaceLogin.statusEl || !inlineStartBtn) {
            return;
        }

        inlineStartBtn.addEventListener('click', startInlineFaceCamera);
        ensureInlineFaceModelsLoaded().catch((error) => {
            setInlineFaceStatus(error.message || 'Failed to load face models.', 'error');
        });
    })();

    (function () {
        const shouldShowQuickModal = @json((bool) session('show_admin_login'));
        const modalEl = document.getElementById('quickLoginModal');
        const facePane = document.getElementById('quickFacePane');
        const passwordPane = document.getElementById('quickPasswordPane');
        const useFaceBtn = document.getElementById('quickUseFaceBtn');
        const usePasswordBtn = document.getElementById('quickUsePasswordBtn');
        const passwordInput = document.getElementById('quickPasswordInput');

        if (!modalEl || !facePane || !passwordPane || !useFaceBtn || !usePasswordBtn) {
            return;
        }

        const showPane = (mode) => {
            const useFace = mode === 'face';

            facePane.classList.toggle('d-none', !useFace);
            passwordPane.classList.toggle('d-none', useFace);

            useFaceBtn.classList.toggle('btn-primaryish', useFace);
            useFaceBtn.classList.toggle('btn-secondaryish', !useFace);
            usePasswordBtn.classList.toggle('btn-primaryish', !useFace);
            usePasswordBtn.classList.toggle('btn-secondaryish', useFace);

            if (useFace) {
                startInlineFaceCamera();
                stopInlineAutoDetection();
                inlineFaceLogin.autoDetectTimer = setInterval(autoDetectAndSubmitInlineFace, 1200);
                return;
            }

            stopInlineAutoDetection();
            setTimeout(() => passwordInput?.focus(), 120);
        };

        useFaceBtn.addEventListener('click', () => showPane('face'));
        usePasswordBtn.addEventListener('click', () => showPane('password'));

        if (!shouldShowQuickModal || !window.bootstrap || !window.bootstrap.Modal) {
            return;
        }

        const modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
        modalEl.addEventListener('hidden.bs.modal', () => stopInlineAutoDetection());
        modalEl.addEventListener('shown.bs.modal', () => showPane('face'), { once: true });
        modal.show();
    })();
</script>
</body>
</html>
