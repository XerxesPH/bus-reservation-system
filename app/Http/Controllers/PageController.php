<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Terminal;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Display the bus schedule.
     */
    public function schedule()
    {
        // Fetch all terminals for the search dropdowns
        $terminals = Terminal::all();

        // Fetch active schedules, ordered by date/time
        // Filter: Show trips that are strictly in the future
        $schedules = Schedule::where('status', 'scheduled')
            ->where(function ($query) {
                $query->whereDate('departure_date', '>', \Carbon\Carbon::today())
                    ->orWhere(function ($q) {
                        $q->whereDate('departure_date', \Carbon\Carbon::today())
                            ->whereTime('departure_time', '>', \Carbon\Carbon::now());
                    });
            })
            ->with(['origin', 'destination', 'bus'])
            ->orderBy('departure_date')
            ->orderBy('departure_time')
            ->paginate(15);

        return view('pages.schedule', compact('schedules', 'terminals'));
    }
}
