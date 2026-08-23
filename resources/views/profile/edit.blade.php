@extends('admin.layouts.adminlayout')

@section('content')
    <style>
        main {
            background: #f4f6f9;
        }

        .profile-shell {
            max-width: 980px;
        }

        .profile-hero {
            background: #00284d;
            color: #fff;
            border-radius: 6px;
            padding: 1rem 1.15rem;
            box-shadow: 0 8px 22px rgba(0, 40, 77, .12);
        }

        .profile-hero h1 {
            font-size: 1.35rem;
            font-weight: 800;
            margin: 0;
            letter-spacing: 0;
        }

        .profile-chip {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            background: rgba(212, 160, 23, .16);
            border: 1px solid rgba(212, 160, 23, .45);
            color: #ffe7a1;
            border-radius: 999px;
            padding: .35rem .65rem;
            font-size: .82rem;
            font-weight: 700;
        }

        .profile-panel {
            background: #fff;
            border: 1px solid #e3e8ef;
            border-radius: 6px;
            box-shadow: 0 1px 8px rgba(15, 23, 42, .06);
        }

        .profile-panel-header {
            padding: .8rem 1rem;
            border-bottom: 1px solid #e7edf4;
        }

        .profile-panel-header h2 {
            font-size: 1rem;
            font-weight: 800;
            color: #00284d;
            margin: 0;
        }

        .profile-panel-body {
            padding: 1rem;
        }

        .profile-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .75rem;
        }

        .profile-meta-item {
            border: 1px solid #edf1f5;
            border-radius: 6px;
            padding: .75rem;
            background: #fbfcfe;
        }

        .profile-meta-item span {
            display: block;
            color: #6c757d;
            font-size: .78rem;
            margin-bottom: .25rem;
        }

        .profile-meta-item strong {
            color: #17212b;
            font-size: .95rem;
            font-weight: 800;
            word-break: break-word;
        }

        .profile-panel .form-label {
            color: #334155;
            font-weight: 700;
            font-size: .88rem;
        }

        .profile-panel .form-control {
            border-radius: 5px;
            min-height: 42px;
        }

        .profile-panel .form-control:focus {
            border-color: #d4a017;
            box-shadow: 0 0 0 .18rem rgba(212, 160, 23, .14);
        }

        .btn-rtfts {
            background: #d4a017;
            border-color: #d4a017;
            color: #fff;
            font-weight: 800;
        }

        .btn-rtfts:hover {
            background: #b98c14;
            border-color: #b98c14;
            color: #fff;
        }

        @media (max-width: 767.98px) {
            .profile-meta {
                grid-template-columns: 1fr;
            }
        }
    </style>

    @php
        $roleNames = $user->roles->pluck('name')->filter()->values();
        $canEditIdentity = $user->user_type === 'admin';
    @endphp

    <div class="container profile-shell py-4">
        <div class="profile-hero mb-3">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                <div>
                    <h1>My Account</h1>
                    <div class="small opacity-75 mt-1">RTFTS user profile and password</div>
                </div>
                <span class="profile-chip">
                    <i class="bi bi-person-badge"></i>
                    {{ ucfirst((string) $user->user_type) }}
                </span>
            </div>
        </div>

        @if (session('status') === 'profile-updated')
            <div class="alert alert-success py-2">Profile updated successfully.</div>
        @endif

        @if (session('status') === 'password-updated')
            <div class="alert alert-success py-2">Password changed successfully.</div>
        @endif

        @if (session('status') === 'profile-readonly')
            <div class="alert alert-warning py-2">Name and email are managed by administrator.</div>
        @endif

        <div class="row g-3">
            <div class="col-lg-5">
                <div class="profile-panel h-100">
                    <div class="profile-panel-header">
                        <h2><i class="bi bi-info-circle me-1"></i> Account Details</h2>
                    </div>
                    <div class="profile-panel-body">
                        <div class="profile-meta">
                            <div class="profile-meta-item">
                                <span>Name</span>
                                <strong>{{ $user->name }}</strong>
                            </div>
                            <div class="profile-meta-item">
                                <span>Email</span>
                                <strong>{{ $user->email }}</strong>
                            </div>
                            <div class="profile-meta-item">
                                <span>Login / Card ID</span>
                                <strong>{{ $user->login_id ?: 'Not set' }}</strong>
                            </div>
                            <div class="profile-meta-item">
                                <span>Department</span>
                                <strong>{{ $user->departmentRelation?->name ?? 'Not assigned' }}</strong>
                            </div>
                            <div class="profile-meta-item">
                                <span>Role</span>
                                <strong>{{ $roleNames->isNotEmpty() ? $roleNames->join(', ') : 'Not assigned' }}</strong>
                            </div>
                            <div class="profile-meta-item">
                                <span>Status</span>
                                <strong>{{ $user->is_active ? 'Active' : 'Inactive' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                @if ($canEditIdentity)
                    <div class="profile-panel mb-3">
                        <div class="profile-panel-header">
                            <h2><i class="bi bi-pencil-square me-1"></i> Edit Profile</h2>
                        </div>
                        <div class="profile-panel-body">
                            <form method="POST" action="{{ route('profile.update') }}">
                                @csrf
                                @method('patch')

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">Name</label>
                                        <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autocomplete="name">
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email</label>
                                        <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="text-end mt-3">
                                    <button type="submit" class="btn btn-rtfts">
                                        <i class="bi bi-check2-circle me-1"></i> Save Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                <div class="profile-panel">
                    <div class="profile-panel-header">
                        <h2><i class="bi bi-shield-lock me-1"></i> Change Password</h2>
                    </div>
                    <div class="profile-panel-body">
                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            @method('put')

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input id="current_password" name="current_password" type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password">
                                    @error('current_password', 'updatePassword')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="password" class="form-label">New Password</label>
                                    <input id="password" name="password" type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
                                    @error('password', 'updatePassword')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password">
                                </div>
                            </div>

                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-rtfts">
                                    <i class="bi bi-key me-1"></i> Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
