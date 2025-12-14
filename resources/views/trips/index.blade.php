@extends('layouts.app')

@section('content')
<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-5 border-bottom pb-3">
        <div>
            <h2 class="fw-bold text-dark">Select Your Trip</h2>
            <p class="text-muted mb-0">
                Found {{ $outboundTrips->count() }} outbound results
            </p>
        </div>
        <a href="{{ url('/') }}" class="btn btn-outline-dark btn-sm rounded-0">
            NEW SEARCH
        </a>
    </div>

    <div class="mb-5">
        <h5 class="fw-bold text-danger mb-3 d-flex align-items-center">
            <span class="badge bg-danger me-2 rounded-0">1</span>
            DEPARTURE: {{ $origin->city }} <i class="fa-solid fa-arrow-right mx-2 text-muted small"></i> {{ $destination->city }}
        </h5>

        <div class="row">
            @forelse($outboundTrips as $trip)
            @include('trips.partials.trip_card', [
            'trip' => $trip,
            'btnLabel' => 'SELECT OUTBOUND',
            'btnClass' => 'btn-danger rounded-0'
            ])
            @empty
            <div class="col-12 text-center py-5 bg-light border">
                <p class="text-muted mb-0">No departure trips found for this date.</p>
            </div>
            @endforelse
        </div>
    </div>

    @if($returnTrips->isNotEmpty() || request('trip_type') == 'roundtrip')
    <div>
        <h5 class="fw-bold text-secondary mb-3 d-flex align-items-center opacity-75">
            <span class="badge bg-secondary me-2 rounded-0">2</span>
            RETURN:
            @if(isset($returnOrigin) && isset($returnDestination))
            {{-- Show the specific terminals user selected for return --}}
            {{ $returnOrigin->city }} <i class="fa-solid fa-arrow-right mx-2 text-muted small"></i> {{ $returnDestination->city }}
            @else
            {{-- Fallback to reverse if no specific return terminals passed --}}
            {{ $destination->city }} <i class="fa-solid fa-arrow-right mx-2 text-muted small"></i> {{ $origin->city }}
            @endif
        </h5>

        <div class="card bg-light border-0 p-5 text-center">
            <div class="text-muted">
                <i class="fa-solid fa-lock mb-3 fa-2x opacity-50"></i>
                <h5 class="fw-bold">Return Trips Locked</h5>
                <p class="mb-0">Please select your <strong>Departure Trip</strong> above to proceed to return schedules.</p>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection