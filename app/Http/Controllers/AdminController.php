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
use Illuminate\Support\Facades\Storage;

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
        $recentBookings = Booking::with(['schedule.origin', 'schedule.destination', 'schedule.bus'])
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
    public function schedules(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        // Get schedules ordered by date and time
        // Pagination is important as there could be many
        $query = Schedule::with(['bus', 'origin', 'destination']);

        if ($q !== '') {
            $terms = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY) ?: [];

            foreach ($terms as $term) {
                $like = '%' . $term . '%';

                $query->where(function ($sub) use ($term, $like) {
                    if (ctype_digit($term)) {
                        $sub->orWhere('id', (int) $term);
                    }

                    $sub->orWhere('departure_date', 'like', $like)
                        ->orWhere('departure_time', 'like', $like)
                        ->orWhere('status', 'like', $like)
                        ->orWhere('price', 'like', $like)
                        ->orWhereHas('bus', function ($busQ) use ($like) {
                            $busQ->where('code', 'like', $like)
                                ->orWhere('type', 'like', $like);
                        })
                        ->orWhereHas('origin', function ($termQ) use ($like) {
                            $termQ->where('city', 'like', $like)
                                ->orWhere('name', 'like', $like)
                                ->orWhere('province', 'like', $like);
                        })
                        ->orWhereHas('destination', function ($termQ) use ($like) {
                            $termQ->where('city', 'like', $like)
                                ->orWhere('name', 'like', $like)
                                ->orWhere('province', 'like', $like);
                        });
                });
            }
        }

        $schedules = $query
            ->orderBy('departure_date', 'desc')
            ->orderBy('departure_time', 'asc')
            ->paginate(15)
            ->appends($request->query());

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
            'type' => 'required|in:deluxe,regular,luxury',
            'capacity' => 'required|integer|min:10|max:60',
            'driver_name' => 'required|string',
            'driver_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->only(['code', 'type', 'capacity', 'driver_name']);
        $fixedCapacity = Bus::fixedCapacityForType($request->type);
        if ($fixedCapacity !== null) {
            $data['capacity'] = $fixedCapacity;
        }

        if ($request->hasFile('driver_image')) {
            $path = $request->file('driver_image')->store('drivers', 'public');
            $data['driver_image'] = $path;
        }

        Bus::create($data);

        return redirect()->route('admin.buses')->with('success', 'Bus added successfully!');
    }

    // --- BOOKING MANAGER ---

    public function bookings(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $query = Booking::with([
            'user',
            'schedule.origin',
            'schedule.destination',
            'schedule.bus',
        ]);

        if ($q !== '') {
            $terms = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY) ?: [];

            foreach ($terms as $term) {
                $like = '%' . $term . '%';

                $query->where(function ($sub) use ($term, $like) {
                    if (ctype_digit($term)) {
                        $sub->orWhere('id', (int) $term);
                    }

                    $sub->orWhere('booking_reference', 'like', $like)
                        ->orWhere('guest_name', 'like', $like)
                        ->orWhere('guest_email', 'like', $like)
                        ->orWhere('guest_phone', 'like', $like)
                        ->orWhere('status', 'like', $like)
                        ->orWhere('payment_method', 'like', $like)
                        ->orWhere('payment_status', 'like', $like)
                        ->orWhere('trip_type', 'like', $like)
                        ->orWhere('bus_number', 'like', $like)
                        ->orWhere('total_price', 'like', $like)
                        ->orWhereHas('schedule', function ($scheduleQ) use ($like) {
                            $scheduleQ->where('departure_date', 'like', $like)
                                ->orWhere('departure_time', 'like', $like)
                                ->orWhere('status', 'like', $like)
                                ->orWhere('price', 'like', $like);
                        })
                        ->orWhereHas('schedule.bus', function ($busQ) use ($like) {
                            $busQ->where('code', 'like', $like)
                                ->orWhere('type', 'like', $like);
                        })
                        ->orWhereHas('schedule.origin', function ($termQ) use ($like) {
                            $termQ->where('city', 'like', $like)
                                ->orWhere('name', 'like', $like)
                                ->orWhere('province', 'like', $like);
                        })
                        ->orWhereHas('schedule.destination', function ($termQ) use ($like) {
                            $termQ->where('city', 'like', $like)
                                ->orWhere('name', 'like', $like)
                                ->orWhere('province', 'like', $like);
                        });
                });
            }
        }

        // Get all bookings, newest first, with pagination
        $bookings = $query
            ->latest()
            ->paginate(10)
            ->appends($request->query());

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
            'code' => 'required|string|unique:buses,code,' . $id,
            'type' => 'required|in:deluxe,regular,luxury',
            'capacity' => 'required|integer|min:10|max:60',
            'driver_name' => 'nullable|string|max:255',
            'driver_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $bus = Bus::findOrFail($id);

        $data = $request->only(['code', 'type', 'capacity', 'driver_name']);
        $fixedCapacity = Bus::fixedCapacityForType($request->type);
        if ($fixedCapacity !== null) {
            $data['capacity'] = $fixedCapacity;
        }

        if ($request->hasFile('driver_image')) {
            if ($bus->driver_image && Storage::disk('public')->exists($bus->driver_image)) {
                Storage::disk('public')->delete($bus->driver_image);
            }
            $data['driver_image'] = $request->file('driver_image')->store('drivers', 'public');
        }

        $bus->update($data);

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

    // ==========================================
    // 8. PASSENGER MANIFEST (DRIVER HANDOUT)
    // ==========================================

    /**
     * Show passenger manifest for a specific schedule/trip.
     * This is the list the driver needs before departure.
     */
    public function manifest($scheduleId)
    {
        $schedule = Schedule::with(['bus', 'origin', 'destination'])
            ->findOrFail($scheduleId);

        // Get all confirmed bookings for this trip
        $bookings = Booking::where('schedule_id', $scheduleId)
            ->where('status', 'confirmed')
            ->orderBy('created_at')
            ->get();

        // Calculate totals
        $totalPassengers = $bookings->sum('adults') + $bookings->sum('children');
        $totalRevenue = $bookings->sum('total_price');
        $seatsBooked = $bookings->pluck('seat_numbers')->flatten()->unique()->count();

        return view('admin.manifest', compact(
            'schedule',
            'bookings',
            'totalPassengers',
            'totalRevenue',
            'seatsBooked'
        ));
    }

    // ==========================================
    // 9. CONTACT MESSAGES INBOX
    // ==========================================

    /**
     * List all contact form messages.
     */
    public function messages()
    {
        $messages = \App\Models\Message::orderBy('created_at', 'desc')
            ->paginate(15);

        $unreadCount = \App\Models\Message::where('is_read', false)->count();

        return view('admin.messages.index', compact('messages', 'unreadCount'));
    }

    /**
     * View a single message and mark as read.
     */
    public function showMessage($id)
    {
        $message = \App\Models\Message::findOrFail($id);

        // Mark as read
        if (!$message->is_read) {
            $message->update(['is_read' => true]);
        }

        return view('admin.messages.show', compact('message'));
    }

    /**
     * Delete a message.
     */
    public function deleteMessage($id)
    {
        \App\Models\Message::findOrFail($id)->delete();

        return back()->with('success', 'Message deleted.');
    }

    // ==========================================
    // 10. SALES REPORTS
    // ==========================================

    /**
     * Sales report with filters.
     */
    public function salesReport(Request $request)
    {
        $period = $request->get('period', 'today');
        $startDate = null;
        $endDate = Carbon::now();

        switch ($period) {
            case 'today':
                $startDate = Carbon::today();
                break;
            case 'yesterday':
                $startDate = Carbon::yesterday();
                $endDate = Carbon::yesterday()->endOfDay();
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                break;
            case 'custom':
                $startDate = Carbon::parse($request->get('start_date', Carbon::today()));
                $endDate = Carbon::parse($request->get('end_date', Carbon::now()));
                break;
            default:
                $startDate = Carbon::today();
        }

        $startDate = $startDate->copy()->startOfDay();
        $endDate = $endDate->copy()->endOfDay();

        // Revenue Query
        $revenue = Booking::where('status', 'confirmed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_price');

        $bookingsCount = Booking::where('status', 'confirmed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $cancelledCount = Booking::where('status', 'cancelled')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Daily breakdown for charts
        $dailyRevenue = Booking::where('status', 'confirmed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(total_price) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top routes by revenue
        $topRoutes = Booking::where('bookings.status', 'confirmed')
            ->whereBetween('bookings.created_at', [$startDate, $endDate])
            ->join('schedules', 'bookings.schedule_id', '=', 'schedules.id')
            ->join('terminals as origin', 'schedules.origin_id', '=', 'origin.id')
            ->join('terminals as dest', 'schedules.destination_id', '=', 'dest.id')
            ->selectRaw('CONCAT(origin.city, " → ", dest.city) as route, SUM(bookings.total_price) as total, COUNT(*) as bookings')
            ->groupBy('route')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return view('admin.reports.sales', compact(
            'period',
            'startDate',
            'endDate',
            'revenue',
            'bookingsCount',
            'cancelledCount',
            'dailyRevenue',
            'topRoutes'
        ));
    }

    // ==========================================
    // 11. USER MANAGEMENT
    // ==========================================

    /**
     * List all users.
     */
    public function users(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $query = User::where('role', 'user');

        if ($q !== '') {
            $terms = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY) ?: [];

            foreach ($terms as $term) {
                $like = '%' . $term . '%';
                $normalized = strtolower($term);

                $query->where(function ($sub) use ($term, $like, $normalized) {
                    if (ctype_digit($term)) {
                        $sub->orWhere('id', (int) $term);
                    }

                    if ($normalized === 'banned') {
                        $sub->orWhere('is_banned', true);
                    }

                    if ($normalized === 'active') {
                        $sub->orWhere(function ($q2) {
                            $q2->whereNull('is_banned')->orWhere('is_banned', false);
                        });
                    }

                    $sub->orWhere('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('contact_number', 'like', $like)
                        ->orWhere('created_at', 'like', $like);
                });
            }
        }

        $users = $query
            ->withCount('bookings')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->appends($request->query());

        return view('admin.users.index', compact('users'));
    }

    /**
     * Toggle user ban status.
     */
    public function toggleUserBan($id)
    {
        $user = User::findOrFail($id);

        // Don't allow banning admins
        if ($user->role === 'admin') {
            return back()->with('error', 'Cannot ban an admin user.');
        }

        $user->is_banned = !($user->is_banned ?? false);
        $user->save();

        $status = $user->is_banned ? 'banned' : 'unbanned';
        return back()->with('success', "User {$user->name} has been {$status}.");
    }
}
