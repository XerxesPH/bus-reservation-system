<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Schedule;
use App\Models\Terminal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TripController extends Controller
{
    public function search(Request $request)
    {
        // === STEP CHECKER ===
        // If "step" is 2, we are looking for the return trip.
        // If "step" is missing or 1, we are looking for the outbound trip.
        $step = $request->query('step', 1);

        $originId = $request->input('origin');
        $destinationId = $request->input('destination');
        $date = $request->input('date');

        // Logic for Return Trip Search (Step 2)
        if ($step == 2) {
            // If we are in Step 2, the "Origin" is actually the destination of the first leg
            // unless the user specified a custom return route.
            $searchOrigin = $request->input('return_origin') ?? $destinationId;
            $searchDest = $request->input('return_destination') ?? $originId;
            $searchDate = $request->input('return_date');

            $headerTitle = "Select Return Trip";
            $stepLabel = "2";
        } else {
            // Step 1: Normal Outbound
            $searchOrigin = $originId;
            $searchDest = $destinationId;
            $searchDate = $date;

            $headerTitle = "Select Outbound Trip";
            $stepLabel = "1";
        }

        // Query the Trips
        $trips = Schedule::where('origin_id', $searchOrigin)
            ->where('destination_id', $searchDest)
            ->where('departure_date', $searchDate)
            ->with(['bus', 'origin', 'destination'])
            ->get();

        $origin = Terminal::find($searchOrigin);
        $destination = Terminal::find($searchDest);

        return view('trips.index', compact(
            'trips',
            'origin',
            'destination',
            'step',
            'headerTitle',
            'stepLabel'
        ));
    }

    public function selectSeats(Request $request)
    {
        $scheduleId = $request->query('schedule_id');
        $trip = Schedule::with('bus', 'origin', 'destination')->findOrFail($scheduleId);

        // 1. Get the 'leg' (outbound or return)
        $leg = $request->query('leg', 'outbound');

        // 2. FIX: Retrieve Passenger Counts from URL
        // The view needs these to calculate prices and limit seat selection
        $adults = (int) $request->query('adults', 1);
        $children = (int) $request->query('children', 0);

        // 3. Get Occupied Seats
        $occupiedSeats = Booking::where('schedule_id', $scheduleId)
            ->where('status', 'confirmed')
            ->get()
            ->pluck('seat_numbers')
            ->flatten()
            ->toArray();

        $outboundBookings = Booking::where('schedule_id', $scheduleId)
            ->where('status', 'confirmed')
            ->pluck('seat_numbers')
            ->flatten();

        $returnBookings = Booking::where('return_schedule_id', $scheduleId)
            ->where('status', 'confirmed')
            ->pluck('return_seat_numbers') // Note: Fetching from return_seat_numbers column
            ->flatten();

        $occupiedSeats = $outboundBookings->merge($returnBookings)->unique()->toArray();
        // 4. Pass $adults and $children to the view
        return view('trips.seats', compact('trip', 'occupiedSeats', 'leg', 'adults', 'children'));
    }

    // === NEW FUNCTION: Stores the first choice in Session ===
    public function storeOutbound(Request $request)
    {
        // 1. Validate
        $request->validate([
            'schedule_id' => 'required',
            'selected_seats' => 'required', // JSON
            'return_date' => 'required', // Needed for next step
        ]);

        $seats = json_decode($request->selected_seats, true);

        // 2. Save to Session
        session()->put('outbound_selection', [
            'schedule_id' => $request->schedule_id,
            'seats' => $seats,
            'adults' => $request->adults,
            'children' => $request->children,
            'guest_name' => $request->guest_name,
            'guest_email' => $request->guest_email,
        ]);

        // 3. Redirect to Search (Step 2)
        // We pass the parameters needed for the Return Search
        return redirect()->route('trips.search', [
            'step' => 2,
            'date' => $request->input('original_date'), // keep original date for reference
            'origin' => $request->input('original_origin'), // keep original origin
            'destination' => $request->input('original_destination'), // keep original dest
            'return_date' => $request->return_date,
            'return_origin' => $request->return_origin,
            'return_destination' => $request->return_destination,
            'trip_type' => 'roundtrip',
            'adults' => $request->adults,
            'children' => $request->children,
        ]);
    }

    // === FINAL FUNCTION: Books the ticket (Single or Merged) ===
    public function bookTicket(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required',
            'selected_seats' => 'required',
        ]);

        $booking = DB::transaction(function () use ($request) {

            // Check if there is a PREVIOUS trip in Session (this makes it a Round Trip)
            if (session()->has('outbound_selection')) {
                // --- ROUND TRIP LOGIC ---

                // 1. Get Outbound data from Session
                $outboundData = session()->get('outbound_selection');
                $outboundTrip = Schedule::find($outboundData['schedule_id']);
                $outboundSeats = $outboundData['seats'];
                $outboundPrice = ($outboundTrip->price * $outboundData['adults']) +
                    (($outboundTrip->price * 0.8) * $outboundData['children']);

                // 2. Get Return data from current Request
                $returnTrip = Schedule::find($request->schedule_id);
                $returnSeats = json_decode($request->selected_seats, true);
                $returnPrice = ($returnTrip->price * $request->adults) +
                    (($returnTrip->price * 0.8) * $request->children);

                // 3. Create a SINGLE booking record
                $newBooking = Booking::create([
                    // Outbound details
                    'schedule_id' => $outboundTrip->id,
                    'seat_numbers' => $outboundSeats,

                    // Return details
                    'return_schedule_id' => $returnTrip->id,
                    'return_seat_numbers' => $returnSeats,

                    // Passenger & pricing details
                    'user_id' => Auth::id(),
                    'guest_name' => $outboundData['guest_name'], // Use guest name from first leg
                    'guest_email' => $outboundData['guest_email'],
                    'adults' => $outboundData['adults'],
                    'children' => $outboundData['children'],
                    'total_price' => $outboundPrice + $returnPrice, // Combined price
                    'status' => 'confirmed',
                ]);

                // 4. Clear Session
                session()->forget('outbound_selection');

                return $newBooking;
            } else {
                // --- ONE-WAY TRIP LOGIC ---

                // 1. Get trip data from current Request
                $trip = Schedule::find($request->schedule_id);
                $seats = json_decode($request->selected_seats, true);
                $adults = $request->adults;
                $children = $request->children;

                // 2. Calculate Price
                $totalPrice = ($trip->price * $adults) + (($trip->price * 0.8) * $children);

                // 3. Create the booking
                return Booking::create([
                    'schedule_id' => $trip->id,
                    'user_id' => Auth::id(),
                    'guest_name' => $request->guest_name,
                    'guest_email' => $request->guest_email,
                    'seat_numbers' => $seats,
                    'total_price' => $totalPrice,
                    'status' => 'confirmed',
                    'adults' => $adults,
                    'children' => $children,
                ]);
            }
        });

        return redirect()->route('booking.success', ['booking' => $booking->id]);
    }

    public function showSuccess(Booking $booking)
    {
        // Eager load all the relationships needed for the ticket view
        $booking->load([
            'user',
            'schedule.bus',
            'schedule.origin',
            'schedule.destination',
            'returnSchedule.bus',
            'returnSchedule.origin',
            'returnSchedule.destination'
        ]);

        return view('bookings.success', compact('booking'));
    }
}
