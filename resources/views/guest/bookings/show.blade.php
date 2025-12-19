@extends('layouts.app')

@section('content')
<div class="container page-container">

    <div class="page-header-left d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <h1 class="page-title mb-1">Booking Details</h1>
            <p class="page-subtitle mb-0">Reference: {{ $booking->booking_reference }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('guest.bookings.search') }}" class="btn btn-outline-secondary btn-unified btn-unified-sm fw-bold">
                Back
            </a>
        </div>
    </div>

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

    @php
    $totalPaid = $booking->total_price;
    if ($booking->linkedBooking) {
    $totalPaid = ($booking->total_price ?? 0) + ($booking->linkedBooking->total_price ?? 0);
    }

    $returnSchedule = null;
    $returnSeats = null;
    $returnBus = null;
    if ($booking->return_schedule_id && $booking->returnSchedule) {
    $returnSchedule = $booking->returnSchedule;
    $returnSeats = $booking->return_seat_numbers;
    $returnBus = $booking->returnSchedule->bus->code
    ?? ($booking->returnSchedule->bus->bus_number ?? null)
    ?? ($booking->returnSchedule->bus->name ?? null)
    ?? 'N/A';
    } elseif ($booking->linkedBooking && $booking->linkedBooking->schedule) {
    $returnSchedule = $booking->linkedBooking->schedule;
    $returnSeats = $booking->linkedBooking->seat_numbers;
    $returnBus = ($booking->linkedBooking->bus_number ?: (
    $booking->linkedBooking->schedule->bus->code
    ?? ($booking->linkedBooking->schedule->bus->bus_number ?? null)
    ?? ($booking->linkedBooking->schedule->bus->name ?? null)
    )) ?: 'N/A';
    }
    @endphp

    <div class="row g-4">
        {{-- LEFT COLUMN: TRIP DETAILS --}}
        <div class="col-lg-8">
            <div class="card card-unified">
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
                    @if($returnSchedule)
                    <div class="mb-4">
                        <h6 class="fw-bold text-muted text-uppercase mb-3"><i class="fa-solid fa-rotate-left me-2"></i> Return Trip</h6>
                        <div class="border rounded-3 p-3 bg-light">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="fw-bold mb-1">{{ $returnSchedule->origin->city }} <i class="fa-solid fa-arrow-right mx-2 text-muted small"></i> {{ $returnSchedule->destination->city }}</h5>
                                    <p class="mb-1 text-muted">
                                        {{ \Carbon\Carbon::parse($returnSchedule->departure_date)->format('F d, Y') }} at
                                        {{ \Carbon\Carbon::parse($returnSchedule->departure_time)->format('h:i A') }}
                                    </p>
                                    <span class="badge bg-secondary">{{ $returnSchedule->bus->name }}</span>
                                </div>
                                <div class="text-end">
                                    <div class="small text-muted">Seats</div>
                                    <div class="fw-bold h5 text-danger mb-0">{{ is_array($returnSeats) ? implode(', ', $returnSeats) : $returnSeats }}</div>
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
            <div class="card card-unified mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-uppercase mb-3">Payment Summary</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Paid</span>
                        <span class="fw-bold text-success">₱{{ number_format($totalPaid, 2) }}</span>
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

            @if($booking->status !== 'cancelled' && !empty($booking->booking_reference))
            <div class="card card-unified mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-uppercase mb-3">E-Ticket</h6>
                    <div class="d-grid gap-2">
                        <button type="button"
                            class="btn btn-outline-dark btn-unified btn-unified-sm fw-bold"
                            data-bs-toggle="modal"
                            data-bs-target="#ticketModal"
                            data-reference="{{ $booking->booking_reference }}"
                            data-passenger="{{ $booking->user ? $booking->user->name : ($booking->guest_name ?? 'Passenger') }}"
                            data-origin="{{ $booking->schedule->origin->city }}"
                            data-destination="{{ $booking->schedule->destination->city }}"
                            data-date="{{ \Carbon\Carbon::parse($booking->schedule->departure_date)->format('M d, Y') }}"
                            data-time="{{ \Carbon\Carbon::parse($booking->schedule->departure_time)->format('h:i A') }}"
                            data-bus="{{ ($booking->bus_number ?: ($booking->schedule->bus->code ?: ($booking->schedule->bus->bus_number ?? null) ?: ($booking->schedule->bus->name ?? null))) ?: 'N/A' }}"
                            data-seats="{{ is_array($booking->seat_numbers) ? implode(', ', $booking->seat_numbers) : $booking->seat_numbers }}"
                            data-price="{{ number_format($totalPaid, 2) }}"
                            data-status="{{ ucfirst($booking->status) }}"
                            data-trip-type="{{ $booking->trip_type ?? 'oneway' }}"
                            data-return-origin="{{ $returnSchedule ? $returnSchedule->origin->city : '' }}"
                            data-return-destination="{{ $returnSchedule ? $returnSchedule->destination->city : '' }}"
                            data-return-date="{{ $returnSchedule ? \Carbon\Carbon::parse($returnSchedule->departure_date)->format('M d, Y') : '' }}"
                            data-return-time="{{ $returnSchedule ? \Carbon\Carbon::parse($returnSchedule->departure_time)->format('h:i A') : '' }}"
                            data-return-bus="{{ $returnBus ?? '' }}"
                            data-return-seats="{{ is_array($returnSeats) ? implode(', ', $returnSeats) : ($returnSeats ?? '') }}">
                            View E-Ticket
                        </button>
                        <a href="{{ route('ticket.download', ['bookingReference' => $booking->booking_reference]) }}" class="btn btn-dark btn-unified btn-unified-sm fw-bold">
                            Download E-Ticket
                        </a>
                    </div>
                </div>
            </div>
            @endif

            {{-- CANCELLATION CARD --}}
            @if($booking->status !== 'cancelled')
            <div class="card card-unified">
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
                    <button class="btn btn-outline-danger btn-unified btn-unified-sm w-100 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#cancelForm">
                        Request Cancellation
                    </button>

                    <div class="collapse mt-3" id="cancelForm">
                        <form action="{{ route('guest.bookings.cancel', $booking->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="guest_email" value="{{ $booking->guest_email }}">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Reason for Cancellation</label>
                                <textarea name="cancellation_reason" class="form-control" rows="2" required placeholder="Why are you cancelling?"></textarea>
                            </div>
                            <button class="btn btn-danger btn-unified btn-unified-sm w-100 fw-bold">Confirm Cancellation</button>
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

