@extends('layouts.app')

@section('content')
<div class="container page-container">
    <div class="modal fade show" id="verifyingModal" tabindex="-1" aria-modal="true" role="dialog" style="display:block;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Verifying Payment</h5>
                </div>
                <div class="modal-body pt-2">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="spinner-border text-primary" role="status" aria-label="Loading"></div>
                        <div class="small text-muted">
                            Please wait while we confirm your payment and send your ticket details.
                        </div>
                    </div>

                    <div class="small text-muted mb-3">
                        <div><span class="fw-semibold">Email:</span> {{ $booking->guest_email ?? ($booking->user->email ?? '-') }}</div>
                        <div><span class="fw-semibold">Mobile:</span> {{ $booking->guest_phone ?? '-' }}</div>
                    </div>

                    <div class="list-group list-group-flush rounded-3" id="verifySteps" data-success-url="{{ route('booking.success', ['booking' => $booking->id]) }}">
                        <div class="list-group-item d-flex align-items-center gap-2" data-step="0">
                            <i class="fa-regular fa-circle text-muted"></i>
                            <span class="small">Connecting to payment gateway...</span>
                        </div>
                        <div class="list-group-item d-flex align-items-center gap-2" data-step="1">
                            <i class="fa-regular fa-circle text-muted"></i>
                            <span class="small">Validating transaction reference...</span>
                        </div>
                        <div class="list-group-item d-flex align-items-center gap-2" data-step="2">
                            <i class="fa-regular fa-circle text-muted"></i>
                            <span class="small">Sending confirmation email...</span>
                        </div>
                        <div class="list-group-item d-flex align-items-center gap-2" data-step="3">
                            <i class="fa-regular fa-circle text-muted"></i>
                            <span class="small">Sending SMS to your mobile number...</span>
                        </div>
                        <div class="list-group-item d-flex align-items-center gap-2" data-step="4">
                            <i class="fa-regular fa-circle text-muted"></i>
                            <span class="small">Generating e-ticket...</span>
                        </div>
                    </div>

                    <div class="mt-3 small text-muted">
                        You will be redirected automatically.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rows = Array.from(document.querySelectorAll('#verifySteps [data-step]'));
        const stepsEl = document.getElementById('verifySteps');
        const successUrl = stepsEl ? stepsEl.dataset.successUrl : null;

        function setRowState(row, state) {
            const icon = row.querySelector('i');
            if (!icon) return;

            if (state === 'active') {
                icon.className = 'fa-solid fa-circle-notch fa-spin text-primary';
            } else if (state === 'done') {
                icon.className = 'fa-solid fa-circle-check text-success';
            } else {
                icon.className = 'fa-regular fa-circle text-muted';
            }
        }

        let current = 0;
        rows.forEach(r => setRowState(r, 'pending'));
        setRowState(rows[0], 'active');

        const interval = setInterval(function() {
            setRowState(rows[current], 'done');
            current += 1;

            if (current >= rows.length) {
                clearInterval(interval);
                setTimeout(function() {
                    if (successUrl) {
                        window.location.href = successUrl;
                    }
                }, 650);
                return;
            }

            setRowState(rows[current], 'active');
        }, 800);
    });
</script>
@endpush
@endsection