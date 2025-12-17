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
            $table->string('guest_phone')->nullable()->after('guest_email');
            $table->string('payment_method')->nullable()->after('total_price'); // e.g. 'card', 'ewallet'
            $table->string('payment_status')->default('pending')->after('payment_method'); // pending, paid, failed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['guest_phone', 'payment_method', 'payment_status']);
        });
    }
};
