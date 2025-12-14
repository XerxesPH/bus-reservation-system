@extends('layouts.app')

@section('content')
<div class="hero-section">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3">Where will you go next?</h1>
        <p class="lead">Book bus tickets easily, safely, and instantly.</p>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="search-box">
                <form action="{{ route('trips.search') }}" method="GET">
                    <div class="row g-3 align-items-end">

                        <div class="col-md-3">
                            <label class="form-label text-muted fw-bold small text-uppercase">From</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-location-dot text-primary"></i></span>
                                <select name="origin" class="form-select border-start-0 ps-0 bg-light">
                                    @foreach($terminals as $t)
                                    <option value="{{ $t->id }}">{{ $t->city }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-muted fw-bold small text-uppercase">To</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-map-pin text-danger"></i></span>
                                <select name="destination" class="form-select border-start-0 ps-0 bg-light">
                                    @foreach($terminals as $t)
                                    <option value="{{ $t->id }}">{{ $t->city }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-muted fw-bold small text-uppercase">Date</label>
                            <input type="date" name="date" class="form-control" required min="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-md-1">
                            <label class="form-label text-muted fw-bold small text-uppercase">Pax</label>
                            <input type="number" name="pax" class="form-control text-center" value="1" min="1" max="10">
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100 fw-bold py-2">
                                <i class="fa-solid fa-magnifying-glass me-2"></i> Find
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container py-5 mt-5">
    <div class="row text-center g-4">
        <div class="col-md-4">
            <div class="p-4 bg-white rounded shadow-sm h-100">
                <i class="fa-solid fa-shield-halved fa-3x text-primary mb-3"></i>
                <h5>Safe Travel</h5>
                <p class="text-muted">All our buses are sanitized and tracked via GPS.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-4 bg-white rounded shadow-sm h-100">
                <i class="fa-solid fa-bolt fa-3x text-warning mb-3"></i>
                <h5>Instant Booking</h5>
                <p class="text-muted">Get your e-ticket immediately after payment.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-4 bg-white rounded shadow-sm h-100">
                <i class="fa-solid fa-headset fa-3x text-success mb-3"></i>
                <h5>24/7 Support</h5>
                <p class="text-muted">We are here to help you anytime, anywhere.</p>
            </div>
        </div>
    </div>
</div>
@endsection