<div class="modal fade" id="ticketModal" tabindex="-1" aria-labelledby="ticketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header text-white border-0 py-3" style="background: #1E293B;">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="ticketModalLabel">
                        <i class="fas fa-ticket me-2" style="color: #FFC107;"></i>
                        E-Ticket: <span id="modal-reference" class="text-warning">--</span>
                    </h5>
                    <small class="text-light opacity-75">Southern Lines Bus Transportation</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4" id="ticket-content">
                <div class="row">
                    <div class="col-lg-7">
                        <div class="mb-3">
                            <small class="text-muted text-uppercase" style="font-size: 0.7rem;">Passenger</small>
                            <div class="fw-bold fs-5" id="modal-passenger" style="color: #1E293B;">--</div>
                        </div>

                        <div class="d-flex align-items-center mb-4 p-3 rounded-3" style="background: #F8FAFC;">
                            <div class="text-center flex-grow-1">
                                <small class="text-muted text-uppercase d-block" style="font-size: 0.65rem;">From</small>
                                <div class="fw-bold" id="modal-origin" style="color: #1E293B; font-size: 1.1rem;">--</div>
                            </div>
                            <div class="px-3">
                                <i class="fa-solid fa-bus text-muted"></i>
                            </div>
                            <div class="text-center flex-grow-1">
                                <small class="text-muted text-uppercase d-block" style="font-size: 0.65rem;">To</small>
                                <div class="fw-bold" id="modal-destination" style="color: #1E293B; font-size: 1.1rem;">--</div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-6">
                                <small class="text-muted text-uppercase d-block" style="font-size: 0.65rem;">Date</small>
                                <div class="fw-bold" id="modal-date">--</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted text-uppercase d-block" style="font-size: 0.65rem;">Time</small>
                                <div class="fw-bold" id="modal-time">--</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted text-uppercase d-block" style="font-size: 0.65rem;">Bus No.</small>
                                <div class="fw-bold" id="modal-bus">--</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted text-uppercase d-block" style="font-size: 0.65rem;">Seat(s)</small>
                                <div class="fw-bold" id="modal-seats">--</div>
                            </div>
                        </div>

                        <div id="modal-return-block" class="mt-4 pt-3 border-top" style="display: none;">
                            <div class="fw-bold mb-1">Return Trip</div>
                            <div class="text-muted small mb-2">
                                <span id="modal-return-origin">--</span>
                                <i class="fa-solid fa-arrow-right mx-1 text-muted small"></i>
                                <span id="modal-return-destination">--</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <small class="text-muted text-uppercase d-block" style="font-size: 0.65rem;">Date</small>
                                    <div class="fw-bold" id="modal-return-date">--</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted text-uppercase d-block" style="font-size: 0.65rem;">Time</small>
                                    <div class="fw-bold" id="modal-return-time">--</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted text-uppercase d-block" style="font-size: 0.65rem;">Bus No.</small>
                                    <div class="fw-bold" id="modal-return-bus">--</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted text-uppercase d-block" style="font-size: 0.65rem;">Seat(s)</small>
                                    <div class="fw-bold" id="modal-return-seats">--</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5 text-center ticket-modal-right">
                        <div class="mb-3 p-3 rounded-3" style="background: #FFF8E1; border: 2px solid #FFC107;">
                            <small class="text-muted text-uppercase d-block" style="font-size: 0.65rem;">Total Fare</small>
                            <div class="fw-bold" style="font-size: 1.8rem; color: #1E293B;">₱<span id="modal-price">--</span></div>
                        </div>

                        <div class="mb-3" id="modal-qr-container"></div>
                        <div class="small text-muted">Scan for verification</div>

                        <div class="mt-3">
                            <span class="badge bg-success px-3 py-2 rounded-pill" id="modal-status">Confirmed</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top text-center">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Keep this ticket safe. Present to conductor upon boarding.
                    </small>
                </div>
            </div>

            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Close
                </button>
                <button type="button" class="btn btn-navy" onclick="printTicket()">
                    <i class="fas fa-print me-1"></i> Print Ticket
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .ticket-modal-right {
        border-top: 1px solid #E2E8F0;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
    }

    @media (min-width: 992px) {
        .ticket-modal-right {
            border-top: 0;
            margin-top: 0;
            padding-top: 0;
            border-left: 1px solid #E2E8F0;
            padding-left: 1.5rem;
        }
    }

    @media print {
        body * {
            visibility: hidden;
        }

        #ticket-content,
        #ticket-content * {
            visibility: visible;
        }

        #ticket-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 20px;
        }

        .modal-footer,
        .modal-header .btn-close {
            display: none !important;
        }
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ticketModal = document.getElementById('ticketModal');
        if (!ticketModal) return;

        ticketModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            if (!button) return;

            const reference = button.getAttribute('data-reference');
            const passenger = button.getAttribute('data-passenger');
            const origin = button.getAttribute('data-origin');
            const destination = button.getAttribute('data-destination');
            const date = button.getAttribute('data-date');
            const time = button.getAttribute('data-time');
            const bus = button.getAttribute('data-bus');
            const seats = button.getAttribute('data-seats');
            const price = button.getAttribute('data-price');
            const status = button.getAttribute('data-status');
            const returnOrigin = button.getAttribute('data-return-origin');
            const returnDestination = button.getAttribute('data-return-destination');
            const returnDate = button.getAttribute('data-return-date');
            const returnTime = button.getAttribute('data-return-time');
            const returnBus = button.getAttribute('data-return-bus');
            const returnSeats = button.getAttribute('data-return-seats');

            document.getElementById('modal-reference').textContent = reference;
            document.getElementById('modal-passenger').textContent = passenger;
            document.getElementById('modal-origin').textContent = origin;
            document.getElementById('modal-destination').textContent = destination;
            document.getElementById('modal-date').textContent = date;
            document.getElementById('modal-time').textContent = time;
            document.getElementById('modal-bus').textContent = bus;
            document.getElementById('modal-seats').textContent = seats;
            document.getElementById('modal-price').textContent = price;
            document.getElementById('modal-status').textContent = status;

            const returnBlock = document.getElementById('modal-return-block');
            const hasReturn = !!(returnOrigin && returnDestination && returnDate && returnTime);
            if (returnBlock) {
                if (hasReturn) {
                    document.getElementById('modal-return-origin').textContent = returnOrigin;
                    document.getElementById('modal-return-destination').textContent = returnDestination;
                    document.getElementById('modal-return-date').textContent = returnDate;
                    document.getElementById('modal-return-time').textContent = returnTime;
                    document.getElementById('modal-return-bus').textContent = returnBus || 'N/A';
                    document.getElementById('modal-return-seats').textContent = returnSeats || 'N/A';
                    returnBlock.style.display = '';
                } else {
                    returnBlock.style.display = 'none';
                }
            }

            const qrContainer = document.getElementById('modal-qr-container');
            qrContainer.innerHTML = `<img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=${encodeURIComponent(reference)}&color=1E293B" alt="QR Code" class="img-fluid" style="width: 120px; height: 120px;">`;
        });
    });

    function printTicket() {
        window.print();
    }
</script>
@endpush
@endsection