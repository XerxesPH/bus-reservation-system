<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * IMPORTANT: Drops the UNIQUE constraint from booking_reference column.
     * This allows Round Trip bookings to share the same reference ID
     * across two separate rows (Outbound + Return).
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Drop the unique index on booking_reference
            // The index name follows Laravel's convention: tablename_columnname_unique
            $table->dropUnique(['booking_reference']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Re-add the unique constraint if rolling back
            $table->unique('booking_reference');
        });
    }
};
