@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row g-5">

        {{-- LEFT COLUMN: Forms --}}
        <div class="col-lg-8">
            <h4 class="fw-bold mb-4">Passenger Details</h4>

            <form action="{{ route('checkout.store') }}" method="POST" id="checkout-form">
                @csrf

                {{-- 1. Contact Info --}}
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Full Name</label>
                                <input type="text" name="guest_name" class="form-control" required
                                    value="{{ Auth::check() ? Auth::user()->name : old('guest_name') }}" placeholder="Enter full name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Contact Number</label>
                                <input type="text" name="guest_phone" class="form-control" required
                                    value="{{ Auth::check() ? Auth::user()->contact_number : old('guest_phone') }}" placeholder="09XX XXX XXXX">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Email Address</label>
                                <input type="email" name="guest_email" class="form-control" required
                                    value="{{ Auth::check() ? Auth::user()->email : old('guest_email') }}" placeholder="name@example.com">
                                <div class="form-text">We'll send your e-ticket to this email.</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Payment Section --}}
                <h4 class="fw-bold mb-4">Payment Method</h4>
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-body p-4">

                        {{-- AUTH USER: SAVED METHODS --}}
                        @auth
                        @if($paymentMethods->isNotEmpty())
                        <h6 class="fw-bold mb-3">Saved Methods</h6>
                        <div class="mb-4">
                            @foreach($paymentMethods as $method)
                            <div class="form-check mb-2 p-3 border rounded-3 bg-light">
                                <input class="form-check-input mt-2" type="radio" name="payment_method" id="saved_{{ $method->id }}" value="saved_{{ $method->id }}" checked>
                                <label class="form-check-label w-100" for="saved_{{ $method->id }}">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            @if($method->type == 'card')
                                            <i class="fa-brands fa-cc-{{ strtolower($method->provider) }} fa-lg me-2 text-primary"></i>
                                            <strong>{{ $method->provider }}</strong>
                                            <span class="text-muted ms-1">**** {{ substr($method->account_number, -4) }}</span>
                                            @else
                                            <i class="fa-solid fa-wallet fa-lg me-2 text-success"></i>
                                            <strong>{{ $method->provider }}</strong>
                                            <span class="text-muted ms-1">{{ $method->account_number }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            </div>
                            @endforeach

                            {{-- Option to use New Method --}}
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="use_new_method" value="new">
                                <label class="form-check-label fw-bold" for="use_new_method">
                                    Use a different payment method
                                </label>
                            </div>
                        </div>
                        @endif
                        @endauth

                        {{-- NEW PAYMENT METHOD FORM --}}
                        <div id="new-method-section" class="{{ (Auth::check() && $paymentMethods->isNotEmpty()) ? 'd-none' : '' }}">
                            @if(Auth::check() && $paymentMethods->isNotEmpty())
                            <hr class="my-4">
                            @endif

                            <h6 class="fw-bold mb-3">Select Payment Option</h6>

                            {{-- Tabs/Radio for Type --}}
                            <div class="d-flex gap-3 mb-4">
                                <div class="form-check ps-0">
                                    <input type="radio" class="btn-check" name="new_payment_type" id="type_card" value="card" checked autocomplete="off">
                                    <label class="btn btn-outline-primary px-4 py-2 rounded-3 fw-bold" for="type_card">
                                        <i class="fa-regular fa-credit-card me-2"></i> Credit/Debit Card
                                    </label>
                                </div>
                                <div class="form-check ps-0">
                                    <input type="radio" class="btn-check" name="new_payment_type" id="type_ewallet" value="ewallet" autocomplete="off">
                                    <label class="btn btn-outline-success px-4 py-2 rounded-3 fw-bold" for="type_ewallet">
                                        <i class="fa-solid fa-wallet me-2"></i> GCash / E-Wallet
                                    </label>
                                </div>
                            </div>

                            {{-- Card Fields --}}
                            <div id="card-fields">
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">Card Number</label>
                                    <input type="text" class="form-control" placeholder="0000 0000 0000 0000">
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label text-muted small fw-bold">Expiry</label>
                                        <input type="text" class="form-control" placeholder="MM/YY">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-muted small fw-bold">CVC</label>
                                        <input type="text" class="form-control" placeholder="123">
                                    </div>
                                </div>
                            </div>

                            {{-- E-Wallet Fields --}}
                            <div id="ewallet-fields" class="d-none">
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">Select Provider</label>
                                    <select class="form-select">
                                        <option>GCash</option>
                                        <option>Maya</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">Mobile Number</label>
                                    <input type="text" class="form-control" placeholder="09XX XXX XXXX">
                                </div>
                            </div>

                            {{-- Hidden input to store final "payment_method" string for controller if new --}}
                            {{-- We handle this via JS to populate the main 'payment_method' input if "new" is selected --}}
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-dark w-100 py-3 fw-bold shadow-lg" id="pay-now-btn">
                    PAY ₱{{ number_format($totalPrice, 2) }} NOW
                </button>
            </form>
        </div>

        {{-- RIGHT COLUMN: Summary --}}
        <div class="col-lg-4">
            <div class="card shadow border-0 rounded-4 sticky-top" style="top: 100px;">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Booking Summary</h5>
                </div>
                <div class="card-body p-4">

                    {{-- OUTBOUND --}}
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-primary text-uppercase">Outbound</span>
                            <span class="small text-muted fw-bold">
                                {{ \Carbon\Carbon::parse($outboundSchedule->departure_date)->format('M d') }}
                            </span>
                        </div>
                        <h6 class="fw-bold mb-1">
                            {{ $outboundSchedule->origin->city }} <i class="fa-solid fa-arrow-right mx-1 text-muted small"></i> {{ $outboundSchedule->destination->city }}
                        </h6>
                        <p class="small text-muted mb-1">
                            {{ \Carbon\Carbon::parse($outboundSchedule->departure_time)->format('h:i A') }} • {{ $outboundSchedule->bus->name }}
                        </p>
                        <div class="small">
                            Seats: <strong>{{ implode(', ', $data['outbound']['seats']) }}</strong>
                        </div>
                        <div class="text-end fw-bold mt-1">₱{{ number_format($outboundPrice, 2) }}</div>
                    </div>

                    {{-- RETURN (If Round Trip) --}}
                    @if($returnSchedule)
                    <hr class="border-dashed">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-danger text-uppercase">Return</span>
                            <span class="small text-muted fw-bold">
                                {{ \Carbon\Carbon::parse($returnSchedule->departure_date)->format('M d') }}
                            </span>
                        </div>
                        <h6 class="fw-bold mb-1">
                            {{ $returnSchedule->origin->city }} <i class="fa-solid fa-arrow-right mx-1 text-muted small"></i> {{ $returnSchedule->destination->city }}
                        </h6>
                        <p class="small text-muted mb-1">
                            {{ \Carbon\Carbon::parse($returnSchedule->departure_time)->format('h:i A') }} • {{ $returnSchedule->bus->name }}
                        </p>
                        <div class="small">
                            Seats: <strong>{{ implode(', ', $data['return']['seats']) }}</strong>
                        </div>
                        <div class="text-end fw-bold mt-1">₱{{ number_format($returnPrice, 2) }}</div>
                    </div>
                    @endif

                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="h5 mb-0">Total Amount</span>
                        <span class="h3 fw-bold text-success mb-0">₱{{ number_format($totalPrice, 2) }}</span>
                    </div>

                    <div class="alert alert-light border small text-muted">
                        <i class="fa-solid fa-lock me-1"></i> Secure Payment by Southern Lines
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const useNewMethodRadio = document.getElementById('use_new_method');
        const savedMethodRadios = document.querySelectorAll('input[name="payment_method"][id^="saved_"]');
        const newMethodSection = document.getElementById('new-method-section');

        // Toggle New Method Section
        function toggleNewMethod(show) {
            if (show) {
                newMethodSection.classList.remove('d-none');
                newMethodSection.classList.add('animate__animated', 'animate__fadeIn');
            } else {
                newMethodSection.classList.add('d-none');
            }
        }

        if (useNewMethodRadio) {
            useNewMethodRadio.addEventListener('change', function() {
                if (this.checked) toggleNewMethod(true);
            });
        }

        savedMethodRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) toggleNewMethod(false);
            });
        });

        // Toggle Card vs E-Wallet fields
        const typeCard = document.getElementById('type_card');
        const typeWallet = document.getElementById('type_ewallet');
        const cardFields = document.getElementById('card-fields');
        const walletFields = document.getElementById('ewallet-fields');
        const form = document.getElementById('checkout-form');

        function toggleFields() {
            if (typeCard.checked) {
                cardFields.classList.remove('d-none');
                walletFields.classList.add('d-none');
            } else {
                cardFields.classList.add('d-none');
                walletFields.classList.remove('d-none');
            }
        }

        typeCard.addEventListener('change', toggleFields);
        typeWallet.addEventListener('change', toggleFields);

        // Intercept Form Submit to handle "New Method" value
        form.addEventListener('submit', function(e) {
            // If "Use New Method" is checked OR no saved methods exist (Guest)
            // We need to set the payment_method value to something meaningful like "credit_card" or "gcash"
            // instead of just "new".

            const isNewSelected = useNewMethodRadio ? useNewMethodRadio.checked : true;

            if (isNewSelected) {
                // Determine if Card or Wallet
                const type = typeCard.checked ? 'Card' : 'E-Wallet';
                // We create a hidden input to override the "new" value, or just update the radio value dynamically?
                // Easier: Append a hidden input with the specific type

                // If the user selected "new", the radio value sent is "new". 
                // But the controller needs the type.

                // Actually, let's just inject the specific string into the request by manipulating the form data?
                // Or better, change the value of the radio button itself before submit? 
                // No, radio groups are tricky.

                // Solution: Add a hidden input named 'payment_method' ONLY if we are in "new" mode, 
                // and disable the radio buttons so they don't submit.

                if (useNewMethodRadio) {
                    // Disable the "new" radio so it doesn't send "new"
                    useNewMethodRadio.disabled = true;
                }

                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'payment_method';
                hiddenInput.value = typeCard.checked ? 'Credit Card' : 'GCash';
                form.appendChild(hiddenInput);
            }
        });
    });
</script>
@endsection