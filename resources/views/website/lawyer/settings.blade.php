@extends('website.layouts.lawyerlayout')

@section('title', __('lawyer.nav.settings'))

@section('content')
    <style>
        main {
            padding-top: 40px !important;
        }

        .lawyer-sidebar {
            background: #00284d;
            border-radius: 10px;
            padding: 20px 0;
            color: #fff;
            width: 250px;
        }

        .lawyer-sidebar a {
            display: block;
            padding: 12px 20px;
            color: #fff;
            text-decoration: none;
            font-weight: 500;
        }

        .lawyer-sidebar a:hover,
        .lawyer-sidebar a.active {
            background: #d4a017;
            color: #fff;
        }

        .profile-section-title {
            font-size: 20px;
            font-weight: 700;
            color: #003366;
            margin-bottom: 10px;
        }

        .gold-text {
            color: #d4a017;
        }

        .lawyer-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .img-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #d4a017;
        }

        .text-danger {
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        }

        @media (max-width: 767px) {
            .lawyer-sidebar {
                display: none !important;
            }
        }
    </style>

    <div class="container py-4" style="min-height:75vh;">
        <div class="row">
            {{-- Sidebar --}}
            @include('website.lawyer.sidebar')

            {{-- Main Content --}}
            <div class="col-md-9">

                {{-- Flash Messages --}}
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form action="{{ route('lawyer.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- 1. Basic Info --}}
                    <div class="card lawyer-card">
                        <div class="card-body">
                            <h5 class="profile-section-title">{{ __('lawyer.section.basic_info') }}</h5>
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('lawyer.label.full_name') }}</label>
                                    <input type="text" name="full_name"
                                        class="form-control @error('full_name') is-invalid @enderror"
                                        value="{{ old('full_name', $lawyer->full_name) }}">
                                    @error('full_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('lawyer.label.email') }}</label>
                                    <input type="email" name="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email', $user->email) }}">
                                    @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('lawyer.label.phone') }}</label>
                                    <input type="text" name="phone"
                                        class="form-control @error('phone') is-invalid @enderror"
                                        value="{{ old('phone', $lawyer->phone) }}" placeholder="01XXXXXXXXX">
                                    @error('phone')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('lawyer.label.bar_council_id') }}</label>
                                    <input type="text" class="form-control" value="{{ $lawyer->bar_council_id }}"
                                        readonly>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">{{ __('lawyer.label.enrolmentCourt') }}</label>
                                    <input type="text" class="form-control" value="{{ $lawyer->barCourtType }}"
                                        readonly>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- 2. Change Password --}}
                    <div class="card lawyer-card">
                        <div class="card-body">
                            <h5 class="profile-section-title">{{ __('lawyer.section.change_password') }}</h5>
                            <div class="row g-3">

                                <div class="col-md-4">
                                    <label class="form-label">{{ __('lawyer.label.current_password') }}</label>
                                    <input type="password" name="old_password"
                                        class="form-control @error('old_password') is-invalid @enderror"
                                        placeholder="{{ __('lawyer.placeholder.current_password') }}">
                                    @error('old_password')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">{{ __('lawyer.label.new_password') }}</label>
                                    <input type="password" name="new_password"
                                        class="form-control @error('new_password') is-invalid @enderror"
                                        placeholder="{{ __('lawyer.placeholder.new_password') }}">
                                    @error('new_password')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">{{ __('lawyer.label.confirm_new_password') }}</label>
                                    <input type="password" name="new_password_confirmation" class="form-control"
                                        placeholder="{{ __('lawyer.placeholder.confirm_new_password') }}">
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- 3. Profile Picture --}}
                    <div class="card lawyer-card">
                        <div class="card-body">
                            <h5 class="profile-section-title">{{ __('lawyer.section.profile_picture') }}</h5>
                            <div class="mb-3">
                                @php
                                    $pic = $lawyer->picture ?? null;
                                    $displaySrc = !$pic
                                        ? 'https://via.placeholder.com/120x120?text=' .
                                            urlencode(__('lawyer.placeholder.photo'))
                                        : (str_starts_with($pic, 'http')
                                            ? $pic
                                            : asset($pic));
                                @endphp
                                <img id="currentPhoto" src="{{ $displaySrc }}" class="img-preview mb-2"
                                    alt="{{ __('lawyer.alt.profile_photo') }}">
                                <input type="file" name="picture" id="pictureInput"
                                    class="form-control @error('picture') is-invalid @enderror">
                                @error('picture')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <small class="text-muted">{{ __('lawyer.helper.upload_new_image') }}</small>
                            </div>
                        </div>
                    </div>


                    <button type="submit" class="btn btn-gold mt-3">{{ __('lawyer.button.save_changes') }}</button>
                </form>

            </div>
        </div>
    </div>

    {{-- JS for image preview --}}
    <script>
        document.getElementById('pictureInput')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(ev) {
                document.getElementById('currentPhoto').src = ev.target.result;
            };
            reader.readAsDataURL(file);
        });
    </script>
@endsection
