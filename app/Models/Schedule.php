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
        // 1. Get all confirmed bookings for this specific trip
        $bookings = $this->bookings()->where('status', 'confirmed')->get();

        $bookedCount = 0;
        foreach ($bookings as $booking) {
            // 2. Sum up the seats from the JSON array
            $seats = $booking->seat_numbers;
            if (is_array($seats)) {
                $bookedCount += count($seats);
            }
        }

        // 3. Return (Bus Capacity) - (Booked Seats)
        // If bus relation is missing, default to 45
        return ($this->bus->capacity ?? 45) - $bookedCount;
    }
}
