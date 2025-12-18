@extends('layouts.app')

@section('content')

{{--
    LOGIC BLOCK: 
    Determine if this is the First Leg (Outbound) or Final Leg (Return/OneWay).
--}}
@php
// FIX: Use the $leg variable passed from the Controller.
// Do NOT use request('leg') here, as it might mismatch the controller's default.
$isOutbound = ($leg == 'outbound');

// Dynamic Form Action
$formAction = $isOutbound ? route('trips.store_outbound') : route('checkout.prepare');

// Dynamic Button Text
$btnText = $isOutbound ? 'CONFIRM & SELECT RETURN TRIP' : 'PROCEED TO CHECKOUT';
$btnColor = $isOutbound ? 'btn-dark' : 'btn-success';
@endphp

<div class="container page-container" id="seat-selection"
    data-adults="{{ $adults }}"
    data-children="{{ $children }}"
    data-base-price="{{ $trip->price }}">
    <div class="row g-5">

        <div class="col-lg-7">
            <h4 class="section-title">
                {{ $isOutbound ? 'Step 1: Outbound Seats' : 'Select Seats' }}
            </h4>

            <div class="alert alert-info d-flex align-items-center">
                <i class="fa-solid fa-circle-info me-2"></i>
                <div>
                    Please select <strong>{{ $adults + $children }}</strong> seat(s).
                    <small class="d-block text-muted">({{ $adults }} Adults, {{ $children }} Children)</small>
                </div>
            </div>

            <div class="bus-layout shadow-sm p-4 bg-light rounded-4">
                <div class="driver-seat mb-4 text-end border-bottom pb-2">
                    <span class="badge bg-secondary"><i class="fa-solid fa-user-tie me-1"></i> Driver</span>
                </div>

                <div class="row justify-content-center g-2">
                    @php
                    $seats = $trip->bus->capacity;
                    @endphp

                    @for($i = 1; $i <= $seats; $i++)
                        @php $isTaken=in_array($i, $occupiedSeats); @endphp

                        {{-- Aisle Spacer --}}
                        @if(($i-1) % 4==2)
                        <div class="col-1">
                </div>
                @endif

                <div class="col-2 text-center">
                    <button type="button"
                        class="btn w-100 seat-btn fw-bold {{ $isTaken ? 'btn-secondary disabled opacity-50' : 'btn-outline-primary' }}"
                        data-seat="{{ $i }}"
                        {{ $isTaken ? 'disabled' : '' }}
                        onclick="toggleSeat(this)">
                        {{ $i }}
                    </button>
                </div>

                @if($i % 4 == 0)
                <div class="w-100 my-1"></div>
                @endif
                @endfor
            </div>
        </div>

        <div class="d-flex justify-content-center gap-4 mt-4">
            <div class="d-flex align-items-center">
                <div class="btn btn-sm btn-outline-primary disabled seat-btn seat-legend-icon"></div><span class="ms-2 small">Available</span>
            </div>
            <div class="d-flex align-items-center">
                <div class="btn btn-sm btn-warning seat-btn seat-legend-icon"></div><span class="ms-2 small">Selected</span>
            </div>
            <div class="d-flex align-items-center">
                <div class="btn btn-sm btn-secondary seat-btn seat-legend-icon"></div><span class="ms-2 small">Booked</span>
            </div>
        </div>
    </div>

    {{-- SIDEBAR SUMMARY --}}
    <div class="col-lg-5">
        <div class="card card-unified card-summary">
            <div class="card-header {{ $isOutbound ? 'bg-dark' : 'bg-primary' }} text-white py-3">
                <h5 class="mb-0">
                    <i class="fa-solid fa-receipt me-2"></i>
                    {{ $isOutbound ? 'Outbound Summary' : 'Trip Summary' }}
                </h5>
            </div>
            <div class="card-body p-4">
                <h5 class="fw-bold">{{ $trip->origin->city }} <i class="fa-solid fa-arrow-right mx-2 text-muted"></i> {{ $trip->destination->city }}</h5>
                <p class="text-muted border-bottom pb-3">
                    {{ \Carbon\Carbon::parse($trip->departure_date)->format('D, M d, Y') }} at
                    {{ \Carbon\Carbon::parse($trip->departure_time)->format('h:i A') }}
                </p>

                <div class="mb-3 small">
                    <div class="d-flex justify-content-between">
                        <span>Adults ({{ $adults }} x ₱{{ number_format($trip->price, 0) }})</span>
                        <span class="fw-bold">₱{{ number_format($trip->price * $adults, 2) }}</span>
                    </div>
                    @if($children > 0)
                    <div class="d-flex justify-content-between text-success">
                        <span>Children ({{ $children }} x ₱{{ number_format($trip->price * 0.8, 0) }})</span>
                        <span class="fw-bold">₱{{ number_format(($trip->price * 0.8) * $children, 2) }}</span>
                    </div>
                    @endif
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span>Selected Seats:</span>
                    <span class="fw-bold text-primary" id="display-seats">-</span>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <span class="h5 mb-0">Total</span>
                    <span class="h3 fw-bold text-success mb-0">₱ <span id="total-price">0.00</span></span>
                </div>

                {{-- DYNAMIC FORM START --}}
                <form action="{{ $formAction }}" method="POST" class="mt-4">
                    @csrf
                    <input type="hidden" name="schedule_id" value="{{ $trip->id }}">
                    <input type="hidden" name="adults" value="{{ $adults }}">
                    <input type="hidden" name="children" value="{{ $children }}">

                    {{-- CRITICAL: Pass Trip Type (oneway/roundtrip) to enforce correct booking logic --}}
                    <input type="hidden" name="trip_type" value="{{ $searchParams['trip_type'] }}">

                    {{-- Seat Data --}}
                    <input type="hidden" name="selected_seats" id="input-seats" required>

                    @if($isOutbound)
                    {{-- CRITICAL: Pass Return Trip details so we can Redirect to Step 2 --}}

                    {{-- Use request()->input to prevent errors if keys are missing --}}
                    <input type="hidden" name="return_date" value="{{ request()->input('return_date') }}">
                    <input type="hidden" name="return_origin" value="{{ request()->input('return_origin') }}">
                    <input type="hidden" name="return_destination" value="{{ request()->input('return_destination') }}">

                    {{-- Pass original search params to maintain context --}}
                    <input type="hidden" name="original_date" value="{{ request()->input('date') }}">
                    <input type="hidden" name="original_origin" value="{{ request()->input('origin') }}">
                    <input type="hidden" name="original_destination" value="{{ request()->input('destination') }}">
                    @endif

                    <button type="submit" class="btn {{ $btnColor }} btn-unified btn-unified-md w-100" id="checkout-btn" disabled>
                        {{ $btnText }}
                    </button>
                </form>
                {{-- DYNAMIC FORM END --}}

            </div>
        </div>
    </div>
</div>
</div>

@push('scripts')
<script src="{{ asset('js/seats.js') }}"></script>
@endpush
@endsection