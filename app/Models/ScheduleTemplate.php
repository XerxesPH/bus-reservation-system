<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'bus_id',
        'origin_id',
        'destination_id',
        'price',
        'departure_times',
        'active_days',
        'is_active',
    ];

    protected $casts = [
        'departure_times' => 'array',
        'active_days' => 'array',
        'is_active' => 'boolean',
    ];

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
}
