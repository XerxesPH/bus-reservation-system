<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Bus;
use App\Models\Schedule;
use App\Models\ScheduleTemplate;
use App\Models\Terminal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // ... existing dashboard ...
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
            'active_days' => 'required|array', // e.g., ['Mon', 'Wed']
            'excluded_dates' => 'nullable|string',
            'price' => 'required|numeric',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $count = 0;

        // Parse excluded dates
        $excludedDates = [];
        if ($request->excluded_dates) {
            $rawDates = explode(',', $request->excluded_dates);
            foreach ($rawDates as $d) {
                try {
                    $excludedDates[] = Carbon::parse(trim($d))->format('Y-m-d');
                } catch (\Exception $e) {
                    // Ignore invalid dates
                }
            }
        }

        // Loop through every day from Start to End
        while ($startDate->lte($endDate)) {

            $currentDateStr = $startDate->format('Y-m-d');

            // CHECK 1: Is this date in the "Excluded Dates" list?
            if (in_array($currentDateStr, $excludedDates)) {
                $startDate->addDay();
                continue;
            }

            // CHECK 2: Is this day in the allowed "Active Days"?
            // Carbon format 'D' returns Mon, Tue, etc.
            if (! in_array($startDate->format('D'), $request->active_days)) {
                $startDate->addDay();
                continue; // Skip this day
            }

            // Loop through selected times (e.g., 8am, 12pm)
            foreach ($request->times as $time) {
                // Check if this specific bus is already scheduled for this date/time
                $exists = Schedule::where('bus_id', $request->bus_id)
                    ->where('departure_date', $startDate->format('Y-m-d'))
                    ->where('departure_time', $time)
                    ->exists();

                if (! $exists) {
                    Schedule::create([
                        'bus_id' => $request->bus_id,
                        'origin_id' => $request->origin_id,
                        'destination_id' => $request->destination_id,
                        'departure_date' => $startDate->format('Y-m-d'),
                        'departure_time' => $time,
                        'price' => $request->price,
                    ]);
                }
                $count++;
            }

            $startDate->addDay(); // Go to next day
        }

        return redirect()->route('admin.dashboard')->with('success', "$count trips generated successfully!");
    }

    // 4. Schedule Management List
    public function schedules()
    {
        // Get schedules ordered by date and time
        // Pagination is important as there could be many
        $schedules = Schedule::with(['bus', 'origin', 'destination'])
            ->orderBy('departure_date', 'desc')
            ->orderBy('departure_time', 'asc')
            ->paginate(15);

        return view('admin.schedules', compact('schedules'));
    }

    // 5. Cancel a Specific Schedule
    public function cancelSchedule($id)
    {
        $schedule = Schedule::withCount(['bookings' => function ($query) {
            $query->where('status', 'confirmed');
        }])->findOrFail($id);

        if ($schedule->bookings_count > 0) {
            return back()->with('error', 'Cannot cancel this trip. It has ' . $schedule->bookings_count . ' active booking(s).');
        }

        // Update status to cancelled
        $schedule->update(['status' => 'cancelled']);

        return back()->with('success', 'Trip successfully cancelled.');
    }

    // 6. Bus Fleet Management
    public function buses()
    {
        $buses = Bus::paginate(10);
        return view('admin.buses', compact('buses'));
    }

    public function createBus()
    {
        return view('admin.buses.create');
    }

    public function storeBus(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:buses,code',
            'type' => 'required|in:deluxe,regular',
            'capacity' => 'required|integer|min:10|max:60',
            'driver_name' => 'required|string',
            'driver_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('driver_image')) {
            $path = $request->file('driver_image')->store('drivers', 'public');
            $data['driver_image'] = $path;
        }

        Bus::create($data);

        return redirect()->route('admin.buses')->with('success', 'Bus added successfully!');
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

        return back()->with('success', 'Bus deleted successfully.');
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
            'capacity' => 'required|integer|min:10|max:60',
        ]);

        $bus = Bus::findOrFail($id);
        $bus->update($request->all());

        return redirect()->route('admin.dashboard')->with('success', 'Bus updated successfully!');
    }

    // ==========================================
    // 7. TEMPLATE MANAGEMENT (AUTOMATION)
    // ==========================================

    public function templates()
    {
        $templates = ScheduleTemplate::with(['bus', 'origin', 'destination'])->paginate(10);
        return view('admin.templates.index', compact('templates'));
    }

    public function createTemplate()
    {
        $buses = Bus::all();
        $terminals = Terminal::all();
        return view('admin.templates.create', compact('buses', 'terminals'));
    }

    public function storeTemplate(Request $request)
    {
        $request->validate([
            'bus_id' => 'required',
            'origin_id' => 'required',
            'destination_id' => 'required|different:origin_id',
            'times' => 'required|array',
            'active_days' => 'required|array',
            'price' => 'required|numeric',
        ]);

        ScheduleTemplate::create([
            'bus_id' => $request->bus_id,
            'origin_id' => $request->origin_id,
            'destination_id' => $request->destination_id,
            'price' => $request->price,
            'departure_times' => $request->times,
            'active_days' => $request->active_days,
            'is_active' => true
        ]);

        return redirect()->route('admin.templates')->with('success', 'Route Plan (Template) created successfully. The system will now auto-generate trips based on this plan.');
    }

    public function toggleTemplate($id)
    {
        $template = ScheduleTemplate::findOrFail($id);
        $template->is_active = ! $template->is_active;
        $template->save();

        $status = $template->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Template has been $status.");
    }

    public function deleteTemplate($id)
    {
        $template = ScheduleTemplate::findOrFail($id);
        $template->delete();

        return back()->with('success', 'Template deleted successfully.');
    }
}
