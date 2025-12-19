@extends('layouts.app')

@section('content')
<div class="container page-container">
    <div class="page-header-left mb-4">
        <h1 class="page-title mb-1">My Bookings</h1>
        <p class="page-subtitle mb-0">View and manage your trip history.</p>
    </div>

    @if($bookings->isEmpty())
    <div class="alert alert-info">
        You haven't booked any trips yet. <a href="{{ url('/') }}">Find a trip now!</a>
    </div>
    @else
    <div class="row">
        @foreach($bookings as $booking)
        <div class="col-md-6 mb-4">
            <div class="card card-unified h-100">
                <div class="card-header d-flex justify-content-between align-items-center 
                            {{ $booking->status == 'cancelled' ? 'bg-secondary text-white' : 'bg-primary text-white' }}">
                    <span class="fw-bold">Reference: #{{ $booking->booking_reference ?? $booking->id }}</span>
                    <span class="badge bg-light text-dark">{{ strtoupper($booking->status) }}</span>
                </div>

                <div class="card-body">
                    <h5 class="card-title text-center mb-3">
                        {{ $booking->schedule->origin->city }}
                        <span class="text-muted">➔</span>
                        {{ $booking->schedule->destination->city }}
                    </h5>

                    <div class="row mb-2">
                        <div class="col-6 text-muted">Departure:</div>
                        <div class="col-6 fw-bold text-end">
                            {{ \Carbon\Carbon::parse($booking->schedule->departure_date)->format('M d, Y') }}<br>
                            {{ \Carbon\Carbon::parse($booking->schedule->departure_time)->format('h:i A') }}
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-6 text-muted">Bus:</div>
                        <div class="col-6 fw-bold text-end">
                            {{ $booking->schedule->bus->code }} ({{ ucfirst($booking->schedule->bus->type) }})
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6 text-muted">Seats:</div>
                        <div class="col-6 fw-bold text-end">
                            @foreach($booking->seat_numbers as $seat)
                            <span class="badge bg-secondary">{{ $seat }}</span>
                            @endforeach
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Total Paid:</span>
                        <h4 class="text-success m-0">₱ {{ number_format($booking->total_price, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection