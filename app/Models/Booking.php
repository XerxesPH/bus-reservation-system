<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'return_schedule_id',
        'user_id',
        'booking_reference',
        'cancellation_reason',
        'guest_name',
        'guest_email',
        'guest_phone',
        'payment_method',
        'payment_status',
        'adults',
        'children',
        'seat_numbers',
        'return_seat_numbers',
        'total_price',
        'status',
        'trip_type',
        'linked_booking_id',
        'bus_number',
        'reserved_at',         // FIX #4: Track reservation time for expiry
    ];

    /**
     * Auto-generate Booking Reference only if not already set.
     * This allows round trip bookings to share the same reference.
     */
    protected static function booted()
    {
        static::creating(function ($booking) {
            if (empty($booking->booking_reference)) {
                $booking->booking_reference = 'BUS-' . strtoupper(\Illuminate\Support\Str::random(8));
            }
        });
    }

    /**
     * Generate a new booking reference string.
     * Use this to create a shared reference for round trips.
     */
    public static function generateReference(): string
    {
        return 'BUS-' . strtoupper(\Illuminate\Support\Str::random(8));
    }

    // AUTOMATICALLY CONVERT JSON TO ARRAY
    protected $casts = [
        'seat_numbers' => 'array',
        'return_seat_numbers' => 'array', // Added
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    // New relationship for the return trip
    public function returnSchedule()
    {
        return $this->belongsTo(Schedule::class, 'return_schedule_id');
    }

    /**
     * Get the linked booking (for round trips).
     * Outbound booking links to Return booking and vice versa.
     */
    public function linkedBooking()
    {
        return $this->belongsTo(Booking::class, 'linked_booking_id');
    }
}
