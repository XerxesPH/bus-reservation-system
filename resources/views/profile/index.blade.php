@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold">My Account</h2>
            <p class="text-muted">Manage your profile, security, payments, and bookings.</p>
        </div>
    </div>

    <div class="row g-5">
        {{-- Sidebar Navigation --}}
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush rounded-4 overflow-hidden">
                        <a href="#profile" class="list-group-item list-group-item-action py-3 px-4 active" data-bs-toggle="list">
                            <i class="fa-solid fa-user me-2"></i> Profile Settings
                        </a>
                        <a href="#security" class="list-group-item list-group-item-action py-3 px-4" data-bs-toggle="list">
                            <i class="fa-solid fa-lock me-2"></i> Security
                        </a>
                        <a href="#payments" class="list-group-item list-group-item-action py-3 px-4" data-bs-toggle="list">
                            <i class="fa-solid fa-credit-card me-2"></i> Payment Methods
                        </a>
                        <a href="#tickets" class="list-group-item list-group-item-action py-3 px-4" data-bs-toggle="list">
                            <i class="fa-solid fa-ticket me-2"></i> My Tickets
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Content Area --}}
        <div class="col-lg-9">

            {{-- Flash Messages --}}
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="tab-content">

                {{-- 1. Profile Settings --}}
                <div class="tab-pane fade show active" id="profile">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold">Personal Information</h5>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('profile.update') }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">Full Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">Email Address</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">Contact Number</label>
                                    <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $user->contact_number) }}" placeholder="+63 9XX XXX XXXX">
                                </div>
                                <div class="text-end mt-4">
                                    <button type="submit" class="btn btn-primary px-4 fw-bold">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- 2. Security --}}
                <div class="tab-pane fade" id="security">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold">Change Password</h5>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('profile.password') }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">Current Password</label>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">New Password</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">Confirm New Password</label>
                                    <input type="password" name="password_confirmation" class="form-control" required>
                                </div>
                                <div class="text-end mt-4">
                                    <button type="submit" class="btn btn-primary px-4 fw-bold">Update Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- 3. Payment Methods --}}
                <div class="tab-pane fade" id="payments">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">Saved Payment Methods</h5>
                            <button class="btn btn-sm btn-outline-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                                <i class="fa-solid fa-plus me-1"></i> Add New
                            </button>
                        </div>
                        <div class="card-body p-4">
                            @if($paymentMethods->isEmpty())
                            <div class="text-center text-muted py-4">
                                <i class="fa-regular fa-credit-card fa-2x mb-2 opacity-50"></i>
                                <p>No payment methods saved yet.</p>
                            </div>
                            @else
                            <div class="row g-3">
                                @foreach($paymentMethods as $method)
                                <div class="col-md-6">
                                    <div class="p-3 border rounded-3 position-relative bg-light">
                                        <div class="d-flex align-items-center mb-2">
                                            @if($method->type == 'card')
                                            <i class="fa-brands fa-cc-{{ strtolower($method->provider) }} fa-2x me-2 text-primary"></i>
                                            @else
                                            <i class="fa-solid fa-wallet fa-2x me-2 text-success"></i>
                                            @endif
                                            <div>
                                                <div class="fw-bold">{{ $method->provider }}</div>
                                                <div class="small text-muted">{{ $method->type == 'card' ? '**** **** **** ' . substr($method->account_number, -4) : $method->account_number }}</div>
                                            </div>
                                        </div>
                                        <form action="{{ route('profile.payment_methods.destroy', $method->id) }}" method="POST" class="position-absolute top-0 end-0 p-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm text-danger" onclick="return confirm('Remove this payment method?')">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- 4. My Tickets --}}
                <div class="tab-pane fade" id="tickets">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold">Booking History</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Trip Details</th>
                                        <th>Date & Time</th>
                                        <th>Seats</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bookings as $booking)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold">{{ $booking->schedule->origin->city }} <i class="fa-solid fa-arrow-right mx-1 text-muted small"></i> {{ $booking->schedule->destination->city }}</div>
                                            <div class="small text-muted">{{ $booking->schedule->bus->bus_number }} ({{ $booking->schedule->bus->type }})</div>
                                        </td>
                                        <td>
                                            <div>{{ \Carbon\Carbon::parse($booking->schedule->departure_date)->format('M d, Y') }}</div>
                                            <div class="small text-muted">{{ \Carbon\Carbon::parse($booking->schedule->departure_time)->format('h:i A') }}</div>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 100px;" title="{{ is_array($booking->seat_numbers) ? implode(', ', $booking->seat_numbers) : $booking->seat_numbers }}">
                                                {{ is_array($booking->seat_numbers) ? implode(', ', $booking->seat_numbers) : $booking->seat_numbers }}
                                            </div>
                                        </td>
                                        <td class="fw-bold">₱{{ number_format($booking->total_price, 2) }}</td>
                                        <td>
                                            @if($booking->status == 'confirmed')
                                            <span class="badge bg-success bg-opacity-10 text-success">Confirmed</span>
                                            @elseif($booking->status == 'cancelled')
                                            <span class="badge bg-danger bg-opacity-10 text-danger">Cancelled</span>
                                            @else
                                            <span class="badge bg-secondary">{{ $booking->status }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            @if($booking->status == 'confirmed')
                                            @php
                                            $deptTime = \Carbon\Carbon::parse($booking->schedule->departure_date . ' ' . $booking->schedule->departure_time);
                                            @endphp
                                            @if($deptTime->isFuture())
                                            <form action="{{ route('profile.bookings.cancel', $booking->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('POST') {{-- Using POST for cancellation action --}}
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to cancel this ticket?')">
                                                    Cancel
                                                </button>
                                            </form>
                                            @else
                                            <span class="text-muted small">Completed</span>
                                            @endif
                                            @else
                                            <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-ticket fa-2x mb-3 opacity-25"></i>
                                            <p>No booking history found.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- Add Payment Method Modal --}}
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Add Payment Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('profile.payment_methods.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Payment Type</label>
                        <select name="type" class="form-select" id="paymentType" required>
                            <option value="card">Credit/Debit Card</option>
                            <option value="ewallet">E-Wallet</option>
                        </select>
                    </div>

                    <div id="cardFields">
                        <div class="mb-3">
                            <label class="form-label">Provider</label>
                            <select name="provider" class="form-select">
                                <option value="Visa">Visa</option>
                                <option value="Mastercard">Mastercard</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Card Number</label>
                            <input type="text" name="account_number" class="form-control" placeholder="XXXX XXXX XXXX XXXX">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Expiry Date</label>
                            <input type="text" name="expiry_date" class="form-control" placeholder="MM/YY">
                        </div>
                    </div>

                    <div id="walletFields" class="d-none">
                        <div class="mb-3">
                            <label class="form-label">Provider</label>
                            <select name="provider" class="form-select" disabled>
                                <option value="GCash">GCash</option>
                                <option value="Maya">Maya</option>
                                <option value="GrabPay">GrabPay</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mobile Number</label>
                            <input type="text" name="account_number" class="form-control" placeholder="09XX XXX XXXX" disabled>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Method</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Simple script to toggle fields in modal
    document.getElementById('paymentType').addEventListener('change', function() {
        const type = this.value;
        const cardFields = document.getElementById('cardFields');
        const walletFields = document.getElementById('walletFields');

        // Inputs
        const cardInputs = cardFields.querySelectorAll('input, select');
        const walletInputs = walletFields.querySelectorAll('input, select');

        if (type === 'card') {
            cardFields.classList.remove('d-none');
            walletFields.classList.add('d-none');
            cardInputs.forEach(el => el.disabled = false);
            walletInputs.forEach(el => el.disabled = true);
        } else {
            cardFields.classList.add('d-none');
            walletFields.classList.remove('d-none');
            cardInputs.forEach(el => el.disabled = true);
            walletInputs.forEach(el => el.disabled = false);
        }
    });
</script>
@endsection