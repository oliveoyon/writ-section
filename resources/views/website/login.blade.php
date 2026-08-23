@extends('website.layouts.weblayout')

@section('title', __('writ.auth.login_page_title') . ' | RTFTS')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<div class="lawyer-login-page">
    <div class="container">
        <div class="lawyer-login-shell">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <section class="lawyer-login-intro">
                        <span class="system-badge">
                            <i class="bi bi-person-vcard"></i> Lawyer Access
                        </span>
                        <h1>RTFTS lawyer case portal</h1>
                        <p>
                            Submit writ petitions, follow case progress and access filed case documents through one secure account.
                        </p>

                        <div class="portal-points">
                            <div>
                                <i class="bi bi-file-earmark-plus"></i>
                                <strong>Submit</strong>
                                <span>Case filing</span>
                            </div>
                            <div>
                                <i class="bi bi-search"></i>
                                <strong>Track</strong>
                                <span>Case progress</span>
                            </div>
                            <div>
                                <i class="bi bi-folder-check"></i>
                                <strong>Manage</strong>
                                <span>Documents</span>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="col-lg-5">
                    <section class="lawyer-login-card">
                        <div class="lawyer-login-card-header">
                            <h2>{{ __('writ.auth.login_title') }}</h2>
                            <p>{{ __('writ.auth.login_subtitle') }}</p>
                        </div>

                        <div class="lawyer-login-card-body">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('lawyer.login.submit') }}">
                                @csrf

                                <div class="mb-3">
                                    <label for="email_or_phone" class="form-label">
                                        {{ __('writ.auth.email_or_phone') }}
                                    </label>
                                    <div class="input-wrap">
                                        <i class="bi bi-envelope-at"></i>
                                        <input type="text" id="email_or_phone" name="email_or_phone" class="form-control form-control-lg @error('email_or_phone') is-invalid @enderror"
                                            value="{{ old('email_or_phone') }}" autocomplete="username" autofocus required>
                                    </div>
                                    @error('email_or_phone')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        {{ __('writ.auth.password') }}
                                    </label>
                                    <div class="input-wrap">
                                        <i class="bi bi-shield-lock"></i>
                                        <input type="password" id="password" name="password" class="form-control form-control-lg @error('password') is-invalid @enderror"
                                            autocomplete="current-password" required>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">{{ __('writ.auth.remember_me') }}</label>
                                </div>

                                <button type="submit" class="btn btn-lawyer-login btn-lg w-100">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> {{ __('writ.auth.login_button') }}
                                </button>
                            </form>

                            <div class="auth-switch text-center mt-4">
                                <span>
                                    {{ __('writ.auth.no_account') }}
                                </span>
                                <a href="{{ route('lawyer.register') }}" class="text-decoration-none">
                                    {{ __('writ.auth.register_now') }}
                                </a>
                            </div>
                        </div>

                        <div class="lawyer-login-footer">
                            Technical Assistance by Access to Justice For Women, GIZ Bangladesh
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .lawyer-login-page {
        min-height: calc(100vh - 64px);
        display: grid;
        align-items: center;
        padding: 2rem 0;
        background: linear-gradient(180deg, #f7f9fc 0%, #eef3f8 100%);
    }

    .lawyer-login-shell {
        position: relative;
        overflow: hidden;
        border-radius: 12px;
        padding: clamp(1.1rem, 3vw, 2.1rem);
        background: linear-gradient(135deg, rgba(0, 40, 77, .98) 0%, rgba(5, 60, 104, .98) 62%, rgba(17, 105, 109, .96) 100%);
        box-shadow: 0 22px 60px rgba(16, 32, 51, .18);
    }

    .lawyer-login-shell::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(90deg, rgba(255, 255, 255, .06) 1px, transparent 1px),
            linear-gradient(180deg, rgba(255, 255, 255, .05) 1px, transparent 1px);
        background-size: 34px 34px;
        opacity: .34;
        pointer-events: none;
    }

    .lawyer-login-shell::after {
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

    .lawyer-login-shell > .row {
        position: relative;
        z-index: 1;
    }

    .lawyer-login-intro {
        color: #fff;
        padding: .4rem 1.5rem .4rem .4rem;
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

    .lawyer-login-intro h1 {
        margin: 1rem 0 .65rem;
        max-width: 560px;
        color: #fff;
        font-size: clamp(2rem, 4vw, 3.1rem);
        line-height: 1.05;
        font-weight: 900;
        letter-spacing: 0;
        text-shadow: 0 2px 10px rgba(0, 0, 0, .14);
    }

    .lawyer-login-intro p {
        max-width: 520px;
        margin: 0;
        color: rgba(255, 255, 255, .78);
        font-size: 1rem;
        line-height: 1.65;
        font-weight: 600;
    }

    .portal-points {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .65rem;
        max-width: 560px;
        margin-top: 1.35rem;
    }

    .portal-points div {
        border: 1px solid rgba(255, 255, 255, .18);
        border-left: 3px solid rgba(212, 160, 23, .85);
        border-radius: 5px;
        background: rgba(255, 255, 255, .08);
        padding: .75rem;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .07);
    }

    .portal-points i {
        display: block;
        color: #ffe5a0;
        font-size: 1.1rem;
        margin-bottom: .25rem;
    }

    .portal-points strong {
        display: block;
        color: #fff;
        font-size: .92rem;
        font-weight: 900;
    }

    .portal-points span {
        color: rgba(255, 255, 255, .68);
        font-size: .78rem;
        font-weight: 700;
    }

    .lawyer-login-card {
        width: min(440px, 100%);
        margin-left: auto;
        background: #fff;
        border: 1px solid rgba(0, 40, 77, .12);
        border-radius: 8px;
        box-shadow: 0 18px 45px rgba(0, 0, 0, .22);
        overflow: hidden;
    }

    .lawyer-login-card-header {
        padding: 1.1rem 1.15rem;
        border-top: 4px solid #d4a017;
        border-bottom: 1px solid #d9e2ec;
        background: linear-gradient(180deg, #ffffff 0%, #f7fafc 100%);
    }

    .lawyer-login-card-header h2 {
        margin: 0;
        color: #00284d;
        font-size: 1.22rem;
        font-weight: 900;
        letter-spacing: 0;
    }

    .lawyer-login-card-header p {
        margin: .2rem 0 0;
        color: #607086;
        font-size: .86rem;
        font-weight: 600;
    }

    .lawyer-login-card-body {
        padding: 1rem 1.15rem 1.15rem;
    }

    .lawyer-login-card .form-label {
        margin-bottom: .36rem;
        color: #00284d;
        font-size: .88rem;
        font-weight: 800;
    }

    .input-wrap {
        position: relative;
    }

    .input-wrap i {
        position: absolute;
        left: .85rem;
        top: 50%;
        transform: translateY(-50%);
        color: #7a8899;
        pointer-events: none;
    }

    .lawyer-login-card .form-control {
        min-height: 44px;
        border-radius: 5px;
        border-color: #cfd9e4;
        color: #132238;
        font-size: .98rem;
        font-weight: 600;
        padding-left: 2.35rem;
    }

    .lawyer-login-card .form-control:focus {
        border-color: #d4a017;
        box-shadow: 0 0 0 .2rem rgba(212, 160, 23, .14);
    }

    .lawyer-login-card .form-check-label {
        color: #607086;
        font-size: .86rem;
        font-weight: 700;
    }

    .btn-lawyer-login {
        min-height: 44px;
        border-radius: 5px;
        border: 0;
        background: #00284d;
        color: #fff;
        font-weight: 900;
    }

    .btn-lawyer-login:hover,
    .btn-lawyer-login:focus {
        background: #073b70;
        color: #fff;
    }

    .auth-switch {
        color: #607086;
        font-size: .9rem;
        font-weight: 700;
    }

    .auth-switch a {
        color: #00284d;
        font-weight: 900;
    }

    .lawyer-login-footer {
        padding: .75rem 1.15rem;
        border-top: 1px solid #d9e2ec;
        background: #f8fafc;
        color: #607086;
        font-size: .76rem;
        font-weight: 700;
        text-align: center;
    }

    @media (max-width: 575.98px) {
        .lawyer-login-page {
            align-items: flex-start;
            padding: 1.25rem 0;
        }

        .lawyer-login-shell {
            border-radius: 10px;
        }

        .lawyer-login-intro {
            padding: 0;
        }

        .lawyer-login-intro h1 {
            font-size: 1.9rem;
        }

        .portal-points {
            display: none;
        }

        .lawyer-login-card {
            margin: 0;
            width: 100%;
        }
    }
</style>

@endsection
