<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function myBookings()
    {
        // Fetch bookings for the CURRENT logged-in user only
        $bookings = Booking::where('user_id', Auth::id())
            ->with(['schedule.origin', 'schedule.destination', 'schedule.bus']) // Eager load relationships
            ->latest()
            ->get();

        return view('user.bookings', compact('bookings'));
    }
}
