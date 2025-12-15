@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow-lg border-0 mx-auto" style="max-width: 600px;">
        <div class="card-header bg-success text-white text-center py-4">
            <h1 class="display-4">✔ Success!</h1>
            <p class="lead mb-0">Your seat has been reserved.</p>
        </div>

        <div class="card-body p-5">
            <h5 class="text-muted text-center mb-4">Booking Reference: #{{ $booking->id }}</h5>

            <div class="row mb-4">
                <div class="col-6 text-muted">Passenger:</div>
                <div class="col-6 text-end fw-bold">{{ $booking->guest_name ?? $booking->user->name }}</div>
            </div>

            {{-- Outbound Trip Details --}}
            <h6 class="text-danger fw-bold border-bottom pb-2 mb-3">
                @if($booking->return_schedule_id) Outbound Trip @else Trip Details @endif
            </h6>

            <div class="row mb-3">
                <div class="col-6 text-muted">Route:</div>
                <div class="col-6 text-end fw-bold">
                    {{ $booking->schedule->origin->city }} ➔ {{ $booking->schedule->destination->city }}
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-6 text-muted">Departure:</div>
                <div class="col-6 text-end fw-bold">
                    {{ \Carbon\Carbon::parse($booking->schedule->departure_date)->format('M d, Y') }}<br>
                    {{ \Carbon\Carbon::parse($booking->schedule->departure_time)->format('h:i A') }}
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-6 text-muted">Seats:</div>
                <div class="col-6 text-end fw-bold">
                    @foreach($booking->seat_numbers as $seat)
                    <span class="badge bg-primary">{{ $seat }}</span>
                    @endforeach
                </div>
            </div>

            {{-- Return Trip Details (if it exists) --}}
            @if($booking->return_schedule_id && $booking->returnSchedule)
            <h6 class="text-danger fw-bold border-bottom pb-2 my-3">Return Trip</h6>

            <div class="row mb-3">
                <div class="col-6 text-muted">Route:</div>
                <div class="col-6 text-end fw-bold">
                    {{ $booking->returnSchedule->origin->city }} ➔ {{ $booking->returnSchedule->destination->city }}
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-6 text-muted">Departure:</div>
                <div class="col-6 text-end fw-bold">
                    {{ \Carbon\Carbon::parse($booking->returnSchedule->departure_date)->format('M d, Y') }}<br>
                    {{ \Carbon\Carbon::parse($booking->returnSchedule->departure_time)->format('h:i A') }}
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-6 text-muted">Seats:</div>
                <div class="col-6 text-end fw-bold">
                    @foreach($booking->return_seat_numbers as $seat)
                    <span class="badge bg-primary">{{ $seat }}</span>
                    @endforeach
                </div>
            </div>
            @endif


            <hr>

            <div class="row mb-4">
                <div class="col-6 fs-5">Total Paid:</div>
                <div class="col-6 text-end fs-4 text-success fw-bold">
                    ₱ {{ number_format($booking->total_price, 2) }}
                </div>
            </div>

            <div class="d-grid gap-2">
                <a href="{{ url('/') }}" class="btn btn-outline-dark btn-lg">Book Another Trip</a>
            </div>
        </div>
    </div>
</div>
@endsection