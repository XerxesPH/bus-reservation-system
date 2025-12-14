<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Bus;
use App\Models\Terminal;
use App\Models\Schedule;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // 1. Create Terminals
        $manila = Terminal::create(['name' => 'Cubao Station', 'city' => 'Manila']);
        $baguio = Terminal::create(['name' => 'Gov Pack Station', 'city' => 'Baguio']);
        $pampanga = Terminal::create(['name' => 'Dau Terminal', 'city' => 'Pampanga']);

        // 2. Create Buses
        $bus1 = Bus::create(['code' => 'BUS-101', 'type' => 'deluxe', 'capacity' => 20]);
        $bus2 = Bus::create(['code' => 'BUS-102', 'type' => 'regular', 'capacity' => 40]);

        // 3. Create Schedules (Trips) for Today
        Schedule::create([
            'bus_id' => $bus1->id,
            'origin_id' => $manila->id,
            'destination_id' => $baguio->id,
            'departure_date' => now()->format('Y-m-d'), // Today
            'departure_time' => '08:00:00',
            'price' => 550.00
        ]);

        Schedule::create([
            'bus_id' => $bus2->id,
            'origin_id' => $manila->id,
            'destination_id' => $pampanga->id,
            'departure_date' => now()->format('Y-m-d'), // Today
            'departure_time' => '10:00:00',
            'price' => 200.00
        ]);
    }
}
