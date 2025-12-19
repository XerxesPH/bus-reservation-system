@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/trips.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container page-container">

    {{-- HEADER SECTION --}}
    <div class="page-header-left d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            {{-- We use $headerTitle passed from the Controller (e.g., "Select Outbound" or "Select Return") --}}
            <h1 class="page-title mb-1">{{ $headerTitle ?? 'Select Trip' }}</h1>
            <p class="page-subtitle mb-0">
                Found {{ $trips->count() }} results for {{ $origin->city }} to {{ $destination->city }}
            </p>
        </div>

        {{-- If we are in Step 2, show Cancel, otherwise New Search --}}
        <div class="d-flex gap-2">
            @if(isset($step) && $step == 2)
            <a href="{{ url('/') }}" class="btn btn-outline-danger btn-unified btn-unified-sm fw-bold">
                Cancel Round Trip
            </a>
            @else
            <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-unified btn-unified-sm fw-bold">
                New Search
            </a>
            @endif
        </div>
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
        <h5 class="section-title d-flex align-items-center mb-3">
            <span class="badge bg-danger me-2 rounded-pill">{{ $stepLabel ?? '1' }}</span>
            {{ $origin->city }} <i class="fa-solid fa-arrow-right mx-2 text-muted small"></i> {{ $destination->city }}
        </h5>

        <div class="row">
            @forelse($trips as $trip)
            <div class="col-md-6 mb-4">
                <div class="card card-unified h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="fw-bold mb-1">{{ $trip->bus->name ?? 'Bus ' . $trip->bus->code }}</h5>
                                <div class="text-muted small">
                                    <i class="fa-regular fa-clock me-1"></i>
                                    {{ \Carbon\Carbon::parse($trip->departure_time)->format('h:i A') }}
                                    <span class="mx-2">•</span>
                                    <span class="text-uppercase">{{ $trip->bus->type }}</span>
                                </div>
                            </div>
                            <span class="badge bg-light text-dark border">{{ $trip->seats_left ?? '-' }} seats left</span>
                        </div>

                        {{-- PRICE DISPLAY --}}
                        <div class="mt-3 mb-3">
                            <div class="small text-muted">Price per adult</div>
                            <div class="h4 fw-bold text-danger mb-0">₱{{ number_format($trip->price, 2) }}</div>
                        </div>

                        {{--
                                SELECT BUTTON 
                                We merge the current request parameters so we don't lose the User's search data 
                                (dates, pax count, etc.) when they click a specific bus.
                            --}}
                        <a href="{{ route('trips.seats', array_merge(request()->all(), [
                                'schedule_id' => $trip->id,
                                'leg' => (isset($step) && $step == 1 && request('trip_type') == 'roundtrip') ? 'outbound' : 'return'
                            ])) }}"
                            class="btn btn-navy btn-unified btn-unified-md w-100 mt-auto fw-bold">
                            Select Seats
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="empty-state">
                    <i class="fa-solid fa-bus empty-state-icon"></i>
                    <p class="empty-state-text">No trips found for this date.</p>
                    <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-unified btn-unified-sm fw-bold">New Search</a>
                </div>
            </div>
            @endforelse
        </div>
    </div>

</div>
@endsection