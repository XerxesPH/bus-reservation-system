<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * FIX #4: GHOST BOOKING PREVENTION
     * Adds reserved_at timestamp to track when a seat was reserved.
     * A scheduled job can release seats that have been "pending" for too long.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Timestamp when seat was reserved (for pending bookings)
            $table->timestamp('reserved_at')->nullable()->after('status');

            // Index for efficient cleanup queries
            $table->index(['status', 'reserved_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['status', 'reserved_at']);
            $table->dropColumn('reserved_at');
        });
    }
};
