@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="mb-5">
        <h2 class="fw-bold text-center mb-4">Bus Schedules</h2>

        {{-- Horizontal Search Form --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('trips.search') }}" method="GET" class="row g-3 align-items-end">
                    {{-- Hidden defaults for quick search --}}
                    <input type="hidden" name="trip_type" value="oneway">
                    <input type="hidden" name="adults" value="1">
                    <input type="hidden" name="children" value="0">

                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">From</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-location-dot text-primary"></i></span>
                            <select name="origin" class="form-select border-start-0 ps-0" required>
                                <option value="" selected disabled>Select Origin</option>
                                @foreach($terminals as $t)
                                <option value="{{ $t->id }}">{{ $t->city }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">To</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-location-crosshairs text-danger"></i></span>
                            <select name="destination" class="form-select border-start-0 ps-0" required>
                                <option value="" selected disabled>Select Destination</option>
                                @foreach($terminals as $t)
                                <option value="{{ $t->id }}">{{ $t->city }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Date</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fa-regular fa-calendar text-muted"></i></span>
                            <input type="date" name="date" class="form-control border-start-0 ps-0" required min="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2">
                            <i class="fa-solid fa-magnifying-glass me-2"></i> Find Tickets
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3">Date & Time</th>
                        <th class="py-3">Route</th>
                        <th class="py-3">Bus</th>
                        <th class="py-3">Price</th>
                        <th class="py-3 text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $schedule)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold">{{ \Carbon\Carbon::parse($schedule->departure_date)->format('M d, Y') }}</div>
                            <div class="text-muted small">{{ \Carbon\Carbon::parse($schedule->departure_time)->format('h:i A') }}</div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="fw-bold text-dark">{{ $schedule->origin->city }}</span>
                                <i class="fa-solid fa-arrow-right mx-2 text-muted small"></i>
                                <span class="fw-bold text-dark">{{ $schedule->destination->city }}</span>
                            </div>
                        </td>
                        <td>
                            <div>{{ $schedule->bus->name ?? 'Bus ' . $schedule->bus->code }}</div>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border">{{ ucfirst($schedule->bus->type) }}</span>
                        </td>
                        <td class="fw-bold text-danger">
                            ₱{{ number_format($schedule->price, 2) }}
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('trips.seats', [
                                'schedule_id' => $schedule->id,
                                'trip_type' => 'oneway',
                                'origin' => $schedule->origin_id,
                                'destination' => $schedule->destination_id,
                                'date' => $schedule->departure_date,
                                'adults' => 1,
                                'children' => 0
                            ]) }}" class="btn btn-sm btn-dark rounded-pill px-3">
                                Book Now
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <i class="fa-solid fa-calendar-xmark fa-3x text-muted mb-3 opacity-50"></i>
                            <p class="text-muted fw-bold">No active schedules found.</p>
                            <a href="{{ route('home') }}" class="btn btn-outline-dark btn-sm">Go to Home</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($schedules->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $schedules->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>
@endsection