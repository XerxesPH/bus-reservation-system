<?php

use App\Http\Controllers\Api\TripApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public Routes (Anyone can access)
Route::get('/trips/search', [TripApiController::class, 'search']);
Route::post('/book-ticket', [TripApiController::class, 'bookTicket']);

// Protected Routes (Requires Login)
Route::middleware('auth:sanctum')->group(function () {

    // 1. View My Bookings
    Route::get('/my-bookings', [TripApiController::class, 'myBookings']);

    // 2. Cancel Booking (The "Delete" Requirement)
    Route::delete('/bookings/{id}', [TripApiController::class, 'cancelBooking']);

    // 3. Admin Only Route (Satisfies "Access Control" Requirement)
    Route::middleware('admin')->get('/admin/all-bookings', function () {
        return \App\Models\Booking::all();
    });
});
