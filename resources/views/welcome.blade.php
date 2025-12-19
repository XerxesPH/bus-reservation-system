@extends('layouts.app')

@push('styles')
{{-- Google Fonts for Cursive --}}
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital@1&display=swap" rel="stylesheet">
{{-- Leaflet CSS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
<style>
    /* ===== BRAND COLORS ===== */
    :root {
        --navy: #1E293B;
        --navy-light: #334155;
        --amber: #FFC107;
        --amber-hover: #FFB300;
    }

    /* ===== HERO SECTION ===== */
    .hero-section {
        min-height: 90vh;
        padding-top: 100px;
        position: relative;
        overflow: hidden;
    }

    .hero-tagline {
        color: var(--navy);
        font-weight: 600;
        font-size: 0.875rem;
        letter-spacing: 2px;
        text-transform: uppercase;
    }

    .hero-title {
        font-size: 3.5rem;
        font-weight: 700;
        color: var(--navy);
        line-height: 1.2;
    }

    .hero-title .cursive {
        font-family: 'Playfair Display', serif;
        font-style: italic;
        color: var(--amber);
    }

    .hero-text {
        color: #64748B;
        font-size: 1.1rem;
        line-height: 1.8;
    }

    .btn-cta {
        background-color: var(--amber);
        color: var(--navy);
        font-weight: 700;
        padding: 14px 32px;
        border-radius: 8px;
        border: none;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .btn-cta:hover {
        background-color: var(--amber-hover);
        color: var(--navy);
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(255, 193, 7, 0.3);
    }

    .hero-image-wrapper {
        position: relative;
    }

    .hero-blob {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 90%;
        height: 90%;
        background: linear-gradient(135deg, #FFC107 0%, #FFE082 100%);
        border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
        z-index: 0;
        animation: blob-morph 8s ease-in-out infinite;
    }

    @keyframes blob-morph {

        0%,
        100% {
            border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
        }

        50% {
            border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%;
        }
    }

    .hero-img {
        position: relative;
        z-index: 1;
        max-height: 500px;
        object-fit: contain;
    }

    /* ===== BOOKING WIDGET ===== */
    .booking-widget {
        background: white;
        border-radius: 20px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        padding: 2.5rem;
        margin-top: -80px;
        position: relative;
        z-index: 10;
    }

    .booking-widget .form-label {
        font-weight: 600;
        color: var(--navy);
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 0.5rem;
    }

    .booking-widget .form-control,
    .booking-widget .form-select {
        border: 2px solid #E2E8F0;
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 0.95rem;
        transition: all 0.2s ease;
    }

    .booking-widget .form-control:focus,
    .booking-widget .form-select:focus {
        border-color: var(--amber);
        box-shadow: 0 0 0 4px rgba(255, 193, 7, 0.15);
    }

    .trip-type-btn {
        padding: 10px 24px;
        border: 2px solid #E2E8F0;
        background: white;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        color: #64748B;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .trip-type-btn.active {
        border-color: var(--navy);
        background: var(--navy);
        color: white;
    }

    .booking-widget .passenger-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        background: white;
        color: #0F172A;
    }

    .booking-widget .passenger-trigger .passenger-subtext {
        color: #64748B;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .btn-find-tickets {
        background: var(--amber);
        color: var(--navy);
        font-weight: 700;
        padding: 16px 32px;
        border-radius: 10px;
        border: none;
        font-size: 1rem;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }

    .btn-find-tickets:hover {
        background: var(--amber-hover);
        color: var(--navy);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(255, 193, 7, 0.4);
    }

    /* ===== SECTION STYLES ===== */
    .section-padding {
        padding: 100px 0;
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--navy);
        margin-bottom: 1rem;
    }

    .section-subtitle {
        color: #64748B;
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto;
    }

    /* ===== TERMINALS & MAP ===== */
    #map {
        height: 450px;
        width: 100%;
        border-radius: 16px;
        z-index: 1;
    }

    .terminal-list {
        max-height: 450px;
        overflow-y: auto;
    }

    .terminal-item {
        cursor: pointer;
        transition: all 0.2s ease;
        border-left: 4px solid transparent;
        padding: 1rem 1.25rem;
    }

    .terminal-item:hover {
        background-color: #FFF8E1;
        border-left-color: var(--amber);
    }

    .terminal-item.active {
        background-color: #FFF8E1;
        border-left-color: var(--amber);
    }

    .route-info {
        display: none;
    }

    .route-info.show {
        display: block;
    }

    .leaflet-routing-container {
        display: none;
    }

    /* ===== SERVICES ===== */
    .service-card {
        background: white;
        border-radius: 16px;
        padding: 2.5rem 2rem;
        text-align: center;
        transition: all 0.3s ease;
        border: 1px solid #E2E8F0;
    }

    .service-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .service-icon {
        width: 70px;
        height: 70px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 1.75rem;
    }

    .service-icon.navy {
        background: rgba(30, 41, 59, 0.1);
        color: var(--navy);
    }

    .service-icon.amber {
        background: rgba(255, 193, 7, 0.2);
        color: #B7791F;
    }

    .service-title {
        font-weight: 700;
        color: var(--navy);
        margin-bottom: 0.75rem;
    }

    /* ===== DESTINATIONS ===== */
    .destination-card {
        border-radius: 16px;
        overflow: hidden;
        background: white;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .destination-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .destination-card img {
        height: 200px;
        width: 100%;
        object-fit: cover;
    }

    .destination-card .card-body {
        padding: 1.25rem;
    }

    .destination-card .card-title {
        font-weight: 700;
        color: var(--navy);
        margin-bottom: 0;
    }

    /* ===== STEPS SECTION ===== */
    .steps-section {
        background: linear-gradient(135deg, #F8FAFC 0%, #EEF2FF 100%);
    }

    .step-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 2rem;
    }

    .step-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: white;
        flex-shrink: 0;
        margin-right: 1.25rem;
    }

    .step-icon.amber {
        background: var(--amber);
        color: var(--navy);
    }

    .step-icon.navy {
        background: var(--navy);
    }

    .step-icon.success {
        background: #10B981;
    }

    .step-title {
        font-weight: 700;
        color: var(--navy);
        margin-bottom: 0.5rem;
    }

    .step-text {
        color: #64748B;
        font-size: 0.95rem;
        line-height: 1.7;
    }

    .phone-mockup {
        background: white;
        border-radius: 24px;
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
        padding: 1rem;
        transform: rotate(5deg);
        max-width: 280px;
        margin: 0 auto;
    }

    .phone-mockup img {
        border-radius: 16px;
        width: 100%;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 992px) {
        .hero-title {
            font-size: 2.5rem;
        }

        .booking-widget {
            margin-top: 2rem;
            padding: 1.5rem;
        }

        .section-title {
            font-size: 2rem;
        }
    }

    @media (max-width: 768px) {
        .hero-section {
            min-height: auto;
            padding-top: 80px;
            padding-bottom: 40px;
        }

        .hero-title {
            font-size: 2rem;
        }

        .hero-blob {
            display: none;
        }
    }
</style>
@endpush

@section('content')

{{-- Error Alert --}}
@if(session('error'))
<div class="position-fixed start-50 translate-middle-x" style="z-index: 1050; top: 100px; width: 90%; max-width: 600px;">
    <div class="alert alert-danger alert-dismissible fade show shadow-lg border-0 rounded-3" role="alert">
        <div class="d-flex align-items-center">
            <i class="fa-solid fa-circle-exclamation fa-lg me-3"></i>
            <div><strong>Oops!</strong> {{ session('error') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
@endif

{{-- ===== 1. HERO SECTION ===== --}}
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-5">
            {{-- Left: Text --}}
            <div class="col-lg-6">
                <p class="hero-tagline mb-3">
                    <i class="fa-solid fa-bus me-2"></i> Best Bus Travel in Calabarzon
                </p>

                <h1 class="hero-title mb-4">
                    Travel, <span class="cursive">enjoy</span><br>
                    and Have a Great<br>
                    Experience with us
                </h1>

                <p class="hero-text mb-4">
                    Travel smarter, not harder. We offer auto-ticketing so you get your e-ticket
                    right after payment. Plus, with Safe Travel protocols, sanitized buses & GPS
                    tracking, your journey is always safe and comfortable.
                </p>

                <a href="#book-section" class="btn btn-cta">
                    <i class="fa-solid fa-ticket me-2"></i> Book Now
                </a>
            </div>

            {{-- Right: Image with Blob --}}
            <div class="col-lg-6">
                <div class="hero-image-wrapper text-center">
                    <div class="hero-blob"></div>
                    <img src="{{ asset('images/traveler.png') }}" alt="Happy Traveler" class="hero-img img-fluid">
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===== 2. BOOKING WIDGET ===== --}}
<section id="book-section" class="pb-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="booking-widget">
                    <form id="welcomeBookingForm" action="{{ route('trips.search') }}" method="GET">
                        <div class="row g-4">
                            {{-- Origin --}}
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="fa-solid fa-location-dot me-1"></i> From
                                </label>
                                <select name="origin" class="form-select" required>
                                    <option value="" selected disabled>Select Origin</option>
                                    @foreach($terminals as $t)
                                    <option value="{{ $t->id }}">{{ $t->city }} - {{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Destination --}}
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="fa-solid fa-location-crosshairs me-1"></i> To
                                </label>
                                <select name="destination" class="form-select" required>
                                    <option value="" selected disabled>Select Destination</option>
                                    @foreach($terminals as $t)
                                    <option value="{{ $t->id }}">{{ $t->city }} - {{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Departure Date --}}
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="fa-regular fa-calendar me-1"></i> Departure
                                </label>
                                <input type="date" name="date" class="form-control" required min="{{ date('Y-m-d') }}">
                            </div>

                            <input type="hidden" name="adults" id="welcome-adults" value="1">
                            <input type="hidden" name="children" id="welcome-children" value="0">
                            <input type="hidden" name="whole_bus" id="welcome-whole-bus" value="0">
                        </div>

                        {{-- Trip Type & Return Fields --}}
                        <div class="row g-4 mt-2">
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                    <span class="form-label mb-0">Trip Type:</span>
                                    <div class="d-flex gap-2">
                                        <label class="trip-type-btn active" id="onewayBtn">
                                            <input type="radio" name="trip_type" value="oneway" checked class="visually-hidden" onclick="toggleLayout(false)">
                                            One Way
                                        </label>
                                        <label class="trip-type-btn" id="roundtripBtn">
                                            <input type="radio" name="trip_type" value="roundtrip" class="visually-hidden" onclick="toggleLayout(true)">
                                            Round Trip
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Return Fields (Hidden by default) --}}
                        <div class="row g-4 mt-2 d-none" id="returnRow">
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="fa-solid fa-rotate-left me-1"></i> Return From
                                </label>
                                <select name="return_origin" class="form-select">
                                    <option value="" selected disabled>Select Terminal</option>
                                    @foreach($terminals as $t)
                                    <option value="{{ $t->id }}">{{ $t->city }} - {{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="fa-solid fa-rotate-left me-1"></i> Return To
                                </label>
                                <select name="return_destination" class="form-select">
                                    <option value="" selected disabled>Select Terminal</option>
                                    @foreach($terminals as $t)
                                    <option value="{{ $t->id }}">{{ $t->city }} - {{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="fa-regular fa-calendar me-1"></i> Return Date
                                </label>
                                <input type="date" name="return_date" class="form-control" min="{{ date('Y-m-d') }}">
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="button" class="btn btn-find-tickets w-100" id="welcomeOpenPassengerModalBtn">
                                    <i class="fa-solid fa-magnifying-glass me-2"></i> Find Tickets
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="welcomePassengerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-users me-2" style="color: var(--amber);"></i> Select Passengers
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="welcomePassengerModalForm">
                <div class="modal-body py-4">
                    <p class="text-muted small mb-4">How many passengers will be travelling?</p>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Adults</label>
                            <select id="welcome-modal-adults" class="form-select" required>
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ $i == 1 ? 'selected' : '' }}>{{ $i }} Adult{{ $i > 1 ? 's' : '' }}</option>
                                    @endfor
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">Children <span class="text-muted fw-normal">(20% off)</span></label>
                            <select id="welcome-modal-children" class="form-select">
                                @for($i = 0; $i <= 5; $i++)
                                    <option value="{{ $i }}" {{ $i == 0 ? 'selected' : '' }}>{{ $i }} Child{{ $i != 1 ? 'ren' : '' }}</option>
                                    @endfor
                            </select>
                        </div>
                    </div>

                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" value="1" id="welcome-whole-bus-toggle">
                        <label class="form-check-label fw-bold" for="welcome-whole-bus-toggle">Book the whole bus</label>
                        <div class="text-muted small">Only shows buses that are fully available.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn fw-bold px-4" style="background-color: var(--amber); color: var(--navy);">
                        Continue
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== 3. TERMINALS & MAP ===== --}}
<section class="section-padding bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Our Terminals</h2>
            <p class="section-subtitle">Click a terminal to view its location on the map</p>
        </div>

        <div class="row justify-content-center g-4">
            {{-- Map --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div id="map"></div>
                </div>
            </div>

            {{-- Terminal List --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-lg rounded-4 h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="mb-0 fw-bold" style="color: var(--navy);">
                            <i class="fas fa-map-marker-alt me-2" style="color: var(--amber);"></i> All Terminals
                        </h6>
                    </div>
                    <div class="card-body p-0 terminal-list">
                        @foreach($terminals as $terminal)
                        <div class="terminal-item border-bottom"
                            data-terminal-id="{{ $terminal->id }}"
                            data-lat="{{ $terminal->latitude }}"
                            data-lng="{{ $terminal->longitude }}"
                            data-name="{{ $terminal->name }}"
                            data-city="{{ $terminal->city }}"
                            data-province="{{ $terminal->province }}"
                            data-type="{{ $terminal->type }}">
                            <div class="d-flex align-items-center">
                                <div class="me-3" style="width: 40px; height: 40px; background: rgba(30,41,59,0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-bus" style="color: var(--navy);"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold" style="color: var(--navy);">{{ $terminal->name }}</div>
                                    <small class="text-muted">{{ $terminal->city }}</small>
                                </div>
                                <i class="fas fa-chevron-right text-muted"></i>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===== 4. SERVICES ===== --}}
<section class="section-padding">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">We Offer Best Services</h2>
            <p class="section-subtitle">Experience hassle-free travel with our top-notch amenities and support</p>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="service-card h-100">
                    <div class="service-icon amber">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h5 class="service-title">Safe & Sanitized</h5>
                    <p class="text-muted mb-0">All our buses undergo thorough sanitization and are tracked via GPS, providing you with peace of mind throughout your journey in Calabarzon.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card h-100">
                    <div class="service-icon amber">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <h5 class="service-title">Instant Booking</h5>
                    <p class="text-muted mb-0">Our system offers instant booking, ensuring you receive your digital e-ticket immediately after payment, allowing you to secure your seat in seconds.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card h-100">
                    <div class="service-icon amber">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <h5 class="service-title">24/7 Support</h5>
                    <p class="text-muted mb-0">Our dedicated support team is ready to help you anytime, anywhere, ensuring a smooth and worry-free experience from booking to arrival.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===== 5. TOP DESTINATIONS ===== --}}
