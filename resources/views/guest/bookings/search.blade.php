@extends('layouts.app')

@section('content')
<div class="container page-container">
    <div class="page-header">
        <h1 class="page-title">Manage Booking</h1>
        <p class="page-subtitle">View, print, or cancel your ticket.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card card-unified">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                            <i class="fa-solid fa-ticket fa-lg"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Find your booking</div>
                            <div class="text-muted small">Enter your reference and email to view details.</div>
                        </div>
                    </div>

                    @if(session('error'))
                    <div class="alert alert-danger text-center small rounded-3 mb-4">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> {{ session('error') }}
                    </div>
                    @endif

                    <form action="{{ route('guest.bookings.show') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Booking Reference</label>
                            <input type="text" name="booking_reference" class="form-control form-control-lg text-uppercase fw-bold text-center" placeholder="BUS-XXXXXX" required>
                            <div class="form-text text-center small">Found in your email confirmation.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                            <div class="form-text text-center small">Used during booking.</div>
                        </div>

                        <button type="submit" class="btn btn-navy btn-unified btn-unified-md w-100 fw-bold">
                            FIND BOOKING
                        </button>
                    </form>

                    <div class="d-grid gap-2 mt-3">
                        <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-unified btn-unified-sm fw-bold">Back to Home</a>
                        <a href="{{ route('pages.contact') }}" class="btn btn-outline-dark btn-unified btn-unified-sm fw-bold">Contact Support</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection