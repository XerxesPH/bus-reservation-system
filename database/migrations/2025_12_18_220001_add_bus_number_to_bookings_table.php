<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * FIX #2: Add bus_number column to permanently lock the bus assignment to a ticket.
     * This prevents ticket data from changing if the schedule is later modified.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('bus_number')->nullable()->after('schedule_id');
        });

        // Backfill existing bookings with bus_number from their schedules
        $bookings = \App\Models\Booking::with('schedule.bus')->get();
        foreach ($bookings as $booking) {
            if ($booking->schedule && $booking->schedule->bus) {
                $booking->update(['bus_number' => $booking->schedule->bus->bus_number]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('bus_number');
        });
    }
};
