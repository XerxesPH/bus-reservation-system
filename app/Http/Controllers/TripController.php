<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Schedule;
use App\Models\Terminal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TripController extends Controller
{
    public function search(Request $request)
    {
        $step = $request->query('step', 1);

        // === VALIDATION: Check for Return Trip Availability (Round Trip Only) ===
        // This ensures we don't let the user select an outbound bus if they can't return.
        if ($request->input('trip_type') === 'roundtrip' && $step == 1) {
            $requiredSeats = $request->input('adults') + $request->input('children');

            // 1. Check Return Schedule Availability (Seats)
            $returnSchedules = Schedule::where('origin_id', $request->input('return_origin'))
                ->where('destination_id', $request->input('return_destination'))
                ->whereDate('departure_date', $request->input('return_date'))
                ->where('status', 'scheduled')
                ->when($request->input('return_date') == \Carbon\Carbon::today()->toDateString(), function ($query) {
                    $query->whereTime('departure_time', '>', \Carbon\Carbon::now()->format('H:i:s'));
                })
                ->get();

            $hasAvailableReturn = $returnSchedules->contains(function ($schedule) use ($requiredSeats) {
                return $schedule->seats_left >= $requiredSeats;
            });

            if ($returnSchedules->isEmpty()) {
                return redirect()->route('home')->with('error', 'No return buses found for your selected return date. Please choose another date.');
            }

            if (! $hasAvailableReturn) {
                return redirect()->route('home')->with('error', 'All return buses are fully booked for your selected date. Please choose another date.');
            }

            // 2. Check Outbound Schedule Availability (Seats)
            $outboundSchedules = Schedule::where('origin_id', $request->input('origin'))
                ->where('destination_id', $request->input('destination'))
                ->whereDate('departure_date', $request->input('date'))
                ->where('status', 'scheduled')
                ->when($request->input('date') == \Carbon\Carbon::today()->toDateString(), function ($query) {
                    $query->whereTime('departure_time', '>', \Carbon\Carbon::now()->format('H:i:s'));
                })
                ->get();

            $hasAvailableOutbound = $outboundSchedules->contains(function ($schedule) use ($requiredSeats) {
                return $schedule->seats_left >= $requiredSeats;
            });

            if ($outboundSchedules->isEmpty()) {
                return redirect()->route('home')->with('error', 'No outbound buses found for your selected date. Please choose another date.');
            }

            if (! $hasAvailableOutbound) {
                return redirect()->route('home')->with('error', 'All outbound buses are fully booked for your selected date. Please choose another date.');
            }
        }

        $originId = $request->input('origin');
        $destinationId = $request->input('destination');
        $date = $request->input('date');

        if ($step == 2) {
            $searchOrigin = $request->input('return_origin') ?? $destinationId;
            $searchDest = $request->input('return_destination') ?? $originId;
            $searchDate = $request->input('return_date');

            $headerTitle = "Select Return Trip";
            $stepLabel = "2";
        } else {
            // STEP 1: New Search
            // CRITICAL FIX: Clear any previous partial bookings (e.g., from an abandoned round trip)
            session()->forget('outbound_selection');

            $searchOrigin = $originId;
            $searchDest = $destinationId;
            $searchDate = $date;

            $headerTitle = "Select Outbound Trip";
            $stepLabel = "1";
        }

        // Use whereDate for robust date comparison
        $trips = Schedule::where('origin_id', $searchOrigin)
            ->where('destination_id', $searchDest)
            ->whereDate('departure_date', $searchDate)
            ->where('status', 'scheduled') // Only show active schedules
            ->when($searchDate == \Carbon\Carbon::today()->toDateString(), function ($query) {
                $query->whereTime('departure_time', '>', \Carbon\Carbon::now()->format('H:i:s'));
            })
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

        // === CRITICAL FIX START ===
        // We override the 'leg' parameter based on the Trip Type and Step.
        // This prevents the system from accidentally booking a One Way ticket
        // when the user intends to book a Round Trip.

        $tripType = $request->query('trip_type', 'oneway');
        $step = (int) $request->query('step', 1); // Default to Step 1

        if ($tripType == 'roundtrip') {
            if ($step == 1) {
                // If it's Round Trip Step 1, it MUST be outbound
                $leg = 'outbound';
            } else {
                // If it's Round Trip Step 2 (or higher), it's return
                $leg = 'return';
            }
        } else {
            // Default logic for One Way
            $leg = 'oneway';
        }
        // === CRITICAL FIX END ===

        $adults = (int) $request->query('adults', 1);
        $children = (int) $request->query('children', 0);

        // Bundle search params to pass to view (prevents data loss)
        $searchParams = [
            'trip_type' => $tripType,
            'return_date' => $request->query('return_date'),
            'return_origin' => $request->query('return_origin'),
            'return_destination' => $request->query('return_destination'),
            'origin' => $request->query('origin'),
            'destination' => $request->query('destination'),
            'date' => $request->query('date'),
            'adults' => $adults,
            'children' => $children,
        ];

        // Get Occupied Seats
        $occupiedSeats = Booking::where('schedule_id', $scheduleId)->where('status', 'confirmed')->pluck('seat_numbers')->flatten();
        $returnBookings = Booking::where('return_schedule_id', $scheduleId)->where('status', 'confirmed')->pluck('return_seat_numbers')->flatten();
        $occupiedSeats = $occupiedSeats->merge($returnBookings)->unique()->toArray();

        return view('trips.seats', compact('trip', 'occupiedSeats', 'leg', 'adults', 'children', 'searchParams'));
    }

    public function storeOutbound(Request $request)
    {
        // 1. Validate
        $request->validate([
            'schedule_id' => 'required',
            'selected_seats' => 'required',
            'return_date' => 'required',
        ]);

        // === DOUBLE CHECK VALIDATION: Verify Return Trip Availability ===
        // This prevents the user from landing on Step 2 (Result URL) if the return leg is invalid.
        if ($request->input('trip_type') === 'roundtrip') {
            $request->validate([
                'return_origin' => 'required',
                'return_destination' => 'required',
            ]);

            $requiredSeats = $request->input('adults') + $request->input('children');

            $returnSchedules = Schedule::where('origin_id', $request->input('return_origin'))
                ->where('destination_id', $request->input('return_destination'))
                ->whereDate('departure_date', $request->input('return_date'))
                ->where('status', 'scheduled')
                ->get();

            $hasAvailableReturn = $returnSchedules->contains(function ($schedule) use ($requiredSeats) {
                return $schedule->seats_left >= $requiredSeats;
            });

            if ($returnSchedules->isEmpty()) {
                return redirect()->route('home')->with('error', 'The selected return trip is no longer available. Please search again.');
            }

            if (! $hasAvailableReturn) {
                return redirect()->route('home')->with('error', 'All return buses are fully booked. Please search again.');
            }
        }

        $seats = json_decode($request->selected_seats, true);

        // 2. Save to Session
        session()->put('outbound_selection', [
            'schedule_id' => $request->schedule_id,
            'seats' => $seats,
            'adults' => $request->adults,
            'children' => $request->children,
        ]);

        // 3. Redirect to Search (Step 2)
        return redirect()->route('trips.search', [
            'step' => 2,
            'trip_type' => 'roundtrip', // Ensure we stay in Round Trip mode
            'date' => $request->return_date, // Search for the Return Date (Primary Date for Step 2)
            'return_date' => $request->return_date, // Explicitly pass return_date for validation logic
            'return_origin' => $request->return_origin,
            'return_destination' => $request->return_destination,
            // Keep original params for context
            'origin' => $request->original_origin,
            'destination' => $request->original_destination,
            'original_date' => $request->original_date,
            'adults' => $request->adults,
            'children' => $request->children,
        ]);
    }

    public function bookTicket(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required',
            'selected_seats' => 'required',
        ]);

        $booking = DB::transaction(function () use ($request) {
            // FIX: Check intent explicitly.
            // Only proceed with Round Trip logic if the Session exists AND the User intentionally requested a Round Trip.
            $isRoundTripSession = session()->has('outbound_selection');
            $isRoundTripIntent = $request->input('trip_type') === 'roundtrip';

            if ($isRoundTripSession && $isRoundTripIntent) {
                // ROUND TRIP BOOKING
                $outboundData = session()->get('outbound_selection');
                $outboundTrip = Schedule::find($outboundData['schedule_id']);
                $outboundPrice = ($outboundTrip->price * $outboundData['adults']) + (($outboundTrip->price * 0.8) * $outboundData['children']);

                $returnTrip = Schedule::find($request->schedule_id);
                $returnSeats = json_decode($request->selected_seats, true);
                $returnPrice = ($returnTrip->price * $request->adults) + (($returnTrip->price * 0.8) * $request->children);

                $newBooking = Booking::create([
                    'schedule_id' => $outboundTrip->id,
                    'seat_numbers' => $outboundData['seats'],
                    'return_schedule_id' => $returnTrip->id,
                    'return_seat_numbers' => $returnSeats,
                    'user_id' => Auth::id(),
                    'guest_name' => $outboundData['guest_name'],
                    'guest_email' => $outboundData['guest_email'],
                    'adults' => $outboundData['adults'],
                    'children' => $outboundData['children'],
                    'total_price' => $outboundPrice + $returnPrice,
                    'status' => 'confirmed',
                ]);

                session()->forget('outbound_selection');
                return $newBooking;
            } else {
                // ONE WAY BOOKING
                // If this runs, we ignore any 'outbound_selection' in the session because the user wanted a One Way trip.
                $trip = Schedule::find($request->schedule_id);
                $seats = json_decode($request->selected_seats, true);
                $totalPrice = ($trip->price * $request->adults) + (($trip->price * 0.8) * $request->children);

                // Explicitly clear any stale session data just to be clean
                session()->forget('outbound_selection');

                return Booking::create([
                    'schedule_id' => $trip->id,
                    'user_id' => Auth::id(),
                    'guest_name' => $request->guest_name,
                    'guest_email' => $request->guest_email,
                    'seat_numbers' => $seats,
                    'total_price' => $totalPrice,
                    'status' => 'confirmed',
                    'adults' => $request->adults,
                    'children' => $request->children,
                ]);
            }
        });

        return redirect()->route('booking.success', ['booking' => $booking->id]);
    }

    public function showSuccess(Booking $booking)
    {
        $booking->load(['user', 'schedule.bus', 'schedule.origin', 'schedule.destination', 'returnSchedule.bus', 'returnSchedule.origin', 'returnSchedule.destination']);
        return view('bookings.success', compact('booking'));
    }
}
