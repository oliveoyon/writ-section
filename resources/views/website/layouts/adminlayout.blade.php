<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

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
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">RTFTS</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">

                    <!-- Regular Menu -->
                    <li class="nav-item">
                        <a class="nav-link" href="#">Home</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#">Dashboard</a>
                    </li>

                    <!-- Dropdown Menu 1 -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Writ Section</a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">All Writs</a></li>
                            <li><a class="dropdown-item" href="#">Create New</a></li>
                            <li><a class="dropdown-item" href="#">Pending Files</a></li>
                            <li><a class="dropdown-item" href="#">Completed Files</a></li>
                        </ul>
                    </li>

                    <!-- Dropdown Menu 2 -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Reports</a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">Monthly Report</a></li>
                            <li><a class="dropdown-item" href="#">Yearly Summary</a></li>
                            <li><a class="dropdown-item" href="#">Analytics Dashboard</a></li>
                        </ul>
                    </li>

                    <!-- Profile Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            {{ Auth::user()->name ?? 'Profile' }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">My Profile</a></li>
                            <li><a class="dropdown-item" href="#">Account Settings</a></li>
                            <li><a class="dropdown-item" href="#">Notifications</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item text-danger">Logout</button>
                                </form>
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
                    <h5>Contact Us</h5>
                    <p>Email: info@example.com<br>Phone: +880 1234 567890</p>
                </div>
                <div class="col-md-4 mb-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">Dashboard</a></li>
                        <li><a href="#">Reports</a></li>
                        <li><a href="#">Writ Section</a></li>
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
            <p class="text-center mt-2 mb-1">© {{ date('Y') }} RTFTS - Real Time File Tracking System</p>
            <p class="text-center small mb-0">Technical Assistance by Access to Justice For Women, GIZ Bangladesh</p>
        </div>
    </footer>

    <!-- JS -->
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
