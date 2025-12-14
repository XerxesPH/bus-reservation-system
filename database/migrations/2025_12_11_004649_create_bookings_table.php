<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained(); // Which trip?
            $table->string('guest_name');     // Name of passenger
            $table->string('guest_email');    // Contact info
            $table->json('seat_numbers');     // Stores array like ["1", "2", "5"]
            $table->integer('total_price');
            $table->string('status')->default('confirmed'); // confirmed/cancelled
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
