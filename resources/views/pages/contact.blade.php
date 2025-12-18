@extends('layouts.app')

@section('content')
<div class="container page-container">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            {{-- Page Header --}}
            <div class="page-header">
                <h1 class="page-title">Contact Us</h1>
                <p class="page-subtitle">Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
            </div>

            <div class="row g-5">
                {{-- Contact Form --}}
                <div class="col-lg-7">
                    <div class="card card-unified">
                        <div class="card-body">
                            <h5 class="section-title">Send us a Message</h5>

                            <form action="{{ route('contact.submit') }}" method="POST">
                                @csrf

                                {{-- SECURITY: Honeypot field - hidden from real users, bots will fill it --}}
                                <div style="position: absolute; left: -9999px;" aria-hidden="true">
                                    <input type="text" name="website" tabindex="-1" autocomplete="off">
                                </div>
                                {{-- SECURITY: Timestamp token to detect bots submitting too fast --}}
                                <input type="hidden" name="_form_time" value="{{ encrypt(time()) }}">

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Full Name</label>
                                        <input type="text" name="name" class="form-control" placeholder="John Doe" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Email Address</label>
                                        <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted">Phone Number</label>
                                        <input type="tel" name="phone" class="form-control" placeholder="+63 9XX XXX XXXX">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted">Subject</label>
                                        <select name="subject" class="form-select" required>
                                            <option value="" selected disabled>Select a topic</option>
                                            <option value="booking">Booking Inquiry</option>
                                            <option value="refund">Refund Request</option>
                                            <option value="feedback">Feedback</option>
                                            <option value="complaint">Complaint</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted">Message</label>
                                        <textarea name="message" class="form-control" rows="5" placeholder="How can we help you?" required></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-unified btn-unified-md w-100" style="background-color: #1E293B; color: white;">
                                            <i class="fas fa-paper-plane me-2"></i> Send Message
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Contact Info --}}
                <div class="col-lg-5">
                    {{-- Info Cards --}}
                    <div class="card card-unified mb-4">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <div class="icon-box me-3" style="background: rgba(255, 193, 7, 0.15); color: #B7791F;">
                                    <i class="fas fa-map-marker-alt fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Our Office</h6>
                                    <p class="text-muted mb-0 small">123 Main Terminal Building<br>Calabarzon, Philippines 4000</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-unified mb-4">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <div class="icon-box bg-success bg-opacity-10 text-success me-3">
                                    <i class="fas fa-phone fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Phone</h6>
                                    <p class="text-muted mb-0 small">(02) 8123-4567<br>+63 917 123 4567</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-unified mb-4">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <div class="icon-box bg-danger bg-opacity-10 text-danger me-3">
                                    <i class="fas fa-envelope fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Email</h6>
                                    <p class="text-muted mb-0 small">info@southernlines.ph<br>support@southernlines.ph</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-unified">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <div class="icon-box bg-warning bg-opacity-10 text-warning me-3">
                                    <i class="fas fa-clock fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Business Hours</h6>
                                    <p class="text-muted mb-0 small">Mon - Fri: 6:00 AM - 10:00 PM<br>Sat - Sun: 5:00 AM - 11:00 PM</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Social Links --}}
                    <div class="mt-4 text-center">
                        <p class="fw-bold mb-3">Follow Us</p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="#" class="btn btn-outline-secondary rounded-circle p-2">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="btn btn-outline-secondary rounded-circle p-2">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="btn btn-outline-secondary rounded-circle p-2">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="btn btn-outline-secondary rounded-circle p-2">
                                <i class="fab fa-youtube"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection