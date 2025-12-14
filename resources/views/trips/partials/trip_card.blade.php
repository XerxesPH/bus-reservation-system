<div class="col-12 mb-3">
    <div class="card trip-card shadow-sm p-3">
        <div class="row align-items-center">

            <div class="col-md-3 text-center border-end">
                <div class="trip-time">{{ \Carbon\Carbon::parse($trip->departure_time)->format('h:i A') }}</div>
                <span class="badge bg-light text-dark border mt-2">
                    {{ $trip->bus->code }}
                </span>
                <div class="text-muted small mt-1">{{ ucfirst($trip->bus->type) }} Class</div>
            </div>

            <div class="col-md-5 text-center my-3 my-md-0">
                <div class="d-flex justify-content-between text-muted small px-4">
                    <span>{{ $trip->origin->city }}</span>
                    <span>{{ $trip->destination->city }}</span>
                </div>
                <div class="position-relative my-2">
                    <hr class="border-primary opacity-50" style="margin: 10px 0;">
                    <i class="fa-solid fa-bus text-primary position-absolute top-50 start-50 translate-middle bg-white px-2"></i>
                </div>
                <small class="text-muted">Direct • Approx 5h</small>
            </div>

            <div class="col-md-4 text-center text-md-end ps-md-4">
                <div class="mb-2">
                    <span class="text-muted small">Price per seat</span>
                    <h3 class="text-success fw-bold mb-0">₱ {{ number_format($trip->price, 0) }}</h3>
                </div>

                @if($trip->seats_left > 0)
                <a href="{{ route('trips.seats', ['schedule_id' => $trip->id]) }}"
                    class="btn {{ $btnClass ?? 'btn-primary' }} w-100 fw-bold">
                    {{ $btnLabel ?? 'Select Seats' }} <i class="fa-solid fa-chevron-right ms-1"></i>
                </a>

                <div class="text-center mt-2">
                    <small class="{{ $trip->seats_left < 5 ? 'text-danger fw-bold' : 'text-success' }}">
                        {{ $trip->seats_left }} seats remaining
                    </small>
                </div>
                @else
                <button class="btn btn-secondary w-100" disabled>Sold Out</button>
                @endif
            </div>
        </div>
    </div>
</div>