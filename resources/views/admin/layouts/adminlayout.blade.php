<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('messages.admin_panel_title') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <style>
        @font-face {
            font-family: 'SolaimanLipi';
            src: url('/assets/font/SolaimanLipi.ttf') format('truetype');
        }

        body {
            font-family: 'SolaimanLipi', "Segoe UI", sans-serif;
        }

        main {
            padding-top: 80px;
        }

        .navbar {
            background: #00284d;
        }

        .navbar-brand,
        .navbar-nav .nav-link {
            color: #fff;
        }

        .navbar-nav .nav-link:hover,
        .dropdown-item:hover {
            color: #d4a017 !important;
        }

        footer {
            background: #00284d;
            color: #fff;
            padding: 50px 0;
        }

        footer a {
            color: #d4a017;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }
    </style>
    @stack('css')
</head>

<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                @php
                    $isLoggedIn = auth()->check();
                    $currentUser = $isLoggedIn ? auth()->user() : null;
                    $currentType = $currentUser?->user_type;
                    $departmentName = strtolower((string) ($currentUser?->departmentRelation?->name ?? ''));
                    $sectionTrackingKeywords = ['affidavit', 'requisite', 'put-up', 'put up', 'typing', 'compare', 'superintendent', 'ready table', 'record room', 'court'];

                    $canSeeAdminMenu = $isLoggedIn && $currentType === 'admin';
                    $canSeeFilingMenu = $isLoggedIn && str_contains($departmentName, 'filing');
                    $canSeeRegistrarMenu = $isLoggedIn && str_contains($departmentName, 'registrar');
                    $canSeeCourtMenu = $isLoggedIn && (
                        str_contains($departmentName, 'office assistant') ||
                        str_contains($departmentName, 'assistant registrar')
                    );
                    $canSeeSectionReceiveMenu = $isLoggedIn && collect($sectionTrackingKeywords)->contains(
                        fn ($keyword) => str_contains($departmentName, $keyword)
                    );
                    $isAffidavitSection = str_contains($departmentName, 'affidavit');

                    $brandRoute = '#';
                    if ($canSeeAdminMenu) {
                        $brandRoute = route('admin.dashboard');
                    } elseif ($canSeeFilingMenu) {
                        $brandRoute = route('admin.tracking.filing.index');
                    } elseif ($canSeeRegistrarMenu) {
                        $brandRoute = route('admin.tracking.lookup');
                    } elseif ($canSeeCourtMenu) {
                        $brandRoute = route('admin.tracking.court.dispatch.index');
                    } elseif ($canSeeSectionReceiveMenu) {
                        $brandRoute = route('admin.tracking.section.receive');
                    }
                @endphp

                <a class="navbar-brand fw-bold" href="{{ $brandRoute }}">{{ __('messages.admin_panel_brand') }}</a>

                <ul class="navbar-nav ms-auto align-items-lg-center">
                    @if($canSeeAdminMenu)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-speedometer"></i> {{ __('messages.dashboard') }}
                            </a>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-people"></i> {{ __('messages.admin_menu') }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">{{ __('messages.users') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.departments.index') }}">{{ __('messages.departments') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.courts.index') }}">{{ __('messages.courts') }}</a></li>
                            </ul>
                        </li>
                    @endif

                    @if($canSeeFilingMenu)
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-folder-check"></i> {{ __('messages.filing_menu') }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('admin.tracking.filing.index') }}">{{ __('messages.filing_module') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.tracking.filing.scan-temp') }}">{{ __('messages.filing_scan_temp') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.tracking.filing.direct-create') }}">{{ __('messages.filing_direct_create') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.tracking.filing.print-index') }}">{{ __('messages.filing_print_module') }}</a></li>
                            </ul>
                        </li>
                    @endif

                    @if($canSeeSectionReceiveMenu)
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-upc-scan"></i> {{ $isAffidavitSection ? __('messages.affidavit_menu') : __('messages.section_menu') }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('admin.tracking.section.receive') }}">{{ $isAffidavitSection ? __('messages.affidavit_receive') : __('messages.section_receive') }}</a></li>
                            </ul>
                        </li>
                    @endif

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.tracking.register-report') }}">
                            <i class="bi bi-printer"></i> {{ __('messages.register_report') }}
                        </a>
                    </li>

                    @if($canSeeCourtMenu)
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-building"></i> {{ __('messages.court_menu') }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('admin.tracking.court.dispatch.index') }}">{{ __('messages.court_dispatch') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.tracking.court.return.index') }}">{{ __('messages.court_return') }}</a></li>
                            </ul>
                        </li>
                    @endif

                    @if($canSeeRegistrarMenu)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.tracking.lookup') }}">
                                <i class="bi bi-search"></i> {{ __('messages.registrar_lookup') }}
                            </a>
                        </li>
                    @endif

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            {{ __('messages.language') }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('locale.set', 'en') }}">{{ __('messages.lang_en') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('locale.set', 'bn') }}">{{ __('messages.lang_bn') }}</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            {{ Auth::user()->name ?? __('messages.profile') }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">{{ __('messages.my_profile') }}</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item text-danger">{{ __('messages.logout') }}</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="flex-fill">
        @yield('content')
    </main>

    <footer>
        <div class="container">
            <div class="row text-center text-md-start">
                <div class="col-md-4 mb-3">
                    <h5>{{ __('messages.contact_us') }}</h5>
                    <p>{{ __('messages.contact_email') }}<br>{{ __('messages.contact_phone') }}</p>
                </div>
                <div class="col-md-4 mb-3">
                    <h5>{{ __('messages.quick_links') }}</h5>
                    <ul class="list-unstyled">
                        @if($canSeeAdminMenu)
                            <li><a href="{{ route('admin.dashboard') }}">{{ __('messages.dashboard') }}</a></li>
                        @endif
                        @if($canSeeFilingMenu)
                            <li><a href="{{ route('admin.tracking.filing.index') }}">{{ __('messages.filing_module') }}</a></li>
                        @endif
                        @if($canSeeSectionReceiveMenu)
                            <li><a href="{{ route('admin.tracking.section.receive') }}">{{ $isAffidavitSection ? __('messages.affidavit_receive') : __('messages.section_receive') }}</a></li>
                        @endif
                        <li><a href="{{ route('admin.tracking.register-report') }}">{{ __('messages.register_report') }}</a></li>
                        @if($canSeeCourtMenu)
                            <li><a href="{{ route('admin.tracking.court.dispatch.index') }}">{{ __('messages.court_dispatch') }}</a></li>
                            <li><a href="{{ route('admin.tracking.court.return.index') }}">{{ __('messages.court_return') }}</a></li>
                        @endif
                        @if($canSeeRegistrarMenu)
                            <li><a href="{{ route('admin.tracking.lookup') }}">{{ __('messages.registrar_lookup') }}</a></li>
                        @endif
                    </ul>
                </div>
                <div class="col-md-4 mb-3">
                    <h5>{{ __('messages.follow_us') }}</h5>
                    <a href="#" class="me-2">{{ __('messages.facebook') }}</a>
                    <a href="#" class="me-2">{{ __('messages.twitter') }}</a>
                    <a href="#">{{ __('messages.linkedin') }}</a>
                </div>
            </div>

            <hr class="mt-3" style="border-color: #d4a017;">
            <p class="text-center mt-2 mb-0">{{ __('messages.copyright', ['year' => date('Y')]) }}</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });
    </script>
    @stack('js')
</body>

</html>
