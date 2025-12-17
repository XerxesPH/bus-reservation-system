@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">Manage Bookings</h2>
        {{-- Back button not strictly needed with sidebar, but can keep or remove. Removing since sidebar exists. --}}
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Ref ID</th>
                        <th>Passenger</th>
                        <th>Trip Details</th>
                        <th>Seats</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                    <tr>
                        <td class="fw-bold">#{{ $booking->id }}</td>
                        <td>
                            {{ $booking->guest_name }}<br>
                            <small class="text-muted">{{ $booking->guest_email }}</small>
                        </td>
                        <td>
                            <strong>{{ $booking->schedule->origin->city }} ➔ {{ $booking->schedule->destination->city }}</strong><br>
                            <small>
                                {{ \Carbon\Carbon::parse($booking->schedule->departure_date)->format('M d, Y') }} at
                                {{ \Carbon\Carbon::parse($booking->schedule->departure_time)->format('h:i A') }}
                            </small>
                        </td>
                        <td>
                            @foreach($booking->seat_numbers as $seat)
                            <span class="badge bg-secondary">{{ $seat }}</span>
                            @endforeach
                        </td>
                        <td>₱ {{ number_format($booking->total_price) }}</td>
                        <td>
                            @if($booking->status == 'confirmed')
                            <span class="badge bg-success">Confirmed</span>
                            @else
                            <span class="badge bg-danger">Cancelled</span>
                            @endif
                        </td>
                        <td>
                            @if($booking->status == 'confirmed')
                            <form action="{{ route('admin.cancel_booking', $booking->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this?');">
                                @csrf
                                <button class="btn btn-sm btn-outline-danger">Cancel</button>
                            </form>
                            @else
                            <button class="btn btn-sm btn-secondary" disabled>Cancelled</button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-3">
                {{ $bookings->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection