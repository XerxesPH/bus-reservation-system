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
            $table->unsignedBigInteger('return_schedule_id')->nullable()->after('schedule_id');
            $table->json('return_seat_numbers')->nullable()->after('seat_numbers');

            // Add foreign key constraint
            $table->foreign('return_schedule_id')->references('id')->on('schedules')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['return_schedule_id']);
            $table->dropColumn('return_schedule_id');
            $table->dropColumn('return_seat_numbers');
        });
    }
};
