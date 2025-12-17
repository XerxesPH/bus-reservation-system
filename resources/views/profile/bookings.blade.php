@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold">My Bookings</h2>
                <p class="text-muted">View and manage your trip history.</p>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light">
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
                            <td colspan="6" class="text-center py-5 text-muted">
                                <div class="mb-3">
                                    <i class="fa-solid fa-ticket fa-3x opacity-25"></i>
                                </div>
                                <p class="mb-0">No booking history found.</p>
                                <a href="{{ route('home') }}" class="btn btn-primary btn-sm mt-3 fw-bold">Book a Trip</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection