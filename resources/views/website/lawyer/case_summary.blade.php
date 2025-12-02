@extends('website.layouts.lawyerlayout')

@section('title', __('lawyer.case.summary_title'))

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

    .lawyer-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
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

            <h3 class="mb-4">{{ __('lawyer.case.summary_title') }}</h3>

            <!-- CASE INFO -->
            <div class="card lawyer-card">
                <div class="card-body">
                    <h5 class="profile-section-title">{{ __('lawyer.case.basic_info') }}</h5>
                    <p><strong>{{ __('lawyer.case.case_type') }}:</strong> {{ $case->case_type }}</p>
                    <p><strong>{{ __('lawyer.case.subject') }}:</strong> {{ $case->subject }}</p>
                    <p><strong>{{ __('lawyer.case.description') }}:</strong> {{ $case->description }}</p>
                </div>
            </div>

            <!-- PETITIONERS -->
            <div class="card lawyer-card">
                <div class="card-body">
                    <h5 class="profile-section-title">{{ __('lawyer.case.petitioners') }}</h5>
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('lawyer.case.name_or_organization') }}</th>
                                <th>{{ __('lawyer.case.represented_by') }}</th>
                                <th>{{ __('lawyer.case.phone') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($case->petitioners as $p)
                                <tr>
                                    <td>{{ $p->name_or_organization }}</td>
                                    <td>{{ $p->represented_by }}</td>
                                    <td>{{ $p->phone }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- RESPONDENTS -->
            <div class="card lawyer-card">
                <div class="card-body">
                    <h5 class="profile-section-title">{{ __('lawyer.case.respondents') }}</h5>
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('lawyer.case.name') }}</th>
                                <th>{{ __('lawyer.case.designation') }}</th>
                                <th>{{ __('lawyer.case.organization') }}</th>
                                <th>{{ __('lawyer.case.address') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($case->respondents as $r)
                                <tr>
                                    <td>{{ $r->name }}</td>
                                    <td>{{ $r->designation }}</td>
                                    <td>{{ $r->organization }}</td>
                                    <td>{{ $r->address }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- FILES -->
            @if ($case->files->count() > 0)
                <div class="card lawyer-card">
                    <div class="card-body">
                        <h5 class="profile-section-title">{{ __('lawyer.case.uploaded_files') }}</h5>
                        <ul>
                            @foreach ($case->files as $file)
                                <li>
                                    <a href="{{ Storage::url($file->file_path) }}" target="_blank">{{ $file->original_name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- PRINT TOP SHEET AND EDIT BUTTON -->
            <div class="mb-4">
                <a href="{{ route('lawyer.case.top_sheet', $case->id) }}" class="btn btn-primary">
                    {{ __('lawyer.case.print_top_sheet') }}
                </a>

                @if ($case->status === 'draft')
                    <a href="{{ route('lawyer.case.edit', $case->id) }}" class="btn btn-warning">
                        {{ __('lawyer.case.edit_case') }}
                    </a>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
