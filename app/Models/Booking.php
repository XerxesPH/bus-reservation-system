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
        'booking_reference', // Added
        'cancellation_reason', // Added
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
    ];

    /**
     * Auto-generate Booking Reference
     */
    protected static function booted()
    {
        static::creating(function ($booking) {
            $booking->booking_reference = 'BUS-' . strtoupper(\Illuminate\Support\Str::random(8));
        });
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
}
