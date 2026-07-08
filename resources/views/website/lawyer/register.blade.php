@extends('website.layouts.weblayout')

@section('title', __('writ.lawyer.register_page_title') . ' | RTFTS')

@section('content')

    <div class="container auth-page py-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">

                <div class="auth-panel">

                        <h3 class="auth-title text-center mb-4">
                            {{ __('writ.lawyer.register_page_title') }}
                        </h3>

                        {{-- Success Message --}}
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <div id="lawyer-stepper">

                            {{-- ---------------- STEP 1 ---------------- --}}
                            <div class="step step-1" style="{{ $errors->any() ? 'display:none;' : '' }}">

                                <label class="form-label">
                                    {{ __('writ.lawyer.enter_member_id') }}
                                </label>

                                <input type="text" class="form-control form-control-lg mb-2 @error('member_id') is-invalid @enderror"
                                    id="member_id"
                                    value="{{ old('member_id') }}">

                                <div class="invalid-feedback" id="member_error">
                                    @error('member_id')
                                        {{ $message }}
                                    @enderror
                                </div>

                                <button
                                    class="btn btn-gold btn-lg w-100 mt-3 d-flex justify-content-center align-items-center gap-2"
                                    id="check_member_btn">

                                    <!-- Spinner -->
                                    <span id="loader" style="display:none;">
                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                        {{ __('writ.lawyer.loading') }}
                                    </span>

                                    <!-- Original Button Text -->
                                    <span id="btn_text">{{ __('writ.lawyer.check_member') }}</span>
                                </button>

                            </div>

                            {{-- ---------------- STEP 2 ---------------- --}}
                            <div class="step step-2" style="{{ $errors->any() ? 'display:block;' : 'display:none;' }}">

                                <form method="POST" action="{{ route('lawyer.register.submit') }}">
                                    @csrf

                                    <input type="hidden" id="form_picture" name="picture">
                                    <input type="hidden" id="form_barDateOfJoining" name="barDateOfJoining">
                                    <input type="hidden" id="form_barDateOfEnrollment" name="barDateOfEnrollment">
                                    <input type="hidden" id="form_barCourtType" name="barCourtType">
                                    <input type="hidden" id="form_status" name="status">


                                    <input type="hidden" name="member_id" id="form_member_id"
                                        value="{{ old('member_id') }}">

                                    <div class="mb-3">
                                        <label class="form-label">{{ __('writ.lawyer.full_name') }}</label>
                                        <input type="text" name="full_name" id="form_full_name"
                                            class="form-control form-control-lg @error('full_name') is-invalid @enderror"
                                            value="{{ old('full_name') }}" required>
                                        @error('full_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">{{ __('writ.lawyer.phone') }}</label>
                                        <input type="text" name="phone" id="form_phone"
                                            class="form-control form-control-lg @error('phone') is-invalid @enderror"
                                            value="{{ old('phone') }}" required>
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">{{ __('writ.lawyer.email') }}</label>
                                        <input type="email" name="email"
                                            class="form-control form-control-lg @error('email') is-invalid @enderror"
                                            value="{{ old('email') }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">{{ __('writ.lawyer.password') }}</label>
                                            <input type="password" name="password"
                                                class="form-control form-control-lg @error('password') is-invalid @enderror" required>
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">{{ __('writ.lawyer.confirm_password') }}</label>
                                            <input type="password" name="password_confirmation" class="form-control form-control-lg"
                                                required>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-gold btn-lg w-100">
                                        {{ __('writ.lawyer.register') }}
                                    </button>

                                </form>
                            </div>

                        </div>

                        <div class="auth-switch text-center mt-4">
                            <a href="{{ route('lawyer.login') }}">{{ __('writ.auth.login_button') }}</a>
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
        .auth-switch a { color: #00284d; font-weight: 600; text-decoration: none; }
        @media (max-width: 575.98px) {
            .auth-page { align-items: flex-start; padding-top: 2rem !important; }
            .auth-panel { padding: 1.25rem; }
        }
    </style>


    {{-- ---------------- JS (unchanged) ---------------- --}}
    <script>
        document.getElementById('check_member_btn').addEventListener('click', function(e) {
            e.preventDefault();

            let memberId = document.getElementById('member_id').value;
            let loader = document.getElementById('loader');
            let btnText = document.getElementById('btn_text');
            let errorDiv = document.getElementById('member_error');

            loader.style.display = 'inline';
            btnText.style.display = 'none';
            this.disabled = true;
            errorDiv.innerText = '';

            fetch('{{ route('lawyer.check-member') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        member_id: memberId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    loader.style.display = 'none';
                    btnText.style.display = 'inline';
                    this.disabled = false;
                    errorDiv.style.display = 'block';

                    if (data.found) {
                        document.querySelector('.step-1').style.display = 'none';
                        document.querySelector('.step-2').style.display = 'block';

                        // Existing fields
                        document.getElementById('form_member_id').value = data.member.memberId;
                        document.getElementById('form_full_name').value = data.member.memberName;
                        document.getElementById('form_phone').value = data.member.mobile;

                        // New fields
                        document.getElementById('form_picture').value = data.member.picture || '';
                        document.getElementById('form_barDateOfJoining').value = data.member.barDateOfJoining ||
                            '';
                        document.getElementById('form_barDateOfEnrollment').value = data.member
                            .barDateOfEnrollment || '';
                        document.getElementById('form_barCourtType').value = data.member.barCourtType || '';
                        document.getElementById('form_status').value = data.member.status || 'active';

                        errorDiv.innerText = '';
                    } else {
                        document.querySelector('.step-1').style.display = 'block';
                        document.querySelector('.step-2').style.display = 'none';
                        errorDiv.innerText = data.message || '{{ __('writ.lawyer.member_not_found') }}';
                    }
                })
                .catch(err => {
                    loader.style.display = 'none';
                    btnText.style.display = 'inline';
                    this.disabled = false;
                    errorDiv.innerText = '{{ __('writ.lawyer.api_error') }}';
                });
        });
    </script>

@endsection
