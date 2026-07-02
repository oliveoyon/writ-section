@extends('website.layouts.lawyerlayout')

@section('title', __('lawyer.title'))

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

        .profile-header {
            background: #003366;
            color: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .profile-header img {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
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
        }

        @media (max-width: 767px) {
            .lawyer-sidebar {
                display: none !important;
            }
        }
    </style>

    <div class="container py-4" style="min-height: 75vh;">

        <div class="row">

            @include('website.lawyer.sidebar')

            <div class="col-md-9">
                @php
                    $localPic = $lawyer->picture ?? null;
                    $apiPic = $lawyer->api_picture ?? null;
                    if ($localPic) {
                        $displaySrc = str_starts_with($localPic, 'http') ? $localPic : asset($localPic);
                    } elseif ($apiPic) {
                        $displaySrc = $apiPic;
                    } else {
                        $displaySrc = 'https://via.placeholder.com/110x110?text=' . urlencode(__('lawyer.placeholder.photo'));
                    }
                @endphp

                <div class="profile-header d-flex align-items-center">
                    <img src="{{ $displaySrc }}" class="me-3" alt="{{ __('lawyer.alt.profile_photo') }}"
                        style="width:110px; height:110px; border-radius:50%; object-fit:cover;">

                    <div>
                        <h4 class="mb-1">{{ $lawyer->full_name ?? ($user->name ?? __('lawyer.meta.not_available')) }}</h4>
                        <p class="mb-1">{{ __('lawyer.label.bar_council_id') }}:
                            <span class="gold-text">{{ $lawyer->bar_council_id ?? __('lawyer.meta.not_available') }}</span>
                        </p>
                        <p class="mb-0">{{ __('lawyer.label.member_since') }}:
                            {{ !empty($lawyer?->barDateOfEnrollment) ? date('Y', strtotime($lawyer->barDateOfEnrollment)) : __('lawyer.meta.not_available') }}
                        </p>
                    </div>
                </div>

                <div class="card lawyer-card mb-4">
                    <div class="card-body">
                        <h5 class="profile-section-title">{{ __('lawyer.section.basic_info') }}</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>{{ __('lawyer.label.full_name') }}:</strong>
                                <p class="mb-0">{{ $lawyer->full_name ?? ($user->name ?? __('lawyer.meta.not_available')) }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>{{ __('lawyer.label.email') }}:</strong>
                                <p class="mb-0">{{ $user->email ?? __('lawyer.meta.not_available') }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>{{ __('lawyer.label.phone') }}:</strong>
                                <p class="mb-0">{{ $lawyer->phone ?? __('lawyer.meta.not_available') }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>{{ __('lawyer.label.enrolmentCourt') }}:</strong>
                                <p class="mb-0">{{ $lawyer->barCourtType ?? __('lawyer.meta.not_available') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card lawyer-card">
                    <div class="card-body">
                        <h5 class="profile-section-title">{{ __('lawyer.section.recent_cases') }}</h5>
                        @if($recentCases->isEmpty())
                            <p class="mb-0">{{ __('lawyer.meta.no_recent_cases') }}</p>
                        @else
                            <ul class="mb-0">
                                @foreach($recentCases as $case)
                                    <li>
                                        {{ __('lawyer.meta.case_no') }} {{ $case->case_reference ?? ('TEMP-' . ($case->temporary_barcode ?? $case->id)) }}
                                        - {{ $case->subject ?? __('lawyer.meta.not_available') }}
                                        ({{ $case->status ?? __('lawyer.meta.status_unknown') }})
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
