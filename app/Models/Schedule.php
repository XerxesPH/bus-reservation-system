<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    // 1. Allow these columns to be filled
    protected $fillable = [
        'bus_id',
        'origin_id',
        'destination_id',
        'departure_date',
        'departure_time',
        'price'
    ];

    // 2. Relationship: This schedule belongs to a specific Bus
    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    // 3. Relationship: origin_id links to the Terminal model
    public function origin()
    {
        return $this->belongsTo(Terminal::class, 'origin_id');
    }

    // 4. Relationship: destination_id links to the Terminal model
    public function destination()
    {
        return $this->belongsTo(Terminal::class, 'destination_id');
    }
}
