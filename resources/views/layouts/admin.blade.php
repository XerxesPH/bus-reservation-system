<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Southern Lines</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Admin CSS -->
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    @stack('styles')
</head>

<body>

    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <h4><i class="fa-solid fa-bus-simple me-2"></i>Admin Panel</h4>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('admin.bookings') }}" class="{{ request()->routeIs('admin.bookings') ? 'active' : '' }}">
                    <i class="fa-solid fa-ticket"></i> Bookings
                </a>
            </li>
            <li>
                <a href="{{ route('admin.schedules') }}" class="{{ request()->routeIs('admin.schedules') ? 'active' : '' }}">
                    <i class="fa-regular fa-calendar-days"></i> Schedules
                </a>
            </li>
            <li>
                <a href="{{ route('admin.templates') }}" class="{{ request()->routeIs('admin.templates*') ? 'active' : '' }}">
                    <i class="fa-solid fa-robot"></i> Route Templates
                </a>
            </li>
            <li>
                <a href="{{ route('admin.buses') }}" class="{{ request()->routeIs('admin.buses*') ? 'active' : '' }}">
                    <i class="fa-solid fa-bus"></i> Buses
                </a>
            </li>

            {{-- NEW FEATURES --}}
            <li class="mt-3">
                <small class="text-muted px-4 text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Management</small>
            </li>
            <li>
                <a href="{{ route('admin.messages') }}" class="{{ request()->routeIs('admin.messages*') ? 'active' : '' }}">
                    <i class="fa-solid fa-envelope"></i> Messages
                    @php $unreadCount = \App\Models\Message::where('is_read', false)->count(); @endphp
                    @if($unreadCount > 0)
                    <span class="badge bg-danger rounded-pill ms-2">{{ $unreadCount }}</span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i> Users
                </a>
            </li>
            <li>
                <a href="{{ route('admin.reports.sales') }}" class="{{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line"></i> Sales Report
                </a>
            </li>

            <li class="mt-3">
                <small class="text-muted px-4 text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Links</small>
            </li>
            <li>
                <a href="{{ route('home') }}">
                    <i class="fa-solid fa-house"></i> View Website
                </a>
            </li>
            <li class="mt-5">
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-link text-decoration-none w-100 text-start px-4 text-danger">
                        <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                    </button>
                </form>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Header (Mobile Toggle could go here) -->
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom d-md-none">
            <button class="btn btn-dark" id="sidebarToggle"><i class="fa-solid fa-bars"></i></button>
            <h5 class="m-0 fw-bold">Southern Lines Admin</h5>
        </div>


        @yield('content')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/admin.js') }}"></script>
    @stack('scripts')
</body>

</html>