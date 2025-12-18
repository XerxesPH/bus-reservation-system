<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ReleaseExpiredReservations extends Command
{
    /**
     * The name and signature of the console command.
     * 
     * FIX #4: GHOST BOOKING CLEANUP
     * Releases seats that have been "pending" for more than 15 minutes.
     */
    protected $signature = 'bookings:release-expired {--minutes=15 : Minutes before a pending reservation expires}';

    /**
     * The console command description.
     */
    protected $description = 'Release seats from pending bookings that have expired (default: 15 minutes)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $minutes = $this->option('minutes');
        $expiryTime = Carbon::now()->subMinutes($minutes);

        // Find and cancel expired pending reservations
        $expiredBookings = Booking::where('status', 'pending')
            ->where('reserved_at', '<', $expiryTime)
            ->get();

        $count = $expiredBookings->count();

        foreach ($expiredBookings as $booking) {
            $booking->update([
                'status' => 'expired',
                'cancellation_reason' => 'Reservation expired after ' . $minutes . ' minutes',
            ]);

            $this->info("Released booking #{$booking->id} (Ref: {$booking->booking_reference})");
        }

        $this->info("Released {$count} expired reservation(s).");

        return Command::SUCCESS;
    }
}
