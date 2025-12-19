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

    {{-- 3. Custom CSS --}}
    <link href="{{ asset('css/design-system.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    {{-- 4. Page-specific styles --}}
    @stack('styles')
</head>

<body>

    {{-- CORNER WAVE BACKGROUNDS (Hidden on welcome, login, register, admin routes) --}}
    @unless(request()->routeIs('home') || request()->routeIs('welcome') || request()->routeIs('login') || request()->routeIs('register') || request()->is('admin*'))
    <img src="{{ asset('images/top-right.png') }}" alt="" class="corner-wave wave-top-right">
    <img src="{{ asset('images/bottom-left.png') }}" alt="" class="corner-wave wave-bottom-left">
    @endunless

    {{-- NAVBAR --}}
    <nav class="navbar navbar-expand-lg navbar-light fixed-top bg-transparent {{ (!Auth::check() || Auth::user()->role !== 'admin') ? 'nav-drawer-enabled' : '' }}" id="mainNavbar">
        <div class="container-fluid px-4">

            {{-- 1. LOGO (Left) --}}
            <a class="navbar-brand" href="{{ url('/') }}">
                <img src="{{ asset('images/logo.png') }}" alt="Southern Lines Logo" class="logo-img">
            </a>

            {{-- Hide navigation buttons and user icon on login/register pages --}}
            @unless(request()->routeIs('login') || request()->routeIs('register'))

            @if(!Auth::check() || Auth::user()->role !== 'admin')
            <button type="button" class="btn p-2 border-0 bg-transparent d-lg-none" id="mobileDrawerToggle" aria-label="Open menu" aria-expanded="false">
                <i class="fa-solid fa-bars fa-lg"></i>
            </button>
            @endif

            {{-- 2. CENTER NAV LINKS --}}
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 align-items-center">
                    @auth
                    @if(Auth::user()->role === 'admin')
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.schedules') }}">Schedules</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.buses') }}">Buses</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.bookings') }}">Bookings</a></li>
                    @else
                    <li class="nav-item"><a class="nav-link" href="{{ url('/') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('user.bookings') }}">Booking</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('pages.schedule') }}">Schedule</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('pages.contact') }}">Contact</a></li>
                    @endif
                    @else
                    <li class="nav-item"><a class="nav-link" href="{{ url('/') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('guest.bookings.search') }}">Booking</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('pages.schedule') }}">Schedule</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('pages.contact') }}">Contact</a></li>
                    @endauth
                </ul>

                {{-- 3. RIGHT SECTION: Auth buttons or User dropdown --}}
                <div class="d-flex align-items-center gap-2">
                    @auth
                    {{-- Authenticated User: Dropdown with Profile & Logout --}}
                    <div class="dropdown">
                        <button class="user-icon-btn dropdown-toggle hide-arrow" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            @if(Auth::user()->avatar)
                            <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Profile" class="rounded-circle" style="width: 34px; height: 34px; object-fit: cover; display: block;">
                            @else
                            <i class="bi bi-person-circle"></i>
                            @endif
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow mt-2">
                            <li class="px-3 py-2 text-muted small">{{ Auth::user()->name }}</li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="{{ route('profile.index') }}"><i class="fas fa-user me-2"></i>My Account</a></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                    @else
                    {{-- Guest: Login & Sign Up Buttons --}}
                    <a href="{{ route('login') }}" class="btn btn-outline-dark btn-sm fw-bold px-3">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-dark btn-sm fw-bold px-3">
                        Sign Up
                    </a>
                    @endauth
                </div>
            </div>

            @endunless

        </div>
    </nav>

    @unless(request()->routeIs('login') || request()->routeIs('register') || request()->is('admin*'))
    @if(!Auth::check() || Auth::user()->role !== 'admin')
    <div class="mobile-drawer-overlay" id="mobileDrawerOverlay" aria-hidden="true"></div>
    <aside class="mobile-drawer" id="mobileDrawer" aria-hidden="true">
        <div class="mobile-drawer-header">
            <div class="mobile-drawer-title">Menu</div>
            <button type="button" class="mobile-drawer-close" id="mobileDrawerClose" aria-label="Close menu">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="mobile-drawer-body">
            @auth
            @if(Auth::user()->role !== 'admin')
            <a class="mobile-drawer-link" href="{{ url('/') }}">Home</a>
            <a class="mobile-drawer-link" href="{{ route('user.bookings') }}">Booking</a>
            <a class="mobile-drawer-link" href="{{ route('pages.schedule') }}">Schedule</a>
            <a class="mobile-drawer-link" href="{{ route('pages.contact') }}">Contact</a>
            <hr class="my-3">
            <a class="mobile-drawer-link" href="{{ route('profile.index') }}">My Account</a>
            <form action="{{ route('logout') }}" method="POST" class="mt-2">
                @csrf
                <button type="submit" class="mobile-drawer-link mobile-drawer-link-danger w-100 text-start">Logout</button>
            </form>
            @endif
            @else
            <a class="mobile-drawer-link" href="{{ url('/') }}">Home</a>
            <a class="mobile-drawer-link" href="{{ route('guest.bookings.search') }}">Booking</a>
            <a class="mobile-drawer-link" href="{{ route('pages.schedule') }}">Schedule</a>
            <a class="mobile-drawer-link" href="{{ route('pages.contact') }}">Contact</a>
            <hr class="my-3">
            <a class="mobile-drawer-link" href="{{ route('login') }}">Login</a>
            <a class="mobile-drawer-link" href="{{ route('register') }}">Sign Up</a>
            @endauth
        </div>
    </aside>
    @endif
    @endunless

    {{-- MAIN CONTENT --}}
    @if(request()->routeIs('login') || request()->routeIs('register'))
    <main class="main-content-auth">
        @yield('content')
    </main>
    @else
    <main class="main-content-wrapper">
        @yield('content')
    </main>
    @endif

    {{-- FOOTER (Hidden on login/register pages) --}}
    @unless(request()->routeIs('login') || request()->routeIs('register'))
    <footer class="footer-dark">
        <div class="container">
            <div class="row g-4">
                {{-- Brand Section --}}
                <div class="col-lg-4 col-md-6">
                    <h5 class="footer-brand">SOUTHERN LINES</h5>
                    <p class="footer-text">Safe, comfortable, and affordable bus travel across the Calabarzon region. Your journey, our priority.</p>
                    <div class="footer-social">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                {{-- Quick Links --}}
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="footer-heading">Quick Links</h6>
                    <ul class="footer-links">
                        <li><a href="{{ url('/') }}">Home</a></li>
                        <li><a href="{{ route('pages.schedule') }}">Schedules</a></li>
                        <li><a href="{{ route('guest.bookings.search') }}">Track Booking</a></li>
                    </ul>
                </div>

                {{-- Support --}}
                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="footer-heading">Support</h6>
                    <ul class="footer-links">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">FAQs</a></li>
                        <li><a href="#">Contact Us</a></li>
                    </ul>
                </div>

                {{-- Contact Info --}}
                <div class="col-lg-4 col-md-6">
                    <h6 class="footer-heading">Contact Us</h6>
                    <ul class="footer-contact">
                        <li><i class="fas fa-map-marker-alt"></i> 123 Main Terminal, Calabarzon</li>
                        <li><i class="fas fa-phone"></i> (02) 8123-4567</li>
                        <li><i class="fas fa-envelope"></i> info@southernlines.ph</li>
                    </ul>
                </div>
            </div>

            {{-- Bottom Bar --}}
            <div class="footer-bottom">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start">
                        <p class="mb-0">&copy; {{ date('Y') }} Southern Lines Transportation. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <a href="#" class="footer-bottom-link">Privacy Policy</a>
                        <a href="#" class="footer-bottom-link">Terms of Service</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    @endunless

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/main.js') }}"></script>

    {{-- Smart Navbar: Hide on scroll down, show on scroll up --}}
    <script>
        (function() {
            const navbar = document.getElementById('mainNavbar');
            if (!navbar) return;

            let lastScrollY = window.scrollY;
            let ticking = false;

            function updateNavbar() {
                const currentScrollY = window.scrollY;

                if (currentScrollY > lastScrollY && currentScrollY > 80) {
                    // Scrolling down & past threshold - hide navbar
                    navbar.classList.add('navbar-hidden');
                } else {
                    // Scrolling up - show navbar
                    navbar.classList.remove('navbar-hidden');
                }

                lastScrollY = currentScrollY;
                ticking = false;
            }

            window.addEventListener('scroll', function() {
                if (!ticking) {
                    window.requestAnimationFrame(updateNavbar);
                    ticking = true;
                }
            });
        })();
    </script>

    @stack('scripts')
</body>

</html>