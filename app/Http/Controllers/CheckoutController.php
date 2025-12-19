<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\PaymentMethod;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    /**
     * Step A: Prepare Checkout
     * Receives the seat selection from the final leg (One Way or Return Leg).
     * Stores data in session and redirects to the Checkout Page.
     */
    public function prepare(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'selected_seats' => 'required', // JSON string
            'trip_type' => 'required|in:oneway,roundtrip',
            'adults' => 'required|integer|min:1',
            'children' => 'required|integer|min:0',
        ]);

        $seats = json_decode($request->selected_seats, true);
        if (empty($seats)) {
            return back()->with('error', 'Please select at least one seat.');
        }

        $tripType = $request->trip_type;

        if ($tripType === 'roundtrip') {
            // This 'prepare' is called after selecting the RETURN leg.
            // We expect 'outbound_selection' to already exist in the session.
            if (!session()->has('outbound_selection')) {
                return redirect()->route('home')->with('error', 'Session expired. Please search again.');
            }

            $outboundData = session()->get('outbound_selection');

            // Structure the Checkout Payload
            $checkoutData = [
                'type' => 'roundtrip',
                'adults' => $request->adults,
                'children' => $request->children,
                'outbound' => $outboundData, // { schedule_id, seats, ... }
                'return' => [
                    'schedule_id' => $request->schedule_id,
                    'seats' => $seats,
                ]
            ];
        } else {
            // One Way Trip
            $checkoutData = [
                'type' => 'oneway',
                'adults' => $request->adults,
                'children' => $request->children,
                'outbound' => [
                    'schedule_id' => $request->schedule_id,
                    'seats' => $seats,
                ],
                'return' => null
            ];
        }

        session()->put('checkout_payload', $checkoutData);

        return redirect()->route('checkout.index');
    }

    /**
     * Step B: Show Checkout Page
     */
    public function index()
    {
        if (!session()->has('checkout_payload')) {
            return redirect()->route('home');
        }

        $data = session()->get('checkout_payload');

        // Load Models
        $outboundSchedule = Schedule::with(['bus', 'origin', 'destination'])->find($data['outbound']['schedule_id']);
        $returnSchedule = null;

        if ($data['type'] === 'roundtrip' && isset($data['return'])) {
            $returnSchedule = Schedule::with(['bus', 'origin', 'destination'])->find($data['return']['schedule_id']);
        }

        // Calculate Totals
        $adults = $data['adults'];
        $children = $data['children'];

        // Outbound Price
        $outboundPrice = ($outboundSchedule->price * $adults) + (($outboundSchedule->price * 0.8) * $children);

        // Return Price
        $returnPrice = 0;
        if ($returnSchedule) {
            $returnPrice = ($returnSchedule->price * $adults) + (($returnSchedule->price * 0.8) * $children);
        }

        $totalPrice = $outboundPrice + $returnPrice;

        // Payment Methods (If User is Logged In)
        $paymentMethods = collect();
        if (Auth::check()) {
            $paymentMethods = PaymentMethod::where('user_id', Auth::id())->get();
        }

        return view('checkout', compact(
            'data',
            'outboundSchedule',
            'returnSchedule',
            'outboundPrice',
            'returnPrice',
            'totalPrice',
            'paymentMethods'
        ));
    }

    /**
     * Step C: Process Payment & Store Booking
     * 
     * For ROUND TRIPS: Creates TWO separate booking records that SHARE
     * the same booking_reference for unified ticket management.
     */
    public function store(Request $request)
    {
        // FIX #4: Strict validation for phone and payment fields
        $rules = [
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email|max:255',
            // Philippine phone format: 10-11 digits, allows spaces/dashes
            'guest_phone' => ['required', 'regex:/^[\d\s\-\+]{10,15}$/'],
            'payment_method' => 'required|string',
        ];

        // Add credit card validation if paying with new card
        if ($request->payment_method === 'new_card') {
            $rules['card_number'] = ['required', 'regex:/^\d{16}$/'];
            $rules['card_expiry_month'] = 'required|integer|between:1,12';
            $rules['card_expiry_year'] = 'required|integer|min:' . date('Y');
            $rules['card_cvv'] = ['required', 'regex:/^\d{3,4}$/'];
        }

        $request->validate($rules, [
            'guest_phone.regex' => 'Phone number must be a valid Philippine mobile number (e.g., 09171234567).',
            'card_number.regex' => 'Card number must be exactly 16 digits.',
            'card_expiry_month.between' => 'Expiry month must be between 1 and 12.',
            'card_expiry_year.min' => 'Card has expired. Year must be ' . date('Y') . ' or later.',
            'card_cvv.regex' => 'CVV must be 3 or 4 digits.',
        ]);

        if (!session()->has('checkout_payload')) {
            return redirect()->route('home')->with('error', 'Session expired.');
        }

        $data = session()->get('checkout_payload');
        $userId = Auth::id();

        $paymentMethodStr = $request->payment_method;
        $paymentStatus = 'paid';

        $bookingReference = null;
        $outboundBookingId = null;

        try {
            DB::transaction(function () use ($request, $data, $userId, $paymentMethodStr, $paymentStatus, &$outboundBookingId, &$bookingReference) {

                // ========================================
                // GENERATE SINGLE BOOKING REFERENCE
                // ========================================
                $bookingReference = Booking::generateReference();

                // ========================================
                // 1. CREATE OUTBOUND BOOKING
                // ========================================

                // FIX #2: LOCK SCHEDULE ROW to prevent race condition
                $outboundSchedule = Schedule::with('bus')
                    ->where('id', $data['outbound']['schedule_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$outboundSchedule || !$outboundSchedule->bus) {
                    throw new \Exception('Schedule not found. Please search again.');
                }

                // FIX #1: TIME TRAVEL CHECK - Ensure schedule is in the future
                $departureDateTime = \Carbon\Carbon::parse(
                    $outboundSchedule->departure_date . ' ' . $outboundSchedule->departure_time
                );
                if ($departureDateTime->isPast()) {
                    throw new \Exception('This schedule has already departed. Please select a future trip.');
                }

                // FIX #2: CHECK SEAT AVAILABILITY (prevent double booking)
                $existingBookings = Booking::where('schedule_id', $outboundSchedule->id)
                    ->where('status', '!=', 'cancelled')
                    ->pluck('seat_numbers')
                    ->flatten()
                    ->toArray();

                $requestedSeats = $data['outbound']['seats'];
                $conflictingSeats = array_intersect($requestedSeats, $existingBookings);

                if (!empty($conflictingSeats)) {
                    throw new \Exception('Seats ' . implode(', ', $conflictingSeats) . ' are no longer available. Please select different seats.');
                }

                // FIX #5: SERVER-SIDE PRICE CALCULATION (ignore any client-submitted price)
                $outboundTotal = ($outboundSchedule->price * $data['adults']) + (($outboundSchedule->price * 0.8) * $data['children']);

                $outboundBooking = Booking::create([
                    'user_id' => $userId,
                    'schedule_id' => $outboundSchedule->id,
                    'bus_number' => $outboundSchedule->bus->code
                        ?? $outboundSchedule->bus->bus_number
                        ?? $outboundSchedule->bus->name
                        ?? null,
                    'seat_numbers' => $data['outbound']['seats'],
                    'adults' => $data['adults'],
                    'children' => $data['children'],
                    'total_price' => $outboundTotal,
                    'status' => 'confirmed',
                    'guest_name' => $request->guest_name,
                    'guest_email' => $request->guest_email,
                    'guest_phone' => $request->guest_phone,
                    'payment_method' => $paymentMethodStr,
                    'payment_status' => $paymentStatus,
                    'trip_type' => $data['type'] === 'roundtrip' ? 'roundtrip_outbound' : 'oneway',
                    'booking_reference' => $bookingReference,
                ]);

                $outboundBookingId = $outboundBooking->id;

                // ========================================
                // 2. CREATE RETURN BOOKING (If Round Trip)
                // ========================================
                if ($data['type'] === 'roundtrip' && isset($data['return'])) {
                    // FIX #2: LOCK RETURN SCHEDULE ROW
                    $returnSchedule = Schedule::with('bus')
                        ->where('id', $data['return']['schedule_id'])
                        ->lockForUpdate()
                        ->first();

                    if (!$returnSchedule || !$returnSchedule->bus) {
                        throw new \Exception('Return schedule not found. Please search again.');
                    }

                    // FIX #1: TIME TRAVEL CHECK for return trip
                    $returnDateTime = \Carbon\Carbon::parse(
                        $returnSchedule->departure_date . ' ' . $returnSchedule->departure_time
                    );
                    if ($returnDateTime->isPast()) {
                        throw new \Exception('Return schedule has already departed.');
                    }

                    // FIX #1: Ensure return date is after outbound date
                    if ($returnDateTime->lessThanOrEqualTo($departureDateTime)) {
                        throw new \Exception('Return trip must be after the outbound trip.');
                    }

                    // FIX #2: CHECK SEAT AVAILABILITY for return
                    $existingReturnBookings = Booking::where('schedule_id', $returnSchedule->id)
                        ->where('status', '!=', 'cancelled')
                        ->pluck('seat_numbers')
                        ->flatten()
                        ->toArray();

                    $requestedReturnSeats = $data['return']['seats'];
                    $conflictingReturnSeats = array_intersect($requestedReturnSeats, $existingReturnBookings);

                    if (!empty($conflictingReturnSeats)) {
                        throw new \Exception('Return seats ' . implode(', ', $conflictingReturnSeats) . ' are no longer available.');
                    }

                    // FIX #5: SERVER-SIDE PRICE for return
                    $returnTotal = ($returnSchedule->price * $data['adults']) + (($returnSchedule->price * 0.8) * $data['children']);

                    $returnBooking = Booking::create([
                        'user_id' => $userId,
                        'schedule_id' => $returnSchedule->id,
                        'bus_number' => $returnSchedule->bus->code
                            ?? $returnSchedule->bus->bus_number
                            ?? $returnSchedule->bus->name
                            ?? null,
                        'seat_numbers' => $data['return']['seats'],
                        'adults' => $data['adults'],
                        'children' => $data['children'],
                        'total_price' => $returnTotal,
                        'status' => 'confirmed',
                        'guest_name' => $request->guest_name,
                        'guest_email' => $request->guest_email,
                        'guest_phone' => $request->guest_phone,
                        'payment_method' => $paymentMethodStr,
                        'payment_status' => $paymentStatus,
                        'trip_type' => 'roundtrip_return',
                        'linked_booking_id' => $outboundBooking->id,
                        'booking_reference' => $bookingReference,
                    ]);

                    // Link outbound to return for bidirectional reference
                    $outboundBooking->update(['linked_booking_id' => $returnBooking->id]);
                }

                session()->put('last_booking_id', $outboundBookingId);
                session()->put('last_booking_reference', $bookingReference);

                // ========================================
                // FIX #3: SYNC PHONE TO USER PROFILE
                // Save contact number to user profile for future bookings
                // ========================================
                if ($userId && $request->guest_phone) {
                    $user = \App\Models\User::find($userId);
                    if ($user && empty($user->contact_number)) {
                        $user->update(['contact_number' => $request->guest_phone]);
                    }
                }
            });
        } catch (\Exception $e) {
            // Clear session to prevent loop and redirect to home with error
            session()->forget(['checkout_payload', 'outbound_selection']);
            return redirect()->route('home')->with('error', $e->getMessage());
        }

        // Clear Session
        session()->forget(['checkout_payload', 'outbound_selection']);

        // Guests should NOT be redirected to auth-only pages.
        // Send guests to the public success page; authenticated users can go to My Bookings.
        if (!Auth::check()) {
            return redirect()->route('booking.verifying', ['booking' => $outboundBookingId]);
        }

        return redirect()->route('user.bookings')->with('success', "Booking confirmed! Your reference is: {$bookingReference}");
    }
}
