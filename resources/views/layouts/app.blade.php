<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Southern Lines') }}</title>

    {{-- 1. Bootstrap & Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    {{-- 2. Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    {{-- 3. YOUR CUSTOM CSS --}}
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">

    <style>
        /* Navbar Specific Overrides */
        body {
            font-family: 'Poppins', sans-serif;
            padding-top: 0 !important;
            /* Essential for transparent nav */
        }

        /* Navbar Layout & Animation */
        .navbar {
            transition: background-color 0.3s ease;
            padding: 1rem 2rem;
        }

        /* Nav Links Styling */
        .nav-link {
            color: #000 !important;
            /* Force black text */
            font-weight: 500;
            font-size: 0.9rem;
            margin: 0 10px;
        }

        .nav-link:hover {
            color: #F4A261 !important;
            /* Orange hover effect */
        }

        /* Search Bar Styling */
        .custom-search {
            background-color: #FFF8E7;
            /* Light cream background */
            border: 1px solid #333;
            border-radius: 4px;
            padding: 5px 10px;
            width: 300px;
            display: flex;
            align-items: center;
        }

        .custom-search input {
            background: transparent;
            border: none;
            outline: none;
            width: 100%;
            font-size: 0.9rem;
            font-family: 'Poppins', sans-serif;
        }

        /* User Icon Styling */
        .user-icon-btn {
            font-size: 2rem;
            color: #000;
            line-height: 1;
            background: none;
            border: none;
            padding: 0;
        }

        .user-icon-btn:hover {
            color: #F4A261;
        }
    </style>
</head>

<body>

    {{-- NAVBAR --}}
    <nav class="navbar navbar-expand-lg fixed-top bg-transparent">
        <div class="container-fluid">

            {{-- 1. LOGO IMAGE --}}
            <a class="navbar-brand" href="{{ url('/') }}">
                {{-- REPLACE 'images/logo.png' with your file path --}}
                {{-- You can adjust 'height: 50px' to make it bigger or smaller --}}
                <img src="{{ asset('images/logo.png') }}" alt="Southern Lines Logo" style="height: 50px; width: auto;">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            {{-- 2. CENTER SECTION (Links + Search) --}}
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav mb-2 mb-lg-0 align-items-center">

                    {{-- Links Logic --}}
                    @auth
                    @if(Auth::user()->role === 'admin')
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.schedules') }}">Schedules</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.buses') }}">Buses</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.bookings') }}">Bookings</a></li>
                    @else
                    <li class="nav-item"><a class="nav-link" href="{{ url('/') }}">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Booking</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Schedule</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Seat Reservation</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('user.bookings') }}">Ticket</a></li>
                    @endif
                    @else
                    <li class="nav-item"><a class="nav-link" href="#">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Booking</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Schedule</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Log In</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Sign Up</a></li>
                    @endauth

                    {{-- SEARCH BAR --}}
                    <li class="nav-item ms-lg-4">
                        <div class="custom-search">
                            <input type="text" placeholder="Search Your Destination Now">
                            <i class="bi bi-search"></i>
                        </div>
                    </li>
                </ul>
            </div>

            {{-- 3. USER ICON (Right) --}}
            <div class="d-none d-lg-block ms-3">
                @auth
                <div class="dropdown">
                    <button class="user-icon-btn dropdown-toggle hide-arrow" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow mt-3">
                        <li class="px-3 py-2 text-muted small">Signed in as <strong>{{ Auth::user()->name }}</strong></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="{{ route('profile.index') }}">My Account</a></li>
                        <li><a class="dropdown-item" href="{{ route('user.bookings') }}">My Tickets</a></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="dropdown-item text-danger">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
                @else
                <a href="{{ route('login') }}" class="user-icon-btn">
                    <i class="bi bi-person-circle"></i>
                </a>
                @endauth
            </div>

        </div>
    </nav>

    {{-- MAIN CONTENT --}}
    <main>
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="bg-light text-muted py-5 mt-5 border-top">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    {{-- Footer Logo (Text Version) --}}
                    <h5 class="fw-bold text-dark">SOUTHERN LINES</h5>
                    <p class="small">Safe, comfortable, and affordable bus travel across the Calabarzon region.</p>
                </div>
                <div class="col-md-8 text-md-end">
                    <p class="small mb-0">&copy; {{ date('Y') }} Southern Lines Transportation. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>