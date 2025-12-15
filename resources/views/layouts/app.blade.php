<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Southern Lines') }} - Travel the Philippines</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,700&family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
</head>

<body class="bg-white">

    {{-- SOUTHERN LINES NAVBAR --}}
    <nav class="navbar navbar-expand-lg navbar-light bg-white py-3 sticky-top">
        <div class="container">
            <a class="navbar-brand brand-logo" href="{{ url('/') }}">
                SOUTHERN<br>L I N E S
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center gap-3">

                    {{-- Common Links --}}
                    <li class="nav-item"><a class="nav-link" href="#">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Activity</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Schedule</a></li>

                    {{-- Authentication Links --}}
                    @guest
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Log In</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Sign Up</a></li>
                    @else
                    {{-- Logged In User Dropdown --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-danger" href="#" role="button" data-bs-toggle="dropdown">
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu border-0 shadow mt-2">
                            <li><a class="dropdown-item small fw-bold" href="#">My Tickets</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button class="dropdown-item small fw-bold text-danger">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    @endguest

                    {{-- Search Bar (As seen in image) --}}
                    <li class="nav-item ms-lg-3">
                        <div class="input-group input-group-sm rounded-pill border bg-light overflow-hidden" style="width: 220px;">
                            <input type="text" class="form-control border-0 bg-transparent ps-3" placeholder="Search Destination">
                            <span class="input-group-text border-0 bg-transparent pe-3"><i class="fa fa-search text-muted"></i></span>
                        </div>
                    </li>
                </ul>
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
                    <h5 class="brand-logo mb-3" style="font-size: 1.2rem;">SOUTHERN<br>L I N E S</h5>
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