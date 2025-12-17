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

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        /* Sidebar Styling */
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #2c3e50;
            padding-top: 20px;
            color: #ecf0f1;
            z-index: 1000;
            transition: all 0.3s;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #34495e;
            margin-bottom: 20px;
        }

        .sidebar-header h4 {
            font-weight: 700;
            color: #fff;
            margin: 0;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            padding: 0;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: #bdc3c7;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background-color: #34495e;
            color: #fff;
            border-left-color: #3498db;
        }

        .sidebar-menu li a i {
            width: 30px;
            font-size: 1.1rem;
        }

        /* Main Content Styling */
        .main-content {
            margin-left: 250px;
            padding: 30px;
            padding-top: 50px;
            /* Spacing for top area */
            min-height: 100vh;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }

            .sidebar.active {
                margin-left: 0;
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
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

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @yield('content')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple sidebar toggle for mobile
        const toggleBtn = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
        }
    </script>
</body>

</html>