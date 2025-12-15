@extends('layouts.app')

@section('content')

{{-- ERROR ALERT --}}
@if(session('error'))
<div class="container mt-4">
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
@endif

{{-- 1. HERO SECTION --}}
<div class="container mt-4">
    <div class="row align-items-center">
        <div class="col-lg-5 ps-lg-5">
            <p class="text-danger fw-bold small mb-2" style="letter-spacing: 1px;">BEST BUS TRAVEL AROUND THE CALABARZON</p>

            <h1 class="display-4 fw-bold mb-4" style="line-height: 1.2;">
                Transport, <span class="text-danger underline-red fst-italic">enjoy</span><br>
                and Have a Great<br>
                Experience with us
            </h1>

            <p class="text-muted mb-4" style="font-size: 0.9rem; line-height: 1.6;">
                Travel smarter, not harder. We offer auto-ticketing so you get
                your e-ticket right after payment via online. Plus, with
                Safe Travel protocols, sanitized buses & GPS, your journey is always safe.
            </p>

            <a href="#book-section" class="btn btn-yellow shadow-sm text-uppercase small fw-bold">Read More</a>
        </div>

        <div class="col-lg-7 position-relative text-center">
            <img src="https://placehold.co/800x500/e0f7fa/006064?text=Traveler+Image"
                alt="Traveler" class="img-fluid rounded-4" style="max-height: 500px; width: 100%; object-fit: cover;">
        </div>
    </div>
</div>

