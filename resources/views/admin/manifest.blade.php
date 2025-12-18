@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    {{-- Header with Print Button --}}
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h2 class="fw-bold text-dark mb-1">Passenger Manifest</h2>
            <p class="text-muted mb-0">Driver handout for trip verification</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fa-solid fa-print me-2"></i> Print Manifest
            </button>
            <a href="{{ route('admin.schedules') }}" class="btn btn-outline-secondary ms-2">
                <i class="fa-solid fa-arrow-left me-2"></i> Back
            </a>
        </div>
    </div>

    {{-- Trip Information Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h4 class="fw-bold mb-3">
                        {{ $schedule->origin->city }}
                        <i class="fa-solid fa-arrow-right mx-2 text-primary"></i>
                        {{ $schedule->destination->city }}
                    </h4>
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-muted" width="150">Date:</td>
                            <td class="fw-bold">{{ \Carbon\Carbon::parse($schedule->departure_date)->format('F d, Y (l)') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Departure:</td>
                            <td class="fw-bold">{{ \Carbon\Carbon::parse($schedule->departure_time)->format('h:i A') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Bus Number:</td>
                            <td class="fw-bold">{{ $schedule->bus->code ?? $schedule->bus->bus_number ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Bus Type:</td>
                            <td><span class="badge bg-{{ $schedule->bus->type == 'deluxe' ? 'warning' : 'secondary' }}">{{ ucfirst($schedule->bus->type) }}</span></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                <h3 class="fw-bold text-primary mb-0">{{ $totalPassengers }}</h3>
                                <small class="text-muted">Passengers</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                <h3 class="fw-bold text-success mb-0">{{ $seatsBooked }}</h3>
                                <small class="text-muted">Seats</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                <h3 class="fw-bold text-warning mb-0">₱{{ number_format($totalRevenue) }}</h3>
                                <small class="text-muted">Revenue</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Passenger List --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fa-solid fa-users me-2"></i> Passenger List</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Passenger Name</th>
                        <th>Contact</th>
                        <th>Seat(s)</th>
                        <th>Adults</th>
                        <th>Children</th>
                        <th>Booking Ref</th>
                        <th class="no-print">Verified</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $index => $booking)
                    <tr>
                        <td class="fw-bold">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $booking->guest_name }}</strong>
                            @if($booking->user_id)
                            <span class="badge bg-info ms-1">Member</span>
                            @endif
                        </td>
                        <td>
                            <div class="small">{{ $booking->guest_phone ?? 'N/A' }}</div>
                            <div class="small text-muted">{{ $booking->guest_email }}</div>
                        </td>
                        <td>
                            @foreach($booking->seat_numbers as $seat)
                            <span class="badge bg-primary me-1">{{ $seat }}</span>
                            @endforeach
                        </td>
                        <td>{{ $booking->adults }}</td>
                        <td>{{ $booking->children }}</td>
                        <td><code>{{ $booking->booking_reference ?? '#'.$booking->id }}</code></td>
                        <td class="no-print">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" style="width: 25px; height: 25px;">
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No passengers booked for this trip yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Driver Signature Section (Print Only) --}}
    <div class="mt-5 print-only" style="display: none;">
        <div class="row">
            <div class="col-6">
                <p class="mb-4">Driver Name: _______________________________</p>
                <p>Signature: _______________________________</p>
            </div>
            <div class="col-6 text-end">
                <p class="mb-4">Conductor Name: _______________________________</p>
                <p>Signature: _______________________________</p>
            </div>
        </div>
        <hr class="my-4">
        <p class="text-center text-muted small">
            Generated on {{ now()->format('F d, Y h:i A') }} | Southern Lines Bus Reservation System
        </p>
    </div>
</div>

<style>
    @media print {
        .no-print {
            display: none !important;
        }

        .print-only {
            display: block !important;
        }

        .card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }

        body {
            font-size: 12px;
        }
    }
</style>
@endsection