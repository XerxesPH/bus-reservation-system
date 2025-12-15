<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'return_schedule_id', // Added
        'user_id',
        'guest_name',
        'guest_email',
        'adults',
        'children',
        'seat_numbers',
        'return_seat_numbers', // Added
        'total_price',
        'status',
    ];

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
