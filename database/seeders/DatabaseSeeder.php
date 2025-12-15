<?php

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\Schedule;
use App\Models\Terminal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // 1. CREATE TERMINALS
        // ==========================================
        $terminalsData = [
            'Cubao' => ['city' => 'Manila (Cubao)', 'name' => 'Cubao Victory Liner Terminal'],
            'Pasay' => ['city' => 'Manila (Pasay)', 'name' => 'PITX Parañaque'],
            'Baguio' => ['city' => 'Baguio',         'name' => 'Gov. Pack Road Terminal'],
            'Pampanga' => ['city' => 'Pampanga',       'name' => 'Dau Bus Terminal'],
            'LaUnion' => ['city' => 'La Union',       'name' => 'San Fernando Terminal'],
            'Vigan' => ['city' => 'Vigan',          'name' => 'Partas Vigan Station'],
            'Laoag' => ['city' => 'Laoag',          'name' => 'Laoag City Terminal'],
            'Tuguegarao' => ['city' => 'Tuguegarao',     'name' => 'Tuguegarao City Terminal'],
            'Batangas' => ['city' => 'Batangas',       'name' => 'Batangas Grand Terminal'],
            'Naga' => ['city' => 'Naga',           'name' => 'Bicol Central Station'],
            'Legazpi' => ['city' => 'Legazpi',        'name' => 'Legazpi Grand Terminal'],
        ];

        // Store terminal objects
        $t = [];
        foreach ($terminalsData as $key => $data) {
            $t[$key] = Terminal::create($data);
        }

        // ==========================================
        // 2. DEFINE ROUTES (Fully Connected Mesh)
        // ==========================================
        // Format: [Origin, Destination, Duration(Hrs), Price, RegularCount, DeluxeCount]
        // We ensure AT LEAST 2 Regular and 2 Deluxe per route.

        $routes = [
            // --- FROM CUBAO (North & South Hub) ---
            ['Cubao', 'Baguio',     6,  750,  2, 2],
            ['Cubao', 'Pampanga',   2,  300,  2, 2],
            ['Cubao', 'LaUnion',    5,  550,  2, 2],
            ['Cubao', 'Vigan',      9,  900,  2, 2],
            ['Cubao', 'Laoag',      11, 1100, 2, 2],
            ['Cubao', 'Tuguegarao', 12, 1200, 2, 2],
            ['Cubao', 'Batangas',   3,  250,  2, 2],
            ['Cubao', 'Naga',       9,  1100, 2, 2],
            ['Cubao', 'Legazpi',    11, 1300, 2, 2],

            // --- FROM PASAY (South & North Hub) ---
            ['Pasay', 'Baguio',     7,  780,  2, 2], // Direct from PITX
            ['Pasay', 'Pampanga',   3,  350,  2, 2],
            ['Pasay', 'Batangas',   2,  200,  2, 2],
            ['Pasay', 'Naga',       8,  1050, 2, 2],
            ['Pasay', 'Legazpi',    10, 1250, 2, 2],

            // --- METRO SHUTTLE ---
            ['Cubao', 'Pasay',      1,  100,  2, 2],

            // --- NORTH LUZON INTER-PROVINCIAL ---
            ['Pampanga', 'Baguio',    4,  450,  2, 2],
            ['Pampanga', 'LaUnion',   3,  350,  2, 2],
            ['Pampanga', 'Vigan',     7,  700,  2, 2], // New
            ['Pampanga', 'Laoag',     9,  900,  2, 2], // New
            ['Baguio',   'LaUnion',   2,  150,  2, 2],
            ['Baguio',   'Vigan',     5,  500,  2, 2], // New: Baguio -> Vigan
            ['Baguio',   'Tuguegarao', 8,  750,  2, 2], // New: Baguio -> Tuguegarao
            ['LaUnion',  'Vigan',     4,  400,  2, 2],
            ['Vigan',    'Laoag',     2,  150,  2, 2],
            ['Laoag',    'Tuguegarao', 5,  500,  2, 2],

            // --- SOUTH LUZON INTER-PROVINCIAL ---
            ['Batangas', 'Naga',      7,  800,  2, 2],
            ['Naga',     'Legazpi',   3,  200,  2, 2],
        ];

        // ==========================================
        // 3. GENERATE FLEET & SCHEDULES
        // ==========================================
        $daysToCheck = 7; // Generate for 1 week
        $busCounter = 1000;

        foreach ($routes as $routeCfg) {
            [$originKey, $destKey, $duration, $price, $regCount, $dlxCount] = $routeCfg;

            $origin = $t[$originKey];
            $dest = $t[$destKey];

            // 3a. Create Fleet
            $fleet = [];

            // Create Regular Buses (Capacity 40)
            for ($i = 0; $i < $regCount; $i++) {
                $fleet[] = Bus::create([
                    'code' => 'REG-'.$busCounter++,
                    'type' => 'regular',
                    'capacity' => 40, // Requested Capacity
                ]);
            }
            // Create Deluxe Buses (Capacity 20)
            for ($i = 0; $i < $dlxCount; $i++) {
                $fleet[] = Bus::create([
                    'code' => 'DLX-'.$busCounter++,
                    'type' => 'deluxe',
                    'capacity' => 20, // Requested Capacity
                ]);
            }

            // 3b. Smart Rotation Schedule
            for ($day = 0; $day < $daysToCheck; $day++) {
                $date = Carbon::today()->addDays($day)->format('Y-m-d');
                $isEvenDay = ($day % 2 == 0);

                foreach ($fleet as $index => $bus) {
                    // Stagger departures (6am, 7am, 8am...) to avoid overlap
                    $baseTime = 6 + $index;
                    if ($baseTime > 20) {
                        $baseTime = 6;
                    } // Wrap around if too late

                    $depTime = sprintf('%02d:00:00', $baseTime);

                    if ($duration < 8) {
                        // --- SAME DAY RETURN ---
                        // Leg 1: A -> B
                        $this->createTrip($bus, $origin, $dest, $date, $depTime, $price);

                        // Leg 2: B -> A (after rest)
                        $returnHour = $baseTime + $duration + 2; // +2 hrs rest
                        if ($returnHour < 23) {
                            $returnTime = sprintf('%02d:00:00', $returnHour);
                            $this->createTrip($bus, $dest, $origin, $date, $returnTime, $price);
                        }
                    } else {
                        // --- NEXT DAY RETURN ---
                        // We split the fleet so there is service BOTH ways every day.
                        // Half start at Origin, Half start at Destination.

                        // Determine starting point based on (Bus Index + Day) parity
                        $startAtOrigin = ($isEvenDay && $index % 2 == 0) || (! $isEvenDay && $index % 2 != 0);

                        if ($startAtOrigin) {
                            $this->createTrip($bus, $origin, $dest, $date, $depTime, $price);
                        } else {
                            $this->createTrip($bus, $dest, $origin, $date, $depTime, $price);
                        }
                    }
                }
            }
        }

        // ==========================================
        // 4. CREATE TEST USER
        // ==========================================
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    private function createTrip($bus, $origin, $dest, $date, $time, $price)
    {
        // Adjust price for Deluxe (+20%)
        if ($bus->type === 'deluxe') {
            $price = $price * 1.2;
        }

        Schedule::create([
            'bus_id' => $bus->id,
            'origin_id' => $origin->id,
            'destination_id' => $dest->id,
            'departure_date' => $date,
            'departure_time' => $time,
            'price' => $price,
        ]);
    }
}
