<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bus extends Model
{
    use HasFactory;

    public static function fixedCapacityForType(?string $type): ?int
    {
        return match (strtolower((string) $type)) {
            'regular' => 40,
            'deluxe' => 20,
            default => null,
        };
    }

    protected static function booted()
    {
        static::saving(function (self $bus) {
            $fixed = self::fixedCapacityForType($bus->type);
            if ($fixed !== null) {
                $bus->attributes['capacity'] = $fixed;
            }
        });
    }

    // 1. Allow these columns to be filled
    protected $fillable = [
        'code',      // e.g., BUS-101
        'type',      // e.g., Deluxe
        'capacity',   // e.g., 20
        'driver_name',
        'driver_image',
    ];

    public function getCapacityAttribute($value)
    {
        $fixed = self::fixedCapacityForType($this->attributes['type'] ?? null);
        return $fixed ?? $value;
    }

    // 2. Define Relationship: A bus has many trips (schedules)
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
