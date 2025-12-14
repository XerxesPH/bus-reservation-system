<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Terminal;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TripController extends Controller
{
    public function search(Request $request)
    {
        // 1. Get Inputs
        $originId = $request->input('origin');
        $destinationId = $request->input('destination');
        $date = $request->input('date');

        // Passenger Counts
        $adults = $request->input('adults', 1);
        $children = $request->input('children', 0);

        // 2. Query Outbound Trips (Standard)
        $outboundTrips = Schedule::where('origin_id', $originId)
            ->where('destination_id', $destinationId)
            ->where('departure_date', $date)
            ->with(['bus', 'origin', 'destination'])
            ->get();

        // 3. Query Return Trips (If Round Trip selected)
        $returnTrips = collect(); // Default to empty
        if ($request->has('return_date') && $request->trip_type == 'roundtrip') {
            $returnTrips = Schedule::where('origin_id', $destinationId) // Swap Origin/Dest
                ->where('destination_id', $originId)
                ->where('departure_date', $request->return_date)
                ->with(['bus', 'origin', 'destination'])
                ->get();
        }

        // 4. Get Terminals for Display
        $origin = Terminal::find($originId);
        $destination = Terminal::find($destinationId);

        return view('trips.index', compact(
            'outboundTrips',
            'returnTrips',
            'origin',
            'destination',
            'date',
            'adults',
            'children'
        ));
    }

    public function selectSeats(Request $request)
    {
        $scheduleId = $request->query('schedule_id');
        $trip = Schedule::with('bus', 'origin', 'destination')->findOrFail($scheduleId);

        // Get seat info
        $occupiedSeats = Booking::where('schedule_id', $scheduleId)
            ->where('status', 'confirmed')
            ->pluck('seat_numbers')
            ->flatten()
            ->toArray();

        // Pass passenger counts if they exist in URL
        $adults = $request->query('adults', 1);
        $children = $request->query('children', 0);

        return view('trips.seats', compact('trip', 'occupiedSeats', 'adults', 'children'));
    }

    public function bookTicket(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email',
            'selected_seats' => 'required', // JSON string
            'adults' => 'required|integer|min:1',
            'children' => 'required|integer|min:0',
        ]);

        $seats = json_decode($request->selected_seats, true);
        $adults = (int) $request->adults;
        $children = (int) $request->children;
        $totalPax = $adults + $children;

        // Validation: Ensure seats matched selected passengers
        if (count($seats) !== $totalPax) {
            return back()->withErrors(['selected_seats' => "You selected $totalPax passengers but picked " . count($seats) . " seats."]);
        }

        return DB::transaction(function () use ($request, $seats, $adults, $children) {
            $trip = Schedule::with('bus')->lockForUpdate()->find($request->schedule_id);

            // 1. Double Booking Check
            $existingBookings = Booking::where('schedule_id', $trip->id)->where('status', 'confirmed')->get();
            $takenSeats = $existingBookings->pluck('seat_numbers')->flatten()->toArray();

            foreach ($seats as $seat) {
                if (in_array($seat, $takenSeats)) {
                    throw ValidationException::withMessages(['selected_seats' => "Seat $seat was just taken."]);
                }
            }

            // 2. Calculate Price (20% Discount for Children)
            // Example: Price is 500. Adult = 500. Child = 400.
            $adultPrice = $trip->price * $adults;
            $childPrice = ($trip->price * 0.8) * $children;
            $totalPrice = $adultPrice + $childPrice;

            // 3. Create Booking
            $booking = Booking::create([
                'schedule_id' => $trip->id,
                'user_id' => Auth::id(),
                'guest_name' => $request->guest_name,
                'guest_email' => $request->guest_email,
                'adults' => $adults,       // <--- Saved
                'children' => $children,   // <--- Saved
                'seat_numbers' => $seats,
                'total_price' => $totalPrice,
                'status' => 'confirmed'
            ]);

            return redirect()->route('booking.success', ['booking' => $booking->id]);
        });
    }

    public function showSuccess(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }
        return view('bookings.success', compact('booking'));
    }
}
