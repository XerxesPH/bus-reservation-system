<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bus extends Model
{
    use HasFactory;

    // 1. Allow these columns to be filled
    protected $fillable = [
        'code',      // e.g., BUS-101
        'type',      // e.g., Deluxe
        'capacity',   // e.g., 20
    ];

    // 2. Define Relationship: A bus has many trips (schedules)
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
