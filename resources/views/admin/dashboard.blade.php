@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Admin Dashboard</h2>
        <a href="{{ route('admin.bookings') }}" class="btn btn-outline-dark ms-2">View All Bookings</a>
        <a href="{{ route('admin.create_schedule') }}" class="btn btn-primary">+ Generate New Schedules</a>
    </div>

    <div class="row text-center mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white p-3">
                <h3>₱ {{ number_format($totalRevenue) }}</h3>
                <small>Total Revenue</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white p-3">
                <h3>{{ $totalBookings }}</h3>
                <small>Total Bookings</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark p-3">
                <h3>{{ $todayTrips }}</h3>
                <small>Trips Today</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white p-3">
                <h3>{{ $totalUsers }}</h3>
                <small>Registered Users</small>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            Recent Bookings
        </div>
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Ref ID</th>
                    <th>Guest</th>
                    <th>Route</th>
                    <th>Date</th>
                    <th>Seats</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentBookings as $booking)
                <tr>
                    <td>#{{ $booking->id }}</td>
                    <td>{{ $booking->guest_name }}</td>
                    <td>{{ $booking->schedule->origin->city }} ➔ {{ $booking->schedule->destination->city }}</td>
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