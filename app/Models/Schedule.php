<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'bus_id',
        'origin_id',
        'destination_id',
        'departure_date',
        'departure_time',
        'price',
        'status',
    ];

    // IMPORTANT: This tells Laravel to always calculate 'seats_left'
    protected $appends = ['seats_left'];

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function origin()
    {
        return $this->belongsTo(Terminal::class, 'origin_id');
    }

    public function destination()
    {
        return $this->belongsTo(Terminal::class, 'destination_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // THIS IS THE MISSING LOGIC
    public function getSeatsLeftAttribute()
    {
        // 1. Count seats where this schedule is the OUTBOUND leg
        $outboundBookings = $this->bookings()->where('status', 'confirmed')->get();
        $bookedCount = 0;
        foreach ($outboundBookings as $b) {
            if (is_array($b->seat_numbers)) {
                $bookedCount += count($b->seat_numbers);
            }
        }

        // 2. Count seats where this schedule is the RETURN leg
        // We can't use the simple $this->bookings() relation here because that only looks at schedule_id
        $returnBookings = Booking::where('return_schedule_id', $this->id)
            ->where('status', 'confirmed')
            ->get();

        foreach ($returnBookings as $b) {
            if (is_array($b->return_seat_numbers)) {
                $bookedCount += count($b->return_seat_numbers);
            }
        }

        $capacity = $this->bus->capacity ?? 0;
        return $capacity - $bookedCount;
    }
}
