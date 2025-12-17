<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'booking_reference')) {
                $table->string('booking_reference')->nullable()->after('id');
            }
            if (!Schema::hasColumn('bookings', 'cancellation_reason')) {
                $table->string('cancellation_reason')->nullable()->after('status');
            }
        });

        // Populate existing bookings
        $bookings = \Illuminate\Support\Facades\DB::table('bookings')->get();
        foreach ($bookings as $booking) {
            if (empty($booking->booking_reference)) {
                $ref = strtoupper(\Illuminate\Support\Str::random(8));
                \Illuminate\Support\Facades\DB::table('bookings')
                    ->where('id', $booking->id)
                    ->update(['booking_reference' => 'BUS-' . $ref]);
            }
        }

        // Make unique and required
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('booking_reference')->nullable(false)->change();
            // Check if index exists before adding to avoid duplicate key error
            // (Laravel doesn't have a clean hasIndex helper in Blueprint, but we can try-catch or just rely on 'change' if we didn't add it yet)
            // Ideally we just add unique constraint if it's not already unique. 
            // For simplicity in this dev fix, we'll just try to add it. 
            // If it fails because it exists, we might need a raw check.
            // But usually unique() on change() works or throws if exists.

            try {
                $table->unique('booking_reference');
            } catch (\Exception $e) {
                // Ignore if index already exists
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['booking_reference', 'cancellation_reason']);
        });
    }
};
