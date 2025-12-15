@extends('layouts.app')

@section('content')
<div class="hero-section text-white d-flex align-items-center"
    style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?q=80&w=2069&auto=format&fit=crop'); background-size: cover; min-height: 85vh;">
    <div class="container text-center">
        <h1 class="display-3 fw-bold mb-3">Travel the Philippines</h1>
        <p class="lead mb-5">Safe, comfortable, and affordable bus travel.</p>
    </div>
</div>

<div class="container" style="margin-top: -120px; position: relative; z-index: 10;">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-lg border-0 rounded-0">
                <div class="card-header bg-danger text-white py-2 rounded-0 border-0"></div>

                <div class="card-body p-4 p-lg-5 bg-white">
                    <form action="{{ route('trips.search') }}" method="GET">

                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <label class="fw-bold text-danger small mb-2">DEPARTURE DATE</label>
                                <input type="date" name="date" class="form-control form-control-lg rounded-0 bg-light border-0" required min="{{ date('Y-m-d') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="fw-bold text-danger small mb-2">PICK UP LOCATION</label>
                                <select name="origin" id="originSelect" class="form-select form-select-lg rounded-0 bg-light border-0" onchange="calculateDuration()">
                                    <option value="" selected disabled>Select Origin</option>
                                    @foreach($terminals as $t)
                                    <option value="{{ $t->id }}" data-city="{{ $t->city }}">{{ $t->city }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="fw-bold text-danger small mb-2">DROP OFF LOCATION</label>
                                <select name="destination" id="destSelect" class="form-select form-select-lg rounded-0 bg-light border-0" onchange="calculateDuration()">
                                    <option value="" selected disabled>Select Destination</option>
                                    @foreach($terminals as $t)
                                    <option value="{{ $t->id }}" data-city="{{ $t->city }}">{{ $t->city }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div id="durationRow" class="row mb-4" style="display: none;">
                            <div class="col-12">
                                <div class="alert alert-light border d-flex align-items-center justify-content-center text-muted">
                                    <i class="fa-regular fa-clock me-2"></i>
                                    <span id="durationText" class="fw-bold text-dark"></span>
                                </div>
                            </div>
                        </div>

                        <div id="returnRow" class="row g-4 mb-4" style="display: none;">
                            <div class="col-md-4">
                                <label class="fw-bold text-danger small mb-2">RETURN DATE</label>
                                <input type="date" name="return_date" class="form-control form-control-lg rounded-0 bg-light border-0" min="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold text-danger small mb-2">RETURN PICK UP</label>
                                <select name="return_origin" class="form-select form-select-lg rounded-0 bg-light border-0">
                                    <option value="" selected disabled>Select Terminal</option>
                                    @foreach($terminals as $t)
                                    <option value="{{ $t->id }}">{{ $t->city }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold text-danger small mb-2">RETURN DROP OFF</label>
                                <select name="return_destination" class="form-select form-select-lg rounded-0 bg-light border-0">
                                    <option value="" selected disabled>Select Terminal</option>
                                    @foreach($terminals as $t)
                                    <option value="{{ $t->id }}">{{ $t->city }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr class="my-4 opacity-25">

                        <div class="row align-items-center">
                            <div class="col-md-2 mb-3 mb-md-0">
                                <label class="fw-bold text-danger small mb-2">ADULTS</label>
                                <input type="number" name="adults" class="form-control form-control-lg rounded-0 bg-light border-0" value="1" min="1" placeholder="Adults">
                            </div>

                            <div class="col-md-2 mb-3 mb-md-0">
                                <label class="fw-bold text-danger small mb-2">CHILDREN</label>
                                <input type="number" name="children" class="form-control form-control-lg rounded-0 bg-light border-0" value="0" min="0" placeholder="Children">
                            </div>

                            <div class="col-md-4 mb-3 mb-md-0 text-center text-md-start">
                                <div class="d-inline-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="trip_type" id="oneWay" value="oneway" checked onclick="toggleLayout(false)">
                                        <label class="form-check-label fw-bold small text-uppercase" for="oneWay">One Way</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="trip_type" id="roundTrip" value="roundtrip" onclick="toggleLayout(true)">
                                        <label class="form-check-label fw-bold small text-uppercase" for="roundTrip">Return</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <button type="submit" class="btn btn-dark w-100 py-3 rounded-0 text-uppercase fw-bold" style="letter-spacing: 1px;">
                                    Find a Transfer
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. Toggle Return Row
    function toggleLayout(isRoundTrip) {
        const returnRow = document.getElementById('returnRow');
        returnRow.style.display = isRoundTrip ? 'flex' : 'none';
    }

    // 2. Define the Route Matrix (Matches your DatabaseSeeder)
    const routeMatrix = {
        // FROM MANILA (CUBAO)
        "Manila (Cubao)|Baguio": 6,
        "Manila (Cubao)|Pampanga": 2,
        "Manila (Cubao)|La Union": 5,
        "Manila (Cubao)|Vigan": 9,
        "Manila (Cubao)|Laoag": 11,
        "Manila (Cubao)|Tuguegarao": 12,
        "Manila (Cubao)|Batangas": 3,
        "Manila (Cubao)|Naga": 9,
        "Manila (Cubao)|Legazpi": 11,
        "Manila (Cubao)|Pasay": 1,

        // FROM MANILA (PASAY)
        "Manila (Pasay)|Batangas": 3,
        "Manila (Pasay)|Naga": 9,
        "Manila (Pasay)|Legazpi": 11,
        "Manila (Pasay)|Baguio": 7,
        "Manila (Pasay)|Pampanga": 3,

        // INTER-PROVINCIAL
        "Pampanga|Baguio": 4,
        "Pampanga|La Union": 3,
        "Pampanga|Vigan": 7,
        "Pampanga|Laoag": 9,
        "Baguio|La Union": 2,
        "Baguio|Vigan": 5,
        "La Union|Vigan": 4,
        "Vigan|Laoag": 2,
        "Batangas|Naga": 7,
        "Naga|Legazpi": 3
    };

    // 3. Calculate Logic
    function calculateDuration() {
        const originSelect = document.getElementById('originSelect');
        const destSelect = document.getElementById('destSelect');
        const displayRow = document.getElementById('durationRow');
        const displayText = document.getElementById('durationText');

        // Get the selected "Data City" (Name), not the ID
        const originCity = originSelect.options[originSelect.selectedIndex]?.getAttribute('data-city');
        const destCity = destSelect.options[destSelect.selectedIndex]?.getAttribute('data-city');

        if (originCity && destCity) {
            // Try explicit direction first
            let key = `${originCity}|${destCity}`;
            let hours = routeMatrix[key];

            // If not found, try reverse direction (assuming same time)
            if (!hours) {
                key = `${destCity}|${originCity}`;
                hours = routeMatrix[key];
            }

            if (hours) {
                displayText.innerHTML = `Estimated Travel Time: <strong>${hours} Hours</strong>`;
                displayRow.style.display = 'block';
                displayRow.classList.add('animate__animated', 'animate__fadeIn');
            } else {
                displayRow.style.display = 'none'; // Route not defined in JS
            }
        } else {
            displayRow.style.display = 'none';
        }
    }
</script>
@endsection