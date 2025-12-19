@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/checkout.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container page-container">
    <div class="page-header-left mb-4">
        <h1 class="page-title mb-1">Checkout</h1>
        <p class="page-subtitle mb-0">Confirm passenger details and complete payment.</p>
    </div>
    <div class="row g-5">

        {{-- LEFT COLUMN: Forms --}}
        <div class="col-lg-8">
            <h4 class="section-title">Passenger Details</h4>

            <form action="{{ route('checkout.store') }}" method="POST" id="checkout-form">
                @csrf

                {{-- 1. Contact Info --}}
                <div class="card card-unified mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Full Name</label>
                                <input type="text" name="guest_name" class="form-control" required
                                    value="{{ Auth::check() ? Auth::user()->name : old('guest_name') }}" placeholder="Enter full name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Contact Number</label>
                                <input type="text" name="guest_phone" class="form-control" required
                                    value="{{ Auth::check() ? Auth::user()->contact_number : old('guest_phone') }}" placeholder="09XX XXX XXXX"
                                    maxlength="13" inputmode="tel"
                                    oninput="this.value = this.value.replace(/[^0-9\+\-\s]/g, '').slice(0, 13)">
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
                <h4 class="section-title">Payment Method</h4>
                <div class="card card-unified mb-4">
                    <div class="card-body">

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
                            <div class="d-flex gap-3 mb-4 checkout-payment-tabs">
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
                                    <input type="text" name="card_number" class="form-control" placeholder="0000 0000 0000 0000"
                                        id="card_number" maxlength="19" pattern="\d{4}\s?\d{4}\s?\d{4}\s?\d{4}" inputmode="numeric">
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label text-muted small fw-bold">Expiry</label>
                                        <div class="d-flex gap-2">
                                            <input type="text" name="card_expiry_month" class="form-control" placeholder="MM"
                                                id="card_expiry_month" maxlength="2" pattern="\d{1,2}" inputmode="numeric" style="width: 60px;">
                                            <span class="align-self-center">/</span>
                                            <input type="text" name="card_expiry_year" class="form-control" placeholder="YYYY"
                                                maxlength="4" pattern="\d{4}" inputmode="numeric" style="width: 80px;"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4)">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-muted small fw-bold">CVC</label>
                                        <input type="text" name="card_cvv" class="form-control" placeholder="123"
                                            maxlength="4" pattern="\d{3,4}" inputmode="numeric"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4)">
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
                                    <input type="text" name="ewallet_number" class="form-control" placeholder="09XX XXX XXXX"
                                        maxlength="11" pattern="[0-9]{11}" inputmode="numeric"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)">
                                </div>
                            </div>

                            {{-- Hidden input to store final "payment_method" string for controller if new --}}
                            {{-- We handle this via JS to populate the main 'payment_method' input if "new" is selected --}}
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-dark btn-unified btn-unified-md w-100" id="pay-now-btn">
                    PAY ₱{{ number_format($totalPrice, 2) }} NOW
                </button>
            </form>
        </div>

        {{-- RIGHT COLUMN: Summary --}}
        <div class="col-lg-4">
            <div class="card card-unified card-summary">
                <div class="card-header">
                    <h5 class="card-header-title">Booking Summary</h5>
                </div>
                <div class="card-body">

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

@push('scripts')
<script src="{{ asset('js/checkout.js') }}"></script>
@endpush
@endsection