<section class="section-padding bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Top Destinations in Calabarzon</h2>
            <p class="section-subtitle">Explore the most beautiful places in the region</p>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="destination-card">
                    <img src="{{ asset('images/taal.png') }}" alt="Taal Volcano">
                    <div class="card-body text-center">
                        <h5 class="card-title">Taal Volcano</h5>
                        <p class="text-muted small mb-0">Batangas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="destination-card">
                    <img src="{{ asset('images/laguna.png') }}" alt="Pagsanjan Falls">
                    <div class="card-body text-center">
                        <h5 class="card-title">Pagsanjan Falls</h5>
                        <p class="text-muted small mb-0">Laguna</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="destination-card">
                    <img src="{{ asset('images/cavite.png') }}" alt="Tagaytay City">
                    <div class="card-body text-center">
                        <h5 class="card-title">Tagaytay City</h5>
                        <p class="text-muted small mb-0">Cavite</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===== 6. STEPS SECTION ===== --}}
<section class="section-padding steps-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <h2 class="section-title mb-5">Book Your Next Trip<br>In 3 Easy Steps</h2>

                <div class="step-item">
                    <div class="step-icon amber">
                        <i class="fa-regular fa-map"></i>
                    </div>
                    <div>
                        <h5 class="step-title">Select Your Trip</h5>
                        <p class="step-text">Choose your origin and destination within the Calabarzon region, then pick your travel date and preferred time.</p>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-icon navy">
                        <i class="fa-solid fa-chair"></i>
                    </div>
                    <div>
                        <h5 class="step-title">Secure Your Seat</h5>
                        <p class="step-text">Select your seat on the bus layout and complete the payment. You'll receive your e-ticket immediately after confirmation.</p>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-icon success">
                        <i class="fa-solid fa-bus-simple"></i>
                    </div>
                    <div>
                        <h5 class="step-title">Hop on & Travel Safely</h5>
                        <p class="step-text">Present your e-ticket at the terminal. Enjoy your safe, sanitized, and GPS-tracked ride to your destination.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 text-center">
                <div class="phone-mockup">
                    <img src="https://placehold.co/260x500/1E293B/FFC107?text=Southern+Lines+App" alt="Mobile App Preview">
                </div>
            </div>
        </div>
    </div>
