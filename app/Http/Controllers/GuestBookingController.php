<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GuestBookingController extends Controller
{
    /**
     * Show the search form for managing bookings.
     */
    public function search()
    {
        return view('guest.bookings.search');
    }

    /**
     * Find and display the booking details.
     */
    public function show(Request $request)
    {
        $request->validate([
            'booking_reference' => 'required|string',
            'email' => 'required|email',
        ]);

        // Validate Ownership
        // We check against the 'guest_email' column in the bookings table.
        $bookings = Booking::where('booking_reference', $request->booking_reference)
            ->where('guest_email', $request->email)
            ->with([
                'user',
                'schedule.bus',
                'schedule.origin',
                'schedule.destination',
                'returnSchedule.bus',
                'returnSchedule.origin',
                'returnSchedule.destination',
                'linkedBooking.schedule.bus',
                'linkedBooking.schedule.origin',
                'linkedBooking.schedule.destination',
            ])
            ->get();

        if ($bookings->isEmpty()) {
            return back()->with('error', 'Booking not found or email does not match.');
        }

        $booking = $bookings->firstWhere('trip_type', 'roundtrip_outbound')
            ?? $bookings->firstWhere('trip_type', 'oneway')
            ?? $bookings->first();

        $returnBooking = $bookings->firstWhere('trip_type', 'roundtrip_return');
        if ($returnBooking && !$booking->linkedBooking) {
            $booking->setRelation('linkedBooking', $returnBooking);
        }

        return view('guest.bookings.show', compact('booking'));
    }

    /**
     * Cancel the booking if eligible.
     */
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:255',
            'guest_email' => 'required|email', // Required for ownership verification
        ]);

        // SECURITY: Verify ownership via email to prevent IDOR attacks
        $booking = Booking::where('id', $id)
            ->where('guest_email', $request->guest_email)
            ->firstOrFail();

        // Check if 24 hours before departure
        $departureDate = $booking->schedule->departure_date;
        $departureTime = $booking->schedule->departure_time;

        // Combine date and time
        $tripStart = Carbon::parse("$departureDate $departureTime");
        $now = Carbon::now();

        if ($now->diffInHours($tripStart, false) < 24) {
            return back()->with('error', 'Cancellations are only allowed at least 24 hours before departure.');
        }

        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->cancellation_reason,
        ]);

        return back()->with('success', 'Booking cancelled successfully.');
    }
}
