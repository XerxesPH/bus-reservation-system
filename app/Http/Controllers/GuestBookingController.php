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
        $booking = Booking::where('booking_reference', $request->booking_reference)
            ->where('guest_email', $request->email)
            ->with(['schedule.bus', 'schedule.origin', 'schedule.destination', 'returnSchedule.bus', 'returnSchedule.origin', 'returnSchedule.destination'])
            ->first();

        if (!$booking) {
            return back()->with('error', 'Booking not found or email does not match.');
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
        ]);

        $booking = Booking::findOrFail($id);

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
