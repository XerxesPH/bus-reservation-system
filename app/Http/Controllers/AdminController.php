<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Bus;
use App\Models\Terminal;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;

class AdminController extends Controller
{
    // 1. Show the Dashboard with Stats
    public function dashboard()
    {
        $totalRevenue = Booking::where('status', 'confirmed')->sum('total_price');
        $totalBookings = Booking::count();
        $totalUsers = User::where('role', 'user')->count();
        $todayTrips = Schedule::whereDate('departure_date', Carbon::today())->count();

        // Get recent bookings for the list
        $recentBookings = Booking::with(['schedule.origin', 'schedule.destination'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('totalRevenue', 'totalBookings', 'totalUsers', 'todayTrips', 'recentBookings'));
    }

    // 2. Show the Schedule Generator Form
    public function createSchedule()
    {
        $buses = Bus::all();
        $terminals = Terminal::all();
        return view('admin.create_schedule', compact('buses', 'terminals'));
    }

    // 3. Logic to Bulk Generate Schedules
    public function storeSchedule(Request $request)
    {
        $request->validate([
            'bus_id' => 'required',
            'origin_id' => 'required',
            'destination_id' => 'required|different:origin_id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'times' => 'required|array', // e.g., ['08:00', '12:00']
            'price' => 'required|numeric'
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $count = 0;

        // Loop through every day from Start to End
        while ($startDate->lte($endDate)) {

            // Loop through selected times (e.g., 8am, 12pm)
            foreach ($request->times as $time) {
                // Check if this specific bus is already scheduled for this date/time
                $exists = Schedule::where('bus_id', $request->bus_id)
                    ->where('departure_date', $startDate->format('Y-m-d'))
                    ->where('departure_time', $time)
                    ->exists();

                if (!$exists) {
                    Schedule::create([
                        'bus_id' => $request->bus_id,
                        'origin_id' => $request->origin_id,
                        'destination_id' => $request->destination_id,
                        'departure_date' => $startDate->format('Y-m-d'),
                        'departure_time' => $time,
                        'price' => $request->price
                    ]);
                }
                $count++;
            }

            $startDate->addDay(); // Go to next day
        }

        return redirect()->route('admin.dashboard')->with('success', "$count trips generated successfully!");
    }

    // --- BOOKING MANAGER ---

    public function bookings()
    {
        // Get all bookings, newest first, with pagination
        $bookings = Booking::with(['user', 'schedule.origin', 'schedule.destination'])
            ->latest()
            ->paginate(10); // Show 10 per page

        return view('admin.bookings', compact('bookings'));
    }

    public function cancelBooking($id)
    {
        $booking = Booking::findOrFail($id);

        // Update status to cancelled
        $booking->update(['status' => 'cancelled']);

        return back()->with('success', 'Booking #' . $id . ' has been cancelled. Seats are now free.');
    }

    public function deleteBus($id)
    {
        $bus = Bus::find($id);
        if ($bus->schedules()->count() > 0) {
            return back()->with('error', 'Cannot delete bus. It has existing schedules.');
        }
        $bus->delete();
    }

    // Show Edit Form
    public function editBus($id)
    {
        $bus = Bus::findOrFail($id);
        return view('admin.edit_bus', compact('bus'));
    }

    // Process Update
    public function updateBus(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|string',
            'type' => 'required|in:deluxe,regular',
            'capacity' => 'required|integer|min:10|max:60'
        ]);

        $bus = Bus::findOrFail($id);
        $bus->update($request->all());

        return redirect()->route('admin.dashboard')->with('success', 'Bus updated successfully!');
    }
}
