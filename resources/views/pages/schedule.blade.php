@extends('layouts.app')

@section('content')
<div class="container page-container">
    <div class="page-header">
        <h1 class="page-title">Bus Schedules</h1>
        <p class="page-subtitle">Find and book your next bus trip across Calabarzon</p>
    </div>

    {{-- Horizontal Search Form --}}
    <div class="card card-unified mb-5">
        <div class="card-body">
            <form action="{{ route('trips.search') }}" method="GET" class="row g-3 align-items-end">
                {{-- Hidden defaults for quick search --}}
                <input type="hidden" name="trip_type" value="oneway">
                <input type="hidden" name="adults" value="1">
                <input type="hidden" name="children" value="0">

                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">From</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-location-dot" style="color: #1E293B;"></i></span>
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
                    <button type="submit" class="btn btn-unified btn-unified-md w-100" style="background-color: #FFC107; color: #1E293B; font-weight: 700;">
                        <i class="fa-solid fa-magnifying-glass me-2"></i> Find Tickets
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-unified">
        <div class="table-responsive">
            <table class="table table-hover table-unified">
                <thead>
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
                            <button type="button" class="btn btn-dark btn-unified btn-unified-sm rounded-pill"
                                data-bs-toggle="modal"
                                data-bs-target="#passengerModal"
                                data-schedule-id="{{ $schedule->id }}"
                                data-origin="{{ $schedule->origin_id }}"
                                data-destination="{{ $schedule->destination_id }}"
                                data-date="{{ $schedule->departure_date }}">
                                Book Now
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <i class="fa-solid fa-calendar-xmark empty-state-icon"></i>
                                <p class="empty-state-text">No active schedules found.</p>
                                <a href="{{ route('home') }}" class="btn btn-outline-dark btn-unified btn-unified-sm">Go to Home</a>
                            </div>
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

{{-- Passenger Selection Modal --}}
<div class="modal fade" id="passengerModal" tabindex="-1" aria-labelledby="passengerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="passengerModalLabel">
                    <i class="fa-solid fa-users me-2" style="color: #FFC107;"></i> Select Passengers
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="passengerForm" method="GET">
                <div class="modal-body py-4">
                    <p class="text-muted small mb-4">How many passengers will be travelling?</p>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Adults</label>
                            <select name="adults" id="modal-adults" class="form-select" required>
                                <option value="" selected disabled>Select</option>
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}">{{ $i }} Adult{{ $i > 1 ? 's' : '' }}</option>
                                    @endfor
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Children <span class="text-muted fw-normal">(20% off)</span></label>
                            <select name="children" id="modal-children" class="form-select">
                                <option value="0" selected>0</option>
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}">{{ $i }} Child{{ $i > 1 ? 'ren' : '' }}</option>
                                    @endfor
                            </select>
                        </div>
                    </div>

                    {{-- Hidden fields populated by JS --}}
                    <input type="hidden" name="schedule_id" id="modal-schedule-id">
                    <input type="hidden" name="trip_type" value="oneway">
                    <input type="hidden" name="origin" id="modal-origin">
                    <input type="hidden" name="destination" id="modal-destination">
                    <input type="hidden" name="date" id="modal-date">
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn fw-bold px-4" style="background-color: #FFC107; color: #1E293B;">
                        <i class="fa-solid fa-arrow-right me-1"></i> Continue to Seats
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const passengerModal = document.getElementById('passengerModal');
    passengerModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;

        // Extract data from button attributes
        document.getElementById('modal-schedule-id').value = button.getAttribute('data-schedule-id');
        document.getElementById('modal-origin').value = button.getAttribute('data-origin');
        document.getElementById('modal-destination').value = button.getAttribute('data-destination');
        document.getElementById('modal-date').value = button.getAttribute('data-date');

        // Reset selections
        document.getElementById('modal-adults').value = '';
        document.getElementById('modal-children').value = '0';
    });

    // Set form action to seats route
    document.getElementById('passengerForm').action = '{{ route("trips.seats") }}';
</script>
@endpush
@endsection