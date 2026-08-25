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

        .navbar .container {
            gap: .75rem;
        }

        .navbar-brand,
        .navbar-nav .nav-link {
            color: #fff;
        }

        .navbar-brand {
            letter-spacing: 0;
            white-space: nowrap;
        }

        .navbar-nav {
            gap: .15rem;
        }

        .navbar-nav .nav-link {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            white-space: nowrap;
            padding-left: .55rem;
            padding-right: .55rem;
            font-weight: 700;
        }

        .navbar-nav .nav-link i {
            line-height: 1;
        }

        .navbar-nav .nav-link:hover,
        .dropdown-item:hover {
            color: #d4a017 !important;
        }

        .dropdown-menu {
            border: 0;
            border-radius: 4px;
            box-shadow: 0 8px 24px rgba(0, 40, 77, .18);
            padding: .45rem;
        }

        .dropdown-header {
            color: #64748b;
            font-size: .72rem;
            font-weight: 900;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: .55rem;
            border-radius: 4px;
            color: #1f2937;
            font-weight: 700;
            padding: .48rem .65rem;
        }

        .dropdown-item i {
            width: 1.05rem;
            color: #0b4f8a;
            text-align: center;
        }

        .dropdown-item.text-danger i {
            color: #dc3545;
        }

        .navbar-identity {
            min-width: 0;
        }

        .user-menu-label {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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

        .footer-meta {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: .35rem .75rem;
            margin-top: .5rem;
            font-size: .86rem;
            color: rgba(255, 255, 255, .82);
        }

        .footer-meta span + span::before {
            content: "|";
            margin-right: .75rem;
            color: #d4a017;
        }

        @media (max-width: 575.98px) {
            .footer-meta {
                display: block;
                line-height: 1.45;
            }

            .footer-meta span {
                display: block;
            }

            .footer-meta span + span::before {
                content: "";
                margin: 0;
            }
        }

        @media (max-width: 991.98px) {
            main {
                padding-top: 72px;
            }

            .navbar-nav {
                gap: 0;
                padding-top: .75rem;
            }

            .navbar-nav .nav-link {
                display: flex;
                padding: .6rem 0;
            }

            .dropdown-menu {
                box-shadow: none;
                border: 1px solid rgba(255, 255, 255, .12);
                background: rgba(255, 255, 255, .98);
            }

            .user-menu-label {
                max-width: none;
            }
        }
    </style>
    @stack('css')
</head>

<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm">
        <div class="container">
            @php
                    $isLoggedIn = auth()->check();
                    $currentUser = $isLoggedIn ? auth()->user() : null;
                    $currentType = $currentUser?->user_type;
                    $isSuperAdmin = $currentUser?->hasRole('Super Admin') ?? false;
                    $departmentName = strtolower((string) ($currentUser?->departmentRelation?->name ?? ''));
                    $hasAssignedDepartment = $departmentName !== '';
                    $sectionTrackingKeywords = ['office assistant', 'affidavit', 'requisite', 'put-up', 'put up', 'typing', 'compare', 'superintendent', 'ready table', 'record room', 'court'];

                    $canSeeAdminMenu = $isLoggedIn && $currentType === 'admin';
                    $canSeeFilingMenu = $isLoggedIn && ($isSuperAdmin || str_contains($departmentName, 'filing'));
                    $canSeeRegistrarMenu = $isLoggedIn && str_contains($departmentName, 'registrar');
                    $canSeeCourtMenu = $isLoggedIn && ($isSuperAdmin ||
                        str_contains($departmentName, 'office assistant') ||
                        str_contains($departmentName, 'assistant registrar')
                    );
                    $canSeeSectionReceiveMenu = $isLoggedIn && ($isSuperAdmin ||
                        ($currentType === 'staff' && $hasAssignedDepartment && !$canSeeFilingMenu && !$canSeeRegistrarMenu) ||
                        collect($sectionTrackingKeywords)->contains(
                            fn ($keyword) => str_contains($departmentName, $keyword)
                        )
                    );
                    $isAffidavitSection = str_contains($departmentName, 'affidavit');
                    $isOfficeAssistantSection = str_contains($departmentName, 'office assistant');

                    $brandRoute = '#';
                    if ($canSeeAdminMenu) {
                        $brandRoute = route('admin.dashboard');
                    } elseif ($canSeeFilingMenu) {
                        $brandRoute = route('admin.tracking.filing.index');
                    } elseif ($canSeeRegistrarMenu) {
                        $brandRoute = route('admin.tracking.lookup');
                    } elseif ($canSeeSectionReceiveMenu && str_contains($departmentName, 'office assistant')) {
                        $brandRoute = route('admin.tracking.section.receive');
                    } elseif ($canSeeCourtMenu) {
                        $brandRoute = route('admin.tracking.court.dispatch.index');
                    } elseif ($canSeeSectionReceiveMenu) {
                        $brandRoute = route('admin.tracking.section.receive');
                    }
            @endphp

            <a class="navbar-brand fw-bold navbar-identity" href="{{ $brandRoute }}">{{ __('messages.admin_panel_brand') }}</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">

                <ul class="navbar-nav ms-auto align-items-lg-center">
                    @if($canSeeAdminMenu)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-speedometer"></i> {{ __('messages.dashboard') }}
                            </a>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-sliders"></i> {{ __('messages.manage_menu') }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header">{{ __('messages.admin_menu') }}</h6></li>
                                <li><a class="dropdown-item" href="{{ route('admin.users.index') }}"><i class="bi bi-people"></i>{{ __('messages.users') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.departments.index') }}"><i class="bi bi-diagram-3"></i>{{ __('messages.departments') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.courts.index') }}"><i class="bi bi-bank"></i>{{ __('messages.courts') }}</a></li>
                                @unless($canSeeCourtMenu)
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.tracking.court.batches.index') }}"><i class="bi bi-collection"></i>{{ __('messages.court_batches') }}</a></li>
                                @endunless
                            </ul>
                        </li>
                    @endif

                    @if($canSeeAdminMenu || $canSeeFilingMenu || $canSeeSectionReceiveMenu || $canSeeCourtMenu || $canSeeRegistrarMenu)
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-upc-scan"></i> {{ __('messages.tracking_menu') }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @if($canSeeSectionReceiveMenu)
                                    <li><h6 class="dropdown-header">{{ $isAffidavitSection ? __('messages.affidavit_menu') : __('messages.section_menu') }}</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.tracking.section.receive') }}"><i class="bi bi-upc-scan"></i>{{ $isAffidavitSection ? __('messages.affidavit_receive') : __('messages.section_receive') }}</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.tracking.old-case-receive') }}"><i class="bi bi-archive"></i>{{ __('messages.old_case_receive') }}</a></li>
                                @endif

                                @if($canSeeFilingMenu)
                                    @if($canSeeSectionReceiveMenu)
                                        <li><hr class="dropdown-divider"></li>
                                    @endif
                                    <li><h6 class="dropdown-header">{{ __('messages.filing_menu') }}</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.tracking.filing.index') }}"><i class="bi bi-folder-check"></i>{{ __('messages.filing_module') }}</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.tracking.filing.scan-temp') }}"><i class="bi bi-qr-code-scan"></i>{{ __('messages.filing_scan_temp') }}</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.tracking.filing.direct-create') }}"><i class="bi bi-file-earmark-plus"></i>{{ __('messages.filing_direct_create') }}</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.tracking.filing.print-index') }}"><i class="bi bi-printer"></i>{{ __('messages.filing_print_module') }}</a></li>
                                @endif

                                @if($canSeeCourtMenu)
                                    @if($canSeeFilingMenu || $canSeeSectionReceiveMenu)
                                        <li><hr class="dropdown-divider"></li>
                                    @endif
                                    <li><h6 class="dropdown-header">{{ __('messages.court_menu') }}</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.tracking.court.dispatch.index') }}"><i class="bi bi-send"></i>{{ __('messages.court_dispatch') }}</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.tracking.court.batches.index') }}"><i class="bi bi-collection"></i>{{ __('messages.court_batches') }}</a></li>
                                    @if(!$isOfficeAssistantSection)
                                        <li><a class="dropdown-item" href="{{ route('admin.tracking.court.return.index') }}"><i class="bi bi-box-arrow-in-down"></i>{{ __('messages.court_return') }}</a></li>
                                    @endif
                                @endif

                                @if($canSeeRegistrarMenu)
                                    @if($canSeeFilingMenu || $canSeeSectionReceiveMenu || $canSeeCourtMenu)
                                        <li><hr class="dropdown-divider"></li>
                                    @endif
                                    <li><h6 class="dropdown-header">{{ __('messages.search_menu') }}</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.tracking.lookup') }}"><i class="bi bi-search"></i>{{ __('messages.registrar_lookup') }}</a></li>
                                @endif

                                @if($canSeeFilingMenu || $canSeeSectionReceiveMenu || $canSeeCourtMenu || $canSeeRegistrarMenu)
                                    <li><hr class="dropdown-divider"></li>
                                @endif
                                <li><a class="dropdown-item" href="{{ route('admin.tracking.register-report') }}"><i class="bi bi-file-earmark-bar-graph"></i>{{ __('messages.register_report') }}</a></li>
                            </ul>
                        </li>
                    @endif

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" title="{{ __('messages.language') }}">
                            <i class="bi bi-translate"></i> {{ __('messages.language') }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('locale.set', 'en') }}"><i class="bi bi-type"></i>{{ __('messages.lang_en') }}</a></li>
                            <li><a class="dropdown-item" href="{{ route('locale.set', 'bn') }}"><i class="bi bi-fonts"></i>{{ __('messages.lang_bn') }}</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <span class="user-menu-label">{{ Auth::user()->name ?? __('messages.profile') }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person-gear"></i>{{ __('messages.my_profile') }}</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right"></i>{{ __('messages.logout') }}</button>
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
                            <li><a href="{{ route('admin.tracking.court.batches.index') }}">{{ __('messages.court_batches') }}</a></li>
                            @if(!$isOfficeAssistantSection)
                                <li><a href="{{ route('admin.tracking.court.return.index') }}">{{ __('messages.court_return') }}</a></li>
                            @endif
                        @endif
                        @if($canSeeRegistrarMenu)
                            <li><a href="{{ route('admin.tracking.lookup') }}">{{ __('messages.registrar_lookup') }}</a></li>
                        @endif
                    </ul>
                </div>
                <div class="col-md-4 mb-3">
                    <h5>{{ __('writ.footer.system_access') }}</h5>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('lawyer.login') }}">{{ __('writ.footer.lawyer_login') }}</a></li>
                        <li><a href="{{ route('login') }}">{{ __('writ.footer.admin_login') }}</a></li>
                    </ul>
                </div>
            </div>

            <hr class="mt-3" style="border-color: #d4a017;">
            <div class="footer-meta">
                <span>{{ __('messages.copyright', ['year' => date('Y')]) }}</span>
                <span>{{ __('messages.implemented_by') }}</span>
                <span>{{ __('messages.technical_assistance') }}</span>
            </div>
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
