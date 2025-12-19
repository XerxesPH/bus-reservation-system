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

// NEW: Public Pages
Route::get('/schedule', [App\Http\Controllers\PageController::class, 'schedule'])->name('pages.schedule');
Route::get('/contact', [App\Http\Controllers\PageController::class, 'contact'])->name('pages.contact');
Route::post('/contact', [App\Http\Controllers\PageController::class, 'submitContact'])->name('contact.submit');
Route::get('/terminals', [App\Http\Controllers\PageController::class, 'terminals'])->name('pages.terminals');

// --- TRIP & BOOKING ROUTES ---

// 1. Search Results (Step 1 or Step 2)
Route::get('/search', [TripController::class, 'search'])->name('trips.search');

// 2. Seat Selection Page
Route::get('/seat-selection', [TripController::class, 'selectSeats'])->name('trips.seats');

// 3. INTERMEDIATE STEP: Store Outbound Selection (For Round Trips)
Route::post('/book/outbound', [TripController::class, 'storeOutbound'])->name('trips.store_outbound');

// 4. CHECKOUT FLOW
// Step A: Prepare Checkout (Store seats in session & Redirect to Checkout Page)
Route::post('/checkout/prepare', [App\Http\Controllers\CheckoutController::class, 'prepare'])->name('checkout.prepare');

// Step B: Show Checkout Page (Payment & Contact Info)
Route::get('/checkout', [App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout.index');

// Step C: Process Payment & Create Booking
Route::post('/checkout/process', [App\Http\Controllers\CheckoutController::class, 'store'])->name('checkout.store');

// 6. GUEST BOOKING MANAGEMENT
Route::get('/manage-booking', [App\Http\Controllers\GuestBookingController::class, 'search'])->name('guest.bookings.search');
Route::get('/manage-booking/search', function () {
    return redirect()->route('guest.bookings.search')->with('error', 'Please use the form to search for your booking.');
})->name('guest.bookings.show.get');
Route::post('/manage-booking/search', [App\Http\Controllers\GuestBookingController::class, 'show'])->name('guest.bookings.show');
Route::post('/manage-booking/cancel/{id}', [App\Http\Controllers\GuestBookingController::class, 'cancel'])->name('guest.bookings.cancel');

// 7. Success Page
Route::get('/booking-verifying/{booking}', [TripController::class, 'showVerifying'])->name('booking.verifying');
Route::get('/booking-success/{booking}', [TripController::class, 'showSuccess'])->name('booking.success');

// 8. E-TICKET DOWNLOAD
Route::get('/ticket/{bookingReference}/download', [App\Http\Controllers\TicketController::class, 'downloadTicket'])->name('ticket.download');
Route::get('/ticket/{bookingReference}/view', [App\Http\Controllers\TicketController::class, 'viewTicket'])->name('ticket.view');


// --- AUTH ROUTES ---
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1') // SECURITY: Limit to 5 login attempts per minute
    ->name('login.post');
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
    Route::get('/buses/create', [AdminController::class, 'createBus'])->name('admin.buses.create');
    Route::post('/buses', [AdminController::class, 'storeBus'])->name('admin.buses.store');
    Route::get('/buses/{id}/edit', [AdminController::class, 'editBus'])->name('admin.buses.edit');
    Route::put('/buses/{id}', [AdminController::class, 'updateBus'])->name('admin.buses.update');
    Route::delete('/buses/{id}', [AdminController::class, 'deleteBus'])->name('admin.buses.delete');

    Route::get('/bookings', [AdminController::class, 'bookings'])->name('admin.bookings');
    Route::post('/bookings/{id}/cancel', [AdminController::class, 'cancelBooking'])->name('admin.cancel_booking');

    // NEW: Passenger Manifest (Driver Handout)
    Route::get('/manifest/{scheduleId}', [AdminController::class, 'manifest'])->name('admin.manifest');

    // NEW: Contact Messages Inbox
    Route::get('/messages', [AdminController::class, 'messages'])->name('admin.messages');
    Route::get('/messages/{id}', [AdminController::class, 'showMessage'])->name('admin.messages.show');
    Route::delete('/messages/{id}', [AdminController::class, 'deleteMessage'])->name('admin.messages.delete');

    // NEW: Sales Reports
    Route::get('/reports/sales', [AdminController::class, 'salesReport'])->name('admin.reports.sales');

    // NEW: User Management
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/users/{id}/toggle-ban', [AdminController::class, 'toggleUserBan'])->name('admin.users.toggle_ban');
});

Route::middleware(['auth'])->group(function () {
    // Deprecated standalone route, redirecting to new dashboard or keeping as alias? 
    // Let's point 'My Bookings' to the new dashboard for consistency.
    Route::get('/my-bookings', [App\Http\Controllers\ProfileController::class, 'bookings'])->name('user.bookings');

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
