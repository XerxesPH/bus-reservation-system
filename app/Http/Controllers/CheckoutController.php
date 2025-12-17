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
     */
    public function store(Request $request)
    {
        $request->validate([
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email|max:255',
            'guest_phone' => 'required|string|max:20',
            'payment_method' => 'required|string', // 'saved_method_ID' or 'new_card' or 'new_ewallet'
        ]);

        if (!session()->has('checkout_payload')) {
            return redirect()->route('home')->with('error', 'Session expired.');
        }

        $data = session()->get('checkout_payload');
        $userId = Auth::id(); // Null if guest

        // Determine Payment Details
        $paymentMethodStr = $request->payment_method;
        $paymentStatus = 'paid'; // Simulating successful payment for prototype

        DB::transaction(function () use ($request, $data, $userId, $paymentMethodStr, $paymentStatus) {

            $outboundSchedule = Schedule::find($data['outbound']['schedule_id']);
            $outboundTotal = ($outboundSchedule->price * $data['adults']) + (($outboundSchedule->price * 0.8) * $data['children']);

            $returnScheduleId = null;
            $returnSeatNumbers = null;
            $returnTotal = 0;

            if ($data['type'] === 'roundtrip' && isset($data['return'])) {
                $returnSchedule = Schedule::find($data['return']['schedule_id']);
                $returnTotal = ($returnSchedule->price * $data['adults']) + (($returnSchedule->price * 0.8) * $data['children']);

                $returnScheduleId = $returnSchedule->id;
                $returnSeatNumbers = $data['return']['seats'];
            }

            $booking = Booking::create([
                'user_id' => $userId,
                'schedule_id' => $outboundSchedule->id,
                'seat_numbers' => $data['outbound']['seats'],
                'return_schedule_id' => $returnScheduleId,
                'return_seat_numbers' => $returnSeatNumbers,
                'adults' => $data['adults'],
                'children' => $data['children'],
                'total_price' => $outboundTotal + $returnTotal,
                'status' => 'confirmed',
                'guest_name' => $request->guest_name,
                'guest_email' => $request->guest_email,
                'guest_phone' => $request->guest_phone,
                'payment_method' => $paymentMethodStr,
                'payment_status' => $paymentStatus,
            ]);

            // Store main booking ID for success page
            session()->put('last_booking_id', $booking->id);
        });

        // Clear Session
        session()->forget(['checkout_payload', 'outbound_selection']);

        // Redirect
        $bookingId = session()->get('last_booking_id');
        return redirect()->route('booking.success', ['booking' => $bookingId]);
    }
}
