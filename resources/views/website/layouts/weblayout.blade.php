<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('writ.title') }}</title>

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
        }

        main {
            /* padding-top: 80px; */
            /* or height of your navbar */
        }


        .navbar {
            background: #00284d;
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
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">{{ __('writ.nav.brand') }}</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item"><a class="nav-link" href="#about">{{ __('writ.nav.about') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features">{{ __('writ.nav.features') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#process">{{ __('writ.nav.process') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#benefits">{{ __('writ.nav.benefits') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="#faq">{{ __('writ.nav.faq') }}</a></li>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-gold btn-sm" href="{{ route('lawyer.login') }}">{{ __('writ.nav.login') }}</a>
                    </li>
                    <!-- Add inside your <ul class="navbar-nav ms-auto align-items-lg-center">, preferably before the login button -->
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
                    <h5>{{ __('writ.footer.follow_us') }}</h5>
                    <a href="#" class="me-2">Facebook</a>
                    <a href="#" class="me-2">Twitter</a>
                    <a href="#">LinkedIn</a>
                </div>
            </div>
            <hr class="mt-3" style="border-color: #d4a017;">
            <p class="text-center mt-2 mb-0">{{ __('writ.footer.copyright') }}</p>
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
