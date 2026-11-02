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

        .current-place {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 8px;
            color: #075b46;
            background: #e7f5ef;
            border: 1px solid #b9dfcf;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
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
                    @php
                        $localPic = auth()->user()->lawyer->picture ?? null; // uploaded image in storage
                        $apiPic = auth()->user()->lawyer->api_picture ?? null; // picture from API
                        if ($localPic) {
                            $displaySrc = asset($localPic); // use local uploaded image
                        } elseif ($apiPic) {
                            $displaySrc = $apiPic; // use API link
                        } else {
                            $displaySrc = 'https://via.placeholder.com/110x110?text=Photo'; // placeholder
                        }
                    @endphp

                    <img src="{{ $displaySrc }}" class="me-3" alt="Lawyer Photo"
                        style="width:110px; height:110px; border-radius:50%; object-fit:cover;">

                    <div>
                        <h4 class="mb-1">{{ auth()->user()->lawyer->full_name ?? '' }}</h4>
                        <p class="mb-1">{{ __('lawyer.label.bar_council_id') }}:
                            <span class="gold-text">{{ auth()->user()->lawyer->bar_council_id ?? '' }}</span>
                        </p>
                        {{-- <p class="mb-0">{{ __('lawyer.label.member_since') }}:
                            {{ auth()->user()->lawyer->barDateOfEnrollment ? date('Y', strtotime(auth()->user()->lawyer->barDateOfEnrollment)) : '' }}
                        </p> --}}
                    </div>
                </div>

                <!-- Recent Cases -->
                <!-- Recent Cases -->
                <div class="card lawyer-card mt-4">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <h5 class="profile-section-title mb-0">{{ __('lawyer.section.recent_cases') }}</h5>

                        <!-- Create New Case Button -->
                        <a href="{{ route('lawyer.case.create') }}" class="btn btn-sm btn-success">
                            {{ __('lawyer.case.create_new') }}
                        </a>
                    </div>

                    <div class="table-responsive mt-3">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('lawyer.case.id') }}</th>
                                <th>{{ __('lawyer.case.case_type') }}</th>
                                <th>{{ __('lawyer.case.status') }}</th>
                                <th>{{ __('lawyer.case.current_place') }}</th>
                                <th>{{ __('lawyer.case.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cases as $case)
                                @php
                                    $currentPlace = $case->current_section ?: __('lawyer.case.lawyer_desk');
                                    if (strtolower((string) $case->current_section) === 'court' && $case->latestMovement?->court) {
                                        $currentPlace = 'Court (' . $case->latestMovement->court->displayName() . ')';
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $case->id }}</td>
                                    <td>{{ $case->case_type }}</td>
                                    <td>{{ ucfirst($case->status) }}</td>
                                    <td>
                                        <span class="current-place">
                                            <i class="bi bi-geo-alt" aria-hidden="true"></i>
                                            {{ $currentPlace }}
                                        </span>
                                    </td>
                                    <td>
                                        <!-- View Summary Button (Always Visible) -->
                                        <a href="{{ route('lawyer.case.summary', $case->id) }}"
                                            class="btn btn-sm btn-info">View Summary</a>

                                        <!-- Edit/Delete Buttons (Only for Draft) -->
                                        @if (in_array($case->status, ['draft', 'returned_to_lawyer']))
                                            <a href="{{ route('lawyer.case.edit', $case->id) }}"
                                                class="btn btn-sm btn-warning">Edit</a>

                                            @if ($case->status == 'draft')
                                                <form action="{{ route('lawyer.case.destroy', $case->id) }}" method="POST"
                                                    class="d-inline-block"
                                                    onsubmit="return confirm('Are you sure you want to delete this draft case?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            @endif
                                        @endif

                                        @if ($case->status == 'returned_to_lawyer')
                                            <form action="{{ route('lawyer.case.resubmit', $case->id) }}" method="POST" class="d-inline-block">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary">Re-Submit</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            @if ($cases->isEmpty())
                                <tr>
                                    <td colspan="5" class="text-center">No cases found.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                    </div>
                </div>




            </div>
        </div>
    </div>

@endsection
