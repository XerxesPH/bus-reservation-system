<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Terminal;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // <--- ADDED THIS
use Illuminate\Validation\ValidationException; // <--- ADDED FOR BETTER ERRORS

class TripController extends Controller
{
    public function search(Request $request)
    {
        // 1. Get Inputs
        $originId = $request->input('origin');
        $destinationId = $request->input('destination');
        $date = $request->input('date');
        $pax = $request->input('pax', 1); // Default to 1 person

        // 2. Find Schedules that match the route and date
        $schedules = Schedule::where('origin_id', $originId)
            ->where('destination_id', $destinationId)
            ->where('departure_date', $date)
            ->with(['bus', 'origin', 'destination']) // Eager load data
            ->get();

        // 3. FILTER: Only keep buses with enough seats
        // We use the filter() method to check availability one by one
        $availableTrips = $schedules->filter(function ($trip) use ($pax) {

            // Count how many seats are already confirmed/booked
            $bookedSeats = Booking::where('schedule_id', $trip->id)
                ->where('status', 'confirmed')
                ->get()
                ->pluck('seat_numbers')
                ->flatten()
                ->count();

            // Calculate seats left
            $seatsLeft = $trip->bus->capacity - $bookedSeats;

            // Save this number so we can show it in the view later!
            $trip->seats_left = $seatsLeft;

            // Only return true (keep this trip) if there is enough space
            return $seatsLeft >= $pax;
        });

        // 4. Get Terminal names for display
        $origin = Terminal::find($originId);
        $destination = Terminal::find($destinationId);

        return view('trips.index', [
            'trips' => $availableTrips,
            'origin' => $origin,
            'destination' => $destination,
            'date' => $date,
            'pax' => $pax
        ]);
    }

    public function selectSeats(Request $request)
    {
        $scheduleId = $request->query('schedule_id');
        $trip = Schedule::with('bus', 'origin', 'destination')->findOrFail($scheduleId);

        $occupiedSeats = Booking::where('schedule_id', $scheduleId)
            ->where('status', 'confirmed')
            ->pluck('seat_numbers') // Assumes model casts this to array/json
            ->flatten()
            ->toArray();

        return view('trips.seats', compact('trip', 'occupiedSeats'));
    }

    public function bookTicket(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email',
            'selected_seats' => 'required', // JSON string
        ]);

        // Decode and force it to be an array
        $seats = json_decode($request->selected_seats, true);

        if (empty($seats) || !is_array($seats)) {
            return back()->withErrors(['selected_seats' => 'Please select at least one seat.']);
        }

        return DB::transaction(function () use ($request, $seats) {
            // 1. Lock the row to prevent race conditions
            // We also load the 'bus' relationship here to check capacity later
            $trip = Schedule::with('bus')->lockForUpdate()->find($request->schedule_id);

            // --- SAFETY CHECK 1: Validate Seat Capacity ---
            $maxCapacity = $trip->bus->capacity; // Assuming your Bus model has a 'capacity' column
            foreach ($seats as $seat) {
                if ($seat > $maxCapacity || $seat < 1) {
                    throw ValidationException::withMessages([
                        'selected_seats' => "Seat #$seat does not exist on this bus."
                    ]);
                }
            }
            // ----------------------------------------------

            // 2. Check for double bookings (The Race Condition Check)
            $existingBookings = Booking::where('schedule_id', $trip->id)
                ->where('status', 'confirmed')
                ->get();

            $takenSeats = $existingBookings->pluck('seat_numbers')->flatten()->toArray();

            foreach ($seats as $seat) {
                if (in_array($seat, $takenSeats)) {
                    throw ValidationException::withMessages([
                        'selected_seats' => "Seat $seat was just taken by another user. Please choose another."
                    ]);
                }
            }

            // 3. Calculate Total Price
            $totalPrice = $trip->price * count($seats);

            // 4. Save Booking
            $booking = Booking::create([
                'schedule_id' => $trip->id,
                'user_id' => Auth::id(), // Returns NULL if guest. Ensure your DB 'user_id' column is nullable!
                'guest_name' => $request->guest_name,
                'guest_email' => $request->guest_email,
                'seat_numbers' => $seats,
                'total_price' => $totalPrice,
                'status' => 'confirmed'
            ]);

            return redirect()->route('booking.success', ['booking' => $booking->id]);
        });
    }

    public function showSuccess(Booking $booking)
    {
        // SECURITY FIX: Ensure the user owns this booking
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('bookings.success', compact('booking'));
    }
}
