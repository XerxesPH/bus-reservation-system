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
        Schema::create('schedule_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bus_id')->constrained()->onDelete('cascade');
            $table->foreignId('origin_id')->constrained('terminals');
            $table->foreignId('destination_id')->constrained('terminals');
            $table->decimal('price', 8, 2);
            $table->json('departure_times'); // Store times like ["08:00", "12:00"]
            $table->json('active_days'); // Store days like ["Mon", "Wed", "Fri"]
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_templates');
    }
};
