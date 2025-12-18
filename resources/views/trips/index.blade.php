@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/trips.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container py-5">

    {{-- HEADER SECTION --}}
    <div class="d-flex justify-content-between align-items-center mb-5 border-bottom pb-3">
        <div>
            {{-- We use $headerTitle passed from the Controller (e.g., "Select Outbound" or "Select Return") --}}
            <h2 class="fw-bold text-dark">{{ $headerTitle ?? 'Select Trip' }}</h2>
            <p class="text-muted mb-0">
                Found {{ $trips->count() }} results for {{ $origin->city }} to {{ $destination->city }}
            </p>
        </div>

        {{-- If we are in Step 2, show Cancel, otherwise New Search --}}
        @if(isset($step) && $step == 2)
        <a href="{{ url('/') }}" class="btn btn-danger btn-sm rounded-0">CANCEL ROUND TRIP</a>
        @else
        <a href="{{ url('/') }}" class="btn btn-outline-dark btn-sm rounded-0">NEW SEARCH</a>
        @endif
    </div>

    {{-- PROGRESS BAR (Only if Round Trip) --}}
    @if(request('trip_type') == 'roundtrip' || (isset($step) && $step == 2))
    <div class="progress mb-4 progress-thin">
        {{-- If Step 1, width 50%. If Step 2, width 100% --}}
        <div class="progress-bar bg-danger" role="progressbar" style="width: {{ (isset($step) && $step == 2) ? '100%' : '50%' }}"></div>
    </div>
    @endif

    {{-- TRIPS LIST --}}
    <div class="mb-5">
        <h5 class="fw-bold text-danger mb-3 d-flex align-items-center">
            <span class="badge bg-danger me-2 rounded-0">{{ $stepLabel ?? '1' }}</span>
            {{ $origin->city }} <i class="fa-solid fa-arrow-right mx-2 text-muted small"></i> {{ $destination->city }}
        </h5>

        <div class="row">
            @forelse($trips as $trip)
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title fw-bold">{{ $trip->bus->name ?? 'Bus' }}</h5>
                        <p class="text-muted mb-2">
                            <i class="fa-regular fa-clock me-1"></i>
                            {{ \Carbon\Carbon::parse($trip->departure_time)->format('h:i A') }}
                        </p>

                        {{-- PRICE DISPLAY --}}
                        <h4 class="text-danger fw-bold mb-3">
                            ₱{{ number_format($trip->price, 2) }}
                        </h4>

                        {{--
                                SELECT BUTTON 
                                We merge the current request parameters so we don't lose the User's search data 
                                (dates, pax count, etc.) when they click a specific bus.
                            --}}
                        <a href="{{ route('trips.seats', array_merge(request()->all(), [
                                'schedule_id' => $trip->id,
                                'leg' => (isset($step) && $step == 1 && request('trip_type') == 'roundtrip') ? 'outbound' : 'return'
                            ])) }}"
                            class="btn btn-dark w-100 rounded-0 text-uppercase fw-bold">
                            Select Seats
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5 bg-light border">
                <i class="fa-solid fa-bus fa-3x text-muted mb-3 opacity-50"></i>
                <p class="text-muted mb-0 fw-bold">No trips found for this date.</p>
                <small>Try selecting a different date or route.</small>
            </div>
            @endforelse
        </div>
    </div>

</div>
@endsection