<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('writ.title'))</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- AOS Animation -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <style>
        /* Load SolaimanLipi font */
        @font-face {
            font-family: 'SolaimanLipi';
            src: url('/assets/font/SolaimanLipi.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        /* Apply to body */
        body {
            font-family: 'SolaimanLipi', "Segoe UI", sans-serif;
            padding-top: 64px;
        }

        .navbar {
            background: #00284d;
            min-height: 64px;
        }

        .navbar-brand,
        .navbar-nav .nav-link {
            color: #fff;
        }

        .navbar-nav .nav-link:hover {
            color: #d4a017;
        }

        .btn-gold {
            background: #d4a017;
            color: white;
        }

        .btn-gold:hover {
            background: #b98c14;
            color: white;
        }

        .hero {
            background: linear-gradient(rgba(0, 0, 50, 0.45), rgba(0, 0, 0, 0.6)),
                url('https://images.unsplash.com/photo-1587821525056-895ca712a5a2?q=80&w=1800') no-repeat center center/cover;
            padding: 150px 0;
            color: #fff;
            text-align: center;
        }

        .section-title {
            font-weight: 700;
            font-size: 32px;
            color: #003366;
            margin-bottom: 20px;
        }

        .feature-box {
            background: #ffffff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
            transition: 0.3s ease;
        }

        .feature-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 18px rgba(0, 0, 0, 0.15);
        }

        .steps {
            background: #f7f9fc;
        }

        .accordion-button:not(.collapsed) {
            color: #003366;
            background-color: #d4a017;
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
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('web.home') }}">{{ __('writ.nav.brand') }}</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item"><a class="nav-link" href="{{ route('web.home') }}#about">{{ __('writ.nav.about') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('web.home') }}#features">{{ __('writ.nav.features') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('web.home') }}#process">{{ __('writ.nav.process') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('web.home') }}#benefits">{{ __('writ.nav.benefits') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('web.home') }}#faq">{{ __('writ.nav.faq') }}</a></li>
                    @guest
                        <li class="nav-item ms-lg-3">
                            <a class="nav-link" href="{{ route('lawyer.login') }}">{{ __('writ.nav.login') }}</a>
                        </li>
                    @else
                        @if(auth()->user()->user_type === 'lawyer')
                            <li class="nav-item ms-lg-3">
                                <a class="nav-link" href="{{ route('lawyer.dashboard') }}">{{ __('lawyer.nav.dashboard') }}</a>
                            </li>
                        @endif
                        <li class="nav-item ms-lg-3">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-link nav-link border-0">{{ __('lawyer.nav.logout') }}</button>
                            </form>
                        </li>
                    @endguest
                    <li class="nav-item dropdown ms-lg-3">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            {{ strtoupper(app()->getLocale()) }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item"
                                    href="{{ route('locale.set', ['locale' => 'en']) }}">English</a></li>
                            <li><a class="dropdown-item" href="{{ route('locale.set', ['locale' => 'bn']) }}">বাংলা</a>
                            </li>
                        </ul>
                    </li>

                </ul>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="flex-fill">
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer>
        <div class="container">
            <div class="row text-center text-md-start">
                <div class="col-md-4 mb-3">
                    <h5>{{ __('writ.footer.contact') }}</h5>
                    <p>{{ __('writ.footer.email') }}<br>{{ __('writ.footer.phone') }}</p>
                </div>
                <div class="col-md-4 mb-3">
                    <h5>{{ __('writ.footer.quick_links') }}</h5>
                    <ul class="list-unstyled">
                        <li><a href="#about">{{ __('writ.nav.about') }}</a></li>
                        <li><a href="#features">{{ __('writ.nav.features') }}</a></li>
                        <li><a href="#faq">{{ __('writ.nav.faq') }}</a></li>
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
                <span>{{ __('writ.footer.copyright', ['year' => date('Y')]) }}</span>
                <span>{{ __('writ.footer.implemented_by') }}</span>
                <span>{{ __('writ.footer.technical_assistance') }}</span>
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
</body>

</html>
