<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\UserController;
use App\Models\Terminal;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $terminals = Schema::hasTable('terminals') ? Terminal::all() : collect();
    return view('welcome', compact('terminals'));
})->name('home');

// --- TRIP & BOOKING ROUTES ---

// 1. Search Results (Step 1 or Step 2)
Route::get('/search', [TripController::class, 'search'])->name('trips.search');

// 2. Seat Selection Page
Route::get('/seat-selection', [TripController::class, 'selectSeats'])->name('trips.seats');

// 3. INTERMEDIATE STEP: Store Outbound Selection (For Round Trips)
Route::post('/book/outbound', [TripController::class, 'storeOutbound'])->name('trips.store_outbound');

// 4. FINAL STEP: Finalize Booking (For One Way or Return Leg)
Route::post('/book/finalize', [TripController::class, 'bookTicket'])->name('trips.book');

// 5. Success Page
Route::get('/booking-success/{booking}', [TripController::class, 'showSuccess'])->name('booking.success');


// --- AUTH ROUTES ---
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- ADMIN & USER ROUTES (Unchanged) ---
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/create-schedule', [AdminController::class, 'createSchedule'])->name('admin.create_schedule');
    Route::post('/create-schedule', [AdminController::class, 'storeSchedule'])->name('admin.store_schedule');

    // NEW: Schedule Management
    Route::get('/schedules', [AdminController::class, 'schedules'])->name('admin.schedules');
    Route::post('/schedules/{id}/cancel', [AdminController::class, 'cancelSchedule'])->name('admin.cancel_schedule');

    // NEW: Automated Route Plans (Templates)
    Route::get('/templates', [AdminController::class, 'templates'])->name('admin.templates');
    Route::get('/templates/create', [AdminController::class, 'createTemplate'])->name('admin.templates.create');
    Route::post('/templates', [AdminController::class, 'storeTemplate'])->name('admin.templates.store');
    Route::post('/templates/{id}/toggle', [AdminController::class, 'toggleTemplate'])->name('admin.templates.toggle');
    Route::delete('/templates/{id}', [AdminController::class, 'deleteTemplate'])->name('admin.templates.delete');

    // NEW: Bus/Fleet Management
    Route::get('/buses', [AdminController::class, 'buses'])->name('admin.buses');
    Route::get('/buses/{id}/edit', [AdminController::class, 'editBus'])->name('admin.buses.edit');
    Route::put('/buses/{id}', [AdminController::class, 'updateBus'])->name('admin.buses.update');
    Route::delete('/buses/{id}', [AdminController::class, 'deleteBus'])->name('admin.buses.delete');

    Route::get('/bookings', [AdminController::class, 'bookings'])->name('admin.bookings');
    Route::post('/bookings/{id}/cancel', [AdminController::class, 'cancelBooking'])->name('admin.cancel_booking');
});

Route::middleware(['auth'])->group(function () {
    // Deprecated standalone route, redirecting to new dashboard or keeping as alias? 
    // Let's point 'My Bookings' to the new dashboard for consistency.
    Route::get('/my-bookings', [App\Http\Controllers\ProfileController::class, 'index'])->name('user.bookings');

    // Profile Dashboard
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile/update', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');

    // Payment Methods
    Route::post('/profile/payment-methods', [App\Http\Controllers\ProfileController::class, 'storePaymentMethod'])->name('profile.payment_methods.store');
    Route::delete('/profile/payment-methods/{paymentMethod}', [App\Http\Controllers\ProfileController::class, 'destroyPaymentMethod'])->name('profile.payment_methods.destroy');

    // Booking Actions
    Route::post('/profile/bookings/{booking}/cancel', [App\Http\Controllers\ProfileController::class, 'cancelBooking'])->name('profile.bookings.cancel');
});
