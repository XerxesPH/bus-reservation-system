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
});

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
    Route::get('/bookings', [AdminController::class, 'bookings'])->name('admin.bookings');
    Route::post('/bookings/{id}/cancel', [AdminController::class, 'cancelBooking'])->name('admin.cancel_booking');
    Route::get('/admin/buses/{id}/edit', [AdminController::class, 'editBus'])->name('admin.buses.edit');
    Route::put('/admin/buses/{id}', [AdminController::class, 'updateBus'])->name('admin.buses.update');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/my-bookings', [UserController::class, 'myBookings'])->name('user.bookings');
});
