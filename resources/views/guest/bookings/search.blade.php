@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                            <i class="fa-solid fa-ticket fa-2x text-primary"></i>
                        </div>
                        <h4 class="fw-bold">Manage Booking</h4>
                        <p class="text-muted small">View, print, or cancel your ticket.</p>
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

                        <button type="submit" class="btn btn-navy w-100 py-3 fw-bold rounded-3 shadow-sm">
                            FIND BOOKING
                        </button>
                    </form>
                </div>
            </div>

            <div class="text-center mt-4">
                <p class="text-muted small">Having trouble? <a href="#" class="text-decoration-none fw-bold">Contact Support</a></p>
            </div>
        </div>
    </div>
</div>
@endsection