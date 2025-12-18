<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    /**
     * Download E-Ticket as PDF.
     *
     * @param string $bookingReference
     * @return \Illuminate\Http\Response
     */
    public function downloadTicket($bookingReference)
    {
        // Find the booking by reference
        $booking = Booking::where('booking_reference', $bookingReference)
            ->with(['schedule.origin', 'schedule.destination', 'schedule.bus', 'user'])
            ->firstOrFail();

        // SECURITY: Verify ownership
        // For authenticated users - check if booking belongs to them
        if (Auth::check() && $booking->user_id) {
            if ($booking->user_id !== Auth::id()) {
                abort(403, 'You are not authorized to download this ticket.');
            }
        }

        // For guest bookings, we rely on the booking reference being hard to guess
        // Additional security: Could require email verification via session

        // Check booking status - only allow confirmed bookings
        if ($booking->status === 'cancelled') {
            return back()->with('error', 'Cannot download ticket for a cancelled booking.');
        }

        // Generate PDF
        $pdf = Pdf::loadView('pdf.ticket', compact('booking'));

        // Set paper size (A5 landscape works well for tickets)
        $pdf->setPaper('a5', 'landscape');

        // Download the PDF
        $filename = 'ticket-' . $booking->booking_reference . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Stream/View E-Ticket as PDF in browser.
     *
     * @param string $bookingReference
     * @return \Illuminate\Http\Response
     */
    public function viewTicket($bookingReference)
    {
        // Find the booking by reference
        $booking = Booking::where('booking_reference', $bookingReference)
            ->with(['schedule.origin', 'schedule.destination', 'schedule.bus', 'user'])
            ->firstOrFail();

        // SECURITY: Verify ownership for authenticated users
        if (Auth::check() && $booking->user_id) {
            if ($booking->user_id !== Auth::id()) {
                abort(403, 'You are not authorized to view this ticket.');
            }
        }

        // Check booking status
        if ($booking->status === 'cancelled') {
            return back()->with('error', 'Cannot view ticket for a cancelled booking.');
        }

        // Generate PDF
        $pdf = Pdf::loadView('pdf.ticket', compact('booking'));
        $pdf->setPaper('a5', 'landscape');

        // Stream to browser (view inline)
        return $pdf->stream('ticket-' . $booking->booking_reference . '.pdf');
    }

    /**
     * Download E-Ticket for guest users (requires email verification).
     *
     * @param Request $request
     * @param string $bookingReference
     * @return \Illuminate\Http\Response
     */
    public function downloadGuestTicket(Request $request, $bookingReference)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Find the booking and verify email ownership
        $booking = Booking::where('booking_reference', $bookingReference)
            ->where('guest_email', $request->email)
            ->with(['schedule.origin', 'schedule.destination', 'schedule.bus'])
            ->first();

        if (!$booking) {
            return back()->with('error', 'Booking not found or email does not match.');
        }

        if ($booking->status === 'cancelled') {
            return back()->with('error', 'Cannot download ticket for a cancelled booking.');
        }

        // Generate PDF
        $pdf = Pdf::loadView('pdf.ticket', compact('booking'));
        $pdf->setPaper('a5', 'landscape');

        return $pdf->download('ticket-' . $booking->booking_reference . '.pdf');
    }
}
