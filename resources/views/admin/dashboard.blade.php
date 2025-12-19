@extends('layouts.admin')

@push('styles')
<link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">Dashboard Overview</h2>
        <div>
            <a href="{{ route('admin.create_schedule') }}" class="btn btn-primary shadow-sm">
                <i class="fa-solid fa-plus me-2"></i> Generate New Schedules
            </a>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-white card-revenue">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 fw-bold">₱{{ number_format($totalRevenue) }}</h3>
                            <small class="text-white-50 text-uppercase fw-bold">Total Revenue</small>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center dashboard-icon-circle">
                            <i class="fa-solid fa-sack-dollar fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-white card-bookings">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 fw-bold">{{ $totalBookings }}</h3>
                            <small class="text-white-50 text-uppercase fw-bold">Total Bookings</small>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center dashboard-icon-circle">
                            <i class="fa-solid fa-ticket fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-white card-trips">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 fw-bold">{{ $todayTrips }}</h3>
                            <small class="text-white-50 text-uppercase fw-bold">Trips Today</small>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center dashboard-icon-circle">
                            <i class="fa-solid fa-bus fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-white card-users">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 fw-bold">{{ $totalUsers }}</h3>
                            <small class="text-white-50 text-uppercase fw-bold">Active Users</small>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center dashboard-icon-circle">
                            <i class="fa-solid fa-users fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Shortcuts Row --}}
    <div class="row mb-5">
        <div class="col-12">
            <h5 class="fw-bold text-muted mb-3">Quick Actions</h5>
        </div>
        <div class="col-md-4">
            <a href="{{ route('admin.templates') }}" class="card border-0 shadow-sm p-3 text-decoration-none h-100 hover-lift">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3 me-3">
                        <i class="fa-solid fa-robot fa-xl"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Route Templates</h6>
                        <small class="text-muted">Manage automated schedule generation plans.</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('admin.buses') }}" class="card border-0 shadow-sm p-3 text-decoration-none h-100 hover-lift">
                <div class="d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 text-info rounded-3 p-3 me-3">
                        <i class="fa-solid fa-bus fa-xl"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Manage Fleet</h6>
                        <small class="text-muted">Add, edit, or remove buses and drivers.</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('admin.schedules') }}" class="card border-0 shadow-sm p-3 text-decoration-none h-100 hover-lift">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 text-success rounded-3 p-3 me-3">
                        <i class="fa-solid fa-calendar-days fa-xl"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Trip Schedules</h6>
                        <small class="text-muted">View active trips and manage cancellations.</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-bold">Recent Bookings</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Booking Reference</th>
                        <th>Guest</th>
                        <th>Route</th>
                        <th>Bus</th>
                        <th>Date</th>
                        <th>Seats</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentBookings as $booking)
                    <tr>
                        <td>{{ $booking->booking_reference }}</td>
                        <td>{{ $booking->guest_name }}</td>
                        <td>{{ $booking->schedule->origin->city }} ➔ {{ $booking->schedule->destination->city }}</td>
                        <td>{{ $booking->schedule->bus->code ?? $booking->schedule->bus->bus_number ?? $booking->schedule->bus->name ?? 'N/A' }}</td>
                        <td>{{ $booking->schedule->departure_date }}</td>
                        <td>{{ count($booking->seat_numbers) }}</td>
                        <td>₱ {{ number_format($booking->total_price) }}</td>
                        <td><span class="badge bg-success">{{ $booking->status }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endsection