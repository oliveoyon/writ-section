<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    @include('partials.favicon')
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

        .workspace-user-bar {
            position: sticky;
            top: 56px;
            z-index: 1020;
            margin-top: 56px;
            background: #fff;
            border-top: 1px solid rgba(0, 40, 77, .08);
            border-bottom: 3px solid #d4a017;
            box-shadow: 0 5px 16px rgba(0, 40, 77, .16);
        }

        .workspace-bar-inner {
            display: grid;
            grid-template-columns: minmax(230px, 1fr) auto auto;
            align-items: center;
            gap: .9rem;
            min-height: 78px;
            padding: .55rem 0;
        }

        .workspace-user-identity {
            display: flex;
            align-items: center;
            min-width: 0;
            gap: .7rem;
        }

        .workspace-user-photo {
            width: 50px;
            height: 62px;
            flex: 0 0 auto;
            object-fit: cover;
            border: 2px solid #fff;
            border-radius: 4px;
            outline: 2px solid #0b5f78;
            background: #e8eef3;
        }

        .workspace-user-copy {
            display: block;
            min-width: 0;
        }

        .workspace-user-copy small,
        .workspace-user-copy strong,
        .workspace-user-copy span {
            display: block;
        }

        .workspace-user-copy small {
            color: #7a5a08;
            font-size: .68rem;
            font-weight: 900;
            text-transform: uppercase;
        }

        .workspace-user-copy strong {
            overflow: hidden;
            color: #00284d;
            font-size: 1rem;
            font-weight: 900;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .workspace-user-copy span {
            overflow: hidden;
            color: #475569;
            font-size: .82rem;
            font-weight: 800;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .workspace-primary-actions {
            display: flex;
            align-items: stretch;
            gap: .55rem;
        }

        .workspace-action {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            min-width: 142px;
            min-height: 48px;
            border: 2px solid transparent;
            border-radius: 5px;
            color: #fff;
            font-size: .91rem;
            font-weight: 900;
            text-decoration: none;
        }

        .workspace-action:hover,
        .workspace-action:focus {
            color: #fff;
            filter: brightness(.93);
        }

        .workspace-action.receive {
            background: #08765d;
            border-color: #075f4c;
        }

        .workspace-action.send {
            background: #0b5f91;
            border-color: #084d76;
        }

        .workspace-action i {
            font-size: 1.15rem;
        }

        .workspace-action-count {
            display: inline-grid;
            place-items: center;
            min-width: 25px;
            height: 25px;
            padding: 0 .35rem;
            border: 2px solid #fff;
            border-radius: 999px;
            color: #fff;
            background: #c81e32;
            box-shadow: 0 2px 6px rgba(107, 20, 32, .35);
            font-family: "Segoe UI", sans-serif;
            font-size: .75rem;
            font-weight: 900;
            line-height: 1;
        }

        .workspace-custody {
            display: flex;
            align-items: center;
            gap: .55rem;
            min-width: 126px;
            min-height: 50px;
            padding: .4rem .7rem;
            border: 2px solid #c81e32;
            border-radius: 5px;
            color: #7f1524;
            background: #fff4f5;
        }

        .workspace-custody strong {
            display: grid;
            place-items: center;
            min-width: 39px;
            height: 39px;
            padding: 0 .35rem;
            border-radius: 4px;
            color: #fff;
            background: #c81e32;
            font-family: "Segoe UI", sans-serif;
            font-size: 1.15rem;
            font-weight: 900;
        }

        .workspace-custody span {
            color: #7f1524;
            font-size: .8rem;
            font-weight: 900;
            line-height: 1.12;
        }

        body.has-workspace-bar main {
            padding-top: 18px;
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

            .workspace-bar-inner {
                grid-template-columns: minmax(190px, 1fr) auto;
            }

            .workspace-custody {
                grid-column: 2;
                grid-row: 1;
            }

            .workspace-primary-actions {
                grid-column: 1 / -1;
            }

            .workspace-action {
                flex: 1 1 50%;
            }
        }

        @media (max-width: 575.98px) {
            .workspace-user-bar {
                top: 55px;
                margin-top: 55px;
            }

            .workspace-bar-inner {
                grid-template-columns: minmax(0, 1fr) auto;
                gap: .55rem;
                min-height: 0;
                padding: .55rem 0;
            }

            .workspace-user-photo {
                width: 42px;
                height: 52px;
            }

            .workspace-user-copy small {
                font-size: .62rem;
            }

            .workspace-user-copy strong {
                font-size: .9rem;
            }

            .workspace-user-copy span {
                font-size: .75rem;
            }

            .workspace-custody {
                min-width: 104px;
                min-height: 46px;
                padding: .3rem .45rem;
            }

            .workspace-custody strong {
                min-width: 34px;
                height: 34px;
                font-size: 1rem;
            }

            .workspace-action {
                min-width: 0;
                min-height: 44px;
                font-size: .82rem;
            }
        }
    </style>
    @stack('css')
</head>

<body class="d-flex flex-column min-vh-100 {{ $workspaceBar ? 'has-workspace-bar' : '' }}">
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm">
        <div class="container">
            @php
                    $isLoggedIn = auth()->check();
                    $currentUser = $isLoggedIn ? auth()->user() : null;
                    $currentType = $currentUser?->user_type;
                    $isSuperAdmin = $currentUser?->hasRole('Super Admin') ?? false;
                    $departmentName = strtolower((string) ($currentUser?->departmentRelation?->name ?? ''));
                    $hasAssignedDepartment = $departmentName !== '';
                    $sectionTrackingKeywords = ['office assistant', 'dealing assistant', 'affidavit', 'requisite', 'put-up', 'put up', 'typing', 'compare', 'superintendent', 'ready table', 'record room', 'court'];

                    $canSeeAdminMenu = $isLoggedIn && $currentType === 'admin';
                    $canManageSystem = $canSeeAdminMenu && $isSuperAdmin;
                    $canSeeFilingMenu = $isLoggedIn && ($isSuperAdmin || str_contains($departmentName, 'filing'));
                    $canSeeRegistrarMenu = $isLoggedIn && str_contains($departmentName, 'registrar');
                    $canUseHandover = $isLoggedIn
                        && in_array($currentType, ['admin', 'staff'], true)
                        && ($hasAssignedDepartment || $isSuperAdmin);
                    $canSeeCourtMenu = $isLoggedIn && ($isSuperAdmin ||
                        str_contains($departmentName, 'office assistant') ||
                        str_contains($departmentName, 'dealing assistant') ||
                        str_contains($departmentName, 'assistant registrar')
                    );
                    $canSeeSectionReceiveMenu = $isLoggedIn && ($isSuperAdmin ||
                        ($currentType === 'staff' && $hasAssignedDepartment && !$canSeeFilingMenu && !$canSeeRegistrarMenu) ||
                        collect($sectionTrackingKeywords)->contains(
                            fn ($keyword) => str_contains($departmentName, $keyword)
                        )
                    );
                    $isAffidavitSection = str_contains($departmentName, 'affidavit');
                    $isCourtMovementSection = str_contains($departmentName, 'office assistant') || str_contains($departmentName, 'dealing assistant');

                    $brandRoute = '#';
                    if ($canSeeAdminMenu) {
                        $brandRoute = route('admin.dashboard');
                    } elseif ($currentType === 'staff') {
                        $brandRoute = route('admin.tracking.handover.workspace');
                    } elseif ($canSeeFilingMenu) {
                        $brandRoute = route('admin.tracking.filing.index');
                    } elseif ($canSeeRegistrarMenu) {
                        $brandRoute = route('admin.tracking.lookup');
                    } elseif ($canSeeSectionReceiveMenu && $isCourtMovementSection) {
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

                        @if($canManageSystem)
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
                    @endif

                    @if($canUseHandover || $canSeeAdminMenu || $canSeeFilingMenu || $canSeeSectionReceiveMenu || $canSeeCourtMenu || $canSeeRegistrarMenu)
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-upc-scan"></i> {{ __('messages.tracking_menu') }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @if($canUseHandover)
                                    <li><h6 class="dropdown-header">File Handover</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.tracking.handover.workspace') }}"><i class="bi bi-grid"></i>File Desk</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.tracking.handover.recipients') }}"><i class="bi bi-send"></i>Send Files</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.tracking.handover.index') }}"><i class="bi bi-arrow-left-right"></i>Handovers</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                @endif
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
                                    @if(!$isCourtMovementSection)
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

    @if($workspaceBar)
        <section class="workspace-user-bar" aria-label="Current user workspace">
            <div class="container workspace-bar-inner">
                <a href="{{ route('profile.edit') }}" class="workspace-user-identity text-decoration-none" title="View profile">
                    <img class="workspace-user-photo"
                        src="{{ $currentUser->photo ? asset($currentUser->photo) : asset('assets/img/user1-128x128.jpg') }}"
                        alt="{{ $currentUser->name }}">
                    <span class="workspace-user-copy">
                        <small>Current Working User</small>
                        <strong>{{ $currentUser->name }}</strong>
                        <span>{{ $currentUser->departmentRelation?->label ?? 'Department not assigned' }}</span>
                    </span>
                </a>

                <div class="workspace-primary-actions" aria-label="File actions">
                    <a class="workspace-action receive" href="{{ route('admin.tracking.section.receive') }}">
                        <i class="bi bi-upc-scan" aria-hidden="true"></i>
                        <span>Receive Files</span>
                        @if($workspaceBar['incoming_count'] > 0)
                            <span class="workspace-action-count" aria-label="{{ $workspaceBar['incoming_count'] }} files waiting">
                                {{ number_format($workspaceBar['incoming_count']) }}
                            </span>
                        @endif
                    </a>
                    <a class="workspace-action send" href="{{ route('admin.tracking.handover.recipients') }}">
                        <i class="bi bi-send" aria-hidden="true"></i>
                        <span>Send Files</span>
                    </a>
                </div>

                <a class="workspace-custody text-decoration-none" href="{{ route('admin.tracking.handover.workspace') }}#custody-files" aria-label="{{ $workspaceBar['custody_count'] }} files in custody">
                    <strong>{{ number_format($workspaceBar['custody_count']) }}</strong>
                    <span>in<br>custody</span>
                </a>
            </div>
        </section>
    @endif

    <main class="flex-fill">
        @yield('content')
    </main>

    <footer>
        <div class="container">
            <div class="row g-4 text-center text-sm-start mx-auto" style="max-width: 1000px;">
                @include('partials.footer-logo')
                <div class="col-sm-6 col-md-4">
                    <h5>{{ __('messages.quick_links') }}</h5>
                    <ul class="list-unstyled mb-0" style="line-height: 2;">
                        @if($canSeeAdminMenu)
                            <li><a href="{{ route('admin.dashboard') }}">{{ __('messages.dashboard') }}</a></li>
                        @endif
                        @if($canUseHandover)
                            <li><a href="{{ route('admin.tracking.handover.workspace') }}">File Desk</a></li>
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
                            @if(!$isCourtMovementSection)
                                <li><a href="{{ route('admin.tracking.court.return.index') }}">{{ __('messages.court_return') }}</a></li>
                            @endif
                        @endif
                        @if($canSeeRegistrarMenu)
                            <li><a href="{{ route('admin.tracking.lookup') }}">{{ __('messages.registrar_lookup') }}</a></li>
                        @endif
                    </ul>
                </div>
                <div class="col-sm-6 col-md-4">
                    <h5>{{ __('writ.footer.system_access') }}</h5>
                    <ul class="list-unstyled mb-0" style="line-height: 2;">
                        <li><a href="{{ route('lawyer.login') }}">{{ __('writ.footer.lawyer_login') }}</a></li>
                        <li><a href="{{ route('login') }}">{{ __('writ.footer.section_login') }}</a></li>
                    </ul>
                </div>
            </div>

            <hr class="my-4" style="border-color: #d4a017;">
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