{{-- 2. BOOK NOW FORM --}}
<div id="book-section" class="container mt-5 mb-5">
    <div class="text-center mb-4">
        <h2 class="fw-bold">Book Now</h2>


    </div>

    {{-- FORM BOX --}}
    <form action="{{ route('trips.search') }}" method="GET">

        {{-- ROW 1: ORIGIN, DESTINATION, DATE --}}
        <div class="row justify-content-center g-3">
            <div class="col-md-4">
                <div class="custom-input-group h-100 d-flex flex-column justify-content-center">
                    <label class="custom-input-label">FROM:</label>
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-location-dot text-muted me-2 small"></i>
                        <select name="origin" class="custom-input-field" required>
                            <option value="" selected disabled>Select Origin</option>
                            @foreach($terminals as $t)
                            <option value="{{ $t->id }}">{{ $t->city }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="custom-input-group h-100 d-flex flex-column justify-content-center">
                    <label class="custom-input-label">TO:</label>
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-location-dot text-muted me-2 small"></i>
                        <select name="destination" class="custom-input-field" required>
                            <option value="" selected disabled>Select Destination</option>
                            @foreach($terminals as $t)
                            <option value="{{ $t->id }}">{{ $t->city }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="custom-input-group h-100 d-flex flex-column justify-content-center">
                    <label class="custom-input-label">DEPARTURE:</label>
                    <input type="text" name="date" class="custom-input-field" placeholder="DD/MM/YYYY" onfocus="(this.type='date')" required min="{{ date('Y-m-d') }}">
                </div>
            </div>
        </div>

        {{-- ROW 2: RETURN ORIGIN, RETURN DESTINATION, RETURN DATE (Hidden by default) --}}
        <div class="row justify-content-center g-3 mt-1" id="returnRow" style="display: none;">

            <div class="col-md-4">
                <div class="custom-input-group h-100 d-flex flex-column justify-content-center bg-white border border-danger">
                    <label class="custom-input-label text-danger">RETURN PICK UP:</label>
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-location-arrow text-danger me-2 small"></i>
                        <select name="return_origin" class="custom-input-field">
                            <option value="" selected disabled>Select Terminal</option>
                            @foreach($terminals as $t)
                            <option value="{{ $t->id }}">{{ $t->city }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="custom-input-group h-100 d-flex flex-column justify-content-center bg-white border border-danger">
                    <label class="custom-input-label text-danger">RETURN DROP OFF:</label>
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-location-arrow text-danger me-2 small"></i>
                        <select name="return_destination" class="custom-input-field">
                            <option value="" selected disabled>Select Terminal</option>
                            @foreach($terminals as $t)
                            <option value="{{ $t->id }}">{{ $t->city }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="custom-input-group h-100 d-flex flex-column justify-content-center bg-white border border-danger">
                    <label class="custom-input-label text-danger">RETURN DATE:</label>
                    <input type="text" name="return_date" class="custom-input-field" placeholder="DD/MM/YYYY" onfocus="(this.type='date')" min="{{ date('Y-m-d') }}">
                </div>
            </div>
        </div>

        {{-- NEW ROW for Trip Type --}}
        <div class="row justify-content-center g-3 mt-3">
            <div class="col-md-8 text-center">
                <div class="d-inline-flex gap-4">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="trip_type" id="oneWay" value="oneway" checked onclick="toggleLayout(false)">
                        <label class="form-check-label fw-bold small text-uppercase" for="oneWay">One Way</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="trip_type" id="roundTrip" value="roundtrip" onclick="toggleLayout(true)">
                        <label class="form-check-label fw-bold small text-uppercase" for="roundTrip">Round Trip</label>
                    </div>
                </div>
            </div>
        </div>

        {{-- ROW 3: PASSENGERS & BUTTON (Smaller & Centered) --}}
        <div class="row justify-content-center g-3 mt-1">

            <div class="col-md-4">
                <div class="custom-input-group h-100 d-flex flex-column justify-content-center" style="min-height: 48px;">
                    <label class="custom-input-label">PASSENGERS:</label>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <input type="number" name="adults" class="custom-input-field text-center" value="1" min="1" style="width: 40px;">
                            <span class="text-muted small ms-1">Adult</span>
                        </div>
                        <span class="text-muted">|</span>
                        <div class="d-flex align-items-center">
                            <input type="number" name="children" class="custom-input-field text-center" value="0" min="0" style="width: 40px;">
                            <span class="text-muted small ms-1">Child</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <button type="submit" class="btn btn-light-purple w-100 h-100 fw-bold shadow-sm" style="min-height: 48px;">
                    FIND TICKETS
                </button>
            </div>
        </div>

    </form>
</div>

{{-- 3. MAP AND LOCATIONS --}}
<div class="container my-5">
    <div class="row justify-content-center g-4">
        <div class="col-md-6">
            <div class="card border shadow-sm rounded-4 p-3 h-100">
                <h6 class="fw-bold small mb-3 ms-2">My Route</h6>
                <div class="rounded-3 overflow-hidden border">
                    <img src="https://placehold.co/600x400/png?text=Map+View" alt="Map" class="img-fluid w-100" style="object-fit: cover; height: 300px;">
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border shadow-sm rounded-4 p-4 h-100">
                <h6 class="fw-bold small text-uppercase mb-3 border-bottom pb-2">ALL LOCATION</h6>
                <div class="d-flex flex-column gap-2">
                    <a href="#" class="location-pill active">SAN PABLO</a>
                    <a href="#" class="location-pill">TANAUAN</a>
                    <a href="#" class="location-pill">BATANGAS</a>
                    <a href="#" class="location-pill">CAVITE</a>
                    <div class="text-center text-muted small mt-2">...</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 4. SERVICES --}}
<div class="container my-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">We Offer Best Services</h2>
    </div>
    <div class="row g-4 text-center px-lg-5">
        <div class="col-md-4">
            <div class="card h-100 border p-4 shadow-sm rounded-4">
                <div class="mb-3"><i class="fa-solid fa-shield-halved fa-2x text-success"></i></div>
                <p class="small text-muted mb-0">Thorough sanitation for peace of mind.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border p-4 shadow-sm rounded-4">
                <div class="mb-3"><i class="fa-solid fa-bolt fa-2x text-warning"></i></div>
                <p class="small text-muted mb-0">Instant booking & digital tickets.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border p-4 shadow-sm rounded-4">
                <div class="mb-3"><i class="fa-solid fa-headset fa-2x text-dark"></i></div>
                <p class="small text-muted mb-0">24/7 support team ready to help.</p>
            </div>
        </div>
    </div>
</div>

{{-- 5. TOP DESTINATIONS --}}
<div class="container my-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Top Destinations In<br>Calabarzon</h2>
    </div>
    <div class="row g-4 justify-content-center">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden h-100">
                <img src="https://placehold.co/400x300/e0f2f1/004d40?text=Taal+Volcano" class="card-img-top" style="height: 150px; object-fit: cover;">
                <div class="card-body text-center p-2">
                    <p class="small fw-bold mb-0">Taal Volcano</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden h-100">
                <img src="https://placehold.co/400x300/e8f5e9/1b5e20?text=Pagsanjan+Falls" class="card-img-top" style="height: 150px; object-fit: cover;">
                <div class="card-body text-center p-2">
                    <p class="small fw-bold mb-0">Pagsanjan Falls</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden h-100">
                <img src="https://placehold.co/400x300/fff3e0/e65100?text=Tagaytay+City" class="card-img-top" style="height: 150px; object-fit: cover;">
                <div class="card-body text-center p-2">
                    <p class="small fw-bold mb-0">Tagaytay City</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 6. THREE STEPS --}}
