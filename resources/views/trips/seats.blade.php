@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row g-5">

        <div class="col-lg-7">
            <h4 class="mb-4 fw-bold">Select Seats</h4>

            <div class="bus-layout shadow-sm">
                <div class="driver-seat">
                    <span class="badge bg-secondary"><i class="fa-solid fa-user-tie me-1"></i> Driver</span>
                </div>

                <div class="row justify-content-center g-2">
                    @php
                    $seats = $trip->bus->capacity;
                    $cols = 4; // 4 seats per row
                    @endphp

                    @for($i = 1; $i <= $seats; $i++)
                        @php $isTaken=in_array($i, $occupiedSeats); @endphp

                        @if(($i-1) % 4==2)
                        <div class="col-1">
                </div>
                @endif

                <div class="col-2 text-center">
                    <button type="button"
                        class="btn w-100 seat-btn {{ $isTaken ? 'btn-secondary disabled' : 'btn-outline-primary' }}"
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
                <div class="btn btn-sm btn-outline-primary disabled seat-btn" style="width:25px;height:25px;"></div><span class="ms-2 small">Available</span>
            </div>
            <div class="d-flex align-items-center">
                <div class="btn btn-sm btn-warning seat-btn" style="width:25px;height:25px;"></div><span class="ms-2 small">Selected</span>
            </div>
            <div class="d-flex align-items-center">
                <div class="btn btn-sm btn-secondary seat-btn" style="width:25px;height:25px;"></div><span class="ms-2 small">Booked</span>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow border-0 sticky-top" style="top: 100px;">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0"><i class="fa-solid fa-receipt me-2"></i> Trip Summary</h5>
            </div>
            <div class="card-body p-4">
                <h5 class="fw-bold">{{ $trip->origin->city }} <i class="fa-solid fa-arrow-right mx-2 text-muted"></i> {{ $trip->destination->city }}</h5>
                <p class="text-muted border-bottom pb-3">
                    {{ \Carbon\Carbon::parse($trip->departure_date)->format('D, M d') }} at
                    {{ \Carbon\Carbon::parse($trip->departure_time)->format('h:i A') }}
                </p>

                <div class="d-flex justify-content-between mb-2">
                    <span>Selected Seats:</span>
                    <span class="fw-bold text-primary" id="display-seats">-</span>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <span class="h5 mb-0">Total</span>
                    <span class="h3 fw-bold text-success mb-0">₱ <span id="total-price">0.00</span></span>
                </div>

                <form action="{{ url('/book-ticket') }}" method="POST" class="mt-4">
                    @csrf
                    <input type="hidden" name="schedule_id" value="{{ $trip->id }}">
                    <input type="hidden" name="selected_seats" id="input-seats" required>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Passenger Name</label>
                        <input type="text" name="guest_name" class="form-control" required value="{{ Auth::check() ? Auth::user()->name : '' }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Contact Email</label>
                        <input type="email" name="guest_email" class="form-control" required value="{{ Auth::check() ? Auth::user()->email : '' }}">
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-3 fw-bold shadow-sm" id="checkout-btn" disabled>
                        PROCEED TO PAYMENT
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    // (JavaScript logic remains the same as previous step, 
    // just ensure class names match the new HTML structure)
    let selectedSeats = [];
    const pricePerSeat = Number("{{ $trip->price }}");

    function toggleSeat(button) {
        const seatNum = button.getAttribute('data-seat');

        if (selectedSeats.includes(seatNum)) {
            selectedSeats = selectedSeats.filter(s => s !== seatNum);
            button.classList.remove('btn-warning');
            button.classList.add('btn-outline-primary');
        } else {
            selectedSeats.push(seatNum);
            button.classList.remove('btn-outline-primary');
            button.classList.add('btn-warning');
        }
        updateUI();
    }

    function updateUI() {
        const count = selectedSeats.length;
        document.getElementById('display-seats').innerText = count > 0 ? selectedSeats.join(', ') : '-';

        const total = count * pricePerSeat;
        document.getElementById('total-price').innerText = total.toLocaleString('en-US', {
            minimumFractionDigits: 2
        });

        document.getElementById('input-seats').value = JSON.stringify(selectedSeats);

        const btn = document.getElementById('checkout-btn');
        if (count > 0) {
            btn.removeAttribute('disabled');
        } else {
            btn.setAttribute('disabled', 'true');
        }
    }
</script>
@endsection