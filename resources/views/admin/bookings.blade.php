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

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.bookings') }}" class="row g-2 align-items-center">
                <div class="col-12 col-lg">
                    <input type="text" name="q" class="form-control" placeholder="Search bookings (supports multiple terms, e.g. 'BUS-101 2025-12-20 passenger')" value="{{ request('q') }}">
                </div>
                <div class="col-12 col-lg-auto d-grid d-lg-flex gap-2">
                    <button type="submit" class="btn btn-primary fw-bold">
                        <i class="fa-solid fa-magnifying-glass me-2"></i> Search
                    </button>
                    @if(request()->filled('q'))
                    <a href="{{ route('admin.bookings') }}" class="btn btn-outline-secondary fw-bold">
                        Clear
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

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

                    @if($bookings->isEmpty())
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fa-solid fa-ticket fa-3x text-muted mb-3 opacity-50"></i>
                            <p class="text-muted">No bookings found.</p>
                            @if(request()->filled('q'))
                            <div class="mt-2">
                                <a href="{{ route('admin.bookings') }}" class="btn btn-sm btn-outline-secondary fw-bold">Clear search</a>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>

            <div class="mt-3">
                {{ $bookings->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection