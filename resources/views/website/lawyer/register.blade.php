@extends('website.layouts.weblayout')

@section('title', __('writ.lawyer.register_page_title') . ' | RTFTS')

@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <div class="lawyer-register-page">
        <div class="container">
            <div class="lawyer-register-shell">
                <div class="row align-items-center g-4">
                    <div class="col-lg-6">
                        <section class="lawyer-register-intro">
                            <span class="system-badge">
                                <i class="bi bi-person-plus"></i> Lawyer Registration
                            </span>
                            <h1>Create your RTFTS lawyer account</h1>
                            <p>
                                Register with SCB membership verification or continue manually when membership data is not available.
                            </p>

                            <div class="register-points">
                                <div>
                                    <i class="bi bi-patch-check"></i>
                                    <strong>Verify</strong>
                                    <span>SCB membership</span>
                                </div>
                                <div>
                                    <i class="bi bi-person-lines-fill"></i>
                                    <strong>Register</strong>
                                    <span>Account details</span>
                                </div>
                                <div>
                                    <i class="bi bi-hourglass-split"></i>
                                    <strong>Approval</strong>
                                    <span>Admin review</span>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="col-lg-6">
                        <section class="lawyer-register-card">
                            <div class="lawyer-register-card-header">
                                <h2>{{ __('writ.lawyer.register_page_title') }}</h2>
                                <p>SCB member lookup or manual registration</p>
                            </div>

                            <div class="lawyer-register-card-body">
                                @if (session('success'))
                                    <div class="alert alert-success">
                                        {{ session('success') }}
                                    </div>
                                @endif

                                <div id="lawyer-stepper">
                                    <div class="step step-1" style="{{ $errors->any() ? 'display:none;' : '' }}">
                                        <div class="step-heading">
                                            <span>Step 1</span>
                                            <strong>Find Membership</strong>
                                        </div>

                                        <label class="form-label">
                                            {{ __('writ.lawyer.enter_member_id') }}
                                        </label>

                                        <div class="input-wrap">
                                            <i class="bi bi-person-vcard"></i>
                                            <input type="text" class="form-control form-control-lg mb-2 @error('member_id') is-invalid @enderror"
                                                id="member_id"
                                                value="{{ old('member_id') }}">
                                        </div>

                                        <div class="invalid-feedback" id="member_error">
                                            @error('member_id')
                                                {{ $message }}
                                            @enderror
                                        </div>

                                        <button
                                            class="btn btn-register-main btn-lg w-100 mt-3 d-flex justify-content-center align-items-center gap-2"
                                            id="check_member_btn">
                                            <span id="loader" style="display:none;">
                                                <span class="spinner-border spinner-border-sm me-2"></span>
                                                {{ __('writ.lawyer.loading') }}
                                            </span>
                                            <span id="btn_text">
                                                <i class="bi bi-search me-1"></i> {{ __('writ.lawyer.check_member') }}
                                            </span>
                                        </button>

                                        <button type="button" class="btn btn-link w-100 mt-2 register-manual-link" id="manual_register_btn">
                                            {{ __('writ.lawyer.not_bar_member') }}
                                        </button>
                                    </div>

                                    <div class="step step-2" style="{{ $errors->any() ? 'display:block;' : 'display:none;' }}">
                                        <div class="step-heading">
                                            <span>Step 2</span>
                                            <strong>Account Details</strong>
                                        </div>

                                        <form method="POST" action="{{ route('lawyer.register.submit') }}">
                                            @csrf

                                            <input type="hidden" id="form_picture" name="picture">
                                            <input type="hidden" id="form_barDateOfJoining" name="barDateOfJoining">
                                            <input type="hidden" id="form_barDateOfEnrollment" name="barDateOfEnrollment">
                                            <input type="hidden" id="form_barCourtType" name="barCourtType">
                                            <input type="hidden" id="form_status" name="status">

                                            <div class="mb-3" id="member_id_field" style="{{ old('member_id') ? 'display:block;' : 'display:none;' }}">
                                                <label class="form-label">{{ __('writ.lawyer.enter_member_id') }}</label>
                                                <div class="input-wrap">
                                                    <i class="bi bi-person-vcard"></i>
                                                    <input type="text" name="member_id" id="form_member_id"
                                                        class="form-control form-control-lg @error('member_id') is-invalid @enderror"
                                                        value="{{ old('member_id') }}">
                                                </div>
                                                @error('member_id')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">{{ __('writ.lawyer.full_name') }}</label>
                                                <div class="input-wrap">
                                                    <i class="bi bi-person"></i>
                                                    <input type="text" name="full_name" id="form_full_name"
                                                        class="form-control form-control-lg @error('full_name') is-invalid @enderror"
                                                        value="{{ old('full_name') }}" required>
                                                </div>
                                                @error('full_name')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">{{ __('writ.lawyer.phone') }}</label>
                                                <div class="input-wrap">
                                                    <i class="bi bi-telephone"></i>
                                                    <input type="text" name="phone" id="form_phone"
                                                        class="form-control form-control-lg @error('phone') is-invalid @enderror"
                                                        value="{{ old('phone') }}" required>
                                                </div>
                                                @error('phone')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">{{ __('writ.lawyer.email') }}</label>
                                                <div class="input-wrap">
                                                    <i class="bi bi-envelope-at"></i>
                                                    <input type="email" name="email"
                                                        class="form-control form-control-lg @error('email') is-invalid @enderror"
                                                        value="{{ old('email') }}" required>
                                                </div>
                                                @error('email')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">{{ __('writ.lawyer.password') }}</label>
                                                    <div class="input-wrap">
                                                        <i class="bi bi-shield-lock"></i>
                                                        <input type="password" name="password"
                                                            class="form-control form-control-lg @error('password') is-invalid @enderror" required>
                                                    </div>
                                                    @error('password')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">{{ __('writ.lawyer.confirm_password') }}</label>
                                                    <div class="input-wrap">
                                                        <i class="bi bi-key"></i>
                                                        <input type="password" name="password_confirmation" class="form-control form-control-lg"
                                                            required>
                                                    </div>
                                                </div>
                                            </div>

                                            <button type="submit" class="btn btn-register-main btn-lg w-100">
                                                <i class="bi bi-check2-circle me-1"></i> {{ __('writ.lawyer.register') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div class="auth-switch text-center mt-4">
                                    <span>Already registered?</span>
                                    <a href="{{ route('lawyer.login') }}">{{ __('writ.auth.login_button') }}</a>
                                </div>
                            </div>

                            <div class="lawyer-register-footer">
                                Technical Assistance by Access to Justice For Women, GIZ Bangladesh
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .lawyer-register-page {
            min-height: calc(100vh - 64px);
            display: grid;
            align-items: center;
            padding: 2rem 0;
            background: linear-gradient(180deg, #f7f9fc 0%, #eef3f8 100%);
        }

        .lawyer-register-shell {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            padding: clamp(1.1rem, 3vw, 2.1rem);
            background: linear-gradient(135deg, rgba(0, 40, 77, .98) 0%, rgba(5, 60, 104, .98) 62%, rgba(17, 105, 109, .96) 100%);
            box-shadow: 0 22px 60px rgba(16, 32, 51, .18);
        }

        .lawyer-register-shell::before {
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

        .lawyer-register-shell::after {
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

        .lawyer-register-shell > .row {
            position: relative;
            z-index: 1;
        }

        .lawyer-register-intro {
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

        .lawyer-register-intro h1 {
            margin: 1rem 0 .65rem;
            max-width: 560px;
            color: #fff;
            font-size: clamp(2rem, 4vw, 3.1rem);
            line-height: 1.05;
            font-weight: 900;
            letter-spacing: 0;
            text-shadow: 0 2px 10px rgba(0, 0, 0, .14);
        }

        .lawyer-register-intro p {
            max-width: 520px;
            margin: 0;
            color: rgba(255, 255, 255, .78);
            font-size: 1rem;
            line-height: 1.65;
            font-weight: 600;
        }

        .register-points {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .65rem;
            max-width: 560px;
            margin-top: 1.35rem;
        }

        .register-points div {
            border: 1px solid rgba(255, 255, 255, .18);
            border-left: 3px solid rgba(212, 160, 23, .85);
            border-radius: 5px;
            background: rgba(255, 255, 255, .08);
            padding: .75rem;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .07);
        }

        .register-points i {
            display: block;
            color: #ffe5a0;
            font-size: 1.1rem;
            margin-bottom: .25rem;
        }

        .register-points strong {
            display: block;
            color: #fff;
            font-size: .92rem;
            font-weight: 900;
        }

        .register-points span {
            color: rgba(255, 255, 255, .68);
            font-size: .78rem;
            font-weight: 700;
        }

        .lawyer-register-card {
            width: min(520px, 100%);
            margin-left: auto;
            background: #fff;
            border: 1px solid rgba(0, 40, 77, .12);
            border-radius: 8px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, .22);
            overflow: hidden;
        }

        .lawyer-register-card-header {
            padding: 1.1rem 1.15rem;
            border-top: 4px solid #d4a017;
            border-bottom: 1px solid #d9e2ec;
            background: linear-gradient(180deg, #ffffff 0%, #f7fafc 100%);
        }

        .lawyer-register-card-header h2 {
            margin: 0;
            color: #00284d;
            font-size: 1.22rem;
            font-weight: 900;
            letter-spacing: 0;
        }

        .lawyer-register-card-header p {
            margin: .2rem 0 0;
            color: #607086;
            font-size: .86rem;
            font-weight: 600;
        }

        .lawyer-register-card-body {
            padding: 1rem 1.15rem 1.15rem;
        }

        .step-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            margin-bottom: .9rem;
            padding-bottom: .75rem;
            border-bottom: 1px solid #e5ebf2;
        }

        .step-heading span {
            display: inline-flex;
            border-radius: 999px;
            background: #fff8e3;
            color: #9a710b;
            padding: .28rem .62rem;
            font-size: .78rem;
            font-weight: 900;
        }

        .step-heading strong {
            color: #00284d;
            font-size: .95rem;
            font-weight: 900;
        }

        .lawyer-register-card .form-label {
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

        .lawyer-register-card .form-control {
            min-height: 44px;
            border-radius: 5px;
            border-color: #cfd9e4;
            color: #132238;
            font-size: .98rem;
            font-weight: 600;
            padding-left: 2.35rem;
        }

        .lawyer-register-card .form-control:focus {
            border-color: #d4a017;
            box-shadow: 0 0 0 .2rem rgba(212, 160, 23, .14);
        }

        .btn-register-main {
            min-height: 44px;
            border-radius: 5px;
            border: 0;
            background: #00284d;
            color: #fff;
            font-weight: 900;
        }

        .btn-register-main:hover,
        .btn-register-main:focus {
            background: #073b70;
            color: #fff;
        }

        .register-manual-link {
            color: #00284d;
            font-weight: 800;
            text-decoration: none;
        }

        .register-manual-link:hover {
            color: #d4a017;
            text-decoration: underline;
        }

        .auth-switch {
            color: #607086;
            font-size: .9rem;
            font-weight: 700;
        }

        .auth-switch a {
            color: #00284d;
            font-weight: 900;
            text-decoration: none;
        }

        .lawyer-register-footer {
            padding: .75rem 1.15rem;
            border-top: 1px solid #d9e2ec;
            background: #f8fafc;
            color: #607086;
            font-size: .76rem;
            font-weight: 700;
            text-align: center;
        }

        @media (max-width: 575.98px) {
            .lawyer-register-page {
                align-items: flex-start;
                padding: 1.25rem 0;
            }

            .lawyer-register-shell {
                border-radius: 10px;
            }

            .lawyer-register-intro {
                padding: 0;
            }

            .lawyer-register-intro h1 {
                font-size: 1.9rem;
            }

            .register-points {
                display: none;
            }

            .lawyer-register-card {
                margin: 0;
                width: 100%;
            }
        }
    </style>

    <script>
        function openRegistrationForm(member = null, showMemberField = false) {
            document.querySelector('.step-1').style.display = 'none';
            document.querySelector('.step-2').style.display = 'block';

            document.getElementById('member_id_field').style.display = showMemberField ? 'block' : 'none';
            document.getElementById('form_member_id').value = member?.memberId || '';
            document.getElementById('form_full_name').value = member?.memberName || '';
            document.getElementById('form_phone').value = member?.mobile || '';
            document.getElementById('form_picture').value = member?.picture || '';
            document.getElementById('form_barDateOfJoining').value = member?.barDateOfJoining || '';
            document.getElementById('form_barDateOfEnrollment').value = member?.barDateOfEnrollment || '';
            document.getElementById('form_barCourtType').value = member?.barCourtType || '';
            document.getElementById('form_status').value = member?.status || 'active';
        }

        document.getElementById('manual_register_btn').addEventListener('click', function() {
            openRegistrationForm(null, false);
        });

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
                        openRegistrationForm(data.member, true);

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
                    errorDiv.style.display = 'block';
                    errorDiv.innerText = '{{ __('writ.lawyer.api_error') }}';
                });
        });
    </script>

@endsection
