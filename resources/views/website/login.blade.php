@extends('website.layouts.weblayout')

@section('title', __('writ.auth.login_page_title') . ' | RTFTS')

@section('content')

<div class="container auth-page py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">

            <div class="auth-panel">

                    <h3 class="auth-title text-center mb-4">
                        {{ __('writ.auth.login_title') }}
                    </h3>

                    {{-- Success Message --}}
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
                            <input type="text" id="email_or_phone" name="email_or_phone" class="form-control form-control-lg @error('email_or_phone') is-invalid @enderror"
                                   value="{{ old('email_or_phone') }}" autocomplete="username" autofocus required>
                            @error('email_or_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                {{ __('writ.auth.password') }}
                            </label>
                            <input type="password" id="password" name="password" class="form-control form-control-lg @error('password') is-invalid @enderror"
                                   autocomplete="current-password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label" for="remember">{{ __('writ.auth.remember_me') }}</label>
                        </div>

                        <button type="submit" class="btn btn-gold btn-lg w-100">
                            {{ __('writ.auth.login_button') }}
                        </button>
                    </form>

                    <div class="auth-switch text-center mt-4">
                        <span class="text-muted">
                            {{ __('writ.auth.no_account') }}
                        </span>
                        <a href="{{ route('lawyer.register') }}" class="text-decoration-none" style="color:#00284d;">
                            {{ __('writ.auth.register_now') }}
                        </a>
                    </div>

            </div>

        </div>
    </div>
</div>

<style>
    .auth-page { min-height: calc(100vh - 64px); display: flex; align-items: center; }
    .auth-page > .row { width: 100%; }
    .auth-panel { padding: 2rem; border: 1px solid #e5e7eb; border-top: 4px solid #d4a017; background: #fff; box-shadow: 0 10px 28px rgba(0, 40, 77, .08); }
    .auth-title { color: #003366; font-size: 1.55rem; font-weight: 700; }
    .auth-panel .form-label { color: #374151; font-weight: 600; }
    .auth-panel .form-control { min-height: 50px; }
    .auth-switch { color: #6b7280; }
    .auth-switch a { color: #00284d; font-weight: 600; }
    @media (max-width: 575.98px) {
        .auth-page { align-items: flex-start; padding-top: 2rem !important; }
        .auth-panel { padding: 1.25rem; }
    }
</style>

@endsection
