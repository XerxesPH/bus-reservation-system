<?php

use Illuminate\Support\Facades\Route;
use App\Models\Terminal;
use App\Http\Controllers\TripController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;


Route::get('/', function () {
    $terminals = Terminal::all(); // Get all terminals from DB
    return view('welcome', compact('terminals')); // Send them to the view
});
// 2. Search Results
Route::get('/search', [TripController::class, 'search'])->name('trips.search');

// 3. Seat Selection
Route::get('/seat-selection', [TripController::class, 'selectSeats'])->name('trips.seats');

// Handle the POST form submission
Route::post('/book-ticket', [TripController::class, 'bookTicket']);

// Show the Success Page (Pass the booking ID)
Route::get('/booking-success/{booking}', [TripController::class, 'showSuccess'])->name('booking.success');

// --- AUTH ROUTES ---
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Group all Admin routes together
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Schedule Generator
    Route::get('/create-schedule', [AdminController::class, 'createSchedule'])->name('admin.create_schedule');
    Route::post('/create-schedule', [AdminController::class, 'storeSchedule'])->name('admin.store_schedule');

    // Booking Manager
    Route::get('/bookings', [AdminController::class, 'bookings'])->name('admin.bookings');
    Route::post('/bookings/{id}/cancel', [AdminController::class, 'cancelBooking'])->name('admin.cancel_booking');

    Route::get('/admin/buses/{id}/edit', [App\Http\Controllers\AdminController::class, 'editBus'])->name('admin.buses.edit');
    Route::put('/admin/buses/{id}', [App\Http\Controllers\AdminController::class, 'updateBus'])->name('admin.buses.update');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/my-bookings', [UserController::class, 'myBookings'])->name('user.bookings');
});
