<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TripApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public Routes
Route::get('/trips/search', [TripApiController::class, 'search']);
Route::post('/book-ticket', [TripApiController::class, 'bookTicket']);

// Protected Routes (Requires Login)
Route::middleware('auth:sanctum')->group(function () {
    // You can put routes here that require the user to be logged in
    // Example: View My Booking History
});
