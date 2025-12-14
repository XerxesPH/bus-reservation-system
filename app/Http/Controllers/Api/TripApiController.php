<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TripApiController extends Controller
{
    // GET /api/trips/search
    public function search(Request $request)
    {
        // 1. Validate Input (Good practice for APIs)
        $validator = Validator::make($request->all(), [
            'origin' => 'required|exists:terminals,id',
            'destination' => 'required|exists:terminals,id',
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Query DB
        $trips = Schedule::where('origin_id', $request->origin)
            ->where('destination_id', $request->destination)
            ->where('departure_date', $request->date)
            ->with(['bus', 'origin', 'destination']) // Eager load relationships
            ->get();

        // 3. Return JSON (Satisfies "Read" operation)
        return response()->json([
            'status' => 'success',
            'data' => $trips
        ], 200);
    }

    // POST /api/book-ticket
    public function bookTicket(Request $request)
    {
        // 1. Validate
        $validator = Validator::make($request->all(), [
            'schedule_id' => 'required|exists:schedules,id',
            'guest_name' => 'required|string',
            'guest_email' => 'required|email',
            'selected_seats' => 'required|array', // API expects an actual array, not a JSON string
            'selected_seats.*' => 'integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $seats = $request->selected_seats; // In API, we usually send raw JSON array, so no need to json_decode

        // 2. Transaction (Same logic as your Web Controller)
        try {
            $booking = DB::transaction(function () use ($request, $seats) {
                $trip = Schedule::with('bus')->lockForUpdate()->find($request->schedule_id);

                // Capacity Check
                foreach ($seats as $seat) {
                    if ($seat > $trip->bus->capacity || $seat < 1) {
                        throw new \Exception("Seat #$seat does not exist.");
                    }
                }

                // Double Booking Check
                $existing = Booking::where('schedule_id', $trip->id)
                    ->where('status', 'confirmed')
                    ->pluck('seat_numbers')
                    ->flatten()
                    ->toArray();

                foreach ($seats as $seat) {
                    if (in_array($seat, $existing)) {
                        throw new \Exception("Seat $seat is already taken.");
                    }
                }

                // Create Booking
                // Note: Auth::id() will work here ONLY if you set up API Authentication (Sanctum)
                return Booking::create([
                    'schedule_id' => $trip->id,
                    'user_id' => Auth::guard('sanctum')->id(),
                    'guest_name' => $request->guest_name,
                    'guest_email' => $request->guest_email,
                    'seat_numbers' => $seats,
                    'total_price' => $trip->price * count($seats),
                    'status' => 'confirmed'
                ]);
            });

            // 3. Return JSON (Satisfies "Create" operation)
            return response()->json([
                'status' => 'success',
                'message' => 'Booking successful',
                'booking_id' => $booking->id
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400); // 400 Bad Request
        }
    }

    // DELETE /api/bookings/{id} (Satisfies "Delete" Operation)
    public function cancelBooking($id)
    {
        // 1. Find Booking
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json(['status' => 'error', 'message' => 'Booking not found'], 404);
        }

        // 2. Security Check (User must own the booking)
        if ($booking->user_id !== Auth::guard('sanctum')->id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        // 3. Perform Delete (Cancel)
        $booking->update(['status' => 'cancelled']);

        return response()->json(['status' => 'success', 'message' => 'Booking cancelled']);
    }

    // GET /api/my-bookings (Satisfies "Read" for Authenticated User)
    public function myBookings(Request $request)
    {
        $bookings = Booking::where('user_id', Auth::guard('sanctum')->id())
            ->with(['schedule.bus', 'schedule.origin', 'schedule.destination'])
            ->get();

        return response()->json(['status' => 'success', 'data' => $bookings]);
    }
}
