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

            <!-- Sidebar (Desktop Only) -->
            @include('website.lawyer.sidebar')

            <!-- Main Content -->
            <div class="col-md-9">

                <!-- Profile Header -->
                <div class="profile-header d-flex align-items-center">
                    <img src="{{ auth()->user()->lawyer->picture ?? '' }}" class="me-3" alt="Lawyer Photo">
                    <div>
                        <h4 class="mb-1">{{ auth()->user()->lawyer->full_name ?? '' }}</h4>
                        <p class="mb-1">{{ __('lawyer.label.bar_council_id') }}:
                            <span class="gold-text">{{ auth()->user()->lawyer->bar_council_id ?? '' }}</span>
                        </p>
                        <p class="mb-0">{{ __('lawyer.label.member_since') }}: {{ auth()->user()->lawyer->barDateOfEnrollment ? date('Y', strtotime(auth()->user()->lawyer->barDateOfEnrollment)) : '' }}</p>
                    </div>
                </div>

                <!-- Basic Info -->
                <div class="card lawyer-card mb-4">
                    <div class="card-body">
                        <h5 class="profile-section-title">{{ __('lawyer.section.basic_info') }}</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>{{ __('lawyer.label.full_name') }}:</strong>
                                <p class="mb-0">{{ auth()->user()->lawyer->full_name ?? '' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>{{ __('lawyer.label.email') }}:</strong>
                                <p class="mb-0">{{ auth()->user()->email ?? '' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>{{ __('lawyer.label.phone') }}:</strong>
                                <p class="mb-0">{{ auth()->user()->lawyer->phone ?? '' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>{{ __('lawyer.label.address') }}:</strong>
                                <p class="mb-0">Dhaka, Bangladesh</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- About Me -->
                <div class="card lawyer-card mb-4">
                    <div class="card-body">
                        <h5 class="profile-section-title">{{ __('lawyer.section.about_me') }}</h5>
                        <p>
                            Professional lawyer specializing in civil and family matters with over 8 years of experience.
                            Passionate about justice, ethics, and client support.
                        </p>
                    </div>
                </div>

                <!-- Recent Cases -->
                <div class="card lawyer-card">
                    <div class="card-body">
                        <h5 class="profile-section-title">{{ __('lawyer.section.recent_cases') }}</h5>
                        <ul class="mb-0">
                            <li>Case #001 – Property Dispute (Ongoing)</li>
                            <li>Case #002 – Family Law (Resolved)</li>
                            <li>Case #003 – Contract Dispute (Ongoing)</li>
                            <li>Case #004 – Criminal Appeal (New)</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
