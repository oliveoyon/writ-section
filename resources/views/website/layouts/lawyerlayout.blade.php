<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('lawyer.title') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- AOS Animation -->
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
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">{{ __('lawyer.nav.brand') }}</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">

                    <!-- Lawyer dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="lawyerMenu" role="button" data-bs-toggle="dropdown">
                            {{ Auth::user()->name ?? __('lawyer.nav.lawyer') }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="lawyerMenu">
                            <li><a class="dropdown-item" href="#">{{ __('lawyer.nav.dashboard') }}</a></li>
                            <li><a class="dropdown-item" href="#">{{ __('lawyer.nav.my_cases') }}</a></li>
                            <li><a class="dropdown-item" href="#">{{ __('lawyer.nav.notifications') }}</a></li>
                            <li><a class="dropdown-item" href="#">{{ __('lawyer.nav.messages') }}</a></li>
                            <li><a class="dropdown-item" href="#">{{ __('lawyer.nav.documents') }}</a></li>
                            <li><a class="dropdown-item" href="#">{{ __('lawyer.nav.settings') }}</a></li>
                        </ul>
                    </li>

                    <!-- Logout button as separate menu -->
                    <li class="nav-item ms-lg-3">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-danger btn-sm" type="submit">{{ __('lawyer.nav.logout') }}</button>
                        </form>
                    </li>

                    <!-- Language dropdown -->
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
                    <h5>{{ __('lawyer.footer.contact') }}</h5>
                    <p>{{ __('lawyer.footer.email') }}<br>{{ __('lawyer.footer.phone') }}</p>
                </div>
                <div class="col-md-4 mb-3">
                    <h5>{{ __('lawyer.footer.quick_links') }}</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">{{ __('lawyer.nav.dashboard') }}</a></li>
                        <li><a href="#">{{ __('lawyer.nav.my_cases') }}</a></li>
                        <li><a href="#">{{ __('lawyer.nav.messages') }}</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-3">
                    <h5>{{ __('lawyer.footer.follow_us') }}</h5>
                    <a href="#" class="me-2">Facebook</a>
                    <a href="#" class="me-2">Twitter</a>
                    <a href="#">LinkedIn</a>
                </div>
            </div>
            <hr class="mt-3" style="border-color: #d4a017;">
            <p class="text-center mt-2 mb-0">{{ __('lawyer.footer.copyright') }}</p>
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
