@extends('layouts.app')

@section('content')
<div class="container py-5">

    {{-- ALERT MESSAGES --}}
    @if(session('success'))
    <div class="alert alert-success d-flex align-items-center mb-4 shadow-sm rounded-3">
        <i class="fa-solid fa-circle-check me-2 fa-lg"></i>
        <div>{{ session('success') }}</div>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger d-flex align-items-center mb-4 shadow-sm rounded-3">
        <i class="fa-solid fa-circle-exclamation me-2 fa-lg"></i>
        <div>{{ session('error') }}</div>
    </div>
    @endif

    <div class="row g-4">
        {{-- LEFT COLUMN: TRIP DETAILS --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="opacity-75 small text-uppercase fw-bold">Booking Reference</span>
                        <h5 class="mb-0 fw-bold letter-spacing-1">{{ $booking->booking_reference }}</h5>
                    </div>
                    <span class="badge bg-white text-primary px-3 py-2 rounded-pill fw-bold text-uppercase">
                        {{ $booking->status }}
                    </span>
                </div>
                <div class="card-body p-4">

                    {{-- OUTBOUND --}}
                    <div class="mb-4">
                        <h6 class="fw-bold text-muted text-uppercase mb-3"><i class="fa-solid fa-bus me-2"></i> Outbound Trip</h6>
                        <div class="border rounded-3 p-3 bg-light">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="fw-bold mb-1">{{ $booking->schedule->origin->city }} <i class="fa-solid fa-arrow-right mx-2 text-muted small"></i> {{ $booking->schedule->destination->city }}</h5>
                                    <p class="mb-1 text-muted">
                                        {{ \Carbon\Carbon::parse($booking->schedule->departure_date)->format('F d, Y') }} at
                                        {{ \Carbon\Carbon::parse($booking->schedule->departure_time)->format('h:i A') }}
                                    </p>
                                    <span class="badge bg-secondary">{{ $booking->schedule->bus->name }}</span>
                                </div>
                                <div class="text-end">
                                    <div class="small text-muted">Seats</div>
                                    <div class="fw-bold h5 text-primary mb-0">{{ implode(', ', $booking->seat_numbers) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- RETURN (If Round Trip) --}}
                    @if($booking->return_schedule_id)
                    <div class="mb-4">
                        <h6 class="fw-bold text-muted text-uppercase mb-3"><i class="fa-solid fa-rotate-left me-2"></i> Return Trip</h6>
                        <div class="border rounded-3 p-3 bg-light">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="fw-bold mb-1">{{ $booking->returnSchedule->origin->city }} <i class="fa-solid fa-arrow-right mx-2 text-muted small"></i> {{ $booking->returnSchedule->destination->city }}</h5>
                                    <p class="mb-1 text-muted">
                                        {{ \Carbon\Carbon::parse($booking->returnSchedule->departure_date)->format('F d, Y') }} at
                                        {{ \Carbon\Carbon::parse($booking->returnSchedule->departure_time)->format('h:i A') }}
                                    </p>
                                    <span class="badge bg-secondary">{{ $booking->returnSchedule->bus->name }}</span>
                                </div>
                                <div class="text-end">
                                    <div class="small text-muted">Seats</div>
                                    <div class="fw-bold h5 text-danger mb-0">{{ implode(', ', $booking->return_seat_numbers ?? []) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <hr>

                    {{-- PASSENGER INFO --}}
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="small text-muted fw-bold">Passenger Name</label>
                            <div class="fw-bold">{{ $booking->guest_name }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="small text-muted fw-bold">Email</label>
                            <div class="fw-bold">{{ $booking->guest_email }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="small text-muted fw-bold">Phone</label>
                            <div class="fw-bold">{{ $booking->guest_phone ?? 'N/A' }}</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: ACTIONS --}}
        <div class="col-lg-4">

            {{-- SUMMARY CARD --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-uppercase mb-3">Payment Summary</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Paid</span>
                        <span class="fw-bold text-success">₱{{ number_format($booking->total_price, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Payment Method</span>
                        <span class="text-capitalize">{{ str_replace('_', ' ', $booking->payment_method ?? 'N/A') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Status</span>
                        <span class="badge bg-success text-uppercase">{{ $booking->payment_status ?? 'Paid' }}</span>
                    </div>
                </div>
            </div>

            {{-- CANCELLATION CARD --}}
            @if($booking->status !== 'cancelled')
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-danger mb-3"><i class="fa-solid fa-ban me-2"></i> Cancel Booking</h6>
                    <p class="small text-muted">
                        Cancellations are only allowed at least <strong>24 hours</strong> before the departure time. Refunds are subject to review.
                    </p>

                    @php
                    $dept = \Carbon\Carbon::parse($booking->schedule->departure_date . ' ' . $booking->schedule->departure_time);
                    $canCancel = now()->diffInHours($dept, false) >= 24;
                    @endphp

                    @if($canCancel)
                    <button class="btn btn-outline-danger w-100 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#cancelForm">
                        Request Cancellation
                    </button>

                    <div class="collapse mt-3" id="cancelForm">
                        <form action="{{ route('guest.bookings.cancel', $booking->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Reason for Cancellation</label>
                                <textarea name="cancellation_reason" class="form-control" rows="2" required placeholder="Why are you cancelling?"></textarea>
                            </div>
                            <button class="btn btn-danger w-100 fw-bold btn-sm">Confirm Cancellation</button>
                        </form>
                    </div>
                    @else
                    <div class="alert alert-secondary small mb-0 text-center">
                        Cancellation unavailable (less than 24h before trip).
                    </div>
                    @endif
                </div>
            </div>
            @else
            <div class="alert alert-danger shadow-sm rounded-4 border-0">
                <h6 class="fw-bold mb-1"><i class="fa-solid fa-circle-xmark me-2"></i> Booking Cancelled</h6>
                <p class="small mb-0">Reason: {{ $booking->cancellation_reason }}</p>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection