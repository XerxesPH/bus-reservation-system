<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds fields to support separate booking records for round trips.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Trip type: 'oneway', 'roundtrip_outbound', 'roundtrip_return'
            $table->string('trip_type')->default('oneway')->after('status');

            // Links outbound booking to return booking (and vice versa)
            $table->unsignedBigInteger('linked_booking_id')->nullable()->after('trip_type');

            // Foreign key constraint (self-referencing)
            $table->foreign('linked_booking_id')
                ->references('id')
                ->on('bookings')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['linked_booking_id']);
            $table->dropColumn(['trip_type', 'linked_booking_id']);
        });
    }
};
