<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'user_id',
        'guest_name',
        'guest_email',
        'seat_numbers',
        'total_price',
        'status'
    ];

    // AUTOMATICALLY CONVERT JSON TO ARRAY
    protected $casts = [
        'seat_numbers' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