</section>

</div> {{-- END OF MAIN CONTENT WRAPPER --}}

{{-- 7. JAVASCRIPT LOGIC --}}
@push('scripts')
<script src="{{ asset('js/welcome.js') }}"></script>

{{-- Leaflet JS --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>

<script>
    const terminals = @json($terminals);
    let map, markers = [],
        routingControl = null;

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize map centered on Calabarzon
        map = L.map('map').setView([14.1407, 121.4692], 9);

        // Add OpenStreetMap tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        // Add markers for each terminal
        const bounds = L.latLngBounds();
        terminals.forEach(terminal => {
            if (terminal.latitude && terminal.longitude) {
                const lat = parseFloat(terminal.latitude);
                const lng = parseFloat(terminal.longitude);

                const marker = L.marker([lat, lng]).addTo(map);
                marker.bindPopup(`
                    <div style="min-width:150px;">
                        <strong>${terminal.name}</strong><br>
                        <small class="text-muted">${terminal.city}, ${terminal.province || ''}</small>
                    </div>
                `);
                marker.terminalData = terminal;
                markers.push(marker);
                bounds.extend([lat, lng]);
            }
        });

        // Fit map to show all markers
        if (markers.length > 0) {
            map.fitBounds(bounds, {
                padding: [30, 30]
            });
        }

        // Terminal list click handler
        document.querySelectorAll('.terminal-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.terminal-item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');

                const lat = parseFloat(this.dataset.lat);
                const lng = parseFloat(this.dataset.lng);
                const terminalId = parseInt(this.dataset.terminalId);

                map.setView([lat, lng], 14);

                // Open popup for clicked terminal
                const marker = markers.find(m => m.terminalData.id === terminalId);
                if (marker) marker.openPopup();
            });
        });

        // Show Route button
        document.getElementById('showRouteBtn').addEventListener('click', function() {
            const origin = document.getElementById('originSelect');
            const dest = document.getElementById('destinationSelect');

            if (!origin.value || !dest.value) {
                alert('Please select both terminals.');
                return;
            }
            if (origin.value === dest.value) {
                alert('Origin and destination cannot be the same.');
                return;
            }

            const oOpt = origin.options[origin.selectedIndex];
            const dOpt = dest.options[dest.selectedIndex];

            const oLat = parseFloat(oOpt.dataset.lat);
            const oLng = parseFloat(oOpt.dataset.lng);
            const dLat = parseFloat(dOpt.dataset.lat);
            const dLng = parseFloat(dOpt.dataset.lng);

            // Remove existing route
            if (routingControl) {
                map.removeControl(routingControl);
            }

            // Add new route
            routingControl = L.Routing.control({
                waypoints: [
                    L.latLng(oLat, oLng),
                    L.latLng(dLat, dLng)
                ],
                routeWhileDragging: false,
                showAlternatives: false,
                fitSelectedRoutes: true,
                lineOptions: {
                    styles: [{
                        color: '#0d6efd',
                        weight: 5,
                        opacity: 0.8
                    }]
                },
                createMarker: function() {
                    return null;
                } // Use existing markers
            }).addTo(map);

            // Listen for route found event
            routingControl.on('routesfound', function(e) {
                const route = e.routes[0];
                const distance = (route.summary.totalDistance / 1000).toFixed(1) + ' km';
                const duration = Math.round(route.summary.totalTime / 60) + ' mins';

                document.getElementById('routeDistance').textContent = distance;
                document.getElementById('routeDuration').textContent = duration;
                document.getElementById('routeSummary').textContent = route.name || 'Via main road';
                document.getElementById('routeInfo').classList.add('show');
            });
        });

        // Clear Route button
        document.getElementById('clearRouteBtn').addEventListener('click', function() {
            if (routingControl) {
                map.removeControl(routingControl);
                routingControl = null;
            }
            document.getElementById('routeInfo').classList.remove('show');
            document.getElementById('originSelect').value = '';
            document.getElementById('destinationSelect').value = '';

            // Reset map view
            if (markers.length > 0) {
                const bounds = L.latLngBounds();
                markers.forEach(m => bounds.extend(m.getLatLng()));
                map.fitBounds(bounds, {
                    padding: [30, 30]
                });
            }
        });
    });
</script>
@endpush

@endsection