<div class="container my-5 pb-5">
    <h2 class="fw-bold mb-5 ms-lg-5 ps-lg-5">Book Your Next Trip<br>In 3 Easy Steps</h2>
    <div class="row align-items-center">
        <div class="col-md-6 ps-lg-5 ms-lg-5">
            <div class="d-flex mb-4">
                <div class="me-3">
                    <div class="step-icon-box" style="background-color: #f1c40f;"><i class="fa-regular fa-map"></i></div>
                </div>
                <div>
                    <h6 class="fw-bold mb-1">Select Your Trip</h6>
                    <p class="small text-muted">Choose origin and destination.</p>
                </div>
            </div>
            <div class="d-flex mb-4">
                <div class="me-3">
                    <div class="step-icon-box" style="background-color: #ff6b6b;"><i class="fa-solid fa-chair"></i></div>
                </div>
                <div>
                    <h6 class="fw-bold mb-1">Secure Your Seat</h6>
                    <p class="small text-muted">Select seat and pay.</p>
                </div>
            </div>
            <div class="d-flex">
                <div class="me-3">
                    <div class="step-icon-box" style="background-color: #1abc9c;"><i class="fa-solid fa-bus-simple"></i></div>
                </div>
                <div>
                    <h6 class="fw-bold mb-1">Hop on & Travel Safely</h6>
                    <p class="small text-muted">Present e-ticket and enjoy.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 position-relative">
            <img src="https://placehold.co/400x500/f3e5f5/4a148c?text=Step+Image" class="img-fluid rounded-3 shadow border border-white border-4" style="transform: rotate(-5deg); z-index: 1;">
        </div>
    </div>
</div>

{{-- 7. JAVASCRIPT LOGIC --}}
<script>
    function toggleLayout(isRoundTrip) {
        const returnRow = document.getElementById('returnRow');
        returnRow.style.display = isRoundTrip ? 'flex' : 'none';

        // Toggle REQUIRED attributes for return fields
        const retOrigin = document.querySelector('select[name="return_origin"]');
        const retDest = document.querySelector('select[name="return_destination"]');
        const retDate = document.querySelector('input[name="return_date"]');

        if (isRoundTrip) {
            returnRow.classList.add('animate__animated', 'animate__fadeIn');
            retOrigin.setAttribute('required', 'required');
            retDest.setAttribute('required', 'required');
            retDate.setAttribute('required', 'required');
        } else {
            retOrigin.removeAttribute('required');
            retDest.removeAttribute('required');
            retDate.removeAttribute('required');
        }
    }
</script>

@endsection