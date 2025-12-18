@extends('layouts.app')

@section('content')
<div class="container page-container">
    <div class="page-header-left">
        <h1 class="page-title">My Bookings</h1>
        <p class="page-subtitle">View and manage your trip history.</p>
    </div>

    <div class="card card-unified">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-unified">
                    <thead>
                        <tr>
                            <th class="ps-4 py-3">Trip Details</th>
                            <th class="py-3">Date & Time</th>
                            <th class="py-3">Seats</th>
                            <th class="py-3">Total</th>
                            <th class="py-3">Status</th>
                            <th class="text-end pe-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold">{{ $booking->schedule->origin->city }} <i class="fa-solid fa-arrow-right mx-1 text-muted small"></i> {{ $booking->schedule->destination->city }}</div>
                                <div class="small text-muted">{{ $booking->schedule->bus->bus_number }} ({{ $booking->schedule->bus->type }})</div>
                                <div class="small text-muted text-uppercase mt-1" style="font-size: 0.75rem;">Ref: {{ $booking->booking_reference ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <div>{{ \Carbon\Carbon::parse($booking->schedule->departure_date)->format('M d, Y') }}</div>
                                <div class="small text-muted">{{ \Carbon\Carbon::parse($booking->schedule->departure_time)->format('h:i A') }}</div>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 150px;" title="{{ is_array($booking->seat_numbers) ? implode(', ', $booking->seat_numbers) : $booking->seat_numbers }}">
                                    {{ is_array($booking->seat_numbers) ? implode(', ', $booking->seat_numbers) : $booking->seat_numbers }}
                                </div>
                            </td>
                            <td class="fw-bold">₱{{ number_format($booking->total_price, 2) }}</td>
                            <td>
                                @if($booking->status == 'confirmed')
                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Confirmed</span>
                                @elseif($booking->status == 'cancelled')
                                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">Cancelled</span>
                                @else
                                <span class="badge bg-secondary px-3 py-2 rounded-pill">{{ ucfirst($booking->status) }}</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @if($booking->status == 'confirmed')
                                {{-- View Ticket Button - Opens Modal --}}
                                <button type="button"
                                    class="btn btn-sm btn-navy fw-bold me-2 view-ticket-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#ticketModal"
                                    data-reference="{{ $booking->booking_reference }}"
                                    data-passenger="{{ $booking->user ? $booking->user->name : $booking->guest_name }}"
                                    data-origin="{{ $booking->schedule->origin->city }}"
                                    data-destination="{{ $booking->schedule->destination->city }}"
                                    data-date="{{ \Carbon\Carbon::parse($booking->schedule->departure_date)->format('M d, Y') }}"
                                    data-time="{{ \Carbon\Carbon::parse($booking->schedule->departure_time)->format('h:i A') }}"
                                    data-bus="{{ $booking->schedule->bus->bus_number }}"
                                    data-seats="{{ is_array($booking->seat_numbers) ? implode(', ', $booking->seat_numbers) : $booking->seat_numbers }}"
                                    data-price="{{ number_format($booking->total_price, 2) }}"
                                    data-status="{{ ucfirst($booking->status) }}"
                                    data-trip-type="{{ $booking->trip_type ?? 'oneway' }}">
                                    <i class="fas fa-ticket me-1"></i> View Ticket
                                </button>
                                @php
                                $deptTime = \Carbon\Carbon::parse($booking->schedule->departure_date . ' ' . $booking->schedule->departure_time);
                                @endphp
                                @if($deptTime->isFuture())
                                <form action="{{ route('profile.bookings.cancel', $booking->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger fw-bold" onclick="return confirm('Are you sure you want to cancel this ticket?')">
                                        Cancel
                                    </button>
                                </form>
                                @else
                                <span class="text-muted small">Completed</span>
                                @endif
                                @else
                                <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="fa-solid fa-ticket empty-state-icon"></i>
                                    <p class="empty-state-text">No booking history found.</p>
                                    <a href="{{ route('home') }}" class="btn btn-amber btn-unified btn-unified-sm">Book a Trip</a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ============================================== --}}
{{-- E-TICKET MODAL --}}
{{-- ============================================== --}}
<div class="modal fade" id="ticketModal" tabindex="-1" aria-labelledby="ticketModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            {{-- Modal Header --}}
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

            {{-- Modal Body --}}
            <div class="modal-body p-4" id="ticket-content">
                <div class="row">
                    {{-- Left Column: Trip Details --}}
                    <div class="col-md-7">
                        {{-- Passenger Name --}}
                        <div class="mb-3">
                            <small class="text-muted text-uppercase" style="font-size: 0.7rem;">Passenger</small>
                            <div class="fw-bold fs-5" id="modal-passenger" style="color: #1E293B;">--</div>
                        </div>

                        {{-- Route --}}
                        <div class="d-flex align-items-center mb-4 p-3 rounded-3" style="background: #F8FAFC;">
                            <div class="text-center flex-grow-1">
                                <small class="text-muted text-uppercase d-block" style="font-size: 0.65rem;">From</small>
                                <div class="fw-bold" id="modal-origin" style="color: #1E293B; font-size: 1.1rem;">--</div>
                            </div>
                            <div class="px-3">
                                <i class="fas fa-plane text-muted" style="transform: rotate(90deg);"></i>
                            </div>
                            <div class="text-center flex-grow-1">
                                <small class="text-muted text-uppercase d-block" style="font-size: 0.65rem;">To</small>
                                <div class="fw-bold" id="modal-destination" style="color: #1E293B; font-size: 1.1rem;">--</div>
                            </div>
                        </div>

                        {{-- Details Grid --}}
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
                    </div>

                    {{-- Right Column: QR Code & Price --}}
                    <div class="col-md-5 text-center border-start ps-4">
                        {{-- Price Badge --}}
                        <div class="mb-3 p-3 rounded-3" style="background: #FFF8E1; border: 2px solid #FFC107;">
                            <small class="text-muted text-uppercase d-block" style="font-size: 0.65rem;">Total Fare</small>
                            <div class="fw-bold" style="font-size: 1.8rem; color: #1E293B;">₱<span id="modal-price">--</span></div>
                        </div>

                        {{-- QR Code --}}
                        <div class="mb-3" id="modal-qr-container">
                            {{-- QR Code will be injected here --}}
                        </div>
                        <div class="small text-muted">Scan for verification</div>

                        {{-- Status Badge --}}
                        <div class="mt-3">
                            <span class="badge bg-success px-3 py-2 rounded-pill" id="modal-status">Confirmed</span>
                        </div>
                    </div>
                </div>

                {{-- Footer Notice --}}
                <div class="mt-4 pt-3 border-top text-center">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Keep this ticket safe. Present to conductor upon boarding.
                    </small>
                </div>
            </div>

            {{-- Modal Footer --}}
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

{{-- Print Styles --}}
<style>
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
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Listen for modal show event
        const ticketModal = document.getElementById('ticketModal');

        ticketModal.addEventListener('show.bs.modal', function(event) {
            // Get the button that triggered the modal
            const button = event.relatedTarget;

            // Extract data from data-* attributes
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
            const tripType = button.getAttribute('data-trip-type');

            // Populate modal fields
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

            // Generate QR Code using an inline SVG placeholder or external library
            // For simplicity, we'll use a QR code API service
            const qrContainer = document.getElementById('modal-qr-container');
            qrContainer.innerHTML = `<img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=${encodeURIComponent(reference)}&color=1E293B" alt="QR Code" class="img-fluid" style="width: 120px; height: 120px;">`;
        });
    });

    // Print function
    function printTicket() {
        window.print();
    }
</script>
@endpush