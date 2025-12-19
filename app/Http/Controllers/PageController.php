<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Schedule;
use App\Models\Terminal;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Display the bus schedule.
     */
    public function schedule(Request $request)
    {
        // Fetch all terminals for the search dropdowns
        $terminals = Terminal::all();

        $filters = $request->validate([
            'origin' => ['nullable', 'string', 'max:120'],
            'destination' => ['nullable', 'string', 'max:120'],
            'date' => ['nullable', 'date'],
        ]);

        // Fetch active schedules, ordered by date/time
        // Filter: Show trips that are strictly in the future
        $schedulesQuery = Schedule::where('status', 'scheduled')
            ->where(function ($query) {
                $query->whereDate('departure_date', '>', \Carbon\Carbon::today())
                    ->orWhere(function ($q) {
                        $q->whereDate('departure_date', \Carbon\Carbon::today())
                            ->whereTime('departure_time', '>', \Carbon\Carbon::now());
                    });
            })

            ->with(['origin', 'destination', 'bus']);

        if (!empty($filters['origin'])) {
            $origin = $filters['origin'];
            $originParts = array_values(array_filter(array_map('trim', preg_split('/\s*-\s*/', $origin))));
            $schedulesQuery->whereHas('origin', function ($q) use ($originParts, $origin) {
                $parts = !empty($originParts) ? $originParts : [$origin];
                foreach ($parts as $term) {
                    $q->where(function ($qq) use ($term) {
                        $qq->where('city', 'like', "%{$term}%")
                            ->orWhere('name', 'like', "%{$term}%");
                    });
                }
            });
        }

        if (!empty($filters['destination'])) {
            $destination = $filters['destination'];
            $destinationParts = array_values(array_filter(array_map('trim', preg_split('/\s*-\s*/', $destination))));
            $schedulesQuery->whereHas('destination', function ($q) use ($destinationParts, $destination) {
                $parts = !empty($destinationParts) ? $destinationParts : [$destination];
                foreach ($parts as $term) {
                    $q->where(function ($qq) use ($term) {
                        $qq->where('city', 'like', "%{$term}%")
                            ->orWhere('name', 'like', "%{$term}%");
                    });
                }
            });
        }

        if (!empty($filters['date'])) {
            $schedulesQuery->whereDate('departure_date', $filters['date']);
        }

        $schedules = $schedulesQuery
            ->orderBy('departure_date')
            ->orderBy('departure_time')
            ->paginate(15);

        return view('pages.schedule', compact('schedules', 'terminals', 'filters'));
    }

    /**
     * Display the contact page.
     */
    public function contact()
    {
        return view('pages.contact');
    }

    /**
     * Handle contact form submission with spam protection.
     */
    public function submitContact(Request $request)
    {
        // SECURITY: Honeypot check - if filled, it's a bot
        if ($request->filled('website')) {
            // Silent redirect for bots - don't reveal detection
            return redirect()->route('pages.contact')->with('success', 'Message sent successfully!');
        }

        // SECURITY: Timestamp check - form submitted too fast = bot
        try {
            $formTime = decrypt($request->_form_time);
            if (time() - $formTime < 3) { // Less than 3 seconds
                return redirect()->route('pages.contact')->with('error', 'Please take your time filling the form.');
            }
        } catch (\Exception $e) {
            abort(403, 'Invalid form submission.');
        }

        // Validate form inputs
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'subject' => ['required', 'string', 'in:booking,refund,feedback,complaint,other'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        // Sanitize message to prevent stored XSS
        $validated['message'] = strip_tags($validated['message']);

        // FIX #1: Save message to database for Admin dashboard
        Message::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'is_read' => false,
        ]);

        return redirect()->route('pages.contact')->with('success', 'Thank you! Your message has been sent successfully.');
    }

    /**
     * Display the terminals/locations page with Google Maps.
     */
    public function terminals()
    {
        $terminals = Terminal::all();
        return view('pages.terminals', compact('terminals'));
    }
}
