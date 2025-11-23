@extends('website.layouts.weblayout')

@section('title', __('writ.auth.login_page_title'))

@section('content')

<div class="container py-5" style="min-height: 70vh; margin-top: 30px;">
    <div class="row justify-content-center">
        <div class="col-md-5">

            <div class="card shadow-lg border-0 rounded-3">
                <div class="card-body p-4">

                    <h3 class="text-center mb-2" style="color:#003366; font-weight:700;">
                        {{ __('writ.auth.login_title') }}
                    </h3>

                    <p class="text-center text-muted mb-4">
                        {{ __('writ.auth.login_subtitle') }}
                    </p>

                    {{-- Success Message --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- Validation Errors --}}
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('lawyer.login.submit') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">
                                {{ __('writ.auth.email_or_phone') }}
                            </label>
                            <input type="text" name="email_or_phone" class="form-control @error('email_or_phone') is-invalid @enderror"
                                   value="{{ old('email_or_phone') }}"
                                   placeholder="{{ __('writ.auth.email_or_phone_placeholder') }}">
                            @error('email_or_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                {{ __('writ.auth.password') }}
                            </label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                   placeholder="{{ __('writ.auth.password_placeholder') }}">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <div>
                                <input type="checkbox" id="remember" name="remember">
                                <label for="remember">{{ __('writ.auth.remember_me') }}</label>
                            </div>
                            <a href="#" class="text-decoration-none" style="color:#00284d;">
                                {{ __('writ.auth.forgot_password') }}
                            </a>
                        </div>

                        <button type="submit" class="btn btn-gold w-100 py-2">
                            {{ __('writ.auth.login_button') }}
                        </button>
                    </form>

                    <div class="text-center mt-3">
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
</div>

@endsection
